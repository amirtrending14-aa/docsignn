@extends('layouts.admin')

@section('content')
<style>
    /* === ADMIN PROFILE STYLE === */
    .profile-page {
        padding: clamp(16px, 3vw, 32px);
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
    }

    .profile-header {
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .profile-title {
        font-size: clamp(20px, 3vw, 24px);
        font-weight: 700;
        color: var(--text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .profile-title::before {
        content: "";
        width: 4px;
        height: 24px;
        background: rgb(var(--glow));
        border-radius: 2px;
        box-shadow: 0 0 10px rgba(var(--glow), 0.5);
    }

    /* Сетка профиля */
    .profile-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    @media (min-width: 992px) {
        .profile-grid {
            grid-template-columns: 320px 1fr;
        }
    }

    /* Карточки в стиле админки */
    .profile-card {
        background: var(--bg-1);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .profile-card:hover {
        border-color: rgba(var(--glow), 0.3);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }

    /* Левая карточка: Аватар */
    .avatar-card {
        padding: 32px 24px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        background: radial-gradient(circle at 50% 0%, rgba(var(--glow), 0.08), transparent 60%);
    }

    .avatar-box {
        width: 120px;
        height: 120px;
        border-radius: 16px;
        background: rgba(var(--glow), 0.1);
        border: 2px solid rgba(var(--glow), 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .avatar-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-letter {
        color: rgb(var(--glow));
        font-size: 48px;
        font-weight: 800;
    }

    .profile-name {
        font-size: 20px;
        font-weight: 700;
        color: var(--text);
        margin: 0 0 4px;
    }

    .profile-email {
        font-size: 13px;
        color: var(--muted);
        margin: 0 0 16px;
        word-break: break-all;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: rgba(var(--glow), 0.1);
        border: 1px solid rgba(var(--glow), 0.25);
        color: rgb(var(--glow));
    }

    .role-badge::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 8px #10b981;
    }

    /* Правая карточка: Детали */
    .details-header {
        padding: 16px 24px;
        border-bottom: 1px solid var(--line);
        background: rgba(255,255,255,0.02);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .details-header-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--muted);
    }

    .status-active {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #10b981;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.7; transform: scale(0.9); }
    }

    .details-body {
        padding: 24px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    @media (min-width: 640px) {
        .info-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    .info-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--muted);
        margin-bottom: 6px;
        display: block;
    }

    .info-value {
        font-size: 15px;
        font-weight: 600;
        color: var(--text);
        margin: 0;
    }

    .access-section {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid var(--line);
    }

    .details-footer {
        padding: 16px 24px 24px;
        display: flex;
        justify-content: flex-end;
    }

    .btn-edit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: rgb(var(--glow));
        color: #ffffff;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        box-shadow: 0 4px 12px rgba(var(--glow), 0.3);
    }

    .btn-edit:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(var(--glow), 0.4);
        filter: brightness(1.1);
    }

    .btn-edit svg {
        width: 14px;
        height: 14px;
    }

    /* === ACTIVITY GRID (Admin Style) === */
    .activity-card {
        margin-top: 20px;
        background: var(--bg-1);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 24px;
    }

    .activity-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 20px;
    }

    .activity-title span {
        color: rgb(var(--glow));
        font-weight: 700;
    }

    .gh-wrapper {
        overflow-x: auto;
        overflow-y: hidden;
        scrollbar-width: thin;
        scrollbar-color: var(--line) transparent;
        padding-bottom: 8px;
    }

    .gh-wrapper::-webkit-scrollbar {
        height: 6px;
    }
    .gh-wrapper::-webkit-scrollbar-thumb {
        background: var(--line);
        border-radius: 3px;
    }

    .gh-grid {
        display: inline-grid;
        grid-template-areas: ". months" "days squares";
        grid-template-columns: 40px 1fr;
        gap: 4px 8px;
        min-width: fit-content;
    }

    .gh-months {
        grid-area: months;
        display: grid;
        grid-template-columns: repeat(var(--weeks-count, 53), 12px);
        gap: 4px;
        font-size: 10px;
        color: var(--muted);
        font-weight: 500;
        height: 16px;
    }

    .gh-days {
        grid-area: days;
        display: grid;
        grid-template-rows: repeat(7, 12px);
        gap: 4px;
        font-size: 10px;
        color: var(--muted);
        font-weight: 500;
        line-height: 12px;
    }

    .gh-squares {
        grid-area: squares;
        display: grid;
        grid-template-rows: repeat(7, 12px);
        grid-auto-flow: column;
        grid-auto-columns: 12px;
        gap: 4px;
    }

    .sq {
        width: 12px;
        height: 12px;
        border-radius: 2px;
        background-color: rgba(255,255,255,0.04);
        border: 1px solid var(--line);
        cursor: pointer;
        transition: transform 0.1s ease;
    }

    .sq:hover {
        transform: scale(1.3);
        z-index: 5;
        border-color: rgba(var(--glow), 0.6);
    }

    /* Цвета квадратов на основе системного --glow */
    .l1 { background-color: rgba(var(--glow), 0.2) !important; border-color: rgba(var(--glow), 0.3) !important; }
    .l2 { background-color: rgba(var(--glow), 0.4) !important; border-color: rgba(var(--glow), 0.5) !important; }
    .l3 { background-color: rgba(var(--glow), 0.7) !important; border-color: rgba(var(--glow), 0.8) !important; }
    .l4 { background-color: rgb(var(--glow)) !important; border-color: rgb(var(--glow)) !important; box-shadow: 0 0 8px rgba(var(--glow), 0.4); }

    .activity-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        font-size: 11px;
        color: var(--muted);
        flex-wrap: wrap;
        gap: 12px;
    }

    .activity-legend {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .activity-legend .sq {
        cursor: default;
        width: 10px;
        height: 10px;
    }
    .activity-legend .sq:hover { transform: none; }

    /* Светлая тема */
    html[data-theme="light"] .profile-card,
    html[data-theme="light"] .activity-card {
        background: #ffffff;
        border-color: #e2e8f0;
    }
    html[data-theme="light"] .details-header {
        background: #f8fafc;
        border-color: #e2e8f0;
    }
    html[data-theme="light"] .sq {
        background-color: #f1f5f9;
        border-color: #e2e8f0;
    }
    html[data-theme="light"] .l1 { background-color: rgba(var(--glow), 0.15) !important; border-color: rgba(var(--glow), 0.2) !important; }
    html[data-theme="light"] .l2 { background-color: rgba(var(--glow), 0.35) !important; border-color: rgba(var(--glow), 0.4) !important; }
    html[data-theme="light"] .l3 { background-color: rgba(var(--glow), 0.6) !important; border-color: rgba(var(--glow), 0.7) !important; }
    html[data-theme="light"] .l4 { background-color: rgb(var(--glow)) !important; border-color: rgb(var(--glow)) !important; }
</style>

<div class="profile-page font-inter">
    <div class="profile-header">
        <h1 class="profile-title">
            <span data-i18n="profileTitle">Профиль</span>
        </h1>
    </div>

    <div class="profile-grid">
        <!-- Левая карточка -->
        <div class="profile-card avatar-card">
            <div class="avatar-box">
                @if($user->avatar)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                @else
                    <span class="avatar-letter">{{ Str::upper(Str::substr($user->name, 0, 1)) }}</span>
                @endif
            </div>

            <h2 class="profile-name">{{ $user->name }}</h2>
            <p class="profile-email">{{ $user->email }}</p>

            <div class="role-badge">
                {{ ucfirst($user->role ?? 'User') }}
            </div>
        </div>

        <!-- Правая карточка -->
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
                        <label class="info-label" data-i18n="labelEmail">Email адрес</label>
                        <p class="info-value">{{ $user->email }}</p>
                    </div>
                    <div>
                        <label class="info-label" data-i18n="labelCompany">Компания</label>
                        <p class="info-value">{{ $user->company ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="info-label" data-i18n="labelPhone">Телефон</label>
                        <p class="info-value">{{ $user->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="info-label" data-i18n="labelCreatedAt">Дата регистрации</label>
                        <p class="info-value">{{ $user->created_at->translatedFormat('d M Y') }}</p>
                    </div>
                </div>

                <div class="access-section">
                    <label class="info-label" data-i18n="labelAccess">Уровень доступа</label>
                    <p class="info-value" style="color: rgb(var(--glow));" data-i18n="accessFull">Полный административный контроль</p>
                </div>
            </div>

            <div class="details-footer">
                <a href="{{ route('profile.edit', $user->id) }}" class="btn-edit">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    <span data-i18n="btnEdit">Редактировать</span>
                </a>
            </div>
        </div>
    </div>

    <!-- График активности -->
    <div class="activity-card">
        <div class="activity-title">
            <span>{{ array_sum($activityData) }}</span>
            <span data-i18n="activitySummary">вкладов в</span> {{ $year ?? date('Y') }}
        </div>

        <div class="gh-wrapper">
            <div class="gh-grid" style="--weeks-count: {{ $weeksCount ?? 53 }}">
                <div class="gh-days">
                    <div class="gh-day-label" data-i18n="dayMon">Пн</div>
                    <div class="gh-day-label"></div>
                    <div class="gh-day-label" data-i18n="dayWed">Ср</div>
                    <div class="gh-day-label"></div>
                    <div class="gh-day-label" data-i18n="dayFri">Пт</div>
                    <div class="gh-day-label"></div>
                    <div class="gh-day-label" data-i18n="daySun">Вс</div>
                </div>

                <div class="gh-months">
                    @php $lastMonth = -1; @endphp
                    @for($w = 0; $w < ($weeksCount ?? 53); $w++)
                        @php
                            $dateInWeek = ($startDate ?? now()->startOfYear())->copy()->addWeeks($w);
                            $month = $dateInWeek->month;
                        @endphp
                        @if($month != $lastMonth)
                            <span style="grid-column: {{ $w + 1 }}; font-size: 10px; color: var(--muted);">
                                {{ $dateInWeek->translatedFormat('M') }}
                            </span>
                            @php $lastMonth = $month; @endphp
                        @endif
                    @endfor
                </div>

                <div class="gh-squares">
                    @for($i = 0; $i < (($weeksCount ?? 53) * 7); $i++)
                        @php
                            $day = ($startDate ?? now()->startOfYear())->copy()->addDays($i);
                            $isCurrentYear = $day->year == ($year ?? date('Y'));
                            $key = $day->toDateString();
                            $count = $activityData[$key] ?? 0;

                            $level = 0;
                            if ($count > 0) $level = 1;
                            if ($count > 2) $level = 2;
                            if ($count > 5) $level = 3;
                            if ($count > 10) $level = 4;

                            $tooltipText = ($count > 0 ? $count : 'Нет') . ' действий за ' . $day->translatedFormat('j F Y');
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

        <div class="activity-footer">
            <span data-i18n="activityLegend">Уровень активности</span>
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
                labelCompany: 'Компания',
                labelPhone: 'Телефон',
                labelCreatedAt: 'Дата регистрации',
                labelAccess: 'Уровень доступа',
                accessFull: 'Полный административный контроль',
                btnEdit: 'Редактировать',
                activitySummary: 'вкладов в',
                activityLegend: 'Уровень активности',
                legendLess: 'Меньше',
                legendMore: 'Больше',
                dayMon: 'Пн', dayWed: 'Ср', dayFri: 'Пт', daySun: 'Вс'
            },
            tj: {
                profileTitle: 'Профил',
                mainInfo: 'Маълумоти асосӣ',
                statusActive: 'Фаъол',
                labelFullName: 'Номи пурра',
                labelEmail: 'Суроғаи Email',
                labelCompany: 'Ширкат',
                labelPhone: 'Телефон',
                labelCreatedAt: 'Санаи бақайдгирӣ',
                labelAccess: 'Сатҳи дастрасӣ',
                accessFull: 'Назорати пурраи маъмурӣ',
                btnEdit: 'Таҳрир',
                activitySummary: 'саҳмҳо дар соли',
                activityLegend: 'Сатҳи фаъолият',
                legendLess: 'Камтар',
                legendMore: 'Бештар',
                dayMon: 'Дш', dayWed: 'Чш', dayFri: 'Ҷм', daySun: 'Як'
            },
            en: {
                profileTitle: 'Profile',
                mainInfo: 'Main Information',
                statusActive: 'Active',
                labelFullName: 'Full Name',
                labelEmail: 'Email Address',
                labelCompany: 'Company',
                labelPhone: 'Phone',
                labelCreatedAt: 'Registration Date',
                labelAccess: 'Access Level',
                accessFull: 'Full Administrative Control',
                btnEdit: 'Edit',
                activitySummary: 'contributions in',
                activityLegend: 'Activity Level',
                legendLess: 'Less',
                legendMore: 'More',
                dayMon: 'Mon', dayWed: 'Wed', dayFri: 'Fri', daySun: 'Sun'
            }
        };

        function applyProfileTranslations(lang) {
            const dict = PROFILE_TRANSLATIONS[lang] || PROFILE_TRANSLATIONS.ru;
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (dict[key] !== undefined) el.textContent = dict[key];
            });
        }

        // Инициализация тултипов графика
        if (typeof tippy !== 'undefined') {
            tippy('[data-tippy-content]', {
                theme: 'custom-admin',
                animation: 'fade',
                duration: 150,
                offset: [0, 8],
            });
        }

        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applyProfileTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            applyProfileTranslations(e.detail?.lang || 'ru');
        });
    });
</script>
@endsection