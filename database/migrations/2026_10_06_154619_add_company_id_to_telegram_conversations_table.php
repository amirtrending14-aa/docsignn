<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegram_conversations', function (Blueprint $table) {
            // 🔗 Привязка группового чата к компании (null = обычная группа)
            $table->foreignId('company_id')
                  ->nullable()
                  ->after('type')
                  ->constrained('companies')
                  ->cascadeOnDelete();

            // 🚫 Одна группа на компанию
            $table->unique('company_id');
        });
    }

    public function down(): void
    {
        Schema::table('telegram_conversations', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropUnique(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};