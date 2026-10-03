<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('on_time','late','absent','excused','refused')");
    }

    public function down()
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

    DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('on_time','late','absent','excused')");
}
};
