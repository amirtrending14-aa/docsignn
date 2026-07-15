@extends('layouts.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">

<style>
    .font-inter { font-family: 'Inter', sans-serif; }

    /* === DOC SIGN PROFILE STYLE === */

    /* Глобальная защита от горизонтального скролла на мобильных.
       Именно это было главной причиной "некрасиво": элементы вылезали
       за пределы экрана и страницу можно было скроллить вбок. */
    .profile-page,
    .profile-page *,
    .profile-page *::before,
    .profile-page *::after {
        box-sizing: border-box;
    }

    .profile-page {
        min-height: 100vh;
        width: 100%;
        max-width: 100vw;
        overflow-x: hidden;
        padding: clamp(14px, 4vw, 32px) clamp(10px, 3.5vw, 24px);
        color: var(--text);
    }

    /* Заголовок страницы */
    .profile-header {
        max-width: 1200px;
        margin: 0 auto clamp(14px, 3vw, 28px);
    }

    .profile-title {
        font-size: clamp(16px, 3.2vw, 22px);
        font-weight: 700;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: clamp(5px, 1.2vw, 10px);
        letter-spacing: -0.3px;
        margin: 0;
    }

    .profile-title::before {
        content: "";
        width: 4px;
        height: clamp(16px, 2.8vw, 22px);
        background: linear-gradient(180deg, rgba(var(--glow), 1), rgba(var(--glow), 0.3));
        border-radius: 2px;
        box-shadow: 0 0 10px rgba(var(--glow), 0.6);
        flex-shrink: 0;
    }

    /* Сетка карточек */
    .profile-grid {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr;
        gap: clamp(12px, 2.5vw, 20px);
        align-items: stretch;
        width: 100%;
    }

    @media (min-width: 1024px) {
        .profile-grid {
            grid-template-columns: 1fr 2fr;
        }
    }

    /* Карточка - glassmorphism */
    .profile-card {
        position: relative;
        background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 0;
        overflow: hidden;
        min-width: 0; /* критично внутри grid, иначе контент может распирать колонку */
        transition: all 0.3s ease;
    }

    .profile-card::before {
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: var(--radius);
        padding: 1px;
        background: linear-gradient(135deg, rgba(var(--glow),0.4), transparent 40%, transparent 60%, rgba(var(--glow),0.2));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0.6;
        pointer-events: none;
    }

    .profile-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 40px -20px rgba(var(--glow), 0.3);
    }

    /* Левая карточка - аватар */
    .avatar-card {
        padding: clamp(18px, 4vw, 32px) clamp(14px, 3vw, 24px);
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        background: radial-gradient(ellipse at 50% 0%, rgba(var(--glow), 0.15), transparent 70%);
    }

    .avatar-box {
        width: clamp(70px, 18vw, 128px);
        height: clamp(70px, 18vw, 128px);
        border-radius: clamp(12px, 2.5vw, 20px);
        background: linear-gradient(135deg, rgba(var(--glow), 0.6), rgba(var(--glow), 0.2));
        border: 3px solid rgba(10, 13, 20, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: clamp(12px, 2.5vw, 20px);
        box-shadow: 0 12px 28px rgba(var(--glow), 0.3), 0 0 0 1px rgba(var(--glow), 0.3);
        position: relative;
        overflow: hidden;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .avatar-box:hover {
        transform: scale(1.03);
        box-shadow: 0 16px 36px rgba(var(--glow), 0.4), 0 0 0 1px rgba(var(--glow), 0.5);
    }

    .avatar-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-letter {
        color: #ffffff;
        font-size: clamp(32px, 7vw, 56px);
        font-weight: 900;
        font-style: italic;
        text-shadow: 0 2px 12px rgba(0,0,0,0.3);
    }

    .profile-name {
        font-size: clamp(16px, 3.2vw, 22px);
        font-weight: 800;
        color: var(--text);
        margin: 0 0 4px;
        letter-spacing: -0.3px;
        max-width: 100%;
        overflow-wrap: break-word;
        word-break: break-word;
        hyphens: auto;
    }

    .profile-email {
        font-size: clamp(9px, 1.8vw, 11px);
        font-weight: 600;
        color: var(--muted);
        margin: 0 0 clamp(12px, 2.5vw, 20px);
        max-width: 100%;
        overflow-wrap: anywhere;
        word-break: break-word;
        letter-spacing: 0.3px;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: clamp(4px, 1vw, 6px) clamp(10px, 2.5vw, 16px);
        border-radius: 20px;
        font-size: clamp(8px, 1.6vw, 10px);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        background: rgba(var(--glow), 0.15);
        border: 1px solid rgba(var(--glow), 0.3);
        color: rgba(var(--glow), 1);
        font-family: 'JetBrains Mono', monospace;
        max-width: 100%;
        white-space: nowrap;
        justify-content: center;
    }

    .role-badge::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #4cd982;
        box-shadow: 0 0 8px #4cd982;
        flex-shrink: 0;
    }

    /* Правая карточка - детали */
    .details-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .details-header {
        padding: clamp(11px, 2vw, 18px) clamp(14px, 3vw, 24px);
        border-bottom: 1px solid var(--line);
        background: rgba(255,255,255,0.02);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .details-header-label {
        font-size: clamp(8px, 1.6vw, 10px);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--muted);
    }

    .status-active {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: clamp(8px, 1.6vw, 10px);
        font-weight: 700;
        text-transform: uppercase;
        color: #4cd982;
        letter-spacing: 0.8px;
        white-space: nowrap;
    }

    .status-dot {
        width: clamp(6px, 1.2vw, 8px);
        height: clamp(6px, 1.2vw, 8px);
        border-radius: 50%;
        background: #4cd982;
        box-shadow: 0 0 0 3px rgba(76, 217, 130, 0.2), 0 0 8px rgba(76, 217, 130, 0.6);
        animation: pulse 2s infinite;
        flex-shrink: 0;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .details-body {
        padding: clamp(12px, 3vw, 24px);
        flex-grow: 1;
        min-width: 0;
    }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: clamp(12px, 2.5vw, 20px);
    }

    @media (min-width: 480px) {
        .info-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    .info-grid > div {
        min-width: 0;
    }

    .info-label {
        font-size: clamp(7px, 1.5vw, 10px);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--muted);
        margin-bottom: 6px;
        display: block;
    }

    .info-value {
        font-size: clamp(11px, 2.2vw, 14px);
        font-weight: 600;
        color: var(--text);
        letter-spacing: -0.2px;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .access-section {
        margin-top: clamp(12px, 2.5vw, 24px);
        padding-top: clamp(10px, 2vw, 20px);
        border-top: 1px solid var(--line);
    }

    .access-text {
        font-size: clamp(9px, 1.8vw, 12px);
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text);
        letter-spacing: 0.3px;
        line-height: 1.4;
        overflow-wrap: break-word;
    }

    .details-footer {
        padding: 0 clamp(12px, 3vw, 24px) clamp(12px, 3vw, 24px);
        display: flex;
        justify-content: flex-end;
    }

    .btn-edit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: clamp(6px, 1.3vw, 8px);
        background: linear-gradient(180deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.65));
        color: #0a0d14;
        font-size: clamp(9px, 1.8vw, 10px);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        padding: clamp(9px, 2vw, 10px) clamp(14px, 3vw, 20px);
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.25s ease;
        border: 1px solid transparent;
        box-shadow: 0 8px 24px rgba(var(--glow), 0.35), inset 0 1px 0 rgba(255,255,255,0.3);
        min-height: 44px; /* touch-friendly на всех размерах, не только на телефонах */
        white-space: nowrap;
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(var(--glow), 0.5);
        filter: brightness(1.08);
    }

    .btn-edit:active {
        transform: scale(0.97);
    }

    .btn-edit svg {
        width: clamp(10px, 2vw, 12px);
        height: clamp(10px, 2vw, 12px);
        flex-shrink: 0;
    }

    /* На узких экранах кнопка растягивается на всю ширину — это удобнее пальцем */
    @media (max-width: 480px) {
        .details-footer {
            justify-content: stretch;
        }
        .btn-edit {
            width: 100%;
        }
    }

    /* === ACTIVITY GRID === */
    .activity-card {
        max-width: 1200px;
        margin: clamp(14px, 3vw, 20px) auto 0;
        background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: clamp(12px, 3vw, 20px) clamp(14px, 3vw, 24px);
        position: relative;
        width: 100%;
        min-width: 0;
    }

    .activity-card::before {
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: var(--radius);
        padding: 1px;
        background: linear-gradient(135deg, rgba(var(--glow),0.4), transparent 40%, transparent 60%, rgba(var(--glow),0.2));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0.6;
        pointer-events: none;
    }

    .activity-title {
        font-size: clamp(11px, 2.2vw, 14px);
        font-weight: 700;
        color: var(--text);
        margin-bottom: clamp(9px, 2vw, 16px);
        letter-spacing: -0.2px;
    }

    .activity-title span {
        color: rgba(var(--glow), 1);
        font-family: 'JetBrains Mono', monospace;
    }

    /* Сетка вкладов (github-style) всегда скроллится по горизонтали
       ВНУТРИ своего блока, а не толкает всю страницу вбок */
    .gh-wrapper {
        overflow-x: auto;
        overflow-y: hidden;
        max-width: 100%;
        scrollbar-width: none;
        position: relative;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 8px;
    }
    .gh-wrapper::-webkit-scrollbar { display: none; }

    .scroll-indicator {
        display: none;
        text-align: center;
        font-size: 9px;
        color: var(--muted);
        margin-top: 8px;
        opacity: 0.7;
    }

    @media (max-width: 768px) {
        .scroll-indicator { display: block; }
    }

    :root {
        --sq-size: 11px;
    }
    @media (max-width: 768px) {
        :root { --sq-size: 10px; }
    }
    @media (max-width: 640px) {
        :root { --sq-size: 9px; }
    }
    @media (max-width: 480px) {
        :root { --sq-size: 8px; }
    }
    @media (max-width: 380px) {
        :root { --sq-size: 7px; }
    }

    .gh-grid {
        display: inline-grid;
        grid-template-areas: ". months" "days squares";
        grid-template-columns: clamp(28px, 8vw, 45px) 1fr;
        gap: 4px 8px;
        min-width: fit-content;
    }
    .gh-months {
        grid-area: months;
        display: grid;
        grid-template-columns: repeat({{ $weeksCount }}, var(--sq-size));
        gap: 3px;
        font-size: 9px;
        color: var(--muted);
        font-weight: 600;
        height: 16px;
        position: relative;
    }
    .gh-days {
        grid-area: days;
        display: grid;
        grid-template-rows: repeat(7, var(--sq-size));
        gap: 3px;
        font-size: 9px;
        color: var(--muted);
        font-weight: 600;
        user-select: none;
    }
    .gh-day-label {
        display: flex;
        align-items: center;
        height: var(--sq-size);
        line-height: 1;
    }
    .gh-squares {
        grid-area: squares;
        display: grid;
        grid-template-rows: repeat(7, var(--sq-size));
        grid-auto-flow: column;
        grid-auto-columns: var(--sq-size);
        gap: 3px;
    }
    .sq {
        width: var(--sq-size);
        height: var(--sq-size);
        border-radius: 2px;
        background-color: rgba(255,255,255,0.04);
        border: 1px solid var(--line);
        box-sizing: border-box;
        cursor: pointer;
        transition: all 0.1s ease;
    }
    .sq:hover {
        transform: scale(1.3);
        z-index: 5;
        border-color: rgba(var(--glow), 0.6);
    }
    .l1 { background-color: #9be9a8 !important; border: none; box-shadow: 0 0 8px rgba(155, 233, 168, 0.4); }
    .l2 { background-color: #40c463 !important; border: none; box-shadow: 0 0 8px rgba(64, 196, 99, 0.4); }
    .l3 { background-color: #30a14e !important; border: none; box-shadow: 0 0 8px rgba(48, 161, 78, 0.4); }
    .l4 { background-color: #216e39 !important; border: none; box-shadow: 0 0 8px rgba(33, 110, 57, 0.4); }

    .activity-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: clamp(8px, 2vw, 14px);
        font-size: clamp(8px, 1.6vw, 10px);
        font-weight: 600;
        color: var(--muted);
        flex-wrap: wrap;
        gap: 10px;
    }

    @media (max-width: 640px) {
        .activity-footer {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    .activity-legend {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .activity-legend .sq {
        width: 10px;
        height: 10px;
        cursor: default;
    }

    .activity-legend .sq:hover { transform: none; }
</style>

<div class="profile-page font-inter">

    {{-- Заголовок --}}
    <div class="profile-header">
        <h1 class="profile-title">
            <span data-i18n="profileTitle">Профиль</span>
        </h1>
    </div>

    {{-- СЕТКА КАРТОЧЕК --}}
    <div class="profile-grid">

        {{-- ЛЕВАЯ КАРТОЧКА (АВАТАР) --}}
        <div class="profile-card avatar-card">
            <div class="avatar-box">
                @if($user->avatar)
                <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}">
                @else
                <span class="avatar-letter">{{ Str::upper(Str::substr($user->name, 0, 1)) }}</span>
                @endif
            </div>

            <h2 class="profile-name">{{ $user->name }}</h2>
            <p class="profile-email">{{ $user->email }}</p>

            <div class="role-badge">
                {{ $user->role ?? 'Director' }}
            </div>
        </div>

        {{-- ПРАВАЯ КАРТОЧКА (ДЕТАЛИ) --}}
        <div class="profile-card details-card">
            <div class="details-header">
                <span class="details-header-label" data-i18n="mainInfo">Основная информация</span>
                <div class="status-active">
                    <span class="status-dot"></span>
                    <span data-i18n="statusActive">Активен</span>
                </div>
            </div>

            <div class="details-body">
                <div class="info-grid">
                    <div>
                        <label class="info-label" data-i18n="labelFullName">Полное имя</label>
                        <p class="info-value">{{ $user->name }}</p>
                    </div>
                    <div>
                        <label class="info-label" data-i18n="labelEmail">Email</label>
                        <p class="info-value">{{ $user->email }}</p>
                    </div>
                    <div>
                        <label class="info-label" data-i18n="labelCompany">Название компании</label>
                        <p class="info-value">{{ $user->company ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="info-label" data-i18n="labelPhone">Контактный телефон</label>
                        <p class="info-value">{{ $user->phone ?? '+992 00 000 0000' }}</p>
                    </div>
                    <div>
                        <label class="info-label" data-i18n="labelCreatedAt">Дата создания</label>
                        <p class="info-value">{{ $user->created_at->translatedFormat('d M Y') }}</p>
                    </div>
                </div>

                <div class="access-section">
                    <label class="info-label" data-i18n="labelAccess">Уровень доступа</label>
                    <p class="access-text" data-i18n="accessFull">Назорати пурраи маъмурӣ</p>
                </div>
            </div>

            <div class="details-footer">
                <a href="{{ route('profile.edit', $user->id) }}" class="btn-edit">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32L19.513 8.2z" />
                    </svg>
                    <span data-i18n="btnEdit">Таҳрир</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ACTIVITY GRID --}}
    <div class="activity-card">
        <div class="activity-title">
            <span>{{ array_sum($activityData) }}</span>
            <span data-i18n="activitySummary">вкладов в</span> {{ $year }}
        </div>

        <div class="gh-wrapper">
            <div class="gh-grid">
                <div class="gh-months select-none">
                    @php $lastMonth = -1; @endphp
                    @for($w = 0; $w < $weeksCount; $w++)
                    @php
                    $dateInWeek = $startDate->copy()->addWeeks($w);
                    $month = $dateInWeek->month;
                    @endphp
                    <div style="grid-column: {{ $w + 1 }}; position: relative;">
                        @if($month != $lastMonth && $dateInWeek->year == $year)
                        <span style="position: absolute; left: 0; bottom: 0; white-space: nowrap;">
                                        {{ $dateInWeek->translatedFormat('M') }}
                                    </span>
                        @php $lastMonth = $month; @endphp
                        @endif
                    </div>
                    @endfor
                </div>

                <div class="gh-squares">
                    @for($i = 0; $i < ($weeksCount * 7); $i++)
                    @php
                    $day = $startDate->copy()->addDays($i);
                    $isCurrentYear = $day->year == $year;
                    $key = $day->toDateString();
                    $count = $activityData[$key] ?? 0;

                    $level = 0;
                    if ($count > 0) $level = 1;
                    if ($count > 2) $level = 2;
                    if ($count > 5) $level = 3;
                    if ($count > 10) $level = 4;

                    $tooltipText = ($count > 0 ? $count : 'No') . ' contributions on ' . $day->translatedFormat('j F, Y');
                    @endphp
                    @if($isCurrentYear)
                    <div class="sq {{ $level ? 'l'.$level : '' }}" data-tippy-content="{{ $tooltipText }}"></div>
                    @else
                    <div class="sq" style="background: transparent; border: none; cursor: default;"></div>
                    @endif
                    @endfor
                </div>
            </div>
        </div>

        <div class="scroll-indicator" data-i18n="scrollHint">← Прокрутите для просмотра →</div>

        <div class="activity-footer">
            <span data-i18n="activityLegend">Как мы считаем вклады</span>
            <div class="activity-legend">
                <span data-i18n="legendLess">Меньше</span>
                <div class="sq"></div>
                <div class="sq l1"></div>
                <div class="sq l2"></div>
                <div class="sq l3"></div>
                <div class="sq l4"></div>
                <span data-i18n="legendMore">Больше</span>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const PROFILE_TRANSLATIONS = {
            ru: {
                profileTitle: 'Профиль',
                mainInfo: 'Основная информация',
                statusActive: 'Активен',
                labelFullName: 'Полное имя',
                labelEmail: 'Email адрес',
                labelCompany: 'Название компании',
                labelPhone: 'Контактный телефон',
                labelCreatedAt: 'Дата создания',
                labelAccess: 'Уровень доступа',
                accessFull: 'Полный административный контроль',
                btnEdit: 'Изменить',
                activitySummary: 'вкладов в',
                activityLegend: 'Как мы считаем вклады',
                legendLess: 'Меньше',
                legendMore: 'Больше',
                scrollHint: '← Прокрутите для просмотра →',
                dayMon: 'Пн', dayTue: 'Вт', dayWed: 'Ср', dayThu: 'Чт', dayFri: 'Пт', daySat: 'Сб', daySun: 'Вс'
            },
            tj: {
                profileTitle: 'Профил',
                mainInfo: 'Маълумоти асосӣ',
                statusActive: 'Фаъол',
                labelFullName: 'Номи пурра',
                labelEmail: 'Суроғаи Email',
                labelCompany: 'Номи ширкат',
                labelPhone: 'Телефони тамос',
                labelCreatedAt: 'Санаи эҷод',
                labelAccess: 'Сатҳи дастрасӣ',
                accessFull: 'Назорати пурраи маъмурӣ',
                btnEdit: 'Таҳрир',
                activitySummary: 'саҳмҳо дар соли',
                activityLegend: 'Чӣ тавр мо саҳмҳоро ҳисоб мекунем',
                legendLess: 'Камтар',
                legendMore: 'Бештар',
                scrollHint: '← Барои дидан скрол кунед →',
                dayMon: 'Дш', dayTue: 'Сш', dayWed: 'Чш', dayThu: 'Пш', dayFri: 'Ҷм', daySat: 'Шн', daySun: 'Як'
            },
            en: {
                profileTitle: 'Profile',
                mainInfo: 'Main Information',
                statusActive: 'Active',
                labelFullName: 'Full Name',
                labelEmail: 'Email Address',
                labelCompany: 'Company Name',
                labelPhone: 'Phone Number',
                labelCreatedAt: 'Created At',
                labelAccess: 'Access Level',
                accessFull: 'Full Administrative Control',
                btnEdit: 'Edit',
                activitySummary: 'contributions in',
                activityLegend: 'Learn how we count contributions',
                legendLess: 'Less',
                legendMore: 'More',
                scrollHint: '← Scroll to view →',
                dayMon: 'Mon', dayTue: 'Tue', dayWed: 'Wed', dayThu: 'Thu', dayFri: 'Fri', daySat: 'Sat', daySun: 'Sun'
            }
        };

        function applyProfileTranslations(lang) {
            const dict = PROFILE_TRANSLATIONS[lang] || PROFILE_TRANSLATIONS.ru;

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

        tippy('[data-tippy-content]', {
            theme: 'dark',
            animation: 'fade',
            duration: [200, 50],
            offset: [0, 10],
            touch: ['hold', 500],
        });

        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applyProfileTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applyProfileTranslations(lang);
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applyProfileTranslations(e.newValue);
            }
        });
    });
</script>
@endsection