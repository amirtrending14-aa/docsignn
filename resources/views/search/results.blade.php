@extends('layouts.admin')

@section('content')
<style>
    /* === СТРАНИЦА ПОИСКА В СТИЛЕ АДМИНКИ === */
    .search-page-custom {
        color: var(--text);
    }

    .search-page-custom .page-head-custom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .search-page-custom h1 {
        margin: 0;
        font-size: 26px;
        font-weight: 700;
        letter-spacing: -0.5px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .search-page-custom h1 .accent-bar {
        width: 4px;
        height: 26px;
        background: rgba(var(--glow), 1);
        border-radius: 2px;
        box-shadow: 0 0 12px rgba(var(--glow), 0.8);
        flex-shrink: 0;
    }

    .search-page-custom .query-highlight {
        color: rgba(var(--glow), 1);
        font-family: 'JetBrains Mono', monospace;
        font-weight: 600;
        word-break: break-word;
    }

    /* Счётчик результатов */
    .results-counter {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 16px;
        background: rgba(255,255,255,0.035);
        border: 1px solid var(--line);
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .results-counter .count-num {
        font-family: 'JetBrains Mono', monospace;
        font-size: 14px;
        font-weight: 700;
        color: rgba(var(--glow), 1);
        text-shadow: 0 0 10px rgba(var(--glow), 0.5);
    }

    .results-counter .count-label {
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 10px;
    }

    /* Контейнер таблицы */
    .search-table-wrap {
        background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 4px;
        overflow: hidden;
        position: relative;
    }

    .search-table-wrap::before {
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: 14px;
        padding: 1px;
        background: linear-gradient(135deg, rgba(var(--glow), 0.4), transparent 40%, transparent 60%, rgba(var(--glow), 0.2));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
        opacity: 0.6;
    }

    /* Скролл-контейнер для таблицы */
    .search-table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .search-table-scroll::-webkit-scrollbar {
        height: 6px;
    }

    .search-table-scroll::-webkit-scrollbar-track {
        background: rgba(255,255,255,0.02);
    }

    .search-table-scroll::-webkit-scrollbar-thumb {
        background: rgba(var(--glow), 0.3);
        border-radius: 20px;
    }

    .search-table-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(var(--glow), 0.5);
    }

    .search-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }

    .search-table th {
        text-align: left;
        padding: 14px 16px;
        font-size: 11px;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 1px solid var(--line);
        background: rgba(255,255,255,0.02);
        white-space: nowrap;
    }

    .search-table th.center { text-align: center; }
    .search-table th.right { text-align: right; }

    .search-table td {
        padding: 16px;
        font-size: 13px;
        color: var(--text);
        border-bottom: 1px solid var(--line);
        transition: all .2s ease;
    }

    .search-table td.center { text-align: center; }
    .search-table td.right { text-align: right; }

    .search-table tbody tr {
        transition: all .25s ease;
    }

    .search-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .search-table tbody tr:hover {
        background: linear-gradient(90deg, rgba(var(--glow), 0.06), transparent 60%);
    }

    .search-table tbody tr:hover td:first-child {
        box-shadow: inset 3px 0 0 0 rgba(var(--glow), 1);
    }

    /* Иконка типа (User/Doc/Sig) */
    .type-icon {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-family: 'JetBrains Mono', monospace;
        font-size: 13px;
        font-weight: 800;
        flex-shrink: 0;
        transition: all .25s ease;
    }

    .search-table tbody tr:hover .type-icon {
        transform: scale(1.08);
        box-shadow: 0 0 16px currentColor;
    }

    .type-icon.user {
        background: rgba(76, 217, 130, 0.12);
        border: 1px solid rgba(76, 217, 130, 0.3);
        color: #4cd982;
    }

    .type-icon.signature {
        background: rgba(255, 181, 71, 0.12);
        border: 1px solid rgba(255, 181, 71, 0.3);
        color: #ffb547;
    }

    .type-icon.document {
        background: rgba(var(--glow), 0.12);
        border: 1px solid rgba(var(--glow), 0.3);
        color: rgba(var(--glow), 1);
    }

    .type-icon svg {
        width: 16px;
        height: 16px;
    }

    /* Ячейка с названием */
    .item-title {
        font-weight: 600;
        font-size: 13.5px;
        color: var(--text);
        display: block;
        max-width: 350px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .item-subtitle {
        font-size: 11px;
        color: var(--muted);
        margin-top: 3px;
        display: block;
        max-width: 350px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        opacity: 0.85;
    }

    /* Бейджи типов */
    .type-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 6px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        border: 1px solid;
        transition: all .2s ease;
        white-space: nowrap;
    }

    .type-badge:hover {
        transform: scale(1.05);
        box-shadow: 0 0 12px currentColor;
    }

    .type-badge.user {
        background: rgba(76, 217, 130, 0.1);
        border-color: rgba(76, 217, 130, 0.3);
        color: #4cd982;
    }

    .type-badge.signature {
        background: rgba(255, 181, 71, 0.1);
        border-color: rgba(255, 181, 71, 0.3);
        color: #ffb547;
    }

    .type-badge.document {
        background: rgba(var(--glow), 0.1);
        border-color: rgba(var(--glow), 0.3);
        color: rgba(var(--glow), 1);
    }

    /* Детали */
    .item-details {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11px;
        color: var(--muted);
        font-weight: 600;
        white-space: nowrap;
    }

    /* Дата и статус */
    .item-date {
        font-family: 'JetBrains Mono', monospace;
        font-size: 12px;
        color: var(--text);
        font-weight: 600;
        white-space: nowrap;
    }

    .item-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 10px;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
        white-space: nowrap;
    }

    .item-status::before {
        content: "";
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 6px currentColor;
    }

    .item-status.signed { color: #4cd982; }
    .item-status.processing { color: #ffb547; }
    .item-status.active { color: rgba(var(--glow), 1); }

    /* Кнопка действия (View) */
    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--line);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--muted);
        text-decoration: none;
        transition: all .2s ease;
    }

    .action-btn:hover {
        color: rgba(var(--glow), 1);
        border-color: rgba(var(--glow), 0.4);
        background: rgba(var(--glow), 0.1);
        box-shadow: 0 0 12px rgba(var(--glow), 0.3);
        transform: scale(1.08) translateX(2px);
    }

    .action-btn svg {
        width: 13px;
        height: 13px;
    }

    /* Пустое состояние */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }

    .empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: rgba(var(--glow), 0.08);
        border: 1px solid rgba(var(--glow), 0.2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        color: rgba(var(--glow), 1);
        font-size: 24px;
        box-shadow: 0 0 20px rgba(var(--glow), 0.15);
    }

    .empty-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 4px;
    }

    .empty-sub {
        font-size: 12px;
        color: var(--muted);
    }

    /* ============================================ */
    /* === ПОЛНАЯ АДАПТИВНОСТЬ === */
    /* ============================================ */

    /* Маленькие десктопы (до 1200px) */
    @media (max-width: 1200px) {
        .search-page-custom .page-head-custom { margin-bottom: 22px; gap: 14px; }
        .search-page-custom h1 { font-size: 24px; gap: 11px; }
        .search-page-custom h1 .accent-bar { width: 4px; height: 24px; }
        .results-counter { padding: 7px 15px; gap: 9px; }
        .results-counter .count-num { font-size: 13px; }
        .results-counter .count-label { font-size: 9px; }
        .search-table th { padding: 13px 15px; font-size: 10px; }
        .search-table td { padding: 15px; font-size: 12px; }
        .type-icon { width: 34px; height: 34px; font-size: 12px; }
        .type-icon svg { width: 15px; height: 15px; }
        .item-title { font-size: 13px; max-width: 300px; }
        .item-subtitle { font-size: 10px; max-width: 300px; }
        .type-badge { font-size: 8px; padding: 3px 9px; }
        .item-details { font-size: 10px; }
        .item-date { font-size: 11px; }
        .item-status { font-size: 9px; }
        .action-btn { width: 30px; height: 30px; }
        .action-btn svg { width: 12px; height: 12px; }
    }

    /* Планшеты (до 992px) */
    @media (max-width: 992px) {
        .search-page-custom .page-head-custom { margin-bottom: 20px; gap: 12px; }
        .search-page-custom h1 { font-size: 22px; gap: 10px; }
        .search-page-custom h1 .accent-bar { width: 3px; height: 22px; }
        .results-counter { padding: 7px 14px; gap: 8px; border-radius: 9px; }
        .results-counter .count-num { font-size: 13px; }
        .results-counter .count-label { font-size: 9px; letter-spacing: 0.9px; }
        .search-table-wrap { border-radius: 12px; }
        .search-table { min-width: 650px; }
        .search-table th { padding: 12px 13px; font-size: 10px; letter-spacing: 0.9px; }
        .search-table td { padding: 13px; font-size: 12px; }
        .type-icon { width: 32px; height: 32px; font-size: 12px; border-radius: 8px; }
        .type-icon svg { width: 14px; height: 14px; }
        .item-title { font-size: 12px; max-width: 250px; }
        .item-subtitle { font-size: 10px; max-width: 250px; margin-top: 2px; }
        .type-badge { font-size: 8px; padding: 3px 8px; border-radius: 5px; }
        .item-details { font-size: 10px; }
        .item-date { font-size: 11px; }
        .item-status { font-size: 9px; gap: 4px; margin-top: 3px; }
        .item-status::before { width: 4px; height: 4px; }
        .action-btn { width: 30px; height: 30px; border-radius: 7px; }
        .action-btn svg { width: 12px; height: 12px; }
        .empty-state { padding: 50px 18px; }
        .empty-icon { width: 58px; height: 58px; margin-bottom: 14px; }
        .empty-icon svg { width: 22px; height: 22px; }
        .empty-title { font-size: 13px; }
        .empty-sub { font-size: 11px; }
    }

    /* Большие телефоны (до 768px) */
    @media (max-width: 768px) {
        .search-page-custom .page-head-custom { margin-bottom: 18px; gap: 10px; }
        .search-page-custom h1 { font-size: 20px; gap: 9px; }
        .search-page-custom h1 .accent-bar { width: 3px; height: 20px; }
        .results-counter { padding: 6px 12px; gap: 7px; border-radius: 8px; }
        .results-counter .count-num { font-size: 12px; }
        .results-counter .count-label { font-size: 9px; letter-spacing: 0.8px; }
        .search-table-wrap { border-radius: 11px; padding: 3px; }
        .search-table { min-width: 600px; }
        .search-table th { padding: 11px 12px; font-size: 9px; letter-spacing: 0.8px; }
        .search-table td { padding: 12px; font-size: 11px; }
        .type-icon { width: 30px; height: 30px; font-size: 11px; border-radius: 7px; }
        .type-icon svg { width: 13px; height: 13px; }
        .item-title { font-size: 11px; max-width: 200px; }
        .item-subtitle { font-size: 9px; max-width: 200px; margin-top: 2px; }
        .type-badge { font-size: 8px; padding: 3px 7px; border-radius: 5px; letter-spacing: 0.7px; }
        .item-details { font-size: 9px; }
        .item-date { font-size: 10px; }
        .item-status { font-size: 9px; gap: 4px; margin-top: 3px; }
        .item-status::before { width: 4px; height: 4px; }
        .action-btn { width: 28px; height: 28px; border-radius: 7px; }
        .action-btn svg { width: 11px; height: 11px; }
        .empty-state { padding: 45px 16px; }
        .empty-icon { width: 54px; height: 54px; margin-bottom: 13px; }
        .empty-icon svg { width: 20px; height: 20px; }
        .empty-title { font-size: 12px; }
        .empty-sub { font-size: 10px; }
    }

    /* Телефоны (до 640px) */
    @media (max-width: 640px) {
        .search-page-custom .page-head-custom { margin-bottom: 16px; gap: 9px; }
        .search-page-custom h1 { font-size: 18px; gap: 8px; }
        .search-page-custom h1 .accent-bar { width: 3px; height: 18px; }
        .results-counter { padding: 6px 11px; gap: 6px; border-radius: 8px; font-size: 10px; }
        .results-counter .count-num { font-size: 11px; }
        .results-counter .count-label { font-size: 8px; letter-spacing: 0.7px; }
        .search-table-wrap { border-radius: 10px; padding: 3px; }
        .search-table { min-width: 550px; }
        .search-table th { padding: 10px 11px; font-size: 9px; letter-spacing: 0.7px; }
        .search-table td { padding: 11px; font-size: 11px; }
        .type-icon { width: 28px; height: 28px; font-size: 10px; border-radius: 7px; }
        .type-icon svg { width: 12px; height: 12px; }
        .item-title { font-size: 11px; max-width: 170px; }
        .item-subtitle { font-size: 9px; max-width: 170px; margin-top: 2px; }
        .type-badge { font-size: 7px; padding: 2px 6px; border-radius: 4px; letter-spacing: 0.6px; }
        .item-details { font-size: 9px; }
        .item-date { font-size: 10px; }
        .item-status { font-size: 8px; gap: 3px; margin-top: 2px; }
        .item-status::before { width: 4px; height: 4px; }
        .action-btn { width: 26px; height: 26px; border-radius: 6px; }
        .action-btn svg { width: 10px; height: 10px; }
        .empty-state { padding: 40px 14px; }
        .empty-icon { width: 50px; height: 50px; margin-bottom: 12px; }
        .empty-icon svg { width: 19px; height: 19px; }
        .empty-title { font-size: 11px; }
        .empty-sub { font-size: 10px; }
    }

    /* Маленькие телефоны (до 480px) */
    @media (max-width: 480px) {
        .search-page-custom .page-head-custom { margin-bottom: 14px; gap: 8px; }
        .search-page-custom h1 { font-size: 17px; gap: 7px; }
        .search-page-custom h1 .accent-bar { width: 3px; height: 17px; }
        .results-counter { padding: 5px 10px; gap: 6px; border-radius: 7px; font-size: 10px; }
        .results-counter .count-num { font-size: 11px; }
        .results-counter .count-label { font-size: 8px; letter-spacing: 0.6px; }
        .search-table-wrap { border-radius: 9px; padding: 2px; }
        .search-table { min-width: 500px; }
        .search-table th { padding: 9px 10px; font-size: 8px; letter-spacing: 0.6px; }
        .search-table td { padding: 10px; font-size: 10px; }
        .type-icon { width: 26px; height: 26px; font-size: 10px; border-radius: 6px; }
        .type-icon svg { width: 11px; height: 11px; }
        .item-title { font-size: 10px; max-width: 150px; }
        .item-subtitle { font-size: 8px; max-width: 150px; margin-top: 2px; }
        .type-badge { font-size: 7px; padding: 2px 5px; border-radius: 4px; }
        .item-details { font-size: 8px; }
        .item-date { font-size: 9px; }
        .item-status { font-size: 8px; gap: 3px; }
        .action-btn { width: 24px; height: 24px; border-radius: 6px; }
        .action-btn svg { width: 10px; height: 10px; }
        .empty-state { padding: 35px 12px; }
        .empty-icon { width: 46px; height: 46px; margin-bottom: 11px; }
        .empty-icon svg { width: 18px; height: 18px; }
        .empty-title { font-size: 11px; }
        .empty-sub { font-size: 9px; }
    }

    /* Очень маленькие телефоны (до 380px) */
    @media (max-width: 380px) {
        .search-page-custom .page-head-custom { margin-bottom: 12px; gap: 7px; }
        .search-page-custom h1 { font-size: 16px; gap: 6px; }
        .search-page-custom h1 .accent-bar { width: 2px; height: 16px; }
        .results-counter { padding: 5px 9px; gap: 5px; font-size: 9px; }
        .results-counter .count-num { font-size: 10px; }
        .results-counter .count-label { font-size: 7px; }
        .search-table-wrap { border-radius: 8px; padding: 2px; }
        .search-table { min-width: 450px; }
        .search-table th { padding: 8px 9px; font-size: 8px; }
        .search-table td { padding: 9px; font-size: 10px; }
        .type-icon { width: 24px; height: 24px; font-size: 9px; border-radius: 6px; }
        .type-icon svg { width: 10px; height: 10px; }
        .item-title { font-size: 10px; max-width: 130px; }
        .item-subtitle { font-size: 8px; max-width: 130px; }
        .type-badge { font-size: 7px; padding: 2px 5px; }
        .item-details { font-size: 8px; }
        .item-date { font-size: 9px; }
        .item-status { font-size: 7px; }
        .action-btn { width: 22px; height: 22px; }
        .action-btn svg { width: 9px; height: 9px; }
        .empty-state { padding: 30px 10px; }
        .empty-icon { width: 42px; height: 42px; margin-bottom: 10px; }
        .empty-icon svg { width: 17px; height: 17px; }
        .empty-title { font-size: 10px; }
        .empty-sub { font-size: 9px; }
    }
