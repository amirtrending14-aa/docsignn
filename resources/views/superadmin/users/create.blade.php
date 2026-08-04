@extends('layouts.superadmin')

@section('title', 'Создание пользователя')
@section('page-title', '✨ Создание нового пользователя')
@section('page-subtitle', 'Добавление нового участника в систему')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Левая колонка - Превью --}}
    <div class="lg:col-span-1">
        <div class="card sticky top-6 bg-zinc-900/50 backdrop-blur-sm border border-white/10 rounded-xl p-6">
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
                <a href="{{ route('superadmin.users.index') }}" class="w-full text-center block bg-zinc-800 hover:bg-zinc-700 text-white font-medium rounded-lg px-4 py-2.5 text-sm transition-all border border-white/10">
                    Назад к списку
                </a>
            </div>
        </div>
    </div>

    {{-- Правая колонка - Форма --}}
    <div class="lg:col-span-2">
        <div class="bg-zinc-900/50 backdrop-blur-sm border border-white/10 rounded-xl p-6">
            <form action="{{ route('superadmin.users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Основная информация --}}
                <div class="mb-6">
                    <h3 class="text-base font-bold text-white mb-4">👤 Основная информация</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Имя *</label>
                            <input type="text" name="name" id="inputName" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50" value="{{ old('name') }}" placeholder="Иван Иванов" required>
                            @error('name')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Email *</label>
                            <input type="email" name="email" id="inputEmail" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50" value="{{ old('email') }}" placeholder="user@example.com" required>
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
                            <input type="password" name="password" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50" placeholder="Минимум 6 символов" required>
                            @error('password')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Подтверждение пароля *</label>
                            <input type="password" name="password_confirmation" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50" placeholder="Повторите пароль" required>
                        </div>
                    </div>
                </div>

                {{-- Роль и компания --}}
                <div class="mb-6 pt-6 border-t border-white/5">
                    <h3 class="text-base font-bold text-white mb-4">🏢 Роль и доступ</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Роль *</label>
                            <select name="role" id="inputRole" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-blue-500/50 [&>option]:bg-zinc-900" style="color-scheme: dark;" required>
                                <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Сотрудник (L0)</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Админ компании</option>
                                <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Супер Админ</option>
                            </select>
                            @error('role')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Уровень доступа <span class="text-xs text-zinc-500 font-normal">(0 = базовый)</span></label>
                            <input type="number" name="level" id="inputLevel" min="0" max="20" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-blue-500/50" value="{{ old('level', 0) }}">
                            @error('level')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Выбор компании --}}
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Компания</label>
                        <select name="company_id" id="inputCompany" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-blue-500/50 [&>option]:bg-zinc-900" style="color-scheme: dark;">
                            <option value="">— Без компании (Свободный агент) —</option>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                        @error('company_id')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- ✅ НОВАЯ КОМПАНИЯ --}}
                    <div id="newCompanyBlock" class="mt-4 hidden p-4 bg-blue-500/5 border border-blue-500/20 rounded-lg">
                        <label class="block text-sm font-bold text-blue-400 mb-3">🆕 Создать новую компанию</label>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-zinc-400 mb-1">Название компании *</label>
                                <input type="text" name="new_company_name" id="inputNewCompanyName" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50" placeholder="Например: Алиф Банк, Хукумат г. Худжанд">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-zinc-400 mb-1">Область *</label>
                                <select name="new_company_region_id" id="inputNewRegion" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-blue-500/50 [&>option]:bg-zinc-900" style="color-scheme: dark;">
                                    <option value="">-- Выберите область --</option>
                                    <option value="1">Согдийская область (Вилояти Суғд)</option>
                                    <option value="2">Хатлонская область (Вилояти Хатлон)</option>
                                    <option value="3">Районы республиканского подчинения (РРП)</option>
                                    <option value="4">Горно-Бадахшанская АО (ГБАО/ВМКБ)</option>
                                    <option value="5">Город Душанбе (Шаҳри Душанбе)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-zinc-400 mb-1">Город / Район *</label>
                                <select name="new_company_city_id" id="inputNewCity" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-blue-500/50 [&>option]:bg-zinc-900" style="color-scheme: dark;">
                                    <option value="">-- Сначала выберите область --</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-zinc-400 mb-1">Точный тип организации *</label>
                                <select name="new_company_type" id="inputNewType" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-blue-500/50 [&>option]:bg-zinc-900 [&>optgroup]:bg-zinc-950 [&>optgroup]:text-zinc-400" style="color-scheme: dark;">
                                    <option value="">-- Выберите точный тип --</option>
                                    <optgroup label="1. Органы государственной власти и управления">
                                        <option value="ministry">Правительство и министерства</option>
                                        <option value="local_government">Местное самоуправление (Хокимият, Кенгаши, районные администрации)</option>
                                        <option value="law_enforcement">Силовые и надзорные структуры (Прокуратура, суды, МВД, налоговая, таможня)</option>
                                        <option value="special_agency">Специализированные агентства (Кадастр, экология, МФЦ/ЦГУ)</option>
                                    </optgroup>
                                    <optgroup label="2. Социальная сфера">
                                        <option value="education">Образование (Детские сады, школы, гимназии, лицеи, колледжи, университеты)</option>
                                        <option value="healthcare">Здравоохранение (Больницы, клиники, поликлиники, аптеки, скорая, лаборатории)</option>
                                        <option value="social_protection">Социальная защита (Пенсионные фонды, центры соцпомощи, приюты)</option>
                                    </optgroup>
                                    <optgroup label="3. Финансовый и бизнес-сектор">
                                        <option value="bank">Финансы (Банки, филиалы, пункты обмена валют, страховые, микрокредитные)</option>
                                        <option value="business_services">Деловые услуги (Юридические бюро, нотариус, аудит, консалтинг)</option>
                                        <option value="it_development">IT и разработка (Компании по разработке ПО, веб-студии, сервисные центры)</option>
                                    </optgroup>
                                    <optgroup label="4. Торговля и общественное питание">
                                        <option value="retail">Торговля (Торговые центры, супермаркеты, специализированные магазины, рынки)</option>
                                        <option value="catering">Общепит (Рестораны, кафе, столовые, кофейни, фастфуд, бары, кондитерские)</option>
                                    </optgroup>
                                    <optgroup label="5. Производство и строительство">
                                        <option value="manufacturing">Промышленность (Заводы, фабрики, пищевые комбинаты, типографии, цеха)</option>
                                        <option value="construction">Строительство (Девелоперские компании, архитектурные бюро, ремонт, строй. рынки)</option>
                                    </optgroup>
                                    <optgroup label="6. Сфера услуг и бытовое обслуживание">
                                        <option value="household_services">Бытовые услуги (Салоны красоты, парикмахерские, ателье, химчистки, ремонт, фото)</option>
                                        <option value="hospitality">Гостиничный бизнес (Отели, хостелы, гостевые дома)</option>
                                        <option value="sport_leisure">Спорт и досуг (Фитнес-клубы, бассейны, кинотеатры, театры, музеи, компьютерные клубы)</option>
                                    </optgroup>
                                    <optgroup label="7. Инфраструктура и логистика">
                                        <option value="utilities">ЖКХ (Управляющие компании, водоканалы, электросети, вывоз отходов)</option>
                                        <option value="transport">Транспорт (Автовокзалы, ж/д вокзалы, аэропорты, таксопарки, автошколы, АЗС, шиномонтаж)</option>
                                        <option value="communication">Связь (Отделения почты, офисы операторов мобильной связи, интернет-провайдеры)</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Контакты --}}
                <div class="mb-6 pt-6 border-t border-white/5">
                    <h3 class="text-base font-bold text-white mb-4">📞 Контакты</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Телефон</label>
                            <input type="text" name="phone" id="inputPhone" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500/50" value="{{ old('phone') }}" placeholder="+992 XXX XX XX XX">
                            @error('phone')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Аватар</label>
                            <input type="file" name="avatar" id="inputAvatar" accept="image/*" class="w-full bg-zinc-800 border border-white/20 rounded-lg px-4 py-2.5 text-white file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-500/20 file:text-blue-400 hover:file:bg-blue-500/30">
                            @error('avatar')<div class="text-red-400 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Кнопки --}}
                <div class="flex justify-end gap-3 pt-6 border-t border-white/10">
                    <a href="{{ route('superadmin.users.index') }}" class="bg-zinc-800 hover:bg-zinc-700 text-white font-medium rounded-lg px-5 py-2.5 text-sm transition-all border border-white/10">Отмена</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg px-5 py-2.5 text-sm transition-all shadow-lg shadow-blue-500/20">
                        Создать пользователя
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    /* ============================================================
       ✅ IIFE-обёртка: все переменные локальные → нет конфликта
       с глобальными скриптами layout'а (главная причина бага)
       ============================================================ */
    (function () {
        'use strict';

        // ✅ ПОЛНЫЙ СПИСОК — ID совпадают с /documents/recipients/create
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

        var UC_TYPE_NAMES = {
            'ministry': 'Министерство', 'local_government': 'Местное самоуправление',
            'law_enforcement': 'Силовые структуры', 'special_agency': 'Специализированное агентство',
            'education': 'Образование', 'healthcare': 'Здравоохранение',
            'social_protection': 'Социальная защита', 'bank': 'Банк / Финансы',
            'business_services': 'Деловые услуги', 'it_development': 'IT и разработка',
            'retail': 'Торговля', 'catering': 'Общественное питание',
            'manufacturing': 'Промышленность', 'construction': 'Строительство',
            'household_services': 'Бытовые услуги', 'hospitality': 'Гостиничный бизнес',
            'sport_leisure': 'Спорт и досуг', 'utilities': 'ЖКХ',
            'transport': 'Транспорт', 'communication': 'Связь'
        };

        var UC_ROLE_NAMES = {
            'employee': '👤 Сотрудник',
            'admin': '🛡️ Админ компании',
            'super_admin': '⚡ Супер Админ'
        };

        function init() {
            console.log('[users/create] script loaded ✅');

            var inputName   = document.getElementById('inputName');
            var inputEmail  = document.getElementById('inputEmail');
            var inputRole   = document.getElementById('inputRole');
            var inputCompany= document.getElementById('inputCompany');
            var inputPhone  = document.getElementById('inputPhone');
            var inputLevel  = document.getElementById('inputLevel');
            var inputAvatar = document.getElementById('inputAvatar');

            var inputNewCompanyName = document.getElementById('inputNewCompanyName');
            var inputNewRegion = document.getElementById('inputNewRegion');
            var inputNewCity   = document.getElementById('inputNewCity');
            var inputNewType   = document.getElementById('inputNewType');
            var newCompanyBlock= document.getElementById('newCompanyBlock');

            var previewName    = document.getElementById('previewName');
            var previewEmail   = document.getElementById('previewEmail');
            var previewRole    = document.getElementById('previewRole');
            var previewCompany = document.getElementById('previewCompany');
            var previewPhone   = document.getElementById('previewPhone');
            var previewLevel   = document.getElementById('previewLevel');
            var avatarImage    = document.getElementById('avatarImage');
            var avatarPlaceholder = document.getElementById('avatarPlaceholder');

            if (!inputRole || !inputCompany || !newCompanyBlock) {
                console.error('[users/create] не найдены ключевые элементы формы!');
                return;
            }

            // Показать/скрыть блок новой компании
            function checkNewCompanyVisibility() {
                var companyId = inputCompany.value;
                var role = inputRole.value;
                // Блок нужен, когда роль = админ И компания не выбрана
                if (companyId === '' && role === 'admin') {
                    newCompanyBlock.classList.remove('hidden');
                } else {
                    newCompanyBlock.classList.add('hidden');
                    if (inputNewCompanyName) inputNewCompanyName.value = '';
                    if (inputNewRegion) inputNewRegion.value = '';
                    if (inputNewCity) inputNewCity.innerHTML = '<option value="">-- Сначала выберите область --</option>';
                    if (inputNewType) inputNewType.value = '';
                    if (previewCompany) previewCompany.textContent = 'Не указана';
                }
            }

            function updateNewCompanyPreview() {
                if (!previewCompany) return;
                if (inputNewCompanyName && inputNewCompanyName.value) {
                    var details = '🆕 ' + inputNewCompanyName.value;
                    var typeVal = inputNewType ? inputNewType.value : '';
                    if (typeVal && UC_TYPE_NAMES[typeVal]) details += ' (' + UC_TYPE_NAMES[typeVal] + ')';
                    previewCompany.textContent = details;
                } else {
                    previewCompany.textContent = 'Не указана';
                }
            }

            function loadNewCities(regionId) {
                if (!inputNewCity) return;
                inputNewCity.innerHTML = '';
                if (!regionId) {
                    inputNewCity.innerHTML = '<option value="">-- Сначала выберите область --</option>';
                    return;
                }
                var cities = UC_CITIES[regionId] || [];
                inputNewCity.innerHTML = '<option value="">-- Выберите город / район --</option>';
                cities.forEach(function (city) {
                    inputNewCity.innerHTML += '<option value="' + city.id + '">' + city.name + '</option>';
                });
            }

            // --- Превью ---
            if (inputName) inputName.addEventListener('input', function (e) {
                previewName.textContent = e.target.value || 'Новый пользователь';
            });
            if (inputEmail) inputEmail.addEventListener('input', function (e) {
                previewEmail.textContent = e.target.value || 'email@example.com';
            });
            if (inputRole) {
                inputRole.addEventListener('change', function (e) {
                    previewRole.textContent = UC_ROLE_NAMES[e.target.value] || 'Сотрудник';
                    checkNewCompanyVisibility();
                });
            }
            if (inputCompany) {
                inputCompany.addEventListener('change', function (e) {
                    var selected = e.target.options[e.target.selectedIndex];
                    previewCompany.textContent = (selected.value === '') ? 'Не указана' : selected.text;
                    checkNewCompanyVisibility();
                });
            }
            if (inputPhone) inputPhone.addEventListener('input', function (e) {
                previewPhone.textContent = e.target.value || '—';
            });
            if (inputLevel) inputLevel.addEventListener('input', function (e) {
                previewLevel.textContent = (!e.target.value || e.target.value === '0') ? 'L0 (Базовый)' : 'L' + e.target.value;
            });
            if (inputAvatar) inputAvatar.addEventListener('change', function (e) {
                if (e.target.files && e.target.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (ev) {
                        avatarImage.src = ev.target.result;
                        avatarImage.classList.remove('hidden');
                        avatarPlaceholder.classList.add('hidden');
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });

            // --- Новая компания ---
            if (inputNewCompanyName) inputNewCompanyName.addEventListener('input', updateNewCompanyPreview);
            if (inputNewType) inputNewType.addEventListener('change', updateNewCompanyPreview);
            if (inputNewRegion) inputNewRegion.addEventListener('change', function () {
                loadNewCities(this.value);
            });

            // Инициализация (с учётом old() значений)
            previewRole.textContent = UC_ROLE_NAMES[inputRole.value] || 'Сотрудник';
            previewLevel.textContent = (!inputLevel.value || inputLevel.value === '0') ? 'L0 (Базовый)' : 'L' + inputLevel.value;
            checkNewCompanyVisibility();

            console.log('[users/create] init done ✅ role=' + inputRole.value + ' company="' + inputCompany.value + '"');
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
</script>
@endsection