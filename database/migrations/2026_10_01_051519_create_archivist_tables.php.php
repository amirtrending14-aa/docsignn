<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Паспорт документа: весь текст, ИИ-саммари, найденные люди
        Schema::create('document_indexes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->unique()->constrained('documents')->cascadeOnDelete();
            $table->longText('extracted_text')->nullable();
            $table->text('ai_summary')->nullable();
            $table->json('people')->nullable();
            $table->timestamp('indexed_at')->nullable();
            $table->timestamps();
        });

        // Документ, нарезанный на куски (~1000 символов) для поиска по содержимому
        Schema::create('document_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->unsignedInteger('chunk_index')->default(0);
            $table->text('content');
            $table->timestamps();
            if (DB::connection()->getDriverName() === 'mysql') {
                $table->fullText('content');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_chunks');
        Schema::dropIfExists('document_indexes');
    }
};