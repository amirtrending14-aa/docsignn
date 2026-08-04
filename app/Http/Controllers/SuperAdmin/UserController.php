<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    /* ---------- словари (id совпадают с фронтом 101..501) ---------- */
    private function regionDictionary(): array
    {
        return [
            1 => ['name_tj'=>'Вилояти Суғд','name_ru'=>'Согдийская область','name_en'=>'Sughd Region'],
            2 => ['name_tj'=>'Вилояти Хатлон','name_ru'=>'Хатлонская область','name_en'=>'Khatlon Region'],
            3 => ['name_tj'=>'Ноҳияҳои тобеи ҷумҳурӣ','name_ru'=>'Районы республиканского подчинения (РРП)','name_en'=>'Districts of Republican Subordination'],
            4 => ['name_tj'=>'Вилояти Мухтори Кӯҳистони Бадахшон','name_ru'=>'Горно-Бадахшанская автономная область (ГБАО)','name_en'=>'Gorno-Badakhshan Autonomous Region'],
            5 => ['name_tj'=>'Шаҳри Душанбе','name_ru'=>'Город Душанбе','name_en'=>'City of Dushanbe'],
        ];
    }

    private function cityDictionary(): array
    {
        return [
            101=>['region_id'=>1,'name_tj'=>'Шаҳри Хуҷанд','name_ru'=>'г. Худжанд','name_en'=>'Khujand'],
            102=>['region_id'=>1,'name_tj'=>'Шаҳри Бӯстон','name_ru'=>'г. Бустон','name_en'=>'Buston'],
            103=>['region_id'=>1,'name_tj'=>'Шаҳри Гулистон','name_ru'=>'г. Гулистон','name_en'=>'Guliston'],
            104=>['region_id'=>1,'name_tj'=>'Шаҳри Истаравшан','name_ru'=>'г. Истаравшан','name_en'=>'Istaravshan'],
            105=>['region_id'=>1,'name_tj'=>'Шаҳри Истиқлол','name_ru'=>'г. Истиклол','name_en'=>'Istiklol'],
            106=>['region_id'=>1,'name_tj'=>'Шаҳри Исфара','name_ru'=>'г. Исфара','name_en'=>'Isfara'],
            107=>['region_id'=>1,'name_tj'=>'Шаҳри Конибодом','name_ru'=>'г. Канибадам','name_en'=>'Kanibadam'],
            108=>['region_id'=>1,'name_tj'=>'Шаҳри Панҷакент','name_ru'=>'г. Пенджикент','name_en'=>'Panjakent'],
            109=>['region_id'=>1,'name_tj'=>'Ноҳияи Айнӣ','name_ru'=>'Айнинский район','name_en'=>'Ayni District'],
            110=>['region_id'=>1,'name_tj'=>'Ноҳияи Ашт','name_ru'=>'Аштский район','name_en'=>'Asht District'],
            111=>['region_id'=>1,'name_tj'=>'Ноҳияи Бобоҷон Ғафуров','name_ru'=>'Бободжон-Гафуровский район','name_en'=>'Bobojon Ghafurov District'],
            112=>['region_id'=>1,'name_tj'=>'Ноҳияи Деваштич','name_ru'=>'Деваштичский район','name_en'=>'Devashtich District'],
            113=>['region_id'=>1,'name_tj'=>'Ноҳияи Кӯҳистони Мастчоҳ','name_ru'=>'Кухистони-Мастчохский район','name_en'=>'Kuhistoni Mastchoh District'],
            114=>['region_id'=>1,'name_tj'=>'Ноҳияи Мастчоҳ','name_ru'=>'Матчинский район','name_en'=>'Mastchoh District'],
            115=>['region_id'=>1,'name_tj'=>'Ноҳияи Ҷаббор Расулов','name_ru'=>'Джаббор-Расуловский район','name_en'=>'Jabbor Rasulov District'],
            116=>['region_id'=>1,'name_tj'=>'Ноҳияи Зафаробод','name_ru'=>'Зафарободский район','name_en'=>'Zafarobod District'],
            117=>['region_id'=>1,'name_tj'=>'Ноҳияи Спитамен','name_ru'=>'Спитаменский район','name_en'=>'Spitamen District'],
            118=>['region_id'=>1,'name_tj'=>'Ноҳияи Шаҳристон','name_ru'=>'Шахристанский район','name_en'=>'Shahriston District'],
            201=>['region_id'=>2,'name_tj'=>'Шаҳри Бохтар','name_ru'=>'г. Бохтар','name_en'=>'Bokhtar'],
            202=>['region_id'=>2,'name_tj'=>'Шаҳри Кӯлоб','name_ru'=>'г. Куляб','name_en'=>'Kulob'],
            203=>['region_id'=>2,'name_tj'=>'Шаҳри Норак','name_ru'=>'г. Нурек','name_en'=>'Nurek'],
            204=>['region_id'=>2,'name_tj'=>'Шаҳри Левакант','name_ru'=>'г. Левакант','name_en'=>'Levakant'],
            205=>['region_id'=>2,'name_tj'=>'Ноҳияи Балҷувон','name_ru'=>'Бальджуванский район','name_en'=>'Baljuvon District'],
            206=>['region_id'=>2,'name_tj'=>'Ноҳияи Бохтар','name_ru'=>'Бохтарский район','name_en'=>'Bokhtar District'],
            207=>['region_id'=>2,'name_tj'=>'Ноҳияи Вахш','name_ru'=>'Вахшский район','name_en'=>'Vakhsh District'],
            208=>['region_id'=>2,'name_tj'=>'Ноҳияи Восеъ','name_ru'=>'Восейский район','name_en'=>'Vose District'],
            209=>['region_id'=>2,'name_tj'=>'Ноҳияи Данғара','name_ru'=>'Дангаринский район','name_en'=>'Danghara District'],
            210=>['region_id'=>2,'name_tj'=>'Ноҳияи Абдураҳмони Ҷомӣ','name_ru'=>'Район Абдурахмона Джами','name_en'=>'Abdurahmoni Jomi District'],
            211=>['region_id'=>2,'name_tj'=>'Ноҳияи Ҷайҳун','name_ru'=>'Джайхунский район','name_en'=>'Jayhun District'],
            212=>['region_id'=>2,'name_tj'=>'Ноҳияи Қубодиён','name_ru'=>'Кубодиёнский район','name_en'=>'Qubodiyon District'],
            213=>['region_id'=>2,'name_tj'=>'Ноҳияи Мӯъминобод','name_ru'=>'Муминабадский район','name_en'=>'Muminobod District'],
            214=>['region_id'=>2,'name_tj'=>'Ноҳияи Панҷ','name_ru'=>'Пянджский район','name_en'=>'Panj District'],
            215=>['region_id'=>2,'name_tj'=>'Ноҳияи Темурмалик','name_ru'=>'Темурмаликский район','name_en'=>'Temurmalik District'],
            216=>['region_id'=>2,'name_tj'=>'Ноҳияи Фархор','name_ru'=>'Фархорский район','name_en'=>'Farxor District'],
            217=>['region_id'=>2,'name_tj'=>'Ноҳияи Мир Сайид Алии Ҳамадонӣ','name_ru'=>'Район Мир Сайид Алии Хамадони','name_en'=>'Mir Sayid Alii Hamadoni District'],
            218=>['region_id'=>2,'name_tj'=>'Ноҳияи Носири Хусрав','name_ru'=>'Район Носири Хусрав','name_en'=>'Nosiri Khusrav District'],
            219=>['region_id'=>2,'name_tj'=>'Ноҳияи Ховалинг','name_ru'=>'Ховалингский район','name_en'=>'Khovaling District'],
            220=>['region_id'=>2,'name_tj'=>'Ноҳияи Хуросон','name_ru'=>'Хуросонский район','name_en'=>'Khuroson District'],
            221=>['region_id'=>2,'name_tj'=>'Ноҳияи Шаҳритӯс','name_ru'=>'Шахритусский район','name_en'=>'Shahritus District'],
            222=>['region_id'=>2,'name_tj'=>'Ноҳияи Шамсиддин Шоҳин','name_ru'=>'Район Шамсиддин Шохин','name_en'=>'Shamsiddin Shohin District'],
            223=>['region_id'=>2,'name_tj'=>'Ноҳияи Ёвон','name_ru'=>'Яванский район','name_en'=>'Yovon District'],
            301=>['region_id'=>3,'name_tj'=>'Ноҳияи Варзоб','name_ru'=>'Варзобский район','name_en'=>'Varzob District'],
            302=>['region_id'=>3,'name_tj'=>'Ноҳияи Вахдат','name_ru'=>'Вахдатский район','name_en'=>'Vahdat District'],
            303=>['region_id'=>3,'name_tj'=>'Ноҳияи Ҳисор','name_ru'=>'Гиссарский район','name_en'=>'Hissor District'],
            304=>['region_id'=>3,'name_tj'=>'Ноҳияи Лахш','name_ru'=>'Лахшский район','name_en'=>'Lakhsh District'],
            305=>['region_id'=>3,'name_tj'=>'Ноҳияи Нуробод','name_ru'=>'Нурабадский район','name_en'=>'Nurobod District'],
            306=>['region_id'=>3,'name_tj'=>'Ноҳияи Рашт','name_ru'=>'Раштский район','name_en'=>'Rasht District'],
            307=>['region_id'=>3,'name_tj'=>'Ноҳияи Рӯдакӣ','name_ru'=>'Рудакинский район','name_en'=>'Rudaki District'],
            308=>['region_id'=>3,'name_tj'=>'Ноҳияи Сангвор','name_ru'=>'Сангворский район','name_en'=>'Sangvor District'],
            309=>['region_id'=>3,'name_tj'=>'Ноҳияи Тоҷикобод','name_ru'=>'Таджикабадский район','name_en'=>'Tojikobod District'],
            310=>['region_id'=>3,'name_tj'=>'Ноҳияи Турсунзода','name_ru'=>'Турсунзадевский район','name_en'=>'Tursunzoda District'],
            311=>['region_id'=>3,'name_tj'=>'Ноҳияи Файзобод','name_ru'=>'Файзабадский район','name_en'=>'Fayzobod District'],
            312=>['region_id'=>3,'name_tj'=>'Ноҳияи Шаҳринав','name_ru'=>'Шахринавский район','name_en'=>'Shahrinov District'],
            313=>['region_id'=>3,'name_tj'=>'Ноҳияи Роғун','name_ru'=>'Рогунский район','name_en'=>'Roghun District'],
            401=>['region_id'=>4,'name_tj'=>'Шаҳри Хоруғ','name_ru'=>'г. Хорог','name_en'=>'Khorog'],
            402=>['region_id'=>4,'name_tj'=>'Ноҳияи Дарвоз','name_ru'=>'Дарвазский район','name_en'=>'Darvoz District'],
            403=>['region_id'=>4,'name_tj'=>'Ноҳияи Ванҷ','name_ru'=>'Ванчский район','name_en'=>'Vanj District'],
            404=>['region_id'=>4,'name_tj'=>'Ноҳияи Рӯшон','name_ru'=>'Рушанский район','name_en'=>'Rushon District'],
            405=>['region_id'=>4,'name_tj'=>'Ноҳияи Шуғнон','name_ru'=>'Шугнанский район','name_en'=>'Shughnon District'],
            406=>['region_id'=>4,'name_tj'=>'Ноҳияи Роштқалъа','name_ru'=>'Рошткалинский район','name_en'=>'Roshtqala District'],
            407=>['region_id'=>4,'name_tj'=>'Ноҳияи Ишкошим','name_ru'=>'Ишкашимский район','name_en'=>'Ishkoshim District'],
            408=>['region_id'=>4,'name_tj'=>'Ноҳияи Мурғоб','name_ru'=>'Мургабский район','name_en'=>'Murghob District'],
            501=>['region_id'=>5,'name_tj'=>'Шаҳри Душанбе','name_ru'=>'г. Душанбе','name_en'=>'Dushanbe'],
        ];
    }

    /** Кэш списка колонок таблицы (адаптивность под любую структуру БД). */
    private function tableColumns(string $table): array
    {
        static $cache = [];
        if (!isset($cache[$table])) {
            try { $cache[$table] = Schema::hasTable($table) ? Schema::getColumnListing($table) : []; }
            catch (\Throwable $e) { $cache[$table] = []; }
        }
        return $cache[$table];
    }

    /** Собирает payload только из реально существующих колонок. */
    private function buildPayload(array $row, array $cols): array
    {
        $payload = [];
        foreach ($row as $k => $v) {
            if (in_array($k, $cols, true)) $payload[$k] = $v;
        }
        return $payload;
    }

    private function ensureRegion(?int $id): void
    {
        if (!$id) return;
        $dict = $this->regionDictionary();
        if (!isset($dict[$id])) return;
        $cols = $this->tableColumns('regions');
        if (empty($cols)) return; // таблицы нет → и FK у companies нет, ничего не делаем

        $payload = $this->buildPayload($dict[$id], $cols);
        $now = now();

        if (DB::table('regions')->where('id', $id)->exists()) {
            if (!empty($payload)) {
                if (in_array('updated_at', $cols, true)) $payload['updated_at'] = $now;
                DB::table('regions')->where('id', $id)->update($payload);
            }
        } else {
            $payload['id'] = $id;
            if (in_array('created_at', $cols, true)) $payload['created_at'] = $now;
            if (in_array('updated_at', $cols, true)) $payload['updated_at'] = $now;
            DB::table('regions')->insert($payload);
        }
    }

    private function ensureCity(?int $id): void
    {
        if (!$id) return;
        $dict = $this->cityDictionary();
        if (!isset($dict[$id])) return;
        $cols = $this->tableColumns('cities');
        if (empty($cols)) return;

        $this->ensureRegion((int) $dict[$id]['region_id']); // FK cities.region_id

        $payload = $this->buildPayload($dict[$id], $cols);
        $now = now();

        if (DB::table('cities')->where('id', $id)->exists()) {
            if (!empty($payload)) {
                if (in_array('updated_at', $cols, true)) $payload['updated_at'] = $now;
                DB::table('cities')->where('id', $id)->update($payload);
            }
        } else {
            $payload['id'] = $id;
            if (in_array('created_at', $cols, true)) $payload['created_at'] = $now;
            if (in_array('updated_at', $cols, true)) $payload['updated_at'] = $now;
            DB::table('cities')->insert($payload);
        }
    }

    /* ===================== CRUD ===================== */

    public function index(Request $request)
    {
        $query = User::with('companyRelation');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if ($request->filled('company_id')) $query->where('company_id', $request->company_id);
        if ($request->filled('status')) {
            if ($request->status === 'online') {
                $query->where('last_seen_at', '>=', now()->subMinutes(5));
            } elseif ($request->status === 'offline') {
                $query->where(function ($q) {
                    $q->where('last_seen_at', '<', now()->subMinutes(5))->orWhereNull('last_seen_at');
                });
            }
        }

        $users = $query->latest()->paginate(20)->withQueryString();
        $companies = Company::orderBy('name')->get();
        return view('superadmin.users.index', compact('users', 'companies'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        return view('superadmin.users.create', compact('companies'));
    }

    public function noCompanies()
    {
        $users = User::where(function ($q) {
            $q->whereNull('company_id')->orWhere('company_id', 0);
        })->where('is_super_admin', false)->latest()->paginate(20);
        return view('superadmin.users.no-companies', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|min:6|confirmed',
            'phone'                 => 'nullable|string|max:20',
            'role'                  => 'required|in:employee,admin,super_admin',
            'level'                 => 'required|integer|min:0|max:20',
            'company_id'            => 'nullable|exists:companies,id',
            'new_company_name'      => 'nullable|string|max:255',
            'new_company_type'      => 'nullable|string|max:100',
            'new_company_region_id' => 'nullable|integer|in:1,2,3,4,5',
            'new_company_city_id'   => 'nullable|integer',
            'is_admin'              => 'nullable|boolean',
            'avatar'                => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $companyId = $data['company_id'] ?? null;

        if (!$companyId && !empty($data['new_company_name'])) {
            // ✅ гарантируем наличие region + city (адаптивно, без 1054 и без 1452)
            $this->ensureRegion(isset($data['new_company_region_id']) ? (int) $data['new_company_region_id'] : null);
            $this->ensureCity(isset($data['new_company_city_id']) ? (int) $data['new_company_city_id'] : null);

            $company = Company::create([
                'name'      => $data['new_company_name'],
                'type'      => $data['new_company_type'] ?? null,
                'region_id' => $data['new_company_region_id'] ?? null,
                'city_id'   => $data['new_company_city_id'] ?? null,
                'email'     => $data['email'] ?? null,
            ]);
            $companyId = $company->id;
        }

        $userData = [
            'name'           => $data['name'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),
            'phone'          => $data['phone'] ?? null,
            'role'           => $data['role'],
            'level'          => $data['level'],
            'company_id'     => $companyId,
            'is_admin'       => $data['role'] === 'admin' || ($data['is_admin'] ?? false),
            'is_super_admin' => $data['role'] === 'super_admin',
        ];
        if ($request->hasFile('avatar')) {
            $userData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $user = User::create($userData);

        if ($companyId && empty($data['company_id'])) {
            Company::where('id', $companyId)->update(['owner_id' => $user->id]);
        }

        return redirect()->route('superadmin.users.index')
            ->with('success', '✅ Пользователь и компания созданы успешно!');
    }

    public function edit(User $user)
    {
        $companies = Company::orderBy('name')->get();
        return view('superadmin.users.edit', compact('user', 'companies'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email,' . $user->id,
            'password'          => 'nullable|min:6|confirmed',
            'phone'             => 'nullable|string|max:20',
            'role'              => 'required|in:employee,admin,super_admin',
            'level'             => 'required|integer|min:0|max:20',
            'company_id'        => 'nullable|exists:companies,id',
            'is_admin'          => 'nullable|boolean',
            'avatar'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_avatar'     => 'nullable|boolean',
            'company_region_id' => 'nullable|integer|in:1,2,3,4,5',
            'company_city_id'   => 'nullable|integer',
            'company_type'      => 'nullable|string|max:100',
        ]);

        $companyId = $data['company_id'] ?? null;

        if ($companyId) {
            $companyUpdateData = [];
            if (isset($data['company_region_id'])) {
                $this->ensureRegion((int) $data['company_region_id']);
                $companyUpdateData['region_id'] = $data['company_region_id'];
            }
            if (isset($data['company_city_id'])) {
                $this->ensureCity((int) $data['company_city_id']);
                $companyUpdateData['city_id'] = $data['company_city_id'];
            }
            if (isset($data['company_type'])) {
                $companyUpdateData['type'] = $data['company_type'];
            }
            if (!empty($companyUpdateData)) {
                Company::where('id', $companyId)->update($companyUpdateData);
            }
        }

        $userData = [
            'name'           => $data['name'],
            'email'          => $data['email'],
            'phone'          => $data['phone'] ?? null,
            'role'           => $data['role'],
            'level'          => $data['level'],
            'company_id'     => $companyId,
            'is_admin'       => $data['role'] === 'admin' || ($data['is_admin'] ?? false),
            'is_super_admin' => $data['role'] === 'super_admin',
        ];
        if (!empty($data['password'])) $userData['password'] = Hash::make($data['password']);

        if ($request->input('remove_avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $userData['avatar'] = null;
        }
        if ($request->hasFile('avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $userData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($userData);

        return redirect()->route('superadmin.users.index')
            ->with('success', '✅ Пользователь и данные компании успешно обновлены');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) return back()->with('error', '❌ Нельзя удалить самого себя');
        if ($user->is_super_admin) return back()->with('error', '❌ Нельзя удалить супер-администратора');
        if ($user->avatar) Storage::disk('public')->delete($user->avatar);

        $userName = $user->name;
        $user->delete();
        return redirect()->route('superadmin.users.index')
            ->with('success', "✅ Пользователь '{$userName}' успешно удалён");
    }

    public function userActivity($id)
    {
        $user = User::findOrFail($id);
        $users = User::orderBy('name')->get();
        $activities = \App\Models\DocumentLog::where('user_id', $id)
            ->with('document')->orderBy('created_at', 'desc')->paginate(30);
        return view('superadmin.activity', compact('user', 'users', 'activities'));
    }
}