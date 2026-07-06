<?php

namespace App\Jobs;

use App\Models\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessWalletTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public int $walletTransactionId
    ) {
    }

    public function handle(): void
    {
        $transaction = WalletTransaction::query()
            ->find($this->walletTransactionId);

        if (!$transaction) {
            Log::warning('Wallet transaction job skipped: transaction not found', [
                'wallet_transaction_id' => $this->walletTransactionId,
            ]);

            return;
        }
        Log::info('Wallet transaction queue job processed', [
            'wallet_transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'wallet_id' => $transaction->wallet_id,
            'wallet_type' => $transaction->wallet_type,
            'transaction_type' => $transaction->transaction_type,
            'amount' => $transaction->amount,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Wallet transaction queue job failed', [
            'wallet_transaction_id' => $this->walletTransactionId,
            'error' => $exception->getMessage(),
        ]);
    }
}