<?php

namespace App\Http\Controllers\v2\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class PasscodeController extends Controller
{
    use ApiResponse;

    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'passcode' => ['required', 'digits:4', 'confirmed'],
        ]);

        $user = $request->user();
        $user->passcode = Hash::make($validated['passcode']);
        $user->save();

        return $this->successResponse([], 'Passcode created successfully');
    }

    public function verify(Request $request): JsonResponse
{
    $validated = $request->validate([
        'passcode' => ['required', 'digits:4'],
    ]);

    $user = $request->user();

    $key = 'passcode_verify:' . $user->id;

    $maxAttempts = (int) env('PASSCODE_VERIFY_MAX_ATTEMPTS', 3);
    $decaySeconds = (int) env('PASSCODE_VERIFY_DECAY_SECONDS', 300);

    if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
        $seconds = RateLimiter::availableIn($key);

        return response()->json([
            'success' => false,
            'message' => "Too many wrong passcode attempts. Please try again in " . ceil($seconds / 60) . " minutes.",
            'data' => [
                'retry_after_seconds' => $seconds,
                'retry_after_minutes' => (int) ceil($seconds / 60),
            ],
        ], 429);
    }

    if (!$user->passcode || !Hash::check($validated['passcode'], $user->passcode)) {
        RateLimiter::hit($key, $decaySeconds);

        $attempts = RateLimiter::attempts($key);
        $remainingAttempts = max($maxAttempts - $attempts, 0);

        return response()->json([
            'success' => false,
            'message' => 'Invalid passcode',
            'data' => [
                'remaining_attempts' => $remainingAttempts,
            ],
        ], 400);
    }

    RateLimiter::clear($key);

    return $this->successResponse([], 'Passcode verified successfully');
}

    public function forget(Request $request, OtpService $otpService): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:8', 'max:15'],
        ]);

        $user = $request->user();

        $phone = $otpService->normalizePhone($validated['phone']);

        if ($user->phone_number !== $phone) {
            return $this->errorResponse('Phone number does not match current user', 400);
        }

        $otpService->sendOtp($phone);

        return $this->successResponse([], 'OTP sent for passcode reset');
    }

    public function reset(Request $request, OtpService $otpService): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:8', 'max:15'],
            'otp' => ['required', 'string', 'size:4'],
            'new_passcode' => ['required', 'digits:4'],
            'confirm_passcode' => ['required', 'same:new_passcode'],
        ]);

        $user = $request->user();
        $phone = $otpService->normalizePhone($validated['phone']);

        if ($user->phone_number !== $phone) {
            return $this->errorResponse('Phone number does not match current user', 400);
        }

        $otpService->verify($phone, $validated['otp']);

        $user->passcode = Hash::make($validated['new_passcode']);
        $user->save();

        return $this->successResponse([
            'user_id' => $user->id,
            'phone_number' => $user->phone_number,
        ], 'Passcode reset successfully');
    }
}