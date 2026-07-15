@extends('layouts.admin')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    .dash {
        font-family: 'Inter', sans-serif;
        position: relative;
        padding: 40px 24px 60px;
        min-height: 100vh;
    }

    /* === ФОНОВЫЕ BLOB-Ы === */
    .dash-blob {
        position: fixed;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
        filter: blur(120px);
        opacity: 0.4;
    }

    .dash-blob-1 {
        top: -150px;
        left: -150px;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(var(--glow), 0.4) 0%, transparent 70%);
        animation: dashBlobFloat 20s ease-in-out infinite;
    }

    .dash-blob-2 {
        bottom: -150px;
        right: -150px;
        width: 700px;
        height: 700px;
        background: radial-gradient(circle, rgba(168, 85, 247, 0.35) 0%, transparent 70%);
        animation: dashBlobFloat 25s ease-in-out infinite reverse;
    }

    .dash-blob-3 {
        top: 40%;
        left: 60%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(236, 72, 153, 0.28) 0%, transparent 70%);
        animation: dashBlobFloat3 30s ease-in-out infinite;
    }

    @keyframes dashBlobFloat {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(40px, -40px) scale(1.05); }
    }

    @keyframes dashBlobFloat3 {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(-40px, 40px) scale(1.08); }
    }

    .dash > .container-fluid {
        position: relative;
        z-index: 1;
    }

    /* === АНИМАЦИЯ ПОЯВЛЕНИЯ === */
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

    .mega-card {
        animation: fadeInUp 0.6s ease-out backwards;
    }

    .mega-card:nth-of-type(1) { animation-delay: 0.1s; }
    .mega-card:nth-of-type(2) { animation-delay: 0.25s; }
    .mega-card:nth-of-type(3) { animation-delay: 0.4s; }
    .mega-card:nth-of-type(4) { animation-delay: 0.55s; }

    /* === GLASSMORPHISM CARD === */
    .acard {
        background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02));
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 24px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
    }

    .acard::before {
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: var(--radius);
        padding: 1px;
        background: linear-gradient(135deg, rgba(var(--glow),0.5), transparent 40%, transparent 60%, rgba(var(--glow),0.3));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0.7;
        pointer-events: none;
        transition: opacity 0.4s ease;
    }

    .acard::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(var(--glow), 0.6), transparent);
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .acard:hover {
        transform: translateY(-5px);
        border-color: rgba(var(--glow), 0.4);
        box-shadow: 0 20px 50px rgba(var(--glow), 0.2), 0 0 30px rgba(var(--glow), 0.1);
    }

    .acard:hover::before { opacity: 1; }
    .acard:hover::after { opacity: 1; }

    /* === MEGA CARD === */
    .mega-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.05), rgba(255,255,255,0.015));
        border: 1px solid var(--line);
        border-radius: calc(var(--radius) * 1.3);
        padding: 28px;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(28px);
        -webkit-backdrop-filter: blur(28px);
        margin-bottom: 28px;
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .mega-card::before {
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: calc(var(--radius) * 1.3);
        padding: 1px;
        background: linear-gradient(135deg, rgba(var(--glow),0.35), transparent 35%, transparent 65%, rgba(var(--glow),0.25));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0.6;
        pointer-events: none;
    }

    .mega-card:hover {
        border-color: rgba(var(--glow), 0.25);
        box-shadow: 0 30px 70px rgba(0,0,0,0.35), 0 0 40px rgba(var(--glow), 0.08);
    }

    .mega-card .stat-highlight {
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(var(--glow), 0.04) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.5s ease;
        pointer-events: none;
    }

    .mega-card:hover .stat-highlight { opacity: 1; }

    /* Мини-карточки */
    .mini-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02));
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 20px;
        height: 100%;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }

    .mini-card:hover {
        transform: translateY(-4px);
        border-color: rgba(var(--glow), 0.4);
        box-shadow: 0 15px 40px rgba(var(--glow), 0.15), 0 0 20px rgba(var(--glow), 0.08);
    }

    .mini-card .stat-highlight {
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(var(--glow), 0.05) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.4s ease;
        pointer-events: none;
    }

    .mini-card:hover .stat-highlight { opacity: 1; }

    /* Разделитель */
    .mega-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--line), transparent);
        margin: 24px 0;
        position: relative;
    }

    .mega-divider::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 40px;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(var(--glow), 0.8), transparent);
    }

    /* === SECTION TITLE === */
    .slabel {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 1.3px;
        color: var(--muted);
        font-weight: 800;
    }

    .bignum {
        font-size: 28px;
        font-weight: 900;
        color: var(--text);
        line-height: 1.1;
        letter-spacing: -0.8px;
        transition: all 0.3s ease;
    }

    .mini-card:hover .bignum { transform: scale(1.03); }

    .section-title {
        font-size: 22px;
        font-weight: 900;
        color: var(--text);
        letter-spacing: -0.5px;
        display: flex;
        align-items: center;
        gap: 14px;
        position: relative;
        margin-bottom: 24px;
    }

    .section-title .accent {
        width: 5px;
        height: 32px;
        border-radius: 4px;
        background: linear-gradient(180deg, rgba(var(--glow), 1), rgba(var(--glow), 0.3));
        box-shadow: 0 0 16px rgba(var(--glow), 0.8);
        position: relative;
    }

    .section-title .accent::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(var(--glow), 0.3);
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.5; }
        50% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; }
    }

    /* === ICON BOX === */
    .icon-box {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        flex-shrink: 0;
        position: relative;
        transition: all 0.4s ease;
    }

    .icon-box::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 10px;
        background: inherit;
        filter: blur(8px);
        opacity: 0.5;
        z-index: -1;
    }

    .mini-card:hover .icon-box { transform: scale(1.1) rotate(5deg); }

    .icon-box.blue {
        background: linear-gradient(135deg, rgba(var(--glow), 1), rgba(var(--glow), 0.5));
        box-shadow: 0 8px 24px rgba(var(--glow), 0.6), inset 0 0 12px rgba(255,255,255,0.3);
    }

    .icon-box.purple {
        background: linear-gradient(135deg, rgba(168, 85, 247, 1), rgba(168, 85, 247, 0.5));
        box-shadow: 0 8px 24px rgba(168, 85, 247, 0.6), inset 0 0 12px rgba(255,255,255,0.3);
    }

    .icon-box.green {
        background: linear-gradient(135deg, rgba(16, 185, 129, 1), rgba(16, 185, 129, 0.5));
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.6), inset 0 0 12px rgba(255,255,255,0.3);
    }

    .icon-box.orange {
        background: linear-gradient(135deg, rgba(245, 158, 11, 1), rgba(245, 158, 11, 0.5));
        box-shadow: 0 8px 24px rgba(245, 158, 11, 0.6), inset 0 0 12px rgba(255,255,255,0.3);
    }

    .icon-box.red {
        background: linear-gradient(135deg, rgba(239, 68, 68, 1), rgba(239, 68, 68, 0.5));
        box-shadow: 0 8px 24px rgba(239, 68, 68, 0.6), inset 0 0 12px rgba(255,255,255,0.3);
    }

    .icon-box i {
        font-size: 20px;
        color: #ffffff;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
    }

    /* === PROGRESS BAR === */
    .progbg {
        height: 6px;
        background: rgba(255,255,255,0.06);
        border-radius: 99px;
        margin-top: 12px;
        overflow: hidden;
        position: relative;
    }

    .progbg::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .progbg .fill {
        height: 100%;
        border-radius: 99px;
        transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .progbg .fill::after {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        animation: fillShimmer 2s infinite;
    }

    @keyframes fillShimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .progbg .fill.blue {
        background: linear-gradient(90deg, rgba(var(--glow), 1), rgba(var(--glow), 0.6));
        box-shadow: 0 0 16px rgba(var(--glow), 0.8);
    }

    .progbg .fill.green {
        background: linear-gradient(90deg, #10b981, #059669);
        box-shadow: 0 0 16px rgba(16, 185, 129, 0.8);
    }

    .progbg .fill.red {
        background: linear-gradient(90deg, #ef4444, #dc2626);
        box-shadow: 0 0 16px rgba(239, 68, 68, 0.8);
    }

    /* === BADGE LIVE === */
    .badge-live {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 16px;
        border-radius: 99px;
        background: rgba(var(--glow), 0.15);
        border: 1px solid rgba(var(--glow), 0.4);
        color: rgba(var(--glow), 1);
        font-size: 11px;
        font-weight: 700;
        font-family: 'JetBrains Mono', monospace;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 0 20px rgba(var(--glow), 0.3);
        animation: badgePulse 2s ease-in-out infinite;
    }

    @keyframes badgePulse {
        0%, 100% { box-shadow: 0 0 20px rgba(var(--glow), 0.3); }
        50% { box-shadow: 0 0 30px rgba(var(--glow), 0.5); }
    }

    .badge-live i {
        font-size: 11px;
        animation: spin 2s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* === LEGEND === */
    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 10px currentColor;
        animation: dotPulse 2s ease-in-out infinite;
    }

    @keyframes dotPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }

    /* === CHART CONTAINERS === */
    #mainChart, #userChart {
        width: 100%;
        max-height: 280px;
    }

    #statusChart {
        width: 100%;
        max-height: 360px;
    }

    /* === APEXCHARTS TOOLTIP === */
    .apexcharts-tooltip {
        background: rgba(10, 13, 20, 0.95) !important;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(var(--glow), 0.4) !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6), 0 0 30px rgba(var(--glow), 0.3) !important;
    }

    .apexcharts-tooltip-title {
        background: rgba(255,255,255,0.06) !important;
        border-bottom: 1px solid rgba(255,255,255,0.12) !important;
        color: #ffffff !important;
        font-weight: 700 !important;
    }

    .apexcharts-tooltip-text,
    .apexcharts-tooltip-y-group {
        color: #ffffff !important;
        font-weight: 600 !important;
    }

    /* === RESPONSIVE === */

    /* Большие ноутбуки и десктопы (до 1200px) */
    @media (max-width: 1200px) {
        .dash-blob-1 { width: 500px; height: 500px; }
        .dash-blob-2 { width: 600px; height: 600px; }
        .dash-blob-3 { width: 450px; height: 450px; }
    }

    /* Маленькие ноутбуки и большие планшеты (до 992px) */
    @media (max-width: 992px) {
        .dash { padding: 30px 20px 50px; }
        .mega-card { padding: 24px; margin-bottom: 24px; }
        .section-title { font-size: 20px; gap: 12px; }
        .dash-blob-1 { width: 400px; height: 400px; }
        .dash-blob-2 { width: 500px; height: 500px; }
        .dash-blob-3 { width: 350px; height: 350px; }
        .dash-blob { filter: blur(100px); }
    }

    /* Планшеты (до 768px) */
    @media (max-width: 768px) {
        .dash { padding: 24px 16px 40px; }
        .mega-card { padding: 18px; margin-bottom: 20px; border-radius: calc(var(--radius) * 1.1); }
        .mini-card { padding: 16px; }
        .bignum { font-size: 22px; }
        .section-title { font-size: 18px; gap: 12px; margin-bottom: 18px; }
        .section-title .accent { width: 4px; height: 26px; }
        .badge-live { padding: 5px 12px; font-size: 10px; gap: 5px; }
        .slabel { font-size: 9px; letter-spacing: 1.1px; }

        #mainChart, #userChart { max-height: 250px; }
        #statusChart { max-height: 320px; }
        div[style*="height:380px"] { height: 300px !important; }

        .dash-blob-1 { width: 300px; height: 300px; }
        .dash-blob-2 { width: 400px; height: 400px; }
        .dash-blob-3 { width: 280px; height: 280px; }
        .dash-blob { filter: blur(80px); opacity: 0.3; }
    }

    /* Телефоны (до 576px) */
    @media (max-width: 576px) {
        .dash { padding: 16px 12px 30px; }
        .mega-card { padding: 14px; margin-bottom: 16px; border-radius: calc(var(--radius) * 1); }
        .mini-card { padding: 12px; border-radius: 10px; }
        .bignum { font-size: 20px; letter-spacing: -0.5px; }
        .section-title { font-size: 16px; gap: 10px; margin-bottom: 14px; }
        .section-title .accent { width: 4px; height: 22px; border-radius: 3px; }
        .badge-live { padding: 4px 10px; font-size: 9px; gap: 4px; letter-spacing: 0.8px; }
        .badge-live i { font-size: 9px; }
        .slabel { font-size: 9px; letter-spacing: 1px; }
        .mega-divider { margin: 16px 0; }

        .icon-box { width: 36px; height: 36px; border-radius: 8px; }
        .icon-box i { font-size: 16px; }

        #mainChart, #userChart { max-height: 220px; }
        #statusChart { max-height: 260px; }
        div[style*="height:380px"] { height: 240px !important; }

        .dash-blob { display: none; }

        .d-flex.gap-3 { gap: 0.5rem !important; }
        .apexcharts-legend { font-size: 10px !important; }

        h6[style*="font-size: 16px"] { font-size: 14px !important; }
    }

    /* Очень маленькие телефоны (до 380px) */
    @media (max-width: 380px) {
        .dash { padding: 12px 8px 24px; }
        .mega-card { padding: 12px; }
        .section-title { font-size: 15px; gap: 8px; }
        .bignum { font-size: 18px; }
        .badge-live { padding: 3px 8px; font-size: 8px; }
        #mainChart, #userChart { max-height: 200px; }
        #statusChart { max-height: 220px; }
        div[style*="height:380px"] { height: 220px !important; }
        .slabel { font-size: 8px; }
    }
