<?php

namespace App\Http\Controllers\v2\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\CampaignShareReward;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TransactionController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $cacheKey =
            "reward2:api:user:{$user->id}:transactions:v2";

        $transactions = Cache::remember(
            $cacheKey,
            now()->addSeconds(15),
            function () use ($user) {
                $walletTransactions =
                    WalletTransaction::query()
                        ->with([
                            'fromUser',
                            'toUser',
                        ])
                        ->where(
                            'user_id',
                            $user->id
                        )
                        ->latest()
                        ->get();

                $campaignRewards =
                    CampaignShareReward::query()
                        ->with('campaign')
                        ->whereIn(
                            'wallet_transaction_id',
                            $walletTransactions
                                ->pluck('id')
                                ->all()
                        )
                        ->get()
                        ->keyBy(
                            'wallet_transaction_id'
                        );

                return $walletTransactions
                    ->map(function (
                        WalletTransaction $transaction
                    ) use (
                        $campaignRewards
                    ): array {
                        $campaignReward =
                            $campaignRewards->get(
                                $transaction->id
                            );

                        return [
                            'id' =>
                                $transaction->id,

                            'transaction_type' =>
                                $transaction
                                    ->transaction_type,

                            'wallet_type' =>
                                $transaction
                                    ->wallet_type,

                            'amount' =>
                                (float) $transaction
                                    ->amount,

                            'from' =>
                                $transaction
                                    ->fromUser
                                    ?->phone_number,

                            'to' =>
                                $transaction
                                    ->toUser
                                    ?->phone_number,

                            'special_reward_code' =>
                                $transaction
                                    ->special_reward_code,

                            'description' =>
                                $transaction
                                    ->description,

                            'campaign_id' =>
                                $campaignReward
                                    ? (int) $campaignReward
                                        ->share_campaign_id
                                    : null,

                            'campaign_title' =>
                                $campaignReward
                                    ?->campaign
                                    ?->title,

                            'created_at' =>
                                $transaction
                                    ->created_at
                                    ?->format(
                                        'Y-m-d H:i:s'
                                    ),
                        ];
                    });
            }
        );

        return $this->successResponse([
            'total' =>
                $transactions->count(),

            'transactions' =>
                $transactions,
        ], '');
    }
}