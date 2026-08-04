<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;

class CompanyTreeController extends Controller
{
    private function getCitiesArray(): array {
        return [
            '1' => [
                ['id' => 101, 'name' => 'г. Худжанд (центр)'], ['id' => 102, 'name' => 'г. Бустон'],
                ['id' => 103, 'name' => 'г. Гулистон'], ['id' => 104, 'name' => 'г. Истаравшан'],
                ['id' => 105, 'name' => 'г. Истиклол'], ['id' => 106, 'name' => 'г. Исфара'],
                ['id' => 107, 'name' => 'г. Канибадам'], ['id' => 108, 'name' => 'г. Пенджикент'],
                ['id' => 109, 'name' => 'Айнинский район'], ['id' => 110, 'name' => 'Аштский район'],
                ['id' => 111, 'name' => 'Бободжон-Гафуровский район'], ['id' => 112, 'name' => 'Деваштичский район'],
                ['id' => 113, 'name' => 'Кухистони-Мастчохский (Горно-Матчинский) р-н'],
                ['id' => 114, 'name' => 'Матчинский район'], ['id' => 115, 'name' => 'Джаббор-Расуловский район'],
                ['id' => 116, 'name' => 'Зафарободский район'], ['id' => 117, 'name' => 'Спитаменский район'],
                ['id' => 118, 'name' => 'Шахристанский район']
            ],
            '2' => [
                ['id' => 201, 'name' => 'г. Бохтар (бывш. Курган-Тюбе, центр)'], ['id' => 202, 'name' => 'г. Куляб'],
                ['id' => 203, 'name' => 'г. Нурек'], ['id' => 204, 'name' => 'г. Левакант'],
                ['id' => 205, 'name' => 'Бальджуванский район'], ['id' => 206, 'name' => 'Бохтарский район'],
                ['id' => 207, 'name' => 'Вахшский район'], ['id' => 208, 'name' => 'Восейский район'],
                ['id' => 209, 'name' => 'Дангаринский район'], ['id' => 210, 'name' => 'Район Абдурахмона Джами'],
                ['id' => 211, 'name' => 'Джайхунский район'], ['id' => 212, 'name' => 'Кубодиёнский район'],
                ['id' => 213, 'name' => 'Муминабадский район'], ['id' => 214, 'name' => 'Пянджский район'],
                ['id' => 215, 'name' => 'Темурмаликский район'], ['id' => 216, 'name' => 'Фархорский район'],
                ['id' => 217, 'name' => 'Район Мир Сайид Алии Хамадони'], ['id' => 218, 'name' => 'Район Носири Хусрав'],
                ['id' => 219, 'name' => 'Ховалингский район'], ['id' => 220, 'name' => 'Хуросонский район'],
                ['id' => 221, 'name' => 'Шахритусский район'], ['id' => 222, 'name' => 'Район Шамсиддин Шохин'],
                ['id' => 223, 'name' => 'Яванский район']
            ],
            '3' => [
                ['id' => 301, 'name' => 'Варзобский район'], ['id' => 302, 'name' => 'Вахдатский район'],
                ['id' => 303, 'name' => 'Гиссарский район'], ['id' => 304, 'name' => 'Лахшский район'],
                ['id' => 305, 'name' => 'Нурабадский район'], ['id' => 306, 'name' => 'Раштский район'],
                ['id' => 307, 'name' => 'Рудакинский район'], ['id' => 308, 'name' => 'Сангворский район'],
                ['id' => 309, 'name' => 'Таджикабадский район'], ['id' => 310, 'name' => 'Турсунзадевский район'],
                ['id' => 311, 'name' => 'Файзабадский район'], ['id' => 312, 'name' => 'Шахринавский район'],
                ['id' => 313, 'name' => 'Рогунский район']
            ],
            '4' => [
                ['id' => 401, 'name' => 'г. Хорог (центр)'], ['id' => 402, 'name' => 'Дарвазский район'],
                ['id' => 403, 'name' => 'Ванчский район'], ['id' => 404, 'name' => 'Рушанский район'],
                ['id' => 405, 'name' => 'Шугнанский район'], ['id' => 406, 'name' => 'Рошткалинский район'],
                ['id' => 407, 'name' => 'Ишкашимский район'], ['id' => 408, 'name' => 'Мургабский район']
            ],
            '5' => [
                ['id' => 501, 'name' => 'г. Душанбе (столица)']
            ]
        ];
    }

