<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Добавляем slug если его нет
            if (!Schema::hasColumn('companies', 'slug')) {
                $table->string('slug')->unique()->after('id');
            }

            // Добавляем password_hash если его нет
            if (!Schema::hasColumn('companies', 'password_hash')) {
                $table->string('password_hash')->after('email');
            }

            // Добавляем phone если его нет
            if (!Schema::hasColumn('companies', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }

            // Добавляем owner_telegram_id если его нет
            if (!Schema::hasColumn('companies', 'owner_telegram_id')) {
                $table->bigInteger('owner_telegram_id')->nullable()->after('phone');
            }

            // Добавляем language если его нет
            if (!Schema::hasColumn('companies', 'language')) {
                $table->enum('language', ['ru', 'tj', 'en'])->default('ru')->after('owner_telegram_id');
            }

            // Добавляем status если его нет
            if (!Schema::hasColumn('companies', 'status')) {
                $table->enum('status', ['active', 'blocked', 'pending'])->default('active')->after('language');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['slug', 'password_hash', 'phone', 'owner_telegram_id', 'language', 'status']);
        });
    }
};