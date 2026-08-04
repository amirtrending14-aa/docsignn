<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DocSign — Создание компании</title>

    <link rel="icon" type="image/png" href="{{ asset('img/dss.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/dss.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #4f46e5; --primary-dark: #3730a3; --primary-light: #818cf8;
            --accent: #06b6d4; --bg-dark: #0f172a; --bg-card: rgba(15, 23, 42, 0.6);
            --text-primary: #f1f5f9; --text-secondary: #94a3b8;
            --border: rgba(148, 163, 184, 0.15); --glow: rgba(79, 70, 229, 0.4);
        }
        body {
            font-family: 'Figtree', sans-serif; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: var(--bg-dark); overflow-x: hidden; position: relative;
        }
        .bg-animation { position: fixed; inset: 0; z-index: 0; overflow: hidden; }
        .bg-animation::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(ellipse at 20% 50%, rgba(79, 70, 229, 0.15) 0%, transparent 50%),
                        radial-gradient(ellipse at 80% 20%, rgba(6, 182, 212, 0.1) 0%, transparent 50%),
                        radial-gradient(ellipse at 50% 80%, rgba(139, 92, 246, 0.1) 0%, transparent 50%);
            animation: bgShift 15s ease-in-out infinite alternate;
        }
        @keyframes bgShift { 0% { transform: translate(0, 0) rotate(0deg); } 100% { transform: translate(-5%, -5%) rotate(3deg); } }
        .particles { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
        .particle {
            position: absolute; width: 3px; height: 3px; background: rgba(129, 140, 248, 0.5);
            border-radius: 50%; animation: float linear infinite;
        }
        @keyframes float {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 1; } 90% { opacity: 1; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }
        .grid-overlay {
            position: fixed; inset: 0; z-index: 0;
            background-image: linear-gradient(rgba(148, 163, 184, 0.03) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(148, 163, 184, 0.03) 1px, transparent 1px);
            background-size: 60px 60px; animation: gridMove 20s linear infinite;
        }
        @keyframes gridMove { 0% { transform: translate(0, 0); } 100% { transform: translate(60px, 60px); } }
        .container {
            position: relative; z-index: 10; width: 100%; max-width: 540px;
            padding: 20px; max-height: 100vh; overflow-y: auto;
        }
        .container::-webkit-scrollbar { width: 4px; }
        .container::-webkit-scrollbar-track { background: transparent; }
        .container::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
        .lang-switcher {
            position: fixed; top: 20px; right: 20px; z-index: 100; display: flex;
            background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(20px);
            border: 1px solid var(--border); border-radius: 12px; padding: 4px; gap: 2px;
        }
        .lang-select {
            padding: 8px 14px; background: linear-gradient(145deg, #1a2a6c, #101a44);
            color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px;
            font-family: 'Figtree', sans-serif; font-size: 13px; font-weight: 600;
            cursor: pointer; outline: none; appearance: none; -webkit-appearance: none;
            transition: all 0.3s ease; letter-spacing: 0.5px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .lang-select:hover { background: #1e3a8a; transform: translateY(-1px); }
        .lang-select option { background-color: #101a44; color: white; }
        .login-card {
            background: var(--bg-card); backdrop-filter: blur(40px);
            border: 1px solid var(--border); border-radius: 24px; padding: 40px 36px;
            position: relative; overflow: hidden;
            animation: cardAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
            box-shadow: 0 0 0 1px rgba(148, 163, 184, 0.05), 0 25px 80px rgba(0, 0, 0, 0.4), 0 0 120px rgba(79, 70, 229, 0.08);
        }
        @keyframes cardAppear {
            0% { opacity: 0; transform: translateY(30px) scale(0.96); filter: blur(10px); }
            100% { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
        }
        .login-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--primary-light), var(--primary));
            background-size: 200% 100%; animation: shimmer 3s ease-in-out infinite;
        }
        @keyframes shimmer { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
        .logo-section { text-align: center; margin-bottom: 28px; animation: logoAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both; }
        @keyframes logoAppear { 0% { opacity: 0; transform: translateY(15px); } 100% { opacity: 1; transform: translateY(0); } }
        .logo-img {
            width: 64px; height: 64px; border-radius: 16px; margin: 0 auto 12px; display: block;
            box-shadow: 0 8px 30px rgba(6, 182, 212, 0.25);
        }
        .logo-title { font-size: 24px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.5px; margin-bottom: 4px; }
        .logo-title span {
            background: linear-gradient(135deg, var(--accent), var(--primary-light));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .logo-subtitle { font-size: 12px; font-weight: 500; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 2px; }
        .form-section-title {
            font-size: 11px; font-weight: 700; color: var(--accent);
            text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 14px; margin-top: 6px; padding-left: 2px;
        }
        .form-group { margin-bottom: 16px; animation: formAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
        @keyframes formAppear { 0% { opacity: 0; transform: translateX(-15px); } 100% { opacity: 1; transform: translateX(0); } }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 7px; letter-spacing: 0.3px; }
        .input-wrapper { position: relative; }
        .input-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            width: 18px; height: 18px; color: var(--text-secondary);
            transition: color 0.3s ease; pointer-events: none; z-index: 2;
        }
        .form-input {
            width: 100%; padding: 12px 14px 12px 44px; background: rgba(30, 41, 59, 0.5);
            border: 1px solid var(--border); border-radius: 12px; color: var(--text-primary);
            font-family: 'Figtree', sans-serif; font-size: 14px; font-weight: 500;
            transition: all 0.3s ease; outline: none;
        }
        .form-input::placeholder { color: rgba(148, 163, 184, 0.4); }
        .form-input:focus {
            border-color: var(--accent); background: rgba(30, 41, 59, 0.8);
            box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.15), 0 0 20px rgba(6, 182, 212, 0.1);
        }
        .form-input:focus ~ .input-icon, .form-input:focus + .input-icon { color: var(--accent); }
        .form-input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 30px rgba(30, 41, 59, 0.9) inset !important;
            -webkit-text-fill-color: var(--text-primary) !important; caret-color: var(--text-primary);
        }
        .form-input.error { border-color: #ef4444; box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15); }
        .form-select { appearance: none; -webkit-appearance: none; cursor: pointer; color-scheme: dark; }
        .form-select option, .form-select optgroup { background-color: #1e293b; color: #f1f5f9; }
        .form-select optgroup { color: var(--accent); font-weight: 700; }
        .form-select:disabled { opacity: 0.5; cursor: not-allowed; }
        .error-message { font-size: 12px; color: #ef4444; margin-top: 5px; font-weight: 500; }
        .toggle-password {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: var(--text-secondary);
            cursor: pointer; padding: 4px; border-radius: 8px;
            transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; z-index: 2;
        }
        .toggle-password:hover { color: var(--text-primary); background: rgba(148, 163, 184, 0.1); }
        .toggle-password svg { width: 18px; height: 18px; }
        .role-badge {
            display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px;
            background: rgba(79, 70, 229, 0.15); border: 1px solid rgba(79, 70, 229, 0.3);
            border-radius: 8px; font-size: 12px; font-weight: 700; color: var(--primary-light); margin-top: 4px;
        }
        .role-badge svg { width: 14px; height: 14px; }
        .submit-btn {
            width: 100%; padding: 15px; background: linear-gradient(135deg, var(--accent), var(--primary));
            border: none; border-radius: 14px; color: white; font-family: 'Figtree', sans-serif;
            font-size: 15px; font-weight: 700; cursor: pointer; position: relative; overflow: hidden;
            transition: all 0.3s ease; letter-spacing: 0.3px; margin-top: 8px;
            animation: formAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.6s both;
        }
        .submit-btn::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            opacity: 0; transition: opacity 0.3s ease;
        }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(6, 182, 212, 0.3), 0 0 40px rgba(79, 70, 229, 0.15); }
        .submit-btn:hover::before { opacity: 1; }
        .submit-btn:active { transform: translateY(0); }
        .submit-btn .btn-text { position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .submit-btn.loading .btn-text { opacity: 0; }
        .submit-btn.loading::after {
            content: ''; position: absolute; width: 24px; height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.3); border-top-color: white; border-radius: 50%;
            animation: spin 0.8s linear infinite; top: 50%; left: 50%; margin: -12px 0 0 -12px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .login-section { text-align: center; margin-top: 20px; animation: formAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.7s both; }
        .login-text { font-size: 14px; color: var(--text-secondary); }
        .login-link { color: var(--accent); text-decoration: none; font-weight: 700; transition: all 0.2s ease; }
        .login-link:hover { color: var(--primary-light); }
        .notification {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%) translateY(-100px);
            padding: 14px 24px; border-radius: 12px; font-size: 14px; font-weight: 600; z-index: 1000;
            transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1); backdrop-filter: blur(20px);
        }
        .notification.show { transform: translateX(-50%) translateY(0); }
        .notification.success { background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; }
        .notification.error { background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; }
        @media (max-width: 520px) {
            .container { padding: 16px; }
            .login-card { padding: 28px 18px; border-radius: 20px; }
            .logo-title { font-size: 20px; }
            .form-row { grid-template-columns: 1fr; }
            .lang-switcher { top: 12px; right: 12px; }
        }
    </style>
</head>
<body>
<div class="bg-animation"></div>
<div class="grid-overlay"></div>
<div class="particles" id="particles"></div>
<div class="notification" id="notification"></div>

<div class="lang-switcher">
    <select class="lang-select" onchange="switchLang(this.value)">
        <option value="ru" selected>🇷🇺 RU</option>
        <option value="tj">🇹🇯 TJ</option>
        <option value="en">🇬🇧 EN</option>
    </select>
</div>

<div class="container">
    <div class="login-card">
        <div class="logo-section">
            <img src="{{ asset('img/dss.png') }}" alt="DocSign Logo" class="logo-img">
            <div class="logo-title">Doc<span>Sign</span></div>
            <div class="logo-subtitle" data-i18n="subtitle">Создание компании</div>
        </div>

        <form method="POST" action="{{ route('register.company.store') }}" id="registerCompanyForm" onsubmit="return handleSubmit(event)">
            @csrf

            {{-- ═══════════ СЕКЦИЯ: КОМПАНИЯ ═══════════ --}}
            <div class="form-section-title" data-i18n="sectionCompany">🏢 Данные компании</div>

            {{-- Название компании --}}
            <div class="form-group">
                <label class="form-label" for="company_name" data-i18n="companyNameLabel">Название компании *</label>
                <div class="input-wrapper">
                    <input type="text" name="company_name" id="company_name"
                           class="form-input @error('company_name') error @enderror"
                           value="{{ old('company_name') }}"
                           placeholder="Например: Алиф Банк, Хукумат г. Худжанд" required autofocus>
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/>
                        <path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/>
                    </svg>
                </div>
                @error('company_name')<div class="error-message">{{ $message }}</div>@enderror
            </div>

            {{-- Тип организации --}}
            <div class="form-group">
                <label class="form-label" for="company_type" data-i18n="companyTypeLabel">Тип организации *</label>
                <div class="input-wrapper">
                    <select name="company_type" id="company_type" class="form-input form-select @error('company_type') error @enderror" required>
                        <option value="">-- Выберите тип организации --</option>
                        <optgroup label="1. Органы государственной власти">
                            <option value="ministry" {{ old('company_type')=='ministry'?'selected':'' }}>Правительство и министерства</option>
                            <option value="local_government" {{ old('company_type')=='local_government'?'selected':'' }}>Местное самоуправление</option>
                            <option value="law_enforcement" {{ old('company_type')=='law_enforcement'?'selected':'' }}>Силовые структуры</option>
                            <option value="special_agency" {{ old('company_type')=='special_agency'?'selected':'' }}>Спецагентства</option>
                        </optgroup>
                        <optgroup label="2. Социальная сфера">
                            <option value="education" {{ old('company_type')=='education'?'selected':'' }}>Образование</option>
                            <option value="healthcare" {{ old('company_type')=='healthcare'?'selected':'' }}>Здравоохранение</option>
                            <option value="social_protection" {{ old('company_type')=='social_protection'?'selected':'' }}>Соцзащита</option>
                        </optgroup>
                        <optgroup label="3. Финансы и бизнес">
                            <option value="bank" {{ old('company_type')=='bank'?'selected':'' }}>Финансы</option>
                            <option value="business_services" {{ old('company_type')=='business_services'?'selected':'' }}>Деловые услуги</option>
                            <option value="it_development" {{ old('company_type')=='it_development'?'selected':'' }}>IT и разработка</option>
                        </optgroup>
                        <optgroup label="4. Торговля и общепит">
                            <option value="retail" {{ old('company_type')=='retail'?'selected':'' }}>Торговля</option>
                            <option value="catering" {{ old('company_type')=='catering'?'selected':'' }}>Общепит</option>
                        </optgroup>
                        <optgroup label="5. Производство и строительство">
                            <option value="manufacturing" {{ old('company_type')=='manufacturing'?'selected':'' }}>Промышленность</option>
                            <option value="construction" {{ old('company_type')=='construction'?'selected':'' }}>Строительство</option>
                        </optgroup>
                        <optgroup label="6. Услуги и досуг">
                            <option value="household_services" {{ old('company_type')=='household_services'?'selected':'' }}>Бытовые услуги</option>
                            <option value="hospitality" {{ old('company_type')=='hospitality'?'selected':'' }}>Гостиничный бизнес</option>
                            <option value="sport_leisure" {{ old('company_type')=='sport_leisure'?'selected':'' }}>Спорт и досуг</option>
                        </optgroup>
                        <optgroup label="7. Инфраструктура">
                            <option value="utilities" {{ old('company_type')=='utilities'?'selected':'' }}>ЖКХ</option>
                            <option value="transport" {{ old('company_type')=='transport'?'selected':'' }}>Транспорт</option>
                            <option value="communication" {{ old('company_type')=='communication'?'selected':'' }}>Связь</option>
                        </optgroup>
                    </select>
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                @error('company_type')<div class="error-message">{{ $message }}</div>@enderror
            </div>

            {{-- Область + Город --}}
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="region_id" data-i18n="regionLabel">Область *</label>
                    <div class="input-wrapper">
                        <select name="region_id" id="region_id" class="form-input form-select @error('region_id') error @enderror" required>
                            <option value="">-- Выберите область --</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}" {{ old('region_id')==$region->id?'selected':'' }}>
                                    {{ $region->name_ru }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    @error('region_id')<div class="error-message">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="city_id" data-i18n="cityLabel">Город / Район *</label>
                    <div class="input-wrapper">
                        <select name="city_id" id="city_id" class="form-input form-select @error('city_id') error @enderror" required disabled>
                            <option value="">-- Сначала выберите область --</option>
                        </select>
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"/><path d="M9 8h1"/><path d="M9 12h1"/><path d="M9 16h1"/>
                            <path d="M14 8h1"/><path d="M14 12h1"/><path d="M14 16h1"/>
                            <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/>
                        </svg>
                    </div>
                    @error('city_id')<div class="error-message">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- ═══════════ СЕКЦИЯ: АДМИНИСТРАТОР ═══════════ --}}
            <div class="form-section-title" style="margin-top:22px;" data-i18n="sectionUser">👤 Данные администратора</div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name" data-i18n="nameLabel">Имя *</label>
                    <div class="input-wrapper">
                        <input type="text" name="name" id="name"
                               class="form-input @error('name') error @enderror"
                               value="{{ old('name') }}"
                               placeholder="Иван Иванов" required>
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    @error('name')<div class="error-message">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="email" data-i18n="emailLabel">Email *</label>
                    <div class="input-wrapper">
                        <input type="email" name="email" id="email"
                               class="form-input @error('email') error @enderror"
                               value="{{ old('email') }}"
                               placeholder="admin@company.tj" required>
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                        </svg>
                    </div>
                    @error('email')<div class="error-message">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="password" data-i18n="passwordLabel">Пароль *</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password"
                               class="form-input @error('password') error @enderror"
                               placeholder="Минимум 8 символов" required>
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <button type="button" class="toggle-password" onclick="togglePassword('password','eyeIcon1')">
                            <svg id="eyeIcon1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')<div class="error-message">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation" data-i18n="confirmLabel">Подтверждение *</label>
                    <div class="input-wrapper">
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-input" placeholder="Повторите пароль" required>
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation','eyeIcon2')">
                            <svg id="eyeIcon2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Роль (автоматически) --}}
            <div class="form-group">
                <label class="form-label" data-i18n="roleLabel">Ваша роль</label>
                <div class="role-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <span data-i18n="roleAdmin">Администратор (автоматически)</span>
                </div>
            </div>

            {{-- Кнопка --}}
            <button type="submit" class="submit-btn" id="submitBtn">
                <span class="btn-text">
                    <span data-i18n="submitBtn">Создать компанию и войти</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                    </svg>
                </span>
            </button>
        </form>

        <div class="login-section">
            <p class="login-text">
                <span data-i18n="hasAccount">Уже есть аккаунт?</span>
                <a href="{{ route('login') }}" class="login-link" data-i18n="loginLink">Войти</a>
            </p>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    // ===== Частицы =====
    var particlesEl = document.getElementById('particles');
    for (var i = 0; i < 30; i++) {
        var p = document.createElement('div');
        p.className = 'particle';
        p.style.left = Math.random() * 100 + '%';
        p.style.animationDuration = (Math.random() * 10 + 8) + 's';
        p.style.animationDelay = (Math.random() * 10) + 's';
        p.style.width = (Math.random() * 3 + 1) + 'px';
        p.style.height = p.style.width;
        p.style.opacity = Math.random() * 0.5 + 0.1;
        particlesEl.appendChild(p);
    }

    // ===== Переводы =====
    var translations = {
        ru: {
            subtitle: 'Создание компании', sectionCompany: '🏢 Данные компании', companyNameLabel: 'Название компании *',
            companyTypeLabel: 'Тип организации *', regionLabel: 'Область *', cityLabel: 'Город / Район *',
            sectionUser: '👤 Данные администратора', nameLabel: 'Имя *', emailLabel: 'Email *',
            passwordLabel: 'Пароль *', confirmLabel: 'Подтверждение *', roleLabel: 'Ваша роль',
            roleAdmin: 'Администратор (автоматически)', submitBtn: 'Создать компанию и войти',
            hasAccount: 'Уже есть аккаунт?', loginLink: 'Войти', passMismatch: 'Пароли не совпадают', selectCity: 'Выберите город / район'
        },
        tj: {
            subtitle: 'Сохтани ширкат', sectionCompany: '🏢 Маълумоти ширкат', companyNameLabel: 'Номи ширкат *',
            companyTypeLabel: 'Намуди ташкилот *', regionLabel: 'Вилоят *', cityLabel: 'Шаҳр / Ноҳия *',
            sectionUser: '👤 Маълумоти администратор', nameLabel: 'Ном *', emailLabel: 'Почтаи электронӣ *',
            passwordLabel: 'Рамз *', confirmLabel: 'Тасдиқ *', roleLabel: 'Нақши шумо',
            roleAdmin: 'Администратор (автоматикӣ)', submitBtn: 'Ширкат сохтан ва ворид шудан',
            hasAccount: 'Ҳисоб доред?', loginLink: 'Ворид шудан', passMismatch: 'Рамзҳо мувофиқ нестанд', selectCity: 'Шаҳр / ноҳияро интихоб кунед'
        },
        en: {
            subtitle: 'Create Company', sectionCompany: '🏢 Company Details', companyNameLabel: 'Company Name *',
            companyTypeLabel: 'Organization Type *', regionLabel: 'Region *', cityLabel: 'City / District *',
            sectionUser: '👤 Admin Details', nameLabel: 'Full Name *', emailLabel: 'Email *',
            passwordLabel: 'Password *', confirmLabel: 'Confirm *', roleLabel: 'Your Role',
            roleAdmin: 'Administrator (automatic)', submitBtn: 'Create Company & Sign In',
            hasAccount: 'Already have an account?', loginLink: 'Sign In', passMismatch: 'Passwords do not match', selectCity: 'Select city / district'
        }
    };

    var currentLang = 'ru';
    window.switchLang = function(lang) {
        currentLang = lang;
        document.documentElement.lang = lang;
        var t = translations[lang];
        document.querySelectorAll('[data-i18n]').forEach(function(el) {
            var key = el.getAttribute('data-i18n');
            if (t[key]) el.textContent = t[key];
        });
    };

    // ===== Города из БД =====
    var allCities = @json($cities);
    var regionSelect = document.getElementById('region_id');
    var citySelect = document.getElementById('city_id');

    function loadCities(regionId) {
        citySelect.innerHTML = '';
        if (!regionId) {
            citySelect.innerHTML = '<option value="">-- Сначала выберите область --</option>';
            citySelect.disabled = true;
            return;
        }
        citySelect.disabled = false;
        citySelect.innerHTML = '<option value="">-- Выберите город / район --</option>';
        allCities.forEach(function(city) {
            if (String(city.region_id) === String(regionId)) {
                var opt = document.createElement('option');
                opt.value = city.id;
                opt.textContent = city.name_ru;
                citySelect.appendChild(opt);
            }
        });
    }

    regionSelect.addEventListener('change', function() { loadCities(this.value); });

    // ===== Восстановление old() значений =====
    var oldRegion = '{{ old("region_id") }}';
    var oldCity = '{{ old("city_id") }}';
    if (oldRegion) {
        regionSelect.value = oldRegion;
        loadCities(oldRegion);
        if (oldCity) { setTimeout(function() { citySelect.value = oldCity; }, 50); }
    }

    // ===== Toggle password =====
    window.togglePassword = function(inputId, iconId) {
        var input = document.getElementById(inputId);
        var icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/><line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
        }
    };

    // ===== Notification =====
    function showNotification(message, type) {
        var notif = document.getElementById('notification');
        notif.textContent = message;
        notif.className = 'notification ' + type + ' show';
        setTimeout(function() { notif.classList.remove('show'); }, 3000);
    }

    // ===== Submit =====
    window.handleSubmit = function(e) {
        var t = translations[currentLang];
        var pass = document.getElementById('password').value;
        var confirm = document.getElementById('password_confirmation').value;

        if (pass !== confirm) { showNotification(t.passMismatch, 'error'); return false; }
        if (!citySelect.value) { showNotification(t.selectCity, 'error'); return false; }

        document.getElementById('submitBtn').classList.add('loading');
        return true;
    };

    var errors = document.querySelectorAll('.error-message');
    if (errors.length > 0 && errors[0].textContent.trim()) {
        showNotification(errors[0].textContent.trim(), 'error');
    }
})();
</script>
</body>
</html>