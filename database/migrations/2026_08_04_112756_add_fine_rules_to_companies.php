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
        if (! Schema::hasColumn('companies', 'late_block_minutes')) {
            $table->integer('late_block_minutes')->default(60);   // каждые X минут
        }
        if (! Schema::hasColumn('companies', 'late_block_fine')) {
            $table->decimal('late_block_fine', 10, 2)->default(100); // = Y сом
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
