<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sliders') && Schema::hasColumn('sliders', 'status')) {
            // Step 1: change integer status column to varchar first
            DB::statement("
                ALTER TABLE sliders
                MODIFY status VARCHAR(20) NOT NULL DEFAULT 'active'
            ");

            // Step 2: convert old value 1/0 to active/inactive
            DB::statement("
                UPDATE sliders
                SET status = CASE
                    WHEN status = '1' THEN 'active'
                    WHEN status = '0' THEN 'inactive'
                    WHEN status = 'active' THEN 'active'
                    WHEN status = 'inactive' THEN 'inactive'
                    ELSE 'active'
                END
            ");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sliders') && Schema::hasColumn('sliders', 'status')) {
            DB::statement("
                UPDATE sliders
                SET status = CASE
                    WHEN status = 'active' THEN '1'
                    WHEN status = 'inactive' THEN '0'
                    ELSE '1'
                END
            ");

            DB::statement("
                ALTER TABLE sliders
                MODIFY status TINYINT(1) NOT NULL DEFAULT 1
            ");
        }
    }
};