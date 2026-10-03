@extends('layouts.admin')

@section('content')
<style>
    body { background: #06070b !important; color: #e7ecf3 !important; }

    .doc-create-page { 
        color: var(--text, #e7ecf3); 
        max-width: 1100px; /* Немного расширил для 3 колонок */
        margin: 0 auto; 
        padding: 40px 20px;
    }

    /* === HERO СЕКЦИЯ === */
    .hero-section {
        text-align: center;
        padding: 30px 0 40px;
        margin-bottom: 30px;
        position: relative;
    }

    .hero-section::before {
        content: "";
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 3px;
        background: linear-gradient(90deg, transparent, rgba(var(--glow, 139,92,246), 1), transparent);
        border-radius: 2px;
        box-shadow: 0 0 12px rgba(var(--glow, 139,92,246), 0.6);
    }

    .hero-title {
        font-size: 26px;
        font-weight: 700;
        letter-spacing: -0.5px;
        margin: 0 0 12px 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .hero-title .accent-bar {
        width: 4px;
        height: 26px;
        background: rgba(var(--glow, 139,92,246), 1);
        border-radius: 2px;
        box-shadow: 0 0 10px rgba(var(--glow, 139,92,246), 0.6);
    }

    .hero-subtitle {
        font-size: 14px;
        color: var(--muted);
        line-height: 1.6;
        max-width: 600px;
        margin: 0 auto;
        opacity: 0.85;
    }

    /* === КНОПКИ === */
    .big-buttons-section {
        display: grid;
        grid-template-columns: repeat(3, 1fr); /* 3 колонки на десктопе */
        gap: 16px;
        margin-bottom: 0;
    }

    .big-btn {
        position: relative;
        display: block;
        padding: 28px 24px;
        border-radius: 14px;
        text-decoration: none;
        background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
        border: 1px solid var(--line, rgba(255,255,255,0.08));
        transition: all 0.25s ease;
        overflow: hidden;
    }

    .big-btn::before {
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: 14px;
        padding: 1px;
        background: linear-gradient(135deg, rgba(var(--glow, 139,92,246), 0.3), transparent 40%, transparent 60%, rgba(var(--glow, 139,92,246), 0.15));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.25s ease;
    }

    .big-btn:hover::before { opacity: 1; }

    .big-btn:hover {
        transform: translateY(-2px);
        border-color: rgba(var(--glow, 139,92,246), 0.25);
        box-shadow: 0 8px 24px rgba(0,0,0,0.4), 0 0 20px rgba(var(--glow, 139,92,246), 0.15);
    }

    .big-btn.btn-rag {
        background: linear-gradient(180deg, rgba(245,197,66,0.06), rgba(245,197,66,0.02));
    }
    .big-btn.btn-rag::before {
        background: linear-gradient(135deg, rgba(245,197,66,0.4), transparent 40%, transparent 60%, rgba(245,197,66,0.2));
    }
    .big-btn.btn-rag:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.4), 0 0 24px rgba(245,197,66,0.2);
    }
    .big-btn.btn-rag .big-btn-icon {
        background: rgba(245,197,66,0.1);
        border: 1px solid rgba(245,197,66,0.25);
        color: #f5c542;
    }

    .big-btn.btn-analyze {
        background: linear-gradient(180deg, rgba(77,208,225,0.06), rgba(77,208,225,0.02));
    }
    .big-btn.btn-analyze::before {
        background: linear-gradient(135deg, rgba(77,208,225,0.4), transparent 40%, transparent 60%, rgba(77,208,225,0.2));
    }
    .big-btn.btn-analyze:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.4), 0 0 24px rgba(77,208,225,0.2);
    }
    .big-btn.btn-analyze .big-btn-icon {
        background: rgba(77,208,225,0.1);
        border: 1px solid rgba(77,208,225,0.25);
        color: #4dd0e1;
    }

    /* === НОВАЯ КНОПКА: АРХИВИСТ === */
    .big-btn.btn-archivist {
        background: linear-gradient(180deg, rgba(139,92,246,0.06), rgba(139,92,246,0.02));
    }
    .big-btn.btn-archivist::before {
        background: linear-gradient(135deg, rgba(139,92,246,0.4), transparent 40%, transparent 60%, rgba(139,92,246,0.2));
    }
    .big-btn.btn-archivist:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.4), 0 0 24px rgba(139,92,246,0.2);
    }
    .big-btn.btn-archivist .big-btn-icon {
        background: rgba(139,92,246,0.1);
        border: 1px solid rgba(139,92,246,0.25);
        color: #a78bfa;
    }

    .big-btn-content {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }

    .big-btn-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
        background: rgba(var(--glow, 139,92,246), 0.08);
        border: 1px solid rgba(var(--glow, 139,92,246), 0.2);
        transition: all 0.25s ease;
    }

    .big-btn:hover .big-btn-icon {
        transform: scale(1.05);
    }

    .big-btn-text {
        flex: 1;
        min-width: 0;
    }

    .big-btn-title {
        display: block;
        font-size: 16px;
        font-weight: 700;
        color: var(--text, #e7ecf3);
        margin-bottom: 6px;
        letter-spacing: -0.2px;
    }

    .big-btn-desc {
        display: block;
        font-size: 12.5px;
        color: var(--muted, #8892a6);
        line-height: 1.5;
        opacity: 0.85;
    }

    .big-btn-arrow {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 28px;
        height: 28px;
        border-radius: 7px;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--line, rgba(255,255,255,0.08));
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--muted, #8892a6);
        font-size: 12px;
        transition: all 0.25s ease;
    }

    .big-btn:hover .big-btn-arrow {
        color: var(--text, #e7ecf3);
        transform: translateX(4px);
        border-color: rgba(var(--glow, 139,92,246), 0.3);
    }

    /* === АДАПТИВНОСТЬ === */
    @media (max-width: 900px) {
        .big-buttons-section {
            grid-template-columns: 1fr; /* 1 колонка на планшетах и мобильных */
            gap: 14px;
        }
    }

    @media (max-width: 768px) {
        .doc-create-page { padding: 30px 16px; }
        .hero-section { padding: 24px 0 30px; }
        .hero-title { font-size: 22px; }
        .hero-title .accent-bar { width: 4px; height: 22px; }
        .hero-subtitle { font-size: 13px; }
        .big-btn { padding: 24px 20px; }
        .big-btn-icon { width: 44px; height: 44px; font-size: 20px; }
        .big-btn-title { font-size: 15px; }
        .big-btn-desc { font-size: 12px; }
    }

    @media (max-width: 480px) {
        .doc-create-page { padding: 24px 14px; }
        .hero-section { padding: 20px 0 24px; }
        .hero-title { font-size: 20px; }
        .hero-title .accent-bar { height: 20px; }
        .hero-subtitle { font-size: 12.5px; }
        .big-btn { padding: 22px 18px; border-radius: 12px; }
        .big-btn-content { gap: 14px; }
        .big-btn-icon { width: 42px; height: 42px; font-size: 19px; border-radius: 10px; }
        .big-btn-title { font-size: 14.5px; margin-bottom: 5px; }
        .big-btn-desc { font-size: 11.5px; line-height: 1.45; }
        .big-btn-arrow { display: none; }
    }
</style>

<style>
    html[data-theme="light"] .doc-create-page {
        color: #0f172a !important;
    }
    html[data-theme="light"] .hero-title,
    html[data-theme="light"] .big-btn-title {
        color: #0f172a !important;
    }
    html[data-theme="light"] .hero-subtitle,
    html[data-theme="light"] .big-btn-desc,
    html[data-theme="light"] .big-btn-arrow {
        color: #475569 !important;
    }
    html[data-theme="light"] .big-btn {
        background: #ffffff !important;
        border: 1.5px solid #cbd5e1 !important;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.07);
    }
    html[data-theme="light"] .big-btn:hover {
        border-color: rgba(139,92,246, 0.55) !important;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.13), 0 0 20px rgba(139,92,246, 0.12);
    }
    html[data-theme="light"] .big-btn.btn-rag {
        background: linear-gradient(135deg, #ffffff, #fffbeb) !important;
        border-color: #f1c453 !important;
    }
    html[data-theme="light"] .big-btn.btn-analyze {
        background: linear-gradient(135deg, #ffffff, #ecfeff) !important;
        border-color: #67e8f9 !important;
    }
    html[data-theme="light"] .big-btn.btn-archivist {
        background: linear-gradient(135deg, #ffffff, #f5f3ff) !important;
        border-color: #a78bfa !important;
    }
    html[data-theme="light"] .big-btn-arrow {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    html[data-theme="light"] .big-btn.btn-rag .big-btn-title {
        color: #713f12 !important;
    }
    html[data-theme="light"] .big-btn.btn-analyze .big-btn-title {
        color: #164e63 !important;
    }
    html[data-theme="light"] .big-btn.btn-archivist .big-btn-title {
        color: #5b21b6 !important;
    }
</style>

<div class="doc-create-page">
    <!-- HERO СЕКЦИЯ -->
    <div class="hero-section">
        <h4 class="hero-title">
            <span class="accent-bar"></span>
            <span data-i18n="hero_title">Сохтани ҳуҷҷат бо ИИ</span>
        </h4>
        <p class="hero-subtitle" data-i18n="hero_subtitle">
            Системаи мо бо ёрии зеҳни сунъӣ ҳуҷҷатҳои расмии шуморо дар давоми чанд сония месозад
        </p>
    </div>

    <!-- ТРИ БОЛЬШИЕ КНОПКИ -->
    <div class="big-buttons-section">

        <!-- Кнопка 1: RAG + ИИ -->
        <a href="{{ route('ai.chat') }}" class="big-btn btn-rag">
            <span class="big-btn-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                </svg>
            </span>
            <div class="big-btn-content">
                <span class="big-btn-icon">
                    <i class="bi bi-robot"></i>
                </span>
                <div class="big-btn-text">
                    <span class="big-btn-title" data-i18n="btn_rag_title">Сохтани ҳуҷҷат</span>
                    <span class="big-btn-desc" data-i18n="btn_rag_desc">
                        Анкетаро пур кунед — ИИ ҳуҷҷати шуморо менависад (ариза, дархост, шартнома...)
                    </span>
                </div>
            </div>
        </a>

        <!-- Кнопка 2: Анализ документа -->
        <a href="{{ route('contracts.analyze-page') }}" class="big-btn btn-analyze">
            <span class="big-btn-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                </svg>
            </span>
            <div class="big-btn-content">
                <span class="big-btn-icon">
                    <i class="bi bi-search"></i>
                </span>
                <div class="big-btn-text">
                    <span class="big-btn-title" data-i18n="btn_analyze_title">Таҳлили ҳуҷҷат</span>
                    <span class="big-btn-desc" data-i18n="btn_analyze_desc">
                        Файли Word/PDF-ро бор кунед — ИИ хонда мазмуни асосиро нақл мекунад
                    </span>
                </div>
            </div>
        </a>

        <!-- Кнопка 3: ИИ-Архивист (НОВАЯ) -->
        <a href="{{ route('archivist.page') }}" class="big-btn btn-archivist">
            <span class="big-btn-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                </svg>
            </span>
            <div class="big-btn-content">
                <span class="big-btn-icon">
                    <i class="bi bi-archive-fill"></i>
                </span>
                <div class="big-btn-text">
                    <span class="big-btn-title" data-i18n="btn_archivist_title">ИИ-Архивист</span>
                    <span class="big-btn-desc" data-i18n="btn_archivist_desc">
                        Поиск документов по дате, типу, сроку и содержимому
                    </span>
                </div>
            </div>
        </a>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const CREATE_TRANSLATIONS = {
        tj: {
            hero_title: 'Сохтани ҳуҷҷат бо ИИ',
            hero_subtitle: 'Системаи мо бо ёрии зеҳни сунъӣ ҳуҷҷатҳои расмии шуморо дар давоми чанд сония месозад',
            btn_rag_title: 'Сохтани ҳуҷҷат',
            btn_rag_desc: 'Анкетаро пур кунед — ИИ ҳуҷҷати шуморо менависад (ариза, дархост, шартнома...)',
            btn_analyze_title: 'Таҳлили ҳуҷҷат',
            btn_analyze_desc: 'Файли Word/PDF-ро бор кунед — ИИ хонда мазмуни асосиро нақл мекунад',
            btn_archivist_title: 'Архивчи ИИ',
            btn_archivist_desc: 'Ҷустуҷӯи ҳуҷҷатҳо аз рӯи сана, намуд, мӯҳлат ва мазмун'
        },
        ru: {
            hero_title: 'Создание документов с ИИ',
            hero_subtitle: 'Наша система с помощью искусственного интеллекта создаёт ваши официальные документы за несколько секунд',
            btn_rag_title: 'Создать документ',
            btn_rag_desc: 'Заполните анкету — ИИ напишет ваш документ (заявление, запрос, договор...)',
            btn_analyze_title: 'Анализ документа',
            btn_analyze_desc: 'Загрузите файл Word/PDF — ИИ прочитает и расскажет основное содержание',
            btn_archivist_title: 'ИИ-Архивист',
            btn_archivist_desc: 'Поиск документов по дате, типу, сроку и содержимому'
        },
        en: {
            hero_title: 'Create Documents with AI',
            hero_subtitle: 'Our system uses artificial intelligence to create your official documents in just a few seconds',
            btn_rag_title: 'Create Document',
            btn_rag_desc: 'Fill out the form — AI will write your document (application, request, contract...)',
            btn_analyze_title: 'Document Analysis',
            btn_analyze_desc: 'Upload Word/PDF file — AI will read and summarize the main content',
            btn_archivist_title: 'AI Archivist',
            btn_archivist_desc: 'Search documents by date, type, deadline, and content'
        }
    };

    function applyCreateTranslations(lang) {
        const dict = CREATE_TRANSLATIONS[lang] || CREATE_TRANSLATIONS.tj;

        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (dict[key] !== undefined) el.innerHTML = dict[key];
        });
    }

    const initialLang = localStorage.getItem('docsign_lang') || 'tj';
    applyCreateTranslations(initialLang);

    window.addEventListener('docsign:lang-changed', (e) => {
        applyCreateTranslations(e.detail?.lang || 'tj');
    });

    window.addEventListener('storage', (e) => {
        if (e.key === 'docsign_lang' && e.newValue) {
            applyCreateTranslations(e.newValue);
        }
    });
});
</script>
@endsection