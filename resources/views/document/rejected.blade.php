@extends('layouts.admin')

@section('content')

<style>
    /* ============================================================ */
    /* ===== REJECTED DOCS PAGE (Tabler Style) ===== */
    /* ============================================================ */
    .rejected-docs-page {
        color: var(--text);
        padding: 28px 24px;
        min-height: calc(100vh - 64px);
        font-family: var(--font-sans);
    }

    /* HEADER */
    .rejected-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 32px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--line);
    }
    .rejected-page-header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -0.3px;
        display: flex;
        align-items: center;
        gap: 14px;
        color: var(--text);
    }
    .rejected-page-header h1 .accent-bar {
        width: 4px;
        height: 22px;
        background: #ef4444;
        border-radius: 2px;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
    }
    .rejected-page-header .count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        height: 26px;
        padding: 0 10px;
        background: rgba(239, 68, 68, 0.10);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.25);
        border-radius: var(--radius);
        font-size: 12px;
        font-weight: 700;
        font-family: var(--font-mono);
    }

    /* GRID */
    .rejected-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 28px;
    }

    /* CARD */
    .rejected-card {
        background: var(--bg-1);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 22px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        box-shadow: var(--shadow-card);
    }
    .rejected-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: #ef4444;
        border-radius: var(--radius) 0 0 var(--radius);
    }
    .rejected-card:hover {
        border-color: rgba(239, 68, 68, 0.4);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        transform: translateY(-2px);
    }

    /* CARD HEADER */
    .rejected-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }
    .rejected-card-title {
        font-weight: 700;
        font-size: 14px;
        color: var(--text);
        line-height: 1.4;
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        letter-spacing: -0.2px;
    }
    .rejected-card-number {
        font-family: var(--font-mono);
        font-size: 10px;
        font-weight: 700;
        color: #ef4444;
        background: rgba(239, 68, 68, 0.08);
        padding: 4px 10px;
        border-radius: var(--radius);
        border: 1px solid rgba(239, 68, 68, 0.25);
        white-space: nowrap;
        flex-shrink: 0;
        letter-spacing: 0.3px;
    }

    /* CARD META */
    .rejected-card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
        font-size: 11px;
        color: var(--muted);
    }
    .rejected-card-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 9px;
        background: var(--bg-2);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        font-weight: 600;
    }
    .rejected-card-meta-item i {
        color: #ef4444;
        font-size: 11px;
        flex-shrink: 0;
    }

    /* CARD CONTENT */
    .rejected-card-content {
        font-size: 12px;
        color: var(--text);
        margin-bottom: 14px;
        line-height: 1.5;
        padding: 10px 12px;
        background: var(--bg-2);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        border-left: 3px solid #ef4444;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        box-shadow: inset 0 1px 2px rgba(15,23,42,0.03);
    }

    /* REJECTION REASON BLOCK */
    .rejection-reason-block {
        background: rgba(245, 158, 11, 0.08);
        border: 1px solid rgba(245, 158, 11, 0.25);
        border-radius: var(--radius);
        padding: 14px;
        margin-top: auto;
        margin-bottom: 14px;
    }
    .rejection-reason-label {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 10px;
        font-weight: 700;
        color: #b45309;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 8px;
        font-family: var(--font-sans);
    }
    html[data-theme="dark"] .rejection-reason-label {
        color: #fbbf24;
    }
    .rejection-reason-label i {
        font-size: 12px;
        color: #f59e0b;
        flex-shrink: 0;
    }
    
    .rejection-reason-text {
        font-size: 12px;
        color: #92400e;
        line-height: 1.5;
        font-style: italic;
        padding: 10px 12px;
        background: rgba(255, 251, 235, 0.6);
        border-radius: var(--radius);
        border-left: 3px solid #f59e0b;
        white-space: pre-line;
        font-weight: 500;
    }
    html[data-theme="dark"] .rejection-reason-text {
        color: #fcd34d;
        background: rgba(245, 158, 11, 0.12);
    }
    .rejection-reason-empty {
        font-size: 11px;
        color: var(--muted);
        font-style: italic;
        padding: 8px 12px;
        background: var(--bg-2);
        border-radius: var(--radius);
    }
    .rejected-by-info {
        margin-top: 8px;
        font-size: 10px;
        color: var(--muted);
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
    }
    .rejected-by-info i {
        color: #f59e0b;
        font-size: 11px;
        flex-shrink: 0;
    }
    .rejected-by-info strong {
        color: var(--text);
        font-weight: 700;
    }

    /* CARD FOOTER */
    .rejected-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-top: 14px;
        border-top: 1px solid var(--line);
    }

    /* REJECTED BADGE */
    .rejected-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: var(--radius);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        background: rgba(239, 68, 68, 0.10);
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #dc2626;
        font-family: var(--font-sans);
    }
    html[data-theme="dark"] .rejected-badge {
        color: #f87171;
    }
    .rejected-badge::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #ef4444;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
        animation: badgePulse 2s ease-in-out infinite;
        flex-shrink: 0;
    }
    @keyframes badgePulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.1); }
    }

    /* ACTION BUTTONS */
    .action-btn-group {
        display: flex;
        gap: 6px;
    }
    .action-btn {
        width: 34px;
        height: 34px;
        border-radius: var(--radius);
        background: var(--bg-1);
        border: 1px solid var(--line);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--muted);
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .action-btn:hover {
        color: #ef4444;
        border-color: rgba(239, 68, 68, 0.4);
        background: rgba(239, 68, 68, 0.06);
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.12);
    }
    .action-btn i { font-size: 14px; }

    /* EMPTY STATE */
    .empty-state {
        padding: 70px 20px;
        text-align: center;
    }
    .empty-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: rgba(239, 68, 68, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        color: #ef4444;
        font-size: 28px;
    }
    .empty-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 8px;
        letter-spacing: -0.2px;
    }
    .empty-sub {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 24px;
        font-weight: 500;
    }
    .empty-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 22px;
        background: var(--bg-1);
        color: var(--text);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.25s ease;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        box-shadow: var(--shadow-card);
        font-family: var(--font-sans);
    }
    .empty-back-btn:hover {
        border-color: rgba(var(--glow), 0.4);
        color: rgb(var(--glow));
        transform: translateX(-2px);
        box-shadow: 0 4px 12px rgba(var(--glow), 0.15);
    }
    .empty-back-btn i { font-size: 14px; }

    /* PAGINATION */
    .pagination-wrap {
        margin-top: 32px;
        display: flex;
        justify-content: center;
    }
    .pagination-wrap nav {
        display: flex;
        gap: 4px;
        background: var(--bg-1);
        border: 1px solid var(--line);
        padding: 5px;
        border-radius: var(--radius);
        box-shadow: var(--shadow-card);
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
        min-width: 34px;
        height: 34px;
        padding: 0 12px;
        border-radius: var(--radius);
        font-size: 13px;
        font-weight: 700;
        color: var(--muted);
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        background: transparent;
        font-family: var(--font-sans);
    }
    .pagination-wrap .pagination li a:hover {
        color: #ef4444;
        background: rgba(239, 68, 68, 0.06);
        border-color: rgba(239, 68, 68, 0.25);
    }
    .pagination-wrap .pagination li.active span,
    .pagination-wrap .pagination li span[aria-current="page"] {
        color: #fff;
        background: #ef4444;
        border-color: #ef4444;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
    }
    .pagination-wrap .pagination li.disabled span {
        color: var(--border);
        cursor: not-allowed;
    }

    /* ============ RESPONSIVE ============ */
    @media (max-width: 1024px) {
        .rejected-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .rejected-docs-page { padding: 20px 16px; }
        .rejected-page-header h1 { font-size: 19px; }
        .rejected-grid { grid-template-columns: 1fr; gap: 16px; }
        .rejected-card { padding: 18px; }
        .rejected-card-title { font-size: 13px; }
        .rejected-card-meta { font-size: 10px; gap: 6px; }
        .rejection-reason-text { font-size: 11px; }
        .rejected-card-footer { flex-direction: column; align-items: stretch; gap: 10px; }
        .action-btn-group { justify-content: flex-end; }
        .action-btn { width: 38px; height: 36px; }
    }
    @media (max-width: 480px) {
        .rejected-docs-page { padding: 16px 12px; }
        .rejected-page-header { margin-bottom: 24px; padding-bottom: 16px; }
        .rejected-page-header h1 { font-size: 17px; gap: 10px; }
        .rejected-page-header .count-badge { min-width: 28px; height: 24px; font-size: 11px; padding: 0 8px; }
        .rejected-grid { gap: 14px; }
        .rejected-card { padding: 16px; }
        .rejected-card-title { font-size: 12px; }
        .rejected-card-number { font-size: 9px; padding: 3px 8px; }
        .rejected-card-meta { font-size: 9px; gap: 5px; }
        .rejected-card-meta-item { padding: 3px 7px; }
        .rejected-card-content { font-size: 11px; padding: 8px 10px; }
        .rejection-reason-block { padding: 12px; }
        .rejection-reason-label { font-size: 9px; }
        .rejection-reason-text { font-size: 10px; padding: 8px 10px; }
        .rejected-badge { padding: 4px 10px; font-size: 9px; }
        .action-btn { width: 34px; height: 32px; }
        .empty-icon { width: 60px; height: 60px; font-size: 24px; }
        .empty-title { font-size: 14px; }
        .empty-sub { font-size: 12px; }
    }
