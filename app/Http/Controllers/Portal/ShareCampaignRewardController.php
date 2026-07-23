<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\CampaignShare;
use App\Models\CampaignShareReward;
use App\Models\CampaignUserProgress;
use App\Models\ShareCampaign;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\CampaignShareService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class ShareCampaignRewardController extends Controller
{
    public function __construct(
        private readonly CampaignShareService
            $campaignShareService
    ) {
    }

    /**
     * Display one row for every user and campaign.
     */
    public function index(
        Request $request
    ): View {
        $search = trim(
            $request
                ->string('search')
                ->toString()
        );

        $status = $request
            ->string('status')
            ->toString();

        $campaignId =
            $request->integer(
                'campaign_id'
            );

        $progresses =
            CampaignUserProgress::query()
                ->with([
                    'campaign',
                    'user',
                ])
                ->where(
                    'current_shares',
                    '>',
                    0
                )
                ->when(
                    $campaignId > 0,
                    fn ($query) =>
                        $query->where(
                            'share_campaign_id',
                            $campaignId
                        )
                )
                ->when(
                    $search !== '',
                    function ($query) use (
                        $search
                    ): void {
                        $query->where(
                            function ($query) use (
                                $search
                            ): void {
                                $query
                                    ->whereHas(
                                        'user',
                                        function (
                                            $query
                                        ) use (
                                            $search
                                        ): void {
                                            $query
                                                ->where(
                                                    'phone_number',
                                                    'like',
                                                    "%{$search}%"
                                                )
                                                ->orWhere(
                                                    'name',
                                                    'like',
                                                    "%{$search}%"
                                                )
                                                ->orWhere(
                                                    'email',
                                                    'like',
                                                    "%{$search}%"
                                                );
                                        }
                                    )
                                    ->orWhereHas(
                                        'campaign',
                                        function (
                                            $query
                                        ) use (
                                            $search
                                        ): void {
                                            $query->where(
                                                'title',
                                                'like',
                                                "%{$search}%"
                                            );
                                        }
                                    );
                            }
                        );
                    }
                )
                ->orderByDesc(
                    'last_shared_at'
                )
                ->get()
                ->filter(
                    fn (
                        CampaignUserProgress $progress
                    ): bool =>
                        $progress->campaign !== null
                        && $progress->user !== null
                )
                ->values();

        $userIds = $progresses
            ->pluck('user_id')
            ->unique()
            ->values();

        $campaignIds = $progresses
            ->pluck(
                'share_campaign_id'
            )
            ->unique()
            ->values();

        /*
         * Load all reward records in one query.
         */
        $rewardsByKey =
            CampaignShareReward::query()
                ->with('grantedBy')
                ->when(
                    $userIds->isNotEmpty(),
                    fn ($query) =>
                        $query->whereIn(
                            'user_id',
                            $userIds
                        )
                )
                ->when(
                    $campaignIds->isNotEmpty(),
                    fn ($query) =>
                        $query->whereIn(
                            'share_campaign_id',
                            $campaignIds
                        )
                )
                ->get()
                ->groupBy(
                    fn (
                        CampaignShareReward $reward
                    ): string =>
                        $this->rowKey(
                            $reward->user_id,
                            $reward
                                ->share_campaign_id
                        )
                );

        /*
         * Load the latest Facebook link for each
         * user and campaign.
         */
        $latestSharesByKey =
            CampaignShare::query()
                ->when(
                    $userIds->isNotEmpty(),
                    fn ($query) =>
                        $query->whereIn(
                            'user_id',
                            $userIds
                        )
                )
                ->when(
                    $campaignIds->isNotEmpty(),
                    fn ($query) =>
                        $query->whereIn(
                            'share_campaign_id',
                            $campaignIds
                        )
                )
                ->where(
                    'status',
                    'verified'
                )
                ->orderByDesc(
                    'shared_at'
                )
                ->get()
                ->groupBy(
                    fn (
                        CampaignShare $share
                    ): string =>
                        $this->rowKey(
                            $share->user_id,
                            $share
                                ->share_campaign_id
                        )
                )
                ->map(
                    fn (
                        Collection $shares
                    ) =>
                        $shares->first()
                );

        $rows = $progresses
            ->map(
                function (
                    CampaignUserProgress $progress
                ) use (
                    $rewardsByKey,
                    $latestSharesByKey
                ): object {
                    $campaign =
                        $progress->campaign;

                    $user =
                        $progress->user;

                    $key = $this->rowKey(
                        $progress->user_id,
                        $progress
                            ->share_campaign_id
                    );

                    $campaignRewards =
                        $rewardsByKey->get(
                            $key,
                            collect()
                        );

                    $latestShare =
                        $latestSharesByKey->get(
                            $key
                        );

                    $summary =
                        $this
                            ->campaignShareService
                            ->rewardSummary(
                                $campaign,
                                $progress,
                                $campaignRewards
                            );

                    $lastGrantedReward =
                        $campaignRewards
                            ->sortByDesc(
                                'awarded_at'
                            )
                            ->first();

                    return (object) [
                        'progress_id' =>
                            $progress->id,

                        'user_id' =>
                            $user->id,

                        'phone_number' =>
                            $user
                                ->phone_number
                                ?: '-',

                        'user_name' =>
                            $user->name
                                ?: '-',

                        'campaign_id' =>
                            $campaign->id,

                        'campaign_title' =>
                            $campaign->title,

                        'facebook_post_url' =>
                            $latestShare
                                ?->facebook_post_url,

                        'verified_shares' =>
                            (int) $progress
                                ->current_shares,

                        'required_shares' =>
                            (int) $campaign
                                ->required_shares,

                        'reward_spins' =>
                            (int) $campaign
                                ->reward_spins,

                        'reward_repeatable' =>
                            (bool) $campaign
                                ->reward_repeatable,

                        'reward_status' =>
                            $summary[
                                'reward_status'
                            ],

                        'eligible_for_reward' =>
                            $summary[
                                'eligible_for_reward'
                            ],

                        'pending_milestones' =>
                            $summary[
                                'pending_milestones'
                            ],

                        'granted_milestones' =>
                            $summary[
                                'granted_milestones'
                            ],

                        'next_pending_milestone' =>
                            $summary[
                                'next_pending_milestone'
                            ],

                        'last_shared_at' =>
                            $progress
                                ->last_shared_at,

                        'last_awarded_at' =>
                            $lastGrantedReward
                                ?->awarded_at,

                        'last_granted_by' =>
                            $lastGrantedReward
                                ?->grantedBy
                                ?->name
                                ?? (
                                    $lastGrantedReward
                                        ? 'System / legacy'
                                        : null
                                ),
                    ];
                }
            )
            ->when(
                in_array(
                    $status,
                    [
                        'pending',
                        'granted',
                        'not_eligible',
                    ],
                    true
                ),
                fn (Collection $rows) =>
                    $rows->filter(
                        fn (object $row): bool =>
                            $row->reward_status
                                === $status
                    )
            )
            ->values();

        /*
         * Paginate the calculated status rows.
         */
        $page =
            LengthAwarePaginator
                ::resolveCurrentPage();

        $perPage = 20;

        $paginatedRows =
            new LengthAwarePaginator(
                $rows
                    ->forPage(
                        $page,
                        $perPage
                    )
                    ->values(),
                $rows->count(),
                $perPage,
                $page,
                [
                    'path' =>
                        $request->url(),

                    'query' =>
                        $request->query(),
                ]
            );

        $campaigns =
            ShareCampaign::query()
                ->orderBy('title')
                ->get([
                    'id',
                    'title',
                ]);

        return view(
            'portal.share-campaign-rewards.index',
            [
                'rows' =>
                    $paginatedRows,

                'campaigns' =>
                    $campaigns,

                'search' =>
                    $search,

                'status' =>
                    $status,

                'campaignId' =>
                    $campaignId,
            ]
        );
    }

    /**
     * Manually give the next pending campaign
     * reward to the user.
     */
    public function grant(
        CampaignUserProgress $progress
    ): RedirectResponse {
        try {
            $result = DB::transaction(
                function () use (
                    $progress
                ): array {
                    /*
                     * Lock the user/campaign progress row.
                     * This prevents two admins from granting
                     * the same milestone simultaneously.
                     */
                    $progress =
                        CampaignUserProgress::query()
                            ->with([
                                'campaign',
                                'user',
                            ])
                            ->whereKey(
                                $progress->id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                    $campaign =
                        $progress->campaign;

                    $user =
                        $progress->user;

                    if (!$campaign || !$user) {
                        throw ValidationException
                            ::withMessages([
                                'reward' =>
                                    'The user or campaign could not be found.',
                            ]);
                    }

                    $eligibleMilestone =
                        $this
                            ->campaignShareService
                            ->eligibleMilestone(
                                $campaign,
                                (int) $progress
                                    ->current_shares
                            );

                    if (
                        $eligibleMilestone < 1
                    ) {
                        throw ValidationException
                            ::withMessages([
                                'reward' =>
                                    'This user has not reached the required number of shares.',
                            ]);
                    }

                    /*
                     * Lock existing rewards for this
                     * user and campaign.
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
                            ->pluck(
                                'milestone_number'
                            )
                            ->map(
                                fn ($value): int =>
                                    (int) $value
                            )
                            ->unique()
                            ->values();

                    /*
                     * Find the first eligible milestone
                     * that has not been granted.
                     */
                    $nextMilestone = null;

                    for (
                        $milestone = 1;
                        $milestone
                            <= $eligibleMilestone;
                        $milestone++
                    ) {
                        if (
                            !$grantedMilestones
                                ->contains(
                                    $milestone
                                )
                        ) {
                            $nextMilestone =
                                $milestone;

                            break;
                        }
                    }

                    if ($nextMilestone === null) {
                        throw ValidationException
                            ::withMessages([
                                'reward' =>
                                    'This campaign reward has already been granted.',
                            ]);
                    }

                    $rewardAmount =
                        (int) $campaign
                            ->reward_spins;

                    if ($rewardAmount < 1) {
                        throw ValidationException
                            ::withMessages([
                                'reward' =>
                                    'The campaign reward amount is invalid.',
                            ]);
                    }

                    /*
                     * Get the spin wallet.
                     */
                    $spinWallet =
                        Wallet::query()
                            ->where(
                                'type',
                                'spin'
                            )
                            ->lockForUpdate()
                            ->first();

                    if (!$spinWallet) {
                        throw ValidationException
                            ::withMessages([
                                'wallet' =>
                                    'Spin wallet configuration was not found.',
                            ]);
                    }

                    /*
                     * Create the user's spin wallet
                     * when it does not exist.
                     */
                    $userWallet =
                        UserWallet::query()
                            ->firstOrCreate(
                                [
                                    'user_id' =>
                                        $user->id,

                                    'wallet_id' =>
                                        $spinWallet->id,
                                ],
                                [
                                    'balance' =>
                                        0,
                                ]
                            );

                    /*
                     * Lock wallet balance before adding
                     * the reward.
                     */
                    $userWallet =
                        UserWallet::query()
                            ->whereKey(
                                $userWallet->id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                    $userWallet->balance =
                        (float) $userWallet
                            ->balance
                        + $rewardAmount;

                    $userWallet->save();

                    /*
                     * Create the wallet transaction.
                     */
                    $walletTransaction =
                        WalletTransaction::query()
                            ->create([
                                'user_id' =>
                                    $user->id,

                                'wallet_id' =>
                                    $spinWallet->id,

                                'transaction_type' =>
                                    'campaign_share_reward',

                                'wallet_type' =>
                                    'spin',

                                'amount' =>
                                    $rewardAmount,

                                'from_user_id' =>
                                    null,

                                'to_user_id' =>
                                    $user->id,

                                'description' =>
                                    "Manual campaign share reward. Campaign: {$campaign->id}, milestone: {$nextMilestone}, granted by admin: "
                                    . auth()->id(),
                            ]);

                    /*
                     * Create the granted reward record.
                     */
                    CampaignShareReward::query()
                        ->create([
                            'share_campaign_id' =>
                                $campaign->id,

                            'user_id' =>
                                $user->id,

                            'milestone_number' =>
                                $nextMilestone,

                            'milestone_threshold' =>
                                $nextMilestone
                                * (int) $campaign
                                    ->required_shares,

                            'reward_spins' =>
                                $rewardAmount,

                            'wallet_transaction_id' =>
                                $walletTransaction->id,

                            'granted_by' =>
                                auth()->id(),

                            'awarded_at' =>
                                now(),
                        ]);

                    /*
                     * Recalculate progress reward values.
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

                    $progress
                        ->rewards_earned_count =
                        $rewardQuery->count();

                    $progress
                        ->last_milestone_rewarded =
                        (int) (
                            $rewardQuery->max(
                                'milestone_number'
                            ) ?? 0
                        );

                    $progress->save();

                    return [
                        'phone_number' =>
                            $user->phone_number,

                        'campaign_title' =>
                            $campaign->title,

                        'reward_spins' =>
                            $rewardAmount,

                        'balance' =>
                            (float) $userWallet
                                ->balance,

                        'milestone' =>
                            $nextMilestone,
                    ];
                },
                5
            );

            return back()->with(
                'success',
                "{$result['reward_spins']} spins were given to {$result['phone_number']} for {$result['campaign_title']}. Current balance: {$result['balance']}."
            );
        } catch (
            ValidationException $exception
        ) {
            return back()->withErrors(
                $exception->errors()
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'Unable to give the campaign reward. No wallet balance was changed.'
            );
        }
    }

    private function rowKey(
        int $userId,
        int $campaignId
    ): string {
        return $userId
            . ':'
            . $campaignId;
    }
}