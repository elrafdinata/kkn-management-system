<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name');
            $table->string('role')->default('anggota')->after('password');
            $table->string('schedule_category')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('schedule_category');
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'role', 'schedule_category', 'is_active']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
