@extends('layouts.superadmin')

@section('title', 'Редактирование пользователя')
@section('page-title', '✏️ Редактирование: ' . $user->name)
@section('page-subtitle', 'Изменение данных пользователя')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Карточка профиля --}}
    <div class="bg-zinc-900/50 backdrop-blur-sm border border-white/10 rounded-xl p-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-red-500 to-purple-600 flex items-center justify-center text-white font-bold text-2xl ring-2 ring-white/10 shadow-lg">
                @if($user->avatar)
                <img src="{{ asset('storage/' . $user->avatar) }}" alt="" class="w-16 h-16 rounded-full object-cover">
                @else
                {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                @endif
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-white">{{ $user->name }}</h3>
                <p class="text-sm text-zinc-400">{{ $user->email }}</p>
                <div class="flex items-center gap-2 mt-1">
                    @if($user->isSuperAdmin())
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-500/20 text-purple-300 border border-purple-500/30">Super Admin</span>
                    @elseif($user->isAdmin())
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 text-blue-300 border border-blue-500/30">Admin</span>
                    @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-700 text-zinc-300 border border-white/10">Сотрудник</span>
                    @endif
                    <span class="text-xs text-zinc-500">•</span>
                    <span class="text-xs text-zinc-500">Уровень {{ $user->level }}</span>
                    <span class="text-xs text-zinc-500">•</span>
                    @if($user->isOnline())
                    <span class="inline-flex items-center gap-1 text-xs text-green-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                        Онлайн
                    </span>
                    @else
                    <span class="text-xs text-zinc-500">Офлайн</span>
                    @endif
                </div>
            </div>
            <div class="text-right hidden md:block">
                <div class="text-xs text-zinc-500">Создан</div>
                <div class="text-sm text-zinc-300">{{ $user->created_at->format('d.m.Y H:i') }}</div>
                @if($user->updated_at != $user->created_at)
                <div class="text-xs text-zinc-500 mt-1">Обновлен</div>
                <div class="text-sm text-zinc-300">{{ $user->updated_at->format('d.m.Y H:i') }}</div>
                @endif
            </div>
        </div>
    </div>

    <form action="{{ route('superadmin.users.update', $user) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Основная информация --}}
        <div class="bg-zinc-900/50 backdrop-blur-sm border border-white/10 rounded-xl p-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-blue-400"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <h2 class="text-base font-bold text-white">Основная информация</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">
                        Имя <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="name"
                           class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20 transition-all"
                           value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="text-red-400 text-xs mt-1 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">
                        Email <span class="text-red-400">*</span>
                    </label>
                    <input type="email" name="email"
                           class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20 transition-all"
                           value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="text-red-400 text-xs mt-1 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">Телефон</label>
                    <input type="text" name="phone"
                           class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20 transition-all"
                           value="{{ old('phone', $user->phone) }}" placeholder="+992 XXX XX XX XX">
                    @error('phone')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">Уровень</label>
                    <input type="number" name="level" min="1" max="20"
                           class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20 transition-all"
                           value="{{ old('level', $user->level) }}">
                    @error('level')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Безопасность --}}
        <div class="bg-zinc-900/50 backdrop-blur-sm border border-white/10 rounded-xl p-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-8 h-8 rounded-lg bg-green-500/20 flex items-center justify-center">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-green-400"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h2 class="text-base font-bold text-white">Безопасность</h2>
                <span class="text-xs text-zinc-500 ml-2">Оставьте пустым, если не хотите менять пароль</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">Новый пароль</label>
                    <input type="password" name="password" id="password"
                           class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20 transition-all"
                           placeholder="••••••••">
                    @error('password')<div class="text-red-400 text-xs mt-1 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">Подтверждение пароля</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20 transition-all"
                           placeholder="••••••••">
                </div>
            </div>
        </div>

        {{-- Права и доступ --}}
        <div class="bg-zinc-900/50 backdrop-blur-sm border border-white/10 rounded-xl p-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-purple-400"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h2 class="text-base font-bold text-white">Права и доступ</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">
                        Роль <span class="text-red-400">*</span>
                    </label>
                    <select name="role"
                            class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20 appearance-none cursor-pointer [&>option]:bg-zinc-900 [&>option]:text-white transition-all"
                            style="color-scheme: dark;" required>
                        <option value="employee" class="bg-zinc-900 text-white" {{ (old('role', $user->role) == 'employee') ? 'selected' : '' }}>Сотрудник</option>
                        <option value="admin" class="bg-zinc-900 text-white" {{ (old('role', $user->role) == 'admin') ? 'selected' : '' }}>Админ</option>
                        <option value="super_admin" class="bg-zinc-900 text-white" {{ (old('role', $user->role) == 'super_admin' || $user->is_super_admin) ? 'selected' : '' }}>Супер Админ</option>
                    </select>
                    @error('role')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">Компания</label>
                    <select name="company_id"
                            class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20 appearance-none cursor-pointer [&>option]:bg-zinc-900 [&>option]:text-white transition-all"
                            style="color-scheme: dark;">
                        <option value="" class="bg-zinc-900 text-white">Без компании</option>
                        @foreach($companies as $company)
                        <option value="{{ $company->id }}" class="bg-zinc-900 text-white" {{ (old('company_id', $user->company_id) == $company->id) ? 'selected' : '' }}>
                        {{ $company->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('company_id')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="flex items-end">
                    <label class="flex items-center gap-3 cursor-pointer group w-full bg-zinc-800 border border-white/20 rounded-lg px-4 py-2.5 hover:border-red-500/50 transition-all">
                        <input type="checkbox" name="is_admin" value="1"
                               {{ (old('is_admin', $user->is_admin)) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-white/30 bg-zinc-700 text-red-500 focus:ring-red-500/20 focus:ring-offset-0 cursor-pointer accent-red-500">
                        <div>
                            <div class="text-sm font-semibold text-white">Администратор</div>
                            <div class="text-xs text-zinc-500">Полный доступ к компании</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Аватар --}}
        <div class="bg-zinc-900/50 backdrop-blur-sm border border-white/10 rounded-xl p-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-8 h-8 rounded-lg bg-orange-500/20 flex items-center justify-center">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-orange-400"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h2 class="text-base font-bold text-white">Аватар</h2>
            </div>

            <div class="flex items-center gap-6">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-red-500 to-purple-600 flex items-center justify-center text-white font-bold text-3xl ring-2 ring-white/10 shadow-lg flex-shrink-0">
                    @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="" class="w-24 h-24 rounded-full object-cover">
                    @else
                    {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div class="flex-1">
                    <label class="block">
                        <input type="file" name="avatar" accept="image/*" class="hidden" onchange="updateFileName(this)">
                        <span class="inline-flex items-center gap-2 bg-zinc-800 hover:bg-zinc-700 border border-white/20 hover:border-red-500/50 text-white font-medium rounded-lg px-4 py-2.5 text-sm cursor-pointer transition-all">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Выбрать файл
                        </span>
                        <span id="file-name" class="ml-3 text-sm text-zinc-500">PNG, JPG до 2MB</span>
                    </label>
                    @if($user->avatar)
                    <p class="text-xs text-zinc-500 mt-2">Текущий аватар будет заменен при загрузке нового</p>
                    @endif
                    @error('avatar')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Кнопки действий --}}
        <div class="bg-zinc-900/50 backdrop-blur-sm border border-white/10 rounded-xl p-6">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-sm text-zinc-500">
                    <span class="text-red-400">*</span> — обязательные поля
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('superadmin.users.index') }}"
                       class="flex-1 sm:flex-none bg-zinc-800 hover:bg-zinc-700 text-white font-medium rounded-lg px-5 py-2.5 text-sm flex items-center justify-center gap-2 transition-all border border-white/10">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Отмена
                    </a>
                    <button type="submit"
                            class="flex-1 sm:flex-none bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg px-5 py-2.5 text-sm flex items-center justify-center gap-2 transition-all shadow-lg shadow-red-500/20 hover:shadow-red-500/40 active:scale-[0.98]">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Сохранить изменения
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

<script>
    function updateFileName(input) {
        const fileName = document.getElementById('file-name');
        if (input.files && input.files[0]) {
            fileName.textContent = input.files[0].name;
            fileName.classList.add('text-green-400');
            fileName.classList.remove('text-zinc-500');
        }
    }
</script>
@endsection