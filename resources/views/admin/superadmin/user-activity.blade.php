@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Шапка с информацией о пользователе -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="bg-{{ $user->is_super_admin ? 'warning' : ($user->is_admin ? 'success' : 'primary') }} rounded-circle text-white d-flex align-items-center justify-content-center"
                         style="width: 80px; height: 80px; font-size: 32px; font-weight: bold;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </div>
                <div class="col">
                    <h2 class="mb-1">{{ $user->name }}</h2>
                    <p class="text-muted mb-0">
                        <i class="bi bi-envelope"></i> {{ $user->email }} |
                        <i class="bi bi-calendar"></i> Зарегистрирован: {{ $user->created_at->format('d.m.Y') }}
                    </p>
                    <div class="mt-2">
                        @if($user->is_super_admin)
                        <span class="badge bg-warning text-dark"><i class="bi bi-crown"></i> Супер Админ</span>
                        @elseif($user->is_admin)
                        <span class="badge bg-success"><i class="bi bi-shield-check"></i> Админ</span>
                        @else
                        <span class="badge bg-secondary"><i class="bi bi-person"></i> Пользователь</span>
                        @endif
                        <span class="badge bg-info ms-2"><i class="bi bi-file-text"></i> {{ $documentsCount }} документов</span>
                    </div>
                </div>
                <div class="col-auto">
                    <a href="{{ route('superadmin.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Назад
                    </a>
                    @if($user->id !== auth()->id())
                    <form action="{{ route('superadmin.users.destroy', $user) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя? Это действие нельзя отменить!');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Удалить
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика действий -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Входов в систему</h6>
                    <h3 class="mb-0 text-primary">{{ $stats['login'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Создано документов</h6>
                    <h3 class="mb-0 text-success">{{ $stats['document_created'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Подписано документов</h6>
                    <h3 class="mb-0 text-warning">{{ $stats['document_signed'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Последний вход</h6>
                    <h6 class="mb-0">
                        @if($lastLogin)
                        {{ $lastLogin->created_at->diffForHumans() }}
                        <br>
                        <small class="text-muted">{{ $lastLogin->ip_address }}</small>
                        @else
                        <span class="text-muted">Не входил</span>
                        @endif
                    </h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Полная история действий -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">📜 Полная история действий</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>Действие</th>
                        <th>Описание</th>
                        <th>IP адрес</th>
                        <th>Время</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($activities as $activity)
                    <tr>
                        <td>
                                <span class="badge
                                    @if($activity->action == 'login') bg-info
                                    @elseif($activity->action == 'logout') bg-secondary
                                    @elseif($activity->action == 'document_created') bg-primary
                                    @elseif($activity->action == 'document_updated') bg-warning
                                    @elseif($activity->action == 'document_deleted') bg-danger
                                    @elseif($activity->action == 'document_signed') bg-success
                                    @elseif($activity->action == 'user_created') bg-info
                                    @elseif($activity->action == 'user_updated') bg-warning
                                    @elseif($activity->action == 'user_deleted') bg-danger
                                    @else bg-secondary
                                    @endif">
                                    {{ str_replace('_', ' ', ucfirst($activity->action)) }}
                                </span>
                        </td>
                        <td>{{ $activity->description }}</td>
                        <td>
                            <small class="text-muted font-monospace">{{ $activity->ip_address }}</small>
                        </td>
                        <td>
                            <small>{{ $activity->created_at->format('d.m.Y H:i:s') }}</small>
                            <br>
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Нет записей об активности
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Пагинация -->
    <div class="mt-4">
        {{ $activities->links() }}
    </div>
</div>
@endsection