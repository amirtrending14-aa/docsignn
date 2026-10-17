<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('sender_who');
            $table->string('sender_email');
            $table->string('sender_phone');

            $table->string('doc_type');
            $table->string('doc_title');
            $table->string('doc_number')->nullable();
            $table->text('doc_description')->nullable();
            $table->string('doc_file')->nullable();
            $table->date('deadline')->nullable();

            $table->foreignId('recipient_user_id')->constrained('users')->cascadeOnDelete();

            $table->enum('status', ['new', 'in_progress', 'answered', 'closed'])->default('new');
            $table->text('admin_comment')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_requests');
    }
};