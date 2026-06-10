<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class AuthService
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function handleOtpLoginOrRegister(array $data, UserType $registrationType, array $allowedLoginTypes): array
    {
        $phone = $this->otpService->normalizePhone($data['phone']);
        $otp = $data['otp'];

        $this->otpService->verify($phone, $otp);

        $user = User::findByPhoneNumber($phone);

        if ($user) {
            if (!in_array($user->user_type, $allowedLoginTypes)) {
                throw new Exception(__('messages.user_type_mismatch'), 409);
            }

            if ($user->status !== UserStatus::ACTIVE) {
                throw new Exception(__('messages.user_inactive'), 403);
            }

            if (($data['platform'] ?? 'mobile') !== 'web') {
                $this->syncDeviceDetails($user, $data['device_uuid'], $data['fcm_token']);
            }

            return [
                'user' => $user,
                'message' => __('messages.login_success'),
                'status_code' => 200,
            ];
        }

        $user = $this->registerNewUser($phone, $registrationType);

        if (($data['platform'] ?? 'mobile') !== 'web') {
            $this->syncDeviceDetails($user, $data['device_uuid'], $data['fcm_token']);
        }

        return [
            'user' => $user,
            'message' => __('messages.registration_success'),
            'status_code' => 201,
        ];
    }

    public function syncDeviceDetails(User $user, string $newDeviceUuid, string $newFcmToken): void
    {
        DB::transaction(function () use ($user, $newDeviceUuid, $newFcmToken) {
            User::where('device_uuid', $newDeviceUuid)
                ->where('id', '!=', $user->id)
                ->update(['device_uuid' => null]);

            User::where('fcm_token', $newFcmToken)
                ->where('id', '!=', $user->id)
                ->update(['fcm_token' => null]);

            $user->device_uuid = $newDeviceUuid;
            $user->fcm_token = $newFcmToken;
            $user->save();
        });
    }

    public function createAuthToken(User $user, string $tokenName): string
    {
        $user->tokens()->delete();

        return $user->createToken($tokenName)->plainTextToken;
    }

        private function registerNewUser(string $phone, UserType $userType): User
    {
        return DB::transaction(function () use ($phone, $userType) {
            $user = User::create([
                'phone_number' => $phone,
                'user_type' => $userType,
                'status' => UserStatus::ACTIVE,
                'name' => $phone,
                'password' => bcrypt(str()->random(32)),
            ]);

            app(\App\Services\WalletService::class)->ensureUserWallets($user);

            return $user;
        });
    }
}