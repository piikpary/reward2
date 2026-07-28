<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'user_registration_rewards',
            function (Blueprint $table): void {
                $table->id();

                /*
                 * Unique user_id is the final protection
                 * against duplicate registration gifts.
                 */
                $table->foreignId('user_id')
                    ->unique()
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->foreignId(
                    'registration_reward_setting_id'
                )
                    ->nullable()
                    ->constrained(
                        'registration_reward_settings'
                    )
                    ->nullOnDelete();

                $table->foreignId(
                    'wallet_transaction_id'
                )
                    ->unique()
                    ->constrained(
                        'wallet_transactions'
                    )
                    ->cascadeOnDelete();

                $table->string(
                    'wallet_type',
                    20
                );

                $table->decimal(
                    'amount',
                    18,
                    2
                );
                

                $table->timestamp(
                    'awarded_at'
                );

                $table->timestamps();

                $table->index([
                    'wallet_type',
                    'awarded_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'user_registration_rewards'
        );
    }
};