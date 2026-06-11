<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            if (!Schema::hasColumn('sliders', 'title')) {
                $table->string('title')->nullable()->after('id');
            }

            if (!Schema::hasColumn('sliders', 'description')) {
                $table->text('description')->nullable()->after('title');
            }

            if (!Schema::hasColumn('sliders', 'link')) {
                $table->string('link')->nullable()->after('description');
            }

            if (!Schema::hasColumn('sliders', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('link');
            }

            if (!Schema::hasColumn('sliders', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('sort_order');
            }
        });

        if (!Schema::hasTable('slider_images')) {
            Schema::create('slider_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('slider_id')->constrained('sliders')->cascadeOnDelete();
                $table->string('image');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('slider_images');

        Schema::table('sliders', function (Blueprint $table) {
            if (Schema::hasColumn('sliders', 'title')) {
                $table->dropColumn('title');
            }

            if (Schema::hasColumn('sliders', 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn('sliders', 'link')) {
                $table->dropColumn('link');
            }

            if (Schema::hasColumn('sliders', 'sort_order')) {
                $table->dropColumn('sort_order');
            }

            if (Schema::hasColumn('sliders', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};