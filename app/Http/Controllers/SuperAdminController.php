<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_companies' => Company::count(),
            'online_now' => User::where('last_seen_at', '>=', now()->subMinutes(5))->count(),
            'admins' => User::where('role', 'admin')->orWhere('role', 'super_admin')->count(),
            'documents' => Document::count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
        ];

        $recentUsers = User::with('companyRelation')->latest()->take(8)->get();
        $recentCompanies = Company::withCount('users')->latest()->take(5)->get();

        return view('superadmin.dashboard', compact('stats', 'recentUsers', 'recentCompanies'));
    }

    public function usersIndex(Request $request)
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

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'online') {
                $query->where('last_seen_at', '>=', now()->subMinutes(5));
            } elseif ($request->status === 'offline') {
                $query->where(function ($q) {
                    $q->where('last_seen_at', '<', now()->subMinutes(5))
                        ->orWhereNull('last_seen_at');
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

    public function store(Request $request)
    {
        // ИСПРАВЛЕНО: level теперь nullable и min:0. company_id тоже nullable.
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email',
            'password'          => 'required|min:6|confirmed',
            'phone'             => 'nullable|string|max:20',
            'role'              => 'required|string|in:employee,admin,super_admin',
            'level'             => 'nullable|integer|min:0|max:20', // ✅ РАЗРЕШАЕМ 0
            'company_id'        => 'nullable|exists:companies,id', // ✅ РАЗРЕШАЕМ NULL (без компании)
            'new_company_name'  => 'nullable|string|max:255',
            'avatar'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $companyId = $data['company_id'] ?? null;
        $companyName = null;

        // Логика создания новой компании на лету
        if (empty($companyId) && !empty($data['new_company_name']) && $data['role'] === 'admin') {
            $newCompany = Company::create([
                'name' => $data['new_company_name'],
            ]);
            $companyId = $newCompany->id;
            $companyName = $newCompany->name;
        } elseif ($companyId) {
            $company = Company::find($companyId);
            $companyName = $company?->name;
        }

        // Формируем данные пользователя
        $userData = [
            'name'           => $data['name'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),
            'phone'          => $data['phone'] ?? null,
            'role'           => $data['role'],
            'level'          => $data['level'] ?? 0, // ✅ Если пусто, ставим 0
            'company_id'     => $companyId,          // ✅ Будет NULL, если компания не выбрана
            'is_admin'       => in_array($data['role'], ['admin', 'super_admin']), // Авто-назначение флага
            'is_super_admin' => $data['role'] === 'super_admin',
            'created_by'     => Auth::id(),
        ];

        // Загрузка аватара
        if ($request->hasFile('avatar')) {
            $userData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Создаём пользователя
        $user = User::create($userData);

        // Если создана новая компания, назначаем пользователя её владельцем
        if ($companyId && empty($data['company_id'])) {
            Company::where('id', $companyId)->update(['owner_id' => $user->id]);
        }

        return redirect()->route('superadmin.users.index')
            ->with('success', "✅ Пользователь '{$user->name}' создан успешно! (Уровень: {$user->level}, Компания: " . ($user->company_id ? 'Да' : 'Нет') . ")");
    }

    public function edit(User $user)
    {
        $companies = Company::orderBy('name')->get();
        return view('superadmin.users.edit', compact('user', 'companies'));
    }

    public function update(Request $request, User $user)
    {
        // ИСПРАВЛЕНО: level теперь nullable и min:0
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'phone'      => 'nullable|string|max:20',
            'role'       => 'required|string|in:employee,admin,super_admin',
            'level'      => 'nullable|integer|min:0|max:20', // ✅ РАЗРЕШАЕМ 0
            'company_id' => 'nullable|exists:companies,id',  // ✅ РАЗРЕШАЕМ NULL
            'avatar'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data['is_admin'] = in_array($data['role'], ['admin', 'super_admin']);
        $data['is_super_admin'] = $data['role'] === 'super_admin';
        $data['level'] = $data['level'] ?? 0; // Гарантируем 0, если пришло пустым

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return redirect()->route('superadmin.users.index')
            ->with('success', '✅ Пользователь успешно обновлён');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', '❌ Нельзя удалить самого себя');
        }

        if ($user->is_super_admin) {
            return back()->with('error', '❌ Нельзя удалить супер-администратора через этот интерфейс');
        }

        // Безопасное удаление связи с компанией
        if ($user->companyRelation && $user->companyRelation->owner_id == $user->id) {
            $otherUsersInCompany = User::where('company_id', $user->company_id)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherUsersInCompany > 0) {
                $newOwner = User::where('company_id', $user->company_id)
                    ->where('id', '!=', $user->id)
                    ->first();
                if ($newOwner) {
                    Company::where('id', $user->company_id)->update(['owner_id' => $newOwner->id]);
                }
            } else {
                $user->companyRelation->delete();
            }
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        \App\Models\DocumentLog::where('user_id', $user->id)->delete();

        $userName = $user->name;
        $user->delete();

        return redirect()->route('superadmin.users.index')
            ->with('success', "✅ Пользователь '{$userName}' успешно удалён");
    }

    // === МЕТОДЫ ДЛЯ КОМПАНИЙ ===

    public function companiesIndex()
    {
        $companies = Company::withCount('users')->with('owner')->latest()->paginate(20);
        return view('superadmin.companies.index', compact('companies'));
    }

    public function createCompany()
    {
        return view('superadmin.companies.create');
    }

    public function storeCompany(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255|unique:companies,name',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);

        $company = Company::create($data);

        return redirect()->route('superadmin.companies.index')
            ->with('success', "✅ Компания '{$company->name}' создана успешно!");
    }

    public function showCompany(Company $company)
    {
        $users = User::where('company_id', $company->id)->withCount('documents')->get();

        $documents = Document::whereHas('creator', function ($q) use ($company) {
            $q->where('company_id', $company->id);
        })->latest()->take(20)->get();

        $stats = [
            'total_users' => $users->count(),
            'online_users' => $users->filter(fn($u) => $u->last_seen_at && $u->last_seen_at->gte(now()->subMinutes(5)))->count(),
            'total_documents' => $documents->count(),
            'admins' => $users->filter(fn($u) => in_array($u->role, ['admin', 'super_admin']))->count(),
        ];

        return view('superadmin.companies.show', compact('company', 'users', 'documents', 'stats'));
    }

    public function editCompany(Company $company)
    {
        $users = User::where('company_id', $company->id)->get();
        return view('superadmin.companies.edit', compact('company', 'users'));
    }

    public function updateCompany(Request $request, Company $company)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);

        $company->update($data);

        return redirect()->route('superadmin.companies.index')
            ->with('success', '✅ Компания обновлена');
    }

    public function destroyCompany(Company $company)
    {
        if ($company->users()->count() > 0) {
            return back()->with('error', '❌ Нельзя удалить компанию, в которой есть пользователи');
        }

        $company->delete();
        return back()->with('success', '✅ Компания удалена');
    }

    // === ПРОЧИЕ МЕТОДЫ ===

    public function activityIndex(Request $request)
    {
        $users = User::orderBy('name')->get();
        $query = \App\Models\DocumentLog::with(['user', 'document']);

        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('created_at', '<=', $request->date_to);

        $activities = $query->latest()->paginate(50);

        // ✅ ИСПРАВЛЕНО: Сначала создаем переменные, потом передаем их имена в compact
        $totalActivities = \App\Models\DocumentLog::count();
        $todayLogins = \App\Models\DocumentLog::whereDate('created_at', today())->count();
        $activeUsersCount = User::count();

        return view('superadmin.activity', compact(
            'activities',
            'users',
            'totalActivities',
            'todayLogins',
            'activeUsersCount'
        ));
    }

    public function profile()
    {
        return view('superadmin.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'phone'    => 'nullable|string|max:20',
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'password' => 'nullable|min:6|confirmed',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return back()->with('success', '✅ Профиль успешно обновлён');
    }

    public function userActivity(User $user)
    {
        $documents = Document::where('created_by', $user->id)->latest()->paginate(30);
        return view('superadmin.user-activity', compact('user', 'documents'));
    }
}