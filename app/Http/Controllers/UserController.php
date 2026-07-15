<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index()
    {
        $authUser = auth()->user();

        // 1. Люди без компании идут на no-companies
        if (!$authUser->company_id && !$authUser->isSuperAdmin()) {
            return redirect()->route('users.no-companies');
        }

        // 2. Супер-админ видит всех
        if ($authUser->isSuperAdmin()) {
            $users = User::where('is_super_admin', false)->get();
            $companyName = 'Все компании';
        }
        // 3. Админ видит только свою компанию
        else {
            $users = User::where('company_id', $authUser->company_id)
                ->where('is_super_admin', false)
                ->get();
            $companyName = $authUser->companyRelation->name ?? 'Моя команда';
        }

        $groupedByLevel = $users->groupBy('level')->sortKeys();

        return view('users.index', compact('users', 'groupedByLevel', 'authUser', 'companyName'));
    }

    public function noCompanies()
    {
        $authUser = auth()->user();

        // Если у пользователя есть компания, ему здесь делать нечего
        if ($authUser->company_id) {
            return redirect()->route('users.index');
        }

        // Админы видят список всех пользователей без компании
        if ($authUser->isAdmin() || $authUser->isSuperAdmin()) {
            $users = User::where(function ($q) {
                $q->whereNull('company_id')->orWhere('company_id', 0);
            })
                ->where('is_super_admin', false)
                ->latest()
                ->paginate(20);

            return view('users.no-companies', compact('users', 'authUser'));
        }

        // Обычный пользователь без компании просто видит заглушку
        return view('users.no-companies', compact('authUser'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('users.index')->with('error', 'Только администратор может добавлять пользователей');
        }
        return view('users.create');
    }

    public function store(Request $request)
    {
        $authUser = auth()->user();

        if (!$authUser->isAdmin()) {
            return redirect()->route('users.index')->with('error', 'Только администратор может добавлять пользователей');
        }

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone'    => 'nullable|string|unique:users,phone', // 🔒 ДОБАВЛЕНО: уникальность телефона
            'role'     => 'required|string|max:50',
            'level'    => 'required|integer|min:2|max:20',
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // 🔒 Нормализуем телефон (убираем пробелы, скобки и т.д.)
        if (!empty($data['phone'])) {
            $data['phone'] = preg_replace('/[^0-9+]/', '', $data['phone']);
        }

        $companyId = $authUser->company_id;
        $companyName = $authUser->company;

        if (!$companyId && $companyName) {
            $company = Company::firstOrCreate(
                ['name' => $companyName],
                ['owner_id' => $authUser->id]
            );
            $companyId = $company->id;
            $authUser->update(['company_id' => $companyId]);
        }

        $data['password'] = Hash::make($data['password']);
        $data['created_by'] = $authUser->id;
        $data['company_id'] = $companyId;
        $data['company'] = $companyName;
        $data['is_admin'] = false;
        $data['is_super_admin'] = false;

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        User::create($data);

        return redirect()->route('users.index')->with('success', 'Пользователь создан');
    }

    public function show(User $user)
    {
        $authUser = auth()->user();

        // 🔒 БЕЗОПАСНОСТЬ: Усиленная проверка доступа
        if (!$this->canAccessUser($authUser, $user)) {
            abort(403, 'Нет доступа к этому пользователю');
        }

        $year = now()->year;
        $startDate = Carbon::create($year, 1, 1)->startOfWeek(Carbon::MONDAY);
        $endDate = Carbon::create($year, 12, 31)->endOfWeek(Carbon::SUNDAY);
        $weeksCount = (int)ceil($startDate->diffInDays($endDate) / 7);

        $activityData = Document::where('created_by', $user->id)
            ->whereYear('created_at', $year)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return view('users.show', compact('user', 'activityData', 'year', 'startDate', 'weeksCount'));
    }

    public function edit(User $user)
    {
        $authUser = auth()->user();

        // 🔒 БЕЗОПАСНОСТЬ: Усиленная проверка доступа
        if (!$this->canAccessUser($authUser, $user)) {
            return redirect()->route('users.index')->with('error', 'Нет доступа к этому пользователю');
        }

        // Проверка прав на редактирование
        if (!$authUser->isAdmin() && $user->id !== $authUser->id) {
            return redirect()->route('users.index')->with('error', 'Нет прав для редактирования');
        }

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $authUser = auth()->user();

        // 🔒 БЕЗОПАСНОСТЬ: Усиленная проверка доступа
        if (!$this->canAccessUser($authUser, $user)) {
            return redirect()->route('users.index')->with('error', 'Нет доступа к этому пользователю');
        }

        // Проверка прав на редактирование
        if (!$authUser->isAdmin() && $user->id !== $authUser->id) {
            return redirect()->route('users.index')->with('error', 'Нет прав для редактирования');
        }

        $rules = [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'phone'         => 'nullable|string|unique:users,phone,' . $user->id, // 🔒 Уникальность с исключением себя
            'avatar'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_avatar' => 'nullable|string|in:0,1',
        ];

        if ($authUser->isAdmin()) {
            $rules['role'] = 'required|string|max:50';
            $rules['level'] = 'required|integer|min:2|max:20';
        }

        $data = $request->validate($rules);

        if (!$authUser->isAdmin()) {
            $data['role'] = $user->role;
            $data['level'] = $user->level;
        }

        if ($request->input('remove_avatar') === '1' && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Данные обновлены');
    }

    public function destroy(User $user)
    {
        $authUser = auth()->user();

        // 🔒 БЕЗОПАСНОСТЬ: Усиленная проверка доступа
        if (!$this->canAccessUser($authUser, $user)) {
            return back()->with('error', 'Нет доступа к этому пользователю');
        }

        if ($user->id === $authUser->id) {
            return back()->with('error', 'Нельзя удалить себя');
        }

        if (!$authUser->isAdmin()) {
            return back()->with('error', 'Только администратор может удалять');
        }

        if ($user->avatar) Storage::disk('public')->delete($user->avatar);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Пользователь удалён');
    }

    // 🔒 БЕЗОПАСНОСТЬ: Универсальный метод проверки доступа к пользователю
    private function canAccessUser($authUser, $targetUser)
    {
        // Супер-админ имеет доступ ко всем
        if ($authUser->isSuperAdmin()) {
            return true;
        }

        // Админ имеет доступ только к пользователям своей компании
        if ($authUser->isAdmin()) {
            return $authUser->company_id && $targetUser->company_id === $authUser->company_id;
        }

        // Обычный пользователь имеет доступ только к себе
        return $targetUser->id === $authUser->id;
    }
}