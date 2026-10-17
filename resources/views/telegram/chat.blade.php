@extends('layouts.admin')
@section('content')
<meta name="user-id" content="{{ auth()->id() }}">
<meta name="user-name" content="{{ auth()->user()->name }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="ws-url" content="{{ config('app.ws_url', 'ws://localhost:6001') }}">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">

<style>
/* ===== TELEGRAM UI VARIABLES & BASE ===== */
:root {
    --tg-bg: #ffffff;
    --tg-bg-secondary: #f4f4f5;
    --tg-border: #e0e0e0;
    --tg-text: #000000;
    --tg-hint: #707579;
    --tg-primary: #3390ec;
    --tg-primary-hover: #2b7cd3;
    --tg-bubble-in: #ffffff;
    --tg-bubble-out: #effdde;
    --tg-danger: #e53935;
}

button, .tg-icon-btn, .tg-header-btn, .tg-btn-attach, .tg-sticker-btn, .tg-context-item, .tg-item, a {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

.tg-container { 
    display: flex; 
    height: calc(100vh - 100px); 
    background: var(--bg-1, #fff); 
    border-radius: 12px; 
    overflow: hidden; 
    border: 1px solid var(--line, #ddd); 
    box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
    position: relative; 
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

/* ===== SIDEBAR ===== */
.tg-sidebar { 
    width: 350px; 
    border-right: 1px solid var(--line, #eee); 
    display: flex; 
    flex-direction: column; 
    background: var(--bg-1, #fff); 
    flex-shrink: 0; 
}
.tg-sidebar-header { 
    padding: 10px 16px; 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
}
.tg-sidebar-header h3 { 
    font-size: 18px; 
    font-weight: 600; 
    margin: 0; 
    color: var(--text);
}
.tg-header-actions { display: flex; gap: 4px; }

.tg-header-btn { 
    width: 40px; height: 40px; 
    border-radius: 50%; 
    border: none; 
    background: transparent; 
    color: var(--hint, #707579); 
    cursor: pointer; 
    display: flex; align-items: center; justify-content: center; 
    transition: background 0.2s; 
    font-size: 20px;
}
.tg-header-btn:hover { background: var(--bg-2, #f4f4f5); color: var(--text); }

.tg-search { padding: 8px 12px; position: relative;}
.tg-search input { 
    width: 100%; 
    padding: 10px 16px 10px 42px; 
    border: none; 
    border-radius: 22px; 
    background: var(--bg-2, #f4f4f5); 
    color: var(--text); 
    font-size: 14px; 
    box-sizing: border-box;
    transition: background 0.2s;
}
.tg-search input:focus { outline: none; background: var(--bg-2, #ececec); }
.tg-search-icon { 
    position: absolute; left: 26px; top: 50%; transform: translateY(-50%); 
    color: var(--hint, #707579); pointer-events: none; font-size: 16px;
}

.tg-list { flex: 1; overflow-y: auto; padding: 4px 0; }
.tg-list::-webkit-scrollbar, .tg-messages::-webkit-scrollbar { width: 6px; }
.tg-list::-webkit-scrollbar-thumb, .tg-messages::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 3px; }

.tg-item { 
    padding: 8px 12px; 
    margin: 0 8px; 
    cursor: pointer; 
    display: flex; 
    align-items: center; 
    gap: 12px; 
    border-radius: 10px; 
    transition: background 0.15s; 
    position: relative; 
}
.tg-item:hover { background: var(--bg-2, #f4f4f5); }
.tg-item.active { background: #3390ec; }
.tg-item.active .tg-item-name, .tg-item.active .tg-item-last, .tg-item.active .tg-item-meta div { color: white !important; }
.tg-item.active .tg-unread-badge { background: white; color: #3390ec; }

.tg-item-avatar { width: 54px; height: 54px; border-radius: 50%; object-fit: cover; }
.tg-item-info { flex: 1; min-width: 0; }
.tg-item-name { 
    font-weight: 500; font-size: 15px; margin-bottom: 4px; 
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; 
    display: flex; align-items: center; gap: 4px; color: var(--text);
}
.tg-item-last { 
    font-size: 14px; color: var(--hint, #707579); 
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; 
}
.tg-item-meta { 
    font-size: 12px; color: var(--hint, #707579); 
    display: flex; flex-direction: column; align-items: flex-end; gap: 6px; 
}
.tg-unread-badge { 
    min-width: 20px; height: 20px; padding: 0 6px; 
    background: #c4c9cc; color: white; border-radius: 10px; 
    font-size: 12px; font-weight: 500; 
    display: inline-flex; align-items: center; justify-content: center; 
}

/* ===== CHAT AREA ===== */
.tg-area { 
    flex: 1; display: flex; flex-direction: column; 
    background-color: #dfe5eb;
    background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%239C92AC' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
    min-width: 0; 
    position: relative;
}

.tg-header { 
    padding: 8px 16px; 
    border-bottom: 1px solid rgba(0,0,0,0.05); 
    background: var(--bg-1, #fff); 
    display: flex; 
    align-items: center; 
    gap: 12px; 
    z-index: 10;
}
.tg-header-avatar { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; }
.tg-header-info h4 { font-size: 15px; font-weight: 600; margin: 0 0 2px 0; color: var(--text); }
.tg-header-status { font-size: 13px; color: var(--hint, #707579); }
.tg-header-profile-link { display: flex; align-items: center; gap: 12px; flex: 1; text-decoration: none; color: inherit; cursor: pointer;}

.tg-header-actions-right { display: flex; gap: 4px; margin-left: auto; }
.tg-icon-btn { 
    width: 40px; height: 40px; 
    border-radius: 50%; 
    border: none; 
    background: transparent; 
    color: var(--hint, #707579); 
    cursor: pointer; 
    display: flex; align-items: center; justify-content: center; 
    transition: all 0.2s; 
    font-size: 20px;
    position: relative;
}
.tg-icon-btn:hover { background: var(--bg-2, #f4f4f5); color: var(--text); }
.tg-icon-btn.call-audio:hover { color: #3390ec; background: rgba(51, 144, 236, 0.1); }
.tg-icon-btn.call-video:hover { color: #10b981; background: rgba(16, 185, 129, 0.1); }
.tg-icon-btn.danger:hover { color: #e53935; background: rgba(229, 57, 53, 0.1); }

/* Messages Area */
.tg-messages { 
    flex: 1; overflow-y: auto; padding: 10px 20px; 
    display: flex; flex-direction: column; gap: 2px; 
}
.tg-message { display: flex; max-width: 65%; margin-bottom: 2px; position: relative; }
.tg-message.mine { align-self: flex-end; }
.tg-message.theirs { align-self: flex-start; }

.tg-bubble { 
    padding: 6px 10px 6px 10px; 
    border-radius: 12px; 
    position: relative; 
    word-wrap: break-word; 
    max-width: 100%; 
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    line-height: 1.4;
}
.tg-message.mine .tg-bubble { 
    background: var(--tg-bubble-out); 
    color: #000; 
    border-bottom-right-radius: 4px; 
}
.tg-message.theirs .tg-bubble { 
    background: var(--tg-bubble-in); 
    color: #000; 
    border-bottom-left-radius: 4px; 
}

/* Стикер в чате (без фона) */
.tg-message.sticker-msg .tg-bubble {
    background: transparent !important;
    box-shadow: none !important;
    padding: 0;
    border-radius: 0;
}
.tg-sticker-emoji { 
    font-size: 120px;
    line-height: 1;
    display: block;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.tg-sender { font-size: 13px; font-weight: 600; color: #3390ec; margin-bottom: 2px; display: block;}
.tg-text { font-size: 15px; white-space: pre-wrap; word-break: break-word; }
.tg-text a { color: #3390ec; text-decoration: none; }
.tg-text a:hover { text-decoration: underline; }

.tg-time { 
    font-size: 11px; color: rgba(0,0,0,0.45); 
    float: right; margin-left: 8px; margin-top: 6px;
    display: flex; align-items: center; gap: 3px;
    user-select: none;
}
.tg-message.mine .tg-time { color: #4fae4e; }

/* Анимация появления нового сообщения (как в ТГ) */
@keyframes msgSlideIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.tg-message.new-msg {
    animation: msgSlideIn 0.2s ease-out;
}

.tg-image { max-width: 100%; max-height: 320px; border-radius: 8px; cursor: pointer; display: block; margin-bottom: 4px;}
.tg-file { 
    display: flex; align-items: center; gap: 10px; 
    padding: 8px; background: rgba(255,255,255,0.5); 
    border-radius: 8px; text-decoration: none; color: inherit; 
    border: 1px solid rgba(0,0,0,0.05);
}
.tg-file-icon { 
    width: 40px; height: 40px; background: #3390ec; color: white;
    border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;
}
.tg-file-name { font-weight: 500; font-size: 14px; color: #3390ec;}
.tg-file-size { font-size: 12px; color: var(--hint); }

.tg-voice-msg { display: flex; align-items: center; gap: 10px; min-width: 240px; padding: 4px 0;}
.tg-voice-icon { width: 36px; height: 36px; background: #3390ec; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer;}
.tg-voice-audio { flex: 1; height: 30px; outline: none; }

.tg-date-divider { text-align: center; margin: 12px 0; position: sticky; top: 10px; z-index: 5; pointer-events: none;}
.tg-date-divider span { 
    background: rgba(0,0,0,0.35); color: white; 
    padding: 4px 12px; border-radius: 16px; 
    font-size: 13px; font-weight: 500; 
    backdrop-filter: blur(4px);
}

/* Footer / Input */
.tg-footer-wrap { background: var(--bg-1, #fff); padding: 8px 16px; display: flex; align-items: flex-end; gap: 10px; border-top: 1px solid rgba(0,0,0,0.05); position: relative;}
.tg-input-wrapper { flex: 1; position: relative; background: var(--bg-2, #f4f4f5); border-radius: 22px; padding: 4px 4px 4px 16px; display: flex; align-items: flex-end;}
.tg-input { 
    width: 100%; padding: 8px 0; border: none; background: transparent; 
    color: var(--text); font-size: 15px; resize: none; max-height: 150px; 
    box-sizing: border-box; line-height: 1.4;
}
.tg-input:focus { outline: none; }
.tg-char-counter { position: absolute; right: 50px; bottom: 12px; font-size: 11px; color: var(--hint); pointer-events: none;}

.tg-btn-attach { 
    width: 40px; height: 40px; border-radius: 50%; border: none; 
    background: transparent; color: var(--hint); cursor: pointer; 
    display: flex; align-items: center; justify-content: center; font-size: 22px; transition: color 0.2s;
}
.tg-btn-attach:hover { color: var(--text); }

.tg-btn-send { 
    width: 48px; height: 48px; border-radius: 50%; border: none; 
    background: #3390ec; color: white; cursor: pointer; 
    display: flex; align-items: center; justify-content: center; 
    font-size: 20px; transition: transform 0.1s, background 0.2s; flex-shrink: 0;
}
.tg-btn-send:hover { background: #2b7cd3; }
.tg-btn-send:active { transform: scale(0.95); }

.tg-btn-send.recording { 
    background: #e53935; 
    animation: pulse-red 1.5s infinite; 
}
.tg-btn-send.recording:hover { background: #d32f2f; }

@keyframes pulse-red { 0% { box-shadow: 0 0 0 0 rgba(229, 57, 53, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(229, 57, 53, 0); } 100% { box-shadow: 0 0 0 0 rgba(229, 57, 53, 0); } }

/* Панель стикеров (ГУСТЫЕ но МАЛЕНЬКИЕ) */
.tg-sticker-panel { 
    position: absolute; bottom: 70px; left: 16px; 
    background: var(--bg-1, #fff); 
    border-radius: 12px; 
    box-shadow: 0 4px 20px rgba(0,0,0,0.15); 
    width: 320px; max-height: 350px; 
    z-index: 100; display: none; flex-direction: column;
    border: 1px solid rgba(0,0,0,0.05);
}
.tg-sticker-panel.active { display: flex; }
.tg-sticker-header { padding: 8px 10px; border-bottom: 1px solid #eee; font-weight: 600; font-size: 13px; color: var(--hint); display: flex; justify-content: space-between;}
.tg-sticker-grid { 
    display: grid; 
    grid-template-columns: repeat(7, 1fr); /* Увеличили с 6 до 7 колонок */
    gap: 2px; /* Уменьшили с 4px до 2px - стали плотнее! */
    padding: 6px; /* Уменьшили padding */
    overflow-y: auto; 
    flex: 1;
}
.tg-sticker-btn { 
    aspect-ratio: 1; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    cursor: pointer; 
    border-radius: 6px; /* Чуть меньше скругление */
    transition: background 0.15s; 
    font-size: 22px; /* УМЕНЬШИЛИ с 28px до 22px - стали меньше! */
    padding: 2px; /* Добавили маленький padding */
}
.tg-sticker-btn:hover { 
    background: var(--bg-2, #f4f4f5); 
    transform: scale(1.05); /* Чуть меньше увеличение */
}
/* Context Menu */
.tg-context-menu { 
    position: fixed; background: var(--bg-1, #fff); 
    border-radius: 12px; 
    box-shadow: 0 4px 24px rgba(0,0,0,0.15); 
    z-index: 1500; min-width: 220px; padding: 6px 0; display: none; 
    border: 1px solid rgba(0,0,0,0.05);
    animation: fadeIn 0.1s ease-out;
}
.tg-context-menu.active { display: block; }
.tg-context-item { 
    padding: 10px 16px; cursor: pointer; font-size: 14px; 
    display: flex; align-items: center; gap: 14px; color: var(--text);
    transition: background 0.15s;
}
.tg-context-item i { font-size: 18px; color: var(--hint); width: 20px; text-align: center;}
.tg-context-item:hover { background: var(--bg-2, #f4f4f5); }
.tg-context-item.danger { color: #e53935; }
.tg-context-item.danger i { color: #e53935; }
.tg-context-separator { height: 1px; background: #eee; margin: 4px 0; }

/* Modals */
.tg-modal { position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(2px); display: none; align-items: center; justify-content: center; z-index: 1000; }
.tg-modal.active { display: flex; }
.tg-modal-content { background: var(--bg-1, #fff); border-radius: 12px; width: 100%; max-width: 420px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh;}
.tg-modal-header { padding: 16px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
.tg-modal-title { font-size: 18px; font-weight: 600; margin: 0; }
.tg-modal-body { padding: 20px; overflow-y: auto; }
.tg-modal-input { width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: 10px; background: var(--bg-2, #f9f9f9); font-size: 15px; margin-bottom: 16px; box-sizing: border-box; }
.tg-modal-input:focus { outline: none; border-color: #3390ec; background: #fff; }
.tg-modal-actions { padding: 16px 20px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 10px; background: var(--bg-2, #f9f9f9);}
.tg-btn { padding: 10px 20px; border-radius: 10px; border: none; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity 0.2s; }
.tg-btn-primary { background: #3390ec; color: white; }
.tg-btn-primary:hover { opacity: 0.9; }
.tg-btn-secondary { background: transparent; color: var(--hint); }
.tg-btn-secondary:hover { background: rgba(0,0,0,0.05); }

.tg-user-results { max-height: 300px; overflow-y: auto; }
.tg-user-result { padding: 10px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer; transition: background 0.15s; }
.tg-user-result:hover { background: var(--bg-2, #f4f4f5); }
.tg-user-result img { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; }
.tg-user-result-info { flex: 1; }
.tg-user-result-name { font-weight: 500; font-size: 15px; }
.tg-user-result-meta { font-size: 13px; color: var(--hint); }

/* Info Drawer */
.tg-info-drawer { position: absolute; top: 0; right: 0; bottom: 0; width: 360px; background: var(--bg-1, #fff); border-left: 1px solid #ddd; z-index: 60; transform: translateX(100%); transition: transform 0.3s cubic-bezier(0.2, 0.8, 0.2, 1); display: flex; flex-direction: column; box-shadow: -5px 0 20px rgba(0,0,0,0.05);}
.tg-info-drawer.open { transform: translateX(0); }
.tg-info-header { padding: 16px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 16px; }
.tg-info-body { flex: 1; overflow-y: auto; }
.tg-info-cover { padding: 30px 20px; text-align: center; border-bottom: 8px solid var(--bg-2, #f4f4f5); }
.tg-info-cover img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-bottom: 12px; }
.tg-info-cover-name { font-size: 20px; font-weight: 600; }
.tg-info-section { padding: 16px 0; border-bottom: 8px solid var(--bg-2, #f4f4f5); }
.tg-info-row { padding: 10px 20px; display: flex; flex-direction: column; gap: 4px; cursor: pointer; }
.tg-info-row:hover { background: var(--bg-2, #f4f4f5); }
.tg-info-label { font-size: 13px; color: var(--hint); }
.tg-info-value { font-size: 15px; color: var(--text); }
.tg-danger-zone { padding: 20px; }
.tg-btn-danger { width: 100%; padding: 12px; background: rgba(229, 57, 53, 0.1); color: #e53935; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; }
.tg-btn-danger:hover { background: rgba(229, 57, 53, 0.15); }

/* Call Modal */
#tgCallModal .tg-modal-content { max-width: 100%; max-height: 100%; width: 100vw; height: 100vh; border-radius: 0; background: #111; }
.tg-call-header { position: absolute; top: 0; left: 0; right: 0; padding: 20px; display: flex; justify-content: space-between; z-index: 20; background: linear-gradient(to bottom, rgba(0,0,0,0.6), transparent); }
.tg-call-container { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; position: relative; }
.tg-video-remote { width: 100%; height: 100%; object-fit: cover; }
.tg-video-local { position: absolute; bottom: 100px; right: 20px; width: 120px; height: 160px; background: #333; border-radius: 12px; object-fit: cover; border: 2px solid rgba(255,255,255,0.2); z-index: 10; }
.tg-call-controls { position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%); display: flex; gap: 20px; z-index: 20; }
.tg-call-btn { width: 60px; height: 60px; border-radius: 50%; border: none; background: rgba(255,255,255,0.2); color: white; font-size: 24px; cursor: pointer; backdrop-filter: blur(4px); transition: background 0.2s; display: flex; align-items: center; justify-content: center;}
.tg-call-btn:hover { background: rgba(255,255,255,0.3); }
.tg-call-btn.hangup { background: #e53935; width: 70px; }
.tg-call-btn.hangup:hover { background: #d32f2f; }
.tg-call-info { position: absolute; top: 100px; text-align: center; color: white; z-index: 10; text-shadow: 0 2px 4px rgba(0,0,0,0.5); }
.tg-call-info h3 { font-size: 28px; margin: 0 0 8px 0; font-weight: 500; }
.tg-call-info span { font-size: 16px; opacity: 0.9; }

/* Incoming Call */
.tg-incoming-call-modal { position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.tg-incoming-call-content { text-align: center; color: white; }
.tg-incoming-call-avatar { width: 120px; height: 120px; border-radius: 50%; margin: 0 auto 24px; border: 4px solid rgba(255,255,255,0.2); animation: pulse-ring 2s infinite; }
.tg-incoming-call-name { font-size: 28px; font-weight: 500; margin-bottom: 8px; }
.tg-incoming-call-status { font-size: 16px; color: #aaa; margin-bottom: 40px; }
.tg-incoming-call-actions { display: flex; gap: 60px; justify-content: center; }
.tg-incoming-call-btn { width: 70px; height: 70px; border-radius: 50%; border: none; cursor: pointer; font-size: 28px; color: white; display: flex; align-items: center; justify-content: center; transition: transform 0.2s; }
.tg-incoming-call-btn:hover { transform: scale(1.1); }
.tg-incoming-call-btn.accept { background: #4caf50; box-shadow: 0 4px 20px rgba(76, 175, 80, 0.4); }
.tg-incoming-call-btn.decline { background: #e53935; box-shadow: 0 4px 20px rgba(229, 57, 53, 0.4); }

@keyframes pulse-ring { 0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); } 70% { box-shadow: 0 0 0 20px rgba(255, 255, 255, 0); } 100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); } }
@keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

/* Utilities */
.tg-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--hint); }
.tg-empty-icon { font-size: 80px; margin-bottom: 20px; opacity: 0.2; }

.tg-recording-indicator { position: absolute; bottom: 70px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.8); color: white; padding: 8px 16px; border-radius: 20px; display: none; align-items: center; gap: 10px; z-index: 100; font-size: 14px; }
.tg-recording-indicator.active { display: flex; }
.tg-rec-dot { width: 8px; height: 8px; background: #e53935; border-radius: 50%; animation: blink 1s infinite; }
@keyframes blink { 50% { opacity: 0; } }

@media (max-width: 768px) {
    .tg-sidebar { width: 100%; }
    .tg-area { display: none; position: absolute; inset: 0; z-index: 50; }
    .tg-container.show-chat .tg-sidebar { display: none; }
    .tg-container.show-chat .tg-area { display: flex; }
    .tg-back-btn { display: flex !important; }
    .tg-message { max-width: 85%; }
    .tg-sticker-panel { width: calc(100% - 32px); left: 16px; bottom: 80px; }
    .tg-info-drawer { width: 100%; }
}
.tg-back-btn { display: none; width: 40px; height: 40px; border-radius: 50%; border: none; background: transparent; color: var(--text); cursor: pointer; align-items: center; justify-content: center; font-size: 20px; margin-right: 8px;}
.tg-back-btn:hover { background: var(--bg-2); }

.tg-toast-live { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.8); color: white; padding: 12px 24px; border-radius: 30px; font-size: 14px; z-index: 3000; opacity: 0; transition: opacity 0.3s; pointer-events: none; }
.tg-toast-live.show { opacity: 1; }
</style>

<div class="tg-container" id="tgContainer">
    <!-- Sidebar -->
    <div class="tg-sidebar">
        <div class="tg-sidebar-header">
            <h3>Чаты</h3>
            <div class="tg-header-actions">
                <button class="tg-header-btn" onclick="tgOpenNewChatModal()" title="Новый чат"><i class="bi bi-pencil-square"></i></button>
                <button class="tg-header-btn" onclick="tgOpenGroupModal()" title="Создать группу"><i class="bi bi-people"></i></button>
            </div>
        </div>
        <div class="tg-search">
            <i class="bi bi-search tg-search-icon"></i>
            <input type="text" id="tgSearch" placeholder="Поиск">
        </div>
        <div class="tg-list" id="tgList"></div>
    </div>

    <!-- Chat Area -->
    <div class="tg-area" id="tgArea">
        <div class="tg-empty" id="tgEmpty">
            <div class="tg-empty-icon"><i class="bi bi-chat-dots"></i></div>
            <h4>Выберите чат</h4>
            <p>Выберите чат из списка слева, чтобы начать общение</p>
        </div>

        <div class="tg-header" id="tgHeader" style="display: none;">
            <button class="tg-back-btn" onclick="tgGoBackToList()"><i class="bi bi-arrow-left"></i></button>
            <a href="#" id="tgHeaderProfileLink" class="tg-header-profile-link">
                <img src="" class="tg-header-avatar" id="tgHeaderAvatar">
                <div class="tg-header-info">
                    <h4 id="tgHeaderName">Имя</h4>
                    <div class="tg-header-status" id="tgHeaderStatus">статус</div>
                </div>
            </a>
            
            <div class="tg-header-actions-right">
                <button class="tg-icon-btn call-audio" id="tgAudioCallBtn" onclick="tgStartAudioCall()" title="Аудиозвонок" style="display: none;"><i class="bi bi-telephone-fill"></i></button>
                <button class="tg-icon-btn call-video" id="tgVideoCallBtn" onclick="tgStartVideoCall()" title="Видеозвонок" style="display: none;"><i class="bi bi-camera-video-fill"></i></button>
                <button class="tg-icon-btn" id="tgInfoBtn" onclick="tgOpenInfo(tgConvId)" title="Инфо" style="display: none;"><i class="bi bi-people"></i></button>
                <button class="tg-icon-btn" id="tgBlockBtn" onclick="tgToggleBlockModal()" title="Блокировка" style="display: none;"><i class="bi bi-slash-circle"></i></button>
                <button class="tg-icon-btn danger" id="tgDeleteChatBtn" onclick="tgDeleteCurrentChat()" title="Удалить чат" style="display: none;"><i class="bi bi-trash"></i></button>
            </div>
        </div>

        <div class="tg-blocked-banner" id="tgBlockedBanner" style="display: none; background: #ffeeee; color: #c62828; padding: 12px 20px; text-align: center; font-size: 14px;">
            Вы заблокировали этого пользователя. <button onclick="tgUnblockUser()" style="background:none; border:none; color:#3390ec; cursor:pointer; font-weight:600; margin-left:10px;">Разблокировать</button>
        </div>

        <div class="tg-messages" id="tgMessages" style="display: none;"></div>
        
        <button class="tg-icon-btn" id="tgScrollDownBtn" onclick="tgScrollDown()" style="position: absolute; bottom: 90px; right: 20px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.15); display: none; z-index: 10;"><i class="bi bi-chevron-down"></i></button>

        <div class="tg-footer-wrap" id="tgFooterWrap" style="display: none;">
            <div class="tg-reply-preview" id="tgReplyPreview" style="display:none; position:absolute; bottom:70px; left:16px; right:16px; background:white; padding:10px; border-radius:10px; box-shadow:0 -2px 10px rgba(0,0,0,0.1); border-left: 3px solid #3390ec; z-index: 5;">
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span style="font-weight:600; color:#3390ec; font-size:13px;" id="tgReplyAuthor"></span>
                    <i class="bi bi-x-lg" style="cursor:pointer; color:#999;" onclick="tgCancelReply()"></i>
                </div>
                <div style="font-size:13px; color:#555; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" id="tgReplyText"></div>
            </div>

            <button class="tg-btn-attach" onclick="tgToggleStickerPanel()" title="Стикеры"><i class="bi bi-emoji-smile"></i></button>
            <label class="tg-btn-attach" title="Файл">
                <i class="bi bi-paperclip"></i>
                <input type="file" id="tgFileInput" style="display: none;" onchange="tgUploadFile(this)">
            </label>
            
            <div class="tg-input-wrapper">
                <textarea class="tg-input" id="tgInput" rows="1" placeholder="Сообщение" onkeydown="tgHandleKey(event)" oninput="tgAutoResize(this); tgSendTyping(); tgUpdateCharCounter()"></textarea>
                <span class="tg-char-counter" id="tgCharCounter"></span>
            </div>

            <button class="tg-btn-send" id="tgCancelRecBtn" onclick="tgCancelRecording()" style="display: none; background: #707579; width: 48px; margin-right: -10px; z-index: 2;" title="Отменить">
                <i class="bi bi-trash"></i>
            </button>

            <button class="tg-btn-send" id="tgActionBtn" onclick="tgHandleAction()" title="Отправить">
                <i class="bi bi-mic-fill" id="tgMicIcon"></i>
                <i class="bi bi-send-fill" id="tgSendIcon" style="display: none;"></i>
                <i class="bi bi-stop-fill" id="tgStopIcon" style="display: none; font-size: 24px;"></i>
            </button>
            
            <div class="tg-recording-indicator" id="tgRecordingIndicator">
                <div class="tg-rec-dot"></div>
                <span>Запись...</span>
                <span id="tgRecordTimer" style="font-family: monospace;">0:00</span>
            </div>
        </div>

        <!-- Sticker Panel -->
        <div class="tg-sticker-panel" id="tgStickerPanel">
            <div class="tg-sticker-header">
                <span>Стикеры</span>
                <i class="bi bi-x-lg" style="cursor:pointer;" onclick="tgToggleStickerPanel()"></i>
            </div>
            <div class="tg-sticker-grid" id="tgStickerGrid"></div>
        </div>

        <!-- Info Drawer -->
        <div class="tg-info-drawer" id="tgInfoDrawer">
            <div class="tg-info-header">
                <button class="tg-icon-btn" onclick="tgCloseInfo()"><i class="bi bi-arrow-left"></i></button>
                <div style="font-weight: 600; font-size: 16px;">Инфо</div>
            </div>
            <div class="tg-info-body" id="tgInfoBody"></div>
        </div>
    </div>
</div>

<!-- Context Menu -->
<div class="tg-context-menu" id="tgContextMenu">
    <div class="tg-context-item" onclick="tgReplyToMessage()"><i class="bi bi-reply"></i> Ответить</div>
    <div class="tg-context-item" onclick="tgCopyMessage()"><i class="bi bi-clipboard"></i> Копировать</div>
    <div class="tg-context-separator"></div>
    <div class="tg-context-item danger" id="tgCtxDelete" onclick="tgDeleteMessage()"><i class="bi bi-trash"></i> Удалить</div>
</div>

<!-- Modals -->
<div class="tg-modal" id="tgNewChatModal">
    <div class="tg-modal-content">
        <div class="tg-modal-header"><h3 class="tg-modal-title">Новый чат</h3><button class="tg-icon-btn" onclick="tgCloseNewChatModal()"><i class="bi bi-x-lg"></i></button></div>
        <div class="tg-modal-body">
            <input type="text" class="tg-modal-input" id="tgNewChatSearch" placeholder="Поиск пользователей..." oninput="tgSearchNewChat(this.value)">
            <div class="tg-user-results" id="tgNewChatResults"></div>
        </div>
    </div>
</div>

<div class="tg-modal" id="tgGroupModal">
    <div class="tg-modal-content">
        <div class="tg-modal-header"><h3 class="tg-modal-title">Новая группа</h3><button class="tg-icon-btn" onclick="tgCloseGroupModal()"><i class="bi bi-x-lg"></i></button></div>
        <div class="tg-modal-body">
            <input type="text" class="tg-modal-input" id="tgGroupName" placeholder="Название группы">
            <input type="text" class="tg-modal-input" id="tgUserSearch" placeholder="Добавить участников..." oninput="tgSearchUsers(this.value, 'group')">
            <div class="tg-selected-tags" id="tgSelectedUsers" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px;"></div>
            <div class="tg-user-results" id="tgUserResults"></div>
        </div>
        <div class="tg-modal-actions">
            <button class="tg-btn tg-btn-secondary" onclick="tgCloseGroupModal()">Отмена</button>
            <button class="tg-btn tg-btn-primary" onclick="tgCreateGroup()">Создать</button>
        </div>
    </div>
</div>

<div class="tg-modal" id="tgBlockModal">
    <div class="tg-modal-content" style="max-width: 340px; text-align: center;">
        <div class="tg-modal-body" style="padding: 30px 20px;">
            <i class="bi bi-shield-x" style="font-size: 48px; color: #e53935; margin-bottom: 16px;"></i>
            <h3 id="tgBlockModalTitle" style="margin: 0 0 10px 0;">Заблокировать?</h3>
            <p id="tgBlockModalText" style="color: var(--hint); font-size: 14px; margin: 0;">Пользователь не сможет писать вам.</p>
        </div>
        <div class="tg-modal-actions" style="justify-content: center; border-top: none; padding-top: 0;">
            <button class="tg-btn tg-btn-secondary" onclick="tgCloseBlockModal()">Отмена</button>
            <button class="tg-btn" id="tgBlockConfirmBtn" onclick="tgConfirmBlock()" style="background: #e53935; color: white;">Заблокировать</button>
        </div>
    </div>
</div>

<div class="tg-modal" id="tgCallModal">
    <div class="tg-modal-content">
        <div class="tg-call-header">
            <div style="color: white; font-weight: 500; font-size: 18px;" id="tgCallName">Звонок</div>
            <button class="tg-icon-btn" onclick="tgEndCall()" style="color: white;"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="tg-call-container" id="tgCallContainer">
            <video id="tgRemoteVideo" class="tg-video-remote" autoplay playsinline></video>
            <video id="tgLocalVideo" class="tg-video-local" autoplay playsinline muted></video>
            <div class="tg-call-info"><h3 id="tgCallerName">Имя</h3><span id="tgCallTimer">00:00</span></div>
            <div class="tg-call-controls">
                <button class="tg-call-btn" id="tgCallMicBtn" onclick="tgToggleCallMic()"><i class="bi bi-mic-fill"></i></button>
                <button class="tg-call-btn" id="tgCallCamBtn" onclick="tgToggleCallCam()"><i class="bi bi-camera-video-fill"></i></button>
                <button class="tg-call-btn hangup" onclick="tgEndCall()"><i class="bi bi-telephone-x-fill"></i></button>
            </div>
        </div>
    </div>
</div>

<script>
const TG_USER_ID = document.querySelector('meta[name="user-id"]').content;
const TG_USER_NAME = document.querySelector('meta[name="user-name"]').content;
const TG_CSRF = document.querySelector('meta[name="csrf-token"]').content;
const WS_URL = document.querySelector('meta[name="ws-url"]').content;
const API = '/chat/api';

let tgConvId = null, tgPolling = null, tgLastMsgId = 0, tgAllConversations = [];
let tgCurrentConvData = null, tgCurrentOtherUserId = null;
let tgRecording = false, tgMediaRecorder = null, tgAudioChunks = [];
let tgRecordTimerInterval = null, tgRecordStartTime = 0, tgCurrentMimeType = '';
let tgSelectedUsers = new Map(), tgAddMembersSelected = new Map();
let tgReplyTo = null, tgContextMessageId = null, tgContextMessageData = null;
let tgTypingTimeout = null, tgTypingPollInterval = null, tgHeartbeatInterval = null;
let tgLastRenderedDate = null, tgDiscardRecording = false;
let tgInfoData = null;

let tgLocalStream = null, tgPeerConnection = null;
let tgCallTimer = null, tgCallStartTime = 0;
let tgIsVideoMuted = false, tgIsAudioMuted = false;
let tgCallId = null, tgIsCaller = false, tgCallTimeout = null;
let tgPendingOffer = null, tgRingtoneAudio = null;
let tgSignalPollInterval = null, tgPendingIce = [], tgPollInProgress = new Set(), tgIncomingCallData = null;
let tgProcessedSignals = new Set();
let tgIceRestarted = false, tgIceWatchdog = null;
let tgRetryCount = 0;
let isAudioOnlyCall = false;

// ===== ОЧЕРЕДЬ ОТПРАВЛЯЕМЫХ (оптимистичных) СООБЩЕНИЙ =====
// Каждое: { tempId, body, type, convId, ts }
let tgPendingOptimistic = [];

// ===== WEBSOCKET =====
let tgSocket = null;
let tgSocketReconnectTimer = null;

function tgConnectWebSocket() {
    if (tgSocket && (tgSocket.readyState === WebSocket.OPEN || tgSocket.readyState === WebSocket.CONNECTING)) return;
    try {
        const wsUrl = WS_URL.replace('http://', 'ws://').replace('https://', 'wss://');
        tgSocket = new WebSocket(wsUrl + '/app/local?protocol=7&client=js&version=7.4.0&flash=false');
        tgSocket.onopen = () => {
            if (tgSocketReconnectTimer) { clearTimeout(tgSocketReconnectTimer); tgSocketReconnectTimer = null; }
            tgSocketSubscribe();
        };
        tgSocket.onmessage = (event) => {
            try { tgHandleWebSocketMessage(JSON.parse(event.data)); } catch(e) {}
        };
        tgSocket.onclose = () => {
            tgSocketReconnectTimer = setTimeout(() => tgConnectWebSocket(), 3000);
        };
        tgSocket.onerror = () => {};
    } catch(e) {}
}

function tgSocketSubscribe() {
    if (!tgSocket || tgSocket.readyState !== WebSocket.OPEN) return;
    tgSocket.send(JSON.stringify({ event: 'pusher:subscribe', data: { channel: 'private-user.' + TG_USER_ID } }));
    tgSocket.send(JSON.stringify({ event: 'pusher:subscribe', data: { channel: 'chat-messages' } }));
}

function tgHandleWebSocketMessage(data) {
    if (!data.event) return;
    if (data.event === 'new-message' || data.event === 'App\\Events\\NewMessage' || data.event === 'message.sent') {
        let msgData = data.data;
        if (typeof msgData === 'string') { try { msgData = JSON.parse(msgData); } catch(e) { return; } }
        if (!msgData || !msgData.id) return;
        if (msgData.conversation_id == tgConvId) {
            // ГЛАВНОЕ: используем ту же логику рендера с "усыновлением" временных пузырьков
            tgRenderMessages([msgData]);
        }
        tgLoadConversations();
    }
    if (data.event === 'typing' || data.event === 'App\\Events\\UserTyping') {
        let typingData = data.data;
        if (typeof typingData === 'string') { try { typingData = JSON.parse(typingData); } catch(e) { return; } }
        if (typingData.conversation_id == tgConvId && typingData.user_id != TG_USER_ID) {
            tgShowTyping([typingData.name || 'Собеседник']);
            setTimeout(() => tgSetDefaultStatus(), 3000);
        }
    }
}

const tgIceServers = { 
    iceServers: [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' },
        { urls: 'turn:openrelay.metered.ca:80', username: 'openrelayproject', credential: 'openrelayproject' },
        { urls: 'turn:openrelay.metered.ca:443', username: 'openrelayproject', credential: 'openrelayproject' },
        { urls: 'turn:openrelay.metered.ca:443?transport=tcp', username: 'openrelayproject', credential: 'openrelayproject' }
    ]
};

const TG_STICKERS = ['😀','😃','😄','😁','😆','😅','','😂','🙂','🙃','😉','😊','😇','','😍','','😘','😗','😚','😙','🥲','😋','😛','😜','','😝','🤑','🤗','🤭','🤫','🤔','🤐','🤨','😐','😑','😶','😏','😒','🙄','','🤥','','😔','😪','','😴','😷','','','🤢','🤮','','🥵','','','😵','','🤠','','🥸','','🤓','','😕','😟','','☹️','','😯','😲','😳','','😦','😧','😨','😰','😥','😢','😭','😱','😖','😣','😞','😓','😩','😫','🥱','','😡','😠','','😈','','💀','️','💩','','👹','👺','👻','👽','👾','🤖','','😸','😹','😻','😼','😽','','😿','😾','🙈','','🙊','','💌','💘','💝','💖','💗','💓','💞','💕','💟','❣️','','❤️','🧡','💛','💚','💙','💜','🤎','🖤','🤍','💯','💢','💥','💫','💦','💨','🕳️','💣','💬','️‍️','🗨️','🗯️','💭','💤','','🤚','🖐️','✋','🖖','','','🤏','️','🤞','','🤘','','👈','','👆','','','☝️','','👎','✊','','','🤜','','','👐','','🤝','','✍️','💅','🤳','','🦾','','🦵','','👂',''];

document.addEventListener('DOMContentLoaded', () => {
    tgLoadConversations();
    tgStartHeartbeat();
    tgLoadStickers();
    tgConnectWebSocket();
    
    // Запасной polling (если WS не дошёл) — теперь БЕЗОПАСНЫЙ: дубликатов не будет,
    // т.к. tgRenderMessages умеет обновлять существующие пузырьки на месте
    tgPolling = setInterval(() => { if (tgConvId) tgLoadMessages(); }, 4000);
    
    setTimeout(() => tgStartSignalPolling(), 800);

    document.getElementById('tgSearch').addEventListener('input', () => tgRenderConversations(tgAllConversations));
    document.addEventListener('visibilitychange', () => { if (!document.hidden && tgConvId) tgMarkAsRead(tgConvId); });
    
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.tg-context-menu')) document.getElementById('tgContextMenu').classList.remove('active');
        if (!e.target.closest('.tg-sticker-panel') && !e.target.closest('[onclick*="tgToggleStickerPanel"]'))
            document.getElementById('tgStickerPanel').classList.remove('active');
    });

    document.getElementById('tgMessages').addEventListener('scroll', tgUpdateScrollDownButton);
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (tgRecording) tgCancelRecording();
            if (document.getElementById('tgCallModal').classList.contains('active')) tgEndCall();
            if (document.getElementById('tgIncomingCallModal')) tgDeclineCall();
        }
    });
});

// ===== ОПТИМИСТИЧНЫЙ РЕНДЕРИНГ =====
function tgPruneOptimistic() {
    tgPendingOptimistic = tgPendingOptimistic.filter(p => Date.now() - p.ts < 60000);
}

function tgRenderOptimisticMessage(body, type = 'text') {
    tgPruneOptimistic();
    const c = document.getElementById('tgMessages');
    const tempId = 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6);
    const d = document.createElement('div');
    d.className = 'tg-message mine new-msg' + (type === 'sticker' ? ' sticker-msg' : '');
    d.setAttribute('data-temp-id', tempId);
    
    let content = '';
    if (type === 'text') content = `<div class="tg-text">${tgLinkify(tgEscapeHtml(body))}</div>`;
    else if (type === 'sticker') content = `<span class="tg-sticker-emoji">${body}</span>`;
    
    const time = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
    const checks = '<span class="tg-checks" style="opacity:0.6;"><i class="bi bi-clock"></i></span>';
    
    d.innerHTML = `<div class="tg-bubble">${content}<div class="tg-time">${time}${checks}</div></div>`;
    c.appendChild(d);
    tgScrollDownInstant();
    
    tgPendingOptimistic.push({ tempId, body, type, convId: tgConvId, ts: Date.now() });
    return tempId;
}

// "Усыновление": серверное сообщение ЗАМЕНЯЕТ временный пузырёк НА МЕСТЕ (без дубликата)
function tgAdoptOptimistic(m) {
    const type = m.type || 'text';
    const idx = tgPendingOptimistic.findIndex(p => p.convId == tgConvId && p.type === type && p.body === m.body);
    if (idx === -1) return false;
    const p = tgPendingOptimistic[idx];
    const el = document.querySelector(`#tgMessages [data-temp-id="${p.tempId}"]`);
    if (!el) return false; // пузырёк уже удалён (смена чата) — пусть рендерится как обычно
    tgPendingOptimistic.splice(idx, 1);
    
    // Превращаем временный пузырёк в настоящий: меняем атрибуты и статус
    el.removeAttribute('data-temp-id');
    el.setAttribute('data-msg-id', m.id);
    const timeEl = el.querySelector('.tg-time');
    if (timeEl) {
        const time = new Date(m.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
        timeEl.innerHTML = time + `<span class="tg-checks">${m.is_read ? '<i class="bi bi-check-all"></i>' : '<i class="bi bi-check"></i>'}</span>`;
    }
    // Перепривязываем контекстное меню к реальному id
    el.oncontextmenu = null;
    el.addEventListener('contextmenu', (e) => tgShowContextMenu(e, m.id, true, m));
    return true;
}

// Обновление галочек НА МЕСТЕ: ✓ -> ✓✓ (когда собеседник прочитал)
function tgUpdateChecksInPlace(m) {
    const el = document.querySelector(`#tgMessages [data-msg-id="${m.id}"]`);
    if (!el) return;
    const checksEl = el.querySelector('.tg-checks');
    if (!checksEl) return;
    const want = m.is_read ? 'bi-check-all' : 'bi-check';
    const icon = checksEl.querySelector('i');
    if (icon && !icon.classList.contains(want)) {
        checksEl.innerHTML = `<i class="bi ${want}"></i>`;
        checksEl.style.opacity = '1';
    }
}

// Отметить оптимистичное сообщение как ОШИБКУ (если сервер не принял)
function tgMarkOptimisticFailed(tempId) {
    tgPendingOptimistic = tgPendingOptimistic.filter(p => p.tempId !== tempId);
    const el = document.querySelector(`#tgMessages [data-temp-id="${tempId}"]`);
    if (!el) return;
    const checksEl = el.querySelector('.tg-checks');
    if (checksEl) checksEl.innerHTML = '<i class="bi bi-exclamation-circle" style="color:#e53935;"></i>';
}

function tgStartSignalPolling() {
    if (tgSignalPollInterval) clearInterval(tgSignalPollInterval);
    tgSignalPollInterval = setInterval(async () => {
        const ids = new Set();
        if (tgConvId) ids.add(tgConvId);
        tgAllConversations.forEach(c => { if (c.type === 'private' && c.other_user_id) ids.add(c.id); });
        for (const id of ids) await tgPollSignals(id, id !== tgConvId);
    }, 1200);
}

async function tgPollSignals(conversationId, silent = false) {
    if (tgPollInProgress.has(conversationId)) return;
    tgPollInProgress.add(conversationId);
    const ctrl = new AbortController();
    const tid = setTimeout(() => ctrl.abort(), 5000);
    try {
        const res = await fetch(`${API}/video-signals/${conversationId}`, { method: 'GET', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, signal: ctrl.signal, credentials: 'same-origin' });
        clearTimeout(tid);
        if (!res.ok) return;
        const text = await res.text();
        if (!text || text.trim() === '' || text.trim() === '[]') return;
        let signals; try { signals = JSON.parse(text); } catch(e){ return; }
        if (!Array.isArray(signals) || !signals.length) return;
        for (const s of signals) {
            tgMarkSignalRead(s.id);
            if (tgProcessedSignals.has(s.id)) continue;
            tgProcessedSignals.add(s.id);
            if (tgProcessedSignals.size > 500) tgProcessedSignals.clear();
            tgHandleCallSignal(s, conversationId);
        }
    } catch(e){ if (e.name !== 'AbortError' && !silent) console.error('Poll err:', e); }
    finally { clearTimeout(tid); tgPollInProgress.delete(conversationId); }
}

function tgMarkSignalRead(id) {
    const fd = new FormData();
    fd.append('_token', TG_CSRF); fd.append('_method', 'DELETE');
    fetch(`${API}/video-signals/${id}`, { method:'POST', headers:{'X-CSRF-TOKEN':TG_CSRF}, body: fd }).catch(()=>{});
}

function tgHandleCallSignal(signal, fromConvId = null) {
    switch (signal.type) {
        case 'offer': tgHandleIncomingCall(signal, fromConvId); break;
        case 'answer': tgHandleAnswer(signal); break;
        case 'ice-candidate': tgHandleIceCandidate(signal); break;
        case 'reject': tgHandleCallRejected(signal); break;
        case 'end': tgCleanupCall(); tgShowToast('📞 Завершён','info'); break;
    }
}

function tgAttachPeerHandlers(pc) {
    pc.ontrack = (e) => { const rv = document.getElementById('tgRemoteVideo'); rv.srcObject = e.streams[0]; rv.play().catch(()=>{}); };
    pc.onicecandidate = (e) => { if (e.candidate) tgSendSignal('ice-candidate', e.candidate); };
    pc.oniceconnectionstatechange = () => {
        const st = pc.iceConnectionState;
        if (st === 'connected' || st === 'completed') {
            if (tgCallTimeout) { clearTimeout(tgCallTimeout); tgCallTimeout = null; }
            if (tgIceWatchdog) { clearTimeout(tgIceWatchdog); tgIceWatchdog = null; }
            tgRetryCount = 0; tgIceRestarted = false;
        } else if (st === 'disconnected') {
            setTimeout(() => { if (pc && pc.iceConnectionState === 'disconnected' && !tgIceRestarted) { tgIceRestarted = true; try { pc.restartIce(); } catch(e){} } }, 3000);
        } else if (st === 'failed') {
            if (!tgIceRestarted && tgRetryCount < 2) { tgIceRestarted = true; tgRetryCount++; try { pc.restartIce(); } catch(e){} }
        }
    };
}

function tgStartIceWatchdog() {
    if (tgIceWatchdog) clearTimeout(tgIceWatchdog);
    tgIceWatchdog = setTimeout(() => {
        if (tgPeerConnection && !tgIceRestarted && tgPeerConnection.iceConnectionState !== 'connected' && tgPeerConnection.iceConnectionState !== 'completed') {
            tgIceRestarted = true; try { tgPeerConnection.restartIce(); } catch(e){}
        }
    }, 25000);
}

async function tgGetMedia(audioOnly = false) {
    try {
        return await navigator.mediaDevices.getUserMedia({ video: audioOnly ? false : { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' }, audio: { echoCancellation: true, noiseSuppression: true } });
    } catch(e1) {
        if (!audioOnly) { try { return await navigator.mediaDevices.getUserMedia({ video: false, audio: { echoCancellation: true, noiseSuppression: true } }); } catch(e2) { throw e1; } }
        throw e1;
    }
}

async function tgStartVideoCall() { isAudioOnlyCall = false; await initiateCall(); }
async function tgStartAudioCall() { isAudioOnlyCall = true; await initiateCall(); }

async function initiateCall() {
    if (!tgCurrentOtherUserId || !tgConvId) { tgShowToast('❌ Выберите чат','error'); return; }
    if (tgPeerConnection) { tgShowToast('⚠️ Звонок уже идёт','info'); return; }
    tgIsCaller = true; tgPendingIce = []; tgIceRestarted = false; tgRetryCount = 0;
    tgCallId = Date.now()+'_'+Math.random().toString(36).substr(2,9);
    try {
        tgLocalStream = await tgGetMedia(isAudioOnlyCall);
        document.getElementById('tgLocalVideo').srcObject = tgLocalStream;
        tgPeerConnection = new RTCPeerConnection(tgIceServers);
        tgAttachPeerHandlers(tgPeerConnection);
        tgLocalStream.getTracks().forEach(t => tgPeerConnection.addTrack(t, tgLocalStream));
        const offer = await tgPeerConnection.createOffer({ offerToReceiveVideo: !isAudioOnlyCall, offerToReceiveAudio: true });
        await tgPeerConnection.setLocalDescription(offer);
        await tgSendSignal('offer', { ...offer, isAudioOnly: isAudioOnlyCall });
        openCallUI();
        tgStartIceWatchdog();
        tgCallTimeout = setTimeout(() => { if (tgPeerConnection && tgPeerConnection.iceConnectionState!=='connected' && tgPeerConnection.iceConnectionState!=='completed') { tgSendSignal('end',{reason:'timeout'}); tgCleanupCall(); } }, 45000);
    } catch(err) {
        let msg = isAudioOnlyCall ? 'Ошибка микрофона' : 'Ошибка камеры/микрофона';
        if (err.name==='NotAllowedError') msg = '❌ Разрешите доступ в браузере';
        tgShowToast(msg,'error'); tgCleanupCall();
    }
}

async function tgSendSignal(type, data, attempt = 0) {
    if (!tgConvId || !tgCurrentOtherUserId) return;
    const fd = new FormData();
    fd.append('_token', TG_CSRF); fd.append('call_id', tgCallId); fd.append('conversation_id', String(tgConvId));
    fd.append('from_user_id', String(TG_USER_ID)); fd.append('to_user_id', String(tgCurrentOtherUserId));
    fd.append('signal_type', type); fd.append('data', JSON.stringify(data || {}));
    try {
        const res = await fetch(`${API}/video-signal`, { method:'POST', headers:{'X-CSRF-TOKEN':TG_CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, body: fd });
        if (!res.ok && attempt < 2) { await new Promise(r=>setTimeout(r, 600*(attempt+1))); return tgSendSignal(type, data, attempt+1); }
    } catch(e){ if (attempt < 2) { await new Promise(r=>setTimeout(r, 600*(attempt+1))); return tgSendSignal(type, data, attempt+1); } }
}

function tgHandleIncomingCall(signal, fromConvId) {
    if (tgPeerConnection) { tgSendSignal('reject',{reason:'busy'}); return; }
    if (document.getElementById('tgIncomingCallModal')) return;
    tgIsCaller = false; tgCallId = signal.call_id; tgPendingOffer = signal.data;
    isAudioOnlyCall = signal.data.isAudioOnly || false;
    tgPendingIce = []; tgIceRestarted = false; tgRetryCount = 0;
    const callerId = signal.from_user_id;
    const convId = signal.conversation_id || fromConvId;
    const callerConv = tgAllConversations.find(c => c.id == convId) || null;
    tgConvId = convId; tgCurrentOtherUserId = callerId;
    if (callerConv) tgCurrentConvData = callerConv;
    tgIncomingCallData = { callerId, convId, conv: callerConv };
    const name = callerConv?.name || 'Собеседник';
    const av = callerConv?.avatar || 'https://ui-avatars.com/api/?name=User&size=120';
    const callTypeText = isAudioOnlyCall ? '📞 Входящий аудиозвонок...' : '📹 Входящий видеозвонок...';
    const modal = document.createElement('div');
    modal.className = 'tg-incoming-call-modal'; modal.id = 'tgIncomingCallModal';
    modal.innerHTML = `<div class="tg-incoming-call-content">
        <img src="${av}" class="tg-incoming-call-avatar" onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&size=120'">
        <div class="tg-incoming-call-name">${tgEscapeHtml(name)}</div>
        <div class="tg-incoming-call-status">${callTypeText}</div>
        <div class="tg-incoming-call-actions">
            <button class="tg-incoming-call-btn accept" onclick="tgAcceptCall()" title="Принять"><i class="bi bi-telephone-fill"></i></button>
            <button class="tg-incoming-call-btn decline" onclick="tgDeclineCall()" title="Отклонить"><i class="bi bi-telephone-x-fill"></i></button>
        </div></div>`;
    document.body.appendChild(modal);
    tgPlayRingtone();
    tgCallTimeout = setTimeout(() => { if (document.getElementById('tgIncomingCallModal')) { tgDeclineCall(); tgShowToast('⏰ Пропущенный вызов','info'); } }, 30000);
}

function tgPlayRingtone() {
    try {
        const ctx = new (window.AudioContext||window.webkitAudioContext)();
        const osc = ctx.createOscillator(), g = ctx.createGain();
        osc.connect(g); g.connect(ctx.destination);
        osc.frequency.value=440; osc.type='sine'; g.gain.value=0.1; osc.start();
        [0,400,800,1200,1600,2000,2400,2800].forEach((d,i)=>setTimeout(()=>{try{g.gain.value=i%2===0?0.1:0;}catch(e){}},d));
        tgRingtoneAudio = { stop:()=>{try{osc.stop();ctx.close();}catch(e){}} };
        setTimeout(()=>{if(tgRingtoneAudio){tgRingtoneAudio.stop();tgRingtoneAudio=null;}},3200);
    } catch(e){}
}
function tgStopRingtone(){ if(tgRingtoneAudio){tgRingtoneAudio.stop();tgRingtoneAudio=null;} }

async function tgAcceptCall() {
    const m = document.getElementById('tgIncomingCallModal'); if (m) m.remove();
    tgStopRingtone();
    if (!tgPendingOffer || !tgIncomingCallData) { tgShowToast('❌ Данные звонка устарели','error'); return; }
    const { conv } = tgIncomingCallData;
    if (conv && tgConvId != conv.id) { try { await tgOpenChat(conv); } catch(e){} }
    try {
        tgPeerConnection = new RTCPeerConnection(tgIceServers);
        tgAttachPeerHandlers(tgPeerConnection);
        tgLocalStream = await tgGetMedia(isAudioOnlyCall);
        document.getElementById('tgLocalVideo').srcObject = tgLocalStream;
        tgLocalStream.getTracks().forEach(t => tgPeerConnection.addTrack(t, tgLocalStream));
        await tgPeerConnection.setRemoteDescription(new RTCSessionDescription(tgPendingOffer));
        await tgFlushPendingIce();
        const ans = await tgPeerConnection.createAnswer();
        await tgPeerConnection.setLocalDescription(ans);
        await tgSendSignal('answer', ans);
        tgPendingOffer = null; tgIncomingCallData = null;
        openCallUI(); tgStartIceWatchdog();
    } catch(err) {
        tgShowToast('❌ Ошибка: ' + err.message,'error');
        if (tgCallId) tgSendSignal('reject', { reason: 'media-error' });
        tgCleanupCall();
    }
}

function tgDeclineCall() {
    const m = document.getElementById('tgIncomingCallModal'); if (m) m.remove();
    tgStopRingtone();
    if (tgCallId) tgSendSignal('reject',{reason:'declined'});
    if (tgCallTimeout){clearTimeout(tgCallTimeout);tgCallTimeout=null;}
    tgPendingOffer=null; tgIncomingCallData=null; tgPendingIce=[];
}

function tgHandleCallRejected(s){ if(tgCallTimeout){clearTimeout(tgCallTimeout);tgCallTimeout=null;} tgCleanupCall(); }
async function tgHandleAnswer(s){
    if(tgCallTimeout){clearTimeout(tgCallTimeout);tgCallTimeout=null;}
    if(tgPeerConnection){ try{ await tgPeerConnection.setRemoteDescription(new RTCSessionDescription(s.data)); await tgFlushPendingIce(); }catch(e){console.error(e);} }
}
async function tgHandleIceCandidate(s){
    if (tgPeerConnection && tgPeerConnection.remoteDescription) { try { await tgPeerConnection.addIceCandidate(new RTCIceCandidate(s.data)); } catch(e){} } 
    else { tgPendingIce.push(s.data); }
}
async function tgFlushPendingIce(){
    if (!tgPeerConnection || !tgPendingIce.length) return;
    for (const c of tgPendingIce) { try{ await tgPeerConnection.addIceCandidate(new RTCIceCandidate(c)); }catch(e){} }
    tgPendingIce = [];
}

function openCallUI(){
    const container = document.getElementById('tgCallContainer');
    document.getElementById('tgCallName').textContent = isAudioOnlyCall ? 'Аудиозвонок' : 'Видеозвонок';
    document.getElementById('tgCallerName').textContent = tgCurrentConvData?.name || 'Собеседник';
    if (isAudioOnlyCall) {
        container.style.backgroundImage = `url(${tgCurrentConvData?.avatar || 'https://ui-avatars.com/api/?name=User'})`;
        container.style.backgroundSize = 'cover'; container.style.backgroundPosition = 'center';
        container.style.filter = 'blur(20px) brightness(0.6)';
        document.getElementById('tgRemoteVideo').style.display = 'none';
    } else {
        container.style.backgroundImage = 'none'; container.style.filter = 'none';
        document.getElementById('tgRemoteVideo').style.display = 'block';
    }
    document.getElementById('tgCallModal').classList.add('active');
    tgCallStartTime = Date.now();
    tgCallTimer = setInterval(()=>{
        const d = Math.floor((Date.now()-tgCallStartTime)/1000);
        document.getElementById('tgCallTimer').textContent = `${String(Math.floor(d/60)).padStart(2,'0')}:${String(d%60).padStart(2,'0')}`;
    },1000);
}

function tgToggleCallMic(){ 
    if(!tgLocalStream) return; 
    const t = tgLocalStream.getAudioTracks()[0]; 
    if(t){
        t.enabled = !t.enabled; tgIsAudioMuted = !t.enabled;
        const b = document.getElementById('tgCallMicBtn');
        b.innerHTML = tgIsAudioMuted ? '<i class="bi bi-mic-mute-fill"></i>' : '<i class="bi bi-mic-fill"></i>';
        b.style.background = tgIsAudioMuted ? '#e53935' : 'rgba(255,255,255,0.2)';
    } 
}
function tgToggleCallCam(){ 
    if(!tgLocalStream) return; 
    const t = tgLocalStream.getVideoTracks()[0]; 
    if(t){
        t.enabled = !t.enabled; tgIsVideoMuted = !t.enabled;
        const b = document.getElementById('tgCallCamBtn');
        b.innerHTML = tgIsVideoMuted ? '<i class="bi bi-camera-video-off-fill"></i>' : '<i class="bi bi-camera-video-fill"></i>';
        b.style.background = tgIsVideoMuted ? '#e53935' : 'rgba(255,255,255,0.2)';
    } 
}
function tgEndCall(){ if(tgCallId) tgSendSignal('end',{reason:'hangup'}); tgCleanupCall(); }

function tgCleanupCall(){
    tgStopRingtone();
    if(tgCallTimeout){clearTimeout(tgCallTimeout);tgCallTimeout=null;}
    if(tgIceWatchdog){clearTimeout(tgIceWatchdog);tgIceWatchdog=null;}
    if(tgLocalStream){tgLocalStream.getTracks().forEach(t=>t.stop());tgLocalStream=null;}
    if(tgPeerConnection){tgPeerConnection.close();tgPeerConnection=null;}
    if(tgCallTimer){clearInterval(tgCallTimer);tgCallTimer=null;}
    document.getElementById('tgCallModal')?.classList.remove('active');
    document.getElementById('tgIncomingCallModal')?.remove();
    const rv=document.getElementById('tgRemoteVideo'), lv=document.getElementById('tgLocalVideo');
    if(rv)rv.srcObject=null; if(lv)lv.srcObject=null;
    tgIsCaller=false; tgCallId=null; tgPendingOffer=null; tgIncomingCallData=null; tgPendingIce=[];
    tgIsAudioMuted=false; tgIsVideoMuted=false; tgIceRestarted=false; tgRetryCount=0; isAudioOnlyCall = false;
    document.getElementById('tgCallMicBtn').innerHTML='<i class="bi bi-mic-fill"></i>'; document.getElementById('tgCallMicBtn').style.background='rgba(255,255,255,0.2)';
    document.getElementById('tgCallCamBtn').innerHTML='<i class="bi bi-camera-video-fill"></i>'; document.getElementById('tgCallCamBtn').style.background='rgba(255,255,255,0.2)';
}

// ===== CHAT FUNCTIONS =====
function tgUpdateCallButtonsUI(){ 
    const ok = (tgCurrentConvData?.type === 'private' || tgCurrentConvData?.type === 'group') && !tgCurrentConvData?.is_blocked; 
    document.getElementById('tgAudioCallBtn').style.display = ok ? 'flex' : 'none'; 
    document.getElementById('tgVideoCallBtn').style.display = ok ? 'flex' : 'none'; 
    document.getElementById('tgInfoBtn').style.display = ok ? 'flex' : 'none';
    document.getElementById('tgBlockBtn').style.display = (tgCurrentConvData?.type === 'private') ? 'flex' : 'none';
    document.getElementById('tgDeleteChatBtn').style.display = ok ? 'flex' : 'none';
}

function tgToggleStickerPanel(){ document.getElementById('tgStickerPanel').classList.toggle('active'); }

function tgLoadStickers(){ 
    const grid = document.getElementById('tgStickerGrid');
    // Фильтруем пустые строки, невидимые символы и модификаторы
    const validStickers = TG_STICKERS.filter(s => {
        if (!s || s.trim().length === 0) return false;
        // Проверяем что это видимый символ (не модификатор, не zero-width)
        const code = s.codePointAt(0);
        // Пропускаем невидимые символы и модификаторы
        if (code >= 0x1F3FB && code <= 0x1F3FF) return false; // skin tone modifiers
        if (code === 0x200D) return false; // zero-width joiner
        if (code === 0xFE0F) return false; // variation selector
        return true;
    });
    grid.innerHTML = validStickers.map(s => `<div class="tg-sticker-btn" onclick="tgSendSticker('${s}')">${s}</div>`).join(''); 
}

async function tgSendSticker(s){ 
    if(!tgConvId)return; 
    document.getElementById('tgStickerPanel').classList.remove('active'); 
    const tempId = tgRenderOptimisticMessage(s, 'sticker');
    const fd=new FormData(); 
    fd.append('conversation_id',tgConvId); 
    fd.append('body',s); 
    fd.append('type','sticker'); 
    try{
        const r = await fetch(`${API}/send`,{method:'POST',headers:{'X-CSRF-TOKEN':TG_CSRF},body:fd}); 
        if(!r.ok) tgMarkOptimisticFailed(tempId);
        tgLoadConversations();
    }catch(e){ tgMarkOptimisticFailed(tempId); tgShowToast('❌ Ошибка','error'); } 
}

function tgStartHeartbeat(){ tgHeartbeatInterval=setInterval(async()=>{try{await fetch('/heartbeat');}catch(e){}},30000); }
async function tgLoadConversations(){ try{const r=await fetch(`${API}/conversations`); if(!r.ok)return; tgAllConversations=await r.json(); tgRenderConversations(tgAllConversations);}catch(e){} }

function tgRenderConversations(convs){
    const list=document.getElementById('tgList'), q=document.getElementById('tgSearch').value.toLowerCase().trim();
    const f=convs.filter(c=>!q||(c.name||'').toLowerCase().includes(q)||(c.last_message?.text||'').toLowerCase().includes(q));
    list.innerHTML='';
    if(!f.length){ list.innerHTML=`<div style="padding:40px;text-align:center;color:var(--hint);">Нет чатов</div>`; return; }
    f.forEach(c=>{
        const d=document.createElement('div'); d.className='tg-item'+(c.id===tgConvId?' active':''); d.onclick=()=>tgOpenChat(c);
        const fb=`https://ui-avatars.com/api/?name=${encodeURIComponent(c.name)}&size=54&background=random&color=fff`;
        let lastMsgText = c.last_message ? c.last_message.text : 'Нет сообщений';
        if(c.last_message && c.last_message.type === 'sticker') lastMsgText = '🎭 Стикер';
        
        d.innerHTML=`<img src="${c.avatar || fb}" class="tg-item-avatar" onerror="this.src='${fb}'">
            <div class="tg-item-info">
                <div class="tg-item-name">${c.type==='group'?'<i class="bi bi-people-fill" style="font-size:12px; color:#999;"></i>':''}${tgEscapeHtml(c.name)}</div>
                <div class="tg-item-last">${tgEscapeHtml(lastMsgText)}</div>
            </div>
            <div class="tg-item-meta">
                <div>${c.last_message?c.last_message.time:''}</div>
                ${c.unread_count>0?`<div class="tg-unread-badge">${c.unread_count>99?'99+':c.unread_count}</div>`:''}
            </div>`;
        list.appendChild(d);
    });
}

async function tgOpenChat(conv){
    if(!conv?.id)return;
    tgConvId=conv.id; tgLastMsgId=0; tgLastRenderedDate=null; tgCurrentConvData=conv; tgCurrentOtherUserId=conv.other_user_id||null;
    tgPendingOptimistic = []; // сбрасываем очередь при смене чата
    if(window.matchMedia('(max-width:768px)').matches) document.getElementById('tgContainer').classList.add('show-chat');
    document.getElementById('tgEmpty').style.display='none';
    document.getElementById('tgHeader').style.display='flex';
    document.getElementById('tgMessages').style.display='flex';
    document.getElementById('tgFooterWrap').style.display='flex';
    document.getElementById('tgHeaderName').textContent=conv.name;
    const av=document.getElementById('tgHeaderAvatar'); 
    av.src=conv.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(conv.name)}&size=42&background=random`;
    const pl=document.getElementById('tgHeaderProfileLink');
    if(conv.type==='private'&&conv.other_user_id){pl.href=`/users/${conv.other_user_id}`;pl.onclick=null;}
    else{pl.href='#';pl.onclick=(e)=>{e.preventDefault();tgOpenInfo(conv.id);};}
    tgSetDefaultStatus(); tgUpdateBlockBtnUI(); tgUpdateCallButtonsUI();
    const banner=document.getElementById('tgBlockedBanner'), footer=document.getElementById('tgFooterWrap');
    if(conv.is_blocked&&conv.type==='private'){banner.style.display='block';footer.style.display='none';}
    else{banner.style.display='none';footer.style.display='flex';}
    document.getElementById('tgMessages').innerHTML='';
    await tgLoadMessages(); await tgMarkAsRead(conv.id); tgScrollDownInstant();
    tgStartTypingPoll(); await tgLoadConversations();
}

function tgSetDefaultStatus(){ 
    const s=document.getElementById('tgHeaderStatus'); 
    if(!s||!tgCurrentConvData)return; 
    if(tgCurrentConvData.type==='group'){ s.textContent=`${tgCurrentConvData.member_count||0} участников`; return; } 
    s.textContent=tgCurrentConvData.is_online?'в сети':'был(а) недавно'; 
    s.style.color = tgCurrentConvData.is_online ? '#3390ec' : 'var(--hint)';
}

function tgUpdateBlockBtnUI(){ 
    const b=document.getElementById('tgBlockBtn');
    if(!tgCurrentConvData)return; 
    if(tgCurrentConvData.type==='private'&&tgCurrentOtherUserId){
        b.style.display='flex'; 
        if(tgCurrentConvData.is_blocked){ b.innerHTML='<i class="bi bi-slash-circle-fill"></i>'; b.style.color='#e53935'; }
        else{ b.innerHTML='<i class="bi bi-slash-circle"></i>'; b.style.color=''; }
    }else b.style.display='none'; 
}

function tgGoBackToList(){ document.getElementById('tgContainer').classList.remove('show-chat'); }
function tgSendTyping(){ 
    if(!tgConvId||tgTypingTimeout)return; 
    fetch(`${API}/typing`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':TG_CSRF},body:JSON.stringify({conversation_id:tgConvId})}).catch(()=>{}); 
    tgTypingTimeout=setTimeout(()=>{tgTypingTimeout=null;},3000); 
}
function tgStartTypingPoll(){ if(tgTypingPollInterval)clearInterval(tgTypingPollInterval); tgTypingPollInterval=setInterval(async()=>{ if(!tgConvId)return; try{const r=await fetch(`${API}/typing/${tgConvId}`); const d=await r.json(); tgShowTyping(d.typers||[]);}catch(e){} },2000); }
function tgShowTyping(t){ const s=document.getElementById('tgHeaderStatus'); if(!s)return; if(!t.length){tgSetDefaultStatus();return;} s.innerHTML=`<span style="color:#3390ec;">${tgEscapeHtml(t.join(', '))} печатает...</span>`; }
async function tgMarkAsRead(id){ try{await fetch(`${API}/mark-read/${id}`,{method:'POST',headers:{'X-CSRF-TOKEN':TG_CSRF}});}catch(e){} }
async function tgLoadMessages(){ if(!tgConvId)return; try{const r=await fetch(`${API}/messages/${tgConvId}`); if(!r.ok)return; tgRenderMessages(await r.json());}catch(e){} }

// ===== ГЛАВНАЯ ФУНКЦИЯ РЕНДЕРА (БЕЗ ДУБЛИКАТОВ) =====
function tgRenderMessages(msgs){ 
    const c=document.getElementById('tgMessages');
    const near=c.scrollHeight-c.scrollTop-c.clientHeight<120; 
    let hasNew=false; 
    msgs.forEach(m=>{
        const mine = String(m.sender_id)===String(TG_USER_ID);
        
        // 1) Сообщение УЖЕ есть на экране — только обновляем галочки на месте
        if (m.id <= tgLastMsgId) {
            if (mine) tgUpdateChecksInPlace(m);
            return;
        }
        
        // 2) Моё сообщение с сервера — ЗАМЕНЯЕМ временный пузырёк (без дубликата!)
        if (mine && tgAdoptOptimistic(m)) {
            tgLastMsgId = m.id;
            return;
        }
        
        // 3) Обычное новое сообщение (чаще от собеседника) — рисуем
        tgRenderDateDividerIfNeeded(m); 
        tgRenderMessage(m, !mine); 
        tgLastMsgId = m.id; 
        if(!mine) hasNew=true; 
    }); 
    if(near)tgScrollDownInstant(); 
    tgUpdateScrollDownButton(); 
    if(hasNew&&tgConvId&&!document.hidden)tgMarkAsRead(tgConvId); 
}

function tgRenderDateDividerIfNeeded(m){ 
    const ds=new Date(m.created_at).toDateString(); 
    if(tgLastRenderedDate===ds)return; 
    const d=document.createElement('div'); d.className='tg-date-divider'; 
    d.innerHTML=`<span>${new Date(m.created_at).toLocaleDateString('ru-RU',{day:'numeric',month:'long'})}</span>`; 
    document.getElementById('tgMessages').appendChild(d); 
    tgLastRenderedDate=ds; 
}

function tgLinkify(e){ return e.replace(/(https?:\/\/[^\s<]+)/g,u=>`<a href="${u}" target="_blank" rel="noopener noreferrer">${u}</a>`); }

function tgRenderMessage(m, animate = false){
    const c=document.getElementById('tgMessages'), mine=String(m.sender_id)===String(TG_USER_ID);
    const d=document.createElement('div'); 
    d.className=`tg-message ${mine?'mine':'theirs'} ${m.type==='sticker'?'sticker-msg':''}${animate?' new-msg':''}`; 
    d.setAttribute('data-msg-id',m.id);
    let content='';
    
  if(m.type==='text') {
    const bodyText = (m.body === null || m.body === undefined) ? '' : String(m.body);
    content+=`<div class="tg-text">${tgLinkify(tgEscapeHtml(bodyText))}</div>`;
}
 else if(m.type==='sticker') content+=`<span class="tg-sticker-emoji">${m.body}</span>`;
    else if(m.type==='image'&&m.attachment_url) content+=`<img src="${m.attachment_url}" class="tg-image" onclick="window.open(this.src)">`;
    else if(m.type==='file'&&m.attachment_url){ 
        const fn=decodeURIComponent((m.attachment_url||'').split('/').pop()||'File'); 
        content+=`<a href="${m.attachment_url}" target="_blank" class="tg-file" download><div class="tg-file-icon"><i class="bi bi-file-earmark"></i></div><div><div class="tg-file-name">${tgEscapeHtml(fn)}</div><div class="tg-file-size">Файл</div></div></a>`; 
    }
    else if(m.type==='voice'&&m.attachment_url) content+=`<div class="tg-voice-msg"><div class="tg-voice-icon"><i class="bi bi-play-fill"></i></div><audio controls src="${m.attachment_url}" class="tg-voice-audio" preload="metadata"></audio></div>`;
    
    const time=new Date(m.created_at).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
    const checks=mine?`<span class="tg-checks">${m.is_read?'<i class="bi bi-check-all"></i>':'<i class="bi bi-check"></i>'}</span>`:'';
    const sender=(!mine&&m.sender&&tgCurrentConvData?.type==='group')?`<div class="tg-sender">${tgEscapeHtml(m.sender.name)}</div>`:'';
    
    d.innerHTML=`<div class="tg-bubble">${sender}${content}<div class="tg-time">${time}${checks}</div></div>`;
    d.addEventListener('contextmenu',(e)=>tgShowContextMenu(e,m.id,mine,m));
    c.appendChild(d);
    
    if(m.type==='voice'){ 
        const a=d.querySelector('audio'); 
        if(a) a.addEventListener('loadedmetadata',()=>{ if(!isFinite(a.duration)||a.duration===0||isNaN(a.duration)){ a.currentTime=1e101; a.ontimeupdate=()=>{a.ontimeupdate=null;a.currentTime=0;}; } }); 
    }
}

function tgEscapeHtml(s){ 
    if(s === null || s === undefined || s === '') return ''; 
    const d=document.createElement('div'); 
    d.textContent=String(s); 
    return d.innerHTML; 
}
async function tgSendText(t){ 
    if(!t||!tgConvId)return; 
    
    // 1. СРАЗУ показываем ОДИН пузырёк с часиками
    const tempId = tgRenderOptimisticMessage(t, 'text');
    
    // 2. Очищаем поле
    document.getElementById('tgInput').value=''; 
    tgAutoResize(document.getElementById('tgInput')); 
    tgUpdateBtn(); tgUpdateCharCounter(); tgCancelReply(); 
    
    // 3. Отправляем в фоне. Серверное сообщение ОБНОВИТ этот же пузырёк (галочка появится на нём)
    const fd=new FormData(); 
    fd.append('conversation_id',tgConvId); fd.append('body',t); fd.append('type','text'); 
    if(tgReplyTo)fd.append('reply_to',tgReplyTo.id); 
    try{
        const r = await fetch(`${API}/send`,{method:'POST',headers:{'X-CSRF-TOKEN':TG_CSRF},body:fd}); 
        if(!r.ok) tgMarkOptimisticFailed(tempId);
        tgLoadConversations();
    }catch(e){ tgMarkOptimisticFailed(tempId); tgShowToast('❌ Ошибка отправки','error'); } 
}

async function tgUploadFile(inp){ 
    if(!inp.files[0]||!tgConvId)return; 
    const f=inp.files[0]; 
    if(f.size>50*1024*1024){tgShowToast('Файл > 50MB','error');inp.value='';return;} 
    const fd=new FormData(); 
    fd.append('conversation_id',tgConvId); fd.append('attachment',f); 
    fd.append('type',f.type.startsWith('image/')?'image':'file'); fd.append('body',''); 
    tgShowToast('📤 Отправка...','info'); 
    try{
        const r=await fetch(`${API}/send`,{method:'POST',headers:{'X-CSRF-TOKEN':TG_CSRF},body:fd}); 
        if(r.ok){inp.value='';tgLoadMessages();tgLoadConversations();tgShowToast('✅ Отправлено','success');} else tgShowToast('❌ Ошибка','error');
    }catch(e){tgShowToast('❌ Ошибка','error');inp.value='';} 
}

function tgHandleAction(){ 
    if(tgRecording) {
        tgToggleVoice(); 
        return; 
    }
    const i=document.getElementById('tgInput'); 
    if(i.value.trim()) tgSendText(i.value.trim()); 
    else tgToggleVoice(); 
}

let tgAudioContext=null,tgAnalyser=null,tgLevelTimer=null,tgMaxLevel=0,tgLevelMeterOk=false;
function tgStartLevelMeter(stream){ tgLevelMeterOk=false; tgMaxLevel=0; try{ tgAudioContext=new (window.AudioContext||window.webkitAudioContext)(); if(tgAudioContext.state==='suspended')tgAudioContext.resume(); const src=tgAudioContext.createMediaStreamSource(stream); tgAnalyser=tgAudioContext.createAnalyser(); tgAnalyser.fftSize=512; src.connect(tgAnalyser); const data=new Uint8Array(tgAnalyser.frequencyBinCount); tgLevelMeterOk=true; tgLevelTimer=setInterval(()=>{ tgAnalyser.getByteTimeDomainData(data); let p=0; for(let i=0;i<data.length;i++){const v=Math.abs(data[i]-128)/128; if(v>p)p=v;} if(p>tgMaxLevel)tgMaxLevel=p; },100); }catch(e){console.warn(e);} }
function tgStopLevelMeter(){ if(tgLevelTimer){clearInterval(tgLevelTimer);tgLevelTimer=null;} if(tgAudioContext){try{tgAudioContext.close();}catch(e){}tgAudioContext=null;tgAnalyser=null;} }

async function tgToggleVoice(){
    if(!navigator.mediaDevices?.getUserMedia||typeof MediaRecorder==='undefined'){tgShowToast('Браузер не поддерживает','error');return;}
    if(!tgRecording){
        try{
            const stream=await navigator.mediaDevices.getUserMedia({audio:{echoCancellation:false,noiseSuppression:false,autoGainControl:false,channelCount:1}});
            tgStartLevelMeter(stream); tgCurrentMimeType='';
            for(const t of ['audio/webm;codecs=opus','audio/webm','audio/ogg;codecs=opus','audio/ogg','audio/mp4']) if(MediaRecorder.isTypeSupported&&MediaRecorder.isTypeSupported(t)){tgCurrentMimeType=t;break;}
            tgMediaRecorder=new MediaRecorder(stream,tgCurrentMimeType?{mimeType:tgCurrentMimeType}:{});
            tgAudioChunks=[]; tgRecordStartTime=Date.now(); tgDiscardRecording=false;
            tgMediaRecorder.ondataavailable=e=>{if(e.data?.size>0)tgAudioChunks.push(e.data);};
            tgMediaRecorder.onstop=async()=>{
                stream.getTracks().forEach(t=>t.stop()); tgStopLevelMeter();
                if(tgDiscardRecording){tgDiscardRecording=false;tgAudioChunks=[];tgResetVoiceUI();return;}
                const dur=Math.floor((Date.now()-tgRecordStartTime)/1000);
                if(!tgAudioChunks.length||dur<1){tgResetVoiceUI();tgShowToast('Слишком коротко','error');return;}
                if(tgLevelMeterOk&&tgMaxLevel<0.01){tgResetVoiceUI();tgShowToast('🎤 Микрофон молчит!','error');return;}
                const blob=new Blob(tgAudioChunks,{type:tgCurrentMimeType||'audio/webm'});
                let ext='webm'; if(tgCurrentMimeType.includes('mp4'))ext='m4a'; else if(tgCurrentMimeType.includes('ogg'))ext='ogg';
                const fd=new FormData(); fd.append('conversation_id',tgConvId); fd.append('attachment',new File([blob],`voice_${Date.now()}.${ext}`,{type:tgCurrentMimeType||'audio/webm'})); fd.append('type','voice'); fd.append('body','');
                try{const r=await fetch(`${API}/send`,{method:'POST',headers:{'X-CSRF-TOKEN':TG_CSRF},body:fd}); if(r.ok){tgLoadMessages();tgLoadConversations();tgShowToast('🎤 Отправлено','success');}}catch(e){tgShowToast('❌ Ошибка','error');}
                tgResetVoiceUI();
            };
            tgMediaRecorder.start(); tgRecording=true;
            
            document.getElementById('tgActionBtn').classList.add('recording');
            document.getElementById('tgMicIcon').style.display='none';
            document.getElementById('tgSendIcon').style.display='none';
            document.getElementById('tgStopIcon').style.display='block';
            
            document.getElementById('tgCancelRecBtn').style.display='flex';
            document.getElementById('tgInput').placeholder='Запись... Нажмите красную кнопку для отправки';
            document.getElementById('tgInput').disabled=true;
            document.getElementById('tgRecordingIndicator').classList.add('active');
            
            tgRecordTimerInterval=setInterval(()=>{const s=Math.floor((Date.now()-tgRecordStartTime)/1000);document.getElementById('tgRecordTimer').textContent=`${Math.floor(s/60)}:${String(s%60).padStart(2,'0')}`;},500);
        }catch(e){tgStopLevelMeter();tgShowToast('Ошибка микрофона: '+e.message,'error');}
    } else {
        if(tgMediaRecorder?.state==='recording'){ try{tgMediaRecorder.requestData();}catch(e){} await new Promise(r=>setTimeout(r,200)); tgMediaRecorder.stop(); }
    }
}

function tgCancelRecording(){ if(!tgRecording)return; tgDiscardRecording=true; if(tgMediaRecorder?.state==='recording'){try{tgMediaRecorder.stop();}catch(e){}} tgShowToast('🚫 Отменено','info'); }

function tgResetVoiceUI(){ 
    tgRecording=false; tgDiscardRecording=false; 
    if(tgRecordTimerInterval){clearInterval(tgRecordTimerInterval);tgRecordTimerInterval=null;} 
    document.getElementById('tgRecordingIndicator').classList.remove('active'); 
    
    document.getElementById('tgActionBtn').classList.remove('recording'); 
    document.getElementById('tgCancelRecBtn').style.display='none'; 
    
    document.getElementById('tgStopIcon').style.display='none';
    document.getElementById('tgMicIcon').style.display='block'; 
    document.getElementById('tgSendIcon').style.display='none'; 
    
    const i=document.getElementById('tgInput'); 
    i.placeholder='Сообщение'; 
    i.disabled=false; 
    tgUpdateBtn(); 
}

function tgHandleKey(e){ if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();tgHandleAction();} }
function tgAutoResize(el){ el.style.height='auto'; el.style.height=Math.min(el.scrollHeight,150)+'px'; tgUpdateBtn(); }
function tgUpdateBtn(){ 
    if(tgRecording) return;
    const h=document.getElementById('tgInput').value.trim().length>0; 
    document.getElementById('tgMicIcon').style.display=h?'none':'block'; 
    document.getElementById('tgSendIcon').style.display=h?'block':'none'; 
    document.getElementById('tgStopIcon').style.display='none';
}
function tgUpdateCharCounter(){ const len = document.getElementById('tgInput').value.length; document.getElementById('tgCharCounter').textContent = len > 0 ? len : ''; }
function tgScrollDownInstant(){ const m=document.getElementById('tgMessages'); if(m)m.scrollTop=m.scrollHeight; }
function tgScrollDown(){ document.getElementById('tgMessages').scrollTo({top:document.getElementById('tgMessages').scrollHeight,behavior:'smooth'}); }
function tgUpdateScrollDownButton(){ const el=document.getElementById('tgMessages'),b=document.getElementById('tgScrollDownBtn'); if((el.scrollHeight-el.scrollTop-el.clientHeight)>200) b.style.display='flex'; else b.style.display='none'; }

function tgShowContextMenu(e,id,mine,m){ 
    e.preventDefault(); 
    tgContextMessageId=id; tgContextMessageData=m; 
    const menu=document.getElementById('tgContextMenu'); 
    document.getElementById('tgCtxDelete').style.display=mine?'flex':'none'; 
    let x = e.clientX, y = e.clientY;
    menu.style.left = x + 'px'; menu.style.top = y + 'px'; 
    menu.classList.add('active'); 
    const rect = menu.getBoundingClientRect();
    if (rect.right > window.innerWidth) menu.style.left = (x - rect.width) + 'px';
    if (rect.bottom > window.innerHeight) menu.style.top = (y - rect.height) + 'px';
}

function tgReplyToMessage(){ 
    if(!tgContextMessageData)return; 
    tgReplyTo={id:tgContextMessageData.id}; 
    document.getElementById('tgReplyPreview').style.display='block'; 
    document.getElementById('tgReplyAuthor').textContent=tgContextMessageData.sender?.name||'Вы'; 
    document.getElementById('tgReplyText').textContent=tgContextMessageData.body||'[медиа]'; 
    document.getElementById('tgInput').focus(); 
    document.getElementById('tgContextMenu').classList.remove('active'); 
}
function tgCancelReply(){ tgReplyTo=null; document.getElementById('tgReplyPreview').style.display='none'; }
function tgCopyMessage(){ if(!tgContextMessageData?.body)return; navigator.clipboard.writeText(tgContextMessageData.body).then(()=>tgShowToast('✅ Скопировано','success')); document.getElementById('tgContextMenu').classList.remove('active'); }

async function tgDeleteMessage(){ 
    if(!tgContextMessageId||!confirm('Удалить сообщение?'))return; 
    try{
        const r=await fetch(`${API}/message/${tgContextMessageId}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':TG_CSRF}}); 
        if(r.ok){ const el=document.querySelector(`[data-msg-id="${tgContextMessageId}"]`); if(el)el.remove(); tgShowToast('🗑 Удалено','success'); }
    }catch(e){tgShowToast('❌ Ошибка','error');} 
    document.getElementById('tgContextMenu').classList.remove('active'); 
}

function tgDeleteCurrentChat() {
    if (!tgConvId) return;
    const chatName = tgCurrentConvData?.name || 'этот чат';
    if (confirm(`Вы уверены, что хотите удалить чат "${chatName}"?`)) tgDeleteChatById(tgConvId);
}

async function tgOpenInfo(id){ 
    document.getElementById('tgInfoDrawer').classList.add('open'); 
    const b=document.getElementById('tgInfoBody'); 
    b.innerHTML='<div style="padding:60px;text-align:center;"><i class="bi bi-arrow-repeat" style="font-size:24px;animation:spin 1s linear infinite;"></i></div>'; 
    try{const r=await fetch(`${API}/info/${id}`); tgRenderInfo(await r.json());}catch(e){b.innerHTML='<div style="padding:40px;text-align:center;">Ошибка</div>';} 
}
function tgCloseInfo(){ document.getElementById('tgInfoDrawer').classList.remove('open'); }

function tgRenderInfo(d){
    tgInfoData=d; const b=document.getElementById('tgInfoBody');
    const fb=`https://ui-avatars.com/api/?name=${encodeURIComponent(d.name)}&size=100&background=random`;
    let h=`<div class="tg-info-cover"><img src="${d.avatar || fb}" onerror="this.src='${fb}'"><div class="tg-info-cover-name">${tgEscapeHtml(d.name)}</div></div>`;
    if(d.type==='group'){
        h+=`<div class="tg-info-section"><div class="tg-info-row"><div class="tg-info-label">Участники</div><div class="tg-info-value">${d.member_count}</div></div></div><div class="tg-info-section">`;
        d.members.forEach(m=>{ 
            const af=`https://ui-avatars.com/api/?name=${encodeURIComponent(m.name)}&size=40&background=random`; 
            h+=`<div class="tg-info-row" style="flex-direction:row; align-items:center; gap:12px;"><img src="${m.avatar || af}" style="width:40px;height:40px;border-radius:50%;"><div><div style="font-weight:500;">${tgEscapeHtml(m.name)}</div><div style="font-size:12px;color:var(--hint);">${m.is_creator?'Создатель':(m.online?'В сети':'Был(а) недавно')}</div></div></div>`; 
        });
        h+=`</div><div class="tg-danger-zone"><button class="tg-btn-danger" onclick="tgLeaveGroup()">Покинуть группу</button></div>`;
    } else {
        h+=`<div class="tg-info-section"><div class="tg-info-row"><div class="tg-info-label">Имя</div><div class="tg-info-value">${tgEscapeHtml(d.name)}</div></div><div class="tg-info-row"><div class="tg-info-label">Email</div><div class="tg-info-value">${tgEscapeHtml(d.other_user?.email||'—')}</div></div></div><div class="tg-danger-zone"><button class="tg-btn-danger" onclick="tgDeleteChatById(${d.id})">Удалить чат</button></div>`;
    }
    b.innerHTML=h;
}

function tgOpenNewChatModal(){ document.getElementById('tgNewChatModal').classList.add('active'); document.getElementById('tgNewChatSearch').value=''; tgSearchNewChat(''); }
function tgCloseNewChatModal(){ document.getElementById('tgNewChatModal').classList.remove('active'); }
async function tgSearchNewChat(q){ try{const r=await fetch(`${API}/users?q=${encodeURIComponent(q)}`); const u=await r.json(); const c=document.getElementById('tgNewChatResults'); c.innerHTML=''; if(!u?.length){c.innerHTML='<div style="padding:30px;text-align:center;color:var(--hint);">Не найдено</div>';return;} u.forEach(x=>{const fb=`https://ui-avatars.com/api/?name=${encodeURIComponent(x.name)}&size=44&background=random`; const d=document.createElement('div'); d.className='tg-user-result'; d.innerHTML=`<img src="${x.avatar||fb}" onerror="this.src='${fb}'"><div class="tg-user-result-info"><div class="tg-user-result-name">${tgEscapeHtml(x.name)}</div><div class="tg-user-result-meta">${tgEscapeHtml([x.email,x.phone].filter(Boolean).join(' • '))}</div></div>`; d.onclick=()=>tgStartPrivateChat(x); c.appendChild(d);}); }catch(e){} }
async function tgStartPrivateChat(u){ try{tgShowToast('💬 Открываем...','info'); const r=await fetch(`${API}/private`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':TG_CSRF},body:JSON.stringify({user_id:u.id})}); const d=await r.json(); if(r.ok){tgCloseNewChatModal(); await tgLoadConversations(); const c=tgAllConversations.find(x=>x.id===d.id); if(c)tgOpenChat(c);}}catch(e){tgShowToast('❌ Ошибка','error');} }

function tgOpenGroupModal(){ document.getElementById('tgGroupModal').classList.add('active'); document.getElementById('tgGroupName').value=''; document.getElementById('tgUserSearch').value=''; tgSelectedUsers.clear(); tgRenderSelectedUsers(); tgSearchUsers('','group'); }
function tgCloseGroupModal(){ document.getElementById('tgGroupModal').classList.remove('active'); }
async function tgSearchUsers(q,mode){ try{const r=await fetch(`${API}/users?q=${encodeURIComponent(q)}`); const u=await r.json(); const c=document.getElementById('tgUserResults'); c.innerHTML=''; u.forEach(x=>{const fb=`https://ui-avatars.com/api/?name=${encodeURIComponent(x.name)}&size=44&background=random`; const d=document.createElement('div'); d.className='tg-user-result'+(tgSelectedUsers.has(x.id)?' selected':''); d.innerHTML=`<img src="${x.avatar||fb}" onerror="this.src='${fb}'"><div class="tg-user-result-info"><div class="tg-user-result-name">${tgEscapeHtml(x.name)}</div><div class="tg-user-result-meta">${tgEscapeHtml([x.email,x.phone].filter(Boolean).join(' • '))}</div></div><i class="bi bi-${tgSelectedUsers.has(x.id)?'check-circle-fill text-primary':'plus-circle'}"></i>`; d.onclick=()=>{if(tgSelectedUsers.has(x.id))tgSelectedUsers.delete(x.id);else tgSelectedUsers.set(x.id,x); tgRenderSelectedUsers(); tgSearchUsers(q,mode);}; c.appendChild(d);}); }catch(e){} }
function tgRenderSelectedUsers(){ const c=document.getElementById('tgSelectedUsers'); c.innerHTML=''; tgSelectedUsers.forEach(u=>{const fb=`https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&size=20`; const t=document.createElement('div'); t.className='tg-user-tag'; t.style.cssText='background:#e3f2fd;color:#1565c0;padding:4px 10px;border-radius:16px;font-size:13px;display:flex;align-items:center;gap:6px;'; t.innerHTML=`${tgEscapeHtml(u.name)}<span style="cursor:pointer;" onclick="tgSelectedUsers.delete(${u.id}); tgRenderSelectedUsers(); tgSearchUsers(document.getElementById('tgUserSearch').value,'group');"><i class="bi bi-x"></i></span>`; c.appendChild(t);}); }
async function tgCreateGroup(){ const n=document.getElementById('tgGroupName').value.trim(), ids=Array.from(tgSelectedUsers.keys()); if(!n){tgShowToast('Введите название','error');return;} if(!ids.length){tgShowToast('Выберите участников','error');return;} try{const r=await fetch(`${API}/group`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':TG_CSRF},body:JSON.stringify({name:n,user_ids:ids})}); if(r.ok){tgCloseGroupModal();tgLoadConversations();tgShowToast('🎉 Создана','success');}}catch(e){tgShowToast('❌ Ошибка','error');} }

function tgToggleBlockModal(){ if(!tgCurrentOtherUserId)return; const ib=tgCurrentConvData?.is_blocked, un=tgCurrentConvData?.name; document.getElementById('tgBlockModalTitle').textContent=ib?'Разблокировать?':'Заблокировать?'; document.getElementById('tgBlockModalText').textContent=ib?`"${un}" снова сможет писать вам.`:`"${un}" не сможет писать вам.`; document.getElementById('tgBlockConfirmBtn').textContent=ib?'Разблокировать':'Заблокировать'; document.getElementById('tgBlockConfirmBtn').style.background=ib?'#4caf50':'#e53935'; document.getElementById('tgBlockModal').classList.add('active'); }
function tgCloseBlockModal(){ document.getElementById('tgBlockModal').classList.remove('active'); }
async function tgConfirmBlock(){ const ib=tgCurrentConvData?.is_blocked; tgCloseBlockModal(); try{const r=await fetch(`${API}/block/${tgCurrentOtherUserId}`,{method:ib?'DELETE':'POST',headers:{'X-CSRF-TOKEN':TG_CSRF,'Content-Type':'application/json'}}); if(r.ok){if(tgCurrentConvData)tgCurrentConvData.is_blocked=!ib; tgShowToast(ib?'✅ Разблокирован':'✅ Заблокирован','success'); await tgLoadConversations(); const u=tgAllConversations.find(c=>c.id===tgConvId); if(u)await tgOpenChat(u);}}catch(e){tgShowToast('❌ Ошибка','error');} }
async function tgUnblockUser(){ if(tgCurrentOtherUserId)await tgConfirmBlock(); }

async function tgDeleteChatById(id){ 
    try{
        const r=await fetch(`${API}/conversation/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':TG_CSRF}}); 
        if(r.ok){
            tgShowToast('🗑 Чат удалён','success'); tgCloseInfo(); tgConvId=null; tgLoadConversations();
            document.getElementById('tgEmpty').style.display='flex'; document.getElementById('tgHeader').style.display='none';
            document.getElementById('tgMessages').style.display='none'; document.getElementById('tgFooterWrap').style.display='none';
        }
    }catch(e){tgShowToast('❌ Ошибка','error');} 
}

function tgShowToast(msg,type='info'){ 
    const o=document.querySelector('.tg-toast-live'); if(o)o.remove(); 
    const t=document.createElement('div'); t.className='tg-toast-live'; t.textContent = msg;
    document.body.appendChild(t); 
    setTimeout(()=>t.classList.add('show'), 10);
    setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),300);},3000); 
}

window.addEventListener(    'beforeunload',()=>{ 
    if(tgSocket) tgSocket.close();
    if(tgPeerConnection){ tgSendSignal('end',{reason:'page-close'}); tgCleanupCall(); } 
});
</script>
@endsection