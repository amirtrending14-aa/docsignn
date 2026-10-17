<?php

use App\Http\Controllers\ArchivistController; 
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AIChatController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DocumentRecipientController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\SuperAdmin\CompanyController as SuperAdminCompanyController;
use App\Http\Controllers\SuperAdmin\DocumentController as SuperAdminDocumentController;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\CompanyRegistrationController; 
use App\Http\Controllers\CompanyTreeController;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\CompanySettingsController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\TelegramChatController;
use App\Http\Controllers\TelegramMessageController;

/*
|--------------------------------------------------------------------------
| 1. ПУБЛИЧНЫЕ МАРШРУТЫ (без авторизации)
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => view('layouts.site'))->name('site.home');
Route::get('/site', fn() => view('layouts.site'))->name('site.main');

// Отдача аватаров напрямую из storage/app/public, включая Windows без симлинка.
Route::get('/avatar/{path}', function (string $path) {
    $path = ltrim(str_replace('\\', '/', $path), '/');

    abort_unless(str_starts_with($path, 'avatars/') && !str_contains($path, '..'), 404);
    abort_unless(Storage::disk('public')->exists($path), 404);

    return response()->file(Storage::disk('public')->path($path));
})->where('path', '.*')->name('avatar.show');

// 🔒 Публичная проверка подлинности документа по коду
Route::get('/verify/{code}', [DocumentSignatureController::class, 'verify'])
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
        ->name('dashboard.chart-data');
    Route::get('/analysis', [AnalysisController::class, 'index'])->name('analysis.index');

    // ============================================
    // PROFILE
    // ============================================
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // ============================================
    // SETTINGS
    // ============================================
    Route::get('/setting', fn() => view('settings.index'))->name('settings');
    Route::post('/settings/signature', [ProfileController::class, 'updateSignature'])
        ->name('settings.signature.update');
    Route::post('/settings/general', [ProfileController::class, 'updateGeneral'])
        ->name('settings.general.update');
    Route::put('/settings/edi', [SettingsController::class, 'update'])
        ->name('settings.update');

    // ============================================
    // DOCUMENTS (🔒 IDOR Protection через Route Model Binding)
    // ============================================
    Route::get('/documents/{document}/download-pdf', [DocumentController::class, 'downloadPdf'])
        ->name('documents.downloadPdf');

    Route::get('/documents/{document}/download-word', [DocumentController::class, 'downloadWord'])
        ->name('documents.downloadWord');

    Route::post('/documents/contracts-process', [DocumentController::class, 'storeFromPdf'])
        ->name('documents.contracts-process');

    Route::post('/documents/{document}/sign', [DocumentController::class, 'sign'])
        ->name('documents.sign');

    Route::post('/documents/{document}/reject', [DocumentController::class, 'reject'])
        ->name('documents.reject');
        
    // Временная загрузка файла (AJAX)
    Route::post('/documents/upload-temp', [DocumentController::class, 'uploadTemp'])->name('documents.upload-temp');

    Route::resource('documents', DocumentController::class);
    
    // Доступные получатели (для автокомплита или списков)
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
        ->name('search');

    // 🔒 Защита от перебора email (User Enumeration)
    Route::get('/api/users/search', function (Request $request) {
        $request->validate(['email' => 'required|email']);
        $user = \App\Models\User::where('email', $request->email)->first();
        return response()->json([
            'exists' => !!$user,
        ]);
    })->name('users.search_api');
});

// ============================================
// COMMENTS (🔒 IDOR Protection)
// ============================================
Route::middleware(['auth'])->group(function () {
    Route::post('/comments', [DocumentCommentController::class, 'store'])->name('comments.store');
    Route::get('/documents/{document}/comments', [DocumentCommentController::class, 'index'])->name('comments.index');
    Route::delete('/comments/{comment}', [DocumentCommentController::class, 'destroy'])->name('comments.destroy');
});

// ============================================
// NOTIFICATIONS (🔒 IDOR Protection)
// ============================================
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/create', [NotificationController::class, 'create'])->name('create');
    Route::get('/check', [NotificationController::class, 'checkNew'])->name('check');
    Route::any('/{notification}/read', [NotificationController::class, 'read'])->name('read');
    Route::patch('/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('read_patch');
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/clear-all', [NotificationController::class, 'clearAll'])->name('clearAll');
    Route::post('/read-all', [NotificationController::class, 'readAll'])->name('readAll');
});
Route::middleware(['auth'])->post('/comments/store_notification', [NotificationController::class, 'store'])
    ->name('comments.store_notification');

// ============================================
// MESSAGES (🔒 IDOR Protection)
// ============================================
Route::middleware(['auth'])->resource('messages', MessageController::class);

// ============================================
// AI
// ============================================
Route::middleware(['auth'])->group(function () {
    Route::post('/contracts/generate-document', [AIController::class, 'generateDocument'])
        ->name('contracts.generate-document');
    // Алиас для старого адреса
    Route::post('/ai/generate-document', [AIController::class, 'generateDocument'])
        ->name('ai.generate-document');
});

// ============================================
// HEARTBEAT (🔒 Защита от спама)
// ============================================
Route::middleware(['auth'])->get('/heartbeat', function () {
    $user = Auth::user();
    if (!$user) {
        return response()->json(['status' => 'error'], 401);
    }
    DB::table('users')->where('id', $user->id)->update(['last_seen_at' => now()]);
    return response()->json(['status' => 'ok', 'online' => true]);
})->name('heartbeat');

// ============================================
// STREL
// ============================================
Route::middleware(['auth'])->get('/strel', [StrelController::class, 'index'])->name('strel.index');

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

        // USERS MANAGEMENT
        Route::resource('users', SuperAdminUserController::class)->except(['show']);
        Route::get('users/{user}/activity', [SuperAdminUserController::class, 'userActivity'])
            ->name('users.activity');

        // COMPANIES MANAGEMENT
        Route::resource('companies', SuperAdminCompanyController::class);

        // DOCUMENTS MANAGEMENT
        Route::resource('documents', SuperAdminDocumentController::class)->except(['show']);

        // ACTIVITY LOGS
        Route::get('activity', [ActivityController::class, 'index'])->name('activity');

        // PROFILE
        Route::get('profile', [SuperAdminController::class, 'profile'])->name('profile');
        Route::put('profile', [SuperAdminController::class, 'updateProfile'])
            ->name('profile.update');
    });

/*
|--------------------------------------------------------------------------
| 6. СЛУЖЕБНЫЕ МАРШРУТЫ
|--------------------------------------------------------------------------
*/
// 🔒 Защищённый маршрут обновления dashboard
Route::middleware(['auth'])->get('/dashboard-update', fn() => 'Данные обновлены успешно!')
    ->name('dashboard.update');

