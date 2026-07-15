@extends('layouts.superadmin')

@section('title', 'История активности')
@section('page-title', '📜 История действий')
@section('page-subtitle', 'Полный лог активности в системе')

@section('content')
<style>
    .activity-page {
        font-family: 'Inter', -apple-system, sans-serif;
        background: #000000;
        color: #f1f5f9;
    }

    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.35);
    }
    .page-header h1 { font-size: 1.4rem; font-weight: 600; margin: 0; color: white; }
    .page-header p { margin: 4px 0 0; opacity: 0.9; font-size: 0.85rem; color: rgba(255,255,255,0.9); }

    .stat-card {
        background: #0a0a0a;
        border-radius: 12px;
        padding: 14px 16px;
        border: 1px solid #1f1f1f;
        transition: all 0.25s ease;
        height: 100%;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.6);
        border-color: #2a2a2a;
    }
    .stat-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
    }
    .stat-value { font-size: 1.3rem; font-weight: 700; margin: 0; line-height: 1.2; color: #f1f5f9; }
    .stat-label { font-size: 0.72rem; color: #777777; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }

    .filter-card {
        background: #0a0a0a;
        border-radius: 14px;
        border: 1px solid #1f1f1f;
        padding: 16px 18px;
        margin-bottom: 16px;
    }
    .filter-card .form-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #888888;
        margin-bottom: 5px;
    }
    .filter-card .form-select,
    .filter-card .form-control {
        font-size: 0.82rem;
        padding: 7px 11px;
        border-radius: 8px;
        border: 1.5px solid #2a2a2a;
        background: #000000;
        color: #f1f5f9;
        transition: all 0.2s;
    }
    .filter-card .form-select:focus,
    .filter-card .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.25);
        background: #000000;
        color: #f1f5f9;
    }
    .filter-card .form-select option {
        background: #0a0a0a;
        color: #f1f5f9;
    }
    .btn-filter {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 500;
        padding: 8px 14px;
        transition: all 0.2s;
    }
    .btn-filter:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.45);
        color: white;
    }
    .btn-reset {
        background: #1a1a1a;
        color: #aaaaaa;
        border: none;
        border-radius: 8px;
        font-size: 0.82rem;
        padding: 8px 14px;
    }
    .btn-reset:hover { background: #252525; color: #f1f5f9; }

    .activity-table-wrap {
        background: #0a0a0a;
        border-radius: 14px;
        border: 1px solid #1f1f1f;
        overflow: hidden;
    }
    .activity-table { margin: 0; font-size: 0.82rem; color: #f1f5f9; }
    .activity-table thead th {
        background: #000000;
        border-bottom: 1px solid #1f1f1f;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #888888;
        padding: 11px 14px;
    }
    .activity-table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        border-bottom: 1px solid #1a1a1a;
        color: #e2e8f0;
    }
    .activity-table tbody tr { transition: background 0.15s; }
    .activity-table tbody tr:hover { background: #121212; }
    .activity-table tbody tr:last-child td { border-bottom: none; }

    .user-cell { display: flex; align-items: center; gap: 10px; }
    .user-avatar {
        width: 32px; height: 32px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.78rem;
        flex-shrink: 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .user-name { font-weight: 600; font-size: 0.82rem; color: #f1f5f9; line-height: 1.2; }
    .user-email { font-size: 0.7rem; color: #888888; }

    .action-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .action-login      { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
    .action-logout     { background: rgba(148, 163, 184, 0.15); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.25); }
    .action-doc-create { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
    .action-doc-update { background: rgba(251, 191, 36, 0.2); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3); }
    .action-doc-delete { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
    .action-doc-sign   { background: rgba(99, 102, 241, 0.2); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.3); }
    .action-doc-send   { background: rgba(139, 92, 246, 0.2); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.3); }
    .action-doc-reject { background: rgba(244, 63, 94, 0.2); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); }
    .action-doc-export { background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3); }
    .action-doc-download { background: rgba(20, 184, 166, 0.2); color: #2dd4bf; border: 1px solid rgba(20, 184, 166, 0.3); }
    .action-doc-ai     { background: rgba(168, 85, 247, 0.2); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3); }
    .action-user-create{ background: rgba(6, 182, 212, 0.2); color: #22d3ee; border: 1px solid rgba(6, 182, 212, 0.3); }
    .action-user-update{ background: rgba(251, 191, 36, 0.2); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3); }
    .action-user-delete{ background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
    .action-default    { background: rgba(148, 163, 184, 0.15); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.25); }

    .time-cell .time-main { font-weight: 500; color: #f1f5f9; font-size: 0.8rem; }
    .time-cell .time-ago  { font-size: 0.7rem; color: #888888; }

    .ip-chip {
        display: inline-block;
        padding: 3px 8px;
        background: #000000;
        border: 1px solid #2a2a2a;
        border-radius: 6px;
        font-family: 'SF Mono', Monaco, monospace;
        font-size: 0.72rem;
        color: #cbd5e1;
    }

    .empty-state {
        padding: 50px 20px;
        text-align: center;
    }
    .empty-state-icon {
        width: 60px; height: 60px;
        border-radius: 50%;
        background: #1a1a1a;
        display: inline-flex;
        align-items: center; justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 12px;
    }
    .empty-state-title { font-weight: 600; color: #f1f5f9; margin-bottom: 4px; }
    .empty-state-text { font-size: 0.82rem; color: #888888; }

    .pagination-wrap { padding: 14px 18px; border-top: 1px solid #1f1f1f; }
    .pagination .page-link {
        font-size: 0.78rem;
        padding: 6px 11px;
        border-radius: 8px !important;
        margin: 0 2px;
        border: 1px solid #2a2a2a;
        background: #000000;
        color: #aaaaaa;
    }
    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 10px rgba(102, 126, 234, 0.45);
    }
    .pagination .page-link:hover {
        background: #1a1a1a;
        color: #f1f5f9;
        border-color: #667eea;
    }
    .pagination .page-item.disabled .page-link {
        background: #0a0a0a;
        color: #555555;
        border-color: #1f1f1f;
    }
</style>

<div class="container-fluid px-4 py-3 activity-page">

    <!-- Заголовок страницы -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1>📜 История всех действий</h1>
            <p>Полный лог активности пользователей в системе</p>
        </div>
        <a href="{{ route('superadmin.dashboard') }}" class="btn btn-light btn-sm" style="border-radius: 8px; font-size: 0.8rem;">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>

    <!-- Мини-статистика (ИСПРАВЛЕНО - используем переменные из контроллера) -->
    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background: rgba(102, 126, 234, 0.2); color: #667eea;"><i class="bi bi-clock-history"></i></div>
                <div>
                    <p class="stat-value">{{ number_format($totalActivities ?? 0, 0, '.', ' ') }}</p>
                    <p class="stat-label">Всего записей</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background: rgba(34, 197, 94, 0.2); color: #4ade80;"><i class="bi bi-box-arrow-in-right"></i></div>
                <div>
                    <p class="stat-value">{{ $todayLogins ?? 0 }}</p>
                    <p class="stat-label">Входов сегодня</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background: rgba(251, 191, 36, 0.2); color: #fbbf24;"><i class="bi bi-file-earmark-text"></i></div>
                <div>
                    <p class="stat-value">{{ number_format($documentActions ?? 0, 0, '.', ' ') }}</p>
                    <p class="stat-label">Операции с документами</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background: rgba(239, 68, 68, 0.2); color: #f87171;"><i class="bi bi-people"></i></div>
                <div>
                    <p class="stat-value">{{ $activeUsersCount ?? 0 }}</p>
                    <p class="stat-label">Активных пользователей</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Фильтры (ДОБАВЛЕНЫ новые типы действий) -->
    <div class="filter-card">
        <form method="GET" action="{{ route('superadmin.activity') }}" class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label">Действие</label>
                <select name="action" class="form-select">
                    <option value="">Все действия</option>
                    <optgroup label="🔐 Аутентификация">
                        <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Вход в систему</option>
                        <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Выход из системы</option>
                    </optgroup>
                    <optgroup label="📄 Документы">
                        <option value="document_created" {{ request('action') == 'document_created' ? 'selected' : '' }}>Создание документа</option>
                        <option value="document_updated" {{ request('action') == 'document_updated' ? 'selected' : '' }}>Обновление документа</option>
                        <option value="document_deleted" {{ request('action') == 'document_deleted' ? 'selected' : '' }}>Удаление документа</option>
                        <option value="document_signed" {{ request('action') == 'document_signed' ? 'selected' : '' }}>Подписание документа</option>
                        <option value="document_sent" {{ request('action') == 'document_sent' ? 'selected' : '' }}>Отправка на подпись</option>
                        <option value="document_rejected" {{ request('action') == 'document_rejected' ? 'selected' : '' }}>Отклонение документа</option>
                        <option value="document_exported" {{ request('action') == 'document_exported' ? 'selected' : '' }}>Экспорт документа</option>
                        <option value="document_downloaded" {{ request('action') == 'document_downloaded' ? 'selected' : '' }}>Скачивание файла</option>
                        <option value="document_ai_generated" {{ request('action') == 'document_ai_generated' ? 'selected' : '' }}>Генерация через ИИ</option>
                        <option value="document_ai_parsed" {{ request('action') == 'document_ai_parsed' ? 'selected' : '' }}>Анализ через ИИ</option>
                    </optgroup>
                    <optgroup label="👤 Пользователи">
                        <option value="user_created" {{ request('action') == 'user_created' ? 'selected' : '' }}>Создание пользователя</option>
                        <option value="user_updated" {{ request('action') == 'user_updated' ? 'selected' : '' }}>Обновление пользователя</option>
                        <option value="user_deleted" {{ request('action') == 'user_deleted' ? 'selected' : '' }}>Удаление пользователя</option>
                    </optgroup>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label">Пользователь</label>
                <select name="user_id" class="form-select">
                    <option value="">Все пользователи</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">Дата с</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">Дата по</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 col-sm-4 d-flex gap-2">
                <button type="submit" class="btn btn-filter flex-fill">
                    <i class="bi bi-funnel-fill"></i> Найти
                </button>
                <a href="{{ route('superadmin.activity') }}" class="btn btn-reset" title="Сбросить">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Таблица активности -->
    <div class="activity-table-wrap">
        <div class="table-responsive">
            <table class="table activity-table">
                <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Действие</th>
                    <th>Пользователь</th>
                    <th>Описание</th>
                    <th>IP</th>
                    <th style="width: 160px;">Время</th>
                </tr>
                </thead>
                <tbody>
                @forelse($activities as $activity)
                @php
                $badgeClass = match($activity->action) {
                'login'                 => 'action-login',
                'logout'                => 'action-logout',
                'document_created'      => 'action-doc-create',
                'document_updated'      => 'action-doc-update',
                'document_deleted'      => 'action-doc-delete',
                'document_signed'       => 'action-doc-sign',
                'document_sent'         => 'action-doc-send',
                'document_rejected'     => 'action-doc-reject',
                'document_exported'     => 'action-doc-export',
                'document_downloaded'   => 'action-doc-download',
                'document_ai_parsed'    => 'action-doc-ai',
                'document_ai_generated' => 'action-doc-ai',
                'user_created'          => 'action-user-create',
                'user_updated'          => 'action-user-update',
                'user_deleted'          => 'action-user-delete',
                default                 => 'action-default',
                };
                $icon = match($activity->action) {
                'login'                 => 'bi-box-arrow-in-right',
                'logout'                => 'bi-box-arrow-right',
                'document_created'      => 'bi-file-earmark-plus',
                'document_updated'      => 'bi-file-earmark-text',
                'document_deleted'      => 'bi-file-earmark-x',
                'document_signed'       => 'bi-pen',
                'document_sent'         => 'bi-send',
                'document_rejected'     => 'bi-x-circle',
                'document_exported'     => 'bi-download',
                'document_downloaded'   => 'bi-cloud-download',
                'document_ai_parsed'    => 'bi-robot',
                'document_ai_generated' => 'bi-stars',
                'user_created'          => 'bi-person-plus',
                'user_updated'          => 'bi-person-gear',
                'user_deleted'          => 'bi-person-x',
                default                 => 'bi-activity',
                };
                $label = match($activity->action) {
                'login'                 => 'Вход',
                'logout'                => 'Выход',
                'document_created'      => 'Создание',
                'document_updated'      => 'Обновление',
                'document_deleted'      => 'Удаление',
                'document_signed'       => 'Подписание',
                'document_sent'         => 'Отправка',
                'document_rejected'     => 'Отказ',
                'document_exported'     => 'Экспорт',
                'document_downloaded'   => 'Скачивание',
                'document_ai_parsed'    => 'ИИ-анализ',
                'document_ai_generated' => 'ИИ-генерация',
                'user_created'          => 'Создание',
                'user_updated'          => 'Обновление',
                'user_deleted'          => 'Удаление',
                default                 => str_replace('_', ' ', ucfirst($activity->action)),
                };
                @endphp
                <tr>
                    <td class="text-muted">#{{ $activity->id }}</td>
                    <td>
                        <span class="action-badge {{ $badgeClass }}">
                            <i class="bi {{ $icon }}"></i>
                            {{ $label }}
                        </span>
                    </td>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar">
                                {{ strtoupper(substr($activity->user->name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <div class="user-name">{{ $activity->user->name ?? 'Неизвестно' }}</div>
                                <div class="user-email">{{ $activity->user->email ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="max-width: 350px;">
                        <span class="text-truncate d-inline-block" style="max-width: 350px;" title="{{ $activity->description }}">
                            {{ $activity->description ?? '—' }}
                        </span>
                    </td>
                    <td><span class="ip-chip">{{ $activity->ip_address ?? '—' }}</span></td>
                    <td class="time-cell">
                        <div class="time-main">{{ $activity->created_at->format('d.m.Y H:i') }}</div>
                        <div class="time-ago">{{ $activity->created_at->diffForHumans() }}</div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state-icon">📭</div>
                            <div class="empty-state-title">Записей не найдено</div>
                            <div class="empty-state-text">Попробуйте изменить параметры фильтра или выполните действия в системе</div>
                        </div>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($activities->hasPages())
        <div class="pagination-wrap d-flex justify-content-between align-items-center">
            <div style="font-size: 0.78rem; color: #888888;">
                Показано {{ $activities->firstItem() ?? 0 }}–{{ $activities->lastItem() ?? 0 }} из {{ $activities->total() }}
            </div>
            {{ $activities->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

</div>
@endsection