@extends('layouts.admin')

@section('content')
{{-- Подключение шрифтов Inter и JetBrains Mono --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

@php
    $authUser   = auth()->user();
    $firstName  = $authUser ? explode(' ', trim($authUser->name))[0] : '';
    $recentNotifs = $recentNotifications ?? ($authUser ? $authUser->notifications()->latest()->take(4)->get() : collect());
    $notifAllUrl  = Route::has('notifications.index') ? route('notifications.index') : '#';
    $curMonth = (int) ($month ?? now()->month);
    $curYear  = (int) ($year ?? now()->year);
    $prevM = \Carbon\Carbon::createFromDate($curYear, $curMonth, 1)->subMonth();
    $nextM = \Carbon\Carbon::createFromDate($curYear, $curMonth, 1)->addMonth();
    $isCurrentMonth = ($curYear == now()->year && $curMonth == now()->month);
    $summary = $summaryCards ?? ['total' => 0, 'processing' => 0, 'archive' => 0, 'rejected' => 0];
    $period  = request('period', '7d');
    $periods = ['7d' => ['period_7d', '7 дней'], '30d' => ['period_30d', '30 дней'], '3m' => ['period_3m', '3 месяца'], '1y' => ['period_1y', 'Год']];
    $statusMeta = [
        ['k' => 'incoming',   'l' => 'Входящие',    'c' => '#3b82f6'],
        ['k' => 'outgoing',   'l' => 'Исходящие',   'c' => '#8b5cf6'],
        ['k' => 'processing', 'l' => 'На подписи',  'c' => '#f59e0b'],
        ['k' => 'signed',     'l' => 'Подписанные', 'c' => '#22c55e'],
        ['k' => 'draft',      'l' => 'Черновики',   'c' => '#94a3b8'],
        ['k' => 'rejected',   'l' => 'Отклонённые', 'c' => '#ef4444'],
        ['k' => 'archive',    'l' => 'Архив',       'c' => '#14b8a6'],
    ];
    $statusItems = collect($statusMeta)
        ->filter(fn($m) => data_get($statusData ?? [], $m['k']) !== null)
        ->map(fn($m) => $m + ['v' => (int) data_get($statusData ?? [], $m['k'], 0)])
        ->values();
    $statusTotal = (int) $statusItems->sum('v');
    $daily    = collect($dailyActivity ?? []);
    $hasSplit = $daily->contains(fn($r) => data_get($r, 'incoming') !== null || data_get($r, 'outgoing') !== null);
    $notifKind = function ($n) {
        $d   = is_string($n->data) ? json_decode($n->data, true) : ($n->data ?? []);
        $msg = mb_strtolower((string) ($n->messages ?? ''));
        $typ = mb_strtolower((string) (($d['type'] ?? '') ?: $n->type));
        $hay = $typ . ' ' . $msg;
        if (str_contains($hay, 'comment') || str_contains($hay, 'коммент')) return ['comment', 'bi-chat-dots',  'n-orange', 'notif_comment',  'Новый комментарий'];
        if (str_contains($hay, 'reject')  || str_contains($hay, 'отклон'))  return ['rejected', 'bi-x-lg',       'n-red',    'notif_rejected', 'Документ отклонён'];
        if (str_contains($hay, 'signed')  || str_contains($hay, 'подписал')) return ['signed',   'bi-check-lg',   'n-green',  'notif_signed',   'Вы подписали документ'];
        if (str_contains($hay, 'sign')    || str_contains($hay, 'подпис'))  return ['sign',     'bi-pen',        'n-violet', 'notif_sign',     'Требуется ваша подпись'];
        return ['sent', 'bi-send', 'n-blue', 'notif_sent', 'Документ отправлен вам'];
    };
    $extColors = ['pdf' => '#ef4444', 'doc' => '#3b82f6', 'docx' => '#3b82f6', 'xls' => '#22c55e', 'xlsx' => '#22c55e', 'csv' => '#22c55e', 'ppt' => '#f59e0b', 'pptx' => '#f59e0b', 'jpg' => '#8b5cf6', 'png' => '#8b5cf6'];
    $typeColors = ['#3b82f6', '#8b5cf6', '#22c55e', '#f59e0b', '#ec4899', '#14b8a6'];
@endphp

<div id="pjax-container" class="dash-page">
<div class="dash-main">

    <!-- ===== HERO ===== -->
  <section class="hero">
    <div class="hero-text">
        <div class="hero-hello" data-i18n="welcomeBack">Добро пожаловать</div>
        @auth
        <h1 class="hero-name">{{ $firstName }}!</h1>
        @endauth
        <p class="hero-sub">
            <span data-i18n="dashSubtitle">У вас {{ $stats['pending'] ?? 0 }} документов ожидают подписания и {{ $stats['incoming'] ?? 0 }} новых входящих.</span>
        </p>
        <div class="hero-chips">
            <span class="chip"><i class="bi bi-calendar3"></i><span id="heroDate">{{ now()->format('d.m.Y') }}</span></span>
            <span class="chip"><i class="bi bi-clock"></i><span id="heroTime">{{ now()->format('H:i') }}</span></span>
            @if(isset($onlineUsers) && $onlineUsers->count() > 0)
            <div class="online-chip">
                <span class="online-chip-pulse"></span>
                <div class="online-chip-avatars">
                    @foreach($onlineUsers->take(4) as $onlineUser)
                    <a href="{{ route('users.show', $onlineUser->id) }}" class="online-chip-avatar" data-tooltip="{{ $onlineUser->name }}" title="{{ $onlineUser->name }}">
                        @if($onlineUser->avatar)
                            <img src="{{ $onlineUser->avatar_url }}" alt="{{ $onlineUser->name }}">
                        @else
                            <span class="chip-avatar-letter" style="background: {{ $onlineUser->avatar_color ?? '#3b82f6' }};">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($onlineUser->name, 0, 1)) }}</span>
                        @endif
                    </a>
                    @endforeach
                    @if($onlineUsers->count() > 4)
                    <a href="{{ route('users.index') }}" class="online-chip-more" title="+{{ $onlineUsers->count() - 4 }}">+{{ $onlineUsers->count() - 4 }}</a>
                    @endif
                </div>
                <span class="online-chip-text"><b>{{ $onlineUsers->count() }}</b> <span data-i18n="onlineNowShort">в сети</span></span>
            </div>
            @endif
        </div>
    </div>
    <svg class="hero-art" viewBox="0 0 380 230" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="hgShield" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#7aa8ff"/><stop offset="1" stop-color="#3a4fe3"/></linearGradient>
            <linearGradient id="hgDoc" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffffff"/><stop offset="1" stop-color="#e6edff"/></linearGradient>
            <linearGradient id="hgPlane" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#8fb4ff"/><stop offset="1" stop-color="#4d6bff"/></linearGradient>
            <filter id="hgShadow" x="-25%" y="-20%" width="150%" height="160%"><feDropShadow dx="0" dy="12" stdDeviation="10" flood-color="#3a4fe3" flood-opacity=".26"/></filter>
        </defs>
        <ellipse cx="205" cy="212" rx="150" ry="13" fill="#6f86ff" opacity=".14"/>
        <g fill="#fff" opacity=".85"><circle cx="62" cy="168" r="22"/><circle cx="90" cy="160" r="28"/><circle cx="122" cy="170" r="20"/><rect x="48" y="170" width="94" height="20" rx="10"/><circle cx="318" cy="186" r="16"/><circle cx="340" cy="180" r="20"/><rect x="304" y="186" width="58" height="16" rx="8"/></g>
        <g transform="rotate(-7 170 118)" filter="url(#hgShadow)"><rect x="92" y="26" width="152" height="176" rx="15" fill="url(#hgDoc)"/><rect x="92" y="26" width="152" height="12" rx="6" fill="#4f7cff" opacity=".9"/><rect x="112" y="54" width="62" height="9" rx="4.5" fill="#c7d6fb"/><rect x="112" y="76" width="114" height="6" rx="3" fill="#dce5fd"/><rect x="112" y="90" width="104" height="6" rx="3" fill="#dce5fd"/><rect x="112" y="104" width="114" height="6" rx="3" fill="#dce5fd"/><rect x="112" y="118" width="80" height="6" rx="3" fill="#dce5fd"/><path d="M112 164c11-16 19 7 30-6s15-5 24-2 12-8 22-6" stroke="#4f6bff" stroke-width="3.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/><rect x="112" y="176" width="52" height="5" rx="2.5" fill="#dce5fd"/></g>
        <g transform="translate(216 56)" filter="url(#hgShadow)"><path d="M62 0 118 19v52c0 42-27 70-56 84C33 141 6 113 6 71V19z" fill="url(#hgShield)"/><path d="M62 0 118 19v52c0 42-27 70-56 84z" fill="#fff" opacity=".09"/><path d="M62 10 108 25v46c0 34-21 57-46 70C37 128 16 105 16 71V25z" fill="none" stroke="#fff" stroke-opacity=".28" stroke-width="2"/><path d="M38 78l17 17 33-39" stroke="#fff" stroke-width="12" fill="none" stroke-linecap="round" stroke-linejoin="round"/></g>
        <path d="M268 54c-22-26-44 4-70-20" stroke="#9db5ff" stroke-width="2" stroke-dasharray="2 7" fill="none" stroke-linecap="round"/>
        <g transform="translate(300 18) rotate(10)"><path d="M0 22 60 0 42 56 29 34z" fill="url(#hgPlane)"/><path d="M29 34 60 0" stroke="#fff" stroke-opacity=".7" stroke-width="2" fill="none"/></g>
        <circle cx="52" cy="62" r="4" fill="#8fb0ff" opacity=".7"/><circle cx="352" cy="120" r="3" fill="#a78bfa" opacity=".7"/><circle cx="84" cy="30" r="2.5" fill="#8fb0ff" opacity=".6"/>
    </svg>
</section>

<style>
/* ===== HERO SECTION - УЛУЧШЕННЫЕ СТИЛИ ===== */
.hero {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 32px;
    padding: 32px 40px;
    border-radius: 24px;
    min-height: 280px;
    background: linear-gradient(135deg, #f0f4ff 0%, #e8eeff 50%, #f5f0ff 100%);
    border: 1px solid rgba(var(--glow), 0.15);
    box-shadow: 
        0 4px 20px rgba(40, 60, 160, 0.08),
        0 0 0 1px rgba(255, 255, 255, 0.5) inset;
    animation: heroSlideIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes heroSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Декоративные круги на фоне */
.hero::before {
    content: "";
    position: absolute;
    right: -100px;
    top: -120px;
    width: 450px;
    height: 450px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0) 70%);
    pointer-events: none;
    animation: float 8s ease-in-out infinite;
}

