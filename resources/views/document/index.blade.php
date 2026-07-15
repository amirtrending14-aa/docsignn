@extends('layouts.admin')

@section('content')
<style>
    /* Fallback если layout не загрузился */
body {
    background: #06070b !important;
    color: #e7ecf3 !important;
}

.doc-page-custom {
    color: var(--text, #e7ecf3);
}
</style>
<style>
    /* === Кастомные стили для страницы документов в стиле админки === */
    .doc-page-custom { color: var(--text); }

    .doc-page-custom .page-head-custom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .doc-page-custom h1 {
        margin: 0;
        font-size: 26px;
        font-weight: 700;
        letter-spacing: -0.5px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .doc-page-custom h1 .accent-bar {
        width: 4px;
        height: 26px;
        background: rgba(var(--glow), 1);
        border-radius: 2px;
        box-shadow: 0 0 12px rgba(var(--glow), 0.8);
    }

    .doc-page-custom .btn-new {
        appearance: none;
        border: 0;
        background: linear-gradient(180deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.65));
        color: #0a0d14;
        font: 600 13px 'Inter', sans-serif;
        padding: 10px 18px;
        border-radius: 10px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        box-shadow: 0 8px 24px rgba(var(--glow), 0.35), inset 0 1px 0 rgba(255,255,255,0.3);
        transition: all .25s ease;
    }
    .doc-page-custom .btn-new:hover {
        filter: brightness(1.08);
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(var(--glow), 0.5), inset 0 1px 0 rgba(255,255,255,0.4);
    }
    .doc-page-custom .btn-new i { color: #0a0d14; font-size: 14px; }

    .doc-table-wrap {
        background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 4px;
        overflow: hidden;
        position: relative;
    }
    .doc-table-wrap::before {
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

    .doc-table { width: 100%; border-collapse: collapse; }

    .doc-table th {
        text-align: left;
        padding: 14px 16px;
        font-size: 11px;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 1px solid var(--line);
        background: rgba(255,255,255,0.02);
    }
    .doc-table th.center { text-align: center; }
    .doc-table th.right { text-align: right; }

    .doc-table td {
        padding: 16px;
        font-size: 13px;
        color: var(--text);
        border-bottom: 1px solid var(--line);
        transition: all .2s ease;
    }
    .doc-table td.center { text-align: center; }
    .doc-table td.right { text-align: right; }

    .doc-table tbody tr { transition: all .25s ease; }
    .doc-table tbody tr:last-child td { border-bottom: 0; }
    .doc-table tbody tr:hover {
        background: linear-gradient(90deg, rgba(var(--glow), 0.06), transparent 60%);
    }
    .doc-table tbody tr:hover td:first-child {
        box-shadow: inset 3px 0 0 0 rgba(var(--glow), 1);
    }

    .doc-id {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11px;
        color: var(--muted);
    }

    .doc-title {
        font-weight: 600;
        font-size: 13.5px;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .doc-number {
        font-family: 'JetBrains Mono', monospace;
        font-size: 10px;
        color: rgba(var(--glow), 1);
        background: rgba(var(--glow), 0.1);
        padding: 2px 7px;
        border-radius: 5px;
        border: 1px solid rgba(var(--glow), 0.25);
        letter-spacing: 0.5px;
    }

    .doc-author { font-size: 11px; color: var(--muted); margin-top: 3px; }
    .doc-desc { font-size: 11.5px; color: var(--muted); margin-top: 4px; opacity: 0.75; }
    .doc-date {
        font-family: 'JetBrains Mono', monospace;
        font-size: 12px;
        color: var(--muted);
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        border: 1px solid;
    }
    .status-pill::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 8px currentColor;
    }

    .status-draft { color: #8892a6; background: rgba(136,146,166,0.08); border-color: rgba(136,146,166,0.2); }
    .status-active, .status-processing { color: #ffb547; background: rgba(255,181,71,0.08); border-color: rgba(255,181,71,0.25); }
    .status-approved, .status-completed { color: #4cd982; background: rgba(76,217,130,0.08); border-color: rgba(76,217,130,0.25); }
    .status-rejected { color: #ff7a7a; background: rgba(255,122,122,0.08); border-color: rgba(255,122,122,0.25); }

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
        transform: scale(1.05);
    }
    .action-btn i { font-size: 13px; }

    .empty-state { padding: 60px 20px; text-align: center; }
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
    .empty-title { font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 4px; }
    .empty-sub { font-size: 12px; color: var(--muted); margin-bottom: 20px; }
    .empty-btn {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--line);
        border-radius: 8px;
        color: var(--text);
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all .2s ease;
    }
    .empty-btn:hover {
        border-color: rgba(var(--glow), 0.4);
        background: rgba(var(--glow), 0.1);
        color: rgba(var(--glow), 1);
        box-shadow: 0 0 12px rgba(var(--glow), 0.2);
    }

    .pagination-wrap { margin-top: 24px; display: flex; justify-content: center; }
    .pagination-wrap nav {
        display: flex;
        gap: 4px;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--line);
        padding: 4px;
        border-radius: 10px;
    }
    .pagination-wrap .pagination {
        display: flex;
        gap: 4px;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .pagination-wrap .pagination li { display: inline-flex; }
    .pagination-wrap .pagination li a,
    .pagination-wrap .pagination li span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        color: var(--muted);
        text-decoration: none;
        transition: all .2s ease;
        border: none;
        background: transparent;
    }
    .pagination-wrap .pagination li a:hover {
        color: var(--text);
        background: rgba(255,255,255,0.05);
    }
    .pagination-wrap .pagination li.active span,
    .pagination-wrap .pagination li span[aria-current="page"] {
        color: #0a0d14;
        background: linear-gradient(180deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.65));
        box-shadow: 0 4px 12px rgba(var(--glow), 0.3);
    }
    .pagination-wrap .pagination li.disabled span {
        opacity: 0.4;
        pointer-events: none;
    }

    /* === МОБИЛЬНЫЕ КАРТОЧКИ (скрыты по умолчанию) === */
    .doc-cards-mobile {
        display: none;
        flex-direction: column;
        gap: 12px;
    }

    .doc-card-item {
        background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 16px;
        position: relative;
        overflow: hidden;
        transition: all 0.25s ease;
    }
    .doc-card-item::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 3px;
        height: 100%;
        background: rgba(var(--glow), 0.6);
        border-radius: 3px 0 0 3px;
    }
    .doc-card-item:hover {
        border-color: rgba(var(--glow), 0.3);
        box-shadow: 0 8px 24px rgba(0,0,0,0.3), 0 0 16px rgba(var(--glow), 0.1);
        transform: translateY(-2px);
    }

    .doc-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }
    .doc-card-title {
        font-weight: 700;
        font-size: 14px;
        color: var(--text);
        line-height: 1.3;
        flex: 1;
    }
    .doc-card-number {
        font-family: 'JetBrains Mono', monospace;
        font-size: 10px;
        color: rgba(var(--glow), 1);
        background: rgba(var(--glow), 0.1);
        padding: 2px 7px;
        border-radius: 5px;
        border: 1px solid rgba(var(--glow), 0.25);
        letter-spacing: 0.5px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .doc-card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
        font-size: 11px;
        color: var(--muted);
    }
    .doc-card-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .doc-card-meta-item i {
        font-size: 11px;
        opacity: 0.7;
    }

    .doc-card-desc {
        font-size: 11.5px;
        color: var(--muted);
        opacity: 0.75;
        margin-bottom: 12px;
        line-height: 1.4;
    }

    .doc-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding-top: 10px;
        border-top: 1px solid var(--line);
    }

    /* ===== RESPONSIVE ===== */

    /* Маленькие ноутбуки и большие планшеты (до 992px) */
    @media (max-width: 992px) {
        .doc-page-custom .page-head-custom { margin-bottom: 20px; gap: 12px; }
        .doc-page-custom h1 { font-size: 22px; gap: 10px; }
        .doc-page-custom h1 .accent-bar { width: 4px; height: 22px; }
        .doc-page-custom .btn-new { padding: 9px 16px; font-size: 12px; border-radius: 9px; }

        .doc-table th, .doc-table td { padding: 12px 14px; }
        .doc-table th { font-size: 10px; letter-spacing: 0.8px; }
        .doc-table td { font-size: 12px; }

        /* Скрываем колонку ID */
        .doc-table th:first-child,
        .doc-table td:first-child {
            display: none;
        }

        .doc-title { font-size: 12.5px; }
        .doc-number { font-size: 9px; padding: 2px 6px; }
        .doc-author { font-size: 10px; }
        .doc-desc { font-size: 10.5px; }
        .doc-date { font-size: 11px; }
        .status-pill { padding: 3px 10px; font-size: 10px; }
        .action-btn { width: 30px; height: 30px; }
        .action-btn svg { width: 14px; height: 14px; }
    }

    /* Планшеты (до 768px) — переход на карточки */
    @media (max-width: 768px) {
        .doc-page-custom .page-head-custom { margin-bottom: 18px; gap: 10px; }
        .doc-page-custom h1 { font-size: 20px; gap: 9px; }
        .doc-page-custom h1 .accent-bar { width: 3px; height: 20px; }
        .doc-page-custom .btn-new { padding: 8px 14px; font-size: 11px; border-radius: 8px; gap: 6px; }
        .doc-page-custom .btn-new i { font-size: 12px; }

        /* Скрываем таблицу, показываем карточки */
        .doc-table-wrap { display: none; }
        .doc-cards-mobile { display: flex; }

        .doc-table-wrap {
            padding: 3px;
            border-radius: 12px;
        }

        .doc-card-item { padding: 14px; border-radius: 10px; }
        .doc-card-title { font-size: 13px; }
        .doc-card-number { font-size: 9px; padding: 2px 6px; }
        .doc-card-meta { font-size: 10px; gap: 6px; }
        .doc-card-desc { font-size: 11px; margin-bottom: 10px; }
        .doc-card-footer { padding-top: 9px; gap: 8px; }

        .status-pill { padding: 3px 9px; font-size: 10px; gap: 5px; }
        .status-pill::before { width: 5px; height: 5px; }
        .action-btn { width: 30px; height: 30px; border-radius: 7px; }
        .action-btn svg { width: 14px; height: 14px; }

        .empty-state { padding: 40px 16px; }
        .empty-icon { width: 56px; height: 56px; font-size: 20px; margin-bottom: 14px; }
        .empty-title { font-size: 13px; }
        .empty-sub { font-size: 11px; margin-bottom: 16px; }
        .empty-btn { padding: 7px 14px; font-size: 11px; border-radius: 7px; }

        .pagination-wrap { margin-top: 20px; }
        .pagination-wrap nav { padding: 3px; border-radius: 9px; gap: 3px; }
        .pagination-wrap .pagination { gap: 3px; }
        .pagination-wrap .pagination li a,
        .pagination-wrap .pagination li span {
            min-width: 30px;
            height: 30px;
            padding: 0 8px;
            font-size: 11px;
            border-radius: 5px;
        }
    }

    /* Большие телефоны (до 576px) */
    @media (max-width: 576px) {
        .doc-page-custom .page-head-custom { margin-bottom: 16px; gap: 8px; }
        .doc-page-custom h1 { font-size: 18px; gap: 8px; }
        .doc-page-custom h1 .accent-bar { width: 3px; height: 18px; }
        .doc-page-custom .btn-new { padding: 7px 12px; font-size: 11px; border-radius: 7px; gap: 5px; }
        .doc-page-custom .btn-new i { font-size: 11px; }

        .doc-card-item { padding: 12px; border-radius: 9px; gap: 8px; }
        .doc-card-header { gap: 8px; margin-bottom: 8px; }
        .doc-card-title { font-size: 12px; }
        .doc-card-number { font-size: 9px; padding: 2px 5px; border-radius: 4px; }
        .doc-card-meta { font-size: 10px; gap: 5px; margin-bottom: 8px; }
        .doc-card-desc { font-size: 10px; margin-bottom: 9px; line-height: 1.35; }
        .doc-card-footer { padding-top: 8px; gap: 7px; }

        .status-pill { padding: 3px 8px; font-size: 9px; gap: 4px; border-radius: 16px; }
        .status-pill::before { width: 5px; height: 5px; }
        .action-btn { width: 28px; height: 28px; border-radius: 6px; }
        .action-btn svg { width: 13px; height: 13px; }

        .empty-state { padding: 32px 14px; }
        .empty-icon { width: 48px; height: 48px; font-size: 18px; margin-bottom: 12px; border-radius: 12px; }
        .empty-title { font-size: 12px; }
        .empty-sub { font-size: 10px; margin-bottom: 14px; }
        .empty-btn { padding: 6px 12px; font-size: 10px; border-radius: 6px; }

        .pagination-wrap { margin-top: 16px; }
        .pagination-wrap nav { padding: 3px; border-radius: 8px; gap: 2px; }
        .pagination-wrap .pagination { gap: 2px; }
        .pagination-wrap .pagination li a,
        .pagination-wrap .pagination li span {
            min-width: 28px;
            height: 28px;
            padding: 0 7px;
            font-size: 10px;
            border-radius: 5px;
        }
    }

    /* Телефоны (до 480px) */
    @media (max-width: 480px) {
        .doc-page-custom .page-head-custom { margin-bottom: 14px; gap: 7px; }
        .doc-page-custom h1 { font-size: 16px; gap: 7px; }
        .doc-page-custom h1 .accent-bar { width: 3px; height: 16px; }
        .doc-page-custom .btn-new { padding: 6px 11px; font-size: 10px; border-radius: 7px; gap: 4px; }
        .doc-page-custom .btn-new i { font-size: 10px; }

        .doc-card-item { padding: 11px; border-radius: 8px; }
        .doc-card-header { gap: 7px; margin-bottom: 7px; }
        .doc-card-title { font-size: 12px; }
        .doc-card-number { font-size: 8px; padding: 2px 5px; }
        .doc-card-meta { font-size: 9px; gap: 4px; margin-bottom: 7px; }
        .doc-card-meta-item i { font-size: 10px; }
        .doc-card-desc { font-size: 10px; margin-bottom: 8px; }
        .doc-card-footer { padding-top: 7px; gap: 6px; }

        .status-pill { padding: 2px 7px; font-size: 9px; gap: 4px; border-radius: 14px; }
        .status-pill::before { width: 4px; height: 4px; }
        .action-btn { width: 27px; height: 27px; border-radius: 6px; }
        .action-btn svg { width: 12px; height: 12px; }

        .empty-state { padding: 28px 12px; }
        .empty-icon { width: 44px; height: 44px; font-size: 16px; margin-bottom: 10px; }
        .empty-title { font-size: 11px; }
        .empty-sub { font-size: 10px; margin-bottom: 12px; }
        .empty-btn { padding: 6px 11px; font-size: 10px; }

        .pagination-wrap { margin-top: 14px; }
        .pagination-wrap nav { padding: 2px; border-radius: 7px; }
        .pagination-wrap .pagination li a,
        .pagination-wrap .pagination li span {
            min-width: 26px;
            height: 26px;
            padding: 0 6px;
            font-size: 10px;
            border-radius: 4px;
        }
    }

    /* Очень маленькие телефоны (до 380px) */
    @media (max-width: 380px) {
        .doc-page-custom .page-head-custom { margin-bottom: 12px; gap: 6px; }
        .doc-page-custom h1 { font-size: 15px; gap: 6px; }
        .doc-page-custom h1 .accent-bar { width: 2px; height: 15px; }
        .doc-page-custom .btn-new { padding: 6px 10px; font-size: 10px; border-radius: 6px; gap: 4px; }

        .doc-card-item { padding: 10px; border-radius: 7px; }
        .doc-card-header { gap: 6px; margin-bottom: 6px; }
        .doc-card-title { font-size: 11px; }
        .doc-card-number { font-size: 8px; padding: 1px 4px; }
        .doc-card-meta { font-size: 9px; gap: 4px; margin-bottom: 6px; }
        .doc-card-desc { font-size: 9px; margin-bottom: 7px; }
        .doc-card-footer { padding-top: 6px; gap: 5px; }

        .status-pill { padding: 2px 6px; font-size: 8px; gap: 3px; border-radius: 12px; }
        .status-pill::before { width: 4px; height: 4px; }
        .action-btn { width: 26px; height: 26px; border-radius: 5px; }
        .action-btn svg { width: 11px; height: 11px; }

        .empty-state { padding: 24px 10px; }
        .empty-icon { width: 40px; height: 40px; font-size: 15px; margin-bottom: 9px; }
        .empty-title { font-size: 11px; }
        .empty-sub { font-size: 9px; margin-bottom: 10px; }
        .empty-btn { padding: 5px 10px; font-size: 9px; }

        .pagination-wrap { margin-top: 12px; }
        .pagination-wrap nav { padding: 2px; border-radius: 6px; }
        .pagination-wrap .pagination li a,
        .pagination-wrap .pagination li span {
            min-width: 24px;
            height: 24px;
            padding: 0 5px;
            font-size: 9px;
            border-radius: 4px;
        }
    }
