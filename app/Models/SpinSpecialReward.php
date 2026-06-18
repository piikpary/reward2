<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpinSpecialReward extends Model
{
    protected $fillable = [
        'spin_campaign_id',
        'scope_type',
        'scope_id',
        'assigned_sub_campaign_id',
        'spin_position',
        'special_discount',
        'status',
        'is_used',
        'used_by_user_id',
        'used_at',
        'reward_code',
        'is_redeemed',
        'redeemed_at',
        'discount_id',
        'discount_auto_created',
    ];

    protected $casts = [
        'special_discount' => 'decimal:2',
        'spin_position' => 'integer',
        'is_used' => 'boolean',
        'discount_auto_created' => 'boolean',
        'used_at' => 'datetime',
        'is_redeemed' => 'boolean',
        'redeemed_at' => 'datetime',
        
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(
            SpinCampaign::class,
            'spin_campaign_id'
        );
    }

    public function assignedSubCampaign(): BelongsTo
    {
        return $this->belongsTo(
            SpinSubCampaign::class,
            'assigned_sub_campaign_id'
        );
    }

    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'used_by_user_id'
        );
    }
    public function winner(): BelongsTo
{
    return $this->belongsTo(
        User::class,
        'used_by_user_id'
    );
}
public function discount(): BelongsTo
{
    return $this->belongsTo(
        Discount::class
    );
}
}