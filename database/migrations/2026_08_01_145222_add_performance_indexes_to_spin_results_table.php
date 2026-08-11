<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'spin_results',
            function (Blueprint $table): void {
                /*
                 * Helps the one-row-per-case GROUP BY query.
                 */
                $table->index(
                    [
                        'spin_campaign_id',
                        'spin_sub_campaign_id',
                        'user_id',
                        'case_number',
                        'created_at',
                    ],
                    'spin_results_case_group_idx'
                );

                /*
                 * Helps filtering by customer and date.
                 */
                $table->index(
                    [
                        'user_id',
                        'created_at',
                    ],
                    'spin_results_user_date_idx'
                );

                /*
                 * Helps filtering by campaign and date.
                 */
                $table->index(
                    [
                        'spin_campaign_id',
                        'created_at',
                    ],
                    'spin_results_campaign_date_idx'
                );

                /*
                 * Helps direct Case Number searches.
                 */
                $table->index(
                    'case_number',
                    'spin_results_case_number_idx'
                );

                /*
                 * Helps From Date and To Date filters.
                 */
                $table->index(
                    'created_at',
                    'spin_results_created_at_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'spin_results',
            function (Blueprint $table): void {
                $table->dropIndex(
                    'spin_results_case_group_idx'
                );

                $table->dropIndex(
                    'spin_results_user_date_idx'
                );

                $table->dropIndex(
                    'spin_results_campaign_date_idx'
                );

                $table->dropIndex(
                    'spin_results_case_number_idx'
                );

                $table->dropIndex(
                    'spin_results_created_at_idx'
                );
            }
        );
    }
};