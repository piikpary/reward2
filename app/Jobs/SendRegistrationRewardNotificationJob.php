<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendRegistrationRewardNotificationJob
    implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public int $userId,
        public string $walletType,
        public float $amount,
        public int $transactionId
    ) {
    }

    public function handle(): void
    {
        $user = User::query()->find(
            $this->userId
        );

        if (!$user) {
            Log::warning(
                'Registration reward notification skipped: user not found',
                [
                    'user_id' => $this->userId,
                    'transaction_id' =>
                        $this->transactionId,
                ]
            );

            return;
        }

        if (empty($user->fcm_token)) {
            Log::info(
                'Registration reward notification skipped: user has no FCM token',
                [
                    'user_id' => $user->id,
                    'phone_number' =>
                        $user->phone_number,
                    'transaction_id' =>
                        $this->transactionId,
                ]
            );

            return;
        }

        /*
         * Normalize language values such as:
         * km, kh, khmer, km-KH, kh-KH, km_KH.
         */
        $language = strtolower(
            trim(
                (string) (
                    $user->language
                    ?? 'en'
                )
            )
        );

        $language = str_replace(
            '_',
            '-',
            $language
        );

        $isKhmer = in_array(
            $language,
            [
                'km',
                'kh',
                'khmer',
                'km-kh',
                'kh-kh',
            ],
            true
        );

        $displayAmount =
            $this->formatAmount(
                $this->amount
            );

        if (
            $this->walletType
            === 'discount'
        ) {
            $englishBody =
                "Your registration was successful! "
                . "Enjoy {$displayAmount} bonus Discounts "
                . 'as our welcome gift';

            $khmerBody =
                "ការចុះឈ្មោះរបស់អ្នកបានជោគជ័យ! "
                . "សូមរីករាយជាមួយការបញ្ចុះតម្លៃបន្ថែម "
                . "{$displayAmount}% "
                . "ជាកាដូស្វាគមន៍ពីយើង។";
        } else {
            $englishBody =
                "Your registration was successful! "
                . "Enjoy {$displayAmount} bonus Spins "
                . 'as our welcome gift';

            $khmerBody =
                "ការចុះឈ្មោះរបស់អ្នកបានជោគជ័យ! "
                . "សូមរីករាយជាមួយ Spin បន្ថែមចំនួន "
                . "{$displayAmount} "
                . "ជាកាដូស្វាគមន៍ពីយើង។";
        }

        $title = $isKhmer
            ? 'កាដូស្វាគមន៍'
            : 'Welcome Gift';

        $body = $isKhmer
            ? $khmerBody
            : $englishBody;

        try {
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
                        'registration_reward',

                    'wallet_type' =>
                        (string) $this->walletType,

                    'amount' =>
                        (string) $this->amount,

                    'transaction_id' =>
                        (string) $this->transactionId,

                    'user_id' =>
                        (string) $user->id,

                    'language' =>
                        $isKhmer
                            ? 'km'
                            : 'en',

                    'badge' =>
                        (string) $badgeCount,
                ]
            );

            Log::info(
                'Registration reward notification processed',
                [
                    'user_id' => $user->id,
                    'wallet_type' =>
                        $this->walletType,
                    'amount' =>
                        $this->amount,
                    'transaction_id' =>
                        $this->transactionId,
                    'language' =>
                        $isKhmer
                            ? 'km'
                            : 'en',
                ]
            );
        } catch (\Throwable $exception) {
            Log::error(
                'Registration reward notification failed',
                [
                    'message' =>
                        $exception->getMessage(),

                    'user_id' =>
                        $user->id,

                    'wallet_type' =>
                        $this->walletType,

                    'transaction_id' =>
                        $this->transactionId,
                ]
            );

            throw $exception;
        }
    }

    private function formatAmount(
        float $amount
    ): string {
        return rtrim(
            rtrim(
                number_format(
                    $amount,
                    2,
                    '.',
                    ''
                ),
                '0'
            ),
            '.'
        );
    }
}