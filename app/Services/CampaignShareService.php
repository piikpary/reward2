<?php

namespace App\Services;

use App\Models\CampaignShare;
use App\Models\CampaignShareReward;
use App\Models\CampaignUserProgress;
use App\Models\ShareCampaign;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CampaignShareService
{
    public function __construct(
        private readonly FacebookPostUrlService $urlService
    ) {
    }

    /**
     * Verify and save the user's Facebook share.
     *
     * This method does not give spins automatically.
     * Spins must be granted manually from the portal.
     */
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
             * Lock the campaign to safely update total_shares.
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
             * Prevent the same Facebook post URL
             * from being submitted more than once.
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
             * Create one progress record for each
             * user and campaign.
             */
            CampaignUserProgress::query()
                ->insertOrIgnore([
                    'share_campaign_id' =>
                        $campaign->id,

                    'user_id' =>
                        $user->id,

                    'current_shares' =>
                        0,

                    'rewards_earned_count' =>
                        0,

                    'last_milestone_rewarded' =>
                        0,

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);

            /*
             * Lock the user's campaign progress.
             */
            $progress = CampaignUserProgress::query()
                ->where(
                    'share_campaign_id',
                    $campaign->id
                )
                ->where(
                    'user_id',
                    $user->id
                )
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * Save the verified Facebook post.
             */
            $share = CampaignShare::query()->create([
                'share_campaign_id' =>
                    $campaign->id,

                'user_id' =>
                    $user->id,

                'facebook_post_url' =>
                    $normalizedUrl,

                'facebook_post_url_hash' =>
                    $urlHash,

                'status' =>
                    'verified',

                'verification_method' =>
                    'url_format',

                'shared_at' =>
                    now(),

                'verified_at' =>
                    now(),

                'ip_address' =>
                    $ipAddress,

                'user_agent' =>
                    $userAgent,

                'metadata' => [
                    'normalized_url' =>
                        $normalizedUrl,
                ],
            ]);

            /*
             * Update user progress.
             */
            $progress->current_shares =
                (int) $progress->current_shares + 1;

            $progress->last_shared_at =
                $share->shared_at;

            $progress->save();

            /*
             * Update total campaign shares.
             */
            $campaign->total_shares =
                (int) $campaign->total_shares + 1;

            $campaign->save();

            /*
             * Calculate the manual reward status.
             * This does not change the user's wallet.
             */
            $rewardSummary = $this->rewardSummary(
                $campaign,
                $progress
            );

            return [
                'campaignId' =>
                    $campaign->id,

                'campaignTitle' =>
                    $campaign->title,

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

                /*
                 * Always false here because only an
                 * admin can give the spins.
                 */
                'spinAwarded' =>
                    false,

                'rewardSpinsAwarded' =>
                    0,

                'eligibleForReward' =>
                    $rewardSummary[
                        'eligible_for_reward'
                    ],

                'rewardStatus' =>
                    $rewardSummary[
                        'reward_status'
                    ],

                'eligibleMilestones' =>
                    $rewardSummary[
                        'eligible_milestone'
                    ],

                'pendingMilestones' =>
                    $rewardSummary[
                        'pending_milestones'
                    ],

                'grantedMilestones' =>
                    $rewardSummary[
                        'granted_milestones'
                    ],

                'nextPendingMilestone' =>
                    $rewardSummary[
                        'next_pending_milestone'
                    ],

                'pendingSpins' =>
                    $rewardSummary[
                        'pending_milestones'
                    ] * (int) $campaign->reward_spins,

                'rewardClaimed' =>
                    $rewardSummary[
                        'reward_claimed'
                    ],

                /*
                 * Read the current balance only.
                 * Do not increase it here.
                 */
                'totalSpins' =>
                    $this->currentSpinBalance(
                        $user->id
                    ),

                'sharedAt' =>
                    $share
                        ->shared_at
                        ->toISOString(),
            ];
        }, 5);
    }

    /**
     * Calculate reward status for one user
     * and one campaign.
     */
    public function rewardSummary(
        ShareCampaign $campaign,
        CampaignUserProgress $progress,
        ?Collection $rewards = null
    ): array {
        $eligibleMilestone =
            $this->eligibleMilestone(
                $campaign,
                (int) $progress->current_shares
            );

        /*
         * CampaignShareReward records represent
         * rewards that have already been manually granted.
         */
        $rewards ??= CampaignShareReward::query()
            ->where(
                'share_campaign_id',
                $campaign->id
            )
            ->where(
                'user_id',
                $progress->user_id
            )
            ->get();

        $grantedMilestoneNumbers = $rewards
            ->pluck('milestone_number')
            ->map(
                fn ($value): int =>
                    (int) $value
            )
            ->unique()
            ->sort()
            ->values();

        /*
         * Find the first eligible milestone that
         * has not received spins yet.
         */
        $nextPendingMilestone = null;

        for (
            $milestone = 1;
            $milestone <= $eligibleMilestone;
            $milestone++
        ) {
            if (
                !$grantedMilestoneNumbers
                    ->contains($milestone)
            ) {
                $nextPendingMilestone =
                    $milestone;

                break;
            }
        }

        $grantedEligibleCount =
            $grantedMilestoneNumbers
                ->filter(
                    fn (int $milestone): bool =>
                        $milestone <=
                        $eligibleMilestone
                )
                ->count();

        $pendingMilestones = max(
            0,
            $eligibleMilestone
                - $grantedEligibleCount
        );

        if ($pendingMilestones > 0) {
            $rewardStatus = 'pending';
        } elseif (
            $grantedMilestoneNumbers->isNotEmpty()
        ) {
            $rewardStatus = 'granted';
        } else {
            $rewardStatus = 'not_eligible';
        }

        return [
            'eligible_milestone' =>
                $eligibleMilestone,

            'next_pending_milestone' =>
                $nextPendingMilestone,

            'pending_milestones' =>
                $pendingMilestones,

            'granted_milestones' =>
                $grantedMilestoneNumbers->count(),

            'eligible_for_reward' =>
                $pendingMilestones > 0,

            'reward_status' =>
                $rewardStatus,

            'reward_claimed' =>
                $grantedMilestoneNumbers
                    ->isNotEmpty(),
        ];
    }

    /**
     * Calculate how many reward milestones
     * the user has reached.
     */
    public function eligibleMilestone(
        ShareCampaign $campaign,
        int $currentShares
    ): int {
        $requiredShares =
            (int) $campaign->required_shares;

        if ($requiredShares < 1) {
            return 0;
        }

        if ($campaign->reward_repeatable) {
            return intdiv(
                $currentShares,
                $requiredShares
            );
        }

        return $currentShares >= $requiredShares
            ? 1
            : 0;
    }

    /**
     * Calculate how many more shares are needed.
     *
     * Returns zero when a reward is already eligible
     * but still waiting for admin approval.
     */
    public function remainingShares(
        ShareCampaign $campaign,
        ?CampaignUserProgress $progress
    ): int {
        $requiredShares =
            (int) $campaign->required_shares;

        if ($requiredShares < 1) {
            return 0;
        }

        $currentShares =
            (int) (
                $progress?->current_shares ?? 0
            );

        $lastRewardedMilestone =
            (int) (
                $progress
                    ?->last_milestone_rewarded ?? 0
            );

        $eligibleMilestone =
            $this->eligibleMilestone(
                $campaign,
                $currentShares
            );

        /*
         * The user is eligible, but the admin
         * has not granted the reward yet.
         */
        if (
            $eligibleMilestone >
            $lastRewardedMilestone
        ) {
            return 0;
        }

        /*
         * Non-repeatable campaign already finished.
         */
        if (
            !$campaign->reward_repeatable
            && $lastRewardedMilestone >= 1
        ) {
            return 0;
        }

        if (!$campaign->reward_repeatable) {
            return max(
                0,
                $requiredShares - $currentShares
            );
        }

        $nextMilestone =
            $lastRewardedMilestone + 1;

        $nextThreshold =
            $nextMilestone * $requiredShares;

        return max(
            0,
            $nextThreshold - $currentShares
        );
    }

    /**
     * Read the current spin wallet balance.
     *
     * This method does not modify the balance.
     */
    private function currentSpinBalance(
        int $userId
    ): float {
        $spinWalletId = Wallet::query()
            ->where(
                'type',
                'spin'
            )
            ->value('id');

        if (!$spinWalletId) {
            return 0;
        }

        return (float) UserWallet::query()
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'wallet_id',
                $spinWalletId
            )
            ->value('balance');
    }
}