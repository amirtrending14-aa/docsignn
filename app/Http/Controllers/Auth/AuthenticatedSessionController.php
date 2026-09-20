<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Helpers\ActivityLogger;
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
        try {
            $request->authenticate();

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