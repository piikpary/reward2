<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendTransferNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public int $senderId,
        public int $receiverId,
        public string $walletType,
        public float $amount
    ) {
    }

    public function handle(): void
    {
        $sender = User::find($this->senderId);
        $receiver = User::find($this->receiverId);

        if (!$sender || !$receiver) {
            Log::warning('Transfer notification skipped: sender or receiver not found', [
                'sender_id' => $this->senderId,
                'receiver_id' => $this->receiverId,
            ]);
            return;
        }

        if (empty($receiver->fcm_token)) {
            Log::info('Transfer notification skipped: receiver has no FCM token', [
                'receiver_id' => $receiver->id,
                'receiver_phone' => $receiver->phone_number,
            ]);
            return;
        }

        $language = $receiver->language ?? 'en';

        if ($this->walletType === 'spin') {
            $title = $language === 'km'
                ? 'បានទទួល Spin'
                : 'Transfer Received';

            $body = $language === 'km'
                ? "អ្នកបានទទួល {$this->amount} Spin ពី {$sender->name}។"
                : "You have received {$this->amount} spin from {$sender->name}.";
        } else {
            $title = $language === 'km'
                ? 'បានទទួលការបញ្ចុះតម្លៃ'
                : 'Transfer Received';

            $body = $language === 'km'
                ? "អ្នកបានទទួលការបញ្ចុះតម្លៃ {$this->amount}% ពី {$sender->name}។"
                : "You have received {$this->amount}% discount from {$sender->name}.";
        }

        try {
            $receiver->increment('notification_badge_count');
            $badgeCount = (int) $receiver->fresh()->notification_badge_count;

            sendFcmNotification($receiver->fcm_token, $title, $body, [
                'type' => 'transfer',
                'wallet_type' => (string) $this->walletType,
                'amount' => (string) $this->amount,
                'from_user_id' => (string) $sender->id,
                'from_phone' => (string) $sender->phone_number,
                'to_user_id' => (string) $receiver->id,
                'language' => (string) $language,
                'badge' => (string) $badgeCount,
            ]);

            Log::info('Transfer notification queue job processed', [
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'wallet_type' => $this->walletType,
                'amount' => $this->amount,
            ]);
        } catch (\Throwable $e) {
            Log::error('Transfer FCM notification failed', [
                'message' => $e->getMessage(),
                'receiver_id' => $receiver->id,
                'receiver_phone' => $receiver->phone_number,
            ]);

            throw $e;
        }
    }
}