</style>

<div class="rejected-docs-page">
    <div class="max-w-7xl mx-auto">
        
        {{-- HEADER --}}
        <div class="rejected-page-header">
            <h1>
                <span class="accent-bar"></span>
                <span data-i18n="rejected_docs">Отклонённые документы</span>
                <span class="count-badge">{{ $documents->total() }}</span>
            </h1>
        </div>

        {{-- GRID --}}
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

                {{-- REJECTION REASON --}}
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
                            <div class="rejected-by-info">
                                <i class="bi bi-person-fill"></i>
                                <span data-i18n="rejected_by">Отклонил:</span> 
                                <strong>{{ Str::limit($rejectedBy->name ?? 'Unknown', 15) }}</strong>
                            </div>
                        @endif
                    @else
                        <div class="rejection-reason-empty" data-i18n="no_rejection_reason">
                            Причина не указана
                        </div>
                    @endif
                </div>

                <div class="rejected-card-footer">
                    <span class="rejected-badge" data-i18n="status_rejected">
                        Отклонён
                    </span>
                    <div class="action-btn-group">
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
            <a href="{{ route('documents.index') }}" class="empty-back-btn">
                <i class="bi bi-arrow-left"></i>
                <span data-i18n="back_to_all">Назад</span>
            </a>
        </div>
        @endforelse

        {{-- PAGINATION --}}
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