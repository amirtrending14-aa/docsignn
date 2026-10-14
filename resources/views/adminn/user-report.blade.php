@extends('layouts.admin')

@section('content')
<style>
    /* ============================================================ */
    /* ===== ФОН ПО ТЕМЕ: ТЁМНАЯ → ЧЁРНЫЙ, СВЕТЛАЯ → СВЕТЛЫЙ ===== */
    /* ============================================================ */
    html[data-theme="dark"] body{ background:#06070b !important; color:#e7ecf3 !important; }
    html[data-theme="light"] body{ background:#f4f6fa !important; color:#1e293b !important; }

    .ur-page { color: var(--text, #e7ecf3); max-width: 100%; overflow-x: clip; }
    @keyframes urFadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }

    /* ---------- ШАПКА ---------- */
    .ur-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
    .ur-back { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 9px; background: rgba(255,255,255,0.04); border: 1px solid var(--line); color: var(--muted); text-decoration: none; font-size: 11px; font-weight: 600; transition: all .2s ease; }
    .ur-back:hover { color: var(--text); border-color: rgba(var(--glow), .4); transform: translateY(-1px); }
    .ur-user { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .ur-avatar { width: 40px; height: 40px; border-radius: 10px; overflow: hidden; display: grid; place-items: center; background: linear-gradient(135deg, rgba(var(--glow), .5), rgba(var(--glow), .15)); border: 1px solid rgba(var(--glow), .3); font-weight: 900; font-size: 15px; color: #fff; flex-shrink: 0; }
    .ur-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .ur-user-name { font-size: 14px; font-weight: 800; margin: 0; letter-spacing: -.2px; color: var(--text); }
    .ur-user-meta { font-size: 10px; color: var(--muted); font-weight: 600; margin-top: 1px; }
    .ur-month-nav { display: flex; align-items: center; gap: 6px; }
    .ur-nav-btn { width: 30px; height: 30px; border-radius: 8px; display: grid; place-items: center; background: rgba(255,255,255,0.04); border: 1px solid var(--line); color: var(--muted); text-decoration: none; transition: all .2s ease; }
    .ur-nav-btn i { font-size: 12px; }
    .ur-nav-btn:hover { color: rgba(var(--glow), 1); border-color: rgba(var(--glow), .4); }
    .ur-month-label { min-width: 130px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; color: rgba(var(--glow), 1); }

    /* ---------- АЛЕРТЫ ---------- */
    .ur-alert { display: flex; align-items: center; gap: 8px; background: rgba(76,217,130,0.08); border: 1px solid rgba(76,217,130,0.3); color: #4cd982; padding: 9px 13px; border-radius: 10px; margin-bottom: 14px; font-size: 12px; font-weight: 600; animation: urFadeUp .4s ease both; }
    .ur-alert.err { background: rgba(255,122,122,0.08); border-color: rgba(255,122,122,0.3); color: #ff7a7a; }
    .ur-alert i { font-size: 13px; }

    /* ---------- ИТОГИ МЕСЯЦА ---------- */
    .ur-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(90px, 1fr)); gap: 8px; margin-bottom: 14px; }
    .ur-sum { background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.01)); border: 1px solid var(--line); border-radius: 10px; padding: 9px 8px; text-align: center; animation: urFadeUp .5s ease both; }
    .ur-sum-num { font-family: 'JetBrains Mono', monospace; font-size: 14px; font-weight: 800; line-height: 1; color: var(--text); }
    .ur-sum-label { font-size: 8px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .7px; margin-top: 4px; }
    .ur-c-green { color: #4cd982 !important; }
    .ur-c-orange { color: #ffb547 !important; }
    .ur-c-red { color: #ff7a7a !important; }
    .ur-c-gray { color: #aab4c8 !important; }
    .ur-c-glow { color: rgba(var(--glow), 1) !important; }

    /* ---------- КАЛЕНДАРЬ ---------- */
    .ur-cal-panel { background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01)); border: 1px solid var(--line); border-radius: 10px; padding: 10px; margin-bottom: 14px; animation: urFadeUp .55s ease both; }
    .ur-weekdays { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; margin-bottom: 5px; }
    .ur-weekdays span { text-align: center; font-size: 8px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .5px; padding: 3px 0; }
    .ur-cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; }
    .ur-day { position: relative; aspect-ratio: 1 / 0.75; max-width: 55px; border-radius: 7px; border: 1px solid var(--line); background: rgba(255,255,255,0.025); color: var(--text); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1px; cursor: pointer; transition: all .15s ease; font-family: 'JetBrains Mono', monospace; padding: 0; }
    .ur-day:hover:not(:disabled):not(.blank) { transform: translateY(-1px); }
    .ur-day-num { font-size: 11px; font-weight: 700; line-height: 1; }
    .ur-day-fine { font-size: 7px; font-weight: 700; min-height: 8px; line-height: 1; }
    .ur-day.blank { background: transparent; border-color: transparent; cursor: default; pointer-events: none; }
    .ur-day.st-ontime  { background: rgba(76,217,130,0.12); border-color: rgba(76,217,130,0.45); color: #4cd982; box-shadow: inset 0 0 6px rgba(76,217,130,.08); }
    .ur-day.st-late    { background: rgba(255,181,71,0.12); border-color: rgba(255,181,71,0.45); color: #ffb547; box-shadow: inset 0 0 6px rgba(255,181,71,.08); }
    .ur-day.st-absent  { background: rgba(255,122,122,0.12); border-color: rgba(255,122,122,0.45); color: #ff7a7a; box-shadow: inset 0 0 6px rgba(255,122,122,.08); }
    .ur-day.st-excused { background: rgba(136,146,166,0.12); border-color: rgba(136,146,166,0.4); color: #aab4c8; }
    .ur-day.st-waiting { background: rgba(94,198,255,0.12); border-color: rgba(94,198,255,0.45); color: #5ec6ff; }
    .ur-day.st-future  { opacity: .25; cursor: not-allowed; }
    .ur-day.today { outline: 1.5px solid rgba(var(--glow), .7); outline-offset: 1px; }
    .ur-day.selected { box-shadow: 0 0 0 1.5px rgba(var(--glow), .9), 0 0 12px rgba(var(--glow), .3); }

    .ur-legend { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--line); }
    .ur-legend-item { display: inline-flex; align-items: center; gap: 5px; font-size: 9px; color: var(--muted); font-weight: 600; }
    .ur-legend-dot { width: 7px; height: 7px; border-radius: 3px; }

    /* ---------- ДЕТАЛИ ДНЯ ---------- */
    .ur-detail { background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.01)); border: 1px solid rgba(var(--glow), .35); border-radius: 10px; padding: 12px 14px; display: none; align-items: center; gap: 14px; flex-wrap: wrap; animation: urFadeUp .25s ease both; box-shadow: 0 0 16px rgba(var(--glow), .1); }
    .ur-detail-date { font-family: 'JetBrains Mono', monospace; font-size: 13px; font-weight: 800; color: rgba(var(--glow), 1); min-width: 85px; letter-spacing: -.2px; }
    .ur-detail-items { display: flex; gap: 14px; flex-wrap: wrap; flex: 1; }
    .ur-detail-item .l { font-size: 7.5px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 2px; }
    .ur-detail-item .v { font-family: 'JetBrains Mono', monospace; font-size: 11.5px; font-weight: 700; color: var(--text); }
    .ur-status-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 9px; border-radius: 6px; font-size: 8.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .4px; }
    .ur-status-badge.b-ontime  { background: rgba(76,217,130,0.95); color: #062b16; }
    .ur-status-badge.b-late    { background: rgba(255,181,71,0.95); color: #241503; }
    .ur-status-badge.b-absent  { background: rgba(255,122,122,0.95); color: #2b0a0a; }
    .ur-status-badge.b-excused { background: rgba(136,146,166,0.95); color: #0a0d14; }
    .ur-status-badge.b-waiting { background: rgba(94,198,255,0.95); color: #06202e; }
    .ur-btn { appearance: none; border: 0; background: linear-gradient(180deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.65)); color: #0a0d14; font: 700 10.5px 'Inter', sans-serif; padding: 7px 13px; border-radius: 7px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 5px 16px rgba(var(--glow), 0.3); transition: all .2s ease; text-decoration: none; }
    .ur-btn:hover { filter: brightness(1.08); transform: translateY(-1px); }
    .ur-btn i { font-size: 10px; }

    /* ---------- RESPONSIVE ---------- */
    @media (max-width: 992px) {
        .ur-day { max-width: 50px; }
        .ur-day-num { font-size: 10px; }
        .ur-day-fine { font-size: 6.5px; }
        .ur-cal-grid, .ur-weekdays { gap: 3px; }
    }
    @media (max-width: 768px) {
        .ur-head { flex-direction: column; align-items: flex-start; gap: 10px; }
        .ur-month-nav { width: 100%; justify-content: space-between; }
        .ur-summary { grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); gap: 6px; }
        .ur-sum { padding: 8px 6px; }
        .ur-sum-num { font-size: 13px; }
        .ur-sum-label { font-size: 7.5px; }
        .ur-cal-panel { padding: 8px; }
        .ur-day { max-width: 45px; }
        .ur-day-num { font-size: 9.5px; }
        .ur-day-fine { font-size: 6px; }
        .ur-cal-grid, .ur-weekdays { gap: 3px; }
        .ur-detail { flex-direction: column; align-items: flex-start; gap: 10px; padding: 10px; }
        .ur-detail-items { width: 100%; gap: 10px; }
        .ur-detail-item { flex: 1; min-width: 65px; }
        .ur-btn { width: 100%; justify-content: center; }
        .ur-legend { gap: 7px; }
        .ur-legend-item { font-size: 8px; }
    }
    @media (max-width: 480px) {
        .ur-user-name { font-size: 13px; }
        .ur-avatar { width: 36px; height: 36px; font-size: 14px; }
        .ur-day { max-width: 40px; }
        .ur-day-num { font-size: 9px; }
        .ur-day-fine { font-size: 5.5px; }
        .ur-cal-grid, .ur-weekdays { gap: 2px; }
        .ur-month-label { font-size: 10px; min-width: 110px; }
        .ur-weekdays span { font-size: 7px; }
    }
</style>

<style>
    /* ============================================================ */
    /* ===== ПОЛНАЯ СВЕТЛАЯ ТЕМА ===== */
    /* ============================================================ */
    html[data-theme="light"] .ur-page { color: #0f172a !important; }

    html[data-theme="light"] .ur-user-name,
    html[data-theme="light"] .ur-detail-item .v {
        color: #0f172a !important;
    }
    html[data-theme="light"] .ur-user-meta,
    html[data-theme="light"] .ur-sum-label,
    html[data-theme="light"] .ur-weekdays span,
    html[data-theme="light"] .ur-legend-item,
    html[data-theme="light"] .ur-detail-item .l {
        color: #475569 !important;
    }

    /* Шапка */
    html[data-theme="light"] .ur-back {
        background: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #475569;
        box-shadow: 0 2px 6px rgba(15,23,42,0.04);
    }
    html[data-theme="light"] .ur-back:hover {
        color: rgba(var(--glow), 1);
        border-color: rgba(var(--glow), 0.45);
    }

    html[data-theme="light"] .ur-avatar {
        background: linear-gradient(135deg, rgba(var(--glow), 0.80), rgba(var(--glow), 0.40));
        border-color: rgba(var(--glow), 0.45);
        color: #ffffff;
        text-shadow: 0 1px 2px rgba(0,0,0,0.15);
    }

    html[data-theme="light"] .ur-nav-btn {
        background: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #475569;
        box-shadow: 0 2px 6px rgba(15,23,42,0.04);
    }
    html[data-theme="light"] .ur-nav-btn:hover {
        color: rgba(var(--glow), 1);
        border-color: rgba(var(--glow), 0.45);
    }

    /* Алерты */
    html[data-theme="light"] .ur-alert {
        background: #f0fdf4;
        border-color: #86efac;
        color: #166534;
        box-shadow: 0 2px 8px rgba(16,185,129,0.10);
    }
    html[data-theme="light"] .ur-alert.err {
        background: #fef2f2;
        border-color: #fecaca;
        color: #991b1b;
        box-shadow: 0 2px 8px rgba(220,38,38,0.08);
    }

    /* Итоги месяца */
    html[data-theme="light"] .ur-sum {
        background: #ffffff !important;
        border-color: #cbd5e1 !important;
        box-shadow: 0 4px 12px rgba(15,23,42,0.05);
    }
    html[data-theme="light"] .ur-sum-num { color: #0f172a; }
    html[data-theme="light"] .ur-c-green  { color: #059669 !important; }
    html[data-theme="light"] .ur-c-orange { color: #d97706 !important; }
    html[data-theme="light"] .ur-c-red    { color: #dc2626 !important; }
    html[data-theme="light"] .ur-c-gray   { color: #64748b !important; }
    html[data-theme="light"] .ur-c-glow   { color: rgba(var(--glow), 1) !important; }

    /* Календарь */
    html[data-theme="light"] .ur-cal-panel {
        background: #ffffff !important;
        border-color: #cbd5e1 !important;
        box-shadow: 0 6px 20px rgba(15,23,42,0.06);
    }
    html[data-theme="light"] .ur-day {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a;
    }
    html[data-theme="light"] .ur-day.blank {
        background: transparent;
        border-color: transparent;
        box-shadow: none;
    }
    html[data-theme="light"] .ur-day.st-ontime {
        background: #dcfce7;
        border-color: #86efac;
        color: #166534;
        box-shadow: none;
    }
    html[data-theme="light"] .ur-day.st-late {
        background: #fef3c7;
        border-color: #fde68a;
        color: #92400e;
        box-shadow: none;
    }
    html[data-theme="light"] .ur-day.st-absent {
        background: #fee2e2;
        border-color: #fecaca;
        color: #991b1b;
        box-shadow: none;
    }
    html[data-theme="light"] .ur-day.st-excused {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #475569;
        box-shadow: none;
    }
    html[data-theme="light"] .ur-day.st-waiting {
        background: #dbeafe;
        border-color: #bfdbfe;
        color: #1e40af;
        box-shadow: none;
    }
    html[data-theme="light"] .ur-day.st-future { opacity: 0.4; }
    html[data-theme="light"] .ur-day.today {
        outline: 1.5px solid rgba(var(--glow), 0.8);
        outline-offset: 1px;
    }
    html[data-theme="light"] .ur-day.selected {
        box-shadow: 0 0 0 2px rgba(var(--glow), 0.85), 0 4px 12px rgba(var(--glow), 0.20);
    }
    html[data-theme="light"] .ur-day:hover:not(:disabled):not(.blank) {
        border-color: rgba(var(--glow), 0.40);
    }

    /* Легенда */
    html[data-theme="light"] .ur-legend {
        border-top-color: #e2e8f0;
    }

    /* Детали дня */
    html[data-theme="light"] .ur-detail {
        background: #ffffff !important;
        border-color: rgba(var(--glow), 0.40) !important;
        box-shadow: 0 6px 20px rgba(15,23,42,0.08);
    }
    html[data-theme="light"] .ur-detail-date {
        color: rgba(var(--glow), 1);
        font-weight: 900;
    }

    html[data-theme="light"] .ur-status-badge.b-ontime  { background: #dcfce7; color: #166534; }
    html[data-theme="light"] .ur-status-badge.b-late    { background: #fef3c7; color: #92400e; }
    html[data-theme="light"] .ur-status-badge.b-absent  { background: #fee2e2; color: #991b1b; }
    html[data-theme="light"] .ur-status-badge.b-excused { background: #f1f5f9; color: #334155; }
    html[data-theme="light"] .ur-status-badge.b-waiting { background: #dbeafe; color: #1e40af; }

    /* Кнопка "Я знал" */
    html[data-theme="light"] .ur-btn {
        background: linear-gradient(180deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.75));
        color: #0a0d14;
        border: 1px solid rgba(var(--glow), 0.40);
        box-shadow: 0 4px 12px rgba(var(--glow), 0.22), inset 0 1px 0 rgba(255,255,255,0.5);
    }
</style>

<div class="ur-page">

    {{-- ===== ШАПКА ===== --}}
    <div class="ur-head">
        <a href="{{ route('admin.reports') }}?date={{ now()->toDateString() }}" class="ur-back">
            <i class="bi bi-arrow-left"></i> <span data-i18n="backBtn">Назад</span>
        </a>

        <div class="ur-user">
            <div class="ur-avatar">
                @if ($user->avatar)
                    <img src="{{ $user->avatar_url }}" alt="">
                @else
                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                @endif
            </div>
            <div style="min-width:0">
                <h1 class="ur-user-name">{{ $user->name }}</h1>
                <div class="ur-user-meta">{{ $user->role ?? 'Сотрудник' }} · {{ $company?->name }}</div>
            </div>
        </div>

        <div class="ur-month-nav">
            <a href="?month={{ $prevMonth }}" class="ur-nav-btn"><i class="bi bi-chevron-left"></i></a>
            <div class="ur-month-label" data-month="{{ $monthParam }}"></div>
            <a href="?month={{ $nextMonth }}" class="ur-nav-btn"><i class="bi bi-chevron-right"></i></a>
        </div>
    </div>

    @if (session('success'))
        <div class="ur-alert"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="ur-alert err"><i class="bi bi-exclamation-triangle-fill"></i>{{ $errors->first() }}</div>
    @endif

    {{-- ===== ИТОГИ МЕСЯЦА ===== --}}
    <div class="ur-summary">
        <div class="ur-sum"><div class="ur-sum-num ur-c-green">{{ $counters['on_time'] }}</div><div class="ur-sum-label" data-i18n="statOnTime">Вовремя</div></div>
        <div class="ur-sum"><div class="ur-sum-num ur-c-orange">{{ $counters['late'] }}</div><div class="ur-sum-label" data-i18n="statLate">Опоздали</div></div>
        <div class="ur-sum"><div class="ur-sum-num ur-c-red">{{ $counters['absent'] }}</div><div class="ur-sum-label" data-i18n="statAbsent">Не пришли</div></div>
        <div class="ur-sum"><div class="ur-sum-num ur-c-gray">{{ $counters['excused'] }}</div><div class="ur-sum-label" data-i18n="statExcused">Разрешено</div></div>
        <div class="ur-sum"><div class="ur-sum-num ur-c-red">-{{ number_format($totalFine, 0) }}</div><div class="ur-sum-label" data-i18n="monthFineLabel">Штрафы мес.</div></div>
        <div class="ur-sum"><div class="ur-sum-num">{{ number_format($salary, 0) }}</div><div class="ur-sum-label" data-i18n="salaryLabel">Зарплата</div></div>
        <div class="ur-sum"><div class="ur-sum-num ur-c-green">{{ number_format($payout, 0) }}</div><div class="ur-sum-label" data-i18n="payoutLabel">К выплате</div></div>
    </div>

    {{-- ===== КАЛЕНДАРЬ ===== --}}
    <div class="ur-cal-panel">
        <div class="ur-weekdays">
            <span data-i18n="wd1">Пн</span><span data-i18n="wd2">Вт</span><span data-i18n="wd3">Ср</span>
            <span data-i18n="wd4">Чт</span><span data-i18n="wd5">Пт</span><span data-i18n="wd6">Сб</span><span data-i18n="wd7">Вс</span>
        </div>
        <div class="ur-cal-grid">
            @for ($i = 0; $i < $leadingBlanks; $i++)
                <div class="ur-day blank"></div>
            @endfor

            @foreach ($days as $d)
                <button type="button"
                    class="ur-day st-{{ $d['status'] }} @if($d['is_today']) today @endif"
                    data-date="{{ $d['date'] }}"
                    data-day="{{ $d['day'] }}"
                    data-status="{{ $d['status'] }}"
                    data-fine="{{ $d['fine'] }}"
                    data-time="{{ $d['time'] }}"
                    data-late="{{ $d['late_minutes'] }}"
                    @if($d['status'] === 'future') disabled @endif>
                    <span class="ur-day-num">{{ $d['day'] }}</span>
                    <span class="ur-day-fine">@if($d['fine'] > 0)-{{ number_format($d['fine'], 0) }}@endif</span>
                </button>
            @endforeach
        </div>

        <div class="ur-legend">
            <span class="ur-legend-item"><span class="ur-legend-dot" style="background:#4cd982"></span><span data-i18n="status_on_time">Вовремя</span></span>
            <span class="ur-legend-item"><span class="ur-legend-dot" style="background:#ffb547"></span><span data-i18n="status_late">Опоздал</span></span>
            <span class="ur-legend-item"><span class="ur-legend-dot" style="background:#ff7a7a"></span><span data-i18n="status_absent">Не пришёл</span></span>
            <span class="ur-legend-item"><span class="ur-legend-dot" style="background:#8892a6"></span><span data-i18n="status_excused">Разрешено</span></span>
            <span class="ur-legend-item"><span class="ur-legend-dot" style="background:#5ec6ff"></span><span data-i18n="status_waiting">Ожидание</span></span>
        </div>
    </div>

    {{-- ===== ДЕТАЛИ ДНЯ ===== --}}
    <div class="ur-detail" id="urDetail">
        <div class="ur-detail-date" id="urDDate">—</div>
        <span class="ur-status-badge" id="urDStatus">—</span>
        <div class="ur-detail-items">
            <div class="ur-detail-item"><div class="l" data-i18n="infoArrival">Приход</div><div class="v" id="urDTime">—</div></div>
            <div class="ur-detail-item"><div class="l" data-i18n="infoLate">Опоздал</div><div class="v" id="urDLate">—</div></div>
            <div class="ur-detail-item"><div class="l" data-i18n="infoFine">Штраф</div><div class="v" id="urDFine">—</div></div>
        </div>
        <form method="POST" action="{{ route('admin.reports.excuse') }}" id="urExcuseForm" style="display:none">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <input type="hidden" name="date" id="urExcuseDate" value="">
            <button type="submit" class="ur-btn"><i class="bi bi-shield-check"></i> <span data-i18n="excuseBtn">Я знал</span></button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const UR = {
        ru: {
            backBtn: 'Назад', statOnTime: 'Вовремя', statLate: 'Опоздали', statAbsent: 'Не пришли',
            statExcused: 'Разрешено', monthFineLabel: 'Штрафы мес.', salaryLabel: 'Зарплата', payoutLabel: 'К выплате',
            wd1: 'Пн', wd2: 'Вт', wd3: 'Ср', wd4: 'Чт', wd5: 'Пт', wd6: 'Сб', wd7: 'Вс',
            status_on_time: 'Вовремя', status_late: 'Опоздал', status_absent: 'Не пришёл', status_excused: 'Разрешено', status_waiting: 'Ожидание',
            infoArrival: 'Приход', infoLate: 'Опоздал', infoFine: 'Штраф', excuseBtn: 'Я знал',
            months: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь']
        },
        tj: {
            backBtn: 'Бозгашт', statOnTime: 'Сари вақт', statLate: 'Деркарда', statAbsent: 'Наомада',
            statExcused: 'Иҷозат', monthFineLabel: 'Ҷаримаи моҳ', salaryLabel: 'Маош', payoutLabel: 'Пардохт',
            wd1: 'Дш', wd2: 'Сш', wd3: 'Чш', wd4: 'Пш', wd5: 'Ҷм', wd6: 'Шб', wd7: 'Яш',
            status_on_time: 'Сари вақт', status_late: 'Дер кард', status_absent: 'Наомад', status_excused: 'Иҷозат', status_waiting: 'Интизорӣ',
            infoArrival: 'Омад', infoLate: 'Деромад', infoFine: 'Ҷарима', excuseBtn: 'Медонистам',
            months: ['Январ','Феврал','Март','Апрел','Май','Июн','Июл','Август','Сентябр','Октябр','Ноябр','Декабр']
        },
        en: {
            backBtn: 'Back', statOnTime: 'On time', statLate: 'Late', statAbsent: 'Absent',
            statExcused: 'Excused', monthFineLabel: 'Month fines', salaryLabel: 'Salary', payoutLabel: 'Payout',
            wd1: 'Mo', wd2: 'Tu', wd3: 'We', wd4: 'Th', wd5: 'Fr', wd6: 'Sa', wd7: 'Su',
            status_on_time: 'On time', status_late: 'Late', status_absent: 'Absent', status_excused: 'Excused', status_waiting: 'Waiting',
            infoArrival: 'Arrival', infoLate: 'Late', infoFine: 'Fine', excuseBtn: 'I knew',
            months: ['January','February','March','April','May','June','July','August','September','October','November','December']
        }
    };

    function applyUr(lang) {
        const d = UR[lang] || UR.ru;
        document.querySelectorAll('.ur-page [data-i18n]').forEach(el => {
            const k = el.getAttribute('data-i18n');
            if (d[k]) el.textContent = d[k];
        });
        const ml = document.querySelector('.ur-month-label');
        if (ml && ml.dataset.month) {
            const [y, m] = ml.dataset.month.split('-');
            ml.textContent = d.months[parseInt(m) - 1] + ' ' + y;
        }
    }

    const lang0 = localStorage.getItem('docsign_lang') || 'ru';
    applyUr(lang0);
    window.addEventListener('docsign:lang-changed', e => applyUr(e.detail?.lang || 'ru'));

    const STATUS_CLASS = { on_time: 'b-ontime', late: 'b-late', absent: 'b-absent', excused: 'b-excused', waiting: 'b-waiting' };
    const STATUS_KEY   = { on_time: 'status_on_time', late: 'status_late', absent: 'status_absent', excused: 'status_excused', waiting: 'status_waiting' };

    document.querySelectorAll('.ur-day[data-date]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.disabled) return;
            document.querySelectorAll('.ur-day.selected').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');

            const s = btn.dataset.status;
            const lang = localStorage.getItem('docsign_lang') || 'ru';
            const dict = UR[lang] || UR.ru;
            const fine = parseFloat(btn.dataset.fine) || 0;
            const late = parseInt(btn.dataset.late) || 0;

            document.getElementById('urDDate').textContent = btn.dataset.date;

            const badge = document.getElementById('urDStatus');
            badge.className = 'ur-status-badge ' + (STATUS_CLASS[s] || '');
            badge.textContent = dict[STATUS_KEY[s]] || s;

            document.getElementById('urDTime').textContent = btn.dataset.time ? btn.dataset.time.substring(0, 5) : '—';
            document.getElementById('urDLate').textContent = late > 0 ? late + ' ' + (lang === 'en' ? 'min' : 'мин') : '—';

            const fineEl = document.getElementById('urDFine');
            fineEl.textContent = fine > 0 ? '-' + fine.toLocaleString('ru-RU') : '0';
            // Цвет штрафа в светлой теме делаем темнее
            const isLight = document.documentElement.dataset.theme === 'light';
            fineEl.style.color = fine > 0 ? (isLight ? '#dc2626' : '#ff7a7a') : (isLight ? '#059669' : '#4cd982');

            const form = document.getElementById('urExcuseForm');
            form.style.display = ['absent', 'late', 'waiting'].includes(s) ? 'block' : 'none';
            document.getElementById('urExcuseDate').value = btn.dataset.date;

            document.getElementById('urDetail').style.display = 'flex';
        });
    });
});
</script>
@endsection