<?php

namespace App\Services;

use App\Exceptions\ApiRateLimitException;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;
use App\Jobs\SendOtpJob;

class OtpService
{
    private MekongSmsService $mekongSmsService;

    public function __construct(
        MekongSmsService $mekongSmsService
    ) {
        $this->mekongSmsService =
            $mekongSmsService;
    }

    public function sendOtp(
        string $phone
    ): void {
        $phone = $this->normalizePhone(
            $phone
        );

        $otpCacheKey =
            'otp:' . $phone;

        $cooldownKey =
            'otp_cooldown:' . $phone;

        $attemptsKey =
            'otp_request_attempts:' . $phone;

        $maxAttempts = (int) env(
            'OTP_REQUEST_MAX_ATTEMPTS',
            3
        );

        $decaySeconds = (int) env(
            'OTP_REQUEST_DECAY_SECONDS',
            3600
        );

        $cooldownSeconds = (int) env(
            'OTP_REQUEST_COOLDOWN_SECONDS',
            60
        );

        $expireMinutes = (int) env(
            'OTP_EXPIRE_MINUTES',
            5
        );

        if (
            RateLimiter::tooManyAttempts(
                $attemptsKey,
                $maxAttempts
            )
        ) {
            $seconds =
                RateLimiter::availableIn(
                    $attemptsKey
                );

            throw new ApiRateLimitException(
                'You have requested too many OTPs. '
                . 'Please try again in '
                . ceil($seconds / 60)
                . ' minutes.',
                $seconds
            );
        }

        if (
            RateLimiter::tooManyAttempts(
                $cooldownKey,
                1
            )
        ) {
            $seconds =
                RateLimiter::availableIn(
                    $cooldownKey
                );

            throw new ApiRateLimitException(
                "Please wait {$seconds} seconds "
                . 'before requesting a new OTP.',
                $seconds
            );
        }

        $useStaticOtp =
            $this->shouldUseStaticOtp();

        $otp = $useStaticOtp
            ? (string) env(
                'OTP_STATIC_CODE',
                '0000'
            )
            : $this->generateOtp(4);

        /*
         * Store OTP before sending it so the user
         * never receives a code that cannot be verified.
         */
        Cache::put(
            $otpCacheKey,
            $otp,
            now()->addMinutes(
                $expireMinutes
            )
        );

        try {
            /*
             * Static OTP is normally used only for
             * local development, so do not send SMS
             * when static mode is enabled.
             */
            if (!$useStaticOtp) {
                SendOtpJob::dispatch(
                    $phone,
                    $otp,
                    $expireMinutes
                );
            }
        } catch (Throwable $exception) {
            /*
             * Remove undelivered OTP so it cannot
             * still be used after SMS failure.
             */
            Cache::forget(
                $otpCacheKey
            );

            Log::error(
                'OTP SMS sending failed',
                [
                    'phone' =>
                        $this->maskPhone(
                            $phone
                        ),

                    'message' =>
                        $exception
                            ->getMessage(),
                ]
            );

            throw new Exception(
                'Unable to send OTP. '
                . 'Please try again.',
                503,
                $exception
            );
        }

        /*
         * Apply limits only after OTP generation and
         * SMS sending have completed successfully.
         */
        RateLimiter::hit(
            $cooldownKey,
            $cooldownSeconds
        );

        RateLimiter::hit(
            $attemptsKey,
            $decaySeconds
        );

        /*
         * Never write the OTP value into production logs.
         */
        if (
            filter_var(
                env('OTP_DEBUG_LOG', false),
                FILTER_VALIDATE_BOOLEAN
            )
        ) {
            Log::info(
                'OTP request processed',
                [
                    'phone' =>
                        $this->maskPhone(
                            $phone
                        ),

                    'static' =>
                        $useStaticOtp,

                    'sms_sent' =>
                        !$useStaticOtp,

                    'expires_in_minutes' =>
                        $expireMinutes,
                ]
            );
        }
    }

    public function verify(
        string $phone,
        string $otp
    ): void {
        $phone = $this->normalizePhone(
            $phone
        );

        $cacheKey =
            'otp:' . $phone;

        $cachedOtp = Cache::get(
            $cacheKey
        );

        if (!$cachedOtp) {
            throw new Exception(
                __(
                    'messages.otp_expired_or_invalid'
                ),
                400
            );
        }

        if (
            (string) $cachedOtp
            !== (string) $otp
        ) {
            throw new Exception(
                __('messages.otp_invalid'),
                400
            );
        }

        Cache::forget(
            $cacheKey
        );

        RateLimiter::clear(
            'otp_cooldown:' . $phone
        );

        RateLimiter::clear(
            'otp_request_attempts:' . $phone
        );
    }

    public function normalizePhone(
        string $phone
    ): string {
        $phone = preg_replace(
            '/[^0-9+]/',
            '',
            trim($phone)
        );

        if (
            str_starts_with(
                $phone,
                '+855'
            )
        ) {
            return '855' . substr(
                $phone,
                4
            );
        }

        if (
            str_starts_with(
                $phone,
                '855'
            )
        ) {
            return $phone;
        }

        if (
            str_starts_with(
                $phone,
                '0'
            )
        ) {
            return '855' . substr(
                $phone,
                1
            );
        }

        return $phone;
    }

    private function generateOtp(
        int $length = 4
    ): string {
        $min = (int) str_pad(
            '1',
            $length,
            '0'
        );

        $max = (int) str_repeat(
            '9',
            $length
        );

        return (string) random_int(
            $min,
            $max
        );
    }

    private function shouldUseStaticOtp(): bool
    {
        return filter_var(
            env(
                'OTP_STATIC_ENABLED',
                false
            ),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    private function maskPhone(
        string $phone
    ): string {
        if (strlen($phone) <= 6) {
            return '***';
        }

        return substr(
            $phone,
            0,
            3
        )
            . str_repeat(
                '*',
                strlen($phone) - 6
            )
            . substr(
                $phone,
                -3
            );
    }
}