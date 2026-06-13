<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpinCampaign extends Model
{
    protected $fillable = [
        'name',
        'rule_type',
        'description',
        'start_date',
        'end_date',
        'priority',
        'status',
        'total_cases',
        'spins_per_case',
        'normal_discount_total',
        'total_spins_used',
        'max_spin_qty',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'total_cases' => 'integer',
        'spins_per_case' => 'integer',
        'normal_discount_total' => 'integer',
        'total_spins_used' => 'integer',
        'max_spin_qty' => 'integer',
    ];

    public function specialCases(): HasMany
    {
        return $this->hasMany(SpinSpecialCase::class);
    }

    public function getTotalAllowedSpinsAttribute(): int
    {
        return (int) $this->total_cases * (int) $this->spins_per_case;
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->total_allowed_spins <= 0) {
            return 0;
        }

        return round(((int) $this->total_spins_used / $this->total_allowed_spins) * 100, 2);
    }
    public function subCampaigns()
{
    return $this->hasMany(
        SpinSubCampaign::class,
        'spin_campaign_id'
    )->orderByDesc('priority');
}
}