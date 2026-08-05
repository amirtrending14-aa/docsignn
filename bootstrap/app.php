<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Глобальные middleware
        $middleware->web(append: [
            \App\Http\Middleware\UpdateUserOnlineStatus::class,
        ]);

        // ✅ РЕГИСТРИРУЕМ АЛИАСЫ
        $middleware->alias([
            'superadmin' => \App\Http\Middleware\CheckSuperAdmin::class,
            'superadmin.protection' => \App\Http\Middleware\SuperAdminProtection::class,
            'last.seen' => \App\Http\Middleware\UpdateUserOnlineStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();