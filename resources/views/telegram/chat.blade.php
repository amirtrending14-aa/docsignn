@extends('layouts.admin')
@section('content')
<meta name="user-id" content="{{ auth()->id() }}">
<meta name="user-name" content="{{ auth()->user()->name }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="ws-url" content="{{ config('app.ws_url', 'ws://localhost:6001') }}">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">

<style>
/* ===== БАЗА ===== */
.tg-container { display: flex; height: calc(100vh - 120px); background: var(--bg-1); border-radius: var(--radius); overflow: hidden; border: 1px solid var(--line); box-shadow: var(--shadow-card); position: relative; }
.tg-sidebar { width: 340px; border-right: 1px solid var(--line); display: flex; flex-direction: column; background: var(--bg-1); flex-shrink: 0; }
.tg-sidebar-header { padding: 18px 20px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; gap: 8px; }
.tg-sidebar-header h3 { font-size: 18px; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px; flex: 1; }
.tg-header-actions { display: flex; gap: 6px; }
.tg-header-btn { width: 36px; height: 36px; border-radius: 50%; border: 1px solid var(--line); background: var(--bg-2); color: var(--text); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.tg-header-btn:hover { background: rgb(var(--glow)); color: white; border-color: rgb(var(--glow)); transform: scale(1.08); }
.tg-search { padding: 12px 16px; border-bottom: 1px solid var(--line); position: relative; }
.tg-search input { width: 100%; padding: 10px 14px 10px 38px; border: 1px solid var(--line); border-radius: 20px; background: var(--bg-2); color: var(--text); font-size: 13px; box-sizing: border-box; }
.tg-search input:focus { outline: none; border-color: rgb(var(--glow)); box-shadow: 0 0 0 3px rgba(var(--glow), 0.12); }
.tg-search-icon { position: absolute; left: 30px; top: 50%; transform: translateY(-50%); color: var(--muted); pointer-events: none; }
.tg-list { flex: 1; overflow-y: auto; padding: 4px 0; }
.tg-item { padding: 10px 14px; margin: 2px 8px; cursor: pointer; display: flex; align-items: center; gap: 12px; border-radius: 12px; transition: all 0.2s; position: relative; }
.tg-item:hover { background: var(--bg-2); }
.tg-item.active { background: linear-gradient(135deg, rgba(var(--glow), 0.12), rgba(var(--glow), 0.05)); }
.tg-item.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 70%; background: rgb(var(--glow)); border-radius: 0 4px 4px 0; }
.tg-item-avatar { width: 52px; height: 52px; border-radius: 50%; object-fit: cover; border: 2px solid var(--line); }
.tg-item-info { flex: 1; min-width: 0; }
.tg-item-name { font-weight: 600; font-size: 14px; margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; gap: 5px; }
.tg-item-last { font-size: 13px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tg-item-meta { font-size: 11px; color: var(--muted); display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
.tg-unread-badge { min-width: 20px; height: 20px; padding: 0 6px; background: rgb(var(--glow)); color: white; border-radius: 10px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
.tg-area { flex: 1; display: flex; flex-direction: column; background: var(--bg-2); min-width: 0; }
.tg-header { padding: 14px 20px; border-bottom: 1px solid var(--line); background: var(--bg-1); display: flex; align-items: center; gap: 12px; }
.tg-header-avatar { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid var(--line); }
.tg-header-info h4 { font-size: 15px; font-weight: 600; margin: 0 0 2px 0; }
.tg-header-status { font-size: 12px; color: var(--muted); }
.tg-header-profile-link { display: flex; align-items: center; gap: 12px; flex: 1; text-decoration: none; color: inherit; }
.tg-messages { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 4px; }
.tg-message { display: flex; max-width: 65%; margin-bottom: 4px; animation: tg-fadeIn 0.3s; }
@keyframes tg-fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
.tg-message.mine { align-self: flex-end; }
.tg-message.theirs { align-self: flex-start; }
.tg-bubble { padding: 10px 14px 8px 14px; border-radius: 18px; position: relative; word-wrap: break-word; max-width: 100%; }
.tg-message.mine .tg-bubble { background: linear-gradient(135deg, rgb(var(--glow)), rgba(var(--glow), 0.9)); color: white; border-bottom-right-radius: 6px; }
.tg-message.theirs .tg-bubble { background: var(--bg-1); border-bottom-left-radius: 6px; border: 1px solid var(--line); }
.tg-sender { font-size: 11px; font-weight: 700; color: rgb(var(--glow)); margin-bottom: 3px; }
.tg-text { font-size: 14px; line-height: 1.5; white-space: pre-wrap; word-break: break-word; }
.tg-text a { color: inherit; text-decoration: underline; }
.tg-time { font-size: 10px; opacity: 0.7; text-align: right; margin-top: 4px; display: inline-flex; align-items: center; gap: 2px; }
.tg-image { max-width: 100%; max-height: 300px; border-radius: 12px; cursor: pointer; }
.tg-file { display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: rgba(255,255,255,0.15); border-radius: 12px; text-decoration: none; color: inherit; }
.tg-file-icon { width: 36px; height: 36px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; }
.tg-file-name { font-weight: 600; font-size: 13px; }
.tg-voice-msg { display: flex; align-items: center; gap: 10px; min-width: 220px; }
.tg-voice-icon { width: 36px; height: 36px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.tg-voice-audio { flex: 1; height: 32px; min-width: 150px; }

/* ✅ ОБНОВЛЕНО: Стикеры как картинки (фото) */
.tg-sticker { 
    width: 120px; 
    height: 120px; 
    object-fit: contain; 
    display: block;
}

.tg-date-divider { text-align: center; margin: 16px 0; }
.tg-date-divider span { background: rgba(var(--glow), 0.08); color: rgb(var(--glow)); padding: 4px 14px; border-radius: 12px; font-size: 11px; font-weight: 600; }
.tg-scroll-down-btn { position: absolute; bottom: 96px; right: 24px; width: 42px; height: 42px; border-radius: 50%; background: var(--bg-1); color: var(--text); border: 1px solid var(--line); cursor: pointer; display: none; align-items: center; justify-content: center; z-index: 10; }
.tg-scroll-down-btn.visible { display: flex; }
.tg-footer-wrap { background: var(--bg-1); border-top: 1px solid var(--line); }
.tg-footer { padding: 14px 20px; display: flex; gap: 10px; align-items: flex-end; }
.tg-input-wrapper { flex: 1; position: relative; }
.tg-input { width: 100%; padding: 12px 16px; padding-right: 50px; border: 1px solid var(--line); border-radius: 22px; background: var(--bg-2); color: var(--text); font-size: 14px; resize: none; max-height: 120px; box-sizing: border-box; }
.tg-input:focus { outline: none; border-color: rgb(var(--glow)); }
.tg-char-counter { position: absolute; right: 14px; bottom: 10px; font-size: 10px; color: var(--muted); }
.tg-btn { width: 44px; height: 44px; border-radius: 50%; border: none; background: rgb(var(--glow)); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.tg-btn:hover { transform: scale(1.08); }
.tg-btn.recording { background: #ef4444; animation: tg-pulse 1.5s infinite; }
@keyframes tg-pulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5); } 50% { box-shadow: 0 0 0 12px rgba(239, 68, 68, 0); } }
.tg-btn-secondary { background: var(--bg-2); color: var(--text); border: 1px solid var(--line); }
.tg-btn-cancel-recording { background: #ef4444; color: white; }
.tg-empty { flex: 1; display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 20px; color: var(--muted); padding: 40px; text-align: center; }
.tg-empty-icon { font-size: 96px; opacity: 0.15; color: rgb(var(--glow)); }
.tg-recording-indicator { display: none; position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%); background: #ef4444; color: white; padding: 14px 28px; border-radius: 50px; z-index: 999; align-items: center; gap: 14px; font-weight: 600; }
.tg-recording-indicator.active { display: flex; }
.tg-recording-dot { width: 10px; height: 10px; background: white; border-radius: 50%; animation: tg-recDot 1s infinite; }
@keyframes tg-recDot { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
.tg-recording-timer { font-family: 'SF Mono', Monaco, monospace; font-weight: 700; }
.tg-modal { position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); display: none; align-items: center; justify-content: center; z-index: 1000; padding: 20px; }
.tg-modal.active { display: flex; }
.tg-modal-content { background: var(--bg-1); border-radius: 16px; padding: 24px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; }
.tg-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.tg-modal-title { font-size: 18px; font-weight: 700; margin: 0; }
.tg-modal-close { width: 32px; height: 32px; border-radius: 8px; border: none; background: var(--bg-2); color: var(--text); cursor: pointer; }
.tg-modal-input { width: 100%; padding: 11px 14px; border: 1px solid var(--line); border-radius: var(--radius); background: var(--bg-2); color: var(--text); font-size: 14px; margin-bottom: 12px; box-sizing: border-box; }
.tg-modal-input:focus { outline: none; border-color: rgb(var(--glow)); }
.tg-user-results { max-height: 320px; overflow-y: auto; border: 1px solid var(--line); border-radius: 10px; background: var(--bg-2); }
.tg-user-result { padding: 10px 14px; display: flex; align-items: center; gap: 12px; cursor: pointer; border-bottom: 1px solid var(--line); }
.tg-user-result img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
.tg-user-result-name { font-weight: 600; font-size: 14px; }
.tg-user-result-meta { font-size: 12px; color: var(--muted); }
.tg-selected-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; min-height: 8px; }
.tg-user-tag { display: inline-flex; align-items: center; gap: 6px; background: rgba(var(--glow), 0.15); color: rgb(var(--glow)); padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.tg-user-tag-remove { cursor: pointer; }
.tg-modal-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 20px; }
.tg-modal-actions .tg-btn { width: auto; border-radius: 10px; padding: 10px 18px; height: auto; font-size: 14px; font-weight: 600; }
.tg-avatar-wrap { position: relative; }
.tg-online-dot { position: absolute; bottom: 2px; right: 2px; width: 12px; height: 12px; background: #10b981; border: 2px solid var(--bg-1); border-radius: 50%; }
.tg-typing { font-size: 12px; color: rgb(var(--glow)); font-style: italic; display: inline-flex; align-items: center; gap: 4px; }
.tg-typing-dots { display: inline-flex; gap: 2px; }
.tg-typing-dots span { width: 4px; height: 4px; background: rgb(var(--glow)); border-radius: 50%; animation: tg-typingDot 1.4s infinite; }
.tg-typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.tg-typing-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes tg-typingDot { 0%, 60%, 100% { opacity: 0.3; } 30% { opacity: 1; } }
.tg-reply-preview { border-left: 3px solid rgb(var(--glow)); padding: 10px 14px; background: rgba(var(--glow), 0.08); border-radius: 8px; margin: 0 20px; font-size: 12px; position: relative; display: none; }
.tg-reply-preview.active { display: block; }
.tg-reply-preview-author { font-weight: 700; color: rgb(var(--glow)); }
.tg-reply-close { position: absolute; top: 6px; right: 8px; cursor: pointer; color: var(--muted); }
.tg-context-menu { position: fixed; background: var(--bg-1); border: 1px solid var(--line); border-radius: 12px; box-shadow: 0 12px 32px rgba(0,0,0,0.18); z-index: 1500; min-width: 200px; padding: 6px; display: none; }
.tg-context-menu.active { display: block; }
.tg-context-item { padding: 10px 14px; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 12px; border-radius: 8px; }
.tg-context-item:hover { background: rgba(var(--glow), 0.08); }
.tg-context-item.danger { color: #ef4444; }
.tg-context-separator { height: 1px; background: var(--line); margin: 4px 8px; }
.tg-sticker-panel { position: absolute; bottom: 70px; left: 20px; background: var(--bg-1); border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 12px 32px rgba(0,0,0,0.15); padding: 10px; width: 280px; max-width: calc(100vw - 40px); max-height: 280px; z-index: 100; display: none; }
.tg-sticker-panel.active { display: block; }
.tg-sticker-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; max-height: 240px; overflow-y: auto; }
.tg-sticker-btn { aspect-ratio: 1; display: flex; align-items: center; justify-content: center; cursor: pointer; border-radius: 12px; border: 1px solid var(--line); background: var(--bg-2); transition: all 0.15s; padding: 6px; }
.tg-sticker-btn:hover { transform: scale(1.1); background: rgba(var(--glow), 0.1); border-color: rgb(var(--glow)); }
.tg-sticker-btn img { width: 42px; height: 42px; object-fit: contain; }

.tg-info-drawer { position: absolute; top: 0; right: 0; bottom: 0; width: 360px; background: var(--bg-1); border-left: 1px solid var(--line); z-index: 60; transform: translateX(105%); transition: transform 0.3s; display: flex; flex-direction: column; }
.tg-info-drawer.open { transform: translateX(0); }
.tg-info-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-bottom: 1px solid var(--line); }
.tg-info-body { flex: 1; overflow-y: auto; }
.tg-info-cover { display: flex; flex-direction: column; align-items: center; padding: 24px 16px; text-align: center; }
.tg-info-cover img { width: 88px; height: 88px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(var(--glow), 0.35); }
.tg-info-cover-name { font-size: 17px; font-weight: 700; margin-top: 8px; }
.tg-info-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; padding: 4px 16px 14px; }
.tg-stat-card { background: var(--bg-2); border: 1px solid var(--line); border-radius: 10px; padding: 10px 6px; text-align: center; cursor: pointer; transition: all 0.2s; }
.tg-stat-card:hover { border-color: rgba(var(--glow), 0.5); transform: translateY(-2px); }
.tg-stat-card b { font-size: 14px; display: block; }
.tg-stat-card span { font-size: 9px; color: var(--muted); text-transform: uppercase; }
.tg-info-section-title { padding: 10px 16px 6px; font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; display: flex; justify-content: space-between; }
.tg-info-add-btn { padding: 5px 10px; border-radius: 8px; border: 1px solid rgba(var(--glow), 0.35); background: rgba(var(--glow), 0.1); color: rgb(var(--glow)); font-size: 11px; font-weight: 700; cursor: pointer; }
.tg-info-add-btn:hover { background: rgb(var(--glow)); color: white; }
.tg-member-row { display: flex; align-items: center; padding: 6px 16px; gap: 6px; }
.tg-member-main { display: flex; align-items: center; gap: 12px; flex: 1; padding: 7px 6px; text-decoration: none; color: inherit; border-radius: 10px; }
.tg-member-main:hover { background: var(--bg-2); }
.tg-member-main img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
.tg-member-name { font-size: 13px; font-weight: 600; }
.tg-member-role { font-size: 11px; color: var(--muted); }
.tg-member-role.creator { color: rgb(var(--glow)); font-weight: 600; }
.tg-member-remove { width: 28px; height: 28px; border-radius: 50%; border: 1px solid rgba(239,68,68,0.3); background: rgba(239,68,68,0.08); color: #ef4444; cursor: pointer; }
.tg-member-remove:hover { background: #ef4444; color: #fff; }
.tg-info-actions { padding: 12px 16px; border-top: 1px solid var(--line); display: flex; flex-direction: column; gap: 8px; }
.tg-danger-btn { padding: 11px 16px; border-radius: 10px; border: 1px solid rgba(239,68,68,0.35); background: rgba(239,68,68,0.08); color: #ef4444; font-size: 13px; font-weight: 600; cursor: pointer; width: 100%; }
.tg-danger-btn:hover { background: rgba(239,68,68,0.16); }
.tg-media-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; }
.tg-media-grid img { width: 100%; aspect-ratio: 1/1; object-fit: cover; border-radius: 10px; cursor: pointer; }
.tg-media-row { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid var(--line); border-radius: 10px; margin-bottom: 6px; background: var(--bg-2); }
.tg-media-row audio { width: 100%; height: 32px; }
.tg-checks { margin-left: 4px; font-size: 11px; }
.tg-blocked-banner { background: #fee2e2; border-top: 1px solid #fca5a5; color: #991b1b; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.tg-btn-unblock { background: #dc2626; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; }
.tg-back-btn { display: none; width: 36px; height: 36px; border-radius: 50%; border: 1px solid var(--line); background: var(--bg-2); color: var(--text); cursor: pointer; align-items: center; justify-content: center; }
.tg-block-btn { width: 36px; height: 36px; border-radius: 50%; border: 1px solid var(--line); background: var(--bg-2); color: var(--text); cursor: pointer; display: flex; align-items: center; justify-content: center; }
.tg-toast-live { position: fixed; top: 20px; right: 20px; padding: 12px 20px; border-radius: 12px; display: flex; align-items: center; gap: 10px; box-shadow: 0 12px 32px rgba(0,0,0,0.15); z-index: 3000; font-weight: 600; color: white; min-width: 260px; animation: tg-toastIn 0.35s; }
@keyframes tg-toastIn { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }

@media (max-width: 768px) {
.tg-container { height: calc(100vh - 60px); border-radius: 0; }
.tg-sidebar { width: 100%; }
.tg-area { display: none; }
.tg-container.show-chat .tg-sidebar { display: none; }
.tg-container.show-chat .tg-area { display: flex; }
.tg-back-btn { display: flex; }
.tg-message { max-width: 85%; }
.tg-modal-content { max-width: 95%; padding: 18px; }
.tg-sticker-panel { width: calc(100vw - 24px); left: 12px; }
.tg-info-drawer { position: fixed; inset: 0; width: 100%; z-index: 1600; transform: translateY(100%); }
.tg-info-drawer.open { transform: translateY(0); }
}
@media (max-width: 480px) {
.tg-sticker-grid { grid-template-columns: repeat(3, 1fr); }
}
.tg-rec-meter { width: 60px; height: 6px; background: rgba(255,255,255,0.25); border-radius: 3px; overflow: hidden; }
.tg-rec-meter-fill { height: 100%; width: 0%; background: #fff; border-radius: 3px; transition: width 0.1s; }

/* ===== CALL STYLES ===== */
#tgCallModal .tg-modal-content {
    max-width: 100%; max-height: 100%; width: 100vw; height: 100vh;
    border-radius: 0; padding: 0; background: #0b1220; overflow: hidden;
}
.tg-call-header {
    height: 56px; background: rgba(11, 18, 32, 0.85); border-bottom: 1px solid rgba(255,255,255,0.08);
    display: flex; align-items: center; justify-content: space-between; padding: 0 20px;
    z-index: 10; position: relative; backdrop-filter: blur(12px);
}
.tg-call-container {
    width: 100%; height: calc(100vh - 56px); background: radial-gradient(circle at 50% 40%, #16233d 0%, #0b1220 70%);
    position: relative; display: flex; align-items: center; justify-content: center;
}
.tg-video-remote { width: 100%; height: 100%; object-fit: contain; background: transparent; }
.tg-video-local {
    position: absolute; bottom: 104px; right: 24px; width: 168px; height: 210px; background: #1e293b;
    border-radius: 18px; border: 2px solid rgba(255,255,255,0.25); object-fit: cover; z-index: 20; 
    box-shadow: 0 12px 40px rgba(0,0,0,0.55); transition: transform 0.2s;
}
.tg-video-local:hover { transform: scale(1.04); }
.tg-audio-avatar {
    display: none; width: 140px; height: 140px; border-radius: 50%; 
    border: 4px solid rgba(16, 185, 129, 0.55); object-fit: cover;
    animation: tg-pulse-call 2s infinite; margin-bottom: 20px;
}
.tg-call-mode-audio .tg-video-remote, .tg-call-mode-audio .tg-video-local { display: none !important; }
.tg-call-mode-audio .tg-audio-avatar { display: block; }
.tg-call-mode-audio #tgCallCamBtn { display: none !important; }
.tg-call-controls {
    position: absolute; bottom: 26px; left: 50%; transform: translateX(-50%);
    display: flex; gap: 12px; z-index: 20; background: rgba(11,18,32,0.55); 
    padding: 10px 16px; border-radius: 999px; border: 1px solid rgba(255,255,255,0.12); backdrop-filter: blur(14px);
}
.tg-call-btn {
    width: 50px; height: 50px; border-radius: 50%; border: none; cursor: pointer; 
    display: flex; align-items: center; justify-content: center; font-size: 19px; 
    color: white; background: rgba(255,255,255,0.14); transition: all 0.2s;
}
.tg-call-btn:hover { background: rgba(255,255,255,0.28); transform: scale(1.06); }
.tg-call-btn.active { background: #f59e0b; color: #0b1220; }
.tg-call-btn.hangup { background: #ef4444; width: 68px; border-radius: 26px; }
.tg-call-btn.hangup:hover { background: #dc2626; }
.tg-call-info {
    position: absolute; top: 76px; left: 50%; transform: translateX(-50%);
    text-align: center; color: white; z-index: 20; pointer-events: none;
}
.tg-call-info h3 { margin: 0 0 4px 0; font-size: 22px; text-shadow: 0 2px 10px rgba(0,0,0,0.6); }
.tg-call-info span { font-size: 13px; opacity: 0.9; font-family: monospace; background: rgba(11,18,32,0.5); padding: 3px 12px; border-radius: 999px; }

.tg-incoming-call-modal {
    position: fixed; inset: 0; background: rgba(5,10,20,0.85); backdrop-filter: blur(12px);
    display: flex; align-items: center; justify-content: center; z-index: 2000; animation: tg-fadeIn 0.3s;
}
.tg-incoming-call-content {
    background: linear-gradient(160deg, #1c2a44, #0b1220); border-radius: 28px;
    padding: 44px 36px; text-align: center; color: white; max-width: 400px; width: 90%;
    box-shadow: 0 30px 80px rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.08);
}
.tg-incoming-call-avatar {
    width: 118px; height: 118px; border-radius: 50%; margin: 0 auto 22px;
    border: 4px solid rgba(16, 185, 129, 0.55); object-fit: cover; animation: tg-pulse-call 2s infinite;
}
@keyframes tg-pulse-call { 0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); } 50% { box-shadow: 0 0 0 22px rgba(16, 185, 129, 0); } }
.tg-incoming-call-name { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
.tg-incoming-call-status { font-size: 14px; color: #34d399; margin-bottom: 34px; letter-spacing: 0.3px; }
.tg-incoming-call-actions { display: flex; gap: 46px; justify-content: center; }
.tg-incoming-call-btn {
    width: 66px; height: 66px; border-radius: 50%; border: none; cursor: pointer; 
    display: flex; align-items: center; justify-content: center; font-size: 25px; 
    color: white; transition: all 0.2s; box-shadow: 0 10px 30px rgba(0,0,0,0.4);
}
.tg-incoming-call-btn:hover { transform: scale(1.12); }
.tg-incoming-call-btn.accept { background: linear-gradient(135deg, #10b981, #059669); }
.tg-incoming-call-btn.decline { background: linear-gradient(135deg, #ef4444, #dc2626); }
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="tg-container" id="tgContainer">
    <div class="tg-sidebar">
        <div class="tg-sidebar-header">
            <h3><i class="bi bi-chat-dots-fill" style="color: rgb(var(--glow));"></i> Чаты</h3>
            <div class="tg-header-actions">
                <button class="tg-header-btn" onclick="tgOpenNewChatModal()" title="Новый чат"><i class="bi bi-pencil-square"></i></button>
                <button class="tg-header-btn" onclick="tgOpenGroupModal()" title="Создать группу"><i class="bi bi-people-fill"></i></button>
            </div>
        </div>
        <div class="tg-search">
            <i class="bi bi-search tg-search-icon"></i>
            <input type="text" id="tgSearch" placeholder="Поиск чатов...">
        </div>
        <div class="tg-list" id="tgList"></div>
    </div>

    <div class="tg-area" id="tgArea">
        <div class="tg-empty" id="tgEmpty">
            <div class="tg-empty-icon">💬</div>
            <h4>Выберите чат</h4>
            <p>Или создайте новый чат с помощью кнопок в шапке.</p>
        </div>

        <div class="tg-header" id="tgHeader" style="display: none;">
            <button class="tg-back-btn" onclick="tgGoBackToList()"><i class="bi bi-arrow-left"></i></button>
            <a href="#" id="tgHeaderProfileLink" class="tg-header-profile-link">
                <div class="tg-avatar-wrap">
                    <img src="" class="tg-header-avatar" id="tgHeaderAvatar">
                    <div class="tg-online-dot" id="tgHeaderOnlineDot" style="display: none;"></div>
                </div>
                <div class="tg-header-info">
                    <h4 id="tgHeaderName"></h4>
                    <div class="tg-header-status" id="tgHeaderStatus"></div>
                </div>
            </a>
            <div style="display: flex; gap: 6px; margin-left: auto;">
                <button class="tg-block-btn" id="tgAudioCallBtn" onclick="tgStartAudioCall()" title="Аудиозвонок" style="display: none; background: #3b82f6; color: white; border-color: #3b82f6; margin-right: 6px;">
                    <i class="bi bi-telephone-fill"></i>
                </button>
                <button class="tg-block-btn" id="tgVideoCallBtn" onclick="tgStartVideoCall()" title="Видеозвонок" style="display: none; background: #10b981; color: white; border-color: #10b981;">
                    <i class="bi bi-camera-video-fill"></i>
                </button>
                <button class="tg-block-btn" id="tgInfoBtn" onclick="tgOpenInfo(tgConvId)" title="Инфо" style="display: none;">
                    <i class="bi bi-people"></i>
                </button>
                <button class="tg-block-btn" id="tgBlockBtn" onclick="tgToggleBlockModal()" style="display: none;">
                    <i class="bi bi-slash-circle" id="tgBlockIcon"></i>
                </button>
                <!-- ✅ НОВАЯ КНОПКА: Удалить чат -->
                <button class="tg-block-btn" id="tgDeleteChatBtn" onclick="tgDeleteCurrentChat()" title="Удалить чат" style="display: none; color: #ef4444;">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>

        <div class="tg-blocked-banner" id="tgBlockedBanner" style="display: none;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="bi bi-shield-x"></i>
                <span id="tgBlockedBannerText">Вы заблокировали этого пользователя</span>
            </div>
            <button class="tg-btn-unblock" onclick="tgUnblockUser()">
                <i class="bi bi-unlock"></i> Разблокировать
            </button>
        </div>

        <div class="tg-messages" id="tgMessages" style="display: none;"></div>
        <button class="tg-scroll-down-btn" id="tgScrollDownBtn" onclick="tgScrollDown()"><i class="bi bi-arrow-down"></i></button>

        <div class="tg-footer-wrap" id="tgFooterWrap" style="display: none;">
            <div class="tg-reply-preview" id="tgReplyPreview">
                <span class="tg-reply-close" onclick="tgCancelReply()"><i class="bi bi-x-lg"></i></span>
                <div class="tg-reply-preview-author" id="tgReplyAuthor"></div>
                <div class="tg-reply-preview-text" id="tgReplyText"></div>
            </div>
            <div class="tg-footer" id="tgFooter">
                <button class="tg-btn tg-btn-secondary" onclick="tgToggleStickerPanel()" title="Стикеры"><i class="bi bi-stars"></i></button>
                <label class="tg-btn tg-btn-secondary" title="Файл">
                    <i class="bi bi-paperclip"></i>
                    <input type="file" id="tgFileInput" style="display: none;" onchange="tgUploadFile(this)">
                </label>
                <div class="tg-input-wrapper">
                    <textarea class="tg-input" id="tgInput" rows="1" placeholder="Написать сообщение..." onkeydown="tgHandleKey(event)" oninput="tgAutoResize(this); tgSendTyping(); tgUpdateCharCounter()"></textarea>
                    <span class="tg-char-counter" id="tgCharCounter">0</span>
                </div>
                <button class="tg-btn tg-btn-cancel-recording" id="tgCancelRecBtn" onclick="tgCancelRecording()" style="display: none;">
                    <i class="bi bi-x-lg"></i>
                </button>
                <button class="tg-btn" id="tgActionBtn" onclick="tgHandleAction()">
                    <i class="bi bi-mic-fill" id="tgMicIcon"></i>
                    <i class="bi bi-send-fill" id="tgSendIcon" style="display: none;"></i>
                </button>
            </div>

            <div class="tg-sticker-panel" id="tgStickerPanel">
                <div class="tg-sticker-grid" id="tgStickerGrid"></div>
            </div>
        </div>

        <div class="tg-info-drawer" id="tgInfoDrawer">
            <div class="tg-info-header">
                <button class="tg-modal-close" onclick="tgCloseInfo()"><i class="bi bi-x-lg"></i></button>
                <div style="font-weight: 700;">Инфо</div>
                <div style="width: 32px;"></div>
            </div>
            <div class="tg-info-body" id="tgInfoBody"></div>
        </div>
    </div>
</div>

<div class="tg-recording-indicator" id="tgRecordingIndicator">
    <div class="tg-recording-dot"></div>
    <span>🎙 Запись</span>
    <span class="tg-recording-timer" id="tgRecordTimer">0:00</span>
    <div class="tg-rec-meter"><div class="tg-rec-meter-fill" id="tgRecMeterFill"></div></div>
</div>

<div class="tg-context-menu" id="tgContextMenu">
    <div class="tg-context-item" onclick="tgReplyToMessage()"><i class="bi bi-reply"></i> Ответить</div>
    <div class="tg-context-item" onclick="tgCopyMessage()"><i class="bi bi-clipboard"></i> Копировать</div>
    <div class="tg-context-separator"></div>
    <div class="tg-context-item danger" id="tgCtxDelete" onclick="tgDeleteMessage()"><i class="bi bi-trash"></i> Удалить</div>
</div>

<div class="tg-modal" id="tgNewChatModal">
    <div class="tg-modal-content">
        <div class="tg-modal-header">
            <h3 class="tg-modal-title">Новый чат</h3>
            <button class="tg-modal-close" onclick="tgCloseNewChatModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <input type="text" class="tg-modal-input" id="tgNewChatSearch" placeholder="Имя, email или телефон..." oninput="tgSearchNewChat(this.value)">
        <div class="tg-user-results" id="tgNewChatResults"></div>
    </div>
</div>

<div class="tg-modal" id="tgGroupModal">
    <div class="tg-modal-content">
        <div class="tg-modal-header">
            <h3 class="tg-modal-title">Новая группа</h3>
            <button class="tg-modal-close" onclick="tgCloseGroupModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <input type="text" class="tg-modal-input" id="tgGroupName" placeholder="Название группы">
        <input type="text" class="tg-modal-input" id="tgUserSearch" placeholder="Поиск пользователей..." oninput="tgSearchUsers(this.value, 'group')">
        <div class="tg-selected-tags" id="tgSelectedUsers"></div>
        <div class="tg-user-results" id="tgUserResults"></div>
        <div class="tg-modal-actions">
            <button class="tg-btn tg-btn-secondary" onclick="tgCloseGroupModal()">Отмена</button>
            <button class="tg-btn" onclick="tgCreateGroup()"><i class="bi bi-check2"></i> Создать</button>
        </div>
    </div>
</div>

<div class="tg-modal" id="tgAddMemberModal">
    <div class="tg-modal-content">
        <div class="tg-modal-header">
            <h3 class="tg-modal-title">Добавить участника</h3>
            <button class="tg-modal-close" onclick="tgCloseAddMemberModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <input type="text" class="tg-modal-input" id="tgAddMemberSearch" placeholder="Поиск..." oninput="tgSearchAddMember(this.value)">
        <div class="tg-selected-tags" id="tgAddMemberTags"></div>
        <div class="tg-user-results" id="tgAddMemberResults"></div>
        <div class="tg-modal-actions">
            <button class="tg-btn tg-btn-secondary" onclick="tgCloseAddMemberModal()">Отмена</button>
            <button class="tg-btn" onclick="tgConfirmAddMembers()">Добавить</button>
        </div>
    </div>
</div>

<div class="tg-modal" id="tgMediaModal">
    <div class="tg-modal-content" style="max-width: 640px;">
        <div class="tg-modal-header">
            <h3 class="tg-modal-title" id="tgMediaTitle"></h3>
            <button class="tg-modal-close" onclick="tgCloseMedia()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div id="tgMediaBody" style="max-height: 65vh; overflow-y: auto;"></div>
    </div>
</div>

<div class="tg-modal" id="tgBlockModal">
    <div class="tg-modal-content" style="max-width: 400px;">
        <h3 id="tgBlockModalTitle" style="color: #dc2626;"><i class="bi bi-shield-x"></i> Заблокировать?</h3>
        <p id="tgBlockModalText"></p>
        <div class="tg-modal-actions">
            <button class="tg-btn tg-btn-secondary" onclick="tgCloseBlockModal()">Отмена</button>
            <button class="tg-btn" id="tgBlockConfirmBtn" onclick="tgConfirmBlock()" style="background: #dc2626;">Заблокировать</button>
        </div>
    </div>
</div>

<div class="tg-modal" id="tgCallModal">
    <div class="tg-modal-content">
        <div class="tg-call-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="bi bi-telephone-fill" id="tgCallHeaderIcon" style="color: #10b981; font-size: 18px;"></i>
                <span style="font-weight: 700; font-size: 15px; color: white;" id="tgCallName">Звонок</span>
            </div>
            <button class="tg-modal-close" onclick="tgEndCall()" style="background: #ef4444; color: white; width: 36px; height: 36px;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="tg-call-container" id="tgCallContainer">
            <img src="" id="tgCallAvatar" class="tg-audio-avatar" onerror="this.src='https://ui-avatars.com/api/?name=User&size=140'">
            <video id="tgRemoteVideo" class="tg-video-remote" autoplay playsinline></video>
            <video id="tgLocalVideo" class="tg-video-local" autoplay playsinline muted></video>
            <div class="tg-call-info">
                <h3 id="tgCallerName">Имя собеседника</h3>
                <span id="tgCallTimer">00:00</span>
            </div>
            <div class="tg-call-controls">
                <button class="tg-call-btn" id="tgCallMicBtn" onclick="tgToggleCallMic()" title="Микрофон"><i class="bi bi-mic-fill"></i></button>
                <button class="tg-call-btn" id="tgCallCamBtn" onclick="tgToggleCallCam()" title="Камера"><i class="bi bi-camera-video-fill"></i></button>
                <button class="tg-call-btn hangup" onclick="tgEndCall()" title="Завершить"><i class="bi bi-telephone-x-fill"></i></button>
            </div>
        </div>
    </div>
</div>

<script>
const TG_USER_ID = document.querySelector('meta[name="user-id"]').content;
const TG_USER_NAME = document.querySelector('meta[name="user-name"]').content;
const TG_CSRF = document.querySelector('meta[name="csrf-token"]').content;
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

const tgIceServers = { 
    iceServers: [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' },
        { urls: 'turn:openrelay.metered.ca:80', username: 'openrelayproject', credential: 'openrelayproject' },
        { urls: 'turn:openrelay.metered.ca:443', username: 'openrelayproject', credential: 'openrelayproject' },
        { urls: 'turn:openrelay.metered.ca:443?transport=tcp', username: 'openrelayproject', credential: 'openrelayproject' }
    ],
    iceTransportPolicy: 'all',
    iceCandidatePoolSize: 2
};

const TG_STICKERS = ['😂','🤣','😊','😹','😍','🤡','🤪','😜','😎','👻','🥳','🤩','❤️','👍','🎉','👏','🔥','✨','💯','🙏'];

// ✅ Функция для превращения эмодзи в картинку (Twemoji CDN)
function getStickerUrl(emoji) {
    const codePoint = emoji.codePointAt(0).toString(16);
    return `https://cdnjs.cloudflare.com/ajax/libs/twemoji/14.0.2/72x72/${codePoint}.png`;
}

document.addEventListener('DOMContentLoaded', () => {
    tgLoadConversations();
    tgStartHeartbeat();
    tgLoadStickers();
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
        const res = await fetch(`${API}/video-signals/${conversationId}`, {
            method: 'GET', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            signal: ctrl.signal, credentials: 'same-origin'
        });
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
    pc.ontrack = (e) => {
        const rv = document.getElementById('tgRemoteVideo');
        rv.srcObject = e.streams[0];
        rv.play().catch(()=>{});
    };
    pc.onicecandidate = (e) => {
        if (e.candidate) tgSendSignal('ice-candidate', e.candidate);
    };
    pc.oniceconnectionstatechange = () => {
        const st = pc.iceConnectionState;
        if (st === 'connected' || st === 'completed') {
            if (tgCallTimeout) { clearTimeout(tgCallTimeout); tgCallTimeout = null; }
            if (tgIceWatchdog) { clearTimeout(tgIceWatchdog); tgIceWatchdog = null; }
            tgRetryCount = 0; tgIceRestarted = false;
        } else if (st === 'disconnected') {
            setTimeout(() => {
                if (pc && pc.iceConnectionState === 'disconnected' && !tgIceRestarted) {
                    tgIceRestarted = true;
                    try { pc.restartIce(); } catch(e){}
                }
            }, 3000);
        } else if (st === 'failed') {
            if (!tgIceRestarted && tgRetryCount < 2) {
                tgIceRestarted = true; tgRetryCount++;
                try { pc.restartIce(); } catch(e){}
            }
        }
    };
}

function tgStartIceWatchdog() {
    if (tgIceWatchdog) clearTimeout(tgIceWatchdog);
    tgIceWatchdog = setTimeout(() => {
        if (tgPeerConnection && !tgIceRestarted &&
            tgPeerConnection.iceConnectionState !== 'connected' &&
            tgPeerConnection.iceConnectionState !== 'completed') {
            tgIceRestarted = true;
            try { tgPeerConnection.restartIce(); } catch(e){}
        }
    }, 25000);
}

async function tgGetMedia(audioOnly = false) {
    try {
        return await navigator.mediaDevices.getUserMedia({
            video: audioOnly ? false : { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' },
            audio: { echoCancellation: true, noiseSuppression: true }
        });
    } catch(e1) {
        if (!audioOnly) {
            try { return await navigator.mediaDevices.getUserMedia({ video: false, audio: { echoCancellation: true, noiseSuppression: true } }); } 
            catch(e2) { throw e1; }
        }
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
        
        tgCallTimeout = setTimeout(() => {
            if (tgPeerConnection && tgPeerConnection.iceConnectionState!=='connected' && tgPeerConnection.iceConnectionState!=='completed') {
                tgSendSignal('end',{reason:'timeout'}); tgCleanupCall();
            }
        }, 45000);
    } catch(err) {
        let msg = isAudioOnlyCall ? 'Ошибка микрофона' : 'Ошибка камеры/микрофона';
        if (err.name==='NotAllowedError') msg = '❌ Разрешите доступ в браузере';
        tgShowToast(msg,'error'); tgCleanupCall();
    }
}

async function tgSendSignal(type, data, attempt = 0) {
    if (!tgConvId || !tgCurrentOtherUserId) return;
    const fd = new FormData();
    fd.append('_token', TG_CSRF);
    fd.append('call_id', tgCallId);
    fd.append('conversation_id', String(tgConvId));
    fd.append('from_user_id', String(TG_USER_ID));
    fd.append('to_user_id', String(tgCurrentOtherUserId));
    fd.append('signal_type', type);
    fd.append('data', JSON.stringify(data || {}));
    try {
        const res = await fetch(`${API}/video-signal`, {
            method:'POST', headers:{'X-CSRF-TOKEN':TG_CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, body: fd
        });
        if (!res.ok && attempt < 2) { await new Promise(r=>setTimeout(r, 600*(attempt+1))); return tgSendSignal(type, data, attempt+1); }
    } catch(e){
        if (attempt < 2) { await new Promise(r=>setTimeout(r, 600*(attempt+1))); return tgSendSignal(type, data, attempt+1); }
    }
}

function tgHandleIncomingCall(signal, fromConvId) {
    if (tgPeerConnection) { tgSendSignal('reject',{reason:'busy'}); return; }
    if (document.getElementById('tgIncomingCallModal')) return;
    
    tgIsCaller = false;
    tgCallId = signal.call_id;
    tgPendingOffer = signal.data;
    isAudioOnlyCall = signal.data.isAudioOnly || false;
    tgPendingIce = []; tgIceRestarted = false; tgRetryCount = 0;
    
    const callerId = signal.from_user_id;
    const convId = signal.conversation_id || fromConvId;
    const callerConv = tgAllConversations.find(c => c.id == convId) || null;
    tgConvId = convId;
    tgCurrentOtherUserId = callerId;
    if (callerConv) tgCurrentConvData = callerConv;
    tgIncomingCallData = { callerId, convId, conv: callerConv };
    
    const name = callerConv?.name || 'Собеседник';
    const av = callerConv?.avatar || 'https://ui-avatars.com/api/?name=User&size=120';
    const callTypeText = isAudioOnlyCall ? '📞 Входящий аудиозвонок...' : '📹 Входящий видеозвонок...';
    
    const modal = document.createElement('div');
    modal.className = 'tg-incoming-call-modal';
    modal.id = 'tgIncomingCallModal';
    modal.innerHTML = `
        <div class="tg-incoming-call-content">
            <img src="${av}" class="tg-incoming-call-avatar" onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&size=120'">
            <div class="tg-incoming-call-name">${tgEscapeHtml(name)}</div>
            <div class="tg-incoming-call-status">${callTypeText}</div>
            <div class="tg-incoming-call-actions">
                <button class="tg-incoming-call-btn accept" onclick="tgAcceptCall()" title="Принять"><i class="bi bi-telephone-fill"></i></button>
                <button class="tg-incoming-call-btn decline" onclick="tgDeclineCall()" title="Отклонить"><i class="bi bi-telephone-x-fill"></i></button>
            </div>
        </div>`;
    document.body.appendChild(modal);
    tgPlayRingtone();
    
    tgCallTimeout = setTimeout(() => {
        if (document.getElementById('tgIncomingCallModal')) { tgDeclineCall(); tgShowToast('⏰ Пропущенный вызов','info'); }
    }, 30000);
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
        openCallUI();
        tgStartIceWatchdog();
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
    const avatar = document.getElementById('tgCallAvatar');
    const headerIcon = document.getElementById('tgCallHeaderIcon');
    
    document.getElementById('tgCallName').textContent = isAudioOnlyCall ? 'Аудиозвонок' : 'Видеозвонок';
    document.getElementById('tgCallerName').textContent = tgCurrentConvData?.name || 'Собеседник';
    headerIcon.className = isAudioOnlyCall ? 'bi bi-telephone-fill' : 'bi bi-camera-video-fill';
    headerIcon.style.color = isAudioOnlyCall ? '#3b82f6' : '#10b981';
    
    avatar.src = tgCurrentConvData?.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(tgCurrentConvData?.name || 'User')}&size=140`;
    
    if (isAudioOnlyCall) container.classList.add('tg-call-mode-audio');
    else container.classList.remove('tg-call-mode-audio');
    
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
        b.classList.toggle('active', tgIsAudioMuted);
    } 
}
function tgToggleCallCam(){ 
    if(!tgLocalStream) return; 
    const t = tgLocalStream.getVideoTracks()[0]; 
    if(t){
        t.enabled = !t.enabled; tgIsVideoMuted = !t.enabled;
        const b = document.getElementById('tgCallCamBtn');
        b.innerHTML = tgIsVideoMuted ? '<i class="bi bi-camera-video-off-fill"></i>' : '<i class="bi bi-camera-video-fill"></i>';
        b.classList.toggle('active', tgIsVideoMuted);
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
    tgIsAudioMuted=false; tgIsVideoMuted=false; tgIceRestarted=false; tgRetryCount=0;
    isAudioOnlyCall = false;
    
    document.getElementById('tgCallMicBtn').innerHTML='<i class="bi bi-mic-fill"></i>';
    document.getElementById('tgCallCamBtn').innerHTML='<i class="bi bi-camera-video-fill"></i>';
}

// ===== ФУНКЦИИ ЧАТА =====
function tgUpdateCallButtonsUI(){ 
    const ok = (tgCurrentConvData?.type === 'private' || tgCurrentConvData?.type === 'group') && !tgCurrentConvData?.is_blocked; 
    document.getElementById('tgAudioCallBtn').style.display = ok ? 'flex' : 'none'; 
    document.getElementById('tgVideoCallBtn').style.display = ok ? 'flex' : 'none'; 
    document.getElementById('tgInfoBtn').style.display = ok ? 'flex' : 'none';
    document.getElementById('tgBlockBtn').style.display = (tgCurrentConvData?.type === 'private') ? 'flex' : 'none';
    // ✅ Показываем кнопку удаления чата
    document.getElementById('tgDeleteChatBtn').style.display = ok ? 'flex' : 'none';
}

function tgToggleStickerPanel(){ document.getElementById('tgStickerPanel').classList.toggle('active'); }

function tgLoadStickers(){ 
    const grid = document.getElementById('tgStickerGrid');
    // ✅ Стикеры теперь загружаются как картинки
    grid.innerHTML = TG_STICKERS.map(s => `<div class="tg-sticker-btn" onclick="tgSendSticker('${s}')"><img src="${getStickerUrl(s)}" alt="${s}"></div>`).join(''); 
}

async function tgSendSticker(s){ 
    if(!tgConvId)return; 
    document.getElementById('tgStickerPanel').classList.remove('active'); 
    const fd=new FormData(); 
    fd.append('conversation_id',tgConvId); 
    fd.append('body',s); 
    fd.append('type','sticker'); 
    try{
        const r=await fetch(`${API}/send`,{method:'POST',headers:{'X-CSRF-TOKEN':TG_CSRF},body:fd}); 
        if(r.ok){tgLoadMessages();tgLoadConversations();}
    }catch(e){tgShowToast('❌ Ошибка','error');} 
}

function tgStartHeartbeat(){ tgHeartbeatInterval=setInterval(async()=>{try{await fetch('/heartbeat');}catch(e){}},30000); }
async function tgLoadConversations(){ try{const r=await fetch(`${API}/conversations`); if(!r.ok)return; tgAllConversations=await r.json(); tgRenderConversations(tgAllConversations);}catch(e){} }

function tgRenderConversations(convs){
    const list=document.getElementById('tgList'), q=document.getElementById('tgSearch').value.toLowerCase().trim();
    const f=convs.filter(c=>!q||(c.name||'').toLowerCase().includes(q)||(c.last_message?.text||'').toLowerCase().includes(q));
    list.innerHTML='';
    if(!f.length){ list.innerHTML=`<div style="padding:40px;text-align:center;color:var(--muted);"><i class="bi bi-chat-square-dots" style="font-size:48px;opacity:.3;"></i><div style="margin-top:12px;">Нет чатов</div></div>`; return; }
    f.forEach(c=>{
        const d=document.createElement('div'); d.className='tg-item'+(c.id===tgConvId?' active':''); d.onclick=()=>tgOpenChat(c);
        const fb=`https://ui-avatars.com/api/?name=${encodeURIComponent(c.name)}&size=52&background=4f8cff&color=fff&bold=true`;
        d.innerHTML=`<div class="tg-avatar-wrap"><img src="${c.avatar}" class="tg-item-avatar" onerror="this.onerror=null;this.src='${fb}'">${c.is_online?'<div class="tg-online-dot"></div>':''}</div>
            <div class="tg-item-info"><div class="tg-item-name">${c.type==='group'?'<i class="bi bi-people-fill" style="font-size:11px;color:rgb(var(--glow));"></i>':''}${tgEscapeHtml(c.name)}</div>
            <div class="tg-item-last">${c.last_message?tgEscapeHtml(c.last_message.text):'Нет сообщений'}</div></div>
            <div class="tg-item-meta"><div style="font-size:11px;color:var(--muted);">${c.last_message?c.last_message.time:''}</div>
            ${c.unread_count>0?`<div class="tg-unread-badge">${c.unread_count>99?'99+':c.unread_count}</div>`:''}</div>`;
        list.appendChild(d);
    });
}

async function tgOpenChat(conv){
    if(!conv?.id)return;
    tgConvId=conv.id; tgLastMsgId=0; tgLastRenderedDate=null; tgCurrentConvData=conv; tgCurrentOtherUserId=conv.other_user_id||null;
    if(window.matchMedia('(max-width:768px)').matches) document.getElementById('tgContainer').classList.add('show-chat');
    document.getElementById('tgEmpty').style.display='none';
    document.getElementById('tgHeader').style.display='flex';
    document.getElementById('tgMessages').style.display='flex';
    document.getElementById('tgFooterWrap').style.display='block';
    document.getElementById('tgHeaderName').textContent=conv.name;
    const av=document.getElementById('tgHeaderAvatar'); av.src=conv.avatar;
    av.onerror=function(){this.onerror=null;this.src=`https://ui-avatars.com/api/?name=${encodeURIComponent(conv.name)}&size=44&background=4f8cff&color=fff&bold=true`;};
    const pl=document.getElementById('tgHeaderProfileLink');
    if(conv.type==='private'&&conv.other_user_id){pl.href=`/users/${conv.other_user_id}`;pl.onclick=null;}
    else{pl.href='#';pl.onclick=(e)=>{e.preventDefault();tgOpenInfo(conv.id);};}
    document.getElementById('tgHeaderOnlineDot').style.display=conv.is_online?'block':'none';
    tgSetDefaultStatus(); tgUpdateBlockBtnUI(); tgUpdateCallButtonsUI();
    const banner=document.getElementById('tgBlockedBanner'), footer=document.getElementById('tgFooter');
    if(conv.is_blocked&&conv.type==='private'){banner.style.display='flex';footer.style.display='none';}
    else{banner.style.display='none';footer.style.display='flex';}
    document.getElementById('tgMessages').innerHTML='';
    await tgLoadMessages(); await tgMarkAsRead(conv.id); tgScrollDownInstant();
    if(tgPolling)clearInterval(tgPolling); tgPolling=setInterval(tgLoadMessages,3000);
    tgStartTypingPoll(); await tgLoadConversations();
}

function tgSetDefaultStatus(){ const s=document.getElementById('tgHeaderStatus'); if(!s||!tgCurrentConvData)return; if(tgCurrentConvData.type==='group'){s.innerHTML=`<span style="color:var(--muted);">${tgCurrentConvData.member_count||0} участников</span>`;return;} s.innerHTML=tgCurrentConvData.is_online?'<span style="color:#10b981;">● онлайн</span>':'<span style="color:var(--muted);">был(а) недавно</span>'; }
function tgUpdateBlockBtnUI(){ const b=document.getElementById('tgBlockBtn'),i=document.getElementById('tgInfoBtn'); if(!tgCurrentConvData)return; i.style.display=tgCurrentConvData.type==='group'?'flex':'none'; if(tgCurrentConvData.type==='private'&&tgCurrentOtherUserId){b.style.display='flex'; if(tgCurrentConvData.is_blocked){b.classList.add('active');document.getElementById('tgBlockIcon').className='bi bi-slash-circle-fill';}else{b.classList.remove('active');document.getElementById('tgBlockIcon').className='bi bi-slash-circle';}}else b.style.display='none'; }
function tgGoBackToList(){ document.getElementById('tgContainer').classList.remove('show-chat'); if(tgPolling)clearInterval(tgPolling); }
function tgSendTyping(){ if(!tgConvId||tgTypingTimeout)return; fetch(`${API}/typing`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':TG_CSRF},body:JSON.stringify({conversation_id:tgConvId})}).catch(()=>{}); tgTypingTimeout=setTimeout(()=>{tgTypingTimeout=null;},3000); }
function tgStartTypingPoll(){ if(tgTypingPollInterval)clearInterval(tgTypingPollInterval); tgTypingPollInterval=setInterval(async()=>{if(!tgConvId)return;try{const r=await fetch(`${API}/typing/${tgConvId}`);const d=await r.json();tgShowTyping(d.typers||[]);}catch(e){}},2000); }
function tgShowTyping(t){ const s=document.getElementById('tgHeaderStatus'); if(!s)return; if(!t.length){tgSetDefaultStatus();return;} s.innerHTML=`<span class="tg-typing">${tgEscapeHtml(t.join(', '))}${t.length===1?' печатает':' печатают'}<span class="tg-typing-dots"><span></span><span></span><span></span></span></span>`; }
async function tgMarkAsRead(id){ try{await fetch(`${API}/mark-read/${id}`,{method:'POST',headers:{'X-CSRF-TOKEN':TG_CSRF}});}catch(e){} }
async function tgLoadMessages(){ if(!tgConvId)return; try{const r=await fetch(`${API}/messages/${tgConvId}`); if(!r.ok)return; tgRenderMessages(await r.json());}catch(e){} }

function tgRenderMessages(msgs){ 
    const c=document.getElementById('tgMessages'), near=c.scrollHeight-c.scrollTop-c.clientHeight<120; 
    let hasNew=false; 
    msgs.forEach(m=>{
        if(m.id>tgLastMsgId){
            tgRenderDateDividerIfNeeded(m);
            tgRenderMessage(m);
            tgLastMsgId=m.id;
            if(String(m.sender_id)!==String(TG_USER_ID))hasNew=true;
        }
    }); 
    if(near)tgScrollDownInstant(); 
    tgUpdateScrollDownButton(); 
    if(hasNew&&tgConvId&&!document.hidden)tgMarkAsRead(tgConvId); 
}

function tgRenderDateDividerIfNeeded(m){ const ds=new Date(m.created_at).toDateString(); if(tgLastRenderedDate===ds)return; const d=document.createElement('div'); d.className='tg-date-divider'; d.innerHTML=`<span>${new Date(m.created_at).toLocaleDateString('ru-RU',{day:'numeric',month:'long'})}</span>`; document.getElementById('tgMessages').appendChild(d); tgLastRenderedDate=ds; }
function tgLinkify(e){ return e.replace(/(https?:\/\/[^\s<]+)/g,u=>`<a href="${u}" target="_blank" rel="noopener noreferrer">${u}</a>`); }

function tgRenderMessage(m){
    const c=document.getElementById('tgMessages'), mine=String(m.sender_id)===String(TG_USER_ID);
    const d=document.createElement('div'); d.className=`tg-message ${mine?'mine':'theirs'}`; d.setAttribute('data-msg-id',m.id);
    let content='';
    if(m.type==='text') content+=`<div class="tg-text">${tgLinkify(tgEscapeHtml(m.body))}</div>`;
    // ✅ Стикеры отображаются как красивые картинки
    else if(m.type==='sticker') content+=`<img src="${getStickerUrl(m.body)}" class="tg-sticker" alt="sticker">`;
    else if(m.type==='image'&&m.attachment_url) content+=`<img src="${m.attachment_url}" class="tg-image" onclick="window.open(this.src)">`;
    else if(m.type==='file'&&m.attachment_url){ const fn=decodeURIComponent((m.attachment_url||'').split('/').pop()||'File'); content+=`<a href="${m.attachment_url}" target="_blank" class="tg-file" download><div class="tg-file-icon"><i class="bi bi-file-earmark"></i></div><div><div class="tg-file-name">${tgEscapeHtml(fn)}</div></div><i class="bi bi-download"></i></a>`; }
    else if(m.type==='voice'&&m.attachment_url) content+=`<div class="tg-voice-msg"><div class="tg-voice-icon"><i class="bi bi-mic-fill"></i></div><audio controls src="${m.attachment_url}" class="tg-voice-audio" preload="metadata"></audio></div>`;
    
    const time=new Date(m.created_at).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
    const checks=mine?`<span class="tg-checks">${m.is_read?'<i class="bi bi-check-all"></i>':'<i class="bi bi-check"></i>'}</span>`:'';
    const sender=(!mine&&m.sender&&tgCurrentConvData?.type==='group')?`<div class="tg-sender">${tgEscapeHtml(m.sender.name)}</div>`:'';
    d.innerHTML=`<div class="tg-bubble">${sender}${content}<div class="tg-time">${time}${checks}</div></div>`;
    d.addEventListener('contextmenu',(e)=>tgShowContextMenu(e,m.id,mine,m));
    c.appendChild(d);
    if(m.type==='voice'){ const a=d.querySelector('audio'); if(a) a.addEventListener('loadedmetadata',()=>{ if(!isFinite(a.duration)||a.duration===0||isNaN(a.duration)){ a.currentTime=1e101; a.ontimeupdate=()=>{a.ontimeupdate=null;a.currentTime=0;}; } }); }
}

function tgEscapeHtml(s){ if(!s)return''; const d=document.createElement('div'); d.textContent=s; return d.innerHTML; }

async function tgSendText(t){ 
    if(!t||!tgConvId)return; 
    const fd=new FormData(); 
    fd.append('conversation_id',tgConvId); 
    fd.append('body',t); 
    fd.append('type','text'); 
    if(tgReplyTo)fd.append('reply_to',tgReplyTo.id); 
    document.getElementById('tgInput').value=''; 
    tgAutoResize(document.getElementById('tgInput')); 
    tgUpdateBtn(); 
    tgUpdateCharCounter(); 
    tgCancelReply(); 
    try{
        const r=await fetch(`${API}/send`,{method:'POST',headers:{'X-CSRF-TOKEN':TG_CSRF},body:fd}); 
        if(r.ok){tgLoadMessages();tgLoadConversations();}
    }catch(e){tgShowToast('❌ Ошибка','error');} 
}

async function tgUploadFile(inp){ 
    if(!inp.files[0]||!tgConvId)return; 
    const f=inp.files[0]; 
    if(f.size>50*1024*1024){tgShowToast('Файл > 50MB','error');inp.value='';return;} 
    const fd=new FormData(); 
    fd.append('conversation_id',tgConvId); 
    fd.append('attachment',f); 
    fd.append('type',f.type.startsWith('image/')?'image':'file'); 
    fd.append('body',''); 
    tgShowToast('📤 Отправка...','info'); 
    try{
        const r=await fetch(`${API}/send`,{method:'POST',headers:{'X-CSRF-TOKEN':TG_CSRF},body:fd}); 
        if(r.ok){inp.value='';tgLoadMessages();tgLoadConversations();tgShowToast('✅ Отправлено','success');}
        else tgShowToast('❌ Ошибка','error');
    }catch(e){tgShowToast('❌ Ошибка','error');inp.value='';} 
}

function tgHandleAction(){ const i=document.getElementById('tgInput'); if(i.value.trim())tgSendText(i.value.trim()); else tgToggleVoice(); }

let tgAudioContext=null,tgAnalyser=null,tgLevelTimer=null,tgMaxLevel=0,tgLevelMeterOk=false;
function tgStartLevelMeter(stream){ tgLevelMeterOk=false; tgMaxLevel=0; try{ tgAudioContext=new (window.AudioContext||window.webkitAudioContext)(); if(tgAudioContext.state==='suspended')tgAudioContext.resume(); const src=tgAudioContext.createMediaStreamSource(stream); tgAnalyser=tgAudioContext.createAnalyser(); tgAnalyser.fftSize=512; src.connect(tgAnalyser); const data=new Uint8Array(tgAnalyser.frequencyBinCount); tgLevelMeterOk=true; tgLevelTimer=setInterval(()=>{ tgAnalyser.getByteTimeDomainData(data); let p=0; for(let i=0;i<data.length;i++){const v=Math.abs(data[i]-128)/128; if(v>p)p=v;} if(p>tgMaxLevel)tgMaxLevel=p; const fl=document.getElementById('tgRecMeterFill'); if(fl)fl.style.width=Math.min(100,p*150)+'%'; },100); }catch(e){console.warn(e);} }
function tgStopLevelMeter(){ if(tgLevelTimer){clearInterval(tgLevelTimer);tgLevelTimer=null;} if(tgAudioContext){try{tgAudioContext.close();}catch(e){}tgAudioContext=null;tgAnalyser=null;} const fl=document.getElementById('tgRecMeterFill'); if(fl)fl.style.width='0%'; }

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
            document.getElementById('tgSendIcon').style.display='block';
            document.getElementById('tgCancelRecBtn').style.display='flex';
            document.getElementById('tgInput').placeholder='🔴 Запись...';
            document.getElementById('tgInput').disabled=true;
            document.getElementById('tgRecordingIndicator').classList.add('active');
            tgRecordTimerInterval=setInterval(()=>{const s=Math.floor((Date.now()-tgRecordStartTime)/1000);document.getElementById('tgRecordTimer').textContent=`${Math.floor(s/60)}:${String(s%60).padStart(2,'0')}`;},500);
        }catch(e){tgStopLevelMeter();tgShowToast('Ошибка микрофона: '+e.message,'error');}
    } else {
        if(tgMediaRecorder?.state==='recording'){ try{tgMediaRecorder.requestData();}catch(e){} await new Promise(r=>setTimeout(r,200)); tgMediaRecorder.stop(); }
    }
}
function tgCancelRecording(){ if(!tgRecording)return; tgDiscardRecording=true; if(tgMediaRecorder?.state==='recording'){try{tgMediaRecorder.stop();}catch(e){}} tgShowToast('🚫 Отменено','info'); }
function tgResetVoiceUI(){ tgRecording=false; tgDiscardRecording=false; if(tgRecordTimerInterval){clearInterval(tgRecordTimerInterval);tgRecordTimerInterval=null;} document.getElementById('tgRecordingIndicator').classList.remove('active'); document.getElementById('tgActionBtn').classList.remove('recording'); document.getElementById('tgCancelRecBtn').style.display='none'; document.getElementById('tgMicIcon').style.display='block'; document.getElementById('tgSendIcon').style.display='none'; const i=document.getElementById('tgInput'); i.placeholder='Написать сообщение...'; i.disabled=false; tgUpdateBtn(); }
function tgHandleKey(e){ if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();tgHandleAction();} }
function tgAutoResize(el){ el.style.height='auto'; el.style.height=Math.min(el.scrollHeight,120)+'px'; tgUpdateBtn(); }
function tgUpdateBtn(){ if(tgRecording)return; const h=document.getElementById('tgInput').value.trim().length>0; document.getElementById('tgMicIcon').style.display=h?'none':'block'; document.getElementById('tgSendIcon').style.display=h?'block':'none'; }
function tgUpdateCharCounter(){ document.getElementById('tgCharCounter').textContent=document.getElementById('tgInput').value.length; }
function tgScrollDownInstant(){ const m=document.getElementById('tgMessages'); if(m)m.scrollTop=m.scrollHeight; }
function tgScrollDown(){ document.getElementById('tgMessages').scrollTo({top:document.getElementById('tgMessages').scrollHeight,behavior:'smooth'}); }
function tgUpdateScrollDownButton(){ const el=document.getElementById('tgMessages'),b=document.getElementById('tgScrollDownBtn'); b.classList.toggle('visible',(el.scrollHeight-el.scrollTop-el.clientHeight)>200); }
function tgShowContextMenu(e,id,mine,m){ e.preventDefault(); tgContextMessageId=id; tgContextMessageData=m; const menu=document.getElementById('tgContextMenu'); document.getElementById('tgCtxDelete').style.display=mine?'flex':'none'; menu.style.left=e.clientX+'px'; menu.style.top=e.clientY+'px'; menu.classList.add('active'); }
function tgReplyToMessage(){ if(!tgContextMessageData)return; tgReplyTo={id:tgContextMessageData.id}; document.getElementById('tgReplyPreview').classList.add('active'); document.getElementById('tgReplyAuthor').textContent=tgContextMessageData.sender?.name||'Вы'; document.getElementById('tgReplyText').textContent=tgContextMessageData.body||'[медиа]'; document.getElementById('tgInput').focus(); document.getElementById('tgContextMenu').classList.remove('active'); }
function tgCancelReply(){ tgReplyTo=null; document.getElementById('tgReplyPreview').classList.remove('active'); }
function tgCopyMessage(){ if(!tgContextMessageData?.body)return; navigator.clipboard.writeText(tgContextMessageData.body).then(()=>tgShowToast('✅ Скопировано','success')); document.getElementById('tgContextMenu').classList.remove('active'); }

async function tgDeleteMessage(){ 
    if(!tgContextMessageId||!confirm('Удалить сообщение?'))return; 
    try{
        const r=await fetch(`${API}/message/${tgContextMessageId}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':TG_CSRF}}); 
        if(r.ok){
            const el=document.querySelector(`[data-msg-id="${tgContextMessageId}"]`); 
            if(el)el.remove(); 
            tgShowToast('🗑 Удалено','success');
        }
    }catch(e){tgShowToast('❌ Ошибка','error');} 
    document.getElementById('tgContextMenu').classList.remove('active'); 
}

// ✅ НОВАЯ ФУНКЦИЯ: Удаление всего чата с подтверждением
function tgDeleteCurrentChat() {
    if (!tgConvId) return;
    const chatName = tgCurrentConvData?.name || 'этот чат';
    if (confirm(`Вы уверены, что хотите удалить чат "${chatName}"? Это действие необратимо.`)) {
        tgDeleteChatById(tgConvId);
    }
}

async function tgOpenInfo(id){ document.getElementById('tgInfoDrawer').classList.add('open'); const b=document.getElementById('tgInfoBody'); b.innerHTML='<div style="padding:60px;text-align:center;"><i class="bi bi-arrow-repeat" style="font-size:22px;animation:spin 1s linear infinite;"></i></div>'; try{const r=await fetch(`${API}/info/${id}`); tgRenderInfo(await r.json());}catch(e){b.innerHTML='<div style="padding:40px;text-align:center;">Ошибка</div>';} }
function tgCloseInfo(){ document.getElementById('tgInfoDrawer').classList.remove('open'); }

function tgRenderInfo(d){
    tgInfoData=d; const b=document.getElementById('tgInfoBody');
    const fb=`https://ui-avatars.com/api/?name=${encodeURIComponent(d.name)}&size=96&background=4f8cff&color=fff&bold=true`;
    let h=`<div class="tg-info-cover"><img src="${d.avatar}" onerror="this.onerror=null;this.src='${fb}'"><div class="tg-info-cover-name">${tgEscapeHtml(d.name)}</div></div>`;
    if(d.type==='group'){
        h+=`<div class="tg-info-stats"><div class="tg-stat-card"><b>${d.stats.messages}</b><span>сообщ.</span></div><div class="tg-stat-card" onclick="tgOpenMedia('file')"><b>${d.stats.files}</b><span>файлов</span></div><div class="tg-stat-card" onclick="tgOpenMedia('image')"><b>${d.stats.photos}</b><span>фото</span></div><div class="tg-stat-card" onclick="tgOpenMedia('voice')"><b>${d.stats.voice}</b><span>голос.</span></div></div>
        <div class="tg-info-section-title"><span>Участники · ${d.member_count}</span>${d.can_add?`<button class="tg-info-add-btn" onclick="tgOpenAddMemberModal()"><i class="bi bi-person-plus-fill"></i> Добавить</button>`:''}</div><div>`;
        d.members.forEach(m=>{ const af=`https://ui-avatars.com/api/?name=${encodeURIComponent(m.name)}&size=40&background=4f8cff&color=fff`; const cr=d.is_manager&&!m.is_creator&&!m.is_me; h+=`<div class="tg-member-row"><a class="tg-member-main" href="/users/${m.id}"><img src="${m.avatar}" onerror="this.onerror=null;this.src='${af}'"><div><div class="tg-member-name">${tgEscapeHtml(m.name)}${m.is_me?' <span style="font-size:10px;background:rgba(var(--glow),0.12);padding:1px 6px;border-radius:8px;">вы</span>':''}</div><div class="tg-member-role ${m.is_creator?'creator':''}">${m.is_creator?'создатель':(m.online?'● онлайн':'был(а) недавно')}</div></div></a>${cr?`<button class="tg-member-remove" onclick="event.preventDefault();tgRemoveMember(${m.id})"><i class="bi bi-x-lg"></i></button>`:''}</div>`; });
        h+=`</div><div class="tg-info-actions">${d.can_leave?`<button class="tg-danger-btn" onclick="tgLeaveGroup()"><i class="bi bi-box-arrow-right"></i> Покинуть</button>`:''}${d.can_delete?`<button class="tg-danger-btn" onclick="tgDeleteChatById(${d.id})"><i class="bi bi-trash"></i> Удалить</button>`:''}</div>`;
    } else {
        h+=`<div style="padding:16px;"><div style="margin-bottom:8px;"><i class="bi bi-envelope-fill" style="color:rgb(var(--glow));"></i> ${tgEscapeHtml(d.other_user?.email||'—')}</div><div><i class="bi bi-telephone-fill" style="color:rgb(var(--glow));"></i> ${tgEscapeHtml(d.other_user?.phone||'—')}</div></div><div class="tg-info-actions"><button class="tg-danger-btn" onclick="tgDeleteChatById(${d.id})"><i class="bi bi-trash"></i> Удалить чат</button></div>`;
    }
    b.innerHTML=h;
}

async function tgOpenMedia(type){ document.getElementById('tgMediaModal').classList.add('active'); const titles={image:'Фото',file:'Файлы',voice:'Голосовые'}; document.getElementById('tgMediaTitle').textContent=titles[type]||''; const b=document.getElementById('tgMediaBody'); b.innerHTML='<div style="padding:40px;text-align:center;"><i class="bi bi-arrow-repeat"></i></div>'; try{const r=await fetch(`${API}/media/${tgConvId}?type=${type}`); const it=await r.json(); if(!it?.length){b.innerHTML='<div style="padding:40px;text-align:center;color:var(--muted);">Пусто</div>';return;} if(type==='image')b.innerHTML=`<div class="tg-media-grid">${it.map(x=>`<img src="${x.url}" onclick="window.open('${x.url}')">`).join('')}</div>`; else if(type==='voice')b.innerHTML=it.map(x=>`<div class="tg-media-row"><i class="bi bi-mic-fill" style="color:rgb(var(--glow));"></i><div style="flex:1;"><audio controls src="${x.url}" preload="metadata"></audio></div></div>`).join(''); else b.innerHTML=it.map(x=>`<div class="tg-media-row"><i class="bi bi-file-earmark-fill" style="color:rgb(var(--glow));"></i><div style="flex:1;"><div style="font-weight:600;">${tgEscapeHtml(x.name)}</div><div style="font-size:11px;color:var(--muted);">${x.time}</div></div><a href="${x.url}" target="_blank" download><i class="bi bi-download"></i></a></div>`).join(''); }catch(e){b.innerHTML='<div style="padding:40px;text-align:center;">Ошибка</div>';} }
function tgCloseMedia(){ document.getElementById('tgMediaModal').classList.remove('active'); }
function tgOpenNewChatModal(){ document.getElementById('tgNewChatModal').classList.add('active'); document.getElementById('tgNewChatSearch').value=''; tgSearchNewChat(''); }
function tgCloseNewChatModal(){ document.getElementById('tgNewChatModal').classList.remove('active'); }
async function tgSearchNewChat(q){ try{const r=await fetch(`${API}/users?q=${encodeURIComponent(q)}`); const u=await r.json(); const c=document.getElementById('tgNewChatResults'); c.innerHTML=''; if(!u?.length){c.innerHTML='<div style="padding:30px;text-align:center;color:var(--muted);">Не найдено</div>';return;} u.forEach(x=>{const fb=`https://ui-avatars.com/api/?name=${encodeURIComponent(x.name)}&size=40&background=4f8cff&color=fff&bold=true`; const d=document.createElement('div'); d.className='tg-user-result'; d.innerHTML=`<img src="${x.avatar||fb}" onerror="this.onerror=null;this.src='${fb}'"><div><div class="tg-user-result-name">${tgEscapeHtml(x.name)}</div><div class="tg-user-result-meta">${tgEscapeHtml([x.email,x.phone].filter(Boolean).join(' • '))}</div></div><i class="bi bi-chat-dots" style="color:rgb(var(--glow));"></i>`; d.onclick=()=>tgStartPrivateChat(x); c.appendChild(d);}); }catch(e){} }
async function tgStartPrivateChat(u){ try{tgShowToast('💬 Открываем...','info'); const r=await fetch(`${API}/private`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':TG_CSRF},body:JSON.stringify({user_id:u.id})}); const d=await r.json(); if(r.ok){tgCloseNewChatModal(); await tgLoadConversations(); const c=tgAllConversations.find(x=>x.id===d.id); if(c)tgOpenChat(c);}}catch(e){tgShowToast('❌ Ошибка','error');} }
function tgOpenGroupModal(){ document.getElementById('tgGroupModal').classList.add('active'); document.getElementById('tgGroupName').value=''; document.getElementById('tgUserSearch').value=''; tgSelectedUsers.clear(); tgRenderSelectedUsers(); tgSearchUsers('','group'); }
function tgCloseGroupModal(){ document.getElementById('tgGroupModal').classList.remove('active'); }
async function tgSearchUsers(q,mode){ try{const r=await fetch(`${API}/users?q=${encodeURIComponent(q)}`); const u=await r.json(); const c=document.getElementById('tgUserResults'); c.innerHTML=''; u.forEach(x=>{const fb=`https://ui-avatars.com/api/?name=${encodeURIComponent(x.name)}&size=40&background=4f8cff&color=fff&bold=true`; const d=document.createElement('div'); d.className='tg-user-result'+(tgSelectedUsers.has(x.id)?' selected':''); d.innerHTML=`<img src="${x.avatar||fb}" onerror="this.onerror=null;this.src='${fb}'"><div><div class="tg-user-result-name">${tgEscapeHtml(x.name)}</div><div class="tg-user-result-meta">${tgEscapeHtml([x.email,x.phone].filter(Boolean).join(' • '))}</div></div><i class="bi bi-${tgSelectedUsers.has(x.id)?'check-circle-fill':'plus-circle'}"></i>`; d.onclick=()=>{if(tgSelectedUsers.has(x.id))tgSelectedUsers.delete(x.id);else tgSelectedUsers.set(x.id,x); tgRenderSelectedUsers(); tgSearchUsers(q,mode);}; c.appendChild(d);}); }catch(e){} }
function tgRenderSelectedUsers(){ const c=document.getElementById('tgSelectedUsers'); c.innerHTML=''; tgSelectedUsers.forEach(u=>{const fb=`https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&size=20`; const t=document.createElement('div'); t.className='tg-user-tag'; t.innerHTML=`<img src="${u.avatar||fb}" onerror="this.onerror=null;this.src='${fb}'" style="width:18px;height:18px;border-radius:50%;">${tgEscapeHtml(u.name)}<span class="tg-user-tag-remove" onclick="tgSelectedUsers.delete(${u.id}); tgRenderSelectedUsers(); tgSearchUsers(document.getElementById('tgUserSearch').value,'group');"><i class="bi bi-x"></i></span>`; c.appendChild(t);}); }
async function tgCreateGroup(){ const n=document.getElementById('tgGroupName').value.trim(), ids=Array.from(tgSelectedUsers.keys()); if(!n){tgShowToast('Введите название','error');return;} if(!ids.length){tgShowToast('Выберите участников','error');return;} try{const r=await fetch(`${API}/group`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':TG_CSRF},body:JSON.stringify({name:n,user_ids:ids})}); if(r.ok){tgCloseGroupModal();tgLoadConversations();tgShowToast('🎉 Создана','success');}}catch(e){tgShowToast('❌ Ошибка','error');} }
function tgOpenAddMemberModal(){ tgAddMembersSelected.clear(); document.getElementById('tgAddMemberModal').classList.add('active'); document.getElementById('tgAddMemberSearch').value=''; tgRenderAddMemberTags(); tgSearchAddMember(''); }
function tgCloseAddMemberModal(){ document.getElementById('tgAddMemberModal').classList.remove('active'); }
async function tgSearchAddMember(q){ try{const r=await fetch(`${API}/users?q=${encodeURIComponent(q)}`); let u=await r.json(); const ex=new Set((tgInfoData?.members||[]).map(m=>m.id)); u=u.filter(x=>!ex.has(x.id)); const c=document.getElementById('tgAddMemberResults'); c.innerHTML=''; u.forEach(x=>{const fb=`https://ui-avatars.com/api/?name=${encodeURIComponent(x.name)}&size=40&background=4f8cff&color=fff&bold=true`; const d=document.createElement('div'); d.className='tg-user-result'+(tgAddMembersSelected.has(x.id)?' selected':''); d.innerHTML=`<img src="${x.avatar||fb}"><div><div class="tg-user-result-name">${tgEscapeHtml(x.name)}</div><div class="tg-user-result-meta">${tgEscapeHtml([x.email,x.phone].filter(Boolean).join(' • '))}</div></div><i class="bi bi-${tgAddMembersSelected.has(x.id)?'check-circle-fill':'plus-circle'}"></i>`; d.onclick=()=>{if(tgAddMembersSelected.has(x.id))tgAddMembersSelected.delete(x.id);else tgAddMembersSelected.set(x.id,x); tgRenderAddMemberTags(); tgSearchAddMember(q);}; c.appendChild(d);}); }catch(e){} }
function tgRenderAddMemberTags(){ const c=document.getElementById('tgAddMemberTags'); c.innerHTML=''; tgAddMembersSelected.forEach(u=>{const t=document.createElement('div'); t.className='tg-user-tag'; t.innerHTML=`${tgEscapeHtml(u.name)}<span class="tg-user-tag-remove" onclick="tgAddMembersSelected.delete(${u.id}); tgRenderAddMemberTags(); tgSearchAddMember(document.getElementById('tgAddMemberSearch').value);"><i class="bi bi-x"></i></span>`; c.appendChild(t);}); }
async function tgConfirmAddMembers(){ const ids=Array.from(tgAddMembersSelected.keys()); if(!ids.length){tgShowToast('Выберите','error');return;} try{const r=await fetch(`${API}/group/${tgInfoData.id}/add-members`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':TG_CSRF},body:JSON.stringify({user_ids:ids})}); if(r.ok){tgCloseAddMemberModal();tgShowToast('✅ Добавлены','success');tgOpenInfo(tgInfoData.id);tgLoadConversations();}}catch(e){tgShowToast('❌ Ошибка','error');} }
async function tgRemoveMember(uid){ if(!confirm('Удалить?'))return; try{const r=await fetch(`${API}/group/${tgInfoData.id}/member/${uid}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':TG_CSRF}}); if(r.ok){tgShowToast('✅ Удалён','success');tgOpenInfo(tgInfoData.id);tgLoadConversations();}}catch(e){tgShowToast('❌ Ошибка','error');} }
async function tgLeaveGroup(){ if(!confirm('Покинуть?'))return; try{const r=await fetch(`${API}/leave/${tgInfoData.id}`,{method:'POST',headers:{'X-CSRF-TOKEN':TG_CSRF}}); if(r.ok){tgShowToast('🚪 Покинули','success');tgCloseInfo();tgConvId=null;tgLoadConversations();document.getElementById('tgEmpty').style.display='flex';document.getElementById('tgHeader').style.display='none';document.getElementById('tgMessages').style.display='none';document.getElementById('tgFooterWrap').style.display='none';}}catch(e){tgShowToast('❌ Ошибка','error');} }
async function tgDeleteChatById(id){ 
    try{
        const r=await fetch(`${API}/conversation/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':TG_CSRF}}); 
        if(r.ok){
            tgShowToast('🗑 Чат удалён','success');
            tgCloseInfo();
            tgConvId=null;
            tgLoadConversations();
            document.getElementById('tgEmpty').style.display='flex';
            document.getElementById('tgHeader').style.display='none';
            document.getElementById('tgMessages').style.display='none';
            document.getElementById('tgFooterWrap').style.display='none';
        }
    }catch(e){tgShowToast('❌ Ошибка','error');} 
}

function tgToggleBlockModal(){ if(!tgCurrentOtherUserId)return; const ib=tgCurrentConvData?.is_blocked, un=tgCurrentConvData?.name; document.getElementById('tgBlockModalTitle').innerHTML=ib?'<i class="bi bi-unlock" style="color:#10b981;"></i> Разблокировать?':'<i class="bi bi-shield-x" style="color:#dc2626;"></i> Заблокировать?'; document.getElementById('tgBlockModalText').textContent=ib?`"${un}" снова сможет писать вам.`:`"${un}" не сможет писать вам.`; document.getElementById('tgBlockConfirmBtn').textContent=ib?'Разблокировать':'Заблокировать'; document.getElementById('tgBlockConfirmBtn').style.background=ib?'#10b981':'#dc2626'; document.getElementById('tgBlockModal').classList.add('active'); }
function tgCloseBlockModal(){ document.getElementById('tgBlockModal').classList.remove('active'); }
async function tgConfirmBlock(){ const ib=tgCurrentConvData?.is_blocked; tgCloseBlockModal(); try{const r=await fetch(`${API}/block/${tgCurrentOtherUserId}`,{method:ib?'DELETE':'POST',headers:{'X-CSRF-TOKEN':TG_CSRF,'Content-Type':'application/json'}}); if(r.ok){if(tgCurrentConvData)tgCurrentConvData.is_blocked=!ib; tgShowToast(ib?'✅ Разблокирован':'✅ Заблокирован','success'); await tgLoadConversations(); const u=tgAllConversations.find(c=>c.id===tgConvId); if(u)await tgOpenChat(u);}}catch(e){tgShowToast('❌ Ошибка','error');} }
async function tgUnblockUser(){ if(tgCurrentOtherUserId)await tgConfirmBlock(); }

function tgShowToast(msg,type='info'){ 
    const o=document.querySelector('.tg-toast-live'); if(o)o.remove(); 
    const t=document.createElement('div'); t.className='tg-toast-live'; 
    const col={success:'#10b981',error:'#ef4444',info:'rgb(var(--glow, 79, 140, 255))'}; 
    t.style.background=col[type]||col.info; 
    t.innerHTML=`<span style="flex:1;">${tgEscapeHtml(msg)}</span>`; 
    document.body.appendChild(t); 
    setTimeout(()=>{t.style.opacity='0';t.style.transition='all .3s';setTimeout(()=>t.remove(),300);},3000); 
}

window.addEventListener('beforeunload',()=>{ if(tgPeerConnection){ tgSendSignal('end',{reason:'page-close'}); tgCleanupCall(); } });
</script>
@endsection