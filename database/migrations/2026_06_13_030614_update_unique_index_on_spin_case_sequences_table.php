<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * The old unique index is currently supporting the foreign key
         * on spin_campaign_id. Add a normal index first.
         */
        Schema::table('spin_case_sequences', function (Blueprint $table) {
            $table->index(
                'spin_campaign_id',
                'spin_case_sequences_campaign_id_index'
            );
        });

        /*
         * Now MySQL can safely remove the old unique index.
         */
        Schema::table('spin_case_sequences', function (Blueprint $table) {
            $table->dropUnique(
                'spin_case_sequences_spin_campaign_id_case_number_unique'
            );
        });

        /*
         * Each subcampaign can now have its own Case 1, Case 2, etc.
         */
        Schema::table('spin_case_sequences', function (Blueprint $table) {
            $table->unique(
                [
                    'spin_campaign_id',
                    'spin_sub_campaign_id',
                    'case_number',
                ],
                'spin_case_sequences_campaign_sub_case_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('spin_case_sequences', function (Blueprint $table) {
            $table->dropUnique(
                'spin_case_sequences_campaign_sub_case_unique'
            );
        });

        Schema::table('spin_case_sequences', function (Blueprint $table) {
            $table->unique(
                [
                    'spin_campaign_id',
                    'case_number',
                ],
                'spin_case_sequences_spin_campaign_id_case_number_unique'
            );
        });

        Schema::table('spin_case_sequences', function (Blueprint $table) {
            $table->dropIndex(
                'spin_case_sequences_campaign_id_index'
            );
        });
    }
};