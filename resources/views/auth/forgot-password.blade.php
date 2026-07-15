<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DocSign — Восстановление пароля</title>

    <!-- ✅ Значок сайта (Favicon) DocSign -->
    <link rel="icon" type="image/png" href="{{ asset('img/dss.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/dss.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #818cf8;
            --accent: #06b6d4;
            --warning: #f59e0b;
            --bg-dark: #0f172a;
            --bg-card: rgba(15, 23, 42, 0.6);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border: rgba(148, 163, 184, 0.15);
            --glow: rgba(79, 70, 229, 0.4);
        }

        html, body {
            font-family: 'Figtree', sans-serif;
            min-height: 100vh;
            background: var(--bg-dark);
            color: var(--text-primary);
            overflow-x: hidden;
            position: relative;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        /* ===== ФОН ===== */
        .bg-animation {
            position: fixed; inset: 0; z-index: 0; overflow: hidden; pointer-events: none;
        }
        .bg-animation::before {
            content: ''; position: absolute; top: -50%; left: -50%;
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

        .particles { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
        .particle {
            position: absolute; width: 3px; height: 3px;
            background: rgba(129, 140, 248, 0.5); border-radius: 50%;
            animation: float linear infinite;
        }
        @keyframes float {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 1; } 90% { opacity: 1; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }

        .grid-overlay {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
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

        /* ===== КОНТЕЙНЕР ===== */
        .container {
            position: relative; z-index: 10;
            width: 100%; max-width: 480px;
            display: flex; flex-direction: column; align-items: center;
        }

        /* ===== ПЕРЕКЛЮЧАТЕЛЬ ЯЗЫКА ===== */
        .lang-switcher {
            position: fixed; top: 20px; right: 20px; z-index: 100;
        }
        .lang-switcher select {
            appearance: none; -webkit-appearance: none; -moz-appearance: none;
            background: rgba(15, 23, 42, 0.7) url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") no-repeat right 12px center / 14px;
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border); border-radius: 12px;
            padding: 10px 36px 10px 16px;
            color: var(--text-primary);
            font-family: 'Figtree', sans-serif;
            font-size: 13px; font-weight: 600;
            cursor: pointer; outline: none;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            min-width: 110px;
        }
        .lang-switcher select:hover { border-color: var(--primary-light); }
        .lang-switcher select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.15); }
        .lang-switcher option { background: #0f172a; color: var(--text-primary); }

        /* ===== КАРТОЧКА ===== */
        .reset-card {
            width: 100%;
            background: var(--bg-card); backdrop-filter: blur(40px);
            border: 1px solid var(--border); border-radius: 24px;
            padding: 48px 40px; position: relative; overflow: hidden;
            animation: cardAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
            box-shadow:
                0 0 0 1px rgba(148, 163, 184, 0.05),
                0 25px 80px rgba(0, 0, 0, 0.4),
                0 0 120px rgba(79, 70, 229, 0.08);
        }
        @keyframes cardAppear {
            0% { opacity: 0; transform: translateY(30px) scale(0.96); filter: blur(10px); }
            100% { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
        }
        .reset-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--warning), var(--primary), var(--accent));
            background-size: 200% 100%; animation: shimmer 3s ease-in-out infinite;
        }
        @keyframes shimmer {
            0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; }
        }

        /* ===== ЛОГОТИП ===== */
        .logo-section { text-align: center; margin-bottom: 32px; }
        .logo-img {
            width: 80px; height: 80px; border-radius: 20px;
            margin: 0 auto 16px; display: block;
            box-shadow: 0 8px 30px rgba(245, 158, 11, 0.25);
            transition: transform 0.3s ease;
        }
        .logo-img:hover { transform: scale(1.05) rotate(-2deg); }
        .logo-title {
            font-size: 28px; font-weight: 800;
            color: var(--text-primary); letter-spacing: -0.5px;
            margin-bottom: 4px; line-height: 1.2;
        }
        .logo-title span {
            background: linear-gradient(135deg, var(--warning), var(--accent));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .logo-subtitle {
            font-size: 12px; font-weight: 500;
            color: var(--text-secondary);
            text-transform: uppercase; letter-spacing: 2px;
        }

        /* ===== ИНФО-БАННЕР ===== */
        .info-banner {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 14px 16px;
            background: rgba(245, 158, 11, 0.08);
            border: 1px solid rgba(245, 158, 11, 0.15);
            border-radius: 12px; margin-bottom: 24px;
            text-align: left;
        }
        .info-banner svg {
            width: 18px; height: 18px; color: var(--warning);
            flex-shrink: 0; margin-top: 1px;
        }
        .info-banner p {
            font-size: 13px; color: var(--text-secondary);
            line-height: 1.6; margin: 0;
        }

        /* ===== СТАТУС ===== */
        .status-banner {
            padding: 14px 16px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #34d399; border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px; line-height: 1.5;
            text-align: center;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .status-banner svg {
            width: 16px; height: 16px; flex-shrink: 0;
        }

        /* ===== ФОРМА ===== */
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-label {
            display: block;
            font-size: 13px; font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
            letter-spacing: 0.2px;
        }
        .input-wrapper { position: relative; }
        .input-icon {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            width: 20px; height: 20px; color: var(--text-secondary); z-index: 2;
            pointer-events: none;
        }
        .form-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid var(--border); border-radius: 14px;
            color: var(--text-primary);
            font-family: 'Figtree', sans-serif; font-size: 15px;
            transition: all 0.3s ease; outline: none;
            letter-spacing: 0.2px;
        }
        .form-input::placeholder {
            color: rgba(148, 163, 184, 0.5);
        }
        .form-input:focus {
            border-color: var(--accent);
            background: rgba(30, 41, 59, 0.8);
            box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.15);
        }
        .form-input.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }
        .error-message {
            color: #ef4444; font-size: 12px;
            margin-top: 6px; line-height: 1.4;
            display: flex; align-items: center; gap: 4px;
        }

        /* ===== КНОПКА ===== */
        .submit-btn {
            width: 100%; padding: 16px;
            background: linear-gradient(135deg, var(--warning), var(--primary));
            border: none; border-radius: 14px;
            color: white;
            font-family: 'Figtree', sans-serif;
            font-size: 15px; font-weight: 700;
            cursor: pointer; position: relative; overflow: hidden;
            transition: all 0.3s ease;
            letter-spacing: 0.3px;
            margin-top: 8px;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);
        }
        .submit-btn:active { transform: translateY(0); }
        .submit-btn.loading .btn-text { opacity: 0; }
        .submit-btn.loading::after {
            content: ''; position: absolute; width: 24px; height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.3); border-top-color: white;
            border-radius: 50%; animation: spin 0.8s linear infinite;
            top: 50%; left: 50%; margin: -12px 0 0 -12px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ===== ССЫЛКА НАЗАД ===== */
        .back-link-section {
            text-align: center; margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }
        .back-link {
            font-size: 14px; color: var(--text-secondary);
            text-decoration: none;
            display: inline-flex; align-items: center; gap: 6px;
            transition: all 0.2s ease;
            padding: 6px 12px; border-radius: 8px;
        }
        .back-link:hover {
            color: var(--accent);
            background: rgba(6, 182, 212, 0.05);
        }

        /* ===== БЕЙДЖИ ===== */
        .footer-badges {
            display: flex; justify-content: center; gap: 24px;
            margin-top: 32px; opacity: 0.7;
            flex-wrap: wrap;
        }
        .badge {
            display: flex; align-items: center; gap: 6px;
            font-size: 11px; font-weight: 600;
            color: var(--text-secondary);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .badge svg { width: 14px; height: 14px; color: var(--warning); }

        .copyright {
            text-align: center; margin-top: 20px;
            font-size: 12px;
            color: rgba(148, 163, 184, 0.5);
            line-height: 1.5;
        }

        /* ===== УВЕДОМЛЕНИЕ ===== */
        .notification {
            position: fixed; top: 20px; left: 50%;
            transform: translateX(-50%) translateY(-100px);
            padding: 14px 24px; border-radius: 12px;
            font-size: 14px; z-index: 1000;
            transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            backdrop-filter: blur(20px);
            max-width: 90%;
            text-align: center;
        }
        .notification.show { transform: translateX(-50%) translateY(0); }
        .notification.error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }
        .notification.success {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
        }

        /* ===== RESPONSIVE ===== */

        /* Планшеты и маленькие ноутбуки (до 768px) */
        @media (max-width: 768px) {
            body { padding: 30px 16px; }
            .lang-switcher { top: 16px; right: 16px; }
            .lang-switcher select {
                padding: 9px 32px 9px 14px;
                font-size: 12px;
                min-width: 100px;
                border-radius: 10px;
            }
            .reset-card { padding: 40px 32px; border-radius: 22px; }
            .logo-section { margin-bottom: 28px; }
            .logo-img { width: 72px; height: 72px; border-radius: 18px; margin-bottom: 14px; }
            .logo-title { font-size: 26px; }
            .logo-subtitle { font-size: 11px; letter-spacing: 1.8px; }
            .info-banner { padding: 13px 15px; font-size: 12px; }
            .info-banner svg { width: 16px; height: 16px; }
            .status-banner { padding: 13px 15px; font-size: 12px; }
            .form-group { margin-bottom: 18px; }
            .form-label { font-size: 12px; margin-bottom: 7px; }
            .form-input { padding: 13px 15px 13px 44px; font-size: 14px; border-radius: 12px; }
            .input-icon { left: 15px; width: 18px; height: 18px; }
            .submit-btn { padding: 15px; font-size: 14px; border-radius: 12px; }
            .back-link-section { margin-top: 20px; padding-top: 20px; }
            .back-link { font-size: 13px; }
            .footer-badges { gap: 20px; margin-top: 28px; }
            .badge { font-size: 10px; gap: 5px; }
            .badge svg { width: 13px; height: 13px; }
            .copyright { margin-top: 18px; font-size: 11px; }
            .notification { padding: 12px 20px; font-size: 13px; border-radius: 10px; }
        }

        /* Большие телефоны (до 520px) */
        @media (max-width: 520px) {
            body { padding: 20px 16px; }
            .lang-switcher { top: 12px; right: 12px; }
            .lang-switcher select {
                padding: 8px 28px 8px 12px;
                font-size: 11px;
                min-width: 90px;
                border-radius: 9px;
                background-position: right 10px center;
                background-size: 12px;
            }
            .reset-card { padding: 36px 24px; border-radius: 20px; }
            .logo-section { margin-bottom: 24px; }
            .logo-img { width: 64px; height: 64px; border-radius: 16px; margin-bottom: 12px; }
            .logo-title { font-size: 24px; }
            .logo-subtitle { font-size: 10px; letter-spacing: 1.6px; }
            .info-banner { padding: 12px 14px; gap: 10px; margin-bottom: 20px; border-radius: 10px; }
            .info-banner svg { width: 16px; height: 16px; }
            .info-banner p { font-size: 12px; }
            .status-banner { padding: 12px 14px; font-size: 12px; border-radius: 10px; }
            .status-banner svg { width: 14px; height: 14px; }
            .form-group { margin-bottom: 16px; }
            .form-label { font-size: 12px; margin-bottom: 6px; }
            .form-input { padding: 12px 14px 12px 42px; font-size: 14px; border-radius: 11px; }
            .input-icon { left: 14px; width: 18px; height: 18px; }
            .error-message { font-size: 11px; margin-top: 5px; }
            .error-message svg { width: 11px; height: 11px; }
            .submit-btn { padding: 14px; font-size: 14px; border-radius: 11px; margin-top: 6px; }
            .submit-btn.loading::after { width: 22px; height: 22px; margin: -11px 0 0 -11px; }
            .back-link-section { margin-top: 18px; padding-top: 18px; }
            .back-link { font-size: 13px; padding: 5px 10px; border-radius: 7px; }
            .back-link svg { width: 14px; height: 14px; }
            .footer-badges { gap: 16px; margin-top: 24px; }
            .badge { font-size: 10px; gap: 5px; }
            .badge svg { width: 12px; height: 12px; }
            .copyright { margin-top: 16px; font-size: 11px; }
            .notification { padding: 11px 18px; font-size: 12px; border-radius: 9px; top: 16px; }
        }

        /* Телефоны (до 480px) */
        @media (max-width: 480px) {
            body { padding: 16px 12px; }
            .lang-switcher { top: 10px; right: 10px; }
            .lang-switcher select {
                padding: 7px 26px 7px 10px;
                font-size: 10px;
                min-width: 85px;
                border-radius: 8px;
                letter-spacing: 0.3px;
            }
            .reset-card { padding: 32px 20px; border-radius: 18px; }
            .logo-section { margin-bottom: 22px; }
            .logo-img { width: 60px; height: 60px; border-radius: 15px; margin-bottom: 10px; }
            .logo-title { font-size: 22px; }
            .logo-subtitle { font-size: 10px; letter-spacing: 1.4px; }
            .info-banner { padding: 11px 13px; gap: 9px; margin-bottom: 18px; border-radius: 9px; }
            .info-banner svg { width: 15px; height: 15px; }
            .info-banner p { font-size: 11px; line-height: 1.5; }
            .status-banner { padding: 11px 13px; font-size: 11px; border-radius: 9px; gap: 6px; }
            .status-banner svg { width: 13px; height: 13px; }
            .form-group { margin-bottom: 14px; }
            .form-label { font-size: 11px; margin-bottom: 5px; }
            .form-input { padding: 11px 13px 11px 40px; font-size: 13px; border-radius: 10px; }
            .input-icon { left: 13px; width: 16px; height: 16px; }
            .error-message { font-size: 10px; margin-top: 4px; gap: 3px; }
            .error-message svg { width: 10px; height: 10px; }
            .submit-btn { padding: 13px; font-size: 13px; border-radius: 10px; margin-top: 5px; letter-spacing: 0.2px; }
            .submit-btn.loading::after { width: 20px; height: 20px; margin: -10px 0 0 -10px; border-width: 2px; }
            .back-link-section { margin-top: 16px; padding-top: 16px; }
            .back-link { font-size: 12px; padding: 5px 9px; gap: 5px; }
            .back-link svg { width: 13px; height: 13px; }
            .footer-badges { gap: 14px; margin-top: 20px; }
            .badge { font-size: 9px; gap: 4px; letter-spacing: 0.3px; }
            .badge svg { width: 11px; height: 11px; }
            .copyright { margin-top: 14px; font-size: 10px; }
            .notification { padding: 10px 16px; font-size: 11px; border-radius: 8px; top: 12px; max-width: 92%; }
        }

        /* Очень маленькие телефоны (до 380px) */
        @media (max-width: 380px) {
            body { padding: 12px 10px; }
            .lang-switcher { top: 8px; right: 8px; }
            .lang-switcher select {
                padding: 6px 24px 6px 9px;
                font-size: 9px;
                min-width: 80px;
                border-radius: 7px;
            }
            .reset-card { padding: 28px 16px; border-radius: 16px; }
            .logo-section { margin-bottom: 20px; }
            .logo-img { width: 56px; height: 56px; border-radius: 14px; margin-bottom: 9px; }
            .logo-title { font-size: 20px; }
            .logo-subtitle { font-size: 9px; letter-spacing: 1.2px; }
            .info-banner { padding: 10px 12px; gap: 8px; margin-bottom: 16px; border-radius: 8px; }
            .info-banner svg { width: 14px; height: 14px; }
            .info-banner p { font-size: 10px; line-height: 1.45; }
            .status-banner { padding: 10px 12px; font-size: 10px; border-radius: 8px; gap: 5px; }
            .status-banner svg { width: 12px; height: 12px; }
            .form-group { margin-bottom: 12px; }
            .form-label { font-size: 10px; margin-bottom: 4px; }
            .form-input { padding: 10px 12px 10px 38px; font-size: 12px; border-radius: 9px; }
            .input-icon { left: 12px; width: 15px; height: 15px; }
            .error-message { font-size: 9px; margin-top: 3px; }
            .error-message svg { width: 9px; height: 9px; }
            .submit-btn { padding: 12px; font-size: 12px; border-radius: 9px; margin-top: 4px; }
            .submit-btn.loading::after { width: 18px; height: 18px; margin: -9px 0 0 -9px; }
            .back-link-section { margin-top: 14px; padding-top: 14px; }
            .back-link { font-size: 11px; padding: 4px 8px; gap: 4px; }
            .back-link svg { width: 12px; height: 12px; }
            .footer-badges { gap: 12px; margin-top: 18px; }
            .badge { font-size: 8px; gap: 3px; }
            .badge svg { width: 10px; height: 10px; }
            .copyright { margin-top: 12px; font-size: 9px; }
            .notification { padding: 9px 14px; font-size: 10px; border-radius: 7px; top: 10px; }
        }
    </style>
