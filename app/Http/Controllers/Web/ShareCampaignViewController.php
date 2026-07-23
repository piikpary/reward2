<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ShareCampaign;
use Illuminate\Support\Facades\Log;
use Throwable;

class ShareCampaignViewController extends Controller
{
    public function show(ShareCampaign $shareCampaign)
    {
        try {
            $title = $shareCampaign->title
                ?: 'Join this campaign';

            $description = $shareCampaign->description
                ?: 'Share and earn rewards!';

            $imageUrl = $shareCampaign->imageUrl();

            $pageUrl = $shareCampaign->campaignShareUrl();

            $deepLinkBase = config(
                'services.scanprize.app_deep_link'
            );

            $appDeepLink = $deepLinkBase
                ? rtrim($deepLinkBase, '?&')
                    . '?'
                    . http_build_query([
                        'id' => $shareCampaign->id,
                    ])
                : null;

            return response()
                ->view('share-campaigns.show', [
                    'shareCampaign' => $shareCampaign,
                    'title' => $title,
                    'description' => $description,
                    'imageUrl' => $imageUrl,
                    'pageUrl' => $pageUrl,
                    'appDeepLink' => $appDeepLink,
                ])
                ->header(
                    'Content-Type',
                    'text/html; charset=UTF-8'
                );
        } catch (Throwable $exception) {
            Log::error('Share campaign view error', [
                'campaign_id' => $shareCampaign->id,
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            return response(
                'Something went wrong',
                500,
                [
                    'Content-Type' =>
                        'text/plain; charset=UTF-8',
                ]
            );
        }
    }
}