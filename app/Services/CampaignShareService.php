<?php

namespace App\Services;

use App\Models\CampaignShare;
use App\Models\CampaignShareReward;
use App\Models\CampaignUserProgress;
use App\Models\ShareCampaign;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CampaignShareService
{
    public function __construct(
        private readonly FacebookPostUrlService $urlService
    ) {
    }

    public function verify(
        User $user,
        ShareCampaign $campaign,
        string $facebookPostUrl,
        ?string $ipAddress,
        ?string $userAgent
    ): array {
        $normalizedUrl = $this->urlService
            ->normalize($facebookPostUrl);

        $urlHash = $this->urlService
            ->hash($normalizedUrl);

        return DB::transaction(function () use (
            $user,
            $campaign,
            $normalizedUrl,
            $urlHash,
            $ipAddress,
            $userAgent
        ): array {
            /*
             * Lock campaign to protect total_shares.
             */
            $campaign = ShareCampaign::query()
                ->whereKey($campaign->id)
                ->lockForUpdate()
                ->first();

            if (!$campaign || !$campaign->isAvailable()) {
                throw ValidationException::withMessages([
                    'campaign_id' =>
                        'This campaign is no longer available.',
                ]);
            }

            /*
             * Prevent duplicate Facebook URL.
             */
            $duplicate = CampaignShare::query()
                ->where(
                    'facebook_post_url_hash',
                    $urlHash
                )
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'facebook_post_url' =>
                        'This Facebook post has already been submitted.',
                ]);
            }

            /*
             * Create the progress row if this is the user's first share.
             */
            CampaignUserProgress::query()
                ->insertOrIgnore([
                    'share_campaign_id' => $campaign->id,
                    'user_id' => $user->id,
                    'current_shares' => 0,
                    'rewards_earned_count' => 0,
                    'last_milestone_rewarded' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            /*
             * Lock user progress.
             */
            $progress = CampaignUserProgress::query()
                ->where(
                    'share_campaign_id',
                    $campaign->id
                )
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * Save the verified share.
             */
            $share = CampaignShare::query()->create([
                'share_campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'facebook_post_url' => $normalizedUrl,
                'facebook_post_url_hash' => $urlHash,
                'status' => 'verified',
                'verification_method' => 'url_format',
                'shared_at' => now(),
                'verified_at' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'metadata' => [
                    'normalized_url' => $normalizedUrl,
                ],
            ]);

            $progress->current_shares++;
            $progress->last_shared_at = $share->shared_at;
            $progress->save();

            $campaign->total_shares++;
            $campaign->save();

            $eligibleMilestone = $this->eligibleMilestone(
                $campaign,
                $progress->current_shares
            );

            $rewardSpinsAwarded = 0;
            $totalSpins = $this->currentSpinBalance($user->id);

            if (
                $eligibleMilestone >
                $progress->last_milestone_rewarded
            ) {
                $spinWallet = Wallet::query()
                    ->where('type', 'spin')
                    ->first();

                if (!$spinWallet) {
                    throw ValidationException::withMessages([
                        'wallet' =>
                            'Spin wallet configuration was not found.',
                    ]);
                }

                /*
                 * Make sure the customer has a spin wallet.
                 */
                $userWallet = UserWallet::query()->firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'wallet_id' => $spinWallet->id,
                    ],
                    [
                        'balance' => 0,
                    ]
                );

                $userWallet = UserWallet::query()
                    ->whereKey($userWallet->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                for (
                    $milestone =
                        $progress->last_milestone_rewarded + 1;

                    $milestone <= $eligibleMilestone;

                    $milestone++
                ) {
                    $alreadyRewarded =
                        CampaignShareReward::query()
                            ->where(
                                'share_campaign_id',
                                $campaign->id
                            )
                            ->where(
                                'user_id',
                                $user->id
                            )
                            ->where(
                                'milestone_number',
                                $milestone
                            )
                            ->exists();

                    if ($alreadyRewarded) {
                        continue;
                    }

                    $rewardAmount = (int) $campaign->reward_spins;

                    $userWallet->balance =
                        (float) $userWallet->balance
                        + $rewardAmount;

                    $userWallet->save();

                    $walletTransaction =
                        WalletTransaction::query()->create([
                            'user_id' => $user->id,
                            'wallet_id' => $spinWallet->id,
                            'transaction_type' =>
                                'campaign_share_reward',
                            'wallet_type' => 'spin',
                            'amount' => $rewardAmount,
                            'from_user_id' => null,
                            'to_user_id' => $user->id,
                            'description' =>
                                "Campaign share reward. Campaign: {$campaign->id}, milestone: {$milestone}",
                        ]);

                    CampaignShareReward::query()->create([
                        'share_campaign_id' => $campaign->id,
                        'user_id' => $user->id,
                        'milestone_number' => $milestone,
                        'milestone_threshold' =>
                            $milestone
                            * $campaign->required_shares,
                        'reward_spins' => $rewardAmount,
                        'wallet_transaction_id' =>
                            $walletTransaction->id,
                        'awarded_at' => now(),
                    ]);

                    $rewardSpinsAwarded += $rewardAmount;
                    $progress->rewards_earned_count++;
                }

                $progress->last_milestone_rewarded =
                    $eligibleMilestone;

                $progress->save();

                $totalSpins = (float) $userWallet->balance;
            }

            return [
                'campaignTitle' => $campaign->title,
                'currentShares' =>
                    (int) $progress->current_shares,
                'requiredShares' =>
                    (int) $campaign->required_shares,
                'remainingShares' =>
                    $this->remainingShares(
                        $campaign,
                        $progress
                    ),
                'rewardSpins' =>
                    (int) $campaign->reward_spins,
                'spinAwarded' =>
                    $rewardSpinsAwarded > 0,
                'rewardSpinsAwarded' =>
                    $rewardSpinsAwarded,
                'totalSpins' => $totalSpins,
                'rewardClaimed' =>
                    $progress->rewards_earned_count > 0,
                'sharedAt' =>
                    $share->shared_at->toISOString(),
            ];
        }, 5);
    }

    public function remainingShares(
        ShareCampaign $campaign,
        ?CampaignUserProgress $progress
    ): int {
        $currentShares =
            (int) ($progress?->current_shares ?? 0);

        $lastMilestone =
            (int) (
                $progress?->last_milestone_rewarded ?? 0
            );

        if (
            !$campaign->reward_repeatable
            && $lastMilestone >= 1
        ) {
            return 0;
        }

        if (!$campaign->reward_repeatable) {
            return max(
                0,
                $campaign->required_shares - $currentShares
            );
        }

        $remainder =
            $currentShares % $campaign->required_shares;

        return $remainder === 0
            ? $campaign->required_shares
            : $campaign->required_shares - $remainder;
    }

    private function eligibleMilestone(
        ShareCampaign $campaign,
        int $currentShares
    ): int {
        if ($campaign->required_shares < 1) {
            return 0;
        }

        if ($campaign->reward_repeatable) {
            return intdiv(
                $currentShares,
                $campaign->required_shares
            );
        }

        return $currentShares >= $campaign->required_shares
            ? 1
            : 0;
    }

    private function currentSpinBalance(int $userId): float
    {
        $spinWalletId = Wallet::query()
            ->where('type', 'spin')
            ->value('id');

        if (!$spinWalletId) {
            return 0;
        }

        return (float) UserWallet::query()
            ->where('user_id', $userId)
            ->where('wallet_id', $spinWalletId)
            ->value('balance');
    }
}