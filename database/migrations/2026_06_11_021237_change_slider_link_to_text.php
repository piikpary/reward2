<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sliders') && Schema::hasColumn('sliders', 'link')) {
            DB::statement("ALTER TABLE sliders MODIFY link TEXT NULL");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sliders') && Schema::hasColumn('sliders', 'link')) {
            DB::statement("ALTER TABLE sliders MODIFY link VARCHAR(255) NULL");
        }
    }
};