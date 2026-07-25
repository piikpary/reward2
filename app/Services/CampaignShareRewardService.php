<?php

namespace App\Services;

use App\Jobs\SendCampaignShareRewardNotificationJob;
use App\Models\CampaignShareReward;
use App\Models\CampaignUserProgress;
use App\Models\ShareCampaign;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CampaignShareRewardService
{
    /**
     * Award the next eligible campaign share reward.
     *
     * Returns null when the user has not completed
     * the requirement or the reward was already given.
     */
    public function awardIfEligible(
        CampaignUserProgress $progress,
        ?int $grantedBy = null
    ): ?array {
        return DB::transaction(
            function () use (
                $progress,
                $grantedBy
            ): ?array {
                /*
                 * Lock progress to prevent two approval
                 * requests from giving the same reward.
                 */
                $progress = CampaignUserProgress::query()
                    ->whereKey($progress->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $campaign = ShareCampaign::query()
                    ->whereKey(
                        $progress->share_campaign_id
                    )
                    ->lockForUpdate()
                    ->first();

                $user = User::query()
                    ->whereKey($progress->user_id)
                    ->first();

                if (!$campaign || !$user) {
                    throw ValidationException::withMessages([
                        'reward' =>
                            'The campaign or user could not be found.',
                    ]);
                }

                $requiredShares = (int)
                    $campaign->required_shares;

                $currentShares = (int)
                    $progress->current_shares;

                if ($requiredShares < 1) {
                    throw ValidationException::withMessages([
                        'reward' =>
                            'The campaign required share amount is invalid.',
                    ]);
                }

                /*
                 * Calculate the number of reward
                 * milestones currently completed.
                 */
                if ($campaign->reward_repeatable) {
                    $eligibleMilestone = intdiv(
                        $currentShares,
                        $requiredShares
                    );
                } else {
                    $eligibleMilestone =
                        $currentShares >= $requiredShares
                            ? 1
                            : 0;
                }

                /*
                 * The user has not completed the
                 * campaign requirement yet.
                 */
                if ($eligibleMilestone < 1) {
                    return null;
                }

                /*
                 * Lock existing reward records and
                 * find the next unawarded milestone.
                 */
                $grantedMilestones =
                    CampaignShareReward::query()
                        ->where(
                            'share_campaign_id',
                            $campaign->id
                        )
                        ->where(
                            'user_id',
                            $user->id
                        )
                        ->lockForUpdate()
                        ->pluck('milestone_number')
                        ->map(
                            fn ($value): int =>
                                (int) $value
                        )
                        ->unique()
                        ->values();

                $nextMilestone = null;

                for (
                    $milestone = 1;
                    $milestone <= $eligibleMilestone;
                    $milestone++
                ) {
                    if (
                        !$grantedMilestones
                            ->contains($milestone)
                    ) {
                        $nextMilestone = $milestone;

                        break;
                    }
                }

                /*
                 * Reward was already given.
                 */
                if ($nextMilestone === null) {
                    return null;
                }

                $rewardAmount = (int)
                    $campaign->reward_spins;

                if ($rewardAmount < 1) {
                    throw ValidationException::withMessages([
                        'reward' =>
                            'The campaign reward spin amount is invalid.',
                    ]);
                }

                /*
                 * Find and lock the main spin wallet.
                 */
                $spinWallet = Wallet::query()
                    ->where('type', 'spin')
                    ->lockForUpdate()
                    ->first();

                if (!$spinWallet) {
                    throw ValidationException::withMessages([
                        'wallet' =>
                            'Spin wallet configuration was not found.',
                    ]);
                }

                /*
                 * Create the user's spin wallet when
                 * it does not already exist.
                 */
                $userWallet = UserWallet::query()
                    ->firstOrCreate(
                        [
                            'user_id' =>
                                $user->id,

                            'wallet_id' =>
                                $spinWallet->id,
                        ],
                        [
                            'balance' => 0,
                        ]
                    );

                /*
                 * Lock the balance before adding spins.
                 */
                $userWallet = UserWallet::query()
                    ->whereKey($userWallet->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $userWallet->balance =
                    (float) $userWallet->balance
                    + $rewardAmount;

                $userWallet->save();

                $description =
                    "Earn {$rewardAmount} Spins by successfully completing share campaign '{$campaign->title}'";

                /*
                 * Create the received spin transaction.
                 */
                $walletTransaction =
                    WalletTransaction::query()->create([
                        'user_id' =>
                            $user->id,

                        'wallet_id' =>
                            $spinWallet->id,

                        /*
                         * This follows the requested
                         * API transaction type.
                         */
                        'transaction_type' =>
                            'share_campaign_reward',

                        'wallet_type' =>
                            'spin',

                        'amount' =>
                            $rewardAmount,

                        'from_user_id' =>
                            null,

                        'to_user_id' =>
                            $user->id,

                        'special_reward_code' =>
                            null,

                        'description' =>
                            $description,
                    ]);

                /*
                 * Connect the campaign reward with
                 * its wallet transaction.
                 */
                $campaignReward =
                    CampaignShareReward::query()->create([
                        'share_campaign_id' =>
                            $campaign->id,

                        'user_id' =>
                            $user->id,

                        'milestone_number' =>
                            $nextMilestone,

                        'milestone_threshold' =>
                            $nextMilestone
                            * $requiredShares,

                        'reward_spins' =>
                            $rewardAmount,

                        'wallet_transaction_id' =>
                            $walletTransaction->id,

                        /*
                         * null means automatically
                         * granted by the system.
                         */
                        'granted_by' =>
                            $grantedBy,

                        'awarded_at' =>
                            now(),
                    ]);

                /*
                 * Synchronize progress reward values.
                 */
                $rewardQuery =
                    CampaignShareReward::query()
                        ->where(
                            'share_campaign_id',
                            $campaign->id
                        )
                        ->where(
                            'user_id',
                            $user->id
                        );

                $progress->rewards_earned_count =
                    $rewardQuery->count();

                $progress->last_milestone_rewarded =
                    (int) (
                        $rewardQuery->max(
                            'milestone_number'
                        ) ?? 0
                    );

                $progress->save();

                $userId = (int) $user->id;
                $campaignId = (int) $campaign->id;
                $transactionId = (int)
                    $walletTransaction->id;

                /*
                 * Send the alert only after all
                 * database changes are committed.
                 */
                DB::afterCommit(
                    function () use (
                        $userId,
                        $campaignId,
                        $rewardAmount,
                        $transactionId
                    ): void {
                        try {
                            SendCampaignShareRewardNotificationJob
                                ::dispatch(
                                    $userId,
                                    $campaignId,
                                    $rewardAmount,
                                    $transactionId
                                );
                        } catch (\Throwable $exception) {
                            Log::error(
                                'Campaign reward notification dispatch failed',
                                [
                                    'message' =>
                                        $exception->getMessage(),

                                    'user_id' =>
                                        $userId,

                                    'campaign_id' =>
                                        $campaignId,

                                    'transaction_id' =>
                                        $transactionId,
                                ]
                            );
                        }
                    }
                );

                return [
                    'awarded' =>
                        true,

                    'campaign_id' =>
                        (int) $campaign->id,

                    'campaign_title' =>
                        $campaign->title,

                    'milestone' =>
                        $nextMilestone,

                    'reward_spins' =>
                        $rewardAmount,

                    'balance' =>
                        (float) $userWallet->balance,

                    'campaign_reward_id' =>
                        (int) $campaignReward->id,

                    'transaction_id' =>
                        (int) $walletTransaction->id,

                    'transaction' => [
                        'id' =>
                            (int) $walletTransaction->id,

                        'transaction_type' =>
                            $walletTransaction
                                ->transaction_type,

                        'wallet_type' =>
                            $walletTransaction
                                ->wallet_type,

                        'amount' =>
                            (float) $walletTransaction
                                ->amount,

                        'from' =>
                            null,

                        'to' =>
                            $user->phone_number,

                        'special_reward_code' =>
                            $walletTransaction
                                ->special_reward_code,

                        'description' =>
                            $walletTransaction
                                ->description,

                        'created_at' =>
                            $walletTransaction
                                ->created_at
                                ?->toISOString(),
                    ],
                ];
            },
            5
        );
    }
}