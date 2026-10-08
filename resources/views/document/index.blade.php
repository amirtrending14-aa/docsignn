@extends('layouts.admin')

@section('content')
<style>
    .sign-registry,
    .sign-registry * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
        box-sizing: border-box;
    }

    .sign-registry { color: var(--text); padding: 22px 24px 40px; }

    /* ===================== HEADER ===================== */
    .sr-header { margin-bottom: 22px; }
    .sr-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }
    .sr-title-row { display: flex; align-items: center; gap: 13px; }
    .sr-accent {
        width: 5px; height: 28px; border-radius: 3px;
        background: linear-gradient(180deg, rgba(var(--glow), 1), rgba(var(--glow), 0.5));
        box-shadow: 0 0 12px rgba(var(--glow), 0.5); flex-shrink: 0;
    }
    .sr-header h1 { margin: 0; font-size: 25px; font-weight: 800; letter-spacing: -0.6px; line-height: 1.1; }

    .sr-btn-new {
        appearance: none; border: 0;
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        padding: 10px 18px; border-radius: 10px; cursor: pointer; text-decoration: none;
        background: rgba(var(--glow), 1); color: #0a0d14;
        font-size: 13px; font-weight: 700; letter-spacing: 0.1px;
        box-shadow: 0 4px 14px rgba(var(--glow), 0.35), inset 0 1px 0 rgba(255,255,255,0.25);
        transition: all .22s ease; flex-shrink: 0;
    }
    .sr-btn-new:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(var(--glow), 0.45), inset 0 1px 0 rgba(255,255,255,0.3); filter: brightness(1.05); }
    .sr-btn-new:active { transform: translateY(0) scale(0.98); }
    .sr-btn-new svg { width: 14px; height: 14px; stroke-width: 3; }

    .sr-status-line { display: flex; align-items: center; gap: 8px; margin-left: 18px; font-size: 12.5px; color: var(--muted); font-weight: 500; }
    .sr-live-dot { width: 8px; height: 8px; border-radius: 50%; background: #34d399; position: relative; }
    .sr-live-dot::after { content: ""; position: absolute; inset: -3px; border-radius: 50%; border: 2px solid #34d399; animation: livePulse 1.8s ease-out infinite; }
    @keyframes livePulse { 0% { transform: scale(0.8); opacity: 1; } 100% { transform: scale(1.8); opacity: 0; } }

    /* ===================== KPI ===================== */
    .sr-kpi { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 22px; }
    .sr-kpi-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.015));
        border: 1px solid var(--line); border-radius: 14px; padding: 15px 17px;
        transition: all .25s ease;
    }
    .sr-kpi-card:hover { transform: translateY(-2px); border-color: rgba(var(--glow), 0.3); box-shadow: 0 8px 20px rgba(0,0,0,0.16); }
    .sr-kpi-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
    .sr-kpi-icon svg { width: 18px; height: 18px; }
    .kpi-total  .sr-kpi-icon { background: rgba(var(--glow), 0.14); color: rgba(var(--glow), 1); border: 1px solid rgba(var(--glow), 0.25); }
    .kpi-signed .sr-kpi-icon { background: rgba(52,211,153,0.14);  color: #34d399; border: 1px solid rgba(52,211,153,0.3); }
    .kpi-wait   .sr-kpi-icon { background: rgba(251,191,36,0.14);  color: #fbbf24; border: 1px solid rgba(251,191,36,0.3); }
    .kpi-over   .sr-kpi-icon { background: rgba(248,113,113,0.14); color: #f87171; border: 1px solid rgba(248,113,113,0.3); }
    .sr-kpi-value { font-size: 30px; font-weight: 800; letter-spacing: -1.2px; line-height: 1; color: var(--text); margin-bottom: 5px; }
    .kpi-total .sr-kpi-value { color: rgba(var(--glow), 1); }
    .sr-kpi-label { font-size: 10.5px; font-weight: 700; letter-spacing: 1.1px; text-transform: uppercase; color: var(--muted); }

    /* ===================== GRID (3 в ряд, ширина не тронута) ===================== */
    .sr-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }

    .sign-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.015));
        border: 1px solid var(--line); border-radius: 14px; overflow: hidden;
        transition: all .28s cubic-bezier(.4,0,.2,1); animation: cardIn .45s ease backwards;
        display: flex; flex-direction: column; cursor: pointer;
        text-decoration: none; color: inherit; position: relative;
    }
    .sign-card:hover { transform: translateY(-3px); border-color: rgba(var(--glow), 0.35); box-shadow: 0 14px 28px -8px rgba(0,0,0,0.26), 0 0 0 1px rgba(var(--glow), 0.1); }
    @keyframes cardIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

    .sign-card::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 3px; }
    .sign-card[data-status="signed"]::before  { background: linear-gradient(90deg, #34d399, #10b981); }
    .sign-card[data-status="pending"]::before { background: linear-gradient(90deg, #fbbf24, #f59e0b); }
    .sign-card[data-status="overdue"]::before { background: linear-gradient(90deg, #f87171, #ef4444); }
    .sign-card[data-status="draft"]::before   { background: #64748b; opacity: 0.5; }

    /* ===== HEAD (чуть выше) ===== */
    .sc-head { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px 11px; border-bottom: 1px dashed rgba(255,255,255,0.08); }
    .sc-doc-label { font-size: 10.5px; font-weight: 700; letter-spacing: 1.1px; text-transform: uppercase; color: var(--muted); }
    .sc-id-badge { font-family: 'JetBrains Mono', monospace; font-size: 10.5px; font-weight: 700; color: var(--muted); background: rgba(255,255,255,0.05); border: 1px solid var(--line); padding: 2px 8px; border-radius: 6px; }

    /* ===== BODY (увеличены отступы → карточка длиннее) ===== */
    .sc-body { padding: 16px; flex: 1; display: flex; flex-direction: column; gap: 14px; }
    .sc-title-row { display: flex; align-items: center; gap: 11px; }
    .sc-doc-icon {
        width: 38px; height: 38px; border-radius: 10px;
        background: rgba(var(--glow), 0.12); border: 1px solid rgba(var(--glow), 0.22);
        display: flex; align-items: center; justify-content: center; color: rgba(var(--glow), 1); flex-shrink: 0;
    }
    .sc-doc-icon svg { width: 17px; height: 17px; }
    .sc-title { font-size: 14.5px; font-weight: 700; color: var(--text); line-height: 1.3; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* ===== НОВЫЙ БЛОК: ОТПРАВИТЕЛЬ (имя + email) ===== */
    .sc-sender {
        display: flex;
        align-items: center;
        gap: 11px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 10px;
        padding: 10px 12px;
    }
    .sc-sender-avatar {
        width: 34px; height: 34px; border-radius: 9px;
        background: linear-gradient(135deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.6));
        color: #ffffff;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 800; flex-shrink: 0;
        box-shadow: 0 3px 8px rgba(var(--glow), 0.25);
    }
    .sc-sender-info { min-width: 0; display: flex; flex-direction: column; gap: 1px; flex: 1; }
    .sc-sender-label { font-size: 9px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: var(--muted); opacity: 0.8; }
    .sc-sender-name { font-size: 12.5px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sc-sender-email {
        font-size: 11px; color: var(--muted); font-weight: 500;
        display: flex; align-items: center; gap: 5px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .sc-sender-email svg { width: 11px; height: 11px; flex-shrink: 0; opacity: 0.75; }
    .sc-sender-email span { overflow: hidden; text-overflow: ellipsis; }

    /* ===== PROGRESS (чуть толще) ===== */
    .sc-progress-wrap { display: flex; flex-direction: column; gap: 7px; }
    .sc-progress-top { display: flex; align-items: center; justify-content: space-between; font-size: 11px; color: var(--muted); font-weight: 600; }
    .sc-progress-top strong { color: var(--text); font-weight: 700; }
    .sc-progress-bar { height: 6px; background: rgba(255,255,255,0.06); border-radius: 999px; overflow: hidden; }
    .sc-progress-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, rgba(var(--glow), 1), rgba(var(--glow), 0.7)); transition: width .6s cubic-bezier(.4,0,.2,1); }
    .sign-card[data-status="signed"] .sc-progress-fill  { background: linear-gradient(90deg, #34d399, #10b981); }
    .sign-card[data-status="overdue"] .sc-progress-fill { background: linear-gradient(90deg, #f87171, #ef4444); }

    /* ===== SIGNERS ===== */
    .sc-signers { display: flex; align-items: center; gap: 9px; }
    .sc-avatar {
        width: 30px; height: 30px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; color: #fff; flex-shrink: 0;
        border: 2px solid var(--card-bg, #1a1d24);
    }
    .sc-avatar.signed  { background: linear-gradient(135deg, #34d399, #10b981); }
    .sc-avatar.pending { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #0a0d14; }
    .sc-avatar.overdue { background: linear-gradient(135deg, #f87171, #ef4444); }
    .sc-signers-text { font-size: 11.5px; color: var(--muted); font-weight: 500; }
    .sc-signers-text strong { color: var(--text); font-weight: 700; }

    /* ===== FOOT (чуть выше) ===== */
    .sc-foot { padding: 12px 16px; border-top: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: space-between; gap: 10px; background: rgba(255,255,255,0.015); }
    .sc-status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 11px; border-radius: 20px; font-size: 10px; font-weight: 800; letter-spacing: 0.4px; text-transform: uppercase; border: 1px solid; }
    .sc-status-pill::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .pill-signed  { color: #34d399; background: rgba(52,211,153,0.12); border-color: rgba(52,211,153,0.35); }
    .pill-pending { color: #fbbf24; background: rgba(251,191,36,0.12); border-color: rgba(251,191,36,0.35); }
    .pill-pending::before { animation: pulseRing 1.8s ease-out infinite; }
    .pill-overdue { color: #f87171; background: rgba(248,113,113,0.12); border-color: rgba(248,113,113,0.35); }
    .pill-draft   { color: #94a3b8; background: rgba(148,163,184,0.12); border-color: rgba(148,163,184,0.3); }
    @keyframes pulseRing { 0% { box-shadow: 0 0 0 0 currentColor; opacity: 1; } 70% { box-shadow: 0 0 0 5px transparent; } 100% { box-shadow: 0 0 0 0 transparent; } }

    .sc-deadline { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; color: var(--muted); font-weight: 600; }
    .sc-deadline svg { width: 12px; height: 12px; opacity: 0.7; }
    .sc-deadline.urgent { color: #f87171; }
    .sc-deadline.urgent svg { opacity: 1; }

    /* ===================== EMPTY ===================== */
    .sr-empty { grid-column: 1 / -1; padding: 70px 20px; text-align: center; background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.005)); border: 1px dashed rgba(var(--glow), 0.3); border-radius: 18px; }
    .sr-empty-icon { width: 66px; height: 66px; border-radius: 50%; background: linear-gradient(135deg, rgba(var(--glow), 0.18), rgba(var(--glow), 0.04)); border: 1px solid rgba(var(--glow), 0.25); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; color: rgba(var(--glow), 1); }
    .sr-empty-icon svg { width: 30px; height: 30px; }
    .sr-empty-title { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 5px; }
    .sr-empty-sub { font-size: 12.5px; color: var(--muted); margin-bottom: 18px; }
    .sr-empty-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 20px; background: rgba(var(--glow), 1);
        border-radius: 10px; color: #0a0d14; font-size: 13px; font-weight: 700;
        text-decoration: none; transition: all .2s ease;
        box-shadow: 0 4px 14px rgba(var(--glow), 0.35);
    }
    .sr-empty-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(var(--glow), 0.45); }
    .sr-empty-btn svg { width: 14px; height: 14px; stroke-width: 3; }

    /* ===================== PAGINATION ===================== */
    .sr-pagination { margin-top: 24px; display: flex; justify-content: center; }
    .sr-pagination nav { display: flex; gap: 4px; background: rgba(255,255,255,0.025); border: 1px solid var(--line); padding: 5px; border-radius: 11px; }
    .sr-pagination ul { display: flex; gap: 3px; list-style: none; margin: 0; padding: 0; }
    .sr-pagination li a, .sr-pagination li span { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; padding: 0 10px; border-radius: 7px; font-size: 12px; font-weight: 700; color: var(--muted); text-decoration: none; transition: all .2s ease; background: transparent; border: none; }
    .sr-pagination li a:hover { color: var(--text); background: rgba(255,255,255,0.05); }
    .sr-pagination li.active span { color: #0a0d14; background: rgba(var(--glow), 1); box-shadow: 0 4px 12px rgba(var(--glow), 0.3); }
    .sr-pagination li.disabled span { opacity: 0.35; pointer-events: none; }

    /* ===================== LIGHT THEME ===================== */
    html[data-theme="light"] .sign-registry { color: #0f172a; }
    html[data-theme="light"] .sr-header h1 { color: #0f172a; }
    html[data-theme="light"] .sr-status-line { color: #64748b; }

    html[data-theme="light"] .sr-kpi-card,
    html[data-theme="light"] .sign-card { background: #ffffff; border-color: #e2e8f0; box-shadow: 0 3px 10px rgba(15, 23, 42, 0.04); }
    html[data-theme="light"] .sr-kpi-card:hover,
    html[data-theme="light"] .sign-card:hover { box-shadow: 0 12px 26px rgba(15, 23, 42, 0.09); }

    html[data-theme="light"] .sr-kpi-value,
    html[data-theme="light"] .sc-title,
    html[data-theme="light"] .sc-sender-name { color: #0f172a; }
    html[data-theme="light"] .sr-kpi-label,
    html[data-theme="light"] .sc-doc-label,
    html[data-theme="light"] .sc-id-badge,
    html[data-theme="light"] .sc-progress-top,
    html[data-theme="light"] .sc-signers-text,
    html[data-theme="light"] .sc-deadline,
    html[data-theme="light"] .sc-sender-email,
    html[data-theme="light"] .sr-empty-sub { color: #64748b; }
    html[data-theme="light"] .sc-progress-top strong,
    html[data-theme="light"] .sc-signers-text strong { color: #0f172a; }
    html[data-theme="light"] .sc-sender-label { color: #94a3b8; }

    /* Блок отправителя в светлой теме */
    html[data-theme="light"] .sc-sender { background: #f8fafc; border-color: #eef2f7; }
    html[data-theme="light"] .sc-sender-avatar { color: #ffffff; }

    html[data-theme="light"] .sr-pagination nav { background: #ffffff; border-color: #e2e8f0; }
    html[data-theme="light"] .sr-pagination li a:hover { background: #f1f5f9; color: #0f172a; }

    html[data-theme="light"] .sc-head,
    html[data-theme="light"] .sc-foot { border-color: #eef2f7; }
    html[data-theme="light"] .sc-foot { background: #fafbfc; }
    html[data-theme="light"] .sc-id-badge { background: #f1f5f9; border-color: #e2e8f0; }
    html[data-theme="light"] .sc-progress-bar { background: #eef2f7; }
    html[data-theme="light"] .sc-avatar { border-color: #ffffff; }

    html[data-theme="light"] .pill-signed  { color: #15803d; background: #dcfce7; border-color: rgba(22,163,74,0.4); }
    html[data-theme="light"] .pill-pending { color: #b45309; background: #fef3c7; border-color: rgba(217,119,6,0.4); }
    html[data-theme="light"] .pill-overdue { color: #b91c1c; background: #fee2e2; border-color: rgba(220,38,38,0.4); }
    html[data-theme="light"] .pill-draft   { color: #475569; background: #f1f5f9; border-color: #cbd5e1; }

    html[data-theme="light"] .sr-empty { background: #fafafa; border-color: #e2e8f0; }

    /* ===================== RESPONSIVE ===================== */
    @media (max-width: 992px) {
        .sr-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .sign-registry { padding: 16px; }
        .sr-header h1 { font-size: 22px; }
        .sr-kpi { grid-template-columns: 1fr 1fr; gap: 10px; }
        .sr-kpi-value { font-size: 26px; }
    }
    @media (max-width: 560px) {
        .sign-registry { padding: 12px; }
        .sr-grid { grid-template-columns: 1fr; }
        .sr-header-top { flex-direction: column; align-items: stretch; gap: 12px; }
        .sr-btn-new { width: 100%; padding: 12px; justify-content: center; }
        .sr-kpi-card { padding: 13px 15px; }
        .sr-kpi-value { font-size: 24px; }
        .sr-kpi-icon { width: 34px; height: 34px; margin-bottom: 9px; }
        .sr-kpi-icon svg { width: 16px; height: 16px; }
        .sc-body { padding: 14px; }
        .sc-head, .sc-foot { padding-left: 14px; padding-right: 14px; }
        .sc-title { font-size: 14px; }
    }
</style>

@php
    // ===== Реальный подсчёт KPI из коллекции =====
    $kpiTotal   = $documents->total();
    $kpiSigned  = 0;
    $kpiWaiting = 0;
    $kpiOverdue = 0;
    foreach ($documents as $d) {
        $st = strtolower($d->status);
        if ($st === 'rejected' || $st === 'draft') continue;
        if ($d->isSigned()) {
            $kpiSigned++;
        } elseif ($d->deadline && $d->deadline->isPast()) {
            $kpiOverdue++;
        } else {
            $kpiWaiting++;
        }
    }
@endphp

<div class="sign-registry">
    <!-- HEADER -->
    <div class="sr-header">
        <div class="sr-header-top">
            <div class="sr-title-row">
                <span class="sr-accent"></span>
                <h1>{{ $tabTitle ?? __('Реестр подписей') }}</h1>
            </div>

            <a href="{{ route('documents.create') }}" class="sr-btn-new">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span data-i18n="newDocument">Создать документ</span>
            </a>
        </div>
       
    </div>

    <!-- KPI -->
    <div class="sr-kpi">
        <div class="sr-kpi-card kpi-total">
            <div class="sr-kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
            <div class="sr-kpi-value">{{ $kpiTotal }}</div>
            <div class="sr-kpi-label" data-i18n="kpiTotal">Всего</div>
        </div>
        <div class="sr-kpi-card kpi-signed">
            <div class="sr-kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
            <div class="sr-kpi-value">{{ $kpiSigned }}</div>
            <div class="sr-kpi-label" data-i18n="kpiSigned">Подписано</div>
        </div>
        <div class="sr-kpi-card kpi-wait">
            <div class="sr-kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
            <div class="sr-kpi-value">{{ $kpiWaiting }}</div>
            <div class="sr-kpi-label" data-i18n="kpiWaiting">Ожидает</div>
        </div>
        <div class="sr-kpi-card kpi-over">
            <div class="sr-kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
            <div class="sr-kpi-value">{{ $kpiOverdue }}</div>
            <div class="sr-kpi-label" data-i18n="kpiOverdue">Просрочено</div>
        </div>
    </div>

    <!-- CARDS (3 в ряд) -->
    <div class="sr-grid">
        @forelse($documents as $index => $doc)
            @php
                $rawStatus = strtolower($doc->status);
                if ($rawStatus === 'draft') {
                    $signStatus = 'draft';
                } elseif ($rawStatus === 'rejected') {
                    $signStatus = 'overdue';
                } elseif ($doc->isSigned()) {
                    $signStatus = 'signed';
                } elseif ($doc->deadline && $doc->deadline->isPast()) {
                    $signStatus = 'overdue';
                } else {
                    $signStatus = 'pending';
                }
                $pillClass = 'pill-' . $signStatus;
                $statusKey = 'st_' . $signStatus;

                // ===== Подписанты через реальную связь signatures() =====
                $sigs = $doc->signatures;
                $totalSigners = max($sigs->count(), 1);
                $signedCount  = $sigs->where('signature', '!=', '')->where('signature', '!=', null)->count();
                if ($sigs->isEmpty()) {
                    $totalSigners = 1;
                    $signedCount  = $doc->isSigned() ? 1 : 0;
                }
                $percent = round(($signedCount / $totalSigners) * 100);

                // ===== ОТПРАВИТЕЛЬ: от кого пришёл документ =====
                // Если документ создал НЕ я → отправитель = создатель.
                // Если я создатель → показываю получателя (кому ушёл / от кого жду возврат).
                $authId = auth()->id();
                if ($doc->created_by && $doc->created_by !== $authId) {
                    $sender = $doc->createdBy;
                } else {
                    $sender = $doc->receiver ?? $doc->createdBy;
                }
                $senderName     = $sender?->name ?? 'System';
                $senderEmail    = $sender?->email ?? '—';
                $senderInitials = mb_strtoupper(mb_substr($senderName, 0, 1));

                // ===== Аватар подписанта =====
                $firstSigner = $sigs->where('signature', '!=', '')->where('signature', '!=', null)->first();
                $avatarUser  = $firstSigner ? ($firstSigner->user ?? null) : ($doc->receiver ?? $doc->createdBy);
                $avatarName  = $avatarUser ? $avatarUser->name : '?';
                $initials    = mb_strtoupper(mb_substr($avatarName, 0, 1));
                $avatarClass = $signStatus === 'signed' ? 'signed' : ($signStatus === 'overdue' ? 'overdue' : 'pending');

                $isOverdue = ($signStatus === 'overdue');
            @endphp

            <a href="{{ route('documents.show', $doc->id) }}" class="sign-card" data-status="{{ $signStatus }}" style="animation-delay: {{ min($index, 12) * 45 }}ms">
                <div class="sc-head">
                    <span class="sc-doc-label" data-i18n="docLabel">Документ</span>
                    <span class="sc-id-badge">ID-{{ $doc->number ?? $doc->id }}</span>
                </div>

                <div class="sc-body">
                    <div class="sc-title-row">
                        <div class="sc-doc-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        </div>
                        <div class="sc-title">{{ Str::limit($doc->title, 32) }}</div>
                    </div>

                    <!-- ===== НОВЫЙ БЛОК: ОТПРАВИТЕЛЬ + EMAIL ===== -->
                    <div class="sc-sender">
                        <div class="sc-sender-avatar">{{ $senderInitials }}</div>
                        <div class="sc-sender-info">
                            <span class="sc-sender-label" data-i18n="senderLabel">Отправитель</span>
                            <span class="sc-sender-name">{{ $senderName }}</span>
                            <span class="sc-sender-email">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                <span>{{ $senderEmail }}</span>
                            </span>
                        </div>
                    </div>

                    <div class="sc-progress-wrap">
                        <div class="sc-progress-top">
                            <span data-i18n="progress">Прогресс подписи</span>
                            <span><strong>{{ $signedCount }}</strong> / {{ $totalSigners }}</span>
                        </div>
                        <div class="sc-progress-bar">
                            <div class="sc-progress-fill" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>

                    <div class="sc-signers">
                        <div class="sc-avatar {{ $avatarClass }}">{{ $initials }}</div>
                        <span class="sc-signers-text">
                            <strong>{{ $signedCount }}</strong> <span data-i18n="ofSigned">из</span> {{ $totalSigners }} <span data-i18n="signedWord">подписали</span>
                        </span>
                    </div>
                </div>

                <div class="sc-foot">
                    <span class="sc-status-pill {{ $pillClass }}" data-status-key="{{ $statusKey }}">
                        {{ $doc->status_label }}
                    </span>
                    @if($doc->deadline)
                        <span class="sc-deadline {{ $isOverdue ? 'urgent' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ $doc->deadline->format('d.m.Y') }}
                        </span>
                    @endif
                </div>
            </a>
        @empty
            <div class="sr-empty">
                <div class="sr-empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div class="sr-empty-title" data-i18n="emptyTitle">Нет документов для подписи</div>
                <div class="sr-empty-sub" data-i18n="emptySub">Создайте первый документ, чтобы начать работу</div>
                <a href="{{ route('documents.create') }}" class="sr-empty-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    <span data-i18n="newDocument">Создать документ</span>
                </a>
            </div>
        @endforelse
    </div>

    @if($documents->hasPages())
        <div class="sr-pagination">
            <nav>
                <ul>
                    @if($documents->onFirstPage())
                        <li class="disabled"><span>&laquo;</span></li>
                    @else
                        <li><a href="{{ $documents->previousPageUrl() }}" rel="prev">&laquo;</a></li>
                    @endif
                    @foreach($documents->getUrlRange(1, $documents->lastPage()) as $page => $url)
                        @if($page == $documents->currentPage())
                            <li class="active"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                    @if($documents->hasMorePages())
                        <li><a href="{{ $documents->nextPageUrl() }}" rel="next">&raquo;</a></li>
                    @else
                        <li class="disabled"><span>&raquo;</span></li>
                    @endif
                </ul>
            </nav>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const T = {
            ru: {
                monitoringActive: 'Система мониторинга активна',
                newDocument: 'Создать документ',
                kpiTotal: 'Всего', kpiSigned: 'Подписано', kpiWaiting: 'Ожидает', kpiOverdue: 'Просрочено',
                docLabel: 'Документ', progress: 'Прогресс подписи', ofSigned: 'из', signedWord: 'подписали',
                senderLabel: 'Отправитель',
                st_signed: 'Подписан', st_pending: 'Ожидает подписи', st_overdue: 'Просрочен', st_draft: 'Черновик',
                emptyTitle: 'Нет документов для подписи', emptySub: 'Создайте первый документ, чтобы начать работу',
            },
            tj: {
                monitoringActive: 'Системаи мониторинг фаъол аст',
                newDocument: 'Эҷод кардани ҳуҷҷат',
                kpiTotal: 'Ҳамагӣ', kpiSigned: 'Имзошуда', kpiWaiting: 'Интизор', kpiOverdue: 'Мӯҳлатгузашта',
                docLabel: 'Ҳуҷҷат', progress: 'Пешрафти имзо', ofSigned: 'аз', signedWord: 'имзо карданд',
                senderLabel: 'Фиристанда',
                st_signed: 'Имзошуда', st_pending: 'Интизори имзо', st_overdue: 'Мӯҳлат гузашт', st_draft: 'Лоиҳа',
                emptyTitle: 'Ҳуҷҷат барои имзо нест', emptySub: 'Барои оғози кор аввалин ҳуҷҷатро эҷод кунед',
            },
            en: {
                monitoringActive: 'Monitoring system is active',
                newDocument: 'Create document',
                kpiTotal: 'Total', kpiSigned: 'Signed', kpiWaiting: 'Waiting', kpiOverdue: 'Overdue',
                docLabel: 'Document', progress: 'Signing progress', ofSigned: 'of', signedWord: 'signed',
                senderLabel: 'Sender',
                st_signed: 'Signed', st_pending: 'Awaiting signature', st_overdue: 'Overdue', st_draft: 'Draft',
                emptyTitle: 'No documents to sign', emptySub: 'Create your first document to get started',
            }
        };
        function apply(lang) {
            const d = T[lang] || T.ru;
            document.querySelectorAll('[data-i18n]').forEach(el => { const k = el.getAttribute('data-i18n'); if (d[k] !== undefined) el.textContent = d[k]; });
            document.querySelectorAll('[data-status-key]').forEach(el => { const k = el.getAttribute('data-status-key'); if (d[k]) el.textContent = d[k]; });
        }
        apply(localStorage.getItem('docsign_lang') || 'ru');
        window.addEventListener('docsign:lang-changed', e => apply(e.detail?.lang || 'ru'));
        window.addEventListener('storage', e => { if (e.key === 'docsign_lang' && e.newValue) apply(e.newValue); });
    });
</script>
@endsection