@extends('layouts.admin')

@section('content')
<style>
    body { background: #06070b !important; color: #e7ecf3 !important; }

    .preview-page { color: var(--text, #e7ecf3); max-width: 860px; margin: 0 auto; }

    .preview-page .page-head-custom {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .preview-page h1 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        letter-spacing: -0.4px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .preview-page h1 .accent-bar {
        width: 4px;
        height: 20px;
        background: rgba(var(--glow), 1);
        border-radius: 2px;
        box-shadow: 0 0 12px rgba(var(--glow), 0.8);
    }

    .preview-page .page-sub {
        color: var(--muted);
        font-size: 11.5px;
        margin-top: 2px;
        opacity: 0.75;
    }

    /* Алерт */
    .ai-alert-success {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 10px;
        margin-bottom: 12px;
        border: 1px solid rgba(76,217,130,0.3);
        background: rgba(76,217,130,0.08);
        font-size: 12px;
        color: #a8e8bf;
    }
    .ai-alert-success i {
        width: 22px; height: 22px;
        border-radius: 6px;
        background: rgba(76,217,130,0.15);
        color: #4cd982;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px;
    }

    /* Верхняя панель действий */
    .preview-actions {
        background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        position: relative;
        overflow: hidden;
    }
    .preview-actions::before {
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: 12px;
        padding: 1px;
        background: linear-gradient(135deg, rgba(var(--glow), 0.3), transparent 40%, transparent 60%, rgba(var(--glow), 0.15));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
        opacity: 0.6;
    }

    .preview-info {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .preview-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        background: rgba(var(--glow), 0.1);
        border: 1px solid rgba(var(--glow), 0.25);
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
        color: rgba(var(--glow), 1);
        flex-shrink: 0;
    }

    .preview-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
        margin: 0;
        line-height: 1.2;
    }

    .preview-sample {
        font-size: 10px;
        color: var(--muted);
        opacity: 0.75;
        margin-top: 2px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .preview-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .preview-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.2s ease;
        border: 1px solid var(--line);
        background: rgba(255,255,255,0.04);
        color: var(--text);
    }
    .preview-btn:hover {
        border-color: rgba(var(--glow), 0.4);
        background: rgba(var(--glow), 0.1);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(var(--glow), 0.15);
    }
    .preview-btn i { font-size: 12px; }

    .preview-btn.btn-word {
        background: linear-gradient(90deg, rgba(245,197,66,0.15), rgba(224,169,44,0.1));
        color: #f5c542;
        border-color: rgba(245,197,66,0.3);
    }
    .preview-btn.btn-word:hover {
        background: linear-gradient(90deg, rgba(245,197,66,0.25), rgba(224,169,44,0.2));
        box-shadow: 0 0 16px rgba(245,197,66,0.25);
        color: #f5c542;
    }

    .preview-btn.btn-pdf {
        background: linear-gradient(90deg, rgba(255,107,107,0.15), rgba(238,90,82,0.1));
        color: #ff6b6b;
        border-color: rgba(255,107,107,0.3);
    }
    .preview-btn.btn-pdf:hover {
        background: linear-gradient(90deg, rgba(255,107,107,0.25), rgba(238,90,82,0.2));
        box-shadow: 0 0 16px rgba(255,107,107,0.25);
        color: #ff6b6b;
    }

    .preview-btn.btn-new {
        background: rgba(var(--glow), 0.08);
        color: rgba(var(--glow), 1);
        border-color: rgba(var(--glow), 0.2);
    }
    .preview-btn.btn-new:hover {
        background: rgba(var(--glow), 0.15);
        border-color: rgba(var(--glow), 0.4);
    }

    /* Бумажный лист */
    .paper-card {
        background: #fff;
        color: #1a1a1a;
        border-radius: 12px;
        padding: 48px 56px;
        font-family: 'Times New Roman', Georgia, serif;
        font-size: 14px;
        line-height: 1.7;
        white-space: pre-wrap;
        word-break: break-word;
        box-shadow: 0 8px 32px rgba(0,0,0,0.4), 0 0 20px rgba(var(--glow), 0.1);
        position: relative;
    }

    .paper-card::before {
        content: "";
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 4px;
        background: linear-gradient(90deg, rgba(var(--glow), 1), transparent);
        border-radius: 12px 12px 0 0;
    }

    .paper-meta {
        position: absolute;
        top: 12px;
        right: 16px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 9px;
        color: rgba(0,0,0,0.3);
        letter-spacing: 0.5px;
    }

    /* Адаптив */
    @media (max-width: 768px) {
        .preview-actions { padding: 12px 14px; gap: 10px; }
        .preview-icon { width: 32px; height: 32px; font-size: 14px; }
        .preview-title { font-size: 12.5px; }
        .preview-sample { font-size: 9.5px; }
        .preview-btn { padding: 7px 12px; font-size: 11px; }
        .paper-card { padding: 32px 28px; font-size: 13px; border-radius: 10px; }
    }

    @media (max-width: 576px) {
        .preview-page h1 { font-size: 18px; }
        .preview-actions { flex-direction: column; align-items: stretch; }
        .preview-buttons { width: 100%; justify-content: flex-start; }
        .preview-btn { flex: 1; justify-content: center; padding: 8px 10px; }
        .paper-card { padding: 24px 18px; font-size: 12.5px; border-radius: 8px; }
        .paper-meta { top: 8px; right: 10px; font-size: 8px; }
    }

    @media (max-width: 380px) {
        .preview-page h1 { font-size: 16px; }
        .preview-btn { padding: 7px 8px; font-size: 10.5px; gap: 4px; }
        .paper-card { padding: 20px 14px; font-size: 12px; }
    }
</style>

<div class="preview-page">
    <div class="page-head-custom">
        <div>
            <h1>
                <span class="accent-bar"></span>
                <span data-i18n="preview_title">Ҳуҷҷат тайёр</span>
            </h1>
            <div class="page-sub" data-i18n="preview_sub">Шумо метавонед онро бинед ва зеркашӣ кунед</div>
        </div>
    </div>

    @if(session('success'))
    <div class="ai-alert-success">
        <i class="bi bi-check-circle-fill"></i>
        <div data-i18n="doc_created">{{ session('success') }}</div>
    </div>
    @endif

    <div class="preview-actions">
        <div class="preview-info">
            <div class="preview-icon">
                <i class="bi bi-file-earmark-text-fill"></i>
            </div>
            <div>
                <h3 class="preview-title" data-i18n="doc_ready">📄 Ҳуҷҷат тайёр!</h3>
                <div class="preview-sample">
                    <i class="bi bi-link-45deg"></i>
                    <span data-i18n="sample_used">Аз намуна:</span>
                    <strong>{{ $contract->detected_filename ?? $contract->detected_type ?? 'с нуля' }}</strong>
                </div>
            </div>
        </div>

        <div class="preview-buttons">
            <a href="{{ route('contracts.download', ['id' => $contract->id]) }}" class="preview-btn btn-word">
                <i class="bi bi-file-earmark-word-fill"></i>
                <span>Word</span>
            </a>

            @if($contract->pdf_filepath)
                <a href="{{ route('contracts.downloadPdf', ['id' => $contract->id]) }}" class="preview-btn btn-pdf">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                    <span>PDF</span>
                </a>
            @endif

            <a href="{{ route('contracts.create') }}" class="preview-btn btn-new">
                <i class="bi bi-plus-lg"></i>
                <span data-i18n="new_doc">Нав</span>
            </a>
        </div>
    </div>

    <div class="paper-card">
        <div class="paper-meta">#{{ $contract->id }} • {{ $contract->created_at->format('d.m.Y') }}</div>
        {{ $text }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const PREVIEW_TRANSLATIONS = {
        ru: {
            preview_title: 'Документ готов',
            preview_sub: 'Просмотрите и скачайте в нужном формате',
            doc_created: 'Документ успешно создан!',
            doc_ready: '📄 Документ готов!',
            sample_used: 'Из образца:',
            new_doc: 'Новый'
        },
        tj: {
            preview_title: 'Ҳуҷҷат тайёр',
            preview_sub: 'Шумо метавонед онро бинед ва зеркашӣ кунед',
            doc_created: 'Ҳуҷҷат бомуваффақият сохта шуд!',
            doc_ready: '📄 Ҳуҷҷат тайёр!',
            sample_used: 'Аз намуна:',
            new_doc: 'Нав'
        },
        en: {
            preview_title: 'Document ready',
            preview_sub: 'View and download in any format',
            doc_created: 'Document successfully created!',
            doc_ready: '📄 Document ready!',
            sample_used: 'From sample:',
            new_doc: 'New'
        }
    };

    function applyTranslations(lang) {
        const dict = PREVIEW_TRANSLATIONS[lang] || PREVIEW_TRANSLATIONS.tj;
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (dict[key] !== undefined) el.innerHTML = dict[key];
        });
    }

    const lang = localStorage.getItem('docsign_lang') || 'tj';
    applyTranslations(lang);

    window.addEventListener('docsign:lang-changed', (e) => applyTranslations(e.detail?.lang || 'tj'));
    window.addEventListener('storage', (e) => {
        if (e.key === 'docsign_lang' && e.newValue) applyTranslations(e.newValue);
    });
});
</script>
@endsection