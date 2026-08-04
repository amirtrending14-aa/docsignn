<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cities', 'name_en')) {
            Schema::table('cities', function (Blueprint $table) {
                $table->string('name_en')->nullable()->after('name_tj');
            });

            // Опционально: заполняем английские названия (упрощённо)
            $translations = [
                101 => 'Khujand', 102 => 'Buston', 103 => 'Guliston', 104 => 'Istaravshan',
                105 => 'Istiklol', 106 => 'Isfara', 107 => 'Kanibadam', 108 => 'Panjakent',
                201 => 'Bokhtar', 202 => 'Kulob', 203 => 'Nurek', 204 => 'Levakant',
                301 => 'Varzob', 302 => 'Vahdat', 303 => 'Hissor', 304 => 'Lakhsh',
                401 => 'Khorog', 501 => 'Dushanbe'
                // ... можно добавить остальные по аналогии
            ];
            foreach ($translations as $id => $name) {
                DB::table('cities')->where('id', $id)->update(['name_en' => $name]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cities', 'name_en')) {
            Schema::table('cities', function (Blueprint $table) {
                $table->dropColumn('name_en');
            });
        }
    }
};