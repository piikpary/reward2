<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'spin_special_rewards',
            function (Blueprint $table) {
                $table->boolean('is_redeemed')
                    ->default(false)
                    ->after('used_at');

                $table->timestamp('redeemed_at')
                    ->nullable()
                    ->after('is_redeemed');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'spin_special_rewards',
            function (Blueprint $table) {
                $table->dropColumn([
                    'is_redeemed',
                    'redeemed_at',
                ]);
            }
        );
    }
};