<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('spin_campaigns')) {
            Schema::create('spin_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('rule_type')->default('standard');
                $table->text('description')->nullable();
                $table->dateTime('start_date')->nullable();
                $table->dateTime('end_date')->nullable();
                $table->integer('priority')->default(1);
                $table->tinyInteger('status')->default(1);

                $table->unsignedInteger('total_cases')->default(0);
                $table->unsignedInteger('spins_per_case')->default(4);
                $table->unsignedInteger('normal_discount_total')->default(30);
                $table->unsignedInteger('total_spins_used')->default(0);

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('spin_campaigns');
    }
};