<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DocSign — Вход в систему</title>

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
            gap: 24px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 440px));
            justify-content: center;
            align-items: stretch;
            gap: 26px;
            width: 100%;
            max-width: 920px;
        }

        /* ================= ОБЩИЙ СТИЛЬ КАРТОЧЕК ================= */
        .card {
            background: var(--panel);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 30px 28px 26px;
            position: relative;
            display: flex;
            flex-direction: column;
            box-shadow: 0 26px 70px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.04);
            transition: border-color .35s ease, box-shadow .35s ease;
        }
        .card:hover {
            border-color: rgba(226, 178, 90, 0.28);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.55), 0 0 60px rgba(226, 178, 90, 0.05), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        .login-card  { animation: cardAppear .8s cubic-bezier(.16,1,.3,1) .05s both; }
        .company-card{ animation: cardAppear .8s cubic-bezier(.16,1,.3,1) .18s both; }
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

        /* ================= КАРТОЧКА ВХОДА ================= */
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

        .session-status {
            padding: 11px 14px; border-radius: 11px; margin-bottom: 16px;
            font-size: 13px; font-weight: 600; text-align: center;
            background: rgba(79, 201, 189, 0.1);
            border: 1px solid rgba(79, 201, 189, 0.3);
            color: var(--teal);
        }

        .form-group { margin-bottom: 14px; animation: riseIn .6s cubic-bezier(.16,1,.3,1) both; }
        .form-group:nth-of-type(1) { animation-delay: .28s; }
        .form-group:nth-of-type(2) { animation-delay: .38s; }

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

        .form-options {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 18px;
            animation: riseIn .6s cubic-bezier(.16,1,.3,1) .48s both;
        }

        .remember-me {
            display: flex; align-items: center; gap: 9px;
            cursor: pointer; font-size: 13px;
            color: var(--muted); font-weight: 600; user-select: none;
        }
        .custom-checkbox {
            width: 18px; height: 18px;
            border: 1.5px solid rgba(160, 175, 200, 0.35);
            border-radius: 5px;
            display: flex; align-items: center; justify-content: center;
            transition: all .3s ease; flex-shrink: 0;
        }
        .custom-checkbox svg { width: 11px; height: 11px; opacity: 0; transform: scale(.5); transition: all .2s ease; color: #171004; }
        .remember-input { display: none; }
        .remember-input:checked + .custom-checkbox { background: var(--gold); border-color: var(--gold); }
        .remember-input:checked + .custom-checkbox svg { opacity: 1; transform: scale(1); }

        .forgot-link {
            font-size: 13px; color: var(--gold-soft);
            text-decoration: none; font-weight: 600;
            transition: color .2s ease;
        }
        .forgot-link:hover { color: var(--gold); text-decoration: underline; }

        .submit-btn {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #e8bc66, var(--gold-deep));
            border: none; border-radius: 12px;
            color: #171004; font-family: 'Manrope', sans-serif;
            font-size: 15px; font-weight: 800;
            cursor: pointer; position: relative; overflow: hidden;
            transition: all .3s ease; letter-spacing: .2px;
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

        .divider { display: flex; align-items: center; margin: 18px 0 14px; gap: 12px; animation: riseIn .6s cubic-bezier(.16,1,.3,1) .66s both; }
        .divider-line { flex: 1; height: 1px; background: var(--line); }
        .divider-text { font-size: 12px; color: var(--muted); font-weight: 600; }

        .register-section { text-align: center; margin-top: auto; animation: riseIn .6s cubic-bezier(.16,1,.3,1) .74s both; }
        .register-text { font-size: 13.5px; color: var(--muted); font-weight: 500; }
        .register-link { color: var(--teal); text-decoration: none; font-weight: 700; }
        .register-link:hover { text-decoration: underline; }

        /* ================= КАРТОЧКА КОМПАНИИ ================= */
        .chip {
            align-self: flex-start;
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px;
            border-radius: 100px;
            border: 1px solid rgba(226, 178, 90, 0.4);
            background: rgba(226, 178, 90, 0.08);
            color: var(--gold-soft);
            font-size: 11px; font-weight: 800;
            letter-spacing: 1.6px; text-transform: uppercase;
            margin-bottom: 18px;
            animation: riseIn .7s cubic-bezier(.16,1,.3,1) .25s both;
        }
        .chip .dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--gold);
            box-shadow: 0 0 8px var(--gold);
            animation: pulseDot 2s ease-in-out infinite;
        }
        @keyframes pulseDot {
            0%, 100% { opacity: 1; }
            50% { opacity: .4; }
        }

        .company-head { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; animation: riseIn .7s cubic-bezier(.16,1,.3,1) .32s both; }

        .company-icon {
            width: 48px; height: 48px; flex-shrink: 0;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, rgba(226, 178, 90, 0.2), rgba(226, 178, 90, 0.06));
            border: 1px solid rgba(226, 178, 90, 0.35);
        }
        .company-icon svg { width: 24px; height: 24px; color: var(--gold); }

        .company-head h2 { font-size: 18px; font-weight: 800; letter-spacing: -0.2px; line-height: 1.25; }
        .role-tag {
            display: inline-flex; align-items: center; gap: 5px;
            margin-top: 4px;
            font-size: 10.5px; font-weight: 800;
            color: var(--gold);
            text-transform: uppercase; letter-spacing: 1.1px;
        }
        .role-tag svg { width: 11px; height: 11px; }

        .company-text {
            font-size: 13px; line-height: 1.65;
            color: #a7b2c8; font-weight: 500;
            margin-bottom: 14px;
            animation: riseIn .7s cubic-bezier(.16,1,.3,1) .4s both;
        }

        .company-features {
            list-style: none; margin-bottom: 14px;
            display: grid; gap: 8px;
            animation: riseIn .7s cubic-bezier(.16,1,.3,1) .48s both;
        }
        .company-features li {
            display: flex; align-items: center; gap: 10px;
            font-size: 13px; font-weight: 600; color: #ccd5e4;
        }
        .feat-check {
            width: 18px; height: 18px; flex-shrink: 0;
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(226, 178, 90, 0.12);
            border: 1px solid rgba(226, 178, 90, 0.35);
        }
        .feat-check svg { width: 9px; height: 9px; color: var(--gold); }

        .admin-note {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 13px;
            border-radius: 11px;
            background: rgba(226, 178, 90, 0.06);
            border: 1px solid rgba(226, 178, 90, 0.2);
            font-size: 12.5px; line-height: 1.5;
            color: var(--gold-soft); font-weight: 600;
            margin-bottom: 18px;
            animation: riseIn .7s cubic-bezier(.16,1,.3,1) .56s both;
        }
        .admin-note svg { width: 16px; height: 16px; flex-shrink: 0; color: var(--gold); }

        .company-bottom { margin-top: auto; animation: riseIn .7s cubic-bezier(.16,1,.3,1) .64s both; }

        .create-company-btn {
            width: 100%; padding: 14px;
            display: flex; align-items: center; justify-content: center; gap: 9px;
            background: rgba(226, 178, 90, 0.08);
            border: 1.5px solid rgba(226, 178, 90, 0.55);
            border-radius: 12px;
            color: var(--gold-soft);
            font-family: 'Manrope', sans-serif;
            font-size: 14.5px; font-weight: 800; letter-spacing: .2px;
            text-decoration: none; cursor: pointer;
            transition: all .3s ease;
        }
        .create-company-btn:hover {
            background: rgba(226, 178, 90, 0.16);
            border-color: var(--gold);
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(226, 178, 90, 0.2);
        }
        .create-company-btn .cc-arrow { transition: transform .3s ease; }
        .create-company-btn:hover .cc-arrow { transform: translateX(4px); }

        .company-hint {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            margin-top: 11px;
            font-size: 11.5px; font-weight: 600;
            color: rgba(147, 160, 184, 0.65);
        }
        .company-hint svg { width: 12px; height: 12px; color: var(--teal); }

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
        @media (min-width: 901px) and (min-height: 780px) {
            body { overflow: hidden; }
        }

        @media (max-width: 900px) {
            .page { padding-top: 64px; }
            .cards { grid-template-columns: minmax(0, 460px); gap: 20px; }
        }

        @media (max-width: 480px) {
            .page { padding: 60px 14px 18px; }
            .card { padding: 24px 18px 20px; border-radius: 18px; }
            .logo-img { width: 48px; height: 48px; }
            .logo-title { font-size: 21px; }
            .form-options { flex-direction: column; gap: 10px; align-items: flex-start; }
            .lang-switcher { top: 12px; right: 12px; }
            .lang-select { padding: 6px 10px; font-size: 12px; }
            .footer-badges { gap: 14px; }
        }

        @media (max-height: 800px) and (min-width: 901px) {
            .page { padding-top: 58px; gap: 16px; }
            .card { padding: 24px 24px 20px; }
            .logo-section { margin-bottom: 16px; }
            .logo-img { width: 48px; height: 48px; }
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
    <div class="cards">

        {{-- ================= КАРТОЧКА 1: ВХОД ================= --}}
        <section class="card login-card">
            <div class="card-topline"></div>

            <div class="logo-section">
                <img src="{{ asset('img/dss.png') }}" alt="DocSign Logo" class="logo-img">
                <div class="logo-title">Doc<span>Sign</span></div>
                <div class="logo-subtitle" data-i18n="subtitle">Система электронного документооборота</div>
            </div>

            @if (session('status'))
            <div class="session-status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm" onsubmit="return handleLogin(event)">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email" data-i18n="emailLabel">Электронная почта</label>
                    <div class="input-wrapper">
                        <input type="email" name="email" id="email"
                               class="form-input @error('email') error @enderror"
                               value="{{ old('email') }}"
                               placeholder="name@company.com"
                               required autofocus autocomplete="username">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                        </svg>
                    </div>
                    @error('email')<div class="error-message">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password" data-i18n="passwordLabel">Пароль</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password"
                               class="form-input @error('password') error @enderror"
                               placeholder="••••••••••"
                               required autocomplete="current-password">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Toggle password visibility">
                            <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')<div class="error-message">{{ $message }}</div>@enderror
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember_me" class="remember-input">
                        <div class="custom-checkbox">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                        <span data-i18n="remember">Запомнить меня</span>
                    </label>
                    @if (Route::has('password.request'))
                    <a class="forgot-link" href="{{ route('password.request') }}" data-i18n="forgot">Забыли пароль?</a>
                    @endif
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <span class="btn-text">
                        <span data-i18n="loginBtn">Войти в систему</span>
                        <svg class="btn-arrow" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                        </svg>
                    </span>
                </button>
            </form>

            <div class="divider">
                <div class="divider-line"></div>
                <span class="divider-text" data-i18n="divider">или</span>
                <div class="divider-line"></div>
            </div>

            <div class="register-section">
                <p class="register-text">
                    <span data-i18n="noAccount">Нет аккаунта?</span>
                    <a href="{{ route('register') }}" class="register-link" data-i18n="register">Зарегистрироваться</a>
                </p>
            </div>
        </section>

        {{-- ================= КАРТОЧКА 2: СОЗДАТЬ КОМПАНИЮ ================= --}}
        <section class="card company-card">
            <div class="card-topline"></div>

            <span class="chip"><span class="dot"></span><span data-i18n="chip">Для бизнеса</span></span>

            <div class="company-head">
                <div class="company-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18"/>
                        <path d="M5 21V7l8-4v18"/>
                        <path d="M19 21V11l-6-4"/>
                        <path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/>
                    </svg>
                </div>
                <div>
                    <h2 data-i18n="companyTitle">Создайте свою компанию</h2>
                    <span class="role-tag">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        <span data-i18n="roleTag">Роль: Администратор</span>
                    </span>
                </div>
            </div>

            <p class="company-text" data-i18n="companyText">Если у вас есть своя компания — создайте её и получите собственное рабочее пространство для документооборота.</p>

            <ul class="company-features">
                <li>
                    <span class="feat-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                    <span data-i18n="feat1">Вы автоматически станете администратором</span>
                </li>
                <li>
                    <span class="feat-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                    <span data-i18n="feat2">Приглашайте сотрудников и настраивайте права</span>
                </li>
                <li>
                    <span class="feat-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                    <span data-i18n="feat3">Юридически значимая электронная подпись</span>
                </li>
            </ul>

            <div class="admin-note">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
                <span data-i18n="adminNote">После создания компании вы получаете роль «Администратор» с полным доступом.</span>
            </div>

            <div class="company-bottom">
                <a href="{{ route('register.company') }}" class="create-company-btn">
                    <span data-i18n="companyBtn">Создать компанию</span>
                    <svg class="cc-arrow" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                    </svg>
                </a>
                <div class="company-hint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    <span data-i18n="companyHint">Бесплатно • Занимает меньше минуты</span>
                </div>
            </div>
        </section>
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
            subtitle: 'Система электронного документооборота',
            emailLabel: 'Электронная почта',
            passwordLabel: 'Пароль',
            remember: 'Запомнить меня',
            forgot: 'Забыли пароль?',
            loginBtn: 'Войти в систему',
            divider: 'или',
            noAccount: 'Нет аккаунта?',
            register: 'Зарегистрироваться',
            badgeSecurity: 'Защита',
            badgeSign: 'ЭЦП',
            rights: 'Все права защищены.',
            invalidEmail: 'Неверный формат email',
            emptyFields: 'Заполните все поля',
            chip: 'Для бизнеса',
            companyTitle: 'Создайте свою компанию',
            roleTag: 'Роль: Администратор',
            companyText: 'Если у вас есть своя компания — создайте её и получите собственное рабочее пространство для документооборота.',
            feat1: 'Вы автоматически станете администратором',
            feat2: 'Приглашайте сотрудников и настраивайте права',
            feat3: 'Юридически значимая электронная подпись',
            adminNote: 'После создания компании вы получаете роль «Администратор» с полным доступом.',
            companyBtn: 'Создать компанию',
            companyHint: 'Бесплатно • Занимает меньше минуты'
        },
        tj: {
            subtitle: 'Системаи ҳуҷҷатгардонии электронӣ',
            emailLabel: 'Почтаи электронӣ',
            passwordLabel: 'Рамз',
            remember: 'Маро дар ёд дор',
            forgot: 'Рамзро фаромӯш кардед?',
            loginBtn: 'Ворид шудан',
            divider: 'ё',
            noAccount: 'Ҳисоб надоред?',
            register: 'Бақайдгирӣ',
            badgeSecurity: 'Ҳифз',
            badgeSign: 'ЭИИ',
            rights: 'Ҳамаи ҳуқуқҳо ҳифз шудаанд.',
            invalidEmail: 'Формати email нодуруст',
            emptyFields: 'Ҳамаи майдонҳоро пур кунед',
            chip: 'Барои бизнес',
            companyTitle: 'Ширкати худро созед',
            roleTag: 'Нақш: Администратор',
            companyText: 'Агар шумо ширкати худро дошта бошед — онро созед ва фазои кории худро барои ҳуҷҷатгардонӣ ба даст оред.',
            feat1: 'Шумо ба таври худкор администратор мешавед',
            feat2: 'Кормандонро даъват кунед ва ҳуқуқҳоро танзим намоед',
            feat3: 'Имзои электронии дорои қувваи ҳуқуқӣ',
            adminNote: 'Пас аз таъсиси ширкат шумо нақши «Администратор» бо дастрасии пурра мегиред.',
            companyBtn: 'Ширкат сохтан',
            companyHint: 'Ройгон • Камтар аз 1 дақиқа'
        },
        en: {
            subtitle: 'Electronic Document Management System',
            emailLabel: 'Email Address',
            passwordLabel: 'Password',
            remember: 'Remember me',
            forgot: 'Forgot password?',
            loginBtn: 'Sign In',
            divider: 'or',
            noAccount: "Don't have an account?",
            register: 'Sign Up',
            badgeSecurity: 'Security',
            badgeSign: 'EDS',
            rights: 'All rights reserved.',
            invalidEmail: 'Invalid email format',
            emptyFields: 'Please fill in all fields',
            chip: 'For Business',
            companyTitle: 'Create your company',
            roleTag: 'Role: Administrator',
            companyText: 'If you own a company — create it and get your own workspace for document management.',
            feat1: 'You automatically become the administrator',
            feat2: 'Invite employees and configure their permissions',
            feat3: 'Legally binding electronic signature',
            adminNote: 'After creating the company you receive the "Administrator" role with full access.',
            companyBtn: 'Create Company',
            companyHint: 'Free • Takes less than a minute'
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
    }

    /* ============ Пароль ============ */
    var passwordVisible = false;
    function togglePassword() {
        passwordVisible = !passwordVisible;
        var input = document.getElementById('password');
        var icon = document.getElementById('eyeIcon');
        if (passwordVisible) {
            input.type = 'text';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/><line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
        }
    }

    /* ============ Уведомления ============ */
    function showNotification(message, type) {
        var notif = document.getElementById('notification');
        notif.textContent = message;
        notif.className = 'notification ' + type + ' show';
        setTimeout(function() { notif.classList.remove('show'); }, 3000);
    }

    /* ============ Логин ============ */
    function handleLogin(e) {
        var email = document.getElementById('email');
        var password = document.getElementById('password');
        var t = translations[currentLang];
        var hasError = false;
        email.classList.remove('error');
        password.classList.remove('error');
        if (!email.value.trim()) {
            email.classList.add('error');
            showNotification(t.emptyFields, 'error');
            hasError = true;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            email.classList.add('error');
            showNotification(t.invalidEmail, 'error');
            hasError = true;
        }
        if (!password.value.trim()) {
            password.classList.add('error');
            hasError = true;
        }
        if (hasError) return false;
        document.getElementById('submitBtn').classList.add('loading');
        return true;
    }

    var emailInput = document.getElementById('email');
    var passInput = document.getElementById('password');
    if (emailInput) emailInput.addEventListener('input', function() { this.classList.remove('error'); });
    if (passInput) passInput.addEventListener('input', function() { this.classList.remove('error'); });

    document.addEventListener('DOMContentLoaded', function() {
        var errorMessages = document.querySelectorAll('.error-message');
        if (errorMessages.length > 0 && errorMessages[0].textContent.trim()) {
            showNotification(errorMessages[0].textContent.trim(), 'error');
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="/js/faceid-warmup.js"></script>
</body>
</html>
