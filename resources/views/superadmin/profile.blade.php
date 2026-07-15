@extends('layouts.superadmin')

@section('title', 'Мой профиль')
@section('page-title', 'Мой профиль')
@section('page-subtitle', 'Управление личной информацией')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Левая колонка - Аватар и информация --}}
    <div class="lg:col-span-1">
        <div class="card text-center">
            <div class="mb-6">
                <div class="relative inline-block">
                    <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-red-500/30 shadow-2xl shadow-red-500/20 mx-auto">
                        @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="" class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-4xl font-bold text-white">
                            {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                        </div>
                        @endif
                    </div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-green-500 border-4 border-black flex items-center justify-center">
                        <div class="w-2 h-2 rounded-full bg-white"></div>
                    </div>
                </div>
            </div>

            <h2 class="text-xl font-bold text-white mb-1">{{ $user->name }}</h2>
            <p class="text-sm text-zinc-400 mb-4">{{ $user->email }}</p>

            <div class="space-y-2 text-left">
                <div class="flex items-center gap-2 text-sm">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-red-400">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span class="text-zinc-300">Super Administrator</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-blue-400">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span class="text-zinc-300">{{ $user->phone ?? 'Не указан' }}</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-green-400">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-zinc-300">Регистрация: {{ $user->created_at->format('d.m.Y') }}</span>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-white/10">
                <div class="grid grid-cols-2 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $user->level }}</div>
                        <div class="text-xs text-zinc-500 uppercase">Уровень</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $user->documents_count ?? 0 }}</div>
                        <div class="text-xs text-zinc-500 uppercase">Документов</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Правая колонка - Форма редактирования --}}
    <div class="lg:col-span-2">
        <div class="card">
            <h3 class="text-lg font-bold text-white mb-6">Редактировать профиль</h3>

            <form action="{{ route('superadmin.profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Имя *</label>
                        <input type="text" name="name"
                               class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-red-500/50"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Email *</label>
                        <input type="email" name="email"
                               class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-red-500/50"
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">Телефон</label>
                    <input type="text" name="phone"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-red-500/50"
                           value="{{ old('phone', $user->phone) }}" placeholder="+992 XXX XX XX XX">
                    @error('phone')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">Аватар</label>
                    <input type="file" name="avatar" accept="image/*"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-red-500/20 file:text-red-400 hover:file:bg-red-500/30">
                    @error('avatar')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- Блок смены пароля с повышенной безопасностью --}}
                <div class="border-t border-white/10 pt-4 mt-6">
                    <div class="flex items-center gap-2 mb-4">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-red-400">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <h4 class="text-sm font-bold text-zinc-300">Изменить пароль</h4>
                        <span class="text-xs text-zinc-500 font-normal">(оставьте пустым, если не меняете)</span>
                    </div>

                    {{-- Текущий пароль --}}
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">
                            Текущий пароль <span class="text-red-400">*</span>
                            <span class="text-xs text-zinc-500 font-normal">(обязательно при смене пароля)</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="current_password" id="current_password"
                                   autocomplete="current-password"
                                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 pr-10 text-white placeholder-zinc-500 focus:outline-none focus:border-red-500/50"
                                   placeholder="Введите текущий пароль">
                            <button type="button" onclick="togglePassword('current_password', this)"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-zinc-300 transition">
                                <svg id="eye-current_password" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        @error('current_password')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Индикатор сложности пароля --}}
                    <div id="passwordStrength" class="mb-4 hidden">
                        <div class="flex gap-1 mb-1">
                            <div id="str1" class="h-1 flex-1 rounded bg-white/10"></div>
                            <div id="str2" class="h-1 flex-1 rounded bg-white/10"></div>
                            <div id="str3" class="h-1 flex-1 rounded bg-white/10"></div>
                            <div id="str4" class="h-1 flex-1 rounded bg-white/10"></div>
                        </div>
                        <div id="strengthText" class="text-xs text-zinc-500"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Новый пароль --}}
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Новый пароль</label>
                            <div class="relative">
                                <input type="password" name="password" id="password"
                                       autocomplete="new-password"
                                       oninput="checkPasswordStrength(this.value)"
                                       class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 pr-10 text-white placeholder-zinc-500 focus:outline-none focus:border-red-500/50"
                                       placeholder="Минимум 8 символов">
                                <button type="button" onclick="togglePassword('password', this)"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-zinc-300 transition">
                                    <svg id="eye-password" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>

                        {{-- Подтверждение пароля --}}
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Подтверждение пароля</label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       autocomplete="new-password"
                                       class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 pr-10 text-white placeholder-zinc-500 focus:outline-none focus:border-red-500/50"
                                       placeholder="Повторите новый пароль">
                                <button type="button" onclick="togglePassword('password_confirmation', this)"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-zinc-300 transition">
                                    <svg id="eye-password_confirmation" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            <div id="matchError" class="text-red-400 text-xs mt-1 hidden">Пароли не совпадают</div>
                        </div>
                    </div>

                    {{-- Требования к паролю --}}
                    <div class="mt-4 p-3 bg-white/5 rounded-lg border border-white/10">
                        <div class="text-xs text-zinc-400 mb-2 font-semibold">Требования к паролю:</div>
                        <div class="grid grid-cols-2 gap-1 text-xs">
                            <div class="flex items-center gap-1" id="req-length">
                                <span class="text-zinc-600">○</span>
                                <span class="text-zinc-500">Минимум 8 символов</span>
                            </div>
                            <div class="flex items-center gap-1" id="req-upper">
                                <span class="text-zinc-600">○</span>
                                <span class="text-zinc-500">Заглавная буква</span>
                            </div>
                            <div class="flex items-center gap-1" id="req-lower">
                                <span class="text-zinc-600">○</span>
                                <span class="text-zinc-500">Строчная буква</span>
                            </div>
                            <div class="flex items-center gap-1" id="req-number">
                                <span class="text-zinc-600">○</span>
                                <span class="text-zinc-500">Цифра</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-white/10">
                    <a href="{{ route('superadmin.dashboard') }}" class="btn-ghost">Отмена</a>
                    <button type="submit" class="btn-primary" id="submitBtn">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Показ/скрытие пароля
    function togglePassword(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const eyeIcon = document.getElementById('eye-' + fieldId);

        if (input.type === 'password') {
            input.type = 'text';
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
            `;
        } else {
            input.type = 'password';
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            `;
        }
    }

    // Проверка сложности пароля
    function checkPasswordStrength(password) {
        const strengthDiv = document.getElementById('passwordStrength');
        const bars = [document.getElementById('str1'), document.getElementById('str2'),
                     document.getElementById('str3'), document.getElementById('str4')];
        const strengthText = document.getElementById('strengthText');

        if (!password) {
            strengthDiv.classList.add('hidden');
            updateRequirements(password);
            return;
        }

        strengthDiv.classList.remove('hidden');

        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        const colors = ['#ef4444', '#f59e0b', '#eab308', '#22c55e'];
        const texts = ['Слабый', 'Средний', 'Хороший', 'Отличный'];

        bars.forEach((bar, i) => {
            if (i < strength) {
                bar.style.background = colors[strength - 1];
            } else {
                bar.style.background = 'rgba(255,255,255,0.1)';
            }
        });

        strengthText.textContent = texts[strength - 1] || '';
        strengthText.style.color = colors[strength - 1] || '#71717a';

        updateRequirements(password);
        checkPasswordMatch();
    }

    // Обновление требований
    function updateRequirements(password) {
        const reqs = {
            'req-length': password.length >= 8,
            'req-upper': /[A-Z]/.test(password),
            'req-lower': /[a-z]/.test(password),
            'req-number': /\d/.test(password)
        };

        for (const [id, met] of Object.entries(reqs)) {
            const el = document.getElementById(id);
            const span = el.querySelector('span:first-child');
            const text = el.querySelector('span:last-child');

            if (met) {
                span.textContent = '●';
                span.className = 'text-green-400';
                text.className = 'text-green-400';
            } else {
                span.textContent = '○';
                span.className = 'text-zinc-600';
                text.className = 'text-zinc-500';
            }
        }
    }

    // Проверка совпадения паролей
    function checkPasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;
        const errorDiv = document.getElementById('matchError');

        if (confirmation && password !== confirmation) {
            errorDiv.classList.remove('hidden');
        } else {
            errorDiv.classList.add('hidden');
        }
    }

    document.getElementById('password_confirmation').addEventListener('input', checkPasswordMatch);

    // Валидация формы перед отправкой
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;
        const currentPassword = document.getElementById('current_password').value;

        // Если заполнено хотя бы одно поле пароля - проверяем все
        if (password || confirmation || currentPassword) {
            if (!currentPassword) {
                e.preventDefault();
                alert('⚠️ Для смены пароля необходимо ввести текущий пароль');
                document.getElementById('current_password').focus();
                return false;
            }

            if (!password) {
                e.preventDefault();
                alert('⚠️ Введите новый пароль');
                document.getElementById('password').focus();
                return false;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('⚠️ Новый пароль должен содержать минимум 8 символов');
                document.getElementById('password').focus();
                return false;
            }

            if (password !== confirmation) {
                e.preventDefault();
                alert('⚠️ Пароли не совпадают');
                document.getElementById('password_confirmation').focus();
                return false;
            }

            if (password === currentPassword) {
                e.preventDefault();
                alert('⚠️ Новый пароль должен отличаться от текущего');
                document.getElementById('password').focus();
                return false;
            }
        }
    });
</script>
@endsection