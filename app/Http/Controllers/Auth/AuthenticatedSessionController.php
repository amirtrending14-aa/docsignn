<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Helpers\ActivityLogger;
use App\Services\RateLimitService; // <-- ДОБАВЛЕНО: Сервис лимитов
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        // 🛡️ ЛИМИТ: Проверка перед входом (5 попыток, далее 15 мин, 20 -> 1 час)
        // Ключ включает Email + IP для точной идентификации нарушителя
        $key = 'login:' . $request->ip() . ':' . $request->input('email');

        $check = RateLimitService::check($key, 5, [10 => 15, 15 => 60, 20 => 1440]);

        if ($check['blocked']) {
            return back()->withErrors([
                'email' => $check['message']
            ])->withInput();
        }

        try {
            $request->authenticate();

            // ✅ Успешный вход — сбрасываем счетчик попыток
            RateLimitService::reset($key);

            $request->session()->regenerate();

            $user = $request->user();
            ActivityLogger::log(
                'login',
                "Пользователь {$user->name} ({$user->email}) вошёл в систему",
                $user->id
            );

            // Супер-админ → на свой дашборд
            if ($user->is_super_admin) {
                return redirect()->route('superadmin.dashboard');
            }

            // Обычные пользователи → на обычный дашборд
            return redirect()->route('dashboard');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // ❌ Неудачная попытка — счетчик уже увеличен в check()
            // Пользователь увидит ошибку "Неверные учетные данные" или сообщение о блокировке

            // Опционально: можно добавить кастомное сообщение об оставшихся попытках
            if (!$check['blocked'] && isset($check['attempts_left'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => 'Неверные учетные данные. Осталось попыток: ' . $check['attempts_left']
                ]);
            }

            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // 📝 Логируем выход ДО logout (иначе user будет null)
        if (Auth::check()) {
            $user = Auth::user();
            ActivityLogger::log(
                'logout',
                "Пользователь {$user->name} ({$user->email}) вышел из системы",
                $user->id
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}