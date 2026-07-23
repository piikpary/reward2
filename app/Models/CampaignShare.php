<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignShare extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'share_campaign_id',
        'user_id',
        'facebook_post_url',
        'facebook_post_url_hash',
        'status',
        'verification_method',
        'shared_at',
        'verified_at',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'shared_at' => 'datetime',
            'verified_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(
            ShareCampaign::class,
            'share_campaign_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_PENDING
        );
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_VERIFIED
        );
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_REJECTED
        );
    }
}