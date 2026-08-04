<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        /* ===================== REGIONS ===================== */
        if (!Schema::hasTable('regions')) {
            Schema::create('regions', function (Blueprint $t) {
                $t->id();
                $t->string('name_tj')->nullable();
                $t->string('name_ru')->nullable();
                $t->string('name_en')->nullable();
                $t->timestamps();
            });
        } else {
            // добавляем недостающие колонки (не трогаем существующие)
            Schema::table('regions', function (Blueprint $t) {
                if (!Schema::hasColumn('regions', 'name_tj')) $t->string('name_tj')->nullable();
                if (!Schema::hasColumn('regions', 'name_ru')) $t->string('name_ru')->nullable();
                if (!Schema::hasColumn('regions', 'name_en')) $t->string('name_en')->nullable();
            });
        }

        /* ===================== CITIES ===================== */
        if (!Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('region_id')->nullable();
                $t->string('name_tj')->nullable();
                $t->string('name_ru')->nullable();
                $t->string('name_en')->nullable();
                $t->timestamps();
                $t->foreign('region_id')->references('id')->on('regions')->onDelete('set null');
            });
        } else {
            Schema::table('cities', function (Blueprint $t) {
                if (!Schema::hasColumn('cities', 'region_id')) $t->unsignedBigInteger('region_id')->nullable();
                if (!Schema::hasColumn('cities', 'name_tj'))   $t->string('name_tj')->nullable();
                if (!Schema::hasColumn('cities', 'name_ru'))   $t->string('name_ru')->nullable();
                if (!Schema::hasColumn('cities', 'name_en'))   $t->string('name_en')->nullable();
            });
        }

        /* ===================== СЛОВАРИ ===================== */
        $regions = [
            1 => ['name_tj' => 'Вилояти Суғд',                       'name_ru' => 'Согдийская область',                            'name_en' => 'Sughd Region'],
            2 => ['name_tj' => 'Вилояти Хатлон',                     'name_ru' => 'Хатлонская область',                            'name_en' => 'Khatlon Region'],
            3 => ['name_tj' => 'Ноҳияҳои тобеи ҷумҳурӣ',             'name_ru' => 'Районы республиканского подчинения (РРП)',      'name_en' => 'Districts of Republican Subordination'],
            4 => ['name_tj' => 'Вилояти Мухтори Кӯҳистони Бадахшон', 'name_ru' => 'Горно-Бадахшанская автономная область (ГБАО)',  'name_en' => 'Gorno-Badakhshan Autonomous Region'],
            5 => ['name_tj' => 'Шаҳри Душанбе',                      'name_ru' => 'Город Душанбе',                                 'name_en' => 'City of Dushanbe'],
        ];

        $cities = [
            101 => ['region_id'=>1,'name_tj'=>'Шаҳри Хуҷанд','name_ru'=>'г. Худжанд','name_en'=>'Khujand'],
            102 => ['region_id'=>1,'name_tj'=>'Шаҳри Бӯстон','name_ru'=>'г. Бустон','name_en'=>'Buston'],
            103 => ['region_id'=>1,'name_tj'=>'Шаҳри Гулистон','name_ru'=>'г. Гулистон','name_en'=>'Guliston'],
            104 => ['region_id'=>1,'name_tj'=>'Шаҳри Истаравшан','name_ru'=>'г. Истаравшан','name_en'=>'Istaravshan'],
            105 => ['region_id'=>1,'name_tj'=>'Шаҳри Истиқлол','name_ru'=>'г. Истиклол','name_en'=>'Istiklol'],
            106 => ['region_id'=>1,'name_tj'=>'Шаҳри Исфара','name_ru'=>'г. Исфара','name_en'=>'Isfara'],
            107 => ['region_id'=>1,'name_tj'=>'Шаҳри Конибодом','name_ru'=>'г. Канибадам','name_en'=>'Kanibadam'],
            108 => ['region_id'=>1,'name_tj'=>'Шаҳри Панҷакент','name_ru'=>'г. Пенджикент','name_en'=>'Panjakent'],
            109 => ['region_id'=>1,'name_tj'=>'Ноҳияи Айнӣ','name_ru'=>'Айнинский район','name_en'=>'Ayni District'],
            110 => ['region_id'=>1,'name_tj'=>'Ноҳияи Ашт','name_ru'=>'Аштский район','name_en'=>'Asht District'],
            111 => ['region_id'=>1,'name_tj'=>'Ноҳияи Бобоҷон Ғафуров','name_ru'=>'Бободжон-Гафуровский район','name_en'=>'Bobojon Ghafurov District'],
            112 => ['region_id'=>1,'name_tj'=>'Ноҳияи Деваштич','name_ru'=>'Деваштичский район','name_en'=>'Devashtich District'],
            113 => ['region_id'=>1,'name_tj'=>'Ноҳияи Кӯҳистони Мастчоҳ','name_ru'=>'Кухистони-Мастчохский район','name_en'=>'Kuhistoni Mastchoh District'],
            114 => ['region_id'=>1,'name_tj'=>'Ноҳияи Мастчоҳ','name_ru'=>'Матчинский район','name_en'=>'Mastchoh District'],
            115 => ['region_id'=>1,'name_tj'=>'Ноҳияи Ҷаббор Расулов','name_ru'=>'Джаббор-Расуловский район','name_en'=>'Jabbor Rasulov District'],
            116 => ['region_id'=>1,'name_tj'=>'Ноҳияи Зафаробод','name_ru'=>'Зафарободский район','name_en'=>'Zafarobod District'],
            117 => ['region_id'=>1,'name_tj'=>'Ноҳияи Спитамен','name_ru'=>'Спитаменский район','name_en'=>'Spitamen District'],
            118 => ['region_id'=>1,'name_tj'=>'Ноҳияи Шаҳристон','name_ru'=>'Шахристанский район','name_en'=>'Shahriston District'],
            201 => ['region_id'=>2,'name_tj'=>'Шаҳри Бохтар','name_ru'=>'г. Бохтар','name_en'=>'Bokhtar'],
            202 => ['region_id'=>2,'name_tj'=>'Шаҳри Кӯлоб','name_ru'=>'г. Куляб','name_en'=>'Kulob'],
            203 => ['region_id'=>2,'name_tj'=>'Шаҳри Норак','name_ru'=>'г. Нурек','name_en'=>'Nurek'],
            204 => ['region_id'=>2,'name_tj'=>'Шаҳри Левакант','name_ru'=>'г. Левакант','name_en'=>'Levakant'],
            205 => ['region_id'=>2,'name_tj'=>'Ноҳияи Балҷувон','name_ru'=>'Бальджуванский район','name_en'=>'Baljuvon District'],
            206 => ['region_id'=>2,'name_tj'=>'Ноҳияи Бохтар','name_ru'=>'Бохтарский район','name_en'=>'Bokhtar District'],
            207 => ['region_id'=>2,'name_tj'=>'Ноҳияи Вахш','name_ru'=>'Вахшский район','name_en'=>'Vakhsh District'],
            208 => ['region_id'=>2,'name_tj'=>'Ноҳияи Восеъ','name_ru'=>'Восейский район','name_en'=>'Vose District'],
            209 => ['region_id'=>2,'name_tj'=>'Ноҳияи Данғара','name_ru'=>'Дангаринский район','name_en'=>'Danghara District'],
            210 => ['region_id'=>2,'name_tj'=>'Ноҳияи Абдураҳмони Ҷомӣ','name_ru'=>'Район Абдурахмона Джами','name_en'=>'Abdurahmoni Jomi District'],
            211 => ['region_id'=>2,'name_tj'=>'Ноҳияи Ҷайҳун','name_ru'=>'Джайхунский район','name_en'=>'Jayhun District'],
            212 => ['region_id'=>2,'name_tj'=>'Ноҳияи Қубодиён','name_ru'=>'Кубодиёнский район','name_en'=>'Qubodiyon District'],
            213 => ['region_id'=>2,'name_tj'=>'Ноҳияи Мӯъминобод','name_ru'=>'Муминабадский район','name_en'=>'Muminobod District'],
            214 => ['region_id'=>2,'name_tj'=>'Ноҳияи Панҷ','name_ru'=>'Пянджский район','name_en'=>'Panj District'],
            215 => ['region_id'=>2,'name_tj'=>'Ноҳияи Темурмалик','name_ru'=>'Темурмаликский район','name_en'=>'Temurmalik District'],
            216 => ['region_id'=>2,'name_tj'=>'Ноҳияи Фархор','name_ru'=>'Фархорский район','name_en'=>'Farxor District'],
            217 => ['region_id'=>2,'name_tj'=>'Ноҳияи Мир Сайид Алии Ҳамадонӣ','name_ru'=>'Район Мир Сайид Алии Хамадони','name_en'=>'Mir Sayid Alii Hamadoni District'],
            218 => ['region_id'=>2,'name_tj'=>'Ноҳияи Носири Хусрав','name_ru'=>'Район Носири Хусрав','name_en'=>'Nosiri Khusrav District'],
            219 => ['region_id'=>2,'name_tj'=>'Ноҳияи Ховалинг','name_ru'=>'Ховалингский район','name_en'=>'Khovaling District'],
            220 => ['region_id'=>2,'name_tj'=>'Ноҳияи Хуросон','name_ru'=>'Хуросонский район','name_en'=>'Khuroson District'],
            221 => ['region_id'=>2,'name_tj'=>'Ноҳияи Шаҳритӯс','name_ru'=>'Шахритусский район','name_en'=>'Shahritus District'],
            222 => ['region_id'=>2,'name_tj'=>'Ноҳияи Шамсиддин Шоҳин','name_ru'=>'Район Шамсиддин Шохин','name_en'=>'Shamsiddin Shohin District'],
            223 => ['region_id'=>2,'name_tj'=>'Ноҳияи Ёвон','name_ru'=>'Яванский район','name_en'=>'Yovon District'],
            301 => ['region_id'=>3,'name_tj'=>'Ноҳияи Варзоб','name_ru'=>'Варзобский район','name_en'=>'Varzob District'],
            302 => ['region_id'=>3,'name_tj'=>'Ноҳияи Вахдат','name_ru'=>'Вахдатский район','name_en'=>'Vahdat District'],
            303 => ['region_id'=>3,'name_tj'=>'Ноҳияи Ҳисор','name_ru'=>'Гиссарский район','name_en'=>'Hissor District'],
            304 => ['region_id'=>3,'name_tj'=>'Ноҳияи Лахш','name_ru'=>'Лахшский район','name_en'=>'Lakhsh District'],
            305 => ['region_id'=>3,'name_tj'=>'Ноҳияи Нуробод','name_ru'=>'Нурабадский район','name_en'=>'Nurobod District'],
            306 => ['region_id'=>3,'name_tj'=>'Ноҳияи Рашт','name_ru'=>'Раштский район','name_en'=>'Rasht District'],
            307 => ['region_id'=>3,'name_tj'=>'Ноҳияи Рӯдакӣ','name_ru'=>'Рудакинский район','name_en'=>'Rudaki District'],
            308 => ['region_id'=>3,'name_tj'=>'Ноҳияи Сангвор','name_ru'=>'Сангворский район','name_en'=>'Sangvor District'],
            309 => ['region_id'=>3,'name_tj'=>'Ноҳияи Тоҷикобод','name_ru'=>'Таджикабадский район','name_en'=>'Tojikobod District'],
            310 => ['region_id'=>3,'name_tj'=>'Ноҳияи Турсунзода','name_ru'=>'Турсунзадевский район','name_en'=>'Tursunzoda District'],
            311 => ['region_id'=>3,'name_tj'=>'Ноҳияи Файзобод','name_ru'=>'Файзабадский район','name_en'=>'Fayzobod District'],
            312 => ['region_id'=>3,'name_tj'=>'Ноҳияи Шаҳринав','name_ru'=>'Шахринавский район','name_en'=>'Shahrinov District'],
            313 => ['region_id'=>3,'name_tj'=>'Ноҳияи Роғун','name_ru'=>'Рогунский район','name_en'=>'Roghun District'],
            401 => ['region_id'=>4,'name_tj'=>'Шаҳри Хоруғ','name_ru'=>'г. Хорог','name_en'=>'Khorog'],
            402 => ['region_id'=>4,'name_tj'=>'Ноҳияи Дарвоз','name_ru'=>'Дарвазский район','name_en'=>'Darvoz District'],
            403 => ['region_id'=>4,'name_tj'=>'Ноҳияи Ванҷ','name_ru'=>'Ванчский район','name_en'=>'Vanj District'],
            404 => ['region_id'=>4,'name_tj'=>'Ноҳияи Рӯшон','name_ru'=>'Рушанский район','name_en'=>'Rushon District'],
            405 => ['region_id'=>4,'name_tj'=>'Ноҳияи Шуғнон','name_ru'=>'Шугнанский район','name_en'=>'Shughnon District'],
            406 => ['region_id'=>4,'name_tj'=>'Ноҳияи Роштқалъа','name_ru'=>'Рошткалинский район','name_en'=>'Roshtqala District'],
            407 => ['region_id'=>4,'name_tj'=>'Ноҳияи Ишкошим','name_ru'=>'Ишкашимский район','name_en'=>'Ishkoshim District'],
            408 => ['region_id'=>4,'name_tj'=>'Ноҳияи Мурғоб','name_ru'=>'Мургабский район','name_en'=>'Murghob District'],
            501 => ['region_id'=>5,'name_tj'=>'Шаҳри Душанбе','name_ru'=>'г. Душанбе','name_en'=>'Dushanbe'],
        ];

        /* ===================== СИНХРОНИЗАЦИЯ (адаптивно) ===================== */
        $regionCols = Schema::getColumnListing('regions');
        foreach ($regions as $id => $r) {
            $payload = [];
            foreach (['name_tj', 'name_ru', 'name_en'] as $c) {
                if (in_array($c, $regionCols, true)) $payload[$c] = $r[$c];
            }
            if (DB::table('regions')->where('id', $id)->exists()) {
                if (!empty($payload)) {
                    $payload['updated_at'] = $now;
                    DB::table('regions')->where('id', $id)->update($payload);
                }
            } else {
                $payload['id'] = $id;
                if (in_array('created_at', $regionCols, true)) $payload['created_at'] = $now;
                if (in_array('updated_at', $regionCols, true)) $payload['updated_at'] = $now;
                DB::table('regions')->insert($payload);
            }
        }

        $cityCols = Schema::getColumnListing('cities');
        foreach ($cities as $id => $c) {
            $payload = [];
            foreach (['region_id', 'name_tj', 'name_ru', 'name_en'] as $col) {
                if (in_array($col, $cityCols, true)) $payload[$col] = $c[$col];
            }
            if (DB::table('cities')->where('id', $id)->exists()) {
                if (!empty($payload)) {
                    $payload['updated_at'] = $now;
                    DB::table('cities')->where('id', $id)->update($payload);
                }
            } else {
                $payload['id'] = $id;
                if (in_array('created_at', $cityCols, true)) $payload['created_at'] = $now;
                if (in_array('updated_at', $cityCols, true)) $payload['updated_at'] = $now;
                DB::table('cities')->insert($payload);
            }
        }
    }

    public function down(): void
    {
        // ⚠️ В ПРОДЕ НЕ ДРОПАЕМ ТАБЛИЦЫ (на них ссылаются компании по FK).
        // При откате просто удаляем справочные записи по нашим id (если на них нет ссылок).
        $ids = [101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,
            201,202,203,204,205,206,207,208,209,210,211,212,213,214,215,216,217,218,219,220,221,222,223,
            301,302,303,304,305,306,307,308,309,310,311,312,313,
            401,402,403,404,405,406,407,408, 501];
        try { DB::table('cities')->whereIn('id', $ids)->delete(); } catch (\Throwable $e) {}
        try { DB::table('regions')->whereIn('id', [1,2,3,4,5])->delete(); } catch (\Throwable $e) {}
    }
};