@extends('layouts.superadmin')

@section('title', 'Все пользователи')
@section('page-title', 'Пользователи системы')
@section('page-subtitle', 'Управление всеми пользователями всех компаний')

@section('content')
<div class="bg-zinc-900/50 backdrop-blur-sm border border-white/10 rounded-xl p-4 mb-6">
    <div class="bg-zinc-900/50 backdrop-blur-sm border border-white/10 rounded-xl p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <!-- Поле поиска -->
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Поиск по имени, email, телефону..."
                   class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20 transition-all">

            <!-- Выбор компании (с принудительной темной темой) -->
            <select name="company_id"
                    class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20 appearance-none cursor-pointer [&>option]:bg-zinc-900 [&>option]:text-white"
                    style="color-scheme: dark;">
                <option value="" class="bg-zinc-900 text-white">Все компании</option>
                @foreach($companies as $company)
                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }} class="bg-zinc-900 text-white">
                {{ $company->name }}
                </option>
                @endforeach
            </select>

            <!-- Выбор статуса (с принудительной темной темой) -->
            <select name="status"
                    class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20 appearance-none cursor-pointer [&>option]:bg-zinc-900 [&>option]:text-white"
                    style="color-scheme: dark;">
                <option value="" class="bg-zinc-900 text-white">Все статусы</option>
                <option value="online" {{ request('status') === 'online' ? 'selected' : '' }} class="bg-zinc-900 text-white">Онлайн</option>
                <option value="offline" {{ request('status') === 'offline' ? 'selected' : '' }} class="bg-zinc-900 text-white">Офлайн</option>
            </select>

            <!-- Кнопка фильтра -->
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg px-4 py-2 text-sm flex items-center justify-center gap-2 transition-all shadow-lg shadow-red-500/20 hover:shadow-red-500/40 active:scale-[0.98]">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Фильтр
            </button>
        </form>
    </div>
</div>

<div class="bg-zinc-900/50 backdrop-blur-sm border border-white/10 rounded-xl overflow-hidden">
    <div class="flex items-center justify-between p-4 border-b border-white/10">
        <h2 class="text-base font-bold text-white">
            Всего: <span class="text-red-400 font-semibold">{{ $users->total() }}</span>
        </h2>
        <a href="{{ route('superadmin.users.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg px-4 py-2 text-sm flex items-center gap-2 transition-all shadow-lg shadow-green-500/20 hover:shadow-green-500/40">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Добавить
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-zinc-800/50 border-b border-white/10">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Пользователь</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Компания</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Роль</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Уровень</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Статус</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Создан</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-400 uppercase tracking-wider">Действия</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
            @forelse($users as $user)
            <tr class="hover:bg-white/5 transition-colors">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm ring-2 ring-white/10">
                            @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="" class="w-10 h-10 rounded-full object-cover">
                            @else
                            {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <div class="font-semibold text-white">{{ $user->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-sm text-zinc-300">{{ $user->companyRelation->name ?? '—' }}</td>
                <td class="px-4 py-3">
                    @if($user->isSuperAdmin())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-500/20 text-purple-300 border border-purple-500/30">Super</span>
                    @elseif($user->isAdmin())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 text-blue-300 border border-blue-500/30">Admin</span>
                    @else
                    <span class="text-xs text-zinc-400">{{ $user->role }}</span>
                    @endif
                </td>
                <td class="px-4 py-3"><span class="text-xs font-mono font-semibold text-zinc-300 bg-zinc-800 px-2 py-1 rounded border border-white/10">L{{ $user->level }}</span></td>
                <td class="px-4 py-3">
                    @if($user->isOnline())
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-300 border border-green-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                        Online
                    </span>
                    @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-800 text-zinc-400 border border-white/10">Offline</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-zinc-500">{{ $user->created_at->format('d.m.Y') }}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('superadmin.users.activity', $user->id) }}"
                           class="p-2 text-blue-400 hover:text-blue-300 hover:bg-blue-500/10 rounded-lg transition-colors"
                           title="Активность">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </a>
                        <a href="{{ route('superadmin.users.edit', $user->id) }}"
                           class="p-2 text-zinc-400 hover:text-zinc-300 hover:bg-white/10 rounded-lg transition-colors"
                           title="Редактировать">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('superadmin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Удалить пользователя?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="p-2 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-colors"
                                    title="Удалить">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-zinc-500">Пользователи не найдены</td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="p-4 border-t border-white/10">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection