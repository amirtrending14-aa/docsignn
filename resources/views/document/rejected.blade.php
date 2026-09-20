@extends('layouts.admin')

@section('content')
<style>
    /* === СТРАНИЦА СПИСКА ОТКЛОНЁННЫХ ДОКУМЕНТОВ === */
    .rejected-docs-page {
        color: #e7ecf3;
        padding: 24px 20px;
        min-height: calc(100vh - 64px);
    }

    /* Заголовок страницы */
    .rejected-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
        padding-bottom: 16px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .rejected-page-header h1 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -0.3px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .rejected-page-header h1 .accent-bar {
        width: 4px;
        height: 24px;
        background: linear-gradient(180deg, #ef4444, #f87171);
        border-radius: 2px;
        box-shadow: 0 0 10px rgba(239, 68, 68, 0.6);
    }

    /* СЕТКА КАРТОЧЕК - 3 в ряд */
    .rejected-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    /* Компактная карточка */
    .rejected-card {
        background: linear-gradient(180deg, rgba(22, 26, 38, 0.95), rgba(16, 19, 28, 0.95));
        border: 1px solid rgba(239, 68, 68, 0.25);
        border-radius: 12px;
        padding: 16px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }
    .rejected-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 3px;
        height: 100%;
        background: linear-gradient(180deg, #ef4444, #f87171);
        box-shadow: 0 0 8px rgba(239, 68, 68, 0.5);
    }
    .rejected-card:hover {
        border-color: rgba(239, 68, 68, 0.5);
        box-shadow: 0 8px 24px rgba(0,0,0,0.4), 0 0 20px rgba(239, 68, 68, 0.15);
        transform: translateY(-3px);
    }

    .rejected-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }
    .rejected-card-title {
        font-weight: 600;
        font-size: 13px;
        color: #fff;
        line-height: 1.4;
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    .rejected-card-number {
        font-family: 'JetBrains Mono', monospace;
        font-size: 10px;
        color: #f87171;
        background: rgba(239, 68, 68, 0.1);
        padding: 3px 8px;
        border-radius: 5px;
        border: 1px solid rgba(239, 68, 68, 0.3);
        white-space: nowrap;
        flex-shrink: 0;
    }

    .rejected-card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 12px;
        font-size: 10px;
        color: #8892a6;
    }
    .rejected-card-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .rejected-card-meta-item i {
        color: #ef4444;
        font-size: 11px;
    }

    .rejected-card-content {
        font-size: 11px;
        color: #c5cdd9;
        margin-bottom: 12px;
        line-height: 1.5;
        padding: 8px;
        background: rgba(255,255,255,0.02);
        border-radius: 6px;
        border-left: 2px solid rgba(239, 68, 68, 0.4);
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    /* Блок с причиной отклонения - ЖЁЛТЫЙ (как ты просил) */
    .rejection-reason-block {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(251, 191, 36, 0.05));
        border: 1px solid rgba(245, 158, 11, 0.3);
        border-radius: 8px;
        padding: 10px;
        margin-top: auto;
        margin-bottom: 12px;
    }
    .rejection-reason-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 9px;
        font-weight: 700;
        color: #fbbf24;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .rejection-reason-label i { 
        font-size: 12px;
        color: #f59e0b;
    }
    
    .rejection-reason-text {
        font-size: 11px;
        color: #fcd34d;
        line-height: 1.5;
        font-style: italic;
        padding: 6px 8px;
        background: rgba(0,0,0,0.3);
        border-radius: 5px;
        border-left: 2px solid #f59e0b;
        white-space: pre-line;
    }
    .rejection-reason-empty {
        font-size: 10px;
        color: #8892a6;
        font-style: italic;
    }

    .rejected-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding-top: 12px;
        border-top: 1px solid rgba(255,255,255,0.06);
    }

    /* 🚩 БЕЙДЖ "ОТКЛОНЁН" - КРАСНЫЙ */
    .rejected-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        background: rgba(239, 68, 68, 0.15);
        border: 1px solid rgba(239, 68, 68, 0.4);
        color: #f87171;
    }
    .rejected-badge::before {
        content: "";
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: #ef4444;
        box-shadow: 0 0 8px rgba(239, 68, 68, 1);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    .action-btn {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #8892a6;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .action-btn:hover {
        color: #f87171;
        border-color: rgba(239, 68, 68, 0.5);
        background: rgba(239, 68, 68, 0.1);
        box-shadow: 0 0 10px rgba(239, 68, 68, 0.3);
        transform: scale(1.05);
    }
    .action-btn i { font-size: 12px; }

    .empty-state { padding: 60px 20px; text-align: center; }
    .empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        color: #f87171;
        font-size: 24px;
    }
    .empty-title { font-size: 14px; font-weight: 600; color: #fff; margin-bottom: 6px; }
    .empty-sub { font-size: 11px; color: #8892a6; margin-bottom: 20px; }

    .pagination-wrap { margin-top: 24px; display: flex; justify-content: center; }
    .pagination-wrap nav {
        display: flex;
        gap: 4px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
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
        color: #8892a6;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
        background: transparent;
    }
    .pagination-wrap .pagination li a:hover {
        color: #fff;
        background: rgba(255,255,255,0.05);
    }
    .pagination-wrap .pagination li.active span,
    .pagination-wrap .pagination li span[aria-current="page"] {
        color: #0a0d14;
        background: linear-gradient(180deg, #f87171, #ef4444);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    /* === АДАПТИВНОСТЬ === */
    @media (max-width: 1024px) {
        .rejected-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .rejected-docs-page { padding: 18px 14px; }
        .rejected-page-header h1 { font-size: 20px; }
        .rejected-grid { grid-template-columns: 1fr; gap: 14px; }
        .rejected-card { padding: 14px; }
        .rejected-card-title { font-size: 12px; }
        .rejected-card-meta { font-size: 9px; gap: 8px; }
        .rejection-reason-text { font-size: 10px; }
        .rejected-card-footer { flex-direction: column; align-items: stretch; }
        .action-btn { width: 100%; height: 32px; }
    }
    @media (max-width: 480px) {
        .rejected-docs-page { padding: 14px 10px; }
        .rejected-page-header { margin-bottom: 20px; padding-bottom: 12px; }
        .rejected-page-header h1 { font-size: 18px; }
        .rejected-grid { gap: 12px; }
        .rejected-card { padding: 12px; border-radius: 10px; }
        .rejected-card-title { font-size: 11px; }
        .rejected-card-number { font-size: 9px; padding: 2px 6px; }
        .rejected-card-meta { font-size: 9px; gap: 6px; }
        .rejected-card-content { font-size: 10px; padding: 6px; }
        .rejection-reason-block { padding: 8px; }
        .rejection-reason-label { font-size: 8px; }
        .rejection-reason-text { font-size: 10px; padding: 5px 6px; }
        .rejected-badge { padding: 3px 8px; font-size: 8px; }
        .action-btn { height: 30px; }
    }
</style>

<style>
    html[data-theme="light"] .rejected-docs-page {
        color: #0f172a !important;
    }
    html[data-theme="light"] .rejected-page-header {
        border-bottom: 2px solid #cbd5e1;
    }
    html[data-theme="light"] .rejected-page-header h1,
    html[data-theme="light"] .rejected-card-title,
    html[data-theme="light"] .empty-title {
        color: #0f172a !important;
    }
    html[data-theme="light"] .rejected-card {
        background: #ffffff;
        border: 1.5px solid #f87171;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
    }
    html[data-theme="light"] .rejected-card:hover {
        border-color: #ef4444;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14);
    }
    html[data-theme="light"] .rejected-card-meta,
    html[data-theme="light"] .rejection-reason-empty,
    html[data-theme="light"] .empty-sub {
        color: #475569;
    }
    html[data-theme="light"] .rejected-card-content {
        color: #334155;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-left: 3px solid #ef4444;
    }
    html[data-theme="light"] .rejection-reason-block {
        background: #fffbeb;
        border: 1.5px solid #f59e0b;
    }
    html[data-theme="light"] .rejection-reason-label {
        color: #b45309;
    }
    html[data-theme="light"] .rejection-reason-text {
        color: #92400e;
        background: #fef3c7;
        border-left: 3px solid #f59e0b;
    }
    html[data-theme="light"] .rejected-card-footer {
        border-top: 1px solid #cbd5e1;
    }
    html[data-theme="light"] .action-btn {
        color: #475569;
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    html[data-theme="light"] .pagination-wrap nav {
        background: #ffffff;
        border-color: #cbd5e1;
    }
    html[data-theme="light"] .pagination-wrap .pagination li a,
    html[data-theme="light"] .pagination-wrap .pagination li span {
        color: #475569;
    }
</style>

<div class="rejected-docs-page">
    <div class="max-w-7xl mx-auto">
        
        {{-- Заголовок страницы --}}
        <div class="rejected-page-header">
            <h1>
                <span class="accent-bar"></span>
                <span data-i18n="rejected_docs">Отклонённые документы</span>
            </h1>
        </div>

        {{-- СЕТКА карточек --}}
        <div class="rejected-grid">
            @forelse($documents as $index => $doc)
            @php
                $rejectionReason = $doc->rejection_reason ?? null;
                $rejectedBy = null;
                $rejectedAt = null;
                
                if (!$rejectionReason && $doc->logs) {
                    $rejectionLog = $doc->logs->where('action', 'rejected')->sortByDesc('created_at')->first();
                    if ($rejectionLog) {
                        $rejectionReason = $rejectionLog->comment ?? $rejectionLog->notes ?? $rejectionLog->reason ?? null;
                        $rejectedBy = $rejectionLog->user ?? $rejectionLog->creator ?? null;
                        $rejectedAt = $rejectionLog->created_at ?? null;
                    }
                }
                
                if (!$rejectionReason && $doc->signatures) {
                    $rejectedSig = $doc->signatures->where('status', 'rejected')->sortByDesc('updated_at')->first();
                    if ($rejectedSig) {
                        $rejectionReason = $rejectedSig->comment ?? $rejectedSig->rejection_reason ?? null;
                        $rejectedBy = $rejectedSig->user ?? null;
                        $rejectedAt = $rejectedSig->updated_at ?? null;
                    }
                }
                
                if (!$rejectionReason && $doc->comments && $doc->comments->count() > 0) {
                    $firstComment = $doc->comments->sortBy('created_at')->first();
                    if ($firstComment) {
                        $commentText = trim($firstComment->comment);
                        $cleanReason = preg_replace('/^(отказ|reject|отклонён|отклонено|отказано)[:\s]*/i', '', $commentText);
                        if (!empty(trim($cleanReason))) {
                            $rejectionReason = trim($cleanReason);
                        } else {
                            $rejectionReason = $commentText;
                        }
                        $rejectedBy = $firstComment->user ?? null;
                        $rejectedAt = $firstComment->created_at ?? null;
                    }
                }
            @endphp

            <div class="rejected-card">
                <div class="rejected-card-header">
                    <div class="rejected-card-title" title="{{ $doc->title }}">
                        {{ $doc->title }}
                    </div>
                    <span class="rejected-card-number">
                        №{{ $doc->number ?? $doc->id }}
                    </span>
                </div>

                <div class="rejected-card-meta">
                    <span class="rejected-card-meta-item">
                        <i class="bi bi-hash"></i>
                        #{{ ($documents->currentPage() - 1) * $documents->perPage() + $index + 1 }}
                    </span>
                    <span class="rejected-card-meta-item">
                        <i class="bi bi-person"></i>
                        {{ Str::limit($doc->createdBy->name ?? 'System', 15) }}
                    </span>
                    @if($doc->receiver)
                    <span class="rejected-card-meta-item">
                        <i class="bi bi-person-check"></i>
                        {{ Str::limit($doc->receiver->name, 15) }}
                    </span>
                    @endif
                    @if($rejectedAt)
                    <span class="rejected-card-meta-item">
                        <i class="bi bi-clock"></i>
                        {{ $rejectedAt->format('d.m.Y') }}
                    </span>
                    @endif
                </div>

                @if($doc->content)
                <div class="rejected-card-content" title="{{ $doc->content }}">
                    {{ Str::limit($doc->content, 80) }}
                </div>
                @endif

                {{-- Блок с причиной отклонения - ЖЁЛТЫЙ --}}
                <div class="rejection-reason-block">
                    <div class="rejection-reason-label">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span data-i18n="rejection_reason">Причина</span>
                    </div>
                    
                    @if($rejectionReason)
                        <div class="rejection-reason-text" title="{{ $rejectionReason }}">
                            {{ Str::limit($rejectionReason, 100) }}
                        </div>
                        @if($rejectedBy)
                            <div style="margin-top: 6px; font-size: 9px; color: #8892a6; display:flex; align-items:center; gap:4px;">
                                <i class="bi bi-person-fill" style="color:#f59e0b; font-size: 9px;"></i>
                                <span data-i18n="rejected_by">Отклонил:</span> 
                                <strong style="color:#e7ecf3;">{{ Str::limit($rejectedBy->name ?? 'Unknown', 15) }}</strong>
                            </div>
                        @endif
                    @else
                        <div class="rejection-reason-empty" data-i18n="no_rejection_reason">
                            Причина не указана
                        </div>
                    @endif
                </div>

                <div class="rejected-card-footer">
                    <!-- 🚩 КРАСНЫЙ БЕЙДЖ -->
                    <span class="rejected-badge" data-i18n="status_rejected">
                        Отклонён
                    </span>
                    <div style="display: flex; gap: 6px;">
                        <a href="{{ route('documents.show', $doc->id) }}" 
                           class="action-btn" 
                           data-i18n-title="doc_view" 
                           title="Просмотр">
                            <i class="bi bi-eye-fill"></i>
                        </a>
                        @if($doc->file_path)
                        <a href="{{ route('documents.pdf', $doc->id) }}" 
                           class="action-btn" 
                           data-i18n-title="download" 
                           title="Скачать">
                            <i class="bi bi-download"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
        </div>
        
        <div class="empty-state">
            <div class="empty-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="empty-title" data-i18n="no_rejected_docs">
                Нет отклонённых документов
            </div>
            <div class="empty-sub" data-i18n="all_docs_approved">
                Все документы одобрены или в работе
            </div>
            <a href="{{ route('documents.index') }}" class="action-btn" style="width:auto; padding: 0 16px; height: 32px; gap: 6px; font-size: 11px; font-weight: 600;">
                <i class="bi bi-arrow-left"></i>
                <span data-i18n="back_to_all">Назад</span>
            </a>
        </div>
        @endforelse

        {{-- Пагинация --}}
        @if($documents->hasPages())
        <div class="pagination-wrap">
            {{ $documents->links() }}
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const REJECTED_TRANSLATIONS = {
            ru: {
                rejected_docs: 'Отклонённые документы',
                rejection_reason: 'Причина',
                no_rejection_reason: 'Причина не указана',
                rejected_by: 'Отклонил:',
                status_rejected: 'Отклонён',
                doc_view: 'Просмотр',
                download: 'Скачать',
                no_rejected_docs: 'Нет отклонённых документов',
                all_docs_approved: 'Все документы одобрены или в работе',
                back_to_all: 'Назад'
            },
            tj: {
                rejected_docs: 'Ҳуҷҷатҳои радшуда',
                rejection_reason: 'Сабаб',
                no_rejection_reason: 'Сабаб нишон дода нашудааст',
                rejected_by: 'Рад кард:',
                status_rejected: 'Рад шуд',
                doc_view: 'Дидан',
                download: 'Боргирӣ',
                no_rejected_docs: 'Ҳуҷҷати радшуда нест',
                all_docs_approved: 'Ҳамаи ҳуҷҷатҳо тасдиқ шудаанд',
                back_to_all: 'Қафо'
            },
            en: {
                rejected_docs: 'Rejected Documents',
                rejection_reason: 'Reason',
                no_rejection_reason: 'No reason specified',
                rejected_by: 'Rejected by:',
                status_rejected: 'Rejected',
                doc_view: 'View',
                download: 'Download',
                no_rejected_docs: 'No rejected documents',
                all_docs_approved: 'All documents approved or in progress',
                back_to_all: 'Back'
            }
        };

        function applyRejectedTranslations(lang) {
            const dict = REJECTED_TRANSLATIONS[lang] || REJECTED_TRANSLATIONS.ru;
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (dict[key] !== undefined) el.textContent = dict[key];
            });
            document.querySelectorAll('[data-i18n-title]').forEach(el => {
                const key = el.getAttribute('data-i18n-title');
                if (dict[key] !== undefined) el.setAttribute('title', dict[key]);
            });
        }

        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applyRejectedTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            applyRejectedTranslations(e.detail?.lang || 'ru');
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applyRejectedTranslations(e.newValue);
            }
        });
    });
</script>
@endsection