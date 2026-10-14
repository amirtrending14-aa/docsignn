@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    /* ============================================================ */
    /* ===== ANALYTICS DASHBOARD (Tabler 3D Light) ===== */
    /* ============================================================ */
    .dash {
        font-family: var(--font-sans);
        position: relative;
        padding: 8px 0 40px;
        min-height: 100vh;
        color: var(--text);
    }

    /* Мягкие фоновые пятна (только для светлой темы) */
    html[data-theme="light"] .dash::before,
    html[data-theme="light"] .dash::after {
        content: "";
        position: fixed;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
        filter: blur(120px);
        opacity: 0.45;
    }
    html[data-theme="light"] .dash::before {
        top: -200px; right: -100px;
        width: 500px; height: 500px;
        background: radial-gradient(circle, rgba(var(--glow), 0.18) 0%, transparent 70%);
        animation: dashBlobFloat 22s ease-in-out infinite;
    }
    html[data-theme="light"] .dash::after {
        bottom: -200px; left: -100px;
        width: 600px; height: 600px;
        background: radial-gradient(circle, rgba(168, 85, 247, 0.12) 0%, transparent 70%);
        animation: dashBlobFloat 28s ease-in-out infinite reverse;
    }
    @keyframes dashBlobFloat {
        0%,100% { transform: translate(0,0) scale(1); }
        50%     { transform: translate(30px,-30px) scale(1.05); }
    }

    .dash > * { position: relative; z-index: 1; }

    /* Появление */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .mega-card { animation: fadeInUp 0.5s ease-out backwards; }
    .mega-card:nth-of-type(1) { animation-delay: 0.06s; }
    .mega-card:nth-of-type(2) { animation-delay: 0.15s; }
    .mega-card:nth-of-type(3) { animation-delay: 0.24s; }
    .mega-card:nth-of-type(4) { animation-delay: 0.33s; }

    /* === 3D CARDS (Tabler-style) === */
    .mega-card {
        background: var(--bg-1);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 22px;
        margin-bottom: 20px;
        position: relative;
        box-shadow: var(--shadow-card);
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* 3D-тень снизу (всегда видна, но усиливается на hover) */
    .mega-card::before {
        content: "";
        position: absolute;
        left: 10px; right: 10px; bottom: -8px;
        height: 12px;
        background: linear-gradient(180deg,
            rgba(15,23,42,0.06),
            rgba(15,23,42,0.02));
        border-radius: var(--radius);
        z-index: -1;
        filter: blur(10px);
        transition: all 0.35s ease;
    }
    /* Верхний "глянец" */
    .mega-card::after {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; height: 1px;
        background: linear-gradient(90deg,
            transparent 0%,
            rgba(255,255,255,0.9) 20%,
            rgba(255,255,255,0.9) 80%,
            transparent 100%);
        border-radius: var(--radius) var(--radius) 0 0;
        pointer-events: none;
        opacity: 0.7;
    }

    .mega-card:hover {
        transform: translateY(-4px);
        border-color: rgba(var(--glow), 0.45);
        box-shadow:
            0 1px 2px rgba(15,23,42,0.04),
            0 10px 24px -8px rgba(var(--glow), 0.22),
            0 6px 14px -4px rgba(15,23,42,0.08);
    }
    .mega-card:hover::before {
        bottom: -14px;
        opacity: 1.2;
    }

    /* === SECTION TITLE === */
    .section-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text);
        letter-spacing: -0.2px;
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
        margin: 4px 0 14px;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 1.2px;
        color: var(--muted);
    }
    .section-title .accent {
        width: 4px; height: 18px;
        border-radius: 2px;
        background: rgb(var(--glow));
        box-shadow: 0 0 0 3px rgba(var(--glow), 0.14);
        position: relative;
    }
    .section-title .accent::after {
        content: "";
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        width: 10px; height: 10px;
        border-radius: 50%;
        background: rgba(var(--glow), 0.35);
        animation: pulse 2.2s ease-in-out infinite;
    }
    @keyframes pulse {
        0%,100% { transform: translate(-50%,-50%) scale(1); opacity: 0.6; }
        50%     { transform: translate(-50%,-50%) scale(1.7); opacity: 0; }
    }
    .section-title span { font-weight: 700; }

    /* === TYPOGRAPHY === */
    .slabel {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 1.3px;
        color: var(--muted);
        font-weight: 700;
    }

    /* === BADGE LIVE === */
    .badge-live {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: var(--radius);
        background: rgba(var(--glow), 0.10);
        border: 1px solid rgba(var(--glow), 0.3);
        color: rgb(var(--glow));
        font-size: 10px;
        font-weight: 700;
        font-family: var(--font-mono);
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.2s ease;
    }
    .badge-live:hover { background: rgba(var(--glow), 0.15); }
    .badge-live i {
        font-size: 10px;
        animation: spin 2s linear infinite;
    }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    /* === 3D NAV BUTTONS === */
    .month-nav {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .month-nav-btn {
        background: var(--bg-1);
        border: 1px solid var(--line);
        color: var(--text);
        padding: 9px 18px;
        border-radius: var(--radius);
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        cursor: pointer;
        box-shadow: var(--shadow-card);
        transition: all 0.2s ease;
        position: relative;
        font-family: var(--font-sans);
    }
    .month-nav-btn::before {
        /* нижняя 3D-подложка */
        content: "";
        position: absolute;
        left: 6px; right: 6px; bottom: -4px;
        height: 5px;
        background: linear-gradient(180deg, rgba(15,23,42,0.08), rgba(15,23,42,0.02));
        border-radius: var(--radius);
        z-index: -1;
        filter: blur(4px);
        transition: all 0.2s ease;
    }
    .month-nav-btn:hover {
        border-color: rgba(var(--glow), 0.5);
        color: rgb(var(--glow));
        transform: translateY(-2px);
        box-shadow:
            0 6px 14px rgba(var(--glow), 0.18),
            0 1px 2px rgba(15,23,42,0.04);
    }
    .month-nav-btn:hover::before {
        bottom: -7px;
    }
    .month-nav-btn:active {
        transform: translateY(1px);
        box-shadow: var(--shadow-card);
    }
    .month-nav-btn:active::before { bottom: -2px; }
    .month-nav-btn i { font-size: 15px; }

    .month-current-label {
        background: linear-gradient(180deg, var(--bg-1) 0%, var(--bg-2) 100%);
        border: 1px solid var(--line);
        padding: 9px 22px;
        border-radius: var(--radius);
        font-weight: 700;
        font-size: 14px;
        color: var(--text);
        letter-spacing: 0.2px;
        box-shadow: var(--shadow-card);
        position: relative;
        font-family: var(--font-sans);
    }
    .month-current-label::before {
        content: "";
        position: absolute;
        top: -4px; left: 50%;
        transform: translateX(-50%);
        width: 26px; height: 3px;
        border-radius: 2px;
        background: rgb(var(--glow));
        box-shadow: 0 0 10px rgba(var(--glow), 0.55);
    }

    /* === SUMMARY MINI-CARDS === */
    .mini-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 20px;
    }
    .mini-card {
        background: var(--bg-1);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 16px;
        position: relative;
        box-shadow: var(--shadow-card);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    .mini-card::before {
        /* нижняя 3D-тень */
        content: "";
        position: absolute;
        left: 8px; right: 8px; bottom: -5px;
        height: 6px;
        background: linear-gradient(180deg, rgba(15,23,42,0.08), rgba(15,23,42,0.02));
        border-radius: var(--radius);
        z-index: -1;
        filter: blur(6px);
        transition: all 0.3s ease;
    }
    .mini-card::after {
        /* верхний глянец */
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.9), transparent);
        pointer-events: none;
    }
    .mini-card:hover {
        transform: translateY(-3px);
        border-color: rgba(var(--glow), 0.35);
        box-shadow:
            0 8px 18px rgba(var(--glow), 0.14),
            0 1px 2px rgba(15,23,42,0.04);
    }
    .mini-card:hover::before { bottom: -8px; }

    .mini-card .top-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .bignum {
        font-size: 24px;
        font-weight: 700;
        color: var(--text);
        line-height: 1;
        letter-spacing: -0.5px;
        transition: all 0.25s ease;
    }
    .mini-card:hover .bignum { color: rgb(var(--glow)); }

    /* ICON BOX — 3D */
    .icon-box {
        width: 34px; height: 34px;
        border-radius: var(--radius);
        display: grid; place-items: center;
        flex-shrink: 0;
        position: relative;
        transition: all 0.3s ease;
    }
    .icon-box::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: var(--radius);
        background: linear-gradient(180deg, rgba(255,255,255,0.3), rgba(255,255,255,0));
        pointer-events: none;
    }
    .mini-card:hover .icon-box {
        transform: scale(1.08) rotate(4deg);
    }
    .icon-box i {
        font-size: 16px;
        color: #fff;
        position: relative;
        z-index: 1;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.2));
    }

    .icon-box.blue   { background: linear-gradient(135deg, rgb(var(--glow)), rgb(var(--glow)) 70%, rgba(var(--glow),0.6)); box-shadow: 0 4px 10px rgba(var(--glow), 0.35); }
    .icon-box.purple { background: linear-gradient(135deg, #a855f7, #7c3aed); box-shadow: 0 4px 10px rgba(168,85,247,0.35); }
    .icon-box.green  { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 10px rgba(16,185,129,0.35); }
    .icon-box.orange { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 4px 10px rgba(245,158,11,0.35); }
    .icon-box.red    { background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 4px 10px rgba(239,68,68,0.35); }

    /* PROGRESS BAR */
    .progbg {
        height: 5px;
        background: var(--bg-2);
        border: 1px solid var(--line);
        border-radius: 99px;
        margin-top: 10px;
        overflow: hidden;
        position: relative;
        box-shadow: inset 0 1px 2px rgba(15,23,42,0.05);
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
        position: absolute; inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.55), transparent);
        animation: fillShimmer 2.2s infinite;
    }
    @keyframes fillShimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    .progbg .fill.blue   { background: linear-gradient(90deg, rgb(var(--glow)), rgba(var(--glow),0.75)); }
    .progbg .fill.green  { background: linear-gradient(90deg, #10b981, #059669); }
    .progbg .fill.red    { background: linear-gradient(90deg, #ef4444, #dc2626); }

    /* Legend */
    .legend-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 2px var(--bg-1), 0 0 0 3px currentColor;
        animation: dotPulse 2.2s ease-in-out infinite;
    }
    @keyframes dotPulse {
        0%,100% { transform: scale(1); }
        50%     { transform: scale(1.2); }
    }

    /* === CHART CONTAINERS === */
    #mainChart, #userChart { width: 100%; max-height: 280px; }
    #statusChart { width: 100%; max-height: 360px; }

    /* === STAT SUMMARY BLOCK === */
    .stat-block {
        background: linear-gradient(180deg, var(--bg-2), var(--bg-1));
        padding: 10px 18px;
        border-radius: var(--radius);
        border: 1px solid var(--line);
        box-shadow: inset 0 1px 2px rgba(15,23,42,0.04);
    }
    .stat-block .value {
        font-size: 20px;
        font-weight: 800;
        color: var(--text);
        letter-spacing: -0.5px;
    }

    /* === TOOLTIP (light + dark auto) === */
    .apexcharts-tooltip {
        background: var(--bg-1) !important;
        border: 1px solid var(--line) !important;
        border-radius: var(--radius) !important;
        box-shadow: 0 10px 28px rgba(15,23,42,0.14), 0 4px 10px rgba(15,23,42,0.08) !important;
    }
    .apexcharts-tooltip-title {
        background: var(--bg-2) !important;
        border-bottom: 1px solid var(--line) !important;
        color: var(--text) !important;
        font-weight: 700 !important;
    }
    .apexcharts-tooltip-text,
    .apexcharts-tooltip-y-group {
        color: var(--text) !important;
        font-weight: 600 !important;
    }

    /* === RESPONSIVE === */
    @media (max-width: 1200px) {
        .mini-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 992px) {
        .mega-card { padding: 18px; margin-bottom: 18px; }
        .section-title { font-size: 10px; letter-spacing: 1px; }
        .month-nav-btn { padding: 8px 16px; font-size: 12px; }
        .month-current-label { padding: 8px 18px; font-size: 13px; }
    }
    @media (max-width: 768px) {
        .dash { padding: 4px 0 30px; }
        .mega-card { padding: 16px; margin-bottom: 16px; }
        .mini-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .mini-card { padding: 14px; }
        .bignum { font-size: 20px; }
        .section-title { margin: 2px 0 10px; }
        .section-title .accent { width: 3px; height: 14px; }
        .badge-live { padding: 3px 8px; font-size: 9px; }
        .slabel { font-size: 9px; }
        .month-nav { gap: 8px; margin-bottom: 18px; }
        .month-nav-btn { padding: 7px 14px; font-size: 11px; gap: 5px; }
        .month-nav-btn i { font-size: 13px; }
        .month-current-label { padding: 7px 14px; font-size: 12px; }
        .icon-box { width: 30px; height: 30px; }
        .icon-box i { font-size: 14px; }
        #mainChart, #userChart { max-height: 240px; }
        #statusChart { max-height: 300px; }
        div[style*="height:380px"] { height: 280px !important; }
    }
    @media (max-width: 576px) {
        .mini-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
        .mini-card { padding: 12px; }
        .bignum { font-size: 18px; }
        .month-nav { gap: 6px; margin-bottom: 14px; }
        .month-nav-btn { padding: 7px 12px; font-size: 11px; }
        .month-current-label { padding: 7px 12px; font-size: 11px; }
        .badge-live { padding: 3px 7px; font-size: 8px; }
        #mainChart, #userChart { max-height: 200px; }
        #statusChart { max-height: 240px; }
        div[style*="height:380px"] { height: 220px !important; }
    }
    @media (max-width: 380px) {
        .mini-grid { grid-template-columns: 1fr; }
        .bignum { font-size: 17px; }
        .month-nav-btn { padding: 6px 10px; font-size: 10px; }
        .month-current-label { padding: 6px 10px; font-size: 10px; }
    }
</style>

<div class="dash">

    {{-- === НАВИГАЦИЯ ПО МЕСЯЦАМ === --}}
    <div class="month-nav">
        @php
            $prev = \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth();
            $next = \Carbon\Carbon::createFromDate($year, $month, 1)->addMonth();
            $isCurrentMonth = ($year == \Carbon\Carbon::now()->year && $month == \Carbon\Carbon::now()->month);
            $summaryCards = [
                'total' => max(0, (int) ($statusData['total'] ?? 0)),
                'processing' => max(0, (int) ($statusData['processing'] ?? 0)),
                'archive' => max(0, (int) ($statusData['archive'] ?? 0)),
                'rejected' => max(0, (int) ($statusData['rejected'] ?? 0)),
            ];
            $summaryTotal = $summaryCards['total'];
            $chartPercent = fn($value) => $summaryTotal > 0 ? min(100, max(0, round(($value / $summaryTotal) * 100, 1))) : 0;
        @endphp

        <a href="{{ url()->current() }}?month={{ $prev->month }}&year={{ $prev->year }}"
           class="month-nav-btn"
           title="{{ $prev->translatedFormat('F Y') }}">
            <i class="bi bi-chevron-left"></i>
            <span>{{ $prev->translatedFormat('M') }}</span>
        </a>

        <div class="month-current-label">
            {{ $startDate->translatedFormat('F Y') }}
        </div>

        <a href="{{ url()->current() }}?month={{ $next->month }}&year={{ $next->year }}"
           class="month-nav-btn {{ $isCurrentMonth ? 'opacity-50' : '' }}"
           @if($isCurrentMonth) style="pointer-events: none; opacity: 0.5;" @endif
           title="{{ $next->translatedFormat('F Y') }}">
            <span>{{ $next->translatedFormat('M') }}</span>
            <i class="bi bi-chevron-right"></i>
        </a>
    </div>

    {{-- === СВОДКА: МИНИ-КАРТОЧКИ 3D === --}}
    <div class="section-title">
        <div class="accent"></div>
        <span data-i18n="summary">Сводка</span>
    </div>

    <div class="mini-grid">
        <div class="mini-card">
            <div class="top-row">
                <div class="icon-box blue"><i class="bi bi-file-earmark-text"></i></div>
                <small class="slabel" data-i18n="total">Всего</small>
            </div>
            <div class="bignum">{{ $summaryCards['total'] }}</div>
            <div class="progbg"><div class="fill blue" style="width: 100%;"></div></div>
        </div>
        <div class="mini-card">
            <div class="top-row">
                <div class="icon-box purple"><i class="bi bi-hourglass-split"></i></div>
                <small class="slabel" data-i18n="on_signing">На подписании</small>
            </div>
            <div class="bignum">{{ $summaryCards['processing'] }}</div>
            <div class="progbg"><div class="fill blue" style="width: {{ $chartPercent($summaryCards['processing']) }}%;"></div></div>
        </div>
        <div class="mini-card">
            <div class="top-row">
                <div class="icon-box green"><i class="bi bi-check-circle"></i></div>
                <small class="slabel" data-i18n="archive">Подписано</small>
            </div>
            <div class="bignum">{{ $summaryCards['archive'] }}</div>
            <div class="progbg"><div class="fill green" style="width: {{ $chartPercent($summaryCards['archive']) }}%;"></div></div>
        </div>
        <div class="mini-card">
            <div class="top-row">
                <div class="icon-box red"><i class="bi bi-x-circle"></i></div>
                <small class="slabel" data-i18n="rejected">Отклонено</small>
            </div>
            <div class="bignum">{{ $summaryCards['rejected'] }}</div>
            <div class="progbg"><div class="fill red" style="width: {{ $chartPercent($summaryCards['rejected']) }}%;"></div></div>
        </div>
    </div>

    {{-- === АНАЛИТИКА ДОКУМЕНТООБОРОТА === --}}
    <div class="section-title">
        <div class="accent"></div>
        <span data-i18n="docAnalytics">Аналитика документооборота</span>
    </div>

    <div class="mega-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <div class="slabel" data-i18n="systemStats">Статистика системы</div>
                <h6 class="mb-0 fw-bold mt-1" style="color: var(--text); font-size: 15px; font-weight: 700;" data-i18n="incomingFlow">Поток входящих документов</h6>
            </div>
            <div class="badge-live">
                <i class="bi bi-arrow-repeat"></i> <span data-i18n="live">Live</span>
            </div>
        </div>
        <div id="mainChart"></div>
    </div>

    {{-- === АНАЛИТИКА ПОЛЬЗОВАТЕЛЕЙ === --}}
    <div class="section-title">
        <div class="accent" style="background: #a855f7; box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.14);"></div>
        <span data-i18n="userAnalytics">Аналитика пользователей</span>
    </div>

    <div class="mega-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <div class="slabel" data-i18n="baseActivity">Активность базы</div>
                <h6 class="mb-0 fw-bold mt-1" style="color: var(--text); font-size: 15px; font-weight: 700;" data-i18n="audienceDynamics">Динамика прироста аудитории</h6>
            </div>
            <div class="d-flex gap-3">
                <small class="d-flex align-items-center gap-1" style="color: rgb(var(--glow)); font-size: 11px; font-weight: 700;">
                    <span class="legend-dot" style="background: rgb(var(--glow)); color: rgb(var(--glow));"></span>
                    <span data-i18n="registrations">Регистрации</span>
                </small>
                <small class="d-flex align-items-center gap-1" style="color: #ef4444; font-size: 11px; font-weight: 700;">
                    <span class="legend-dot" style="background: #ef4444; color: #ef4444;"></span>
                    <span data-i18n="deletions">Удаления</span>
                </small>
            </div>
        </div>
        <div id="userChart"></div>
    </div>

    {{-- === СТАТУСЫ ДОКУМЕНТОВ === --}}
    <div class="section-title">
        <div class="accent" style="background: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.14);"></div>
        <span data-i18n="docStatuses">Статусы документов</span>
    </div>

    <div class="mega-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <div class="slabel" data-i18n="distByCat">Распределение по категориям</div>
                <h6 class="mb-0 fw-bold mt-1" style="color: var(--text); font-size: 15px; font-weight: 700;" data-i18n="docStatuses">Статусы документов</h6>
            </div>
            <div class="stat-block">
                <span class="slabel me-1" data-i18n="forMonth">За месяц:</span>
                <span class="value">{{ $summaryCards['total'] }}</span>
            </div>
        </div>
        <div style="height:380px; position:relative;">
            <div id="statusChart"></div>
        </div>
    </div>

</div>

<script src="{{ asset('assets/vendor/js/apexcharts.min.js') }}"></script>
<script>
    function initAnalysisCharts() {
        const chartRoot = document.querySelector('.dash');
        if (!chartRoot || chartRoot.dataset.chartsInitialized === 'true') return;
        chartRoot.dataset.chartsInitialized = 'true';

        if (typeof ApexCharts === 'undefined') {
            chartRoot.querySelectorAll('#mainChart, #userChart, #statusChart').forEach((element) => {
                element.textContent = 'Не удалось загрузить библиотеку графиков';
                element.style.minHeight = '120px';
                element.style.display = 'grid';
                element.style.placeItems = 'center';
            });
            console.error('Analysis charts unavailable: ApexCharts failed to load.');
            return;
        }

        const translations = {
            ru: {
                summary: "Сводка", total: "Всего",
                docAnalytics: "Аналитика документооборота",
                systemStats: "Статистика системы",
                incomingFlow: "Поток документов",
                userAnalytics: "Аналитика пользователей",
                baseActivity: "Активность базы",
                audienceDynamics: "Динамика прироста аудитории",
                registrations: "Регистрации",
                deletions: "Удаления",
                docStatuses: "Статусы документов",
                distByCat: "Распределение по категориям",
                forMonth: "За месяц:",
                documents: "ДОКУМЕНТОВ",
                live: "Live",
                incoming: "Входящие",
                outgoing: "Исходящие",
                processing: "На подписании",
                draft: "Черновики",
                rejected: "Отклоненные",
                archive: "Архив (Подписанные)",
                failed: "Ошибка генерации",
                generating: "Генерация",
                other: "Другие статусы"
            },
            tj: {
                summary: "Хулоса", total: "Ҳамагӣ",
                docAnalytics: "Таҳлили ҳуҷҷатгардонӣ",
                systemStats: "Омори система",
                incomingFlow: "Ҷараёни ҳуҷҷатҳо",
                userAnalytics: "Таҳлили корбарон",
                baseActivity: "Фаъолияти база",
                audienceDynamics: "Динамикаи афзоиши аудитория",
                registrations: "Бақайдгириҳо",
                deletions: "Ҳазфшудаҳо",
                docStatuses: "Ҳолатҳои ҳуҷҷатҳо",
                distByCat: "Тақсимшавӣ аз рӯи категорияҳо",
                forMonth: "Дар моҳ:",
                documents: "ҲУҶҶАТҲО",
                live: "Зинда",
                incoming: "Воридотӣ",
                outgoing: "Содиротӣ",
                processing: "Дар имзокунӣ",
                draft: "Пешнависҳо",
                rejected: "Радшуда",
                archive: "Архив (Имзошуда)",
                failed: "Хатои тавлид",
                generating: "Тавлид",
                other: "Ҳолатҳои дигар"
            },
            en: {
                summary: "Summary", total: "Total",
                docAnalytics: "Document Analytics",
                systemStats: "System Statistics",
                incomingFlow: "Document Flow",
                userAnalytics: "User Analytics",
                baseActivity: "Base Activity",
                audienceDynamics: "Audience Dynamics",
                registrations: "Registrations",
                deletions: "Deletions",
                docStatuses: "Document Statuses",
                distByCat: "Distribution by category",
                forMonth: "For month:",
                documents: "DOCUMENTS",
                live: "Live",
                incoming: "Incoming",
                outgoing: "Outgoing",
                processing: "Processing",
                draft: "Drafts",
                rejected: "Rejected",
                archive: "Archive (Signed)",
                failed: "Generation failed",
                generating: "Generating",
                other: "Other statuses"
            }
        };

        function getCurrentLang() {
            return localStorage.getItem('docsign_lang') || localStorage.getItem('app-lang') || 'ru';
        }
        
        function applyAnalyticsTranslations() {
            const lang = getCurrentLang();
            const t = translations[lang] || translations['ru'];
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (t[key] !== undefined) el.textContent = t[key];
            });
            return t;
        }
        
        let currentTranslations = applyAnalyticsTranslations();

        function getTheme() {
            return document.documentElement.dataset.theme || 'light';
        }
        function isDark() { return getTheme() === 'dark'; }
        
        function getGlowColor() {
            const rgb = getComputedStyle(document.documentElement).getPropertyValue('--glow').trim();
            return rgb ? `rgb(${rgb})` : 'rgb(79, 140, 255)';
        }
        function getMutedColor() { return isDark() ? '#9aa5b1' : '#626b76'; }
        function getGridColor() { return isDark() ? 'rgba(255,255,255,0.06)' : '#f1f5f9'; }
        function getStrokeColor() { return isDark() ? '#1a2028' : '#ffffff'; }

        const co = {
            chart: { type: 'area', height: 280, toolbar: { show: false }, zoom: { enabled: false }, foreColor: getMutedColor(), fontFamily: 'Inter, sans-serif' },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            markers: { size: 4, strokeWidth: 2, strokeColors: getStrokeColor() },
            grid: { borderColor: getGridColor(), strokeDashArray: 4, padding: { left: 10, right: 10 } },
            tooltip: { theme: getTheme(), x: { show: true }, style: { fontSize: '12px', fontFamily: 'Inter' } },
            xaxis: { axisBorder: { show: false }, axisTicks: { show: false }, labels: { style: { fontFamily: 'Inter' } } },
            yaxis: { labels: { style: { fontFamily: 'Inter' } } }
        };

        const dailyActivityData  = @json($dailyActivity->pluck('count'));
        const dailyActivityDates = @json($dailyActivity->pluck('date'));
        const userActivityReg    = @json($userActivity->pluck('reg'));
        const userActivityDel    = @json($userActivity->pluck('del'));
        const userActivityDates  = @json($userActivity->pluck('date'));

        const statusProcessing = {{ $statusData['processing'] ?? 0 }};
        const statusDraft      = {{ $statusData['draft'] ?? 0 }};
        const statusRejected   = {{ $statusData['rejected'] ?? 0 }};
        const statusArchive    = {{ $statusData['archive'] ?? 0 }};
        const statusFailed     = {{ $statusData['failed'] ?? 0 }};
        const statusGenerating = {{ $statusData['generating'] ?? 0 }};
        const statusOther      = {{ $statusData['other'] ?? 0 }};
        const statusTotal      = {{ $summaryCards['total'] }};

        // 1. ГРАФИК APEX: Поток документов
        const safeDailyData = Array.isArray(dailyActivityData) && dailyActivityData.length ? dailyActivityData : Array((dailyActivityDates.length || 31)).fill(0);
        const safeDailyLabels = Array.isArray(dailyActivityDates) && dailyActivityDates.length ? dailyActivityDates : Array.from({ length: 31 }, (_, i) => String(i + 1).padStart(2, '0'));
        const safeUserReg = Array.isArray(userActivityReg) && userActivityReg.length ? userActivityReg : Array((userActivityDates.length || 31)).fill(0);
        const safeUserDel = Array.isArray(userActivityDel) && userActivityDel.length ? userActivityDel : Array((userActivityDates.length || 31)).fill(0);
        const safeUserLabels = Array.isArray(userActivityDates) && userActivityDates.length ? userActivityDates : Array.from({ length: 31 }, (_, i) => String(i + 1).padStart(2, '0'));

        const mainChart = new ApexCharts(document.querySelector('#mainChart'), {
            ...co,
            series: [{ name: currentTranslations.incomingFlow, data: safeDailyData }],
            colors: [getGlowColor()],
            xaxis: { ...co.xaxis, categories: safeDailyLabels },
            fill: { type: 'gradient', gradient: { opacityFrom: .40, opacityTo: .04, stops: [0, 100] } }
        });
        mainChart.render();

        // 2. ГРАФИК APEX: Пользователи
        const userChart = new ApexCharts(document.querySelector('#userChart'), {
            ...co,
            series: [
                { name: currentTranslations.registrations, data: safeUserReg },
                { name: currentTranslations.deletions, data: safeUserDel }
            ],
            colors: [getGlowColor(), '#ef4444'],
            xaxis: { ...co.xaxis, categories: safeUserLabels },
            fill: { type: 'gradient', gradient: { opacityFrom: .35, opacityTo: 0, stops: [0, 100] } }
        });
        userChart.render();

        const statusValues = [statusProcessing, statusDraft, statusRejected, statusArchive, statusFailed, statusGenerating, statusOther];
        const statusLabels = [
            currentTranslations.processing,
            currentTranslations.draft,
            currentTranslations.rejected,
            currentTranslations.archive,
            currentTranslations.failed,
            currentTranslations.generating,
            currentTranslations.other
        ];
        const statusColors = ['#f59e0b', '#94a3b8', '#ef4444', '#10b981', '#7c3aed', '#0ea5e9', '#64748b'];
        const hasStatusData = statusValues.some((value) => Number(value) > 0);
        const statusChart = new ApexCharts(document.querySelector('#statusChart'), {
            chart: {
                type: 'donut',
                height: 380,
                fontFamily: 'Inter, sans-serif',
                foreColor: getMutedColor(),
                animations: { enabled: true }
            },
            series: hasStatusData ? statusValues : [],
            labels: statusLabels,
            colors: statusColors,
            stroke: { width: 3, colors: [getStrokeColor()] },
            dataLabels: { enabled: hasStatusData },
            plotOptions: {
                pie: {
                    expandOnClick: true,
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: false },
                            value: { show: false },
                            total: {
                                show: true,
                                label: currentTranslations.documents,
                                color: getMutedColor(),
                                formatter: () => String(statusTotal)
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                labels: { colors: getMutedColor() },
                markers: { radius: 8 },
                itemMargin: { horizontal: 10, vertical: 5 }
            },
            tooltip: { theme: getTheme() },
            noData: {
                text: 'Нет данных за выбранный месяц',
                align: 'center',
                verticalAlign: 'middle',
                style: { color: getMutedColor(), fontSize: '14px' }
            },
            responsive: [{
                breakpoint: 576,
                options: {
                    chart: { height: 300 },
                    legend: { itemMargin: { horizontal: 5, vertical: 3 } }
                }
            }]
        });
        statusChart.render();

        // Функция обновления языков
        function updateChartsLanguage(t) {
            mainChart.updateOptions({ series: [{ name: t.incomingFlow, data: safeDailyData }] });
            userChart.updateOptions({
                series: [
                    { name: t.registrations, data: safeUserReg },
                    { name: t.deletions, data: safeUserDel }
                ]
            });
            statusChart.updateOptions({
                labels: [
                    t.processing, t.draft, t.rejected, t.archive, t.failed, t.generating, t.other
                ],
                plotOptions: { pie: { donut: { labels: { total: { label: t.documents } } } } }
            });
        }

        // Слушаем смену темы и перерисовываем цвета
        function refreshThemeColors() {
            setTimeout(() => {
                const glow = getGlowColor();
                const muted = getMutedColor();
                const grid = getGridColor();
                const stroke = getStrokeColor();
                const theme = getTheme();

                mainChart.updateOptions({
                    colors: [glow],
                    chart: { foreColor: muted },
                    grid: { borderColor: grid },
                    markers: { strokeColors: stroke },
                    tooltip: { theme: theme }
                });
                userChart.updateOptions({
                    colors: [glow, '#ef4444'],
                    chart: { foreColor: muted },
                    grid: { borderColor: grid },
                    markers: { strokeColors: stroke },
                    tooltip: { theme: theme }
                });
                
                statusChart.updateOptions({
                    colors: statusColors,
                    chart: { foreColor: muted },
                    stroke: { colors: [stroke] },
                    legend: { labels: { colors: muted } },
                    tooltip: { theme: theme },
                    plotOptions: { pie: { donut: { labels: { total: { label: currentTranslations.documents, color: muted } } } } }
                });
            }, 50);
        }

        // MutationObserver на смену data-theme
        const observer = new MutationObserver(() => refreshThemeColors());
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

        // При смене языка
        window.addEventListener('docsign:lang-changed', function() {
            currentTranslations = applyAnalyticsTranslations();
            updateChartsLanguage(currentTranslations);
            refreshThemeColors();
        });

        // Обновляем цвета при смене glow (ambient)
        setInterval(() => {
            const current = mainChart.opts.colors?.[0];
            const glow = getGlowColor();
            if (current && current !== glow) refreshThemeColors();
        }, 1500);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAnalysisCharts, { once: true });
    } else {
        initAnalysisCharts();
    }
</script>

@endsection