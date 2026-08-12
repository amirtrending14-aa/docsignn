<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DocSign — Регистрация</title>

    <link rel="icon" type="image/png" href="{{ asset('img/dss.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/dss.png') }}">

    {{-- Fontsource: Manrope --}}
    <link href="https://cdn.jsdelivr.net/npm/@fontsource/manrope/400.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fontsource/manrope/500.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fontsource/manrope/600.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fontsource/manrope/700.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fontsource/manrope/800.css" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg: #080d18;
            --ink: #eef2f9;
            --muted: #93a0b8;
            --gold: #e2b25a;
            --gold-soft: #f0d9a8;
            --gold-deep: #c8923a;
            --teal: #4fc9bd;
            --line: rgba(160, 175, 200, 0.15);
            --panel: rgba(10, 15, 28, 0.78);
        }

        body {
            font-family: 'Manrope', sans-serif;
            background: var(--bg);
            color: var(--ink);
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* ================= ФОН ================= */
        .bg-glow { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
        .bg-glow::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 60% 45% at 22% 18%, rgba(226, 178, 90, 0.08) 0%, transparent 60%),
                radial-gradient(ellipse 55% 45% at 80% 82%, rgba(226, 178, 90, 0.05) 0%, transparent 60%),
                radial-gradient(ellipse 40% 35% at 78% 12%, rgba(79, 201, 189, 0.05) 0%, transparent 60%);
        }

        .particles { position: fixed; inset: 0; z-index: 1; pointer-events: none; }
        .particle {
            position: absolute;
            width: 2px; height: 2px;
            background: rgba(226, 178, 90, 0.4);
            border-radius: 50%;
            animation: floatUp linear infinite;
        }
        @keyframes floatUp {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            12% { opacity: 1; }
            88% { opacity: 1; }
            100% { transform: translateY(-8vh) scale(1); opacity: 0; }
        }

        /* ================= СТРАНИЦА ================= */
        .page {
            position: relative;
            z-index: 5;
            min-height: 100vh;
            min-height: 100svh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 70px 20px 24px;
            gap: 22px;
        }

        /* ================= КАРТОЧКА ================= */
        .register-card {
            width: 100%;
            max-width: 460px;
            background: var(--panel);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 32px 30px 28px;
            position: relative;
            box-shadow: 0 26px 70px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.04);
            transition: border-color .35s ease, box-shadow .35s ease;
            animation: cardAppear .8s cubic-bezier(.16,1,.3,1) .05s both;
        }
        .register-card:hover {
            border-color: rgba(226, 178, 90, 0.28);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.55), 0 0 60px rgba(226, 178, 90, 0.05), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }
        @keyframes cardAppear {
            0% { opacity: 0; transform: translateY(26px); filter: blur(8px); }
            100% { opacity: 1; transform: translateY(0); filter: blur(0); }
        }

        .card-topline {
            position: absolute;
            top: 0; left: 22px; right: 22px;
            height: 2px;
            border-radius: 0 0 4px 4px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            background-size: 200% 100%;
            animation: shimmer 4s ease-in-out infinite;
        }
        @keyframes shimmer {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes riseIn {
            0% { opacity: 0; transform: translateY(14px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .logo-section { text-align: center; margin-bottom: 24px; animation: riseIn .7s cubic-bezier(.16,1,.3,1) .15s both; }

        .logo-img {
            width: 58px; height: 58px;
            border-radius: 16px;
            margin: 0 auto 10px;
            display: block;
            box-shadow: 0 8px 26px rgba(226, 178, 90, 0.22);
        }

        .logo-title { font-size: 24px; font-weight: 800; letter-spacing: -0.4px; margin-bottom: 3px; }
        .logo-title span { color: var(--gold); }
        .logo-subtitle {
            font-size: 11px; font-weight: 600;
            color: var(--muted);
            text-transform: uppercase; letter-spacing: 2px;
        }

        .form-group { margin-bottom: 15px; animation: riseIn .6s cubic-bezier(.16,1,.3,1) both; }
        .form-group:nth-of-type(1) { animation-delay: .25s; }
        .form-group:nth-of-type(2) { animation-delay: .33s; }
        .form-group:nth-of-type(3) { animation-delay: .41s; }
        .form-group:nth-of-type(4) { animation-delay: .49s; }

        .form-label {
            display: block; font-size: 12.5px; font-weight: 700;
            color: var(--muted); margin-bottom: 7px; letter-spacing: .3px;
        }

        .input-wrapper { position: relative; }

        .input-icon {
            position: absolute; left: 15px; top: 50%;
            transform: translateY(-50%);
            width: 18px; height: 18px;
            color: var(--muted);
            transition: color .3s ease;
            pointer-events: none; z-index: 2;
        }

        .form-input {
            width: 100%;
            padding: 12px 44px;
            background: rgba(18, 25, 44, 0.6);
            border: 1px solid var(--line);
            border-radius: 12px;
            color: var(--ink);
            font-family: 'Manrope', sans-serif;
            font-size: 14px; font-weight: 500;
            transition: all .3s ease;
            outline: none;
        }
        .form-input::placeholder { color: rgba(147, 160, 184, 0.45); }
        .form-input:focus {
            border-color: rgba(226, 178, 90, 0.6);
            background: rgba(18, 25, 44, 0.9);
            box-shadow: 0 0 0 3px rgba(226, 178, 90, 0.14);
        }
        .form-input:focus ~ .input-icon { color: var(--gold); }
        .form-input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 30px rgba(18, 25, 44, 0.95) inset !important;
            -webkit-text-fill-color: var(--ink) !important;
            caret-color: var(--ink);
        }
        .form-input.error { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15); }
        .error-message { font-size: 12px; color: #f87171; margin-top: 5px; font-weight: 500; }

        .toggle-password {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: var(--muted);
            cursor: pointer; padding: 4px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            transition: all .2s ease; z-index: 2;
        }
        .toggle-password:hover { color: var(--ink); background: rgba(160, 175, 200, 0.1); }
        .toggle-password svg { width: 17px; height: 17px; }

        /* ---- индикатор надёжности пароля ---- */
        .password-strength { margin-top: 8px; display: flex; gap: 4px; }
        .strength-bar {
            flex: 1; height: 3px; border-radius: 3px;
            background: rgba(160, 175, 200, 0.15);
            transition: all .3s ease;
        }
        .strength-bar.weak { background: #ef4444; }
        .strength-bar.medium { background: #f59e0b; }
        .strength-bar.strong { background: #10b981; }
        .strength-text {
            font-size: 11px; font-weight: 600;
            margin-top: 5px; min-height: 14px;
            color: var(--muted);
            transition: color .3s ease;
        }
        .strength-text.weak { color: #f87171; }
        .strength-text.medium { color: #f59e0b; }
        .strength-text.strong { color: #34d399; }

        .submit-btn {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #e8bc66, var(--gold-deep));
            border: none; border-radius: 12px;
            color: #171004; font-family: 'Manrope', sans-serif;
            font-size: 15px; font-weight: 800;
            cursor: pointer; position: relative; overflow: hidden;
            transition: all .3s ease; letter-spacing: .2px;
            margin-top: 4px;
            animation: riseIn .6s cubic-bezier(.16,1,.3,1) .58s both;
        }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(226, 178, 90, 0.35); }
        .submit-btn:active { transform: translateY(0); }
        .submit-btn .btn-text { display: flex; align-items: center; justify-content: center; gap: 8px; position: relative; z-index: 1; }
        .submit-btn .btn-arrow { transition: transform .3s ease; }
        .submit-btn:hover .btn-arrow { transform: translateX(4px); }
        .submit-btn.loading .btn-text { opacity: 0; }
        .submit-btn.loading::after {
            content: ''; position: absolute;
            width: 22px; height: 22px;
            border: 3px solid rgba(23, 16, 4, 0.3);
            border-top-color: #171004; border-radius: 50%;
            top: 50%; left: 50%; margin: -11px 0 0 -11px;
            animation: spin .8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .login-link-section { text-align: center; margin-top: 20px; animation: riseIn .6s cubic-bezier(.16,1,.3,1) .68s both; }
        .login-text { font-size: 13.5px; color: var(--muted); font-weight: 500; }
        .login-link { color: var(--teal); text-decoration: none; font-weight: 700; }
        .login-link:hover { text-decoration: underline; }

        /* ================= ФУТЕР ================= */
        .page-footer { text-align: center; animation: riseIn .8s cubic-bezier(.16,1,.3,1) .8s both; }
        .footer-badges { display: flex; justify-content: center; gap: 20px; }
        .badge { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; color: var(--muted); opacity: .8; }
        .badge svg { width: 13px; height: 13px; color: var(--gold); }
        .copyright { margin-top: 10px; font-size: 11.5px; color: rgba(147, 160, 184, 0.45); font-weight: 500; }

        /* ================= ЯЗЫК / УВЕДОМЛЕНИЯ ================= */
        .lang-switcher {
            position: fixed; top: 18px; right: 18px; z-index: 100;
            background: rgba(9, 13, 24, 0.8);
            backdrop-filter: blur(16px);
            border: 1px solid var(--line);
            border-radius: 11px; padding: 4px;
        }
        .lang-select {
            padding: 7px 12px;
            background: rgba(226, 178, 90, 0.08);
            color: var(--gold-soft);
            border: 1px solid rgba(226, 178, 90, 0.25);
            border-radius: 8px;
            font-family: 'Manrope', sans-serif;
            font-size: 12.5px; font-weight: 700;
            cursor: pointer; outline: none;
            appearance: none; -webkit-appearance: none;
            transition: all .3s ease;
        }
        .lang-select:hover { background: rgba(226, 178, 90, 0.16); }
        .lang-select:focus { border-color: var(--gold); }
        .lang-select option { background-color: #0d1322; color: var(--ink); }

        .notification {
            position: fixed; top: 18px; left: 50%;
            transform: translateX(-50%) translateY(-120px);
            padding: 13px 24px; border-radius: 13px;
            font-size: 13.5px; font-weight: 700;
            z-index: 1000;
            transition: transform .5s cubic-bezier(.16,1,.3,1);
            backdrop-filter: blur(16px);
            max-width: calc(100vw - 32px);
            text-align: center;
        }
        .notification.show { transform: translateX(-50%) translateY(0); }
        .notification.success { background: rgba(79, 201, 189, 0.16); border: 1px solid rgba(79, 201, 189, 0.4); color: var(--teal); }
        .notification.error { background: rgba(239, 68, 68, 0.16); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; }

        /* ================= АДАПТИВ ================= */
        @media (max-width: 520px) {
            .page { padding: 60px 14px 18px; }
            .register-card { padding: 26px 18px 22px; border-radius: 18px; }
            .logo-img { width: 48px; height: 48px; }
            .logo-title { font-size: 21px; }
            .lang-switcher { top: 12px; right: 12px; }
            .lang-select { padding: 6px 10px; font-size: 12px; }
            .footer-badges { gap: 14px; }
        }

        @media (min-height: 900px) and (min-width: 901px) {
            body { overflow: hidden; }
        }
    </style>
</head>
<body>

<div class="bg-glow"></div>
<div class="particles" id="particles"></div>

<div class="notification" id="notification"></div>

<div class="lang-switcher">
    <select class="lang-select" onchange="switchLang(this.value)">
        <option value="ru" selected>🇷🇺 RU</option>
        <option value="tj">🇹🇯 TJ</option>
        <option value="en">🇬🇧 EN</option>
    </select>
</div>

<div class="page">
    <div class="register-card">
        <div class="card-topline"></div>

        <div class="logo-section">
            <img src="{{ asset('img/dss.png') }}" alt="DocSign Logo" class="logo-img">
            <div class="logo-title">Doc<span>Sign</span></div>
            <div class="logo-subtitle" data-i18n="subtitle">Создайте аккаунт</div>
        </div>

        <form method="POST" action="{{ route('register') }}" id="registerForm" onsubmit="return handleRegister(event)">
            @csrf

            {{-- Имя --}}
            <div class="form-group">
                <label class="form-label" for="name" data-i18n="nameLabel">Полное имя</label>
                <div class="input-wrapper">
                    <input type="text" name="name" id="name"
                           class="form-input @error('name') error @enderror"
                           value="{{ old('name') }}"
                           data-i18n-placeholder="namePlaceholder"
                           placeholder="Иван Иванов"
                           required autofocus autocomplete="name">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                @error('name')<div class="error-message">{{ $message }}</div>@enderror
            </div>

            {{-- Email --}}
            <div class="form-group">
                <label class="form-label" for="email" data-i18n="emailLabel">Электронная почта</label>
                <div class="input-wrapper">
                    <input type="email" name="email" id="email"
                           class="form-input @error('email') error @enderror"
                           value="{{ old('email') }}"
                           data-i18n-placeholder="emailPlaceholder"
                           placeholder="name@company.com"
                           required autocomplete="username">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                    </svg>
                </div>
                @error('email')<div class="error-message">{{ $message }}</div>@enderror
            </div>

            {{-- Пароль --}}
            <div class="form-group">
                <label class="form-label" for="password" data-i18n="passwordLabel">Пароль</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password"
                           class="form-input @error('password') error @enderror"
                           data-i18n-placeholder="passwordPlaceholder"
                           placeholder="••••••••••"
                           required autocomplete="new-password"
                           oninput="checkPasswordStrength(this.value)">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <button type="button" class="toggle-password" onclick="togglePassword('password', 'eyeIcon1')" aria-label="Toggle password visibility">
                        <svg id="eyeIcon1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <div class="password-strength" id="strengthBars">
                    <div class="strength-bar" id="bar1"></div>
                    <div class="strength-bar" id="bar2"></div>
                    <div class="strength-bar" id="bar3"></div>
                    <div class="strength-bar" id="bar4"></div>
                </div>
                <div class="strength-text" id="strengthText"></div>
                @error('password')<div class="error-message">{{ $message }}</div>@enderror
            </div>

            {{-- Подтверждение пароля --}}
            <div class="form-group">
                <label class="form-label" for="password_confirmation" data-i18n="confirmLabel">Подтвердите пароль</label>
                <div class="input-wrapper">
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="form-input @error('password_confirmation') error @enderror"
                           data-i18n-placeholder="confirmPlaceholder"
                           placeholder="••••••••••"
                           required autocomplete="new-password">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation', 'eyeIcon2')" aria-label="Toggle password visibility">
                        <svg id="eyeIcon2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                @error('password_confirmation')<div class="error-message">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">
                <span class="btn-text">
                    <span data-i18n="registerBtn">Зарегистрироваться</span>
                    <svg class="btn-arrow" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                    </svg>
                </span>
            </button>
        </form>

        <div class="login-link-section">
            <p class="login-text">
                <span data-i18n="hasAccount">Уже есть аккаунт?</span>
                <a href="{{ route('login') }}" class="login-link" data-i18n="loginLink">Войти</a>
            </p>
        </div>
    </div>

    <footer class="page-footer">
        <div class="footer-badges">
            <div class="badge">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
                <span>SSL</span>
            </div>
            <div class="badge">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                <span data-i18n="badgeSecurity">Защита</span>
            </div>
            <div class="badge">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"/></svg>
                <span data-i18n="badgeSign">ЭЦП</span>
            </div>
        </div>
        <p class="copyright">© {{ date('Y') }} DocSign Ecosystem. <span data-i18n="rights">Все права защищены.</span></p>
    </footer>
</div>

<script>
    /* ============ Частицы фона ============ */
    (function() {
        var wrap = document.getElementById('particles');
        for (var i = 0; i < 22; i++) {
            var pt = document.createElement('div');
            pt.className = 'particle';
            pt.style.left = Math.random() * 100 + '%';
            pt.style.animationDuration = (Math.random() * 12 + 10) + 's';
            pt.style.animationDelay = (Math.random() * 12) + 's';
            var sz = (Math.random() * 2 + 1) + 'px';
            pt.style.width = sz; pt.style.height = sz;
            pt.style.opacity = Math.random() * 0.4 + 0.1;
            wrap.appendChild(pt);
        }
    })();

    /* ============ Переводы ============ */
    var translations = {
        ru: {
            subtitle: 'Создайте аккаунт',
            nameLabel: 'Полное имя',
            namePlaceholder: 'Иван Иванов',
            emailLabel: 'Электронная почта',
            emailPlaceholder: 'name@company.com',
            passwordLabel: 'Пароль',
            passwordPlaceholder: '••••••••••',
            confirmLabel: 'Подтвердите пароль',
            confirmPlaceholder: '••••••••••',
            registerBtn: 'Зарегистрироваться',
            hasAccount: 'Уже есть аккаунт?',
            loginLink: 'Войти',
            badgeSecurity: 'Защита',
            badgeSign: 'ЭЦП',
            rights: 'Все права защищены.',
            weak: 'Слабый пароль',
            medium: 'Средний пароль',
            strong: 'Сильный пароль',
            veryStrong: 'Очень сильный пароль',
            passwordMismatch: 'Пароли не совпадают',
            minLen: 'Пароль должен быть не менее 8 символов'
        },
        tj: {
            subtitle: 'Ҳисоб эҷод кунед',
            nameLabel: 'Номи пурра',
            namePlaceholder: 'Иван Иванов',
            emailLabel: 'Почтаи электронӣ',
            emailPlaceholder: 'name@company.com',
            passwordLabel: 'Рамз',
            passwordPlaceholder: '••••••••••',
            confirmLabel: 'Рамзро тасдиқ кунед',
            confirmPlaceholder: '••••••••••',
            registerBtn: 'Бақайдгирӣ',
            hasAccount: 'Аллакай ҳисоб доред?',
            loginLink: 'Ворид шавед',
            badgeSecurity: 'Ҳифз',
            badgeSign: 'ЭИИ',
            rights: 'Ҳамаи ҳуқуқҳо ҳифз шудаанд.',
            weak: 'Рамзи заиф',
            medium: 'Рамзи миёна',
            strong: 'Рамзи қавӣ',
            veryStrong: 'Рамзи хеле қавӣ',
            passwordMismatch: 'Рамзҳо мувофиқ нестанд',
            minLen: 'Рамз бояд камаш 8 аломат дошта бошад'
        },
        en: {
            subtitle: 'Create an Account',
            nameLabel: 'Full Name',
            namePlaceholder: 'John Doe',
            emailLabel: 'Email Address',
            emailPlaceholder: 'name@company.com',
            passwordLabel: 'Password',
            passwordPlaceholder: '••••••••••',
            confirmLabel: 'Confirm Password',
            confirmPlaceholder: '••••••••••',
            registerBtn: 'Register',
            hasAccount: 'Already have an account?',
            loginLink: 'Sign In',
            badgeSecurity: 'Security',
            badgeSign: 'EDS',
            rights: 'All rights reserved.',
            weak: 'Weak password',
            medium: 'Medium password',
            strong: 'Strong password',
            veryStrong: 'Very strong password',
            passwordMismatch: 'Passwords do not match',
            minLen: 'Password must be at least 8 characters'
        }
    };

    var currentLang = 'ru';

    function switchLang(lang) {
        currentLang = lang;
        document.documentElement.lang = lang;
        var t = translations[lang];

        document.querySelectorAll('[data-i18n]').forEach(function(el) {
            var key = el.getAttribute('data-i18n');
            if (t[key]) el.textContent = t[key];
        });

        document.querySelectorAll('[data-i18n-placeholder]').forEach(function(el) {
            var key = el.getAttribute('data-i18n-placeholder');
            if (t[key]) el.placeholder = t[key];
        });

        var passVal = document.getElementById('password').value;
        if (passVal) checkPasswordStrength(passVal);
    }

    /* ============ Показ пароля ============ */
    var passwordStates = { password: false, password_confirmation: false };

    function togglePassword(fieldId, iconId) {
        passwordStates[fieldId] = !passwordStates[fieldId];
        var input = document.getElementById(fieldId);
        var icon = document.getElementById(iconId);
        if (passwordStates[fieldId]) {
            input.type = 'text';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/><line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
        }
    }

    /* ============ Надёжность пароля ============ */
    function checkPasswordStrength(password) {
        var bars = [
            document.getElementById('bar1'),
            document.getElementById('bar2'),
            document.getElementById('bar3'),
            document.getElementById('bar4')
        ];
        var textEl = document.getElementById('strengthText');
        var t = translations[currentLang];

        bars.forEach(function(bar) { bar.className = 'strength-bar'; });
        textEl.className = 'strength-text';
        textEl.textContent = '';
        if (!password) return;

        var score = 0;
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[^a-zA-Z0-9]/.test(password)) score++;

        var level, label;
        if (score <= 1) { level = 'weak'; label = t.weak; }
        else if (score === 2) { level = 'medium'; label = t.medium; }
        else if (score === 3) { level = 'strong'; label = t.strong; }
        else { level = 'strong'; label = t.veryStrong; }

        for (var i = 0; i < score; i++) {
            bars[i].className = 'strength-bar ' + level;
        }
        textEl.className = 'strength-text ' + level;
        textEl.textContent = label;
    }

    /* ============ Уведомления ============ */
    function showNotification(message, type) {
        var notif = document.getElementById('notification');
        notif.textContent = message;
        notif.className = 'notification ' + type + ' show';
        setTimeout(function() { notif.classList.remove('show'); }, 3000);
    }

    /* ============ Регистрация ============ */
    function handleRegister(e) {
        var password = document.getElementById('password');
        var confirm = document.getElementById('password_confirmation');
        var t = translations[currentLang];

        password.classList.remove('error');
        confirm.classList.remove('error');

        if (password.value !== confirm.value) {
            confirm.classList.add('error');
            showNotification(t.passwordMismatch, 'error');
            return false;
        }

        if (password.value.length < 8) {
            password.classList.add('error');
            showNotification(t.minLen, 'error');
            return false;
        }

        document.getElementById('submitBtn').classList.add('loading');
        return true;
    }

    document.querySelectorAll('.form-input').forEach(function(input) {
        input.addEventListener('input', function() {
            this.classList.remove('error');
            document.getElementById('submitBtn').classList.remove('loading');
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        var errorMessages = document.querySelectorAll('.error-message');
        if (errorMessages.length > 0 && errorMessages[0].textContent.trim()) {
            showNotification(errorMessages[0].textContent.trim(), 'error');
        }
    });
</script>
</body>
</html>
