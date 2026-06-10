<?php

namespace App\Http\Controllers\v2\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

     public function show(Request $request, WalletService $walletService): JsonResponse
{
    $user = $request->user();

    $walletService->ensureUserWallets($user);

    $user->load('userWallets.wallet');

    $walletDetails = $user->userWallets
        ->filter(function ($userWallet) {
            return $userWallet->wallet
                && in_array($userWallet->wallet->type, ['spin', 'discount']);
        })
        ->sortBy(function ($userWallet) {
            return $userWallet->wallet->type === 'spin' ? 1 : 2;
        })
        ->map(function ($userWallet) {
            return [
                'wallet_id' => $userWallet->wallet->id,
                'wallet_name' => $userWallet->wallet->name,
                'balance' => (float) $userWallet->balance,
            ];
        })
        ->values();

    $name = $user->name ?: $user->phone_number;
    $phone = $user->phone_number ?: '';

    return $this->successResponse([
        'id' => $user->id,
        'name' => $user->name,
        'status' => is_object($user->status) ? (int) $user->status->value : (int) $user->status,
        'phone_number' => $user->phone_number,
        'user_type' => is_object($user->user_type) ? (int) $user->user_type->value : (int) $user->user_type,
        'passcode' => empty($user->passcode) ? 0 : 1,
        'signature' => $this->generateQrString($name, $phone),
        'wallets' => $walletDetails,
    ], '');
}
   private function generateQrString(string $name, string $phone): string
{
    $dataPayload = $this->createTlv('59', $name) . $this->createTlv('99', $phone);

    $crcValue = $this->crc16CcittFalse($dataPayload);

    $crcHex = sprintf('%04X', $crcValue);

    $crcTlv = $this->createTlv('63', $crcHex);

    return $dataPayload . $crcTlv;
}

private function createTlv(string $tag, string $value): string
{
    $length = sprintf('%02d', strlen($value));

    return $tag . $length . $value;
}

private function crc16CcittFalse(string $data): int
{
    $crc = 0xFFFF;
    $length = strlen($data);

    for ($i = 0; $i < $length; $i++) {
        $crc ^= ord($data[$i]) << 8;

        for ($j = 0; $j < 8; $j++) {
            if ($crc & 0x8000) {
                $crc = ($crc << 1) ^ 0x1021;
            } else {
                $crc <<= 1;
            }
        }
    }

    return $crc & 0xFFFF;
}
    public function saveFcmToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => ['required', 'string'],
        ]);

        $user = $request->user();
        $user->fcm_token = $validated['fcm_token'];
        $user->save();

        return $this->successResponse([], 'FCM token saved successfully');
    }
}