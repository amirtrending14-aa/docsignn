{{--
    ИИ-АРХИВИСТ (Поиск по всему архиву документов)
    • Фиолетовый акцент, иконка архива
    • Карточки: дедлайн + дни, формат файла, владелец→получатель, цитата
    • История — в localStorage
--}}
@extends('layouts.admin')

@section('content')
<style>
    .archivist-scope, .archivist-scope * { box-sizing: border-box; }
    body { background: #06070b !important; color: #e7ecf3 !important; }

    .arc-page { display: flex; gap: 16px; max-width: 1240px; margin: 0 auto; height: calc(100vh - 140px); min-height: 520px; }

    /* ============ САЙДБАР ============ */
    .arc-sidebar { width: 264px; flex-shrink: 0; display: flex; flex-direction: column; gap: 12px; min-height: 0; }
    .new-arc-btn { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 13px; border-radius: 13px; font-size: 13px; font-weight: 800; letter-spacing: .2px; color: #1a0b2e; background: linear-gradient(180deg, rgba(167,139,250,.95), rgba(139,92,246,.85)); text-decoration: none; box-shadow: 0 8px 22px rgba(139,92,246,.28); transition: transform .18s ease, filter .18s ease, box-shadow .18s ease; cursor: pointer; border: none; width: 100%; }
    .new-arc-btn:hover { filter: brightness(1.08); transform: translateY(-2px); box-shadow: 0 12px 28px rgba(139,92,246,.36); color:#1a0b2e; }
    .new-arc-btn:active { transform: translateY(0); }

    .sidebar-head { display: flex; align-items: center; justify-content: space-between; padding: 2px 6px 0; }
    .sidebar-head span { font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.1px; color: #6c7689; }
    .sidebar-count { font-size: 10.5px; font-weight: 800; color: #a78bfa; background: rgba(167,139,250,.1); border: 1px solid rgba(167,139,250,.25); padding: 2px 8px; border-radius: 20px; min-width: 26px; text-align: center; }

    .arc-list { flex: 1; min-height: 0; overflow-y: auto; display: flex; flex-direction: column; gap: 4px; padding: 2px; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.14) transparent; }
    .arc-list::-webkit-scrollbar { width: 6px; }
    .arc-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,.14); border-radius: 10px; }
    .arc-list::-webkit-scrollbar-thumb:hover { background: rgba(167,139,250,.4); }

    .arc-item { position: relative; display: flex; align-items: center; gap: 6px; border-radius: 11px; border: 1px solid transparent; transition: background .16s ease, border-color .16s ease; cursor: pointer; }
    .arc-item:hover { background: rgba(255,255,255,.045); }
    .arc-item-content { flex: 1; min-width: 0; display: flex; align-items: center; gap: 9px; padding: 10px 10px 10px 12px; color: #9aa4b8; font-size: 12.5px; font-weight: 500; }
    .arc-item:hover .arc-item-content { color: #e7ecf3; }
    .arc-item-content i { font-size: 13px; flex-shrink: 0; opacity: .8; }
    .arc-item-text { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .arc-item-del { flex-shrink: 0; width: 28px; height: 28px; margin-right: 4px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background: transparent; color: #7b8598; font-size: 12px; cursor: pointer; opacity: 0; transition: all .16s ease; border: none; }
    .arc-item:hover .arc-item-del { opacity: 1; }
    .arc-item-del:hover { color: #ff7b7b; background: rgba(255,107,107,.12); }

    .sidebar-empty { text-align: center; padding: 26px 14px; color: #5d667a; font-size: 11.5px; line-height: 1.6; border: 1px dashed rgba(255,255,255,.1); border-radius: 12px; }
    .sidebar-empty i { display:block; font-size: 22px; margin-bottom: 8px; color:#3d4557; }

    .sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.6); backdrop-filter: blur(3px); z-index: 1040; }
    .sidebar-backdrop.show { display:block; }

    /* ============ ОСНОВНАЯ ЧАСТЬ ============ */
    .arc-main { flex: 1; min-width: 0; display: flex; flex-direction: column; background: linear-gradient(180deg, rgba(255,255,255,.035), rgba(255,255,255,.012)); border: 1px solid rgba(255,255,255,.08); border-radius: 18px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,.45); }
    .arc-header { display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-bottom: 1px solid rgba(255,255,255,.08); background: rgba(255,255,255,.015); }
    .arc-header-icon { width: 38px; height: 38px; flex-shrink: 0; border-radius: 11px; background: rgba(167,139,250,.1); border: 1px solid rgba(167,139,250,.3); display: flex; align-items: center; justify-content: center; color: #a78bfa; font-size: 17px; box-shadow: 0 0 18px rgba(167,139,250,.18) inset; }
    .arc-header-info { min-width: 0; flex: 1; }
    .arc-header-title { font-size: 14px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .arc-header-sub { font-size: 10.5px; color: #8892a6; margin-top: 2px; }
    .arc-header-status { display: inline-flex; align-items: center; gap: 5px; font-size: 10px; font-weight: 700; color: #a78bfa; background: rgba(167,139,250,.08); border: 1px solid rgba(167,139,250,.22); padding: 4px 9px; border-radius: 20px; flex-shrink: 0; }
    .status-dot { width:6px;height:6px;border-radius:50%;background:#3ddc97;box-shadow:0 0 8px #3ddc97; animation: pulse 1.8s infinite; }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.35} }
    .burger { display: none; width: 38px; height: 38px; flex-shrink: 0; border-radius: 11px; border: 1px solid rgba(255,255,255,.1); background: rgba(255,255,255,.04); color: #cfd6e2; font-size: 16px; cursor: pointer; }

    /* ============ СООБЩЕНИЯ ============ */
    .arc-messages { flex: 1; min-height: 0; overflow-y: auto; padding: 22px; display: flex; flex-direction: column; gap: 14px; scroll-behavior: smooth; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.14) transparent; }
    .arc-messages::-webkit-scrollbar { width: 7px; }
    .arc-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,.13); border-radius: 10px; }

    .msg { max-width: 82%; padding: 14px 17px; border-radius: 15px; font-size: 13px; line-height: 1.7; animation: msgIn .28s cubic-bezier(.2,.8,.3,1); }
    @keyframes msgIn { from { opacity:0; transform: translateY(10px) scale(.98);} to {opacity:1; transform:none;} }

    .msg-body { white-space: pre-wrap; word-break: break-word; }
    .msg-body p { margin: 0 0 8px 0; }
    .msg-body p:last-child { margin-bottom: 0; }
    .msg-body strong, .msg-body b { color: inherit; font-weight: 700; }

    .msg.user { align-self: flex-end; background: linear-gradient(180deg, rgba(167,139,250,.92), rgba(139,92,246,.82)); color: #1a0b2e; font-weight: 600; border-bottom-right-radius: 5px; box-shadow: 0 6px 18px rgba(139,92,246,.22); }
    .msg.bot { align-self: flex-start; background: rgba(255,255,255,.045); border: 1px solid rgba(255,255,255,.08); color: #e7ecf3; border-bottom-left-radius: 5px; }
    .msg.error { align-self: flex-start; background: rgba(255,107,107,.09); border-color: rgba(255,107,107,.3); color: #ffb3b3; }
    .msg-time { display: block; margin-top: 6px; font-size: 9.5px; opacity: .5; font-weight: 500; }

    /* ============ КАРТОЧКИ ДОКУМЕНТОВ ============ */
    .doc-cards { display: flex; flex-direction: column; gap: 8px; margin-top: 14px; padding-top: 12px; border-top: 1px dashed rgba(255,255,255,.08); }
    .doc-card { display: flex; align-items: flex-start; gap: 12px; padding: 11px 14px; border-radius: 11px; background: rgba(167,139,250,.06); border: 1px solid rgba(167,139,250,.22); text-decoration: none; color: #e7ecf3; font-size: 12.5px; transition: all .2s ease; }
    .doc-card:hover { background: rgba(167,139,250,.14); border-color: rgba(167,139,250,.5); transform: translateX(3px); color: #e7ecf3; }
    .doc-card-ico { width: 34px; height: 34px; flex-shrink: 0; border-radius: 9px; background: rgba(167,139,250,.15); display: flex; align-items: center; justify-content: center; color: #a78bfa; font-size: 16px; }
    .doc-card-ico.fmt-pdf { background: rgba(255,107,107,.15); color: #ff7b7b; }
    .doc-card-ico.fmt-word { background: rgba(77,159,225,.15); color: #4d9fe1; }
    .doc-card-ico.fmt-excel { background: rgba(61,220,151,.15); color: #3ddc97; }
    .doc-card-info { flex: 1; min-width: 0; }
    .doc-card-title { font-weight: 700; font-size: 12.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .doc-card-meta { font-size: 10.5px; color: #8892a6; margin-top: 2px; display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .doc-card-meta .fmt-badge { font-weight: 800; font-size: 9.5px; padding: 1px 6px; border-radius: 6px; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12); letter-spacing: .5px; }
    .doc-card-deadline { margin-top: 5px; font-size: 10.5px; font-weight: 700; color: #8892a6; }
    .doc-card-deadline.dl-warn { color: #f5c542; }
    .doc-card-deadline.dl-bad { color: #ff7b7b; }
    .doc-card-people { margin-top: 3px; font-size: 10.5px; color: #6c7689; }
    .doc-card-quote { margin-top: 6px; padding-top: 6px; border-top: 1px dashed rgba(167,139,250,.2); font-size: 11px; color: #8892a6; font-style: italic; }
    .doc-card-status { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 12px; background: rgba(61,220,151,.12); color: #3ddc97; border: 1px solid rgba(61,220,151,.3); flex-shrink: 0; }
    .doc-card-status.pending { background: rgba(245,197,66,.12); color: #f5c542; border-color: rgba(245,197,66,.3); }
    .doc-card-status.rejected { background: rgba(255,107,107,.12); color: #ff7b7b; border-color: rgba(255,107,107,.3); }

    .meta-line { margin-top: 10px; padding-top: 10px; border-top: 1px dashed rgba(255,255,255,.06); font-size: 10.5px; color: #6c7689; display: flex; gap: 14px; flex-wrap: wrap; }
    .meta-line span { display: inline-flex; align-items: center; gap: 5px; }
    .meta-line i { color: #a78bfa; }

    /* ============ ПЕЧАТАЕТ ============ */
    .typing { display:flex; gap:5px; align-items:center; padding:15px 17px; }
    .typing span { width:7px; height:7px; border-radius:50%; background:#a78bfa; animation: bounce 1.2s infinite; }
    .typing span:nth-child(2){ animation-delay:.15s } .typing span:nth-child(3){ animation-delay:.3s }
    @keyframes bounce { 0%,60%,100%{transform:translateY(0);opacity:.35} 30%{transform:translateY(-6px);opacity:1} }

    /* ============ ПРИВЕТСТВИЕ ============ */
    .welcome { text-align:center; margin:auto; max-width:480px; padding:20px; animation: msgIn .4s ease; }
    .welcome-icon { width:66px; height:66px; margin:0 auto 18px; border-radius:19px; background:rgba(167,139,250,.1); border:1px solid rgba(167,139,250,.3); display:flex; align-items:center; justify-content:center; font-size:29px; color:#a78bfa; box-shadow:0 0 28px rgba(167,139,250,.25); }
    .welcome h2 { font-size:19px; font-weight:800; margin-bottom:8px; }
    .welcome p { font-size:12.5px; color:#8892a6; line-height:1.65; margin-bottom:18px; }
    .welcome-chips { display:flex; flex-direction:column; gap:8px; }
    .welcome-chip { width:100%; padding:11px 14px; border-radius:11px; text-align:left; background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08); font:inherit; font-size:12px; color:#9aa4b8; cursor:pointer; transition:all .2s ease; }
    .welcome-chip:hover { border-color:rgba(167,139,250,.45); color:#c4b5fd; background:rgba(167,139,250,.06); transform:translateX(3px); }

    /* ============ ВВОД ============ */
    .arc-input-area { display:flex; gap:10px; align-items:flex-end; padding:14px 16px; border-top:1px solid rgba(255,255,255,.08); background:rgba(0,0,0,.25); }
    #arcInput { flex:1; min-width:0; background:rgba(255,255,255,.045); border:1px solid rgba(255,255,255,.09); border-radius:13px; padding:13px 16px; color:#e7ecf3; font:500 13px 'Inter', system-ui, sans-serif; outline:none; resize:none; height:48px; max-height:140px; line-height:1.5; transition:border-color .2s, box-shadow .2s; scrollbar-width: thin; }
    #arcInput::placeholder { color:#6c7689; }
    #arcInput:focus { border-color:rgba(167,139,250,.55); background:rgba(167,139,250,.05); box-shadow:0 0 0 3px rgba(167,139,250,.12); }
    #sendBtn { width:48px; height:48px; flex-shrink:0; border:none; border-radius:13px; background:linear-gradient(180deg, rgba(167,139,250,.95), rgba(139,92,246,.85)); color:#1a0b2e; font-size:17px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .2s ease; box-shadow:0 6px 18px rgba(139,92,246,.28); }
    #sendBtn:hover:not(:disabled){ filter:brightness(1.1); transform:translateY(-2px); }
    #sendBtn:disabled { opacity:.45; cursor:not-allowed; box-shadow:none; }
    .input-hint { font-size:10px; color:#5d667a; padding:0 18px 10px; background:rgba(0,0,0,.25); }

    /* ============ ТОСТЫ ============ */
    .toast-wrap { position:fixed; top:20px; right:20px; z-index:1300; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
    .toast { display:flex; align-items:center; gap:10px; min-width:250px; max-width:340px; padding:13px 16px; border-radius:13px; background:rgba(18,21,29,.97); border:1px solid rgba(255,255,255,.1); box-shadow:0 16px 40px rgba(0,0,0,.55); font-size:12.5px; font-weight:600; color:#e7ecf3; animation: toastIn .3s cubic-bezier(.2,.8,.3,1); pointer-events:auto; }
    .toast.hide { animation: toastOut .25s ease forwards; }
    .toast i { font-size:16px; }
    .toast.ok { border-color:rgba(61,220,151,.4); } .toast.ok i { color:#3ddc97; }
    .toast.err { border-color:rgba(255,107,107,.4); } .toast.err i { color:#ff7b7b; }
    @keyframes toastIn { from{opacity:0; transform:translateX(30px) scale(.95)} to{opacity:1; transform:none} }
    @keyframes toastOut { to{opacity:0; transform:translateX(30px) scale(.95)} }

    /* ============ АДАПТИВ ============ */
    @media (max-width: 900px) {
        .arc-page { height: calc(100vh - 118px); gap: 0; }
        .burger { display:flex; align-items:center; justify-content:center; }
        .arc-main { border-radius:14px; }
        .msg { max-width: 92%; }
        .arc-sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: 280px; z-index: 1050; padding: 16px 14px; background: #0a0c12; border-right: 1px solid rgba(255,255,255,.08); transform: translateX(-105%); transition: transform .3s cubic-bezier(.2,.8,.3,1); box-shadow: 20px 0 60px rgba(0,0,0,.6); }
        .arc-sidebar.open { transform: none; }
        .arc-item-del { opacity:1; }
        .toast-wrap { top:12px; right:12px; left:12px; }
        .toast { min-width:0; max-width:none; }
    }
    @media (max-width: 420px) {
        .arc-header-status { display:none; }
        .arc-messages { padding:16px 13px; }
    }
</style>

<style>
    html[data-theme="light"] .archivist-scope { color: #0f172a !important; }
    html[data-theme="light"] .arc-sidebar, html[data-theme="light"] .arc-main { background: #ffffff !important; border-color: #cbd5e1 !important; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08); }
    html[data-theme="light"] .arc-header, html[data-theme="light"] .arc-input-area { background: #f8fafc !important; border-color: #cbd5e1 !important; }
    html[data-theme="light"] .arc-header-title, html[data-theme="light"] .msg.bot, html[data-theme="light"] .welcome-chip, html[data-theme="light"] .arc-item-content { color: #0f172a !important; }
    html[data-theme="light"] .arc-header-sub, html[data-theme="light"] .welcome p, html[data-theme="light"] .input-hint, html[data-theme="light"] .sidebar-head span, html[data-theme="light"] .sidebar-empty { color: #475569 !important; }
    html[data-theme="light"] .arc-item:hover, html[data-theme="light"] .welcome-chip:hover { background: #f1f5f9 !important; }
    html[data-theme="light"] .msg.bot { color: #1e293b !important; }
    html[data-theme="light"] #arcInput { background: #ffffff; color: #0f172a !important; border-color: #cbd5e1; }
    html[data-theme="light"] #arcInput::placeholder { color: #64748b !important; }
    html[data-theme="light"] .doc-card { background: #f8fafc; border-color: #cbd5e1; color: #0f172a !important; }
    html[data-theme="light"] .doc-card:hover { background: #f1f5f9; }
    html[data-theme="light"] .doc-card-title { color: #0f172a !important; }
    html[data-theme="light"] .doc-card-deadline { color: #475569; }
    html[data-theme="light"] .doc-card-people, html[data-theme="light"] .doc-card-quote { color: #64748b; }
    html[data-theme="light"] .doc-card-quote { border-color: #cbd5e1; }
    html[data-theme="light"] .doc-card-meta .fmt-badge { background: #f1f5f9; border-color: #cbd5e1; color: #475569; }
    html[data-theme="light"] .toast { background: #ffffff !important; color: #0f172a !important; border-color: #cbd5e1 !important; }
</style>

<div class="arc-page archivist-scope">

    {{-- 📚 Сайдбар --}}
    <aside class="arc-sidebar" id="arcSidebar">
        <button class="new-arc-btn" id="newArcBtn">
            <i class="bi bi-plus-lg"></i> <span data-i18n="new_search">Новый поиск</span>
        </button>

        <div class="sidebar-head">
            <span data-i18n="history">История</span>
            <span class="sidebar-count" id="arcCount">0</span>
        </div>

        <div class="arc-list" id="arcList"></div>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    {{-- 💬 Чат --}}
    <div class="arc-main">
        <div class="arc-header">
            <button type="button" class="burger" id="burgerBtn" aria-label="Меню"><i class="bi bi-list"></i></button>
            <div class="arc-header-icon"><i class="bi bi-archive-fill"></i></div>
            <div class="arc-header-info">
                <div class="arc-header-title" data-i18n="title">ИИ-Архивист</div>
                <div class="arc-header-sub" data-i18n="sub">Дедлайны, форматы, подписи, содержимое файлов</div>
            </div>
            <div class="arc-header-status"><span class="status-dot"></span> ONLINE</div>
        </div>

        <div class="arc-messages" id="arcMessages">
            <div class="welcome" id="welcomeBlock">
                <div class="welcome-icon"><i class="bi bi-archive-fill"></i></div>
                <h2 data-i18n="welcome_title">Архив в вашем распоряжении</h2>
                <p data-i18n="welcome_sub">Ищу по типу, сроку, участникам и содержимому файлов — задавайте вопрос своими словами.</p>
                <div class="welcome-chips">
                    <button type="button" class="welcome-chip" onclick="useHint(this)" data-i18n="hint_sign_today">✍️ Какие документы я должен подписать сегодня?</button>
                    <button type="button" class="welcome-chip" onclick="useHint(this)" data-i18n="hint_sign_week">⏰ Какие документы нужно подписать в течение 7 дней?</button>
                    <button type="button" class="welcome-chip" onclick="useHint(this)" data-i18n="hint_pdf_count">📊 Сколько у меня документов в формате PDF?</button>
                    <button type="button" class="welcome-chip" onclick="useHint(this)" data-i18n="hint_overdue">🔴 Какие документы просрочены?</button>
                    <button type="button" class="welcome-chip" onclick="useHint(this)" data-i18n="hint_content">🔎 Найди договоры, где говорится о расторжении</button>
                    <button type="button" class="welcome-chip" onclick="useHint(this)" data-i18n="hint_amount">💰 В каких документах указана сумма 50 000?</button>
                </div>
            </div>
        </div>

        <form class="arc-input-area" id="arcForm">
            <textarea id="arcInput" placeholder="Напиши вопрос по архиву..." data-i18n-ph="placeholder" rows="1"></textarea>
            <button type="submit" id="sendBtn"><i class="bi bi-send-fill"></i></button>
        </form>
        <div class="input-hint">Enter — <span data-i18n="send">отправить</span> · Shift + Enter — <span data-i18n="newline">новая строка</span></div>
    </div>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ================= i18n ================= */
    const TRANSLATIONS = {
        tj: {
            new_search: 'Ҷустуҷӯи нав', history: 'Таърих',
            title: 'Архивчии Зеҳнӣ', sub: 'Мӯҳлатҳо, форматҳо, имзоҳо, мундариҷаи файлҳо',
            welcome_title: 'Архив дар ихтиёри шумо',
            welcome_sub: 'Аз рӯи навъ, муҳлат, иштирокчиён ва матни файлҳо меҷӯям — саволро бо суханони худ нависед.',
            hint_sign_today: '✍️ Кадом ҳуҷҷатҳоро имрӯз бояд имзо кунам?',
            hint_sign_week: '⏰ Дар 7 рӯзи оянда кадом ҳуҷҷатҳоро бояд имзо кунам?',
            hint_pdf_count: '📊 Чанд ҳуҷҷати PDF дорам?',
            hint_overdue: '🔴 Кадом ҳуҷҷатҳо муҳлати гузашта доранд?',
            hint_content: '🔎 Шартномаҳоеро ёб, ки дар бораи бекоркунӣ мегӯянд',
            hint_amount: '💰 Дар кадом ҳуҷҷатҳо маблағи 50 000 навишта шудааст?',
            signed: 'имзо шудааст', waiting_signature: 'интизори имзо', rejected: 'рад шудааст', draft: 'лоиҳа',
            placeholder: 'Саволи худро нависед...', send: 'фиристодан', newline: 'сатри нав',
            found: 'Ёфт шуд', intent: 'намуди дархост'
        },
        ru: {
            new_search: 'Новый поиск', history: 'История',
            title: 'ИИ-Архивист', sub: 'Дедлайны, форматы, подписи, содержимое файлов',
            welcome_title: 'Архив в вашем распоряжении',
            welcome_sub: 'Ищу по типу, сроку, участникам и содержимому файлов — задавайте вопрос своими словами.',
            hint_sign_today: '✍️ Какие документы я должен подписать сегодня?',
            hint_sign_week: '⏰ Какие документы нужно подписать в течение 7 дней?',
            hint_pdf_count: '📊 Сколько у меня документов в формате PDF?',
            hint_overdue: '🔴 Какие документы просрочены?',
            hint_content: '🔎 Найди договоры, где говорится о расторжении',
            hint_amount: '💰 В каких документах указана сумма 50 000?',
            signed: 'подписан', waiting_signature: 'ждёт подписи', rejected: 'отклонён', draft: 'черновик',
            placeholder: 'Напиши вопрос по архиву...', send: 'отправить', newline: 'новая строка',
            found: 'Найдено', intent: 'тип запроса'
        },
        en: {
            new_search: 'New search', history: 'History',
            title: 'AI Archivist', sub: 'Deadlines, formats, signatures, file content',
            welcome_title: 'Archive at your service',
            welcome_sub: 'Search by type, deadline, people, or file contents. Ask in your own words.',
            hint_sign_today: '✍️ Which documents do I need to sign today?',
            hint_sign_week: '⏰ Which documents need my signature in the next 7 days?',
            hint_pdf_count: '📊 How many PDF documents do I have?',
            hint_overdue: '🔴 Which documents are overdue?',
            hint_content: '🔎 Find contracts that mention termination',
            hint_amount: '💰 Which documents mention the amount 50,000?',
            signed: 'signed', waiting_signature: 'awaiting signature', rejected: 'rejected', draft: 'draft',
            placeholder: 'Ask about archive...', send: 'send', newline: 'new line',
            found: 'Found', intent: 'query type'
        }
    };
    let LANG = localStorage.getItem('docsign_lang') || 'ru';
    const t = (k, fallback) => (TRANSLATIONS[LANG] && TRANSLATIONS[LANG][k]) || fallback || k;

    function applyTranslations(lang) {
        LANG = TRANSLATIONS[lang] ? lang : 'ru';
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const k = el.getAttribute('data-i18n');
            if (TRANSLATIONS[LANG][k] !== undefined) el.innerHTML = TRANSLATIONS[LANG][k];
        });
        document.querySelectorAll('[data-i18n-ph]').forEach(el => {
            const k = el.getAttribute('data-i18n-ph');
            if (TRANSLATIONS[LANG][k] !== undefined) el.placeholder = TRANSLATIONS[LANG][k];
        });
    }
    applyTranslations(LANG);
    window.addEventListener('docsign:lang-changed', e => applyTranslations(e.detail?.lang || 'ru'));

    /* ================= ТОСТЫ ================= */
    const toastWrap = document.getElementById('toastWrap');
    function toast(message, type = 'info') {
        const icons = { ok: 'bi-check-circle-fill', err: 'bi-x-circle-fill', info: 'bi-info-circle-fill' };
        const el = document.createElement('div');
        el.className = 'toast ' + type;
        el.innerHTML = '<i class="bi ' + (icons[type] || icons.info) + '"></i><span></span>';
        el.querySelector('span').textContent = message;
        toastWrap.appendChild(el);
        setTimeout(() => { el.classList.add('hide'); setTimeout(() => el.remove(), 260); }, 3200);
    }

    /* ================= МОБИЛЬНЫЙ САЙДБАР ================= */
    const sidebar = document.getElementById('arcSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const burger = document.getElementById('burgerBtn');
    function openSidebar() { sidebar.classList.add('open'); backdrop.classList.add('show'); }
    function closeSidebar() { sidebar.classList.remove('open'); backdrop.classList.remove('show'); }
    burger?.addEventListener('click', openSidebar);
    backdrop?.addEventListener('click', closeSidebar);

    /* ================= ИСТОРИЯ (localStorage) ================= */
    const HISTORY_KEY = 'archivist_history_v1';
    const arcList = document.getElementById('arcList');
    const arcCount = document.getElementById('arcCount');

    function loadHistory() {
        try { return JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]'); }
        catch (e) { return []; }
    }
    function saveHistory(items) {
        localStorage.setItem(HISTORY_KEY, JSON.stringify(items.slice(0, 50)));
    }
    function renderHistory() {
        const items = loadHistory();
        arcList.innerHTML = '';
        arcCount.textContent = items.length;

        if (items.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'sidebar-empty';
            empty.innerHTML = '<i class="bi bi-inbox"></i><span>' + t('history') + ': —</span>';
            arcList.appendChild(empty);
            return;
        }

        items.forEach((item, idx) => {
            const row = document.createElement('div');
            row.className = 'arc-item';
            row.innerHTML =
                '<div class="arc-item-content">' +
                '<i class="bi bi-search"></i>' +
                '<span class="arc-item-text"></span>' +
                '</div>' +
                '<button type="button" class="arc-item-del" aria-label="Удалить"><i class="bi bi-x-lg"></i></button>';
            row.querySelector('.arc-item-text').textContent = item.q;
            row.querySelector('.arc-item-content').addEventListener('click', () => {
                document.getElementById('arcInput').value = item.q;
                send();
                closeSidebar();
            });
            row.querySelector('.arc-item-del').addEventListener('click', (e) => {
                e.stopPropagation();
                const cur = loadHistory();
                cur.splice(idx, 1);
                saveHistory(cur);
                renderHistory();
            });
            arcList.appendChild(row);
        });
    }

    /* ================= ЛОГИКА ЧАТА ================= */
    const sendUrl = @json(route('archivist.chat'));
    const box = document.getElementById('arcMessages');
    const input = document.getElementById('arcInput');
    const sendBtn = document.getElementById('sendBtn');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    let busy = false;

    const WELCOME_CHIPS = [
        'hint_sign_today', 'hint_sign_week', 'hint_pdf_count', 'hint_overdue', 'hint_content', 'hint_amount'
    ];

    function welcomeHtml() {
        return '<div class="welcome-icon"><i class="bi bi-archive-fill"></i></div>' +
            '<h2 data-i18n="welcome_title">' + t('welcome_title') + '</h2>' +
            '<p data-i18n="welcome_sub">' + t('welcome_sub') + '</p>' +
            '<div class="welcome-chips">' +
            WELCOME_CHIPS.map(k => '<button type="button" class="welcome-chip" onclick="useHint(this)">' + t(k) + '</button>').join('') +
            '</div>';
    }

    window.useHint = function (el) {
        input.value = el.textContent.replace(/^[^\s]+\s/, '');
        autoGrow();
        input.focus();
        closeSidebar();
    };

    document.getElementById('newArcBtn').addEventListener('click', () => {
        box.innerHTML = '';
        const w = document.createElement('div');
        w.className = 'welcome';
        w.id = 'welcomeBlock';
        w.innerHTML = welcomeHtml();
        box.appendChild(w);
        applyTranslations(LANG);
        input.value = '';
        input.focus();
    });

    function autoGrow() {
        input.style.height = '48px';
        input.style.height = Math.min(input.scrollHeight, 140) + 'px';
    }
    input.addEventListener('input', autoGrow);

    function scrollDown() { box.scrollTop = box.scrollHeight; }

    function nowTime() {
        const d = new Date();
        return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    /* Статусы: completed/approved/signed → зелёный; draft/active/processing → жёлтый; rejected/failed → красный */
    function statusClass(s) {
        const v = (s || '').toLowerCase();
        if (v === 'rejected' || v.includes('reject') || v.includes('fail') || v.includes('отклон')) return 'rejected';
        if (v === 'draft' || v.includes('active') || v.includes('processing') || v.includes('waiting') || v.includes('ожид') || v.includes('чернов')) return 'pending';
        return '';
    }

    /* Иконка и цвет по формату файла */
    function formatInfo(ext) {
        const e = (ext || '').toLowerCase();
        if (e === 'pdf') return { icon: 'bi-file-earmark-pdf-fill', cls: 'fmt-pdf' };
        if (e === 'doc' || e === 'docx') return { icon: 'bi-file-earmark-word-fill', cls: 'fmt-word' };
        if (e === 'xls' || e === 'xlsx') return { icon: 'bi-file-earmark-excel-fill', cls: 'fmt-excel' };
        if (e === 'rtf') return { icon: 'bi-file-earmark-richtext-fill', cls: '' };
        return { icon: 'bi-file-earmark-text-fill', cls: '' };
    }

    function addMsg(role, text, docs, meta) {
        const welcome = document.getElementById('welcomeBlock');
        if (welcome) welcome.remove();

        const div = document.createElement('div');
        div.className = 'msg ' + role;

        const body = document.createElement('div');
        body.className = 'msg-body';
        body.innerHTML = escapeHtml(text).replace(/\n/g, '<br>');
        div.appendChild(body);

        /* ===== Карточки документов (дедлайн, формат, люди, цитата) ===== */
        if (docs && docs.length > 0) {
            const cards = document.createElement('div');
            cards.className = 'doc-cards';

            docs.forEach(d => {
                const fmt = formatInfo(d.format);
                const card = document.createElement('a');
                card.className = 'doc-card';
                card.href = d.url || ('/documents/' + d.id);
                card.target = '_blank';

                // Дедлайн с цветом срочности
                let deadlineHtml = '';
                if (d.deadline) {
                    let cls = '';
                    let label = '⏰ ' + d.deadline;
                    if (d.days_left !== null && d.days_left !== undefined) {
                        if (d.days_left < 0)        { cls = 'dl-bad';  label += ' · просрочен на ' + Math.abs(d.days_left) + ' дн.'; }
                        else if (d.days_left === 0) { cls = 'dl-warn'; label += ' · СЕГОДНЯ!'; }
                        else if (d.days_left <= 7)  { cls = 'dl-warn'; label += ' · осталось ' + d.days_left + ' дн.'; }
                        else                        { label += ' · осталось ' + d.days_left + ' дн.'; }
                    }
                    deadlineHtml = '<div class="doc-card-deadline ' + cls + '">' + label + '</div>';
                }

                // Владелец → получатель
                let peopleHtml = '';
                if (d.owner || d.receiver) {
                    peopleHtml = '<div class="doc-card-people">👤 ' + escapeHtml(d.owner || '?') + ' → ' + escapeHtml(d.receiver || '?') + '</div>';
                }

                // Цитата из файла
                let quoteHtml = '';
                if (d.quote) {
                    quoteHtml = '<div class="doc-card-quote">«' + escapeHtml(d.quote) + '»</div>';
                }

                card.innerHTML =
                    '<div class="doc-card-ico ' + fmt.cls + '"><i class="bi ' + fmt.icon + '"></i></div>' +
                    '<div class="doc-card-info">' +
                        '<div class="doc-card-title"></div>' +
                        '<div class="doc-card-meta">' +
                            '<span>ID: #' + d.id + '</span>' +
                            (d.format ? '<span class="fmt-badge">' + escapeHtml(d.format.toUpperCase()) + '</span>' : '') +
                            '<span>' + escapeHtml(t(d.signature_state === 'signed' ? 'signed' : (d.signature_state === 'rejected' ? 'rejected' : (d.signature_state === 'draft' ? 'draft' : 'waiting_signature')))) + '</span>' +
                        '</div>' +
                        deadlineHtml + peopleHtml + quoteHtml +
                    '</div>' +
                    '<span class="doc-card-status ' + statusClass(d.signature_state) + '"></span>';

                card.querySelector('.doc-card-title').textContent = d.title || 'Документ';
                card.querySelector('.doc-card-status').textContent = t(d.signature_state || 'waiting_signature');
                cards.appendChild(card);
            });

            div.appendChild(cards);
        }

        /* ===== Мета ===== */
        if (meta && role === 'bot') {
            const ml = document.createElement('div');
            ml.className = 'meta-line';
            ml.innerHTML =
                '<span><i class="bi bi-folder2-open"></i> ' + t('found') + ': <b>' + (meta.found_count || 0) + '</b></span>' +
                '<span><i class="bi bi-lightning-charge-fill"></i> ' + t('intent') + ': <b>' + escapeHtml(meta.intent || '—') + '</b></span>';
            div.appendChild(ml);
        }

        const tm = document.createElement('span');
        tm.className = 'msg-time';
        tm.textContent = nowTime();
        div.appendChild(tm);

        box.appendChild(div);
        scrollDown();
        return div;
    }

    function showTyping() {
        const el = document.createElement('div');
        el.className = 'msg bot typing';
        el.id = 'typingBox';
        el.innerHTML = '<span></span><span></span><span></span>';
        box.appendChild(el);
        scrollDown();
    }
    function hideTyping() { document.getElementById('typingBox')?.remove(); }

    async function send() {
        const text = input.value.trim();
        if (!text || busy) return;
        busy = true;
        sendBtn.disabled = true;
        closeSidebar();

        addMsg('user', text);
        input.value = '';
        autoGrow();
        showTyping();

        try {
            const resp = await fetch(sendUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ message: text })
            });
            const data = await resp.json();
            hideTyping();

            if (!resp.ok) {
                throw new Error(data.message || 'Не удалось выполнить поиск. Попробуйте ещё раз.');
            }
            addMsg('bot', data.answer || '—', data.documents || [], data.meta || {});

            const hist = loadHistory();
            if (!hist.some(h => h.q === text)) {
                hist.unshift({ q: text, t: Date.now() });
                saveHistory(hist);
                renderHistory();
            }
        } catch (e) {
            hideTyping();
            addMsg('error', '❌ ' + e.message);
        }

        busy = false;
        sendBtn.disabled = false;
        input.focus();
    }

    document.getElementById('arcForm').addEventListener('submit', e => { e.preventDefault(); send(); });

    input.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
    });

    renderHistory();
    input.focus();
});
</script>
@endsection