</style>

<div class="doc-page-custom">
    <!-- Заголовок страницы -->
    <div class="page-head-custom">
        <h1>
            <span class="accent-bar"></span>
            <span data-i18n="documents">DOCUMENTS</span>
        </h1>
        <a href="{{ route('documents.create') }}" class="btn-new">
            <i class="bi bi-plus-lg"></i>
            <span data-i18n="newDocument">New Document</span>
        </a>
    </div>

    <!-- Таблица документов (для десктопов и планшетов) -->
    <div class="doc-table-wrap">
        <table class="doc-table">
            <thead>
            <tr>
                <th><span data-i18n="doc_id">ID</span></th>
                <th><span data-i18n="docInfo">Document</span></th>
                <th class="center"><span data-i18n="doc_deadline">Date</span></th>
                <th class="center"><span data-i18n="status">Status</span></th>
                <th class="right"><span data-i18n="doc_actions">Action</span></th>
            </tr>
            </thead>
            <tbody>
            @forelse($documents as $index => $doc)
            @php
            $status = strtolower($doc->status);
            $statusClass = match($status) {
            'draft' => 'status-draft',
            'active', 'processing' => 'status-processing',
            'approved', 'completed' => 'status-completed',
            'rejected' => 'status-rejected',
            default => 'status-draft',
            };
            $statusKey = match($status) {
            'draft' => 'status_draft',
            'active', 'processing' => 'status_processing',
            'approved', 'completed' => 'status_completed',
            'rejected' => 'status_rejected',
            default => 'status_draft',
            };
            @endphp
            <tr>
                <td>
                    <span class="doc-id">#{{ ($documents->currentPage() - 1) * $documents->perPage() + $index + 1 }}</span>
                </td>
                <td>
                    <div class="doc-title">
                        {{ Str::limit($doc->title, 45) }}
                        <span class="doc-number">{{ $doc->number ?? $doc->id }}</span>
                    </div>
                    @if(auth()->user()->is_admin)
                    <div class="doc-author">
                        <span data-i18n="doc_by">By</span>: {{ $doc->createdBy->name ?? 'System' }}
                    </div>
                    @endif
                    <div class="doc-desc">
                        {{ Str::limit($doc->content, 40) ?: __('No description') }}
                    </div>
                </td>
                <td class="center">
                    <span class="doc-date">
                        {{ $doc->deadline ? $doc->deadline->format('d.m.Y') : '—' }}
                    </span>
                </td>
                <td class="center">
                    <span class="status-pill {{ $statusClass }}" data-status-key="{{ $statusKey }}">
                        {{ $doc->status_label ?? ucfirst($status) }}
                    </span>
                </td>
                <td class="right">
                    <a href="{{ route('documents.show', $doc->id) }}" class="action-btn" data-i18n-title="doc_view" title="View">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.123.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                        </svg>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="bi bi-search"></i>
                        </div>
                        <div class="empty-title" data-i18n="docNotFound">Документ не найден</div>
                        <div class="empty-sub" data-i18n="tryDifferentSearch">Попробуйте изменить запрос</div>
                        <a href="{{ route('documents.index') }}" class="empty-btn" data-i18n="resetSearch">
                            Сбросить поиск
                        </a>
                    </div>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Мобильные карточки (для телефонов) -->
    <div class="doc-cards-mobile">
        @forelse($documents as $index => $doc)
        @php
        $status = strtolower($doc->status);
        $statusClass = match($status) {
        'draft' => 'status-draft',
        'active', 'processing' => 'status-processing',
        'approved', 'completed' => 'status-completed',
        'rejected' => 'status-rejected',
        default => 'status-draft',
        };
        $statusKey = match($status) {
        'draft' => 'status_draft',
        'active', 'processing' => 'status_processing',
        'approved', 'completed' => 'status_completed',
        'rejected' => 'status_rejected',
        default => 'status_draft',
        };
        @endphp
        <div class="doc-card-item">
            <div class="doc-card-header">
                <div class="doc-card-title">{{ Str::limit($doc->title, 50) }}</div>
                <span class="doc-card-number">{{ $doc->number ?? $doc->id }}</span>
            </div>
            <div class="doc-card-meta">
                <span class="doc-card-meta-item">
                    <i class="bi bi-hash"></i>
                    #{{ ($documents->currentPage() - 1) * $documents->perPage() + $index + 1 }}
                </span>
                @if(auth()->user()->is_admin)
                <span class="doc-card-meta-item">
                    <i class="bi bi-person"></i>
                    {{ $doc->createdBy->name ?? 'System' }}
                </span>
                @endif
                <span class="doc-card-meta-item">
                    <i class="bi bi-calendar3"></i>
                    {{ $doc->deadline ? $doc->deadline->format('d.m.Y') : '—' }}
                </span>
            </div>
            <div class="doc-card-desc">
                {{ Str::limit($doc->content, 60) ?: __('No description') }}
            </div>
            <div class="doc-card-footer">
                <span class="status-pill {{ $statusClass }}" data-status-key="{{ $statusKey }}">
                    {{ $doc->status_label ?? ucfirst($status) }}
                </span>
                <a href="{{ route('documents.show', $doc->id) }}" class="action-btn" data-i18n-title="doc_view" title="View">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.123.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                    </svg>
                </a>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div class="empty-icon">
                <i class="bi bi-search"></i>
            </div>
            <div class="empty-title" data-i18n="docNotFound">Документ не найден</div>
            <div class="empty-sub" data-i18n="tryDifferentSearch">Попробуйте изменить запрос</div>
            <a href="{{ route('documents.index') }}" class="empty-btn" data-i18n="resetSearch">
                Сбросить поиск
            </a>
        </div>
        @endforelse
    </div>

    <!-- Пагинация -->
    @if($documents->hasPages())
    <div class="pagination-wrap">
        {{ $documents->links() }}
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ============================================================
        // ЛОКАЛЬНЫЙ СЛОВАРЬ СТРАНИЦЫ ДОКУМЕНТОВ
        // (дополняет глобальный TRANSLATIONS из layouts/admin.blade.php)
        // ============================================================
        const DOC_TRANSLATIONS = {
            ru: {
                documents: 'ДОКУМЕНТЫ',
                newDocument: 'Новый документ',
                doc_id: 'ID',
                docInfo: 'Инфо о документе',
                doc_deadline: 'Срок',
                status: 'Статус',
                doc_actions: 'Действия',
                doc_by: 'От',
                doc_view: 'Просмотр',
                docNotFound: 'Документ не найден',
                tryDifferentSearch: 'Попробуйте изменить запрос',
                resetSearch: 'Сбросить поиск',
                no_description: 'Нет описания',
                status_draft: 'Черновик',
                status_processing: 'В процессе',
                status_completed: 'Завершено',
                status_rejected: 'Отклонён'
            },
            tj: {
                documents: 'ҲУҶҶАТҲО',
                newDocument: 'Ҳуҷҷати нав',
                doc_id: 'ID',
                docInfo: 'Маълумоти ҳуҷҷат',
                doc_deadline: 'Мӯҳлат',
                status: 'Статус',
                doc_actions: 'Амалҳо',
                doc_by: 'Аз ҷониби',
                doc_view: 'Дидан',
                docNotFound: 'Ҳуҷҷат ёфт нашуд',
                tryDifferentSearch: 'Кӯшиш кунед дархостро иваз кунед',
                resetSearch: 'Тоза кардани ҷустуҷӯ',
                no_description: 'Тавсиф нест',
                status_draft: 'Лоиҳа',
                status_processing: 'Дар раванд',
                status_completed: 'Анҷом ёфт',
                status_rejected: 'Рад шуд'
            },
            en: {
                documents: 'DOCUMENTS',
                newDocument: 'New Document',
                doc_id: 'ID',
                docInfo: 'Document Info',
                doc_deadline: 'Date',
                status: 'Status',
                doc_actions: 'Actions',
                doc_by: 'By',
                doc_view: 'View',
                docNotFound: 'Document not found',
                tryDifferentSearch: 'Try changing your search query',
                resetSearch: 'Reset search',
                no_description: 'No description',
                status_draft: 'Draft',
                status_processing: 'Processing',
                status_completed: 'Completed',
                status_rejected: 'Rejected'
            }
        };

        // ============================================================
        // ФУНКЦИЯ ПРИМЕНЕНИЯ ПЕРЕВОДОВ НА ЭТОЙ СТРАНИЦЕ
        // ============================================================
        function applyDocTranslations(lang) {
            const dict = DOC_TRANSLATIONS[lang] || DOC_TRANSLATIONS.ru;

            // 1) Переводим все элементы с data-i18n
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (dict[key] !== undefined) el.textContent = dict[key];
            });

            // 2) Переводим title (подсказки)
            document.querySelectorAll('[data-i18n-title]').forEach(el => {
                const key = el.getAttribute('data-i18n-title');
                if (dict[key] !== undefined) el.setAttribute('title', dict[key]);
            });

            // 3) Переводим placeholder
            document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
                const key = el.getAttribute('data-i18n-placeholder');
                if (dict[key] !== undefined) el.setAttribute('placeholder', dict[key]);
            });

            // 4) Переводим СТАТУСЫ документов через data-status-key
            document.querySelectorAll('[data-status-key]').forEach(el => {
                const key = el.getAttribute('data-status-key');
                if (dict[key]) el.textContent = dict[key];
            });
        }

        // ============================================================
        // 1. Применяем сразу при загрузке
        // ============================================================
        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applyDocTranslations(initialLang);

        // ============================================================
        // 2. Слушаем событие смены языка от layouts/admin.blade.php
        //    (когда юзер кликает на 🇷🇺/🇹🇯/🇬🇧 в админке)
        // ============================================================
        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applyDocTranslations(lang);
        });

        // ============================================================
        // 3. Синхронизация между вкладками браузера
        // ============================================================
        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applyDocTranslations(e.newValue);
            }
        });
    });
</script>
@endsection