.hero::after {
    content: "";
    position: absolute;
    left: -80px;
    bottom: -140px;
    width: 380px;
    height: 380px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(139, 92, 246, 0.15) 0%, transparent 70%);
    pointer-events: none;
    animation: float 10s ease-in-out infinite reverse;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(10px, -10px); }
}

/* Тёмная тема */
html[data-theme="dark"] .hero {
    background: linear-gradient(135deg, rgba(var(--glow), 0.20) 0%, rgba(139, 92, 246, 0.18) 100%);
    border-color: rgba(var(--glow), 0.30);
    box-shadow: 
        0 4px 20px rgba(0, 0, 0, 0.3),
        0 0 0 1px rgba(255, 255, 255, 0.05) inset;
}

html[data-theme="dark"] .hero::before {
    background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
}

/* Текст */
.hero-text {
    position: relative;
    z-index: 2;
    min-width: 0;
    flex: 1 1 auto;
    max-width: 600px;
}



/* Приветствие и имя в одну строку */
/* Приветствие и имя в одну строку */
.hero-hello {
    display: inline;
    font-size: clamp(2rem, 3.5vw, 2.8rem); /* Такой же большой размер, как у имени */
    font-weight: 600; /* Чуть легче, чем у имени (800), чтобы имя всё же выделялось */
    color: #6b7280; /* Чуть серый цвет для светлой темы */
    margin-bottom: 0;
    margin-right: 6px; /* Небольшой пробел между приветствием и именем */
     letter-spacing: -0.03em; /* Совпадает с именем */
    line-height: 1.1; /* Совпадает с именем */
    vertical-align: baseline; /* Идеальное выравнивание по одной линии */
    animation: fadeInUp 0.5s ease 0.1s both;
}

