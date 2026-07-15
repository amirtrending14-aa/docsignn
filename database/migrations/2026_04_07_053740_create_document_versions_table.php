<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('document_id')
                ->constrained('documents')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->integer('version');

            $table->string('original_name')->nullable();
            $table->string('file_path');
            $table->string('extension', 10)->nullable();
            $table->bigInteger('file_size')->nullable();

            $table->text('change_summary')->nullable();

            // PRO
            $table->string('status')->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index('document_id');
            $table->index('user_id');

            $table->unique(['document_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
