<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('ai_chats', function (Blueprint $table) {
        $table->json('state')->nullable()->after('id'); // или после другой колонки
    });
}

public function down()
{
    Schema::table('ai_chats', function (Blueprint $table) {
        $table->dropColumn('state');
    });
}
};
