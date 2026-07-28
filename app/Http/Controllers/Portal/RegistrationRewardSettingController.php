<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\RegistrationRewardSetting;
use App\Services\RegistrationRewardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegistrationRewardSettingController
    extends Controller
{
    public function edit(): View
    {
        $setting =
            RegistrationRewardSetting::query()
                ->firstOrCreate(
                    ['id' => 1],
                    [
                        'is_enabled' => false,
                        'wallet_type' => 'spin',
                        'amount' => 0,
                    ]
                );

        return view(
            'portal.registration-reward.edit',
            compact('setting')
        );
    }

    public function update(
        Request $request
    ): RedirectResponse {
        /*
         * Checkbox is absent when unchecked, so
         * normalize it before validation.
         */
        $request->merge([
            'is_enabled' =>
                $request->boolean(
                    'is_enabled'
                ),
        ]);

        $validated = $request->validate([
            'is_enabled' => [
                'required',
                'boolean',
            ],

            'wallet_type' => [
                'required_if:is_enabled,1',
                'nullable',
                Rule::in([
                    'spin',
                    'discount',
                ]),
            ],

            'amount' => [
                'required_if:is_enabled,1',
                'nullable',
                'numeric',
                'min:1',
                'max:1000000',
            ],
        ]);

        $setting =
            RegistrationRewardSetting::query()
                ->firstOrCreate(
                    ['id' => 1],
                    [
                        'is_enabled' => false,
                        'wallet_type' => 'spin',
                        'amount' => 0,
                    ]
                );

        $setting->update([
            'is_enabled' =>
                (bool) $validated[
                    'is_enabled'
                ],

            'wallet_type' =>
                $validated['wallet_type']
                ?? $setting->wallet_type,

            'amount' =>
                $validated['amount']
                ?? $setting->amount,

            'updated_by' =>
                $request->user()?->id,
        ]);

        RegistrationRewardService
            ::clearSettingCache();

        return back()->with(
            'success',
            'Registration reward setting updated successfully.'
        );
    }
}