<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            // Если супер-админ пытается зайти на обычный профиль
            if ($request->is('profile*') && !$request->is('super-admin/profile*')) {
                return redirect()->route('superadmin.profile');
            }
        }

        return $next($request);
    }
}