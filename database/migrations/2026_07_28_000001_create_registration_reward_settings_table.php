<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'registration_reward_settings',
            function (Blueprint $table): void {
                $table->id();

                $table->boolean('is_enabled')
                    ->default(false);

                $table->string(
                    'wallet_type',
                    20
                )->default('spin');

                $table->decimal(
                    'amount',
                    18,
                    2
                )->default(0);

                $table->foreignId('updated_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();
            }
        );

        /*
         * Create the single default settings row.
         */
        DB::table(
            'registration_reward_settings'
        )->insert([
            'id' => 1,
            'is_enabled' => false,
            'wallet_type' => 'spin',
            'amount' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'registration_reward_settings'
        );
    }
};