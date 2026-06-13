<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spin_sub_campaigns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('spin_campaign_id')
                ->constrained('spin_campaigns')
                ->cascadeOnDelete();

            $table->string('name');

            $table->unsignedInteger('total_cases');
            $table->unsignedInteger('spins_per_case');

            $table->decimal('normal_discount_total', 10, 2);

            $table->unsignedBigInteger('total_spins_used')->default(0);

            $table->unsignedInteger('priority')->default(1);

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index([
                'spin_campaign_id',
                'status',
                'priority',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spin_sub_campaigns');
    }
};