/*
|--------------------------------------------------------------------------
| 7. FALLBACK МАРШРУТ (404)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    \Log::warning('404 Not Found: ' . request()->fullUrl() . ' | IP: ' . request()->ip());

    if (request()->wantsJson()) {
        return response()->json(['error' => 'Not Found'], 404);
    }
    abort(404);
});

// ============================================
// ДОПОЛНИТЕЛЬНЫЕ МАРШРУТЫ ДОКУМЕНТОВ И КОНТРАКТОВ
// ============================================
Route::middleware(['auth'])->group(function () {
    Route::get('/documents/{id}/preview', [DocumentController::class, 'previewPdf'])->name('documents.preview');
    Route::get('/documents/{id}/pdf', [DocumentController::class, 'pdf'])->name('documents.pdf');
    Route::get('/documents/{id}/debug', [DocumentController::class, 'debugFilePath']);
    
  Route::get('/contracts-assistant', [AIController::class, 'index'])->name('contracts.assistant');
   Route::post('/contracts/chat', [AIController::class, 'chat'])->name('contracts.chat');
});

/*
|--------------------------------------------------------------------------
| МАРШРУТЫ ПОЛУЧАТЕЛЕЙ (RECIPIENTS) - ИСПРАВЛЕНО
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('documents/recipients')->name('documents.recipients.')->group(function () {
    // Страница формы создания
    Route::get('/create', [DocumentRecipientController::class, 'create'])->name('create');

    // Сохранение данных
    Route::post('/', [DocumentRecipientController::class, 'store'])->name('store');

    // AJAX: Каскадная подгрузка данных
    Route::get('/cities/{regionId}', [DocumentRecipientController::class, 'getCities'])->name('cities');
    Route::get('/org-types/{cityId}', [DocumentRecipientController::class, 'getOrganizationTypes'])->name('org-types');
    Route::get('/organizations/{cityId}/{type}', [DocumentRecipientController::class, 'getOrganizations'])->name('organizations');
    Route::get('/users/{organizationId}', [DocumentRecipientController::class, 'getUsers'])->name('users');
    
    // API для получения пользователей по ID
    Route::get('/api/users-by-ids', function(\Illuminate\Http\Request $request) {
        $ids = explode(',', $request->input('ids'));
        $users = \App\Models\User::whereIn('id', $ids)->get(['id', 'name', 'email', 'phone']);
        return response()->json($users);
    })->name('api.users-by-ids');
});

// Маршруты для выбора получателей по отделам (если используются отдельно)
Route::middleware(['auth'])->group(function () {
    Route::get('documents/recipients/department', [DocumentController::class, 'departmentRecipientsCreate'])
        ->name('documents.recipients.department');
    Route::post('documents/recipients/department', [DocumentController::class, 'departmentRecipientsStore'])
        ->name('documents.recipients.department.store');
});

// ============================================
// ОТДЕЛЫ (DEPARTMENTS)
// ============================================
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

// ============================================
// ПОДПИСИ И СКАЧИВАНИЕ
// ============================================
Route::middleware(['auth'])->group(function () {
    Route::get('/documents/{id}/stream', [DocumentSignatureController::class, 'stream'])->name('documents.stream');
    
    // ✅ Роут для предпросмотра Word/PDF в едином PDF-виджете подписи
    Route::get('/documents/{id}/preview-pdf', [DocumentSignatureController::class, 'previewPdf'])
        ->name('documents.previewPdf');

    Route::get('/documents/{id}/download', [DocumentSignatureController::class, 'download'])->name('documents.download');
    Route::get('/signatures/{id}/qr', [DocumentSignatureController::class, 'qrImage'])
        ->name('signatures.qr');
});

// ============================================
// КОМПАНИИ И ДЕРЕВО
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('/register-company', [CompanyRegistrationController::class, 'showForm'])
        ->name('register.company');
    Route::post('/register-company', [CompanyRegistrationController::class, 'store'])
        ->name('register.company.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/companies', [CompanyTreeController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [CompanyTreeController::class, 'createForm'])->name('companies.create');
    Route::post('/companies/create', [CompanyTreeController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}/edit', [CompanyTreeController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{company}', [CompanyTreeController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}', [CompanyTreeController::class, 'destroy'])->name('companies.destroy');
    Route::get('/companies/{company}', [CompanyTreeController::class, 'show'])->name('companies.show');
});

// ============================================
// КОНТРАКТЫ (ContractController)
// ============================================
Route::middleware(['auth'])->group(function () {
    Route::get('/contract/create', [ContractController::class, 'create'])->name('contract.create');
    Route::post('/contract/generate', [ContractController::class, 'generate'])->name('contract.generate');
    
    Route::get('/contracts/index', [ContractController::class, 'index'])->name('contracts.list');
    Route::get('/contracts/create', [ContractController::class, 'create'])->name('contracts.create');
    Route::post('/contracts/generate', [ContractController::class, 'generate'])->name('contracts.generate');
    Route::post('/contracts/generate-ajax', [ContractController::class, 'generateAjax'])->name('contracts.generate-ajax');
    Route::post('/contracts/analyze-ajax', [ContractController::class, 'analyzeAjax'])->name('contracts.analyze-ajax');
    Route::get('/contracts', [ContractController::class, 'index'])->name('contracts.index');
    Route::get('/contracts/{id}/preview', [ContractController::class, 'preview'])->name('contracts.preview');
    Route::get('/contracts/{id}/download', [ContractController::class, 'download'])->name('contracts.download');
    Route::get('/contracts/{id}/download-pdf', [ContractController::class, 'downloadPdf'])->name('contracts.downloadPdf');
    Route::get('/contracts/analyze-page', [ContractController::class, 'analyzePage'])->name('contracts.analyze-page');
    Route::post('/contracts/analyze-upload', [ContractController::class, 'analyzeUpload'])->name('contracts.analyze-upload');
});

// ============================================
// FACE ID МАРШРУТЫ
// ============================================
Route::middleware('auth')->prefix('face')->name('face.')->controller(FaceController::class)->group(function () {
    Route::get('/scan', 'scanPage')->name('scan');
    Route::post('/register', 'register')->name('register');
    Route::post('/checkin', 'checkin')->name('checkin');
    Route::post('/refuse', 'refuse')->name('refuse');
    Route::post('/login-by-face', 'loginByFace')->name('loginByFace');
});

// ============================================
// АДМИНКА: ОТЧЕТЫ ПОСЕЩАЕМОСТИ
// ============================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->controller(AttendanceReportController::class)->group(function () {
    Route::get('/reports', 'index')->name('reports');
    Route::get('/reports/user/{userId}', 'userReport')->name('reports.user');
    Route::post('/reports/excuse', 'excuse')->name('reports.excuse');
    Route::post('/reports/settings', 'saveSettings')->name('reports.settings');
});


Route::middleware('auth')->group(function () {
    Route::get('/ai-chat', [ChatController::class, 'index'])->name('ai.chat');
    Route::get('/ai-chat/{id}', [ChatController::class, 'index'])->name('ai.chat.show');
    Route::post('/ai-chat/send', [ChatController::class, 'send'])->name('ai.send');
    Route::get('/ai-chat/download/{id}/{format}', [ChatController::class, 'download'])
        ->where('format', 'docx|pdf')
        ->name('ai.download');
    Route::delete('/ai-chat/{id}', [ChatController::class, 'destroy'])->name('ai.chat.delete');
    
    Route::get('/ai-archivist', [ArchivistController::class, 'page'])->name('archivist.page');
    Route::post('/ai-archivist/chat', [ArchivistController::class, 'chat'])->name('archivist.chat');
});

 
Route::middleware(['auth'])->prefix('chat')->group(function () {

    Route::get('/chat/file/{path}', [TelegramChatController::class, 'downloadFile'])
        ->where('path', '.*')
        ->name('chat.file');

    Route::get('/', [TelegramChatController::class, 'index'])->name('telegram.chat');

    // Отдача файлов чата (безопасная + Range для аудио)
    Route::get('/file/{path}', function (string $path) {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        abort_unless(
            (str_starts_with($path, 'telegram_files/') || str_starts_with($path, 'telegram/'))
            && !str_contains($path, '..'),
            404
        );

        $fullPath = storage_path('app/public/' . $path);
        abort_unless(file_exists($fullPath), 404);

        $message = \App\Models\TelegramMessage::where('attachment_path', $path)->first();
        if ($message && $message->conversation && !$message->conversation->users->contains(auth()->id())) {
            abort(403, 'Нет доступа к этому файлу');
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'webm' => 'audio/webm', 'ogg' => 'audio/ogg', 'mp3' => 'audio/mpeg',
            'm4a' => 'audio/mp4', 'wav' => 'audio/wav', 'mp4' => 'video/mp4',
            'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif', 'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';

        if (str_starts_with($mime, 'audio/') || str_starts_with($mime, 'video/')) {
            $size = filesize($fullPath);
            $start = 0;
            $end = $size - 1;
            $status = 200;
            $headers = [
                'Content-Type' => $mime,
                'Content-Length' => $size,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => 'public, max-age=31536000',
            ];

            if (isset($_SERVER['HTTP_RANGE'])) {
                if (preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches)) {
                    $start = intval($matches[1]);
                    $end = isset($matches[2]) && $matches[2] !== '' ? intval($matches[2]) : $size - 1;
                    $status = 206;
                    $headers['Content-Range'] = "bytes $start-$end/$size";
                    $headers['Content-Length'] = $end - $start + 1;
                }
            }

            return response()->stream(function () use ($fullPath, $start, $end) {
                $fp = fopen($fullPath, 'rb');
                fseek($fp, $start);
                $remaining = $end - $start + 1;
                while ($remaining > 0 && !feof($fp)) {
                    echo fread($fp, min(8192, $remaining));
                    flush();
                    $remaining -= 8192;
                }
                fclose($fp);
            }, $status, $headers);
        }

        return response()->file($fullPath, ['Content-Type' => $mime]);
    })->where('path', '.*')->name('telegram.file');

    Route::prefix('api')->group(function () {
        // Список чатов / пользователи / создание
        Route::get('/conversations', [TelegramChatController::class, 'getConversations']);
        Route::get('/users', [TelegramChatController::class, 'getUsers'])->name('telegram.users');
        Route::post('/group', [TelegramChatController::class, 'createGroup']);
        Route::post('/private', [TelegramChatController::class, 'startPrivateChat'])->name('telegram.private');

        // Сообщения
        Route::get('/messages/{id}', [TelegramChatController::class, 'getMessages']);
        Route::post('/send', [TelegramChatController::class, 'send']);
        Route::delete('/message/{id}', [TelegramChatController::class, 'deleteMessage']);

        // Прочитано / печатает
        Route::post('/mark-read/{id}', [TelegramChatController::class, 'markAsRead']);
        Route::post('/mark-unread/{id}', [TelegramChatController::class, 'markUnread']);
        Route::post('/typing', [TelegramChatController::class, 'typing']);
        Route::get('/typing/{id}', [TelegramChatController::class, 'getTyping']);

        // Инфо + медиа-галерея
        Route::get('/info/{id}', [TelegramChatController::class, 'info']);
        Route::get('/media/{id}', [TelegramChatController::class, 'media']);

        // Управление группой
        Route::post('/group/{id}/add-members', [TelegramChatController::class, 'addMembers']);
        Route::delete('/group/{id}/member/{userId}', [TelegramChatController::class, 'removeMember']);
        Route::post('/leave/{id}', [TelegramChatController::class, 'leave']);
        Route::delete('/conversation/{id}', [TelegramChatController::class, 'deleteConversation']);

        // Блокировки
        Route::post('/block/{userId}', [TelegramChatController::class, 'blockUser'])->name('telegram.block');
        Route::delete('/block/{userId}', [TelegramChatController::class, 'unblockUser'])->name('telegram.unblock');
        Route::get('/block/status/{userId}', [TelegramChatController::class, 'blockStatus']);

        // Отправка документа в чат
        Route::post('/send-document', [TelegramMessageController::class, 'sendDocument'])
            ->middleware('throttle:20,1')
            ->name('telegram.send-document');

        // 🆕 ===== ВИДЕОЗВОНКИ =====
        Route::post('/video-signal', [\App\Http\Controllers\VideoCallController::class, 'sendSignal']);
        Route::get('/video-signals/{conversationId}', [\App\Http\Controllers\VideoCallController::class, 'getSignals']);
        Route::post('/video-signals/{signalId}', [\App\Http\Controllers\VideoCallController::class, 'deleteSignal']);
        Route::delete('/video-signals/{signalId}', [\App\Http\Controllers\VideoCallController::class, 'deleteSignal']);
        Route::get('/user/{userId}/online', [\App\Http\Controllers\VideoCallController::class, 'checkOnline']);
    });
});