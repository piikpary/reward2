<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShareCampaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'title',
        'description',
        'image_path',
        'share_url',
        'required_shares',
        'reward_spins',
        'reward_repeatable',
        'is_active',
        'is_published',
        'starts_at',
        'expires_at',
        'published_at',
        'total_shares',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'required_shares' => 'integer',
            'reward_spins' => 'integer',
            'reward_repeatable' => 'boolean',
            'is_active' => 'boolean',
            'is_published' => 'boolean',
            'total_shares' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function shares(): HasMany
    {
        return $this->hasMany(
            CampaignShare::class,
            'share_campaign_id'
        );
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(
            CampaignUserProgress::class,
            'share_campaign_id'
        );
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(
            CampaignShareReward::class,
            'share_campaign_id'
        );
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('is_published', true)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function isAvailable(): bool
    {
        if (!$this->is_active || !$this->is_published) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function imageUrl(): ?string
{
    if (!$this->image_path) {
        return null;
    }

    if (
        Str::startsWith(
            $this->image_path,
            ['http://', 'https://']
        )
    ) {
        return $this->image_path;
    }

    $storageUrl = Storage::disk('public')
        ->url($this->image_path);

    if (
        Str::startsWith(
            $storageUrl,
            ['http://', 'https://']
        )
    ) {
        return $storageUrl;
    }

    return url($storageUrl);
}

   public function campaignShareUrl(): string
    {
        return route('share-campaigns.public.show', [
            'shareCampaign' => $this->id,
        ]);
    }
}