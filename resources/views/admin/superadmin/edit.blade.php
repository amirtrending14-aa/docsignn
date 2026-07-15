@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">✏️ Редактирование: {{ $user->name }}</h1>
        <a href="{{ route('superadmin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('superadmin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Имя *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Email *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Новый пароль <small class="text-muted">(оставьте пустым, если не меняете)</small></label>
                        <input type="password" name="password" class="form-control">
                        @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Подтверждение пароля</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Роль *</label>
                        <select name="role" class="form-select" required>
                            <option value="employee" {{ (old('role', $user->role) == 'employee') ? 'selected' : '' }}>Сотрудник</option>
                            <option value="admin" {{ (old('role', $user->role) == 'admin') ? 'selected' : '' }}>Админ</option>
                            <option value="super_admin" {{ (old('role', $user->role) == 'super_admin' || $user->is_super_admin) ? 'selected' : '' }}>Супер Админ</option>
                        </select>
                        @error('role')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Компания</label>
                        <input type="text" name="company" class="form-control" value="{{ old('company', $user->company) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Телефон</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="+992 XXX XX XX XX">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('superadmin.users.index') }}" class="btn btn-outline-secondary">Отмена</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection