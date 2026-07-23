<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasColumn(
                'campaign_share_rewards',
                'granted_by'
            )
        ) {
            Schema::table(
                'campaign_share_rewards',
                function (Blueprint $table): void {
                    $table
                        ->foreignId('granted_by')
                        ->nullable()
                        ->after('wallet_transaction_id')
                        ->constrained('users')
                        ->nullOnDelete();
                }
            );
        }
    }

    public function down(): void
    {
        if (
            Schema::hasColumn(
                'campaign_share_rewards',
                'granted_by'
            )
        ) {
            Schema::table(
                'campaign_share_rewards',
                function (Blueprint $table): void {
                    $table->dropForeign([
                        'granted_by',
                    ]);

                    $table->dropColumn(
                        'granted_by'
                    );
                }
            );
        }
    }
};