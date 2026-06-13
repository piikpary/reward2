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
        | Mobile only sends:
        |
        | {
        |     "qty": 1
        | }
        |
        | Backend automatically selects the active main campaign and the
        | highest-priority active subcampaign with enough remaining quota.
        |
        */

        $validated = $request->validate([
            'qty' => [
                'required',
                'integer',
                'min:1',
                'max:100',
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
                | Find wallets
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
                | Selection order:
                |
                | 1. Active subcampaign only
                | 2. Highest priority first
                | 3. Must have enough remaining quota for the entire qty
                |
                | One request stays inside one subcampaign.
                |
                */

                $subCampaigns = SpinSubCampaign::query()
                    ->where('spin_campaign_id', $campaign->id)
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
                        $totalAllowedSpins =
                            (int) $item->total_cases
                            * (int) $item->spins_per_case;

                        $remainingQuota =
                            $totalAllowedSpins
                            - (int) $item->total_spins_used;

                        return $remainingQuota >= $qty;
                    }
                );

                if (!$subCampaign) {
                    throw new \Exception(
                        'No active subcampaign has enough remaining spin quota.',
                        400
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Validate selected subcampaign rule
                |--------------------------------------------------------------------------
                */

                if ((int) $subCampaign->total_cases < 1) {
                    throw new \Exception(
                        'Subcampaign total cases is not configured.',
                        400
                    );
                }

                if ((int) $subCampaign->spins_per_case < 1) {
                    throw new \Exception(
                        'Subcampaign spins per case is not configured.',
                        400
                    );
                }

                if (
                    (float) $subCampaign->normal_discount_total < 1
                ) {
                    throw new \Exception(
                        'Subcampaign normal discount total is not configured.',
                        400
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Get active discount list
                |--------------------------------------------------------------------------
                */

                $discountList = $this->getActiveDiscountList();

                if (empty($discountList)) {
                    throw new \Exception(
                        'Discount list is empty. Please add active discounts first.',
                        400
                    );
                }

                $results = [];
                $totalDiscountEarned = 0;

                /*
                |--------------------------------------------------------------------------
                | Process requested spin quantity
                |--------------------------------------------------------------------------
                */

                for (
                    $requestSpinIndex = 1;
                    $requestSpinIndex <= $qty;
                    $requestSpinIndex++
                ) {
                    $totalAllowedSpins =
                        (int) $subCampaign->total_cases
                        * (int) $subCampaign->spins_per_case;

                    if (
                        (int) $subCampaign->total_spins_used
                        >= $totalAllowedSpins
                    ) {
                        throw new \Exception(
                            'Subcampaign spin quota is finished.',
                            400
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Calculate case number and spin number
                    |--------------------------------------------------------------------------
                    */

                    $nextGlobalSpinNumber =
                        (int) $subCampaign->total_spins_used + 1;

                    $caseNumber = (int) ceil(
                        $nextGlobalSpinNumber
                        / (int) $subCampaign->spins_per_case
                    );

                    $spinNumberInCase =
                        (($nextGlobalSpinNumber - 1)
                        % (int) $subCampaign->spins_per_case)
                        + 1;

                    /*
                    |--------------------------------------------------------------------------
                    | Find special case
                    |--------------------------------------------------------------------------
                    |
                    | Special case belongs to the selected subcampaign.
                    |
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
                        : (int) $subCampaign->normal_discount_total;

                    /*
                    |--------------------------------------------------------------------------
                    | Get or create case sequence
                    |--------------------------------------------------------------------------
                    */

                    $caseSequence = SpinCaseSequence::query()
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
                        ->lockForUpdate()
                        ->first();

                    if (!$caseSequence) {
                        $caseSequence = SpinCaseSequence::create([
                            'spin_campaign_id' =>
                                $campaign->id,

                            'spin_sub_campaign_id' =>
                                $subCampaign->id,

                            'case_number' =>
                                $caseNumber,

                            'total_discount' =>
                                $caseTotalDiscount,

                            'sequence' =>
                                [],

                            'used_spins' =>
                                0,
                        ]);
                    }

                    $currentSequence = $caseSequence->sequence ?? [];

                    if (is_string($currentSequence)) {
                        $decodedSequence = json_decode(
                            $currentSequence,
                            true
                        );

                        $currentSequence = is_array($decodedSequence)
                            ? $decodedSequence
                            : [];
                    }

                    if (!is_array($currentSequence)) {
                        $currentSequence = [];
                    }

                    /*
                     * The saved sequence must match current case progress.
                     */
                    if (
                        count($currentSequence)
                        !== ($spinNumberInCase - 1)
                    ) {
                        throw new \Exception(
                            'Spin sequence does not match current case progress.',
                            400
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Generate controlled random discount
                    |--------------------------------------------------------------------------
                    |
                    | The result:
                    |
                    | - Comes from Discount List
                    | - Can repeat
                    | - Must allow the case to finish with the exact target
                    |
                    */

                    $discountPercentage =
                        $this->generateDiscountFromDiscountList(
                            $caseTotalDiscount,
                            (int) $subCampaign->spins_per_case,
                            $currentSequence,
                            $discountList
                        );

                    $currentSequence[] = $discountPercentage;

                    $sequenceTotal = array_sum($currentSequence);

                    $caseCompleted =
                        count($currentSequence)
                        === (int) $subCampaign->spins_per_case;

                    if (
                        $caseCompleted
                        && $sequenceTotal !== $caseTotalDiscount
                    ) {
                        throw new \Exception(
                            'Completed case discount total is invalid.',
                            400
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Deduct one user spin
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
                        + $discountPercentage;

                    $userDiscountWallet->save();

                    /*
                    |--------------------------------------------------------------------------
                    | Update subcampaign progress
                    |--------------------------------------------------------------------------
                    */

                    $subCampaign->total_spins_used =
                        $nextGlobalSpinNumber;

                    $subCampaign->save();

                    /*
                    |--------------------------------------------------------------------------
                    | Synchronize old parent counter temporarily
                    |--------------------------------------------------------------------------
                    |
                    | This is temporary while total_spins_used still exists
                    | in the spin_campaigns table.
                    |
                    */

                    $campaign->total_spins_used =
                        (int) $campaign->subCampaigns()
                            ->sum('total_spins_used');

                    $campaign->save();

                    /*
                    |--------------------------------------------------------------------------
                    | Save case sequence
                    |--------------------------------------------------------------------------
                    */

                    $caseSequence->sequence =
                        $currentSequence;

                    $caseSequence->used_spins =
                        $spinNumberInCase;

                    $caseSequence->total_discount =
                        $caseTotalDiscount;

                    $caseSequence->save();

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
                            $spinNumberInCase,

                        'discount_percentage' =>
                            $discountPercentage,

                        'case_total_discount' =>
                            $caseTotalDiscount,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Record spin wallet transaction
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
                            "Spin used: campaign {$campaign->id}, subcampaign {$subCampaign->id}, case {$caseNumber}, spin {$spinNumberInCase}",
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Record discount wallet transaction
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
                            $discountPercentage,

                        'from_user_id' =>
                            null,

                        'to_user_id' =>
                            $user->id,

                        'description' =>
                            "Discount {$discountPercentage}% earned: campaign {$campaign->id}, subcampaign {$subCampaign->id}, case {$caseNumber}, spin {$spinNumberInCase}",
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Build this spin result
                    |--------------------------------------------------------------------------
                    */

                    $remainingCaseDiscount = max(
                        0,
                        $caseTotalDiscount - $sequenceTotal
                    );

                    $remainingCaseSpins = max(
                        0,
                        (int) $subCampaign->spins_per_case
                        - count($currentSequence)
                    );

                    $totalDiscountEarned += $discountPercentage;

                    $results[] = [
                        'request_spin_index' =>
                            $requestSpinIndex,

                        'campaign_id' =>
                            $campaign->id,

                        'campaign_name' =>
                            $campaign->name,

                        'sub_campaign_id' =>
                            $subCampaign->id,

                        'sub_campaign_name' =>
                            $subCampaign->name,

                        'case_number' =>
                            $caseNumber,

                        'spin_number' =>
                            $spinNumberInCase,

                        'spins_per_case' =>
                            (int) $subCampaign->spins_per_case,

                        'discount_percentage' =>
                            $discountPercentage,

                        'case_total_discount' =>
                            $caseTotalDiscount,

                        'is_special_case' =>
                            $specialCase !== null,

                        'sequence' =>
                            $currentSequence,

                        'sequence_total' =>
                            $sequenceTotal,

                        'remaining_case_discount' =>
                            $remainingCaseDiscount,

                        'remaining_case_spins' =>
                            $remainingCaseSpins,

                        'case_completed' =>
                            $caseCompleted,

                        'case_valid' =>
                            $caseCompleted
                                ? $sequenceTotal === $caseTotalDiscount
                                : null,

                        'total_spins_used' =>
                            $nextGlobalSpinNumber,

                        'total_allowed_spins' =>
                            $totalAllowedSpins,
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Prepare simple mobile response
                |--------------------------------------------------------------------------
                |
                | spins count always equals qty.
                |
                | current_case.sequence belongs to current case progress,
                | so it does not need to equal qty.
                |
                */

                $frontendSpins = collect($results)
                    ->map(function (array $item) {
                        return [
                            'spin_no' =>
                                $item['request_spin_index'],

                            'discount_percentage' =>
                                $item['discount_percentage'],

                            'case_number' =>
                                $item['case_number'],

                            'spin_number' =>
                                $item['spin_number'],

                            'is_special_case' =>
                                $item['is_special_case'],
                        ];
                    })
                    ->values();

                $lastResult = collect($results)->last();

                return [
                    'qty' =>
                        $qty,

                    'total_discount_earned' =>
                        $totalDiscountEarned,

                    /*
                     * Backend automatically selected this rule.
                     * Mobile does not need to send it.
                     */
                    'selected_rule' => [
                        'id' =>
                            $lastResult['sub_campaign_id'],

                        'name' =>
                            $lastResult['sub_campaign_name'],
                    ],

                    /*
                     * Number of items here always equals qty.
                     */
                    'spins' =>
                        $frontendSpins,

                    /*
                     * Current progress of the last case touched by this request.
                     */
                    'current_case' => [
                        'case_number' =>
                            $lastResult['case_number'],

                        'spin_number' =>
                            $lastResult['spin_number'],

                        'spins_per_case' =>
                            $lastResult['spins_per_case'],

                        'case_total_discount' =>
                            $lastResult['case_total_discount'],

                        'is_special_case' =>
                            $lastResult['is_special_case'],

                        'sequence' =>
                            $lastResult['sequence'],

                        'sequence_total' =>
                            $lastResult['sequence_total'],

                        'remaining_case_discount' =>
                            $lastResult[
                                'remaining_case_discount'
                            ],

                        'remaining_case_spins' =>
                            $lastResult[
                                'remaining_case_spins'
                            ],

                        'case_completed' =>
                            $lastResult['case_completed'],

                        'case_valid' =>
                            $lastResult['case_valid'],
                    ],

                    'remaining_spins' =>
                        (float) $userSpinWallet->balance,

                    'discount_balance' =>
                        (float) $userDiscountWallet->balance,

                    'total_spins_used' =>
                        $lastResult['total_spins_used'],

                    'total_allowed_spins' =>
                        $lastResult['total_allowed_spins'],
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
            ->where(
                'start_date',
                '<=',
                $now
            )
            ->where(
                'end_date',
                '>=',
                $now
            )
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
    | Generate controlled random discount
    |--------------------------------------------------------------------------
    */

    private function generateDiscountFromDiscountList(
        int $caseTotalDiscount,
        int $spinsPerCase,
        array $currentSequence,
        array $discountList
    ): int {
        $usedTotal = array_sum($currentSequence);
        $usedSpins = count($currentSequence);

        $remainingTotal =
            $caseTotalDiscount - $usedTotal;

        $remainingSpins =
            $spinsPerCase - $usedSpins;

        if ($remainingTotal <= 0) {
            throw new \Exception(
                'No remaining discount is available for this case.',
                400
            );
        }

        if ($remainingSpins <= 0) {
            throw new \Exception(
                'No remaining spins are available for this case.',
                400
            );
        }

        /*
         * The last spin must exactly complete the target.
         */
        if ($remainingSpins === 1) {
            if (
                !in_array(
                    $remainingTotal,
                    $discountList,
                    true
                )
            ) {
                throw new \Exception(
                    "Cannot complete case. Remaining discount {$remainingTotal}% is not available in Discount List.",
                    400
                );
            }

            return $remainingTotal;
        }

        $validDiscounts = [];

        foreach ($discountList as $discount) {
            $discount = (int) $discount;

            if ($discount <= 0) {
                continue;
            }

            if ($discount >= $remainingTotal) {
                continue;
            }

            $nextRemainingTotal =
                $remainingTotal - $discount;

            $nextRemainingSpins =
                $remainingSpins - 1;

            if (
                $this->canCompleteRemainingTotal(
                    $nextRemainingTotal,
                    $nextRemainingSpins,
                    $discountList
                )
            ) {
                $validDiscounts[] = $discount;
            }
        }

        if (empty($validDiscounts)) {
            throw new \Exception(
                "Cannot generate a valid {$spinsPerCase}-spin sequence totaling {$caseTotalDiscount}% from the active Discount List.",
                400
            );
        }

        return $validDiscounts[
            array_rand($validDiscounts)
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Check if remaining target can be completed
    |--------------------------------------------------------------------------
    |
    | Repeated Discount List values are allowed.
    |
    */

    private function canCompleteRemainingTotal(
        int $targetTotal,
        int $spinsLeft,
        array $discountList
    ): bool {
        if ($spinsLeft === 0) {
            return $targetTotal === 0;
        }

        if ($targetTotal <= 0) {
            return false;
        }

        foreach ($discountList as $discount) {
            $discount = (int) $discount;

            if ($discount <= 0) {
                continue;
            }

            if ($discount > $targetTotal) {
                continue;
            }

            if (
                $this->canCompleteRemainingTotal(
                    $targetTotal - $discount,
                    $spinsLeft - 1,
                    $discountList
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Safe HTTP error code
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