    private function getAllValidCityIds(): array {
        $ids = [];
        foreach ($this->getCitiesArray() as $regionCities) {
            foreach ($regionCities as $city) {
                $ids[] = $city['id'];
            }
        }
        return $ids;
    }

    /**
     * Преобразует коллекцию компаний в плоский массив для статистики
     */
        /**
     * Преобразует коллекцию компаний в плоский список с расчётом глубины
     */
    private function flatten(Collection $companies): Collection {
        if ($companies->isEmpty()) {
            return collect();
        }
        
        // 1. Создаём карту для быстрого поиска родителей
        $map = [];
        foreach ($companies as $c) {
            $c->depth = 0; // Инициализируем глубину
            $map[$c->id] = $c;
        }

        // 2. Вычисляем реальную глубину для каждой компании
        foreach ($map as $id => $c) {
            $depth = 0;
            $current = $c;
            
            // Поднимаемся вверх по дереву, пока есть родитель и он есть в выборке
            while ($current->parent_id && isset($map[$current->parent_id])) {
                $depth++;
                $current = $map[$current->parent_id];
            }
            $map[$id]->depth = $depth;
        }

        // 3. Сортируем: сначала по глубине, потом по региону, потом по городу
        return $companies->sortBy([
            fn($a, $b) => $a->depth <=> $b->depth,
            fn($a, $b) => $a->region_id <=> $b->region_id,
            fn($a, $b) => $a->city_id <=> $b->city_id,
        ])->values(); // Возвращаем Коллекцию, а не массив!
    }

    /**
     * ✅ Строит вложенное дерево из плоской коллекции
     */
    private function buildNestedTree(Collection $companies, $parentId = null): Collection {
        return $companies->where('parent_id', $parentId)
            ->sortBy('id')
            ->values()
            ->map(function ($company) use ($companies) {
                $company->nestedChildren = $this->buildNestedTree($companies, $company->id);
                return $company;
            });
    }

