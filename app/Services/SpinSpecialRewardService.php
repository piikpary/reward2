<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\SpinCampaign;
use App\Models\SpinSpecialReward;
use App\Models\SpinSubCampaign;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SpinSpecialRewardService
{
    public function saveMainCampaignReward(
        SpinCampaign $campaign,
        bool $enabled,
        float|array|null $discounts
    ): Collection {
        return DB::transaction(function () use (
            $campaign,
            $enabled,
            $discounts
        ) {
            $existingRewards = SpinSpecialReward::query()
                ->where('scope_type', 'main_campaign')
                ->where('scope_id', $campaign->id)
                ->lockForUpdate()
                ->get();

            if (!$enabled) {
                $this->disableRewards($existingRewards);

                return collect();
            }

            $discountValues = $this->normalizeDiscounts(
                $discounts
            );

            if ($discountValues->isEmpty()) {
                throw new RuntimeException(
                    'At least one special discount is required.'
                );
            }

            return $this->syncRewards(
                campaign: $campaign,
                subCampaign: null,
                scopeType: 'main_campaign',
                scopeId: (int) $campaign->id,
                discounts: $discountValues,
                existingRewards: $existingRewards
            );
        });
    }

    public function saveSubCampaignReward(
        SpinCampaign $campaign,
        SpinSubCampaign $subCampaign,
        bool $enabled,
        float|array|null $discounts
    ): Collection {
        return DB::transaction(function () use (
            $campaign,
            $subCampaign,
            $enabled,
            $discounts
        ) {
            if (
                (int) $subCampaign->spin_campaign_id
                !== (int) $campaign->id
            ) {
                throw new RuntimeException(
                    'Subcampaign does not belong to the campaign.'
                );
            }

            $subCampaign = SpinSubCampaign::query()
                ->whereKey($subCampaign->id)
                ->lockForUpdate()
                ->firstOrFail();

            $existingRewards = SpinSpecialReward::query()
                ->where('scope_type', 'sub_campaign')
                ->where('scope_id', $subCampaign->id)
                ->lockForUpdate()
                ->get();

            if (!$enabled) {
                $this->disableRewards($existingRewards);

                return collect();
            }

            $discountValues = $this->normalizeDiscounts(
                $discounts
            );

            if ($discountValues->isEmpty()) {
                throw new RuntimeException(
                    'At least one special discount is required.'
                );
            }

            return $this->syncRewards(
                campaign: $campaign,
                subCampaign: $subCampaign,
                scopeType: 'sub_campaign',
                scopeId: (int) $subCampaign->id,
                discounts: $discountValues,
                existingRewards: $existingRewards
            );
        });
    }

    private function syncRewards(
        SpinCampaign $campaign,
        ?SpinSubCampaign $subCampaign,
        string $scopeType,
        int $scopeId,
        Collection $discounts,
        Collection $existingRewards
    ): Collection {
        $availableExistingRewards = $existingRewards
            ->filter(
                fn (SpinSpecialReward $reward) =>
                    !$reward->is_used
            )
            ->values();

        $syncedRewards = collect();
        $matchedRewardIds = [];

        foreach ($discounts as $discountValue) {
            /*
             * Match one unused existing reward for each occurrence.
             * This also allows two rewards to use the same percentage.
             */
            $existingReward = $availableExistingRewards
                ->first(function (
                    SpinSpecialReward $reward
                ) use (
                    $discountValue,
                    $matchedRewardIds
                ) {
                    return
                        !in_array(
                            (int) $reward->id,
                            $matchedRewardIds,
                            true
                        )
                        && round(
                            (float) $reward->special_discount,
                            2
                        ) === round(
                            (float) $discountValue,
                            2
                        );
                });

            $reward = $existingReward
                ?: new SpinSpecialReward();

            if ($existingReward) {
                $matchedRewardIds[] =
                    (int) $existingReward->id;
            }

            [$discount, $autoCreated] =
                $this->findOrCreateDiscount(
                    (float) $discountValue
                );

            $reward->fill([
                'spin_campaign_id' =>
                    $campaign->id,

                'scope_type' =>
                    $scopeType,

                'scope_id' =>
                    $scopeId,

                'special_discount' =>
                    $discountValue,

                'discount_id' =>
                    $discount->id,

                'discount_auto_created' =>
                    $reward->exists
                        ? (bool) $reward
                            ->discount_auto_created
                        : $autoCreated,

                'status' =>
                    'active',

                'is_used' =>
                    false,

                'reward_code' =>
                    $reward->reward_code
                        ?: $this
                            ->generateUniqueRewardCode(),
            ]);

            if (
                $reward->exists
                && $this->positionIsStillValid(
                    $reward
                )
            ) {
                $reward->save();

                $syncedRewards->push(
                    $reward->fresh()
                );

                continue;
            }

            if ($scopeType === 'main_campaign') {
                [
                    $assignedSubCampaignId,
                    $spinPosition,
                ] = $this->randomPositionFromCampaign(
                    $campaign,
                    $reward->exists
                        ? (int) $reward->id
                        : null
                );

                $reward->assigned_sub_campaign_id =
                    $assignedSubCampaignId;

                $reward->spin_position =
                    $spinPosition;
            } else {
                $reward->assigned_sub_campaign_id =
                    $subCampaign->id;

                $reward->spin_position =
                    $this->randomPositionFromSubCampaign(
                        $subCampaign,
                        $reward->exists
                            ? (int) $reward->id
                            : null
                    );
            }

            $reward->save();

            $syncedRewards->push(
                $reward->fresh()
            );
        }

        /*
         * Remove rewards that the admin removed from the form.
         * Already-won rewards remain as historical records.
         */
        $unusedRemovedRewards = $availableExistingRewards
            ->reject(function (
                SpinSpecialReward $reward
            ) use (
                $matchedRewardIds
            ) {
                return in_array(
                    (int) $reward->id,
                    $matchedRewardIds,
                    true
                );
            });

        foreach ($unusedRemovedRewards as $reward) {
            $discountId =
                $reward->discount_id;

            $discountAutoCreated =
                (bool) $reward->discount_auto_created;

            $reward->delete();

            $this->deactivateDiscountWhenUnused(
                $discountId,
                $discountAutoCreated
            );
        }

        return $syncedRewards;
    }

    private function normalizeDiscounts(
        float|array|null $discounts
    ): Collection {
        if ($discounts === null) {
            return collect();
        }

        $values = is_array($discounts)
            ? $discounts
            : [$discounts];

        /*
         * Do not call unique() here.
         * Two special spins may use the same percentage.
         */
        return collect($values)
            ->filter(function ($value) {
                return
                    is_numeric($value)
                    && (float) $value > 0;
            })
            ->map(function ($value) {
                return round(
                    (float) $value,
                    2
                );
            })
            ->values();
    }

    private function findOrCreateDiscount(
        float $percentage
    ): array {
        $discount = Discount::query()
        ->where('discount_percentage', $percentage)
        ->first();
        $autoCreated = false;

        if (!$discount) {
            $discount = Discount::query()->create([
                'discount_percentage' => $percentage,
                'status' => 'active',
            ]);
            $autoCreated = true;
        } elseif (
            (string) $discount->status
            !== 'active'
            && (string) $discount->status
            !== '1'
        ) {
            $discount->update([
                'status' => 'active',
            ]);
        }

        return [
            $discount,
            $autoCreated,
        ];
    }

    private function disableRewards(
        Collection $rewards
    ): void {
        foreach ($rewards as $reward) {
            if ($reward->is_used) {
                $reward->update([
                    'status' => 'inactive',
                ]);

                continue;
            }

            $discountId =
                $reward->discount_id;

            $discountAutoCreated =
                (bool) $reward->discount_auto_created;

            $reward->delete();

            $this->deactivateDiscountWhenUnused(
                $discountId,
                $discountAutoCreated
            );
        }
    }

    private function randomPositionFromCampaign(
        SpinCampaign $campaign,
        ?int $excludeRewardId = null
    ): array {
        $subCampaigns = $campaign
            ->subCampaigns()
            ->where(function ($query) {
                $query
                    ->where('status', 'active')
                    ->orWhere('status', 1);
            })
            ->lockForUpdate()
            ->get();

        $candidates = [];

        foreach ($subCampaigns as $subCampaign) {
            $positions = $this->availablePositions(
                $subCampaign,
                $excludeRewardId
            );

            foreach ($positions as $position) {
                $candidates[] = [
                    'sub_campaign_id' =>
                        (int) $subCampaign->id,

                    'position' =>
                        (int) $position,
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
            $selected['sub_campaign_id'],
            $selected['position'],
        ];
    }

    private function randomPositionFromSubCampaign(
        SpinSubCampaign $subCampaign,
        ?int $excludeRewardId = null
    ): int {
        $positions = $this->availablePositions(
            $subCampaign,
            $excludeRewardId
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
        SpinSubCampaign $subCampaign,
        ?int $excludeRewardId = null
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

        $occupiedQuery =
            SpinSpecialReward::query()
                ->where(
                    'assigned_sub_campaign_id',
                    $subCampaign->id
                )
                ->whereNotNull(
                    'spin_position'
                );

        if ($excludeRewardId !== null) {
            $occupiedQuery->whereKeyNot(
                $excludeRewardId
            );
        }

        $occupied = $occupiedQuery
            ->pluck('spin_position')
            ->map(
                fn ($value) => (int) $value
            )
            ->flip();

        $positions = [];

        for (
            $position = $firstUnusedPosition;
            $position <= $totalAllowedSpins;
            $position++
        ) {
            if (!$occupied->has($position)) {
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

        $subCampaign = SpinSubCampaign::query()
            ->find(
                $reward->assigned_sub_campaign_id
            );

        if (!$subCampaign) {
            return false;
        }

        $totalAllowedSpins =
            (int) $subCampaign->total_cases
            * (int) $subCampaign->spins_per_case;

        if (
            (int) $reward->spin_position
            <= (int) $subCampaign->total_spins_used
            || (int) $reward->spin_position
            > $totalAllowedSpins
        ) {
            return false;
        }

        return !SpinSpecialReward::query()
            ->where(
                'assigned_sub_campaign_id',
                $subCampaign->id
            )
            ->where(
                'spin_position',
                $reward->spin_position
            )
            ->whereKeyNot(
                $reward->id
            )
            ->exists();
    }

    /**
     * Call this after a special reward is successfully won.
     */
    public function handleRewardWon(
        SpinSpecialReward $reward
    ): void {
        DB::transaction(function () use ($reward) {
            $reward = SpinSpecialReward::query()
                ->whereKey($reward->id)
                ->lockForUpdate()
                ->first();

            if (!$reward) {
                return;
            }

            $this->deactivateDiscountWhenUnused(
                $reward->discount_id,
                (bool) $reward
                    ->discount_auto_created
            );
        });
    }

    private function deactivateDiscountWhenUnused(
        ?int $discountId,
        bool $discountAutoCreated
    ): void {
        if (
            !$discountId
            || !$discountAutoCreated
        ) {
            return;
        }

        $stillRequired =
            SpinSpecialReward::query()
                ->where(
                    'discount_id',
                    $discountId
                )
                ->where(
                    'status',
                    'active'
                )
                ->where(
                    'is_used',
                    false
                )
                ->exists();

        if ($stillRequired) {
            return;
        }

        Discount::query()
            ->whereKey($discountId)
            ->delete();
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