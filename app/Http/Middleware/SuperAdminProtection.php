<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DocumentLog;
use App\Models\User;

class SuperAdminProtection
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // 🔒 Проверка: есть ли больше одного супер-админа
        $superAdminsCount = User::where('is_super_admin', true)->count();

        if ($superAdminsCount > 1) {
            \Log::critical('🚨 ОБНАРУЖЕН ВТОРОЙ СУПЕР-АДМИН! IP: ' . $request->ip());
            abort(403, 'Доступ запрещён. Обнаружена подозрительная активность.');
        }

        // 🔒 Логируем все действия супер-админа
        try {
            DocumentLog::create([
                'document_id' => null,
                'user_id' => $user->id,
                'action' => 'super_admin_access',
                'description' => "Доступ к супер-админ панели. URL: {$request->fullUrl()} | IP: {$request->ip()}"
            ]);
        } catch (\Exception $e) {
            \Log::error("Ошибка логирования: " . $e->getMessage());
        }

        return $next($request);
    }
}