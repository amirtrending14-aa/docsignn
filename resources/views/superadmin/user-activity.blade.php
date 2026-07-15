@extends('layouts.superadmin')

@section('title', 'Активность пользователя')
@section('page-title', $user->name)
@section('page-subtitle', 'История активности и документы')

@section('content')
{{-- Информация о пользователе --}}
<div class="card mb-6">
    <div class="flex items-center gap-4">
        <div class="avatar w-16 h-16 text-xl">
            @if($user->avatar)
            <img src="{{ asset('storage/' . $user->avatar) }}" alt="">
            @else
            {{ Str::upper(Str::substr($user->name, 0, 1)) }}
            @endif
        </div>
        <div class="flex-1">
            <h2 class="text-xl font-bold text-white mb-1">{{ $user->name }}</h2>
            <div class="flex items-center gap-3 text-sm text-zinc-400">
                <span>{{ $user->email }}</span>
                <span>•</span>
                <span>{{ $user->role }}</span>
                <span>•</span>
                <span>Уровень {{ $user->level }}</span>
            </div>
        </div>
        <div class="text-right">
            @if($user->isOnline())
            <span class="badge badge-online">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                    Online
                </span>
            @else
            <span class="badge badge-offline">Offline</span>
            @endif
        </div>
    </div>
</div>

{{-- Статистика --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="card stat-card" style="--accent-from: #3b82f6; --accent-to: #1d4ed8">
        <div class="text-2xl font-bold text-white mb-1">{{ $documents->total() }}</div>
        <div class="text-xs text-zinc-500 uppercase tracking-wider font-semibold">Всего документов</div>
    </div>

    <div class="card stat-card" style="--accent-from: #22c55e; --accent-to: #15803d">
        <div class="text-2xl font-bold text-white mb-1">
            {{ $documents->where('created_at', '>=', now()->subDays(7))->count() }}
        </div>
        <div class="text-xs text-zinc-500 uppercase tracking-wider font-semibold">За 7 дней</div>
    </div>

    <div class="card stat-card" style="--accent-from: #a855f7; --accent-to: #7c3aed">
        <div class="text-2xl font-bold text-white mb-1">
            {{ $documents->where('created_at', '>=', now()->subMonth())->count() }}
        </div>
        <div class="text-xs text-zinc-500 uppercase tracking-wider font-semibold">За месяц</div>
    </div>

    <div class="card stat-card" style="--accent-from: #f59e0b; --accent-to: #d97706">
        <div class="text-2xl font-bold text-white mb-1">
            {{ $documents->where('created_at', '>=', today())->count() }}
        </div>
        <div class="text-xs text-zinc-500 uppercase tracking-wider font-semibold">Сегодня</div>
    </div>
</div>

{{-- Список документов --}}
<div class="card p-0">
    <div class="flex items-center justify-between p-4 border-b border-white/5">
        <h2 class="text-base font-bold text-white">История документов</h2>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Тип</th>
                <th>Статус</th>
                <th>Создан</th>
                <th>Обновлён</th>
            </tr>
            </thead>
            <tbody>
            @forelse($documents as $doc)
            <tr>
                <td class="text-xs font-mono text-zinc-500">#{{ $doc->id }}</td>
                <td>
                    <div class="font-semibold text-white">{{ $doc->title ?? 'Без названия' }}</div>
                    @if($doc->description)
                    <div class="text-xs text-zinc-500 truncate max-w-xs">{{ Str::limit($doc->description, 50) }}</div>
                    @endif
                </td>
                <td>
                    <span class="text-xs text-zinc-400">{{ $doc->type ?? '—' }}</span>
                </td>
                <td>
                    <span class="badge badge-admin">{{ $doc->status ?? 'draft' }}</span>
                </td>
                <td class="text-xs text-zinc-500">
                    @if($doc->created_at)
                    <div>{{ $doc->created_at->format('d.m.Y') }}</div>
                    <div class="text-[10px]">{{ $doc->created_at->diffForHumans() }}</div>
                    @else
                    <span class="text-zinc-600">—</span>
                    @endif
                </td>
                <td class="text-xs text-zinc-500">
                    @if($doc->updated_at)
                    <div>{{ $doc->updated_at->format('d.m.Y') }}</div>
                    <div class="text-[10px]">{{ $doc->updated_at->diffForHumans() }}</div>
                    @else
                    <span class="text-zinc-600">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-8 text-zinc-500">
                    <div class="flex flex-col items-center gap-2">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-8 h-8 opacity-50">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Документы не найдены</span>
                    </div>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($documents->hasPages())
    <div class="p-4 border-t border-white/5">
        {{ $documents->links() }}
    </div>
    @endif
</div>
@endsection