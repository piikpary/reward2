<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpinSpecialCase extends Model
{
    protected $fillable = [
        'spin_campaign_id',
        'case_number',
        'total_discount',
        'status',
    ];

    public function spinCampaign(): BelongsTo
    {
        return $this->belongsTo(SpinCampaign::class);
    }
}