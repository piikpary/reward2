<?php

namespace App\Http\Controllers\v2\Transfer;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\OtpService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class TransferController extends Controller
{
    use ApiResponse;

    public function checkReceiver(Request $request, OtpService $otpService): JsonResponse
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'string'],
        ]);

        $currentUser = $request->user();
        $phone = $otpService->normalizePhone($validated['phone_number']);

        $receiver = User::where('phone_number', $phone)->first();

        if (!$receiver) {
            return $this->errorResponse('Receiver not found', 404);
        }

        if ($receiver->id === $currentUser->id) {
            return $this->errorResponse('You cannot transfer to yourself', 400);
        }

        return $this->successResponse([
            'receiver' => [
                'id' => $receiver->id,
                'phone_number' => $receiver->phone_number,
            ],
        ], '');
    }

    public function transferSpin(Request $request, OtpService $otpService, WalletService $walletService): JsonResponse
    {
        $validated = $request->validate([
            'receiver_phone' => ['required', 'string'],
            'amount' => ['required', 'integer', 'min:1'],
            'passcode' => ['required', 'digits:4'],
        ]);

        return $this->transferWallet(
            $request,
            $otpService,
            $walletService,
            $validated['receiver_phone'],
            (float) $validated['amount'],
            $validated['passcode'],
            'spin',
            'Spin transfer successful'
        );
    }

    public function transferDiscount(Request $request, OtpService $otpService, WalletService $walletService): JsonResponse
    {
        $validated = $request->validate([
            'receiver_phone' => ['required', 'string'],
            'discount_percentage' => ['required', 'numeric', 'min:1'],
            'passcode' => ['required', 'digits:4'],
        ]);

        return $this->transferWallet(
            $request,
            $otpService,
            $walletService,
            $validated['receiver_phone'],
            (float) $validated['discount_percentage'],
            $validated['passcode'],
            'discount',
            'Discount transferred successfully'
        );
    }

    private function transferWallet(
        Request $request,
        OtpService $otpService,
        WalletService $walletService,
        string $receiverPhone,
        float $amount,
        string $passcode,
        string $walletType,
        string $successMessage
    ): JsonResponse {
        $sender = $request->user();

        $passcodeLimitResponse = $this->checkPasscodeLimit($sender, $passcode);

        if ($passcodeLimitResponse) {
            return $passcodeLimitResponse;
        }

        $receiverPhone = $otpService->normalizePhone($receiverPhone);

        $receiver = User::where('phone_number', $receiverPhone)->first();

        if (!$receiver) {
            return $this->errorResponse('Receiver not found', 404);
        }

        if ($receiver->id === $sender->id) {
            return $this->errorResponse('You cannot transfer to yourself', 400);
        }

        $walletService->ensureUserWallets($sender);
        $walletService->ensureUserWallets($receiver);

        try {
            $result = DB::transaction(function () use ($sender, $receiver, $amount, $walletType) {
                $wallet = Wallet::where('type', $walletType)->firstOrFail();

                $senderWallet = UserWallet::where('user_id', $sender->id)
                    ->where('wallet_id', $wallet->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $receiverWallet = UserWallet::where('user_id', $receiver->id)
                    ->where('wallet_id', $wallet->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((float) $senderWallet->balance < $amount) {
                    throw new \Exception("Insufficient {$walletType} balance", 400);
                }

                $senderWallet->balance = (float) $senderWallet->balance - $amount;
                $senderWallet->save();

                $receiverWallet->balance = (float) $receiverWallet->balance + $amount;
                $receiverWallet->save();

                WalletTransaction::create([
                    'user_id' => $sender->id,
                    'wallet_id' => $wallet->id,
                    'transaction_type' => 'transfer_out',
                    'wallet_type' => $walletType,
                    'amount' => $amount,
                    'from_user_id' => $sender->id,
                    'to_user_id' => $receiver->id,
                    'description' => $walletType === 'spin'
                        ? 'Transfer Spin to User'
                        : 'Transfer Discount to User',
                ]);

                WalletTransaction::create([
                    'user_id' => $receiver->id,
                    'wallet_id' => $wallet->id,
                    'transaction_type' => 'transfer_in',
                    'wallet_type' => $walletType,
                    'amount' => $amount,
                    'from_user_id' => $sender->id,
                    'to_user_id' => $receiver->id,
                    'description' => $walletType === 'spin'
                        ? 'Receive Spin from User'
                        : 'Receive Discount from User',
                ]);

                if ($walletType === 'spin') {
                    return [
                        'from_user_balance' => (float) $senderWallet->balance,
                        'to_user_balance' => (float) $receiverWallet->balance,
                        'transferred_amount' => $amount,
                    ];
                }

                return [
                    'from_user_discount_balance' => (float) $senderWallet->balance,
                    'to_user_discount_balance' => (float) $receiverWallet->balance,
                    'transferred_discount' => $amount,
                ];
            });

            $this->sendTransferNotification($sender, $receiver, $walletType, $amount);

            return $this->successResponse($result, $successMessage);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $this->safeCode($e->getCode()));
        }
    }

    private function sendTransferNotification(User $sender, User $receiver, string $walletType, float $amount): void
{
    if (empty($receiver->fcm_token)) {
        return;
    }

    $language = $receiver->language ?? 'en';

    if ($walletType === 'spin') {
        $title = $language === 'km'
            ? 'បានទទួល Spin'
            : 'Transfer Received';

        $body = $language === 'km'
            ? "អ្នកបានទទួល {$amount} Spin ពី {$sender->name}។"
            : "You have received {$amount} spin from {$sender->name}.";
    } else {
        $title = $language === 'km'
            ? 'បានទទួលការបញ្ចុះតម្លៃ'
            : 'Transfer Received';

        $body = $language === 'km'
            ? "អ្នកបានទទួលការបញ្ចុះតម្លៃ {$amount}% ពី {$sender->name}។"
            : "You have received {$amount}% discount from {$sender->name}.";
    }

    try {
        $receiver->increment('notification_badge_count');
        $badgeCount = (int) $receiver->fresh()->notification_badge_count;
        sendFcmNotification($receiver->fcm_token, $title, $body, [
            'type' => 'transfer',
            'wallet_type' => $walletType,
            'amount' => $amount,
            'from_user_id' => $sender->id,
            'from_phone' => $sender->phone_number,
            'to_user_id' => $receiver->id,
            'language' => $language,
            'badge' => $badgeCount,
        ]);
    } catch (\Throwable $e) {
        \Log::error('Transfer FCM notification failed', [
            'message' => $e->getMessage(),
            'receiver_id' => $receiver->id,
            'receiver_phone' => $receiver->phone_number,
        ]);
    }
}

    private function checkPasscodeLimit(User $sender, string $passcode): ?JsonResponse
    {
        $key = 'passcode_verify:' . $sender->id;

        $maxAttempts = (int) env('PASSCODE_VERIFY_MAX_ATTEMPTS', 3);
        $decaySeconds = (int) env('PASSCODE_VERIFY_DECAY_SECONDS', 300);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'message' => 'Too many wrong passcode attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.',
                'data' => [
                    'retry_after_seconds' => $seconds,
                    'retry_after_minutes' => (int) ceil($seconds / 60),
                ],
            ], 429);
        }

        if (!$sender->passcode || !Hash::check($passcode, $sender->passcode)) {
            RateLimiter::hit($key, $decaySeconds);

            $attempts = RateLimiter::attempts($key);
            $remainingAttempts = max($maxAttempts - $attempts, 0);

            return response()->json([
                'success' => false,
                'message' => 'Invalid passcode',
                'data' => [
                    'remaining_attempts' => $remainingAttempts,
                ],
            ], 400);
        }

        RateLimiter::clear($key);

        return null;
    }  

    private function safeCode($code): int
    {
        $code = (int) $code;

        return $code >= 100 && $code <= 599 ? $code : 400;
    }
}