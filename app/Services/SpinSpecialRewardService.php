<?php

namespace App\Services;

use App\Models\SpinCampaign;
use App\Models\SpinSpecialReward;
use App\Models\SpinSubCampaign;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Illuminate\Support\Str;

class SpinSpecialRewardService
{
    public function saveMainCampaignReward(
        SpinCampaign $campaign,
        bool $enabled,
        ?float $discount
    ): ?SpinSpecialReward {
        return DB::transaction(function () use (
            $campaign,
            $enabled,
            $discount
        ) {
            $reward = SpinSpecialReward::query()
                ->where(
                    'scope_type',
                    'main_campaign'
                )
                ->where(
                    'scope_id',
                    $campaign->id
                )
                ->lockForUpdate()
                ->first();

            if (!$enabled) {
                if ($reward && !$reward->is_used) {
                    $reward->delete();
                } elseif ($reward) {
                    $reward->update([
                        'status' => 'inactive',
                    ]);
                }

                return null;
            }

            if ($discount === null) {
                throw new RuntimeException(
                    'Special discount is required.'
                );
            }

            if ($reward && $reward->is_used) {
                return $reward;
            }

            $reward = $reward ?: new SpinSpecialReward();

            $reward->fill([
                'spin_campaign_id' =>
                    $campaign->id,

                'scope_type' =>
                    'main_campaign',

                'scope_id' =>
                    $campaign->id,

                'special_discount' =>
                    $discount,

                'status' =>
                    'active',

                'is_used' =>
                    false,
                'reward_code' =>
                    $reward->reward_code
                        ?: $this->generateUniqueRewardCode(),
            ]);

            /*
             * Keep an existing valid hidden position.
             */
            if (
                $reward->exists
                && $this->positionIsStillValid($reward)
            ) {
                $reward->save();

                return $reward->fresh();
            }

            [$subCampaignId, $position] =
                $this->randomPositionFromCampaign(
                    $campaign
                );

            $reward->assigned_sub_campaign_id =
                $subCampaignId;

            $reward->spin_position =
                $position;

            $reward->save();

            return $reward->fresh();
        });
    }

    public function saveSubCampaignReward(
        SpinCampaign $campaign,
        SpinSubCampaign $subCampaign,
        bool $enabled,
        ?float $discount
    ): ?SpinSpecialReward {
        return DB::transaction(function () use (
            $campaign,
            $subCampaign,
            $enabled,
            $discount
        ) {
            if (
                (int) $subCampaign->spin_campaign_id
                !== (int) $campaign->id
            ) {
                throw new RuntimeException(
                    'Subcampaign does not belong to the campaign.'
                );
            }

            $reward = SpinSpecialReward::query()
                ->where(
                    'scope_type',
                    'sub_campaign'
                )
                ->where(
                    'scope_id',
                    $subCampaign->id
                )
                ->lockForUpdate()
                ->first();

            if (!$enabled) {
                if ($reward && !$reward->is_used) {
                    $reward->delete();
                } elseif ($reward) {
                    $reward->update([
                        'status' => 'inactive',
                    ]);
                }

                return null;
            }

            if ($discount === null) {
                throw new RuntimeException(
                    'Special discount is required.'
                );
            }

            if ($reward && $reward->is_used) {
                return $reward;
            }

            $reward = $reward ?: new SpinSpecialReward();

            $reward->fill([
                'spin_campaign_id' =>
                    $campaign->id,

                'scope_type' =>
                    'sub_campaign',

                'scope_id' =>
                    $subCampaign->id,

                'special_discount' =>
                    $discount,

                'status' =>
                    'active',

                'is_used' =>
                    false,

                'reward_code' =>
                    $reward->reward_code
                        ?: $this->generateUniqueRewardCode(),
            ]);

            if (
                $reward->exists
                && $this->positionIsStillValid($reward)
            ) {
                $reward->save();

                return $reward->fresh();
            }

            $reward->assigned_sub_campaign_id =
                $subCampaign->id;

            $reward->spin_position =
                $this->randomPositionFromSubCampaign(
                    $subCampaign
                );

            $reward->save();

            return $reward->fresh();
        });
    }

    private function randomPositionFromCampaign(
        SpinCampaign $campaign
    ): array {
        $candidates = [];

        $subCampaigns = $campaign
            ->subCampaigns()
            ->where(function ($query) {
                $query
                    ->where('status', 'active')
                    ->orWhere('status', 1);
            })
            ->lockForUpdate()
            ->get();

        foreach ($subCampaigns as $subCampaign) {
            foreach (
                $this->availablePositions(
                    $subCampaign
                ) as $position
            ) {
                $candidates[] = [
                    'sub_campaign_id' =>
                        $subCampaign->id,

                    'position' =>
                        $position,
                ];
            }
        }

        if (empty($candidates)) {
            throw new RuntimeException(
                'No unused spin position is available.'
            );
        }

        $selected = $candidates[
            array_rand($candidates)
        ];

        return [
            (int) $selected['sub_campaign_id'],
            (int) $selected['position'],
        ];
    }

    private function randomPositionFromSubCampaign(
        SpinSubCampaign $subCampaign
    ): int {
        $positions = $this->availablePositions(
            $subCampaign
        );

        if (empty($positions)) {
            throw new RuntimeException(
                'No unused spin position is available in this subcampaign.'
            );
        }

        return (int) $positions[
            array_rand($positions)
        ];
    }

    private function availablePositions(
        SpinSubCampaign $subCampaign
    ): array {
        $totalAllowedSpins =
            (int) $subCampaign->total_cases
            * (int) $subCampaign->spins_per_case;

        $firstUnusedPosition =
            (int) $subCampaign->total_spins_used
            + 1;

        if (
            $firstUnusedPosition
            > $totalAllowedSpins
        ) {
            return [];
        }

        $occupied = SpinSpecialReward::query()
            ->where(
                'assigned_sub_campaign_id',
                $subCampaign->id
            )
            ->whereNotNull('spin_position')
            ->pluck('spin_position')
            ->map(fn ($value) => (int) $value)
            ->all();

        $positions = [];

        for (
            $position = $firstUnusedPosition;
            $position <= $totalAllowedSpins;
            $position++
        ) {
            if (
                !in_array(
                    $position,
                    $occupied,
                    true
                )
            ) {
                $positions[] = $position;
            }
        }

        return $positions;
    }

    private function positionIsStillValid(
        SpinSpecialReward $reward
    ): bool {
        if (
            !$reward->assigned_sub_campaign_id
            || !$reward->spin_position
        ) {
            return false;
        }

        $subCampaign = SpinSubCampaign::find(
            $reward->assigned_sub_campaign_id
        );

        if (!$subCampaign) {
            return false;
        }

        $totalAllowedSpins =
            (int) $subCampaign->total_cases
            * (int) $subCampaign->spins_per_case;

        return (
            (int) $reward->spin_position
            > (int) $subCampaign->total_spins_used
            && (int) $reward->spin_position
            <= $totalAllowedSpins
        );
    }
    private function generateUniqueRewardCode(): string
{
    do {
        $code =
            'SSR-'
            . strtoupper(
                Str::random(8)
            );
    } while (
        SpinSpecialReward::query()
            ->where(
                'reward_code',
                $code
            )
            ->exists()
    );

    return $code;
}
}