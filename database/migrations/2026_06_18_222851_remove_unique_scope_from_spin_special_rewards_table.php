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
                $table->dropUnique(
                    'spin_special_reward_scope_unique'
                );

                $table->index(
                    [
                        'scope_type',
                        'scope_id',
                    ],
                    'spin_special_reward_scope_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'spin_special_rewards',
            function (Blueprint $table) {
                $table->dropIndex(
                    'spin_special_reward_scope_index'
                );

                $table->unique(
                    [
                        'scope_type',
                        'scope_id',
                    ],
                    'spin_special_reward_scope_unique'
                );
            }
        );
    }
};