<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Share Campaigns
        |--------------------------------------------------------------------------
        | Stores campaigns created by the business/admin.
        */
        Schema::create('share_campaigns', function (Blueprint $table) {
            $table->id();

            // Keep nullable because we have not added a business foreign key.
            $table->unsignedBigInteger('business_id')
                ->nullable()
                ->index();

            $table->string('title', 255);
            $table->text('description')->nullable();

            $table->string('image_path', 2048)->nullable();
            $table->string('share_url', 2048)->nullable();

            // Example: share 100 times to receive 10 spins.
            $table->unsignedInteger('required_shares');
            $table->unsignedInteger('reward_spins');

            /*
             * false = customer receives reward only once.
             * true  = customer can receive rewards at every milestone.
             */
            $table->boolean('reward_repeatable')
                ->default(false);

            $table->boolean('is_active')
                ->default(true);

            $table->boolean('is_published')
                ->default(false);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('published_at')->nullable();

            // Total verified shares from all customers.
            $table->unsignedBigInteger('total_shares')
                ->default(0);

            $table->unsignedBigInteger('created_by')
                ->nullable();

            $table->unsignedBigInteger('updated_by')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                [
                    'is_active',
                    'is_published',
                    'published_at',
                ],
                'share_campaign_availability_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 2. Campaign Shares
        |--------------------------------------------------------------------------
        | Stores every Facebook post submitted by a customer.
        */
        Schema::create('campaign_shares', function (Blueprint $table) {
            $table->id();

            $table->foreignId('share_campaign_id')
                ->constrained('share_campaigns')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->text('facebook_post_url');

            /*
             * SHA-256 hash of the normalized Facebook URL.
             * Prevents the same Facebook post from being submitted twice.
             */
            $table->char('facebook_post_url_hash', 64)
                ->unique();

            $table->string('status', 30)
                ->default('verified');

            $table->string('verification_method', 50)
                ->default('url_format');

            $table->timestamp('shared_at');
            $table->timestamp('verified_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(
                [
                    'share_campaign_id',
                    'user_id',
                    'shared_at',
                ],
                'campaign_share_history_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 3. Campaign User Progresses
        |--------------------------------------------------------------------------
        | Stores one customer's progress for one campaign.
        */
        Schema::create('campaign_user_progresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('share_campaign_id')
                ->constrained('share_campaigns')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('current_shares')
                ->default(0);

            $table->unsignedInteger('rewards_earned_count')
                ->default(0);

            /*
             * 0 = no milestone rewarded
             * 1 = first milestone rewarded
             * 2 = second milestone rewarded
             */
            $table->unsignedInteger('last_milestone_rewarded')
                ->default(0);

            $table->timestamp('last_shared_at')
                ->nullable();

            $table->timestamps();

            // One progress row per user and campaign.
            $table->unique(
                [
                    'share_campaign_id',
                    'user_id',
                ],
                'campaign_user_progress_unique'
            );

            $table->index(
                'last_shared_at',
                'campaign_progress_last_shared_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 4. Campaign Share Rewards
        |--------------------------------------------------------------------------
        | Stores every spin reward awarded from campaign sharing.
        */
        Schema::create('campaign_share_rewards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('share_campaign_id')
                ->constrained('share_campaigns')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
             * Example:
             * milestone_number = 1
             * milestone_threshold = 100
             */
            $table->unsignedInteger('milestone_number');
            $table->unsignedBigInteger('milestone_threshold');

            $table->unsignedInteger('reward_spins');

            /*
             * References the existing wallet transaction record.
             * No foreign key yet because we must match your exact
             * Reward2 wallet transaction table structure.
             */
            $table->unsignedBigInteger('wallet_transaction_id')
                ->nullable()
                ->unique();

            $table->timestamp('awarded_at');

            $table->timestamps();

            // Prevent the same milestone from being rewarded twice.
            $table->unique(
                [
                    'share_campaign_id',
                    'user_id',
                    'milestone_number',
                ],
                'campaign_share_reward_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
         * Delete child tables first because they contain foreign keys.
         */
        Schema::dropIfExists('campaign_share_rewards');
        Schema::dropIfExists('campaign_user_progresses');
        Schema::dropIfExists('campaign_shares');
        Schema::dropIfExists('share_campaigns');
    }
};