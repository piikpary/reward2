<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spin_special_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spin_campaign_id')->constrained('spin_campaigns')->cascadeOnDelete();
            $table->unsignedInteger('case_number');
            $table->unsignedSmallInteger('total_discount');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['spin_campaign_id', 'case_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spin_special_cases');
    }
};