<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spin_rewards', function (Blueprint $table) {
            $table->id();
            $table->integer('discount_percentage');
            $table->integer('chance_weight')->default(1);
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        DB::table('spin_rewards')->insert([
            ['discount_percentage' => 5, 'chance_weight' => 30, 'sort_order' => 1, 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['discount_percentage' => 10, 'chance_weight' => 25, 'sort_order' => 2, 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['discount_percentage' => 15, 'chance_weight' => 20, 'sort_order' => 3, 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['discount_percentage' => 20, 'chance_weight' => 15, 'sort_order' => 4, 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['discount_percentage' => 25, 'chance_weight' => 10, 'sort_order' => 5, 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['discount_percentage' => 30, 'chance_weight' => 8, 'sort_order' => 6, 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['discount_percentage' => 35, 'chance_weight' => 5, 'sort_order' => 7, 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['discount_percentage' => 50, 'chance_weight' => 2, 'sort_order' => 8, 'status' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('spin_rewards');
    }
};