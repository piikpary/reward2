<?php

namespace App\Http\Controllers\v2\Customer;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\RequestOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\VerifyOtpResource;
use App\Http\Traits\ApiResponse;
use App\Services\AuthService;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use App\Exceptions\ApiRateLimitException;

class AuthController extends Controller
{
    use ApiResponse;

    public function requestOtp(RequestOtpRequest $request, OtpService $otpService): JsonResponse
{
    try {
        $phone = $request->validated('phone');

        $otpService->sendOtp($phone);

        return $this->successResponse([], __('messages.otp_sent_successfully'));
    } catch (ApiRateLimitException $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'data' => [
                'retry_after_seconds' => $e->retryAfterSeconds(),
                'retry_after_minutes' => $e->retryAfterMinutes(),
            ],
        ], 429);
    } catch (\Exception $e) {
        return $this->errorResponse($e->getMessage(), $e->getCode() ?: 400);
    }
}

    public function verifyOtp(VerifyOtpRequest $request, AuthService $authService): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            $platform = $request->input('platform', 'mobile');
            $validatedData['platform'] = $platform;

            $result = $authService->handleOtpLoginOrRegister(
                $validatedData,
                UserType::CUSTOMER,
                [UserType::CUSTOMER]
            );

            $tokenName = $platform === 'web'
                ? 'webapp-session-' . uniqid()
                : $validatedData['device_uuid'];

            $token = $authService->createAuthToken($result['user'], $tokenName);

            $userData = new VerifyOtpResource($result['user'], $token);

            return $this->successResponse($userData, $result['message'], $result['status_code']);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $this->safeHttpCode($e->getCode()));
        }
    }

    private function safeHttpCode($code): int
    {
        $code = (int) $code;

        return $code >= 100 && $code <= 599 ? $code : 400;
    }
}