@extends('layouts.superadmin')

@section('title', $company->name)
@section('page-title', $company->name)
@section('page-subtitle', 'Детальная информация о компании')

@section('content')
{{-- Шапка с основной информацией --}}
<div class="card mb-6 relative overflow-hidden">
    {{-- Фоновый градиент --}}
    <div class="absolute inset-0 bg-gradient-to-br from-blue-600/10 via-transparent to-purple-600/10"></div>

    <div class="relative flex flex-col md:flex-row items-start md:items-center gap-6">
        {{-- Иконка компании --}}
        <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center shadow-2xl shadow-blue-900/40 flex-shrink-0">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-12 h-12 text-white">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>

        {{-- Информация --}}
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
                <h2 class="text-2xl font-bold text-white">{{ $company->name }}</h2>
                <span class="badge badge-admin">ID #{{ $company->id }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                @if($company->email)
                <div class="flex items-center gap-2 text-sm">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-blue-400">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-zinc-300">{{ $company->email }}</span>
                </div>
                @endif

                @if($company->phone)
                <div class="flex items-center gap-2 text-sm">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-green-400">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span class="text-zinc-300">{{ $company->phone }}</span>
                </div>
                @endif

                @if($company->address)
                <div class="flex items-center gap-2 text-sm md:col-span-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-purple-400">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-zinc-300">{{ $company->address }}</span>
                </div>
                @endif

                <div class="flex items-center gap-2 text-sm">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-orange-400">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-zinc-300">Создана: {{ $company->created_at->format('d.m.Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Кнопки действий --}}
        <div class="flex flex-col gap-2">
            <a href="{{ route('superadmin.companies.edit', $company->id) }}" class="btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Редактировать
            </a>
            <a href="{{ route('superadmin.companies.index') }}" class="btn-ghost">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад
            </a>
        </div>
    </div>
</div>

{{-- Статистика --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="card stat-card" style="--accent-from: #3b82f6; --accent-to: #1d4ed8">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-2xl font-bold text-white">{{ $stats['total_users'] }}</div>
                <div class="text-xs text-zinc-500 uppercase tracking-wider font-semibold mt-1">Всего сотрудников</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-blue-400">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="card stat-card" style="--accent-from: #22c55e; --accent-to: #15803d">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-2xl font-bold text-white">{{ $stats['online_users'] }}</div>
                <div class="text-xs text-zinc-500 uppercase tracking-wider font-semibold mt-1">Сейчас онлайн</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-green-400">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="card stat-card" style="--accent-from: #a855f7; --accent-to: #7c3aed">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-2xl font-bold text-white">{{ $stats['total_documents'] }}</div>
                <div class="text-xs text-zinc-500 uppercase tracking-wider font-semibold mt-1">Документов</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-purple-400">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="card stat-card" style="--accent-from: #f59e0b; --accent-to: #d97706">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-2xl font-bold text-white">{{ $stats['admins'] }}</div>
                <div class="text-xs text-zinc-500 uppercase tracking-wider font-semibold mt-1">Администраторов</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-orange-500/20 flex items-center justify-center">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-orange-400">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Владелец --}}
    @if($company->owner)
    <div class="card">
        <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-purple-400">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Владелец компании
        </h3>

        <div class="text-center">
            <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-purple-500/30 shadow-xl shadow-purple-500/20 mx-auto mb-3">
                @if($company->owner->avatar)
                <img src="{{ asset('storage/' . $company->owner->avatar) }}" alt="" class="w-full h-full object-cover">
                @else
                <div class="w-full h-full bg-gradient-to-br from-purple-600 to-purple-800 flex items-center justify-center text-2xl font-bold text-white">
                    {{ Str::upper(Str::substr($company->owner->name, 0, 1)) }}
                </div>
                @endif
            </div>
            <h4 class="text-lg font-bold text-white mb-1">{{ $company->owner->name }}</h4>
            <p class="text-xs text-zinc-400 mb-3">{{ $company->owner->email }}</p>

            @if($company->owner->isOnline())
            <span class="badge badge-online">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                    Online
                </span>
            @else
            <span class="badge badge-offline">Offline</span>
            @endif
        </div>
    </div>
    @endif

    {{-- Последние документы --}}
    <div class="card lg:col-span-2">
        <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-purple-400">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Последние документы
        </h3>

        @if($documents->count() > 0)
        <div class="space-y-2">
            @foreach($documents as $doc)
            <div class="flex items-center gap-3 p-3 rounded-lg bg-white/5 hover:bg-white/10 transition">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-600 to-purple-800 flex items-center justify-center flex-shrink-0">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-white truncate">{{ $doc->title ?? 'Без названия' }}</div>
                    <div class="text-xs text-zinc-500">
                        {{ $doc->creator->name ?? 'Неизвестно' }} • {{ $doc->created_at->diffForHumans() }}
                    </div>
                </div>
                <div class="text-xs text-zinc-500">{{ $doc->created_at->format('d.m.Y') }}</div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8 text-zinc-500">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-12 h-12 mx-auto mb-2 opacity-50">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p>Документов пока нет</p>
        </div>
        @endif
    </div>
</div>

{{-- Сотрудники --}}
<div class="card mt-6">
    <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-blue-400">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Сотрудники компании
    </h3>

    @if($users->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($users as $user)
        <div class="flex items-center gap-3 p-3 rounded-lg bg-white/5 hover:bg-white/10 transition group">
            <div class="avatar group-hover:border-blue-500/50">
                @if($user->avatar)
                <img src="{{ asset('storage/' . $user->avatar) }}" alt="">
                @else
                {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <div class="text-sm font-semibold text-white truncate">{{ $user->name }}</div>
                    @if($user->isAdmin())
                    <span class="badge badge-admin text-[10px] py-0.5 px-1.5">Admin</span>
                    @endif
                </div>
                <div class="text-xs text-zinc-500 truncate">{{ $user->email }}</div>
            </div>
            <div class="text-right flex-shrink-0">
                @if($user->isOnline())
                <span class="badge badge-online text-[10px] py-0.5 px-1.5">
                        <span class="w-1 h-1 rounded-full bg-green-400 animate-pulse"></span>
                        Online
                    </span>
                @else
                <span class="badge badge-offline text-[10px] py-0.5 px-1.5">Offline</span>
                @endif
                <div class="text-[10px] text-zinc-600 mt-1">{{ $user->documents_count ?? 0 }} док.</div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-8 text-zinc-500">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-12 h-12 mx-auto mb-2 opacity-50">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <p>Сотрудников пока нет</p>
    </div>
    @endif
</div>
@endsection