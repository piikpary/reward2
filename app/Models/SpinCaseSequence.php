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
    ];

    protected $casts = [
        'sequence' => 'array',
    ];
}