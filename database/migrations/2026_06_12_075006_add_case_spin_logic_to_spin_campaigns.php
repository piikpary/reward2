<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('spin_campaigns')) {
            return;
        }

        Schema::table('spin_campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('spin_campaigns', 'total_cases')) {
                $table->unsignedInteger('total_cases')->default(0)->after('priority');
            }

            if (!Schema::hasColumn('spin_campaigns', 'spins_per_case')) {
                $table->unsignedInteger('spins_per_case')->default(4)->after('total_cases');
            }

            if (!Schema::hasColumn('spin_campaigns', 'normal_discount_total')) {
                $table->unsignedInteger('normal_discount_total')->default(30)->after('spins_per_case');
            }

            if (!Schema::hasColumn('spin_campaigns', 'total_spins_used')) {
                $table->unsignedInteger('total_spins_used')->default(0)->after('normal_discount_total');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('spin_campaigns')) {
            return;
        }

        Schema::table('spin_campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('spin_campaigns', 'total_spins_used')) {
                $table->dropColumn('total_spins_used');
            }

            if (Schema::hasColumn('spin_campaigns', 'normal_discount_total')) {
                $table->dropColumn('normal_discount_total');
            }

            if (Schema::hasColumn('spin_campaigns', 'spins_per_case')) {
                $table->dropColumn('spins_per_case');
            }

            if (Schema::hasColumn('spin_campaigns', 'total_cases')) {
                $table->dropColumn('total_cases');
            }
        });
    }
};