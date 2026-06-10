<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'signature')) {
                $table->string('signature')->nullable()->unique()->after('passcode');
            }
        });

        DB::table('users')
            ->whereNull('signature')
            ->orderBy('id')
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'signature' => self::generateSignature(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'signature')) {
                $table->dropUnique(['signature']);
                $table->dropColumn('signature');
            }
        });
    }

    private static function generateSignature(): string
    {
        do {
            $signature = '';

            for ($i = 0; $i < 38; $i++) {
                $signature .= random_int(0, 9);
            }
        } while (DB::table('users')->where('signature', $signature)->exists());

        return $signature;
    }
};