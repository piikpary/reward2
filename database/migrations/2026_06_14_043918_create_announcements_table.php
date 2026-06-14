<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->longText('content');

            $table->dateTime('announcement_date');

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'status',
                'announcement_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};