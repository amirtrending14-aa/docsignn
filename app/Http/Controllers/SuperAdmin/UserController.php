<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('companyRelation');

        // Фильтр по поиску
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Фильтр по компании
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Фильтр по статусу (онлайн/офлайн)
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

    public function noCompanies()
    {
        $users = User::where(function($q) {
            $q->whereNull('company_id')
                ->orWhere('company_id', 0);
        })
            ->where('is_super_admin', false)
            ->latest()
            ->paginate(20);

        return view('superadmin.users.no-companies', compact('users'));
    }

    public function store(Request $request)
    {
        // ✅ ИСПРАВЛЕНО: min:0 вместо min:1
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email',
            'password'          => 'required|min:6|confirmed',
            'phone'             => 'nullable|string|max:20',
            'role'              => 'required|in:employee,admin,super_admin',
            'level'             => 'required|integer|min:0|max:20', // ✅ ТЕПЕРЬ 0 РАЗРЕШЕН
            'company_id'        => 'nullable|exists:companies,id',
            'new_company_name'  => 'nullable|string|max:255',
            'is_admin'          => 'nullable|boolean',
            'avatar'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $companyId = $data['company_id'] ?? null;
        $companyName = null;

        // Если выбрана существующая компания
        if ($companyId) {
            $company = Company::find($companyId);
            $companyName = $company->name;
        }
        // Если введено название новой компании
        elseif (!empty($data['new_company_name'])) {
            $company = Company::create([
                'name' => $data['new_company_name'],
                'email' => $data['email'] ?? null,
                'owner_id' => null,
            ]);
            $companyId = $company->id;
            $companyName = $company->name;
        }

        // ✅ ИСПРАВЛЕНО: Удалено поле 'company', так как в БД используется только company_id
        $userData = [
            'name'           => $data['name'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),
            'phone'          => $data['phone'] ?? null,
            'role'           => $data['role'],
            'level'          => $data['level'], // Здесь теперь спокойно придет 0
            'company_id'     => $companyId,
            'is_admin'       => $data['role'] === 'admin' || ($data['is_admin'] ?? false),
            'is_super_admin' => $data['role'] === 'super_admin',
        ];

        if ($request->hasFile('avatar')) {
            $userData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($userData);

        // Если создана новая компания, назначаем пользователя владельцем
        if ($companyId && empty($data['company_id'])) {
            Company::find($companyId)->update(['owner_id' => $user->id]);
        }

        return redirect()->route('superadmin.users.index')
            ->with('success', '✅ Пользователь "' . $user->name . '" создан успешно! (Уровень: ' . $user->level . ')');
    }

    public function edit(User $user)
    {
        $companies = Company::orderBy('name')->get();
        return view('superadmin.users.edit', compact('user', 'companies'));
    }

    public function update(Request $request, User $user)
    {
        // ✅ ИСПРАВЛЕНО: min:0 вместо min:1
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email,' . $user->id,
            'password'          => 'nullable|min:6|confirmed',
            'phone'             => 'nullable|string|max:20',
            'role'              => 'required|in:employee,admin,super_admin',
            'level'             => 'required|integer|min:0|max:20', // ✅ ТЕПЕРЬ 0 РАЗРЕШЕН
            'company_id'        => 'nullable|exists:companies,id',
            'is_admin'          => 'nullable|boolean',
            'avatar'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_avatar'     => 'nullable|boolean',
        ]);

        $companyId = $data['company_id'] ?? null;
        $companyName = null;

        if ($companyId) {
            $company = Company::find($companyId);
            $companyName = $company->name;
        }

        // ✅ ИСПРАВЛЕНО: Удалено поле 'company'
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

        if (!empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        if ($request->input('remove_avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $userData['avatar'] = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $userData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($userData);

        return redirect()->route('superadmin.users.index')
            ->with('success', '✅ Пользователь успешно обновлён');
    }

    public function destroy(User $user)
    {
        // Защита от удаления самого себя
        if ($user->id === auth()->id()) {
            return back()->with('error', '❌ Нельзя удалить самого себя');
        }

        // Защита от удаления супер-админа через этот интерфейс
        if ($user->is_super_admin) {
            return back()->with('error', '❌ Нельзя удалить супер-администратора');
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('superadmin.users.index')
            ->with('success', "✅ Пользователь '{$userName}' успешно удалён");
    }

    public function userActivity($id)
    {
        $user = User::findOrFail($id);
        $users = User::orderBy('name')->get();

        // Используем DocumentLog или Activity, в зависимости от твоей модели
        $activities = \App\Models\DocumentLog::where('user_id', $id)
            ->with('document')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('superadmin.activity', compact('user', 'users', 'activities'));
    }
}