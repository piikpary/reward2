<?php

namespace App\Http\Controllers\v2\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $transactions = WalletTransaction::query()
            ->with(['fromUser', 'toUser'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_type' => $transaction->transaction_type,
                    'wallet_type' => $transaction->wallet_type,
                    'amount' => (float) $transaction->amount,
                    'from' => $transaction->fromUser?->phone_number,
                    'to' => $transaction->toUser?->phone_number,
                    'special_reward_code' =>$transaction->special_reward_code,
                    'created_at' => $transaction->created_at?->format('Y-m-d H:i:s'),
                ];
            });

        return $this->successResponse([
            'total' => $transactions->count(),
            'transactions' => $transactions,
        ], '');
    }
}