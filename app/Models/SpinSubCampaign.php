<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpinSubCampaign extends Model
{
    protected $fillable = [
        'spin_campaign_id',
        'name',
        'total_cases',
        'spins_per_case',
        'normal_discount_total',
        'total_spins_used',
        'priority',
        'status',
        'description',
    ];

    protected $casts = [
        'total_cases' => 'integer',
        'spins_per_case' => 'integer',
        'normal_discount_total' => 'decimal:2',
        'total_spins_used' => 'integer',
        'priority' => 'integer',
    ];

    public function campaign()
    {
        return $this->belongsTo(
            SpinCampaign::class,
            'spin_campaign_id'
        );
    }

    public function specialCases()
    {
        return $this->hasMany(
            SpinSpecialCase::class,
            'spin_sub_campaign_id'
        );
    }

    public function caseSequences()
    {
        return $this->hasMany(
            SpinCaseSequence::class,
            'spin_sub_campaign_id'
        );
    }

    public function spinResults()
    {
        return $this->hasMany(
            SpinResult::class,
            'spin_sub_campaign_id'
        );
    }

    public function getTotalAllowedSpinsAttribute(): int
    {
        return $this->total_cases * $this->spins_per_case;
    }

    public function getRemainingSpinsAttribute(): int
    {
        return max(
            0,
            $this->total_allowed_spins - $this->total_spins_used
        );
    }
}