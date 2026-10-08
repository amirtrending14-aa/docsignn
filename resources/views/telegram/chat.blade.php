@extends('layouts.admin')
@section('content')
<meta name="user-id" content="{{ auth()->id() }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">

<style>
/* ===== БАЗА ===== */
.tg-container { display: flex; height: calc(100vh - 120px); background: var(--bg-1); border-radius: var(--radius); overflow: hidden; border: 1px solid var(--line); box-shadow: var(--shadow-card); position: relative; }
.tg-sidebar { width: 340px; border-right: 1px solid var(--line); display: flex; flex-direction: column; background: var(--bg-1); flex-shrink: 0; }
.tg-sidebar-header { padding: 18px 20px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; background: var(--bg-1); gap: 8px; flex-shrink: 0; }
.tg-sidebar-header h3 { font-size: 18px; font-weight: 700; color: var(--text); margin: 0; display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0; }
.tg-header-actions { display: flex; gap: 6px; flex-shrink: 0; }
.tg-header-btn { width: 36px; height: 36px; border-radius: 50%; border: 1px solid var(--line); background: var(--bg-2); color: var(--text); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); flex-shrink: 0; }
.tg-header-btn:hover { background: rgb(var(--glow)); color: white; border-color: rgb(var(--glow)); transform: scale(1.08) rotate(8deg); box-shadow: 0 4px 12px rgba(var(--glow), 0.3); }
.tg-search { padding: 12px 16px; border-bottom: 1px solid var(--line); position: relative; flex-shrink: 0; }
.tg-search input { width: 100%; padding: 10px 14px 10px 38px; border: 1px solid var(--line); border-radius: 20px; background: var(--bg-2); color: var(--text); font-size: 13px; transition: all 0.25s; box-sizing: border-box; }
.tg-search input:focus { outline: none; border-color: rgb(var(--glow)); box-shadow: 0 0 0 3px rgba(var(--glow), 0.12); background: var(--bg-1); }
.tg-search-icon { position: absolute; left: 30px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 14px; pointer-events: none; transition: color 0.2s; }
.tg-search input:focus ~ .tg-search-icon { color: rgb(var(--glow)); }

.tg-list, .tg-messages, .tg-user-results, .tg-emoji-panel, .tg-info-body, .tg-media-body { scrollbar-width: thin; scrollbar-color: rgba(var(--glow), 0.3) transparent; }
.tg-list::-webkit-scrollbar, .tg-messages::-webkit-scrollbar, .tg-user-results::-webkit-scrollbar, .tg-emoji-panel::-webkit-scrollbar, .tg-info-body::-webkit-scrollbar, .tg-media-body::-webkit-scrollbar { width: 6px; }
.tg-list::-webkit-scrollbar-track, .tg-messages::-webkit-scrollbar-track, .tg-user-results::-webkit-scrollbar-track, .tg-emoji-panel::-webkit-scrollbar-track, .tg-info-body::-webkit-scrollbar-track, .tg-media-body::-webkit-scrollbar-track { background: transparent; }
.tg-list::-webkit-scrollbar-thumb, .tg-messages::-webkit-scrollbar-thumb, .tg-user-results::-webkit-scrollbar-thumb, .tg-emoji-panel::-webkit-scrollbar-thumb, .tg-info-body::-webkit-scrollbar-thumb, .tg-media-body::-webkit-scrollbar-thumb { background: rgba(var(--glow), 0.25); border-radius: 10px; }

