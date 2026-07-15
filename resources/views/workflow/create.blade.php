@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-3">Добавить этап согласования</h2>

        {{-- ошибки --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('workflow.store', $documentId) }}" method="POST">
            @csrf

            {{-- пользователь --}}
            <div class="mb-3">
                <label class="form-label">Пользователь</label>
                <select name="user_id" class="form-control" required>
                    <option value="">Выбери пользователя</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- порядок --}}
            <div class="mb-3">
                <label class="form-label">Порядок (step)</label>
                <input type="number" name="step_order" class="form-control" required>
            </div>

            {{-- статус --}}
            <div class="mb-3">
                <label class="form-label">Статус</label>
                <select name="status" class="form-control">
                    <option value="pending">Ожидает</option>
                    <option value="approved">Одобрено</option>
                    <option value="rejected">Отклонено</option>
                </select>
            </div>

            <button class="btn btn-success">Сохранить</button>
            <a href="{{ route('workflow.index', $documentId) }}" class="btn btn-secondary">Назад</a>
        </form>
    </div>
@endsection
