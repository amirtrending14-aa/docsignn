<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('on_time','late','absent','excused','refused')");
}

public function down()
{
    DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('on_time','late','absent','excused')");
}
};
