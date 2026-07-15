<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Проверяем что пользователь супер-админ
        if (!$user->isSuperAdmin()) {
            abort(403, 'Доступ запрещён. Требуются права супер-администратора.');
        }

        return $next($request);
    }
}