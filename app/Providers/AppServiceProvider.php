<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\Notification;
use App\Observers\DocumentObserver;
use App\Policies\CommentPolicy;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Observers\UserObserver;
use App\Models\Company;
use App\Observers\CompanyObserver;

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
    public function boot(): void
    {
        Company::observe(CompanyObserver::class);
        Carbon::setLocale('ru');
 User::observe(UserObserver::class);
        // 🚀 АВТОМАТИЧЕСКАЯ ИНДЕКСАЦИЯ ДОКУМЕНТОВ (ИИ-АРХИВИСТ)
        // Эта строка говорит Laravel: "Следи за моделью Document. 
        // Когда она создается (created), обновляется (updated) или удаляется (deleted), 
        // автоматически запускай методы из DocumentObserver"
        Document::observe(DocumentObserver::class);
        Gate::policy(DocumentComment::class, CommentPolicy::class);

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