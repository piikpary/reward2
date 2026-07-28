<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRegistrationReward extends Model
{
    protected $fillable = [
        'user_id',
        'registration_reward_setting_id',
        'wallet_transaction_id',
        'wallet_type',
        'amount',
        'awarded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'awarded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function setting(): BelongsTo
    {
        return $this->belongsTo(
            RegistrationRewardSetting::class,
            'registration_reward_setting_id'
        );
    }

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(
            WalletTransaction::class,
            'wallet_transaction_id'
        );
    }
}