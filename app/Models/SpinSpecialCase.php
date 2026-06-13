<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpinSpecialCase extends Model
{
    protected $fillable = [
        'spin_campaign_id',
        'case_number',
        'spin_sub_campaign_id',
        'total_discount',
        'status',
    ];

    public function spinCampaign(): BelongsTo
    {
        return $this->belongsTo(SpinCampaign::class);
    }
    public function subCampaign()
{
    return $this->belongsTo(
        SpinSubCampaign::class,
        'spin_sub_campaign_id'
    );
}
}