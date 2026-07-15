<?php
//
//use Illuminate\Database\Migrations\Migration;
//use Illuminate\Database\Schema\Blueprint;
//use Illuminate\Support\Facades\Schema;
//
//return new class extends Migration
//    /**
//     * Run the migrations.
//     */
//{
//    public function up(): void
//    {
//        Schema::table('document_workflows', function (Blueprint $table) {
//            $table->enum('status', ['pending', 'approved', 'rejected', 'waiting'])
//                ->default('pending')
//                ->change();
//        });
//    }
//
//    public function down(): void
//    {
//        Schema::table('document_workflows', function (Blueprint $table) {
//            $table->enum('status', ['pending', 'approved', 'rejected'])
//                ->default('pending')
//                ->change();
//        });
//    }
//};
//


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE document_workflows
            MODIFY status ENUM('pending','approved','rejected','waiting')
            DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE document_workflows
            SET status = 'pending'
            WHERE status NOT IN ('pending','approved','rejected')
        ");

        DB::statement("
            ALTER TABLE document_workflows
            MODIFY status ENUM('pending','approved','rejected')
            DEFAULT 'pending'
        ");
    }
};
