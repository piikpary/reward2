<?php

namespace App\Http\Controllers\v2\Spin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\SpinReward;
use Illuminate\Support\Facades\DB;

class SpinController extends Controller
{
    use ApiResponse;

    public function getDiscount(Request $request, WalletService $walletService): JsonResponse
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();
        $qty = (int) $validated['qty'];

        $walletService->ensureUserWallets($user);

        try {
            $result = DB::transaction(function () use ($user, $qty) {
                $spinWallet = Wallet::where('type', 'spin')->firstOrFail();
                $discountWallet = Wallet::where('type', 'discount')->firstOrFail();

                $userSpinWallet = UserWallet::where('user_id', $user->id)
                    ->where('wallet_id', $spinWallet->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $userDiscountWallet = UserWallet::where('user_id', $user->id)
                    ->where('wallet_id', $discountWallet->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((float) $userSpinWallet->balance < $qty) {
                    throw new \Exception('Insufficient spin balance', 400);
                }

                $discountPercentage = $this->generateDiscountPercentage();

                $userSpinWallet->balance = (float) $userSpinWallet->balance - $qty;
                $userSpinWallet->save();

                $userDiscountWallet->balance = (float) $userDiscountWallet->balance + $discountPercentage;
                $userDiscountWallet->save();

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $spinWallet->id,
                    'transaction_type' => 'spin_used',
                    'wallet_type' => 'spin',
                    'amount' => $qty,
                    'from_user_id' => $user->id,
                    'to_user_id' => null,
                    'description' => 'Spin used to get discount',
                ]);

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $discountWallet->id,
                    'transaction_type' => 'discount_earned',
                    'wallet_type' => 'discount',
                    'amount' => $discountPercentage,
                    'from_user_id' => null,
                    'to_user_id' => $user->id,
                    'description' => 'Discount earned from spin',
                ]);

                return [
                    'discount_percentage' => $discountPercentage,
                    'remaining_spins' => (float) $userSpinWallet->balance,
                ];
            });

            return $this->successResponse($result, 'Spin successful');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $this->safeCode($e->getCode()));
        }
    }

    private function generateDiscountPercentage(): int
{
    $rewards = SpinReward::query()
        ->where('status', true)
        ->where('chance_weight', '>', 0)
        ->orderBy('sort_order')
        ->get();

    if ($rewards->isEmpty()) {
        $fallback = [5, 10, 15, 20, 25, 30, 35, 50];
        return $fallback[array_rand($fallback)];
    }

    $totalWeight = $rewards->sum('chance_weight');
    $random = random_int(1, $totalWeight);

    $current = 0;

    foreach ($rewards as $reward) {
        $current += $reward->chance_weight;

        if ($random <= $current) {
            return (int) $reward->discount_percentage;
        }
    }

    return (int) $rewards->last()->discount_percentage;
}

    private function safeCode($code): int
    {
        $code = (int) $code;

        return $code >= 100 && $code <= 599 ? $code : 400;
    }
}