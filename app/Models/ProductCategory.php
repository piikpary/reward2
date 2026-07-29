<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function exchangePrizes(): HasMany
    {
        return $this->hasMany(
            ExchangePrize::class,
            'product_category_id'
        );
    }
}