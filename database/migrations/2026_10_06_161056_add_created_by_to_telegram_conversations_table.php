<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('telegram_conversations', 'created_by')) {
            Schema::table('telegram_conversations', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->after('company_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('telegram_conversations', 'created_by')) {
            Schema::table('telegram_conversations', function (Blueprint $table) {
                $table->dropColumn('created_by');
            });
        }
    }
};