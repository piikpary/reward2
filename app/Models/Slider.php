<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slider extends Model
{
    protected $fillable = [
        'title',
        'description',
        'link',
        'image',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
    public function images(): HasMany
    {
        return $this->hasMany(SliderImage::class)->orderBy('sort_order');
    }
}