.tg-list { flex: 1; overflow-y: auto; padding: 4px 0; min-height: 0; }
.tg-item { padding: 10px 14px; margin: 2px 8px; cursor: pointer; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 12px; position: relative; border-radius: 12px; -webkit-tap-highlight-color: transparent; touch-action: manipulation; }
.tg-item:hover { background: var(--bg-2); transform: translateX(2px); }
.tg-item.active { background: linear-gradient(135deg, rgba(var(--glow), 0.12), rgba(var(--glow), 0.05)); }
.tg-item.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 70%; background: linear-gradient(to bottom, rgb(var(--glow)), rgba(var(--glow), 0.5)); border-radius: 0 4px 4px 0; box-shadow: 0 0 8px rgba(var(--glow), 0.4); }
.tg-item-avatar { width: 52px; height: 52px; border-radius: 50%; object-fit: cover; border: 2px solid var(--line); flex-shrink: 0; background: var(--bg-2); transition: all 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.tg-item:hover .tg-item-avatar { border-color: rgba(var(--glow), 0.3); transform: scale(1.03); }
.tg-item-info { flex: 1; min-width: 0; }
.tg-item-name { font-weight: 600; color: var(--text); font-size: 14px; margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; gap: 5px; }
.tg-item-blocked-icon { color: #dc2626; font-size: 12px; }
.tg-item-last { font-size: 13px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }
.tg-item-meta { font-size: 11px; color: var(--muted); flex-shrink: 0; align-self: flex-start; margin-top: 2px; display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
.tg-item-time { font-size: 11px; color: var(--muted); font-weight: 500; }

.tg-no-results { padding: 50px 20px; text-align: center; color: var(--muted); font-size: 13px; display: flex; flex-direction: column; align-items: center; gap: 12px; }
.tg-no-results i { font-size: 48px; opacity: 0.3; color: rgb(var(--glow)); }
.tg-no-results-title { font-weight: 600; color: var(--text); font-size: 15px; }
.tg-no-results-sub { font-size: 12px; line-height: 1.5; max-width: 200px; }

.tg-area { flex: 1; display: flex; flex-direction: column; background: var(--bg-2); position: relative; min-width: 0; min-height: 0; }
.tg-header { padding: 14px 20px; border-bottom: 1px solid var(--line); background: var(--bg-1); display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
.tg-header-avatar { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid var(--line); background: var(--bg-2); transition: all 0.2s; }
.tg-header-info h4 { font-size: 15px; font-weight: 600; color: var(--text); margin: 0 0 2px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tg-header-status { font-size: 12px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.tg-header-profile-link { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; text-decoration: none; padding: 6px 10px; margin: -6px -10px; border-radius: var(--radius); border: 1px solid transparent; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; }
.tg-header-profile-link:hover { background: var(--bg-2); border-color: var(--line); box-shadow: 0 4px 12px rgba(0,0,0,0.06); transform: translateY(-1px); }
.tg-header-profile-link:hover .tg-header-avatar { transform: scale(1.08); border-color: rgba(var(--glow), 0.4); box-shadow: 0 4px 12px rgba(var(--glow), 0.2); }
.tg-header-profile-link:hover .tg-header-info h4 { color: rgb(var(--glow)); }
.tg-header-profile-link.tg-no-link { pointer-events: none; cursor: default; }

.tg-sender-link { display: inline-block; text-decoration: none; border-bottom: 1px dashed transparent; transition: all 0.15s; margin-bottom: 3px; }
.tg-sender-link:hover { border-bottom-color: currentColor; }
.tg-sender-link:hover .tg-sender { text-decoration: underline; }

.tg-messages { flex: 1 1 auto; min-height: 0; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 4px; background: var(--bg-2); }
.tg-message { display: flex; max-width: 65%; animation: tg-fadeIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); margin-bottom: 4px; flex-shrink: 0; }
@keyframes tg-fadeIn { from { opacity: 0; transform: translateY(8px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
.tg-message.mine { align-self: flex-end; justify-content: flex-end; }
.tg-message.theirs { align-self: flex-start; }
.tg-bubble { padding: 10px 14px 8px 14px; border-radius: 18px; position: relative; box-shadow: 0 1px 2px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.04); word-wrap: break-word; cursor: context-menu; transition: all 0.15s; max-width: 100%; }
.tg-bubble:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.tg-message.mine .tg-bubble { background: linear-gradient(135deg, rgb(var(--glow)), rgba(var(--glow), 0.9)); color: white; border-bottom-right-radius: 6px; box-shadow: 0 2px 8px rgba(var(--glow), 0.25); }
.tg-message.theirs .tg-bubble { background: var(--bg-1); color: var(--text); border-bottom-left-radius: 6px; border: 1px solid var(--line); }
.tg-message.mine .tg-bubble::after { content: ''; position: absolute; bottom: 0; right: -6px; width: 12px; height: 12px; background: linear-gradient(135deg, rgb(var(--glow)), rgba(var(--glow), 0.9)); clip-path: polygon(0 100%, 0 0, 100% 100%); }
.tg-message.theirs .tg-bubble::after { content: ''; position: absolute; bottom: 0; left: -6px; width: 12px; height: 12px; background: var(--bg-1); clip-path: polygon(100% 100%, 100% 0, 0 100%); border-left: 1px solid var(--line); }
.tg-sender { font-size: 11px; font-weight: 700; color: rgb(var(--glow)); margin-bottom: 3px; letter-spacing: 0.3px; }
.tg-text { font-size: 14px; line-height: 1.5; white-space: pre-wrap; word-break: break-word; }
.tg-text a { color: inherit; text-decoration: underline; word-break: break-all; }
.tg-message.theirs .tg-text a { color: rgb(var(--glow)); }
.tg-time { font-size: 10px; opacity: 0.7; text-align: right; margin-top: 4px; display: inline-flex; align-items: center; float: right; margin-left: 10px; font-weight: 500; gap: 2px; }
.tg-message.mine .tg-time { color: rgba(255,255,255,0.85); }
.tg-image { max-width: 100%; max-height: 300px; border-radius: 12px; margin-bottom: 6px; cursor: pointer; display: block; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.tg-image:hover { transform: scale(1.02); box-shadow: 0 8px 24px rgba(0,0,0,0.15); }
.tg-file { display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: rgba(255,255,255,0.15); border-radius: 12px; text-decoration: none; color: inherit; transition: all 0.2s; }
.tg-file:hover { background: rgba(255,255,255,0.25); transform: translateY(-1px); }
.tg-message.theirs .tg-file { background: rgba(var(--glow), 0.08); }
.tg-message.theirs .tg-file:hover { background: rgba(var(--glow), 0.15); }
.tg-file-icon { width: 36px; height: 36px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.tg-message.theirs .tg-file-icon { background: rgba(var(--glow), 0.15); color: rgb(var(--glow)); }
.tg-file-info { flex: 1; min-width: 0; }
.tg-file-name { font-weight: 600; font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.tg-file-size { font-size: 11px; opacity: 0.7; margin-top: 2px; }
.tg-voice-msg { display: flex; align-items: center; gap: 10px; min-width: 220px; }
.tg-voice-icon { width: 36px; height: 36px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.tg-message.theirs .tg-voice-icon { background: rgba(var(--glow), 0.15); color: rgb(var(--glow)); }
.tg-voice-audio { flex: 1; height: 32px; min-width: 150px; }

.tg-date-divider { text-align: center; margin: 16px 0; position: relative; display: flex; justify-content: center; flex-shrink: 0; }
.tg-date-divider span { background: rgba(var(--glow), 0.08); color: rgb(var(--glow)); padding: 4px 14px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; backdrop-filter: blur(8px); border: 1px solid rgba(var(--glow), 0.15); }

.tg-scroll-down-btn { position: absolute; bottom: 96px; right: 24px; width: 42px; height: 42px; border-radius: 50%; background: var(--bg-1); color: var(--text); border: 1px solid var(--line); cursor: pointer; display: none; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.25s; z-index: 10; }
.tg-scroll-down-btn.visible { display: flex; animation: tg-slideInUp 0.3s ease-out; }
.tg-scroll-down-btn:hover { background: rgb(var(--glow)); color: white; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(var(--glow), 0.3); }
@keyframes tg-slideInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.tg-footer-wrap { position: relative; background: var(--bg-1); border-top: 1px solid var(--line); flex-shrink: 0; padding-bottom: env(safe-area-inset-bottom, 0); }
.tg-footer { padding: 14px 20px; display: flex; gap: 10px; align-items: flex-end; transition: all 0.2s; flex-wrap: nowrap; }
.tg-footer.tg-recording-state { background: rgba(239, 68, 68, 0.04); }
.tg-input-wrapper { flex: 1; position: relative; min-width: 0; }
.tg-input { width: 100%; padding: 12px 16px; padding-right: 50px; border: 1px solid var(--line); border-radius: 22px; background: var(--bg-2); color: var(--text); font-size: 14px; resize: none; max-height: 120px; font-family: inherit; transition: all 0.25s; box-sizing: border-box; }
.tg-input:focus { outline: none; border-color: rgb(var(--glow)); box-shadow: 0 0 0 3px rgba(var(--glow), 0.12); background: var(--bg-1); }
.tg-input:disabled { opacity: 0.6; cursor: not-allowed; }
.tg-recording-state .tg-input { border-color: #ef4444; background: rgba(239, 68, 68, 0.05); }
.tg-char-counter { position: absolute; right: 14px; bottom: 10px; font-size: 10px; color: var(--muted); font-weight: 500; pointer-events: none; transition: color 0.2s; }
.tg-char-counter.warning { color: #f59e0b; }
.tg-char-counter.danger { color: #ef4444; }

.tg-btn { width: 44px; height: 44px; border-radius: 50%; border: none; background: rgb(var(--glow)); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); flex-shrink: 0; box-shadow: 0 2px 8px rgba(var(--glow), 0.25); }
.tg-btn:hover { transform: scale(1.08) rotate(5deg); box-shadow: 0 6px 16px rgba(var(--glow), 0.4); }
.tg-btn:active { transform: scale(0.95); }
.tg-btn.recording { background: #ef4444; animation: tg-pulse 1.5s infinite; box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5); }
@keyframes tg-pulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5); } 50% { box-shadow: 0 0 0 12px rgba(239, 68, 68, 0); } }
.tg-btn-secondary { background: var(--bg-2); color: var(--text); border: 1px solid var(--line); box-shadow: none; }
.tg-btn-secondary:hover { background: var(--bg-1); border-color: rgb(var(--glow)); color: rgb(var(--glow)); transform: scale(1.08) rotate(-5deg); box-shadow: 0 4px 12px rgba(var(--glow), 0.15); }

.tg-btn-cancel-recording { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; animation: tg-pulse-red 1.5s infinite; border: 1px solid rgba(239, 68, 68, 0.5); }
.tg-btn-cancel-recording:hover { background: linear-gradient(135deg, #dc2626, #b91c1c); transform: scale(1.08) rotate(5deg); box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4); }
@keyframes tg-pulse-red { 0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5); } 50% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); } }

.tg-empty { flex: 1; display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 20px; color: var(--muted); padding: 40px; text-align: center; background: radial-gradient(circle at center, rgba(var(--glow), 0.03), transparent 70%); min-height: 0; }
.tg-empty-icon { font-size: 96px; opacity: 0.15; color: rgb(var(--glow)); animation: tg-float 3s ease-in-out infinite; }
@keyframes tg-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
.tg-empty h4 { color: var(--text); margin: 0; font-size: 18px; font-weight: 600; }
.tg-empty p { margin: 0; font-size: 13px; max-width: 280px; line-height: 1.5; }

.tg-recording-indicator { display: none; position: fixed; bottom: calc(100px + env(safe-area-inset-bottom, 0)); left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 14px 28px; border-radius: 50px; z-index: 999; align-items: center; gap: 14px; box-shadow: 0 8px 24px rgba(239, 68, 68, 0.4); font-weight: 600; font-size: 14px; backdrop-filter: blur(10px); }
.tg-recording-indicator.active { display: flex; animation: tg-slideInUp 0.3s ease-out; }
.tg-recording-dot { width: 10px; height: 10px; background: white; border-radius: 50%; animation: tg-recDot 1s infinite; box-shadow: 0 0 8px rgba(255,255,255,0.5); }
@keyframes tg-recDot { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.4; transform: scale(1.3); } }
.tg-recording-timer { font-family: 'SF Mono', Monaco, monospace; font-weight: 700; }

.tg-modal { position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); display: none; align-items: center; justify-content: center; z-index: 1000; padding: 20px; }
.tg-modal.active { display: flex; animation: tg-modalIn 0.25s ease-out; }
@keyframes tg-modalIn { from { opacity: 0; } to { opacity: 1; } }
.tg-modal-content { background: var(--bg-1); border-radius: 16px; padding: 24px; width: 100%; max-width: 520px; border: 1px solid var(--line); box-shadow: 0 25px 80px rgba(0,0,0,0.3); max-height: 90vh; display: flex; flex-direction: column; animation: tg-modalContentIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
@keyframes tg-modalContentIn { from { opacity: 0; transform: scale(0.9) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
.tg-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 10px; }
.tg-modal-title { font-size: 18px; font-weight: 700; color: var(--text); margin: 0; flex: 1; min-width: 0; }
.tg-modal-close { width: 32px; height: 32px; border-radius: 8px; border: none; background: var(--bg-2); color: var(--text); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s; flex-shrink: 0; }
.tg-modal-close:hover { background: #ef4444; color: white; transform: rotate(90deg); }
.tg-modal-input { width: 100%; padding: 11px 14px; border: 1px solid var(--line); border-radius: var(--radius); background: var(--bg-2); color: var(--text); font-size: 14px; margin-bottom: 12px; transition: all 0.2s; box-sizing: border-box; }
.tg-modal-input:focus { outline: none; border-color: rgb(var(--glow)); box-shadow: 0 0 0 3px rgba(var(--glow), 0.12); }
.tg-search-wrapper { position: relative; margin-bottom: 12px; }
.tg-search-wrapper input { padding-left: 38px !important; margin-bottom: 0 !important; border-radius: 10px; }
.tg-search-wrapper i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); pointer-events: none; }
.tg-selected-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; min-height: 8px; }
.tg-user-tag { display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, rgba(var(--glow), 0.15), rgba(var(--glow), 0.08)); color: rgb(var(--glow)); padding: 5px 10px 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; animation: tg-tagIn 0.25s ease-out; border: 1px solid rgba(var(--glow), 0.2); }
@keyframes tg-tagIn { from { opacity: 0; transform: scale(0.8); } to { opacity: 1; transform: scale(1); } }
.tg-user-tag-remove { width: 16px; height: 16px; border-radius: 50%; background: rgba(var(--glow), 0.25); display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 10px; transition: all 0.15s; }
.tg-user-tag-remove:hover { background: #ef4444; color: white; transform: rotate(90deg); }
.tg-user-results { max-height: 320px; overflow-y: auto; border: 1px solid var(--line); border-radius: 10px; background: var(--bg-2); }
.tg-user-result { padding: 10px 14px; display: flex; align-items: center; gap: 12px; cursor: pointer; border-bottom: 1px solid var(--line); transition: all 0.15s; }
.tg-user-result:last-child { border-bottom: none; }
.tg-user-result:hover { background: rgba(var(--glow), 0.08); padding-left: 18px; }
.tg-user-result.selected { background: rgba(var(--glow), 0.12); }
.tg-user-result img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0; background: var(--bg-1); transition: all 0.2s; }
.tg-user-result:hover img { transform: scale(1.05); }
.tg-user-result-info { flex: 1; min-width: 0; }
.tg-user-result-name { font-weight: 600; color: var(--text); font-size: 14px; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tg-user-result-meta { font-size: 12px; color: var(--muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.tg-user-result-check { font-size: 20px; color: var(--muted); flex-shrink: 0; transition: all 0.2s; }
.tg-user-result.selected .tg-user-result-check { color: rgb(var(--glow)); transform: scale(1.1); }
.tg-highlight { background: rgba(var(--glow), 0.3); padding: 0 2px; border-radius: 2px; }
.tg-modal-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 20px; flex-wrap: wrap; }
.tg-modal-actions .tg-btn { width: auto; border-radius: 10px; padding: 10px 18px; height: auto; font-size: 14px; font-weight: 600; }
.tg-modal-actions .tg-btn-secondary { background: transparent; border: 1px solid var(--line); }

.tg-avatar-wrap { position: relative; display: inline-block; flex-shrink: 0; }
.tg-online-dot { position: absolute; bottom: 2px; right: 2px; width: 12px; height: 12px; background: #10b981; border: 2px solid var(--bg-1); border-radius: 50%; box-shadow: 0 0 0 1px rgba(16, 185, 129, 0.3), 0 0 8px rgba(16, 185, 129, 0.5); animation: tg-onlinePulse 2s infinite; }
@keyframes tg-onlinePulse { 0%, 100% { box-shadow: 0 0 0 1px rgba(16, 185, 129, 0.3), 0 0 8px rgba(16, 185, 129, 0.5); } 50% { box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2), 0 0 16px rgba(16, 185, 129, 0.4); } }
.tg-unread-badge { min-width: 20px; height: 20px; padding: 0 6px; background: linear-gradient(135deg, rgb(var(--glow)), rgba(var(--glow), 0.85)); color: white; border-radius: 10px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(var(--glow), 0.4); animation: tg-badgePulse 2s infinite; }
@keyframes tg-badgePulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }

.tg-typing { font-size: 12px; color: rgb(var(--glow)); font-style: italic; display: inline-flex; align-items: center; gap: 4px; }
.tg-typing-dots { display: inline-flex; gap: 2px; }
.tg-typing-dots span { width: 4px; height: 4px; background: rgb(var(--glow)); border-radius: 50%; animation: tg-typingDot 1.4s infinite ease-in-out; }
.tg-typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.tg-typing-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes tg-typingDot { 0%, 60%, 100% { opacity: 0.3; transform: scale(0.8); } 30% { opacity: 1; transform: scale(1.2); } }

.tg-reply-preview { border-left: 3px solid rgb(var(--glow)); padding: 10px 14px; background: rgba(var(--glow), 0.08); border-radius: 8px; margin: 0 20px; font-size: 12px; position: relative; display: none; animation: tg-slideDown 0.2s ease-out; }
@keyframes tg-slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
.tg-reply-preview.active { display: block; }
.tg-reply-preview-author { font-weight: 700; color: rgb(var(--glow)); margin-bottom: 2px; }
.tg-reply-preview-text { color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: calc(100% - 30px); }
.tg-reply-close { position: absolute; top: 6px; right: 8px; cursor: pointer; color: var(--muted); font-size: 14px; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.15s; }
.tg-reply-close:hover { color: #ef4444; background: rgba(239,68,68,0.1); transform: rotate(90deg); }
.tg-reply-in-msg { border-left: 3px solid currentColor; opacity: 0.85; padding: 4px 8px; margin-bottom: 6px; border-radius: 4px; font-size: 12px; cursor: pointer; background: rgba(255,255,255,0.1); transition: all 0.15s; max-width: 100%; }
.tg-reply-in-msg:hover { background: rgba(255,255,255,0.18); }
.tg-message.theirs .tg-reply-in-msg { background: rgba(var(--glow), 0.05); }
.tg-message.theirs .tg-reply-in-msg:hover { background: rgba(var(--glow), 0.12); }
.tg-reply-in-msg-author { font-weight: 700; margin-bottom: 2px; }
.tg-reply-in-msg-text { opacity: 0.8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 250px; }

.tg-context-menu { position: fixed; background: var(--bg-1); border: 1px solid var(--line); border-radius: 12px; box-shadow: 0 12px 32px rgba(0,0,0,0.18); z-index: 1500; min-width: 200px; max-width: calc(100vw - 20px); padding: 6px; display: none; animation: tg-menuIn 0.15s ease-out; }
@keyframes tg-menuIn { from { opacity: 0; transform: scale(0.95) translateY(-4px); } to { opacity: 1; transform: scale(1) translateY(0); } }
.tg-context-menu.active { display: block; }
.tg-context-item { padding: 10px 14px; cursor: pointer; font-size: 13px; color: var(--text); display: flex; align-items: center; gap: 12px; transition: all 0.15s; border-radius: 8px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tg-context-item:hover { background: rgba(var(--glow), 0.08); transform: translateX(2px); }
.tg-context-item.danger { color: #ef4444; }
.tg-context-item.danger:hover { background: rgba(239,68,68,0.08); }
.tg-context-item i { font-size: 16px; width: 20px; text-align: center; flex-shrink: 0; }
.tg-context-separator { height: 1px; background: var(--line); margin: 4px 8px; }

/* ✅ ПЛОТНАЯ ПАНЕЛЬ ЭМОДЗИ */
.tg-emoji-panel { position: absolute; bottom: 70px; left: 20px; background: var(--bg-1); border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 12px 32px rgba(0,0,0,0.15); padding: 10px; width: 348px; max-width: calc(100vw - 40px); max-height: 320px; overflow-y: auto; z-index: 100; display: none; }
.tg-emoji-panel.active { display: block; animation: tg-menuIn 0.2s ease-out; }
.tg-emoji-grid { display: grid; grid-template-columns: repeat(8, 1fr); gap: 2px; }
.tg-emoji-btn { width: 100%; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; font-size: 22px; line-height: 1; cursor: pointer; border-radius: 10px; transition: all 0.12s; border: none; background: transparent; padding: 0; }
.tg-emoji-btn:hover { background: var(--bg-2); transform: scale(1.25); z-index: 2; position: relative; }

.tg-error-banner { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #991b1b; padding: 12px 14px; margin: 10px; border-radius: 10px; font-size: 13px; border: 1px solid #fca5a5; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 500; }
.tg-error-banner i { font-size: 16px; }

.tg-blocked-banner { background: linear-gradient(135deg, #fee2e2, #fecaca); border-top: 1px solid #fca5a5; color: #991b1b; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px; animation: tg-slideDown 0.3s ease-out; flex-shrink: 0; flex-wrap: wrap; }
.tg-blocked-banner-info { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; flex: 1; min-width: 150px; }
.tg-blocked-banner-info i { font-size: 18px; color: #dc2626; flex-shrink: 0; }
.tg-blocked-banner-info span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tg-blocked-banner .tg-btn-unblock { background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 6px; flex-shrink: 0; box-shadow: 0 2px 8px rgba(220,38,38,0.3); white-space: nowrap; }
.tg-blocked-banner .tg-btn-unblock:hover { background: linear-gradient(135deg, #b91c1c, #991b1b); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220,38,38,0.4); }

.tg-header-actions-right { display: flex; gap: 6px; margin-left: auto; flex-shrink: 0; }
.tg-block-btn { width: 36px; height: 36px; border-radius: 50%; border: 1px solid var(--line); background: var(--bg-2); color: var(--text); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.25s; flex-shrink: 0; }
.tg-block-btn:hover { background: #fee2e2; color: #dc2626; border-color: #fca5a5; transform: rotate(8deg) scale(1.08); }
.tg-block-btn.active { background: #fee2e2; color: #dc2626; border-color: #fca5a5; }
.tg-block-btn.active:hover { background: #10b981; color: white; border-color: #10b981; }

.tg-block-modal { position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); display: none; align-items: center; justify-content: center; z-index: 2000; padding: 20px; }
.tg-block-modal.active { display: flex; animation: tg-modalIn 0.2s ease-out; }
.tg-block-modal-content { background: var(--bg-1); border-radius: 16px; padding: 24px; max-width: 400px; width: 100%; box-shadow: 0 25px 80px rgba(0,0,0,0.3); animation: tg-modalContentIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.tg-block-modal h3 { margin: 0 0 10px 0; color: #dc2626; display: flex; align-items: center; gap: 8px; }
.tg-block-modal.unblock h3 { color: #10b981; }
.tg-block-modal p { color: var(--text); font-size: 14px; margin: 0 0 20px 0; line-height: 1.5; }
.tg-block-modal-actions { display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap; }
.tg-block-modal-actions button { padding: 10px 20px; border-radius: 10px; border: none; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.2s; }
.tg-block-modal-actions .tg-cancel-btn { background: var(--bg-2); color: var(--text); border: 1px solid var(--line); }
.tg-block-modal-actions .tg-cancel-btn:hover { background: var(--bg-1); }
.tg-block-modal-actions .tg-confirm-btn { background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; box-shadow: 0 2px 8px rgba(220,38,38,0.3); }
.tg-block-modal-actions .tg-confirm-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220,38,38,0.4); }
.tg-block-modal.unblock .tg-confirm-btn { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 2px 8px rgba(16,185,129,0.3); }

.tg-toast-live { position: fixed; top: 20px; right: 20px; padding: 12px 20px; border-radius: 12px; display: flex; align-items: center; gap: 10px; box-shadow: 0 12px 32px rgba(0,0,0,0.15); z-index: 3000; font-weight: 600; min-width: 260px; max-width: calc(100vw - 40px); overflow: hidden; animation: tg-toastIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
@keyframes tg-toastIn { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }
.tg-toast-progress { position: absolute; bottom: 0; left: 0; height: 3px; background: rgba(255,255,255,0.5); width: 100%; animation: tg-toastProgress 3s linear; }
@keyframes tg-toastProgress { from { width: 100%; } to { width: 0; } }

/* ===== ИНФО-ПАНЕЛЬ ===== */
.tg-spin { animation: tg-spinAnim 1s linear infinite; display: inline-block; }
@keyframes tg-spinAnim { to { transform: rotate(360deg); } }

.tg-info-drawer { position: absolute; top: 0; right: 0; bottom: 0; width: 360px; max-width: 100%; background: var(--bg-1); border-left: 1px solid var(--line); z-index: 60; transform: translateX(105%); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; box-shadow: -12px 0 32px rgba(0,0,0,0.12); }
.tg-info-drawer.open { transform: translateX(0); }
.tg-info-header { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 16px; border-bottom: 1px solid var(--line); flex-shrink: 0; }
.tg-info-header-title { font-size: 15px; font-weight: 700; color: var(--text); flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.tg-info-body { flex: 1; overflow-y: auto; display: flex; flex-direction: column; }
.tg-info-loader { padding: 60px 0; text-align: center; color: var(--muted); font-size: 22px; }

.tg-info-cover { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 24px 16px 18px; text-align: center; background: linear-gradient(180deg, rgba(var(--glow), 0.06), transparent); }
.tg-info-cover img { width: 88px; height: 88px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(var(--glow), 0.35); box-shadow: 0 6px 18px rgba(var(--glow), 0.25); }
.tg-info-cover-name { font-size: 17px; font-weight: 700; color: var(--text); word-break: break-word; }
.tg-info-cover-sub { font-size: 12px; color: var(--muted); }

.tg-info-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; padding: 4px 16px 14px; }
.tg-stat-card { background: var(--bg-2); border: 1px solid var(--line); border-radius: 10px; padding: 10px 6px; display: flex; flex-direction: column; align-items: center; gap: 3px; transition: all 0.2s; min-width: 0; }
.tg-stat-card.clickable { cursor: pointer; }
.tg-stat-card.clickable:hover { border-color: rgba(var(--glow), 0.5); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(var(--glow), 0.15); }
.tg-stat-card i { font-size: 15px; color: rgb(var(--glow)); }
.tg-stat-card b { font-size: 14px; color: var(--text); }
.tg-stat-card span { font-size: 9px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.4px; text-align: center; }

.tg-info-section-title { padding: 10px 16px 6px; font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.8px; display: flex; align-items: center; justify-content: space-between; gap: 8px; }

/* ✅ ЯВНЫЕ КНОПКИ УПРАВЛЕНИЯ ГРУППОЙ */
.tg-info-add-btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(var(--glow), 0.35); background: rgba(var(--glow), 0.1); color: rgb(var(--glow)); font-size: 11px; font-weight: 700; cursor: pointer; transition: all 0.2s; text-transform: none; letter-spacing: 0; }
.tg-info-add-btn:hover { background: rgb(var(--glow)); color: white; transform: scale(1.05); }

.tg-info-members { display: flex; flex-direction: column; }
.tg-member-row { display: flex; align-items: center; gap: 6px; padding: 3px 10px 3px 16px; }
.tg-member-main { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; padding: 7px 6px; text-decoration: none; color: inherit; border-radius: 10px; transition: background 0.15s; }
.tg-member-main:hover { background: var(--bg-2); }
.tg-member-main img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
.tg-member-main .dot { position: absolute; bottom: 1px; right: 1px; width: 10px; height: 10px; border-radius: 50%; border: 2px solid var(--bg-1); }
.tg-member-main .dot.on { background: #10b981; }
.tg-member-main .dot.off { background: #94a3b8; }
.tg-member-info { flex: 1; min-width: 0; }
.tg-member-name { font-size: 13px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
.tg-member-you { font-size: 10px; font-weight: 700; color: rgb(var(--glow)); background: rgba(var(--glow), 0.12); padding: 1px 6px; border-radius: 8px; }
.tg-member-role { font-size: 11px; color: var(--muted); }
.tg-member-role.creator { color: rgb(var(--glow)); font-weight: 600; }
.tg-member-chev { color: var(--muted); font-size: 12px; flex-shrink: 0; }

/* ✅ КНОПКА УДАЛЕНИЯ УЧАСТНИКА */
.tg-member-remove { width: 28px; height: 28px; border-radius: 50%; border: 1px solid rgba(239,68,68,0.3); background: rgba(239,68,68,0.08); color: #ef4444; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: all 0.15s; font-size: 11px; }
.tg-member-remove:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: rotate(90deg) scale(1.1); box-shadow: 0 4px 12px rgba(239,68,68,0.3); }

.tg-info-contacts { padding: 4px 0; }
.tg-info-contacts .tg-member-row { padding: 4px 16px; }
.tg-info-contacts i.lead { color: rgb(var(--glow)); width: 20px; text-align: center; flex-shrink: 0; font-size: 15px; }
.tg-info-contacts span.val { font-size: 13px; color: var(--text); word-break: break-all; }

.tg-info-actions { padding: 12px 16px calc(16px + env(safe-area-inset-bottom, 0)); border-top: 1px solid var(--line); display: flex; flex-direction: column; gap: 8px; flex-shrink: 0; background: var(--bg-1); }
.tg-danger-btn { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 16px; border-radius: 10px; border: 1px solid rgba(239,68,68,0.35); background: rgba(239,68,68,0.08); color: #ef4444; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; width: 100%; }
.tg-danger-btn:hover { background: rgba(239,68,68,0.16); transform: translateY(-1px); }

/* ===== ГАЛЕРЕЯ МЕДИА ===== */
.tg-media-body { padding: 4px 16px 16px; overflow-y: auto; }
.tg-media-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; }
.tg-media-grid img { width: 100%; aspect-ratio: 1/1; object-fit: cover; border-radius: 10px; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; border: 1px solid var(--line); }
.tg-media-grid img:hover { transform: scale(1.04); box-shadow: 0 6px 16px rgba(0,0,0,0.2); }
.tg-media-row { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid var(--line); border-radius: 10px; margin-bottom: 6px; background: var(--bg-2); transition: all 0.15s; }
.tg-media-row:hover { border-color: rgba(var(--glow), 0.4); }
.tg-media-row > i { color: rgb(var(--glow)); font-size: 18px; flex-shrink: 0; }
.tg-media-row .nm { flex: 1; min-width: 0; font-size: 12px; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.tg-media-row .nm a { color: rgb(var(--glow)); word-break: break-all; white-space: normal; }
.tg-media-row .tm { font-size: 10px; color: var(--muted); margin-top: 2px; }
.tg-media-row a.dl { color: rgb(var(--glow)); font-size: 16px; flex-shrink: 0; text-decoration: none; }
.tg-media-row audio { width: 100%; height: 32px; }

.tg-checks { margin-left: 4px; font-size: 11px; opacity: 0.95; display: inline-flex; }
.tg-checks i { font-size: 11px; }
.tg-message.mine .tg-checks { color: rgba(255,255,255,0.95); }

.tg-back-btn { display: none; width: 36px; height: 36px; border-radius: 50%; border: 1px solid var(--line); background: var(--bg-2); color: var(--text); cursor: pointer; align-items: center; justify-content: center; transition: all 0.2s; flex-shrink: 0; }
.tg-back-btn:hover { background: rgb(var(--glow)); color: white; border-color: rgb(var(--glow)); }

/* ===== 📱 АДАПТИВ ===== */
@media (max-width: 1024px) { .tg-sidebar { width: 300px; } .tg-info-drawer { width: 320px; } }
@media (max-width: 900px) { .tg-sidebar { width: 280px; } .tg-info-drawer { width: 300px; } .tg-message { max-width: 75%; } }
@media (max-width: 768px) {
    .tg-container { height: calc(100vh - 60px); border-radius: 0; border: none; }
    .tg-sidebar { width: 100%; }
    .tg-area { display: none; }
    .tg-container.show-chat .tg-sidebar { display: none; }
    .tg-container.show-chat .tg-area { display: flex; }
    .tg-message { max-width: 85%; }
    .tg-modal-content, .tg-block-modal-content { max-width: 95%; padding: 18px; }
    .tg-emoji-panel { width: calc(100vw - 24px); left: 12px; max-height: 45vh; }
    .tg-back-btn { display: flex; }
    .tg-header { padding: 12px 14px; gap: 8px; }
    .tg-header-avatar { width: 40px; height: 40px; }
    .tg-header-info h4 { font-size: 14px; }
    .tg-header-status { font-size: 11px; }
    .tg-messages { padding: 12px 10px; }
    .tg-footer { padding: 10px 12px; gap: 6px; }
    .tg-btn, .tg-btn-cancel-recording { width: 40px; height: 40px; }
    .tg-input { font-size: 16px; padding: 10px 14px; padding-right: 44px; }
    .tg-sidebar-header { padding: 12px 14px; }
    .tg-sidebar-header h3 { font-size: 16px; }
    .tg-header-btn { width: 34px; height: 34px; }
    .tg-search { padding: 8px 12px; }
    .tg-search input { padding: 9px 12px 9px 36px; font-size: 14px; }
    .tg-search-icon { left: 26px; }
    .tg-scroll-down-btn { bottom: calc(80px + env(safe-area-inset-bottom, 0)); right: 14px; width: 38px; height: 38px; }
    .tg-recording-indicator { bottom: calc(90px + env(safe-area-inset-bottom, 0)); padding: 10px 18px; font-size: 13px; }
    .tg-blocked-banner { padding: 10px 14px; font-size: 12px; }
    .tg-toast-live { top: 10px; right: 10px; left: 10px; max-width: none; min-width: auto; }
    .tg-info-drawer { position: fixed; inset: 0; width: 100%; z-index: 1600; border-left: none; transform: translateY(100%); max-width: 100%; }
    .tg-info-drawer.open { transform: translateY(0); }
    .tg-info-cover img { width: 80px; height: 80px; }
    .tg-info-cover-name { font-size: 16px; }
    .tg-media-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 480px) {
    .tg-container { height: calc(100vh - 50px); }
    .tg-footer { padding: 8px 8px; gap: 4px; }
    .tg-btn, .tg-btn-cancel-recording { width: 38px; height: 38px; }
    .tg-input { font-size: 16px; padding: 9px 12px; padding-right: 40px; border-radius: 20px; }
    .tg-messages { padding: 10px 6px; }
    .tg-message { max-width: 90%; }
    .tg-bubble { padding: 8px 12px 6px 12px; font-size: 13px; }
    .tg-text { font-size: 13px; }
    .tg-image { max-height: 220px; }
    .tg-voice-msg { min-width: 180px; }
    .tg-emoji-panel { width: calc(100vw - 16px); left: 8px; }
    .tg-emoji-grid { grid-template-columns: repeat(7, 1fr); }
    .tg-info-stats { grid-template-columns: repeat(3, 1fr); gap: 6px; }
    .tg-stat-card { padding: 8px 4px; }
    .tg-stat-card span { font-size: 8px; }
    .tg-modal-content { padding: 16px; }
    .tg-modal-title { font-size: 16px; }
    .tg-modal-input { font-size: 15px; padding: 10px 12px; }
    .tg-modal-actions .tg-btn { padding: 9px 14px; font-size: 13px; }
    .tg-block-modal-content { padding: 18px; }
    .tg-block-modal-actions button { padding: 9px 16px; font-size: 13px; flex: 1; }
    .tg-media-grid { grid-template-columns: repeat(2, 1fr); }
    .tg-info-add-btn { padding: 5px 10px; font-size: 10px; }
    .tg-member-remove { width: 26px; height: 26px; font-size: 10px; }
}
@media (max-width: 380px) {
    .tg-container { height: calc(100vh - 45px); }
    .tg-footer { padding: 6px 4px; gap: 3px; }
    .tg-btn, .tg-btn-cancel-recording { width: 36px; height: 36px; }
    .tg-input { padding: 8px 10px; padding-right: 36px; font-size: 15px; }
    .tg-messages { padding: 8px 4px; }
    .tg-bubble { padding: 7px 10px 5px 10px; font-size: 13px; }
    .tg-sidebar-header h3 { font-size: 15px; }
    .tg-item { padding: 8px 10px; margin: 1px 4px; gap: 8px; }
    .tg-item-avatar { width: 44px; height: 44px; }
    .tg-item-name { font-size: 13px; }
    .tg-item-last { font-size: 12px; }
    .tg-header { padding: 10px 8px; }
    .tg-header-avatar { width: 36px; height: 36px; }
    .tg-header-info h4 { font-size: 13px; }
    .tg-block-btn, .tg-header-btn, .tg-back-btn { width: 32px; height: 32px; }
    .tg-info-stats { grid-template-columns: repeat(2, 1fr); }
    .tg-info-cover { padding: 16px 12px 12px; }
    .tg-info-cover img { width: 70px; height: 70px; }
    .tg-info-cover-name { font-size: 15px; }
    .tg-member-row { padding: 3px 6px 3px 10px; }
    .tg-member-main img { width: 36px; height: 36px; }
    .tg-member-name { font-size: 12px; }
    .tg-danger-btn { padding: 10px 12px; font-size: 12px; }
    .tg-modal-content { padding: 14px; }
    .tg-modal-title { font-size: 15px; }
    .tg-context-menu { min-width: 170px; }
    .tg-context-item { padding: 9px 12px; font-size: 12px; gap: 10px; }
    .tg-recording-indicator { padding: 8px 14px; font-size: 12px; gap: 8px; }
    .tg-voice-msg { min-width: 150px; gap: 6px; }
    .tg-voice-icon { width: 30px; height: 30px; font-size: 14px; }
    .tg-voice-audio { height: 28px; }
    .tg-file { padding: 8px 10px; gap: 6px; }
    .tg-file-icon { width: 30px; height: 30px; font-size: 14px; }
    .tg-file-name { font-size: 12px; }
    .tg-emoji-grid { grid-template-columns: repeat(6, 1fr); }
}
@media (max-width: 340px) {
    .tg-container { height: 100vh; }
    .tg-footer { padding: 4px 2px; gap: 2px; }
    .tg-btn, .tg-btn-cancel-recording { width: 34px; height: 34px; }
    .tg-input { padding: 7px 8px; padding-right: 32px; font-size: 14px; }
    .tg-item-avatar { width: 40px; height: 40px; }
    .tg-item { gap: 6px; padding: 6px 8px; }
    .tg-header { padding: 8px 6px; gap: 6px; }
    .tg-back-btn, .tg-block-btn, .tg-header-btn { width: 30px; height: 30px; font-size: 12px; }
    .tg-header-avatar { width: 32px; height: 32px; }
    .tg-modal-content, .tg-block-modal-content { padding: 12px; }
    .tg-modal-actions { gap: 6px; }
    .tg-modal-actions .tg-btn { padding: 8px 12px; font-size: 12px; }
}
@media (max-height: 500px) and (orientation: landscape) {
    .tg-container { height: 100vh; }
    .tg-empty { padding: 20px; gap: 10px; }
    .tg-empty-icon { font-size: 60px; }
    .tg-info-cover { padding: 12px 12px 8px; }
    .tg-info-cover img { width: 60px; height: 60px; }
    .tg-messages { padding: 10px; }
    .tg-recording-indicator { bottom: 80px; padding: 8px 16px; }
}
@media (max-width: 900px) and (orientation: landscape) {
    .tg-container { height: 100vh; }
    .tg-sidebar-header { padding: 8px 12px; }
    .tg-header { padding: 8px 12px; }
    .tg-footer { padding: 6px 12px; }
    .tg-messages { padding: 8px; }
    .tg-info-drawer { width: 40%; min-width: 280px; }
}
@media (hover: none) and (pointer: coarse) {
    .tg-item:hover { background: transparent; transform: none; }
    .tg-item.active:hover { background: linear-gradient(135deg, rgba(var(--glow), 0.12), rgba(var(--glow), 0.05)); }
    .tg-btn:hover { transform: none; }
    .tg-btn:active { transform: scale(0.92); }
    .tg-emoji-btn:hover { background: transparent; transform: none; }
    .tg-emoji-btn:active { background: var(--bg-2); transform: scale(1.15); }
    .tg-context-item:hover { background: transparent; transform: none; }
    .tg-context-item:active { background: rgba(var(--glow), 0.08); }
}
html[data-theme="dark"] .tg-stat-card { background: rgba(255,255,255,0.03); }
html[data-theme="dark"] .tg-member-main:hover { background: rgba(255,255,255,0.05); }
html[data-theme="dark"] .tg-danger-btn { background: rgba(239,68,68,0.12); }
@media print { .tg-container, .tg-recording-indicator, .tg-toast-live { display: none !important; } }
</style>

<div class="tg-container" id="tgContainer">
    <div class="tg-sidebar">
        <div class="tg-sidebar-header">
            <h3><i class="bi bi-chat-dots-fill" style="color: rgb(var(--glow));"></i> <span data-i18n="chats">Чаты</span></h3>
            <div class="tg-header-actions">
                <button class="tg-header-btn" onclick="tgOpenNewChatModal()" data-i18n-title="new_chat_btn" title="Новый личный чат"><i class="bi bi-pencil-square"></i></button>
                <button class="tg-header-btn" onclick="tgOpenGroupModal()" data-i18n-title="new_group_btn" title="Создать группу"><i class="bi bi-people-fill"></i></button>
            </div>
        </div>
        <div class="tg-search">
            <i class="bi bi-search tg-search-icon"></i>
            <input type="text" id="tgSearch" data-i18n-placeholder="search_chats" placeholder="Поиск чатов...">
        </div>
        <div class="tg-list" id="tgList"></div>
    </div>

    <div class="tg-area" id="tgArea">
        <div class="tg-empty" id="tgEmpty">
            <div class="tg-empty-icon">💬</div>
            <div>
                <h4 data-i18n="empty_title">Выберите чат</h4>
                <p data-i18n="empty_desc">Или создайте новый чат с помощью кнопок в шапке.</p>
            </div>
        </div>

        <div class="tg-header" id="tgHeader" style="display: none;">
            <button class="tg-back-btn" onclick="tgGoBackToList()" data-i18n-title="back_to_list" title="Назад"><i class="bi bi-arrow-left"></i></button>
            <a href="#" id="tgHeaderProfileLink" class="tg-header-profile-link" title="">
                <div class="tg-avatar-wrap">
                    <img src="" class="tg-header-avatar" id="tgHeaderAvatar">
                    <div class="tg-online-dot" id="tgHeaderOnlineDot" style="display: none;"></div>
                </div>
                <div class="tg-header-info" style="flex: 1; min-width: 0;">
                    <h4 id="tgHeaderName"></h4>
                    <div class="tg-header-status" id="tgHeaderStatus"></div>
                </div>
            </a>
            <div class="tg-header-actions-right" id="tgHeaderActions">
                <button class="tg-block-btn" id="tgInfoBtn" onclick="tgOpenInfo(tgConvId)" data-i18n-title="ctx_chat_info" title="Инфо" style="display: none;">
                    <i class="bi bi-people"></i>
                </button>
                <button class="tg-block-btn" id="tgBlockBtn" onclick="tgToggleBlockModal()" title="Заблокировать" style="display: none;">
                    <i class="bi bi-slash-circle" id="tgBlockIcon"></i>
                </button>
            </div>
        </div>

        <div class="tg-blocked-banner" id="tgBlockedBanner" style="display: none;">
            <div class="tg-blocked-banner-info">
                <i class="bi bi-shield-x"></i>
                <span id="tgBlockedBannerText" data-i18n="blocked_banner">Вы заблокировали этого пользователя</span>
            </div>
            <button class="tg-btn-unblock" onclick="tgUnblockUser()">
                <i class="bi bi-unlock"></i> <span data-i18n="unblock_btn">Разблокировать</span>
            </button>
        </div>

        <div class="tg-messages" id="tgMessages" style="display: none;"></div>

        <button class="tg-scroll-down-btn" id="tgScrollDownBtn" onclick="tgScrollDown()" data-i18n-title="scroll_down" title="К новым сообщениям">
            <i class="bi bi-arrow-down"></i>
        </button>

        <div class="tg-footer-wrap" id="tgFooterWrap" style="display: none;">
            <div class="tg-reply-preview" id="tgReplyPreview">
                <span class="tg-reply-close" onclick="tgCancelReply()"><i class="bi bi-x-lg"></i></span>
                <div class="tg-reply-preview-author" id="tgReplyAuthor"></div>
                <div class="tg-reply-preview-text" id="tgReplyText"></div>
            </div>
            <div class="tg-footer" id="tgFooter">
                <button class="tg-btn tg-btn-secondary" onclick="tgToggleEmojiPanel()" data-i18n-title="emoji_title" title="Эмодзи"><i class="bi bi-emoji-smile"></i></button>
                <label class="tg-btn tg-btn-secondary" data-i18n-title="attach_title" title="Прикрепить файл">
                    <i class="bi bi-paperclip"></i>
                    <input type="file" id="tgFileInput" style="display: none;" onchange="tgUploadFile(this)">
                </label>
                <div class="tg-input-wrapper">
                    <textarea class="tg-input" id="tgInput" rows="1" data-i18n-placeholder="message_ph" placeholder="Написать сообщение..." onkeydown="tgHandleKey(event)" oninput="tgAutoResize(this); tgSendTyping(); tgUpdateCharCounter()"></textarea>
                    <span class="tg-char-counter" id="tgCharCounter">0</span>
                </div>
                <button class="tg-btn tg-btn-cancel-recording" id="tgCancelRecBtn" onclick="tgCancelRecording()" data-i18n-title="cancel_recording" title="Отменить запись" style="display: none;">
                    <i class="bi bi-x-lg"></i>
                </button>
                <button class="tg-btn" id="tgActionBtn" onclick="tgHandleAction()" data-i18n-title="send_title" title="Отправить / Записать голос">
                    <i class="bi bi-mic-fill" id="tgMicIcon"></i>
                    <i class="bi bi-send-fill" id="tgSendIcon" style="display: none;"></i>
                </button>
            </div>
            <div class="tg-emoji-panel" id="tgEmojiPanel">
                <div class="tg-emoji-grid" id="tgEmojiGrid"></div>
            </div>
        </div>

        <!-- ✅ ПАНЕЛЬ ИНФО -->
        <div class="tg-info-drawer" id="tgInfoDrawer">
            <div class="tg-info-header">
                <button class="tg-modal-close" onclick="tgCloseInfo()"><i class="bi bi-x-lg"></i></button>
                <div class="tg-info-header-title" id="tgInfoTitle" data-i18n="ctx_chat_info">Инфо</div>
                <div style="width:32px; flex-shrink:0;"></div>
            </div>
            <div class="tg-info-body" id="tgInfoBody">
                <div class="tg-info-loader"><i class="bi bi-arrow-repeat tg-spin"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="tg-recording-indicator" id="tgRecordingIndicator">
    <div class="tg-recording-dot"></div>
    <span data-i18n="recording">🎙 Запись</span>
    <span class="tg-recording-timer" id="tgRecordTimer">0:00</span>
</div>

<div class="tg-context-menu" id="tgContextMenu">
    <div class="tg-context-item" onclick="tgReplyToMessage()"><i class="bi bi-reply"></i> <span data-i18n="ctx_reply">Ответить</span></div>
    <div class="tg-context-item" onclick="tgCopyMessage()"><i class="bi bi-clipboard"></i> <span data-i18n="ctx_copy">Копировать</span></div>
    <div class="tg-context-separator"></div>
    <div class="tg-context-item danger" id="tgCtxDelete" onclick="tgDeleteMessage()"><i class="bi bi-trash"></i> <span data-i18n="ctx_delete">Удалить</span></div>
</div>

<div class="tg-context-menu" id="tgChatContextMenu">
    <div class="tg-context-item" onclick="tgChatCtxInfo()"><i class="bi bi-info-circle"></i> <span data-i18n="ctx_chat_info">Инфо</span></div>
    <div class="tg-context-item" onclick="tgChatCtxUnread()"><i class="bi bi-envelope"></i> <span data-i18n="ctx_mark_unread">Непрочитанное</span></div>
    <div class="tg-context-separator"></div>
    <div class="tg-context-item danger" id="tgChatCtxLeave" onclick="tgChatCtxLeave()"><i class="bi bi-box-arrow-right"></i> <span data-i18n="leave_group">Покинуть группу</span></div>
    <div class="tg-context-item danger" onclick="tgChatCtxDelete()"><i class="bi bi-trash"></i> <span data-i18n="delete_chat">Удалить чат</span></div>
</div>

<div class="tg-modal" id="tgNewChatModal">
    <div class="tg-modal-content">
        <div class="tg-modal-header">
            <h3 class="tg-modal-title"><i class="bi bi-pencil-square" style="color: rgb(var(--glow)); margin-right: 8px;"></i><span data-i18n="new_chat_title">Новый чат</span></h3>
            <button class="tg-modal-close" onclick="tgCloseNewChatModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="tg-search-wrapper">
            <i class="bi bi-search"></i>
            <input type="text" class="tg-modal-input" id="tgNewChatSearch" data-i18n-placeholder="search_users_ph" placeholder="Имя, email или телефон..." autocomplete="off">
        </div>
        <div class="tg-user-results" id="tgNewChatResults" style="max-height: 400px;"></div>
    </div>
</div>

<div class="tg-modal" id="tgGroupModal">
    <div class="tg-modal-content">
        <div class="tg-modal-header">
            <h3 class="tg-modal-title"><i class="bi bi-people-fill" style="color: rgb(var(--glow)); margin-right: 8px;"></i><span data-i18n="new_group_title">Новая группа</span></h3>
            <button class="tg-modal-close" onclick="tgCloseGroupModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <input type="text" class="tg-modal-input" id="tgGroupName" data-i18n-placeholder="group_name_ph" placeholder="Название группы">
        <div class="tg-search-wrapper">
            <i class="bi bi-search"></i>
            <input type="text" class="tg-modal-input" id="tgUserSearch" data-i18n-placeholder="search_users_ph" placeholder="Поиск: имя, email, телефон..." autocomplete="off">
        </div>
        <div class="tg-selected-tags" id="tgSelectedUsers"></div>
        <div class="tg-user-results" id="tgUserResults"></div>
        <div class="tg-modal-actions">
            <button class="tg-btn tg-btn-secondary" onclick="tgCloseGroupModal()" data-i18n="cancel">Отмена</button>
            <button class="tg-btn" onclick="tgCreateGroup()"><i class="bi bi-check2" style="margin-right: 6px;"></i><span data-i18n="create_btn">Создать</span></button>
        </div>
    </div>
</div>

<!-- ✅ МОДАЛКА: ДОБАВИТЬ УЧАСТНИКОВ -->
<div class="tg-modal" id="tgAddMemberModal">
    <div class="tg-modal-content">
        <div class="tg-modal-header">
            <h3 class="tg-modal-title"><i class="bi bi-person-plus-fill" style="color: rgb(var(--glow)); margin-right: 8px;"></i><span data-i18n="add_member">Добавить участника</span></h3>
            <button class="tg-modal-close" onclick="tgCloseAddMemberModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="tg-search-wrapper">
            <i class="bi bi-search"></i>
            <input type="text" class="tg-modal-input" id="tgAddMemberSearch" data-i18n-placeholder="search_users_ph" placeholder="Имя, email или телефон..." autocomplete="off">
        </div>
        <div class="tg-selected-tags" id="tgAddMemberTags"></div>
        <div class="tg-user-results" id="tgAddMemberResults" style="max-height: 300px;"></div>
        <div class="tg-modal-actions">
            <button class="tg-btn tg-btn-secondary" onclick="tgCloseAddMemberModal()" data-i18n="cancel">Отмена</button>
            <button class="tg-btn" onclick="tgConfirmAddMembers()"><i class="bi bi-person-plus" style="margin-right: 6px;"></i><span data-i18n="add_member_btn">Добавить</span></button>
        </div>
    </div>
</div>

<!-- ✅ МОДАЛКА: ГАЛЕРЕЯ МЕДИА -->
<div class="tg-modal" id="tgMediaModal">
    <div class="tg-modal-content" style="max-width: 640px;">
        <div class="tg-modal-header">
            <h3 class="tg-modal-title" id="tgMediaTitle"></h3>
            <button class="tg-modal-close" onclick="tgCloseMedia()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="tg-media-body" id="tgMediaBody" style="max-height: 65vh;"></div>
    </div>
</div>

<div class="tg-block-modal" id="tgBlockModal">
    <div class="tg-block-modal-content">
        <h3><i class="bi bi-shield-x" id="tgBlockModalIcon"></i> <span id="tgBlockModalTitle">Заблокировать?</span></h3>
        <p id="tgBlockModalText"></p>
        <div class="tg-block-modal-actions">
            <button class="tg-cancel-btn" onclick="tgCloseBlockModal()" data-i18n="cancel">Отмена</button>
            <button class="tg-confirm-btn" id="tgBlockConfirmBtn" onclick="tgConfirmBlock()">Заблокировать</button>
        </div>
    </div>
</div>

<script>
const TG_USER_ID = document.querySelector('meta[name="user-id"]').content;
const TG_CSRF = document.querySelector('meta[name="csrf-token"]').content;

const TG_TRANSLATIONS = {
    ru: {
        chats: 'Чаты', new_chat_btn: 'Новый личный чат', new_group_btn: 'Создать группу',
        search_chats: 'Поиск чатов...', empty_title: 'Выберите чат',
        empty_desc: 'Или создайте новый чат с помощью кнопок в шапке.',
        online: '● онлайн', last_seen: 'был(а) недавно',
        blocked_banner: 'Вы заблокировали этого пользователя',
        blocked_banner_user: 'Вы заблокировали пользователя ',
        unblock_btn: 'Разблокировать', scroll_down: 'К новым сообщениям',
        ctx_reply: 'Ответить', ctx_copy: 'Копировать', ctx_delete: 'Удалить',
        new_chat_title: 'Новый чат', new_group_title: 'Новая группа',
        search_users_ph: 'Имя, email или телефон...', group_name_ph: 'Название группы',
        cancel: 'Отмена', create_btn: 'Создать',
        message_ph: 'Написать сообщение...', emoji_title: 'Эмодзи',
        attach_title: 'Прикрепить файл', send_title: 'Отправить / Записать голос',
        recording: '🎙 Запись', recording_ph: '🔴 Запись...',
        typing_one: ' печатает', typing_many: ' печатают',
        no_chats_title: 'Нет чатов', no_chats_sub: 'Создайте новый чат с помощью кнопок выше',
        no_results_title: 'Ничего не найдено', no_results_sub: 'Попробуйте изменить запрос',
        no_messages: 'Нет сообщений', today: 'Сегодня', yesterday: 'Вчера',
        users_not_found_title: 'Не найдено', users_not_found_sub: 'Попробуйте другой запрос',
        file_too_big: 'Файл слишком большой (макс. 50MB)',
        sending_file: '📤 Отправка файла...', file_sent: '✅ Файл отправлен!',
        no_rec_support: 'Браузер не поддерживает запись', too_short: 'Слишком коротко',
        voice_sent: '🎤 Голосовое отправлено!', send_failed: 'Не удалось отправить',
        mic_error: 'Ошибка микрофона', copied: '📋 Скопировано!',
        deleted: '🗑 Удалено', delete_error: '❌ Ошибка удаления',
        confirm_delete: 'Удалить сообщение?', opening_chat: '💬 Открываем чат с ',
        enter_name: 'Введите название', select_members: 'Выберите участников',
        group_created: '🎉 Группа создана!', blocking: '🔒 Блокировка...',
        unblocking: '🔓 Разблокировка...', user_blocked: '✅ Пользователь заблокирован!',
        user_unblocked: '✅ Пользователь разблокирован!',
        server_error: 'Ошибка сервера', load_chats_failed: 'Не удалось загрузить чаты',
        open_profile: 'Открыть профиль: ', you: 'Вы', media: '[медиа]',
        download: 'Скачать', deleted_msg: 'Удалено',
        block_title: 'Заблокировать?', unblock_title: 'Разблокировать?',
        block_desc_pre: 'Пользователь "', block_desc_mid: '" не сможет писать вам. Разблокировать можно в любое время.',
        unblock_desc_pre: 'Пользователь "', unblock_desc_mid: '" снова сможет писать вам и видеть ваши сообщения.',
        block_btn: 'Заблокировать', unblock_btn_confirm: 'Разблокировать',
        send_failed_with: 'Не удалось отправить: ', error_generic: 'Ошибка',
        blocked_label: 'Заблокирован', group_label: 'Группа',
        cancel_recording: 'Отменить запись', recording_canceled: '🚫 Запись отменена',
        ctx_chat_info: 'Информация', ctx_mark_unread: 'Пометить непрочитанным',
        leave_group: 'Покинуть группу', delete_chat: 'Удалить чат',
        confirm_leave: 'Покинуть группу?', confirm_delete_chat: 'Удалить чат? Действие необратимо.',
        info_members: 'Участники', info_you: 'вы', info_creator: 'создатель',
        left_group: '🚪 Вы покинули группу', chat_deleted: '🗑 Чат удалён',
        back_to_list: 'Назад к списку',
        members: { one: 'участник', few: 'участника', many: 'участников' },
        files_word: { one: 'файл', few: 'файла', many: 'файлов' },
        links_word: { one: 'ссылка', few: 'ссылки', many: 'ссылок' },
        stat_messages: 'сообщ.', stat_photos: 'фото', stat_voice: 'голос.',
        add_member: '+ Добавить', add_member_btn: 'Добавить',
        remove_member: 'Удалить из группы',
        confirm_remove_member: 'Удалить участника из группы?',
        member_removed: '✅ Участник удалён', member_added: '✅ Участники добавлены',
        gallery_photos: '📷 Фото', gallery_files: '📎 Файлы', gallery_voice: '🎤 Голосовые', gallery_links: '🔗 Ссылки',
        no_media: 'Пока ничего нет',
    },
    tj: {
        chats: 'Чатҳо', new_chat_btn: 'Чати нави шахсӣ', new_group_btn: 'Эҷоди гурӯҳ',
        search_chats: 'Ҷустуҷӯи чатҳо...', empty_title: 'Чатро интихоб кунед',
        empty_desc: 'Ё бо тугмаҳои боло чати нав созед.',
        online: '● дар онлайн', last_seen: 'ба наздикӣ буд',
        blocked_banner: 'Шумо ин корбарро бастаед',
        blocked_banner_user: 'Шумо корбари бастед: ',
        unblock_btn: 'Кушодан', scroll_down: 'Ба паёмҳои нав',
        ctx_reply: 'Ҷавоб додан', ctx_copy: 'Нусхабардорӣ', ctx_delete: 'Нест кардан',
        new_chat_title: 'Чати нав', new_group_title: 'Гурӯҳи нав',
        search_users_ph: 'Ном, email ё телефон...', group_name_ph: 'Номи гурӯҳ',
        cancel: 'Лағв', create_btn: 'Эҷод кардан',
        message_ph: 'Паём нависед...', emoji_title: 'Эмоҷи',
        attach_title: 'Замима кардани файл', send_title: 'Фиристодан / Сабти овоз',
        recording: '🎙 Сабт', recording_ph: '🔴 Сабт...',
        typing_one: ' менависад', typing_many: ' менависанд',
        no_chats_title: 'Чатҳо нестанд', no_chats_sub: 'Бо тугмаҳои боло чати нав созед',
        no_results_title: 'Чизе ёфт нашуд', no_results_sub: 'Дархостро дигар кунед',
        no_messages: 'Паёмҳо нестанд', today: 'Имрӯз', yesterday: 'Дирӯз',
        users_not_found_title: 'Ёфт нашуд', users_not_found_sub: 'Дархости дигарро санҷед',
        file_too_big: 'Файл хеле калон аст (макс. 50MB)',
        sending_file: '📤 Фиристодани файл...', file_sent: '✅ Файл фиристода шуд!',
        no_rec_support: 'Браузер сабтро дастгирӣ намекунад', too_short: 'Хеле кӯтоҳ',
        voice_sent: '🎤 Паёми овозӣ фиристода шуд!', send_failed: 'Фиристода нашуд',
        mic_error: 'Хатои микрофон', copied: '📋 Нусхабардорӣ шуд!',
        deleted: '🗑 Нест карда шуд', delete_error: '❌ Хатои несткунӣ',
        confirm_delete: 'Паём нест карда шавад?', opening_chat: '💬 Кушодани чат бо ',
        enter_name: 'Номро ворид кунед', select_members: 'Иштирокчиёнро интихоб кунед',
        group_created: '🎉 Гурӯҳ сохта шуд!', blocking: '🔒 Баста истодааст...',
        unblocking: '🔓 Кушода истодааст...', user_blocked: '✅ Корбар баста шуд!',
        user_unblocked: '✅ Корбар кушода шуд!',
        server_error: 'Хатои сервер', load_chats_failed: 'Боргирии чатҳо муяссар нашуд',
        open_profile: 'Кушодани профил: ', you: 'шумо', media: '[медиа]',
        download: 'Боргирӣ', deleted_msg: 'Нестшуда',
        block_title: 'Бастани корбар?', unblock_title: 'Кушодани корбар?',
        block_desc_pre: 'Корбари "', block_desc_mid: '" дигар наметавонад ба шумо нависад. Кушодан мумкин аст.',
        unblock_desc_pre: 'Корбари "', unblock_desc_mid: '" боз метавонад ба шумо нависад ва паёмҳоро бинад.',
        block_btn: 'Бастани', unblock_btn_confirm: 'Кушодан',
        send_failed_with: 'Фиристода нашуд: ', error_generic: 'Хато',
        blocked_label: 'Басташуда', group_label: 'Гурӯҳ',
        cancel_recording: 'Бекор кардани сабт', recording_canceled: '🚫 Сабт бекор карда шуд',
        ctx_chat_info: 'Маълумот', ctx_mark_unread: 'Ба нахонда гузаронидан',
        leave_group: 'Баромадан аз гурӯҳ', delete_chat: 'Нест кардани чат',
        confirm_leave: 'Аз гурӯҳ баромадан?', confirm_delete_chat: 'Чат нест карда шавад? Амал бозгашт надорад.',
        info_members: 'Иштирокчиён', info_you: 'шумо', info_creator: 'соҳиб',
        left_group: '🚪 Шумо аз гурӯҳ баромадед', chat_deleted: '🗑 Чат нест карда шуд',
        back_to_list: 'Бозгашт ба рӯйхат',
        members: 'иштирокчӣ',
        files_word: 'файл',
        links_word: 'пайванд',
        stat_messages: 'паём', stat_photos: 'акс', stat_voice: 'овозӣ',
        add_member: '+ Илова', add_member_btn: 'Илова',
        remove_member: 'Аз гурӯҳ нест кардан',
        confirm_remove_member: 'Иштирокчиро аз гурӯҳ нест кардан?',
        member_removed: '✅ Иштирокчӣ нест карда шуд', member_added: '✅ Иштирокчиён илова шуданд',
        gallery_photos: '📷 Аксҳо', gallery_files: '📎 Файлҳо', gallery_voice: '🎤 Овозӣ', gallery_links: '🔗 Пайвандҳо',
        no_media: 'Ҳоло чизе нест',
    },
    en: {
        chats: 'Chats', new_chat_btn: 'New private chat', new_group_btn: 'Create group',
        search_chats: 'Search chats...', empty_title: 'Select a chat',
        empty_desc: 'Or start a new conversation using the buttons above.',
        online: '● online', last_seen: 'last seen recently',
        blocked_banner: 'You have blocked this user',
        blocked_banner_user: 'You have blocked: ',
        unblock_btn: 'Unblock', scroll_down: 'To new messages',
        ctx_reply: 'Reply', ctx_copy: 'Copy', ctx_delete: 'Delete',
        new_chat_title: 'New chat', new_group_title: 'New group',
        search_users_ph: 'Name, email or phone...', group_name_ph: 'Group name',
        cancel: 'Cancel', create_btn: 'Create',
        message_ph: 'Write a message...', emoji_title: 'Emoji',
        attach_title: 'Attach file', send_title: 'Send / Record voice',
        recording: '🎙 Recording', recording_ph: '🔴 Recording...',
        typing_one: ' is typing', typing_many: ' are typing',
        no_chats_title: 'No chats', no_chats_sub: 'Create a new chat using the buttons above',
        no_results_title: 'Nothing found', no_results_sub: 'Try changing your query',
        no_messages: 'No messages', today: 'Today', yesterday: 'Yesterday',
        users_not_found_title: 'Not found', users_not_found_sub: 'Try another query',
        file_too_big: 'File is too large (max 50MB)',
        sending_file: '📤 Sending file...', file_sent: '✅ File sent!',
        no_rec_support: 'Browser does not support recording', too_short: 'Too short',
        voice_sent: '🎤 Voice message sent!', send_failed: 'Failed to send',
        mic_error: 'Microphone error', copied: '📋 Copied!',
        deleted: '🗑 Deleted', delete_error: '❌ Delete error',
        confirm_delete: 'Delete message?', opening_chat: '💬 Opening chat with ',
        enter_name: 'Enter a name', select_members: 'Select members',
        group_created: '🎉 Group created!', blocking: '🔒 Blocking...',
        unblocking: '🔓 Unblocking...', user_blocked: '✅ User blocked!',
        user_unblocked: '✅ User unblocked!',
        server_error: 'Server error', load_chats_failed: 'Failed to load chats',
        open_profile: 'Open profile: ', you: 'you', media: '[media]',
        download: 'Download', deleted_msg: 'Deleted',
        block_title: 'Block user?', unblock_title: 'Unblock user?',
        block_desc_pre: 'User "', block_desc_mid: '" will not be able to write to you. You can unblock anytime.',
        unblock_desc_pre: 'User "', unblock_desc_mid: '" will be able to write to you and see your messages again.',
        block_btn: 'Block', unblock_btn_confirm: 'Unblock',
        send_failed_with: 'Failed to send: ', error_generic: 'Error',
        blocked_label: 'Blocked', group_label: 'Group',
        cancel_recording: 'Cancel recording', recording_canceled: '🚫 Recording canceled',
        ctx_chat_info: 'Info', ctx_mark_unread: 'Mark as unread',
        leave_group: 'Leave group', delete_chat: 'Delete chat',
        confirm_leave: 'Leave group?', confirm_delete_chat: 'Delete chat? This cannot be undone.',
        info_members: 'Members', info_you: 'you', info_creator: 'creator',
        left_group: '🚪 You left the group', chat_deleted: '🗑 Chat deleted',
        back_to_list: 'Back to list',
        members: { one: 'member', few: 'members', many: 'members' },
        files_word: { one: 'file', few: 'files', many: 'files' },
        links_word: { one: 'link', few: 'links', many: 'links' },
        stat_messages: 'msgs', stat_photos: 'photos', stat_voice: 'voice',
        add_member: '+ Add', add_member_btn: 'Add',
        remove_member: 'Remove from group',
        confirm_remove_member: 'Remove member from group?',
        member_removed: '✅ Member removed', member_added: '✅ Members added',
        gallery_photos: '📷 Photos', gallery_files: '📎 Files', gallery_voice: '🎤 Voice', gallery_links: '🔗 Links',
        no_media: 'Nothing yet',
    }
};

let TG_LANG = localStorage.getItem('docsign_lang') || localStorage.getItem('app-lang') || 'ru';
if (!TG_TRANSLATIONS[TG_LANG]) TG_LANG = 'ru';

function tgT(key) {
    const v = TG_TRANSLATIONS[TG_LANG]?.[key];
    if (typeof v === 'string') return v;
    const r = TG_TRANSLATIONS.ru[key];
    return typeof r === 'string' ? r : (key);
}

function tgPlural(key, n) {
    const entry = TG_TRANSLATIONS[TG_LANG]?.[key] ?? TG_TRANSLATIONS.ru[key];
    if (typeof entry === 'string') return entry;
    if (!entry) return '';
    if (TG_LANG === 'en') return n === 1 ? entry.one : (entry.many || entry.one);
    const m10 = n % 10, m100 = n % 100;
    if (m10 === 1 && m100 !== 11) return entry.one;
    if (m10 >= 2 && m10 <= 4 && (m100 < 12 || m100 > 14)) return entry.few || entry.many;
    return entry.many || entry.few || entry.one;
}

const TG_LOCALES = { ru: 'ru-RU', tj: 'tg-TJ', en: 'en-US' };

function tgApplyTranslations() {
    const scope = '.tg-container, .tg-modal, .tg-block-modal, .tg-context-menu, .tg-recording-indicator';
    document.querySelectorAll(scope + ' [data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        const val = tgT(key);
        if (val !== key) el.textContent = val;
    });
    document.querySelectorAll(scope + ' [data-i18n-placeholder]').forEach(el => {
        const key = el.getAttribute('data-i18n-placeholder');
        const val = tgT(key);
        if (val !== key) el.setAttribute('placeholder', val);
    });
    document.querySelectorAll(scope + ' [data-i18n-title]').forEach(el => {
        const key = el.getAttribute('data-i18n-title');
        const val = tgT(key);
        if (val !== key) el.setAttribute('title', val);
    });
}

let tgConvId = null, tgPolling = null, tgLastMsgId = 0, tgAllConversations = [];
let tgCurrentConvData = null, tgCurrentOtherUserId = null;
let tgRecording = false, tgMediaRecorder = null, tgAudioChunks = [];
let tgRecordTimerInterval = null, tgRecordStartTime = 0, tgCurrentMimeType = '';
let tgSelectedUsers = new Map(), tgSearchTimeout = null, tgNewChatSearchTimeout = null;
let tgReplyTo = null, tgContextMessageId = null, tgContextMessageIsMine = false, tgContextMessageData = null;
let tgTypingTimeout = null, tgTypingPollInterval = null, tgHeartbeatInterval = null;
let tgLastRenderedDate = null, tgDiscardRecording = false;
let tgTouchTimer = null, tgChatCtxConv = null, tgInfoData = null;
let tgAddMembersSelected = new Map(), tgAddMemberSearchTimeout = null;

const EMOJIS = ['😀','😃','😄','😁','😆','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😝','😜','🧐','🤓','😎','🥳','😏','😒','😞','😔','😟','😕','🙁','😣','😖','😫','😩','🥺','😢','😭','😤','😠','😡','🤯','😳','😱','😨','😰','😥','😓','🤗','🤔','🤭','🤫','🤥','😶','😐','😑','😬','🙄','😯','😦','😧','😮','😲','🥱','😴','🤤','😪','😵','🤐','🥴','🤢','🤮','🤧','😷','🤒','🤕','🤠','👿','👍','👎','✌️','🤞','🤟','🤘','🤙','👈','👉','👆','👇','☝️','👋','🤚','🖐️','✋','🖖','👏','🙌','👐','🤲','🤝','🙏','✍️','💪','❤️','🧡','💛','💚','💙','💜','🖤','🤍','💔','❣️','💕','💞','💓','💗','💖','💘','💝','💯','💥','💫','💦','🔥','⭐','🌟','✨','⚡','🎉','🎊','🎁','🏆','🥇','🎯','🚀','🌈','☀️','🌙','✅','❌','⚠️','📎','📄','📁','💡','🔔','👀','🐱','🍕','🍔','🎵'];

function tgIsMobile() { return window.matchMedia('(max-width: 768px)').matches; }

function tgGoBackToList() {
    document.getElementById('tgContainer').classList.remove('show-chat');
    if (tgPolling) clearInterval(tgPolling);
    if (tgTypingPollInterval) clearInterval(tgTypingPollInterval);
}

document.addEventListener('DOMContentLoaded', () => {
    tgApplyTranslations();
    tgLoadConversations();
    tgStartHeartbeat();
    document.getElementById('tgSearch').addEventListener('input', () => tgRenderConversations(tgAllConversations));

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && tgConvId) { tgMarkAsRead(tgConvId); tgClearBadgeLocal(tgConvId); }
    });

    const userSearch = document.getElementById('tgUserSearch');
    userSearch.addEventListener('input', () => { clearTimeout(tgSearchTimeout); tgSearchTimeout = setTimeout(() => tgSearchUsers(userSearch.value, 'group'), 250); });
    userSearch.addEventListener('focus', () => { if (!userSearch.value.trim()) tgSearchUsers('', 'group'); });

    const newChatSearch = document.getElementById('tgNewChatSearch');
    newChatSearch.addEventListener('input', () => { clearTimeout(tgNewChatSearchTimeout); tgNewChatSearchTimeout = setTimeout(() => tgSearchUsers(newChatSearch.value, 'private'), 250); });
    newChatSearch.addEventListener('focus', () => { if (!newChatSearch.value.trim()) tgSearchUsers('', 'private'); });

    const addMemberSearch = document.getElementById('tgAddMemberSearch');
    addMemberSearch.addEventListener('input', () => { clearTimeout(tgAddMemberSearchTimeout); tgAddMemberSearchTimeout = setTimeout(() => tgAddMemberSearchFn(addMemberSearch.value), 250); });
    addMemberSearch.addEventListener('focus', () => { if (!addMemberSearch.value.trim()) tgAddMemberSearchFn(''); });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.tg-context-menu')) tgHideContextMenu();
        if (!e.target.closest('#tgChatContextMenu')) tgHideChatContext();
        if (!e.target.closest('.tg-emoji-panel') && !e.target.closest('[onclick*="tgToggleEmojiPanel"]')) {
            document.getElementById('tgEmojiPanel').classList.remove('active');
        }
    });

    document.getElementById('tgMessages').addEventListener('scroll', tgUpdateScrollDownButton);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (tgRecording) { e.preventDefault(); tgCancelRecording(); }
            else { tgCloseInfo(); tgCloseMedia(); tgCloseAddMemberModal(); tgHideContextMenu(); tgHideChatContext(); }
        }
    });

    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', () => {
            document.documentElement.style.setProperty('--vh', `${window.visualViewport.height * 0.01}px`);
        });
    }
});

function tgOnLangChange(lang) {
    if (!lang || !TG_TRANSLATIONS[lang]) return;
    TG_LANG = lang;
    tgApplyTranslations();
    tgRenderConversations(tgAllConversations);
    if (tgCurrentConvData) {
        tgSetDefaultStatus();
        if (tgCurrentConvData.is_blocked && tgCurrentConvData.type === 'private') {
            document.getElementById('tgBlockedBannerText').textContent = tgT('blocked_banner_user') + '"' + tgCurrentConvData.name + '"';
        }
        const profileLink = document.getElementById('tgHeaderProfileLink');
        if (tgCurrentConvData.type === 'private' && tgCurrentConvData.other_user_id) {
            profileLink.title = tgT('open_profile') + tgCurrentConvData.name;
        }
        tgUpdateBlockBtnUI();
    }
    if (!tgRecording) document.getElementById('tgInput').placeholder = tgT('message_ph');
    if (tgConvId) {
        tgLastMsgId = 0; tgLastRenderedDate = null;
        document.getElementById('tgMessages').innerHTML = '';
        tgLoadMessages();
    }
    if (document.getElementById('tgInfoDrawer').classList.contains('open') && tgInfoData) {
        tgOpenInfo(tgInfoData.id);
    }
}
window.addEventListener('docsign:lang-changed', (e) => { tgOnLangChange(e.detail?.lang || 'ru'); });
window.addEventListener('storage', (e) => { if (e.key === 'docsign_lang' && e.newValue) tgOnLangChange(e.newValue); });

function tgStartHeartbeat() {
    if (tgHeartbeatInterval) return;
    tgHeartbeatInterval = setInterval(async () => { try { await fetch('/heartbeat'); } catch(e) {} }, 30000);
}

function tgUpdateScrollDownButton() {
    const el = document.getElementById('tgMessages');
    const btn = document.getElementById('tgScrollDownBtn');
    if (!el || !btn) return;
    btn.classList.toggle('visible', (el.scrollHeight - el.scrollTop - el.clientHeight) > 200);
}

async function tgLoadConversations() {
    const list = document.getElementById('tgList');
    try {
        const res = await fetch('/chat/api/conversations');
        if (!res.ok) { list.innerHTML = `<div class="tg-error-banner"><i class="bi bi-exclamation-triangle"></i> ${tgT('server_error')} (${res.status})</div>`; return; }
        tgAllConversations = await res.json();
        tgRenderConversations(tgAllConversations);
    } catch (e) {
        list.innerHTML = `<div class="tg-error-banner"><i class="bi bi-exclamation-triangle"></i> ${tgT('load_chats_failed')}</div>`;
    }
}

function tgRenderConversations(conversations) {
    const list = document.getElementById('tgList');
    const searchVal = document.getElementById('tgSearch').value.toLowerCase().trim();
    const filtered = conversations.filter(c => {
        if (!searchVal) return true;
        return (c.name || '').toLowerCase().includes(searchVal) || (c.last_message?.text || '').toLowerCase().includes(searchVal);
    });
    list.innerHTML = '';
    if (filtered.length === 0) {
        list.innerHTML = `<div class="tg-no-results"><i class="bi bi-chat-square-dots"></i><div class="tg-no-results-title">${searchVal ? tgT('no_results_title') : tgT('no_chats_title')}</div><div class="tg-no-results-sub">${searchVal ? tgT('no_results_sub') : tgT('no_chats_sub')}</div></div>`;
        return;
    }
    filtered.forEach(c => {
        const div = document.createElement('div');
        div.className = 'tg-item';
        if (c.id === tgConvId) div.classList.add('active');
        div.onclick = () => tgOpenChat(c);
        div.addEventListener('contextmenu', (e) => tgShowChatContext(e, c));
        div.addEventListener('touchstart', (e) => { tgTouchTimer = setTimeout(() => tgShowChatContext(e.touches[0], c), 500); }, { passive: true });
        ['touchend','touchmove','touchcancel'].forEach(ev => div.addEventListener(ev, () => clearTimeout(tgTouchTimer), { passive: true }));
        const fallbackAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(c.name)}&size=52&background=4f8cff&color=fff&bold=true`;
        div.innerHTML = `
            <div class="tg-avatar-wrap">
                <img src="${c.avatar}" class="tg-item-avatar" onerror="this.onerror=null;this.src='${fallbackAvatar}'">
                ${c.is_online ? '<div class="tg-online-dot"></div>' : ''}
            </div>
            <div class="tg-item-info">
                <div class="tg-item-name">
                    ${c.type === 'group' ? `<i class="bi bi-people-fill" style="font-size: 11px; color: rgb(var(--glow));" title="${tgT('group_label')}"></i>` : ''}
                    ${tgEscapeHtml(c.name)}
                    ${c.is_blocked ? `<i class="bi bi-slash-circle-fill tg-item-blocked-icon" title="${tgT('blocked_label')}"></i>` : ''}
                </div>
                <div class="tg-item-last">${c.last_message ? tgEscapeHtml(c.last_message.text) : `<span style="opacity:0.5">${c.type === 'group' ? (c.member_count || 0) + ' ' + tgPlural('members', c.member_count || 0) : tgT('no_messages')}</span>`}</div>
            </div>
            <div class="tg-item-meta">
                <div class="tg-item-time">${c.last_message ? c.last_message.time : ''}</div>
                ${c.unread_count > 0 ? `<div class="tg-unread-badge">${c.unread_count > 99 ? '99+' : c.unread_count}</div>` : ''}
            </div>`;
        list.appendChild(div);
    });
}

function tgSetDefaultStatus() {
    const statusEl = document.getElementById('tgHeaderStatus');
    if (!statusEl || !tgCurrentConvData) return;
    if (tgCurrentConvData.type === 'group') {
        const n = tgCurrentConvData.member_count || 0;
        statusEl.innerHTML = `<span style="color:var(--muted)">${n} ${tgPlural('members', n)}</span>`;
        return;
    }
    statusEl.innerHTML = tgCurrentConvData.is_online
        ? `<span style="color:#10b981">${tgT('online')}</span>`
        : `<span style="color:var(--muted)">${tgT('last_seen')}</span>`;
}

function tgUpdateBlockBtnUI() {
    const blockBtn = document.getElementById('tgBlockBtn');
    const blockIcon = document.getElementById('tgBlockIcon');
    const infoBtn = document.getElementById('tgInfoBtn');
    if (!tgCurrentConvData) return;
    if (infoBtn) infoBtn.style.display = tgCurrentConvData.type === 'group' ? 'flex' : 'none';
    if (tgCurrentConvData.type === 'private' && tgCurrentOtherUserId) {
        blockBtn.style.display = 'flex';
        if (tgCurrentConvData.is_blocked) {
            blockBtn.classList.add('active'); blockIcon.className = 'bi bi-slash-circle-fill'; blockBtn.title = tgT('unblock_btn');
        } else {
            blockBtn.classList.remove('active'); blockIcon.className = 'bi bi-slash-circle'; blockBtn.title = tgT('block_btn');
        }
    } else {
        blockBtn.style.display = 'none';
    }
}

async function tgOpenChat(conv) {
    if (!conv || !conv.id) return;
    tgConvId = conv.id; tgLastMsgId = 0; tgLastRenderedDate = null;
    tgCurrentConvData = conv; tgCurrentOtherUserId = conv.other_user_id || null;

    if (tgIsMobile()) document.getElementById('tgContainer').classList.add('show-chat');

    document.getElementById('tgEmpty').style.display = 'none';
    document.getElementById('tgHeader').style.display = 'flex';
    document.getElementById('tgMessages').style.display = 'flex';
    document.getElementById('tgFooterWrap').style.display = 'block';
    document.getElementById('tgHeaderName').textContent = conv.name;

    const avatarEl = document.getElementById('tgHeaderAvatar');
    avatarEl.src = conv.avatar;
    avatarEl.onerror = function() { this.onerror = null; this.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(conv.name)}&size=44&background=4f8cff&color=fff&bold=true`; };

    const profileLink = document.getElementById('tgHeaderProfileLink');
    if (conv.type === 'private' && conv.other_user_id) {
        profileLink.href = `/users/${conv.other_user_id}`;
        profileLink.classList.remove('tg-no-link');
        profileLink.title = tgT('open_profile') + conv.name;
        profileLink.onclick = null;
    } else {
        profileLink.href = '#';
        profileLink.classList.remove('tg-no-link');
        profileLink.title = tgT('ctx_chat_info');
        profileLink.onclick = (e) => { e.preventDefault(); tgOpenInfo(conv.id); };
    }

    document.getElementById('tgHeaderOnlineDot').style.display = conv.is_online ? 'block' : 'none';
    tgSetDefaultStatus();
    tgUpdateBlockBtnUI();

    const banner = document.getElementById('tgBlockedBanner');
    const footer = document.getElementById('tgFooter');
    if (conv.is_blocked && conv.type === 'private') {
        banner.style.display = 'flex';
        document.getElementById('tgBlockedBannerText').textContent = tgT('blocked_banner_user') + `"${conv.name}"`;
        footer.style.display = 'none';
    } else {
        banner.style.display = 'none';
        footer.style.display = 'flex';
    }

    document.getElementById('tgMessages').innerHTML = '';
    await tgLoadMessages();
    await tgMarkAsRead(conv.id);
    tgClearBadgeLocal(conv.id);
    tgScrollDownInstant();

    if (tgPolling) clearInterval(tgPolling);
    tgPolling = setInterval(tgLoadMessages, 3000);
    tgStartTypingPoll();
    await tgLoadConversations();
    tgUpdateScrollDownButton();
}

function tgSendTyping() {
    if (!tgConvId || tgTypingTimeout) return;
    fetch('/chat/api/typing', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TG_CSRF }, body: JSON.stringify({ conversation_id: tgConvId }) }).catch(e => {});
    tgTypingTimeout = setTimeout(() => { tgTypingTimeout = null; }, 3000);
}
function tgStartTypingPoll() {
    if (tgTypingPollInterval) clearInterval(tgTypingPollInterval);
    tgTypingPollInterval = setInterval(async () => {
        if (!tgConvId) return;
        try { const r = await fetch(`/chat/api/typing/${tgConvId}`); const d = await r.json(); tgShowTyping(d.typers || []); } catch(e) {}
    }, 2000);
}
function tgShowTyping(typers) {
    const statusEl = document.getElementById('tgHeaderStatus');
    if (!statusEl || !tgCurrentConvData) return;
    if (typers.length === 0) { tgSetDefaultStatus(); return; }
    const names = typers.slice(0, 3).join(', ');
    statusEl.innerHTML = `<span class="tg-typing">${tgEscapeHtml(names)}${typers.length === 1 ? tgT('typing_one') : tgT('typing_many')}<span class="tg-typing-dots"><span></span><span></span><span></span></span></span>`;
}
async function tgMarkAsRead(id) { try { await fetch(`/chat/api/mark-read/${id}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': TG_CSRF } }); } catch(e) {} }

async function tgLoadMessages() {
    if (!tgConvId) return;
    try {
        const res = await fetch(`/chat/api/messages/${tgConvId}`);
        if (!res.ok) return;
        tgRenderMessages(await res.json());
    } catch (e) { console.error(e); }
}

function tgRenderMessages(messages) {
    const container = document.getElementById('tgMessages');
    const wasNearBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 120;
    let hasNewIncoming = false;
    messages.forEach(m => {
        if (m.id > tgLastMsgId) {
            tgRenderDateDividerIfNeeded(m);
            tgRenderMessage(m);
            tgLastMsgId = m.id;
            if (String(m.sender_id) !== String(TG_USER_ID)) hasNewIncoming = true;
        }
    });
    if (wasNearBottom) tgScrollDownInstant();
    tgUpdateScrollDownButton();
    if (hasNewIncoming && tgConvId && !document.hidden) { tgMarkAsRead(tgConvId); tgClearBadgeLocal(tgConvId); }
}

function tgClearBadgeLocal(convId) {
    const conv = tgAllConversations.find(c => c.id === convId);
    if (conv && conv.unread_count > 0) { conv.unread_count = 0; tgRenderConversations(tgAllConversations); }
}

function tgDateLabel(date) {
    const today = new Date(); const yesterday = new Date(); yesterday.setDate(yesterday.getDate() - 1);
    const ds = date.toDateString();
    if (ds === today.toDateString()) return tgT('today');
    if (ds === yesterday.toDateString()) return tgT('yesterday');
    return date.toLocaleDateString(TG_LOCALES[TG_LANG] || 'ru-RU', { day: 'numeric', month: 'long', year: 'numeric' });
}
function tgRenderDateDividerIfNeeded(m) {
    const ds = new Date(m.created_at).toDateString();
    if (tgLastRenderedDate === ds) return;
    const div = document.createElement('div');
    div.className = 'tg-date-divider';
    div.innerHTML = `<span>${tgDateLabel(new Date(m.created_at))}</span>`;
    document.getElementById('tgMessages').appendChild(div);
    tgLastRenderedDate = ds;
}

function tgLinkify(escaped) {
    return escaped.replace(/(https?:\/\/[^\s<]+)/g, u => `<a href="${u}" target="_blank" rel="noopener noreferrer">${u}</a>`);
}

function tgRenderMessage(m) {
    const container = document.getElementById('tgMessages');
    const isMine = String(m.sender_id) === String(TG_USER_ID);
    const div = document.createElement('div');
    div.className = `tg-message ${isMine ? 'mine' : 'theirs'}`;
    div.setAttribute('data-msg-id', m.id);

    let content = '';
    if (m.reply_to_message) {
        content += `<div class="tg-reply-in-msg" onclick="tgScrollToMessage(${m.reply_to})">
            <div class="tg-reply-in-msg-author">${tgEscapeHtml(m.reply_to_message.sender?.name || tgT('deleted_msg'))}</div>
            <div class="tg-reply-in-msg-text">${tgEscapeHtml(m.reply_to_message.body || tgT('media'))}</div>
        </div>`;
    }
    if (m.type === 'text') content += `<div class="tg-text">${tgLinkify(tgEscapeHtml(m.body))}</div>`;
    else if (m.type === 'image' && m.attachment_url) {
        content += `<img src="${m.attachment_url}" class="tg-image" onclick="window.open(this.src)" loading="lazy">`;
        if (m.body) content += `<div class="tg-text" style="margin-top:6px;">${tgEscapeHtml(m.body)}</div>`;
    } else if (m.type === 'file' && m.attachment_url) {
        const fn = decodeURIComponent((m.attachment_url || '').split('/').pop() || 'File');
        content += `<a href="${m.attachment_url}" target="_blank" class="tg-file" download><div class="tg-file-icon"><i class="bi bi-file-earmark"></i></div><div class="tg-file-info"><div class="tg-file-name">${tgEscapeHtml(fn)}</div><div class="tg-file-size">${tgT('download')}</div></div><i class="bi bi-download"></i></a>`;
    } else if (m.type === 'voice' && m.attachment_url) {
        content += `<div class="tg-voice-msg"><div class="tg-voice-icon"><i class="bi bi-mic-fill"></i></div><audio controls src="${m.attachment_url}" class="tg-voice-audio" preload="metadata"></audio></div>`;
    }

    const time = new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    const checks = isMine ? `<span class="tg-checks">${m.is_read ? '<i class="bi bi-check-all"></i>' : '<i class="bi bi-check"></i>'}</span>` : '';
    const senderHtml = (!isMine && m.sender) ? `<a href="/users/${m.sender.id}" class="tg-sender-link" title="${tgT('open_profile')}${tgEscapeHtml(m.sender.name)}"><div class="tg-sender">${tgEscapeHtml(m.sender.name)}</div></a>` : '';

    div.innerHTML = `<div class="tg-bubble">${senderHtml}${content}<div class="tg-time">${time}${checks}</div></div>`;
    div.addEventListener('contextmenu', (e) => tgShowContextMenu(e, m.id, isMine, m));
    container.appendChild(div);
    if (m.type === 'voice') { const a = div.querySelector('audio'); if (a) tgFixAudioDuration(a); }
}

function tgScrollToMessage(id) { const el = document.querySelector(`[data-msg-id="${id}"]`); if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
function tgFixAudioDuration(audio) {
    const fix = () => { if (!isFinite(audio.duration) || audio.duration === 0 || isNaN(audio.duration)) { audio.currentTime = 1e101; audio.ontimeupdate = () => { audio.ontimeupdate = null; audio.currentTime = 0; }; } };
    audio.addEventListener('loadedmetadata', fix); setTimeout(fix, 300);
}
function tgEscapeHtml(str) { if (!str) return ''; const d = document.createElement('div'); d.textContent = str; return d.innerHTML; }

function tgHandleApiError(res, errData, def) {
    const msg = errData?.error || errData?.message || def;
    if (res.status === 403) { tgShowToast('🔒 ' + msg, 'error'); if (/блок|block/i.test(msg)) tgLoadConversations(); return true; }
    return false;
}

async function tgSendText(text) {
    if (!text || !tgConvId) return;
    const fd = new FormData();
    fd.append('conversation_id', tgConvId); fd.append('body', text); fd.append('type', 'text');
    if (tgReplyTo) fd.append('reply_to', tgReplyTo.id);
    document.getElementById('tgInput').value = '';
    tgAutoResize(document.getElementById('tgInput')); tgUpdateBtn(); tgUpdateCharCounter(); tgCancelReply();
    try {
        const res = await fetch('/chat/api/send', { method: 'POST', headers: { 'X-CSRF-TOKEN': TG_CSRF }, body: fd });
        if (!res.ok) { const err = await res.json().catch(() => ({})); if (tgHandleApiError(res, err, `HTTP ${res.status}`)) return; throw new Error(err.error || err.message || `HTTP ${res.status}`); }
        tgLoadMessages(); tgLoadConversations();
    } catch (e) { tgShowToast(tgT('send_failed_with') + e.message, 'error'); }
}

async function tgUploadFile(input) {
    if (!input.files[0] || !tgConvId) { input.value = ''; return; }
    const file = input.files[0];
    if (file.size > 50 * 1024 * 1024) { tgShowToast(tgT('file_too_big'), 'error'); input.value = ''; return; }
    const fd = new FormData();
    fd.append('conversation_id', tgConvId); fd.append('attachment', file);
    fd.append('type', file.type.startsWith('image/') ? 'image' : 'file'); fd.append('body', '');
    tgShowToast(tgT('sending_file'), 'info');
    try {
        const res = await fetch('/chat/api/send', { method: 'POST', headers: { 'X-CSRF-TOKEN': TG_CSRF }, body: fd });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) { if (tgHandleApiError(res, data, `${tgT('error_generic')} ${res.status}`)) { input.value = ''; return; } throw new Error(data.error || data.message || `${tgT('error_generic')} ${res.status}`); }
        input.value = ''; tgLoadMessages(); tgLoadConversations(); tgShowToast(tgT('file_sent'), 'success');
    } catch (e) { tgShowToast('❌ ' + e.message, 'error'); input.value = ''; }
}

async function tgHandleAction() {
    const input = document.getElementById('tgInput');
    if (input.value.trim()) tgSendText(input.value.trim()); else tgToggleVoice();
}

async function tgToggleVoice() {
    if (!tgRecording) {
        if (!navigator.mediaDevices?.getUserMedia || typeof MediaRecorder === 'undefined') { tgShowToast(tgT('no_rec_support'), 'error'); return; }
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: { echoCancellation: true, noiseSuppression: true } });
            tgCurrentMimeType = '';
            for (const t of ['audio/webm;codecs=opus','audio/webm','audio/mp4','audio/ogg;codecs=opus']) if (MediaRecorder.isTypeSupported?.(t)) { tgCurrentMimeType = t; break; }
            tgMediaRecorder = tgCurrentMimeType ? new MediaRecorder(stream, { mimeType: tgCurrentMimeType }) : new MediaRecorder(stream);
            tgAudioChunks = []; tgRecordStartTime = Date.now(); tgDiscardRecording = false;
            tgMediaRecorder.ondataavailable = e => { if (e.data?.size > 0) tgAudioChunks.push(e.data); };
            tgMediaRecorder.onstop = async () => {
                stream.getTracks().forEach(t => t.stop());
                if (tgDiscardRecording) { tgDiscardRecording = false; tgAudioChunks = []; tgResetVoiceUI(); return; }
                if (tgAudioChunks.length === 0) { tgResetVoiceUI(); return; }
                const blob = new Blob(tgAudioChunks, { type: tgCurrentMimeType || 'audio/webm' });
                if (blob.size < 5000) { tgResetVoiceUI(); tgShowToast(tgT('too_short'), 'error'); return; }
                let ext = 'webm';
                if (tgCurrentMimeType.includes('mp4')) ext = 'm4a'; else if (tgCurrentMimeType.includes('ogg')) ext = 'ogg';
                const fd = new FormData();
                fd.append('conversation_id', tgConvId);
                fd.append('attachment', new File([blob], `voice_${Date.now()}.${ext}`, { type: tgCurrentMimeType || 'audio/webm' }));
                fd.append('type', 'voice'); fd.append('body', '');
                try {
                    const res = await fetch('/chat/api/send', { method: 'POST', headers: { 'X-CSRF-TOKEN': TG_CSRF }, body: fd });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) { if (tgHandleApiError(res, data, tgT('error_generic'))) { tgResetVoiceUI(); return; } throw new Error(tgT('error_generic')); }
                    tgLoadMessages(); tgLoadConversations(); tgShowToast(tgT('voice_sent'), 'success');
                } catch (err) { tgShowToast('❌ ' + tgT('send_failed'), 'error'); }
                tgResetVoiceUI();
            };
            tgMediaRecorder.start(); tgRecording = true;
            document.getElementById('tgFooter').classList.add('tg-recording-state');
            document.getElementById('tgActionBtn').classList.add('recording');
            document.getElementById('tgMicIcon').style.display = 'none';
            document.getElementById('tgSendIcon').style.display = 'block';
            document.getElementById('tgCancelRecBtn').style.display = 'flex';
            document.getElementById('tgInput').placeholder = tgT('recording_ph');
            document.getElementById('tgInput').disabled = true;
            document.getElementById('tgRecordingIndicator').classList.add('active');
            tgRecordTimerInterval = setInterval(() => {
                const s = Math.floor((Date.now() - tgRecordStartTime) / 1000);
                document.getElementById('tgRecordTimer').textContent = `${Math.floor(s/60)}:${String(s%60).padStart(2,'0')}`;
            }, 500);
        } catch (e) { tgShowToast(tgT('mic_error'), 'error'); tgResetVoiceUI(); }
    } else {
        if (tgMediaRecorder?.state === 'recording') {
            try { tgMediaRecorder.requestData(); } catch(e) {}
            await new Promise(r => setTimeout(r, 200));
            tgMediaRecorder.stop();
        }
    }
}

function tgCancelRecording() {
    if (!tgRecording) return;
    tgDiscardRecording = true;
    if (tgMediaRecorder?.state === 'recording') { try { tgMediaRecorder.stop(); } catch(e) {} }
    else {
        tgDiscardRecording = false;
        tgMediaRecorder?.stream?.getTracks().forEach(t => t.stop());
        tgResetVoiceUI();
    }
    tgShowToast(tgT('recording_canceled'), 'info');
}

function tgResetVoiceUI() {
    tgRecording = false; tgDiscardRecording = false;
    if (tgRecordTimerInterval) { clearInterval(tgRecordTimerInterval); tgRecordTimerInterval = null; }
    document.getElementById('tgRecordingIndicator').classList.remove('active');
    document.getElementById('tgFooter').classList.remove('tg-recording-state');
    document.getElementById('tgActionBtn').classList.remove('recording');
    document.getElementById('tgCancelRecBtn').style.display = 'none';
    document.getElementById('tgMicIcon').style.display = 'block';
    document.getElementById('tgSendIcon').style.display = 'none';
    const i = document.getElementById('tgInput');
    i.placeholder = tgT('message_ph'); i.disabled = false;
    tgUpdateBtn();
}

function tgHandleKey(e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); tgHandleAction(); } }
function tgAutoResize(el) { el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 120) + 'px'; tgUpdateBtn(); }
function tgUpdateBtn() { if (tgRecording) return; const has = document.getElementById('tgInput').value.trim().length > 0; document.getElementById('tgMicIcon').style.display = has ? 'none' : 'block'; document.getElementById('tgSendIcon').style.display = has ? 'block' : 'none'; }
function tgUpdateCharCounter() {
    const input = document.getElementById('tgInput'), counter = document.getElementById('tgCharCounter');
    if (!input || !counter) return;
    const len = input.value.length;
    counter.textContent = len;
    counter.classList.remove('warning', 'danger');
    if (len > 4000) counter.classList.add('danger'); else if (len > 3000) counter.classList.add('warning');
}
function tgScrollDownInstant() { document.getElementById('tgMessages').scrollTop = document.getElementById('tgMessages').scrollHeight; }
function tgScrollDown() {
    const el = document.getElementById('tgMessages');
    el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
    setTimeout(() => document.getElementById('tgScrollDownBtn')?.classList.remove('visible'), 400);
}

function tgShowContextMenu(e, msgId, isMine, msg) {
    e.preventDefault(); e.stopPropagation();
    tgContextMessageId = msgId; tgContextMessageIsMine = isMine; tgContextMessageData = msg;
    const menu = document.getElementById('tgContextMenu');
    document.getElementById('tgCtxDelete').style.display = isMine ? 'flex' : 'none';
    menu.style.left = (e.clientX ?? 0) + 'px'; menu.style.top = (e.clientY ?? 0) + 'px';
    menu.classList.add('active');
    setTimeout(() => { const r = menu.getBoundingClientRect(); if (r.right > innerWidth) menu.style.left = (innerWidth - r.width - 10) + 'px'; if (r.bottom > innerHeight) menu.style.top = (innerHeight - r.height - 10) + 'px'; if (r.left < 0) menu.style.left = '10px'; if (r.top < 0) menu.style.top = '10px'; }, 0);
}
function tgHideContextMenu() { document.getElementById('tgContextMenu').classList.remove('active'); tgContextMessageId = null; }
function tgReplyToMessage() {
    if (!tgContextMessageData) return;
    const m = tgContextMessageData;
    tgReplyTo = { id: m.id, sender_name: m.sender?.name || tgT('you'), text: m.body || tgT('media') };
    document.getElementById('tgReplyPreview').classList.add('active');
    document.getElementById('tgReplyAuthor').textContent = tgReplyTo.sender_name;
    document.getElementById('tgReplyText').textContent = tgReplyTo.text;
    document.getElementById('tgInput').focus();
    tgHideContextMenu();
}
function tgCancelReply() { tgReplyTo = null; document.getElementById('tgReplyPreview').classList.remove('active'); }
function tgCopyMessage() {
    if (!tgContextMessageData?.body) return;
    navigator.clipboard.writeText(tgContextMessageData.body).then(() => tgShowToast(tgT('copied'), 'success'));
    tgHideContextMenu();
}
async function tgDeleteMessage() {
    if (!tgContextMessageId || !confirm(tgT('confirm_delete'))) { tgHideContextMenu(); return; }
    try {
        const res = await fetch(`/chat/api/message/${tgContextMessageId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': TG_CSRF } });
        if (res.ok) {
            const el = document.querySelector(`[data-msg-id="${tgContextMessageId}"]`);
            if (el) { el.style.transition = 'all 0.3s'; el.style.opacity = '0'; el.style.transform = 'translateX(20px)'; setTimeout(() => el.remove(), 300); }
            tgShowToast(tgT('deleted'), 'success'); tgLoadConversations();
        }
    } catch (e) { tgShowToast(tgT('delete_error'), 'error'); }
    tgHideContextMenu();
}

function tgToggleEmojiPanel() {
    const p = document.getElementById('tgEmojiPanel');
    p.classList.toggle('active');
    if (p.classList.contains('active') && !p.dataset.loaded) { tgLoadEmojis(); p.dataset.loaded = 'true'; }
}
function tgLoadEmojis() {
    const g = document.getElementById('tgEmojiGrid');
    g.innerHTML = '';
    EMOJIS.forEach(emoji => {
        const btn = document.createElement('button');
        btn.className = 'tg-emoji-btn'; btn.textContent = emoji; btn.type = 'button';
        btn.onclick = () => { const i = document.getElementById('tgInput'); i.value += emoji; i.focus(); tgUpdateBtn(); tgUpdateCharCounter(); };
        g.appendChild(btn);
    });
}

async function tgSearchUsers(query, mode = 'group') {
    try {
        const res = await fetch(`/chat/api/users?q=${encodeURIComponent(query)}`);
        if (!res.ok) return;
        const users = await res.json();
        if (mode === 'group') tgRenderUserResults(users, query); else tgRenderNewChatResults(users, query);
    } catch (e) { console.error(e); }
}
function tgRenderUserResults(users, query) {
    const c = document.getElementById('tgUserResults'); c.innerHTML = '';
    if (!users?.length) { c.innerHTML = `<div class="tg-no-results"><i class="bi bi-person-x"></i><div class="tg-no-results-title">${tgT('users_not_found_title')}</div><div class="tg-no-results-sub">${tgT('users_not_found_sub')}</div></div>`; return; }
    users.forEach(u => {
        if (!u?.id) return;
        const d = document.createElement('div');
        d.className = 'tg-user-result';
        if (tgSelectedUsers.has(u.id)) d.classList.add('selected');
        const fb = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&size=40&background=4f8cff&color=fff&bold=true`;
        d.innerHTML = `<img src="${u.avatar || fb}" onerror="this.onerror=null;this.src='${fb}'"><div class="tg-user-result-info"><div class="tg-user-result-name">${tgEscapeHtml(u.name)}</div><div class="tg-user-result-meta">${tgEscapeHtml([u.email, u.phone].filter(Boolean).join(' • '))}</div></div><i class="bi bi-${tgSelectedUsers.has(u.id) ? 'check-circle-fill' : 'plus-circle'} tg-user-result-check"></i>`;
        d.addEventListener('click', (e) => { e.stopPropagation(); tgToggleSelectUser(u); });
        c.appendChild(d);
    });
}
function tgRenderNewChatResults(users) {
    const c = document.getElementById('tgNewChatResults'); c.innerHTML = '';
    if (!users?.length) { c.innerHTML = `<div class="tg-no-results"><i class="bi bi-person-x"></i><div class="tg-no-results-title">${tgT('users_not_found_title')}</div><div class="tg-no-results-sub">${tgT('users_not_found_sub')}</div></div>`; return; }
    users.forEach(u => {
        if (!u?.id) return;
        const d = document.createElement('div');
        d.className = 'tg-user-result';
        const fb = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&size=40&background=4f8cff&color=fff&bold=true`;
        d.innerHTML = `<img src="${u.avatar || fb}" onerror="this.onerror=null;this.src='${fb}'"><div class="tg-user-result-info"><div class="tg-user-result-name">${tgEscapeHtml(u.name)}</div><div class="tg-user-result-meta">${tgEscapeHtml([u.email, u.phone].filter(Boolean).join(' • '))}</div></div><i class="bi bi-chat-dots tg-user-result-check" style="color: rgb(var(--glow));"></i>`;
        d.addEventListener('click', (e) => { e.stopPropagation(); tgStartPrivateChat(u); });
        c.appendChild(d);
    });
}
function tgToggleSelectUser(u) {
    if (!u?.id) return;
    if (tgSelectedUsers.has(u.id)) tgSelectedUsers.delete(u.id); else tgSelectedUsers.set(u.id, u);
    tgRenderSelectedUsers();
    tgSearchUsers(document.getElementById('tgUserSearch').value, 'group');
}
function tgRenderSelectedUsers() {
    const c = document.getElementById('tgSelectedUsers'); c.innerHTML = '';
    tgSelectedUsers.forEach(u => {
        const t = document.createElement('div');
        t.className = 'tg-user-tag';
        const fb = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&size=20`;
        t.innerHTML = `<img src="${u.avatar || fb}" onerror="this.onerror=null;this.src='${fb}'" style="width:18px;height:18px;border-radius:50%;">${tgEscapeHtml(u.name)}<span class="tg-user-tag-remove" onclick="tgRemoveUser(${u.id}); event.stopPropagation();"><i class="bi bi-x"></i></span>`;
        c.appendChild(t);
    });
}
function tgRemoveUser(id) { tgSelectedUsers.delete(id); tgRenderSelectedUsers(); tgSearchUsers(document.getElementById('tgUserSearch').value, 'group'); }

function tgOpenNewChatModal() { document.getElementById('tgNewChatModal').classList.add('active'); document.getElementById('tgNewChatSearch').value = ''; tgSearchUsers('', 'private'); setTimeout(() => document.getElementById('tgNewChatSearch').focus(), 100); }
function tgCloseNewChatModal() { document.getElementById('tgNewChatModal').classList.remove('active'); }
async function tgStartPrivateChat(user) {
    try {
        tgShowToast(tgT('opening_chat') + user.name + '...', 'info');
        const res = await fetch('/chat/api/private', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TG_CSRF }, body: JSON.stringify({ user_id: user.id }) });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.error || tgT('error_generic'));
        tgCloseNewChatModal();
        await tgLoadConversations();
        const conv = tgAllConversations.find(c => c.id === data.id);
        if (conv) tgOpenChat(conv);
    } catch (e) { tgShowToast('❌ ' + e.message, 'error'); }
}
function tgOpenGroupModal() { document.getElementById('tgGroupModal').classList.add('active'); document.getElementById('tgGroupName').value = ''; document.getElementById('tgUserSearch').value = ''; tgSelectedUsers.clear(); tgRenderSelectedUsers(); tgSearchUsers('', 'group'); setTimeout(() => document.getElementById('tgGroupName').focus(), 100); }
function tgCloseGroupModal() { document.getElementById('tgGroupModal').classList.remove('active'); }
async function tgCreateGroup() {
    const name = document.getElementById('tgGroupName').value.trim();
    const ids = Array.from(tgSelectedUsers.keys());
    if (!name) { tgShowToast(tgT('enter_name'), 'error'); return; }
    if (ids.length === 0) { tgShowToast(tgT('select_members'), 'error'); return; }
    try {
        const res = await fetch('/chat/api/group', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TG_CSRF }, body: JSON.stringify({ name, user_ids: ids }) });
        if (!res.ok) throw new Error(tgT('error_generic'));
        tgCloseGroupModal(); tgLoadConversations(); tgShowToast(tgT('group_created'), 'success');
    } catch (e) { tgShowToast('❌ ' + e.message, 'error'); }
}

function tgToggleBlockModal() {
    if (!tgCurrentOtherUserId) return;
    const conv = tgCurrentConvData, isBlocked = conv?.is_blocked || false, userName = conv?.name || '';
    const modal = document.getElementById('tgBlockModal');
    if (isBlocked) {
        modal.classList.add('unblock');
        document.getElementById('tgBlockModalTitle').textContent = tgT('unblock_title');
        document.getElementById('tgBlockModalText').textContent = tgT('unblock_desc_pre') + userName + tgT('unblock_desc_mid');
        document.getElementById('tgBlockConfirmBtn').textContent = tgT('unblock_btn_confirm');
        document.getElementById('tgBlockModalIcon').className = 'bi bi-unlock';
    } else {
        modal.classList.remove('unblock');
        document.getElementById('tgBlockModalTitle').textContent = tgT('block_title');
        document.getElementById('tgBlockModalText').textContent = tgT('block_desc_pre') + userName + tgT('block_desc_mid');
        document.getElementById('tgBlockConfirmBtn').textContent = tgT('block_btn');
        document.getElementById('tgBlockModalIcon').className = 'bi bi-shield-x';
    }
    modal.classList.add('active');
}
function tgCloseBlockModal() { document.getElementById('tgBlockModal').classList.remove('active'); }
async function tgConfirmBlock() {
    if (!tgCurrentOtherUserId) return;
    const isBlocked = tgCurrentConvData?.is_blocked || false;
    tgCloseBlockModal();
    tgShowToast(isBlocked ? tgT('unblocking') : tgT('blocking'), 'info');
    try {
        const res = await fetch(`/chat/api/block/${tgCurrentOtherUserId}`, { method: isBlocked ? 'DELETE' : 'POST', headers: { 'X-CSRF-TOKEN': TG_CSRF, 'Content-Type': 'application/json' } });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.error || data.message || tgT('error_generic'));
        if (tgCurrentConvData) tgCurrentConvData.is_blocked = !isBlocked;
        tgShowToast(isBlocked ? tgT('user_unblocked') : tgT('user_blocked'), 'success');
        await tgLoadConversations();
        const upd = tgAllConversations.find(c => c.id === tgConvId);
        if (upd) await tgOpenChat(upd);
    } catch (e) { tgShowToast('❌ ' + e.message, 'error'); }
}
async function tgUnblockUser() { if (tgCurrentOtherUserId) await tgConfirmBlock(); }

/* ============================================================
   ✅ УПРАВЛЕНИЕ ГРУППОЙ: ДОБАВЛЕНИЕ/УДАЛЕНИЕ УЧАСТНИКОВ
   ============================================================ */
function tgShowChatContext(e, conv) {
    e.preventDefault(); e.stopPropagation();
    tgChatCtxConv = conv;
    const menu = document.getElementById('tgChatContextMenu');
    document.getElementById('tgChatCtxLeave').style.display = (conv.type === 'group' && !conv.is_company) ? 'flex' : 'none';
    menu.style.left = (e.clientX ?? e.pageX ?? 0) + 'px';
    menu.style.top = (e.clientY ?? e.pageY ?? 0) + 'px';
    menu.classList.add('active');
    setTimeout(() => { const r = menu.getBoundingClientRect(); if (r.right > innerWidth) menu.style.left = (innerWidth - r.width - 10) + 'px'; if (r.bottom > innerHeight) menu.style.top = (innerHeight - r.height - 10) + 'px'; if (r.left < 0) menu.style.left = '10px'; if (r.top < 0) menu.style.top = '10px'; }, 0);
}
function tgHideChatContext() { document.getElementById('tgChatContextMenu').classList.remove('active'); }
function tgChatCtxInfo() { if (tgChatCtxConv) tgOpenInfo(tgChatCtxConv.id); tgHideChatContext(); }
function tgChatCtxLeave() { if (tgChatCtxConv) { tgHideChatContext(); tgOpenInfo(tgChatCtxConv.id); setTimeout(tgLeaveGroup, 350); } }
function tgChatCtxDelete() { if (tgChatCtxConv) { const c = tgChatCtxConv; tgHideChatContext(); tgDeleteChatById(c.id); } }
async function tgChatCtxUnread() {
    if (!tgChatCtxConv) return;
    const id = tgChatCtxConv.id; tgHideChatContext();
    try { await fetch(`/chat/api/mark-unread/${id}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': TG_CSRF } }); await tgLoadConversations(); } catch (e) {}
}

async function tgOpenInfo(convId) {
    if (!convId) return;
    document.getElementById('tgInfoDrawer').classList.add('open');
    const body = document.getElementById('tgInfoBody');
    body.innerHTML = `<div class="tg-info-loader"><i class="bi bi-arrow-repeat tg-spin"></i></div>`;
    try {
        const res = await fetch(`/chat/api/info/${convId}`);
        if (!res.ok) throw new Error();
        tgRenderInfo(await res.json());
    } catch (e) {
        body.innerHTML = `<div class="tg-no-results"><i class="bi bi-exclamation-triangle"></i><div class="tg-no-results-title">${tgT('error_generic')}</div></div>`;
    }
}
function tgCloseInfo() { document.getElementById('tgInfoDrawer').classList.remove('open'); }

function tgRenderInfo(d) {
    tgInfoData = d;
    const body = document.getElementById('tgInfoBody');
    const fallback = `https://ui-avatars.com/api/?name=${encodeURIComponent(d.name)}&size=96&background=4f8cff&color=fff&bold=true`;

    let html = `<div class="tg-info-cover">
        <img src="${d.avatar}" onerror="this.onerror=null;this.src='${fallback}'">
        <div class="tg-info-cover-name">${tgEscapeHtml(d.name)}</div>
        <div class="tg-info-cover-sub">${d.type === 'group' ? `${d.member_count} ${tgPlural('members', d.member_count)}` : (d.other_user?.online ? tgT('online') : tgT('last_seen'))}</div>
    </div>`;

    if (d.type === 'group') {
        html += `<div class="tg-info-stats">
            <div class="tg-stat-card"><i class="bi bi-chat-left-text"></i><b>${d.stats.messages}</b><span>${tgT('stat_messages')}</span></div>
            <div class="tg-stat-card clickable" onclick="tgOpenMedia('file')"><i class="bi bi-file-earmark"></i><b>${d.stats.files}</b><span>${tgPlural('files_word', d.stats.files)}</span></div>
            <div class="tg-stat-card clickable" onclick="tgOpenMedia('image')"><i class="bi bi-image"></i><b>${d.stats.photos}</b><span>${tgT('stat_photos')}</span></div>
            <div class="tg-stat-card clickable" onclick="tgOpenMedia('voice')"><i class="bi bi-mic"></i><b>${d.stats.voice}</b><span>${tgT('stat_voice')}</span></div>
            <div class="tg-stat-card clickable" onclick="tgOpenMedia('link')"><i class="bi bi-link-45deg"></i><b>${d.stats.links}</b><span>${tgPlural('links_word', d.stats.links)}</span></div>
        </div>
        <div class="tg-info-section-title">
            <span>${tgT('info_members')} · ${d.member_count}</span>
            ${d.can_add ? `<button class="tg-info-add-btn" onclick="tgOpenAddMemberModal()"><i class="bi bi-person-plus-fill"></i> ${tgT('add_member')}</button>` : ''}
        </div>
        <div class="tg-info-members">`;
        d.members.forEach(m => {
            const avFb = `https://ui-avatars.com/api/?name=${encodeURIComponent(m.name)}&size=40&background=4f8cff&color=fff`;
            const canRemove = d.is_manager && !m.is_creator && !m.is_me;
            html += `<div class="tg-member-row">
                <a class="tg-member-main" href="/users/${m.id}">
                    <div class="tg-avatar-wrap">
                        <img src="${m.avatar}" onerror="this.onerror=null;this.src='${avFb}'">
                        <span class="dot ${m.online ? 'on' : 'off'}"></span>
                    </div>
                    <div class="tg-member-info">
                        <div class="tg-member-name">${tgEscapeHtml(m.name)}${m.is_me ? `<span class="tg-member-you">${tgT('info_you')}</span>` : ''}</div>
                        <div class="tg-member-role ${m.is_creator ? 'creator' : ''}">${m.is_creator ? tgT('info_creator') : (m.online ? tgT('online') : tgT('last_seen'))}</div>
                    </div>
                    <i class="bi bi-chevron-right tg-member-chev"></i>
                </a>
                ${canRemove ? `<button class="tg-member-remove" title="${tgT('remove_member')}" onclick="event.preventDefault();event.stopPropagation();tgRemoveMember(${m.id})"><i class="bi bi-x-lg"></i></button>` : ''}
            </div>`;
        });
        html += `</div>`;
    } else {
        html += `<div class="tg-info-contacts">
            <div class="tg-member-row"><i class="bi bi-envelope-fill lead"></i><span class="val">${tgEscapeHtml(d.other_user?.email || '—')}</span></div>
            <div class="tg-member-row"><i class="bi bi-telephone-fill lead"></i><span class="val">${tgEscapeHtml(d.other_user?.phone || '—')}</span></div>
        </div>`;
    }

    html += `<div class="tg-info-actions">`;
    if (d.type === 'group') {
        if (d.can_leave) html += `<button class="tg-danger-btn" onclick="tgLeaveGroup()"><i class="bi bi-box-arrow-right"></i> ${tgT('leave_group')}</button>`;
        if (d.can_delete) html += `<button class="tg-danger-btn" onclick="tgDeleteChatById(${d.id})"><i class="bi bi-trash"></i> ${tgT('delete_chat')}</button>`;
    } else {
        html += `<button class="tg-danger-btn" onclick="tgDeleteChatById(${d.id})"><i class="bi bi-trash"></i> ${tgT('delete_chat')}</button>`;
    }
    html += `</div>`;
    body.innerHTML = html;
}

/* ✅ ГАЛЕРЕЯ МЕДИА */
async function tgOpenMedia(type) {
    if (!tgConvId) return;
    document.getElementById('tgMediaModal').classList.add('active');
    const titles = { image: tgT('gallery_photos'), file: tgT('gallery_files'), voice: tgT('gallery_voice'), link: tgT('gallery_links') };
    document.getElementById('tgMediaTitle').textContent = titles[type] || '';
    const body = document.getElementById('tgMediaBody');
    body.innerHTML = `<div class="tg-info-loader"><i class="bi bi-arrow-repeat tg-spin"></i></div>`;
    try {
        const res = await fetch(`/chat/api/media/${tgConvId}?type=${type}`);
        if (!res.ok) throw new Error();
        tgRenderMedia(await res.json(), type);
    } catch (e) {
        body.innerHTML = `<div class="tg-no-results"><div class="tg-no-results-title">${tgT('error_generic')}</div></div>`;
    }
}
function tgCloseMedia() { document.getElementById('tgMediaModal').classList.remove('active'); }
function tgRenderMedia(items, type) {
    const body = document.getElementById('tgMediaBody');
    if (!items?.length) { body.innerHTML = `<div class="tg-no-results"><i class="bi bi-inbox"></i><div class="tg-no-results-title">${tgT('no_media')}</div></div>`; return; }
    if (type === 'image') {
        body.innerHTML = `<div class="tg-media-grid">` + items.map(it => `<img src="${it.url}" loading="lazy" title="${tgEscapeHtml(it.sender || '')} · ${it.time}" onclick="window.open('${it.url}')">`).join('') + `</div>`;
    } else if (type === 'link') {
        body.innerHTML = items.map(it => `<div class="tg-media-row"><i class="bi bi-link-45deg"></i><div style="flex:1;min-width:0;"><div class="nm">${tgLinkify(tgEscapeHtml(it.body))}</div><div class="tm">${tgEscapeHtml(it.sender || '')} · ${it.time}</div></div></div>`).join('');
    } else if (type === 'voice') {
        body.innerHTML = items.map(it => `<div class="tg-media-row"><i class="bi bi-mic-fill"></i><div style="flex:1;min-width:0;"><audio controls src="${it.url}" preload="metadata"></audio><div class="tm">${tgEscapeHtml(it.sender || '')} · ${it.time}</div></div></div>`).join('');
    } else {
        body.innerHTML = items.map(it => `<div class="tg-media-row"><i class="bi bi-file-earmark-fill"></i><div style="flex:1;min-width:0;"><div class="nm">${tgEscapeHtml(it.name)}</div><div class="tm">${tgEscapeHtml(it.sender || '')} · ${it.time}</div></div><a class="dl" href="${it.url}" target="_blank" download title="${tgT('download')}"><i class="bi bi-download"></i></a></div>`).join('');
    }
}

/* ✅ ДОБАВИТЬ УЧАСТНИКОВ */
function tgOpenAddMemberModal() {
    tgAddMembersSelected.clear();
    document.getElementById('tgAddMemberModal').classList.add('active');
    document.getElementById('tgAddMemberSearch').value = '';
    tgRenderAddMemberTags();
    tgAddMemberSearchFn('');
    setTimeout(() => document.getElementById('tgAddMemberSearch').focus(), 100);
}
function tgCloseAddMemberModal() { document.getElementById('tgAddMemberModal').classList.remove('active'); }
async function tgAddMemberSearchFn(q) {
    const c = document.getElementById('tgAddMemberResults');
    try {
        const res = await fetch(`/chat/api/users?q=${encodeURIComponent(q)}`);
        if (!res.ok) return;
        let users = await res.json();
        const existing = new Set((tgInfoData?.members || []).map(m => m.id));
        users = users.filter(u => !existing.has(u.id));
        c.innerHTML = '';
        if (!users?.length) { c.innerHTML = `<div class="tg-no-results"><i class="bi bi-person-x"></i><div class="tg-no-results-title">${tgT('users_not_found_title')}</div></div>`; return; }
        users.forEach(u => {
            const d = document.createElement('div');
            d.className = 'tg-user-result';
            if (tgAddMembersSelected.has(u.id)) d.classList.add('selected');
            const fb = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&size=40&background=4f8cff&color=fff&bold=true`;
            d.innerHTML = `<img src="${u.avatar || fb}" onerror="this.onerror=null;this.src='${fb}'"><div class="tg-user-result-info"><div class="tg-user-result-name">${tgEscapeHtml(u.name)}</div><div class="tg-user-result-meta">${tgEscapeHtml([u.email, u.phone].filter(Boolean).join(' • '))}</div></div><i class="bi bi-${tgAddMembersSelected.has(u.id) ? 'check-circle-fill' : 'plus-circle'} tg-user-result-check"></i>`;
            d.addEventListener('click', (e) => {
                e.stopPropagation();
                if (tgAddMembersSelected.has(u.id)) tgAddMembersSelected.delete(u.id); else tgAddMembersSelected.set(u.id, u);
                tgRenderAddMemberTags(); tgAddMemberSearchFn(document.getElementById('tgAddMemberSearch').value);
            });
            c.appendChild(d);
        });
    } catch (e) { console.error(e); }
}
function tgRenderAddMemberTags() {
    const c = document.getElementById('tgAddMemberTags'); c.innerHTML = '';
    tgAddMembersSelected.forEach(u => {
        const t = document.createElement('div');
        t.className = 'tg-user-tag';
        const fb = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&size=20`;
        t.innerHTML = `<img src="${u.avatar || fb}" onerror="this.onerror=null;this.src='${fb}'" style="width:18px;height:18px;border-radius:50%;">${tgEscapeHtml(u.name)}<span class="tg-user-tag-remove" onclick="tgAddMembersSelected.delete(${u.id}); tgRenderAddMemberTags(); tgAddMemberSearchFn(document.getElementById('tgAddMemberSearch').value); event.stopPropagation();"><i class="bi bi-x"></i></span>`;
        c.appendChild(t);
    });
}
async function tgConfirmAddMembers() {
    const ids = Array.from(tgAddMembersSelected.keys());
    if (!ids.length || !tgInfoData) { tgShowToast(tgT('select_members'), 'error'); return; }
    try {
        const res = await fetch(`/chat/api/group/${tgInfoData.id}/add-members`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TG_CSRF },
            body: JSON.stringify({ user_ids: ids })
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.error || tgT('error_generic'));
        tgCloseAddMemberModal();
        tgShowToast(tgT('member_added'), 'success');
        tgOpenInfo(tgInfoData.id);
        tgLoadConversations();
    } catch (e) { tgShowToast('❌ ' + e.message, 'error'); }
}

/* ✅ УДАЛИТЬ УЧАСТНИКА */
async function tgRemoveMember(userId) {
    if (!tgInfoData || !confirm(tgT('confirm_remove_member'))) return;
    try {
        const res = await fetch(`/chat/api/group/${tgInfoData.id}/member/${userId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': TG_CSRF } });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.error || tgT('error_generic'));
        tgShowToast(tgT('member_removed'), 'success');
        tgOpenInfo(tgInfoData.id);
        tgLoadConversations();
    } catch (e) { tgShowToast('❌ ' + e.message, 'error'); }
}

async function tgLeaveGroup() {
    if (!tgInfoData || !confirm(tgT('confirm_leave'))) return;
    const id = tgInfoData.id;
    try {
        const res = await fetch(`/chat/api/leave/${id}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': TG_CSRF } });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.error || tgT('error_generic'));
        tgShowToast(tgT('left_group'), 'success');
        tgAfterChatRemoved(id);
    } catch (e) { tgShowToast('❌ ' + e.message, 'error'); }
}
async function tgDeleteChatById(id) {
    if (!confirm(tgT('confirm_delete_chat'))) return;
    try {
        const res = await fetch(`/chat/api/conversation/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': TG_CSRF } });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.error || tgT('error_generic'));
        tgShowToast(tgT('chat_deleted'), 'success');
        tgAfterChatRemoved(id);
    } catch (e) { tgShowToast('❌ ' + e.message, 'error'); }
}
function tgAfterChatRemoved(id) {
    tgCloseInfo();
    if (tgConvId === id) tgResetToEmpty();
    tgLoadConversations();
}
function tgResetToEmpty() {
    tgConvId = null; tgCurrentConvData = null; tgCurrentOtherUserId = null;
    if (tgPolling) clearInterval(tgPolling);
    if (tgTypingPollInterval) clearInterval(tgTypingPollInterval);
    document.getElementById('tgEmpty').style.display = 'flex';
    document.getElementById('tgHeader').style.display = 'none';
    document.getElementById('tgMessages').style.display = 'none';
    document.getElementById('tgFooterWrap').style.display = 'none';
    document.getElementById('tgBlockedBanner').style.display = 'none';
    document.getElementById('tgContainer').classList.remove('show-chat');
}

function tgShowToast(message, type = 'info') {
    const old = document.querySelector('.tg-toast-live'); if (old) old.remove();
    const t = document.createElement('div');
    t.className = 'tg-toast-live';
    const colors = {
        success: { bg: 'linear-gradient(135deg, #10b981, #059669)', icon: 'check-circle-fill' },
        error: { bg: 'linear-gradient(135deg, #ef4444, #dc2626)', icon: 'x-circle-fill' },
        info: { bg: 'linear-gradient(135deg, rgb(var(--glow)), rgba(var(--glow), 0.85))', icon: 'info-circle-fill' }
    };
    const c = colors[type] || colors.info;
    t.style.background = c.bg; t.style.color = 'white';
    t.innerHTML = `<i class="bi bi-${c.icon}" style="font-size:18px;flex-shrink:0;"></i><span style="flex:1;">${tgEscapeHtml(message)}</span><div class="tg-toast-progress"></div>`;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateX(120%)'; t.style.transition = 'all 0.3s cubic-bezier(0.4,0,0.2,1)'; setTimeout(() => t.remove(), 300); }, 3000);
}
</script>
@endsection