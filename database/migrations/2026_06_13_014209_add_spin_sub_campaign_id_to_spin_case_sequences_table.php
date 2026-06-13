<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spin_case_sequences', function (Blueprint $table) {
            $table->foreignId('spin_sub_campaign_id')
                ->nullable()
                ->after('spin_campaign_id')
                ->constrained('spin_sub_campaigns')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('spin_case_sequences', function (Blueprint $table) {
            $table->dropForeign([
                'spin_sub_campaign_id',
            ]);

            $table->dropColumn('spin_sub_campaign_id');
        });
    }
};