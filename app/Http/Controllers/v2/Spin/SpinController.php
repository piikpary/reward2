<?php

namespace App\Http\Controllers\v2\Spin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Discount;
use App\Models\SpinCampaign;
use App\Models\SpinCaseSequence;
use App\Models\SpinResult;
use App\Models\SpinSpecialCase;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpinController extends Controller
{
    use ApiResponse;

    public function getDiscount(Request $request, WalletService $walletService): JsonResponse
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $user = $request->user();
        $qty = (int) $validated['qty'];

        $walletService->ensureUserWallets($user);

        try {
            $result = DB::transaction(function () use ($user, $qty) {
                $spinWallet = Wallet::where('type', 'spin')->firstOrFail();
                $discountWallet = Wallet::where('type', 'discount')->firstOrFail();

                $userSpinWallet = UserWallet::where('user_id', $user->id)
                    ->where('wallet_id', $spinWallet->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $userDiscountWallet = UserWallet::where('user_id', $user->id)
                    ->where('wallet_id', $discountWallet->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((float) $userSpinWallet->balance < $qty) {
                    throw new \Exception('Insufficient spin balance', 400);
                }

                $campaign = $this->getActiveCampaign();

                if (!$campaign) {
                    throw new \Exception('No active spin campaign.', 404);
                }

                if ((int) $campaign->total_cases <= 0) {
                    throw new \Exception('Spin campaign total cases is not configured.', 400);
                }

                if ((int) $campaign->spins_per_case <= 0) {
                    throw new \Exception('Spin campaign spins per case is not configured.', 400);
                }

                $results = [];
                $totalDiscountEarned = 0;

                for ($i = 1; $i <= $qty; $i++) {
                    $totalAllowedSpins = (int) $campaign->total_cases * (int) $campaign->spins_per_case;

                    if ((int) $campaign->total_spins_used >= $totalAllowedSpins) {
                        throw new \Exception('Spin campaign is already finished.', 400);
                    }

                    $nextGlobalSpinNumber = (int) $campaign->total_spins_used + 1;
                    $caseNumber = (int) ceil($nextGlobalSpinNumber / (int) $campaign->spins_per_case);
                    $spinNumberInCase = (($nextGlobalSpinNumber - 1) % (int) $campaign->spins_per_case) + 1;

                    $specialCase = SpinSpecialCase::query()
                        ->where('spin_campaign_id', $campaign->id)
                        ->where('case_number', $caseNumber)
                        ->where(function ($query) {
                            $query->where('status', 'active')
                                ->orWhere('status', 1)
                                ->orWhere('status', true);
                        })
                        ->first();

                    $caseTotalDiscount = $specialCase
                        ? (int) $specialCase->total_discount
                        : (int) $campaign->normal_discount_total;

                    $caseSequence = SpinCaseSequence::query()
                        ->where('spin_campaign_id', $campaign->id)
                        ->where('case_number', $caseNumber)
                        ->lockForUpdate()
                        ->first();

                    if (!$caseSequence) {
                        $caseSequence = SpinCaseSequence::create([
                            'spin_campaign_id' => $campaign->id,
                            'case_number' => $caseNumber,
                            'total_discount' => $caseTotalDiscount,
                            'sequence' => [],
                            'used_spins' => 0,
                        ]);
                    }

                    $currentSequence = $caseSequence->sequence ?? [];

                    if (count($currentSequence) !== ($spinNumberInCase - 1)) {
                        throw new \Exception('Spin sequence is not matching current case progress.', 400);
                    }

                    $discountPercentage = $this->generateDiscountFromDiscountList(
                        $caseTotalDiscount,
                        (int) $campaign->spins_per_case,
                        $currentSequence
                    );

                    $currentSequence[] = $discountPercentage;

                    $userSpinWallet->balance = (float) $userSpinWallet->balance - 1;
                    $userSpinWallet->save();

                    $userDiscountWallet->balance = (float) $userDiscountWallet->balance + $discountPercentage;
                    $userDiscountWallet->save();

                    $campaign->total_spins_used = $nextGlobalSpinNumber;
                    $campaign->save();

                    $caseSequence->sequence = $currentSequence;
                    $caseSequence->used_spins = $spinNumberInCase;
                    $caseSequence->save();

                    SpinResult::create([
                        'spin_campaign_id' => $campaign->id,
                        'user_id' => $user->id,
                        'case_number' => $caseNumber,
                        'spin_number' => $spinNumberInCase,
                        'discount_percentage' => $discountPercentage,
                        'case_total_discount' => $caseTotalDiscount,
                    ]);

                    WalletTransaction::create([
                        'user_id' => $user->id,
                        'wallet_id' => $spinWallet->id,
                        'transaction_type' => 'spin_used',
                        'wallet_type' => 'spin',
                        'amount' => 1,
                        'from_user_id' => $user->id,
                        'to_user_id' => null,
                        'description' => "Spin used for campaign {$campaign->id}, case {$caseNumber}, spin {$spinNumberInCase}",
                    ]);

                    WalletTransaction::create([
                        'user_id' => $user->id,
                        'wallet_id' => $discountWallet->id,
                        'transaction_type' => 'discount_earned',
                        'wallet_type' => 'discount',
                        'amount' => $discountPercentage,
                        'from_user_id' => null,
                        'to_user_id' => $user->id,
                        'description' => "Discount {$discountPercentage}% earned from campaign {$campaign->id}, case {$caseNumber}, spin {$spinNumberInCase}",
                    ]);

                    $sequenceTotal = array_sum($currentSequence);
                    $caseCompleted = count($currentSequence) === (int) $campaign->spins_per_case;

                    $totalDiscountEarned += $discountPercentage;

                    $results[] = [
                        'request_spin_index' => $i,
                        'request_qty' => $qty,

                        'campaign_id' => $campaign->id,
                        'campaign_name' => $campaign->name,

                        'case_number' => $caseNumber,
                        'spin_number' => $spinNumberInCase,
                        'spins_per_case' => (int) $campaign->spins_per_case,

                        'discount_percentage' => $discountPercentage,
                        'case_total_discount' => $caseTotalDiscount,
                        'is_special_case' => (bool) $specialCase,

                        'sequence' => $currentSequence,
                        'case_sequence_count' => count($currentSequence),
                        'sequence_total' => $sequenceTotal,
                        'remaining_case_discount' => $caseTotalDiscount - $sequenceTotal,
                        'remaining_case_spins' => (int) $campaign->spins_per_case - count($currentSequence),

                        'case_completed' => $caseCompleted,
                        'case_valid' => $caseCompleted ? ($sequenceTotal === $caseTotalDiscount) : null,

                        'total_spins_used' => $nextGlobalSpinNumber,
                        'total_allowed_spins' => $totalAllowedSpins,
                    ];
                }

                $lastResult = collect($results)->last();

                return [
                    'campaign_id' => $lastResult['campaign_id'],
                    'case_number' => $lastResult['case_number'],
                    'spin_number' => $lastResult['spin_number'],
                    'spins_per_case' => $lastResult['spins_per_case'],

                    'discount_percentage' => $lastResult['discount_percentage'],
                    'case_total_discount' => $lastResult['case_total_discount'],
                    'is_special_case' => $lastResult['is_special_case'],

                    'sequence' => $lastResult['sequence'],
                    'sequence_total' => $lastResult['sequence_total'],
                    'remaining_case_discount' => $lastResult['remaining_case_discount'],
                    'remaining_case_spins' => $lastResult['remaining_case_spins'],

                    'remaining_spins' => (float) $userSpinWallet->balance,
                    'discount_balance' => (float) $userDiscountWallet->balance,

                    'total_spins_used' => $lastResult['total_spins_used'],
                    'total_allowed_spins' => $lastResult['total_allowed_spins'],
                ];
            });

            return $this->successResponse($result, 'Spin successful');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $this->safeCode($e->getCode()));
        }
    }

    private function getActiveCampaign(): ?SpinCampaign
    {
        $today = Carbon::today();

        return SpinCampaign::query()
            ->where(function ($query) {
                $query->where('status', 'active')
                    ->orWhere('status', 1)
                    ->orWhere('status', true);
            })
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->orderByDesc('priority')
            ->lockForUpdate()
            ->first();
    }

    private function generateDiscountFromDiscountList(
        int $caseTotalDiscount,
        int $spinsPerCase,
        array $currentSequence
    ): int {
        $discountList = $this->getActiveDiscountList();

        if (empty($discountList)) {
            throw new \Exception('Discount list is empty. Please add active discounts first.', 400);
        }

        $usedTotal = array_sum($currentSequence);
        $usedSpins = count($currentSequence);

        $remainingTotal = $caseTotalDiscount - $usedTotal;
        $remainingSpins = $spinsPerCase - $usedSpins;

        if ($remainingTotal <= 0 || $remainingSpins <= 0) {
            throw new \Exception('Invalid case discount progress.', 400);
        }

        if ($remainingSpins === 1) {
            if (!in_array($remainingTotal, $discountList, true)) {
                throw new \Exception("Cannot complete case. Remaining discount {$remainingTotal}% is not in discount list.", 400);
            }

            return $remainingTotal;
        }

        $validDiscounts = [];

        foreach ($discountList as $discount) {
            if ($discount <= 0 || $discount >= $remainingTotal) {
                continue;
            }

            $nextRemainingTotal = $remainingTotal - $discount;
            $nextRemainingSpins = $remainingSpins - 1;

            if ($this->canCompleteRemainingTotal($nextRemainingTotal, $nextRemainingSpins, $discountList)) {
                $validDiscounts[] = $discount;
            }
        }

        if (empty($validDiscounts)) {
            throw new \Exception("Cannot generate valid discount from discount list for total {$caseTotalDiscount}% with {$spinsPerCase} spins.", 400);
        }

        return $validDiscounts[array_rand($validDiscounts)];
    }

    private function getActiveDiscountList(): array
    {
        return Discount::query()
            ->where(function ($query) {
                $query->where('status', 'active')
                    ->orWhere('status', 1)
                    ->orWhere('status', true);
            })
            ->orderBy('discount_percentage')
            ->pluck('discount_percentage')
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->unique()
            ->values()
            ->toArray();
    }

    private function canCompleteRemainingTotal(int $targetTotal, int $spinsLeft, array $discountList): bool
    {
        if ($spinsLeft === 0) {
            return $targetTotal === 0;
        }

        if ($targetTotal <= 0) {
            return false;
        }

        foreach ($discountList as $discount) {
            if ($discount <= 0 || $discount > $targetTotal) {
                continue;
            }

            if ($this->canCompleteRemainingTotal($targetTotal - $discount, $spinsLeft - 1, $discountList)) {
                return true;
            }
        }

        return false;
    }

    private function safeCode($code): int
    {
        $code = (int) $code;

        return $code >= 100 && $code <= 599 ? $code : 400;
    }
}