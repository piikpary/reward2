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
     * Submit a Facebook link for manual review.
     *
     * This method does not verify the post,
     * increase campaign progress, or add spins.
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
            $campaign = ShareCampaign::query()
                ->whereKey($campaign->id)
                ->lockForUpdate()
                ->first();

            if (
                !$campaign
                || !$campaign->isAvailable()
            ) {
                throw ValidationException::withMessages([
                    'campaign_id' =>
                        'This campaign is no longer available.',
                ]);
            }
            /*
 * Load and lock existing approved progress.
 *
 * A completed one-time campaign must not accept
 * another Facebook share submission.
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
    ->first();

if (
    !$campaign->reward_repeatable
    && $progress
    && (
        (int) $progress->current_shares
            >= (int) $campaign->required_shares
        || (int) $progress->last_milestone_rewarded >= 1
    )
) {
    throw ValidationException::withMessages([
        'campaign_id' =>
            'You have already completed this campaign and received its reward.',
    ]);
}

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

            $submittedAt = now();

            $reviewDueAt = $submittedAt
                ->copy()
                ->addDays(7);

            /*
             * Save the link as pending.
             *
             * Do not create or increment progress here.
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
                    CampaignShare::STATUS_PENDING,

                'verification_method' =>
                    'manual_review',

                'shared_at' =>
                    $submittedAt,

                'verified_at' =>
                    null,

                'reviewed_by' =>
                    null,

                'reviewed_at' =>
                    null,

                'rejection_reason' =>
                    null,

                'ip_address' =>
                    $ipAddress,

                'user_agent' =>
                    $userAgent,

                'metadata' => [
                    'normalized_url' =>
                        $normalizedUrl,

                    'review_within_days' =>
                        7,

                    'review_due_at' =>
                        $reviewDueAt->toISOString(),
                ],
            ]);


            $rewardSummary = [
                'eligible_milestone' => 0,
                'next_pending_milestone' => null,
                'pending_milestones' => 0,
                'granted_milestones' => 0,
                'eligible_for_reward' => false,
                'reward_status' => 'not_eligible',
                'reward_claimed' => false,
            ];

            if ($progress) {
                $rewardSummary =
                    $this->rewardSummary(
                        $campaign,
                        $progress
                    );
            }

            return [
                'shareId' =>
                    $share->id,

                'campaignId' =>
                    $campaign->id,

                'campaignTitle' =>
                    $campaign->title,

                /*
                 * Submission status is different
                 * from reward status.
                 */
                'submissionStatus' =>
                    'pending_review',

                'reviewWithinDays' =>
                    7,

                'reviewDueAt' =>
                    $reviewDueAt->toISOString(),

                /*
                 * Count only approved shares.
                 */
                'currentShares' =>
                    (int) (
                        $progress
                            ?->current_shares ?? 0
                    ),

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
                 * API submission never awards spins.
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

                'pendingMilestones' =>
                    $rewardSummary[
                        'pending_milestones'
                    ],

                'grantedMilestones' =>
                    $rewardSummary[
                        'granted_milestones'
                    ],

                'rewardClaimed' =>
                    $rewardSummary[
                        'reward_claimed'
                    ],

                'totalSpins' =>
                    $this->currentSpinBalance(
                        $user->id
                    ),

                'sharedAt' =>
                    $submittedAt->toISOString(),
            ];
        }, 5);
    }

    /**
     * Calculate reward information using only
     * manually approved campaign shares.
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

        $rewards ??=
            CampaignShareReward::query()
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
 * Return the authenticated user's share and reward
 * status for one campaign.
 */
public function userShareStatus(
    User $user,
    ShareCampaign $campaign
): array {
    /*
     * Progress contains only admin-approved shares.
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
        ->first();

    /*
     * Get the latest submitted Facebook link.
     *
     * This may be pending, approved, or rejected.
     */
    $latestShare = CampaignShare::query()
        ->where(
            'share_campaign_id',
            $campaign->id
        )
        ->where(
            'user_id',
            $user->id
        )
        ->orderByDesc('shared_at')
        ->orderByDesc('id')
        ->first();

    /*
     * Get granted reward milestone records.
     */
    $rewards = CampaignShareReward::query()
        ->where(
            'share_campaign_id',
            $campaign->id
        )
        ->where(
            'user_id',
            $user->id
        )
        ->get();

    $currentShares = (int) (
        $progress?->current_shares ?? 0
    );

    $eligibleMilestone = $this->eligibleMilestone(
        $campaign,
        $currentShares
    );

    $grantedMilestoneNumbers = $rewards
        ->pluck('milestone_number')
        ->map(
            fn ($milestone): int =>
                (int) $milestone
        )
        ->unique()
        ->sort()
        ->values();

    /*
     * Only count granted milestones that the user
     * is currently eligible for.
     */
    $grantedEligibleCount =
        $grantedMilestoneNumbers
            ->filter(
                fn (int $milestone): bool =>
                    $milestone <= $eligibleMilestone
            )
            ->count();

    $pendingMilestones = max(
        0,
        $eligibleMilestone
            - $grantedEligibleCount
    );

    /*
     * Convert internal database status into the
     * status required by the mobile application.
     */
    $submissionStatus = match (
        $latestShare?->status
    ) {
        'pending' =>
            'pending_review',

        'verified' =>
            'approved',

        'rejected' =>
            'rejected',

        default =>
            'not_submitted',
    };

    $reviewWithinDays = null;
    $reviewDueAt = null;

    if ($latestShare) {
        $reviewWithinDays = (int) data_get(
            $latestShare->metadata,
            'review_within_days',
            7
        );

        $reviewDueAt = data_get(
            $latestShare->metadata,
            'review_due_at'
        );

        /*
         * Support old records where review_due_at
         * was not saved in metadata.
         */
        if (
            !$reviewDueAt
            && $latestShare->shared_at
        ) {
            $reviewDueAt = $latestShare
                ->shared_at
                ->copy()
                ->addDays($reviewWithinDays)
                ->toISOString();
        }
    }

    return [
        'currentShares' =>
            $currentShares,

        'requiredShares' =>
            (int) $campaign->required_shares,

        'remainingShares' =>
            $this->remainingShares(
                $campaign,
                $progress
            ),

        'userShareUrl' =>
            $latestShare?->facebook_post_url,

        'submissionStatus' =>
            $submissionStatus,

        'reviewWithinDays' =>
            $reviewWithinDays,

        'reviewDueAt' =>
            $reviewDueAt,

        'pendingMilestones' =>
            $pendingMilestones,

        'grantedMilestones' =>
            $grantedMilestoneNumbers->count(),

        'rewardClaimed' =>
            $grantedMilestoneNumbers
                ->isNotEmpty(),

        'totalSpins' =>
            $this->currentSpinBalance(
                $user->id
            ),

        'sharedAt' =>
            $latestShare
                ?->shared_at
                ?->toISOString(),
    ];
}

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

        if (
            $eligibleMilestone >
            $lastRewardedMilestone
        ) {
            return 0;
        }

        if (
            !$campaign->reward_repeatable
            && $lastRewardedMilestone >= 1
        ) {
            return 0;
        }

        $nextMilestone =
            $campaign->reward_repeatable
                ? max(
                    1,
                    $lastRewardedMilestone + 1
                )
                : 1;

        $nextThreshold =
            $nextMilestone
            * $requiredShares;

        return max(
            0,
            $nextThreshold - $currentShares
        );
    }

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