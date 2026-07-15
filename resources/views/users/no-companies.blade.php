@extends('layouts.admin')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    .no-company-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 24px;
        color: var(--text);
        font-family: 'Inter', sans-serif;
        position: relative;
        overflow: hidden;
    }

    /* Фоновые blob-ы */
    .nc-blob {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
        filter: blur(110px);
        opacity: 0.4;
    }

    .nc-blob-1 {
        top: -150px;
        left: -150px;
        width: 550px;
        height: 550px;
        background: radial-gradient(circle, rgba(var(--glow), 0.35) 0%, transparent 70%);
        animation: blobFloat 20s ease-in-out infinite;
    }

    .nc-blob-2 {
        bottom: -150px;
        right: -150px;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(168, 85, 247, 0.3) 0%, transparent 70%);
        animation: blobFloat 25s ease-in-out infinite reverse;
    }

    .nc-blob-3 {
        top: 50%;
        left: 50%;
        width: 450px;
        height: 450px;
        transform: translate(-50%, -50%);
        background: radial-gradient(circle, rgba(236, 72, 153, 0.25) 0%, transparent 70%);
        animation: blobFloat3 30s ease-in-out infinite;
    }

    @keyframes blobFloat {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(30px, -30px); }
    }

    @keyframes blobFloat3 {
        0%, 100% { transform: translate(-50%, -50%); }
        50% { transform: translate(calc(-50% + 30px), calc(-50% - 30px)); }
    }

    /* === CENTER CARD === */
    .nc-card {
        position: relative;
        max-width: 520px;
        width: 100%;
        text-align: center;
        padding: 56px 44px;
        background: linear-gradient(180deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
        border: 1px solid var(--line);
        border-radius: 24px;
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        z-index: 1;
        animation: cardAppear 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes cardAppear {
        0% { opacity: 0; transform: translateY(20px) scale(0.97); }
        100% { opacity: 1; transform: translateY(0) scale(1); }
    }

    .nc-card::before {
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: 24px;
        padding: 1px;
        background: linear-gradient(135deg, rgba(var(--glow), 0.6), transparent 40%, transparent 60%, rgba(168, 85, 247, 0.4));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0.8;
        pointer-events: none;
    }

    /* Иконка здания */
    .nc-icon-wrap {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto 28px;
        display: grid;
        place-items: center;
    }

    .nc-icon-bg {
        position: absolute;
        inset: 0;
        border-radius: 28px;
        background: linear-gradient(135deg, rgba(var(--glow), 0.35), rgba(168, 85, 247, 0.25));
        box-shadow:
            0 20px 50px -10px rgba(var(--glow), 0.5),
            inset 0 0 30px rgba(255,255,255,0.1);
        animation: iconFloat 4s ease-in-out infinite;
    }

    .nc-icon-bg::before {
        content: "";
        position: absolute;
        inset: -2px;
        border-radius: 30px;
        padding: 2px;
        background: linear-gradient(135deg, rgba(var(--glow), 0.8), rgba(168, 85, 247, 0.6));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0.7;
    }

    @keyframes iconFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }

    .nc-icon-glow {
        position: absolute;
        inset: -20px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(var(--glow), 0.4) 0%, transparent 60%);
        filter: blur(20px);
        animation: glowPulse 3s ease-in-out infinite;
    }

    @keyframes glowPulse {
        0%, 100% { opacity: 0.5; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.1); }
    }

    .nc-icon-svg {
        position: relative;
        width: 56px;
        height: 56px;
        color: #ffffff;
        filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
        z-index: 1;
    }

    /* Заголовок */
    .nc-title {
        font-size: 28px;
        font-weight: 800;
        color: var(--text);
        letter-spacing: -0.5px;
        margin: 0 0 12px;
        line-height: 1.2;
    }

    .nc-title .accent {
        background: linear-gradient(135deg, rgba(var(--glow), 1), rgba(168, 85, 247, 1));
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .nc-desc {
        font-size: 14px;
        color: var(--muted);
        font-weight: 500;
        line-height: 1.6;
        margin: 0 0 32px;
        max-width: 380px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Разделитель */
    .nc-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 auto 28px;
        max-width: 280px;
    }

    .nc-divider-line {
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--line), transparent);
    }

    .nc-divider-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: rgba(var(--glow), 0.8);
        box-shadow: 0 0 12px rgba(var(--glow), 0.8);
    }

    /* Инфо-блок */
    .nc-info-box {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px 18px;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--line);
        border-radius: 14px;
        margin-bottom: 28px;
        text-align: left;
        position: relative;
        overflow: hidden;
    }

    .nc-info-box::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, rgba(var(--glow), 1), rgba(var(--glow), 0.3));
        box-shadow: 0 0 12px rgba(var(--glow), 0.6);
    }

    .nc-info-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(var(--glow), 0.15);
        border: 1px solid rgba(var(--glow), 0.3);
        display: grid;
        place-items: center;
        flex-shrink: 0;
        box-shadow: inset 0 0 10px rgba(var(--glow), 0.2);
    }

    .nc-info-icon svg {
        width: 18px;
        height: 18px;
        color: rgba(var(--glow), 1);
    }

    .nc-info-text {
        flex: 1;
        font-size: 13px;
        color: var(--muted);
        font-weight: 500;
        line-height: 1.5;
    }

    .nc-info-text strong {
        color: var(--text);
        font-weight: 700;
        display: block;
        margin-bottom: 4px;
    }

    /* Кнопка */
    .nc-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 32px;
        background: linear-gradient(180deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.65));
        color: #0a0d14;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow:
            0 10px 30px rgba(var(--glow), 0.4),
            inset 0 1px 0 rgba(255,255,255,0.3);
        border: 1px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .nc-btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.6s ease;
    }

    .nc-btn:hover::before {
        left: 100%;
    }

    .nc-btn:hover {
        transform: translateY(-3px);
        box-shadow:
            0 16px 40px rgba(var(--glow), 0.6),
            inset 0 1px 0 rgba(255,255,255,0.4);
        filter: brightness(1.08);
    }

    .nc-btn svg {
        width: 16px;
        height: 16px;
    }

    /* Декоративные частицы */
    .nc-particles {
        position: absolute;
        inset: 0;
        pointer-events: none;
        overflow: hidden;
        z-index: 0;
    }

    .nc-particle {
        position: absolute;
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: rgba(var(--glow), 0.6);
        box-shadow: 0 0 8px rgba(var(--glow), 0.8);
        animation: particleFloat 8s linear infinite;
    }

    .nc-particle:nth-child(1) { top: 20%; left: 10%; animation-delay: 0s; animation-duration: 9s; }
    .nc-particle:nth-child(2) { top: 60%; left: 85%; animation-delay: 2s; animation-duration: 11s; }
    .nc-particle:nth-child(3) { top: 80%; left: 20%; animation-delay: 4s; animation-duration: 10s; }
    .nc-particle:nth-child(4) { top: 30%; left: 75%; animation-delay: 1s; animation-duration: 12s; }
    .nc-particle:nth-child(5) { top: 70%; left: 50%; animation-delay: 3s; animation-duration: 8s; }
    .nc-particle:nth-child(6) { top: 15%; left: 60%; animation-delay: 5s; animation-duration: 13s; }

    @keyframes particleFloat {
        0% { transform: translateY(0) translateX(0); opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { transform: translateY(-100px) translateX(30px); opacity: 0; }
    }

    /* ============================================ */
    /* === ПОЛНАЯ АДАПТИВНОСТЬ === */
    /* ============================================ */

    /* Маленькие десктопы (до 1200px) */
    @media (max-width: 1200px) {
        .no-company-page { padding: 36px 22px; }
        .nc-card { padding: 52px 40px; border-radius: 22px; }
        .nc-icon-wrap { width: 110px; height: 110px; margin-bottom: 26px; }
        .nc-icon-bg { border-radius: 26px; }
        .nc-icon-bg::before { border-radius: 28px; }
        .nc-icon-svg { width: 52px; height: 52px; }
        .nc-title { font-size: 26px; margin-bottom: 11px; }
        .nc-desc { font-size: 13px; margin-bottom: 30px; max-width: 360px; }
        .nc-divider { margin-bottom: 26px; max-width: 260px; }
        .nc-info-box { padding: 15px 17px; margin-bottom: 26px; border-radius: 13px; gap: 13px; }
        .nc-info-icon { width: 34px; height: 34px; border-radius: 9px; }
        .nc-info-icon svg { width: 17px; height: 17px; }
        .nc-info-text { font-size: 12px; }
        .nc-btn { padding: 13px 30px; font-size: 12px; border-radius: 11px; }
        .nc-btn svg { width: 15px; height: 15px; }
    }

    /* Планшеты (до 992px) */
    @media (max-width: 992px) {
        .no-company-page { padding: 32px 20px; }
        .nc-card { padding: 48px 36px; border-radius: 20px; }
        .nc-icon-wrap { width: 100px; height: 100px; margin-bottom: 24px; }
        .nc-icon-bg { border-radius: 24px; }
        .nc-icon-bg::before { border-radius: 26px; }
        .nc-icon-glow { inset: -18px; }
        .nc-icon-svg { width: 48px; height: 48px; }
        .nc-title { font-size: 24px; margin-bottom: 10px; letter-spacing: -0.4px; }
        .nc-desc { font-size: 13px; line-height: 1.55; margin-bottom: 28px; max-width: 340px; }
        .nc-divider { margin-bottom: 24px; max-width: 240px; gap: 11px; }
        .nc-divider-dot { width: 5px; height: 5px; }
        .nc-info-box { padding: 14px 16px; margin-bottom: 24px; border-radius: 12px; gap: 12px; }
        .nc-info-icon { width: 32px; height: 32px; border-radius: 9px; }
        .nc-info-icon svg { width: 16px; height: 16px; }
        .nc-info-text { font-size: 12px; line-height: 1.45; }
        .nc-btn { padding: 13px 28px; font-size: 12px; border-radius: 11px; letter-spacing: 1.1px; }
        .nc-btn svg { width: 15px; height: 15px; }
    }

    /* Большие телефоны (до 768px) */
    @media (max-width: 768px) {
        .no-company-page { padding: 28px 18px; }
        .nc-card { padding: 44px 32px; border-radius: 18px; }
        .nc-icon-wrap { width: 95px; height: 95px; margin-bottom: 22px; }
        .nc-icon-bg { border-radius: 22px; }
        .nc-icon-bg::before { border-radius: 24px; inset: -1.5px; padding: 1.5px; }
        .nc-icon-glow { inset: -16px; filter: blur(18px); }
        .nc-icon-svg { width: 44px; height: 44px; }
        .nc-title { font-size: 22px; margin-bottom: 10px; }
        .nc-desc { font-size: 12px; line-height: 1.5; margin-bottom: 26px; max-width: 320px; }
        .nc-divider { margin-bottom: 22px; max-width: 220px; gap: 10px; }
        .nc-divider-dot { width: 5px; height: 5px; }
        .nc-info-box { padding: 13px 15px; margin-bottom: 22px; border-radius: 11px; gap: 11px; }
        .nc-info-box::before { width: 2.5px; }
        .nc-info-icon { width: 30px; height: 30px; border-radius: 8px; }
        .nc-info-icon svg { width: 15px; height: 15px; }
        .nc-info-text { font-size: 11px; line-height: 1.45; }
        .nc-btn { padding: 12px 26px; font-size: 11px; border-radius: 10px; letter-spacing: 1px; gap: 9px; }
        .nc-btn svg { width: 14px; height: 14px; }
    }

    /* Телефоны (до 640px) */
    @media (max-width: 640px) {
        .no-company-page { padding: 24px 16px; }
        .nc-card { padding: 40px 24px; border-radius: 16px; }
        .nc-icon-wrap { width: 90px; height: 90px; margin-bottom: 20px; }
        .nc-icon-bg { border-radius: 20px; }
        .nc-icon-bg::before { border-radius: 22px; }
        .nc-icon-glow { inset: -14px; }
        .nc-icon-svg { width: 42px; height: 42px; }
        .nc-title { font-size: 20px; margin-bottom: 9px; }
        .nc-desc { font-size: 12px; line-height: 1.5; margin-bottom: 24px; max-width: 100%; }
        .nc-divider { margin-bottom: 20px; max-width: 200px; gap: 9px; }
        .nc-info-box { padding: 12px 14px; margin-bottom: 20px; border-radius: 10px; gap: 10px; }
        .nc-info-box::before { width: 2.5px; }
        .nc-info-icon { width: 28px; height: 28px; border-radius: 7px; }
        .nc-info-icon svg { width: 14px; height: 14px; }
        .nc-info-text { font-size: 11px; }
        .nc-info-text strong { font-size: 11px; margin-bottom: 3px; }
        .nc-btn {
            width: 100%;
            justify-content: center;
            padding: 13px 24px;
            font-size: 11px;
            border-radius: 10px;
            letter-spacing: 1px;
        }
        .nc-btn svg { width: 14px; height: 14px; }
    }

    /* Маленькие телефоны (до 480px) */
    @media (max-width: 480px) {
        .no-company-page { padding: 20px 14px; }
        .nc-card { padding: 36px 20px; border-radius: 15px; }
        .nc-icon-wrap { width: 85px; height: 85px; margin-bottom: 18px; }
        .nc-icon-bg { border-radius: 19px; }
        .nc-icon-bg::before { border-radius: 21px; }
        .nc-icon-glow { inset: -12px; }
        .nc-icon-svg { width: 40px; height: 40px; }
        .nc-title { font-size: 19px; margin-bottom: 9px; letter-spacing: -0.3px; }
        .nc-desc { font-size: 11px; line-height: 1.45; margin-bottom: 22px; }
        .nc-divider { margin-bottom: 18px; max-width: 180px; gap: 8px; }
        .nc-divider-dot { width: 4px; height: 4px; }
        .nc-info-box { padding: 11px 13px; margin-bottom: 18px; border-radius: 9px; gap: 9px; }
        .nc-info-icon { width: 26px; height: 26px; border-radius: 7px; }
        .nc-info-icon svg { width: 13px; height: 13px; }
        .nc-info-text { font-size: 10px; line-height: 1.4; }
        .nc-info-text strong { font-size: 10px; margin-bottom: 2px; }
        .nc-btn { padding: 12px 22px; font-size: 10px; border-radius: 9px; letter-spacing: 0.9px; gap: 8px; }
        .nc-btn svg { width: 13px; height: 13px; }
    }

    /* Очень маленькие телефоны (до 380px) */
    @media (max-width: 380px) {
        .no-company-page { padding: 18px 12px; }
        .nc-card { padding: 32px 18px; border-radius: 14px; }
        .nc-icon-wrap { width: 80px; height: 80px; margin-bottom: 16px; }
        .nc-icon-bg { border-radius: 18px; }
        .nc-icon-bg::before { border-radius: 20px; }
        .nc-icon-glow { inset: -10px; }
        .nc-icon-svg { width: 38px; height: 38px; }
        .nc-title { font-size: 18px; margin-bottom: 8px; }
        .nc-desc { font-size: 11px; line-height: 1.4; margin-bottom: 20px; }
        .nc-divider { margin-bottom: 16px; max-width: 160px; gap: 7px; }
        .nc-info-box { padding: 10px 12px; margin-bottom: 16px; border-radius: 8px; gap: 8px; }
        .nc-info-icon { width: 24px; height: 24px; border-radius: 6px; }
        .nc-info-icon svg { width: 12px; height: 12px; }
        .nc-info-text { font-size: 10px; }
        .nc-btn { padding: 11px 20px; font-size: 10px; border-radius: 8px; letter-spacing: 0.8px; }
        .nc-btn svg { width: 12px; height: 12px; }
    }
</style>

<div class="no-company-page">

    {{-- Фоновые blob-ы --}}
    <div class="nc-blob nc-blob-1"></div>
    <div class="nc-blob nc-blob-2"></div>
    <div class="nc-blob nc-blob-3"></div>

    {{-- Частицы --}}
    <div class="nc-particles">
        <div class="nc-particle"></div>
        <div class="nc-particle"></div>
        <div class="nc-particle"></div>
        <div class="nc-particle"></div>
        <div class="nc-particle"></div>
        <div class="nc-particle"></div>
    </div>

    {{-- Карточка --}}
    <div class="nc-card">

        {{-- Иконка --}}
        <div class="nc-icon-wrap">
            <div class="nc-icon-glow"></div>
            <div class="nc-icon-bg"></div>
            <svg class="nc-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>

        {{-- Заголовок --}}
        <h1 class="nc-title">
            <span data-i18n="title">Нет </span><span class="accent" data-i18n="titleAccent">компании</span>
        </h1>

        {{-- Описание --}}
        <p class="nc-desc" data-i18n="desc">
            Вы не привязаны ни к одной компании. Обратитесь к администратору для добавления в команду.
        </p>

        {{-- Разделитель --}}
        <div class="nc-divider">
            <div class="nc-divider-line"></div>
            <div class="nc-divider-dot"></div>
            <div class="nc-divider-line"></div>
        </div>

        {{-- Инфо-блок --}}
        <div class="nc-info-box">
            <div class="nc-info-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="nc-info-text">
                <strong data-i18n="infoTitle">Что делать?</strong>
                <span data-i18n="infoDesc"> Свяжитесь с вашим администратором системы, чтобы он добавил вас в команду компании.</span>
            </div>
        </div>

        {{-- Кнопка --}}
        <a href="{{ url('/') }}" class="nc-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span data-i18n="homeBtn">На главную</span>
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const NO_COMPANY_TRANSLATIONS = {
            ru: {
                title: 'Нет ',
                titleAccent: 'компании',
                desc: 'Вы не привязаны ни к одной компании. Обратитесь к администратору для добавления в команду.',
                infoTitle: 'Что делать?',
                infoDesc: ' Свяжитесь с вашим администратором системы, чтобы он добавил вас в команду компании.',
                homeBtn: 'На главную'
            },
            tj: {
                title: 'Ширкат ',
                titleAccent: 'нест',
                desc: 'Шумо ба ягон ширкат пайваст нашудаед. Барои илова шудан ба даста бо администратор тамос гиред.',
                infoTitle: 'Чӣ бояд кард?',
                infoDesc: ' Бо администратори системаи худ тамос гиред, то шуморо ба дастаи ширкат илова кунад.',
                homeBtn: 'Ба саҳифаи асосӣ'
            },
            en: {
                title: 'No ',
                titleAccent: 'company',
                desc: 'You are not attached to any company. Contact your administrator to be added to the team.',
                infoTitle: 'What to do?',
                infoDesc: ' Contact your system administrator to be added to the company team.',
                homeBtn: 'Go Home'
            }
        };

        function applyNoCompanyTranslations(lang) {
            const dict = NO_COMPANY_TRANSLATIONS[lang] || NO_COMPANY_TRANSLATIONS.ru;

            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (dict[key] !== undefined) el.textContent = dict[key];
            });

            document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
                const key = el.getAttribute('data-i18n-placeholder');
                if (dict[key] !== undefined) el.setAttribute('placeholder', dict[key]);
            });

            document.querySelectorAll('[data-i18n-title]').forEach(el => {
                const key = el.getAttribute('data-i18n-title');
                if (dict[key] !== undefined) el.setAttribute('title', dict[key]);
            });
        }

        const blobs = document.querySelectorAll('.nc-blob');
        document.addEventListener('mousemove', (e) => {
            const x = (e.clientX / window.innerWidth - 0.5) * 30;
            const y = (e.clientY / window.innerHeight - 0.5) * 30;
            blobs.forEach((blob, i) => {
                const factor = (i + 1) * 0.4;
                if (i === 2) {
                    blob.style.transform = `translate(calc(-50% + ${x * factor}px), calc(-50% + ${y * factor}px))`;
                } else {
                    blob.style.transform = `translate(${x * factor}px, ${y * factor}px)`;
                }
            });
        });

        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applyNoCompanyTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applyNoCompanyTranslations(lang);
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applyNoCompanyTranslations(e.newValue);
            }
        });
    });
</script>

@endsection