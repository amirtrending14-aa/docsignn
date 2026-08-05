@extends('layouts.admin')

@section('content')
<style>
    /* Fallback если layout не загрузился */
    body {
        background: #06070b !important;
        color: #e7ecf3 !important;
    }
    .att-page-custom { color: var(--text, #e7ecf3); }
</style>
<style>
    /* ============================================================
       ПОСЕЩАЕМОСТЬ КОМАНДЫ — dark + glow, 3 языка, full responsive
       ============================================================ */
    .att-page-custom {
        color: var(--text);
        max-width: 100%;
        overflow-x: clip; /* гарантия: нет горизонтального скролла */
    }

    @keyframes attFadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: none; }
    }

    /* ---------- ШАПКА ---------- */
    .att-page-custom .page-head-custom {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 22px; flex-wrap: wrap; gap: 14px;
    }
    .att-head-left { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .att-head-icon {
        width: 48px; height: 48px; border-radius: 13px; flex-shrink: 0;
        background: linear-gradient(135deg, rgba(var(--glow), 0.9), rgba(var(--glow), 0.55));
        display: grid; place-items: center;
        box-shadow: 0 0 22px rgba(var(--glow), 0.45), inset 0 1px 0 rgba(255,255,255,0.3);
    }
    .att-head-icon i { font-size: 20px; color: #0a0d14; }
    .att-page-custom h1 {
        margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px; line-height: 1.15;
    }
    .att-subtitle { font-size: 12px; color: var(--muted); font-weight: 600; margin-top: 3px; }

    .att-date-form {
        display: flex; gap: 8px; align-items: center; flex-wrap: wrap;
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--line);
        padding: 6px; border-radius: 12px;
    }
    .att-input {
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--line);
        color: var(--text);
        border-radius: 9px;
        height: 40px; padding: 0 12px;
        font-size: 13px; font-family: 'Inter', sans-serif;
        width: 100%;
        transition: all .2s ease;
        color-scheme: dark;
    }
    .att-input:focus {
        outline: none;
        border-color: rgba(var(--glow), 0.5);
        box-shadow: 0 0 0 3px rgba(var(--glow), 0.12), 0 0 14px rgba(var(--glow), 0.15);
    }
    .att-date-input {
        width: 175px; border: 0; background: transparent;
        font-family: 'JetBrains Mono', monospace;
        font-size: 12.5px; letter-spacing: 0.5px;
    }
    .att-date-input:focus { box-shadow: none; border: 0; }

    .att-page-custom .btn-new {
        appearance: none; border: 0;
        background: linear-gradient(180deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.65));
        color: #0a0d14;
        font: 600 12.5px 'Inter', sans-serif;
        padding: 10px 16px; border-radius: 9px; cursor: pointer;
        display: inline-flex; align-items: center; gap: 7px;
        text-decoration: none; white-space: nowrap;
        box-shadow: 0 8px 24px rgba(var(--glow), 0.35), inset 0 1px 0 rgba(255,255,255,0.3);
        transition: all .25s ease;
    }
    .att-page-custom .btn-new:hover {
        filter: brightness(1.08); transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(var(--glow), 0.5);
    }
    .att-page-custom .btn-new i { color: #0a0d14; font-size: 13px; }

    /* ---------- АЛЕРТ ---------- */
    .att-alert {
        display: flex; align-items: center; gap: 10px;
        background: rgba(76,217,130,0.08);
        border: 1px solid rgba(76,217,130,0.3);
        color: #4cd982;
        padding: 12px 16px; border-radius: 12px;
        margin-bottom: 18px;
        font-size: 13px; font-weight: 600;
        box-shadow: 0 0 20px rgba(76,217,130,0.1);
        animation: attFadeUp .4s ease both;
    }

    /* ---------- СТАТИСТИКА ---------- */
    .att-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }
    .att-stat {
        --stat-c: var(--glow);
        position: relative;
        background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.01));
        border: 1px solid var(--line);
        border-radius: 13px;
        padding: 15px 14px;
        display: flex; align-items: center; gap: 12px;
        overflow: hidden;
        transition: all .25s ease;
        animation: attFadeUp .5s ease both;
        min-width: 0;
    }
    .att-stat:nth-child(2) { animation-delay: .05s; }
    .att-stat:nth-child(3) { animation-delay: .1s; }
    .att-stat:nth-child(4) { animation-delay: .15s; }
    .att-stat:nth-child(5) { animation-delay: .2s; }
    .att-stat::before {
        content: ""; position: absolute; top: 0; left: 0; right: 0; height: 2px;
        background: linear-gradient(90deg, transparent, rgba(var(--stat-c), 0.9), transparent);
    }
    .att-stat:hover {
        transform: translateY(-3px);
        border-color: rgba(var(--stat-c), 0.35);
        box-shadow: 0 12px 26px rgba(0,0,0,0.35), 0 0 20px rgba(var(--stat-c), 0.14);
    }
    .att-stat.st-total  { --stat-c: var(--glow); }
    .att-stat.st-green  { --stat-c: 76,217,130; }
    .att-stat.st-orange { --stat-c: 255,181,71; }
    .att-stat.st-red    { --stat-c: 255,122,122; }
    .att-stat.st-purple { --stat-c: 167,139,250; }

    .att-stat-icon {
        width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
        background: rgba(var(--stat-c), 0.12);
        border: 1px solid rgba(var(--stat-c), 0.3);
        display: grid; place-items: center;
        color: rgba(var(--stat-c), 1); font-size: 15px;
        box-shadow: 0 0 14px rgba(var(--stat-c), 0.2);
    }
    .att-stat-num {
        font-family: 'JetBrains Mono', monospace;
        font-size: 22px; font-weight: 700; line-height: 1;
        color: rgba(var(--stat-c), 1);
        text-shadow: 0 0 16px rgba(var(--stat-c), 0.4);
    }
    .att-stat-label {
        font-size: 9.5px; font-weight: 600; color: var(--muted);
        text-transform: uppercase; letter-spacing: 0.9px; margin-top: 4px;
    }

    /* ---------- ФИЛЬТРЫ ---------- */
    .att-filters {
        display: flex; gap: 8px; flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .att-filter {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 15px; border-radius: 10px;
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--line);
        color: var(--muted);
        text-decoration: none;
        font-size: 12px; font-weight: 600;
        transition: all .22s ease;
    }
    .att-filter i { font-size: 12px; }
    .att-filter:hover {
        color: var(--text);
        border-color: rgba(var(--glow), 0.4);
        background: rgba(var(--glow), 0.08);
        transform: translateY(-2px);
    }
    .att-filter .cnt {
        font-family: 'JetBrains Mono', monospace;
        font-size: 10.5px; font-weight: 700;
        padding: 2px 8px; border-radius: 6px;
        background: rgba(255,255,255,0.06);
    }
    .att-filter.f-all     .cnt { background: rgba(var(--glow), 0.18); color: rgba(var(--glow), 1); }
    .att-filter.f-ontime  .cnt { background: rgba(76,217,130,0.15);  color: #4cd982; }
    .att-filter.f-late    .cnt { background: rgba(255,181,71,0.15);  color: #ffb547; }
    .att-filter.f-absent  .cnt { background: rgba(255,122,122,0.15); color: #ff7a7a; }
    .att-filter.f-excused .cnt { background: rgba(136,146,166,0.15); color: #aab4c8; }

    .att-filter.active {
        background: linear-gradient(180deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.65));
        border-color: transparent;
        color: #0a0d14;
        box-shadow: 0 8px 20px rgba(var(--glow), 0.4);
    }
    .att-filter.active .cnt { background: rgba(0,0,0,0.25); color: #0a0d14; }
    .att-filter.active i { color: #0a0d14; }

    /* ---------- ПАНЕЛЬ НАСТРОЕК ---------- */
    .att-panel {
        background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
        border: 1px solid var(--line);
        border-radius: 14px;
        overflow: hidden;
        position: relative;
        margin-bottom: 22px;
        animation: attFadeUp .55s ease both; animation-delay: .15s;
    }
    .att-panel::before {
        content: ""; position: absolute; inset: -1px;
        border-radius: 14px; padding: 1px;
        background: linear-gradient(135deg, rgba(var(--glow), 0.4), transparent 40%, transparent 60%, rgba(var(--glow), 0.2));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none; opacity: 0.6;
    }
    .att-panel-head {
        display: flex; align-items: center; gap: 10px;
        padding: 14px 20px;
        border-bottom: 1px solid var(--line);
        background: rgba(255,255,255,0.02);
    }
    .att-panel-title {
        display: flex; align-items: center; gap: 10px;
        font-size: 12.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.8px;
    }
    .att-panel-title > i { color: rgba(var(--glow), 1); font-size: 15px; filter: drop-shadow(0 0 6px rgba(var(--glow), .6)); }
    .att-panel-body { padding: 18px 20px; }

    .att-label {
        display: flex; align-items: center; gap: 6px;
        font-size: 10.5px; font-weight: 600; color: var(--muted);
        text-transform: uppercase; letter-spacing: 0.8px;
        margin-bottom: 7px;
    }
    .att-label i { color: rgba(var(--glow), 1); font-size: 11px; }

    .att-field-company { max-width: 340px; margin-bottom: 15px; }
    select.att-input { cursor: pointer; }
    select.att-input option { background: #0d1117; color: var(--text); }
    .att-company-note {
        font-size: 12px; color: var(--muted); margin-bottom: 15px;
        display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
    }
    .att-company-note b {
        color: var(--text);
        background: rgba(var(--glow), 0.08);
        border: 1px solid rgba(var(--glow), 0.2);
        padding: 3px 10px; border-radius: 6px;
        font-size: 12px; font-weight: 600;
    }

    .att-settings-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
        gap: 13px;
        align-items: end;
    }
    .att-save-btn { height: 40px; }

    /* ---------- КАРТОЧКИ СОТРУДНИКОВ ---------- */
    .att-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(255px, 1fr));
        gap: 15px;
    }

    .att-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.01));
        border: 1px solid var(--line);
        border-radius: 15px;
        overflow: hidden;
        position: relative;
        transition: all .3s cubic-bezier(0.4, 0, 0.2, 1);
        animation: attFadeUp .45s ease both;
        min-width: 0;
    }
    .att-card::before {
        content: ""; position: absolute; top: 0; left: 0; right: 0; height: 3px; z-index: 3;
    }
    .att-card.c-ontime::before  { background: linear-gradient(90deg, transparent, #4cd982, transparent); }
    .att-card.c-late::before    { background: linear-gradient(90deg, transparent, #ffb547, transparent); }
    .att-card.c-absent::before  { background: linear-gradient(90deg, transparent, #ff7a7a, transparent); }
    .att-card.c-excused::before { background: linear-gradient(90deg, transparent, #8892a6, transparent); }
    .att-card.c-waiting::before { background: linear-gradient(90deg, transparent, #5ec6ff, transparent); }

    .att-card:hover {
        transform: translateY(-4px);
        border-color: rgba(var(--glow), 0.35);
        box-shadow: 0 18px 36px -10px rgba(0,0,0,0.5), 0 0 20px rgba(var(--glow), 0.12);
    }

    .att-card-photo {
        position: relative;
        height: 108px;
        background:
            radial-gradient(circle at 70% 20%, rgba(var(--glow), 0.25), transparent 55%),
            linear-gradient(135deg, rgba(var(--glow), 0.28), rgba(var(--glow), 0.08));
        display: flex; align-items: center; justify-content: center;
        overflow: hidden;
    }
    .att-card-photo img { width: 100%; height: 100%; object-fit: cover; }
    .att-card-initials {
        font-size: 40px; font-weight: 900; color: rgba(255,255,255,0.92);
        text-shadow: 0 4px 22px rgba(0,0,0,0.55), 0 0 30px rgba(var(--glow), 0.5);
    }
    .att-card-photo::after {
        content: ""; position: absolute; inset: 0;
        background: linear-gradient(180deg, transparent 35%, rgba(6,7,11,0.92) 100%);
    }

    .att-card-badges {
        position: absolute; top: 10px; left: 10px; right: 10px;
        display: flex; justify-content: space-between; align-items: center;
        gap: 6px; z-index: 2;
    }
    .att-online-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 10px; border-radius: 7px;
        font-size: 9.5px; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.5px;
        background: rgba(6,7,11,0.85);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,0.12);
        white-space: nowrap;
    }
    .att-online-badge::before {
        content: ""; width: 6px; height: 6px; border-radius: 50%;
        background: currentColor; box-shadow: 0 0 8px currentColor;
    }
    .att-online-badge.on  { color: #4cd982; border-color: rgba(76,217,130,0.4); }
    .att-online-badge.off { color: #ff7a7a; border-color: rgba(255,122,122,0.3); }

    .att-status {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 10px; border-radius: 7px;
        font-size: 9.5px; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.5px;
        white-space: nowrap;
    }
    .att-status.s-ontime  { background: rgba(76,217,130,0.95);  color: #062b16; box-shadow: 0 0 14px rgba(76,217,130,0.5); }
    .att-status.s-late    { background: rgba(255,181,71,0.95);  color: #241503; box-shadow: 0 0 14px rgba(255,181,71,0.5); }
    .att-status.s-absent  { background: rgba(255,122,122,0.95); color: #2b0a0a; box-shadow: 0 0 14px rgba(255,122,122,0.5); }
    .att-status.s-excused { background: rgba(136,146,166,0.95); color: #0a0d14; }
    .att-status.s-waiting { background: rgba(94,198,255,0.95);  color: #06202e; box-shadow: 0 0 14px rgba(94,198,255,0.5); }

    .att-card-body { padding: 15px; }
    .att-card-name {
        font-size: 15px; font-weight: 800; color: var(--text);
        margin: 0 0 6px;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .att-card-role {
        display: inline-block;
        padding: 3px 10px; border-radius: 6px;
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.6px;
        color: rgba(var(--glow), 1);
        background: rgba(var(--glow), 0.1);
        border: 1px solid rgba(var(--glow), 0.25);
        margin-bottom: 12px;
        max-width: 100%;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }

    .att-info-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 8px;
        margin-bottom: 10px;
    }
    .att-info-item {
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--line);
        border-radius: 9px;
        padding: 8px 6px;
        text-align: center;
        min-width: 0;
    }
    .att-info-label {
        font-size: 8.5px; font-weight: 700; color: var(--muted);
        text-transform: uppercase; letter-spacing: 0.7px;
        margin-bottom: 3px;
    }
    .att-info-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 13px; font-weight: 700; color: var(--text);
    }
    .att-v-danger  { color: #ff7a7a; }
    .att-v-success { color: #4cd982; }
    .att-v-warning { color: #ffb547; }
    .att-v-muted   { color: var(--muted); font-weight: 400; }

    .att-money-grid {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
        margin-bottom: 12px;
    }
    .att-money-item {
        background: rgba(255,255,255,0.02);
        border: 1px solid var(--line);
        border-radius: 9px;
        padding: 8px 4px;
        text-align: center;
        min-width: 0;
    }
    .att-money-label {
        font-size: 8.5px; font-weight: 700; color: var(--muted);
        text-transform: uppercase; letter-spacing: 0.6px;
        margin-bottom: 3px;
    }
    .att-money-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11.5px; font-weight: 700; color: var(--text);
    }
    .att-money-value small { font-size: 8.5px; color: var(--muted); font-weight: 600; }
    .att-m-red   { color: #ff7a7a; }
    .att-m-green { color: #4cd982; text-shadow: 0 0 10px rgba(76,217,130,0.35); }

    .att-card-actions {
        display: flex; gap: 8px;
        padding-top: 12px;
        border-top: 1px solid var(--line);
    }
    .att-card-actions form { flex: 1; margin: 0; }
    .att-act {
        flex: 1; width: 100%;
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        padding: 9px 6px; border-radius: 9px;
        font: 700 10px 'Inter', sans-serif;
        text-transform: uppercase; letter-spacing: 0.5px;
        border: 1px solid var(--line);
        background: rgba(255,255,255,0.03);
        color: var(--muted);
        text-decoration: none; cursor: pointer;
        transition: all .2s ease;
        white-space: nowrap;
    }
    .att-act i { font-size: 11px; }
    .att-act:hover {
        color: rgba(var(--glow), 1);
        border-color: rgba(var(--glow), 0.4);
        background: rgba(var(--glow), 0.1);
        box-shadow: 0 0 12px rgba(var(--glow), 0.25);
        transform: translateY(-1px);
    }
    .att-act.excuse:hover {
        color: #4cd982;
        border-color: rgba(76,217,130,0.45);
        background: rgba(76,217,130,0.1);
        box-shadow: 0 0 12px rgba(76,217,130,0.3);
    }

    /* ---------- EMPTY ---------- */
    .att-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        background: rgba(255,255,255,0.02);
        border: 1px dashed rgba(var(--glow), 0.25);
        border-radius: 14px;
    }
    .att-empty-icon {
        width: 64px; height: 64px; border-radius: 50%;
        background: rgba(var(--glow), 0.08);
        border: 1px solid rgba(var(--glow), 0.2);
        display: inline-flex; align-items: center; justify-content: center;
        margin-bottom: 14px;
        color: rgba(var(--glow), 1); font-size: 24px;
        box-shadow: 0 0 20px rgba(var(--glow), 0.15);
    }
    .att-empty-title { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
    .att-empty-desc { font-size: 12px; color: var(--muted); }
    .att-empty-link { color: rgba(var(--glow), 1); font-weight: 600; text-decoration: none; }
    .att-empty-link:hover { text-decoration: underline; }

    /* ===== RESPONSIVE ===== */

    /* ≤1200px */
    @media (max-width: 1200px) {
        .att-settings-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .att-field-save { grid-column: span 2; }
        .att-save-btn { width: 100%; justify-content: center; }
    }

    /* ≤992px */
    @media (max-width: 992px) {
        .att-page-custom h1 { font-size: 21px; }
        .att-head-icon { width: 44px; height: 44px; border-radius: 12px; }
        .att-grid { grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 12px; }
        .att-panel-body { padding: 16px; }
        .att-panel-head { padding: 13px 16px; }
        .att-stat-num { font-size: 20px; }
    }

    /* ≤768px */
    @media (max-width: 768px) {
        .att-page-custom .page-head-custom { margin-bottom: 18px; gap: 10px; }
        .att-page-custom h1 { font-size: 19px; }
        .att-subtitle { font-size: 11px; }
        .att-stats { gap: 10px; margin-bottom: 15px; }
        .att-stat { padding: 13px 12px; border-radius: 12px; }
        .att-stat-icon { width: 34px; height: 34px; font-size: 14px; }
        .att-filter { padding: 8px 12px; font-size: 11px; }
        .att-card-photo { height: 98px; }
        .att-card-initials { font-size: 34px; }
        .att-card-body { padding: 13px; }
        .att-card-name { font-size: 14px; }
        .att-info-value { font-size: 12px; }
        .att-money-value { font-size: 10.5px; }
    }

    /* ≤576px */
    @media (max-width: 576px) {
        .att-page-custom h1 { font-size: 17px; }
        .att-date-form { width: 100%; }
        .att-date-input { flex: 1; width: auto; min-width: 0; }
        .att-settings-grid { grid-template-columns: 1fr; }
        .att-field-save { grid-column: span 1; }
        .att-field-company { max-width: none; }
        .att-grid { grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); }
        .att-empty { padding: 44px 16px; }
    }

    /* ≤480px */
    @media (max-width: 480px) {
        .att-page-custom h1 { font-size: 16px; }
        .att-head-icon { width: 40px; height: 40px; border-radius: 10px; }
        .att-head-icon i { font-size: 17px; }
        .att-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        .att-stat { gap: 10px; padding: 12px 11px; }
        .att-stat-num { font-size: 18px; }
        .att-stat-label { font-size: 8.5px; }
        .att-filter { padding: 7px 10px; font-size: 10.5px; gap: 5px; }
        .att-filter .cnt { font-size: 9.5px; padding: 2px 6px; }
        .att-panel-body { padding: 13px; }
        .att-panel-title { font-size: 11px; }
        .att-card-photo { height: 90px; }
        .att-card-initials { font-size: 30px; }
        .att-online-badge, .att-status { font-size: 8.5px; padding: 4px 8px; }
        .att-card-name { font-size: 13px; }
        .att-card-role { font-size: 8.5px; margin-bottom: 10px; }
        .att-info-grid, .att-money-grid { gap: 6px; }
        .att-info-label, .att-money-label { font-size: 7.5px; }
        .att-info-value { font-size: 11px; }
        .att-money-value { font-size: 9.5px; }
        .att-act { font-size: 9px; padding: 8px 4px; }
    }

    /* ≤380px */
    @media (max-width: 380px) {
        .att-page-custom h1 { font-size: 15px; }
        .att-stat-num { font-size: 16px; }
        .att-grid { grid-template-columns: 1fr; }
        .att-card-badges { flex-wrap: wrap; }
        .att-money-value small { display: block; }
        .att-act { font-size: 8.5px; gap: 4px; }
    }
</style>

<div class="att-page-custom">

    {{-- ===== ШАПКА ===== --}}
    <div class="page-head-custom">
        <div class="att-head-left">
            <div class="att-head-icon"><i class="bi bi-bar-chart-line-fill"></i></div>
            <div style="min-width:0">
                <h1 data-i18n="attTitle">ПОСЕЩАЕМОСТЬ КОМАНДЫ</h1>
                <div class="att-subtitle" data-i18n="attSubtitle">Все сотрудники и их действия</div>
            </div>
        </div>
        <form method="GET" class="att-date-form">
            @if ($filter !== 'all') <input type="hidden" name="filter" value="{{ $filter }}"> @endif
            <input type="date" name="date" value="{{ $date }}" class="att-input att-date-input" aria-label="Date">
            <button type="submit" class="btn-new">
                <i class="bi bi-funnel"></i>
                <span data-i18n="showBtn">Показать</span>
            </button>
        </form>
    </div>

    @if (session('success'))
        <div class="att-alert"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>
    @endif

    {{-- ===== СТАТИСТИКА ===== --}}
    <div class="att-stats">
        <div class="att-stat st-total">
            <div class="att-stat-icon"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="att-stat-num" data-count="{{ $totalCount }}">{{ $totalCount }}</div>
                <div class="att-stat-label" data-i18n="statTotal">Всего</div>
            </div>
        </div>
        <div class="att-stat st-green">
            <div class="att-stat-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div>
                <div class="att-stat-num" data-count="{{ $counters['on_time'] }}">{{ $counters['on_time'] }}</div>
                <div class="att-stat-label" data-i18n="statOnTime">Вовремя</div>
            </div>
        </div>
        <div class="att-stat st-orange">
            <div class="att-stat-icon"><i class="bi bi-clock-fill"></i></div>
            <div>
                <div class="att-stat-num" data-count="{{ $counters['late'] }}">{{ $counters['late'] }}</div>
                <div class="att-stat-label" data-i18n="statLate">Опоздали</div>
            </div>
        </div>
        <div class="att-stat st-red">
            <div class="att-stat-icon"><i class="bi bi-x-circle-fill"></i></div>
            <div>
                <div class="att-stat-num" data-count="{{ $counters['absent'] }}">{{ $counters['absent'] }}</div>
                <div class="att-stat-label" data-i18n="statAbsent">Не пришли</div>
            </div>
        </div>
        <div class="att-stat st-purple">
            <div class="att-stat-icon"><i class="bi bi-cash-coin"></i></div>
            <div>
                <div class="att-stat-num" data-count="-{{ number_format($dayFineTotal, 0, '.', '') }}">-{{ number_format($dayFineTotal, 0) }}</div>
                <div class="att-stat-label" data-i18n="statFines">Штрафы дня</div>
            </div>
        </div>
    </div>

    {{-- ===== ФИЛЬТРЫ ===== --}}
    <div class="att-filters">
        <a href="?date={{ $date }}&filter=all" class="att-filter f-all @if($filter==='all') active @endif">
            <i class="bi bi-grid-fill"></i> <span data-i18n="filterAll">Все</span>
            <span class="cnt">{{ $totalCount }}</span>
        </a>
        <a href="?date={{ $date }}&filter=on_time" class="att-filter f-ontime @if($filter==='on_time') active @endif">
            <i class="bi bi-check-circle-fill"></i> <span data-i18n="filterOnTime">Вовремя</span>
            <span class="cnt">{{ $counters['on_time'] }}</span>
        </a>
        <a href="?date={{ $date }}&filter=late" class="att-filter f-late @if($filter==='late') active @endif">
            <i class="bi bi-clock-fill"></i> <span data-i18n="filterLate">Опоздали</span>
            <span class="cnt">{{ $counters['late'] }}</span>
        </a>
        <a href="?date={{ $date }}&filter=absent" class="att-filter f-absent @if($filter==='absent') active @endif">
            <i class="bi bi-x-circle-fill"></i> <span data-i18n="filterAbsent">Не пришли</span>
            <span class="cnt">{{ $counters['absent'] }}</span>
        </a>
        <a href="?date={{ $date }}&filter=excused" class="att-filter f-excused @if($filter==='excused') active @endif">
            <i class="bi bi-shield-check"></i> <span data-i18n="filterExcused">Разрешено</span>
            <span class="cnt">{{ $counters['excused'] }}</span>
        </a>
    </div>

    {{-- ===== ПРАВИЛА ШТРАФОВ ===== --}}
    <div class="att-panel">
        <div class="att-panel-head">
            <div class="att-panel-title">
                <i class="bi bi-sliders"></i>
                <span data-i18n="rulesTitle">Правила штрафов</span>
            </div>
        </div>
        <div class="att-panel-body">
            <form method="POST" action="{{ route('admin.reports.settings') }}">
                @csrf

                @if (auth()->user()->isSuperAdmin())
                    <div class="att-field-company">
                        <label class="att-label"><i class="bi bi-building"></i><span data-i18n="companyLabel">Компания</span></label>
                        <select name="company_id" class="att-input" onchange="window.location='?settings_company='+this.value+'&date={{ $date }}'">
                            @foreach ($companies as $c)
                                <option value="{{ $c->id }}" @selected($settingsCompany && $settingsCompany->id === $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
                    <div class="att-company-note">
                        <span data-i18n="companyLabel">Компания</span>:
                        <b>{{ $settingsCompany?->name }}</b>
                    </div>
                @endif

                <div class="att-settings-grid">
                    <div>
                        <label class="att-label"><i class="bi bi-clock"></i><span data-i18n="workStartLabel">Начало работы</span></label>
                        <input type="time" name="work_start_time" class="att-input" value="{{ $settingsCompany->work_start_time ?? '08:30' }}">
                    </div>
                    <div>
                        <label class="att-label"><i class="bi bi-hourglass-split"></i><span data-i18n="everyMinLabel">Каждые (мин)</span></label>
                        <input type="number" name="late_block_minutes" min="1" class="att-input" value="{{ $settingsCompany->late_block_minutes ?? 60 }}">
                    </div>
                    <div>
                        <label class="att-label"><i class="bi bi-dash-circle"></i><span data-i18n="minusSomLabel">= минус (сом)</span></label>
                        <input type="number" step="0.01" name="late_block_fine" min="0" class="att-input" value="{{ $settingsCompany->late_block_fine ?? 100 }}">
                    </div>
                    <div>
                        <label class="att-label"><i class="bi bi-person-x"></i><span data-i18n="absenceSomLabel">Неявка (сом)</span></label>
                        <input type="number" step="0.01" name="absence_fine" min="0" class="att-input" value="{{ $settingsCompany->absence_fine ?? 200 }}">
                    </div>
                    <div class="att-field-save">
                        <button type="submit" class="btn-new att-save-btn">
                            <i class="bi bi-save"></i>
                            <span data-i18n="saveBtn">Сохранить</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== КАРТОЧКИ СОТРУДНИКОВ ===== --}}
    <div class="att-grid">
        @forelse ($cards as $c)
            @php
                $user = $c->user;
                $company = $user->companyRelation;
                $initials = strtoupper(mb_substr($user->name, 0, 1));
                $isOnline = method_exists($user, 'isOnline') ? $user->isOnline() : false;
                $workStart = substr($company->work_start_time ?? '08:30', 0, 5);

                $statusMeta = [
                    'on_time' => ['Вовремя', 'ontime', 's-ontime', 'bi-check-circle-fill', 'status_on_time'],
                    'late'    => ['Опоздал', 'late', 's-late', 'bi-clock-fill', 'status_late'],
                    'absent'  => ['Не пришёл', 'absent', 's-absent', 'bi-x-circle-fill', 'status_absent'],
                    'excused' => ['Разрешено', 'excused', 's-excused', 'bi-shield-check', 'status_excused'],
                    'waiting' => ['Ожидание', 'waiting', 's-waiting', 'bi-hourglass-split', 'status_waiting'],
                ];
                [$sLabel, $cClass, $sClass, $sIcon, $sKey] = $statusMeta[$c->status] ?? $statusMeta['waiting'];
            @endphp

            <div class="att-card c-{{ $cClass }}" style="animation-delay:{{ min($loop->index, 8) * 0.05 }}s">
                <div class="att-card-photo">
                    @if ($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="">
                    @else
                        <div class="att-card-initials">{{ $initials }}</div>
                    @endif

                    <div class="att-card-badges">
                        <span class="att-online-badge {{ $isOnline ? 'on' : 'off' }}">
                            <span data-i18n="{{ $isOnline ? 'online' : 'offline' }}">{{ $isOnline ? 'Онлайн' : 'Офлайн' }}</span>
                        </span>
                        <span class="att-status {{ $sClass }}" data-status-key="{{ $sKey }}">
                            <i class="bi {{ $sIcon }}"></i> {{ $sLabel }}
                        </span>
                    </div>
                </div>

                <div class="att-card-body">
                    <h3 class="att-card-name">{{ $user->name }}</h3>
                    <span class="att-card-role">{{ $user->role ?? 'Сотрудник' }}</span>

                    <div class="att-info-grid">
                        <div class="att-info-item">
                            <div class="att-info-label" data-i18n="infoStart">Начало</div>
                            <div class="att-info-value">{{ $workStart }}</div>
                        </div>
                        <div class="att-info-item">
                            <div class="att-info-label" data-i18n="infoArrival">Приход</div>
                            <div class="att-info-value {{ $c->time ? '' : 'att-v-muted' }}">
                                {{ $c->time ? substr($c->time, 0, 5) : '—' }}
                            </div>
                        </div>
                        <div class="att-info-item">
                            <div class="att-info-label" data-i18n="infoLate">Опоздал</div>
                            <div class="att-info-value {{ $c->late_minutes > 0 ? 'att-v-warning' : 'att-v-muted' }}">
                                @if ($c->late_minutes > 0)
                                    @if ($c->late_minutes >= 60)
                                        {{ intdiv($c->late_minutes, 60) }}<span data-i18n="lateH">ч</span> {{ $c->late_minutes % 60 }}<span data-i18n="lateM">м</span>
                                    @else
                                        {{ $c->late_minutes }} <span data-i18n="lateM">м</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div class="att-info-item">
                            <div class="att-info-label" data-i18n="infoFine">Штраф</div>
                            <div class="att-info-value {{ $c->fine > 0 ? 'att-v-danger' : 'att-v-success' }}">
                                {{ $c->fine > 0 ? '-' . number_format($c->fine, 0) : '0' }}
                            </div>
                        </div>
                    </div>

                    <div class="att-money-grid">
                        <div class="att-money-item">
                            <div class="att-money-label" data-i18n="monthLabel">Месяц</div>
                            <div class="att-money-value {{ $c->month_fine > 0 ? 'att-m-red' : '' }}">
                                {{ $c->month_fine > 0 ? '-' . number_format($c->month_fine, 0) : '0' }} <small data-i18n="som">сом</small>
                            </div>
                        </div>
                        <div class="att-money-item">
                            <div class="att-money-label" data-i18n="salaryLabel">Зарплата</div>
                            <div class="att-money-value">{{ number_format($c->salary, 0) }} <small data-i18n="som">сом</small></div>
                        </div>
                        <div class="att-money-item">
                            <div class="att-money-label" data-i18n="payoutLabel">К выплате</div>
                            <div class="att-money-value att-m-green">{{ number_format($c->payout, 0) }} <small data-i18n="som">сом</small></div>
                        </div>
                    </div>

                    <div class="att-card-actions">
                        <a href="{{ route('users.show', $user->id) }}" class="att-act">
                            <i class="bi bi-eye-fill"></i> <span data-i18n="profileBtn">Профиль</span>
                        </a>
                        @if (in_array($c->status, ['absent', 'late', 'waiting']))
                            <form method="POST" action="{{ route('admin.reports.excuse') }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <input type="hidden" name="date" value="{{ $date }}">
                                <button type="submit" class="att-act excuse">
                                    <i class="bi bi-shield-check"></i> <span data-i18n="excuseBtn">Я знал</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="att-empty">
                <div class="att-empty-icon"><i class="bi bi-person-x"></i></div>
                <div class="att-empty-title" data-i18n="emptyTitle">Нет сотрудников</div>
                <div class="att-empty-desc">
                    @if ($filter !== 'all')
                        <span data-i18n="emptyFiltered">В этой категории никого нет.</span>
                        <a href="?date={{ $date }}" class="att-empty-link" data-i18n="showAll">Показать всех</a>
                    @else
                        <span data-i18n="emptyAll">Добавьте сотрудников с галочкой «нужен скан»</span>
                    @endif
                </div>
            </div>
        @endforelse
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ============================================================
        // СЛОВАРЬ СТРАНИЦЫ ПОСЕЩАЕМОСТИ (RU / TJ / EN)
        // ============================================================
        const ATT_TRANSLATIONS = {
            ru: {
                attTitle: 'ПОСЕЩАЕМОСТЬ КОМАНДЫ',
                attSubtitle: 'Все сотрудники и их действия',
                showBtn: 'Показать',
                statTotal: 'Всего',
                statOnTime: 'Вовремя',
                statLate: 'Опоздали',
                statAbsent: 'Не пришли',
                statFines: 'Штрафы дня',
                filterAll: 'Все',
                filterOnTime: 'Вовремя',
                filterLate: 'Опоздали',
                filterAbsent: 'Не пришли',
                filterExcused: 'Разрешено',
                rulesTitle: 'Правила штрафов',
                companyLabel: 'Компания',
                workStartLabel: 'Начало работы',
                everyMinLabel: 'Каждые (мин)',
                minusSomLabel: '= минус (сом)',
                absenceSomLabel: 'Неявка (сом)',
                saveBtn: 'Сохранить',
                online: 'Онлайн',
                offline: 'Офлайн',
                infoStart: 'Начало',
                infoArrival: 'Приход',
                infoLate: 'Опоздал',
                infoFine: 'Штраф',
                monthLabel: 'Месяц',
                salaryLabel: 'Зарплата',
                payoutLabel: 'К выплате',
                som: 'сом',
                profileBtn: 'Профиль',
                excuseBtn: 'Я знал',
                emptyTitle: 'Нет сотрудников',
                emptyFiltered: 'В этой категории никого нет.',
                showAll: 'Показать всех',
                emptyAll: 'Добавьте сотрудников с галочкой «нужен скан»',
                status_on_time: 'Вовремя',
                status_late: 'Опоздал',
                status_absent: 'Не пришёл',
                status_excused: 'Разрешено',
                status_waiting: 'Ожидание',
                lateH: 'ч',
                lateM: 'мин'
            },
            tj: {
                attTitle: 'ҲУЗУРИ ДАСТА',
                attSubtitle: 'Ҳамаи кормандон ва амалҳои онҳо',
                showBtn: 'Нишон додан',
                statTotal: 'Ҳамагӣ',
                statOnTime: 'Сари вақт',
                statLate: 'Деркарда',
                statAbsent: 'Наомада',
                statFines: 'Ҷаримаҳои рӯз',
                filterAll: 'Ҳама',
                filterOnTime: 'Сари вақт',
                filterLate: 'Деркарда',
                filterAbsent: 'Наомада',
                filterExcused: 'Иҷозат',
                rulesTitle: 'Қоидаҳои ҷарима',
                companyLabel: 'Ширкат',
                workStartLabel: 'Оғози кор',
                everyMinLabel: 'Ҳар (дақиқа)',
                minusSomLabel: '= минус (сом)',
                absenceSomLabel: 'Неомадан (сом)',
                saveBtn: 'Захира',
                online: 'Онлайн',
                offline: 'Офлайн',
                infoStart: 'Оғоз',
                infoArrival: 'Омад',
                infoLate: 'Деромад',
                infoFine: 'Ҷарима',
                monthLabel: 'Моҳ',
                salaryLabel: 'Маош',
                payoutLabel: 'Пардохт',
                som: 'сом',
                profileBtn: 'Профил',
                excuseBtn: 'Медонистам',
                emptyTitle: 'Кормандон нестанд',
                emptyFiltered: 'Дар ин категория касе нест.',
                showAll: 'Ҳамаро нишон додан',
                emptyAll: 'Кормандонро бо қайди «скан лозим» илова кунед',
                status_on_time: 'Сари вақт',
                status_late: 'Дер кард',
                status_absent: 'Наомад',
                status_excused: 'Иҷозат',
                status_waiting: 'Интизорӣ',
                lateH: 'с',
                lateM: 'дақ'
            },
            en: {
                attTitle: 'TEAM ATTENDANCE',
                attSubtitle: 'All employees and their actions',
                showBtn: 'Show',
                statTotal: 'Total',
                statOnTime: 'On time',
                statLate: 'Late',
                statAbsent: 'Absent',
                statFines: 'Day fines',
                filterAll: 'All',
                filterOnTime: 'On time',
                filterLate: 'Late',
                filterAbsent: 'Absent',
                filterExcused: 'Excused',
                rulesTitle: 'Fine rules',
                companyLabel: 'Company',
                workStartLabel: 'Work start',
                everyMinLabel: 'Every (min)',
                minusSomLabel: '= minus (som)',
                absenceSomLabel: 'Absence (som)',
                saveBtn: 'Save',
                online: 'Online',
                offline: 'Offline',
                infoStart: 'Start',
                infoArrival: 'Arrival',
                infoLate: 'Late',
                infoFine: 'Fine',
                monthLabel: 'Month',
                salaryLabel: 'Salary',
                payoutLabel: 'Payout',
                som: 'som',
                profileBtn: 'Profile',
                excuseBtn: 'I knew',
                emptyTitle: 'No employees',
                emptyFiltered: 'Nobody in this category.',
                showAll: 'Show all',
                emptyAll: 'Add employees with the "scan required" flag',
                status_on_time: 'On time',
                status_late: 'Late',
                status_absent: 'Absent',
                status_excused: 'Excused',
                status_waiting: 'Waiting',
                lateH: 'h',
                lateM: 'min'
            }
        };

        // ============================================================
        // ПРИМЕНЕНИЕ ПЕРЕВОДОВ
        // ============================================================
        function applyAttTranslations(lang) {
            const dict = ATT_TRANSLATIONS[lang] || ATT_TRANSLATIONS.ru;

            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (dict[key] !== undefined) el.textContent = dict[key];
            });

            document.querySelectorAll('[data-status-key]').forEach(el => {
                const key = el.getAttribute('data-status-key');
                if (dict[key]) {
                    const icon = el.querySelector('i');
                    el.textContent = '';
                    if (icon) el.appendChild(icon);
                    el.appendChild(document.createTextNode(' ' + dict[key]));
                }
            });
        }

        // ============================================================
        // АНИМАЦИЯ ЦИФР (count-up)
        // ============================================================
        function animateCounters() {
            document.querySelectorAll('.att-stat-num[data-count]').forEach(el => {
                const target = parseFloat(el.getAttribute('data-count'));
                if (isNaN(target)) return;
                const dur = 900;
                const start = performance.now();
                const fmt = v => Math.round(v).toLocaleString('ru-RU');
                function tick(now) {
                    const p = Math.min(1, (now - start) / dur);
                    const eased = 1 - Math.pow(1 - p, 3);
                    el.textContent = fmt(target * eased);
                    if (p < 1) requestAnimationFrame(tick);
                    else el.textContent = fmt(target);
                }
                requestAnimationFrame(tick);
            });
        }

        // Старт
        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applyAttTranslations(initialLang);
        animateCounters();

        // Смена языка из layout
        window.addEventListener('docsign:lang-changed', (e) => {
            applyAttTranslations(e.detail?.lang || 'ru');
        });

        // Синхронизация между вкладками
        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applyAttTranslations(e.newValue);
            }
        });
    });
</script>
@endsection