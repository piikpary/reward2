<?php

namespace App\Jobs;

use App\Services\MekongSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendOtpJob implements
    ShouldQueue,
    ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /*
     * Do not retry automatically because a retry
     * could send the same OTP twice.
     */
    public int $tries = 1;

    public int $timeout = 40;

    public bool $failOnTimeout = true;

    public function __construct(
        public string $phone,
        public string $otp,
        public int $expireMinutes
    ) {
        $this->onConnection(
            (string) config(
                'otp.queue_connection',
                'redis'
            )
        );

        $this->onQueue(
            (string) config(
                'otp.queue',
                'otp'
            )
        );
    }

    public function handle(
        MekongSmsService $mekongSmsService
    ): void {
        $mekongSmsService->sendOtp(
            $this->phone,
            $this->otp,
            $this->expireMinutes
        );
    }

    public function failed(
        ?Throwable $exception
    ): void {
        Log::warning(
            'OTP SMS queue job failed',
            [
                /*
                 * Never log the OTP or full phone number.
                 */
                'phone_suffix' => substr(
                    $this->phone,
                    -4
                ),

                'exception' => $exception
                    ? get_class($exception)
                    : null,

                'message' => $exception
                    ? mb_substr(
                        $exception->getMessage(),
                        0,
                        200
                    )
                    : null,
            ]
        );
    }
}