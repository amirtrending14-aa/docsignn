<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DocSign — Регистрация</title>

    {{-- ✅ ДОБАВЛЕНО: Favicon DocSign --}}
    <link rel="icon" type="image/png" href="{{ asset('img/dss.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/dss.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #818cf8;
             --accent: #06b6d4;
            --bg-dark: #0f172a;
            --bg-card: rgba(15, 23, 42, 0.6);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border: rgba(148, 163, 184, 0.15);
            --glow: rgba(79, 70, 229, 0.4);
        }

        body {
            font-family: 'Figtree', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-dark);
            overflow: hidden;
            position: relative;
        }

        .bg-animation {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(79, 70, 229, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(6, 182, 212, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 80%, rgba(139, 92, 246, 0.1) 0%, transparent 50%);
            animation: bgShift 15s ease-in-out infinite alternate;
        }

        @keyframes bgShift {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-5%, -5%) rotate(3deg); }
        }

        .particles {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: rgba(129, 140, 248, 0.5);
            border-radius: 50%;
            animation: float linear infinite;
        }

        @keyframes float {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }

        .grid-overlay {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-image:
                linear-gradient(rgba(148, 163, 184, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            animation: gridMove 20s linear infinite;
        }

        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(60px, 60px); }
        }

        .container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 500px;
            padding: 20px;
            max-height: 100vh;
            overflow-y: auto;
        }

        .container::-webkit-scrollbar {
            width: 4px;
        }
        .container::-webkit-scrollbar-track {
            background: transparent;
        }
        .container::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        .lang-switcher {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
            display: flex;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 4px;
            gap: 2px;
        }

        .lang-btn {
            padding: 8px 14px;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            font-family: 'Figtree', sans-serif;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
        }

        .lang-btn:hover {
            color: var(--text-primary);
            background: rgba(79, 70, 229, 0.15);
        }

        .lang-btn.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 2px 10px var(--glow);
        }

        .register-card {
            background: var(--bg-card);
            backdrop-filter: blur(40px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 44px 40px;
            position: relative;
            overflow: hidden;
            animation: cardAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
            box-shadow:
                0 0 0 1px rgba(148, 163, 184, 0.05),
                0 25px 80px rgba(0, 0, 0, 0.4),
                0 0 120px rgba(79, 70, 229, 0.08);
        }

        @keyframes cardAppear {
            0% {
                opacity: 0;
                transform: translateY(30px) scale(0.96);
                filter: blur(10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
                filter: blur(0);
            }
        }

        .register-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--primary-light), var(--primary));
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .register-card::after {
            content: '';
            position: absolute;
            top: -1px; left: -1px; right: -1px; bottom: -1px;
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.2), transparent 40%, transparent 60%, rgba(79, 70, 229, 0.1));
            z-index: -1;
            pointer-events: none;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 32px;
            animation: logoAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both;
        }

        @keyframes logoAppear {
            0% { opacity: 0; transform: translateY(15px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .logo-img {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            margin: 0 auto 14px;
            display: block;
            box-shadow: 0 8px 30px rgba(6, 182, 212, 0.25);
            transition: transform 0.3s ease;
        }

        .logo-img:hover {
            transform: scale(1.05) rotate(2deg);
        }

        .logo-title {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .logo-title span {
            background: linear-gradient(135deg, var(--accent), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo-subtitle {
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .form-group {
            margin-bottom: 18px;
            animation: formAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .form-group:nth-child(1) { animation-delay: 0.3s; }
        .form-group:nth-child(2) { animation-delay: 0.4s; }
        .form-group:nth-child(3) { animation-delay: 0.5s; }

        @keyframes formAppear {
            0% { opacity: 0; transform: translateX(-15px); }
            100% { opacity: 1; transform: translateX(0); }
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            color: var(--text-secondary);
            transition: color 0.3s ease;
            pointer-events: none;
            z-index: 2;
        }

        .form-input {
            width: 100%;
            padding: 13px 16px 13px 48px;
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid var(--border);
            border-radius: 14px;
            color: var(--text-primary);
            font-family: 'Figtree', sans-serif;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input::placeholder {
            color: rgba(148, 163, 184, 0.4);
        }

        .form-input:focus {
            border-color: var(--accent);
            background: rgba(30, 41, 59, 0.8);
            box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.15), 0 0 20px rgba(6, 182, 212, 0.1);
        }

        .form-input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 30px rgba(30, 41, 59, 0.9) inset !important;
            -webkit-text-fill-color: var(--text-primary) !important;
            caret-color: var(--text-primary);
        }

        .form-input.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
        }

        .error-message {
            font-size: 12px;
            color: #ef4444;
            margin-top: 6px;
            font-weight: 500;
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 4px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        .toggle-password:hover {
            color: var(--text-primary);
            background: rgba(148, 163, 184, 0.1);
        }

        .toggle-password svg {
            width: 18px;
            height: 18px;
        }

        /* Password strength indicator */
        .password-strength {
            margin-top: 8px;
            display: flex;
            gap: 4px;
        }

        .strength-bar {
            flex: 1;
            height: 3px;
            border-radius: 3px;
            background: rgba(148, 163, 184, 0.15);
            transition: all 0.3s ease;
        }

        .strength-bar.weak { background: #ef4444; }
        .strength-bar.medium { background: #f59e0b; }
        .strength-bar.strong { background: #10b981; }

        .strength-text {
            font-size: 11px;
            font-weight: 500;
            margin-top: 4px;
            color: var(--text-secondary);
            transition: color 0.3s ease;
        }

        .strength-text.weak { color: #ef4444; }
        .strength-text.medium { color: #f59e0b; }
        .strength-text.strong { color: #10b981; }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--accent), var(--primary));
            border: none;
            border-radius: 14px;
            color: white;
            font-family: 'Figtree', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            letter-spacing: 0.3px;
            animation: formAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.6s both;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(6, 182, 212, 0.3), 0 0 40px rgba(79, 70, 229, 0.15);
        }

        .submit-btn:hover::before {
            opacity: 1;
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn .btn-text {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .submit-btn.loading .btn-text {
            opacity: 0;
        }

        .submit-btn.loading::after {
            content: '';
            position: absolute;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            top: 50%;
            left: 50%;
            margin: -12px 0 0 -12px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .login-link-section {
            text-align: center;
            margin-top: 24px;
            animation: formAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.7s both;
        }

        .login-text {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .login-link {
            color: var(--accent);
            text-decoration: none;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .login-link:hover {
            color: var(--primary-light);
            text-shadow: 0 0 20px rgba(129, 140, 248, 0.3);
        }

        .footer-badges {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-top: 28px;
            animation: footerAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.8s both;
        }

        @keyframes footerAppear {
            0% { opacity: 0; transform: translateY(10px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .badge {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
            opacity: 0.7;
        }

        .badge svg {
            width: 14px;
            height: 14px;
            color: var(--accent);
        }

        .copyright {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: rgba(148, 163, 184, 0.4);
            font-weight: 500;
            animation: footerAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.9s both;
        }

        .notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            z-index: 1000;
            transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            backdrop-filter: blur(20px);
        }

        .notification.show {
            transform: translateX(-50%) translateY(0);
        }

        .notification.success {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
        }

        .notification.error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        @media (max-width: 520px) {
            .container { padding: 16px; }
            .register-card { padding: 32px 20px; border-radius: 20px; }
            .logo-title { font-size: 22px; }
            .lang-switcher { top: 12px; right: 12px; }
            .lang-btn { padding: 6px 10px; font-size: 12px; }
        }
    </style>
</head>
<body>
<!-- Background effects -->
<div class="bg-animation"></div>
<div class="grid-overlay"></div>
<div class="particles" id="particles"></div>

<!-- Notification toast -->
<div class="notification" id="notification"></div>

<!-- Language switcher -->
<div class="lang-switcher">
    <select class="lang-select" onchange="switchLang(this.value)">
        <option value="ru" selected>🇷🇺 RU</option>
        <option value="tj">🇹🇯 TJ</option>
        <option value="en">🇬🇧 EN</option>
    </select>
</div>


<style>
    .lang-switcher {
        display: inline-block;
        margin: 10px;
        font-family: 'Segoe UI', Roboto, sans-serif;
    }

    .lang-select {
        /* Основной стиль: темно-синий градиент */
        background: linear-gradient(145deg, #1a2a6c, #101a44);
        color: #ffffff;
        padding: 10px 15px;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        outline: none;
        appearance: none; /* Убираем стандартную стрелку браузера */
        -webkit-appearance: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    /* Эффект при наведении */
    .lang-select:hover {
        background: #1e3a8a;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        transform: translateY(-1px);
    }

    /* Фокус (когда нажали) */
    .lang-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.4);
    }

    /* Стили для выпадающего списка (в некоторых браузерах) */
    .lang-select option {
        background-color: #101a44;
        color: white;
        padding: 10px;
    }
</style>

<div class="container">
    <div class="register-card">
        <!-- Logo -->
        <div class="logo-section">
            <img src="{{ asset('img/dss.png') }}" alt="DocSign Logo" class="logo-img">
            <div class="logo-title">Doc<span>Sign</span></div>
            <div class="logo-subtitle" data-i18n="subtitle">Создайте аккаунт</div>
        </div>

        <!-- Laravel Register Form -->
        <form method="POST" action="{{ route('register') }}" id="registerForm" onsubmit="return handleRegister(event)">
            @csrf

            <!-- Name -->
            <div class="form-group">
                <label class="form-label" for="name" data-i18n="nameLabel">Полное имя</label>
                <div class="input-wrapper">
                    <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-input @error('name') error @enderror"
                            value="{{ old('name') }}"
                            data-i18n-placeholder="namePlaceholder"
                            placeholder="Иван Иванов"
                            required
                            autofocus
                            autocomplete="name"
                    >
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                @error('name')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="form-group">
                <label class="form-label" for="email" data-i18n="emailLabel">Электронная почта</label>
                <div class="input-wrapper">
                    <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-input @error('email') error @enderror"
                            value="{{ old('email') }}"
                            data-i18n-placeholder="emailPlaceholder"
                            placeholder="name@company.com"
                            required
                            autocomplete="username"
                    >
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                    </svg>
                </div>
                @error('email')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label" for="password" data-i18n="passwordLabel">Пароль</label>
                <div class="input-wrapper">
                    <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-input @error('password') error @enderror"
                            data-i18n-placeholder="passwordPlaceholder"
                            placeholder="••••••••••"
                            required
                            autocomplete="new-password"
                            oninput="checkPasswordStrength(this.value)"
                    >
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
                @error('password')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label class="form-label" for="password_confirmation" data-i18n="confirmLabel">Подтвердите пароль</label>
                <div class="input-wrapper">
                    <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="form-input @error('password_confirmation') error @enderror"
                            data-i18n-placeholder="confirmPlaceholder"
                            placeholder="••••••••••"
                            required
                            autocomplete="new-password"
                    >
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
                @error('password_confirmation')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Submit -->
            <button type="submit" class="submit-btn" id="submitBtn">
                    <span class="btn-text">
                        <span data-i18n="registerBtn">Зарегистрироваться</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/>
                            <path d="m12 5 7 7-7 7"/>
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

    <!-- Footer badges -->
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

    <p class="copyright">
        © {{ date('Y') }} DocSign Ecosystem. <span data-i18n="rights">Все права защищены.</span>
    </p>
</div>

<script>
    // Generate particles
    function createParticles() {
        var container = document.getElementById('particles');
        var count = 30;
        for (var i = 0; i < count; i++) {
            var particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDuration = (Math.random() * 10 + 8) + 's';
            particle.style.animationDelay = (Math.random() * 10) + 's';
            particle.style.width = (Math.random() * 3 + 1) + 'px';
            particle.style.height = particle.style.width;
            particle.style.opacity = Math.random() * 0.5 + 0.1;
            container.appendChild(particle);
        }
    }
    createParticles();

    // Translations
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
            weak: 'Слабый',
            medium: 'Средний',
            strong: 'Сильный',
            veryStrong: 'Очень сильный',
            passwordMismatch: 'Пароли не совпадают',
            registering: 'Регистрация...'
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
            weak: 'Заиф',
            medium: 'Миёна',
            strong: 'Қавӣ',
            veryStrong: 'Хеле қавӣ',
            passwordMismatch: 'Рамзҳо мувофиқ нестанд',
            registering: 'Бақайдгирӣ...'
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
            weak: 'Weak',
            medium: 'Medium',
            strong: 'Strong',
            veryStrong: 'Very Strong',
            passwordMismatch: 'Passwords do not match',
            registering: 'Registering...'
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

        // Re-check password strength text
        var passVal = document.getElementById('password').value;
        if (passVal) checkPasswordStrength(passVal);
    }

    // Toggle password visibility
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

    // Password strength checker
    function checkPasswordStrength(password) {
        var bars = [
            document.getElementById('bar1'),
            document.getElementById('bar2'),
            document.getElementById('bar3'),
            document.getElementById('bar4')
        ];
        var textEl = document.getElementById('strengthText');
        var t = translations[currentLang];

        // Reset
        bars.forEach(function(bar) {
            bar.className = 'strength-bar';
        });
        textEl.className = 'strength-text';
        textEl.textContent = '';

        if (!password) return;

        var score = 0;
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[^a-zA-Z0-9]/.test(password)) score++;

        var level, label;
        if (score <= 1) {
            level = 'weak';
            label = t.weak;
        } else if (score === 2) {
            level = 'medium';
            label = t.medium;
        } else if (score === 3) {
            level = 'strong';
            label = t.strong;
        } else {
            level = 'strong';
            label = t.veryStrong;
        }

        for (var i = 0; i < score; i++) {
            bars[i].className = 'strength-bar ' + level;
        }
        textEl.className = 'strength-text ' + level;
        textEl.textContent = label;
    }

    // Show notification
    function showNotification(message, type) {
        var notif = document.getElementById('notification');
        notif.textContent = message;
        notif.className = 'notification ' + type + ' show';
        setTimeout(function() {
            notif.classList.remove('show');
        }, 3000);
    }

    // Handle register - ИСПРАВЛЕННАЯ ВЕРСИЯ
    function handleRegister(e) {
        var password = document.getElementById('password');
        var confirm = document.getElementById('password_confirmation');
        var t = translations[currentLang];

        // Убираем предыдущие ошибки
        password.classList.remove('error');
        confirm.classList.remove('error');

        // Проверка совпадения паролей
        if (password.value !== confirm.value) {
            confirm.classList.add('error');
            showNotification(t.passwordMismatch, 'error');
            return false; // Блокируем отправку формы
        }

        // Проверяем минимальную длину пароля
        if (password.value.length < 8) {
            password.classList.add('error');
            showNotification(currentLang === 'ru' ? 'Пароль должен быть не менее 8 символов' :
                           (currentLang === 'tj' ? 'Рамз бояд камаш 8 аломат дошта бошад' :
                           'Password must be at least 8 characters'), 'error');
            return false;
        }

        // Добавляем индикатор загрузки, но НЕ блокируем форму
        var btn = document.getElementById('submitBtn');
        btn.classList.add('loading');

        // ВАЖНО: НЕ делаем btn.disabled = true, это блокирует отправку!

        return true; // Разрешаем отправку формы
    }

    // Clear errors on input
    document.querySelectorAll('.form-input').forEach(function(input) {
        input.addEventListener('input', function() {
            this.classList.remove('error');
            // Убираем индикатор загрузки при изменении
            var btn = document.getElementById('submitBtn');
            btn.classList.remove('loading');
        });
    });

    // Обработка ошибок валидации Laravel (если есть)
    document.addEventListener('DOMContentLoaded', function() {
        // Проверяем, есть ли ошибки валидации от Laravel
        var errorMessages = document.querySelectorAll('.error-message');
        if (errorMessages.length > 0) {
            // Показываем первую ошибку как уведомление
            var firstError = errorMessages[0].textContent;
            if (firstError) {
                showNotification(firstError, 'error');
            }
        }
    });
</script>
</body>
</html>