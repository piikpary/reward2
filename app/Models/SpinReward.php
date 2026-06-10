<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpinReward extends Model
{
    protected $fillable = [
        'discount_percentage',
        'chance_weight',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'discount_percentage' => 'integer',
        'chance_weight' => 'integer',
        'sort_order' => 'integer',
    ];
}