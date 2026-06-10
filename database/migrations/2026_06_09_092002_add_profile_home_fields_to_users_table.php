<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'profile_image')) {
                $table->string('profile_image')->nullable()->after('fcm_token');
            }

            if (!Schema::hasColumn('users', 'spin_balance')) {
                $table->integer('spin_balance')->default(0)->after('profile_image');
            }

            if (!Schema::hasColumn('users', 'discount_balance')) {
                $table->integer('discount_balance')->default(0)->after('spin_balance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'profile_image',
                'spin_balance',
                'discount_balance',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};