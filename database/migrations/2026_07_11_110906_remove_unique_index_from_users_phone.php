<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // Убираем уникальный индекс с phone
        DB::statement('ALTER TABLE users DROP INDEX users_phone_unique');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // Возвращаем обратно
        DB::statement('ALTER TABLE users ADD UNIQUE INDEX users_phone_unique (phone)');
    }
};