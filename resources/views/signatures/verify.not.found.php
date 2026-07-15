<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Документ не найден - DocSign</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #06070b 0%, #24243e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #e2e8f0;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient фон */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 20% 30%, rgba(239, 68, 68, 0.12) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(79, 140, 255, 0.08) 0%, transparent 40%);
            pointer-events: none;
            z-index: 0;
        }

        /* Сетка на фоне */
        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: radial-gradient(ellipse at 50% 50%, black 40%, transparent 80%);
            pointer-events: none;
            z-index: 0;
        }

        .container {
            max-width: 520px;
            width: 100%;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ============================================ */
        /* === ПЕРЕКЛЮЧАТЕЛЬ ЯЗЫКА === */
        /* ============================================ */
        .lang-switcher {
            display: flex;
            justify-content: center;
            margin-bottom: 16px;
            gap: 6px;
        }

        .lang-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.25s ease;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-family: 'Inter', sans-serif;
        }

        .lang-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            border-color: rgba(239, 68, 68, 0.3);
        }

        .lang-btn.active {
            background: linear-gradient(180deg, rgba(239, 68, 68, 0.25), rgba(239, 68, 68, 0.1));
            color: #fff;
            border-color: rgba(239, 68, 68, 0.5);
            box-shadow: 0 0 14px rgba(239, 68, 68, 0.3);
        }

        .lang-btn .flag {
            font-size: 14px;
        }

        /* ============================================ */
        /* === КАРТОЧКА === */
        /* ============================================ */
        .card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 50px 40px;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.02) inset;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(239, 68, 68, 0.5), transparent);
        }

        /* ============================================ */
        /* === ЛОГОТИП === */
        /* ============================================ */
        .logo {
            font-size: 32px;
            font-weight: 800;
            color: #4f8cff;
            margin-bottom: 30px;
            letter-spacing: 2px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-shadow: 0 0 20px rgba(79, 140, 255, 0.5);
        }

        .logo-dot {
            width: 10px;
            height: 10px;
            background: #4f8cff;
            border-radius: 50%;
            box-shadow: 0 0 12px rgba(79, 140, 255, 0.8);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.9); }
        }

        /* ============================================ */
        /* === ИКОНКА ОШИБКИ === */
        /* ============================================ */
        .error-icon-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 110px;
            height: 110px;
            margin-bottom: 24px;
        }

        .error-icon-wrapper::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle, rgba(239, 68, 68, 0.25) 0%, transparent 70%);
            border-radius: 50%;
            animation: errorGlow 2s ease-in-out infinite;
        }

        @keyframes errorGlow {
            0%, 100% { transform: scale(1); opacity: 0.6; }
            50% { transform: scale(1.15); opacity: 1; }
        }

        .error-icon {
            position: relative;
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.05));
            border: 2px solid rgba(239, 68, 68, 0.4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 30px rgba(239, 68, 68, 0.3);
        }

        .error-icon svg {
            width: 44px;
            height: 44px;
            color: #ef4444;
            filter: drop-shadow(0 0 8px rgba(239, 68, 68, 0.6));
        }

        /* ============================================ */
        /* === КОД ОШИБКИ === */
        /* ============================================ */
        .error-code {
            display: inline-block;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            padding: 4px 12px;
            border-radius: 6px;
            margin-bottom: 14px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        /* ============================================ */
        /* === ТЕКСТ === */
        /* ============================================ */
        .error-title {
            font-size: 24px;
            font-weight: 700;
            color: #e2e8f0;
            margin-bottom: 14px;
            letter-spacing: -0.3px;
            line-height: 1.3;
        }

        .error-text {
            font-size: 15px;
            color: #94a3b8;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        /* ============================================ */
        /* === КНОПКА ВОЗВРАТА === */
        /* ============================================ */
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            background: linear-gradient(180deg, rgba(79, 140, 255, 0.95), rgba(79, 140, 255, 0.65));
            color: #0a0d14;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.25s ease;
            box-shadow: 0 8px 24px rgba(79, 140, 255, 0.35), inset 0 1px 0 rgba(255,255,255,0.3);
            border: 1px solid transparent;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(79, 140, 255, 0.5);
            filter: brightness(1.08);
        }

        .btn-home:active {
            transform: translateY(0);
        }

        .btn-home svg {
            width: 14px;
            height: 14px;
        }

        /* ============================================ */
        /* === ФУТЕР === */
        /* ============================================ */
        .card-footer {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            font-size: 11px;
            color: #64748b;
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 0.5px;
        }

        /* ============================================ */
        /* === ПОЛНАЯ АДАПТИВНОСТЬ === */
        /* ============================================ */

        /* Маленькие десктопы (до 1200px) */
        @media (max-width: 1200px) {
            .card { padding: 46px 38px; border-radius: 22px; }
            .logo { font-size: 30px; letter-spacing: 1.8px; }
            .error-icon-wrapper { width: 105px; height: 105px; }
            .error-icon { width: 85px; height: 85px; }
            .error-icon svg { width: 42px; height: 42px; }
            .error-title { font-size: 23px; }
            .error-text { font-size: 14px; }
            .btn-home { padding: 11px 26px; font-size: 10px; }
        }

        /* Планшеты (до 992px) */
        @media (max-width: 992px) {
            body { padding: 18px; }
            .card { padding: 42px 34px; border-radius: 20px; }
            .logo { font-size: 28px; letter-spacing: 1.6px; gap: 9px; }
            .logo-dot { width: 9px; height: 9px; }
            .error-icon-wrapper { width: 100px; height: 100px; margin-bottom: 22px; }
            .error-icon { width: 82px; height: 82px; }
            .error-icon svg { width: 40px; height: 40px; }
            .error-code { font-size: 10px; padding: 3px 11px; margin-bottom: 12px; }
            .error-title { font-size: 22px; margin-bottom: 13px; }
            .error-text { font-size: 14px; margin-bottom: 26px; line-height: 1.65; }
            .btn-home { padding: 11px 25px; font-size: 10px; border-radius: 9px; }
            .card-footer { margin-top: 25px; padding-top: 18px; font-size: 10px; }
        }

        /* Большие телефоны (до 768px) */
        @media (max-width: 768px) {
            body { padding: 16px; }
            .lang-switcher { margin-bottom: 14px; gap: 5px; }
            .lang-btn { padding: 6px 12px; font-size: 10px; }
            .card { padding: 36px 28px; border-radius: 18px; }
            .logo { font-size: 26px; letter-spacing: 1.4px; gap: 8px; margin-bottom: 26px; }
            .logo-dot { width: 8px; height: 8px; }
            .error-icon-wrapper { width: 95px; height: 95px; margin-bottom: 20px; }
            .error-icon { width: 78px; height: 78px; border-width: 1.5px; }
            .error-icon svg { width: 38px; height: 38px; }
            .error-code { font-size: 10px; padding: 3px 10px; margin-bottom: 11px; }
            .error-title { font-size: 20px; margin-bottom: 12px; }
            .error-text { font-size: 13px; margin-bottom: 24px; }
            .btn-home { padding: 10px 22px; font-size: 10px; border-radius: 9px; }
            .card-footer { margin-top: 22px; padding-top: 16px; font-size: 10px; }
        }

        /* Телефоны (до 640px) */
        @media (max-width: 640px) {
            body { padding: 14px; }
            .lang-switcher { margin-bottom: 12px; }
            .lang-btn { padding: 6px 11px; font-size: 10px; border-radius: 9px; }
            .lang-btn .flag { font-size: 13px; }
            .card { padding: 30px 22px; border-radius: 16px; }
            .logo { font-size: 24px; letter-spacing: 1.2px; gap: 7px; margin-bottom: 24px; }
            .logo-dot { width: 7px; height: 7px; }
            .error-icon-wrapper { width: 88px; height: 88px; margin-bottom: 18px; }
            .error-icon { width: 72px; height: 72px; }
            .error-icon svg { width: 34px; height: 34px; }
            .error-code { font-size: 9px; padding: 3px 9px; margin-bottom: 10px; letter-spacing: 1.3px; }
            .error-title { font-size: 18px; margin-bottom: 11px; }
            .error-text { font-size: 12.5px; margin-bottom: 22px; line-height: 1.6; }
            .btn-home { padding: 10px 20px; font-size: 10px; border-radius: 8px; }
            .card-footer { margin-top: 20px; padding-top: 14px; font-size: 9px; }
        }

        /* Маленькие телефоны (до 480px) */
        @media (max-width: 480px) {
            body { padding: 12px; }
            .lang-switcher { gap: 4px; }
            .lang-btn { padding: 5px 10px; font-size: 9px; border-radius: 8px; gap: 5px; }
            .lang-btn .flag { font-size: 12px; }
            .card { padding: 26px 18px; border-radius: 15px; }
            .logo { font-size: 22px; letter-spacing: 1px; gap: 6px; margin-bottom: 22px; }
            .logo-dot { width: 6px; height: 6px; }
            .error-icon-wrapper { width: 80px; height: 80px; margin-bottom: 16px; }
            .error-icon { width: 66px; height: 66px; }
            .error-icon svg { width: 30px; height: 30px; }
            .error-code { font-size: 9px; padding: 3px 8px; margin-bottom: 9px; }
            .error-title { font-size: 17px; margin-bottom: 10px; }
            .error-text { font-size: 12px; margin-bottom: 20px; }
            .btn-home { padding: 9px 18px; font-size: 9px; border-radius: 8px; letter-spacing: 1px; }
            .card-footer { margin-top: 18px; padding-top: 12px; font-size: 9px; }
        }

        /* Очень маленькие телефоны (до 380px) */
        @media (max-width: 380px) {
            body { padding: 10px; }
            .lang-btn { padding: 5px 9px; font-size: 8px; }
            .card { padding: 22px 16px; border-radius: 14px; }
            .logo { font-size: 20px; letter-spacing: 0.8px; margin-bottom: 20px; }
            .error-icon-wrapper { width: 72px; height: 72px; margin-bottom: 14px; }
            .error-icon { width: 60px; height: 60px; }
            .error-icon svg { width: 28px; height: 28px; }
            .error-code { font-size: 8px; padding: 2px 7px; }
            .error-title { font-size: 16px; margin-bottom: 9px; }
            .error-text { font-size: 11px; margin-bottom: 18px; }
            .btn-home { padding: 9px 16px; font-size: 9px; }
            .card-footer { margin-top: 16px; padding-top: 10px; font-size: 8px; }
        }
    </style>
</head>
<body>
<div class="container">
    {{-- Переключатель языка --}}
    <div class="lang-switcher">
        <button class="lang-btn active" data-lang="ru">
            <span class="flag">🇷🇺</span>
            <span>RU</span>
        </button>
        <button class="lang-btn" data-lang="tj">
            <span class="flag">🇹🇯</span>
            <span>TJ</span>
        </button>
        <button class="lang-btn" data-lang="en">
            <span class="flag">🇬🇧</span>
            <span>EN</span>
        </button>
    </div>

    <div class="card">
        <div class="logo">
            <span class="logo-dot"></span>
            DOCSIGN
        </div>

        <div class="error-icon-wrapper">
            <div class="error-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
        </div>

        <div class="error-code" data-i18n="errorCode">Error 404</div>

        <div class="error-title" data-i18n="errorTitle">Документ не найден</div>

        <div class="error-text" data-i18n="errorText">
            Документ с таким кодом верификации не существует или был удалён. Пожалуйста, проверьте правильность QR-кода.
        </div>

        <a href="/" class="btn-home">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <span data-i18n="btnHome">На главную</span>
        </a>

        <div class="card-footer">
            <span data-i18n="footerText">DocSign © 2026 — Система электронного документооборота</span>
        </div>
    </div>
</div>

<script>
    // ============================================================
    // ЛОКАЛЬНЫЙ СЛОВАРЬ СТРАНИЦЫ ОШИБКИ
    // ============================================================
    const ERROR_TRANSLATIONS = {
        ru: {
            errorCode: 'Ошибка 404',
            errorTitle: 'Документ не найден',
            errorText: 'Документ с таким кодом верификации не существует или был удалён. Пожалуйста, проверьте правильность QR-кода.',
            btnHome: 'На главную',
            footerText: 'DocSign © 2026 — Система электронного документооборота'
        },
        tj: {
            errorCode: 'Хатои 404',
            errorTitle: 'Ҳуҷҷат ёфт нашуд',
            errorText: 'Ҳуҷҷат бо ин рамзи тасдиқ вуҷуд надорад ё нест карда шудааст. Лутфан, дурустии QR-кодро тафтиш кунед.',
            btnHome: 'Ба саҳифаи асосӣ',
            footerText: 'DocSign © 2026 — Системаи ҳуҷҷатгардонии электронӣ'
        },
        en: {
            errorCode: 'Error 404',
            errorTitle: 'Document Not Found',
            errorText: 'The document with this verification code does not exist or has been deleted. Please check the QR code for accuracy.',
            btnHome: 'Go Home',
            footerText: 'DocSign © 2026 — Electronic Document Management System'
        }
    };

    // ============================================================
    // ФУНКЦИЯ ПРИМЕНЕНИЯ ПЕРЕВОДОВ
    // ============================================================
    function applyErrorTranslations(lang) {
        const dict = ERROR_TRANSLATIONS[lang] || ERROR_TRANSLATIONS.ru;

        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (dict[key] !== undefined) el.textContent = dict[key];
        });

        document.documentElement.lang = lang;
    }

    // ============================================================
    // ОБРАБОТЧИКИ ПЕРЕКЛЮЧАТЕЛЯ ЯЗЫКА
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {
        const langBtns = document.querySelectorAll('.lang-btn');

        // Восстанавливаем язык из localStorage
        const savedLang = localStorage.getItem('docsign_lang') || 'ru';
        langBtns.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.lang === savedLang);
        });
        applyErrorTranslations(savedLang);

        // Обработчики кликов
        langBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const lang = this.dataset.lang;

                langBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                localStorage.setItem('docsign_lang', lang);
                applyErrorTranslations(lang);

                window.dispatchEvent(new CustomEvent('docsign:lang-changed', {
                    detail: { lang }
                }));
            });
        });

        // Синхронизация между вкладками
        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                langBtns.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.lang === e.newValue);
                });
                applyErrorTranslations(e.newValue);
            }
        });
    });
</script>
</body>
</html>