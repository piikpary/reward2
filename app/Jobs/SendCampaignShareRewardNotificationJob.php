<?php

namespace App\Jobs;

use App\Models\ShareCampaign;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendCampaignShareRewardNotificationJob
    implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public int $userId,
        public int $campaignId,
        public int $rewardSpins,
        public int $transactionId
    ) {
    }

    public function handle(): void
    {
        $user = User::query()->find(
            $this->userId
        );

        $campaign = ShareCampaign::query()->find(
            $this->campaignId
        );

        if (!$user || !$campaign) {
            Log::warning(
                'Campaign reward notification skipped: user or campaign not found',
                [
                    'user_id' =>
                        $this->userId,

                    'campaign_id' =>
                        $this->campaignId,

                    'transaction_id' =>
                        $this->transactionId,
                ]
            );

            return;
        }

        if (empty($user->fcm_token)) {
            Log::info(
                'Campaign reward notification skipped: user has no FCM token',
                [
                    'user_id' =>
                        $user->id,

                    'phone_number' =>
                        $user->phone_number,

                    'campaign_id' =>
                        $campaign->id,
                ]
            );

            return;
        }

        $language = $user->language ?? 'en';

        if ($language === 'km') {
            $title =
                'បានទទួល Spin ពីយុទ្ធនាការ';

            $body =
                "អ្នកបានបំពេញការចែករំលែកសម្រាប់យុទ្ធនាការ \"{$campaign->title}\"។ "
                . "អ្នកបានទទួល {$this->rewardSpins} Spin "
                . 'បន្ថែមទៅក្នុងគណនីរបស់អ្នកដោយជោគជ័យ។';
        } else {
            $title =
                'Campaign Reward Received';

            $body =
                "You have completed the share campaign \"{$campaign->title}\". "
                . "You have received {$this->rewardSpins} Spins "
                . 'successfully added to your account.';
        }

        try {
            /*
             * Follow the same notification badge
             * process as transfer notifications.
             */
            $user->increment(
                'notification_badge_count'
            );

            $badgeCount = (int) $user
                ->fresh()
                ->notification_badge_count;

            sendFcmNotification(
                $user->fcm_token,
                $title,
                $body,
                [
                    'type' =>
                        'share_campaign_reward',

                    'campaign_id' =>
                        (string) $campaign->id,

                    'campaign_title' =>
                        (string) $campaign->title,

                    'wallet_type' =>
                        'spin',

                    'amount' =>
                        (string) $this->rewardSpins,

                    'transaction_id' =>
                        (string) $this->transactionId,

                    'user_id' =>
                        (string) $user->id,

                    'language' =>
                        (string) $language,

                    'badge' =>
                        (string) $badgeCount,
                ]
            );

            Log::info(
                'Campaign share reward notification processed',
                [
                    'user_id' =>
                        $user->id,

                    'campaign_id' =>
                        $campaign->id,

                    'reward_spins' =>
                        $this->rewardSpins,

                    'transaction_id' =>
                        $this->transactionId,
                ]
            );
        } catch (\Throwable $exception) {
            Log::error(
                'Campaign share reward FCM notification failed',
                [
                    'message' =>
                        $exception->getMessage(),

                    'user_id' =>
                        $user->id,

                    'phone_number' =>
                        $user->phone_number,

                    'campaign_id' =>
                        $campaign->id,

                    'transaction_id' =>
                        $this->transactionId,
                ]
            );

            throw $exception;
        }
    }
}