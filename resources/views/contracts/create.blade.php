@extends('layouts.admin')

@section('content')
<style>
    /* === Fallback === */
    body { background: #06070b !important; color: #e7ecf3 !important; }

    /* === КОМПАКТНАЯ АНКЕТА === */
    .ai-form-page { 
        color: var(--text, #e7ecf3); 
        max-width: 760px; 
        width: 100%;
        margin-left: auto; 
        margin-right: auto;
        padding: 0 20px; /* отступы по краям */
    }

    .ai-form-page .page-head-custom {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .ai-form-page h1 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        letter-spacing: -0.4px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .ai-form-page h1 .accent-bar {
        width: 4px;
        height: 20px;
        background: rgba(var(--glow), 1);
        border-radius: 2px;
        box-shadow: 0 0 12px rgba(var(--glow), 0.8);
    }

    .ai-form-page .page-sub {
        color: var(--muted);
        font-size: 11.5px;
        margin-top: 2px;
        opacity: 0.75;
    }

    /* === АЛЕРТЫ (компактные) === */
    .ai-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 10px;
        margin-bottom: 10px;
        border: 1px solid;
        font-size: 12px;
    }
    .ai-alert-icon {
        width: 24px; height: 24px;
        border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; flex-shrink: 0;
    }
    .ai-alert.err { background: rgba(255,99,99,0.08); border-color: rgba(255,99,99,0.3); color: #ff9999; }
    .ai-alert.err .ai-alert-icon { background: rgba(255,99,99,0.15); color: #ff6b6b; }
    .ai-alert.warn { background: rgba(255,181,71,0.08); border-color: rgba(255,181,71,0.3); color: #ffd89a; }
    .ai-alert.warn .ai-alert-icon { background: rgba(255,181,71,0.15); color: #ffb547; }

    /* === СЕКЦИЯ-КИРПИЧИК === */
    .form-section {
        background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 10px;
        position: relative;
        overflow: hidden;
    }

    .form-section::before {
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

    .form-section-head {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--line);
    }

    .form-section-icon {
        width: 28px; height: 28px;
        border-radius: 8px;
        background: rgba(var(--glow), 0.1);
        border: 1px solid rgba(var(--glow), 0.25);
        display: flex; align-items: center; justify-content: center;
        font-size: 12px;
        color: rgba(var(--glow), 1);
        flex-shrink: 0;
    }

    .form-section-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
        margin: 0;
        line-height: 1.2;
    }

    .form-section-sub {
        font-size: 10px;
        color: var(--muted);
        opacity: 0.75;
        margin-top: 1px;
    }

    /* === ПОЛЯ: ОДИН СТОЛБИК, ПЛОТНО === */
    .form-stack {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .form-field label {
        display: block;
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 5px;
    }

    .form-field label .req { color: #ff6b6b; }

    .form-field input,
    .form-field select,
    .form-field textarea {
        width: 100%;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--line);
        border-radius: 8px;
        color: var(--text);
        padding: 8px 12px;
        font: 500 12.5px 'Inter', sans-serif;
        outline: none;
        transition: all 0.2s ease;
    }

    .form-field input::placeholder,
    .form-field textarea::placeholder { color: var(--muted); opacity: 0.5; }

    .form-field select {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%238892a6' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 32px;
    }

    .form-field select option { background: #1a1f2e; color: #fff; }

    .form-field textarea { resize: vertical; min-height: 64px; line-height: 1.5; }

    .form-field input:focus,
    .form-field select:focus,
    .form-field textarea:focus {
        border-color: rgba(var(--glow), 0.6);
        background: rgba(255,255,255,0.05);
        box-shadow: 0 0 0 2px rgba(var(--glow), 0.12);
    }

    .form-field-hint {
        display: block;
        font-size: 10px;
        color: var(--muted);
        opacity: 0.75;
        margin-top: 5px;
    }
    .form-field-hint i { color: rgba(var(--glow), 1); margin-right: 4px; }

    /* === ФОРМАТ: МАЛЕНЬКИЕ ПИЛЛЫ (не на всю ширину) === */
    .format-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .format-row .row-label {
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: var(--muted);
        margin-right: 4px;
    }

    .format-pill { position: relative; }

    .format-pill input { position: absolute; opacity: 0; pointer-events: none; }

    .format-pill label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 600;
        color: var(--muted);
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--line);
        cursor: pointer;
        transition: all 0.2s ease;
        margin: 0;
        text-transform: none;
        letter-spacing: 0;
    }

    .format-pill label i { font-size: 12px; }

    .format-pill label:hover { color: var(--text); }

    .format-pill input:checked + label.fmt-word {
        color: #f5c542;
        background: rgba(245,197,66,0.1);
        border-color: rgba(245,197,66,0.4);
        box-shadow: 0 0 10px rgba(245,197,66,0.15);
    }

    .format-pill input:checked + label.fmt-pdf {
        color: #ff6b6b;
        background: rgba(255,107,107,0.1);
        border-color: rgba(255,107,107,0.4);
        box-shadow: 0 0 10px rgba(255,107,107,0.15);
    }

    /* === ФАЙЛ: КОМПАКТНАЯ СТРОКА === */
    .file-drop-zone {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        border: 1.5px dashed var(--line);
        border-radius: 10px;
        background: rgba(255,255,255,0.02);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .file-drop-zone:hover,
    .file-drop-zone.dragover {
        border-color: rgba(var(--glow), 0.5);
        background: rgba(var(--glow), 0.05);
    }

    .file-drop-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }

    .file-drop-icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        background: rgba(var(--glow), 0.1);
        border: 1px solid rgba(var(--glow), 0.25);
        display: flex; align-items: center; justify-content: center;
        color: rgba(var(--glow), 1);
        font-size: 14px;
        flex-shrink: 0;
    }

    .file-drop-text { flex: 1; min-width: 0; }

    .file-drop-title {
        font-size: 12px;
        font-weight: 600;
        color: var(--text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .file-drop-sub { font-size: 10px; color: var(--muted); opacity: 0.75; }

    .file-drop-chips { display: flex; gap: 4px; flex-shrink: 0; }

    .format-chip {
        font-family: 'JetBrains Mono', monospace;
        font-size: 8.5px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 4px;
        border: 1px solid;
    }
    .format-chip.pdf { color: #ff6b6b; background: rgba(255,107,107,0.1); border-color: rgba(255,107,107,0.3); }
    .format-chip.word { color: #f5c542; background: rgba(245,197,66,0.1); border-color: rgba(245,197,66,0.3); }

    /* Превью файла */
    .file-preview {
        display: none;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background: rgba(var(--glow), 0.06);
        border: 1px solid rgba(var(--glow), 0.2);
        border-radius: 8px;
    }
    .file-preview.active { display: flex; }

    .file-preview-icon {
        width: 28px; height: 28px;
        border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; flex-shrink: 0;
    }
    .file-preview-icon.pdf { background: rgba(255,107,107,0.15); color: #ff6b6b; }
    .file-preview-icon.word { background: rgba(245,197,66,0.15); color: #f5c542; }

    .file-preview-info { flex: 1; min-width: 0; }
    .file-preview-name {
        font-size: 11.5px; font-weight: 600; color: var(--text);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .file-preview-size { font-size: 9.5px; color: var(--muted); }

    .file-preview-remove {
        background: none; border: none;
        width: 24px; height: 24px;
        border-radius: 6px;
        color: var(--muted);
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s;
    }
    .file-preview-remove:hover { color: #ff6b6b; background: rgba(255,99,99,0.1); }

    /* === КНОПКА === */
    .submit-btn {
        width: 100%;
        margin-top: 14px;
        padding: 12px 20px;
        font: 700 13px 'Inter', sans-serif;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        color: #0a0d14;
        background: linear-gradient(180deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.65));
        box-shadow: 0 6px 20px rgba(var(--glow), 0.35), inset 0 1px 0 rgba(255,255,255,0.3);
        transition: all 0.25s ease;
    }
    .submit-btn:hover:not(:disabled) {
        filter: brightness(1.08);
        transform: translateY(-1px);
    }
    .submit-btn:disabled { opacity: 0.6; cursor: wait; }

    .submit-btn .spinner {
        display: none;
        width: 14px; height: 14px;
        border: 2px solid rgba(10,13,20,0.3);
        border-top-color: #0a0d14;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    .submit-btn.loading .spinner { display: inline-block; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Вопросы ИИ */
    #questionsBox { display: none; }
    #questionsBox.active { display: block; animation: slideIn 0.3s ease; }
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .ai-sample-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 5px;
        font-size: 9px;
        font-weight: 600;
        color: #4cd982;
        background: rgba(76,217,130,0.1);
        border: 1px solid rgba(76,217,130,0.25);
        margin-left: 8px;
    }

    /* === АДАПТИВ === */
    @media (max-width: 768px) {
        .ai-form-page h1 { font-size: 18px; }
        .form-section { padding: 12px 14px; }
        .form-section-icon { width: 26px; height: 26px; font-size: 11px; }
        .form-section-title { font-size: 12.5px; }
        .format-pill label { padding: 5px 12px; font-size: 10.5px; }
    }

    @media (max-width: 576px) {
        .ai-form-page h1 { font-size: 17px; gap: 8px; }
        .ai-form-page h1 .accent-bar { width: 3px; height: 17px; }
        .ai-form-page .page-sub { font-size: 10.5px; }
        .form-section { padding: 11px 12px; margin-bottom: 8px; border-radius: 10px; }
        .form-section-head { margin-bottom: 10px; padding-bottom: 8px; gap: 8px; }
        .form-section-icon { width: 24px; height: 24px; font-size: 10px; border-radius: 7px; }
        .form-section-title { font-size: 12px; }
        .form-section-sub { font-size: 9.5px; }
        .form-stack { gap: 8px; }
        .form-field label { font-size: 9.5px; margin-bottom: 4px; }
        .form-field input, .form-field select, .form-field textarea { padding: 7px 10px; font-size: 12px; }
        .file-drop-zone { padding: 9px 12px; gap: 10px; }
        .file-drop-icon { width: 28px; height: 28px; font-size: 12px; }
        .file-drop-title { font-size: 11px; }
        .file-drop-sub { font-size: 9.5px; }
        .submit-btn { padding: 11px 16px; font-size: 12.5px; margin-top: 12px; }
    }

    @media (max-width: 480px) {
        .ai-form-page h1 { font-size: 16px; }
        .form-section { padding: 10px 11px; }
        .form-field input, .form-field select, .form-field textarea { padding: 7px 9px; font-size: 11.5px; }
        .format-pill label { padding: 5px 10px; font-size: 10px; gap: 4px; }
        .file-drop-chips { display: none; }
        .submit-btn { padding: 10px 14px; font-size: 12px; }
    }

    @media (max-width: 380px) {
        .ai-form-page h1 { font-size: 15px; }
        .form-section { padding: 9px 10px; }
        .form-section-title { font-size: 11.5px; }
        .form-field input, .form-field select, .form-field textarea { font-size: 11px; }
        .file-drop-title { font-size: 10.5px; }
        .submit-btn { font-size: 11.5px; }
    }
</style>

<div class="ai-form-page">
    <div class="page-head-custom">
        <div>
            <h1>
                <span class="accent-bar"></span>
                <span data-i18n="page_title">Эҷоди ҳуҷҷат</span>
            </h1>
            <div class="page-sub" data-i18n="page_sub">Анкетаро пур кунед — ИИ ҳуҷҷати шуморо менависад</div>
        </div>
    </div>

    @if(session('error'))
    <div class="ai-alert err">
        <div class="ai-alert-icon"><i class="bi bi-exclamation-octagon-fill"></i></div>
        <div>{{ session('error') }}</div>
    </div>
    @endif

    @if($errors->any())
    <div class="ai-alert warn">
        <div class="ai-alert-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div>@foreach($errors->all() as $e)• {{ $e }}<br>@endforeach</div>
    </div>
    @endif

    <form action="{{ route('contracts.generate') }}" method="POST" id="contractForm" enctype="multipart/form-data">
        @csrf

        {{-- 1. Навъи ҳуҷҷат --}}
        <div class="form-section">
            <div class="form-section-head">
                <div class="form-section-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
                <div>
                    <h3 class="form-section-title" data-i18n="section_docType">Навъи ҳуҷҷат</h3>
                    <div class="form-section-sub" data-i18n="section_docType_sub">Кадом ҳуҷҷат лозим аст</div>
                </div>
            </div>
            <div class="form-stack">
                <div class="form-field">
                    <label data-i18n="label_docType">Кадом ҳуҷҷат лозим? <span class="req">*</span></label>
                    <select name="doc_type" id="docType">
                        <option value="Ариза">Ариза</option>
                        <option value="Заявление">Заявление</option>
                        <option value="Рецензия">Рецензия (тақриз)</option>
                        <option value="Доверенность">Ваколатнома (доверенность)</option>
                        <option value="Договор">Шартнома (договор)</option>
                        <option value="Приказ">Фармон (приказ)</option>
                        <option value="Протокол">Протокол</option>
                        <option value="Другой">Дигар...</option>
                    </select>
                </div>
                <div class="form-field" id="customTypeBox" style="display:none">
                    <label data-i18n="label_docType_custom">Навъро нависед</label>
                    <input type="text" name="doc_type_custom" placeholder="Масалан: ходатайство">
                </div>
                <div class="form-field">
                    <label data-i18n="label_city">Шаҳр</label>
                    <!-- ИЗМЕНЕНО: убран конкретный город, добавлен нейтральный пример -->
                    <input type="text" name="city" value="{{ old('city') }}" placeholder="Масалан: Душанбе">
                </div>
            </div>
        </div>

        {{-- 2. Аз кӣ --}}
        <div class="form-section">
            <div class="form-section-head">
                <div class="form-section-icon"><i class="bi bi-person-fill"></i></div>
                <div>
                    <h3 class="form-section-title" data-i18n="section_from">Аз кӣ (аризадиҳанда)</h3>
                    <div class="form-section-sub" data-i18n="section_from_sub">Маълумоти худро ворид кунед</div>
                </div>
            </div>
            <div class="form-stack">
                <div class="form-field">
                    <label data-i18n="label_from_whom">Ному насаб пурра <span class="req">*</span></label>
                    <!-- ИЗМЕНЕНО: убрано твое имя, добавлен нейтральный пример "Иванов Иван Иванович" -->
                    <input type="text" name="from_whom" value="{{ old('from_whom') }}" placeholder="Масалан: Иванов Иван Иванович" required>
                </div>
                <div class="form-field">
                    <label data-i18n="label_from_details">Тамос (суроға, телефон)</label>
                    <!-- ИЗМЕНЕНО: убран твой реальный адрес и телефон, добавлен вымышленный -->
                    <input type="text" name="from_details" value="{{ old('from_details') }}" placeholder="Масалан: ш. Душанбе, кӯчаи Сино 10, тел: +992 900 00 00 00">
                </div>
            </div>
        </div>

        {{-- 3. Ба кӣ --}}
        <div class="form-section">
            <div class="form-section-head">
                <div class="form-section-icon"><i class="bi bi-building-fill"></i></div>
                <div>
                    <h3 class="form-section-title" data-i18n="section_to">Ба кӣ (суроғат)</h3>
                    <div class="form-section-sub" data-i18n="section_to_sub">Касе, ки ҳуҷҷат ба ӯ меравад</div>
                </div>
            </div>
            <div class="form-stack">
                <div class="form-field">
                    <label data-i18n="label_to_whom">Ташкилот ё шахси мансабдор <span class="req">*</span></label>
                    <input type="text" name="to_whom" value="{{ old('to_whom') }}" placeholder="Ба директори мактаби №1" required>
                </div>
            </div>
        </div>

        {{-- 4. Моҳият --}}
        <div class="form-section">
            <div class="form-section-head">
                <div class="form-section-icon"><i class="bi bi-pencil-fill"></i></div>
                <div>
                    <h3 class="form-section-title" data-i18n="section_subject">Моҳияти муроҷиат</h3>
                    <div class="form-section-sub" data-i18n="section_subject_sub">Чӣ дархост доред?</div>
                </div>
            </div>
            <div class="form-stack">
                <div class="form-field">
                    <label data-i18n="label_subject">Бо забони худ нависед <span class="req">*</span></label>
                    <textarea name="subject" rows="3" required placeholder="Масалан: ман мехоҳам ба мактаб қабул шавам...">{{ old('subject') }}</textarea>
                    <small class="form-field-hint">
                        <i class="bi bi-stars"></i>
                        <span data-i18n="hint_subject">ИИ намунаро ёфта, ҳуҷҷатро аз рӯи вазъияти шумо менависад</span>
                    </small>
                </div>
                <div class="form-field">
                    <label data-i18n="label_extra">Иловагӣ (ихтиёрӣ)</label>
                    <textarea name="extra" rows="2">{{ old('extra') }}</textarea>
                </div>
            </div>
        </div>

        {{-- 5. Формат + файл (компактно в один кирпичик) --}}
        <div class="form-section">
            <div class="form-section-head">
                <div class="form-section-icon"><i class="bi bi-gear-fill"></i></div>
                <div>
                    <h3 class="form-section-title" data-i18n="section_format">Формат ва файл</h3>
                    <div class="form-section-sub" data-i18n="section_format_sub">Навъи файл ва замима</div>
                </div>
            </div>
            <div class="form-stack">

                {{-- Формат: маленькие пиллы --}}
                <div class="format-row">
                    <span class="row-label" data-i18n="label_format">Формат:</span>
                    <div class="format-pill">
                        <input type="radio" name="output_format" id="format_word" value="word" checked>
                        <label for="format_word" class="fmt-word">
                            <i class="bi bi-file-earmark-word-fill" style="color:#f5c542"></i>
                            Word
                        </label>
                    </div>
                    <div class="format-pill">
                        <input type="radio" name="output_format" id="format_pdf" value="pdf">
                        <label for="format_pdf" class="fmt-pdf">
                            <i class="bi bi-file-earmark-pdf-fill" style="color:#ff6b6b"></i>
                            PDF
                        </label>
                    </div>
                </div>

                {{-- Файл: компактная строка --}}
                <div class="form-field">
                    <label data-i18n="label_attachment">Замима (ихтиёрӣ)</label>

                    <div class="file-drop-zone" id="fileDropZone">
                        <input type="file" name="attachment" id="fileInput"
                               accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                        <div class="file-drop-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                        <div class="file-drop-text">
                            <div class="file-drop-title" data-i18n="drop_title">Файлро интихоб кунед ё ҷойгир кунед</div>
                            <div class="file-drop-sub" data-i18n="drop_sub">PDF ё Word — то 10 МБ</div>
                        </div>
                        <div class="file-drop-chips">
                            <span class="format-chip pdf">PDF</span>
                            <span class="format-chip word">DOCX</span>
                        </div>
                    </div>

                    <div class="file-preview" id="filePreview" style="margin-top:8px">
                        <div class="file-preview-icon" id="filePreviewIcon"><i class="bi bi-file-earmark-fill"></i></div>
                        <div class="file-preview-info">
                            <div class="file-preview-name" id="filePreviewName">file.pdf</div>
                            <div class="file-preview-size" id="filePreviewSize">0 KB</div>
                        </div>
                        <button type="button" class="file-preview-remove" id="fileRemoveBtn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Вопросы ИИ --}}
        <div id="questionsBox"></div>

        <button type="submit" class="submit-btn" id="submitBtn">
            <span class="spinner"></span>
            <i class="bi bi-rocket-takeoff-fill" id="submitIcon"></i>
            <span id="submitText" data-i18n="submit_btn">🚀 Эҷоди ҳуҷҷат</span>
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const AI_TRANSLATIONS = {
        ru: {
            page_title: 'Создание документа',
            page_sub: 'Заполните анкету — ИИ напишет ваш документ',
            section_docType: 'Тип документа', section_docType_sub: 'Какой документ нужен',
            section_from: 'От кого (заявитель)', section_from_sub: 'Введите свои данные',
            section_to: 'Кому (адресат)', section_to_sub: 'Кому адресован документ',
            section_subject: 'Суть обращения', section_subject_sub: 'Какая у вас просьба',
            section_format: 'Формат и файл', section_format_sub: 'Тип файла и приложение',
            label_docType: 'Какой документ нужен?', label_docType_custom: 'Введите тип',
            label_city: 'Город', label_from_whom: 'ФИО полностью',
            label_from_details: 'Контакты (адрес, телефон)', label_to_whom: 'Организация или должностное лицо',
            label_subject: 'Опишите своими словами', label_extra: 'Дополнительно (необязательно)',
            label_format: 'Формат:', label_attachment: 'Приложение (необязательно)',
            hint_subject: 'ИИ найдёт образец и напишет документ по вашей ситуации',
            drop_title: 'Выберите файл или перетащите', drop_sub: 'PDF или Word — до 10 МБ',
            submit_btn: '🚀 Создать документ', analyzing: '🧠 ИИ анализирует...',
            generating: '⏳ ИИ пишет документ...', ready: '✅ Готово!',
            questions_title: '❓ Уточняющие вопросы ИИ', sample_found: 'образец найден',
            submit_ready: '✅ Готово! Создать документ'
        },
        tj: {
            page_title: 'Эҷоди ҳуҷҷат',
            page_sub: 'Анкетаро пур кунед — ИИ ҳуҷҷати шуморо менависад',
            section_docType: 'Навъи ҳуҷҷат', section_docType_sub: 'Кадом ҳуҷҷат лозим аст',
            section_from: 'Аз кӣ (аризадиҳанда)', section_from_sub: 'Маълумоти худро ворид кунед',
            section_to: 'Ба кӣ (суроғат)', section_to_sub: 'Касе, ки ҳуҷҷат ба ӯ меравад',
            section_subject: 'Моҳияти муроҷиат', section_subject_sub: 'Чӣ дархост доред',
            section_format: 'Формат ва файл', section_format_sub: 'Навъи файл ва замима',
            label_docType: 'Кадом ҳуҷҷат лозим?', label_docType_custom: 'Навъро нависед',
            label_city: 'Шаҳр', label_from_whom: 'Ному насаб пурра',
            label_from_details: 'Тамос (суроға, телефон)', label_to_whom: 'Ташкилот ё шахси мансабдор',
            label_subject: 'Бо забони худ нависед', label_extra: 'Иловагӣ (ихтиёрӣ)',
            label_format: 'Формат:', label_attachment: 'Замима (ихтиёрӣ)',
            hint_subject: 'ИИ намунаро ёфта, ҳуҷҷатро аз рӯи вазъияти шумо менависад',
            drop_title: 'Файлро интихоб кунед ё ҷойгир кунед', drop_sub: 'PDF ё Word — то 10 МБ',
            submit_btn: '🚀 Эҷоди ҳуҷҷат', analyzing: '🧠 ИИ анкетаро таҳлил мекунад...',
            generating: '⏳ ИИ ҳуҷҷат менависад...', ready: '✅ Тайёр!',
            questions_title: '❓ Саволҳои аниқкунандаи ИИ', sample_found: 'намуна ёфт шуд',
            submit_ready: '✅ Тайёр! Эҷоди ҳуҷҷат'
        },
        en: {
            page_title: 'Create Document',
            page_sub: 'Fill the form — AI will write your document',
            section_docType: 'Document type', section_docType_sub: 'Which document you need',
            section_from: 'From (applicant)', section_from_sub: 'Enter your details',
            section_to: 'To (recipient)', section_to_sub: 'Who receives the document',
            section_subject: 'Subject', section_subject_sub: 'What is your request',
            section_format: 'Format & file', section_format_sub: 'File type and attachment',
            label_docType: 'Which document?', label_docType_custom: 'Enter type',
            label_city: 'City', label_from_whom: 'Full name',
            label_from_details: 'Contact (address, phone)', label_to_whom: 'Organization or official',
            label_subject: 'Describe in your words', label_extra: 'Additional (optional)',
            label_format: 'Format:', label_attachment: 'Attachment (optional)',
            hint_subject: 'AI will find a sample and write the document for your situation',
            drop_title: 'Choose or drop a file', drop_sub: 'PDF or Word — up to 10 MB',
            submit_btn: '🚀 Create document', analyzing: '🧠 AI analyzing...',
            generating: '⏳ AI writing...', ready: '✅ Done!',
            questions_title: '❓ AI follow-up questions', sample_found: 'sample found',
            submit_ready: '✅ Done! Create document'
        }
    };

    function applyTranslations(lang) {
        const dict = AI_TRANSLATIONS[lang] || AI_TRANSLATIONS.tj;
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (dict[key] !== undefined) el.innerHTML = dict[key];
        });
    }

    const initialLang = localStorage.getItem('docsign_lang') || 'tj';
    applyTranslations(initialLang);

    window.addEventListener('docsign:lang-changed', (e) => applyTranslations(e.detail?.lang || 'tj'));
    window.addEventListener('storage', (e) => {
        if (e.key === 'docsign_lang' && e.newValue) applyTranslations(e.newValue);
    });

    // Смена типа
    document.getElementById('docType').addEventListener('change', function () {
        document.getElementById('customTypeBox').style.display = (this.value === 'Другой') ? 'block' : 'none';
    });

    // Файл
    const fileInput = document.getElementById('fileInput');
    const fileDropZone = document.getElementById('fileDropZone');
    const filePreview = document.getElementById('filePreview');
    const filePreviewIcon = document.getElementById('filePreviewIcon');
    const filePreviewName = document.getElementById('filePreviewName');
    const filePreviewSize = document.getElementById('filePreviewSize');

    function formatSize(b) {
        if (b < 1024) return b + ' B';
        if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
        return (b / 1048576).toFixed(2) + ' MB';
    }

    function fileType(name) {
        const ext = name.split('.').pop().toLowerCase();
        return ext === 'pdf' ? 'pdf' : (['doc', 'docx'].includes(ext) ? 'word' : 'other');
    }

    function showPreview(file) {
        const t = fileType(file.name);
        filePreviewIcon.className = 'file-preview-icon ' + t;
        filePreviewIcon.innerHTML = t === 'pdf' ? '<i class="bi bi-file-earmark-pdf-fill"></i>' : '<i class="bi bi-file-earmark-word-fill"></i>';
        filePreviewName.textContent = file.name;
        filePreviewSize.textContent = formatSize(file.size);
        filePreview.classList.add('active');
        fileDropZone.style.display = 'none';
    }

    function clearFile() {
        fileInput.value = '';
        filePreview.classList.remove('active');
        fileDropZone.style.display = 'flex';
    }

    fileInput.addEventListener('change', function () {
        if (this.files && this.files[0]) showPreview(this.files[0]);
    });

    document.getElementById('fileRemoveBtn').addEventListener('click', clearFile);

    ['dragenter', 'dragover'].forEach(ev => fileDropZone.addEventListener(ev, e => {
        e.preventDefault(); fileDropZone.classList.add('dragover');
    }));
    ['dragleave', 'drop'].forEach(ev => fileDropZone.addEventListener(ev, e => {
        e.preventDefault(); fileDropZone.classList.remove('dragover');
    }));
    fileDropZone.addEventListener('drop', e => {
        const f = e.dataTransfer.files[0];
        if (f) {
            const t = fileType(f.name);
            if (t !== 'pdf' && t !== 'word') { alert('⚠️ Танҳо PDF ва Word'); return; }
            fileInput.files = e.dataTransfer.files;
            showPreview(f);
        }
    });

    // Отправка
    const form = document.getElementById('contractForm');
    const btn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitIcon = document.getElementById('submitIcon');
    let stage = 1;

    function getDict() {
        return AI_TRANSLATIONS[localStorage.getItem('docsign_lang') || 'tj'];
    }

    function setLoading(on, text) {
        btn.disabled = on;
        btn.classList.toggle('loading', on);
        submitText.textContent = text;
        submitIcon.style.display = on ? 'none' : 'inline-block';
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const fd = new FormData(form);
        const dict = getDict();

        if (stage === 1) {
            setLoading(true, dict.analyzing);
            try {
                const resp = await fetch('{{ route("contracts.analyze-ajax") }}', {
                    method: 'POST', body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const data = await resp.json();

                if (data.success && data.questions && data.questions.length > 0) {
                    let html = '<div class="form-section"><div class="form-section-head">' +
                        '<div class="form-section-icon"><i class="bi bi-question-circle-fill"></i></div>' +
                        '<div><h3 class="form-section-title">' + dict.questions_title +
                        (data.sample ? '<span class="ai-sample-badge"><i class="bi bi-check-circle-fill"></i>' + dict.sample_found + ': ' + data.sample + '</span>' : '') +
                        '</h3></div></div><div class="form-stack">';
                    data.questions.forEach(q => {
                        html += '<div class="form-field"><label>' + q + '</label><input type="text" name="answers[]"></div>';
                    });
                    html += '</div></div>';
                    const box = document.getElementById('questionsBox');
                    box.innerHTML = html;
                    box.classList.add('active');
                    box.scrollIntoView({ behavior: 'smooth' });
                    stage = 2;
                    setLoading(false, dict.submit_ready);
                    return;
                }
                await doGenerate(fd);
            } catch (err) {
                alert('❌ Хато: ' + err.message);
                setLoading(false, dict.submit_btn);
            }
            return;
        }
        await doGenerate(fd);
    });

    async function doGenerate(fd) {
        const dict = getDict();
        setLoading(true, dict.generating);
        try {
            const resp = await fetch('{{ route("contracts.generate-ajax") }}', {
                method: 'POST', body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const text = await resp.text();
            let data;
            try { data = JSON.parse(text); }
            catch (err) {
                alert('❌ Хатои сервер (статус ' + resp.status + ')');
                console.error(text.substring(0, 800));
                setLoading(false, dict.submit_btn);
                return;
            }
            if (data.success && data.redirect) {
                setLoading(false, dict.ready);
                setTimeout(() => window.location.href = data.redirect, 600);
            } else {
                let msg = data.error || 'Хатои номаълум';
                if (data.errors) {
                    msg = 'Майдонҳоро пур кунед:\n';
                    for (let f in data.errors) msg += '• ' + data.errors[f].join(', ') + '\n';
                }
                alert('❌ ' + msg);
                setLoading(false, dict.submit_btn);
            }
        } catch (err) {
            alert('❌ Хатои шабака: ' + err.message);
            setLoading(false, dict.submit_btn);
        }
    }
});
</script>
@endsection