</style>

<div class="search-page-custom">
    {{-- Заголовок страницы --}}
    <div class="page-head-custom">
        <h1>
            <span class="accent-bar"></span>
            <span data-i18n="resultsFor">Результаты:</span>
            <span class="query-highlight">"{{ $query }}"</span>
        </h1>

        <div class="results-counter">
            <span class="count-num">{{ $results->count() }}</span>
            <span class="count-label" data-i18n="totalFound">Всего найдено</span>
        </div>
    </div>

    {{-- Таблица результатов --}}
    <div class="search-table-wrap">
        <div class="search-table-scroll">
            <table class="search-table">
                <thead>
                <tr>
                    <th><span data-i18n="thObject">Объект и описание</span></th>
                    <th class="center"><span data-i18n="thCategory">Категория</span></th>
                    <th class="center"><span data-i18n="thDetails">Детали</span></th>
                    <th class="center"><span data-i18n="thStatus">Статус / Дата</span></th>
                    <th class="right"><span data-i18n="thAction">Действие</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse($results as $item)
                @php
                $isUser = $item instanceof \App\Models\User;
                $isSig = $item instanceof \App\Models\DocumentSignature;

                if ($isUser) {
                $typeClass = 'user';
                $typeKey = 'typeUser';
                $title = $item->name ?? 'User #' . $item->id;

                // ✅ ДОБАВЛЕНО: Красивое отображение Email и Телефона через разделитель " • "
                $phoneDisplay = $item->phone ? ' • ' . $item->phone : '';
                $subtitle = trim(($item->email ?? '') . $phoneDisplay);
                if (empty($subtitle)) {
                $subtitle = 'Нет контактных данных';
                }

                $details = $item->role ?? 'User';
                $date = $item->created_at?->format('d.m.Y') ?? '—';
                $statusKey = 'statusActive';
                $statusClass = 'active';
                $route = route('users.show', $item->id);
                } elseif ($isSig) {
                $typeClass = 'signature';
                $typeKey = 'typeSig';
                $title = ($item->document->title ?? 'Signature') . ' #' . $item->id;
                $subtitle = $item->document->title ?? 'N/A';
                $details = 'ID: ' . $item->id;
                $date = $item->created_at?->format('d.m.Y') ?? '—';
                $statusKey = $item->signed_at ? 'statusSigned' : 'statusProcess';
                $statusClass = $item->signed_at ? 'signed' : 'processing';
                $route = route('signatures.show', $item->id);
                } else {
                $typeClass = 'document';
                $typeKey = 'typeDoc';
                $title = $item->title ?? 'Document #' . $item->id;
                $subtitle = \Illuminate\Support\Str::limit($item->content ?? '', 60);
                $details = $item->number ?? 'Стандартный';
                $date = $item->created_at?->format('d.m.Y') ?? '—';
                $statusKey = 'statusActive';
                $statusClass = 'active';
                $route = route('documents.show', $item->id);
                }
                @endphp
                <tr>
                    {{-- Объект --}}
                    <td>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div class="type-icon {{ $typeClass }}">
                                @if($isUser)
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($item->name ?? 'U', 0, 1)) }}
                                @elseif($isSig)
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                @else
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                @endif
                            </div>
                            <div style="min-width:0; flex:1;">
                                <span class="item-title">{{ $title }}</span>
                                <span class="item-subtitle">
                                    @if($isUser)
                                        {{ $subtitle }}
                                    @elseif($isSig)
                                        <span data-i18n="docPrefix">Документ:</span> {{ $subtitle }}
                                    @else
                                        {{ $subtitle ?: '—' }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </td>

                    {{-- Категория --}}
                    <td class="center">
                        <span class="type-badge {{ $typeClass }}" data-i18n="{{ $typeKey }}">
                            {{ $typeKey === 'typeUser' ? 'User' : ($typeKey === 'typeSig' ? 'Signature' : 'Document') }}
                        </span>
                    </td>

                    {{-- Детали --}}
                    <td class="center">
                        <span class="item-details">
                            @if($isUser)
                                {{ $details }}
                            @elseif($isSig)
                                ID: {{ $item->id }}
                            @else
                                <span data-i18n="typeStandard">Стандартный</span>
                            @endif
                        </span>
                    </td>

                    {{-- Статус / Дата --}}
                    <td class="center">
                        <div style="display:flex; flex-direction:column; align-items:center;">
                            <span class="item-date">{{ $date }}</span>
                            <span class="item-status {{ $statusClass }}" data-i18n="{{ $statusKey }}">
                                Активен
                            </span>
                        </div>
                    </td>

                    {{-- Действие --}}
                    <td class="right">
                        <a href="{{ $route }}" class="action-btn" data-i18n-title="viewAction" title="View">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                <path d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="empty-icon">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"/>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                </svg>
                            </div>
                            <div class="empty-title" data-i18n="noResults">По запросу ничего не найдено</div>
                            <div class="empty-sub" data-i18n="tryDifferentQuery">Попробуйте изменить запрос</div>
                        </div>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const SEARCH_TRANSLATIONS = {
            ru: {
                resultsFor: 'Результаты:',
                totalFound: 'Всего найдено',
                thObject: 'Объект и описание',
                thCategory: 'Категория',
                thDetails: 'Детали',
                thStatus: 'Статус / Дата',
                thAction: 'Действие',
                typeUser: 'Пользователь',
                typeSig: 'Подпись',
                typeDoc: 'Документ',
                typeStandard: 'Стандартный',
                statusSigned: 'Подписан',
                statusProcess: 'В процессе',
                statusActive: 'Активен',
                docPrefix: 'Документ:',
                noResults: 'По запросу ничего не найдено',
                tryDifferentQuery: 'Попробуйте изменить запрос',
                viewAction: 'Просмотр'
            },
            tj: {
                resultsFor: 'Натиҷаҳо:',
                totalFound: 'Ҳамагӣ ёфт шуд',
                thObject: 'Объект ва тавсиф',
                thCategory: 'Категория',
                thDetails: 'Тафсилот',
                thStatus: 'Статус / Сана',
                thAction: 'Амал',
                typeUser: 'Корбар',
                typeSig: 'Имзо',
                typeDoc: 'Ҳуҷҷат',
                typeStandard: 'Стандартӣ',
                statusSigned: 'Имзо шуд',
                statusProcess: 'Дар ҷараён',
                statusActive: 'Фаъол',
                docPrefix: 'Ҳуҷҷат:',
                noResults: 'Тибқи дархост чизе ёфт нашуд',
                tryDifferentQuery: 'Кӯшиш кунед дархостро иваз кунед',
                viewAction: 'Дидан'
            },
            en: {
                resultsFor: 'Results:',
                totalFound: 'Total found',
                thObject: 'Object & Description',
                thCategory: 'Category',
                thDetails: 'Details',
                thStatus: 'Status / Date',
                thAction: 'Action',
                typeUser: 'User',
                typeSig: 'Signature',
                typeDoc: 'Document',
                typeStandard: 'Standard',
                statusSigned: 'Signed',
                statusProcess: 'In Process',
                statusActive: 'Active',
                docPrefix: 'Document:',
                noResults: 'No results found',
                tryDifferentQuery: 'Try changing your query',
                viewAction: 'View'
            }
        };

        function applySearchTranslations(lang) {
            const dict = SEARCH_TRANSLATIONS[lang] || SEARCH_TRANSLATIONS.ru;

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

        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applySearchTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applySearchTranslations(lang);
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applySearchTranslations(e.newValue);
            }
        });
    });
</script>
@endsection