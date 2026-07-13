<?php

namespace App\Http\Requests\Portal\ShareCampaign;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreShareCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' =>
                $this->boolean('is_active'),

            'is_published' =>
                $this->boolean('is_published'),

            'reward_repeatable' =>
                $this->boolean('reward_repeatable'),
        ]);
    }

    public function rules(): array
    {
        return [
            'business_id' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'share_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'required_shares' => [
                'required',
                'integer',
                'min:1',
                'max:1000000',
            ],

            'reward_spins' => [
                'required',
                'integer',
                'min:1',
                'max:1000000',
            ],

            'starts_at' => [
                'nullable',
                'date',
            ],

            'expires_at' => [
                'nullable',
                'date',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'is_published' => [
                'required',
                'boolean',
            ],

            'reward_repeatable' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (
                    !$this->filled('starts_at')
                    || !$this->filled('expires_at')
                ) {
                    return;
                }

                $startsAt = strtotime(
                    (string) $this->input('starts_at')
                );

                $expiresAt = strtotime(
                    (string) $this->input('expires_at')
                );

                if (
                    $startsAt !== false
                    && $expiresAt !== false
                    && $expiresAt <= $startsAt
                ) {
                    $validator->errors()->add(
                        'expires_at',
                        'Expiry date must be after the start date.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' =>
                'Campaign title is required.',

            'image.required' =>
                'Campaign poster is required.',

            'image.image' =>
                'Campaign poster must be an image.',

            'image.max' =>
                'Campaign poster must not exceed 5 MB.',

            'required_shares.required' =>
                'Required shares is required.',

            'required_shares.min' =>
                'Required shares must be at least 1.',

            'reward_spins.required' =>
                'Reward spins is required.',

            'reward_spins.min' =>
                'Reward spins must be at least 1.',
        ];
    }
}