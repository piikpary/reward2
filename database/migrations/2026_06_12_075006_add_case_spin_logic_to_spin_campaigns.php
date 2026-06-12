<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spin_campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('spin_campaigns', 'total_cases')) {
                $table->unsignedInteger('total_cases')->default(0)->after('priority');
            }

            if (!Schema::hasColumn('spin_campaigns', 'spins_per_case')) {
                $table->unsignedTinyInteger('spins_per_case')->default(4)->after('total_cases');
            }

            if (!Schema::hasColumn('spin_campaigns', 'normal_discount_total')) {
                $table->unsignedSmallInteger('normal_discount_total')->default(30)->after('spins_per_case');
            }

            if (!Schema::hasColumn('spin_campaigns', 'total_spins_used')) {
                $table->unsignedInteger('total_spins_used')->default(0)->after('normal_discount_total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('spin_campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'total_cases',
                'spins_per_case',
                'normal_discount_total',
                'total_spins_used',
            ]);
        });
    }
};