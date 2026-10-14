<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Таблица диалогов Telegram
        Schema::create('telegram_conversations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['private', 'group'])->default('private');
            $table->string('name')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamps();
        });

        // Таблица участников
        Schema::create('telegram_conversation_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telegram_conversation_id')->constrained('telegram_conversations')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['admin', 'member'])->default('member');
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();
        });

        // Таблица сообщений
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telegram_conversation_id')->constrained('telegram_conversations')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->text('body')->nullable();
            $table->enum('type', ['text', 'image', 'file', 'voice', 'sticker'])->default('text');
            $table->string('attachment_path')->nullable();
            $table->unsignedBigInteger('reply_to')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
        Schema::dropIfExists('telegram_conversation_user');
        Schema::dropIfExists('telegram_conversations');
    }
};