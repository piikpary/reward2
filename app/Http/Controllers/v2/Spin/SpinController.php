<?php

namespace App\Http\Controllers\v2\Spin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Discount;
use App\Models\SpinCampaign;
use App\Models\SpinCaseSequence;
use App\Models\SpinResult;
use App\Models\SpinSpecialCase;
use App\Models\SpinSpecialReward;
use App\Models\SpinSubCampaign;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\SpinSpecialRewardService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpinController extends Controller
{
    use ApiResponse;

    public function getDiscount(
        Request $request,
        WalletService $walletService,
        SpinSpecialRewardService $specialRewardService
    ): JsonResponse {
        $validated = $request->validate([
            'qty' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $user = $request->user();
        $qty = (int) $validated['qty'];

        $walletService->ensureUserWallets($user);

        try {
            $result = DB::transaction(function () use (
                $user,
                $qty,
                $specialRewardService
            ) {
                $spinWallet = Wallet::query()
                    ->where('type', 'spin')
                    ->firstOrFail();

                $discountWallet = Wallet::query()
                    ->where('type', 'discount')
                    ->firstOrFail();

                $userSpinWallet = UserWallet::query()
                    ->where('user_id', $user->id)
                    ->where('wallet_id', $spinWallet->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $userDiscountWallet = UserWallet::query()
                    ->where('user_id', $user->id)
                    ->where('wallet_id', $discountWallet->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((float) $userSpinWallet->balance < $qty) {
                    throw new \Exception(
                        'Insufficient spin balance.',
                        400
                    );
                }

                $campaign = $this->getActiveCampaign();

                if (!$campaign) {
                    throw new \Exception(
                        'No active spin campaign found.',
                        404
                    );
                }

                $subCampaigns = SpinSubCampaign::query()
                    ->where(
                        'spin_campaign_id',
                        $campaign->id
                    )
                    ->where(function ($query) {
                        $query
                            ->where('status', 'active')
                            ->orWhere('status', 1)
                            ->orWhere('status', true);
                    })
                    ->orderByDesc('priority')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $subCampaign = $subCampaigns->first(
                    function ($item) use ($qty) {
                        $spinsPerCase =
                            (int) $item->spins_per_case;

                        if ($spinsPerCase < 1) {
                            return false;
                        }

                        if ($qty > $spinsPerCase) {
                            return false;
                        }

                        $totalAllowedSpins =
                            (int) $item->total_cases
                            * $spinsPerCase;

                        $remainingQuota =
                            $totalAllowedSpins
                            - (int) $item->total_spins_used;

                        return $remainingQuota >= $qty;
                    }
                );

                if (!$subCampaign) {
                    throw new \Exception(
                        'The requested quantity exceeds the active spin rule or there is not enough remaining spin quota.',
                        400
                    );
                }

                $totalCases =
                    (int) $subCampaign->total_cases;

                $spinsPerCase =
                    (int) $subCampaign->spins_per_case;

                $normalDiscountTotal =
                    (int) $subCampaign->normal_discount_total;

                if ($totalCases < 1) {
                    throw new \Exception(
                        'Subcampaign total cases is not configured.',
                        400
                    );
                }

                if ($spinsPerCase < 1) {
                    throw new \Exception(
                        'Subcampaign spins per case is not configured.',
                        400
                    );
                }

                if ($normalDiscountTotal < 1) {
                    throw new \Exception(
                        'Subcampaign normal discount total is not configured.',
                        400
                    );
                }

                if ($qty > $spinsPerCase) {
                    throw new \Exception(
                        "Quantity cannot exceed {$spinsPerCase} spins for the selected rule.",
                        422
                    );
                }

                $totalAllowedSpins =
                    $totalCases * $spinsPerCase;

                $remainingQuota =
                    $totalAllowedSpins
                    - (int) $subCampaign->total_spins_used;

                if ($remainingQuota < $qty) {
                    throw new \Exception(
                        'The selected subcampaign does not have enough remaining spin quota.',
                        400
                    );
                }

                $discountList =
                    $this->getActiveDiscountList();

                if (empty($discountList)) {
                    throw new \Exception(
                        'Discount list is empty. Please add active discounts first.',
                        400
                    );
                }

                $lastCaseNumber = SpinCaseSequence::query()
                    ->where(
                        'spin_campaign_id',
                        $campaign->id
                    )
                    ->where(
                        'spin_sub_campaign_id',
                        $subCampaign->id
                    )
                    ->max('case_number');

                $caseNumber =
                    ((int) $lastCaseNumber) + 1;

                $specialCase = SpinSpecialCase::query()
                    ->where(
                        'spin_campaign_id',
                        $campaign->id
                    )
                    ->where(
                        'spin_sub_campaign_id',
                        $subCampaign->id
                    )
                    ->where(
                        'case_number',
                        $caseNumber
                    )
                    ->where(function ($query) {
                        $query
                            ->where('status', 'active')
                            ->orWhere('status', 1)
                            ->orWhere('status', true);
                    })
                    ->first();

                $caseTotalDiscount = $specialCase
                    ? (int) $specialCase->total_discount
                    : $normalDiscountTotal;

                $requestSequence =
                    $this->generateRequestSequence(
                        $qty,
                        $spinsPerCase,
                        $caseTotalDiscount,
                        $discountList
                    );

                $sequenceTotal =
                    array_sum($requestSequence);

                $isFullRotation =
                    $qty === $spinsPerCase;

                if (
                    $isFullRotation
                    && $sequenceTotal !== $caseTotalDiscount
                ) {
                    throw new \Exception(
                        'Full rotation discount total does not match the configured target.',
                        400
                    );
                }

                if ($sequenceTotal > $caseTotalDiscount) {
                    throw new \Exception(
                        'Rotation discount total exceeds the configured target.',
                        400
                    );
                }

                SpinCaseSequence::create([
                    'spin_campaign_id' =>
                        $campaign->id,

                    'spin_sub_campaign_id' =>
                        $subCampaign->id,

                    'case_number' =>
                        $caseNumber,

                    'total_discount' =>
                        $caseTotalDiscount,

                    'sequence' =>
                        $requestSequence,

                    'used_spins' =>
                        $qty,
                ]);

                $results = [];
                $totalDiscountEarned = 0;

                foreach (
                    $requestSequence
                    as $index => $normalDiscountPercentage
                ) {
                    $spinNumber = $index + 1;

                    $currentSpinPosition =
                        (int) $subCampaign->total_spins_used
                        + $spinNumber;

                    $specialReward = SpinSpecialReward::query()
                        ->where(
                            'spin_campaign_id',
                            $campaign->id
                        )
                        ->where(
                            'assigned_sub_campaign_id',
                            $subCampaign->id
                        )
                        ->where(
                            'spin_position',
                            $currentSpinPosition
                        )
                        ->where('status', 'active')
                        ->where('is_used', false)
                        ->lockForUpdate()
                        ->first();

                    $discountPercentage = $specialReward
                        ? (float) $specialReward->special_discount
                        : (float) $normalDiscountPercentage;

                    $userSpinWallet->balance =
                        (float) $userSpinWallet->balance - 1;

                    $userSpinWallet->save();

                    $userDiscountWallet->balance =
                        (float) $userDiscountWallet->balance
                        + $discountPercentage;

                    $userDiscountWallet->save();

                    SpinResult::create([
                        'spin_campaign_id' =>
                            $campaign->id,

                        'spin_sub_campaign_id' =>
                            $subCampaign->id,

                        'user_id' =>
                            $user->id,

                        'case_number' =>
                            $caseNumber,

                        'spin_number' =>
                            $spinNumber,

                        'discount_percentage' =>
                            $discountPercentage,

                        'case_total_discount' =>
                            $caseTotalDiscount,
                    ]);

                    if ($specialReward) {
                        $updated = SpinSpecialReward::query()
                            ->whereKey($specialReward->id)
                            ->where('is_used', false)
                            ->update([
                                'is_used' =>
                                    true,

                                'used_by_user_id' =>
                                    $user->id,

                                'used_at' =>
                                    now(),
                            ]);

                        if ($updated !== 1) {
                            throw new \RuntimeException(
                                'This special reward has already been awarded.'
                            );
                        }

                        $specialReward->refresh();

                        $specialRewardService->handleRewardWon(
                            $specialReward
                        );
                    }

                    /*
                     * Keep one discount transaction for every spin,
                     * because each spin may have a different value.
                     */
                    WalletTransaction::create([
                        'user_id' =>
                            $user->id,

                        'wallet_id' =>
                            $discountWallet->id,

                        'transaction_type' =>
                            'discount_earned',

                        'wallet_type' =>
                            'discount',

                        'amount' =>
                            $discountPercentage,

                        'from_user_id' =>
                            null,

                        'to_user_id' =>
                            $user->id,

                        'description' =>
                            $specialReward
                                ? "Special discount {$discountPercentage}% earned: campaign {$campaign->id}, subcampaign {$subCampaign->id}, position {$currentSpinPosition}"
                                : "Discount {$discountPercentage}% earned: campaign {$campaign->id}, subcampaign {$subCampaign->id}, case {$caseNumber}, spin {$spinNumber}",

                        'special_reward_code' =>
                            $specialReward?->reward_code,
                    ]);

                    $totalDiscountEarned +=
                        $discountPercentage;

                    $results[] = [
                        'spin_no' =>
                            $spinNumber,

                        'discount_percentage' =>
                            $discountPercentage,

                        'normal_discount_percentage' =>
                            (float) $normalDiscountPercentage,

                        'case_number' =>
                            $caseNumber,

                        'spin_number' =>
                            $spinNumber,

                        'is_special_case' =>
                            $specialCase !== null,

                        'is_special_spin' =>
                            $specialReward !== null,

                        'special_reward_scope' =>
                            $specialReward?->scope_type,

                        'special_reward_code' =>
                            $specialReward?->reward_code,
                    ];
                }

                /*
                 * Save only one spin-used transaction for the
                 * complete API request.
                 *
                 * qty 1 = amount 1
                 * qty 2 = amount 2
                 * qty 3 = amount 3
                 * qty 4 = amount 4
                 */
                WalletTransaction::create([
                    'user_id' =>
                        $user->id,

                    'wallet_id' =>
                        $spinWallet->id,

                    'transaction_type' =>
                        'spin_used',

                    'wallet_type' =>
                        'spin',

                    'amount' =>
                        $qty,

                    'from_user_id' =>
                        $user->id,

                    'to_user_id' =>
                        null,

                    'description' =>
                        "{$qty} spin(s) used: campaign {$campaign->id}, subcampaign {$subCampaign->id}, case {$caseNumber}",

                    'special_reward_code' =>
                        null,
                ]);

                $subCampaign->total_spins_used =
                    (int) $subCampaign->total_spins_used
                    + $qty;

                $subCampaign->save();

                $campaign->total_spins_used =
                    (int) $campaign->subCampaigns()
                        ->sum('total_spins_used');

                $campaign->save();

                $remainingCaseDiscount = max(
                    0,
                    $caseTotalDiscount - $sequenceTotal
                );

                $remainingCaseSpins = max(
                    0,
                    $spinsPerCase - $qty
                );

                return [
                    'qty' =>
                        $qty,

                    'total_discount_earned' =>
                        $totalDiscountEarned,

                    'selected_rule' => [
                        'id' =>
                            $subCampaign->id,

                        'name' =>
                            $subCampaign->name,
                    ],

                    'spins' =>
                        $results,

                    'current_case' => [
                        'case_number' =>
                            $caseNumber,

                        'spin_number' =>
                            $qty,

                        'spins_per_case' =>
                            $spinsPerCase,

                        'case_total_discount' =>
                            $caseTotalDiscount,

                        'is_special_case' =>
                            $specialCase !== null,

                        'sequence' =>
                            $requestSequence,

                        'sequence_total' =>
                            $sequenceTotal,

                        'remaining_case_discount' =>
                            $remainingCaseDiscount,

                        'remaining_case_spins' =>
                            $remainingCaseSpins,

                        'case_completed' =>
                            $isFullRotation,

                        'case_valid' =>
                            $isFullRotation
                                ? $sequenceTotal === $caseTotalDiscount
                                : null,
                    ],

                    'remaining_spins' =>
                        (float) $userSpinWallet->balance,

                    'discount_balance' =>
                        (float) $userDiscountWallet->balance,

                    'total_spins_used' =>
                        (int) $subCampaign->total_spins_used,

                    'total_allowed_spins' =>
                        $totalAllowedSpins,
                ];
            });

            return $this->successResponse(
                $result,
                'Spin successful'
            );
        } catch (\Throwable $exception) {
            return $this->errorResponse(
                $exception->getMessage(),
                $this->safeCode($exception->getCode())
            );
        }
    }

    private function getActiveCampaign(): ?SpinCampaign
    {
        $now = now();

        return SpinCampaign::query()
            ->where(function ($query) {
                $query
                    ->where('status', 'active')
                    ->orWhere('status', 1)
                    ->orWhere('status', true);
            })
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->orderByDesc('priority')
            ->orderBy('id')
            ->lockForUpdate()
            ->first();
    }

    private function getActiveDiscountList(): array
    {
        return Discount::query()
            ->where(function ($query) {
                $query
                    ->where('status', 'active')
                    ->orWhere('status', 1)
                    ->orWhere('status', true);
            })
            ->orderBy('discount_percentage')
            ->pluck('discount_percentage')
            ->map(function ($value) {
                return (int) $value;
            })
            ->filter(function ($value) {
                return $value > 0;
            })
            ->unique()
            ->values()
            ->toArray();
    }

    private function findPartialSequence(
        int $maximumTotal,
        int $count,
        array $discountList
    ): ?array {
        if ($count === 0) {
            return [];
        }

        if ($maximumTotal <= 0) {
            return null;
        }

        $values = array_values(
            array_filter(
                array_map('intval', $discountList),
                fn ($value) => $value > 0
            )
        );

        shuffle($values);

        foreach ($values as $discount) {
            if ($discount > $maximumTotal) {
                continue;
            }

            $remainingSequence =
                $this->findPartialSequence(
                    $maximumTotal - $discount,
                    $count - 1,
                    $discountList
                );

            if ($remainingSequence !== null) {
                return array_merge(
                    [$discount],
                    $remainingSequence
                );
            }
        }

        return null;
    }

    private function generateRequestSequence(
        int $qty,
        int $spinsPerCase,
        int $targetDiscount,
        array $discountList
    ): array {
        if ($qty < 1 || $qty > $spinsPerCase) {
            throw new \Exception(
                "Quantity must be between 1 and {$spinsPerCase}.",
                422
            );
        }

        $fullSequence = $this->findExactSequence(
            $targetDiscount,
            $spinsPerCase,
            $discountList
        );

        if ($fullSequence === null) {
            throw new \Exception(
                "Cannot create {$spinsPerCase} spin results totaling exactly {$targetDiscount}% from the active Discount List.",
                400
            );
        }

        shuffle($fullSequence);

        return array_slice(
            $fullSequence,
            0,
            $qty
        );
    }

    private function findExactSequence(
        int $target,
        int $count,
        array $discountList
    ): ?array {
        if ($count === 0) {
            return $target === 0
                ? []
                : null;
        }

        if ($target <= 0) {
            return null;
        }

        $values = $discountList;

        shuffle($values);

        foreach ($values as $discount) {
            $discount = (int) $discount;

            if ($discount <= 0) {
                continue;
            }

            if ($discount > $target) {
                continue;
            }

            $remainingSequence =
                $this->findExactSequence(
                    $target - $discount,
                    $count - 1,
                    $discountList
                );

            if ($remainingSequence !== null) {
                return array_merge(
                    [$discount],
                    $remainingSequence
                );
            }
        }

        return null;
    }

    private function safeCode($code): int
    {
        $code = (int) $code;

        return $code >= 100 && $code <= 599
            ? $code
            : 400;
    }
}