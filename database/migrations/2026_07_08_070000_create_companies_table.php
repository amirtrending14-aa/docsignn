<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Сначала создаем таблицу, если её нет
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password')->nullable(); // Сразу делаем nullable
                $table->timestamps();
                // Добавьте другие нужные поля здесь
            });
        } else {
            // Если таблица уже есть, просто меняем поле
            Schema::table('companies', function (Blueprint $table) {
                $table->string('password')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};