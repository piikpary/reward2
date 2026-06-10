<?php

use App\Enums\UserStatus;
use App\Enums\UserType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'uuid')) {
                $table->string('uuid', 20)->nullable()->unique()->after('id');
            }

            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number', 20)->nullable()->unique()->after('email');
            }

            if (!Schema::hasColumn('users', 'user_type')) {
                $table->unsignedTinyInteger('user_type')->default(UserType::CUSTOMER->value)->after('phone_number');
            }

            if (!Schema::hasColumn('users', 'status')) {
                $table->unsignedTinyInteger('status')->default(UserStatus::ACTIVE->value)->after('user_type');
            }

            if (!Schema::hasColumn('users', 'device_uuid')) {
                $table->string('device_uuid')->nullable()->after('status');
            }

            if (!Schema::hasColumn('users', 'fcm_token')) {
                $table->text('fcm_token')->nullable()->after('device_uuid');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'uuid',
                'phone_number',
                'user_type',
                'status',
                'device_uuid',
                'fcm_token',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};