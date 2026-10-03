@extends('layouts.admin')

@section('content')
<style>
    /* === Fallback === */
    body { background: #06070b !important; color: #e7ecf3 !important; }

    /* === СТРАНИЦА АНАЛИЗА === */
    .analyze-page {
        color: var(--text, #e7ecf3);
        max-width: 820px;
        margin: 0 auto;
        position: relative;
    }

    /* Декоративное свечение */
    .analyze-page::before {
        content: "";
        position: absolute;
        top: -40px;
        left: 50%;
        transform: translateX(-50%);
        width: 400px;
        height: 200px;
        background: radial-gradient(ellipse at center, rgba(77,208,225,0.12), transparent 70%);
        pointer-events: none;
        z-index: -1;
        filter: blur(10px);
    }

    /* === ВЕРХНЯЯ ПАНЕЛЬ: КНОПКА НАЗАД === */
    .form-topbar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .back-btn {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--line);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--muted);
        text-decoration: none;
        font-size: 13px;
        transition: all 0.2s ease;
    }

    .back-btn:hover {
        color: #4dd0e1;
        border-color: rgba(77,208,225,0.4);
        background: rgba(77,208,225,0.08);
        box-shadow: 0 0 12px rgba(77,208,225,0.2);
        transform: translateX(-3px);
    }

    .back-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--muted);
    }

    /* === ЦЕНТРИРОВАННАЯ ШАПКА === */
    .page-head-center {
        text-align: center;
        margin-bottom: 28px;
    }

    .head-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 16px;
        border-radius: 18px;
        background: rgba(77,208,225,0.1);
        border: 1px solid rgba(77,208,225,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: #4dd0e1;
        box-shadow: 0 0 24px rgba(77,208,225,0.25), inset 0 1px 0 rgba(255,255,255,0.1);
        animation: iconFloat 3s ease-in-out infinite;
    }

    @keyframes iconFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }

    .analyze-page h1 {
        margin: 0;
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -0.4px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        justify-content: center;
    }

    .analyze-page h1 .accent-bar {
        width: 4px;
        height: 24px;
        background: #4dd0e1;
        border-radius: 2px;
        box-shadow: 0 0 12px rgba(77,208,225,0.8);
    }

    .analyze-page .page-sub {
        color: var(--muted);
        font-size: 13px;
        margin-top: 8px;
        opacity: 0.85;
        line-height: 1.5;
    }

    /* === СЕКЦИЯ-КИРПИЧИК === */
    .form-section {
        background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 16px;
        position: relative;
        overflow: hidden;
        animation: fadeUp 0.45s ease both;
    }

    .form-section::before {
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: 14px;
        padding: 1px;
        background: linear-gradient(135deg, rgba(77,208,225,0.35), transparent 40%, transparent 60%, rgba(77,208,225,0.2));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
        opacity: 0.6;
    }

    .form-section-head {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--line);
    }

    .form-section-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: rgba(77,208,225,0.1);
        border: 1px solid rgba(77,208,225,0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: #4dd0e1;
        flex-shrink: 0;
        box-shadow: 0 0 12px rgba(77,208,225,0.15);
    }

    .form-section-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
        margin: 0;
        line-height: 1.2;
    }

    .form-section-sub {
        font-size: 11px;
        color: var(--muted);
        opacity: 0.8;
        margin-top: 2px;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* === DROP ZONE === */
    .drop-zone {
        position: relative;
        border: 2px dashed var(--line);
        border-radius: 12px;
        padding: 32px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s ease;
        background: rgba(255,255,255,0.02);
        margin-bottom: 14px;
    }

    .drop-zone:hover,
    .drop-zone.dragover {
        border-color: rgba(77,208,225,0.6);
        background: rgba(77,208,225,0.05);
        box-shadow: 0 0 20px rgba(77,208,225,0.12);
    }

    .drop-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }

    .drop-zone.has-file {
        border-style: solid;
        border-color: rgba(77,208,225,0.4);
        background: rgba(77,208,225,0.06);
    }

    .drop-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: rgba(77,208,225,0.1);
        border: 1px solid rgba(77,208,225,0.25);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        color: #4dd0e1;
        font-size: 24px;
        box-shadow: 0 0 16px rgba(77,208,225,0.2);
        transition: transform 0.3s ease;
    }

    .drop-zone:hover .drop-icon {
        transform: translateY(-3px);
    }

    .drop-zone.has-file .drop-icon {
        background: rgba(77,208,225,0.15);
        color: #4dd0e1;
    }

    .drop-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 4px;
    }

    .drop-sub {
        font-size: 11.5px;
        color: var(--muted);
        opacity: 0.8;
    }

    .drop-sub .hl {
        color: #4dd0e1;
        text-decoration: underline;
    }

    .drop-chips {
        display: flex;
        justify-content: center;
        gap: 6px;
        margin-top: 12px;
    }

    .format-chip {
        font-family: 'JetBrains Mono', monospace;
        font-size: 9px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 5px;
        border: 1px solid;
        letter-spacing: 0.5px;
    }
    .format-chip.pdf { color: #ff6b6b; background: rgba(255,107,107,0.1); border-color: rgba(255,107,107,0.3); }
    .format-chip.word { color: #f5c542; background: rgba(245,197,66,0.1); border-color: rgba(245,197,66,0.3); }
    .format-chip.txt { color: #8892a6; background: rgba(136,146,166,0.1); border-color: rgba(136,146,166,0.3); }

    /* === ПРЕВЬЮ ФАЙЛА === */
    .file-preview {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: rgba(77,208,225,0.08);
        border: 1px solid rgba(77,208,225,0.25);
        border-radius: 10px;
        margin-bottom: 14px;
    }

    .file-preview-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .file-preview-icon.pdf { background: rgba(255,107,107,0.15); color: #ff6b6b; border: 1px solid rgba(255,107,107,0.3); }
    .file-preview-icon.word { background: rgba(245,197,66,0.15); color: #f5c542; border: 1px solid rgba(245,197,66,0.3); }
    .file-preview-icon.txt { background: rgba(136,146,166,0.15); color: #8892a6; border: 1px solid rgba(136,146,166,0.3); }

    .file-preview-info {
        flex: 1;
        min-width: 0;
    }
    .file-preview-name {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .file-preview-meta {
        font-size: 10px;
        color: var(--muted);
        margin-top: 2px;
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .file-preview-meta .dot {
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: var(--muted);
    }

    .file-preview-remove {
        background: rgba(255,99,99,0.1);
        border: 1px solid rgba(255,99,99,0.25);
        width: 28px;
        height: 28px;
        border-radius: 7px;
        color: #ff6b6b;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s;
        font-size: 11px;
    }
    .file-preview-remove:hover {
        background: rgba(255,99,99,0.2);
        border-color: rgba(255,99,99,0.5);
        color: #fff;
    }

    /* === КНОПКА ОТПРАВКИ === */
    .submit-btn {
        width: 100%;
        padding: 14px 24px;
        font: 700 14px 'Inter', sans-serif;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: #062a2a;
        background: linear-gradient(180deg, rgba(77,208,225,0.95), rgba(38,166,154,0.85));
        box-shadow: 0 8px 24px rgba(77,208,225,0.35), inset 0 1px 0 rgba(255,255,255,0.3);
        transition: all 0.25s ease;
        letter-spacing: 0.2px;
    }

    .submit-btn:hover:not(:disabled) {
        filter: brightness(1.08);
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(77,208,225,0.5), inset 0 1px 0 rgba(255,255,255,0.4);
    }

    .submit-btn:disabled {
        opacity: 0.65;
        cursor: wait;
    }

    .submit-btn .spinner {
        display: none;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(6,42,42,0.3);
        border-top-color: #062a2a;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    .submit-btn.loading .spinner { display: inline-block; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* === СЕКЦИЯ РЕЗУЛЬТАТА === */
    .result-section {
        background: linear-gradient(180deg, rgba(77,208,225,0.05), rgba(77,208,225,0.01));
        border: 1px solid rgba(77,208,225,0.25);
        border-radius: 14px;
        padding: 20px;
        position: relative;
        overflow: hidden;
        animation: resultIn 0.4s ease;
    }

    .result-section::before {
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: 14px;
        padding: 1px;
        background: linear-gradient(135deg, rgba(77,208,225,0.5), transparent 40%, transparent 60%, rgba(77,208,225,0.3));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
        opacity: 0.7;
    }

    .result-section.error {
        background: linear-gradient(180deg, rgba(255,99,99,0.05), rgba(255,99,99,0.01));
        border-color: rgba(255,99,99,0.3);
    }
    .result-section.error::before {
        background: linear-gradient(135deg, rgba(255,99,99,0.5), transparent 40%, transparent 60%, rgba(255,99,99,0.3));
    }

    @keyframes resultIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .result-head {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(77,208,225,0.15);
    }

    .result-section.error .result-head {
        border-bottom-color: rgba(255,99,99,0.15);
    }

    .result-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .result-section:not(.error) .result-icon {
        background: rgba(77,208,225,0.15);
        border: 1px solid rgba(77,208,225,0.35);
        color: #4dd0e1;
        box-shadow: 0 0 12px rgba(77,208,225,0.2);
    }

    .result-section.error .result-icon {
        background: rgba(255,99,99,0.15);
        border: 1px solid rgba(255,99,99,0.35);
        color: #ff6b6b;
    }

    .result-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
        margin: 0;
    }

    .result-meta {
        font-size: 10px;
        color: var(--muted);
        margin-top: 2px;
    }

    /* Стилизованное отображение текста ответа */
    .result-text {
        font-size: 13.5px;
        color: #d4d9e1;
        line-height: 1.75;
        white-space: pre-wrap;
        word-break: break-word;
        font-family: 'Inter', sans-serif;
    }

    .result-text strong,
    .result-text b {
        color: #4dd0e1;
        font-weight: 700;
    }

    /* === ПУСТОЕ СОСТОЯНИЕ === */
    .empty-hint {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
        background: rgba(255,255,255,0.02);
        border: 1px dashed var(--line);
        border-radius: 10px;
        margin-top: 14px;
    }

    .empty-hint-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(var(--glow), 0.08);
        border: 1px solid rgba(var(--glow), 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        color: rgba(var(--glow), 1);
        flex-shrink: 0;
    }

    .empty-hint-text {
        font-size: 11.5px;
        color: var(--muted);
        line-height: 1.5;
    }
    .empty-hint-text strong {
        color: var(--text);
        font-weight: 600;
    }

    /* === АДАПТИВ === */
    @media (max-width: 768px) {
        .analyze-page h1 { font-size: 21px; }
        .analyze-page h1 .accent-bar { width: 3px; height: 21px; }
        .analyze-page .page-sub { font-size: 12px; }
        .head-icon { width: 54px; height: 54px; font-size: 23px; }
        .form-section { padding: 18px; }
        .form-section-icon { width: 30px; height: 30px; font-size: 13px; }
        .form-section-title { font-size: 13px; }
        .drop-zone { padding: 28px 16px; }
        .drop-icon { width: 50px; height: 50px; font-size: 21px; }
        .drop-title { font-size: 13px; }
    }

    @media (max-width: 576px) {
        .analyze-page h1 { font-size: 19px; gap: 8px; }
        .analyze-page h1 .accent-bar { width: 3px; height: 19px; }
        .analyze-page .page-sub { font-size: 11.5px; }
        .head-icon { width: 48px; height: 48px; font-size: 20px; border-radius: 14px; margin-bottom: 14px; }
        .form-topbar { margin-bottom: 16px; }
        .back-btn { width: 30px; height: 30px; }
        .back-label { font-size: 9px; }
        .form-section { padding: 16px; border-radius: 12px; }
        .form-section-head { margin-bottom: 14px; padding-bottom: 10px; gap: 10px; }
        .form-section-icon { width: 28px; height: 28px; font-size: 12px; border-radius: 8px; }
        .form-section-title { font-size: 12.5px; }
        .form-section-sub { font-size: 10px; }
        .drop-zone { padding: 24px 14px; border-radius: 10px; }
        .drop-icon { width: 46px; height: 46px; font-size: 19px; border-radius: 14px; margin-bottom: 10px; }
        .drop-title { font-size: 12.5px; }
        .drop-sub { font-size: 11px; }
        .submit-btn { padding: 12px 20px; font-size: 13px; border-radius: 10px; }
        .result-section { padding: 16px; border-radius: 12px; }
        .result-text { font-size: 13px; }
        .empty-hint { padding: 12px; gap: 10px; }
        .empty-hint-icon { width: 32px; height: 32px; font-size: 14px; }
        .empty-hint-text { font-size: 11px; }
    }

    @media (max-width: 480px) {
        .analyze-page h1 { font-size: 18px; }
        .form-section { padding: 14px; }
        .drop-chips { gap: 4px; }
        .format-chip { font-size: 8.5px; padding: 2px 7px; }
        .submit-btn { font-size: 12.5px; }
    }

    @media (max-width: 380px) {
        .analyze-page h1 { font-size: 17px; }
        .form-section { padding: 12px; }
        .drop-zone { padding: 22px 12px; }
        .drop-icon { width: 42px; height: 42px; font-size: 17px; }
        .drop-title { font-size: 12px; }
        .drop-sub { font-size: 10.5px; }
        .submit-btn { font-size: 12px; padding: 11px 16px; }
    }
</style>

<style>
    html[data-theme="light"] .analyze-page {
        color: #0f172a !important;
    }
    html[data-theme="light"] .analyze-page h1,
    html[data-theme="light"] .form-section-title,
    html[data-theme="light"] .drop-title,
    html[data-theme="light"] .file-preview-name,
    html[data-theme="light"] .result-title,
    html[data-theme="light"] .empty-hint-text strong {
        color: #0f172a !important;
    }
    html[data-theme="light"] .page-sub,
    html[data-theme="light"] .back-label,
    html[data-theme="light"] .form-section-sub,
    html[data-theme="light"] .drop-sub,
    html[data-theme="light"] .file-preview-meta,
    html[data-theme="light"] .result-meta,
    html[data-theme="light"] .empty-hint-text {
        color: #475569 !important;
    }
    html[data-theme="light"] .back-btn,
    html[data-theme="light"] .form-section,
    html[data-theme="light"] .result-section {
        background: #ffffff;
        border-color: #cbd5e1 !important;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.07);
    }
    html[data-theme="light"] .form-section-head,
    html[data-theme="light"] .result-head {
        border-bottom-color: #cbd5e1 !important;
    }
    html[data-theme="light"] .drop-zone {
        background: #f8fafc;
        border-color: #94a3b8 !important;
    }
    html[data-theme="light"] .drop-zone:hover,
    html[data-theme="light"] .drop-zone.has-file {
        background: rgba(77, 208, 225, 0.08);
        border-color: #0891b2 !important;
    }
    html[data-theme="light"] .file-preview {
        background: #ecfeff;
        border-color: #67e8f9;
    }
    html[data-theme="light"] .result-text {
        color: #1e293b !important;
    }
    html[data-theme="light"] .empty-hint {
        background: #f8fafc;
        border-color: #94a3b8 !important;
    }
    html[data-theme="light"] .file-preview-remove {
        color: #dc2626;
    }
    html[data-theme="light"] .submit-btn {
        color: #083344;
        border: 1px solid #0891b2;
        box-shadow: 0 6px 18px rgba(8, 145, 178, 0.22);
    }
</style>

<div class="analyze-page">

    {{-- 🔙 Кнопка назад --}}
    <div class="form-topbar">
        <a href="/contracts" class="back-btn" data-i18n-title="back" title="Ба қафо">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="back-label" data-i18n="back">Ба қафо</span>
    </div>

    {{-- 🎯 Центрированная шапка --}}
    <div class="page-head-center">
        <div class="head-icon">
            <i class="bi bi-search"></i>
        </div>
        <h1>
            <span class="accent-bar"></span>
            <span data-i18n="page_title">Таҳлили ҳуҷҷат</span>
        </h1>
        <div class="page-sub" data-i18n="page_sub">
            Файли Word/PDF-ро бор кунед — ИИ хонда, асосиашро нақл мекунад (ҳатто 1000+ калима)
        </div>
    </div>

    {{-- 📁 Секция загрузки --}}
    <form id="anForm" class="form-section">
        @csrf
        <div class="form-section-head">
            <div class="form-section-icon">
                <i class="bi bi-cloud-arrow-up-fill"></i>
            </div>
            <div>
                <h3 class="form-section-title" data-i18n="section_upload">Боргузории файл</h3>
                <div class="form-section-sub" data-i18n="section_upload_sub">Word, PDF ё матнро интихоб кунед</div>
            </div>
        </div>

        <div class="drop-zone" id="dropZone">
            <input type="file" name="doc" id="anFile" accept=".docx,.pdf,.doc" required>
            <div class="drop-icon">
                <i class="bi bi-file-earmark-arrow-up-fill"></i>
            </div>
            <div class="drop-title" data-i18n="drop_title">Файлро интихоб кунед ё ҷойгир кунед</div>
            <div class="drop-sub">
                <span data-i18n="drop_sub1">Клик кунед барои </span><span class="hl" data-i18n="drop_sub2">интихоб</span>
            </div>
            <div class="drop-chips">
                <span class="format-chip word">DOCX</span>
                <span class="format-chip pdf">PDF</span>

            </div>
        </div>

        <div class="file-preview" id="filePreview" style="display:none">
            <div class="file-preview-icon" id="filePreviewIcon">
                <i class="bi bi-file-earmark-fill"></i>
            </div>
            <div class="file-preview-info">
                <div class="file-preview-name" id="filePreviewName">—</div>
                <div class="file-preview-meta">
                    <span id="filePreviewSize">0 KB</span>
                    <span class="dot"></span>
                    <span id="filePreviewType">—</span>
                </div>
            </div>
            <button type="button" class="file-preview-remove" id="fileRemoveBtn" title="Тоза кардан">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <button type="submit" class="submit-btn" id="anBtn">
            <span class="spinner"></span>
            <i class="bi bi-search" id="submitIcon"></i>
            <span id="submitText" data-i18n="submit_btn"> Таҳлил кардан</span>
        </button>

        <div class="empty-hint" id="emptyHint">
            <div class="empty-hint-icon">
                <i class="bi bi-lightbulb-fill"></i>
            </div>
            <div class="empty-hint-text" data-i18n="hint_text">
                <strong>AI</strong>
            </div>
        </div>
    </form>

    {{-- 🤖 Секция результата (скрыта до запроса) --}}
    <div class="result-section" id="resultSection" style="display:none">
        <div class="result-head">
            <div class="result-icon">
                <i class="bi bi-robot" id="resultIcon"></i>
            </div>
            <div>
                <h3 class="result-title" id="resultTitle" data-i18n="result_title">🤖 Разбор документа</h3>
                <div class="result-meta" id="resultMeta"></div>
            </div>
        </div>
        <div class="result-text" id="resultText"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const TRANSLATIONS = {
        tj: {
            back: 'Ба қафо',
            page_title: 'Таҳлили ҳуҷҷат',
            page_sub: 'Файли Word/PDF-ро бор кунед — ИИ хонда, асосиашро нақл мекунад (ҳатто 1000+ калима)',
            section_upload: 'Боргузории файл',
            section_upload_sub: 'Word, PDF ё матнро интихоб кунед',
            drop_title: 'Файлро интихоб кунед ё ҷойгир кунед',
            drop_sub1: 'Клик кунед барои ',
            drop_sub2: 'интихоб',
            submit_btn: ' Таҳлил кардан',
            analyzing: ' ИИ ҳуҷҷатро мехонад...',
            result_title: ' Натиҷаи таҳлил',
            result_error: '❌ Хатои таҳлил',
           unknown_error: 'Хатои номаълум',
            file_selected: 'Интихоб шуд'
        },
        ru: {
            back: 'Назад',
            page_title: 'Анализ документа',
            page_sub: 'Загрузите Word/PDF — ИИ прочитает и расскажет главное (даже 1000+ слов)',
            section_upload: 'Загрузка файла',
            section_upload_sub: 'Выберите Word, PDF или текстовый файл',
            drop_title: 'Выберите файл или перетащите',
            drop_sub1: 'Кликните для ',
            drop_sub2: 'выбора',
            submit_btn: ' Анализировать',
            analyzing: ' ИИ читает документ...',
            result_title: ' Результат анализа',
            result_error: '❌ Ошибка анализа',
          unknown_error: 'Неизвестная ошибка',
            file_selected: 'Выбран'
        },
        en: {
            back: 'Back',
            page_title: 'Document Analysis',
            page_sub: 'Upload Word/PDF — AI will read and summarize the main points (even 1000+ words)',
            section_upload: 'File upload',
            section_upload_sub: 'Choose Word, PDF or text file',
            drop_title: 'Choose a file or drop it here',
            drop_sub1: 'Click to ',
            drop_sub2: 'browse',
            submit_btn: ' Analyze',
            analyzing: ' AI is reading the document...',
            result_title: ' Analysis result',
            result_error: '❌ Analysis error',
           unknown_error: 'Unknown error',
            file_selected: 'Selected'
        }
    };

    function applyTranslations(lang) {
        const dict = TRANSLATIONS[lang] || TRANSLATIONS.tj;
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (dict[key] !== undefined) el.innerHTML = dict[key];
        });
        document.querySelectorAll('[data-i18n-title]').forEach(el => {
            const key = el.getAttribute('data-i18n-title');
            if (dict[key] !== undefined) el.setAttribute('title', dict[key]);
        });
    }

    let currentLang = localStorage.getItem('docsign_lang') || 'tj';
    applyTranslations(currentLang);

    window.addEventListener('docsign:lang-changed', (e) => {
        currentLang = e.detail?.lang || 'tj';
        applyTranslations(currentLang);
    });
    window.addEventListener('storage', (e) => {
        if (e.key === 'docsign_lang' && e.newValue) {
            currentLang = e.newValue;
            applyTranslations(currentLang);
        }
    });

    // === ФАЙЛ ===
    const anFile = document.getElementById('anFile');
    const dropZone = document.getElementById('dropZone');
    const filePreview = document.getElementById('filePreview');
    const filePreviewIcon = document.getElementById('filePreviewIcon');
    const filePreviewName = document.getElementById('filePreviewName');
    const filePreviewSize = document.getElementById('filePreviewSize');
    const filePreviewType = document.getElementById('filePreviewType');
    const fileRemoveBtn = document.getElementById('fileRemoveBtn');
    const emptyHint = document.getElementById('emptyHint');

    function formatSize(b) {
        if (b < 1024) return b + ' B';
        if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
        return (b / 1048576).toFixed(2) + ' MB';
    }

    function getFileType(name) {
        const ext = name.split('.').pop().toLowerCase();
        if (ext === 'pdf') return 'pdf';
        if (['doc', 'docx'].includes(ext)) return 'word';
        return 'txt';
    }

    function getFileIcon(type) {
        if (type === 'pdf') return '<i class="bi bi-file-earmark-pdf-fill"></i>';
        if (type === 'word') return '<i class="bi bi-file-earmark-word-fill"></i>';
        return '<i class="bi bi-file-earmark-text-fill"></i>';
    }

    function showPreview(file) {
        const type = getFileType(file.name);
        filePreviewIcon.className = 'file-preview-icon ' + type;
        filePreviewIcon.innerHTML = getFileIcon(type);
        filePreviewName.textContent = file.name;
        filePreviewSize.textContent = formatSize(file.size);
        filePreviewType.textContent = type.toUpperCase();
        filePreview.style.display = 'flex';
        dropZone.classList.add('has-file');
        emptyHint.style.display = 'none';
    }

    function clearFile() {
        anFile.value = '';
        filePreview.style.display = 'none';
        dropZone.classList.remove('has-file');
        emptyHint.style.display = 'flex';
    }

    anFile.addEventListener('change', function () {
        if (this.files && this.files[0]) showPreview(this.files[0]);
    });

    fileRemoveBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        clearFile();
    });

    ['dragenter', 'dragover'].forEach(ev => dropZone.addEventListener(ev, e => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    }));
    ['dragleave', 'drop'].forEach(ev => dropZone.addEventListener(ev, e => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
    }));
    dropZone.addEventListener('drop', e => {
        const f = e.dataTransfer.files[0];
        if (f) {
            const t = getFileType(f.name);
            if (!['pdf', 'word', 'txt'].includes(t)) {
                alert('⚠️ Танҳо PDF, Word ё TXT');
                return;
            }
            anFile.files = e.dataTransfer.files;
            showPreview(f);
        }
    });

    // === ОТПРАВКА ===
    const form = document.getElementById('anForm');
    const btn = document.getElementById('anBtn');
    const submitText = document.getElementById('submitText');
    const submitIcon = document.getElementById('submitIcon');
    const resultSection = document.getElementById('resultSection');
    const resultText = document.getElementById('resultText');
    const resultTitle = document.getElementById('resultTitle');
    const resultIcon = document.getElementById('resultIcon');
    const resultMeta = document.getElementById('resultMeta');

    function getDict() {
        return TRANSLATIONS[localStorage.getItem('docsign_lang') || 'tj'];
    }

    function setLoading(on, text) {
        btn.disabled = on;
        btn.classList.toggle('loading', on);
        submitText.textContent = text;
        submitIcon.style.display = on ? 'none' : 'inline-block';
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const dict = getDict();

        if (!anFile.files || !anFile.files[0]) {
            alert('⚠️ Файлро интихоб кунед');
            return;
        }

        setLoading(true, dict.analyzing);
        const fd = new FormData(this);

        try {
            const resp = await fetch('{{ route("contracts.analyze-upload") }}', {
                method: 'POST', body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await resp.json();

            resultSection.style.display = 'block';
            resultSection.classList.remove('error');

            if (data.success) {
                resultTitle.innerHTML = dict.result_title;
                resultIcon.className = '';
                resultIcon.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
                resultMeta.innerHTML = '<i class="bi bi-clock"></i> ' + new Date().toLocaleTimeString();
                resultText.textContent = data.summary;
            } else {
                resultSection.classList.add('error');
                resultTitle.innerHTML = dict.result_error;
                resultIcon.className = '';
                resultIcon.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i>';
                resultMeta.innerHTML = '';
                resultText.textContent = '❌ ' + (data.error || dict.unknown_error);
            }

            resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (err) {
            resultSection.style.display = 'block';
            resultSection.classList.add('error');
            resultTitle.innerHTML = dict.result_error;
            resultIcon.className = '';
            resultIcon.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i>';
            resultMeta.innerHTML = '';
            resultText.textContent = '❌ ' + err.message;
            resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        setLoading(false, dict.submit_btn);
    });
});
</script>
@endsection