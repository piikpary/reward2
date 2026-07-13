<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignUserProgress extends Model
{
    protected $table = 'campaign_user_progresses';
    protected $fillable = [
        'share_campaign_id',
        'user_id',
        'current_shares',
        'rewards_earned_count',
        'last_milestone_rewarded',
        'last_shared_at',
    ];

    protected function casts(): array
    {
        return [
            'current_shares' => 'integer',
            'rewards_earned_count' => 'integer',
            'last_milestone_rewarded' => 'integer',
            'last_shared_at' => 'datetime',
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