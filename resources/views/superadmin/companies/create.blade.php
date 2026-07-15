@extends('layouts.superadmin')

@section('title', 'Создание компании')
@section('page-title', '🏢 Создание новой компании')
@section('page-subtitle', 'Добавление компании в систему')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card">
        <form action="{{ route('superadmin.companies.store') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-semibold text-zinc-300 mb-2">Название компании *</label>
                <input type="text" name="name"
                       class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-red-500/50"
                       value="{{ old('name') }}" placeholder="Например: Алиф Банк" required>
                @error('name')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-zinc-300 mb-2">Email</label>
                <input type="email" name="email"
                       class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-red-500/50"
                       value="{{ old('email') }}" placeholder="info@company.com">
                @error('email')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-zinc-300 mb-2">Телефон</label>
                <input type="text" name="phone"
                       class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-red-500/50"
                       value="{{ old('phone') }}" placeholder="+992 XX XXX XX XX">
                @error('phone')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-zinc-300 mb-2">Адрес</label>
                <textarea name="address" rows="3"
                          class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-red-500/50"
                          placeholder="Полный адрес компании">{{ old('address') }}</textarea>
                @error('address')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-white/10">
                <a href="{{ route('superadmin.companies.index') }}" class="btn-ghost">
                    Отмена
                </a>
                <button type="submit" class="btn-primary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Создать компанию
                </button>
            </div>
        </form>
    </div>
</div>
@endsection