<?php

namespace App\Observers;

use App\Models\WalletTransaction;
use App\Support\TransactionSummaryCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class WalletTransactionObserver implements
    ShouldHandleEventsAfterCommit
{
    public function created(
        WalletTransaction $transaction
    ): void {
        $this->clearCache($transaction);
    }

    public function updated(
        WalletTransaction $transaction
    ): void {
        $this->clearCache($transaction);
    }

    public function deleted(
        WalletTransaction $transaction
    ): void {
        $this->clearCache($transaction);
    }

    private function clearCache(
        WalletTransaction $transaction
    ): void {
        TransactionSummaryCache::forget(
            $transaction->user_id,
            $transaction->from_user_id,
            $transaction->to_user_id
        );
    }
}