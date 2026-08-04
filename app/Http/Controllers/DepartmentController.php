<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    private const MAX_USERS_PER_DEPARTMENT = 10;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();

            if (!$user || !($user->is_admin || $user->is_super_admin)) {
                abort(403, 'Доступ запрещён: требуются права администратора.');
            }

            return $next($request);
        })->only(['create', 'store', 'edit', 'update', 'destroy', 'assignUser', 'removeUser']);
    }

    // =========================================================
    //  СПИСОК ОТДЕЛОВ (ДЕРЕВО)
    // =========================================================
    public function index()
    {
        $user      = auth()->user();
        $companyId = $user->company_id;

        $query = Department::with(['parent', 'users', 'children']);

        if (!$user->is_super_admin) {
            $query->where('company_id', $companyId);
        }

        $departments = $query->orderBy('level')->orderBy('name')->get();
        $tree        = $this->buildTree($departments);
        $levelNames  = Department::levelNames();

        return view('departments.index', compact('departments', 'tree', 'levelNames'));
    }

    // =========================================================
    //  ПРОСМОТР ОТДЕЛА
    // =========================================================
    public function show(Department $department)
    {
        $user = auth()->user();
        $this->authorizeCompany($user, $department);

        $department->load(['users', 'parent', 'children.users', 'children.children']);

        $levelNames = Department::levelNames();
        $ancestors  = $this->getAncestors($department);

        // Пользователи для привязки (только своей компании)
        $companyUsers = $this->getAvailableUsers($user, $department);

        // Сколько мест свободно
        $currentCount = $department->users()->count();
        $remainingSlots = self::MAX_USERS_PER_DEPARTMENT - $currentCount;

        return view('departments.show', compact(
            'department',
            'levelNames',
            'ancestors',
            'companyUsers',
            'remainingSlots'
        ));
    }

    // =========================================================
    //  ФОРМА СОЗДАНИЯ
    // =========================================================
    public function create()
    {
        $user       = auth()->user();
        $levelNames = Department::levelNames();
        $tree       = Department::getTree($user->company_id);

        return view('departments.create', compact('levelNames', 'tree'));
    }

    // =========================================================
    //  СОХРАНИТЬ ОТДЕЛ
    // =========================================================
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'level'       => 'required|integer|min:1|max:20',
            'parent_id'   => 'nullable|exists:departments,id',
            'icon'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
        ]);

        // Привязка к компании
        $validated['company_id'] = $user->company_id;

        // Валидация родителя
        $this->validateParent($validated, $user->company_id);

        // Дефолтные значения
        $validated['icon']  = $validated['icon']  ?? '🏢';
        $validated['color'] = $validated['color'] ?? Department::levelColor($validated['level']);

        Department::create($validated);

        return redirect()
            ->route('departments.index')
            ->with('success', 'Отдел успешно создан!');
    }

    // =========================================================
    //  ФОРМА РЕДАКТИРОВАНИЯ
    // =========================================================
    public function edit(Department $department)
    {
        $user = auth()->user();
        $this->authorizeCompany($user, $department);

        $levelNames = Department::levelNames();
        $tree       = Department::getTree($user->company_id, null, 0, $department->id);

        return view('departments.edit', compact('department', 'levelNames', 'tree'));
    }

    // =========================================================
    //  ОБНОВИТЬ ОТДЕЛ
    // =========================================================
    public function update(Request $request, Department $department)
    {
        $user = auth()->user();
        $this->authorizeCompany($user, $department);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'level'       => 'required|integer|min:1|max:20',
            'parent_id'   => 'nullable|exists:departments,id',
            'icon'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
        ]);

        // Валидация родителя + запрет быть родителем самому себе
        $this->validateParent($validated, $user->company_id, $department->id);

        $department->update($validated);

        return redirect()
            ->route('departments.show', $department)
            ->with('success', 'Отдел обновлён!');
    }

    // =========================================================
    //  УДАЛИТЬ ОТДЕЛ
    // =========================================================
    public function destroy(Department $department)
    {
        $user = auth()->user();
        $this->authorizeCompany($user, $department);

        if ($department->users()->exists()) {
            return back()->withErrors([
                'error' => 'Нельзя удалить отдел с сотрудниками. Сначала переведите или удалите их.',
            ]);
        }

        if ($department->children()->exists()) {
            return back()->withErrors([
                'error' => 'Нельзя удалить отдел с дочерними отделами.',
            ]);
        }

        $name = $department->name;
        $department->delete();

        return redirect()
            ->route('departments.index')
            ->with('success', "Отдел «{$name}» удалён.");
    }

    // =========================================================
    //  ДОБАВИТЬ ПОЛЬЗОВАТЕЛЯ В ОТДЕЛ
    //  — НЕТ выбора должности
    //  — Роль присваивается АВТОМАТИЧЕСКИ
    //  — Максимум 10 пользователей
    // =========================================================
    public function assignUser(Request $request, Department $department)
    {
        $user = auth()->user();
        $this->authorizeCompany($user, $department);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Лимит 10
        if ($department->users()->count() >= self::MAX_USERS_PER_DEPARTMENT) {
            return back()->withErrors([
                'error' => 'Достигнут лимит: максимум ' . self::MAX_USERS_PER_DEPARTMENT . ' сотрудников.',
            ]);
        }

        $targetUser = $this->findTargetUser($user, $validated['user_id']);

        if (!$targetUser) {
            return back()->withErrors(['user_id' => 'Сотрудник не найден или не из вашей компании.']);
        }

        if ($department->users()->where('users.id', $targetUser->id)->exists()) {
            return back()->withErrors(['user_id' => 'Этот сотрудник уже в отделе.']);
        }

        // ✅ Просто привязка. Роль пользователя НЕ меняем!
        $department->users()->attach($targetUser->id);

        return back()->with('success', "Сотрудник «{$targetUser->name}» добавлен в отдел.");
    }

    // =========================================================
    //  УБРАТЬ ПОЛЬЗОВАТЕЛЯ ИЗ ОТДЕЛА
    // =========================================================
    public function removeUser(Department $department, int $userId)
    {
        $user = auth()->user();
        $this->authorizeCompany($user, $department);

        $targetUser = $this->findTargetUser($user, $userId);

        if (!$targetUser) {
            return back()->withErrors(['error' => 'Сотрудник не найден или нет прав.']);
        }

        $department->users()->detach($userId);

        return back()->with('success', "Сотрудник «{$targetUser->name}» удалён из отдела.");
    }

    // =========================================================
    //  PRIVATE HELPERS
    // =========================================================

    /**
     * Проверка прав на отдел (принадлежность к компании)
     */
    private function authorizeCompany($user, Department $department): void
    {
        if (!$user->is_super_admin && $department->company_id !== $user->company_id) {
            abort(403, 'У вас нет прав на управление этим отделом.');
        }
    }

    /**
     * Валидация родительского отдела
     */
    private function validateParent(array &$validated, ?int $companyId, ?int $excludeId = null): void
    {
        if (!empty($validated['parent_id'])) {
            $parentQuery = Department::where('company_id', $companyId);

            if ($excludeId) {
                $parentQuery->where('id', '!=', $excludeId);
            }

            $parent = $parentQuery->find($validated['parent_id']);

            if (!$parent) {
                abort(422, 'Родительский отдел не найден или недоступен.');
            }

            if ($parent->level != $validated['level'] - 1) {
                abort(422, "Родитель должен быть уровня " . ($validated['level'] - 1) . ".");
            }
        } else {
            // Без родителя → уровень 1
            $validated['level'] = 1;
        }
    }

    /**
     * Поиск целевого пользователя (с учётом компании)
     */
    private function findTargetUser($admin, int $userId): ?User
    {
        if ($admin->is_super_admin) {
            return User::find($userId);
        }

        return User::where('id', $userId)
            ->where('company_id', $admin->company_id)
            ->first();
    }

    /**
     * Получить доступных пользователей для привязки
     */
    private function getAvailableUsers($user, Department $department)
    {
        $query = User::query();

        if (!$user->is_super_admin) {
            $query->where('company_id', $user->company_id);
        }

        // Исключаем уже привязанных к этому отделу
        $attachedIds = $department->users()->pluck('users.id')->toArray();

        if (!empty($attachedIds)) {
            $query->whereNotIn('id', $attachedIds);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Построение дерева
     */
    private function buildTree($departments, ?int $parentId = null): array
    {
        $result = [];

        foreach ($departments as $dept) {
            if ($dept->parent_id === $parentId) {
                $dept->childrenTree = $this->buildTree($departments, $dept->id);
                $result[] = $dept;
            }
        }

        return $result;
    }

    /**
     * Хлебные крошки (предки отдела)
     */
    private function getAncestors(Department $department)
    {
        $ancestors = collect();
        $current   = $department->parent;

        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }

        return $ancestors;
    }
}