<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\ShareCampaign\StoreShareCampaignRequest;
use App\Http\Requests\Portal\ShareCampaign\UpdateShareCampaignRequest;
use App\Models\ShareCampaign;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use App\Models\CampaignShare;
use App\Models\CampaignUserProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\CampaignShareRewardService;


class ShareCampaignController extends Controller
{
    public function __construct(
        private readonly CampaignShareRewardService
            $campaignShareRewardService
    ) {
    }
    /**
     * Display campaigns.
     */
    public function index(Request $request): View
    {
        $search = trim(
            $request->string('search')->toString()
        );

        $status = $request
            ->string('status')
            ->toString();

        $campaigns = ShareCampaign::query()
            ->withCount([
                'shares',
                'rewards',
            ])
            ->when(
                $search !== '',
                function ($query) use ($search): void {
                    $query->where(function ($query) use (
                        $search
                    ): void {
                        $query
                            ->where(
                                'title',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'description',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->when(
                $status === 'active',
                fn ($query) =>
                    $query->where('is_active', true)
            )
            ->when(
                $status === 'inactive',
                fn ($query) =>
                    $query->where('is_active', false)
            )
            ->when(
                $status === 'published',
                fn ($query) =>
                    $query->where('is_published', true)
            )
            ->when(
                $status === 'draft',
                fn ($query) =>
                    $query->where('is_published', false)
            )
            ->when(
                $status === 'expired',
                fn ($query) =>
                    $query
                        ->whereNotNull('expires_at')
                        ->where('expires_at', '<', now())
            )
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view(
            'portal.share-campaigns.index',
            compact('campaigns', 'search', 'status')
        );
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        return view(
            'portal.share-campaigns.create'
        );
    }

    /**
     * Save campaign.
     */
    public function store(
        StoreShareCampaignRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        unset($data['image']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request
                ->file('image')
                ->store(
                    'share-campaigns',
                    'public'
                );
        }

        if (
            empty($data['business_id'])
            && $request->user()
        ) {
            $data['business_id'] =
                $request->user()->business_id ?? null;
        }

        $data['created_by'] =
            $request->user()?->id;

        $data['updated_by'] =
            $request->user()?->id;

        $data['published_at'] =
            $data['is_published']
                ? now()
                : null;

        ShareCampaign::query()->create($data);

        $this->clearApiCampaignCache();

        return redirect()
            ->route(
                'portal.share-campaigns.index'
            )
            ->with(
                'success',
                'Share campaign created successfully.'
            );
    }

    /**
     * Show edit form.
     */
    public function edit(
        ShareCampaign $shareCampaign
    ): View {
        return view(
            'portal.share-campaigns.edit',
            compact('shareCampaign')
        );
    }

    /**
     * Update campaign.
     */
    public function update(
        UpdateShareCampaignRequest $request,
        ShareCampaign $shareCampaign
    ): RedirectResponse {
        $data = $request->validated();

        unset($data['image']);

        $newImagePath = null;
        $oldImagePath = $shareCampaign->image_path;

        if ($request->hasFile('image')) {
            $newImagePath = $request
                ->file('image')
                ->store(
                    'share-campaigns',
                    'public'
                );

            $data['image_path'] = $newImagePath;
        }

        if (
            empty($data['business_id'])
            && $request->user()
        ) {
            $data['business_id'] =
                $request->user()->business_id
                ?? $shareCampaign->business_id;
        }

        $data['updated_by'] =
            $request->user()?->id;

        if ($data['is_published']) {
            $data['published_at'] =
                $shareCampaign->published_at
                ?? now();
        } else {
            $data['published_at'] = null;
        }

        try {
            $shareCampaign->update($data);

            if (
                $newImagePath
                && $oldImagePath
                && $oldImagePath !== $newImagePath
            ) {
                $this->deleteLocalImage(
                    $oldImagePath
                );
            }
        } catch (Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('public')
                    ->delete($newImagePath);
            }

            throw $exception;
        }

        $this->clearApiCampaignCache();

        return redirect()
            ->route(
                'portal.share-campaigns.index'
            )
            ->with(
                'success',
                'Share campaign updated successfully.'
            );
    }

    /**
     * Activate or deactivate campaign.
     */
    public function toggleActive(
        Request $request,
        ShareCampaign $shareCampaign
    ): RedirectResponse {
        $shareCampaign->update([
            'is_active' =>
                !$shareCampaign->is_active,

            'updated_by' =>
                $request->user()?->id,
        ]);

        $this->clearApiCampaignCache();

        $message = $shareCampaign->is_active
            ? 'Campaign activated successfully.'
            : 'Campaign deactivated successfully.';

        return back()->with(
            'success',
            $message
        );
    }

    /**
     * Publish or unpublish campaign.
     */
    public function togglePublish(
        Request $request,
        ShareCampaign $shareCampaign
    ): RedirectResponse {
        $isPublished =
            !$shareCampaign->is_published;

        $shareCampaign->update([
            'is_published' => $isPublished,

            'published_at' =>
                $isPublished
                    ? now()
                    : null,

            'updated_by' =>
                $request->user()?->id,
        ]);

        $this->clearApiCampaignCache();

        $message = $isPublished
            ? 'Campaign published successfully.'
            : 'Campaign unpublished successfully.';

        return back()->with(
            'success',
            $message
        );
    }

    /**
 * Display customers who shared this campaign.
 */
public function shares(
    Request $request,
    ShareCampaign $shareCampaign
): View {
    $validated = $request->validate([
        'date_from' => [
            'nullable',
            'date',
        ],

        'date_to' => [
            'nullable',
            'date',
            'after_or_equal:date_from',
        ],

        'user_id' => [
            'nullable',
            'integer',
            'exists:users,id',
        ],

        'review_status' => [
            'nullable',
            'in:pending,verified,rejected',
        ],
    ]);

    $shares = $shareCampaign
        ->shares()
        ->with([
            'user',
            'reviewedBy',
        ])
        ->when(
            !empty($validated['date_from']),
            fn ($query) =>
                $query->whereDate(
                    'shared_at',
                    '>=',
                    $validated['date_from']
                )
        )
        ->when(
            !empty($validated['date_to']),
            fn ($query) =>
                $query->whereDate(
                    'shared_at',
                    '<=',
                    $validated['date_to']
                )
        )
        ->when(
            !empty($validated['user_id']),
            fn ($query) =>
                $query->where(
                    'user_id',
                    $validated['user_id']
                )
        )
        ->when(
            !empty($validated['review_status']),
            fn ($query) =>
                $query->where(
                    'status',
                    $validated['review_status']
                )
        )
        ->orderByDesc('shared_at')
        ->orderByDesc('id')
        ->paginate(20)
        ->withQueryString();

    /*
     * Group only the current pagination page.
     */
    $groupedShares = $shares
        ->getCollection()
        ->groupBy(
            fn (CampaignShare $share): string =>
                $share->shared_at
                    ?->toDateString()
                ?? 'unknown'
        );

    /*
     * Users who submitted links to this campaign.
     */
    $users = User::query()
        ->whereIn(
            'id',
            CampaignShare::query()
                ->where(
                    'share_campaign_id',
                    $shareCampaign->id
                )
                ->select('user_id')
                ->distinct()
        )
        ->orderBy('name')
        ->orderBy('phone_number')
        ->get([
            'id',
            'name',
            'phone_number',
        ]);

    $statistics = [
        'total_shares' =>
            $shareCampaign
                ->shares()
                ->count(),

        'pending_shares' =>
            $shareCampaign
                ->shares()
                ->where(
                    'status',
                    CampaignShare::STATUS_PENDING
                )
                ->count(),

        'verified_shares' =>
            $shareCampaign
                ->shares()
                ->where(
                    'status',
                    CampaignShare::STATUS_VERIFIED
                )
                ->count(),

        'rejected_shares' =>
            $shareCampaign
                ->shares()
                ->where(
                    'status',
                    CampaignShare::STATUS_REJECTED
                )
                ->count(),

        'unique_customers' =>
            $shareCampaign
                ->shares()
                ->distinct()
                ->count('user_id'),

        'rewards_awarded' =>
            $shareCampaign
                ->rewards()
                ->count(),

        'spins_awarded' =>
            (int) $shareCampaign
                ->rewards()
                ->sum('reward_spins'),
    ];

    return view(
        'portal.share-campaigns.shares',
        compact(
            'shareCampaign',
            'shares',
            'groupedShares',
            'users',
            'statistics'
        )
    );
}
/**
 * Approve one submitted Facebook post.
 *
 * This is where verified campaign progress increases.
 * When the required approved shares are completed,
 * the campaign spin reward is automatically awarded.
 */
public function approveShare(
    Request $request,
    ShareCampaign $shareCampaign,
    CampaignShare $share
): RedirectResponse {
    try {
        $result = DB::transaction(
            function () use (
                $request,
                $shareCampaign,
                $share
            ): array {
                $campaign = ShareCampaign::query()
                    ->whereKey($shareCampaign->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $share = CampaignShare::query()
                    ->whereKey($share->id)
                    ->where(
                        'share_campaign_id',
                        $campaign->id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $share->status !==
                    CampaignShare::STATUS_PENDING
                ) {
                    throw ValidationException
                        ::withMessages([
                            'share' =>
                                'This share has already been reviewed.',
                        ]);
                }

                CampaignUserProgress::query()
                    ->insertOrIgnore([
                        'share_campaign_id' =>
                            $campaign->id,

                        'user_id' =>
                            $share->user_id,

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

                $progress = CampaignUserProgress::query()
                    ->where(
                        'share_campaign_id',
                        $campaign->id
                    )
                    ->where(
                        'user_id',
                        $share->user_id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $share->update([
                    'status' =>
                        CampaignShare::STATUS_VERIFIED,

                    'verification_method' =>
                        'manual_review',

                    'verified_at' =>
                        now(),

                    'reviewed_by' =>
                        $request->user()->id,

                    'reviewed_at' =>
                        now(),

                    'rejection_reason' =>
                        null,
                ]);

                $progress->current_shares =
                    (int) $progress->current_shares + 1;

                if (
                    !$progress->last_shared_at
                    || $share->shared_at->gt(
                        $progress->last_shared_at
                    )
                ) {
                    $progress->last_shared_at =
                        $share->shared_at;
                }

                $progress->save();

                $campaign->total_shares =
                    (int) $campaign->total_shares + 1;

                $campaign->save();

                /*
                 * Automatically award the campaign
                 * reward when approved progress reaches
                 * the required number of shares.
                 *
                 * Returns null when the requirement is
                 * not complete or reward already exists.
                 */
                $rewardResult =
                    $this->campaignShareRewardService
                        ->awardIfEligible(
                            progress: $progress,
                            grantedBy: null
                        );

                return [
                    'current_shares' =>
                        (int) $progress->current_shares,

                    'required_shares' =>
                        (int) $campaign->required_shares,

                    'reward_awarded' =>
                        $rewardResult !== null,

                    'reward_spins' =>
                        (int) (
                            $rewardResult[
                                'reward_spins'
                            ] ?? 0
                        ),

                    'balance' =>
                        $rewardResult[
                            'balance'
                        ] ?? null,

                    'transaction_id' =>
                        $rewardResult[
                            'transaction_id'
                        ] ?? null,
                ];
            },
            5
        );

        $message =
            "Share approved successfully. Verified progress: "
            . "{$result['current_shares']} / "
            . "{$result['required_shares']}.";

        if ($result['reward_awarded']) {
            $message .=
                " {$result['reward_spins']} Spins were "
                . "automatically added to the user's account.";
        }

        return back()->with(
            'success',
            $message
        );
    } catch (ValidationException $exception) {
        return back()->withErrors(
            $exception->errors()
        );
    } catch (Throwable $exception) {
        report($exception);

        return back()->with(
            'error',
            'Unable to approve this campaign share.'
        );
    }
}

/**
 * Reject one submitted Facebook post.
 *
 * Rejected posts never increase progress.
 */
public function rejectShare(
    Request $request,
    ShareCampaign $shareCampaign,
    CampaignShare $share
): RedirectResponse {
    $validated = $request->validate([
        'rejection_reason' => [
            'nullable',
            'string',
            'max:500',
        ],
    ]);

    try {
        DB::transaction(function () use (
            $request,
            $shareCampaign,
            $share,
            $validated
        ): void {
            $share =
                CampaignShare::query()
                    ->whereKey($share->id)
                    ->where(
                        'share_campaign_id',
                        $shareCampaign->id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

            if (
                $share->status !==
                CampaignShare::STATUS_PENDING
            ) {
                throw ValidationException
                    ::withMessages([
                        'share' =>
                            'This share has already been reviewed.',
                    ]);
            }

            $reason = trim(
                (string) (
                    $validated[
                        'rejection_reason'
                    ] ?? ''
                )
            );

            if ($reason === '') {
                $reason =
                    'The post is not public or does not match the campaign.';
            }

            $share->update([
                'status' =>
                    CampaignShare::STATUS_REJECTED,

                'verification_method' =>
                    'manual_review',

                'verified_at' =>
                    null,

                'reviewed_by' =>
                    $request->user()->id,

                'reviewed_at' =>
                    now(),

                'rejection_reason' =>
                    $reason,
            ]);
        }, 5);

        return back()->with(
            'success',
            'Share rejected successfully.'
        );
    } catch (ValidationException $exception) {
        return back()->withErrors(
            $exception->errors()
        );
    } catch (Throwable $exception) {
        report($exception);

        return back()->with(
            'error',
            'Unable to reject this campaign share.'
        );
    }
}

    /**
     * Soft delete campaign.
     */
    public function destroy(
        ShareCampaign $shareCampaign
    ): RedirectResponse {
        $shareCampaign->update([
            'is_active' => false,
            'is_published' => false,
            'published_at' => null,
        ]);

        $shareCampaign->delete();

        $this->clearApiCampaignCache();

        return redirect()
            ->route(
                'portal.share-campaigns.index'
            )
            ->with(
                'success',
                'Share campaign deleted successfully.'
            );
    }

    private function clearApiCampaignCache(): void
    {
        Cache::forget(
            'reward2:api:share-campaigns:available:v1'
        );
    }

    /**
     * Delete only files stored on the local public disk.
     */
    private function deleteLocalImage(
        ?string $imagePath
    ): void {
        if (!$imagePath) {
            return;
        }

        if (
            Str::startsWith(
                $imagePath,
                ['http://', 'https://']
            )
        ) {
            return;
        }

        Storage::disk('public')
            ->delete($imagePath);
    }
}