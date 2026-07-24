<?php

namespace App\Http\Controllers\v2\Campaign;

use App\Http\Controllers\Controller;
use App\Http\Requests\v2\Campaign\VerifyCampaignShareRequest;
use App\Models\CampaignUserProgress;
use App\Models\ShareCampaign;
use App\Services\CampaignShareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Throwable;

class CampaignController extends Controller
{
    public function __construct(
        private readonly CampaignShareService $campaignShareService
    ) {
    }

    /**
     * GET /api/campaign-list
     *
     * Return active and published campaigns.
     */
    public function index(): JsonResponse
    {
        $data = Cache::remember(
            'reward2:api:share-campaigns:available:v1',
            now()->addSeconds(30),
            function (): array {
                return ShareCampaign::query()
                    ->available()
                    ->orderByDesc('published_at')
                    ->orderByDesc('id')
                    ->limit(50)
                    ->get()
                    ->map(function (
                        ShareCampaign $campaign
                    ): array {
                        return [
                            'campaignId' =>
                                $campaign->id,

                            'campaignImage' =>
                                $campaign->imageUrl(),

                            'campaignTitle' =>
                                $campaign->title,

                            'campaignDescription' =>
                                $campaign->description,

                            'requiredShares' =>
                                (int) $campaign
                                    ->required_shares,

                            'rewardSpins' =>
                                (int) $campaign
                                    ->reward_spins,
                        ];
                    })
                    ->values()
                    ->all();
            }
        );

        return response()->json([
            'success' => true,
            'count' => count($data),
            'data' => $data,
        ]);
    }

    /**
     * POST /api/verify-share-campaign
     *
     * Validate and record the user's Facebook campaign share.
     */
    public function verifyShare(
    VerifyCampaignShareRequest $request
): JsonResponse {
    $campaign = ShareCampaign::query()
        ->findOrFail(
            $request->integer('campaign_id')
        );

    try {
        $result =
            $this->campaignShareService
                ->verify(
                    user: $request->user(),

                    campaign: $campaign,

                    facebookPostUrl: $request
                        ->string(
                            'facebook_post_url'
                        )
                        ->toString(),

                    ipAddress: $request->ip(),

                    userAgent:
                        $request->userAgent()
                );

        return response()->json([
            'success' => true,

            'message' =>
                'Your share link has been submitted successfully. Our team will review your public post within 7 days. Please make sure your post is set to Public and contains the correct campaign content. Invalid or unrelated submissions may be rejected. Once your post is approved and you meet the campaign requirements, your spins will be added to your account.',

            'data' => $result,
        ]);
    } catch (ValidationException $exception) {
        throw $exception;
    } catch (Throwable $exception) {
        report($exception);

        return response()->json([
            'success' => false,

            'message' =>
                'Unable to submit the campaign share link.',

            'data' => null,
        ], 500);
    }
}

    /**
     * GET /api/campaign/{campaign}
     *
     * Return one campaign and the authenticated user's progress.
     */
    public function show(
        Request $request,
        ShareCampaign $campaign
    ): JsonResponse {
        if (!$campaign->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Campaign not found or unavailable.',
                'data' => null,
            ], 404);
        }

        $progress = CampaignUserProgress::query()
            ->where(
                'share_campaign_id',
                $campaign->id
            )
            ->where(
                'user_id',
                $request->user()->id
            )
            ->first();

        return response()->json([
            'success' => true,

            'data' => [
                'campaignId' =>
                    $campaign->id,

                'campaignImage' =>
                    $campaign->imageUrl(),

                'campaignTitle' =>
                    $campaign->title,

                'campaignDescription' =>
                    $campaign->description,

                'requiredShares' =>
                    (int) $campaign->required_shares,

                'rewardSpins' =>
                    (int) $campaign->reward_spins,

                'currentShares' =>
                    (int) (
                        $progress?->current_shares ?? 0
                    ),

                'remainingShares' =>
                    $this->campaignShareService
                        ->remainingShares(
                            $campaign,
                            $progress
                        ),

                'totalCampaignShares' =>
                    (int) $campaign->total_shares,

                'shareUrl' =>
                    $campaign->campaignShareUrl(),

                'expiryDate' =>
                    $campaign->expires_at
                        ?->toISOString(),

                'rewardClaimed' =>
                    (int) (
                        $progress
                            ?->rewards_earned_count ?? 0
                    ) > 0,
            ],
        ]);
    }

    /**
     * GET /api/user-shares
     *
     * Return the authenticated user's campaign progress.
     */
    public function userShares(
        Request $request
    ): JsonResponse {
        $progresses = CampaignUserProgress::query()
            ->with('campaign')
            ->where(
                'user_id',
                $request->user()->id
            )
            ->where(
                'current_shares',
                '>',
                0
            )
            ->orderByDesc('last_shared_at')
            ->limit(50)
            ->get();

        $data = $progresses
            ->filter(function (
                CampaignUserProgress $progress
            ): bool {
                return $progress->campaign !== null;
            })
            ->map(function (
                CampaignUserProgress $progress
            ): array {
                $campaign = $progress->campaign;

                return [
                    'campaignId' =>
                        $campaign->id,

                    'campaignTitle' =>
                        $campaign->title,

                    'campaignImage' =>
                        $campaign->imageUrl(),

                    'sharedAt' =>
                        $progress->last_shared_at
                            ?->toISOString(),

                    'currentShares' =>
                        (int) $progress->current_shares,

                    'requiredShares' =>
                        (int) $campaign->required_shares,

                    'remainingShares' =>
                        $this->campaignShareService
                            ->remainingShares(
                                $campaign,
                                $progress
                            ),

                    'rewardSpins' =>
                        (int) $campaign->reward_spins,

                    'rewardClaimed' =>
                        (int) $progress
                            ->rewards_earned_count > 0,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'count' => $data->count(),
            'data' => $data,
        ]);
    }
            /**
         * GET /api/campaigns/{campaignId}/user-share-status
         *
         * Return the authenticated user's latest submission,
         * approved progress, reward status, and spin balance.
         */
        public function userShareStatus(
            Request $request,
            int $campaignId
        ): JsonResponse {
            $campaign = ShareCampaign::query()
                ->find($campaignId);

            if (!$campaign) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campaign not found.',
                    'data' => null,
                ], 404);
            }

            try {
                $data = $this
                    ->campaignShareService
                    ->userShareStatus(
                        user: $request->user(),
                        campaign: $campaign
                    );

                return response()->json([
                    'success' => true,
                    'data' => $data,
                ]);
            } catch (Throwable $exception) {
                report($exception);

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Unable to retrieve campaign share status.',
                    'data' => null,
                ], 500);
            }
        }
}