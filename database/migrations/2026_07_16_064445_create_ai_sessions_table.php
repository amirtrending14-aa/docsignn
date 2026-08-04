<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Связь с пользователем
            $table->string('status')->default('idle'); // idle, identifying_type, asking_details, confirming, completed
            $table->string('document_type')->nullable(); // contract, application, etc.
            $table->json('collected_data')->nullable(); // Здесь храним все ответы: {name: '...', sum: '...'}
            $table->string('language')->default('ru'); // ru, tj, en
            $table->timestamp('last_active_at')->useCurrent();
            $table->timestamps();

            // Индекс для быстрого поиска активной сессии пользователя
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_sessions');
    }
};