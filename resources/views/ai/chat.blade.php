{{--
    AI ЧАТ — ПОМОЩНИК ЭДО (v3 · Fixed)
    ИСПРАВЛЕНО:
      1. Правильный рендеринг старых сообщений (HTML-safe + переносы строк)
      2. НОВАЯ ЛОГИКА: при первом сообщении страница НЕ перезагружается
      3. URL в адресной строке обновляется через history API (без перезагрузки)
      4. Удаление работает для только что созданных чатов
--}}
@extends('layouts.admin')

@section('content')
<style>
    .ai-chat-scope, .ai-chat-scope * { box-sizing: border-box; }
    body { background: #06070b !important; color: #e7ecf3 !important; }

    .chat-page { display: flex; gap: 16px; max-width: 1240px; margin: 0 auto; height: calc(100vh - 140px); min-height: 520px; }

    /* ============ САЙДБАР ============ */
    .chat-sidebar { width: 264px; flex-shrink: 0; display: flex; flex-direction: column; gap: 12px; min-height: 0; }
    .new-chat-btn { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 13px; border-radius: 13px; font-size: 13px; font-weight: 800; letter-spacing: .2px; color: #062a2a; background: linear-gradient(180deg, rgba(77,208,225,.95), rgba(38,166,154,.85)); text-decoration: none; box-shadow: 0 8px 22px rgba(77,208,225,.28); transition: transform .18s ease, filter .18s ease, box-shadow .18s ease; }
    .new-chat-btn:hover { filter: brightness(1.08); transform: translateY(-2px); box-shadow: 0 12px 28px rgba(77,208,225,.36); color:#062a2a; }
    .new-chat-btn:active { transform: translateY(0); }

    .sidebar-head { display: flex; align-items: center; justify-content: space-between; padding: 2px 6px 0; }
    .sidebar-head span { font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.1px; color: #6c7689; }
    .sidebar-count { font-size: 10.5px; font-weight: 800; color: #4dd0e1; background: rgba(77,208,225,.1); border: 1px solid rgba(77,208,225,.25); padding: 2px 8px; border-radius: 20px; min-width: 26px; text-align: center; }

    .chat-list { flex: 1; min-height: 0; overflow-y: auto; display: flex; flex-direction: column; gap: 4px; padding: 2px; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.14) transparent; }
    .chat-list::-webkit-scrollbar { width: 6px; }
    .chat-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,.14); border-radius: 10px; }
    .chat-list::-webkit-scrollbar-thumb:hover { background: rgba(77,208,225,.4); }

    .chat-item { position: relative; display: flex; align-items: center; gap: 6px; border-radius: 11px; border: 1px solid transparent; transition: background .16s ease, border-color .16s ease, transform .16s ease, opacity .25s ease; }
    .chat-item:hover { background: rgba(255,255,255,.045); }
    .chat-item.active { background: linear-gradient(90deg, rgba(77,208,225,.14), rgba(77,208,225,.04)); border-color: rgba(77,208,225,.32); }
    .chat-item.active::before { content: ''; position: absolute; left: -1px; top: 50%; transform: translateY(-50%); width: 3px; height: 58%; border-radius: 0 4px 4px 0; background: #4dd0e1; box-shadow: 0 0 10px rgba(77,208,225,.8); }
    .chat-item.removing { opacity: 0; transform: translateX(-18px) scale(.96); max-height: 0 !important; margin: 0 !important; padding: 0 !important; overflow: hidden; border-width: 0; transition: all .3s ease; }

    .chat-item-link { flex: 1; min-width: 0; display: flex; align-items: center; gap: 9px; padding: 10px 10px 10px 12px; text-decoration: none; color: #9aa4b8; font-size: 12.5px; font-weight: 500; }
    .chat-item:hover .chat-item-link { color: #e7ecf3; }
    .chat-item.active .chat-item-link { color: #7fe3ef; font-weight: 700; }
    .chat-item-link i { font-size: 13px; flex-shrink: 0; opacity: .8; }
    .chat-item-text { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .chat-item-del { flex-shrink: 0; width: 30px; height: 30px; margin-right: 4px; display: flex; align-items: center; justify-content: center; border: 1px solid transparent; border-radius: 9px; background: transparent; color: #7b8598; font-size: 13px; cursor: pointer; opacity: 0; transform: scale(.85); transition: all .16s ease; }
    .chat-item:hover .chat-item-del, .chat-item:focus-within .chat-item-del { opacity: 1; transform: scale(1); }
    .chat-item-del:hover { color: #ff7b7b; background: rgba(255,107,107,.12); border-color: rgba(255,107,107,.35); }
    .chat-item-del:disabled { cursor: wait; opacity: .6; }
    .chat-item-del .bi-arrow-repeat { animation: spin .8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    .sidebar-empty { text-align: center; padding: 26px 14px; color: #5d667a; font-size: 11.5px; line-height: 1.6; border: 1px dashed rgba(255,255,255,.1); border-radius: 12px; }
    .sidebar-empty i { display:block; font-size: 22px; margin-bottom: 8px; color:#3d4557; }

    .sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.6); backdrop-filter: blur(3px); z-index: 1040; opacity: 0; transition: opacity .25s ease; }
    .sidebar-backdrop.show { opacity: 1; }

    /* ============ ОСНОВНАЯ ЧАСТЬ ============ */
    .chat-main { flex: 1; min-width: 0; display: flex; flex-direction: column; background: linear-gradient(180deg, rgba(255,255,255,.035), rgba(255,255,255,.012)); border: 1px solid rgba(255,255,255,.08); border-radius: 18px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,.45); }
    .chat-header { display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-bottom: 1px solid rgba(255,255,255,.08); background: rgba(255,255,255,.015); }
    .chat-header-icon { width: 38px; height: 38px; flex-shrink: 0; border-radius: 11px; background: rgba(77,208,225,.1); border: 1px solid rgba(77,208,225,.3); display: flex; align-items: center; justify-content: center; color: #4dd0e1; font-size: 17px; box-shadow: 0 0 18px rgba(77,208,225,.18) inset; }
    .chat-header-info { min-width: 0; flex: 1; }
    .chat-header-title { font-size: 14px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .chat-header-sub { font-size: 10.5px; color: #8892a6; margin-top: 2px; }
    .chat-header-status { display: inline-flex; align-items: center; gap: 5px; font-size: 10px; font-weight: 700; color: #4dd0e1; background: rgba(77,208,225,.08); border: 1px solid rgba(77,208,225,.22); padding: 4px 9px; border-radius: 20px; flex-shrink: 0; }
    .status-dot { width:6px;height:6px;border-radius:50%;background:#3ddc97;box-shadow:0 0 8px #3ddc97; animation: pulse 1.8s infinite; }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.35} }
    .burger { display: none; width: 38px; height: 38px; flex-shrink: 0; border-radius: 11px; border: 1px solid rgba(255,255,255,.1); background: rgba(255,255,255,.04); color: #cfd6e2; font-size: 16px; cursor: pointer; }

    /* ============ СООБЩЕНИЯ (ИСПРАВЛЕН РЕНДЕРИНГ) ============ */
    .chat-messages { flex: 1; min-height: 0; overflow-y: auto; padding: 22px; display: flex; flex-direction: column; gap: 12px; scroll-behavior: smooth; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.14) transparent; }
    .chat-messages::-webkit-scrollbar { width: 7px; }
    .chat-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,.13); border-radius: 10px; }
    .chat-messages::-webkit-scrollbar-thumb:hover { background: rgba(77,208,225,.4); }

    .msg { max-width: 76%; padding: 12px 16px; border-radius: 15px; font-size: 13px; line-height: 1.7; animation: msgIn .28s cubic-bezier(.2,.8,.3,1); }
    @keyframes msgIn { from { opacity:0; transform: translateY(10px) scale(.98);} to {opacity:1; transform:none;} }

    /* ✅ ВАЖНО: pre-wrap для сохранения переносов строк */
    .msg-body { white-space: pre-wrap; word-break: break-word; }
    .msg-body p { margin: 0 0 8px 0; }
    .msg-body p:last-child { margin-bottom: 0; }
    .msg-body strong, .msg-body b { color: inherit; font-weight: 700; }
    .msg-body em, .msg-body i { font-style: italic; }
    .msg-body u { text-decoration: underline; }

    .msg.user { align-self: flex-end; background: linear-gradient(180deg, rgba(77,208,225,.92), rgba(38,166,154,.82)); color: #052a2a; font-weight: 600; border-bottom-right-radius: 5px; box-shadow: 0 6px 18px rgba(38,166,154,.22); }
    .msg.bot { align-self: flex-start; background: rgba(255,255,255,.045); border: 1px solid rgba(255,255,255,.08); color: #e7ecf3; border-bottom-left-radius: 5px; }
    .msg.error { align-self: flex-start; background: rgba(255,107,107,.09); border-color: rgba(255,107,107,.3); color: #ffb3b3; }
    .msg-time { display: block; margin-top: 6px; font-size: 9.5px; opacity: .5; font-weight: 500; }

    /* ============ КНОПКИ СКАЧИВАНИЯ ============ */
    .doc-buttons { display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap; }
    .dl-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 10px; font-size: 11.5px; font-weight: 800; text-decoration: none; transition: all .2s ease; }
    .dl-btn.word { color:#f5c542; background:rgba(245,197,66,.1); border:1px solid rgba(245,197,66,.4); }
    .dl-btn.word:hover { background:rgba(245,197,66,.2); box-shadow:0 0 14px rgba(245,197,66,.25); color:#f5c542; transform:translateY(-1px); }
    .dl-btn.pdf { color:#ff7b7b; background:rgba(255,107,107,.1); border:1px solid rgba(255,107,107,.4); }
    .dl-btn.pdf:hover { background:rgba(255,107,107,.2); box-shadow:0 0 14px rgba(255,107,107,.25); color:#ff7b7b; transform:translateY(-1px); }

    /* ============ ПЕЧАТАЕТ ============ */
    .typing { display:flex; gap:5px; align-items:center; padding:15px 17px; }
    .typing span { width:7px; height:7px; border-radius:50%; background:#4dd0e1; animation: bounce 1.2s infinite; }
    .typing span:nth-child(2){ animation-delay:.15s } .typing span:nth-child(3){ animation-delay:.3s }
    @keyframes bounce { 0%,60%,100%{transform:translateY(0);opacity:.35} 30%{transform:translateY(-6px);opacity:1} }

    /* ============ ПРИВЕТСТВИЕ ============ */
    .welcome { text-align:center; margin:auto; max-width:440px; padding:20px; animation: msgIn .4s ease; }
    .welcome-icon { width:66px; height:66px; margin:0 auto 18px; border-radius:19px; background:rgba(77,208,225,.1); border:1px solid rgba(77,208,225,.3); display:flex; align-items:center; justify-content:center; font-size:29px; color:#4dd0e1; box-shadow:0 0 28px rgba(77,208,225,.25); }
    .welcome h2 { font-size:19px; font-weight:800; margin-bottom:8px; }
    .welcome p { font-size:12.5px; color:#8892a6; line-height:1.65; margin-bottom:18px; }
    .welcome-chips { display:flex; flex-direction:column; gap:8px; }
    .welcome-chip { padding:11px 14px; border-radius:11px; text-align:left; background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08); font-size:12px; color:#9aa4b8; cursor:pointer; transition:all .2s ease; }
    .welcome-chip:hover { border-color:rgba(77,208,225,.45); color:#7fe3ef; background:rgba(77,208,225,.06); transform:translateX(3px); }

    /* ============ ВВОД ============ */
    .chat-input-area { display:flex; gap:10px; align-items:flex-end; padding:14px 16px; border-top:1px solid rgba(255,255,255,.08); background:rgba(0,0,0,.25); }
    #chatInput { flex:1; min-width:0; background:rgba(255,255,255,.045); border:1px solid rgba(255,255,255,.09); border-radius:13px; padding:13px 16px; color:#e7ecf3; font:500 13px 'Inter', system-ui, sans-serif; outline:none; resize:none; height:48px; max-height:140px; line-height:1.5; transition:border-color .2s, box-shadow .2s, background .2s; scrollbar-width: thin; }
    #chatInput::placeholder { color:#6c7689; }
    #chatInput:focus { border-color:rgba(77,208,225,.55); background:rgba(77,208,225,.05); box-shadow:0 0 0 3px rgba(77,208,225,.12); }
    #sendBtn { width:48px; height:48px; flex-shrink:0; border:none; border-radius:13px; background:linear-gradient(180deg, rgba(77,208,225,.95), rgba(38,166,154,.85)); color:#052a2a; font-size:17px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .2s ease; box-shadow:0 6px 18px rgba(38,166,154,.28); }
    #sendBtn:hover:not(:disabled){ filter:brightness(1.1); transform:translateY(-2px); }
    #sendBtn:active:not(:disabled){ transform:translateY(0) scale(.96); }
    #sendBtn:disabled { opacity:.45; cursor:not-allowed; box-shadow:none; }
    .input-hint { font-size:10px; color:#5d667a; padding:0 18px 10px; background:rgba(0,0,0,.25); }

    /* ============ МОДАЛКА УДАЛЕНИЯ ============ */
    .modal-overlay { position:fixed; inset:0; z-index:1200; background:rgba(4,5,9,.75); backdrop-filter:blur(6px); display:flex; align-items:center; justify-content:center; padding:20px; opacity:0; pointer-events:none; transition:opacity .22s ease; }
    .modal-overlay.show { opacity:1; pointer-events:auto; }
    .modal-card { width:100%; max-width:400px; background:linear-gradient(180deg,#12151d,#0c0e14); border:1px solid rgba(255,255,255,.1); border-radius:18px; padding:26px 24px 20px; text-align:center; box-shadow:0 30px 70px rgba(0,0,0,.6); transform:translateY(14px) scale(.96); transition:transform .25s cubic-bezier(.2,.8,.3,1); }
    .modal-overlay.show .modal-card { transform:none; }
    .modal-icon { width:56px; height:56px; margin:0 auto 16px; border-radius:16px; background:rgba(255,107,107,.12); border:1px solid rgba(255,107,107,.35); display:flex; align-items:center; justify-content:center; font-size:24px; color:#ff7b7b; box-shadow:0 0 24px rgba(255,107,107,.2); }
    .modal-title { font-size:16px; font-weight:800; margin-bottom:8px; }
    .modal-text { font-size:12.5px; color:#8892a6; line-height:1.65; margin-bottom:6px; }
    .modal-chat-name { display:inline-block; max-width:100%; margin:8px 0 20px; padding:8px 14px; font-size:12px; font-weight:700; color:#e7ecf3; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09); border-radius:10px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .modal-actions { display:flex; gap:10px; }
    .m-btn { flex:1; padding:12px; border-radius:12px; font-size:12.5px; font-weight:800; cursor:pointer; border:1px solid transparent; transition:all .18s ease; display:flex; align-items:center; justify-content:center; gap:7px; }
    .m-btn.cancel { background:rgba(255,255,255,.05); border-color:rgba(255,255,255,.1); color:#c3cad6; }
    .m-btn.cancel:hover { background:rgba(255,255,255,.09); }
    .m-btn.danger { background:linear-gradient(180deg,#ff6b6b,#e04b4b); color:#fff; box-shadow:0 6px 18px rgba(255,80,80,.3); }
    .m-btn.danger:hover { filter:brightness(1.08); transform:translateY(-1px); }
    .m-btn:disabled { opacity:.6; cursor:wait; transform:none !important; }

    /* ============ ТОСТЫ ============ */
    .toast-wrap { position:fixed; top:20px; right:20px; z-index:1300; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
    .toast { display:flex; align-items:center; gap:10px; min-width:250px; max-width:340px; padding:13px 16px; border-radius:13px; background:rgba(18,21,29,.97); border:1px solid rgba(255,255,255,.1); box-shadow:0 16px 40px rgba(0,0,0,.55); font-size:12.5px; font-weight:600; color:#e7ecf3; animation: toastIn .3s cubic-bezier(.2,.8,.3,1); pointer-events:auto; }
    .toast.hide { animation: toastOut .25s ease forwards; }
    .toast i { font-size:16px; }
    .toast.ok { border-color:rgba(61,220,151,.4); } .toast.ok i { color:#3ddc97; }
    .toast.err { border-color:rgba(255,107,107,.4); } .toast.err i { color:#ff7b7b; }
    .toast.info { border-color:rgba(77,208,225,.4); } .toast.info i { color:#4dd0e1; }
    @keyframes toastIn { from{opacity:0; transform:translateX(30px) scale(.95)} to{opacity:1; transform:none} }
    @keyframes toastOut { to{opacity:0; transform:translateX(30px) scale(.95)} }

    /* ============ АДАПТИВ ============ */
    @media (max-width: 900px) {
        .chat-page { height: calc(100vh - 118px); gap: 0; }
        .burger { display:flex; align-items:center; justify-content:center; }
        .chat-main { border-radius:14px; }
        .msg { max-width: 88%; }
        .chat-sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: 280px; z-index: 1050; padding: 16px 14px; background: #0a0c12; border-right: 1px solid rgba(255,255,255,.08); transform: translateX(-105%); transition: transform .3s cubic-bezier(.2,.8,.3,1); box-shadow: 20px 0 60px rgba(0,0,0,.6); }
        .chat-sidebar.open { transform: none; }
        .sidebar-backdrop.show { display:block; }
        .chat-item-del { opacity:1; transform:none; }
        .toast-wrap { top:12px; right:12px; left:12px; }
        .toast { min-width:0; max-width:none; }
    }
    @media (max-width: 420px) {
        .chat-header-status { display:none; }
        .chat-messages { padding:16px 13px; }
    }
</style>

<style>
    html[data-theme="light"] .ai-chat-scope {
        color: #0f172a !important;
    }
    html[data-theme="light"] .chat-sidebar,
    html[data-theme="light"] .chat-main {
        background: #ffffff !important;
        border-color: #cbd5e1 !important;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }
    html[data-theme="light"] .chat-header,
    html[data-theme="light"] .chat-input-area {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
    }
    html[data-theme="light"] .chat-header-title,
    html[data-theme="light"] .msg.bot,
    html[data-theme="light"] .welcome-chip,
    html[data-theme="light"] .chat-item-link {
        color: #0f172a !important;
    }
    html[data-theme="light"] .chat-header-sub,
    html[data-theme="light"] .welcome p,
    html[data-theme="light"] .input-hint,
    html[data-theme="light"] .sidebar-head span,
    html[data-theme="light"] .chat-item-link,
    html[data-theme="light"] .sidebar-empty {
        color: #475569 !important;
    }
    html[data-theme="light"] .chat-item:hover,
    html[data-theme="light"] .welcome-chip:hover {
        background: #f1f5f9 !important;
    }
    html[data-theme="light"] .chat-item.active {
        background: rgba(77, 208, 225, 0.12) !important;
        border-color: rgba(8, 145, 178, 0.45) !important;
    }
    html[data-theme="light"] .chat-item.active .chat-item-link {
        color: #0e7490 !important;
    }
    html[data-theme="light"] .chat-item-del,
    html[data-theme="light"] .burger {
        color: #475569;
        border-color: #cbd5e1;
        background: #f8fafc;
    }
    html[data-theme="light"] .glass-card,
    html[data-theme="light"] .msg.bot,
    html[data-theme="light"] .welcome-chip,
    html[data-theme="light"] #chatInput,
    html[data-theme="light"] .modal-chat-name,
    html[data-theme="light"] .m-btn.cancel {
        background: #ffffff !important;
        border-color: #cbd5e1 !important;
    }
    html[data-theme="light"] .msg.bot {
        color: #1e293b !important;
    }
    html[data-theme="light"] #chatInput {
        color: #0f172a !important;
    }
    html[data-theme="light"] #chatInput::placeholder {
        color: #64748b !important;
    }
    html[data-theme="light"] .modal-card,
    html[data-theme="light"] .toast {
        background: #ffffff !important;
        color: #0f172a !important;
        border-color: #cbd5e1 !important;
    }
    html[data-theme="light"] .modal-text,
    html[data-theme="light"] .modal-chat-name {
        color: #475569 !important;
    }
    html[data-theme="light"] .sidebar-empty {
        border-color: #cbd5e1 !important;
    }
</style>

<div class="chat-page ai-chat-scope">

    {{-- 📚 Сайдбар --}}
    <aside class="chat-sidebar" id="chatSidebar">
        <a href="{{ route('ai.chat') }}" class="new-chat-btn">
            <i class="bi bi-plus-lg"></i> <span data-i18n="new_chat">Новый чат</span>
        </a>

        <div class="sidebar-head">
            <span data-i18n="history">История</span>
            <span class="sidebar-count" id="chatCount">{{ count($chats) }}</span>
        </div>

        <div class="chat-list" id="chatList">
            @forelse($chats as $c)
                <div class="chat-item {{ $activeChat && $activeChat->id === $c->id ? 'active' : '' }}"
                     data-chat-id="{{ $c->id }}">
                    <a href="{{ route('ai.chat', ['chat_id' => $c->id]) }}"
                       class="chat-item-link"
                       title="{{ $c->title }}">
                        <i class="bi bi-chat-left-text"></i>
                        <span class="chat-item-text">{{ $c->title }}</span>
                    </a>
                    @if(Route::has('ai.chat.delete'))
                    <button type="button"
                            class="chat-item-del js-del-chat"
                            data-id="{{ $c->id }}"
                            data-title="{{ $c->title }}"
                            data-url="{{ route('ai.chat.delete', $c->id) }}"
                            aria-label="Удалить чат">
                        <i class="bi bi-trash3"></i>
                    </button>
                    @endif
                </div>
            @empty
                <div class="sidebar-empty" id="sidebarEmpty">
                    <i class="bi bi-inbox"></i>
                    <span data-i18n="no_chats">Пока нет чатов.<br>Начните новый диалог.</span>
                </div>
            @endforelse
        </div>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    {{-- 💬 Чат --}}
    <div class="chat-main">
        <div class="chat-header">
            <button type="button" class="burger" id="burgerBtn" aria-label="Меню"><i class="bi bi-list"></i></button>
            <div class="chat-header-icon"><i class="bi bi-robot"></i></div>
            <div class="chat-header-info">
                <div class="chat-header-title" data-i18n="title">Помощник ЭДО</div>
                <div class="chat-header-sub" data-i18n="sub">Создаёт документы через диалог • TJ / RU</div>
            </div>
            <div class="chat-header-status"><span class="status-dot"></span> ONLINE</div>
        </div>

        <div class="chat-messages" id="chatMessages">
            @forelse($messages as $m)
                <div class="msg {{ $m->role === 'user' ? 'user' : 'bot' }}">
                    {{-- ✅ ИСПРАВЛЕНИЕ 1: безопасный рендеринг с сохранением переносов строк --}}
                    <div class="msg-body">{!! nl2br(e($m->content)) !!}</div>
                    @if($m->file_path)
                        <div class="doc-buttons">
                            <a class="dl-btn word" href="{{ route('ai.download', [$m->id, 'docx']) }}">
                                <i class="bi bi-file-earmark-word-fill"></i> Word
                            </a>
                            @if($m->pdf_path)
                                <a class="dl-btn pdf" href="{{ route('ai.download', [$m->id, 'pdf']) }}">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                                </a>
                            @endif
                        </div>
                    @endif
                    <span class="msg-time">{{ optional($m->created_at)->format('H:i') }}</span>
                </div>
            @empty
                <div class="welcome">
                    <div class="welcome-icon"><i class="bi bi-robot"></i></div>
                    <h2 data-i18n="welcome_title">Салом! Чем помочь?</h2>
                    <p data-i18n="welcome_sub">Напиши, какой документ нужен — я задам уточняющие вопросы и создам его.</p>
                    <div class="welcome-chips">
                        <div class="welcome-chip" onclick="useHint(this)">📝 Ман мехоҳам ариза нависам</div>
                        <div class="welcome-chip" onclick="useHint(this)">📄 Сделай доверенность</div>
                        <div class="welcome-chip" onclick="useHint(this)">🤝 Создай договор оказания услуг</div>
                    </div>
                </div>
            @endforelse
        </div>

        <form class="chat-input-area" id="chatForm">
            <textarea id="chatInput" placeholder="Напиши сообщение..." data-i18n-ph="placeholder" rows="1"></textarea>
            <button type="submit" id="sendBtn"><i class="bi bi-send-fill"></i></button>
        </form>
        <div class="input-hint">Enter — <span data-i18n="send">отправить</span> · Shift + Enter — <span data-i18n="newline">новая строка</span></div>
    </div>
</div>

{{-- 🗑 Модалка --}}
<div class="modal-overlay" id="delModal" role="dialog" aria-modal="true" aria-labelledby="delModalTitle">
    <div class="modal-card">
        <div class="modal-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div class="modal-title" id="delModalTitle" data-i18n="del_title">Удалить чат?</div>
        <div class="modal-text" data-i18n="del_text">Чат и все сообщения будут удалены безвозвратно. Действие нельзя отменить.</div>
        <div class="modal-chat-name" id="delChatName">—</div>
        <div class="modal-actions">
            <button type="button" class="m-btn cancel" id="delCancel" data-i18n="cancel">Отмена</button>
            <button type="button" class="m-btn danger" id="delConfirm">
                <i class="bi bi-trash3-fill"></i> <span data-i18n="delete">Удалить</span>
            </button>
        </div>
    </div>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ================= i18n ================= */
    const TRANSLATIONS = {
        tj: {
            new_chat: 'Чати нав', history: 'Таърих', no_chats: 'Ҳоло чат нест.<br>Гуфтугӯи навро оғоз кунед.',
            title: 'Ёрдамчии ЭДО', sub: 'Ҳуҷҷатҳоро тавассути диалог эҷод мекунад • TJ / RU',
            welcome_title: 'Салом! Чӣ тавр кумак кунам?',
            welcome_sub: 'Нависед, ки кадом ҳуҷҷат лозим аст — ман саволҳо медиҳам ва онро эҷод мекунам.',
            placeholder: 'Паём нависед...', send: 'фиристодан', newline: 'сатри нав',
            del_title: 'Чат нест карда шавад?',
            del_text: 'Чат ва ҳамаи паёмҳо бе бозгашт нест карда мешаванд.',
            cancel: 'Бекор кардан', delete: 'Нест кардан',
            deleted: 'Чат нест карда шуд', del_error: 'Хатои несткунӣ'
        },
        ru: {
            new_chat: 'Новый чат', history: 'История', no_chats: 'Пока нет чатов.<br>Начните новый диалог.',
            title: 'Помощник ЭДО', sub: 'Создаёт документы через диалог • TJ / RU',
            welcome_title: 'Привет! Чем помочь?',
            welcome_sub: 'Напиши, какой документ нужен — я задам уточняющие вопросы и создам его.',
            placeholder: 'Напиши сообщение...', send: 'отправить', newline: 'новая строка',
            del_title: 'Удалить чат?',
            del_text: 'Чат и все сообщения будут удалены безвозвратно. Действие нельзя отменить.',
            cancel: 'Отмена', delete: 'Удалить',
            deleted: 'Чат удалён', del_error: 'Ошибка удаления'
        },
        en: {
            new_chat: 'New chat', history: 'History', no_chats: 'No chats yet.<br>Start a new conversation.',
            title: 'EDM Assistant', sub: 'Creates documents via dialogue • TJ / RU',
            welcome_title: 'Hello! How can I help?',
            welcome_sub: 'Tell me which document you need — I will ask questions and create it.',
            placeholder: 'Type a message...', send: 'send', newline: 'new line',
            del_title: 'Delete this chat?',
            del_text: 'The chat and all its messages will be permanently removed. This cannot be undone.',
            cancel: 'Cancel', delete: 'Delete',
            deleted: 'Chat deleted', del_error: 'Delete failed'
        }
    };
    let LANG = localStorage.getItem('docsign_lang') || 'tj';
    const t = (k, fallback) => (TRANSLATIONS[LANG] && TRANSLATIONS[LANG][k]) || fallback || k;

    function applyTranslations(lang) {
        LANG = TRANSLATIONS[lang] ? lang : 'tj';
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
    window.addEventListener('docsign:lang-changed', e => applyTranslations(e.detail?.lang || 'tj'));

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
    const sidebar = document.getElementById('chatSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const burger = document.getElementById('burgerBtn');
    function openSidebar() {
        sidebar.classList.add('open');
        backdrop.style.display = 'block';
        requestAnimationFrame(() => backdrop.classList.add('show'));
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        backdrop.classList.remove('show');
        setTimeout(() => { if (!sidebar.classList.contains('open')) backdrop.style.display = 'none'; }, 260);
    }
    burger?.addEventListener('click', openSidebar);
    backdrop?.addEventListener('click', closeSidebar);

    /* ================= УДАЛЕНИЕ ЧАТОВ ================= */
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const delModal = document.getElementById('delModal');
    const delName = document.getElementById('delChatName');
    const delCancel = document.getElementById('delCancel');
    const delConfirm = document.getElementById('delConfirm');
    const chatList = document.getElementById('chatList');
    const chatCount = document.getElementById('chatCount');
    const newChatUrl = @json(route('ai.chat'));
    const deleteRouteTemplate = @json(Route::has('ai.chat.delete') ? route('ai.chat.delete', '__ID__') : '');

    let pending = null;

    function openModal(data) {
        pending = data;
        delName.textContent = data.title || '—';
        delModal.classList.add('show');
        delConfirm.disabled = false;
        delConfirm.querySelector('span').textContent = t('delete', 'Удалить');
        delConfirm.querySelector('i').className = 'bi bi-trash3-fill';
        delCancel.textContent = t('cancel', 'Отмена');
        document.getElementById('delModalTitle').textContent = t('del_title', 'Удалить чат?');
        delModal.querySelector('.modal-text').textContent = t('del_text', '');
        setTimeout(() => delConfirm.focus(), 60);
    }
    function closeModal() { delModal.classList.remove('show'); pending = null; }

    delCancel.addEventListener('click', closeModal);
    delModal.addEventListener('click', e => { if (e.target === delModal) closeModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && delModal.classList.contains('show')) closeModal(); });

    function updateCounter() {
        const items = chatList.querySelectorAll('.chat-item').length;
        if (chatCount) chatCount.textContent = items;
        let empty = document.getElementById('sidebarEmpty');
        if (items === 0 && !empty) {
            empty = document.createElement('div');
            empty.className = 'sidebar-empty';
            empty.id = 'sidebarEmpty';
            empty.innerHTML = '<i class="bi bi-inbox"></i><span data-i18n="no_chats">' + t('no_chats', 'Пока нет чатов.') + '</span>';
            chatList.appendChild(empty);
        } else if (items > 0 && empty) {
            empty.remove();
        }
    }

    function removeItemAnimated(item) {
        item.style.maxHeight = item.offsetHeight + 'px';
        item.style.transition = 'all .3s ease';
        requestAnimationFrame(() => {
            item.classList.add('removing');
            setTimeout(() => {
                item.remove();
                updateCounter();
            }, 320);
        });
    }

    chatList.addEventListener('click', e => {
        const btn = e.target.closest('.js-del-chat');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        openModal({
            id: btn.dataset.id,
            url: btn.dataset.url,
            title: btn.dataset.title,
            item: btn.closest('.chat-item'),
            btn: btn
        });
    });

    delConfirm.addEventListener('click', async () => {
        if (!pending) return;
        const { id, url, item, btn } = pending;
        const isActive = item.classList.contains('active');

        delConfirm.disabled = true;
        delCancel.disabled = true;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat"></i>';
        delConfirm.querySelector('i').className = 'bi bi-arrow-repeat';
        delConfirm.querySelector('i').style.animation = 'spin .8s linear infinite';

        try {
            const resp = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-HTTP-Method-Override': 'DELETE' },
                body: JSON.stringify({ _method: 'DELETE', chat_id: id })
            });

            if (!resp.ok) throw new Error('HTTP ' + resp.status);

            let data = {};
            try { data = await resp.json(); } catch (e) {}
            if (data.success === false) throw new Error(data.message || 'error');

            closeModal();
            delCancel.disabled = false;
            toast(t('deleted', 'Чат удалён'), 'ok');

            removeItemAnimated(item);

            if (isActive) {
                // ✅ Если удалили АКТИВНЫЙ чат — переходим к новому через 500мс
                setTimeout(() => { window.location.href = newChatUrl; }, 500);
            }
        } catch (err) {
            delConfirm.disabled = false;
            delCancel.disabled = false;
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash3"></i>';
            delConfirm.querySelector('i').className = 'bi bi-trash3-fill';
            delConfirm.querySelector('i').style.animation = '';
            toast(t('del_error', 'Ошибка удаления') + ': ' + err.message, 'err');
        }
    });

    /* ================= ЛОГИКА ЧАТА ================= */
    const sendUrl = @json(route('ai.send'));
    const box = document.getElementById('chatMessages');
    const input = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    let currentChatId = @json($activeChat?->id ?? null);
    let busy = false;

    window.useHint = function (el) {
        input.value = el.textContent.replace(/^[^\s]+\s/, '');
        autoGrow();
        input.focus();
        closeSidebar();
    };

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
        div.textContent = str;
        return div.innerHTML;
    }

    function addMsg(role, text, files) {
        const welcome = box.querySelector('.welcome');
        if (welcome) welcome.remove();

        const div = document.createElement('div');
        div.className = 'msg ' + role;

        const body = document.createElement('div');
        body.className = 'msg-body';
        // ✅ ИСПРАВЛЕНИЕ 1: безопасный рендеринг с переносами строк
        body.innerHTML = escapeHtml(text).replace(/\n/g, '<br>');
        div.appendChild(body);

        if (files && (files.docx || files.pdf)) {
            const b = document.createElement('div');
            b.className = 'doc-buttons';
            if (files.docx) b.innerHTML += '<a class="dl-btn word" href="' + files.docx + '"><i class="bi bi-file-earmark-word-fill"></i> Word</a>';
            if (files.pdf)  b.innerHTML += '<a class="dl-btn pdf" href="' + files.pdf + '"><i class="bi bi-file-earmark-pdf-fill"></i> PDF</a>';
            div.appendChild(b);
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

    function buildDeleteUrl(id) {
        return deleteRouteTemplate.replace('__ID__', id);
    }

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
                body: JSON.stringify({ message: text, chat_id: currentChatId })
            });
            const data = await resp.json();
            hideTyping();

            if (data.chat_id) {
                const wasNew = !currentChatId;
                currentChatId = data.chat_id;

                // ✅ ИСПРАВЛЕНИЕ 2: НЕ перезагружаем страницу, а обновляем URL через history API
                if (wasNew) {
                    const newUrl = new URL(window.location.href);
                    newUrl.searchParams.set('chat_id', currentChatId);
                    window.history.replaceState({}, '', newUrl.toString());
                    addChatToSidebar(currentChatId, data.chat_title || text.slice(0, 40));
                }
            }

            if (data.success) {
                addMsg('bot', data.reply, data.is_document ? { docx: data.docx_url, pdf: data.pdf_url } : null);
            } else {
                addMsg('error', data.reply || '❌ Ошибка');
            }
        } catch (e) {
            hideTyping();
            addMsg('error', '❌ ' + e.message);
        }

        busy = false;
        sendBtn.disabled = false;
        input.focus();
    }

    function addChatToSidebar(id, title) {
        if (chatList.querySelector('[data-chat-id="' + id + '"]')) return;
        document.getElementById('sidebarEmpty')?.remove();
        chatList.querySelectorAll('.chat-item.active').forEach(el => el.classList.remove('active'));

        const item = document.createElement('div');
        item.className = 'chat-item active';
        item.dataset.chatId = id;

        const chatViewUrl = newChatUrl + (newChatUrl.includes('?') ? '&' : '?') + 'chat_id=' + id;

        item.innerHTML =
            '<a class="chat-item-link" href="' + chatViewUrl + '" title="">' +
            '<i class="bi bi-chat-left-text"></i><span class="chat-item-text"></span></a>';

        // Добавляем кнопку удаления, только если маршрут существует
        if (deleteRouteTemplate) {
            item.innerHTML +=
                '<button type="button" class="chat-item-del js-del-chat" data-id="' + id + '" data-url="' + buildDeleteUrl(id) + '" data-title="" aria-label="Delete">' +
                '<i class="bi bi-trash3"></i></button>';
            const delBtn = item.querySelector('.js-del-chat');
            delBtn.dataset.title = title;
        }

        item.querySelector('.chat-item-text').textContent = title;
        item.querySelector('.chat-item-link').title = title;

        chatList.prepend(item);
        updateCounter();
    }

    document.getElementById('chatForm').addEventListener('submit', e => { e.preventDefault(); send(); });

    input.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
    });

    updateCounter();
    scrollDown();
    input.focus();
});
</script>
@endsection