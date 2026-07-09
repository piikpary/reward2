<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpinResult extends Model
{
    protected $fillable = [
        'spin_campaign_id',
        'spin_sub_campaign_id',
        'user_id',
        'case_number',
        'spin_number',
        'discount_percentage',
        'case_total_discount',
    ];

    public function campaign()
    {
        return $this->belongsTo(SpinCampaign::class, 'spin_campaign_id');
    }

    public function subCampaign()
    {
        return $this->belongsTo(SpinSubCampaign::class, 'spin_sub_campaign_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}