html[data-theme="dark"] .hero-hello {
    color: #9ca3af; /* Светло-серый для тёмной темы */
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hero-name {
    display: inline;
    font-size: clamp(2rem, 3.5vw, 2.8rem);
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.1;
    margin: 0;
    color: var(--text);
    animation: fadeInUp 0.5s ease 0.2s both;
}

.hero-sub {
    max-width: 520px;
    margin: 0 0 20px;
    color: var(--text-2);
    font-size: 14px;
    line-height: 1.6;
    animation: fadeInUp 0.5s ease 0.3s both;
}

/* Чипсы */
.hero-chips {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    animation: fadeInUp 0.5s ease 0.4s both;
}

.chip {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    height: 42px;
    padding: 0 16px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
    background: rgba(255, 255, 255, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.95);
    box-shadow: 
        0 4px 12px rgba(40, 60, 160, 0.12),
        0 0 0 1px rgba(255, 255, 255, 0.5) inset;
    backdrop-filter: blur(10px);
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.chip:hover {
    transform: translateY(-2px);
    box-shadow: 
        0 8px 20px rgba(40, 60, 160, 0.18),
        0 0 0 1px rgba(255, 255, 255, 0.8) inset;
}

.chip i {
    color: var(--c-blue);
    font-size: 15px;
}

html[data-theme="dark"] .chip {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.15);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

html[data-theme="dark"] .chip:hover {
    background: rgba(255, 255, 255, 0.12);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

/* Online Chip */
.online-chip {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    height: 42px;
    padding: 0 16px 0 10px;
    border-radius: 999px;
    max-width: 100%;
    background: rgba(34, 197, 94, 0.12);
    border: 1.5px solid rgba(34, 197, 94, 0.35);
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.15);
}

.online-chip:hover {
    border-color: rgba(34, 197, 94, 0.60);
    box-shadow: 0 8px 24px rgba(34, 197, 94, 0.25);
    transform: translateY(-2px);
}

.online-chip-pulse {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #22c55e;
    margin-left: 6px;
    flex-shrink: 0;
    box-shadow: 0 0 12px #22c55e;
    animation: chipPulse 2s infinite;
}

@keyframes chipPulse {
    0% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
    }
}

.online-chip-avatars {
    display: flex;
    align-items: center;
}

.online-chip-avatar {
    position: relative;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 2px solid var(--bg-1);
    margin-left: -10px;
    flex-shrink: 0;
    text-decoration: none;
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.online-chip-avatar:first-child {
    margin-left: 0;
}

.online-chip-avatar:hover {
    transform: translateY(-4px) scale(1.15);
    z-index: 6;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.online-chip-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    display: block;
}

.chip-avatar-letter {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.35);
}

.online-chip-more {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    margin-left: -10px;
    background: rgba(128, 139, 155, 0.20);
    border: 2px solid var(--bg-1);
    color: var(--text-2);
    font-size: 10px;
    font-weight: 800;
    text-decoration: none;
    transition: all 0.25s ease;
}

.online-chip-more:hover {
    background: rgba(34, 197, 94, 0.25);
    color: #16a34a;
    transform: translateY(-4px);
    z-index: 6;
}

.online-chip-text {
    font-size: 13px;
    color: var(--text-2);
    font-weight: 600;
    white-space: nowrap;
}

.online-chip-text b {
    color: #16a34a;
    font-weight: 800;
}

.online-chip-avatar::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: calc(100% + 10px);
    left: 50%;
    transform: translateX(-50%) translateY(4px);
    background: rgba(10, 13, 20, 0.95);
    color: #fff;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: all 0.2s ease;
    z-index: 60;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.online-chip-avatar:hover::after {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

/* SVG Art */
.hero-art {
    position: relative;
    z-index: 1;
    flex: 0 0 auto;
    width: min(380px, 35%);
    height: auto;
    animation: floatArt 6s ease-in-out infinite;
}

@keyframes floatArt {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

/* Адаптивность */
@media (max-width: 860px) {
    .hero {
        padding: 24px;
        min-height: auto;
    }
    
    .hero-art {
        display: none;
    }
}

@media (max-width: 640px) {
    .hero {
        padding: 20px;
        border-radius: 18px;
    }
    
    .hero-name, .hero-hello {
        font-size: 1.8rem;
    }
    
    .hero-sub {
        font-size: 13px;
    }
    
    .chip {
        height: 38px;
        padding: 0 12px;
        font-size: 12px;
    }
    
    .online-chip {
        height: 38px;
        padding: 0 12px 0 8px;
    }
}
</style>

    <!-- ===== STATS GRID ===== -->
    <div class="stats-grid">
        <div class="stat-card c-blue">
            <div class="stat-head"><div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div><span class="stat-label" data-i18n="totalDocs">Всего документов</span></div>
            <div class="stat-body">
                <div class="stat-left">
                    <div class="stat-value">{{ number_format($stats['total'] ?? 0, 0, '.', ' ') }}</div>
                    <div class="stat-delta-wrap">
                        <span class="stat-delta {{ ($docsGrowth ?? 0) >= 0 ? 'delta-up' : 'delta-down' }}"><i class="bi {{ ($docsGrowth ?? 0) >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i> {{ abs($docsGrowth ?? 0) }}%</span>
                        <span class="stat-period">за 30 дней</span>
                    </div>
                </div>
                <div class="stat-spark">@if(isset($sparklineData['total']))<x-sparkline :data="$sparklineData['total']" id="total" />@endif</div>
            </div>
        </div>
        <div class="stat-card c-purple">
            <div class="stat-head"><div class="stat-icon"><i class="bi bi-pen"></i></div><span class="stat-label" data-i18n="pending">На подписи</span></div>
            <div class="stat-body">
                <div class="stat-left">
                    <div class="stat-value">{{ number_format($stats['waiting'] ?? 0, 0, '.', ' ') }}</div>
                    <div class="stat-delta-wrap">
                        @php $pendingChangeVal = (int) str_replace(['%', '+'], '', $stats['pending_change'] ?? 0); @endphp
                        <span class="stat-delta {{ $pendingChangeVal >= 0 ? 'delta-up' : 'delta-down' }}"><i class="bi {{ $pendingChangeVal >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i> {{ abs($pendingChangeVal) }}%</span>
                        <span class="stat-period">за 30 дней</span>
                    </div>
                </div>
                <div class="stat-spark">@if(isset($sparklineData['waiting']))<x-sparkline :data="$sparklineData['waiting']" id="waiting" />@endif</div>
            </div>
        </div>
        <div class="stat-card c-green">
            <div class="stat-head"><div class="stat-icon"><i class="bi bi-check-lg"></i></div><span class="stat-label" data-i18n="signedMonth">Подписано за месяц</span></div>
            <div class="stat-body">
                <div class="stat-left">
                    <div class="stat-value">{{ number_format($stats['signed'] ?? 0, 0, '.', ' ') }}</div>
                    <div class="stat-delta-wrap">
                        <span class="stat-delta delta-up"><i class="bi bi-arrow-up-right"></i> {{ $signedGrowth ?? 0 }}%</span>
                        <span class="stat-period">за 30 дней</span>
                    </div>
                </div>
                <div class="stat-spark">@if(isset($sparklineData['signed']))<x-sparkline :data="$sparklineData['signed']" id="signed" />@endif</div>
            </div>
        </div>
        <div class="stat-card c-orange">
            <div class="stat-head"><div class="stat-icon"><i class="bi bi-people"></i></div><span class="stat-label" data-i18n="counterparties">Контакты</span></div>
            <div class="stat-body">
                <div class="stat-left">
                    <div class="stat-value">{{ number_format($stats['users'] ?? 0, 0, '.', ' ') }}</div>
                    <div class="stat-delta-wrap">
                        <span class="stat-delta delta-up"><i class="bi bi-arrow-up-right"></i> {{ $stats['new_users_percent'] ?? 8 }}%</span>
                        <span class="stat-period">за 30 дней</span>
                    </div>
                </div>
                <div class="stat-spark">@if(isset($sparklineData['users']))<x-sparkline :data="$sparklineData['users']" id="users" />@endif</div>
            </div>
        </div>
    </div>

    <!-- ===== ГРАФИК 1: СТАТИСТИКА ===== -->
    <section class="d-card chart-full-card">
        <div class="card-head">
            <div>
                <h3 data-i18n="statsTitle">Статистика документов</h3>
                <div class="sub" data-i18n="statsSub">Общий обзор по статусам документов</div>
            </div>
            <div class="seg" role="tablist">
                @foreach($periods as $code => [$pKey, $pLabel])
                    <a href="{{ request()->fullUrlWithQuery(['period' => $code]) }}" class="seg-btn {{ $period === $code ? 'active' : '' }}" data-i18n="{{ $pKey }}">{{ $pLabel }}</a>
                @endforeach
            </div>
        </div>
        <div class="donut-wrap">
            <div id="statusDonutChart" class="donut-box"></div>
            <table class="legend-table">
                <tbody>
                @forelse($statusItems as $it)
                    <tr>
                        <td><span class="lg-dot" style="background: {{ $it['c'] }}"></span><span data-i18n="st_{{ $it['k'] }}">{{ $it['l'] }}</span></td>
                        <td class="lg-num">{{ $it['v'] }}</td>
                        <td class="lg-pct">{{ $statusTotal > 0 ? round($it['v'] / $statusTotal * 100) : 0 }}%</td>
                    </tr>
                @empty
                    <tr><td class="lg-empty" data-i18n="no_data">Нет данных</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- ===== ГРАФИК 2: ДИНАМИКА ===== -->
    <section class="d-card chart-full-card">
        <div class="card-head">
            <div>
                <h3 data-i18n="dynamicsTitle">Динамика документов</h3>
                <div class="sub" data-i18n="chart_by_days">По дням месяца</div>
            </div>
            <div class="month-nav">
                <a href="?month={{ $prevM->month }}&year={{ $prevM->year }}" class="month-btn" title="{{ $prevM->translatedFormat('F Y') }}"><i class="bi bi-chevron-left"></i></a>
                <div class="month-label">{{ \Carbon\Carbon::createFromDate($curYear, $curMonth, 1)->translatedFormat('F Y') }}</div>
                <a href="?month={{ $nextM->month }}&year={{ $nextM->year }}" class="month-btn {{ $isCurrentMonth ? 'month-btn-disabled' : '' }}" @if($isCurrentMonth) style="pointer-events:none;opacity:.4;" @endif title="{{ $nextM->translatedFormat('F Y') }}"><i class="bi bi-chevron-right"></i></a>
            </div>
        </div>
        <div id="docsFlowChart" class="bar-box"></div>
        <div class="bar-legend">
            @if($hasSplit)
                <span><i style="background: rgb(var(--glow))"></i><span data-i18n="st_incoming">Входящие</span></span>
                <span><i style="background:#8b5cf6"></i><span data-i18n="st_outgoing">Исходящие</span></span>
            @else
                <span><i style="background: rgb(var(--glow))"></i><span data-i18n="chart_docs">Документы</span></span>
            @endif
        </div>
        <div class="mini-summary">
            <div class="ms s-blue"><span data-i18n="summary_total">Всего</span><b>{{ (int) ($summary['total'] ?? 0) }}</b></div>
            <div class="ms s-purple"><span data-i18n="summary_processing">В процессе</span><b>{{ (int) ($summary['processing'] ?? 0) }}</b></div>
            <div class="ms s-green"><span data-i18n="summary_archive">Архив</span><b>{{ (int) ($summary['archive'] ?? 0) }}</b></div>
            <div class="ms s-red"><span data-i18n="summary_rejected">Отклонено</span><b>{{ (int) ($summary['rejected'] ?? 0) }}</b></div>
        </div>
    </section>

    <!-- ===== DOCUMENTS TABLE ===== -->
    <section class="d-card table-card">
        <div class="card-head">
            <div>
                <h3 data-i18n="recentDocuments">Последние документы</h3>
                <div class="sub" data-i18n="allOperations">Все операции в системе электронного документооборота</div>
            </div>
            <a href="{{ route('documents.index') }}" class="btn-ghost"><span data-i18n="all_documents">Все документы</span> <i class="bi bi-chevron-down"></i></a>
        </div>
        <div class="table-wrap">
            <table class="doc-table">
                <thead>
                <tr>
                    <th data-i18n="thId">ID</th>
                    <th data-i18n="thDoc">Документ</th>
                    <th data-i18n="thType">Тип</th>
                    <th data-i18n="thStatus">Статус</th>
                    <th data-i18n="thDate">Дата</th>
                    <th class="th-actions" data-i18n="thActions">Действия</th>
                </tr>
                </thead>
                <tbody>
                @forelse($documents as $doc)
                @php
                    $ext   = strtolower(pathinfo($doc->file_path ?? 'doc.pdf', PATHINFO_EXTENSION) ?: 'pdf');
                    $extC  = $extColors[$ext] ?? '#4f7cff';
                    $dtype = $doc->type ?? 'Документ';
                    $typeC = $typeColors[crc32((string) $dtype) % count($typeColors)];
                @endphp
                <tr class="doc-row" data-doc-id="{{ $doc->id }}">
                    <td data-label-key="thId" class="cell-id">{{ $doc->number ?? $doc->doc_number ?? 'DOC-' . str_pad($doc->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td data-label-key="thDoc">
                        <div class="doc-cell">
                            <div class="doc-ico" style="--ic: {{ $extC }}">{{ strtoupper($ext) }}</div>
                            <div class="doc-meta">
                                <b title="{{ $doc->title }}">{{ $doc->title }}</b>
                                <small>{{ optional($doc->user)->name ?? 'Не назначен' }}@if(!empty($doc->file_size)) · {{ $doc->file_size }}@endif</small>
                            </div>
                        </div>
                    </td>
                    <td data-label-key="thType"><span class="type-chip" style="--tc: {{ $typeC }}">{{ $dtype }}</span></td>
                    <td data-label-key="thStatus">
                        <span class="pill pill-{{ $doc->status }}" data-status="{{ $doc->status }}">
                            <span class="pill-dot"></span>
                            @switch($doc->status)
                                @case('pending')<span data-i18n="status_pending">На подписании</span>@break
                                @case('approved')<span data-i18n="status_approved">Одобрен</span>@break
                                @case('signed')<span data-i18n="status_signed">Подписан</span>@break
                                @case('completed')<span data-i18n="status_completed">Завершён</span>@break
                                @case('active')<span data-i18n="status_active">Активен</span>@break
                                @case('rejected')<span data-i18n="status_rejected">Отклонён</span>@break
                                @case('cancelled')<span data-i18n="status_cancelled">Отменён</span>@break
                                @case('draft')<span data-i18n="status_draft">Черновик</span>@break
                                @case('archived')<span data-i18n="status_archived">В архиве</span>@break
                                @case('expired')<span data-i18n="status_expired">Истёк</span>@break
                                @case('review')<span data-i18n="status_review">На рассмотрении</span>@break
                                @default{{ ucfirst($doc->status) }}
                            @endswitch
                        </span>
                    </td>
                    <td data-label-key="thDate" class="cell-muted">{{ $doc->created_at->format('d.m.Y H:i') }}</td>
                    <td data-label-key="thActions">
                        <div class="action-buttons">
                            <a href="{{ route('documents.show', $doc->id) }}" class="btn-action btn-view" data-tooltip="Просмотр" data-i18n-title="tooltip_view" title="Просмотр"><i class="bi bi-eye"></i></a>
                            @if(Route::has('documents.download'))
                            <a href="{{ route('documents.download', $doc->id) }}" class="btn-action btn-download" data-tooltip="Скачать" data-i18n-title="tooltip_download" title="Скачать"><i class="bi bi-download"></i></a>
                            @endif
                            @if(Route::has('documents.destroy') && $authUser && $authUser->can('delete', $doc))
                            <button type="button" class="btn-action btn-delete" data-tooltip="Удалить" data-i18n-title="tooltip_delete" data-name="{{ $doc->title }}" title="Удалить" onclick="confirmDelete({{ $doc->id }}, this.dataset.name)"><i class="bi bi-trash3"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="bi bi-folder2-open"></i></div>
                            <p class="empty-text" data-i18n="no_documents">Документы не найдены</p>
                            <small data-i18n="create_first_doc">Создайте первый документ, чтобы начать работу</small>
                        </div>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($documents instanceof \Illuminate\Pagination\LengthAwarePaginator && $documents->hasPages())
        <div class="pagination-wrapper">{{ $documents->links() }}</div>
        @endif
    </section>

<!-- ===== БЫСТРЫЕ ДЕЙСТВИЯ ===== -->
<section class="quick-actions-card">
    <div class="qa-header">
        <h3 class="qa-title">
            <svg class="qa-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="13 2 3 14 9 14 11 22 21 10 15 10 13 2"></polyline>
            </svg>
            <span data-i18n="quickActions">Амалҳои зуд</span>
        </h3>
    </div>
    
    <div class="qa-grid">
        <a href="{{ route('documents.create') }}" class="qa-item qa-blue">
            <div class="qa-item-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </div>
            <span class="qa-item-label" data-i18n="qa_create">Эҷоди ҳуҷҷат</span>
        </a>
        
        <a href="{{ route('documents.index', ['tab' => 'processing']) }}" class="qa-item qa-violet">
            <div class="qa-item-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 19l7-7 3 3-7 7-3-3z"></path>
                    <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path>
                </svg>
            </div>
            <span class="qa-item-label" data-i18n="qa_sign">Барои имзо</span>
        </a>
        
        <a href="{{ route('users.index') }}" class="qa-item qa-green">
            <div class="qa-item-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <span class="qa-item-label" data-i18n="qa_contact">Ёфтани тамос</span>
        </a>
        
        <a href="{{ url('/analysis') }}" class="qa-item qa-orange">
            <div class="qa-item-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
            </div>
            <span class="qa-item-label" data-i18n="qa_analytics">Таҳлил</span>
        </a>
    </div>
</section>

<style>
/* ===== QUICK ACTIONS CARD ===== */
.quick-actions-card {
    background: var(--bg-1);
    border: 1px solid var(--line);
    border-radius: 16px;
    padding: 24px;
    box-shadow: var(--shadow-card);
    transition: all 0.3s ease;
}

.quick-actions-card:hover {
    box-shadow: var(--shadow-soft);
    border-color: rgba(var(--glow), 0.3);
}

.qa-header {
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--line);
}

.qa-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 600;
    color: var(--text);
    margin: 0;
    letter-spacing: -0.02em;
}

.qa-icon {
    width: 18px;
    height: 18px;
    color: rgb(var(--glow));
}

.qa-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.qa-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 20px 16px;
    border-radius: 12px;
    text-decoration: none;
    color: var(--text);
    background: var(--bg-2);
    border: 1.5px solid var(--line);
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
}

.qa-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--qa-color, rgb(var(--glow)));
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.qa-item:hover {
    transform: translateY(-4px);
    border-color: var(--qa-color, rgb(var(--glow)));
    box-shadow: 0 8px 24px rgba(var(--qa-rgb, var(--glow)), 0.15);
    background: rgba(var(--qa-rgb, var(--glow)), 0.05);
}

.qa-item:hover::before {
    transform: scaleX(1);
}

.qa-item:active {
    transform: translateY(-2px);
}

.qa-item-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(var(--qa-rgb, var(--glow)), 0.12);
    color: var(--qa-color, rgb(var(--glow)));
    transition: all 0.25s ease;
}

.qa-item:hover .qa-item-icon {
    background: var(--qa-color, rgb(var(--glow)));
    color: #fff;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(var(--qa-rgb, var(--glow)), 0.3);
}

.qa-item-icon svg {
    width: 24px;
    height: 24px;
    stroke-width: 2.5;
}

.qa-item-label {
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    color: var(--text);
    line-height: 1.3;
    transition: color 0.2s ease;
}

.qa-item:hover .qa-item-label {
    color: var(--qa-color, rgb(var(--glow)));
}

/* Цвета для элементов */
.qa-blue {
    --qa-color: #3b82f6;
    --qa-rgb: 59, 130, 246;
}

.qa-violet {
    --qa-color: #8b5cf6;
    --qa-rgb: 139, 92, 246;
}

.qa-green {
    --qa-color: #22c55e;
    --qa-rgb: 34, 197, 94;
}

.qa-orange {
    --qa-color: #f59e0b;
    --qa-rgb: 245, 158, 11;
}

/* Адаптивность */
@media (max-width: 1127px) {
    .qa-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .quick-actions-card {
        padding: 18px;
    }
    
    .qa-grid {
        gap: 12px;
    }
    
    .qa-item {
        padding: 16px 12px;
    }
    
    .qa-item-icon {
        width: 42px;
        height: 42px;
    }
    
    .qa-item-label {
        font-size: 12px;
    }
}

/* Тёмная тема */
html[data-theme="dark"] .quick-actions-card {
    background: var(--bg-1);
    border-color: var(--line);
}

html[data-theme="dark"] .qa-item {
    background: var(--bg-2);
    border-color: rgba(255, 255, 255, 0.1);
}

html[data-theme="dark"] .qa-item:hover {
    background: rgba(var(--qa-rgb, var(--glow)), 0.08);
    border-color: var(--qa-color, rgb(var(--glow)));
}
</style>
    <!-- ===== УВЕДОМЛЕНИЯ (внизу, полная ширина) ===== -->
    <section class="d-card bottom-card">
        <div class="card-head">
            <h3 class="bottom-title"><i class="bi bi-bell"></i> <span data-i18n="recentNotifications">Последние уведомления</span></h3>
            <a href="{{ $notifAllUrl }}" class="link-all"><span data-i18n="all_short">Все</span> <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="rn-list-wide">
            @forelse($recentNotifs as $n)
            @php
                $nd = is_string($n->data) ? json_decode($n->data, true) : ($n->data ?? []);
                [$kind, $kIcon, $kClass, $kKey, $kLabel] = $notifKind($n);
                $nSender = $nd['sender_name'] ?? $nd['user_name'] ?? 'Система';
                $nDoc    = $nd['document_name'] ?? $nd['document_title'] ?? 'Документ';
                $nUrl    = $nd['url'] ?? (isset($nd['document_id']) ? route('documents.show', $nd['document_id']) : '#');
                $nUnread = !$n->is_read;
            @endphp
            <a href="{{ $nUrl }}" class="rn-item-wide {{ $nUnread ? 'unread' : '' }}">
                <span class="rn-ico {{ $kClass }}"><i class="bi {{ $kIcon }}"></i></span>
                <span class="rn-body">
                    <b data-i18n="{{ $kKey }}">{{ $kLabel }}</b>
                    <small>{{ $nSender }} · {{ $nDoc }}</small>
                </span>
                <span class="rn-time">
                    @if($n->created_at->isToday()) {{ $n->created_at->format('H:i') }}
                    @elseif($n->created_at->isYesterday()) <span data-i18n="yesterday">Вчера</span>
                    @else {{ $n->created_at->format('d.m') }}
                    @endif
                </span>
            </a>
            @empty
            <div class="rn-empty"><i class="bi bi-inbox"></i><span data-i18n="no_notifications">Нет уведомлений</span></div>
            @endforelse
        </div>
    </section>

</div>

<!-- ===== DELETE MODAL ===== -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon-wrapper danger"><i class="bi bi-exclamation-triangle"></i></div>
        <h3 class="modal-title" data-i18n="delete_doc_title">Удалить документ?</h3>
        <p class="modal-text"><span data-i18n="delete_doc_text">Вы действительно хотите удалить документ</span> <b id="deleteDocName"></b>? <span data-i18n="delete_doc_warning">Это действие нельзя отменить.</span></p>
        <form id="deleteForm" method="POST" style="display:none;">@csrf @method('DELETE')</form>
        <div class="modal-actions">
            <button class="modal-btn modal-btn-cancel" onclick="closeDeleteModal()" data-i18n="cancel">Отмена</button>
            <button class="modal-btn modal-btn-danger" onclick="submitDelete()"><i class="bi bi-trash3"></i> <span data-i18n="delete">Удалить</span></button>
        </div>
    </div>
</div>
<div class="toast-container" id="toastContainer"></div>

<style>
/* ===== АДАПТАЦИЯ ПОД LAYOUT МАКЕТА ===== */
.dash-page {
    /* Используем переменные из макета, не переопределяем их */
    --c-blue: rgb(var(--glow));
    --c-violet: #8b5cf6;
    --c-green: #22c55e;
    --c-orange: #f59e0b;
    --c-red: #ef4444;
    --text-2: var(--muted); /* Используем --muted из макета */
    
    max-width: 100%;
    width: 100%;
    margin: 0;
    color: var(--text);
    animation: dashIn .45s ease both;
}

/* Адаптация под свёрнутый/развёрнутый сайдбар */
html.sidebar-collapsed .dash-page {
    max-width: calc(100vw - var(--side-w-c) - 52px);
}

html:not(.sidebar-collapsed) .dash-page {
    max-width: calc(100vw - var(--side-w) - 52px);
}

@media (max-width: 1100px) {
    html.sidebar-collapsed .dash-page,
    html:not(.sidebar-collapsed) .dash-page {
        max-width: 100%;
    }
}

@keyframes dashIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}

