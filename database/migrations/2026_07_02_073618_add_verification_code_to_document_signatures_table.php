<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('document_signatures', function (Blueprint $table) {
            $table->string('verification_code')->nullable()->after('signed_at');
            $table->index('verification_code');
        });
    }

    public function down()
    {
        Schema::table('document_signatures', function (Blueprint $table) {
            $table->dropIndex(['verification_code']);
            $table->dropColumn('verification_code');
        });
    }
};