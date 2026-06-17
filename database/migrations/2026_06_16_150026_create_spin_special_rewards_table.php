<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spin_special_rewards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('spin_campaign_id')
                ->constrained('spin_campaigns')
                ->cascadeOnDelete();

            /*
             * main_campaign:
             * Configured from the main campaign.
             *
             * sub_campaign:
             * Configured from one specific subcampaign.
             */
            $table->enum('scope_type', [
                'main_campaign',
                'sub_campaign',
            ]);

            /*
             * For main_campaign scope, this stores spin_campaign_id.
             * For sub_campaign scope, this stores spin_sub_campaign_id.
             */
            $table->unsignedBigInteger('scope_id');

            /*
             * The subcampaign where the system secretly placed the reward.
             */
            $table->foreignId('assigned_sub_campaign_id')
                ->nullable()
                ->constrained('spin_sub_campaigns')
                ->cascadeOnDelete();

            /*
             * Hidden position inside the assigned subcampaign.
             *
             * Example:
             * 1 to 4000.
             */
            $table->unsignedBigInteger('spin_position')
                ->nullable();

            $table->decimal(
                'special_discount',
                10,
                2
            );

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            $table->boolean('is_used')
                ->default(false);

            $table->foreignId('used_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('used_at')
                ->nullable();

            $table->timestamps();

            /*
             * Prevent two rewards from occupying the same spin.
             */
            $table->unique(
                [
                    'assigned_sub_campaign_id',
                    'spin_position',
                ],
                'spin_special_reward_position_unique'
            );

            /*
             * One special reward configuration per scope.
             */
            $table->unique(
                [
                    'scope_type',
                    'scope_id',
                ],
                'spin_special_reward_scope_unique'
            );

            $table->index([
                'spin_campaign_id',
                'status',
                'is_used',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spin_special_rewards');
    }
};