/* ОДНА КОЛОНКА НА ВСЮ ШИРИНУ — без правой колонки */
.dash-main{display:flex;flex-direction:column;gap:18px;min-width:0}

.d-card{background:var(--bg-1);border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow-card);padding:18px 20px}
.card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:14px}
.card-head h3{font-size:15.5px;font-weight:700;letter-spacing:-0.02em;margin:0 0 2px;color:var(--text)}
.card-head .sub{font-size:12px;color:var(--muted)}

/* HERO */
.hero{position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:24px;padding:26px 32px;border-radius:22px;min-height:0;background:linear-gradient(115deg,#eaf0ff 0%,#e1e9ff 48%,#ece7ff 100%);border:1px solid rgba(var(--glow),0.14);box-shadow:var(--shadow-card)}
.hero::before{content:"";position:absolute;right:-80px;top:-110px;width:420px;height:420px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,0.9),rgba(255,255,255,0) 68%);pointer-events:none}
.hero::after{content:"";position:absolute;left:-60px;bottom:-120px;width:340px;height:340px;border-radius:50%;background:radial-gradient(circle,rgba(139,92,246,0.14),transparent 70%);pointer-events:none}
html[data-theme="dark"] .hero{background:linear-gradient(115deg,rgba(var(--glow),0.24),rgba(139,92,246,0.20)),var(--bg-1);border-color:rgba(var(--glow),0.25)}
html[data-theme="dark"] .hero::before{background:radial-gradient(circle,rgba(255,255,255,0.08),transparent 68%)}
.hero-text{position:relative;z-index:2;min-width:0;flex:1 1 auto}

