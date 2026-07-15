<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Добавляем только те колонки, которых нет
            if (!Schema::hasColumn('companies', 'owner_id')) {
                $table->unsignedBigInteger('owner_id')->nullable()->after('email');
            }
            if (!Schema::hasColumn('companies', 'owner_telegram_id')) {
                $table->string('owner_telegram_id')->nullable()->after('owner_id');
            }
            if (!Schema::hasColumn('companies', 'address')) {
                $table->text('address')->nullable()->after('owner_telegram_id');
            }
            if (!Schema::hasColumn('companies', 'password')) {
                $table->string('password')->nullable()->after('address');
            }
            if (!Schema::hasColumn('companies', 'status')) {
                $table->enum('status', ['active', 'inactive', 'pending'])->default('active')->after('password');
            }
            if (!Schema::hasColumn('companies', 'slug')) {
                $table->string('slug')->unique()->nullable()->after('name');
            }
        });

        // Добавляем внешний ключ для owner_id (если колонка есть и её создали сейчас)
        if (Schema::hasColumn('companies', 'owner_id')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->foreign('owner_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'owner_id')) {
                $table->dropForeign(['owner_id']);
                $table->dropColumn('owner_id');
            }
            if (Schema::hasColumn('companies', 'owner_telegram_id')) {
                $table->dropColumn('owner_telegram_id');
            }
            if (Schema::hasColumn('companies', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('companies', 'password')) {
                $table->dropColumn('password');
            }
            if (Schema::hasColumn('companies', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};