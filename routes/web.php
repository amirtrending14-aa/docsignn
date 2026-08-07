<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AIChatController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DocumentRecipientController;
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
use App\Http\Controllers\CompanyRegistrationController; 
use App\Http\Controllers\CompanyTreeController;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\CompanySettingsController;

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
    Route::post('/contracts/test', fn() => response()->json([
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
    ]))->name('contracts.test');

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
Route::middleware(['auth', 'last.seen'])->group(function () {
  Route::get('/documents/select-by-department', [DocumentController::class, 'selectByDepartment'])
        ->name('documents.select-by-department');
 Route::get('/documents/select-by-company', [DocumentController::class, 'selectByCompany'])
        ->name('documents.select-by-company');
        
    Route::post('/documents/select-by-company', [DocumentController::class, 'storeCompanySelection'])
        ->name('documents.select-by-company.store');
    Route::post('/documents/select-by-department', [DocumentController::class, 'storeDepartmentSelection'])
        ->name('documents.select-by-department.store');
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

    Route::post('/documents/contracts-process', [DocumentController::class, 'storeFromPdf'])
        ->middleware('throttle:10,1')
        ->name('documents.contracts-process');

    Route::post('/documents/{document}/sign', [DocumentController::class, 'sign'])
        ->middleware('throttle:signing')
        ->name('documents.sign');

    Route::post('/documents/{document}/reject', [DocumentController::class, 'reject'])
        ->middleware('throttle:10,1')
        ->name('documents.reject');
// Временная загрузка файла (AJAX) — внутри группы auth
    Route::post('/documents/upload-temp', [DocumentController::class, 'uploadTemp'])->name('documents.upload-temp');

    Route::resource('documents', DocumentController::class);
    Route::get('/documents/recipients/available', [DocumentController::class, 'getAvailableRecipients'])
    ->name('documents.recipients.available');

Route::post('/documents/recipients/store-region', [DocumentController::class, 'storeRecipientsByRegion'])
    ->name('documents.recipients.store-region');
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
});

Route::middleware(['auth', 'last.seen'])->group(function () {
    Route::get('/documents/select-by-department', [DocumentController::class, 'selectByDepartment'])
        ->name('documents.select-by-department');

    Route::post('/documents/select-by-department', [DocumentController::class, 'storeDepartmentSelection'])
        ->name('documents.select-by-department.store');
});

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
    Route::post('/contracts/generate-document', [AIController::class, 'generateDocument'])
        ->middleware('throttle:contracts-generation')
        ->name('contracts.generate-document');
// Алиас для старого адреса (чтобы кэш браузера не ломал работу)
Route::post('/ai/generate-document', [AIController::class, 'generateDocument'])
    ->middleware('throttle:contracts-generation')
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
    Route::prefix('documents/recipients')->name('recipients.')->group(function () {
        Route::get('/', [DocumentRecipientController::class, 'index'])->name('select');
        Route::get('/cities/{region}', [DocumentRecipientController::class, 'getCities'])->name('cities');
        Route::get('/organizations/{city}', [DocumentRecipientController::class, 'getOrganizations'])->name('organizations');
        Route::get('/users/{organization}', [DocumentRecipientController::class, 'getUsers'])->name('users');
        Route::post('/store', [DocumentRecipientController::class, 'store'])->name('store');
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

});






Route::get('/documents/{id}/preview', [DocumentController::class, 'previewPdf'])->name('documents.preview');
// В routes/web.php
Route::get('/documents/{id}/pdf', [DocumentController::class, 'pdf'])->name('documents.pdf');
Route::get('/documents/{id}/debug', [DocumentController::class, 'debugFilePath']);
Route::get('/contracts-assistant', [AIController::class, 'index'])->name('contracts.index');

// API для отправки сообщений (которое мы писали выше)
Route::post('/contracts/chat', [AIController::class, 'chat'])->name('contracts.chat');


