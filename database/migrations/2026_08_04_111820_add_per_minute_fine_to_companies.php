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
        if (! Schema::hasColumn('companies', 'late_fine_per_minute')) {
            $table->decimal('late_fine_per_minute', 10, 2)->default(0); // сом за 1 минуту опоздания
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            //
        });
    }
};
