@extends('layouts.admin')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    body, .container-fluid, .card, .table, h1, h2, h3, h4, h5, h6, p, span, td, th {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
    }
    .stat-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        transition: all 0.2s ease;
    }
    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    .status-online {
        background-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        animation: pulse 2s infinite;
    }
    .status-offline {
        background-color: #9ca3af;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
    .user-row:hover {
        background-color: #f9fafb !important;
    }
    .avatar-wrapper {
        position: relative;
        display: inline-block;
    }
    .avatar-img {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #f3f4f6;
    }
    .avatar-initial {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 16px;
        color: #fff;
    }
    .avatar-status {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
    }
    .table thead th {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb !important;
        background-color: #f9fafb !important;
    }
    .table tbody td {
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6 !important;
        font-size: 14px;
    }
    .action-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }
</style>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1" style="font-weight: 700;">Панель Супер Администратора</h1>
            <p class="text-muted mb-0" style="font-size: 14px;">Добро пожаловать, {{ auth()->user()->name }}</p>
        </div>
        <div class="text-end">
            <small class="text-muted" style="font-size: 13px;">{{ now()->format('d.m.Y H:i') }}</small>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 12px; font-weight: 500;">Всего пользователей</div>
                        <div class="h4 mb-0" style="font-weight: 700;">{{ $totalUsers }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 12px; font-weight: 500;">Админов</div>
                        <div class="h4 mb-0" style="font-weight: 700;">{{ $totalAdmins }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-crown"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 12px; font-weight: 500;">Супер Админов</div>
                        <div class="h4 mb-0" style="font-weight: 700;">{{ $totalSuperAdmins }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 12px; font-weight: 500;">Документов</div>
                        <div class="h4 mb-0" style="font-weight: 700;">{{ $totalDocuments }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                        <i class="bi bi-trash"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 12px; font-weight: 500;">Удалено пользователей</div>
                        <div class="h4 mb-0" style="font-weight: 700;">{{ $totalDeletedUsers }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-file-x"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 12px; font-weight: 500;">Удалено документов</div>
                        <div class="h4 mb-0" style="font-weight: 700;">{{ $totalDeletedDocuments }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-activity"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 12px; font-weight: 500;">Онлайн сейчас</div>
                        <div class="h4 mb-0" style="font-weight: 700;">{{ $activeUsers }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card stat-card mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0" style="font-weight: 600; font-size: 16px;">👥 Команда</h5>
            <a href="{{ route('superadmin.users.index') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg"></i> Добавить пользователя
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                    <tr>
                        <th style="padding: 12px 16px;">Пользователь</th>
                        <th>Роль</th>
                        <th>Компания</th>
                        <th>Телефон</th>
                        <th>Входов</th>
                        <th>Документов</th>
                        <th>Последняя активность</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($allUsers as $user)
                    @php
                    $loginsCount = \App\Models\ActivityLog::where('user_id', $user->id)
                    ->where('action', 'login')
                    ->count();
                    $userDocsCount = \App\Models\Document::where('created_by', $user->id)->count();
                    $isOnline = $user->last_seen_at && $user->last_seen_at->diffInMinutes(now()) < 5;
                    @endphp
                    <tr class="user-row">
                        <td style="padding: 12px 16px;">
                            <div class="d-flex align-items-center">
                                <div class="avatar-wrapper me-3">
                                    @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}"
                                         alt="{{ $user->name }}"
                                         class="avatar-img">
                                    @else
                                    <div class="avatar-initial bg-primary">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    @endif
                                    <span class="avatar-status {{ $isOnline ? 'status-online' : 'status-offline' }}"></span>
                                </div>
                                <div>
                                    <a href="{{ route('superadmin.user.activity', $user) }}" class="text-decoration-none">
                                        <div class="fw-semibold text-dark" style="font-size: 14px;">{{ $user->name }}</div>
                                    </a>
                                    <small class="text-muted" style="font-size: 12px;">{{ $user->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($user->is_super_admin)
                            <span class="badge bg-warning text-dark" style="font-weight: 500;">
                                        <i class="bi bi-crown"></i> Супер Админ
                                    </span>
                            @elseif($user->role === 'admin' || $user->level === 1)
                            <span class="badge bg-success" style="font-weight: 500;">
                                        <i class="bi bi-shield-check"></i> Админ
                                    </span>
                            @else
                            <span class="badge bg-secondary" style="font-weight: 500;">
                                        <i class="bi bi-person"></i> {{ ucfirst($user->role ?? 'employee') }}
                                    </span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $user->company ?? '—' }}</small>
                        </td>
                        <td>
                            @if($user->phone)
                            <small>{{ $user->phone }}</small>
                            @else
                            <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td><span class="badge bg-primary bg-opacity-10 text-primary" style="font-weight: 600;">{{ $loginsCount }}</span></td>
                        <td><span class="badge bg-info bg-opacity-10 text-info" style="font-weight: 600;">{{ $userDocsCount }}</span></td>
                        <td>
                            @if($user->last_seen_at)
                            <div style="font-size: 13px;">{{ $user->last_seen_at->diffForHumans() }}</div>
                            <small class="text-muted" style="font-size: 11px;">{{ $user->last_seen_at->format('H:i') }}</small>
                            @else
                            <small class="text-muted">Не входил</small>
                            @endif
                        </td>
                        <td>
                            @if($isOnline)
                            <span class="badge bg-success bg-opacity-10 text-success" style="font-weight: 500;">
                                        <span class="status-dot status-online"></span>Онлайн
                                    </span>
                            @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-weight: 500;">
                                        <span class="status-dot status-offline"></span>Оффлайн
                                    </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('superadmin.user.activity', $user) }}"
                                   class="btn btn-outline-primary action-btn" title="Просмотр">
                                    <i class="bi bi-eye" style="font-size: 13px;"></i>
                                </a>
                                <a href="{{ route('superadmin.users.edit', $user) }}"
                                   class="btn btn-outline-warning action-btn" title="Редактировать">
                                    <i class="bi bi-pencil" style="font-size: 13px;"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('superadmin.users.destroy', $user) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Удалить пользователя {{ $user->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger action-btn" title="Удалить">
                                        <i class="bi bi-trash" style="font-size: 13px;"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Нет пользователей</td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card stat-card">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0" style="font-weight: 600; font-size: 16px;">📜 Последние действия в системе</h5>
            <a href="{{ route('superadmin.activity.index') }}" class="btn btn-sm btn-outline-primary">Все действия</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                    <tr>
                        <th style="padding: 12px 16px;">Действие</th>
                        <th>Пользователь</th>
                        <th>Описание</th>
                        <th>IP</th>
                        <th>Время</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($recentActivities as $activity)
                    <tr>
                        <td style="padding: 12px 16px;">
                                <span class="badge
                                    @if($activity->action == 'login') bg-info bg-opacity-10 text-info
                                    @elseif($activity->action == 'document_created') bg-primary bg-opacity-10 text-primary
                                    @elseif($activity->action == 'document_signed') bg-success bg-opacity-10 text-success
                                    @elseif($activity->action == 'user_created') bg-warning bg-opacity-10 text-warning
                                    @elseif($activity->action == 'user_deleted') bg-danger bg-opacity-10 text-danger
                                    @else bg-secondary bg-opacity-10 text-secondary
                                    @endif" style="font-weight: 500;">
                                    {{ str_replace('_', ' ', ucfirst($activity->action)) }}
                                </span>
                        </td>
                        <td>
                            @if($activity->user)
                            <a href="{{ route('superadmin.user.activity', $activity->user) }}" class="text-decoration-none fw-semibold">
                                {{ $activity->user->name }}
                            </a>
                            @else
                            <span class="text-muted">System</span>
                            @endif
                        </td>
                        <td style="font-size: 13px;">{{ $activity->description }}</td>
                        <td><small class="text-muted font-monospace">{{ $activity->ip_address }}</small></td>
                        <td>
                            <div style="font-size: 13px;">{{ $activity->created_at->diffForHumans() }}</div>
                            <small class="text-muted" style="font-size: 11px;">{{ $activity->created_at->format('H:i:s') }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Нет активности</td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection