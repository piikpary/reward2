<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spin_case_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spin_campaign_id')->constrained('spin_campaigns')->cascadeOnDelete();
            $table->unsignedInteger('case_number');
            $table->unsignedSmallInteger('total_discount');
            $table->json('sequence');
            $table->unsignedTinyInteger('used_spins')->default(0);
            $table->timestamps();

            $table->unique(['spin_campaign_id', 'case_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spin_case_sequences');
    }
};