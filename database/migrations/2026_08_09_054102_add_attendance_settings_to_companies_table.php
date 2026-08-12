<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'work_start_time')) {
                $table->string('work_start_time')->default('08:30');
            }
            if (!Schema::hasColumn('companies', 'late_tolerance_minutes')) {
                $table->integer('late_tolerance_minutes')->default(0);
            }
            if (!Schema::hasColumn('companies', 'late_block_minutes')) {
                $table->integer('late_block_minutes')->default(60);
            }
            if (!Schema::hasColumn('companies', 'late_block_fine')) {
                $table->decimal('late_block_fine', 10, 2)->default(100.00);
            }
            if (!Schema::hasColumn('companies', 'absence_fine')) {
                $table->decimal('absence_fine', 10, 2)->default(200.00);
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'work_start_time',
                'late_tolerance_minutes',
                'late_block_minutes',
                'late_block_fine',
                'absence_fine'
            ]);
        });
    }
};