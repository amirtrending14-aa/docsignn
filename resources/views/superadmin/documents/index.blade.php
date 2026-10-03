@extends('layouts.superadmin')

@section('title', 'Документы')
@section('page-title', 'Документы системы')
@section('page-subtitle', 'Поиск, редактирование и безопасное удаление документов')

@section('content')
<div class="card mb-6">
    <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto_auto]">
        <label class="sr-only" for="document-search">Поиск документов</label>
        <input id="document-search" type="search" name="search" value="{{ request('search') }}"
               placeholder="Название, номер или автор"
               class="admin-input">
        <label class="sr-only" for="document-status">Статус</label>
        <select id="document-status" name="status" class="admin-input">
            <option value="">Все статусы</option>
            @foreach([
                'draft' => 'Черновик',
                'active' => 'Активный',
                'processing' => 'В обработке',
                'completed' => 'Завершён',
                'approved' => 'Согласован',
                'rejected' => 'Отклонён',
                'failed' => 'Ошибка',
            ] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary justify-center">Найти</button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('superadmin.documents.index') }}" class="btn-ghost text-center">Сбросить</a>
        @endif
    </form>
</div>

<div class="card !p-0">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Все документы</h2>
            <p class="mt-1 text-sm text-slate-500">Найдено: {{ number_format($documents->total()) }}</p>
        </div>
        <a href="{{ route('superadmin.documents.create') }}" class="btn-primary">Добавить документ</a>
    </div>

    <div class="table-wrap !border-0">
        <table>
            <thead>
            <tr>
                <th>Документ</th>
                <th>Автор</th>
                <th>Статус</th>
                <th>Подписи</th>
                <th>Создан</th>
                <th class="text-right">Действия</th>
            </tr>
            </thead>
            <tbody>
            @forelse($documents as $document)
                <tr>
                    <td>
                        <div class="font-semibold text-slate-900">{{ $document->title }}</div>
                        <div class="mt-1 text-xs text-slate-500">
                            {{ $document->number ?: 'Без номера' }} · {{ $document->type }}
                        </div>
                    </td>
                    <td>
                        <div class="text-slate-800">{{ $document->createdBy?->name ?? 'Пользователь удалён' }}</div>
                        <div class="text-xs text-slate-500">{{ $document->createdBy?->email }}</div>
                    </td>
                    <td><span class="badge badge-document">{{ $document->status }}</span></td>
                    <td>
                        <span>{{ $document->signatures_count }}</span>
                        <span class="text-slate-400">/ {{ $document->comments_count }} комм.</span>
                    </td>
                    <td>{{ $document->created_at?->format('d.m.Y H:i') }}</td>
                    <td>
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('superadmin.documents.edit', $document) }}" class="btn-ghost">Изменить</a>
                            <form action="{{ route('superadmin.documents.destroy', $document) }}" method="POST"
                                  onsubmit="return confirm('Удалить документ и связанные с ним подписи, комментарии и историю?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger">Удалить</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-slate-500">Документы не найдены.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($documents->hasPages())
        <div class="border-t border-slate-200 p-4">{{ $documents->links() }}</div>
    @endif
</div>
@endsection
