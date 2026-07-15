@extends('layouts.superadmin')

@section('title', 'Редактирование компании')
@section('page-title', '✏️ Редактирование: ' . $company->name)
@section('page-subtitle', 'Изменение данных компании')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Левая колонка - Информация о компании --}}
    <div class="lg:col-span-1">
        <div class="card">
            <div class="text-center mb-6">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center mx-auto shadow-lg shadow-blue-900/30 mb-4">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-10 h-10 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-white mb-1">{{ $company->name }}</h2>
                <p class="text-sm text-zinc-400">ID: #{{ $company->id }}</p>
            </div>

            <div class="space-y-3 text-left">
                @if($company->owner)
                <div class="flex items-center gap-3 p-3 rounded-lg bg-white/5">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-600 to-purple-800 flex items-center justify-center text-sm font-bold text-white">
                        {{ Str::upper(Str::substr($company->owner->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs text-zinc-500 uppercase tracking-wider">Владелец</div>
                        <div class="text-sm font-semibold text-white truncate">{{ $company->owner->name }}</div>
                    </div>
                </div>
                @endif

                <div class="flex items-center gap-3 p-3 rounded-lg bg-white/5">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-blue-400">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <div class="flex-1">
                        <div class="text-xs text-zinc-500 uppercase tracking-wider">Пользователей</div>
                        <div class="text-sm font-semibold text-white">{{ $users->count() }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-lg bg-white/5">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-green-400">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <div class="flex-1">
                        <div class="text-xs text-zinc-500 uppercase tracking-wider">Создана</div>
                        <div class="text-sm font-semibold text-white">{{ $company->created_at->format('d.m.Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Правая колонка - Форма редактирования --}}
    <div class="lg:col-span-2">
        <div class="card">
            <h3 class="text-lg font-bold text-white mb-6">Редактировать компанию</h3>

            <form action="{{ route('superadmin.companies.update', $company->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">Название компании *</label>
                    <input type="text" name="name"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50"
                           value="{{ old('name', $company->name) }}" required>
                    @error('name')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Email</label>
                        <input type="email" name="email"
                               class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50"
                               value="{{ old('email', $company->email) }}" placeholder="company@example.com">
                        @error('email')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Телефон</label>
                        <input type="text" name="phone"
                               class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50"
                               value="{{ old('phone', $company->phone) }}" placeholder="+992 XXX XX XX XX">
                        @error('phone')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">Адрес</label>
                    <textarea name="address" rows="3"
                              class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50 resize-none"
                              placeholder="Полный адрес компании">{{ old('address', $company->address) }}</textarea>
                    @error('address')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-white/10">
                    <a href="{{ route('superadmin.companies.index') }}" class="btn-ghost">Отмена</a>
                    <button type="submit" class="btn-primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>

        {{-- Список пользователей компании --}}
        @if($users->count() > 0)
        <div class="card mt-6">
            <h3 class="text-lg font-bold text-white mb-4">Пользователи компании</h3>

            <div class="space-y-2">
                @foreach($users as $user)
                <div class="flex items-center gap-3 p-3 rounded-lg bg-white/5 hover:bg-white/10 transition">
                    <div class="avatar">
                        @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="">
                        @else
                        {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-white truncate">{{ $user->name }}</div>
                        <div class="text-xs text-zinc-500 truncate">{{ $user->email }}</div>
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
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection