<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'campaign_shares',
            function (Blueprint $table): void {
                $table
                    ->foreignId('reviewed_by')
                    ->nullable()
                    ->after('verified_at')
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->timestamp('reviewed_at')
                    ->nullable()
                    ->after('reviewed_by');

                $table
                    ->string('rejection_reason', 500)
                    ->nullable()
                    ->after('reviewed_at');

                $table->index(
                    [
                        'status',
                        'reviewed_at',
                    ],
                    'campaign_share_review_status_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'campaign_shares',
            function (Blueprint $table): void {
                $table->dropIndex(
                    'campaign_share_review_status_index'
                );

                $table->dropForeign([
                    'reviewed_by',
                ]);

                $table->dropColumn([
                    'reviewed_by',
                    'reviewed_at',
                    'rejection_reason',
                ]);
            }
        );
    }
};