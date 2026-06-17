<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SpinSpecialReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpecialSpinRewardController extends Controller
{
    public function index(Request $request)
    {
        $query = SpinSpecialReward::query()
            ->with([
                'campaign',
                'assignedSubCampaign',
                'winner',
            ])
            ->latest();

        if ($request->filled('status')) {
            if ($request->status === 'waiting') {
                $query->where('is_used', false);
            }

            if ($request->status === 'winner') {
                $query
                    ->where('is_used', true)
                    ->where('is_redeemed', false);
            }

            if ($request->status === 'verified') {
                $query->where('is_redeemed', true);
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($query) use ($search) {
                $query
                    ->where(
                        'reward_code',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'winner',
                        function ($winnerQuery) use ($search) {
                            $winnerQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'phone_number',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
            });
        }

        $specialRewards = $query->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => SpinSpecialReward::count(),

            'waiting' => SpinSpecialReward::query()
                ->where('is_used', false)
                ->count(),

            'winner' => SpinSpecialReward::query()
                ->where('is_used', true)
                ->where('is_redeemed', false)
                ->count(),

            'verified' => SpinSpecialReward::query()
                ->where('is_redeemed', true)
                ->count(),
        ];

        return view(
            'portal.special-spin-rewards.index',
            compact(
                'specialRewards',
                'summary'
            )
        );
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'reward_code' => [
                'required',
                'string',
                'exists:spin_special_rewards,reward_code',
            ],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $reward = SpinSpecialReward::query()
                    ->where(
                        'reward_code',
                        strtoupper(
                            trim($validated['reward_code'])
                        )
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$reward->is_used) {
                    throw new \RuntimeException(
                        'This code does not have a winner yet.'
                    );
                }

                if (!$reward->used_by_user_id) {
                    throw new \RuntimeException(
                        'Winner information is missing for this code.'
                    );
                }

                if ($reward->is_redeemed) {
                    throw new \RuntimeException(
                        'This code has already been verified.'
                    );
                }

                $reward->update([
                    'is_redeemed' => true,
                    'redeemed_at' => now(),
                ]);
            });
        } catch (\RuntimeException $exception) {
            return back()
                ->withInput()
                ->withErrors([
                    'reward_code' =>
                        $exception->getMessage(),
                ]);
        }

        return back()->with(
            'success',
            'Special spin code verified successfully.'
        );
    }
}