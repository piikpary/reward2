<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpinCaseSequence extends Model
{
    protected $fillable = [
        'spin_campaign_id',
        'case_number',
        'total_discount',
        'sequence',
        'used_spins',
        'spin_sub_campaign_id',
    ];

    protected $casts = [
        'sequence' => 'array',
    ];
    public function subCampaign()
{
    return $this->belongsTo(
        SpinSubCampaign::class,
        'spin_sub_campaign_id'
    );
}
}