<?php

namespace App\Http\Middleware;

use App\Models\Attendance;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFaceCheckin
{
    /**
     * Проверяет, прошёл ли пользователь Face ID сканирование сегодня.
     * 
     * Логика:
     * - on_time / late        → считается всегда (реальный приход есть)
     * - excused с check_in_time → считается (пришёл + админ снял штраф)
     * - excused БЕЗ check_in_time → НЕ считается (админ нажал "Я знал" заранее,
     *                              но сотрудник ещё должен отсканироваться)
     * - absent                → НЕ считается (это отсутствие)
     * 
     * Если нет ни одной "засчитанной" отметки за сегодня — редирект на скан.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Пропускаем гостей и тех, кому скан не нужен
        if (! $user || ! $user->needs_face_scan) {
            return $next($request);
        }

        // 2. Пропускаем страницу скана и выход из системы
        if ($request->routeIs('face.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        // 3. Проверяем: есть ли ЗАЧТЁННАЯ отметка за сегодня
        $done = $this->hasValidAttendanceToday($user->id);

        // 4. Если отметки нет — кидаем на сканер
        if (! $done) {
            return redirect()->route('face.scan');
        }

        return $next($request);
    }

    /**
     * Проверяет, есть ли у пользователя действительная отметка посещения за сегодня.
     */
    private function hasValidAttendanceToday(int $userId): bool
    {
        return Attendance::where('user_id', $userId)
            ->whereDate('date', now()->toDateString())
            ->where(function ($q) {
                // Реальный приход: вовремя или опоздал
                $q->whereIn('status', [
                    Attendance::STATUS_ON_TIME,
                    Attendance::STATUS_LATE,
                ])
                // Или: админ снял штраф ПОСЛЕ фактического прихода
                ->orWhere(function ($q2) {
                    $q2->where('status', Attendance::STATUS_EXCUSED)
                       ->whereNotNull('check_in_time');
                });
            })
            ->exists();
    }
}