<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignShareReward extends Model
{
    protected $fillable = [
        'share_campaign_id',
        'user_id',
        'milestone_number',
        'milestone_threshold',
        'reward_spins',
        'wallet_transaction_id',
        'granted_by',
        'awarded_at',
    ];

    protected function casts(): array
    {
        return [
            'milestone_number' => 'integer',
            'milestone_threshold' => 'integer',
            'reward_spins' => 'integer',
            'awarded_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(
            ShareCampaign::class,
            'share_campaign_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(
            WalletTransaction::class,
            'wallet_transaction_id'
        );
    }
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'granted_by'
        );
    }
}