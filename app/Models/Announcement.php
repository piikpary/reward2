<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'announcement_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'announcement_date' => 'datetime',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(AnnouncementImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePublished($query)
    {
        return $query->where(
            'announcement_date',
            '<=',
            now()
        );
    }
}