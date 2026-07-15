@extends('layouts.superadmin')

@section('title', 'Дашборд')
@section('page-title', 'Центр управления')
@section('page-subtitle', 'Обзор системы в реальном времени')

@section('content')
{{-- Статистика --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    @php
    $statCards = [
    ['value' => $stats['total_users'], 'label' => 'Пользователей', 'from' => '#3b82f6', 'to' => '#1d4ed8', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
    ['value' => $stats['total_companies'], 'label' => 'Компаний', 'from' => '#8b5cf6', 'to' => '#6d28d9', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
    ['value' => $stats['online_now'], 'label' => 'Онлайн', 'from' => '#22c55e', 'to' => '#15803d', 'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
    ['value' => $stats['admins'], 'label' => 'Админов', 'from' => '#eab308', 'to' => '#a16207', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
    ['value' => $stats['documents'], 'label' => 'Документов', 'from' => '#ec4899', 'to' => '#be185d', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ['value' => $stats['new_users_today'], 'label' => 'Новых сегодня', 'from' => '#06b6d4', 'to' => '#0e7490', 'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6'],
    ];
    @endphp

    @foreach($statCards as $card)
    <div class="card stat-card" style="--accent-from: {{ $card['from'] }}; --accent-to: {{ $card['to'] }}">
        <div class="flex items-center justify-between mb-2">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, {{ $card['from'] }}20, {{ $card['to'] }}20); border: 1px solid {{ $card['from'] }}40;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4" style="color: {{ $card['from'] }}">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                </svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-white mb-0.5">{{ number_format($card['value']) }}</div>
        <div class="text-[10px] text-zinc-500 uppercase tracking-wider font-semibold">{{ $card['label'] }}</div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Последние пользователи --}}
    <div class="card lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-white">Последние пользователи</h2>
            <a href="{{ route('superadmin.users.index') }}" class="btn-ghost">Все →</a>
        </div>

        <div class="space-y-2">
            @foreach($recentUsers as $user)
            <div class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-white/5 transition">
                <div class="avatar">
                    @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="">
                    @else
                    {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <div class="text-sm font-semibold text-white truncate">{{ $user->name }}</div>
                        @if($user->isSuperAdmin())
                        <span class="badge badge-super">Super</span>
                        @elseif($user->isAdmin())
                        <span class="badge badge-admin">Admin</span>
                        @endif
                    </div>
                    <div class="text-xs text-zinc-500 truncate">
                        {{ $user->email }} • {{ $user->companyRelation->name ?? 'Без компании' }}
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
                    <div class="text-[10px] text-zinc-600 mt-1">{{ $user->created_at->diffForHumans() }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Компании --}}
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-white">Компании</h2>
            <a href="{{ route('superadmin.companies.index') }}" class="btn-ghost">Все →</a>
        </div>

        <div class="space-y-2">
            @foreach($recentCompanies as $company)
            <div class="p-3 rounded-lg bg-white/5 border border-white/5">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-sm font-semibold text-white truncate">{{ $company->name }}</div>
                    <span class="text-xs text-zinc-400">{{ $company->users_count }}</span>
                </div>
                <div class="text-[11px] text-zinc-500">
                    Владелец: {{ $company->owner->name ?? '—' }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection