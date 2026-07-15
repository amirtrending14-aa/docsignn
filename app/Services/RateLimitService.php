<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class RateLimitService
{
    /**
     * Проверка лимита с прогрессивной блокировкой
     *
     * @param string $key Уникальный ключ (например, "login:192.168.1.1:user@example.com")
     * @param int $maxAttempts Максимально попыток до блокировки
     * @param array $escalation Прогрессия блокировки [попытки => минуты]
     * @return array Результат проверки
     */
    public static function check(string $key, int $maxAttempts, array $escalation): array
    {
        $attempts = Cache::get($key . ':attempts', 0);
        $blockedUntil = Cache::get($key . ':blocked_until');

        // Проверяем, заблокирован ли пользователь
        if ($blockedUntil && now()->timestamp < $blockedUntil) {
            $secondsLeft = $blockedUntil - now()->timestamp;
            return [
                'blocked' => true,
                'message' => 'Слишком много попыток. Подождите ' . ceil($secondsLeft / 60) . ' минут.',
                'retry_after' => $secondsLeft
            ];
        }

        // Если блокировка закончилась — сбрасываем
        if ($blockedUntil && now()->timestamp >= $blockedUntil) {
            Cache::forget($key . ':attempts');
            Cache::forget($key . ':blocked_until');
            $attempts = 0;
        }

        // Увеличиваем счётчик попыток
        $attempts++;
        Cache::put($key . ':attempts', $attempts, now()->addDay());

        // Проверяем, нужно ли блокировать
        if ($attempts >= $maxAttempts) {
            // Находим уровень блокировки
            $blockMinutes = 5; // по умолчанию
            foreach ($escalation as $threshold => $minutes) {
                if ($attempts >= $threshold) {
                    $blockMinutes = $minutes;
                }
            }

            // Блокируем
            Cache::put($key . ':blocked_until', now()->addMinutes($blockMinutes)->timestamp, now()->addDay());

            return [
                'blocked' => true,
                'message' => 'Слишком много попыток. Подождите ' . $blockMinutes . ' минут.',
                'retry_after' => $blockMinutes * 60
            ];
        }

        return [
            'blocked' => false,
            'attempts_left' => $maxAttempts - $attempts
        ];
    }

    /**
     * Сброс счётчика (при успехе)
     */
    public static function reset(string $key): void
    {
        Cache::forget($key . ':attempts');
        Cache::forget($key . ':blocked_until');
    }
}