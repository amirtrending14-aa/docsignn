<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\SuperAdmin\CompanyController as SuperAdminCompanyController;
use App\Http\Controllers\Admin\AvatarController;
use App\Http\Controllers\StrelController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentCommentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentLogController;
use App\Http\Controllers\DocumentSignatureController;
use App\Http\Controllers\DocumentWorkflowController;
use App\Http\Controllers\DocumentVersionController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\AIController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| 1. ПУБЛИЧНЫЕ МАРШРУТЫ (без авторизации)
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => view('layouts.site'))->name('site.home');
Route::get('/site', fn() => view('layouts.site'))->name('site.main');

// 🔒 Публичная проверка подлинности документа по коду
Route::get('/verify/{code}', [DocumentSignatureController::class, 'verify'])
    ->middleware('throttle:30,1') // 30 проверок в минуту
    ->name('document.verify');

/*
|--------------------------------------------------------------------------
| 2. АУТЕНТИФИКАЦИЯ
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| 3. ЛОКАЛЬНЫЕ МАРШРУТЫ (только для разработки)
|--------------------------------------------------------------------------
*/
if (app()->environment('local')) {
    // 🔒 Переключение пользователя ТОЛЬКО с localhost
    Route::post('/login-as', function (Request $request) {
        // Строгая проверка IP
        if (!in_array($request->ip(), ['127.0.0.1', '::1'])) {
            \Log::warning('🚨 Попытка login-as с незнакомого IP: ' . $request->ip());
            abort(403, 'Доступ запрещён');
        }

        // 🔒 Валидация user_id
        $request->validate([
            'user_id' => 'required|integer|exists:users,id'
        ]);

        Auth::loginUsingId($request->user_id);

        \Log::info('✅ Login-as выполнен: ' . Auth::user()->name . ' (IP: ' . $request->ip() . ')');

        return back()->with('success', 'Переключено на пользователя: ' . Auth::user()->name);
    })->name('login.as');

    // 🔒 Тест AI только локально
    Route::post('/ai/test', fn() => response()->json([
        'status' => 'success',
        'message' => 'Роут работает!',
        'needs_questions' => false,
        'document_data' => [
            'number' => '№ 123',
            'type' => 'Договор аренды',
            'title' => 'Тестовый документ',
            'content' => 'Это тестовый контент от ИИ',
            'deadline' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'draft',
        ],
        'download_url' => null,
    ]))->name('ai.test');

    // 🔒 Тест email только локально
    Route::get('/test-email', function () {
        try {
            \Illuminate\Support\Facades\Mail::raw('Тестовое письмо от DocSign', function ($message) {
                $message->to('test@example.com')->subject('Тест email из Laravel');
            });
            return response()->json(['status' => 'success', 'message' => 'Email отправлен!']);
        } catch (\Exception $e) {
            \Log::error('Ошибка отправки тестового email: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    })->name('test.email');
}

/*
|--------------------------------------------------------------------------
| 4. АВТОРИЗОВАННЫЕ ПОЛЬЗОВАТЕЛИ
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'last.seen', 'verified'])->group(function () {

    // ============================================
    // DASHBOARD & ANALYSIS
    // ============================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])
        ->middleware('throttle:60,1')
        ->name('dashboard.chart-data');
    Route::get('/analysis', [AnalysisController::class, 'index'])->name('analysis.index');

    // ============================================
    // PROFILE
    // ============================================
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->middleware('throttle:3,1')
        ->name('profile.destroy');

    // ============================================
    // SETTINGS
    // ============================================
    Route::get('/setting', fn() => view('settings.index'))->name('settings');
    Route::post('/settings/signature', [ProfileController::class, 'updateSignature'])
        ->middleware('throttle:10,1')
        ->name('settings.signature.update');
    Route::post('/settings/general', [ProfileController::class, 'updateGeneral'])
        ->middleware('throttle:10,1')
        ->name('settings.general.update');
    Route::put('/settings/edi', [SettingsController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('settings.update');

    // ============================================
    // DOCUMENTS (🔒 IDOR Protection через Route Model Binding)
    // ============================================
    Route::get('/documents/{document}/download-pdf', [DocumentController::class, 'downloadPdf'])
        ->middleware('throttle:10,1')
        ->name('documents.downloadPdf');

    Route::get('/documents/{document}/download-word', [DocumentController::class, 'downloadWord'])
        ->middleware('throttle:10,1')
        ->name('documents.downloadWord');

    Route::post('/documents/ai-process', [DocumentController::class, 'storeFromPdf'])
        ->middleware('throttle:10,1')
        ->name('documents.ai-process');

    Route::post('/documents/{document}/sign', [DocumentController::class, 'sign'])
        ->middleware('throttle:signing')
        ->name('documents.sign');

    Route::post('/documents/{document}/reject', [DocumentController::class, 'reject'])
        ->middleware('throttle:10,1')
        ->name('documents.reject');

    Route::resource('documents', DocumentController::class);
    Route::resource('signatures', DocumentSignatureController::class);
    Route::resource('versions', DocumentVersionController::class);

    // ============================================
    // DOCUMENT LOGS
    // ============================================
    Route::resource('logs', DocumentLogController::class);
    Route::post('/logs/clear', [DocumentLogController::class, 'clear'])
        ->middleware('throttle:3,1')
        ->name('logs.clear');
    Route::get('/documents/{document}/logs', [DocumentLogController::class, 'documentLogs'])
        ->name('logs.document');

    Route::resource('workflow', DocumentWorkflowController::class);

    // ============================================
    // USERS (🔒 Защита от IDOR через Policies)
    // ============================================
    Route::get('/users/no-companies', [UserController::class, 'noCompanies'])
        ->name('users.no-companies');
    Route::resource('users', UserController::class);

    // ============================================
    // SEARCH
    // ============================================
    Route::get('/search', [SearchController::class, 'index'])
        ->middleware('throttle:30,1')
        ->name('search');

    // 🔒 Защита от перебора email (User Enumeration)
    Route::get('/api/users/search', function (Request $request) {
        // 🔒 Валидация email
        $request->validate(['email' => 'required|email']);

        $user = \App\Models\User::where('email', $request->email)->first();

        return response()->json([
            'exists' => !!$user,
            // 🔒 НЕ отдаём имя для защиты приватности
        ]);
    })->middleware('throttle:10,1')->name('users.search_api');

    // ============================================
    // COMMENTS (🔒 IDOR Protection)
    // ============================================
    Route::post('/comments', [DocumentCommentController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('comments.store');
    Route::get('/documents/{document}/comments', [DocumentCommentController::class, 'index'])
        ->name('comments.index');
    Route::delete('/comments/{comment}', [DocumentCommentController::class, 'destroy'])
        ->middleware('throttle:10,1')
        ->name('comments.destroy');

    // ============================================
    // NOTIFICATIONS (🔒 IDOR Protection)
    // ============================================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/create', [NotificationController::class, 'create'])->name('create');
        Route::get('/check', [NotificationController::class, 'checkNew'])
            ->middleware('throttle:60,1')
            ->name('check');
        Route::any('/{notification}/read', [NotificationController::class, 'read'])->name('read');
        Route::patch('/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('read_patch');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])
            ->middleware('throttle:10,1')
            ->name('destroy');
        Route::delete('/clear-all', [NotificationController::class, 'clearAll'])
            ->middleware('throttle:3,1')
            ->name('clearAll');
        Route::post('/read-all', [NotificationController::class, 'readAll'])
            ->middleware('throttle:10,1')
            ->name('readAll');
    });
    Route::post('/comments/store_notification', [NotificationController::class, 'store'])
        ->middleware('throttle:forms')
        ->name('comments.store_notification');

    // ============================================
    // MESSAGES (🔒 IDOR Protection)
    // ============================================
    Route::resource('messages', MessageController::class);

    // ============================================
    // AI (🔒 Строгий Rate Limiting)
    // ============================================
    Route::post('/ai/generate-document', [AIController::class, 'generateDocument'])
        ->middleware('throttle:ai-generation')
        ->name('ai.generate-document');

    // ============================================
    // HEARTBEAT (🔒 Защита от спама)
    // ============================================
    Route::get('/heartbeat', function () {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error'], 401);
        }

        DB::table('users')->where('id', $user->id)->update(['last_seen_at' => now()]);
        return response()->json(['status' => 'ok', 'online' => true]);
    })->middleware('throttle:60,1')->name('heartbeat');

    // ============================================
    // STREL
    // ============================================
    Route::get('/strel', [StrelController::class, 'index'])->name('strel.index');
});

/*
|--------------------------------------------------------------------------
| 5. СУПЕР-АДМИН (🔒 МАКСИМАЛЬНАЯ ЗАЩИТА)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'superadmin', 'last.seen'])
    ->prefix('super-admin')
    ->name('superadmin.')
    ->group(function () {

        // Dashboard
        Route::get('/', [SuperAdminController::class, 'index'])->name('dashboard');

        // ============================================
        // USERS MANAGEMENT
        // ============================================
        Route::get('users/no-companies', [SuperAdminUserController::class, 'noCompanies'])
            ->name('users.no-companies');

        Route::get('users/export', [SuperAdminUserController::class, 'exportUsers'])
            ->middleware('throttle:5,1') // 🔒 Защита экспорта
            ->name('users.export');

        Route::post('users/bulk-delete', [SuperAdminUserController::class, 'bulkDelete'])
            ->middleware('throttle:3,1') // 🔒 Строгая защита массового удаления
            ->name('users.bulk');

        Route::resource('users', SuperAdminUserController::class);

        Route::post('users/{user}/reset-password', [SuperAdminUserController::class, 'resetPassword'])
            ->middleware('throttle:5,1')
            ->name('users.reset-password');

        Route::post('users/{user}/toggle-status', [SuperAdminUserController::class, 'toggleStatus'])
            ->middleware('throttle:10,1')
            ->name('users.toggle-status');

        Route::get('users/{user}/activity', [SuperAdminUserController::class, 'userActivity'])
            ->name('users.activity');

        // ============================================
        // COMPANIES MANAGEMENT
        // ============================================
        Route::resource('companies', SuperAdminCompanyController::class);

        // ============================================
        // ACTIVITY LOGS
        // ============================================
        Route::get('activity', [ActivityController::class, 'index'])->name('activity');

        // ============================================
        // PROFILE
        // ============================================
        Route::get('profile', [SuperAdminController::class, 'profile'])->name('profile');
        Route::put('profile', [SuperAdminController::class, 'updateProfile'])
            ->middleware('throttle:10,1')
            ->name('profile.update');
    });

/*
|--------------------------------------------------------------------------
| 6. СЛУЖЕБНЫЕ МАРШРУТЫ
|--------------------------------------------------------------------------
*/
// 🔒 Защищённый маршрут обновления dashboard
Route::get('/dashboard-update', fn() => 'Данные обновлены успешно!')
    ->middleware(['auth', 'throttle:60,1'])
    ->name('dashboard.update');

/*
|--------------------------------------------------------------------------
| 7. FALLBACK МАРШРУТ (404)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    // 🔒 Логируем все 404 ошибки для анализа атак
    \Log::warning('404 Not Found: ' . request()->fullUrl() . ' | IP: ' . request()->ip());

    if (request()->wantsJson()) {
        return response()->json(['error' => 'Not Found'], 404);
    }

    abort(404);
<<<<<<< HEAD
});
=======

});




Route::get('/documents/{id}/preview', [DocumentController::class, 'previewPdf'])->name('documents.preview');
// В routes/web.php
Route::get('/documents/{id}/pdf', [DocumentController::class, 'pdf'])->name('documents.pdf');
Route::get('/documents/{id}/debug', [DocumentController::class, 'debugFilePath']);

>>>>>>> cdd84ef (model)
