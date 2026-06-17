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
                $table->string(
                    'reward_code',
                    30
                )
                    ->nullable()
                    ->unique()
                    ->after('special_discount');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'spin_special_rewards',
            function (Blueprint $table) {
                $table->dropUnique([
                    'reward_code',
                ]);

                $table->dropColumn(
                    'reward_code'
                );
            }
        );
    }
};