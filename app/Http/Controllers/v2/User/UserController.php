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

        $wallets = $user->userWallets()
            ->with('wallet')
            ->whereHas('wallet', function ($query) {
                $query->whereIn('type', ['spin', 'discount']);
            })
            ->get()
            ->map(function ($userWallet) {
                return [
                    'wallet_id' => $userWallet->wallet->id,
                    'wallet_name' => $userWallet->wallet->name,
                    'balance' => (float) $userWallet->balance,
                ];
            })
            ->values();

        return $this->successResponse([
            'id' => $user->id,
            'phone_number' => $user->phone_number,
            'wallets' => $wallets,
        ], '');
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