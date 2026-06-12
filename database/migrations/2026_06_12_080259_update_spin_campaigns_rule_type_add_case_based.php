<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE spin_campaigns MODIFY rule_type ENUM('standard', 'special', 'case_based') NOT NULL DEFAULT 'case_based'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE spin_campaigns MODIFY rule_type ENUM('standard', 'special') NOT NULL DEFAULT 'standard'");
    }
};