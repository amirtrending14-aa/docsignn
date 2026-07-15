@extends('layouts.admin')

@section('content')

<div class="logs-page">

    {{-- Ambient Lighting --}}
    <div class="ambient-lighting">
        <div class="ambient-blob ambient-blob-1"></div>
        <div class="ambient-blob ambient-blob-2"></div>
        <div class="ambient-blob ambient-blob-3"></div>
    </div>

    <div class="logs-container">

        <style>
            /* ============================================ */
            /* === БАЗОВЫЕ СТИЛИ СТРАНИЦЫ === */
            /* ============================================ */
            .logs-page {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
                min-height: 100vh;
                padding: 24px 20px;
                position: relative;
                overflow: hidden;
                color: var(--text);
            }

            .logs-page * {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            }

            .logs-page .mono {
                font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace !important;
            }

            /* Ambient Lighting */
            .ambient-lighting {
                position: absolute;
                inset: 0;
                pointer-events: none;
                overflow: hidden;
                z-index: 0;
            }

            .ambient-blob {
                position: absolute;
                border-radius: 50%;
                filter: blur(120px);
            }

            .ambient-blob-1 {
                top: -160px;
                left: -160px;
                width: 420px;
                height: 420px;
                opacity: 0.18;
                background: radial-gradient(circle, rgba(var(--glow), 1) 0%, transparent 70%);
            }

            .ambient-blob-2 {
                bottom: -160px;
                right: -160px;
                width: 500px;
                height: 500px;
                opacity: 0.12;
                background: radial-gradient(circle, #7c3aed 0%, transparent 70%);
            }

            .ambient-blob-3 {
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 340px;
                height: 340px;
                opacity: 0.08;
                background: radial-gradient(circle, rgba(var(--glow), 1) 0%, transparent 70%);
            }

            .logs-container {
                max-width: 1400px;
                margin: 0 auto;
                position: relative;
                z-index: 10;
            }

            /* ============================================ */
            /* === HEADER === */
            /* ============================================ */
            .logs-header {
                display: flex;
                align-items: flex-end;
                justify-content: space-between;
                margin-bottom: 20px;
                gap: 16px;
                flex-wrap: wrap;
            }

            .logs-title {
                font-size: 22px;
                font-weight: 800;
                letter-spacing: -0.5px;
                color: var(--text);
                margin: 0;
                text-shadow: 0 0 30px rgba(var(--glow), 0.3);
                text-transform: uppercase;
            }

            .logs-subtitle {
                font-family: 'JetBrains Mono', monospace;
                font-size: 10px;
                font-weight: 600;
                color: var(--muted);
                text-transform: uppercase;
                letter-spacing: 0.25em;
                margin-top: 4px;
            }

            .logs-counter {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 8px 14px;
                background: rgba(255, 255, 255, 0.035);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.06);
                border-radius: 12px;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.05);
            }

            .pulse-dot {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #10b981;
                box-shadow: 0 0 10px #10b981;
                animation: pulse-glow 2s ease-in-out infinite;
            }

            @keyframes pulse-glow {
                0%, 100% { opacity: 1; box-shadow: 0 0 10px #10b981; }
                50% { opacity: 0.6; box-shadow: 0 0 18px #10b981; }
            }

            .logs-counter-text {
                font-family: 'JetBrains Mono', monospace;
                font-size: 11px;
                font-weight: 700;
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .logs-counter-number {
                color: rgba(var(--glow), 1);
                font-weight: 800;
            }

            .logs-counter-label {
                color: var(--muted);
            }

            /* ============================================ */
            /* === GLASS CARD === */
            /* ============================================ */
            .glass-card {
                background: rgba(255, 255, 255, 0.035);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.06);
                border-radius: 16px;
                box-shadow:
                    0 8px 32px rgba(0, 0, 0, 0.3),
                    inset 0 1px 0 rgba(255, 255, 255, 0.05);
                overflow: hidden;
                position: relative;
            }

            .glass-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 1px;
                background: linear-gradient(90deg, transparent, rgba(var(--glow), 0.3), transparent);
                pointer-events: none;
            }

            /* ============================================ */
            /* === TABLE === */
            /* ============================================ */
            .logs-scroll {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .logs-scroll::-webkit-scrollbar {
                height: 6px;
            }

            .logs-scroll::-webkit-scrollbar-track {
                background: rgba(255, 255, 255, 0.02);
            }

            .logs-scroll::-webkit-scrollbar-thumb {
                background: rgba(var(--glow), 0.3);
                border-radius: 20px;
            }

            .logs-scroll::-webkit-scrollbar-thumb:hover {
                background: rgba(var(--glow), 0.5);
            }

            .logs-table {
                width: 100%;
                text-align: left;
                border-collapse: collapse;
                min-width: 900px;
            }

            .logs-table th {
                padding: 12px 16px;
                font-family: 'JetBrains Mono', monospace;
                font-size: 9px;
                font-weight: 700;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                color: rgba(148, 163, 184, 0.7);
                background: rgba(255, 255, 255, 0.02);
                border-bottom: 1px solid rgba(255, 255, 255, 0.06);
                white-space: nowrap;
            }

            .logs-table td {
                padding: 14px 16px;
                font-size: 13px;
                color: rgba(226, 232, 240, 0.9);
                vertical-align: middle;
                border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            }

            .tr-hover {
                transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .tr-hover:hover {
                background: rgba(var(--glow), 0.05);
                transform: translateX(2px);
            }

            .tr-hover:hover td {
                color: #fff;
            }

            /* ID ячейка */
            .cell-id {
                text-align: center;
                font-family: 'JetBrains Mono', monospace;
                font-weight: 700;
                color: var(--muted);
                font-size: 11px;
            }

            /* Document - показываем полное название */
            .cell-doc-title {
                font-weight: 600;
                color: var(--text);
                display: block;
                max-width: 350px;
                word-wrap: break-word;
                white-space: normal;
                line-height: 1.5;
                font-size: 13px;
            }

            /* User - показываем полное имя */
            .user-cell {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .mini-avatar {
                width: 32px;
                height: 32px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'JetBrains Mono', monospace;
                font-size: 12px;
                font-weight: 800;
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
                transition: all 0.2s ease;
                flex-shrink: 0;
            }

            .tr-hover:hover .mini-avatar {
                transform: scale(1.1);
                box-shadow: 0 0 16px rgba(var(--glow), 0.4);
            }

            .avatar-red { background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.1)); color: #f87171; border-color: rgba(239, 68, 68, 0.3); }
            .avatar-blue { background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.1)); color: #60a5fa; border-color: rgba(59, 130, 246, 0.3); }
            .avatar-slate { background: linear-gradient(135deg, rgba(100, 116, 139, 0.2), rgba(71, 85, 105, 0.1)); color: #94a3b8; border-color: rgba(100, 116, 139, 0.3); }
            .avatar-indigo { background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(79, 70, 229, 0.1)); color: #818cf8; border-color: rgba(99, 102, 241, 0.3); }

            .user-name {
                font-weight: 600;
                color: #cbd5e1;
                font-size: 13px;
                word-wrap: break-word;
                white-space: normal;
                line-height: 1.4;
                max-width: 180px;
            }

            /* Action badge */
            .action-badge {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 5px 12px;
                font-family: 'JetBrains Mono', monospace;
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                border-radius: 999px;
                border: 1px solid;
                backdrop-filter: blur(10px);
                transition: all 0.2s ease;
                white-space: nowrap;
            }

            .action-badge:hover {
                transform: scale(1.05);
                box-shadow: 0 0 16px currentColor;
            }

            .badge-created { background: rgba(16, 185, 129, 0.1); color: #34d399; border-color: rgba(16, 185, 129, 0.3); }
            .badge-updated { background: rgba(59, 130, 246, 0.1); color: #60a5fa; border-color: rgba(59, 130, 246, 0.3); }
            .badge-deleted { background: rgba(239, 68, 68, 0.1); color: #f87171; border-color: rgba(239, 68, 68, 0.3); }
            .badge-signed  { background: rgba(99, 102, 241, 0.1); color: #818cf8; border-color: rgba(99, 102, 241, 0.3); }
            .badge-status  { background: rgba(245, 158, 11, 0.1); color: #fbbf24; border-color: rgba(245, 158, 11, 0.3); }
            .badge-sent { background: rgba(6, 182, 212, 0.1); color: #22d3ee; border-color: rgba(6, 182, 212, 0.3); }
            .badge-downloaded { background: rgba(168, 85, 247, 0.1); color: #c084fc; border-color: rgba(168, 85, 247, 0.3); }
            .badge-exported { background: rgba(236, 72, 153, 0.1); color: #f472b6; border-color: rgba(236, 72, 153, 0.3); }
            .badge-rejected { background: rgba(220, 38, 38, 0.1); color: #ef4444; border-color: rgba(220, 38, 38, 0.3); }
            .badge-ai { background: rgba(132, 204, 22, 0.1); color: #a3e635; border-color: rgba(132, 204, 22, 0.3); }
            .badge-error { background: rgba(251, 146, 60, 0.1); color: #fb923c; border-color: rgba(251, 146, 60, 0.3); }
            .badge-unknown { background: rgba(100, 116, 139, 0.1); color: #94a3b8; border-color: rgba(100, 116, 139, 0.3); }

            /* Meta - показываем полное описание */
            .cell-meta {
                color: var(--muted);
                font-weight: 500;
                font-size: 12px;
                font-style: italic;
                line-height: 1.5;
                display: block;
                max-width: 300px;
                word-wrap: break-word;
                white-space: normal;
            }

            /* Time */
            .cell-time {
                font-family: 'JetBrains Mono', monospace;
                font-weight: 600;
                color: #cbd5e1;
                font-size: 12px;
                white-space: nowrap;
            }

            /* Delete button - КРАСИВАЯ ИКОНКА МУСОРНОГО БАКА */
            .delete-btn-icon {
                width: 38px;
                height: 38px;
                border-radius: 12px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
                border: none;
                color: #ffffff;
                cursor: pointer;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
                position: relative;
                overflow: hidden;
            }

            .delete-btn-icon::after {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
                transition: 0.5s;
            }

            .delete-btn-icon:hover::after {
                left: 100%;
            }

            .delete-btn-icon svg {
                transition: transform 0.3s ease;
                z-index: 2;
            }

            .delete-btn-icon:hover {
                transform: translateY(-3px) scale(1.05);
                box-shadow: 0 8px 20px rgba(239, 68, 68, 0.6);
                background: linear-gradient(135deg, #f87171 0%, #dc2626 100%);
            }

            .delete-btn-icon:hover svg {
                transform: rotate(15deg);
            }

            .delete-btn-icon:active {
                transform: translateY(0) scale(0.95);
                box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
            }

            /* Empty state */
            .empty-state {
                padding: 50px 20px;
                text-align: center;
            }

            .empty-icon-wrap {
                width: 56px;
                height: 56px;
                border-radius: 50%;
                background: rgba(100, 116, 139, 0.1);
                border: 1px solid rgba(100, 116, 139, 0.2);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 12px;
            }

            .empty-icon-wrap i {
                font-size: 22px;
                color: var(--muted);
            }

            .empty-text {
                font-family: 'JetBrains Mono', monospace;
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.25em;
                color: var(--muted);
            }

            /* ============================================ */
            /* === PAGINATION === */
            /* ============================================ */
            .pagination-wrapper {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 10px;
                margin-top: 20px;
                flex-wrap: wrap;
            }

            .pagination-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 130px;
                height: 40px;
                padding: 0 20px;
                border-radius: 10px;
                font-size: 12px;
                font-weight: 700;
                font-family: 'Inter', sans-serif;
                color: rgba(226, 232, 240, 0.9);
                background: rgba(255, 255, 255, 0.035);
                border: 1px solid rgba(255, 255, 255, 0.06);
                text-decoration: none;
                transition: all 0.25s ease;
                cursor: pointer;
                letter-spacing: 0.5px;
                backdrop-filter: blur(10px);
            }

            .pagination-btn:hover:not(.disabled) {
                color: #fff;
                border-color: rgba(var(--glow), 0.4);
                background: rgba(var(--glow), 0.1);
                transform: translateY(-2px);
                box-shadow: 0 8px 24px rgba(var(--glow), 0.3);
            }

            .pagination-btn.disabled {
                opacity: 0.3;
                cursor: not-allowed;
                pointer-events: none;
            }

            /* ============================================ */
            /* === ПОЛНАЯ АДАПТИВНОСТЬ === */
            /* ============================================ */

            /* Маленькие десктопы (до 1200px) */
            @media (max-width: 1200px) {
                .logs-page { padding: 20px 16px; }
                .logs-container { max-width: 100%; }
                .logs-title { font-size: 20px; }
                .logs-header { gap: 14px; }
                .logs-table th, .logs-table td { padding: 12px 14px; }
                .cell-doc-title { max-width: 280px; }
                .user-name { max-width: 160px; }
                .cell-meta { max-width: 240px; }
            }

            /* Планшеты (до 992px) */
            @media (max-width: 992px) {
                .logs-page { padding: 18px 14px; }
                .logs-title { font-size: 19px; }
                .logs-subtitle { font-size: 9px; }
                .logs-header { margin-bottom: 16px; gap: 12px; }
                .logs-counter { padding: 7px 12px; }
                .logs-counter-text { font-size: 10px; }
                .glass-card { border-radius: 14px; }
                .logs-table th { padding: 10px 12px; font-size: 8px; }
                .logs-table td { padding: 12px; font-size: 12px; }
                .cell-doc-title { max-width: 220px; font-size: 12px; }
                .mini-avatar { width: 30px; height: 30px; font-size: 11px; }
                .user-name { max-width: 140px; font-size: 12px; }
                .action-badge { padding: 4px 10px; font-size: 9px; }
                .cell-meta { max-width: 200px; font-size: 11px; }
                .cell-time { font-size: 11px; }
                .delete-btn-icon { width: 34px; height: 34px; }
                .pagination-btn { min-width: 120px; height: 38px; padding: 0 18px; font-size: 11px; }
            }

            /* Большие телефоны (до 768px) */
            @media (max-width: 768px) {
                .logs-page { padding: 16px 12px; }
                .logs-title { font-size: 18px; }
                .logs-subtitle { font-size: 9px; letter-spacing: 0.2em; }
                .logs-header { margin-bottom: 14px; gap: 10px; }
                .logs-counter { padding: 6px 10px; gap: 8px; }
                .logs-counter-text { font-size: 10px; gap: 5px; }
                .pulse-dot { width: 6px; height: 6px; }
                .glass-card { border-radius: 12px; }
                .logs-table { min-width: 800px; }
                .logs-table th { padding: 9px 10px; font-size: 8px; letter-spacing: 0.1em; }
                .logs-table td { padding: 10px; font-size: 11px; }
                .cell-id { font-size: 10px; }
                .cell-doc-title { max-width: 180px; font-size: 11px; line-height: 1.4; }
                .mini-avatar { width: 28px; height: 28px; font-size: 10px; border-radius: 7px; }
                .user-cell { gap: 8px; }
                .user-name { max-width: 120px; font-size: 11px; }
                .action-badge { padding: 4px 8px; font-size: 8px; gap: 4px; }
                .cell-meta { max-width: 160px; font-size: 10px; }
                .cell-time { font-size: 10px; }
                .delete-btn-icon { width: 32px; height: 32px; border-radius: 9px; }
                .delete-btn-icon svg { width: 15px; height: 15px; }
                .empty-state { padding: 40px 16px; }
                .empty-icon-wrap { width: 48px; height: 48px; }
                .empty-icon-wrap i { font-size: 20px; }
                .empty-text { font-size: 9px; }
                .pagination-wrapper { gap: 8px; margin-top: 16px; }
                .pagination-btn { min-width: 110px; height: 36px; padding: 0 16px; font-size: 11px; border-radius: 9px; }
            }

            /* Телефоны (до 640px) */
            @media (max-width: 640px) {
                .logs-page { padding: 14px 10px; }
                .logs-title { font-size: 16px; }
                .logs-subtitle { font-size: 8px; letter-spacing: 0.18em; margin-top: 3px; }
                .logs-header { margin-bottom: 12px; gap: 8px; }
                .logs-counter { padding: 5px 9px; gap: 7px; border-radius: 10px; }
                .logs-counter-text { font-size: 9px; gap: 4px; }
                .logs-counter-number { font-size: 10px; }
                .pulse-dot { width: 5px; height: 5px; }
                .glass-card { border-radius: 10px; }
                .logs-table { min-width: 700px; }
                .logs-table th { padding: 8px 9px; font-size: 7px; }
                .logs-table td { padding: 9px; font-size: 10px; }
                .cell-id { font-size: 9px; }
                .cell-doc-title { max-width: 150px; font-size: 10px; }
                .mini-avatar { width: 26px; height: 26px; font-size: 9px; border-radius: 6px; }
                .user-cell { gap: 7px; }
                .user-name { max-width: 100px; font-size: 10px; }
                .action-badge { padding: 3px 7px; font-size: 8px; }
                .cell-meta { max-width: 140px; font-size: 9px; }
                .cell-time { font-size: 9px; }
                .delete-btn-icon { width: 30px; height: 30px; border-radius: 8px; }
                .delete-btn-icon svg { width: 14px; height: 14px; }
                .empty-state { padding: 32px 12px; }
                .empty-icon-wrap { width: 44px; height: 44px; margin-bottom: 10px; }
                .empty-icon-wrap i { font-size: 18px; }
                .empty-text { font-size: 8px; letter-spacing: 0.2em; }
                .pagination-wrapper { gap: 6px; margin-top: 14px; }
                .pagination-btn { min-width: 100px; height: 34px; padding: 0 14px; font-size: 10px; border-radius: 8px; }
            }

            /* Маленькие телефоны (до 480px) */
            @media (max-width: 480px) {
                .logs-page { padding: 12px 8px; }
                .logs-title { font-size: 15px; }
                .logs-subtitle { font-size: 8px; }
                .logs-header { margin-bottom: 10px; gap: 6px; }
                .logs-counter { padding: 4px 8px; gap: 6px; }
                .logs-counter-text { font-size: 8px; }
                .glass-card { border-radius: 9px; }
                .logs-table { min-width: 650px; }
                .logs-table th { padding: 7px 8px; font-size: 7px; }
                .logs-table td { padding: 8px; font-size: 10px; }
                .cell-id { font-size: 9px; }
                .cell-doc-title { max-width: 130px; font-size: 9px; }
                .mini-avatar { width: 24px; height: 24px; font-size: 9px; }
                .user-cell { gap: 6px; }
                .user-name { max-width: 90px; font-size: 9px; }
                .action-badge { padding: 3px 6px; font-size: 7px; }
                .cell-meta { max-width: 120px; font-size: 9px; }
                .cell-time { font-size: 9px; }
                .delete-btn-icon { width: 28px; height: 28px; }
                .delete-btn-icon svg { width: 13px; height: 13px; }
                .empty-state { padding: 28px 10px; }
                .empty-icon-wrap { width: 40px; height: 40px; }
                .empty-icon-wrap i { font-size: 16px; }
                .empty-text { font-size: 8px; }
                .pagination-wrapper { gap: 5px; margin-top: 12px; }
                .pagination-btn { min-width: 90px; height: 32px; padding: 0 12px; font-size: 9px; }
            }

            /* Очень маленькие телефоны (до 380px) */
            @media (max-width: 380px) {
                .logs-page { padding: 10px 6px; }
                .logs-title { font-size: 14px; }
                .logs-subtitle { font-size: 7px; }
                .logs-header { margin-bottom: 8px; }
                .logs-counter { padding: 4px 7px; }
                .logs-counter-text { font-size: 8px; }
                .glass-card { border-radius: 8px; }
                .logs-table { min-width: 600px; }
                .logs-table th { padding: 6px 7px; font-size: 6px; }
                .logs-table td { padding: 7px; font-size: 9px; }
                .cell-doc-title { max-width: 110px; }
                .mini-avatar { width: 22px; height: 22px; font-size: 8px; }
                .user-name { max-width: 80px; font-size: 9px; }
                .action-badge { padding: 2px 5px; font-size: 7px; }
                .cell-meta { max-width: 100px; }
                .delete-btn-icon { width: 26px; height: 26px; }
                .delete-btn-icon svg { width: 12px; height: 12px; }
                .pagination-btn { min-width: 80px; height: 30px; font-size: 8px; }
            }

            /* Скрытие колонок на очень маленьких экранах */
            @media (max-width: 1024px) {
                .hidden-lg { display: none !important; }
            }
        </style>

        {{-- HEADER --}}
        <div class="logs-header">
            <div>
                <h1 class="logs-title">
                    <span data-i18n="historyTitle">История событий</span>
                </h1>
                <p class="logs-subtitle">
                    <span data-i18n="systemArchive">System Log Archive</span>
                </p>
            </div>

            <div class="logs-counter">
                <div class="pulse-dot"></div>
                <span class="logs-counter-text">
                    <span id="logsCountNumber" class="logs-counter-number">{{ $logs->total() }}</span>
                    <span data-i18n="logsText" class="logs-counter-label">логов</span>
                </span>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="glass-card">
            <div class="logs-scroll">
                <table class="logs-table">
                    <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;" data-i18n="colId">ID</th>
                        <th data-i18n="colDoc">Документ</th>
                        <th data-i18n="colUser">Инициатор</th>
                        <th data-i18n="colAction">Тип действия</th>
                        <th class="hidden-lg" data-i18n="colMeta">Мета-данные</th>
                        <th data-i18n="colTime">Время</th>
                        <th style="text-align: right; width: 80px;" data-i18n="colManage">Управление</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($logs as $index => $log)
                    <tr class="tr-hover">

                        {{-- ID --}}
                        <td class="cell-id">
                            #{{ str_pad($log->id, 3, '0', STR_PAD_LEFT) }}
                        </td>

                        {{-- DOCUMENT --}}
                        <td>
                            <span class="cell-doc-title">
                                {{ $log->document->title ?? '—' }}
                            </span>
                        </td>

                        {{-- USER --}}
                        <td>
                            <div class="user-cell">
                                @php
                                $name = $log->user->name ?? 'System';
                                $firstChar = mb_substr($name, 0, 1);
                                $avatarStyle = match(strtoupper($firstChar)) {
                                'A','R' => 'avatar-red',
                                'B','D' => 'avatar-blue',
                                'S'     => 'avatar-slate',
                                default => 'avatar-indigo'
                                };
                                @endphp
                                <div class="mini-avatar {{ $avatarStyle }}">
                                    {{ strtoupper($firstChar) }}
                                </div>
                                <span class="user-name">{{ $name }}</span>
                            </div>
                        </td>

                        {{-- ACTION --}}
                        <td>
                            @php
                            $action = strtolower($log->action);
                            $actionKey = match(true) {
                            str_contains($action, 'create') || str_contains($action, 'создание') || str_contains($action, 'сохтан') || str_contains($action, 'эҷод') => 'actionCreated',
                            str_contains($action, 'update') || str_contains($action, 'обновление') || str_contains($action, 'навсозӣ') || str_contains($action, 'нав кардан') => 'actionUpdated',
                            str_contains($action, 'delete') || str_contains($action, 'удаление') || str_contains($action, 'нест кардан') || str_contains($action, 'нобуд') => 'actionDeleted',
                            str_contains($action, 'sign') || str_contains($action, 'подпис') || str_contains($action, 'имзо') => 'actionSigned',
                            str_contains($action, 'send') || str_contains($action, 'отправк') || str_contains($action, 'фиристодан') => 'actionSent',
                            str_contains($action, 'download') || str_contains($action, 'скачиван') || str_contains($action, 'боргирӣ') => 'actionDownloaded',
                            str_contains($action, 'export') || str_contains($action, 'экспорт') || str_contains($action, 'содирот') => 'actionExported',
                            str_contains($action, 'reject') || str_contains($action, 'отклон') || str_contains($action, 'рад кардан') => 'actionRejected',
                            str_contains($action, 'ai') || str_contains($action, 'ии') || str_contains($action, 'таҳлил') || str_contains($action, 'анализ') => 'actionAI',
                            str_contains($action, 'error') || str_contains($action, 'ошибк') || str_contains($action, 'хатогӣ') => 'actionError',
                            str_contains($action, 'status') || str_contains($action, 'статус') || str_contains($action, 'иваз') => 'actionStatus',
                            default => 'actionUnknown'
                            };
                            $badgeClass = match($actionKey) {
                            'actionDeleted' => 'badge-deleted',
                            'actionCreated' => 'badge-created',
                            'actionUpdated' => 'badge-updated',
                            'actionSigned'  => 'badge-signed',
                            'actionSent'    => 'badge-sent',
                            'actionDownloaded' => 'badge-downloaded',
                            'actionExported' => 'badge-exported',
                            'actionRejected' => 'badge-rejected',
                            'actionAI'      => 'badge-ai',
                            'actionError'   => 'badge-error',
                            'actionStatus'  => 'badge-status',
                            default         => 'badge-unknown'
                            };
                            @endphp
                            <span class="action-badge {{ $badgeClass }}" data-i18n="{{ $actionKey }}">
                                {{ $log->action }}
                            </span>
                        </td>

                        {{-- META --}}
                        <td class="hidden-lg">
                            <span class="cell-meta">{{ $log->description }}</span>
                        </td>

                        {{-- TIME --}}
                        <td class="cell-time">
                            {{ $log->created_at->format('d.m.y / H:i') }}
                        </td>

                        {{-- DELETE - ЯРКАЯ КНОПКА С SVG --}}
                        <td style="text-align: right;">
                            <form action="{{ route('logs.destroy', $log->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" data-confirm-i18n="confirmDelete" class="delete-btn-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                        <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-icon-wrap">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <div class="empty-text" data-i18n="noLogs">No logs found</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="pagination-wrapper">
            @if($logs->onFirstPage())
            <span class="pagination-btn disabled" data-i18n="prevPage">« Previous</span>
            @else
            <a href="{{ $logs->previousPageUrl() }}" class="pagination-btn" data-i18n="prevPage">« Previous</a>
            @endif

            @if($logs->hasMorePages())
            <a href="{{ $logs->nextPageUrl() }}" class="pagination-btn" data-i18n="nextPage">Next »</a>
            @else
            <span class="pagination-btn disabled" data-i18n="nextPage">Next »</span>
            @endif
        </div>
        @endif

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const LOGS_TRANSLATIONS = {
            ru: {
                historyTitle: 'История событий',
                systemArchive: 'Архив системных логов',
                colId: 'ID',
                colDoc: 'Документ',
                colUser: 'Инициатор',
                colAction: 'Тип действия',
                colMeta: 'Мета-данные',
                colTime: 'Время',
                colManage: 'Управление',
                actionCreated: 'Создание',
                actionUpdated: 'Обновление',
                actionDeleted: 'Удаление',
                actionSigned: 'Подписание',
                actionSent: 'Отправка',
                actionDownloaded: 'Скачивание',
                actionExported: 'Экспорт',
                actionRejected: 'Отклонение',
                actionAI: 'AI анализ',
                actionError: 'Ошибка',
                actionStatus: 'Смена статуса',
                actionUnknown: 'Действие',
                noLogs: 'Записи не найдены',
                confirmDelete: 'Удалить запись?',
                logsText: 'логов',
                prevPage: '« Назад',
                nextPage: 'Вперёд »'
            },
            tj: {
                historyTitle: 'Таърихи ҳодисаҳо',
                systemArchive: 'Архиви системаи логҳо',
                colId: 'ID',
                colDoc: 'Ҳуҷҷат',
                colUser: 'Ташаббускор',
                colAction: 'Навъи амал',
                colMeta: 'Маълумоти иловагӣ',
                colTime: 'Вақт',
                colManage: 'Идоракунӣ',
                actionCreated: 'Сохтан',
                actionUpdated: 'Навсозӣ',
                actionDeleted: 'Нест кардан',
                actionSigned: 'Имзо кардан',
                actionSent: 'Фиристодан',
                actionDownloaded: 'Боргирӣ кардан',
                actionExported: 'Содирот',
                actionRejected: 'Рад кардан',
                actionAI: 'Таҳлили AI',
                actionError: 'Хатогӣ',
                actionStatus: 'Ивази статус',
                actionUnknown: 'Амал',
                noLogs: 'Сабтҳо ёфт нашуданд',
                confirmDelete: 'Сабтро нест мекунед?',
                logsText: 'сабтҳо',
                prevPage: '« Қафо',
                nextPage: 'Пеш »'
            },
            en: {
                historyTitle: 'Event History',
                systemArchive: 'System Log Archive',
                colId: 'ID',
                colDoc: 'Document',
                colUser: 'Initiator',
                colAction: 'Action Type',
                colMeta: 'Meta Data',
                colTime: 'Time',
                colManage: 'Management',
                actionCreated: 'Creation',
                actionUpdated: 'Update',
                actionDeleted: 'Deletion',
                actionSigned: 'Signing',
                actionSent: 'Sending',
                actionDownloaded: 'Download',
                actionExported: 'Export',
                actionRejected: 'Rejection',
                actionAI: 'AI Analysis',
                actionError: 'Error',
                actionStatus: 'Status Change',
                actionUnknown: 'Action',
                noLogs: 'No logs found',
                confirmDelete: 'Delete this record?',
                logsText: 'logs',
                prevPage: '« Previous',
                nextPage: 'Next »'
            }
        };

        function applyLogsTranslations(lang) {
            const dict = LOGS_TRANSLATIONS[lang] || LOGS_TRANSLATIONS.ru;

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

            // Re-bind confirm handlers with current language
            document.querySelectorAll('[data-confirm-i18n]').forEach(el => {
                const key = el.getAttribute('data-confirm-i18n');
                const newBtn = el.cloneNode(true);
                el.parentNode.replaceChild(newBtn, el);

                newBtn.addEventListener('click', function (e) {
                    const currentLang = localStorage.getItem('docsign_lang') || 'ru';
                    const currentDict = LOGS_TRANSLATIONS[currentLang] || LOGS_TRANSLATIONS.ru;
                    const message = currentDict[key] || 'Are you sure?';

                    if (!confirm(message)) {
                        e.preventDefault();
                    }
                });
            });
        }

        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applyLogsTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applyLogsTranslations(lang);
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applyLogsTranslations(e.newValue);
            }
        });
    });
</script>

@endsection