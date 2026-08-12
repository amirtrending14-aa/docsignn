<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DocSign — Восстановление пароля</title>

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
        .reset-card {
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
        .reset-card:hover {
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

        .logo-section { text-align: center; margin-bottom: 22px; animation: riseIn .7s cubic-bezier(.16,1,.3,1) .15s both; }

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

        .info-banner {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(226, 178, 90, 0.07);
            border: 1px solid rgba(226, 178, 90, 0.22);
            font-size: 12.5px; line-height: 1.55;
            color: var(--gold-soft); font-weight: 600;
            margin-bottom: 18px;
            animation: riseIn .7s cubic-bezier(.16,1,.3,1) .25s both;
        }
        .info-banner svg { width: 17px; height: 17px; flex-shrink: 0; margin-top: 1px; color: var(--gold); }

        .status-banner {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(79, 201, 189, 0.1);
            border: 1px solid rgba(79, 201, 189, 0.3);
            color: var(--teal);
            font-size: 13px; font-weight: 600; text-align: center;
            margin-bottom: 18px;
            animation: riseIn .7s cubic-bezier(.16,1,.3,1) .3s both;
        }
        .status-banner svg { width: 16px; height: 16px; flex-shrink: 0; }

        .form-group { margin-bottom: 18px; animation: riseIn .6s cubic-bezier(.16,1,.3,1) .35s both; }

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
            padding: 12px 16px 12px 44px;
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

        .error-message {
            display: flex; align-items: center; gap: 5px;
            font-size: 12px; color: #f87171;
            margin-top: 6px; font-weight: 500;
        }
        .error-message svg { width: 12px; height: 12px; flex-shrink: 0; }

        .submit-btn {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #e8bc66, var(--gold-deep));
            border: none; border-radius: 12px;
            color: #171004; font-family: 'Manrope', sans-serif;
            font-size: 15px; font-weight: 800;
            cursor: pointer; position: relative; overflow: hidden;
            transition: all .3s ease; letter-spacing: .2px;
            margin-top: 4px;
            animation: riseIn .6s cubic-bezier(.16,1,.3,1) .45s both;
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

        .back-link-section {
            text-align: center; margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
            animation: riseIn .6s cubic-bezier(.16,1,.3,1) .55s both;
        }
        .back-link {
            display: inline-flex; align-items: center; gap: 7px;
            font-size: 13.5px; font-weight: 600;
            color: var(--muted); text-decoration: none;
            padding: 6px 12px; border-radius: 8px;
            transition: all .2s ease;
        }
        .back-link svg { width: 15px; height: 15px; transition: transform .3s ease; }
        .back-link:hover { color: var(--teal); background: rgba(79, 201, 189, 0.06); }
        .back-link:hover svg { transform: translateX(-3px); }

        /* ================= ФУТЕР ================= */
        .page-footer { text-align: center; animation: riseIn .8s cubic-bezier(.16,1,.3,1) .7s both; }
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
            .reset-card { padding: 26px 18px 22px; border-radius: 18px; }
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
    <select class="lang-select" id="lang-select" onchange="switchLang(this.value)">
        <option value="ru" selected>🇷🇺 RU</option>
        <option value="tj">🇹🇯 TJ</option>
        <option value="en">🇬🇧 EN</option>
    </select>
</div>

<div class="page">
    <div class="reset-card">
        <div class="card-topline"></div>

        <div class="logo-section">
            <img src="{{ asset('img/dss.png') }}" alt="DocSign Logo" class="logo-img">
            <div class="logo-title">Doc<span>Sign</span></div>
            <div class="logo-subtitle" data-i18n="subtitle">Восстановление пароля</div>
        </div>

        <div class="info-banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 16v-4"/>
                <path d="M12 8h.01"/>
            </svg>
            <span data-i18n="infoBanner">Забыли пароль? Введите email, и мы отправим ссылку для создания нового пароля.</span>
        </div>

        @if (session('status'))
        <div class="status-banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" id="resetForm" onsubmit="return handleReset(event)">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email" data-i18n="emailLabel">Электронная почта</label>
                <div class="input-wrapper">
                    <input type="email" name="email" id="email"
                           class="form-input @error('email') error @enderror"
                           value="{{ old('email') }}"
                           data-i18n-placeholder="emailPlaceholder"
                           placeholder="name@company.com"
                           required autofocus autocomplete="email">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                    </svg>
                </div>
                @error('email')
                <div class="error-message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ $message }}
                </div>
                @enderror
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">
                <span class="btn-text">
                    <span data-i18n="sendBtn">Отправить ссылку</span>
                    <svg class="btn-arrow" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                    </svg>
                </span>
            </button>
        </form>

        <div class="back-link-section">
            <a href="{{ route('login') }}" class="back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
                </svg>
                <span data-i18n="backLink">Вернуться к входу</span>
            </a>
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
            subtitle: 'Восстановление пароля',
            infoBanner: 'Забыли пароль? Введите email, и мы отправим ссылку для создания нового пароля.',
            emailLabel: 'Электронная почта',
            emailPlaceholder: 'name@company.com',
            sendBtn: 'Отправить ссылку',
            backLink: 'Вернуться к входу',
            badgeSecurity: 'Защита',
            badgeSign: 'ЭЦП',
            rights: 'Все права защищены.',
            invalidEmail: 'Неверный формат email',
            emptyEmail: 'Введите email'
        },
        tj: {
            subtitle: 'Барқарорсозии рамз',
            infoBanner: 'Рамзро фаромӯш кардед? Email-ро ворид кунед, мо пайванди барқарорсозиро мефиристем.',
            emailLabel: 'Почтаи электронӣ',
            emailPlaceholder: 'name@company.com',
            sendBtn: 'Фиристодани пайванд',
            backLink: 'Бозгашт ба вуруд',
            badgeSecurity: 'Ҳифз',
            badgeSign: 'ЭИИ',
            rights: 'Ҳамаи ҳуқуқҳо ҳифз шудаанд.',
            invalidEmail: 'Формати email нодуруст',
            emptyEmail: 'Email-ро ворид кунед'
        },
        en: {
            subtitle: 'Password Recovery',
            infoBanner: "Forgot your password? Enter your email and we'll send you a link to create a new one.",
            emailLabel: 'Email Address',
            emailPlaceholder: 'name@company.com',
            sendBtn: 'Send Reset Link',
            backLink: 'Back to Sign In',
            badgeSecurity: 'Security',
            badgeSign: 'EDS',
            rights: 'All rights reserved.',
            invalidEmail: 'Invalid email format',
            emptyEmail: 'Please enter your email'
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
    }

    /* ============ Уведомления ============ */
    function showNotification(message, type) {
        var notif = document.getElementById('notification');
        notif.textContent = message;
        notif.className = 'notification ' + type + ' show';
        setTimeout(function() { notif.classList.remove('show'); }, 3000);
    }

    /* ============ Отправка формы ============ */
    function handleReset(e) {
        var email = document.getElementById('email');
        var t = translations[currentLang];

        email.classList.remove('error');

        if (!email.value.trim()) {
            email.classList.add('error');
            showNotification(t.emptyEmail, 'error');
            return false;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            email.classList.add('error');
            showNotification(t.invalidEmail, 'error');
            return false;
        }

        document.getElementById('submitBtn').classList.add('loading');
        return true;
    }

    var emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            this.classList.remove('error');
            document.getElementById('submitBtn').classList.remove('loading');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        /* Ошибки валидации Laravel */
        var errorMessages = document.querySelectorAll('.error-message');
        if (errorMessages.length > 0 && errorMessages[0].textContent.trim()) {
            showNotification(errorMessages[0].textContent.trim(), 'error');
        }
    });

    /* Успешная отправка (session status) */
    @if (session('status'))
    document.addEventListener('DOMContentLoaded', function() {
        showNotification(@json(session('status')), 'success');
    });
    @endif
</script>
</body>
</html>
