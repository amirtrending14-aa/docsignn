<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\Notification;
use App\Observers\DocumentObserver;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter; // <-- ДОБАВЛЕНО: Для регистрации лимитов
use Illuminate\Http\Request;                 // <-- ДОБАВЛЕНО: Для работы RateLimiter
use Illuminate\Cache\RateLimiting\Limit;     // <-- ДОБАВЛЕНО: Для создания лимитов

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Carbon::setLocale('ru');

        // 🛡️ РЕГИСТРАЦИЯ ЛИМИТОВ ДЛЯ MIDDLEWARE
        // Это необходимо, чтобы маршруты с ->middleware('throttle:...') не падали с ошибкой 500.
        // Наши умные проверки через RateLimitService::check() в контроллерах продолжают работать параллельно.

        RateLimiter::for('ai-generation', function (Request $request) {
            return Limit::perMinute(3)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip() . $request->input('email', ''));
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // 🔒 БЕЗОПАСНОСТЬ: View composer только для авторизованных пользователей
        View::composer('*', function ($view) {
            if (!Auth::check()) {
                return; // 🔒 Выходим сразу, если пользователь не авторизован
            }

            $userId = Auth::id();

            // 🔒 КЭШИРОВАНИЕ: Кэшируем на 30 секунд для уменьшения нагрузки на БД
            $cacheKey = "header_notifications_{$userId}";

            $notificationData = Cache::remember($cacheKey, 30, function () use ($userId) {
                $headerNotifications = Notification::where('user_id', $userId)
                    ->latest()
                    ->take(10)
                    ->get(['id', 'type', 'messages', 'is_read', 'created_at']); // 🔒 Загружаем только нужные поля

                $unreadCount = Notification::where('user_id', $userId)
                    ->where('is_read', false)
                    ->count();

                return [
                    'headerNotifications' => $headerNotifications,
                    'unreadCount' => $unreadCount
                ];
            });

            $view->with($notificationData);
        });
    }
}