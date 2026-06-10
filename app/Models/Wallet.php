<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = [
        'name',
        'type',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function userWallets(): HasMany
    {
        return $this->hasMany(UserWallet::class);
    }
}