Route::prefix('documents/recipients')->name('documents.recipients.')->group(function () {
    // Страница формы
    Route::get('/create', [DocumentRecipientController::class, 'create'])->name('create');

    // Сохранение
    Route::post('/', [DocumentRecipientController::class, 'store'])->name('store');

    // AJAX: Каскадная подгрузка
    Route::get('/cities/{regionId}', [DocumentRecipientController::class, 'getCities'])->name('cities');
    Route::get('/org-types/{cityId}', [DocumentRecipientController::class, 'getOrganizationTypes'])->name('org-types');
    Route::get('/organizations/{cityId}/{type}', [DocumentRecipientController::class, 'getOrganizations'])->name('organizations');
    Route::get('/users/{organizationId}', [DocumentRecipientController::class, 'getUsers'])->name('users');

// Маршруты для выбора получателей
    Route::get('/documents/recipients/create', [DocumentController::class, 'recipientsCreate'])->name('documents.recipients.create');
Route::post('/documents/recipients/store', [DocumentController::class, 'recipientsStore'])->name('documents.recipients.store');

    Route::get('/documents/recipients/cities/{regionId}', [DocumentRecipientController::class, 'getCities'])
        ->name('documents.recipients.cities');

    Route::get('/documents/recipients/org-types/{cityId}', [DocumentRecipientController::class, 'getOrganizationTypes'])
        ->name('documents.recipients.org-types');

    Route::get('/documents/recipients/organizations/{cityId}/{category}', [DocumentRecipientController::class, 'getOrganizations'])
        ->name('documents.recipients.organizations');

    Route::get('/documents/recipients/users/{organizationId}', [DocumentRecipientController::class, 'getUsers'])
        ->name('documents.recipients.users');

    Route::post('/documents/recipients', [DocumentRecipientController::class, 'store'])
        ->name('documents.recipients.store');
    Route::get('/api/users-by-ids', function(\Illuminate\Http\Request $request) {
        $ids = explode(',', $request->input('ids'));
        $users = \App\Models\User::whereIn('id', $ids)->get(['id', 'name', 'email', 'phone']);
        return response()->json($users);
    })->middleware('auth');
});



Route::middleware(['auth'])->group(function () {
    Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('departments/create', [DepartmentController::class, 'create'])->name('departments.create');
    Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::get('departments/{department}', [DepartmentController::class, 'show'])->name('departments.show');
    Route::get('departments/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
    Route::put('departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
    Route::post('departments/{department}/assign-user', [DepartmentController::class, 'assignUser'])->name('departments.assign-user');
    Route::delete('departments/{department}/remove-user/{user}', [DepartmentController::class, 'removeUser'])->name('departments.remove-user');
});
Route::get('documents/recipients/department', [DocumentController::class, 'departmentRecipientsCreate'])
    ->name('documents.recipients.department');
Route::post('documents/recipients/department', [DocumentController::class, 'departmentRecipientsStore'])
    ->name('documents.recipients.department.store');

Route::get('/documents/{id}/stream', [DocumentSignatureController::class, 'stream'])->name('documents.stream');
Route::get('/documents/{id}/download', [DocumentSignatureController::class, 'download'])->name('documents.download');

Route::get('/contract/create', [ContractController::class, 'create'])->name('contract.create');
Route::post('/contract/generate', [ContractController::class, 'generate'])->name('contract.generate');

Route::middleware('guest')->group(function () {
    Route::get('/register-company', [CompanyRegistrationController::class, 'showForm'])
        ->name('register.company');

    Route::post('/register-company', [CompanyRegistrationController::class, 'store'])
        ->name('register.company.store');
});
Route::middleware('auth')->group(function () {
    Route::get('/companies',                 [CompanyTreeController::class, 'index'])->name('companies.index');
    Route::get('/companies/create',          [CompanyTreeController::class, 'createForm'])->name('companies.create');   // ✅
    Route::post('/companies/create',         [CompanyTreeController::class, 'store'])->name('companies.store');          // ✅
    Route::get('/companies/{company}/edit',  [CompanyTreeController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{company}',       [CompanyTreeController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}',    [CompanyTreeController::class, 'destroy'])->name('companies.destroy');
    Route::get('/companies/{company}',       [CompanyTreeController::class, 'show'])->name('companies.show');
});
Route::middleware('auth')->prefix('face')->name('face.')->controller(FaceController::class)->group(function () {
    Route::get('/scan', 'scanPage')->name('scan');
    Route::post('/register', 'register')->name('register');
    Route::post('/checkin', 'checkin')->name('checkin');
});

// ===== АДМИНКА: отчёты и настройки =====
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::controller(AttendanceReportController::class)->group(function () {
        Route::get('/reports', 'index')->name('reports');
        Route::post('/attendances/{attendance}/excuse', 'excuse')->name('attendances.excuse');
    });

    Route::controller(CompanySettingsController::class)->group(function () {
        Route::get('/companies/{company}/settings', 'settings')->name('companies.settings');
        Route::put('/companies/{company}/settings', 'updateSettings')->name('companies.settings.update');
    });
});
Route::middleware('auth')->prefix('face')->name('face.')->controller(FaceController::class)->group(function () {
    Route::get('/scan', 'scanPage')->name('scan');
    Route::post('/register', 'register')->name('register');
    Route::post('/checkin', 'checkin')->name('checkin');
});
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->controller(AttendanceReportController::class)->group(function () {
    Route::get('/reports', 'index')->name('reports');
    Route::post('/reports/excuse', 'excuse')->name('reports.excuse');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->controller(AttendanceReportController::class)->group(function () {
    Route::get('/reports', 'index')->name('reports');
    Route::post('/reports/excuse', 'excuse')->name('reports.excuse');
    Route::post('/reports/settings', 'saveSettings')->name('reports.settings'); // ← ДОБАВЬ ЭТУ СТРОКУ
});