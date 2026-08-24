<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class MekongSmsService
{
    /**
     * Known MekongSMS provider error responses.
     */
    private const ERROR_MESSAGES = [
        '101' => 'Invalid MekongSMS username or password.',
        '102' => 'Missing or invalid MekongSMS parameter.',
        '103' => 'Invalid destination phone number format.',
        '104' => 'Missing or invalid destination address.',
        '105' => 'Missing SMS text.',
        '106' => 'MekongSMS account has insufficient credits.',
        '108' => 'MekongSMS network problem.',
        '109' => 'MekongSMS account has no API access.',
        '110' => 'MekongSMS account has expired.',
        '111' => 'MekongSMS sender ID is not permitted.',
        '999' => 'MekongSMS general system error.',
    ];

    /**
     * Send a registration or login OTP.
     */
    public function sendOtp(
        string $phone,
        string $otp,
        int $expiresInMinutes = 5
    ): string {
        $message = $otp;

        return $this->send(
            phone: $phone,
            message: $message,
            customData: 'otp-' . now()->format('YmdHis')
        );
    }

    /**
     * Send one SMS through the MekongSMS POST API.
     *
     * Returns the original provider response.
     */
    public function send(
        string $phone,
        string $message,
        ?string $customData = null
    ): string {
        $this->ensureConfigured();

        $phone = $this->normalizePhone($phone);
        $message = trim($message);

        if ($message === '') {
            throw new RuntimeException(
                'The SMS message cannot be empty.'
            );
        }

        /*
         * MekongSMS documentation states that the
         * POST SMS message supports 160 characters.
         */
        if (mb_strlen($message) > 160) {
            throw new RuntimeException(
                'The SMS message must not exceed 160 characters.'
            );
        }

        $sender = trim(
            (string) config(
                'services.mekong_sms.sender'
            )
        );

        if (
            mb_strlen($sender) > 11
            || !preg_match(
                '/^[A-Za-z0-9 ]+$/',
                $sender
            )
        ) {
            throw new RuntimeException(
                'MekongSMS sender may contain letters, '
                . 'numbers, and spaces, with a maximum '
                . 'length of 11 characters.'
            );
        }

        $url = rtrim(
            (string) config(
                'services.mekong_sms.base_url'
            ),
            '/'
        ) . '/postsms.aspx';

        try {
            $response = Http::asForm()
                ->connectTimeout(
                    (int) config(
                        'services.mekong_sms.connect_timeout',
                        5
                    )
                )
                ->timeout(
                    (int) config(
                        'services.mekong_sms.timeout',
                        10
                    )
                )
                /*
                 * Do not automatically retry OTP requests.
                 * A retry could send the same SMS twice.
                 */
                ->post(
                    $url,
                    [
                        'username' => (string) config(
                            'services.mekong_sms.username'
                        ),

                        'pass' => (string) config(
                            'services.mekong_sms.password_md5'
                        ),

                        'sender' => $sender,

                        'smstext' => $message,

                        'gsm' => $phone,

                        'int' => (int) config(
                            'services.mekong_sms.international',
                            0
                        ),

                        'cd' => mb_substr(
                            $customData ?? '',
                            0,
                            100
                        ),
                    ]
                );
        } catch (Throwable $exception) {
            Log::error(
                'MekongSMS request failed',
                [
                    'phone' => $this->maskPhone(
                        $phone
                    ),

                    'message' => $exception->getMessage(),
                ]
            );

            throw new RuntimeException(
                'Unable to connect to the SMS provider.',
                previous: $exception
            );
        }

        if (!$response->successful()) {
            Log::error(
                'MekongSMS returned HTTP error',
                [
                    'phone' => $this->maskPhone(
                        $phone
                    ),

                    'status' => $response->status(),
                ]
            );

            throw new RuntimeException(
                'The SMS provider returned HTTP status '
                . $response->status()
                . '.'
            );
        }

        $providerResponse = $this->cleanResponse(
            $response->body()
        );

        if ($providerResponse === '') {
            throw new RuntimeException(
                'The SMS provider returned an empty response.'
            );
        }

        /*
         * Example response:
         * 116.212.132.148[Invalid Host Address]
         */
        $this->throwIfInvalidHost(
            $providerResponse
        );

        /*
         * A one-number request normally returns one result.
         * For safety, inspect only the first result token.
         */
        $firstResult = preg_split(
            '/\s+/',
            $providerResponse
        )[0] ?? $providerResponse;

        $errorCode = $this->detectErrorCode(
            $firstResult
        );

        if ($errorCode !== null) {
            Log::warning(
                'MekongSMS rejected SMS',
                [
                    'phone' => $this->maskPhone(
                        $phone
                    ),

                    'provider_code' => $errorCode,

                    'provider_message' =>
                        self::ERROR_MESSAGES[
                            $errorCode
                        ],
                ]
            );

            throw new RuntimeException(
                self::ERROR_MESSAGES[
                    $errorCode
                ]
            );
        }

        /*
         * Never log the OTP text, username,
         * password hash, or complete phone number.
         */
        Log::info(
            'MekongSMS accepted SMS',
            [
                'phone' => $this->maskPhone(
                    $phone
                ),

                'provider_response' => mb_substr(
                    $providerResponse,
                    0,
                    100
                ),
            ]
        );

        return $providerResponse;
    }

    /**
     * Check available MekongSMS credits.
     */
    public function checkBalance(): float
    {
        $this->ensureConfigured();

        $baseUrl = rtrim(
            (string) config(
                'services.mekong_sms.base_url'
            ),
            '/'
        );

        $credentials = [
            'username' => (string) config(
                'services.mekong_sms.username'
            ),

            'pass' => (string) config(
                'services.mekong_sms.password_md5'
            ),
        ];

        try {
            $response = Http::asForm()
                ->connectTimeout(
                    (int) config(
                        'services.mekong_sms.connect_timeout',
                        5
                    )
                )
                ->timeout(
                    (int) config(
                        'services.mekong_sms.timeout',
                        10
                    )
                )
                ->post(
                    $baseUrl
                    . '/postcheckbalance.aspx',
                    $credentials
                );
        } catch (Throwable $exception) {
            Log::error(
                'MekongSMS balance request failed',
                [
                    'message' =>
                        $exception->getMessage(),
                ]
            );

            throw new RuntimeException(
                'Unable to connect to MekongSMS balance API.',
                previous: $exception
            );
        }

        if (!$response->successful()) {
            Log::error(
                'MekongSMS balance API HTTP error',
                [
                    'status' => $response->status(),
                ]
            );

            throw new RuntimeException(
                'MekongSMS balance API returned HTTP status '
                . $response->status()
                . '.'
            );
        }

        $value = $this->cleanResponse(
            $response->body()
        );

        if ($value === '') {
            throw new RuntimeException(
                'MekongSMS balance API returned an empty response.'
            );
        }

        $this->throwIfInvalidHost(
            $value
        );

        $errorCode = $this->detectErrorCode(
            $value
        );

        if ($errorCode !== null) {
            throw new RuntimeException(
                self::ERROR_MESSAGES[
                    $errorCode
                ]
            );
        }

        if (!is_numeric($value)) {
            Log::warning(
                'Unexpected MekongSMS balance response',
                [
                    'response_preview' => mb_substr(
                        strip_tags($value),
                        0,
                        200
                    ),
                ]
            );

            throw new RuntimeException(
                'Unexpected MekongSMS balance response: '
                . mb_substr(
                    strip_tags($value),
                    0,
                    100
                )
            );
        }

        return (float) $value;
    }

    /**
     * Confirm that all required configuration exists.
     */
    private function ensureConfigured(): void
    {
        if (
            !config(
                'services.mekong_sms.enabled',
                false
            )
        ) {
            throw new RuntimeException(
                'MekongSMS integration is disabled.'
            );
        }

        $required = [
            'base_url',
            'username',
            'password_md5',
            'sender',
        ];

        foreach ($required as $key) {
            if (
                blank(
                    config(
                        "services.mekong_sms.{$key}"
                    )
                )
            ) {
                throw new RuntimeException(
                    "Missing MekongSMS configuration: {$key}."
                );
            }
        }
    }

    /**
     * Normalize a Cambodian phone number.
     *
     * Examples:
     * 0971234567     → 855971234567
     * +855971234567  → 855971234567
     */
    private function normalizePhone(
        string $phone
    ): string {
        $phone = preg_replace(
            '/[^\d+]/',
            '',
            trim($phone)
        );

        if (
            str_starts_with(
                $phone,
                '+'
            )
        ) {
            $phone = substr(
                $phone,
                1
            );
        }

        if (
            str_starts_with(
                $phone,
                '0'
            )
        ) {
            $phone = '855' . substr(
                $phone,
                1
            );
        }

        if (
            !preg_match(
                '/^855\d{8,10}$/',
                $phone
            )
        ) {
            throw new RuntimeException(
                'Invalid Cambodian phone number format.'
            );
        }

        return $phone;
    }

    /**
     * Detect a documented MekongSMS error code.
     */
    private function detectErrorCode(
        string $providerResponse
    ): ?string {
        foreach (
            array_keys(
                self::ERROR_MESSAGES
            ) as $errorCode
        ) {
            if (
                $providerResponse === $errorCode
                || str_starts_with(
                    $providerResponse,
                    $errorCode . '['
                )
            ) {
                return $errorCode;
            }
        }

        return null;
    }

    /**
     * Detect an unapproved or non-whitelisted IP.
     */
    private function throwIfInvalidHost(
        string $providerResponse
    ): void {
        if (
            str_contains(
                strtolower(
                    $providerResponse
                ),
                'invalid host address'
            )
        ) {
            Log::warning(
                'MekongSMS rejected the current public IP',
                [
                    'provider_response' => mb_substr(
                        $providerResponse,
                        0,
                        100
                    ),
                ]
            );

            throw new RuntimeException(
                'MekongSMS rejected this IP address. '
                . 'Please ask MekongSMS to whitelist '
                . 'your public IP.'
            );
        }
    }

    /**
     * Remove spaces, quotes, line breaks, and UTF-8 BOM.
     */
    private function cleanResponse(
        string $response
    ): string {
        return trim(
            $response,
            "\xEF\xBB\xBF \t\n\r\0\x0B\"'"
        );
    }

    /**
     * Hide most digits before writing a phone to logs.
     */
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
                max(
                    0,
                    strlen($phone) - 6
                )
            )
            . substr(
                $phone,
                -3
            );
    }
}