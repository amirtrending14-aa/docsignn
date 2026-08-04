@extends('layouts.superadmin')

@section('title', 'Редактирование пользователя')
@section('page-title', '✏️ Редактирование: ' . $user->name)
@section('page-subtitle', 'Изменение данных пользователя')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Карточка профиля --}}
    <div class="bg-zinc-900/50 backdrop-blur-sm border border-white/10 rounded-xl p-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-red-500 to-purple-600 flex items-center justify-center text-white font-bold text-2xl ring-2 ring-white/10 shadow-lg overflow-hidden">
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
                    @if($user->is_super_admin)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-500/20 text-purple-300 border border-purple-500/30">Super Admin</span>
                    @elseif($user->is_admin || $user->role === 'admin')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 text-blue-300 border border-blue-500/30">Admin</span>
                    @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-700 text-zinc-300 border border-white/10">Сотрудник</span>
                    @endif
                    <span class="text-xs text-zinc-500">•</span>
                    <span class="text-xs text-zinc-500">Уровень {{ $user->level }}</span>
                </div>
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
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">Имя <span class="text-red-400">*</span></label>
                    <input type="text" name="name" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">Email <span class="text-red-400">*</span></label>
                    <input type="email" name="email" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50" value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">Телефон</label>
                    <input type="text" name="phone" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50" value="{{ old('phone', $user->phone) }}" placeholder="+992 XXX XX XX XX">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">Уровень</label>
                    <input type="number" name="level" min="0" max="20" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500/50" value="{{ old('level', $user->level) }}">
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
                    <input type="password" name="password" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50" placeholder="••••••••">
                    @error('password')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">Подтверждение пароля</label>
                    <input type="password" name="password_confirmation" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50" placeholder="••••••••">
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">Роль <span class="text-red-400">*</span></label>
                    <select name="role" id="inputRole" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500/50 [&>option]:bg-zinc-900" style="color-scheme: dark;" required>
                        <option value="employee" {{ (old('role', $user->role) == 'employee') ? 'selected' : '' }}>Сотрудник</option>
                        <option value="admin" {{ (old('role', $user->role) == 'admin') ? 'selected' : '' }}>Админ компании</option>
                        <option value="super_admin" {{ (old('role', $user->role) == 'super_admin' || $user->is_super_admin) ? 'selected' : '' }}>Супер Админ</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-300 mb-2">Компания</label>
                    <select name="company_id" id="inputCompany" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500/50 [&>option]:bg-zinc-900" style="color-scheme: dark;">
                        <option value="">— Без компании —</option>
                        @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ (old('company_id', $user->company_id) == $company->id) ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- ✅ БЛОК ДАННЫХ КОМПАНИИ --}}
                <div id="companyDetailsBlock" class="md:col-span-2 mt-2 p-4 bg-blue-500/5 border border-blue-500/20 rounded-lg {{ $user->company_id ? '' : 'hidden' }}">
                    <label class="block text-sm font-bold text-blue-400 mb-3">🏢 Данные привязанной компании (можно исправить)</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-400 mb-1">Область</label>
                            <select name="company_region_id" id="editCompanyRegion" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500/50 [&>option]:bg-zinc-900" style="color-scheme: dark;">
                                <option value="">-- Выберите область --</option>
                                <option value="1" {{ $user->company && $user->company->region_id == 1 ? 'selected' : '' }}>Согдийская область (Вилояти Суғд)</option>
                                <option value="2" {{ $user->company && $user->company->region_id == 2 ? 'selected' : '' }}>Хатлонская область (Вилояти Хатлон)</option>
                                <option value="3" {{ $user->company && $user->company->region_id == 3 ? 'selected' : '' }}>Районы республиканского подчинения (РРП)</option>
                                <option value="4" {{ $user->company && $user->company->region_id == 4 ? 'selected' : '' }}>Горно-Бадахшанская АО (ГБАО/ВМКБ)</option>
                                <option value="5" {{ $user->company && $user->company->region_id == 5 ? 'selected' : '' }}>Город Душанбе (Шаҳри Душанбе)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-zinc-400 mb-1">Город / Район</label>
                            <select name="company_city_id" id="editCompanyCity" data-current-city-id="{{ $user->company ? $user->company->city_id : '' }}" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500/50 [&>option]:bg-zinc-900" style="color-scheme: dark;">
                                <option value="">-- Выберите город / район --</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-zinc-400 mb-1">Точный тип организации</label>
                            <select name="company_type" id="editCompanyType" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500/50 [&>option]:bg-zinc-900 [&>optgroup]:bg-zinc-950 [&>optgroup]:text-zinc-400" style="color-scheme: dark;">
                                <option value="">-- Выберите точный тип --</option>
                                <optgroup label="1. Органы власти">
                                    <option value="ministry" {{ $user->company && $user->company->type == 'ministry' ? 'selected' : '' }}>Правительство и министерства</option>
                                    <option value="local_government" {{ $user->company && $user->company->type == 'local_government' ? 'selected' : '' }}>Местное самоуправление</option>
                                    <option value="law_enforcement" {{ $user->company && $user->company->type == 'law_enforcement' ? 'selected' : '' }}>Силовые и надзорные структуры</option>
                                    <option value="special_agency" {{ $user->company && $user->company->type == 'special_agency' ? 'selected' : '' }}>Специализированные агентства</option>
                                </optgroup>
                                <optgroup label="2. Социальная сфера">
                                    <option value="education" {{ $user->company && $user->company->type == 'education' ? 'selected' : '' }}>Образование</option>
                                    <option value="healthcare" {{ $user->company && $user->company->type == 'healthcare' ? 'selected' : '' }}>Здравоохранение</option>
                                    <option value="social_protection" {{ $user->company && $user->company->type == 'social_protection' ? 'selected' : '' }}>Социальная защита</option>
                                </optgroup>
                                <optgroup label="3. Финансы и бизнес">
                                    <option value="bank" {{ $user->company && $user->company->type == 'bank' ? 'selected' : '' }}>Финансы (Банки и т.д.)</option>
                                    <option value="business_services" {{ $user->company && $user->company->type == 'business_services' ? 'selected' : '' }}>Деловые услуги</option>
                                    <option value="it_development" {{ $user->company && $user->company->type == 'it_development' ? 'selected' : '' }}>IT и разработка</option>
                                </optgroup>
                                <optgroup label="4. Торговля и общепит">
                                    <option value="retail" {{ $user->company && $user->company->type == 'retail' ? 'selected' : '' }}>Торговля</option>
                                    <option value="catering" {{ $user->company && $user->company->type == 'catering' ? 'selected' : '' }}>Общепит</option>
                                </optgroup>
                                <optgroup label="5. Производство и строительство">
                                    <option value="manufacturing" {{ $user->company && $user->company->type == 'manufacturing' ? 'selected' : '' }}>Промышленность</option>
                                    <option value="construction" {{ $user->company && $user->company->type == 'construction' ? 'selected' : '' }}>Строительство</option>
                                </optgroup>
                                <optgroup label="6. Сфера услуг">
                                    <option value="household_services" {{ $user->company && $user->company->type == 'household_services' ? 'selected' : '' }}>Бытовые услуги</option>
                                    <option value="hospitality" {{ $user->company && $user->company->type == 'hospitality' ? 'selected' : '' }}>Гостиничный бизнес</option>
                                    <option value="sport_leisure" {{ $user->company && $user->company->type == 'sport_leisure' ? 'selected' : '' }}>Спорт и досуг</option>
                                </optgroup>
                                <optgroup label="7. Инфраструктура">
                                    <option value="utilities" {{ $user->company && $user->company->type == 'utilities' ? 'selected' : '' }}>ЖКХ</option>
                                    <option value="transport" {{ $user->company && $user->company->type == 'transport' ? 'selected' : '' }}>Транспорт</option>
                                    <option value="communication" {{ $user->company && $user->company->type == 'communication' ? 'selected' : '' }}>Связь</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
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
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-red-500 to-purple-600 flex items-center justify-center text-white font-bold text-3xl ring-2 ring-white/10 shadow-lg flex-shrink-0 overflow-hidden">
                    @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="" class="w-24 h-24 rounded-full object-cover">
                    @else
                    {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div class="flex-1">
                    <label class="block">
                        <input type="file" name="avatar" id="inputAvatar" accept="image/*" class="hidden">
                        <span class="inline-flex items-center gap-2 bg-zinc-800 hover:bg-zinc-700 border border-white/20 hover:border-blue-500/50 text-white font-medium rounded-lg px-4 py-2.5 text-sm cursor-pointer transition-all">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Выбрать файл
                        </span>
                        <span id="file-name" class="ml-3 text-sm text-zinc-500">PNG, JPG до 2MB</span>
                    </label>
                    @if($user->avatar)
                    <div class="mt-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remove_avatar" value="1" class="rounded border-white/30 bg-zinc-700 text-red-500 focus:ring-red-500/20">
                            <span class="text-xs text-red-400">Удалить текущий аватар</span>
                        </label>
                    </div>
                    @endif
                    @error('avatar')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Кнопки --}}
        <div class="bg-zinc-900/50 backdrop-blur-sm border border-white/10 rounded-xl p-6">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-sm text-zinc-500"><span class="text-red-400">*</span> — обязательные поля</div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('superadmin.users.index') }}" class="flex-1 sm:flex-none bg-zinc-800 hover:bg-zinc-700 text-white font-medium rounded-lg px-5 py-2.5 text-sm flex items-center justify-center gap-2 transition-all border border-white/10">Отмена</a>
                    <button type="submit" class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg px-5 py-2.5 text-sm flex items-center justify-center gap-2 transition-all shadow-lg shadow-blue-500/20">Сохранить изменения</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    (function () {
        'use strict';

        var UC_CITIES = {
            '1': [
                { id: 101, name: 'г. Худжанд (центр)' }, { id: 102, name: 'г. Бустон' },
                { id: 103, name: 'г. Гулистон' }, { id: 104, name: 'г. Истаравшан' },
                { id: 105, name: 'г. Истиклол' }, { id: 106, name: 'г. Исфара' },
                { id: 107, name: 'г. Канибадам' }, { id: 108, name: 'г. Пенджикент' },
                { id: 109, name: 'Айнинский район' }, { id: 110, name: 'Аштский район' },
                { id: 111, name: 'Бободжон-Гафуровский район' }, { id: 112, name: 'Деваштичский район' },
                { id: 113, name: 'Кухистони-Мастчохский (Горно-Матчинский) р-н' },
                { id: 114, name: 'Матчинский район' }, { id: 115, name: 'Джаббор-Расуловский район' },
                { id: 116, name: 'Зафарободский район' }, { id: 117, name: 'Спитаменский район' },
                { id: 118, name: 'Шахристанский район' }
            ],
            '2': [
                { id: 201, name: 'г. Бохтар (бывш. Курган-Тюбе, центр)' }, { id: 202, name: 'г. Куляб' },
                { id: 203, name: 'г. Нурек' }, { id: 204, name: 'г. Левакант' },
                { id: 205, name: 'Бальджуванский район' }, { id: 206, name: 'Бохтарский район' },
                { id: 207, name: 'Вахшский район' }, { id: 208, name: 'Восейский район' },
                { id: 209, name: 'Дангаринский район' }, { id: 210, name: 'Район Абдурахмона Джами' },
                { id: 211, name: 'Джайхунский район' }, { id: 212, name: 'Кубодиёнский район' },
                { id: 213, name: 'Муминабадский район' }, { id: 214, name: 'Пянджский район' },
                { id: 215, name: 'Темурмаликский район' }, { id: 216, name: 'Фархорский район' },
                { id: 217, name: 'Район Мир Сайид Алии Хамадони' }, { id: 218, name: 'Район Носири Хусрав' },
                { id: 219, name: 'Ховалингский район' }, { id: 220, name: 'Хуросонский район' },
                { id: 221, name: 'Шахритусский район' }, { id: 222, name: 'Район Шамсиддин Шохин' },
                { id: 223, name: 'Яванский район' }
            ],
            '3': [
                { id: 301, name: 'Варзобский район' }, { id: 302, name: 'Вахдатский район' },
                { id: 303, name: 'Гиссарский район' }, { id: 304, name: 'Лахшский район' },
                { id: 305, name: 'Нурабадский район' }, { id: 306, name: 'Раштский район' },
                { id: 307, name: 'Рудакинский район' }, { id: 308, name: 'Сангворский район' },
                { id: 309, name: 'Таджикабадский район' }, { id: 310, name: 'Турсунзадевский район' },
                { id: 311, name: 'Файзабадский район' }, { id: 312, name: 'Шахринавский район' },
                { id: 313, name: 'Рогунский район' }
            ],
            '4': [
                { id: 401, name: 'г. Хорог (центр)' }, { id: 402, name: 'Дарвазский район' },
                { id: 403, name: 'Ванчский район' }, { id: 404, name: 'Рушанский район' },
                { id: 405, name: 'Шугнанский район' }, { id: 406, name: 'Рошткалинский район' },
                { id: 407, name: 'Ишкашимский район' }, { id: 408, name: 'Мургабский район' }
            ],
            '5': [ { id: 501, name: 'г. Душанбе (столица)' } ]
        };

        function init() {
            console.log('[users/edit] script loaded ✅');

            var inputCompany = document.getElementById('inputCompany');
            var companyDetailsBlock = document.getElementById('companyDetailsBlock');
            var editCompanyRegion = document.getElementById('editCompanyRegion');
            var editCompanyCity = document.getElementById('editCompanyCity');
            var inputAvatar = document.getElementById('inputAvatar');

            if (!inputCompany || !companyDetailsBlock || !editCompanyRegion || !editCompanyCity) {
                console.error('[users/edit] не найдены элементы блока компании!');
                return;
            }

            function loadCities(regionId, selectedCityId) {
                editCompanyCity.innerHTML = '<option value="">-- Выберите город / район --</option>';
                if (!regionId) return;
                var cities = UC_CITIES[regionId] || [];
                cities.forEach(function (city) {
                    var sel = (selectedCityId && String(city.id) === String(selectedCityId)) ? ' selected' : '';
                    editCompanyCity.innerHTML += '<option value="' + city.id + '"' + sel + '>' + city.name + '</option>';
                });
            }

            function toggleCompanyDetails() {
                if (inputCompany.value) {
                    companyDetailsBlock.classList.remove('hidden');
                    if (editCompanyRegion.value) {
                        var cur = editCompanyCity.getAttribute('data-current-city-id') || null;
                        // грузим города только если ещё не загружены
                        if (editCompanyCity.options.length <= 1) {
                            loadCities(editCompanyRegion.value, cur);
                        }
                    }
                } else {
                    companyDetailsBlock.classList.add('hidden');
                }
            }

            inputCompany.addEventListener('change', function () {
                // при смене компании перезагружаем города
                if (editCompanyRegion.value) loadCities(editCompanyRegion.value, null);
                toggleCompanyDetails();
            });

            editCompanyRegion.addEventListener('change', function () {
                loadCities(this.value, null);
            });

            if (inputAvatar) inputAvatar.addEventListener('change', function (e) {
                var fileName = document.getElementById('file-name');
                if (e.target.files && e.target.files[0] && fileName) {
                    fileName.textContent = e.target.files[0].name;
                    fileName.classList.add('text-green-400');
                    fileName.classList.remove('text-zinc-500');
                }
            });

            toggleCompanyDetails();
            console.log('[users/edit] init done ✅');
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
            // =====================================================================
        // ✅ АВТОСОХРАНЕНИЕ ФОРМЫ РЕДАКТИРОВАНИЯ (ЧЕРНОВИК) ЧЕРЕЗ LOCALSTORAGE
        // Адаптировано специально для страницы edit, чтобы не ломать существующие данные
        // =====================================================================

        // 1. Уникальный ключ для КАЖДОГО документа, чтобы черновики не путались
        const docId = {{ $document->id ?? '0' }};
        const FORM_STORAGE_KEY = 'docsign_draft_edit_form_' + docId;
        const ORIGINAL_FILE_NAME = "{{ $document->file_name ?? '' }}"; // Имя файла, который уже есть в базе

        // 2. Функция сохранения данных (срабатывает при любом изменении)
        function saveEditFormToStorage() {
            const fileInput = document.getElementById('file-input');
            const formData = {
                number: document.getElementById('field-number').value,
                type: document.getElementById('field-type').value,
                status: document.getElementById('field-status').value,
                deadline: document.getElementById('field-deadline').value,
                title: document.getElementById('field-title').value,
                content: document.getElementById('field-content').value,
                // Сохраняем имя нового файла, если пользователь его выбрал
                newFileName: fileInput && fileInput.files.length > 0 ? fileInput.files[0].name : null,
                timestamp: Date.now() // Добавляем время, чтобы понимать, насколько данные свежие
            };
            localStorage.setItem(FORM_STORAGE_KEY, JSON.stringify(formData));
        }

        // 3. Функция восстановления данных при возврате на страницу
        function restoreEditFormFromStorage() {
            const savedData = localStorage.getItem(FORM_STORAGE_KEY);
            if (!savedData) return;

            try {
                const data = JSON.parse(savedData);

                // Проверяем, что данные не старше 24 часов (защита от вечного мусора в кэше)
                const hoursPassed = (Date.now() - data.timestamp) / (1000 * 60 * 60);
                if (hoursPassed > 24) {
                    localStorage.removeItem(FORM_STORAGE_KEY);
                    return;
                }

                // Восстанавливаем текстовые поля
                if (data.number) document.getElementById('field-number').value = data.number;
                if (data.type) document.getElementById('field-type').value = data.type;
                if (data.status) document.getElementById('field-status').value = data.status;
                if (data.deadline) document.getElementById('field-deadline').value = data.deadline;
                if (data.title) document.getElementById('field-title').value = data.title;
                if (data.content) document.getElementById('field-content').value = data.content;

                // УМНАЯ ЛОГИКА ДЛЯ ФАЙЛА НА СТРАНИЦЕ EDIT:
                const fileNameEl = document.getElementById('file-name');
                const uploadBox = document.querySelector('.file-upload');

                if (data.newFileName && data.newFileName !== ORIGINAL_FILE_NAME) {
                    // Пользователь выбрал НОВЫЙ файл, но браузер его сбросил при переходе
                    fileNameEl.innerHTML = `⚠️ Новый файл "<strong>${data.newFileName}</strong>" был выбран, но сброшен браузером.<br><span style="font-size:10px; color:#f59e0b;">(Пожалуйста, выберите его снова. Старый файл останется, если не загрузить новый)</span>`;
                    fileNameEl.style.color = '#f59e0b';
                    uploadBox.style.borderColor = 'rgba(245, 158, 11, 0.6)';
                    uploadBox.style.background = 'rgba(245, 158, 11, 0.05)';
                } else {
                    // Пользователь не выбирал новый файл, показываем текущий файл из базы
                    if (ORIGINAL_FILE_NAME) {
                        fileNameEl.innerHTML = `<i class="bi bi-file-earmark-check" style="color:#22c55e; margin-right:6px;"></i> Текущий файл: <strong>${ORIGINAL_FILE_NAME}</strong> <span style="font-size:10px; color:#8892a6;">(оставьте пустым, чтобы не менять)</span>`;
                        fileNameEl.style.color = '#e7ecf3';
                        uploadBox.style.borderColor = 'rgba(255,255,255,0.15)';
                        uploadBox.style.background = 'rgba(255,255,255,0.03)';
                    }
                }
            } catch (e) {
                console.error('Ошибка восстановления формы редактирования DocSign:', e);
            }
        }

        // 4. Функция очистки после успешного сохранения
        function clearEditFormStorage() {
            localStorage.removeItem(FORM_STORAGE_KEY);
        }

        // 5. Запуск логики
        document.addEventListener('DOMContentLoaded', function() {
            // Сначала восстанавливаем данные (если пользователь вернулся назад)
            restoreEditFormFromStorage();

            // Вешаем слушатели на все поля формы для автосохранения
            const formInputs = document.querySelectorAll('#documentForm input, #documentForm select, #documentForm textarea');
            formInputs.forEach(el => {
                el.addEventListener('change', saveEditFormToStorage);
                el.addEventListener('input', saveEditFormToStorage);
            });

            // Очищаем память только при успешной отправке формы
            const editForm = document.getElementById('documentForm');
            if (editForm) {
                editForm.addEventListener('submit', function() {
                    setTimeout(clearEditFormStorage, 500);
                });
            }
        });
</script>
@endsection