</style>

<div class="dash">
    {{-- Фоновые blob-ы --}}



    {{-- === СЕКЦИЯ: АНАЛИТИКА ДОКУМЕНТООБОРОТА === --}}
    <div class="section-title">
        <div class="accent"></div>
        <span data-i18n="docAnalytics">Аналитика документооборота</span>
    </div>

    <div class="mega-card">


        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <div class="slabel" data-i18n="systemStats">Статистика системы</div>
                <h6 class="mb-0 fw-bold mt-1" style="color:var(--text); font-size: 16px; font-weight: 800;" data-i18n="incomingFlow">Поток входящих документов</h6>
            </div>
            <div class="badge-live">
                <i class="bi bi-arrow-repeat"></i> <span data-i18n="live">Live</span>
            </div>
        </div>
        <div id="mainChart"></div>
    </div>

    {{-- === СЕКЦИЯ: АНАЛИТИКА ПОЛЬЗОВАТЕЛЕЙ === --}}
    <div class="section-title">
        <div class="accent" style="background: linear-gradient(180deg, #a855f7, rgba(168, 85, 247, 0.3)); box-shadow: 0 0 16px rgba(168, 85, 247, 0.8);"></div>
        <span data-i18n="userAnalytics">Аналитика пользователей</span>
    </div>

    <div class="mega-card">
        <div class="stat-highlight" style="background: radial-gradient(circle, rgba(168, 85, 247, 0.04) 0%, transparent 70%);"></div>



        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <div class="slabel" data-i18n="baseActivity">Активность базы</div>
                <h6 class="mb-0 fw-bold mt-1" style="color:var(--text); font-size: 16px; font-weight: 800;" data-i18n="audienceDynamics">Динамика прироста аудитории</h6>
            </div>
            <div class="d-flex gap-3">
                <small class="d-flex align-items-center gap-1" style="color: rgba(var(--glow), 1); font-size: 11px; font-weight: 600;">
                    <span class="legend-dot" style="background: rgba(var(--glow), 1); color: rgba(var(--glow), 1);"></span>
                    <span data-i18n="registrations">Регистрации</span>
                </small>
                <small class="d-flex align-items-center gap-1" style="color:#ef4444; font-size: 11px; font-weight: 600;">
                    <span class="legend-dot" style="background:#ef4444; color:#ef4444;"></span>
                    <span data-i18n="deletions">Удаления</span>
                </small>
            </div>
        </div>
        <div id="userChart"></div>
    </div>

    {{-- === СЕКЦИЯ: СТАТУСЫ ДОКУМЕНТОВ === --}}
    <div class="section-title">
        <div class="accent" style="background: linear-gradient(180deg, #10b981, rgba(16, 185, 129, 0.3)); box-shadow: 0 0 16px rgba(16, 185, 129, 0.8);"></div>
        <span data-i18n="docStatuses">Статусы документов</span>
    </div>

    <div class="mega-card">
        <div class="stat-highlight" style="background: radial-gradient(circle, rgba(16, 185, 129, 0.04) 0%, transparent 70%);"></div>
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <div class="slabel" data-i18n="distByCat">Распределение по категориям</div>
                <h6 class="mb-0 fw-bold mt-1" style="color:var(--text); font-size: 16px; font-weight: 800;" data-i18n="docStatuses">Статусы документов</h6>
            </div>
            <div style="background: rgba(255,255,255,0.06); padding: 8px 18px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <span class="slabel me-1" data-i18n="forMonth">За месяц:</span>
                <span class="fw-bold" style="color:var(--text); font-size: 17px; font-weight: 800;">{{ array_sum($statusData) }}</span>
            </div>
        </div>
        <div style="height:380px; position:relative;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ===== ПЕРЕВОДЫ / TRANSLATIONS / ТАРҶУМАҲО =====
        const translations = {
            ru: {
                docAnalytics: "Аналитика документооборота",
                systemStats: "Статистика системы",
                incomingFlow: "Поток входящих документов",
                totalDocs: "Всего документов",
                processedFiles: "обработано файлов",
                signedRate: "Доля подписанных",
                rejectedTitle: "Отказы (Rejected)",
                requireAttention: "требуют внимания",
                userAnalytics: "Аналитика пользователей",
                baseActivity: "Активность базы",
                audienceDynamics: "Динамика прироста аудитории",
                registrations: "Регистрации",
                deletions: "Удаления",
                usersCount: "Пользователи",
                activeProfiles: "активных профилей",
                new30Days: "Новые (30 дней)",
                churnRate: "Churn Rate",
                churnDesc: "коэффициент оттока",
                docStatuses: "Статусы документов",
                distByCat: "Распределение по категориям",
                forMonth: "За месяц:",
                incoming: "Входящие",
                acceptedBySys: "принято системой",
                outgoing: "Исходящие",
                sentByYou: "отправлено вами",
                signed: "Подписанные",
                successDone: "завершено успешно",
                pending: "В очереди",
                waitAction: "ожидают действия",
                documents: "ДОКУМЕНТОВ",
                live: "Live"
            },
            tj: {
                docAnalytics: "Таҳлили ҳуҷҷатгардонӣ",
                systemStats: "Омори система",
                incomingFlow: "Ҷараёни ҳуҷҷатҳои воридотӣ",
                totalDocs: "Ҳамаи ҳуҷҷатҳо",
                processedFiles: "файлҳо коркард шуданд",
                signedRate: "Ҳиссаи имзошуда",
                rejectedTitle: "Радшуда",
                requireAttention: "диққатро талаб мекунад",
                userAnalytics: "Таҳлили корбарон",
                baseActivity: "Фаъолияти база",
                audienceDynamics: "Динамикаи афзоиши аудитория",
                registrations: "Бақайдгириҳо",
                deletions: "Ҳазфшудаҳо",
                usersCount: "Корбарон",
                activeProfiles: "профилҳои фаъол",
                new30Days: "Нав (30 рӯз)",
                churnRate: "Churn Rate",
                churnDesc: "коэффисиенти хориҷшавӣ",
                docStatuses: "Ҳолатҳои ҳуҷҷатҳо",
                distByCat: "Тақсимшавӣ аз рӯи категорияҳо",
                forMonth: "Дар моҳ:",
                incoming: "Воридотӣ",
                acceptedBySys: "аз ҷониби система қабул шуд",
                outgoing: "Содиротӣ",
                sentByYou: "аз ҷониби шумо фиристода шуд",
                signed: "Имзошуда",
                successDone: "бомуваффақият анҷом ёфт",
                pending: "Дар навбат",
                waitAction: "интизори амал",
                documents: "ҲУҶҶАТҲО",
                live: "Зинда"
            },
            en: {
                docAnalytics: "Document Analytics",
                systemStats: "System Statistics",
                incomingFlow: "Incoming Document Flow",
                totalDocs: "Total Documents",
                processedFiles: "processed files",
                signedRate: "Signed Rate",
                rejectedTitle: "Rejected",
                requireAttention: "require attention",
                userAnalytics: "User Analytics",
                baseActivity: "Base Activity",
                audienceDynamics: "Audience Dynamics",
                registrations: "Registrations",
                deletions: "Deletions",
                usersCount: "Users",
                activeProfiles: "active profiles",
                new30Days: "New (30 days)",
                churnRate: "Churn Rate",
                churnDesc: "churn coefficient",
                docStatuses: "Document Statuses",
                distByCat: "Distribution by category",
                forMonth: "For month:",
                incoming: "Incoming",
                acceptedBySys: "accepted by system",
                outgoing: "Outgoing",
                sentByYou: "sent by you",
                signed: "Signed",
                successDone: "successfully completed",
                pending: "Pending",
                waitAction: "waiting for action",
                documents: "DOCUMENTS",
                live: "Live"
            }
        };

        // ===== Получение текущего языка (синхронизация с layout) =====
        function getCurrentLang() {
            return localStorage.getItem('docsign_lang')
                || localStorage.getItem('app-lang')
                || 'ru';
        }

        // ===== Применение переводов к HTML =====
        function applyAnalyticsTranslations() {
            const lang = getCurrentLang();
            const t = translations[lang] || translations['ru'];

            // Обновляем все элементы с data-i18n
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (t[key] !== undefined) {
                    el.textContent = t[key];
                }
            });

            return t;
        }

        let currentTranslations = applyAnalyticsTranslations();

        // ===== Определяем тему (dark/light) =====
        const dk = () => document.body.classList.contains('dark') ||
            document.documentElement.classList.contains('dark') ||
            (window.matchMedia && window.matchMedia('(prefers-color-scheme:dark)').matches);

        const isDark = dk();
        const fc = isDark ? '#94a3b8' : '#64748b';

        // ===== Общие настройки ApexCharts =====
        const co = {
            chart: { type: 'area', height: 280, toolbar: { show: false }, zoom: { enabled: false }, foreColor: fc },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            markers: { size: 4, strokeWidth: 2, strokeColors: isDark ? '#151d30' : '#fff' },
            grid: { borderColor: isDark ? 'rgba(255,255,255,.05)' : '#f1f5f9', strokeDashArray: 4 },
            tooltip: {
                theme: isDark ? 'dark' : 'light',
                x: { show: true },
                style: { fontSize: '12px' }
            },
            xaxis: { axisBorder: { show: false }, axisTicks: { show: false } }
        };

        // ===== Данные из Laravel =====
        const dailyActivityData = @json($dailyActivity->pluck('count'));
        const dailyActivityDates = @json($dailyActivity->pluck('date'));
        const userActivityReg = @json($userActivity->pluck('reg'));
        const userActivityDel = @json($userActivity->pluck('del'));
        const userActivityDates = @json($userActivity->pluck('date'));

        const statusIncoming = {{ $statusData['incoming'] ?? 0 }};
        const statusOutgoing = {{ $statusData['outgoing'] ?? 0 }};
        const statusSigned = {{ $statusData['signed'] ?? 0 }};
        const statusPending = {{ $statusData['pending'] ?? 0 }};

        // ===== СОЗДАЁМ ГРАФИКИ И СОХРАНЯЕМ ССЫЛКИ =====

        // 1. Main Chart (ApexCharts)
        const mainChart = new ApexCharts(document.querySelector('#mainChart'), {
            ...co,
            series: [{ name: currentTranslations.incoming || 'Incoming', data: dailyActivityData }],
            colors: ['#3b82f6'],
            xaxis: { categories: dailyActivityDates },
            fill: { type: 'gradient', gradient: { opacityFrom: .45, opacityTo: .06 } }
        });
        mainChart.render();

        // 2. User Chart (ApexCharts)
        const userChart = new ApexCharts(document.querySelector('#userChart'), {
            ...co,
            series: [
                { name: currentTranslations.registrations || 'Registrations', data: userActivityReg },
                { name: currentTranslations.deletions || 'Deletions', data: userActivityDel }
            ],
            colors: ['#3b82f6', '#ef4444'],
            xaxis: { categories: userActivityDates },
            fill: { type: 'gradient', gradient: { opacityFrom: .35, opacityTo: 0 } }
        });
        userChart.render();

        // 3. Status Chart (Chart.js)
        const centerText = {
            id: 'centerText',
            afterDraw(c) {
                const { ctx, width, height } = c;
                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                const textColor = isDark ? '#ffffff' : '#0f172a';
                const subTextColor = isDark ? '#94a3b8' : '#64748b';
                ctx.font = '800 11px Inter, sans-serif';
                ctx.fillStyle = subTextColor;
                ctx.fillText(currentTranslations.documents || 'DOCUMENTS', width / 2, height / 2 + 25);
                ctx.restore();
            }
        };

        const statusChart = new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: [
                    currentTranslations.incoming,
                    currentTranslations.outgoing,
                    currentTranslations.signed,
                    currentTranslations.pending
                ],
                datasets: [{
                    data: [statusIncoming, statusOutgoing, statusSigned, statusPending],
                    borderWidth: 0,
                    hoverOffset: 18,
                    borderRadius: 14,
                    spacing: 6,
                    backgroundColor: ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b']
                }]
            },
            plugins: [centerText],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 24,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            color: fc,
                            font: { size: 12, weight: '700' }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });

        // ===== ФУНКЦИЯ ОБНОВЛЕНИЯ ГРАФИКОВ ПРИ СМЕНЕ ЯЗЫКА =====
        function updateChartsLanguage(t) {
            // Обновляем ApexCharts (mainChart)
            mainChart.updateOptions({
                series: [{ name: t.incoming || 'Incoming', data: dailyActivityData }]
            });

            // Обновляем ApexCharts (userChart)
            userChart.updateOptions({
                series: [
                    { name: t.registrations || 'Registrations', data: userActivityReg },
                    { name: t.deletions || 'Deletions', data: userActivityDel }
                ]
            });

            // Обновляем Chart.js (statusChart)
            statusChart.data.labels = [
                t.incoming,
                t.outgoing,
                t.signed,
                t.pending
            ];
            statusChart.update();

            // Обновляем центральный текст doughnut (перерисовка)
            statusChart.ctx.canvas.dispatchEvent(new Event('render'));
        }

        // ===== ПАРАЛЛАКС ДЛЯ BLOB-ОВ =====
        const blobs = document.querySelectorAll('.dash-blob');
        document.addEventListener('mousemove', (e) => {
            const x = (e.clientX / window.innerWidth - 0.5) * 30;
            const y = (e.clientY / window.innerHeight - 0.5) * 30;
            blobs.forEach((blob, i) => {
                const factor = (i + 1) * 0.4;
                blob.style.transform = `translate(${x * factor}px, ${y * factor}px)`;
            });
        });

        // ===== СЛУШАТЕЛЬ СМЕНЫ ЯЗЫКА ИЗ LAYOUT =====
        window.addEventListener('docsign:lang-changed', function(e) {
            // Синхронизируем localStorage
            if (e.detail && e.detail.lang) {
                localStorage.setItem('docsign_lang', e.detail.lang);
                localStorage.setItem('app-lang', e.detail.lang);
            }

            // Обновляем HTML-тексты
            currentTranslations = applyAnalyticsTranslations();

            // Обновляем графики
            updateChartsLanguage(currentTranslations);
        });
    });
</script>

@endsection