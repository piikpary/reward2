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
use Illuminate\Http\Exceptions\HttpResponseException;

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
        /*
         * Lock the campaign so simultaneous upload
         * requests cannot exceed the maximum.
         */
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
         * Approved campaign progress.
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

        $requiredShares = max(
            0,
            (int) $campaign->required_shares
        );

        /*
         * currentShares counts only approved shares.
         */
        $currentShares = (int) (
            $progress?->current_shares ?? 0
        );

        $remainingShares = max(
            0,
            $requiredShares - $currentShares
        );

        /*
         * Count all links submitted by this user.
         *
         * Pending, approved, and rejected records
         * all use one upload slot.
         */
        $submittedShares = CampaignShare::query()
            ->where(
                'share_campaign_id',
                $campaign->id
            )
            ->where(
                'user_id',
                $user->id
            )
            ->count();

        $remainingUploads = max(
            0,
            $requiredShares - $submittedShares
        );

        /*
         * Block uploads after the user reaches the
         * campaign's required number of shares.
         */
        if (
            $requiredShares < 1
            || $submittedShares >= $requiredShares
            || $currentShares >= $requiredShares
        ) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,

                    'message' =>
                        'You have reached the maximum number of shares for this campaign.',

                    'errors' => [
                        'campaign_id' => [
                            "You have already submitted the maximum of {$requiredShares} shares for this campaign. You cannot upload more.",
                        ],
                    ],

                    'data' => [
                        'currentShares' =>
                            $currentShares,

                        'requiredShares' =>
                            $requiredShares,

                        'remainingShares' =>
                            $remainingShares,

                        'canUploadMore' =>
                            false,

                        'remainingUploads' =>
                            0,

                        'isComplete' =>
                        $currentShares >= $requiredShares,

                        'campaignId' =>
                            (int) $campaign->id,

                        'campaignTitle' =>
                            $campaign->title,
                    ],
                ], 422)
            );
        }

        /*
         * Prevent the same Facebook URL from being
         * submitted more than once.
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

        $submittedAt = now();

        $reviewDueAt = $submittedAt
            ->copy()
            ->addDays(7);

        /*
         * Save the new link as pending review.
         *
         * Do not increase progress or add spins here.
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

        /*
         * Calculate upload availability after the
         * newly submitted link.
         */
        $submittedSharesAfterUpload =
            $submittedShares + 1;

        $remainingUploadsAfterUpload = max(
            0,
            $requiredShares
                - $submittedSharesAfterUpload
        );

        $canUploadMoreAfterUpload =
            $campaign->isAvailable()
            && $remainingUploadsAfterUpload > 0
            && $currentShares < $requiredShares;

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
            $rewardSummary = $this->rewardSummary(
                $campaign,
                $progress
            );
        }

        return [
            'shareId' =>
                (int) $share->id,

            'campaignId' =>
                (int) $campaign->id,

            'campaignTitle' =>
                $campaign->title,

            'submissionStatus' =>
                'pending_review',

            'reviewWithinDays' =>
                7,

            'reviewDueAt' =>
                $reviewDueAt->toISOString(),

            /*
             * Pending uploads do not increase this.
             */
            'currentShares' =>
                $currentShares,

            'requiredShares' =>
                $requiredShares,

            'remainingShares' =>
                $remainingShares,

            'canUploadMore' =>
                $canUploadMoreAfterUpload,

            'remainingUploads' =>
                $remainingUploadsAfterUpload,

            'isComplete' =>
                $requiredShares > 0
                && $currentShares >= $requiredShares,

            'rewardSpins' =>
                (int) $campaign->reward_spins,

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

            'transaction' =>
                null,
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
/**
 * Return the authenticated user's share and reward
 * status for one campaign.
 */
public function userShareStatus(
    User $user,
    ShareCampaign $campaign
): array {
    /*
     * Progress includes only admin-approved shares.
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
     * Load all links submitted by this user
     * for this campaign, newest first.
     */
    $shares = CampaignShare::query()
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
        ->get();

    $latestShare = $shares->first();

    /*
     * Load all granted reward milestones.
     */
    $rewards = CampaignShareReward::query()
        ->with([
            'walletTransaction.fromUser',
            'walletTransaction.toUser',
        ])
        ->where(
            'share_campaign_id',
            $campaign->id
        )
        ->where(
            'user_id',
            $user->id
        )
        ->orderByDesc('awarded_at')
        ->orderByDesc('id')
        ->get();

        
    $currentShares = (int) (
    $progress?->current_shares ?? 0
);

$requiredShares = max(
    0,
    (int) $campaign->required_shares
);

$remainingShares = max(
    0,
    $requiredShares - $currentShares
);

/*
 * Every submitted link uses one upload slot.
 */
$submittedShares = $shares->count();

$remainingUploads = max(
    0,
    $requiredShares - $submittedShares
);

$canUploadMore =
    $campaign->isAvailable()
    && $remainingUploads > 0
    && $currentShares < $requiredShares;

    $isComplete =
    $requiredShares > 0
    && $currentShares >= $requiredShares;

$latestReward =
    $rewards->first();

$rewardTransaction =
    $latestReward?->walletTransaction;

$spinAwarded =
    $rewardTransaction !== null;

$rewardSpinsAwarded =
    $latestReward
        ? (int) $latestReward->reward_spins
        : 0;

$rewardStatus =
    $spinAwarded
        ? 'awarded'
        : (
            $isComplete
                ? 'pending'
                : 'not_eligible'
        );

        
    $rewardSummary = [
        'pending_milestones' => 0,
        'granted_milestones' => 0,
        'reward_claimed' => false,
    ];

    if ($progress) {
        $rewardSummary = $this->rewardSummary(
            $campaign,
            $progress,
            $rewards
        );
    }

    /*
     * Format all submitted links for the API.
     */
    $userShareUrls = $shares
        ->map(function (
            CampaignShare $share
        ): array {
            $reviewWithinDays = (int) data_get(
                $share->metadata,
                'review_within_days',
                7
            );

            $reviewDueAt = data_get(
                $share->metadata,
                'review_due_at'
            );

            /*
             * Support old records that do not have
             * review_due_at inside metadata.
             */
            if (
                !$reviewDueAt
                && $share->shared_at
            ) {
                $reviewDueAt = $share
                    ->shared_at
                    ->copy()
                    ->addDays($reviewWithinDays)
                    ->toISOString();
            }

            $submissionStatus = match (
                $share->status
            ) {
                CampaignShare::STATUS_PENDING =>
                    'pending_review',

                CampaignShare::STATUS_VERIFIED =>
                    'approved',

                CampaignShare::STATUS_REJECTED =>
                    'rejected',

                default =>
                    'not_submitted',
            };

            return [
                'id' =>
                    (int) $share->id,

                'url' =>
                    $share->facebook_post_url,

                'submissionStatus' =>
                    $submissionStatus,

                'sharedAt' =>
                    $share->shared_at
                        ?->toISOString(),

                'reviewDueAt' =>
                    $reviewDueAt,
            ];
        })
        ->values();

    /*
     * Top-level status represents the newest link.
     */
    $latestSubmissionStatus = match (
        $latestShare?->status
    ) {
        CampaignShare::STATUS_PENDING =>
            'pending_review',

        CampaignShare::STATUS_VERIFIED =>
            'approved',

        CampaignShare::STATUS_REJECTED =>
            'rejected',

        default =>
            'not_submitted',
    };

    $reviewWithinDays = null;
    $latestReviewDueAt = null;

    if ($latestShare) {
        $reviewWithinDays = (int) data_get(
            $latestShare->metadata,
            'review_within_days',
            7
        );

        $latestReviewDueAt = data_get(
            $latestShare->metadata,
            'review_due_at'
        );

        if (
            !$latestReviewDueAt
            && $latestShare->shared_at
        ) {
            $latestReviewDueAt = $latestShare
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
            $requiredShares,

        'remainingShares' =>
            $remainingShares,

        'canUploadMore' =>
            $canUploadMore,

        'remainingUploads' =>
            $remainingUploads,

        'isComplete' =>
            $isComplete,

        'rewardSpins' =>
            (int) $campaign->reward_spins,

        'spinAwarded' =>
            $spinAwarded,

        'rewardSpinsAwarded' =>
            $rewardSpinsAwarded,

        'rewardStatus' =>
            $rewardStatus,

        'userShareUrls' =>
            $userShareUrls->all(),

        /*
         * Status of the newest submitted link.
         */
        'submissionStatus' =>
            $latestSubmissionStatus,

        'reviewWithinDays' =>
            $reviewWithinDays,

        'reviewDueAt' =>
            $latestReviewDueAt,

        'pendingMilestones' =>
            (int) $rewardSummary[
                'pending_milestones'
            ],

        'grantedMilestones' =>
            (int) $rewardSummary[
                'granted_milestones'
            ],

        'rewardClaimed' =>
    $latestReward !== null,

'totalSpins' =>
    $this->currentSpinBalance(
        $user->id
    ),

'transaction' =>
    $rewardTransaction
        ? [
            'id' =>
                (int) $rewardTransaction->id,

            'transaction_type' =>
                $rewardTransaction
                    ->transaction_type,

            'wallet_type' =>
                $rewardTransaction
                    ->wallet_type,

            'amount' =>
                (float) $rewardTransaction
                    ->amount,

            'from' =>
                $rewardTransaction
                    ->fromUser
                    ?->phone_number,

            'to' =>
                $rewardTransaction
                    ->toUser
                    ?->phone_number,

            'special_reward_code' =>
                $rewardTransaction
                    ->special_reward_code,

            'description' =>
                $rewardTransaction
                    ->description,

            'campaign_id' =>
                (int) $campaign->id,

            'campaign_title' =>
                $campaign->title,

            'created_at' =>
                $rewardTransaction
                    ->created_at
                    ?->toISOString(),
        ]
        : null,
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