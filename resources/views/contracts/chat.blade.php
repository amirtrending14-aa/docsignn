@extends('layouts.admin')

@section('content')
<style>
    /* ============================================================ */
    /* ===== ФОН ПО ТЕМЕ ===== */
    /* ============================================================ */
    html[data-theme="dark"] body{ background:#06070b !important; color:#e7ecf3 !important; }
    html[data-theme="light"] body{ background:#f4f6fa !important; color:#1e293b !important; }

    /* === КОНТЕЙНЕР === */
    .rag-wrap {
        max-width: 820px;
        margin: 0 auto;
        position: relative;
        color: var(--text);
    }

    /* Декоративное свечение (золотое — фирменный цвет RAG) */
    .rag-wrap::before {
        content: "";
        position: absolute;
        top: -40px;
        left: 50%;
        transform: translateX(-50%);
        width: 400px;
        height: 200px;
        background: radial-gradient(ellipse at center, rgba(245,197,66,0.12), transparent 70%);
        pointer-events: none;
        z-index: -1;
        filter: blur(10px);
    }

    /* === КНОПКА НАЗАД === */
    .rag-topbar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }
    .rag-back-btn {
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
    .rag-back-btn:hover {
        color: #f5c542;
        border-color: rgba(245,197,66,0.4);
        background: rgba(245,197,66,0.08);
        box-shadow: 0 0 12px rgba(245,197,66,0.2);
        transform: translateX(-3px);
    }
    .rag-back-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--muted);
    }

    /* === ЦЕНТРИРОВАННАЯ ШАПКА === */
    .rag-head-center {
        text-align: center;
        margin-bottom: 28px;
    }
    .rag-head-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 16px;
        border-radius: 18px;
        background: rgba(245,197,66,0.10);
        border: 1px solid rgba(245,197,66,0.30);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: #f5c542;
        box-shadow: 0 0 24px rgba(245,197,66,0.25), inset 0 1px 0 rgba(255,255,255,0.1);
        animation: iconFloat 3s ease-in-out infinite;
    }
    @keyframes iconFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }
    .rag-wrap h1 {
        margin: 0;
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -0.4px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        justify-content: center;
        color: var(--text);
    }
    .rag-wrap h1 .accent-bar {
        width: 4px;
        height: 24px;
        background: #f5c542;
        border-radius: 2px;
        box-shadow: 0 0 12px rgba(245,197,66,0.8);
    }
    .rag-page-sub {
        color: var(--muted);
        font-size: 13px;
        margin-top: 8px;
        opacity: 0.85;
        line-height: 1.5;
        max-width: 560px;
        margin-left: auto;
        margin-right: auto;
    }

    /* === КАРТОЧКИ === */
    .rag-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
        border: 1px solid var(--line);
        border-radius: 14px;
        margin-bottom: 16px;
        overflow: hidden;
        position: relative;
        animation: fadeUp 0.45s ease both;
    }
    .rag-card::before {
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: 14px;
        padding: 1px;
        background: linear-gradient(135deg, rgba(245,197,66,0.35), transparent 40%, transparent 60%, rgba(245,197,66,0.2));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
        opacity: 0.6;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .rag-card-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-bottom: 1px solid var(--line);
    }
    .rag-card-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: rgba(245,197,66,0.10);
        border: 1px solid rgba(245,197,66,0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: #f5c542;
        flex-shrink: 0;
        box-shadow: 0 0 12px rgba(245,197,66,0.15);
    }
    .rag-card-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
        margin: 0;
        line-height: 1.2;
    }
    .rag-card-sub {
        font-size: 11px;
        color: var(--muted);
        opacity: 0.8;
        margin-top: 2px;
    }

    .rag-body {
        padding: 18px;
        display: grid;
        gap: 14px;
    }

    /* === ПОЛЯ === */
    .rag-field label {
        display: block;
        font-size: 11.5px;
        font-weight: 600;
        color: var(--muted);
        margin-bottom: 6px;
        letter-spacing: 0.2px;
    }
    .rag-field input,
    .rag-field select {
        width: 100%;
        background: rgba(255,255,255,0.02);
        border: 1px solid var(--line);
        border-radius: 8px;
        color: var(--text);
        padding: 10px 12px;
        font-size: 13.5px;
        font-family: 'Inter', sans-serif;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }
    .rag-field select {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238892a6' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 14px;
        padding-right: 36px;
        cursor: pointer;
    }
    .rag-field input:focus,
    .rag-field select:focus {
        border-color: rgba(245,197,66,0.6);
        background: rgba(245,197,66,0.04);
        box-shadow: 0 0 0 3px rgba(245,197,66,0.12);
    }
    .rag-field input::placeholder { color: var(--muted); opacity: 0.7; }

    /* === КНОПКА === */
    .rag-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 14px 24px;
        font: 700 14px 'Inter', sans-serif;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        color: #1a1000;
        background: linear-gradient(180deg, rgba(245,197,66,0.95), rgba(224,169,44,0.90));
        box-shadow: 0 8px 24px rgba(245,197,66,0.35), inset 0 1px 0 rgba(255,255,255,0.3);
        transition: all 0.25s ease;
        letter-spacing: 0.2px;
        position: relative;
    }
    .rag-btn:hover:not(:disabled) {
        filter: brightness(1.08);
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(245,197,66,0.45), inset 0 1px 0 rgba(255,255,255,0.4);
    }
    .rag-btn:disabled { opacity: 0.65; cursor: wait; }
    .rag-btn .spinner {
        display: none;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(26,16,0,0.3);
        border-top-color: #1a1000;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    .rag-btn.loading .spinner { display: inline-block; }
    .rag-btn.loading .rag-btn-icon { display: none; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* === БЛОК ОТВЕТА === */
    .answer-card { animation: resultIn 0.4s ease; }
    .answer-card .rag-card-icon {
        background: rgba(77,208,225,0.10);
        border-color: rgba(77,208,225,0.30);
        color: #4dd0e1;
        box-shadow: 0 0 12px rgba(77,208,225,0.15);
    }
    @keyframes resultIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .answer-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        background: rgba(77,208,225,0.06);
        border: 1px solid rgba(77,208,225,0.20);
        border-radius: 9px;
        margin-bottom: 14px;
        font-size: 11.5px;
        color: var(--muted);
    }
    .answer-meta i { color: #4dd0e1; }
    .answer-meta .doc-name {
        color: var(--text);
        font-weight: 600;
    }
    .answer-meta .dot {
        width: 3px; height: 3px; border-radius: 50%;
        background: var(--muted); opacity: 0.6;
    }

    #chatText {
        white-space: pre-wrap;
        word-break: break-word;
        color: var(--text);
        line-height: 1.75;
        font-size: 13.5px;
        font-family: 'Inter', sans-serif;
        background: rgba(255,255,255,0.02);
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 14px 16px;
    }
    #chatText.error {
        background: rgba(255,99,99,0.06);
        border-color: rgba(255,99,99,0.25);
        color: #ff8787;
    }
    #chatText strong, #chatText b {
        color: #4dd0e1;
        font-weight: 700;
    }

    /* === ПУСТОЕ СОСТОЯНИЕ === */
    .rag-empty-state {
        text-align: center;
        padding: 32px 20px;
    }
    .rag-empty-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 16px;
        border-radius: 18px;
        background: rgba(136,146,166,0.08);
        border: 1px dashed var(--line);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: var(--muted);
    }
    .rag-empty-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 6px;
    }
    .rag-empty-text {
        font-size: 12.5px;
        color: var(--muted);
        line-height: 1.5;
        margin-bottom: 16px;
    }
    .rag-empty-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: rgba(var(--glow), 0.08);
        border: 1px solid rgba(var(--glow), 0.25);
        border-radius: 8px;
        color: rgb(var(--glow));
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .rag-empty-link:hover {
        background: rgba(var(--glow), 0.15);
        border-color: rgba(var(--glow), 0.45);
        transform: translateY(-1px);
    }

    /* ============================================================ */
    /* ===== СВЕТЛАЯ ТЕМА ===== */
    /* ============================================================ */
    html[data-theme="light"] .rag-wrap { color: #0f172a; }
    html[data-theme="light"] .rag-wrap::before { opacity: 0.5; }

    html[data-theme="light"] .rag-wrap h1,
    html[data-theme="light"] .rag-card-title,
    html[data-theme="light"] .rag-empty-title {
        color: #0f172a !important;
    }
    html[data-theme="light"] .rag-page-sub,
    html[data-theme="light"] .rag-back-label,
    html[data-theme="light"] .rag-card-sub,
    html[data-theme="light"] .rag-field label,
    html[data-theme="light"] .rag-empty-text,
    html[data-theme="light"] .answer-meta {
        color: #475569 !important;
    }

    html[data-theme="light"] .rag-back-btn {
        background: #ffffff;
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06);
    }
    html[data-theme="light"] .rag-back-btn:hover {
        color: #b45309;
        border-color: rgba(180,83,9,0.45);
        background: rgba(245,197,66,0.12);
        box-shadow: 0 4px 14px rgba(180,83,9,0.18);
    }

    html[data-theme="light"] .rag-wrap h1 .accent-bar {
        background: #b45309;
        box-shadow: 0 0 10px rgba(180,83,9,0.5);
    }

    html[data-theme="light"] .rag-head-icon {
        background: rgba(180,83,9,0.10);
        border-color: rgba(180,83,9,0.35);
        color: #b45309;
        box-shadow: 0 6px 18px rgba(180,83,9,0.18), inset 0 1px 0 rgba(255,255,255,0.6);
    }

    html[data-theme="light"] .rag-card {
        background: #ffffff;
        border-color: #cbd5e1 !important;
        box-shadow: 0 8px 24px rgba(15,23,42,0.07);
    }
    html[data-theme="light"] .rag-card::before { opacity: 0.35; }

    html[data-theme="light"] .rag-card-head {
        border-bottom-color: #e2e8f0 !important;
    }
    html[data-theme="light"] .rag-card-icon {
        background: rgba(180,83,9,0.10);
        border-color: rgba(180,83,9,0.30);
        color: #92400e;
        box-shadow: none;
    }

    html[data-theme="light"] .rag-field input,
    html[data-theme="light"] .rag-field select {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a;
    }
    html[data-theme="light"] .rag-field input:focus,
    html[data-theme="light"] .rag-field select:focus {
        border-color: #b45309;
        background: #fffbeb;
        box-shadow: 0 0 0 3px rgba(180,83,9,0.12);
    }
    html[data-theme="light"] .rag-field select {
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e");
    }

    html[data-theme="light"] .rag-btn {
        color: #422006;
        border: 1px solid #b45309;
        background: linear-gradient(180deg, rgba(245,197,66,0.92), rgba(224,169,44,0.85));
        box-shadow: 0 6px 18px rgba(180,83,9,0.22), inset 0 1px 0 rgba(255,255,255,0.5);
    }

    html[data-theme="light"] .answer-card .rag-card-icon {
        background: rgba(8,145,178,0.12);
        border-color: rgba(8,145,178,0.35);
        color: #0e7490;
        box-shadow: none;
    }
    html[data-theme="light"] .answer-meta {
        background: #ecfeff;
        border-color: #67e8f9;
        color: #475569;
    }
    html[data-theme="light"] .answer-meta i { color: #0891b2; }
    html[data-theme="light"] .answer-meta .doc-name { color: #0f172a; }

    html[data-theme="light"] #chatText {
        color: #1e293b;
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    html[data-theme="light"] #chatText.error {
        background: #fef2f2;
        border-color: #fecaca;
        color: #b91c1c;
    }
    html[data-theme="light"] #chatText strong,
    html[data-theme="light"] #chatText b {
        color: #0e7490;
    }

    html[data-theme="light"] .rag-empty-icon {
        background: rgba(100,116,139,0.06);
        border-color: #94a3b8;
        color: #64748b;
    }

    /* === АДАПТИВ === */
    @media (max-width: 768px) {
        .rag-wrap h1 { font-size: 21px; }
        .rag-wrap h1 .accent-bar { width: 3px; height: 21px; }
        .rag-page-sub { font-size: 12px; }
        .rag-head-icon { width: 54px; height: 54px; font-size: 23px; }
        .rag-body { padding: 16px; }
        .rag-card-head { padding: 12px 16px; }
    }
    @media (max-width: 576px) {
        .rag-wrap h1 { font-size: 19px; gap: 8px; }
        .rag-wrap h1 .accent-bar { width: 3px; height: 19px; }
        .rag-page-sub { font-size: 11.5px; }
        .rag-head-icon { width: 48px; height: 48px; font-size: 20px; border-radius: 14px; margin-bottom: 14px; }
        .rag-topbar { margin-bottom: 16px; }
        .rag-back-btn { width: 30px; height: 30px; }
        .rag-back-label { font-size: 9px; }
        .rag-card { border-radius: 12px; }
        .rag-card-head { padding: 12px 14px; }
        .rag-card-icon { width: 28px; height: 28px; font-size: 12px; border-radius: 8px; }
        .rag-card-title { font-size: 12.5px; }
        .rag-card-sub { font-size: 10px; }
        .rag-body { padding: 14px; gap: 12px; }
        .rag-field input,
        .rag-field select { padding: 9px 10px; font-size: 13px; }
        .rag-btn { padding: 12px 20px; font-size: 13px; border-radius: 10px; }
        #chatText { padding: 12px 14px; font-size: 13px; }
    }
    @media (max-width: 380px) {
        .rag-wrap h1 { font-size: 17px; }
        .rag-body { padding: 12px; }
        .rag-btn { font-size: 12.5px; padding: 11px 16px; }
    }
