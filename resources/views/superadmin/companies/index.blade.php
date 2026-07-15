@extends('layouts.superadmin')

@section('title', 'Компании')
@section('page-title', 'Все компании')
@section('page-subtitle', 'Управление всеми компаниями системы')

@section('content')
{{-- Статистика --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="card stat-card" style="--accent-from: #3b82f6; --accent-to: #1d4ed8">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-2xl font-bold text-white">{{ $companies->total() }}</div>
                <div class="text-xs text-zinc-500 uppercase tracking-wider font-semibold mt-1">Всего компаний</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-blue-400">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="card stat-card" style="--accent-from: #22c55e; --accent-to: #15803d">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-2xl font-bold text-white">{{ $companies->where('users_count', '>', 0)->count() }}</div>
                <div class="text-xs text-zinc-500 uppercase tracking-wider font-semibold mt-1">С пользователями</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-green-400">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="card stat-card" style="--accent-from: #a1a1aa; --accent-to: #71717a">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-2xl font-bold text-white">{{ $companies->where('users_count', 0)->count() }}</div>
                <div class="text-xs text-zinc-500 uppercase tracking-wider font-semibold mt-1">Без пользователей</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-zinc-500/20 flex items-center justify-center">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-zinc-400">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- Карточки компаний --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($companies as $company)
    <div class="card group hover:border-blue-500/30 transition-all duration-300">
        {{-- Заголовок карточки --}}
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center shadow-lg shadow-blue-900/30 group-hover:shadow-blue-500/40 transition-all">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-white truncate">{{ $company->name }}</h3>
                    <p class="text-xs text-zinc-500">ID: #{{ $company->id }}</p>
                </div>
            </div>
        </div>

        {{-- Информация --}}
        <div class="space-y-2 mb-4">
            @if($company->email)
            <div class="flex items-center gap-2 text-sm">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-blue-400 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span class="text-zinc-300 truncate">{{ $company->email }}</span>
            </div>
            @endif

            @if($company->phone)
            <div class="flex items-center gap-2 text-sm">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-green-400 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <span class="text-zinc-300">{{ $company->phone }}</span>
            </div>
            @endif

            @if($company->owner)
            <div class="flex items-center gap-2 text-sm">
                <div class="w-5 h-5 rounded-full bg-gradient-to-br from-purple-600 to-purple-800 flex items-center justify-center text-[10px] font-bold text-white flex-shrink-0">
                    {{ Str::upper(Str::substr($company->owner->name, 0, 1)) }}
                </div>
                <span class="text-zinc-300 truncate">{{ $company->owner->name }}</span>
            </div>
            @endif
        </div>

        {{-- Статистика --}}
        <div class="flex items-center justify-between pt-3 border-t border-white/5">
            <div class="flex items-center gap-2">
                <span class="badge badge-admin">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-3 h-3">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ $company->users_count }}
                </span>
            </div>
            <div class="text-xs text-zinc-500">
                {{ $company->created_at->format('d.m.Y') }}
            </div>
        </div>

        {{-- Кнопки действий --}}
        <div class="flex items-center gap-2 mt-4 pt-4 border-t border-white/5">
            {{-- Кнопка Показать --}}
            <a href="{{ route('superadmin.companies.show', $company->id) }}" class="btn-ghost flex-1 text-center text-blue-400 hover:text-blue-300 hover:bg-blue-500/10">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 inline">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Показать
            </a>

            {{-- Кнопка Редактировать --}}
            <a href="{{ route('superadmin.companies.edit', $company->id) }}" class="btn-ghost flex-1 text-center">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 inline">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Редактировать
            </a>

            {{-- Кнопка Удалить --}}
            @if($company->users_count == 0)
            <form action="{{ route('superadmin.companies.destroy', $company->id) }}" method="POST" onsubmit="return confirm('Удалить компанию?')" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-ghost w-full text-red-400 hover:text-red-300 hover:bg-red-500/10">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 inline">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Удалить
                </button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-full">
        <div class="card text-center py-12">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-16 h-16 text-zinc-600 mx-auto mb-4">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="text-zinc-500">Компании не найдены</p>
        </div>
    </div>
    @endforelse
</div>

{{-- Пагинация --}}
@if($companies->hasPages())
<div class="mt-6">
    {{ $companies->links() }}
</div>
@endif
@endsection