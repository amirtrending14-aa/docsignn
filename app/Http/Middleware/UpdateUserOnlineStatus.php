<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserOnlineStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // ✅ ПРАВИЛЬНАЯ проверка: обновляем раз в минуту
            $shouldUpdate = !$user->last_seen_at ||
                $user->last_seen_at->lt(now()->subMinute());

            if ($shouldUpdate) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['last_seen_at' => now()]);
            }
        }

        return $next($request);
    }
}