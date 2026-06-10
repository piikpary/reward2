<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'user_type',
        'status',
        'uuid',
        'device_uuid',
        'fcm_token',
        'profile_image',
        'spin_balance',
        'discount_balance',
        'passcode',
        'signature',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'user_type' => UserType::class,
            'status' => UserStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = static::generateUniqueUuid();
            }
        });
    }

    public static function generateUniqueUuid(): string
    {
        do {
            $uuid = Str::random(10);
        } while (static::where('uuid', $uuid)->exists());

        return $uuid;
    }

    public static function findByPhoneNumber(string $phone): ?User
    {
        return self::where('phone_number', $phone)->first();
    }
    public function userWallets(): HasMany
{
    return $this->hasMany(\App\Models\UserWallet::class);
}

public function walletTransactions(): HasMany
{
    return $this->hasMany(\App\Models\WalletTransaction::class);
}

public static function generateSignature(): string
{
    do {
        $signature = '';

        for ($i = 0; $i < 38; $i++) {
            $signature .= random_int(0, 9);
        }
    } while (self::where('signature', $signature)->exists());

    return $signature;
}
}