        public function index() {
        $user = auth()->user();

        // ✅ ИЗМЕНЕНО: Все пользователи (включая админов) видят ТОЛЬКО своё дерево
        // managedCompanies() возвращает компанию пользователя и всех её потомков
                $visible = $user->managedCompanies();
        // 2. Формируем плоский список для статистики
        $tree = $this->flatten($visible);

        // 3. Формируем вложенное дерево
        $nestedTree = collect();
        if ($visible->isNotEmpty() && Schema::hasColumn('companies', 'parent_id')) {
            $nestedTree = $this->buildNestedTree($visible);
        }

        // ✅ ДОБАВЛЕНО: Подготовка данных для JavaScript (модальное окно выбора пользователей)
        // Преобразуем коллекцию компаний в массив, содержащий ID, имя и список пользователей
        $companiesData = $visible->map(function ($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'users' => $company->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        return view('company.index', compact('tree', 'nestedTree', 'companiesData'));
    }

    public function show(Company $company) {
        $user = auth()->user();
        
        // Проверка: может ли пользователь видеть эту конкретную компанию?
        // canViewCompany проверяет, входит ли компания в дерево пользователя
        abort_unless($user->canViewCompany($company), 403);

        $company->load(['owner', 'region', 'city']);
        $team = User::where('company_id', $company->id)->get();

        return view('company.show', compact('company', 'team'));
    }

    public function createForm(Request $request) {
        $user = auth()->user();
        
        // ✅ ИЗМЕНЕНО: При создании новой компании можно выбрать родителя 
        // только из СВОЕГО дерева
        $visible = $user->managedCompanies();
        
        $parentId = $request->query('parent');
        // Если parent_id передан, проверяем, что он принадлежит пользователю
        $parent = null;
        if ($parentId) {
            $parent = $visible->firstWhere('id', $parentId);
        }
        
        // Если родитель не найден или не передан, берем корневую компанию пользователя
        if (!$parent) {
            $parent = $visible->first(); 
        }

        return view('company.create', [
            'companies' => $visible,
            'parent' => $parent,
            'citiesByRegion' => $this->getCitiesArray()
        ]);
    }

         public function store(Request $request) {
        $user = auth()->user();
        $validCityIds = $this->getAllValidCityIds();

        $v = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'parent_id'    => ['nullable', 'integer'], // Убрали exists, чтобы отловить любые значения
            'company_type' => ['required', 'string', 'max:100'],
            'region_id'    => ['required', 'in:1,2,3,4,5'],
            'city_id'      => ['required', 'integer', Rule::in($validCityIds)],
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'admin_role'   => ['required', 'in:admin,employee'],
            'password'     => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
             abort(403, 'Недостаточно прав');
        }

        $newLevel = 1;
        $finalParentId = null;

        if (!empty($v['parent_id'])) {
            $parentCompany = Company::find($v['parent_id']);
            
            if ($parentCompany) {
                // Получаем ID всех компаний в дереве текущего пользователя
                $myTreeIds = $user->managedCompanies()->pluck('id')->toArray();
                
                if (in_array($parentCompany->id, $myTreeIds)) {
                    $finalParentId = $parentCompany->id;
                    $newLevel = (int)$parentCompany->level + 1; // <-- ГЛАВНЫЙ РАСЧЁТ
                } else {
                    return back()->with('error', 'Ошибка: вы не можете создавать подразделения в чужой компании.');
                }
            }
        }

        // 🔍 ОТЛАДКА: Если уровень всё равно 1, хотя parent_id был передан, мы это узнаем
        if ($newLevel === 1 && !empty($v['parent_id'])) {
            \Log::error('COMPANY LEVEL CALCULATION FAILED', [
                'parent_id_from_request' => $v['parent_id'],
                'parent_found_in_db' => $parentCompany ? 'YES (ID: '.$parentCompany->id.')' : 'NO',
                'parent_level_in_db' => $parentCompany ? $parentCompany->level : 'N/A',
                'my_tree_ids' => $user->managedCompanies()->pluck('id')->toArray(),
            ]);
        }

        $child = null;
        DB::transaction(function () use ($v, $user, $newLevel, $finalParentId, &$child) {
            // 1. Создаём Админа с тем же уровнем
            $admin = User::create([
                'name' => $v['name'],
                'email' => $v['email'],
                'password' => Hash::make($v['password']),
                'role' => $v['admin_role'],
                'level' => $newLevel, 
                'is_admin' => $v['admin_role'] === 'admin',
                'company_id' => null,
                'created_by' => $user->id,
            ]);

            // 2. Создаём Компанию с рассчитанным уровнем
            $child = Company::create([
                'name' => $v['company_name'],
                'type' => $v['company_type'],
                'level' => $newLevel, // <-- ЗДЕСЬ СОХРАНЯЕТСЯ УРОВЕНЬ
                'region_id' => $v['region_id'],
                'city_id' => $v['city_id'],
                'email' => $v['email'],
                'password' => Str::random(16),
                'status' => 'active',
                'owner_id' => $admin->id,
                'parent_id' => $finalParentId,
            ]);

            // 3. Привязываем админа к компании
            $admin->update(['company_id' => $child->id]);
        });

        // В сообщении об успехе мы явно покажем уровень
        return redirect()->route('companies.show', $child)
            ->with('success', 'Компания «' . $child->name . '» успешно создана на УРОВНЕ ' . $newLevel . '!');
    }

    public function edit(Company $company) {
        $user = auth()->user();
        abort_unless($user->canManageCompany($company), 403);

        return view('company.edit', [
            'company' => $company,
            'citiesByRegion' => $this->getCitiesArray()
        ]);
    }

    public function update(Request $request, Company $company) {
        $user = auth()->user();
        abort_unless($user->canManageCompany($company), 403);

        $validCityIds = $this->getAllValidCityIds();

        $v = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_type' => ['required', 'string', 'max:100'],
            'region_id' => ['required', 'in:1,2,3,4,5'],
            'city_id' => ['required', 'integer', Rule::in($validCityIds)],
            'company_email' => ['nullable', 'email', 'max:255'],
        ]);

        $company->update([
            'name' => $v['company_name'],
            'type' => $v['company_type'],
            'region_id' => $v['region_id'],
            'city_id' => $v['city_id'],
            'email' => $v['company_email'] ?? $company->email,
        ]);

        return redirect()->route('companies.show', $company)->with('success', 'Компания обновлена!');
    }

    public function destroy(Company $company) {
        $user = auth()->user();
        abort_unless($user->canManageCompany($company), 403);

        if (Schema::hasColumn('companies', 'parent_id') && $company->children()->exists()) {
            return back()->with('error', 'Сначала удалите или перенесите дочерние компании.');
        }

        if (User::where('company_id', $company->id)->exists()) {
             return back()->with('error', 'Нельзя удалить компанию, в которой есть сотрудники.');
        }

        DB::transaction(function () use ($company) {
            $company->delete();
        });

        return redirect()->route('companies.index')->with('success', 'Компания удалена.');
    }
}