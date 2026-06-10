<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'min:8', 'max:15'],
            'otp' => ['required', 'string', 'size:4'],
            'platform' => ['sometimes', 'string', 'in:mobile,web'],
            'device_uuid' => ['required_unless:platform,web', 'string'],
            'fcm_token' => ['required_unless:platform,web', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Phone number is required.',
            'phone.min' => 'Phone number must be at least 8 digits.',
            'otp.required' => 'OTP is required.',
            'otp.size' => 'OTP must be exactly 4 digits.',
            'device_uuid.required_unless' => 'The device UUID is required.',
            'fcm_token.required_unless' => 'The FCM token is required.',
        ];
    }
}