<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AnnouncementImage extends Model
{
    protected $fillable = [
        'announcement_id',
        'image',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'image_url',
    ];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        return url(
            Storage::disk('public')->url($this->image)
        );
    }
}