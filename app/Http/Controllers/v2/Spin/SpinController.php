<?php

namespace App\Http\Controllers\V2\Spin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Discount;
use App\Models\SpinCampaign;
use App\Models\SpinCaseSequence;
use App\Models\SpinResult;
use App\Models\SpinSpecialCase;
use App\Models\SpinSubCampaign;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpinController extends Controller
{
    use ApiResponse;

    public function getDiscount(
        Request $request,
        WalletService $walletService
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Mobile request
        |--------------------------------------------------------------------------
        |
        | Mobile sends:
        |
        | {
        |     "qty": 1
        | }
        |
        | The maximum qty is dynamic and comes from the selected
        | subcampaign's spins_per_case value.
        |
        */

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
                $qty
            ) {
                /*
                |--------------------------------------------------------------------------
                | Get wallet definitions
                |--------------------------------------------------------------------------
                */

                $spinWallet = Wallet::query()
                    ->where('type', 'spin')
                    ->firstOrFail();

                $discountWallet = Wallet::query()
                    ->where('type', 'discount')
                    ->firstOrFail();

                /*
                |--------------------------------------------------------------------------
                | Lock user wallet balances
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Find active main campaign
                |--------------------------------------------------------------------------
                */

                $campaign = $this->getActiveCampaign();

                if (!$campaign) {
                    throw new \Exception(
                        'No active spin campaign found.',
                        404
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Automatically select subcampaign
                |--------------------------------------------------------------------------
                |
                | Selection:
                |
                | 1. Active subcampaign
                | 2. Highest priority first
                | 3. qty cannot exceed spins_per_case
                | 4. Must have enough remaining quota
                |
                */

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

                        /*
                         * Dynamic request limit.
                         */
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

                /*
                |--------------------------------------------------------------------------
                | Validate selected rule
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Get active Discount List
                |--------------------------------------------------------------------------
                */

                $discountList =
                    $this->getActiveDiscountList();

                if (empty($discountList)) {
                    throw new \Exception(
                        'Discount list is empty. Please add active discounts first.',
                        400
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Create a new independent rotation batch
                |--------------------------------------------------------------------------
                |
                | Each API request creates a new batch.
                | It does not continue an older partial batch.
                |
                */

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

                /*
                |--------------------------------------------------------------------------
                | Find special-case target
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Generate request sequence
                |--------------------------------------------------------------------------
                |
                | qty == spins_per_case:
                | exact total is required.
                |
                | qty < spins_per_case:
                | random total must not exceed the configured target.
                |
                */

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

                /*
                |--------------------------------------------------------------------------
                | Save request case sequence
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Process generated spin results
                |--------------------------------------------------------------------------
                */

                foreach (
                    $requestSequence as $index => $discountPercentage
                ) {
                    $spinNumber = $index + 1;

                    /*
                    |--------------------------------------------------------------------------
                    | Deduct one spin
                    |--------------------------------------------------------------------------
                    */

                    $userSpinWallet->balance =
                        (float) $userSpinWallet->balance - 1;

                    $userSpinWallet->save();

                    /*
                    |--------------------------------------------------------------------------
                    | Add earned discount
                    |--------------------------------------------------------------------------
                    */

                    $userDiscountWallet->balance =
                        (float) $userDiscountWallet->balance
                        + (int) $discountPercentage;

                    $userDiscountWallet->save();

                    /*
                    |--------------------------------------------------------------------------
                    | Save spin result
                    |--------------------------------------------------------------------------
                    */

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
                            (int) $discountPercentage,

                        'case_total_discount' =>
                            $caseTotalDiscount,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Spin wallet transaction
                    |--------------------------------------------------------------------------
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
                            1,

                        'from_user_id' =>
                            $user->id,

                        'to_user_id' =>
                            null,

                        'description' =>
                            "Spin used: campaign {$campaign->id}, subcampaign {$subCampaign->id}, case {$caseNumber}, spin {$spinNumber}",
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Discount wallet transaction
                    |--------------------------------------------------------------------------
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
                            (int) $discountPercentage,

                        'from_user_id' =>
                            null,

                        'to_user_id' =>
                            $user->id,

                        'description' =>
                            "Discount {$discountPercentage}% earned: campaign {$campaign->id}, subcampaign {$subCampaign->id}, case {$caseNumber}, spin {$spinNumber}",
                    ]);

                    $totalDiscountEarned +=
                        (int) $discountPercentage;

                    $results[] = [
                        'spin_no' =>
                            $spinNumber,

                        'discount_percentage' =>
                            (int) $discountPercentage,

                        'case_number' =>
                            $caseNumber,

                        'spin_number' =>
                            $spinNumber,

                        'is_special_case' =>
                            $specialCase !== null,
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Update spin quota progress
                |--------------------------------------------------------------------------
                */

                $subCampaign->total_spins_used =
                    (int) $subCampaign->total_spins_used
                    + $qty;

                $subCampaign->save();

                /*
                 * Synchronize old parent counter temporarily.
                 */
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

    /*
    |--------------------------------------------------------------------------
    | Find active main campaign
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Get active Discount List
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Generate one request sequence
    |--------------------------------------------------------------------------
    */
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

        $remainingSequence = $this->findPartialSequence(
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

    /*
     * Build one valid complete sequence dynamically.
     *
     * $spinsPerCase and $targetDiscount come from
     * the selected subcampaign or special case.
     */
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
    /*
    |--------------------------------------------------------------------------
    | Find exact sequence
    |--------------------------------------------------------------------------
    |
    | Repeated discount values are allowed.
    |
    */

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

    /*
    |--------------------------------------------------------------------------
    | Safe HTTP status code
    |--------------------------------------------------------------------------
    */

    private function safeCode($code): int
    {
        $code = (int) $code;

        return $code >= 100 && $code <= 599
            ? $code
            : 400;
    }
}