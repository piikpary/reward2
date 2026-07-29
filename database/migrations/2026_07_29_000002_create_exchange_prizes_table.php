<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'exchange_prizes',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId(
                    'product_category_id'
                )
                    ->constrained(
                        'product_categories'
                    )
                    ->restrictOnDelete();

                $table->string(
                    'title',
                    255
                );

                $table->string(
                    'image_path',
                    2048
                )->nullable();

                $table->decimal(
                    'exchange_discount_amount',
                    18,
                    2
                )->default(0);

                $table->timestamps();

                /*
                 * Improves category filtering and
                 * newest-product sorting.
                 */
                $table->index([
                    'product_category_id',
                    'id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'exchange_prizes'
        );
    }
};