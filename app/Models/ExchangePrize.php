<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ExchangePrize extends Model
{
    protected $fillable = [
        'product_category_id',
        'title',
        'image_path',
        'exchange_discount_amount',
    ];

    protected function casts(): array
    {
        return [
            'product_category_id' => 'integer',
            'exchange_discount_amount' => 'decimal:2',
        ];
    }

    /**
     * Category assigned to this exchange prize product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ProductCategory::class,
            'product_category_id'
        );
    }

    /**
     * Return the public product image URL.
     */
    public function imageUrl(): ?string
    {
        if (empty($this->image_path)) {
            return null;
        }

        if (
            str_starts_with($this->image_path, 'http://')
            || str_starts_with($this->image_path, 'https://')
        ) {
            return $this->image_path;
        }

        return Storage::disk('public')->url(
            $this->image_path
        );
    }
}