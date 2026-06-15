<?php

namespace App\Http\Controllers\v2\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class PasscodeController extends Controller
{
    use ApiResponse;

    /*
    |--------------------------------------------------------------------------
    | Create passcode
    |--------------------------------------------------------------------------
    */

    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'passcode' => [
                'required',
                'digits:4',
                'confirmed',
            ],
        ]);

        $user = $request->user();

        $user->passcode = Hash::make(
            $validated['passcode']
        );

        $user->save();

        return $this->successResponse(
            [],
            'Passcode created successfully'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Verify current passcode
    |--------------------------------------------------------------------------
    */

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'passcode' => [
                'required',
                'digits:4',
            ],
        ]);

        $user = $request->user();

        $key = 'passcode_verify:' . $user->id;

        $maxAttempts = (int) env(
            'PASSCODE_VERIFY_MAX_ATTEMPTS',
            3
        );

        $decaySeconds = (int) env(
            'PASSCODE_VERIFY_DECAY_SECONDS',
            300
        );

        if (
            RateLimiter::tooManyAttempts(
                $key,
                $maxAttempts
            )
        ) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'message' =>
                    'Too many wrong passcode attempts. '
                    . 'Please try again in '
                    . ceil($seconds / 60)
                    . ' minutes.',
                'data' => [
                    'retry_after_seconds' =>
                        $seconds,

                    'retry_after_minutes' =>
                        (int) ceil($seconds / 60),
                ],
            ], 429);
        }

        if (
            !$user->passcode
            || !Hash::check(
                $validated['passcode'],
                $user->passcode
            )
        ) {
            RateLimiter::hit(
                $key,
                $decaySeconds
            );

            $attempts = RateLimiter::attempts($key);

            $remainingAttempts = max(
                $maxAttempts - $attempts,
                0
            );

            return response()->json([
                'success' => false,
                'message' => 'Invalid passcode',
                'data' => [
                    'remaining_attempts' =>
                        $remainingAttempts,
                ],
            ], 400);
        }

        RateLimiter::clear($key);

        return $this->successResponse(
            [],
            'Passcode verified successfully'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Step 1: Input phone and request OTP
    |--------------------------------------------------------------------------
    */

    public function forget(
        Request $request,
        OtpService $otpService
    ): JsonResponse {
        $validated = $request->validate([
            'phone' => [
                'required',
                'string',
                'min:8',
                'max:15',
            ],
        ]);

        $user = $request->user();

        $phone = $otpService->normalizePhone(
            $validated['phone']
        );

        if ($user->phone_number !== $phone) {
            return $this->errorResponse(
                'Phone number does not match current user',
                400
            );
        }

        /*
         * Remove an older successful OTP verification.
         */
        Cache::forget(
            $this->resetVerificationKey(
                $user->id,
                $phone
            )
        );

        $otpService->sendOtp($phone);

        return $this->successResponse([
            'phone_number' => $phone,
        ], 'OTP sent for passcode reset');
    }

    /*
    |--------------------------------------------------------------------------
    | Step 2: Verify reset OTP
    |--------------------------------------------------------------------------
    */

    public function verifyResetOtp(
        Request $request,
        OtpService $otpService
    ): JsonResponse {
        $validated = $request->validate([
            'phone' => [
                'required',
                'string',
                'min:8',
                'max:15',
            ],

            'otp' => [
                'required',
                'string',
                'size:4',
            ],
        ]);

        $user = $request->user();

        $phone = $otpService->normalizePhone(
            $validated['phone']
        );

        if ($user->phone_number !== $phone) {
            return $this->errorResponse(
                'Phone number does not match current user',
                400
            );
        }

        /*
         * Static OTP is currently 0000.
         * OtpService verifies and consumes the OTP.
         */
        $otpService->verify(
            $phone,
            $validated['otp']
        );

        /*
         * Allow passcode reset for 10 minutes.
         */
        Cache::put(
            $this->resetVerificationKey(
                $user->id,
                $phone
            ),
            true,
            now()->addMinutes(10)
        );

        return $this->successResponse([
            'phone_number' => $phone,
            'expires_in_minutes' => 10,
        ], 'OTP verified successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | Step 3: Reset passcode after OTP verification
    |--------------------------------------------------------------------------
    */

    public function reset(
        Request $request,
        OtpService $otpService
    ): JsonResponse {
        $validated = $request->validate([
            'phone' => [
                'required',
                'string',
                'min:8',
                'max:15',
            ],

            'new_passcode' => [
                'required',
                'digits:4',
            ],

            'confirm_passcode' => [
                'required',
                'same:new_passcode',
            ],
        ]);

        $user = $request->user();

        $phone = $otpService->normalizePhone(
            $validated['phone']
        );

        if ($user->phone_number !== $phone) {
            return $this->errorResponse(
                'Phone number does not match current user',
                400
            );
        }

        $verificationKey =
            $this->resetVerificationKey(
                $user->id,
                $phone
            );

        if (!Cache::has($verificationKey)) {
            return $this->errorResponse(
                'OTP verification is required or has expired',
                403
            );
        }

        $user->passcode = Hash::make(
            $validated['new_passcode']
        );

        $user->save();

        /*
         * Reset permission is valid for one use only.
         */
        Cache::forget($verificationKey);

        /*
         * Clear old incorrect-passcode attempts.
         */
        RateLimiter::clear(
            'passcode_verify:' . $user->id
        );

        return $this->successResponse([
            'user_id' => $user->id,
            'phone_number' => $user->phone_number,
        ], 'Passcode reset successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | Reset verification cache key
    |--------------------------------------------------------------------------
    */

    private function resetVerificationKey(
        int $userId,
        string $phone
    ): string {
        return 'passcode_reset_verified:'
            . $userId
            . ':'
            . sha1($phone);
    }
}