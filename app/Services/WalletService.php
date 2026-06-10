<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;

class WalletService
{
    public function ensureUserWallets(User $user): void
    {
        $wallets = Wallet::whereIn('type', ['spin', 'discount'])->get();

        foreach ($wallets as $wallet) {
            UserWallet::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                ],
                [
                    'balance' => 0,
                ]
            );
        }
    }

    public function getWalletBalance(User $user, string $walletType): float
    {
        $userWallet = UserWallet::query()
            ->where('user_id', $user->id)
            ->whereHas('wallet', function ($query) use ($walletType) {
                $query->where('type', $walletType);
            })
            ->first();

        return $userWallet ? (float) $userWallet->balance : 0;
    }
}