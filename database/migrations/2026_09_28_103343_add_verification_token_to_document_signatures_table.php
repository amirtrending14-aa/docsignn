<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('document_signatures', function (Blueprint $table) {
            // Добавляем колонку для уникального токена проверки
            // unique() гарантирует, что два документа не получат одинаковый код
            $table->string('verification_token')->nullable()->unique()->after('signature');
        });
    }

    public function down()
    {
        Schema::table('document_signatures', function (Blueprint $table) {
            $table->dropColumn('verification_token');
        });
    }
};