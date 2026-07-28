<?php

namespace App\Services;

use App\Jobs\SendRegistrationRewardNotificationJob;
use App\Models\RegistrationRewardSetting;
use App\Models\User;
use App\Models\UserRegistrationReward;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RegistrationRewardService
{
    private const SETTING_CACHE_KEY =
        'reward2:registration-reward-setting:v1';

    /**
     * Give the registration gift to a newly created
     * mobile application user.
     *
     * Call this only from the new-user registration
     * branch. Do not call it from login.
     */
    public function awardToNewUser(
        User $user
    ): ?array {
        $setting = $this->currentSetting();

        if (
            !$setting
            || !$setting['is_enabled']
        ) {
            return null;
        }

        $walletType =
            (string) $setting['wallet_type'];

        $amount =
            (float) $setting['amount'];

        if (
            !in_array(
                $walletType,
                ['spin', 'discount'],
                true
            )
            || $amount <= 0
        ) {
            Log::warning(
                'Registration reward setting is invalid',
                [
                    'wallet_type' => $walletType,
                    'amount' => $amount,
                ]
            );

            return null;
        }

        return DB::transaction(
            function () use (
                $user,
                $setting,
                $walletType,
                $amount
            ): ?array {
                /*
                 * Lock the user so simultaneous requests
                 * cannot reward the same account twice.
                 */
                $user = User::query()
                    ->whereKey($user->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                 * Fast duplicate check. The database
                 * unique constraint is the final guard.
                 */
                $alreadyAwarded =
                    UserRegistrationReward::query()
                        ->where(
                            'user_id',
                            $user->id
                        )
                        ->exists();

                if ($alreadyAwarded) {
                    return null;
                }

                $wallet = Wallet::query()
                    ->select([
                        'id',
                        'type',
                    ])
                    ->where(
                        'type',
                        $walletType
                    )
                    ->where(
                        'status',
                        true
                    )
                    ->first();

                if (!$wallet) {
                    throw ValidationException
                        ::withMessages([
                            'wallet' =>
                                ucfirst($walletType)
                                . ' wallet configuration was not found.',
                        ]);
                }

                /*
                 * Create only the selected wallet if
                 * it does not exist for this user.
                 */
                $userWallet =
                    UserWallet::query()
                        ->firstOrCreate(
                            [
                                'user_id' =>
                                    $user->id,

                                'wallet_id' =>
                                    $wallet->id,
                            ],
                            [
                                'balance' => 0,
                            ]
                        );

                $userWallet =
                    UserWallet::query()
                        ->whereKey(
                            $userWallet->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                $userWallet->balance =
                    (float) $userWallet->balance
                    + $amount;

                $userWallet->save();

                $displayAmount =
                    $this->formatAmount(
                        $amount
                    );

                $rewardName =
                    $walletType === 'spin'
                        ? 'Spins'
                        : 'Discounts';

                $description =
                    "Your registration was successful! "
                    . "Enjoy {$displayAmount} bonus "
                    . "{$rewardName} as our welcome gift";

                $walletTransaction =
                    WalletTransaction::query()
                        ->create([
                            'user_id' =>
                                $user->id,

                            'wallet_id' =>
                                $wallet->id,

                            'transaction_type' =>
                                'registration_reward',

                            'wallet_type' =>
                                $walletType,

                            'amount' =>
                                $amount,

                            'from_user_id' =>
                                null,

                            'to_user_id' =>
                                $user->id,

                            'special_reward_code' =>
                                null,

                            'description' =>
                                $description,
                        ]);

                $registrationReward =
                    UserRegistrationReward::query()
                        ->create([
                            'user_id' =>
                                $user->id,

                            'registration_reward_setting_id' =>
                                $setting['id'],

                            'wallet_transaction_id' =>
                                $walletTransaction->id,

                            'wallet_type' =>
                                $walletType,

                            'amount' =>
                                $amount,

                            'awarded_at' =>
                                now(),
                        ]);

                $userId =
                    (int) $user->id;

                $transactionId =
                    (int) $walletTransaction->id;

                /*
                 * Clear transaction response cache and
                 * send FCM only after successful commit.
                 */
                DB::afterCommit(
                    function () use (
                        $userId,
                        $walletType,
                        $amount,
                        $transactionId
                    ): void {
                        Cache::forget(
                            "reward2:api:user:{$userId}:transactions:v1"
                        );

                        Cache::forget(
                            "reward2:api:user:{$userId}:transactions:v2"
                        );

                        try {
                            SendRegistrationRewardNotificationJob
                                ::dispatch(
                                    $userId,
                                    $walletType,
                                    $amount,
                                    $transactionId
                                );
                        } catch (\Throwable $exception) {
                            Log::error(
                                'Registration reward notification dispatch failed',
                                [
                                    'message' =>
                                        $exception
                                            ->getMessage(),

                                    'user_id' =>
                                        $userId,

                                    'transaction_id' =>
                                        $transactionId,
                                ]
                            );
                        }
                    }
                );

                return [
                    'awarded' => true,

                    'registration_reward_id' =>
                        (int) $registrationReward->id,

                    'transaction_id' =>
                        $transactionId,

                    'transaction_type' =>
                        'registration_reward',

                    'wallet_type' =>
                        $walletType,

                    'amount' =>
                        $amount,

                    'balance' =>
                        (float) $userWallet->balance,

                    'description' =>
                        $description,
                ];
            },
            5
        );
    }

    public static function clearSettingCache(): void
    {
        Cache::forget(
            self::SETTING_CACHE_KEY
        );
    }

    private function currentSetting(): ?array
    {
        return Cache::remember(
            self::SETTING_CACHE_KEY,
            now()->addMinutes(10),
            function (): ?array {
                $setting =
                    RegistrationRewardSetting::query()
                        ->whereKey(1)
                        ->first([
                            'id',
                            'is_enabled',
                            'wallet_type',
                            'amount',
                        ]);

                if (!$setting) {
                    return null;
                }

                return [
                    'id' =>
                        (int) $setting->id,

                    'is_enabled' =>
                        (bool) $setting->is_enabled,

                    'wallet_type' =>
                        (string) $setting
                            ->wallet_type,

                    'amount' =>
                        (float) $setting->amount,
                ];
            }
        );
    }

    private function formatAmount(
        float $amount
    ): string {
        return rtrim(
            rtrim(
                number_format(
                    $amount,
                    2,
                    '.',
                    ''
                ),
                '0'
            ),
            '.'
        );
    }
}