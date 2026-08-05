<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------- USERS ----------
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'needs_face_scan')) {
                $table->boolean('needs_face_scan')->default(false);
            }
            if (! Schema::hasColumn('users', 'face_vector')) {
                $table->json('face_vector')->nullable();
            }
            if (! Schema::hasColumn('users', 'face_registered')) {
                $table->boolean('face_registered')->default(false);
            }
        });

        // ---------- COMPANIES (правила штрафов) ----------
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'work_start_time')) {
                $table->time('work_start_time')->default('08:30');
            }
            if (! Schema::hasColumn('companies', 'late_tolerance_minutes')) {
                $table->integer('late_tolerance_minutes')->default(5);
            }
            if (! Schema::hasColumn('companies', 'late_fine')) {
                $table->decimal('late_fine', 10, 2)->default(100);
            }
            if (! Schema::hasColumn('companies', 'absence_fine')) {
                $table->decimal('absence_fine', 10, 2)->default(200);
            }
        });

        // ---------- ATTENDANCES (посещаемость) ----------
        if (! Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->date('date');
                $table->time('check_in_time')->nullable();
                $table->string('status')->default('absent'); // on_time | late | absent | excused
                $table->decimal('fine', 10, 2)->default(0);
                $table->unique(['user_id', 'date']);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};