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
                $table->foreignId('discount_id')
                    ->nullable()
                    ->after('special_discount')
                    ->constrained('discounts')
                    ->nullOnDelete();

                $table->boolean('discount_auto_created')
                    ->default(false)
                    ->after('discount_id');

                $table->index(
                    [
                        'assigned_sub_campaign_id',
                        'status',
                        'is_used',
                        'spin_position',
                    ],
                    'special_reward_position_lookup'
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
                    'special_reward_position_lookup'
                );

                $table->dropConstrainedForeignId(
                    'discount_id'
                );

                $table->dropColumn(
                    'discount_auto_created'
                );
            }
        );
    }
};