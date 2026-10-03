<!DOCTYPE html>
<html lang="ru" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="Проверка подлинности документа DocSign">
    <title>DocSign </title>
    <link rel="icon" type="image/png" href="{{ asset('img/dss.png') }}">
<link rel="apple-touch-icon" href="{{ asset('img/dss.png') }}">
<link rel="shortcut icon" href="{{ asset('img/dss.png') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/inter@5.0.0/index.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/jetbrains-mono@5.0.0/index.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        /* ============================================================ */
        /* ===== БАЗОВЫЕ ПЕРЕМЕННЫЕ (Светлая тема по умолчанию) ===== */
        /* ============================================================ */
        :root {
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --glow: 59, 130, 246;
            --success: #22c55e;
            --success-bg: rgba(34, 197, 94, 0.08);
            --success-border: rgba(34, 197, 94, 0.2);
        }

        /* Темная тема */
        html[data-theme="dark"] {
            --bg-body: #06070b;
            --bg-card: rgba(255, 255, 255, 0.03);
            --text: #e7ecf3;
            --muted: #94a3b8;
            --line: rgba(148, 163, 184, 0.1);
            --glow: 100, 200, 255;
            --success: #10b981;
            --success-bg: rgba(16, 185, 129, 0.1);
            --success-border: rgba(16, 185, 129, 0.3);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-body);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            line-height: 1.5;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .verify-wrapper {
            width: 100%;
            max-width: 520px;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Карточка в стиле премиум-админки */
        .verify-card {
            background: var(--bg-card);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 32px;
            position: relative;
            overflow: hidden;
            box-shadow: 
                0 10px 40px -10px rgba(15, 23, 42, 0.08),
                0 0 0 1px rgba(255, 255, 255, 0.5) inset;
        }

        html[data-theme="dark"] .verify-card {
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.5);
        }

        /* Акцентная зеленая полоса слева */
        .verify-card::before {
            content: "";
            position: absolute;
            top: 0; left: 0; bottom: 0;
            width: 4px;
            background: var(--success);
            box-shadow: 0 0 15px rgba(34, 197, 94, 0.5);
        }

        /* Заголовок */
        .verify-header {
            text-align: center;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--line);
        }

        .verify-icon {
            width: 64px; height: 64px;
            margin: 0 auto 16px;
            background: var(--success-bg);
            border: 1px solid var(--success-border);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: var(--success);
            animation: pulse 2.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.3); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 12px rgba(34, 197, 94, 0); }
        }

        .verify-title {
            font-size: 22px; font-weight: 800;
            color: var(--success); margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        .verify-subtitle {
            font-size: 13px; color: var(--muted); font-weight: 500;
        }

        /* Секции данных */
        .verify-section { margin-bottom: 24px; }
        
        .verify-label {
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.8px;
            color: var(--muted); margin-bottom: 8px;
            display: flex; align-items: center; gap: 6px;
        }
        .verify-label svg { width: 14px; height: 14px; opacity: 0.7; }

        /* Значения без рамок, но с четким разделением */
        .verify-value {
            font-size: 15px; font-weight: 600;
            color: var(--text); word-break: break-word;
        }

        /* Дополнительные строки (email, телефон) */
        .verify-meta {
            display: flex; flex-direction: column; gap: 4px;
            margin-top: 6px;
        }

        .verify-meta-item {
            font-size: 13px; color: var(--muted);
            display: flex; align-items: center; gap: 6px;
        }

        .verify-meta-item svg {
            width: 14px; height: 14px; opacity: 0.6; flex-shrink: 0;
        }

        .verify-divider {
            height: 1px; background: var(--line);
            margin: 28px 0;
        }

        /* Кнопка скачивания */
        .download-btn {
            display: flex; align-items: center; justify-content: center;
            gap: 8px; width: 100%;
            padding: 16px 20px;
            background: linear-gradient(180deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.8));
            color: #ffffff; border: 0; border-radius: 12px;
            font-size: 14px; font-weight: 700;
            text-decoration: none; cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 8px 20px rgba(var(--glow), 0.25), inset 0 1px 0 rgba(255,255,255,0.2);
        }

        .download-btn:hover {
            filter: brightness(1.1); transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(var(--glow), 0.35), inset 0 1px 0 rgba(255,255,255,0.3);
        }

        .download-btn svg { width: 18px; height: 18px; }

        /* Футер */
        .verify-footer {
            text-align: center; margin-top: 28px;
            padding-top: 20px; border-top: 1px solid var(--line);
        }

        .verify-footer-text {
            font-size: 12px; color: var(--muted); font-weight: 500;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .verify-footer-text svg { width: 14px; height: 14px; opacity: 0.6; }

        .token-id {
            display: inline-block; margin-top: 10px;
            font-size: 11px; color: var(--muted);
            font-family: 'Inter', monospace; opacity: 0.7;
            background: rgba(var(--glow), 0.05);
            padding: 4px 10px; border-radius: 6px;
        }

        /* Адаптивность */
        @media (max-width: 640px) {
            body { padding: 16px; align-items: flex-start; padding-top: 32px; }
            .verify-card { padding: 24px 20px; border-radius: 14px; }
            .verify-icon { width: 56px; height: 56px; margin-bottom: 14px; }
            .verify-title { font-size: 20px; }
            .verify-value { font-size: 14px; }
            .download-btn { padding: 14px 18px; font-size: 13px; border-radius: 10px; }
        }

        @media (max-width: 380px) {
            .verify-card { padding: 20px 16px; }
            .verify-title { font-size: 18px; }
            .verify-value { font-size: 13px; }
            .verify-meta-item { font-size: 12px; }
        }
    </style>
</head>
<body>

<div class="verify-wrapper">
    <div class="verify-card">
        
        <!-- Заголовок -->
        <div class="verify-header">
            <div class="verify-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="verify-title" data-i18n="documentAuthentic">Документ подлинный</h1>
            <p class="verify-subtitle" data-i18n="verifiedDesc">Электронная подпись действительна</p>
        </div>

        <!-- Название документа -->
        <div class="verify-section">
            <div class="verify-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <span data-i18n="docTitle">Название документа</span>
            </div>
            <div class="verify-value">{{ $document->title }}</div>
        </div>

        <!-- Отправитель (Полные данные) -->
        <div class="verify-section">
            <div class="verify-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                <span data-i18n="sender">Отправитель</span>
            </div>
            <div class="verify-value">{{ $creator->name ?? __('Unknown') }}</div>
            
            @if(isset($creator->email) && $creator->email)
            <div class="verify-meta">
                <div class="verify-meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    {{ $creator->email }}
                </div>
            </div>
            @endif
            
            @if(isset($creator->phone) && $creator->phone)
            <div class="verify-meta">
                <div class="verify-meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                    </svg>
                    {{ $creator->phone }}
                </div>
            </div>
            @endif
        </div>

        <!-- Подписант (Полные данные) -->
        <div class="verify-section">
            <div class="verify-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
                <span data-i18n="signer">Подписант</span>
            </div>
            <div class="verify-value">{{ $signer->name ?? __('Unknown') }}</div>
            
            @if(isset($signer->email) && $signer->email)
            <div class="verify-meta">
                <div class="verify-meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    {{ $signer->email }}
                </div>
            </div>
            @endif
            
            @if(isset($signer->phone) && $signer->phone)
            <div class="verify-meta">
                <div class="verify-meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                    </svg>
                    {{ $signer->phone }}
                </div>
            </div>
            @endif
        </div>

        <div class="verify-divider"></div>

        <!-- Дата и время -->
        <div class="verify-section" style="margin-bottom: 0;">
            <div class="verify-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                <span data-i18n="signedAt">Дата и время подписания</span>
            </div>
            <div class="verify-value">
                {{ $signature->signed_at->format('d.m.Y H:i') }}
            </div>
        </div>

        <!-- Кнопка скачивания -->
        <div style="margin-top: 32px;">
            <a href="{{ route('documents.download', $document->id) }}" class="download-btn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span data-i18n="downloadOriginal">Скачать оригинал</span>
            </a>
        </div>

        <!-- Футер -->
        <div class="verify-footer">
            <div class="verify-footer-text">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
                <span data-i18n="verifiedBy">Проверка выполнена через систему DocSign</span>
            </div>
            @if($signature->verification_token)
                <div class="token-id">ID: {{ Str::limit($signature->verification_token, 16, '...') }}</div>
            @endif
        </div>

    </div>
</div>

<script>
    // Словарь переводов (RU, TJ, EN)
    const translations = {
        ru: {
            pageTitle: 'Проверка документа',
            documentAuthentic: 'Документ подлинный',
            verifiedDesc: 'Электронная подпись действительна',
            docTitle: 'Название документа',
            sender: 'Отправитель',
            signer: 'Подписант',
            signedAt: 'Дата и время подписания',
            downloadOriginal: 'Скачать оригинал',
            verifiedBy: 'Проверка выполнена через систему DocSign',
            Unknown: 'Неизвестно'
        },
        tj: {
            pageTitle: 'Тафтиши ҳуҷҷат',
            documentAuthentic: 'Ҳуҷҷат аслӣ аст',
            verifiedDesc: 'Имзои электронӣ дуруст аст',
            docTitle: 'Номи ҳуҷҷат',
            sender: 'Фиристанда',
            signer: 'Имзокунанда',
            signedAt: 'Сана ва вақти имзо',
            downloadOriginal: 'Боргирии аслӣ',
            verifiedBy: 'Тафтиш тавассути системаи DocSign анҷом дода шуд',
            Unknown: 'Номаълум'
        },
        en: {
            pageTitle: 'Document Verification',
            documentAuthentic: 'Document is Authentic',
            verifiedDesc: 'Electronic signature is valid',
            docTitle: 'Document Title',
            sender: 'Sender',
            signer: 'Signer',
            signedAt: 'Date and Time of Signing',
            downloadOriginal: 'Download Original',
            verifiedBy: 'Verified via DocSign System',
            Unknown: 'Unknown'
        }
    };

    function getCurrentLang() {
        return localStorage.getItem('docsign_lang') || 'ru';
    }

    function applyTranslations(lang) {
        const dict = translations[lang] || translations.ru;
        
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (dict[key] !== undefined) el.textContent = dict[key];
        });

        // Перевод fallback значений
        document.querySelectorAll('.verify-value').forEach(el => {
            if (el.textContent.trim() === 'Unknown' || el.textContent.trim() === 'Неизвестно') {
                el.textContent = dict['Unknown'];
            }
        });

        // Синхронизация темы
        const savedTheme = localStorage.getItem('docsign_theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const lang = getCurrentLang();
        applyTranslations(lang);
        
        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang') applyTranslations(e.newValue || 'ru');
            if (e.key === 'docsign_theme') document.documentElement.setAttribute('data-theme', e.newValue || 'light');
        });
    });
</script>

</body>
</html>