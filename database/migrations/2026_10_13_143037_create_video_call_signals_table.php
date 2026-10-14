<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_call_signals', function (Blueprint $table) {
            $table->id();
            $table->string('call_id')->index();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('from_user_id');
            $table->unsignedBigInteger('to_user_id');
            $table->string('signal_type');
            $table->json('data');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['to_user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_call_signals');
    }
};