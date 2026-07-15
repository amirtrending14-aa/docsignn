<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Верификация документа - DocSign</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
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
        }

        /* Ambient эффект фона */
        body::before {
            content: "";
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background:
                radial-gradient(circle at 20% 30%, rgba(79, 140, 255, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(34, 197, 94, 0.08) 0%, transparent 40%);
            pointer-events: none;
            z-index: 0;
        }

        .container {
            max-width: 600px;
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
            justify-content: flex-end;
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
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.25s ease;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .lang-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            border-color: rgba(79, 140, 255, 0.3);
        }

        .lang-btn.active {
            background: linear-gradient(180deg, rgba(79, 140, 255, 0.25), rgba(79, 140, 255, 0.1));
            color: #fff;
            border-color: rgba(79, 140, 255, 0.5);
            box-shadow: 0 0 14px rgba(79, 140, 255, 0.3);
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
            padding: 40px;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.02) inset;
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
            background: linear-gradient(90deg, transparent, rgba(79, 140, 255, 0.5), transparent);
        }

        /* ============================================ */
        /* === HEADER === */
        /* ============================================ */
        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 32px;
            font-weight: 800;
            color: #4f8cff;
            margin-bottom: 10px;
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

        .header-subtitle {
            font-size: 12px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(34, 197, 94, 0.15);
            border: 2px solid rgba(34, 197, 94, 0.6);
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 700;
            color: #22c55e;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.25);
        }

        .status-badge::before {
            content: "✓";
            font-size: 18px;
            font-weight: 900;
            width: 22px;
            height: 22px;
            background: #22c55e;
            color: #fff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 10px rgba(34, 197, 94, 0.6);
        }

        /* ============================================ */
        /* === INFO SECTION === */
        /* ============================================ */
        .info-section {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.2s ease;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row:hover {
            background: rgba(79, 140, 255, 0.03);
            margin: 0 -12px;
            padding-left: 12px;
            padding-right: 12px;
            border-radius: 10px;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            background: rgba(79, 140, 255, 0.15);
            border: 1px solid rgba(79, 140, 255, 0.25);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
            transition: all 0.25s ease;
        }

        .info-row:hover .info-icon {
            transform: scale(1.05);
            box-shadow: 0 0 14px rgba(79, 140, 255, 0.3);
        }

        .info-content {
            flex: 1;
            min-width: 0;
        }

        .info-label {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .info-value {
            font-size: 15px;
            color: #e2e8f0;
            font-weight: 600;
            word-break: break-word;
        }

        .info-value-sub {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 3px;
            word-break: break-all;
        }

        .document-title {
            font-size: 17px;
            font-weight: 700;
            color: #4f8cff;
            word-break: break-word;
            line-height: 1.4;
        }

        .status-text {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .status-text.completed {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .status-text.processing {
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        .status-text.pending {
            background: rgba(148, 163, 184, 0.15);
            color: #94a3b8;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        .status-text::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            box-shadow: 0 0 8px currentColor;
        }

        /* ============================================ */
        /* === FOOTER === */
        /* ============================================ */
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-text {
            font-size: 13px;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .verification-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: #64748b;
            padding: 8px 14px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 8px;
            display: inline-block;
            word-break: break-all;
            letter-spacing: 0.5px;
        }

        .verification-code strong {
            color: #4f8cff;
            font-weight: 600;
        }

        /* ============================================ */
        /* === ПОЛНАЯ АДАПТИВНОСТЬ === */
        /* ============================================ */

        /* Маленькие десктопы (до 1200px) */
        @media (max-width: 1200px) {
            .card { padding: 36px; border-radius: 22px; }
            .logo { font-size: 30px; letter-spacing: 1.8px; }
            .status-badge { font-size: 13px; padding: 9px 18px; }
            .info-section { padding: 23px; }
            .info-icon { width: 38px; height: 38px; font-size: 17px; }
            .document-title { font-size: 16px; }
        }

        /* Планшеты (до 992px) */
        @media (max-width: 992px) {
            body { padding: 18px; }
            .card { padding: 32px; border-radius: 20px; }
            .logo { font-size: 28px; letter-spacing: 1.6px; }
            .header-subtitle { font-size: 11px; letter-spacing: 1.8px; }
            .status-badge { font-size: 12px; padding: 9px 16px; letter-spacing: 0.8px; }
            .status-badge::before { width: 20px; height: 20px; font-size: 16px; }
            .info-section { padding: 20px; border-radius: 14px; }
            .info-row { gap: 13px; padding: 13px 0; }
            .info-icon { width: 36px; height: 36px; font-size: 16px; border-radius: 9px; }
            .info-label { font-size: 9px; letter-spacing: 1.1px; }
            .info-value { font-size: 14px; }
            .info-value-sub { font-size: 12px; }
            .document-title { font-size: 15px; }
            .footer-text { font-size: 12px; }
            .verification-code { font-size: 10px; padding: 7px 12px; }
        }

        /* Большие телефоны (до 768px) */
        @media (max-width: 768px) {
            body { padding: 16px; }
            .lang-switcher { margin-bottom: 14px; gap: 5px; }
            .lang-btn { padding: 6px 12px; font-size: 10px; }
            .card { padding: 28px; border-radius: 18px; }
            .logo { font-size: 26px; letter-spacing: 1.4px; gap: 8px; }
            .logo-dot { width: 9px; height: 9px; }
            .header-subtitle { font-size: 10px; letter-spacing: 1.6px; margin-bottom: 12px; }
            .status-badge { font-size: 11px; padding: 8px 14px; letter-spacing: 0.7px; gap: 6px; }
            .status-badge::before { width: 19px; height: 19px; font-size: 15px; }
            .header { margin-bottom: 25px; }
            .info-section { padding: 18px; border-radius: 13px; margin-bottom: 18px; }
            .info-row { gap: 12px; padding: 12px 0; }
            .info-icon { width: 34px; height: 34px; font-size: 15px; border-radius: 8px; }
            .info-label { font-size: 9px; letter-spacing: 1px; margin-bottom: 4px; }
            .info-value { font-size: 13px; }
            .info-value-sub { font-size: 11px; margin-top: 2px; }
            .document-title { font-size: 14px; }
            .status-text { font-size: 11px; padding: 3px 10px; }
            .footer { margin-top: 25px; padding-top: 18px; }
            .footer-text { font-size: 11px; margin-bottom: 10px; }
            .verification-code { font-size: 10px; padding: 7px 11px; }
        }

        /* Телефоны (до 640px) */
        @media (max-width: 640px) {
            body { padding: 14px; }
            .lang-switcher { justify-content: center; margin-bottom: 12px; }
            .lang-btn { padding: 6px 11px; font-size: 10px; border-radius: 9px; }
            .lang-btn .flag { font-size: 13px; }
            .card { padding: 24px; border-radius: 16px; }
            .logo { font-size: 24px; letter-spacing: 1.2px; gap: 7px; }
            .logo-dot { width: 8px; height: 8px; }
            .header-subtitle { font-size: 10px; letter-spacing: 1.4px; }
            .status-badge { font-size: 10px; padding: 8px 13px; border-radius: 40px; }
            .status-badge::before { width: 18px; height: 18px; font-size: 14px; }
            .header { margin-bottom: 22px; }
            .info-section { padding: 16px; border-radius: 12px; margin-bottom: 16px; }
            .info-row { gap: 11px; padding: 11px 0; }
            .info-row:hover { margin: 0 -8px; padding-left: 8px; padding-right: 8px; }
            .info-icon { width: 32px; height: 32px; font-size: 14px; border-radius: 8px; }
            .info-label { font-size: 8px; letter-spacing: 0.9px; margin-bottom: 3px; }
            .info-value { font-size: 13px; }
            .info-value-sub { font-size: 11px; }
            .document-title { font-size: 13px; }
            .status-text { font-size: 10px; padding: 3px 9px; letter-spacing: 0.7px; }
            .footer { margin-top: 22px; padding-top: 16px; }
            .footer-text { font-size: 11px; }
            .verification-code { font-size: 9px; padding: 6px 10px; }
        }

        /* Маленькие телефоны (до 480px) */
        @media (max-width: 480px) {
            body { padding: 12px; }
            .lang-switcher { gap: 4px; }
            .lang-btn { padding: 5px 10px; font-size: 9px; border-radius: 8px; gap: 5px; }
            .lang-btn .flag { font-size: 12px; }
            .card { padding: 20px; border-radius: 15px; }
            .logo { font-size: 22px; letter-spacing: 1px; gap: 6px; }
            .logo-dot { width: 7px; height: 7px; }
            .header-subtitle { font-size: 9px; letter-spacing: 1.2px; margin-bottom: 10px; }
            .status-badge { font-size: 9px; padding: 7px 12px; gap: 5px; letter-spacing: 0.6px; }
            .status-badge::before { width: 17px; height: 17px; font-size: 13px; }
            .header { margin-bottom: 20px; }
            .info-section { padding: 14px; border-radius: 11px; margin-bottom: 14px; }
            .info-row { gap: 10px; padding: 10px 0; }
            .info-icon { width: 30px; height: 30px; font-size: 13px; border-radius: 7px; }
            .info-label { font-size: 8px; letter-spacing: 0.8px; }
            .info-value { font-size: 12px; }
            .info-value-sub { font-size: 10px; }
            .document-title { font-size: 13px; }
            .status-text { font-size: 9px; padding: 3px 8px; }
            .footer { margin-top: 20px; padding-top: 14px; }
            .footer-text { font-size: 10px; line-height: 1.5; }
            .verification-code { font-size: 9px; padding: 6px 9px; width: 100%; }
        }

        /* Очень маленькие телефоны (до 380px) */
        @media (max-width: 380px) {
            body { padding: 10px; }
            .lang-btn { padding: 5px 9px; font-size: 8px; }
            .card { padding: 18px; border-radius: 14px; }
            .logo { font-size: 20px; letter-spacing: 0.8px; }
            .header-subtitle { font-size: 8px; letter-spacing: 1px; }
            .status-badge { font-size: 9px; padding: 6px 11px; }
            .status-badge::before { width: 16px; height: 16px; font-size: 12px; }
            .info-section { padding: 12px; border-radius: 10px; }
            .info-row { gap: 9px; padding: 9px 0; }
            .info-icon { width: 28px; height: 28px; font-size: 12px; }
            .info-label { font-size: 7px; }
            .info-value { font-size: 12px; }
            .document-title { font-size: 12px; }
            .status-text { font-size: 9px; }
            .footer-text { font-size: 10px; }
            .verification-code { font-size: 8px; padding: 5px 8px; }
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
        <div class="header">
            <div class="logo">
                <span class="logo-dot"></span>
                DOCSIGN
            </div>
            <div class="header-subtitle" data-i18n="subtitle">Система верификации документов</div>
            <div class="status-badge" data-i18n="verified">Документ верифицирован</div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-icon">📄</div>
                <div class="info-content">
                    <div class="info-label" data-i18n="labelDocTitle">Название документа</div>
                    <div class="info-value document-title">{{ $document->title }}</div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-icon">👤</div>
                <div class="info-content">
                    <div class="info-label" data-i18n="labelSender">Отправитель</div>
                    <div class="info-value">{{ $creator->name ?? 'Неизвестно' }}</div>
                    <div class="info-value-sub">{{ $creator->email ?? '-' }}</div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-icon">✍️</div>
                <div class="info-content">
                    <div class="info-label" data-i18n="labelSigner">Подписант</div>
                    <div class="info-value">{{ $signer->name ?? 'Неизвестно' }}</div>
                    <div class="info-value-sub">{{ $signer->email ?? '-' }}</div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-icon">📅</div>
                <div class="info-content">
                    <div class="info-label" data-i18n="labelDateSent">Дата отправки</div>
                    <div class="info-value">
                        {{ $document->created_at ? $document->created_at->format('d.m.Y H:i') : 'Неизвестно' }}
                    </div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-icon">✅</div>
                <div class="info-content">
                    <div class="info-label" data-i18n="labelDateSigned">Дата подписания</div>
                    <div class="info-value">
                        {{ $signature->signed_at ? $signature->signed_at->format('d.m.Y H:i:s') : 'Неизвестно' }}
                    </div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-icon">📊</div>
                <div class="info-content">
                    <div class="info-label" data-i18n="labelDocStatus">Статус документа</div>
                    <div class="info-value">
                        @if($document->status === 'completed')
                        <span class="status-text completed" data-i18n="statusCompleted">Завершен</span>
                        @elseif($document->status === 'processing')
                        <span class="status-text processing" data-i18n="statusProcessing">В процессе</span>
                        @else
                        <span class="status-text pending" data-i18n="statusPending">Ожидает подписи</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="footer-text" data-i18n="footerText">
                Этот документ был подписан электронно через систему DocSign
            </div>
            <div class="verification-code">
                <strong data-i18n="verifyCode">Код верификации:</strong> {{ $signature->verification_code }}
            </div>
        </div>
    </div>
</div>

<script>
    // ============================================================
    // ЛОКАЛЬНЫЙ СЛОВАРЬ СТРАНИЦЫ ВЕРИФИКАЦИИ
    // ============================================================
    const VERIFY_TRANSLATIONS = {
        ru: {
            subtitle: 'Система верификации документов',
            verified: 'Документ верифицирован',
            labelDocTitle: 'Название документа',
            labelSender: 'Отправитель',
            labelSigner: 'Подписант',
            labelDateSent: 'Дата отправки',
            labelDateSigned: 'Дата подписания',
            labelDocStatus: 'Статус документа',
            statusCompleted: 'Завершен',
            statusProcessing: 'В процессе',
            statusPending: 'Ожидает подписи',
            footerText: 'Этот документ был подписан электронно через систему DocSign',
            verifyCode: 'Код верификации:',
            unknown: 'Неизвестно'
        },
        tj: {
            subtitle: 'Системаи тасдиқи ҳуҷҷатҳо',
            verified: 'Ҳуҷҷат тасдиқ шуд',
            labelDocTitle: 'Номи ҳуҷҷат',
            labelSender: 'Фиристанда',
            labelSigner: 'Имзокунанда',
            labelDateSent: 'Санаи фиристодан',
            labelDateSigned: 'Санаи имзо',
            labelDocStatus: 'Статуси ҳуҷҷат',
            statusCompleted: 'Анҷом ёфт',
            statusProcessing: 'Дар ҷараён',
            statusPending: 'Мунтазири имзо',
            footerText: 'Ин ҳуҷҷат тавассути системаи DocSign ба таври электронӣ имзо шудааст',
            verifyCode: 'Рамзи тасдиқ:',
            unknown: 'Номаълум'
        },
        en: {
            subtitle: 'Document Verification System',
            verified: 'Document Verified',
            labelDocTitle: 'Document Title',
            labelSender: 'Sender',
            labelSigner: 'Signer',
            labelDateSent: 'Date Sent',
            labelDateSigned: 'Date Signed',
            labelDocStatus: 'Document Status',
            statusCompleted: 'Completed',
            statusProcessing: 'Processing',
            statusPending: 'Awaiting Signature',
            footerText: 'This document was electronically signed through the DocSign system',
            verifyCode: 'Verification Code:',
            unknown: 'Unknown'
        }
    };

    // ============================================================
    // ФУНКЦИЯ ПРИМЕНЕНИЯ ПЕРЕВОДОВ
    // ============================================================
    function applyVerifyTranslations(lang) {
        const dict = VERIFY_TRANSLATIONS[lang] || VERIFY_TRANSLATIONS.ru;

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
        applyVerifyTranslations(savedLang);

        // Обработчики кликов
        langBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const lang = this.dataset.lang;

                // Обновляем активную кнопку
                langBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Сохраняем в localStorage
                localStorage.setItem('docsign_lang', lang);

                // Применяем переводы
                applyVerifyTranslations(lang);

                // Синхронизируем с другими вкладками
                window.dispatchEvent(new CustomEvent('docsign:lang-changed', {
                    detail: { lang }
                }));
            });
        });

        // Слушаем изменения в других вкладках
        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                langBtns.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.lang === e.newValue);
                });
                applyVerifyTranslations(e.newValue);
            }
        });
    });
</script>
</body>
</html>