</style>

<div class="rag-wrap">

    {{-- 🔙 Кнопка назад --}}
    <div class="rag-topbar">
        <a href="{{ route('contracts.index') }}" class="rag-back-btn" data-i18n-title="back" title="Назад">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="rag-back-label" data-i18n="back">Назад</span>
    </div>

    {{-- 🎯 Центрированная шапка --}}
    <div class="rag-head-center">
        <div class="rag-head-icon">
            <i class="bi bi-chat-dots-fill"></i>
        </div>
        <h1>
            <span class="accent-bar"></span>
            <span data-i18n="page_title">Чат по документам</span>
        </h1>
        <div class="rag-page-sub" data-i18n="page_sub">
            Выбери файл из архива и задай вопрос — ИИ ответит с указанием страницы/абзаца
        </div>
    </div>

    @if($files->isEmpty())
        {{-- 📭 Пустое состояние --}}
        <div class="rag-card">
            <div class="rag-body">
                <div class="rag-empty-state">
                    <div class="rag-empty-icon">
                        <i class="bi bi-folder2-open"></i>
                    </div>
                    <div class="rag-empty-title" data-i18n="empty_title">Архив пуст</div>
                    <div class="rag-empty-text" data-i18n="empty_text">
                        Чтобы задать вопрос ИИ, сначала загрузите документы в архив.
                    </div>
                    <a href="{{ route('documents.index', ['tab' => 'archive']) }}" class="rag-empty-link">
                        <i class="bi bi-arrow-up-square"></i>
                        <span data-i18n="empty_btn">Перейти к документам</span>
                    </a>
                </div>
            </div>
        </div>
    @else
        {{-- 📁 Форма --}}
        <form id="chatForm">
            @csrf
            <div class="rag-card">
                <div class="rag-card-head">
                    <div class="rag-card-icon">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                    <div>
                        <h3 class="rag-card-title" data-i18n="section_ask">Выбери документ и задай вопрос</h3>
                        <div class="rag-card-sub" data-i18n="section_ask_sub">ИИ найдёт ответ в тексте выбранного файла</div>
                    </div>
                </div>
                <div class="rag-body">
                    <div class="rag-field">
                        <label for="chatFile" data-i18n="label_doc">Документ</label>
                        <select name="filename" id="chatFile" required>
                            @foreach($files as $f)
                                <option value="{{ $f }}">{{ $f }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="rag-field">
                        <label for="chatQ" data-i18n="label_question">Ваш вопрос</label>
                        <input type="text" name="question" id="chatQ"
                               data-i18n-placeholder="placeholder_q"
                               placeholder="Например: какая сумма в договоре? кто директор?" required>
                    </div>
                    <button type="submit" class="rag-btn" id="chatBtn">
                        <span class="spinner"></span>
                        <i class="bi bi-stars rag-btn-icon"></i>
                        <span class="rag-btn-text" data-i18n="submit_btn">Спросить ИИ</span>
                    </button>
                </div>
            </div>
        </form>

        {{-- 🤖 Ответ --}}
        <div class="rag-card answer-card" id="answerCard" style="display:none">
            <div class="rag-card-head">
                <div class="rag-card-icon">
                    <i class="bi bi-robot"></i>
                </div>
                <div>
                    <h3 class="rag-card-title" data-i18n="answer_title">Ответ ИИ</h3>
                    <div class="rag-card-sub" data-i18n="answer_sub">Результат обработки вашего вопроса</div>
                </div>
            </div>
            <div class="rag-body">
                <div class="answer-meta" id="answerMeta" style="display:none">
                    <i class="bi bi-file-earmark-text"></i>
                    <span class="doc-name" id="answerDocName">—</span>
                    <span class="dot"></span>
                    <i class="bi bi-clock"></i>
                    <span id="answerTime">—</span>
                </div>
                <div id="chatText"></div>
            </div>
        </div>
    @endif

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const TRANSLATIONS = {
        tj: {
            back: 'Ба қафо',
            page_title: 'Чат аз рӯи ҳуҷҷатҳо',
            page_sub: 'Файлро аз бойгонӣ интихоб кунед ва савол диҳед — ИИ бо нишондоди саҳифа/банд ҷавоб медиҳад',
            section_ask: 'Ҳуҷҷатро интихоб кунед ва савол диҳед',
            section_ask_sub: 'ИИ дар матни файли интихобшуда ҷавоб меёбад',
            label_doc: 'Ҳуҷҷат',
            label_question: 'Саволи шумо',
            placeholder_q: 'Масалан: суммаи шартнома чанд аст? директор кист?',
            submit_btn: 'Аз ИИ пурсидан',
            analyzing: 'ИИ ҳуҷҷатро мехонад...',
            answer_title: 'Ҷавоби ИИ',
            answer_sub: 'Натиҷаи коркарди саволи шумо',
            empty_title: 'Бойгонӣ холӣ аст',
            empty_text: 'Барои савол додан ба ИИ, аввал ҳуҷҷатҳоро ба бойгонӣ бор кунед.',
            empty_btn: 'Ба ҳуҷҷатҳо гузаштан',
            error: 'Хато',
            unknown_error: 'Хатои номаълум'
        },
        ru: {
            back: 'Назад',
            page_title: 'Чат по документам',
            page_sub: 'Выбери файл из архива и задай вопрос — ИИ ответит с указанием страницы/абзаца',
            section_ask: 'Выбери документ и задай вопрос',
            section_ask_sub: 'ИИ найдёт ответ в тексте выбранного файла',
            label_doc: 'Документ',
            label_question: 'Ваш вопрос',
            placeholder_q: 'Например: какая сумма в договоре? кто директор?',
            submit_btn: 'Спросить ИИ',
            analyzing: 'ИИ читает документ...',
            answer_title: 'Ответ ИИ',
            answer_sub: 'Результат обработки вашего вопроса',
            empty_title: 'Архив пуст',
            empty_text: 'Чтобы задать вопрос ИИ, сначала загрузите документы в архив.',
            empty_btn: 'Перейти к документам',
            error: 'Ошибка',
            unknown_error: 'Неизвестная ошибка'
        },
        en: {
            back: 'Back',
            page_title: 'Document Chat',
            page_sub: 'Pick a file from the archive and ask a question — AI will answer with page/paragraph references',
            section_ask: 'Choose a document and ask a question',
            section_ask_sub: 'AI will find the answer in the selected file',
            label_doc: 'Document',
            label_question: 'Your question',
            placeholder_q: 'E.g.: what is the contract amount? who is the director?',
            submit_btn: 'Ask AI',
            analyzing: 'AI is reading the document...',
            answer_title: 'AI Answer',
            answer_sub: 'Result of processing your question',
            empty_title: 'Archive is empty',
            empty_text: 'To ask AI a question, first upload documents to the archive.',
            empty_btn: 'Go to documents',
            error: 'Error',
            unknown_error: 'Unknown error'
        }
    };

    function applyTranslations(lang) {
        const dict = TRANSLATIONS[lang] || TRANSLATIONS.ru;
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

    let currentLang = localStorage.getItem('docsign_lang') || 'ru';
    applyTranslations(currentLang);

    window.addEventListener('docsign:lang-changed', (e) => {
        currentLang = e.detail?.lang || 'ru';
        applyTranslations(currentLang);
    });
    window.addEventListener('storage', (e) => {
        if (e.key === 'docsign_lang' && e.newValue) {
            currentLang = e.newValue;
            applyTranslations(currentLang);
        }
    });

    // === ОТПРАВКА ===
    const form = document.getElementById('chatForm');
    if (!form) return;

    const btn = document.getElementById('chatBtn');
    const btnText = btn.querySelector('.rag-btn-text');
    const answerCard = document.getElementById('answerCard');
    const chatText = document.getElementById('chatText');
    const chatFile = document.getElementById('chatFile');
    const answerMeta = document.getElementById('answerMeta');
    const answerDocName = document.getElementById('answerDocName');
    const answerTime = document.getElementById('answerTime');

    function getDict() {
        return TRANSLATIONS[localStorage.getItem('docsign_lang') || 'ru'];
    }

    function setLoading(on, text) {
        btn.disabled = on;
        btn.classList.toggle('loading', on);
        btnText.textContent = text;
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const dict = getDict();
        const selectedFile = chatFile.value;

        setLoading(true, dict.analyzing);
        const fd = new FormData(this);

        try {
            const resp = await fetch('{{ route("contracts.chat-ask") }}', {
                method: 'POST', body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await resp.json();

            answerCard.style.display = 'block';
            answerMeta.style.display = 'flex';
            answerDocName.textContent = selectedFile || '—';
            answerTime.textContent = new Date().toLocaleTimeString();
            chatText.classList.remove('error');

            if (data.success) {
                chatText.textContent = data.answer;
            } else {
                chatText.classList.add('error');
                chatText.textContent = '❌ ' + (data.error || dict.unknown_error);
            }

            answerCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (err) {
            answerCard.style.display = 'block';
            answerMeta.style.display = 'none';
            chatText.classList.add('error');
            chatText.textContent = '❌ ' + err.message;
            answerCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        setLoading(false, dict.submit_btn);
    });
});
</script>
@endsection