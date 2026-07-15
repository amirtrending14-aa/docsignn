<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\User;
use App\Helpers\ActivityLogger;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\Company;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // role теперь НЕ обязательное поле
            'role' => ['nullable', 'string', 'in:admin,employee,director,users'],
        ]);

        // Если роль не передана - устанавливаем 'users' по умолчанию (простой пользователь без компании)
        $role = $request->role ?? 'users';

        // Если это простой пользователь (без компании)
        if ($role === 'users') {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'users',
                'level' => 0, // простой пользователь
                'company_id' => null,
                'company' => null,
                'created_by' => null,
            ]);

            event(new Registered($user));

            // 📝 Логируем регистрацию простого пользователя
            ActivityLogger::log(
                'user_created',
                "Зарегистрирован новый пользователь: {$user->name} ({$user->email}) — роль: {$role}",
                $user->id
            );

            Auth::login($user);

            // Редирект на страницу для пользователей без компании
            return redirect()->route('users.no-companies');
        }

        // Для остальных ролей (admin, employee, director) - создаём компанию
        $companyName = $request->name . "'s Team";

        $company = Company::create([
            'name' => $companyName,
            'owner_id' => null,
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'level' => 1,
            'company_id' => $company->id,
            'company' => $companyName,
            'created_by' => null,
        ]);

        $company->update([
            'owner_id' => $user->id,
        ]);

        event(new Registered($user));

        // 📝 Логируем регистрацию пользователя с компанией
        Activity::log(
            'user_created',
            "Зарегистрирован новый пользователь: {$user->name} ({$user->email}) — роль: {$role}, компания: {$companyName}",
            $user->id
        );

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}