</head>
<body>
<div class="bg-animation"></div>
<div class="grid-overlay"></div>
<div class="particles" id="particles"></div>
<div class="notification" id="notification"></div>

<div class="lang-switcher">
    <select id="lang-select" onchange="switchLang(this.value)">
        <option value="ru">🇷🇺 Русский</option>
        <option value="tj">🇹🇯 Тоҷикӣ</option>
        <option value="en">🇬🇧 English</option>
    </select>
</div>

<div class="container">
    <div class="reset-card">
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
            <p data-i18n="infoBanner">Забыли пароль? Введите email, и мы отправим ссылку для создания нового пароля.</p>
        </div>

        @if(session('status'))
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
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ $message }}
                </div>
                @enderror
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">
                <span class="btn-text" data-i18n="sendBtn">Отправить ссылку</span>
            </button>
        </form>

        <div class="back-link-section">
            <a href="{{ route('login') }}" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5"/>
                    <path d="m12 19-7-7 7-7"/>
                </svg>
                <span data-i18n="backLink">Вернуться к входу</span>
            </a>
        </div>
    </div>

    <div class="footer-badges">
        <div class="badge">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
            </svg>
            <span>SSL</span>
        </div>
        <div class="badge">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
            </svg>
            <span data-i18n="badgeSecurity">Защита</span>
        </div>
        <div class="badge">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"/>
            </svg>
            <span data-i18n="badgeSign">ЭЦП</span>
        </div>
    </div>

    <p class="copyright">© {{ date('Y') }} DocSign Ecosystem. <span data-i18n="rights">Все права защищены.</span></p>
