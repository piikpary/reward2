<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'wallet_transactions',
            function (Blueprint $table) {
                $table->string(
                    'special_reward_code',
                    30
                )
                    ->nullable()
                    ->after('description');

                $table->index(
                    'special_reward_code',
                    'wallet_transactions_special_code_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'wallet_transactions',
            function (Blueprint $table) {
                $table->dropIndex(
                    'wallet_transactions_special_code_idx'
                );

                $table->dropColumn(
                    'special_reward_code'
                );
            }
        );
    }
};