<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignShare extends Model
{
    protected $fillable = [
        'share_campaign_id',
        'user_id',
        'facebook_post_url',
        'facebook_post_url_hash',
        'status',
        'verification_method',
        'shared_at',
        'verified_at',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'shared_at' => 'datetime',
            'verified_at' => 'datetime',
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
}