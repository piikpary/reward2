<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->unique(); // spin, discount
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        DB::table('wallets')->insert([
            [
                'id' => 1,
                'name' => 'Spin Wallet',
                'type' => 'spin',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Discount Wallet',
                'type' => 'discount',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};