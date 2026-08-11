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
        'unit',
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
     * Return the public original image URL.
     */
    public function imageUrl(): ?string
    {
        if (empty($this->image_path)) {
            return null;
        }

        if (
            str_starts_with(
                $this->image_path,
                'http://'
            )
            || str_starts_with(
                $this->image_path,
                'https://'
            )
        ) {
            return $this->image_path;
        }

        return Storage::disk('public')->url(
            ltrim($this->image_path, '/')
        );
    }

    /**
     * Return the smaller thumbnail image URL.
     *
     * When a thumbnail does not exist, the original
     * image is returned so old products still work.
     */
    public function thumbnailUrl(): ?string
    {
        if (empty($this->image_path)) {
            return null;
        }

        if (
            str_starts_with(
                $this->image_path,
                'http://'
            )
            || str_starts_with(
                $this->image_path,
                'https://'
            )
        ) {
            return $this->image_path;
        }

        $imagePath = ltrim(
            $this->image_path,
            '/'
        );

        $directory = pathinfo(
            $imagePath,
            PATHINFO_DIRNAME
        );

        $filename = pathinfo(
            $imagePath,
            PATHINFO_FILENAME
        );

        $thumbnailPath = (
            $directory !== '.'
                ? $directory . '/'
                : ''
        ) . 'thumbnails/'
            . $filename
            . '.jpg';

        $disk = Storage::disk('public');

        if ($disk->exists($thumbnailPath)) {
            return $disk->url(
                $thumbnailPath
            );
        }

        return $this->imageUrl();
    }
}