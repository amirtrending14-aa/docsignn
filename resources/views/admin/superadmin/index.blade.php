@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">👥 Все пользователи</h1>
        <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Добавить пользователя
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>Пользователь</th>
                        <th>Роль</th>
                        <th>Компания</th>
                        <th>Телефон</th>
                        <th>Дата регистрации</th>
                        <th>Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-2"
                                     style="width: 40px; height: 40px; font-weight: bold;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($user->is_super_admin)
                            <span class="badge bg-warning text-dark">Супер Админ</span>
                            @elseif($user->role === 'admin' || $user->level === 1)
                            <span class="badge bg-success">Админ</span>
                            @else
                            <span class="badge bg-secondary">{{ ucfirst($user->role ?? 'employee') }}</span>
                            @endif
                        </td>
                        <td>{{ $user->company ?? '—' }}</td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td><small>{{ $user->created_at->format('d.m.Y') }}</small></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('superadmin.users.edit', $user) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('superadmin.users.destroy', $user) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Удалить {{ $user->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Нет пользователей</td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection