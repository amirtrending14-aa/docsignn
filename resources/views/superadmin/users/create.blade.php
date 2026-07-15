@extends('layouts.superadmin')

@section('title', 'Создание пользователя')
@section('page-title', '✨ Создание нового пользователя')
@section('page-subtitle', 'Добавление нового участника в систему')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Левая колонка - Превью --}}
    <div class="lg:col-span-1">
        <div class="card sticky top-6">
            <div class="text-center mb-6">
                <div class="relative inline-block">
                    <div id="avatarPreview" class="w-32 h-32 rounded-full overflow-hidden border-4 border-blue-500/30 shadow-2xl shadow-blue-500/20 mx-auto bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center">
                        <svg id="avatarPlaceholder" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-16 h-16 text-white/50">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <img id="avatarImage" src="" alt="" class="w-full h-full object-cover hidden">
                    </div>
                </div>

                <h2 id="previewName" class="text-xl font-bold text-white mt-4 mb-1">Новый пользователь</h2>
                <p id="previewEmail" class="text-sm text-zinc-400">email@example.com</p>
            </div>

            <div class="space-y-3">
                <div class="p-3 rounded-lg bg-white/5 border border-white/5">
                    <div class="text-xs text-zinc-500 uppercase tracking-wider mb-1">Роль</div>
                    <div id="previewRole" class="text-sm font-semibold text-white">Сотрудник</div>
                </div>

                <div class="p-3 rounded-lg bg-white/5 border border-white/5">
                    <div class="text-xs text-zinc-500 uppercase tracking-wider mb-1">Компания</div>
                    <div id="previewCompany" class="text-sm font-semibold text-white">Не указана</div>
                </div>

                <div class="p-3 rounded-lg bg-white/5 border border-white/5">
                    <div class="text-xs text-zinc-500 uppercase tracking-wider mb-1">Телефон</div>
                    <div id="previewPhone" class="text-sm font-semibold text-white">—</div>
                </div>

                <div class="p-3 rounded-lg bg-white/5 border border-white/5">
                    <div class="text-xs text-zinc-500 uppercase tracking-wider mb-1">Уровень</div>
                    <div id="previewLevel" class="text-sm font-semibold text-white">L0 (Базовый)</div>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-white/10">
                <a href="{{ route('superadmin.users.index') }}" class="btn-ghost w-full text-center block">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 inline">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Назад к списку
                </a>
            </div>
        </div>
    </div>

    {{-- Правая колонка - Форма --}}
    <div class="lg:col-span-2">
        <div class="card">
            <form action="{{ route('superadmin.users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Основная информация --}}
                <div class="mb-6">
                    <h3 class="text-base font-bold text-white mb-4">👤 Основная информация</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Имя *</label>
                            <input type="text" name="name" id="inputName"
                                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50"
                                   value="{{ old('name') }}" placeholder="Иван Иванов" required>
                            @error('name')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Email *</label>
                            <input type="email" name="email" id="inputEmail"
                                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50"
                                   value="{{ old('email') }}" placeholder="user@example.com" required>
                            @error('email')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Безопасность --}}
                <div class="mb-6 pt-6 border-t border-white/5">
                    <h3 class="text-base font-bold text-white mb-4">🔒 Безопасность</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Пароль *</label>
                            <input type="password" name="password"
                                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50"
                                   placeholder="Минимум 6 символов" required>
                            @error('password')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Подтверждение пароля *</label>
                            <input type="password" name="password_confirmation"
                                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50"
                                   placeholder="Повторите пароль" required>
                        </div>
                    </div>
                </div>

                {{-- Роль и компания --}}
                <div class="mb-6 pt-6 border-t border-white/5">
                    <h3 class="text-base font-bold text-white mb-4">🏢 Роль и доступ</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Роль *</label>
                            <select name="role" id="inputRole"
                                    class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-blue-500/50" required>
                                <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Сотрудник (L0)</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Админ компании</option>
                                <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Супер Админ</option>
                            </select>
                            @error('role')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">
                                Уровень доступа
                                <span class="text-xs text-zinc-500 font-normal">(0 = базовый)</span>
                            </label>
                            <!-- ИСПРАВЛЕНО: min="0" и value="0" по умолчанию -->
                            <input type="number" name="level" id="inputLevel" min="0" max="20"
                                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-blue-500/50"
                                   value="{{ old('level', 0) }}">
                            @error('level')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Выбор компании --}}
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Компания</label>
                        <select name="company_id" id="inputCompany"
                                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-blue-500/50">
                            <option value="">— Без компании (Свободный агент) —</option>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('company_id')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Новая компания (появляется если выбрано "Без компании" и роль = admin) --}}
                    <div id="newCompanyBlock" class="mt-4 hidden">
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">
                            🆕 Создать новую компанию
                            <span class="text-xs text-zinc-500 font-normal">(только для Админа)</span>
                        </label>
                        <input type="text" name="new_company_name" id="inputNewCompany"
                               class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50"
                               value="{{ old('new_company_name') }}" placeholder="Например: Алиф Банк, DS City">
                        <div class="text-xs text-zinc-500 mt-1">
                            💡 Если оставить пустым, админ будет создан без привязки к компании.
                        </div>
                        @error('new_company_name')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Контакты --}}
                <div class="mb-6 pt-6 border-t border-white/5">
                    <h3 class="text-base font-bold text-white mb-4">📞 Контакты</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Телефон</label>
                            <input type="text" name="phone" id="inputPhone"
                                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50"
                                   value="{{ old('phone') }}" placeholder="+992 XXX XX XX XX">
                            @error('phone')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Аватар</label>
                            <input type="file" name="avatar" id="inputAvatar" accept="image/*"
                                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-500/20 file:text-blue-400 hover:file:bg-blue-500/30">
                            @error('avatar')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Кнопки --}}
                <div class="flex justify-end gap-3 pt-6 border-t border-white/10">
                    <a href="{{ route('superadmin.users.index') }}" class="btn-ghost">Отмена</a>
                    <button type="submit" class="btn-primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Создать пользователя
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputName = document.getElementById('inputName');
        const inputEmail = document.getElementById('inputEmail');
        const inputRole = document.getElementById('inputRole');
        const inputCompany = document.getElementById('inputCompany');
        const inputPhone = document.getElementById('inputPhone');
        const inputLevel = document.getElementById('inputLevel');
        const inputAvatar = document.getElementById('inputAvatar');
        const inputNewCompany = document.getElementById('inputNewCompany');
        const newCompanyBlock = document.getElementById('newCompanyBlock');

        const previewName = document.getElementById('previewName');
        const previewEmail = document.getElementById('previewEmail');
        const previewRole = document.getElementById('previewRole');
        const previewCompany = document.getElementById('previewCompany');
        const previewPhone = document.getElementById('previewPhone');
        const previewLevel = document.getElementById('previewLevel');
        const avatarImage = document.getElementById('avatarImage');
        const avatarPlaceholder = document.getElementById('avatarPlaceholder');

        const roleNames = {
            'employee': '👤 Сотрудник',
            'admin': '🛡️ Админ компании',
            'super_admin': '⚡ Супер Админ'
        };

        function checkNewCompanyVisibility() {
            const companyId = inputCompany.value;
            const role = inputRole.value;
            if (companyId === '' && role === 'admin') {
                newCompanyBlock.classList.remove('hidden');
            } else {
                newCompanyBlock.classList.add('hidden');
                inputNewCompany.value = '';
                // Сброс превью компании, если скрыли блок
                previewCompany.textContent = 'Не указана';
            }
        }

        inputName?.addEventListener('input', (e) => {
            previewName.textContent = e.target.value || 'Новый пользователь';
        });

        inputEmail?.addEventListener('input', (e) => {
            previewEmail.textContent = e.target.value || 'email@example.com';
        });

        inputRole?.addEventListener('change', (e) => {
            const role = e.target.value;
            previewRole.textContent = roleNames[role] || 'Сотрудник';
            checkNewCompanyVisibility();
        });

        inputCompany?.addEventListener('change', (e) => {
            const selected = e.target.options[e.target.selectedIndex];
            if (selected.value === '') {
                previewCompany.textContent = 'Не указана';
            } else {
                previewCompany.textContent = selected.text;
            }
            checkNewCompanyVisibility();
        });

        inputPhone?.addEventListener('input', (e) => {
            previewPhone.textContent = e.target.value || '—';
        });

        // ИСПРАВЛЕНО: Красивое отображение уровня 0
        inputLevel?.addEventListener('input', (e) => {
            let val = e.target.value;
            if (val === '' || val === '0') {
                previewLevel.textContent = 'L0 (Базовый)';
            } else {
                previewLevel.textContent = 'L' + val;
            }
        });

        inputAvatar?.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    avatarImage.src = event.target.result;
                    avatarImage.classList.remove('hidden');
                    avatarPlaceholder.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        inputNewCompany?.addEventListener('input', (e) => {
            if (e.target.value) {
                previewCompany.textContent = '🆕 ' + e.target.value;
            } else {
                previewCompany.textContent = 'Не указана';
            }
        });

        // Инициализация при загрузке
        checkNewCompanyVisibility();
        previewLevel.textContent = 'L0 (Базовый)'; // Устанавливаем дефолтное превью
    });
</script>
@endsection