@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">👤 Создание нового пользователя</h1>
        <a href="{{ route('superadmin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('superadmin.users.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Имя *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Email *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Пароль *</label>
                        <input type="password" name="password" class="form-control" required>
                        @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Подтверждение пароля *</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Роль *</label>
                        <select name="role" class="form-select" required>
                            <option value="employee">Сотрудник</option>
                            <option value="admin">Админ</option>
                            <option value="super_admin">Супер Админ</option>
                        </select>
                        @error('role')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Компания</label>
                        <input type="text" name="company" class="form-control" value="{{ old('company') }}">
                        @error('company')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Телефон</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+992 XXX XX XX XX">
                        @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('superadmin.users.index') }}" class="btn btn-outline-secondary">Отмена</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Создать пользователя
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection