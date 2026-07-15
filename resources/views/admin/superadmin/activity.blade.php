@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">📜 История всех действий</h1>
        <a href="{{ route('superadmin.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад к дашборду
        </a>
    </div>

    <!-- Фильтры -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.activity.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Действие</label>
                    <select name="action" class="form-select">
                        <option value="">Все действия</option>
                        <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Вход в систему</option>
                        <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Выход из системы</option>
                        <option value="document_created" {{ request('action') == 'document_created' ? 'selected' : '' }}>Создание документа</option>
                        <option value="document_updated" {{ request('action') == 'document_updated' ? 'selected' : '' }}>Обновление документа</option>
                        <option value="document_deleted" {{ request('action') == 'document_deleted' ? 'selected' : '' }}>Удаление документа</option>
                        <option value="document_signed" {{ request('action') == 'document_signed' ? 'selected' : '' }}>Подписание документа</option>
                        <option value="user_created" {{ request('action') == 'user_created' ? 'selected' : '' }}>Создание пользователя</option>
                        <option value="user_updated" {{ request('action') == 'user_updated' ? 'selected' : '' }}>Обновление пользователя</option>
                        <option value="user_deleted" {{ request('action') == 'user_deleted' ? 'selected' : '' }}>Удаление пользователя</option>
                    </select>
                </div>

                <div class="col-md-3">
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

                <div class="col-md-2">
                    <label class="form-label">Дата с</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Дата по</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Фильтровать
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Таблица активности -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Действие</th>
                        <th>Пользователь</th>
                        <th>Описание</th>
                        <th>IP адрес</th>
                        <th>Время</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($activities as $activity)
                    <tr>
                        <td>{{ $activity->id }}</td>
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
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-2"
                                     style="width: 32px; height: 32px;">
                                    {{ substr($activity->user->name ?? 'N/A', 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $activity->user->name ?? 'Unknown' }}</div>
                                    <small class="text-muted">{{ $activity->user->email ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $activity->description }}</td>
                        <td><small class="text-muted">{{ $activity->ip_address }}</small></td>
                        <td>
                            <small>{{ $activity->created_at->format('d.m.Y H:i:s') }}</small>
                            <br>
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
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