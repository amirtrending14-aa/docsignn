<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Добавляем is_super_admin, если его нет
            if (!Schema::hasColumn('users', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->after('is_admin');
            }
            // Добавляем role и level, если их нет (раз они требуются моделью)
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('employee')->after('is_super_admin');
            }
            if (!Schema::hasColumn('users', 'level')) {
                $table->integer('level')->default(1)->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_super_admin', 'role', 'level']);
        });
    }
};