</div>

<script>
    // Частицы
    function createParticles() {
        const container = document.getElementById('particles');
        for (let i = 0; i < 30; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            p.style.left = Math.random() * 100 + '%';
            p.style.animationDuration = (Math.random() * 10 + 8) + 's';
            p.style.animationDelay = (Math.random() * 10) + 's';
            p.style.width = p.style.height = (Math.random() * 3 + 1) + 'px';
            p.style.opacity = Math.random() * 0.5 + 0.1;
            container.appendChild(p);
        }
    }
    createParticles();

    // Переводы
    const translations = {
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
            rights: 'Ҳуқуқҳо ҳифз шудаанд.',
            invalidEmail: 'Формати email нодуруст',
            emptyEmail: 'Email-ро ворид кунед'
        },
        en: {
            subtitle: 'Password Recovery',
            infoBanner: 'Forgot your password? Enter your email and we\'ll send you a link to create a new password.',
            emailLabel: 'Email Address',
            emailPlaceholder: 'name@company.com',
            sendBtn: 'Send Reset Link',
            backLink: 'Back to Sign In',
            badgeSecurity: 'Security',
            badgeSign: 'EDS',
            rights: 'All rights reserved.',
            invalidEmail: 'Invalid email format',
            emptyEmail: 'Please enter email'
        }
    };

    let currentLang = 'ru';

    function switchLang(lang) {
        currentLang = lang;
        localStorage.setItem('docSign_lang', lang);
        document.documentElement.lang = lang;

        // Обновляем select
        const select = document.getElementById('lang-select');
        if (select) select.value = lang;

        const t = translations[lang];
        document.querySelectorAll('[data-i18n]').forEach(el => {
            if (t[el.dataset.i18n]) el.textContent = t[el.dataset.i18n];
        });
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            if (t[el.dataset.i18nPlaceholder]) el.placeholder = t[el.dataset.i18nPlaceholder];
        });
    }

    // Инициализация языка
    document.addEventListener('DOMContentLoaded', () => {
        const savedLang = localStorage.getItem('docSign_lang') || 'ru';
        switchLang(savedLang);
    });

    function showNotification(msg, type = 'error') {
        const n = document.getElementById('notification');
        n.textContent = msg;
        n.className = `notification ${type} show`;
        setTimeout(() => n.classList.remove('show'), 3000);
    }

    function handleReset(e) {
        const email = document.getElementById('email');
        const btn = document.getElementById('submitBtn');
        const t = translations[currentLang];
        email.classList.remove('error');

        if (!email.value.trim()) {
            email.classList.add('error');
            showNotification(t.emptyEmail, 'error');
            e.preventDefault();
            return false;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            email.classList.add('error');
            showNotification(t.invalidEmail, 'error');
            e.preventDefault();
            return false;
        }

        btn.classList.add('loading');
        btn.style.pointerEvents = 'none';
        return true;
    }

    document.getElementById('email')?.addEventListener('input', function() {
        this.classList.remove('error');
    });

@if (session('status'))
    document.addEventListener('DOMContentLoaded', () => {
        showNotification("{{ session('status') }}", 'success');
    });
@endif
</script>
</body>
</html>