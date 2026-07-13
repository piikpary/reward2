<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class TransactionSummaryCache
{
    public static function key(int $userId): string
    {
        return sprintf(
            'reward2:api:user:%d:transactions:v1',
            $userId
        );
    }

    public static function forget(
        int|string|null ...$userIds
    ): void {
        $userIds = array_unique(
            array_filter(
                array_map(
                    fn ($userId): int => (int) $userId,
                    $userIds
                )
            )
        );

        foreach ($userIds as $userId) {
            Cache::forget(
                self::key($userId)
            );
        }
    }
}