.hero-sub{max-width:500px;margin:0 0 16px;color:var(--text-2);font-size:13.5px;line-height:1.55}
.hero-chips{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.chip{display:inline-flex;align-items:center;gap:8px;height:38px;padding:0 14px;border-radius:11px;font-size:12.5px;font-weight:600;color:var(--text);background:rgba(255,255,255,0.78);border:1px solid rgba(255,255,255,0.95);box-shadow:0 6px 16px -10px rgba(40,60,160,0.35)}
.chip i{color:var(--c-blue);font-size:14px}
html[data-theme="dark"] .chip{background:rgba(255,255,255,0.07);border-color:rgba(255,255,255,0.12);box-shadow:none}
.hero-art{position:relative;z-index:1;flex:0 0 auto;width:min(360px,30%);height:auto}

/* ONLINE CHIP */
.online-chip{display:inline-flex;align-items:center;gap:10px;height:38px;padding:0 14px 0 8px;border-radius:999px;max-width:100%;background:rgba(34,197,94,0.10);border:1.5px solid rgba(34,197,94,0.30);transition:all .3s cubic-bezier(.23,1,.32,1)}
.online-chip:hover{border-color:rgba(34,197,94,0.55);box-shadow:0 6px 24px rgba(34,197,94,0.18);transform:translateY(-2px)}
.online-chip-pulse{width:8px;height:8px;border-radius:50%;background:#22c55e;margin-left:5px;flex-shrink:0;box-shadow:0 0 10px #22c55e;animation:chipPulse 2s infinite}
@keyframes chipPulse{0%{box-shadow:0 0 0 0 rgba(34,197,94,.6)}70%{box-shadow:0 0 0 8px rgba(34,197,94,0)}100%{box-shadow:0 0 0 0 rgba(34,197,94,0)}}
.online-chip-avatars{display:flex;align-items:center}
.online-chip-avatar{position:relative;width:26px;height:26px;border-radius:50%;border:2px solid var(--bg-1);margin-left:-8px;flex-shrink:0;text-decoration:none;transition:transform .25s cubic-bezier(.23,1,.32,1)}
.online-chip-avatar:first-child{margin-left:0}
.online-chip-avatar:hover{transform:translateY(-3px) scale(1.1);z-index:6}
.online-chip-avatar img{width:100%;height:100%;border-radius:50%;object-fit:cover;display:block}
.chip-avatar-letter{display:flex;align-items:center;justify-content:center;width:100%;height:100%;border-radius:50%;color:#fff;font-size:10px;font-weight:800;text-shadow:0 1px 4px rgba(0,0,0,.35)}
.online-chip-more{display:flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;margin-left:-8px;background:rgba(128,139,155,.18);border:2px solid var(--bg-1);color:var(--text-2);font-size:9.5px;font-weight:800;text-decoration:none;transition:all .25s ease}
.online-chip-more:hover{background:rgba(34,197,94,.2);color:#16a34a;transform:translateY(-3px);z-index:6}
.online-chip-text{font-size:.8rem;color:var(--text-2);font-weight:600;white-space:nowrap}
.online-chip-text b{color:#16a34a;font-weight:800}
.online-chip-avatar::after{content:attr(data-tooltip);position:absolute;bottom:calc(100% + 8px);left:50%;transform:translateX(-50%) translateY(4px);background:rgba(10,13,20,.95);color:#fff;padding:4px 10px;border-radius:8px;font-size:11px;font-weight:600;white-space:nowrap;opacity:0;pointer-events:none;transition:all .2s ease;z-index:60}
.online-chip-avatar:hover::after{opacity:1;transform:translateX(-50%) translateY(0)}

/* STAT CARDS */
.stats-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
.stat-card{position:relative;overflow:hidden;background:var(--bg-1);border:1px solid var(--line);border-radius:18px;padding:16px 18px 14px;box-shadow:var(--shadow-card);transition:transform .3s cubic-bezier(.23,1,.32,1),box-shadow .3s ease,border-color .3s ease}
.stat-card:hover{transform:translateY(-3px);box-shadow:var(--shadow-soft)}
.stat-card.c-blue{--c:var(--c-blue)}.stat-card.c-purple{--c:var(--c-violet)}.stat-card.c-green{--c:var(--c-green)}.stat-card.c-orange{--c:var(--c-orange)}
.stat-card:hover{border-color:rgba(var(--c),0.4)}
.stat-head{display:flex;align-items:center;gap:10px;margin-bottom:12px;min-width:0}
.stat-icon{width:34px;height:34px;border-radius:10px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:rgba(var(--c),0.12);color:var(--c);font-size:16px}
.stat-label{font-size:12.5px;font-weight:500;color:var(--text-2);line-height:1.35;min-width:0;overflow-wrap:break-word}
.stat-body{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;min-width:0}
.stat-left{min-width:0;display:flex;flex-direction:column;gap:8px;flex:1 1 auto}
.stat-value{font-size:clamp(1.6rem,2.1vw,2rem);font-weight:800;letter-spacing:-0.03em;line-height:1;margin:0;font-variant-numeric:tabular-nums;color:var(--text)}
.stat-delta-wrap{display:flex;align-items:center;flex-wrap:wrap;gap:4px 8px;min-width:0}
.stat-delta{display:inline-flex;align-items:center;gap:3px;padding:3px 8px;border-radius:8px;font-size:11px;font-weight:700;white-space:nowrap}
.stat-delta i{font-size:11px;font-weight:bold}
.delta-up{background:rgba(34,197,94,.12);color:#16a34a}
.delta-down{background:rgba(239,68,68,.12);color:#dc2626}
html[data-theme="dark"] .delta-up{color:#4ade80;background:rgba(34,197,94,.15)}
html[data-theme="dark"] .delta-down{color:#f87171;background:rgba(239,68,68,.15)}
.stat-period{font-size:10.5px;color:var(--muted);font-weight:500;white-space:nowrap}
.stat-spark{width:84px;height:38px;flex:0 0 auto;color:var(--c);margin-bottom:2px;min-width:0}
.stat-spark svg{width:100%;height:100%;display:block;overflow:visible}
.stat-spark svg [stroke]:not([stroke="none"]){stroke:var(--c);stroke-width:2.5px;stroke-linecap:round;stroke-linejoin:round}

/* ГРАФИКИ */
.chart-full-card{width:100%}
.seg{display:inline-flex;padding:3px;border-radius:11px;background:var(--bg-2);border:1px solid var(--line)}
.seg-btn{padding:5px 11px;border-radius:8px;font-size:11.5px;font-weight:600;color:var(--muted);text-decoration:none;white-space:nowrap;transition:all .15s ease}
.seg-btn:hover{color:var(--text)}
.seg-btn.active{background:var(--c-blue);color:#fff;box-shadow:0 6px 14px -6px rgba(var(--glow),.7)}
.donut-wrap{display:flex;align-items:center;gap:24px;flex-wrap:wrap}
.donut-box{width:220px;min-height:220px;flex-shrink:0}
.legend-table{flex:1;min-width:200px;border-collapse:collapse}
.legend-table td{padding:9px 6px;font-size:13px;border-bottom:1px solid var(--line);color:var(--text)}
.legend-table tr:last-child td{border-bottom:none}
.lg-dot{display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:10px}
.lg-num{text-align:right;font-weight:700;font-variant-numeric:tabular-nums}
.lg-pct{text-align:right;color:var(--muted);width:48px;font-variant-numeric:tabular-nums}
.lg-empty{color:var(--muted);text-align:center!important;padding:24px 0!important}
.bar-box{width:100%;min-height:260px}
.bar-legend{display:flex;justify-content:center;gap:22px;margin-top:8px;font-size:12px;color:var(--text-2);font-weight:500}
.bar-legend span{display:inline-flex;align-items:center;gap:7px}
.bar-legend i{width:9px;height:9px;border-radius:50%;display:inline-block}
.mini-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:14px;padding-top:14px;border-top:1px solid var(--line)}
.ms{display:flex;flex-direction:column;gap:2px;padding-left:10px;border-left:3px solid var(--k)}
.ms span{font-size:10.5px;color:var(--muted);font-weight:600}
.ms b{font-size:16px;font-weight:800;letter-spacing:-0.03em;font-variant-numeric:tabular-nums}
.ms.s-blue{--k:#3b82f6}.ms.s-purple{--k:#8b5cf6}.ms.s-green{--k:#22c55e}.ms.s-red{--k:#ef4444}
.month-nav{display:flex;align-items:center;gap:6px}
.month-btn{width:30px;height:30px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;background:var(--bg-2);border:1px solid var(--line);color:var(--text-2);text-decoration:none;font-size:12px;transition:all .2s ease}
.month-btn:hover{border-color:rgba(var(--glow),.5);color:var(--c-blue)}
.month-btn-disabled{pointer-events:none}
.month-label{padding:6px 11px;border-radius:9px;background:var(--bg-2);border:1px solid var(--line);font-size:12px;font-weight:700;white-space:nowrap}

/* ТАБЛИЦА */
.btn-ghost{display:inline-flex;align-items:center;gap:8px;height:36px;padding:0 13px;border-radius:10px;font-size:12.5px;font-weight:600;text-decoration:none;color:var(--text);background:var(--bg-1);border:1px solid var(--line);transition:all .2s ease}
.btn-ghost:hover{border-color:rgba(var(--glow),.5);color:var(--c-blue)}
.btn-ghost i{font-size:11px;color:var(--muted)}
.table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
.doc-table{width:100%;border-collapse:separate;border-spacing:0}
.doc-table thead th{font-size:11.5px;font-weight:600;color:var(--muted);text-align:left;padding:9px 12px;white-space:nowrap;border-bottom:1px solid var(--line)}
.doc-table .th-actions{text-align:right}
.doc-table td{padding:11px 12px;font-size:13px;color:var(--text);vertical-align:middle;border-bottom:1px solid var(--line)}
.doc-row{transition:background .2s ease}
.doc-row:hover{background:rgba(var(--glow),.045)}
.doc-row:last-child td{border-bottom:none}
.cell-id{font:600 12px var(--font-mono);color:var(--text-2);white-space:nowrap}
.cell-muted{color:var(--muted);white-space:nowrap;font-size:12.5px;font-variant-numeric:tabular-nums}
.doc-cell{display:flex;align-items:center;gap:12px;min-width:0}
.doc-ico{width:36px;height:36px;border-radius:10px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font:700 9.5px var(--font-mono);letter-spacing:.04em;color:var(--ic);background:rgba(var(--ic),0.13);border:1px solid rgba(var(--ic),0.28)}
.doc-meta{display:flex;flex-direction:column;gap:2px;min-width:0}
.doc-meta b{font-size:13.5px;font-weight:600;max-width:400px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.doc-meta small{font-size:11.5px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:400px}
.type-chip{display:inline-block;padding:4px 10px;border-radius:8px;font-size:11.5px;font-weight:600;white-space:nowrap;color:var(--tc);background:rgba(var(--tc),0.12)}
.pill{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:11.5px;font-weight:600;white-space:nowrap;border:1px solid transparent}
.pill-dot{width:6px;height:6px;border-radius:50%;background:currentColor;flex-shrink:0}
.pill-pending,.pill-review{background:rgba(245,158,11,.12);color:#d97706;border-color:rgba(245,158,11,.28)}
.pill-approved,.pill-signed,.pill-completed,.pill-active{background:rgba(34,197,94,.11);color:#16a34a;border-color:rgba(34,197,94,.28)}
.pill-rejected,.pill-cancelled{background:rgba(239,68,68,.10);color:#dc2626;border-color:rgba(239,68,68,.28)}
.pill-draft,.pill-archived{background:rgba(100,116,139,.11);color:#64748b;border-color:rgba(100,116,139,.26)}
.pill-expired{background:rgba(245,158,11,.14);color:#b45309;border-color:rgba(245,158,11,.36)}
html[data-theme="dark"] .pill-pending,html[data-theme="dark"] .pill-review{color:#fbbf24}
html[data-theme="dark"] .pill-approved,html[data-theme="dark"] .pill-signed,html[data-theme="dark"] .pill-completed,html[data-theme="dark"] .pill-active{color:#6ee7b7}
html[data-theme="dark"] .pill-rejected,html[data-theme="dark"] .pill-cancelled{color:#fca5a5}
html[data-theme="dark"] .pill-draft,html[data-theme="dark"] .pill-archived{color:#94a3b8}
html[data-theme="dark"] .pill-expired{color:#fcd34d}
.action-buttons{display:flex;align-items:center;justify-content:flex-end;gap:7px}
.btn-action{position:relative;width:32px;height:32px;border-radius:9px;padding:0;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;background:var(--bg-2);border:1px solid var(--line);color:var(--text-2);font-size:13px;transition:all .2s ease}
.btn-action:hover{background:rgba(var(--glow),.12);border-color:rgba(var(--glow),.45);color:var(--c-blue);transform:translateY(-1px)}
.btn-delete:hover{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.45);color:#ef4444}
.btn-action::before{content:attr(data-tooltip);position:absolute;bottom:calc(100% + 8px);left:50%;transform:translateX(-50%) translateY(4px);padding:5px 10px;background:rgba(10,13,20,.95);border-radius:8px;color:#fff;font-size:11px;font-weight:600;white-space:nowrap;opacity:0;pointer-events:none;transition:all .2s ease;z-index:100}
.btn-action:hover::before{opacity:1;transform:translateX(-50%) translateY(0)}
.empty-state{text-align:center;padding:clamp(2rem,5vw,3.5rem) 1rem}
.empty-icon{width:68px;height:68px;border-radius:50%;margin:0 auto 1rem;display:flex;align-items:center;justify-content:center;font-size:1.9rem;color:var(--c-blue);background:rgba(var(--glow),.08);border:1.5px solid rgba(var(--glow),.25)}
.empty-text{color:var(--text-2);margin:0 0 .4rem;font-size:1rem;font-weight:700}
.empty-state small{color:var(--muted);font-size:12.5px}
.pagination-wrapper{margin-top:1.25rem;display:flex;justify-content:center}
.pagination-wrapper .pagination{gap:6px;display:flex;list-style:none;padding:0;margin:0}
.pagination-wrapper .page-link{display:block;background:var(--bg-1);border:1px solid var(--line);color:var(--text-2);padding:7px 13px;border-radius:10px;font-size:13px;font-weight:600;transition:all .2s ease;text-decoration:none}
.pagination-wrapper .page-link:hover{background:rgba(var(--glow),.10);color:var(--text);border-color:rgba(var(--glow),.45)}
.pagination-wrapper .page-item.active .page-link{background:var(--c-blue);border-color:transparent;color:#fff;box-shadow:0 6px 16px -4px rgba(var(--glow),.5)}

/* ===== НИЖНИЕ КАРТОЧКИ (быстрые действия + уведомления) ===== */
.bottom-card{width:100%}
.bottom-title{display:flex;align-items:center;gap:9px;font-size:15px!important;margin:0!important}
.bottom-title i{font-size:17px;color:var(--text)}
.link-all{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:700;color:var(--c-blue);text-decoration:none}
.link-all:hover{text-decoration:underline}

/* Быстрые действия — 4 в ряд на всю ширину */
.qa-grid-wide{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.qa{display:flex;flex-direction:column;align-items:flex-start;gap:12px;padding:16px;border-radius:14px;text-decoration:none;color:var(--text);background:rgba(var(--q),0.07);border:1px solid rgba(var(--q),0.20);transition:transform .25s cubic-bezier(.23,1,.32,1),box-shadow .25s ease}
.qa:hover{transform:translateY(-3px);box-shadow:0 14px 26px -16px var(--q)}
.qa-blue{--q:rgb(var(--glow))}.qa-violet{--q:#8b5cf6}.qa-green{--q:#22c55e}.qa-orange{--q:#f59e0b}
.qa-ico{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--q);background:rgba(var(--q),0.16)}
.qa-blue .qa-ico{background:linear-gradient(135deg,rgb(var(--glow)),#5b7bff);color:#fff;box-shadow:0 8px 16px -8px rgba(var(--glow),.8)}
.qa-label{font-size:13px;font-weight:600;line-height:1.3}

/* Уведомления — горизонтальный список на всю ширину */
.rn-list-wide{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
.rn-item-wide{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:13px;text-decoration:none;color:inherit;position:relative;transition:background .15s ease;border:1px solid var(--line);background:var(--bg-1)}
.rn-item-wide:hover{background:rgba(var(--glow),.04);border-color:rgba(var(--glow),.2)}
.rn-item-wide.unread{border-left:3px solid var(--c-blue);background:rgba(var(--glow),.03)}
.rn-ico{width:38px;height:38px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:15px}
.n-blue{background:rgba(59,130,246,.13);color:#3b82f6}
.n-violet{background:rgba(139,92,246,.14);color:#8b5cf6}
.n-green{background:rgba(34,197,94,.14);color:#16a34a}
.n-orange{background:rgba(249,115,22,.13);color:#f97316}
.n-red{background:rgba(239,68,68,.13);color:#ef4444}
.rn-body{flex:1;min-width:0;display:flex;flex-direction:column;gap:2px}
.rn-body b{font-size:12.5px;font-weight:600;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.rn-body small{font-size:11px;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.rn-time{font-size:11px;color:var(--muted);flex-shrink:0;font-variant-numeric:tabular-nums}
.rn-empty{grid-column:1/-1;display:flex;flex-direction:column;align-items:center;gap:8px;padding:30px 0;color:var(--muted);font-size:13px}
.rn-empty i{font-size:2rem}

/* MODAL */
.modal-overlay{position:fixed;inset:0;background:rgba(8,12,30,.6);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);display:flex;align-items:center;justify-content:center;z-index:9999;opacity:0;visibility:hidden;transition:all .3s ease;padding:1rem}
.modal-overlay.active{opacity:1;visibility:visible}
.modal-box{background:var(--bg-1);border:1px solid rgba(239,68,68,.30);border-radius:24px;padding:clamp(1.5rem,3vw,2.2rem);max-width:420px;width:100%;text-align:center;transform:scale(.92) translateY(16px);transition:transform .35s cubic-bezier(.34,1.56,.64,1);box-shadow:0 24px 64px rgba(0,0,0,.3),0 0 40px rgba(239,68,68,.10)}
.modal-overlay.active .modal-box{transform:none}
.modal-icon-wrapper{width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:1.75rem}
.modal-icon-wrapper.danger{background:rgba(239,68,68,.12);color:#ef4444;border:1.5px solid rgba(239,68,68,.35);animation:pulse-danger 2s ease-in-out infinite}
@keyframes pulse-danger{0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.4)}50%{box-shadow:0 0 0 14px rgba(239,68,68,0)}}
.modal-title{color:var(--text);font-size:1.3rem;font-weight:800;margin:0 0 .75rem;letter-spacing:-0.02em}
.modal-text{color:var(--text-2);font-size:.9rem;line-height:1.65;margin:0 0 1.75rem}
.modal-text b{color:var(--text);font-weight:700}
.modal-actions{display:flex;gap:.75rem}
.modal-btn{flex:1;padding:12px 20px;border-radius:999px;font:600 13px var(--font-sans);cursor:pointer;transition:all .3s cubic-bezier(.23,1,.32,1);border:1.5px solid transparent;display:inline-flex;align-items:center;justify-content:center;gap:6px}
.modal-btn-cancel{background:transparent;color:var(--text-2);border-color:var(--line)}
.modal-btn-cancel:hover{background:var(--bg-2);color:var(--text)}
.modal-btn-danger{background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;box-shadow:0 8px 22px -6px rgba(239,68,68,.5)}
.modal-btn-danger:hover{transform:translateY(-2px);box-shadow:0 14px 30px -6px rgba(239,68,68,.6)}

/* TOASTS */
.toast-container{position:fixed;top:1.5rem;right:1.5rem;z-index:10000;display:flex;flex-direction:column;gap:.75rem;pointer-events:none;max-width:calc(100vw - 2rem)}
.toast{background:var(--bg-1);border:1px solid var(--line);border-radius:16px;padding:14px 18px;display:flex;align-items:center;gap:12px;min-width:280px;max-width:400px;box-shadow:0 16px 40px rgba(0,0,0,.22);animation:toast-in .45s cubic-bezier(.34,1.56,.64,1);pointer-events:auto}
.toast.toast-out{animation:toast-out .3s ease forwards}
.toast.success{border-color:rgba(34,197,94,.45)}
.toast.error{border-color:rgba(239,68,68,.45)}
.toast-icon{width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.toast.success .toast-icon{background:rgba(34,197,94,.13);color:#16a34a}
.toast.error .toast-icon{background:rgba(239,68,68,.13);color:#ef4444}
.toast-content{flex:1;min-width:0}
.toast-title{color:var(--text);font-weight:700;font-size:.9rem;margin-bottom:2px}
.toast-message{color:var(--text-2);font-size:.8rem}
@keyframes toast-in{from{opacity:0;transform:translateX(100%)}to{opacity:1;transform:none}}
@keyframes toast-out{to{opacity:0;transform:translateX(100%)}}

.apexcharts-tooltip{background:var(--bg-1)!important;border:1px solid var(--line)!important;border-radius:12px!important;box-shadow:var(--shadow-soft)!important;color:var(--text)!important}
.apexcharts-tooltip-title{background:transparent!important;border-bottom:1px solid var(--line)!important;color:var(--text)!important}
.apexcharts-tooltip span{color:var(--text)!important}
.apexcharts-xaxistooltip{background:var(--bg-1)!important;border:1px solid var(--line)!important;color:var(--text)!important;border-radius:8px!important}

/* АДАПТИВ */
@media(max-width:1127px){.stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.qa-grid-wide{grid-template-columns:repeat(2,1fr)}}
@media(max-width:860px){.hero{padding:22px}.hero-art{display:none}.rn-list-wide{grid-template-columns:1fr}}
@media(max-width:767px){
    .online-chip-avatar::after{display:none}
    .doc-table thead{display:none}
    .doc-table,.doc-table tbody{display:block;width:100%}
    .doc-table tbody tr{display:block;background:var(--bg-1);border:1px solid var(--line);border-radius:16px;margin-bottom:12px;padding:12px 14px;box-shadow:var(--shadow-card)}
    .doc-table td{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:8px 0;border:none;border-bottom:1px solid var(--line)}
    .doc-table td:last-child{border-bottom:none}
    .doc-table td::before{content:attr(data-label);font-weight:700;color:var(--muted);font-size:10.5px;flex-shrink:0}
    .doc-table td[colspan]::before{display:none}
    .doc-meta b,.doc-meta small{max-width:170px}
    .btn-action::before{display:none}
    .modal-actions{flex-direction:column-reverse}
    .toast-container{top:1rem;right:1rem;left:1rem}
    .toast{min-width:auto;max-width:100%}
    .mini-summary{grid-template-columns:repeat(2,1fr)}
    .d-card{padding:16px}
}
@media(max-width:640px){.stats-grid{grid-template-columns:minmax(0,1fr)}.qa-grid-wide{grid-template-columns:1fr 1fr}.donut-wrap{justify-content:center}.card-head{flex-direction:column;align-items:flex-start}.stat-spark{width:76px}}
@media(prefers-reduced-motion:reduce){.dash-page *,.dash-page *::before,.dash-page *::after{animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important}}

/* =========================================
   HERO: приветствие и имя пользователя
   Светлая тема — чёрный текст
   Тёмная тема — белый текст
   ========================================= */

.dash-page .hero .hero-text .hero-hello,
.dash-page .hero .hero-text .hero-name {
    display: inline;
    font-size: clamp(1.35rem, 2.2vw, 1.8rem) !important;
    font-weight: 700;
    letter-spacing: -0.025em;
    line-height: 1.25;
    margin: 0;
    opacity: 1 !important;
    text-shadow: none !important;
    -webkit-text-fill-color: #000000 !important;
    color: #000000 !important;
}

/* Светлая тема */
html[data-theme="light"] .dash-page .hero .hero-text .hero-hello,
html[data-theme="light"] .dash-page .hero .hero-text .hero-name {
    color: #000000 !important;
    -webkit-text-fill-color: #000000 !important;
}

/* Тёмная тема */
html[data-theme="dark"] .dash-page .hero .hero-text .hero-hello,
html[data-theme="dark"] .dash-page .hero .hero-text .hero-name {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}

/* Если светлая тема используется без data-theme="light" */
html:not([data-theme="dark"]) .dash-page .hero .hero-text .hero-hello,
html:not([data-theme="dark"]) .dash-page .hero .hero-text .hero-name {
    color: #000000 !important;
    -webkit-text-fill-color: #000000 !important;
}

/* На телефонах — ещё немного меньше */
@media (max-width: 640px) {
    .dash-page .hero .hero-text .hero-hello,
    .dash-page .hero .hero-text .hero-name {
        font-size: 1.35rem !important;
        line-height: 1.3;
    }
}


</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const translations = {
        ru: {
            welcomeBack:"Добро пожаловать",dashSubtitle:"У вас {pending} документов ожидают подписания и {incoming} новых входящих.",
            totalDocs:"Всего документов",pending:"На подписании",signedMonth:"Подписано за месяц",counterparties:"Контакты",
            recentDocuments:"Последние документы",allOperations:"Все операции в системе электронного документооборота",all_documents:"Все документы",
            thId:"ID",thDoc:"Документ",thType:"Тип",thStatus:"Статус",thDate:"Дата",thActions:"Действия",
            status_pending:"На подписании",status_approved:"Одобрен",status_signed:"Подписан",status_completed:"Завершён",status_active:"Активен",status_rejected:"Отклонён",status_cancelled:"Отменён",status_draft:"Черновик",status_archived:"В архиве",status_expired:"Истёк",status_review:"На рассмотрении",
            tooltip_view:"Просмотр",tooltip_download:"Скачать",tooltip_delete:"Удалить",
            no_documents:"Документы не найдены",create_first_doc:"Создайте первый документ, чтобы начать работу",
            delete_doc_title:"Удалить документ?",delete_doc_text:"Вы действительно хотите удалить документ",delete_doc_warning:"Это действие нельзя отменить.",cancel:"Отмена",delete:"Удалить",toast_success:"Успешно",toast_error:"Ошибка",not_assigned:"Не назначен",onlineNowShort:"в сети",
            statsTitle:"Статистика документов",statsSub:"Общий обзор по статусам документов",period_7d:"7 дней",period_30d:"30 дней",period_3m:"3 месяца",period_1y:"Год",
            dynamicsTitle:"Динамика документов",no_data:"Нет данных",summary_total:"Всего",summary_processing:"В процессе",summary_archive:"Архив",summary_rejected:"Отклонено",chart_by_days:"По дням месяца",chart_docs:"Документы",chart_total:"Всего",
            st_incoming:"Входящие",st_outgoing:"Исходящие",st_processing:"На подписи",st_signed:"Подписанные",st_draft:"Черновики",st_rejected:"Отклонённые",st_archive:"Архив",
            recentNotifications:"Последние уведомления",all_short:"Все",notif_sent:"Документ отправлен вам",notif_sign:"Требуется ваша подпись",notif_signed:"Вы подписали документ",notif_comment:"Новый комментарий",notif_rejected:"Документ отклонён",yesterday:"Вчера",no_notifications:"Нет уведомлений",
            quickActions:"Быстрые действия",qa_create:"Создать документ",qa_sign:"На подпись",qa_contact:"Найти контакт",qa_analytics:"Аналитика"
        },
        tj: {
            welcomeBack:"Хуш омадед,",dashSubtitle:"Шумо {pending} ҳуҷҷат дар интизори имзо ва {incoming} воридоти нав доред.",
            totalDocs:"Ҳамаи ҳуҷҷатҳо",pending:"Дар интизори имзо",signedMonth:"Имзошуда дар моҳ",counterparties:"Тамосҳо",
            recentDocuments:"Ҳуҷҷатҳои охирин",allOperations:"Ҳамаи амалиётҳо дар системаи ҳуҷҷатгардонӣ",all_documents:"Ҳамаи ҳуҷҷатҳо",
            thId:"ID",thDoc:"Ҳуҷҷат",thType:"Намуд",thStatus:"Статус",thDate:"Сана",thActions:"Амалҳо",
            status_pending:"Дар интизори имзо",status_approved:"Тасдиқ шуд",status_signed:"Имзо шуд",status_completed:"Анҷом ёфт",status_active:"Фаъол",status_rejected:"Рад шуд",status_cancelled:"Бекор шуд",status_draft:"Пешнавис",status_archived:"Дар бойгонӣ",status_expired:"Мӯҳлаташ гузашт",status_review:"Дар баррасӣ",
            tooltip_view:"Дидан",tooltip_download:"Зеркашӣ",tooltip_delete:"Нест кардан",
            no_documents:"Ҳуҷҷатҳо ёфт нашуданд",create_first_doc:"Барои оғози кор аввалин ҳуҷҷатро эҷод кунед",
            delete_doc_title:"Ҳуҷҷатро нест мекунед?",delete_doc_text:"Шумо мутмаин ҳастед, ки мехоҳед ҳуҷҷатро нест кунед",delete_doc_warning:"Ин амалро бекор карда намешавад.",cancel:"Бекор кардан",delete:"Нест кардан",toast_success:"Бомуваффақият",toast_error:"Хато",not_assigned:"Таъин нашудааст",onlineNowShort:"дар шабака",
            statsTitle:"Омори ҳуҷҷатҳо",statsSub:"Шарҳи умумӣ аз рӯи ҳолатҳои ҳуҷҷатҳо",period_7d:"7 рӯз",period_30d:"30 рӯз",period_3m:"3 моҳ",period_1y:"Сол",
            dynamicsTitle:"Динамикаи ҳуҷҷатҳо",no_data:"Маълумот нест",summary_total:"Ҳамагӣ",summary_processing:"Дар раванд",summary_archive:"Бойгонӣ",summary_rejected:"Радшуда",chart_by_days:"Аз рӯи рӯзҳои моҳ",chart_docs:"Ҳуҷҷатҳо",chart_total:"Ҳамагӣ",
            st_incoming:"Воридшаванда",st_outgoing:"Содиршаванда",st_processing:"Барои имзо",st_signed:"Имзошуда",st_draft:"Пешнависҳо",st_rejected:"Радшуда",st_archive:"Бойгонӣ",
            recentNotifications:"Огоҳиҳои охирин",all_short:"Ҳама",notif_sent:"Ҳуҷҷат ба шумо фиристода шуд",notif_sign:"Имзои шумо лозим аст",notif_signed:"Шумо ҳуҷҷатро имзо кардед",notif_comment:"Шарҳи нав",notif_rejected:"Ҳуҷҷат рад шуд",yesterday:"Дирӯз",no_notifications:"Огоҳиҳо нест",
            quickActions:"Амалҳои зуд",qa_create:"Эҷоди ҳуҷҷат",qa_sign:"Барои имзо",qa_contact:"Ёфтани тамос",qa_analytics:"Таҳлил"
        },
        en: {
            welcomeBack:"Welcome back,",dashSubtitle:"You have {pending} documents awaiting signature and {incoming} new incoming.",
            totalDocs:"Total Documents",pending:"Pending Signature",signedMonth:"Signed This Month",counterparties:"Contacts",
            recentDocuments:"Recent Documents",allOperations:"All operations in the electronic document management system",all_documents:"All documents",
            thId:"ID",thDoc:"Document",thType:"Type",thStatus:"Status",thDate:"Date",thActions:"Actions",
            status_pending:"Pending",status_approved:"Approved",status_signed:"Signed",status_completed:"Completed",status_active:"Active",status_rejected:"Rejected",status_cancelled:"Cancelled",status_draft:"Draft",status_archived:"Archived",status_expired:"Expired",status_review:"Under Review",
            tooltip_view:"View",tooltip_download:"Download",tooltip_delete:"Delete",
            no_documents:"No documents found",create_first_doc:"Create the first document to get started",
            delete_doc_title:"Delete document?",delete_doc_text:"Are you sure you want to delete the document",delete_doc_warning:"This action cannot be undone.",cancel:"Cancel",delete:"Delete",toast_success:"Success",toast_error:"Error",not_assigned:"Not assigned",onlineNowShort:"online",
            statsTitle:"Document statistics",statsSub:"Overview by document status",period_7d:"7 days",period_30d:"30 days",period_3m:"3 months",period_1y:"Year",
            dynamicsTitle:"Document dynamics",no_data:"No data",summary_total:"Total",summary_processing:"Processing",summary_archive:"Archive",summary_rejected:"Rejected",chart_by_days:"By days of month",chart_docs:"Documents",chart_total:"Total",
            st_incoming:"Incoming",st_outgoing:"Outgoing",st_processing:"To sign",st_signed:"Signed",st_draft:"Drafts",st_rejected:"Rejected",st_archive:"Archive",
            recentNotifications:"Recent notifications",all_short:"All",notif_sent:"Document sent to you",notif_sign:"Your signature is required",notif_signed:"You signed a document",notif_comment:"New comment",notif_rejected:"Document rejected",yesterday:"Yesterday",no_notifications:"No notifications",
            quickActions:"Quick actions",qa_create:"Create document",qa_sign:"To sign",qa_contact:"Find contact",qa_analytics:"Analytics"
        }
    };
    
    // Массивы дней недели и месяцев для разных языков
    const daysOfWeek = {
        ru: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
        tj: ['Якшанбе', 'Душанбе', 'Сешанбе', 'Чоршанбе', 'Панҷшанбе', 'Ҷумъа', 'Шанбе'],
        en: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
    };
    
    const monthsOfYear = {
        ru: ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'],
        tj: ['Январ', 'Феврал', 'Март', 'Апрел', 'Май', 'Июн', 'Июл', 'Август', 'Сентябр', 'Октябр', 'Ноябр', 'Декабр'],
        en: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
    };
    
    function getCurrentLang(){
        return localStorage.getItem('docsign_lang') || localStorage.getItem('app-lang') || 'ru';
    }
    
    function getTr(){
        return translations[getCurrentLang()] || translations.ru;
    }
    
    function updateClock(){
        const lang = getCurrentLang();
        const now = new Date();
        
        // Получаем день недели и месяц на нужном языке
        const dayName = (daysOfWeek[lang] || daysOfWeek.ru)[now.getDay()];
        const monthName = (monthsOfYear[lang] || monthsOfYear.ru)[now.getMonth()];
        const day = now.getDate();
        const year = now.getFullYear();
        
        // Форматируем дату: "Вторник, 13 октября 2026"
        const d = `${dayName}, ${day} ${monthName} ${year}`;
        
        const dEl = document.getElementById('heroDate');
        const tEl = document.getElementById('heroTime');
        
        if(dEl) dEl.textContent = d;
        if(tEl) {
            const timeLocale = lang === 'en' ? 'en-US' : (lang === 'tj' ? 'ru-RU' : 'ru-RU');
            tEl.textContent = now.toLocaleTimeString(timeLocale, {hour: '2-digit', minute: '2-digit'});
        }
    }
    
    updateClock();
    setInterval(updateClock, 15000);
    
    function applyDataLabels(){
        const t = getTr();
        document.querySelectorAll('.doc-table td[data-label-key]').forEach(td => {
            td.setAttribute('data-label', t[td.getAttribute('data-label-key')] || '');
        });
    }
    
    function applyTranslations(){
        const t = getTr();
        
        // Обновляем dashSubtitle с заменой переменных
        const sub = document.querySelector('[data-i18n="dashSubtitle"]');
        if(sub && t.dashSubtitle){
            const pendingCount = {{ (int)($stats['pending'] ?? 0) }};
            const incomingCount = {{ (int)($stats['incoming'] ?? 0) }};
            sub.textContent = t.dashSubtitle.replace('{pending}', pendingCount).replace('{incoming}', incomingCount);
        }
        
        // Обновляем все остальные элементы с data-i18n
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const k = el.getAttribute('data-i18n');
            if(t[k] && k !== 'dashSubtitle'){
                if(el.children.length > 0){
                    el.childNodes.forEach(n => {
                        if(n.nodeType === Node.TEXT_NODE && n.textContent.trim() !== ""){
                            n.textContent = t[k];
                        }
                    });
                } else {
                    el.textContent = t[k];
                }
            }
        });
        
        // Обновляем title и data-tooltip
        document.querySelectorAll('[data-i18n-title]').forEach(el => {
            const k = el.getAttribute('data-i18n-title');
            if(t[k]){
                el.setAttribute('title', t[k]);
                el.setAttribute('data-tooltip', t[k]);
            }
        });
        
        applyDataLabels();
    }
    
    applyTranslations();
    
    window.addEventListener('docsign:lang-changed', function(e){
        if(e.detail && e.detail.lang){
            localStorage.setItem('docsign_lang', e.detail.lang);
            localStorage.setItem('app-lang', e.detail.lang);
        }
        applyTranslations();
        updateClock();
        if(window.updateChartsLang) window.updateChartsLang();
    });
    
    window.showToast = function(type, title, message){
        const c = document.getElementById('toastContainer');
        if(!c) return;
        const t = document.createElement('div');
        t.className = 'toast ' + type;
        const i = document.createElement('div');
        i.className = 'toast-icon';
        i.innerHTML = '<i class="bi ' + (type === 'success' ? 'bi-check-circle-fill' : 'bi-x-circle-fill') + '"></i>';
        const co = document.createElement('div');
        co.className = 'toast-content';
        const tt = document.createElement('div');
        tt.className = 'toast-title';
        tt.textContent = title;
        const m = document.createElement('div');
        m.className = 'toast-message';
        m.textContent = message;
        co.appendChild(tt);
        co.appendChild(m);
        t.appendChild(i);
        t.appendChild(co);
        c.appendChild(t);
        setTimeout(() => {
            t.classList.add('toast-out');
            setTimeout(() => t.remove(), 300);
        }, 4000);
    };
    
    @if(session('success'))
        showToast('success', getTr().toast_success, @json(session('success')));
    @endif
    @if(session('error'))
        showToast('error', getTr().toast_error, @json(session('error')));
    @endif
    
    (function initCharts(){
        if(typeof ApexCharts === 'undefined') return;
        const isDark = () => (document.documentElement.dataset.theme || 'light') === 'dark';
        const cssVar = n => getComputedStyle(document.documentElement).getPropertyValue(n).trim();
        const getGlow = () => {const r = cssVar('--glow'); return r ? 'rgb(' + r + ')' : 'rgb(79,140,255)';};
        const getMuted = () => isDark() ? '#8d99bb' : '#7482a3';
        const getText = () => isDark() ? '#e8ecf8' : '#0f1b3d';
        const getGrid = () => isDark() ? 'rgba(255,255,255,0.07)' : 'rgba(28,43,100,0.08)';
        const getBg = () => cssVar('--bg-1') || (isDark() ? '#111830' : '#ffffff');
        const hasSplit = @json($hasSplit);
        const dayLabels = @json($daily->map(fn($r) => (string) data_get($r, 'date'))->values());
        const dayCount = @json($daily->map(fn($r) => (int) data_get($r, 'count', 0))->values());
        const dayIn = @json($daily->map(fn($r) => (int) data_get($r, 'incoming', 0))->values());
        const dayOut = @json($daily->map(fn($r) => (int) data_get($r, 'outgoing', 0))->values());
        const statusKeys = @json($statusItems->pluck('k')->values());
        const statusVals = @json($statusItems->pluck('v')->values());
        const statusColors = @json($statusItems->pluck('c')->values());
        const statusTotal = statusVals.reduce((a, b) => Number(a) + Number(b), 0);
        
        function statusLabels(){
            const t = getTr();
            return statusKeys.map(k => t['st_' + k] || k);
        }
        
        function barSeries(){
            const t = getTr();
            return hasSplit 
                ? [{name: t.st_incoming, data: dayIn}, {name: t.st_outgoing, data: dayOut}] 
                : [{name: t.chart_docs, data: dayCount}];
        }
        
        let barChart = null, donutChart = null;
        const barEl = document.getElementById('docsFlowChart');
        if(barEl && dayLabels.length){
            barChart = new ApexCharts(barEl, {
                chart: {
                    type: 'bar',
                    height: 260,
                    toolbar: {show: false},
                    zoom: {enabled: false},
                    fontFamily: 'Inter, sans-serif',
                    foreColor: getMuted()
                },
                series: barSeries(),
                colors: [getGlow(), '#8b5cf6'],
                plotOptions: {
                    bar: {
                        columnWidth: hasSplit ? '60%' : '40%',
                        borderRadius: 5,
                        borderRadiusApplication: 'end'
                    }
                },
                dataLabels: {enabled: false},
                stroke: {show: true, width: 2, colors: ['transparent']},
                grid: {
                    borderColor: getGrid(),
                    strokeDashArray: 4,
                    padding: {left: 4, right: 4}
                },
                xaxis: {
                    categories: dayLabels,
                    axisBorder: {show: false},
                    axisTicks: {show: false},
                    labels: {style: {fontSize: '11px'}}
                },
                yaxis: {
                    forceNiceScale: true,
                    labels: {
                        style: {fontSize: '11px'},
                        formatter: v => Math.round(v)
                    }
                },
                legend: {show: false},
                tooltip: {theme: isDark() ? 'dark' : 'light'}
            });
            barChart.render();
        } else if(barEl){
            barEl.innerHTML = '<div class="rn-empty"><i class="bi bi-bar-chart"></i><span>—</span></div>';
        }
        
        const donutEl = document.getElementById('statusDonutChart');
        if(donutEl){
            donutChart = new ApexCharts(donutEl, {
                chart: {
                    type: 'donut',
                    height: 220,
                    width: 220,
                    fontFamily: 'Inter, sans-serif',
                    foreColor: getMuted()
                },
                series: statusTotal > 0 ? statusVals : [],
                labels: statusLabels(),
                colors: statusColors,
                stroke: {width: 3, colors: [getBg()]},
                dataLabels: {enabled: false},
                plotOptions: {
                    pie: {
                        expandOnClick: false,
                        donut: {
                            size: '76%',
                            labels: {
                                show: true,
                                name: {show: true, offsetY: 20, fontSize: '12px', color: getMuted()},
                                value: {show: true, offsetY: -14, fontSize: '28px', fontWeight: 800, color: getText()},
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: getTr().chart_total,
                                    fontSize: '12px',
                                    fontWeight: 500,
                                    color: getMuted(),
                                    formatter: function(){return String(statusTotal)}
                                }
                            }
                        }
                    }
                },
                legend: {show: false},
                tooltip: {theme: isDark() ? 'dark' : 'light'},
                noData: {
                    text: '—',
                    align: 'center',
                    verticalAlign: 'middle',
                    style: {color: getMuted(), fontSize: '14px'}
                }
            });
            donutChart.render();
        }
        
        function refreshTheme(){
            setTimeout(() => {
                if(barChart) barChart.updateOptions({
                    colors: [getGlow(), '#8b5cf6'],
                    chart: {foreColor: getMuted()},
                    grid: {borderColor: getGrid()},
                    tooltip: {theme: isDark() ? 'dark' : 'light'}
                });
                if(donutChart) donutChart.updateOptions({
                    chart: {foreColor: getMuted()},
                    stroke: {colors: [getBg()]},
                    tooltip: {theme: isDark() ? 'dark' : 'light'},
                    plotOptions: {
                        pie: {
                            donut: {
                                labels: {
                                    name: {color: getMuted()},
                                    value: {color: getText()},
                                    total: {color: getMuted(), label: getTr().chart_total}
                                }
                            }
                        }
                    }
                });
            }, 60);
        }
        
        new MutationObserver(refreshTheme).observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme']
        });
        
        window.updateChartsLang = function(){
            if(barChart) barChart.updateOptions({series: barSeries()});
            if(donutChart) donutChart.updateOptions({
                labels: statusLabels(),
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                total: {label: getTr().chart_total}
                            }
                        }
                    }
                }
            });
            refreshTheme();
        };
        
        let lastGlow = getGlow();
        setInterval(() => {
            const g = getGlow();
            if(g !== lastGlow){
                lastGlow = g;
                refreshTheme();
            }
        }, 1500);
    })();
});

let currentDeleteId = null;
function confirmDelete(id, name){
    currentDeleteId = id;
    const m = document.getElementById('deleteModal');
    const n = document.getElementById('deleteDocName');
    const f = document.getElementById('deleteForm');
    if(n) n.textContent = name;
    if(f) f.action = '/documents/' + id;
    if(m) m.classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeDeleteModal(){
    const m = document.getElementById('deleteModal');
    if(m) m.classList.remove('active');
    document.body.style.overflow = '';
    currentDeleteId = null;
}
function submitDelete(){
    const f = document.getElementById('deleteForm');
    if(currentDeleteId && f){
        f.style.display = 'block';
        f.submit();
    }
}
document.addEventListener('click', function(e){
    if(e.target.id === 'deleteModal') closeDeleteModal();
});
document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') closeDeleteModal();
});
</script>
<script>setInterval(function(){location.reload()}, 600000);</script>
@endsection