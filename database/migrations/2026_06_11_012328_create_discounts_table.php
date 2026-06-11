<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->integer('discount_percentage');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique('discount_percentage');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};