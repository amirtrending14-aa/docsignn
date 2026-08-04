<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Добавляем колонку parent_id
            $table->unsignedBigInteger('parent_id')->nullable()->after('owner_id');
            
            // Добавляем внешний ключ (связь с самой таблицей companies)
            // onDelete('cascade') удалит дочерние компании, если удалится родитель
            $table->foreign('parent_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};