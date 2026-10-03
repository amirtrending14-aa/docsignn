@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    /* ============================================================ */
    /* ===== USERS TREE PAGE (Tabler 3D Style) ===== */
    /* ============================================================ */
    .users-tree-page {
        min-height: 100vh;
        padding: 8px 0 40px;
        color: var(--text);
        font-family: var(--font-sans);
        position: relative;
    }

    /* Фоновые пятна (только для светлой темы) */
    html[data-theme="light"] .users-tree-page::before,
    html[data-theme="light"] .users-tree-page::after {
        content: "";
        position: fixed;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
        filter: blur(120px);
        opacity: 0.4;
    }
    html[data-theme="light"] .users-tree-page::before {
        top: -200px; left: -100px;
        width: 500px; height: 500px;
        background: radial-gradient(circle, rgba(var(--glow), 0.18) 0%, transparent 70%);
        animation: treeBlobFloat 22s ease-in-out infinite;
    }
    html[data-theme="light"] .users-tree-page::after {
        bottom: -200px; right: -100px;
        width: 600px; height: 600px;
        background: radial-gradient(circle, rgba(168, 85, 247, 0.10) 0%, transparent 70%);
        animation: treeBlobFloat 28s ease-in-out infinite reverse;
    }
    @keyframes treeBlobFloat {
        0%,100% { transform: translate(0,0) scale(1); }
        50%     { transform: translate(30px,-30px) scale(1.05); }
    }

    .users-tree-page > * { position: relative; z-index: 1; }

    /* === TOP BAR === */
    .users-topbar {
        max-width: 1400px;
        margin: 0 auto 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .users-topbar-left {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
        flex: 1;
    }

    .users-topbar-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius);
        background: linear-gradient(135deg, rgb(var(--glow)), rgba(var(--glow), 0.7));
        display: grid;
        place-items: center;
        box-shadow:
            0 6px 16px rgba(var(--glow), 0.35),
            inset 0 1px 0 rgba(255,255,255,0.35);
        flex-shrink: 0;
        position: relative;
    }
    .users-topbar-icon::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: var(--radius);
        background: linear-gradient(180deg, rgba(255,255,255,0.25), transparent 60%);
        pointer-events: none;
    }
    .users-topbar-icon svg {
        width: 24px;
        height: 24px;
        color: #fff;
        position: relative;
        z-index: 1;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.2));
    }

    .users-topbar-title {
        font-size: 22px;
        font-weight: 700;
        color: var(--text);
        letter-spacing: -0.3px;
        margin: 0;
        line-height: 1.2;
        word-break: break-word;
    }
    .users-topbar-subtitle {
        font-size: 12px;
        color: var(--muted);
        font-weight: 600;
        margin-top: 3px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    /* === 3D ADD BUTTON === */
    .btn-add {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: linear-gradient(180deg, rgb(var(--glow)), rgba(var(--glow), 0.85));
        color: #fff;
        border-radius: var(--radius);
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.25s ease;
        box-shadow:
            0 6px 16px rgba(var(--glow), 0.35),
            inset 0 1px 0 rgba(255,255,255,0.3);
        border: 1px solid rgba(var(--glow), 0.6);
        white-space: nowrap;
        flex-shrink: 0;
        position: relative;
        font-family: var(--font-sans);
    }
    .btn-add::before {
        /* нижняя 3D-тень */
        content: "";
        position: absolute;
        left: 8px; right: 8px; bottom: -5px;
        height: 7px;
        background: linear-gradient(180deg, rgba(var(--glow),0.35), transparent);
        border-radius: var(--radius);
        z-index: -1;
        filter: blur(5px);
        transition: all 0.25s ease;
    }
    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow:
            0 10px 22px rgba(var(--glow), 0.45),
            inset 0 1px 0 rgba(255,255,255,0.4);
        filter: brightness(1.05);
    }
    .btn-add:hover::before { bottom: -8px; }
    .btn-add:active {
        transform: translateY(1px);
        box-shadow:
            0 2px 8px rgba(var(--glow), 0.3),
            inset 0 1px 0 rgba(255,255,255,0.2);
    }
    .btn-add:active::before { bottom: -2px; }
    .btn-add svg { width: 15px; height: 15px; }

    /* === STATS ROW === */
    .stats-row {
        max-width: 1400px;
        margin: 0 auto 28px;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    @media (min-width: 768px) {
        .stats-row { grid-template-columns: repeat(4, 1fr); }
    }

    .stat-card {
        position: relative;
        background: var(--bg-1);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 20px;
        overflow: visible;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: var(--shadow-card);
    }
    .stat-card::before {
        /* нижняя 3D-тень */
        content: "";
        position: absolute;
        left: 10px; right: 10px; bottom: -7px;
        height: 10px;
        background: linear-gradient(180deg, rgba(15,23,42,0.08), rgba(15,23,42,0.02));
        border-radius: var(--radius);
        z-index: -1;
        filter: blur(8px);
        transition: all 0.3s ease;
    }
    .stat-card::after {
        /* верхний глянец */
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.9), transparent);
        pointer-events: none;
        opacity: 0.8;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        border-color: rgba(var(--glow), 0.4);
        box-shadow:
            0 12px 28px -10px rgba(var(--glow), 0.25),
            0 4px 10px rgba(15,23,42,0.06);
    }
    .stat-card:hover::before { bottom: -12px; }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius);
        display: grid;
        place-items: center;
        font-size: 22px;
        flex-shrink: 0;
        position: relative;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .stat-icon::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: var(--radius);
        background: linear-gradient(180deg, rgba(255,255,255,0.3), transparent 70%);
        pointer-events: none;
    }
    .stat-icon svg, .stat-icon i {
        width: 20px; height: 20px;
        position: relative; z-index: 1;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.2));
    }
    .stat-icon.users   { background: linear-gradient(135deg, rgb(var(--glow)), rgba(var(--glow),0.7)); color: #fff; box-shadow: 0 4px 10px rgba(var(--glow),0.35); }
    .stat-icon.leaders { background: linear-gradient(135deg, #a855f7, #7c3aed); color: #fff; box-shadow: 0 4px 10px rgba(168,85,247,0.35); }
    .stat-icon.levels  { background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 4px 10px rgba(16,185,129,0.35); }
    .stat-icon.online  { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; box-shadow: 0 4px 10px rgba(245,158,11,0.35); }

    .stat-value {
        font-size: 24px;
        font-weight: 800;
        color: var(--text);
        letter-spacing: -0.5px;
        line-height: 1;
        transition: color 0.25s ease;
    }
    .stat-card:hover .stat-value { color: rgb(var(--glow)); }
    .stat-label {
        font-size: 10px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        margin-top: 6px;
    }

    /* === TREE WRAP === */
    .tree-wrap {
        max-width: 1400px;
        margin: 0 auto;
        background: var(--bg-1);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 36px 28px;
        position: relative;
        box-shadow: var(--shadow-card);
    }
    .tree-wrap::before {
        /* нижняя 3D-тень */
        content: "";
        position: absolute;
        left: 16px; right: 16px; bottom: -10px;
        height: 14px;
        background: linear-gradient(180deg, rgba(15,23,42,0.08), rgba(15,23,42,0.02));
        border-radius: var(--radius);
        z-index: -1;
        filter: blur(10px);
    }
    .tree-wrap::after {
        /* верхний глянец */
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.9), transparent);
        pointer-events: none;
        opacity: 0.8;
    }

    .tree-header {
        text-align: center;
        margin-bottom: 36px;
        padding-bottom: 24px;
        border-bottom: 1px solid var(--line);
    }
    .tree-header h2 {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
        letter-spacing: -0.3px;
        margin: 0 0 8px;
    }
    .tree-header p {
        font-size: 12px;
        color: var(--muted);
        font-weight: 600;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* === LEVEL SECTION === */
    .level-section { margin-bottom: 36px; }
    .level-section:last-child { margin-bottom: 0; }

    .level-connector {
        width: 2px;
        height: 32px;
        margin: 0 auto 20px;
        background: linear-gradient(180deg,
            rgba(var(--glow), 0.6),
            rgba(var(--glow), 0.15));
        position: relative;
        border-radius: 2px;
    }
    .level-connector::before,
    .level-connector::after {
        content: '';
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: rgb(var(--glow));
        border: 2px solid var(--bg-1);
        box-shadow: 0 0 0 3px rgba(var(--glow), 0.2), 0 0 12px rgba(var(--glow), 0.5);
    }
    .level-connector::before { top: -5px; }
    .level-connector::after { bottom: -5px; }

    .level-header { text-align: center; margin-bottom: 20px; }

    .level-bar {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 9px 20px;
        border-radius: var(--radius);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        background: linear-gradient(180deg, var(--bg-1), var(--bg-2));
        color: var(--text);
        border: 1px solid var(--line);
        box-shadow:
            var(--shadow-card),
            inset 0 1px 0 rgba(255,255,255,0.6);
        position: relative;
        font-family: var(--font-sans);
    }
    .level-bar::before {
        /* левый акцент */
        content: "";
        position: absolute;
        top: 50%; left: -4px;
        transform: translateY(-50%);
        width: 4px; height: 20px;
        border-radius: 2px;
        background: rgb(var(--glow));
        box-shadow: 0 0 10px rgba(var(--glow), 0.6);
    }
    .level-bar .count {
        background: rgba(var(--glow), 0.15);
        color: rgb(var(--glow));
        padding: 3px 10px;
        border-radius: var(--radius);
        font-family: var(--font-mono);
        font-size: 10px;
        font-weight: 800;
        border: 1px solid rgba(var(--glow), 0.3);
    }

    /* === USERS GRID === */
    .users-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 16px;
    }

    /* === USER CARD (3D) === */
    .user-card {
        position: relative;
        background: var(--bg-1);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        overflow: visible;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        width: 220px;
        flex-shrink: 0;
        box-shadow: var(--shadow-card);
    }
    .user-card::before {
        /* нижняя 3D-тень */
        content: "";
        position: absolute;
        left: 10px; right: 10px; bottom: -7px;
        height: 10px;
        background: linear-gradient(180deg, rgba(15,23,42,0.08), rgba(15,23,42,0.02));
        border-radius: var(--radius);
        z-index: -1;
        filter: blur(8px);
        transition: all 0.4s ease;
    }
    .user-card::after {
        /* верхний глянец */
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.9), transparent);
        pointer-events: none;
        opacity: 0.8;
        z-index: 3;
    }
    .user-card:hover {
        transform: translateY(-5px);
        border-color: rgba(var(--glow), 0.45);
        box-shadow:
            0 18px 38px -12px rgba(var(--glow), 0.3),
            0 4px 10px rgba(15,23,42,0.06),
            0 0 0 3px rgba(var(--glow), 0.08);
    }
    .user-card:hover::before { bottom: -12px; }

    /* User photo */
    .user-photo {
        position: relative;
        width: 100%;
        height: 110px;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(var(--glow), 0.25), rgba(168, 85, 247, 0.15));
        border-bottom: 1px solid var(--line);
    }
    .user-photo img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .user-card:hover .user-photo img { transform: scale(1.06); }

    .user-photo-placeholder {
        width: 100%; height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        font-weight: 900;
        color: #fff;
        background: linear-gradient(135deg, rgb(var(--glow)), #8b5cf6);
        text-shadow: 0 2px 8px rgba(0,0,0,0.25);
        letter-spacing: -1px;
        position: relative;
    }
    .user-photo-placeholder::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(255,255,255,0.2), transparent 60%);
    }

    .user-photo-gradient {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 40%, rgba(0,0,0,0.35) 100%);
        pointer-events: none;
    }

    .photo-top {
        position: absolute;
        top: 8px; left: 8px; right: 8px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        z-index: 2;
        gap: 6px;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        border-radius: var(--radius);
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.25);
        box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        white-space: nowrap;
    }
    .status-pill.online {
        background: rgba(16, 185, 129, 0.95);
        color: #fff;
        border-color: rgba(16, 185, 129, 0.6);
    }
    .status-pill.offline {
        background: rgba(100, 116, 139, 0.9);
        color: #fff;
        border-color: rgba(100, 116, 139, 0.6);
    }

    .status-dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: currentColor;
        flex-shrink: 0;
        box-shadow: 0 0 0 2px rgba(255,255,255,0.3);
    }
    .status-dot.active {
        animation: dotPulse 2s infinite;
        box-shadow: 0 0 0 2px rgba(255,255,255,0.3), 0 0 8px currentColor;
    }
    @keyframes dotPulse {
        0%,100% { opacity: 1; transform: scale(1); }
        50%     { opacity: 0.5; transform: scale(1.15); }
    }

    .level-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        border-radius: var(--radius);
        font-size: 9px;
        font-weight: 800;
        font-family: var(--font-mono);
        background: rgba(var(--glow), 1);
        color: #fff;
        box-shadow:
            0 0 12px rgba(var(--glow), 0.6),
            inset 0 1px 0 rgba(255,255,255,0.3);
        border: 1px solid rgba(255,255,255,0.3);
        white-space: nowrap;
    }
    .level-pill svg { width: 10px; height: 10px; flex-shrink: 0; }

    /* User body */
    .user-body { padding: 12px 12px 14px; }

    .user-name {
        font-size: 14px;
        font-weight: 800;
        color: var(--text);
        text-align: center;
        margin: 0 0 8px;
        letter-spacing: -0.2px;
        line-height: 1.3;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .user-name a {
        color: inherit;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .user-name a:hover { color: rgb(var(--glow)); }

    .user-role {
        display: block;
        text-align: center;
        padding: 5px 10px;
        border-radius: var(--radius);
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: rgb(var(--glow));
        background: rgba(var(--glow), 0.10);
        border: 1px solid rgba(var(--glow), 0.25);
        margin-bottom: 8px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .admin-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: var(--radius);
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }
    html[data-theme="dark"] .admin-tag {
        color: #fbbf24;
    }
    .admin-tag-wrapper { text-align: center; margin-bottom: 8px; }

    .contact-row {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 5px 6px;
        font-size: 11px;
        color: var(--text);
        border-radius: var(--radius);
        transition: background 0.2s ease;
        font-weight: 500;
    }
    .contact-row:hover { background: var(--bg-2); }
    .contact-row i {
        width: 13px; height: 13px;
        color: rgb(var(--glow));
        flex-shrink: 0;
        font-size: 11px;
    }
    .contact-row span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-weight: 500;
    }

    .creator-box {
        padding: 7px 10px;
        background: var(--bg-2);
        border-radius: var(--radius);
        margin: 8px 0;
        border-left: 3px solid rgb(var(--glow));
        box-shadow: inset 0 1px 2px rgba(15,23,42,0.04);
    }
    .creator-box-label {
        font-size: 8px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-weight: 700;
        margin-bottom: 2px;
    }
    .creator-box-name {
        font-size: 11px;
        font-weight: 700;
        color: var(--text);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Action row */
    .action-row {
        display: flex;
        gap: 5px;
        padding-top: 10px;
        margin-top: 10px;
        border-top: 1px solid var(--line);
    }
    .act-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 7px 4px;
        border-radius: var(--radius);
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid var(--line);
        background: var(--bg-1);
        color: var(--muted);
        text-decoration: none;
        transition: all 0.25s ease;
        cursor: pointer;
        white-space: nowrap;
        font-family: var(--font-sans);
    }
    .act-btn i {
        width: 11px; height: 11px;
        font-size: 11px;
        flex-shrink: 0;
    }
    .act-btn.view:hover {
        background: rgba(var(--glow), 0.10);
        border-color: rgba(var(--glow), 0.4);
        color: rgb(var(--glow));
        box-shadow: 0 0 0 3px rgba(var(--glow), 0.08);
    }
    .act-btn.edit:hover {
        background: rgba(245, 158, 11, 0.10);
        border-color: rgba(245, 158, 11, 0.4);
        color: #d97706;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.08);
    }
    html[data-theme="dark"] .act-btn.edit:hover { color: #fbbf24; }
    .act-btn.delete:hover {
        background: rgba(239, 68, 68, 0.10);
        border-color: rgba(239, 68, 68, 0.4);
        color: #dc2626;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.08);
    }

    /* Empty state */
    .empty-wrap { text-align: center; padding: 60px 20px; }
    .empty-icon {
        width: 80px; height: 80px;
        border-radius: var(--radius);
        background: linear-gradient(135deg, rgba(var(--glow), 0.15), rgba(var(--glow), 0.05));
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        border: 1px solid rgba(var(--glow), 0.25);
        box-shadow:
            0 8px 20px rgba(var(--glow), 0.15),
            inset 0 1px 0 rgba(255,255,255,0.5);
        position: relative;
    }
    .empty-icon::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: var(--radius);
        background: linear-gradient(180deg, rgba(255,255,255,0.3), transparent 70%);
        pointer-events: none;
    }
    .empty-icon i {
        font-size: 34px;
        color: rgb(var(--glow));
        position: relative;
        z-index: 1;
    }
    .empty-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 8px;
    }
    .empty-desc {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 24px;
    }

    /* ============ RESPONSIVE ============ */
    @media (max-width: 1200px) {
        .users-tree-page { padding: 6px 0 38px; }
        .users-topbar { margin-bottom: 22px; gap: 14px; }
        .users-topbar-icon { width: 44px; height: 44px; }
        .users-topbar-icon svg { width: 22px; height: 22px; }
        .users-topbar-title { font-size: 20px; }
        .users-topbar-subtitle { font-size: 11px; }
        .btn-add { padding: 9px 18px; font-size: 11px; }
        .stats-row { margin-bottom: 24px; gap: 10px; }
        .stat-card { padding: 18px; gap: 14px; }
        .stat-icon { width: 38px; height: 38px; }
        .stat-value { font-size: 22px; }
        .stat-label { font-size: 9px; }
        .tree-wrap { padding: 32px 24px; }
        .tree-header { margin-bottom: 32px; padding-bottom: 20px; }
        .tree-header h2 { font-size: 20px; }
        .tree-header p { font-size: 11px; }
        .user-card { width: 210px; }
        .user-photo { height: 105px; }
        .user-photo-placeholder { font-size: 38px; }
    }

    @media (max-width: 992px) {
        .users-tree-page { padding: 4px 0 34px; }
        .users-topbar { margin-bottom: 20px; gap: 12px; }
        .users-topbar-left { gap: 12px; }
        .users-topbar-icon { width: 42px; height: 42px; }
        .users-topbar-icon svg { width: 21px; height: 21px; }
        .users-topbar-title { font-size: 18px; }
        .users-topbar-subtitle { font-size: 10px; }
        .btn-add { padding: 9px 16px; font-size: 11px; }
        .btn-add svg { width: 14px; height: 14px; }
        .stats-row { margin-bottom: 22px; gap: 10px; }
        .stat-card { padding: 16px; gap: 12px; }
        .stat-icon { width: 36px; height: 36px; }
        .stat-icon svg, .stat-icon i { width: 18px; height: 18px; }
        .stat-value { font-size: 20px; }
        .stat-label { font-size: 9px; }
        .tree-wrap { padding: 28px 20px; }
        .tree-header { margin-bottom: 28px; padding-bottom: 18px; }
        .tree-header h2 { font-size: 19px; }
        .tree-header p { font-size: 10px; }
        .level-section { margin-bottom: 30px; }
        .level-connector { height: 28px; margin-bottom: 18px; }
        .level-bar { padding: 8px 18px; font-size: 10px; gap: 10px; }
        .level-bar .count { font-size: 9px; padding: 2px 8px; }
        .users-grid { gap: 14px; }
        .user-card { width: 200px; }
        .user-photo { height: 100px; }
        .user-photo-placeholder { font-size: 36px; }
    }

    @media (max-width: 768px) {
        .users-tree-page { padding: 2px 0 30px; }
        .users-topbar {
            flex-direction: column;
            align-items: stretch;
            margin-bottom: 18px;
            gap: 12px;
        }
        .users-topbar-left { gap: 12px; width: 100%; }
        .users-topbar-icon { width: 40px; height: 40px; }
        .users-topbar-icon svg { width: 20px; height: 20px; }
        .users-topbar-title { font-size: 17px; }
        .users-topbar-subtitle { font-size: 10px; }
        .btn-add { width: 100%; justify-content: center; padding: 10px 16px; }
        .stats-row { margin-bottom: 20px; gap: 8px; }
        .stat-card { padding: 14px; gap: 10px; }
        .stat-icon { width: 34px; height: 34px; }
        .stat-icon svg, .stat-icon i { width: 17px; height: 17px; }
        .stat-value { font-size: 19px; }
        .stat-label { font-size: 9px; }
        .tree-wrap { padding: 24px 16px; }
        .tree-header { margin-bottom: 24px; padding-bottom: 16px; }
        .tree-header h2 { font-size: 18px; }
        .tree-header p { font-size: 10px; }
        .level-section { margin-bottom: 26px; }
        .level-connector { height: 24px; margin-bottom: 16px; }
        .level-bar { padding: 7px 14px; font-size: 10px; }
        .level-bar .count { font-size: 9px; padding: 2px 7px; }
        .users-grid { gap: 12px; }
        .user-card { width: 190px; }
        .user-photo { height: 95px; }
        .user-photo-placeholder { font-size: 34px; }
        .user-body { padding: 10px 10px 12px; }
        .user-name { font-size: 13px; }
        .user-role { font-size: 9px; padding: 4px 8px; }
        .contact-row { padding: 4px 5px; font-size: 10px; }
    }

    @media (max-width: 640px) {
        .users-topbar-title { font-size: 16px; }
        .users-topbar-subtitle { font-size: 9px; }
        .btn-add { padding: 9px 14px; font-size: 10px; }
        .stats-row { gap: 8px; }
        .stat-card { padding: 12px; gap: 8px; }
        .stat-icon { width: 32px; height: 32px; }
        .stat-value { font-size: 18px; }
        .tree-wrap { padding: 20px 14px; }
        .tree-header { margin-bottom: 20px; padding-bottom: 14px; }
        .tree-header h2 { font-size: 17px; }
        .users-grid { gap: 10px; }
        .user-card { width: 100%; max-width: 340px; }
        .user-photo { height: 120px; }
        .user-photo-placeholder { font-size: 42px; }
        .user-body { padding: 12px; }
        .user-name { font-size: 14px; }
        .user-role { font-size: 10px; }
        .contact-row { font-size: 11px; }
        .creator-box { padding: 7px 10px; }
    }

    @media (max-width: 480px) {
        .stat-card { padding: 10px; gap: 8px; }
        .stat-icon { width: 30px; height: 30px; }
        .stat-value { font-size: 17px; }
        .stat-label { font-size: 8px; }
        .tree-wrap { padding: 16px 12px; }
        .tree-header h2 { font-size: 16px; }
        .level-bar { padding: 6px 12px; font-size: 9px; }
        .level-bar .count { font-size: 8px; padding: 2px 6px; }
        .user-card { max-width: 100%; }
        .user-photo { height: 110px; }
        .user-photo-placeholder { font-size: 38px; }
        .user-name { font-size: 13px; }
        .user-role { font-size: 9px; }
        .action-row { flex-direction: column; }
        .act-btn { padding: 8px 6px; font-size: 9px; justify-content: center; }
        .empty-wrap { padding: 40px 14px; }
        .empty-icon { width: 70px; height: 70px; }
        .empty-icon i { font-size: 28px; }
        .empty-title { font-size: 17px; }
        .empty-desc { font-size: 12px; }
    }

    @media (max-width: 380px) {
        .stat-card { padding: 9px; gap: 6px; }
        .stat-icon { width: 28px; height: 28px; }
        .stat-icon svg, .stat-icon i { width: 15px; height: 15px; }
        .stat-value { font-size: 16px; }
        .tree-wrap { padding: 14px 10px; }
        .tree-header h2 { font-size: 15px; }
        .user-photo { height: 100px; }
        .user-photo-placeholder { font-size: 34px; }
        .user-name { font-size: 12px; }
        .contact-row { font-size: 10px; }
    }
</style>

<div class="users-tree-page">

    {{-- TOP BAR --}}
    <div class="users-topbar">
        <div class="users-topbar-left">
            <div class="users-topbar-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <div class="users-topbar-title">{{ $companyName }}</div>
                <div class="users-topbar-subtitle" data-i18n="teamStructure">Структура команды</div>
            </div>
        </div>

        @if($authUser->isAdmin())
        <a href="{{ route('users.create') }}" class="btn-add">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            <span data-i18n="addBtn">Добавить</span>
        </a>
        @endif
    </div>

    {{-- STATS ROW --}}
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon users">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <div class="stat-value">{{ $users->count() }}</div>
                <div class="stat-label" data-i18n="totalMembers">Всего участников</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon leaders">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <div class="stat-value">{{ $users->where('level', 1)->count() }}</div>
                <div class="stat-label" data-i18n="leaders">Лидеры</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon levels">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            </div>
            <div>
                <div class="stat-value">{{ $groupedByLevel->count() }}</div>
                <div class="stat-label" data-i18n="levels">Уровней</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon online">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </div>
            <div>
                <div class="stat-value">{{ $users->filter(fn($u) => $u->isOnline())->count() }}</div>
                <div class="stat-label" data-i18n="onlineNow">Сейчас онлайн</div>
            </div>
        </div>
    </div>

    {{-- TREE WRAP --}}
    <div class="tree-wrap">
        <div class="tree-header">
            <h2 data-i18n="hierarchyTitle">Иерархическое дерево</h2>
            <p data-i18n="hierarchySubtitle">Структура команды по уровням</p>
        </div>

        @foreach($groupedByLevel as $level => $levelUsers)
        @php
        $lvlClass = match($level) {
        1 => 'lvl-1', 2 => 'lvl-2', 3 => 'lvl-3',
        4 => 'lvl-4', 5 => 'lvl-5', default => 'lvl-default',
        };
        $lvlIcon = match($level) {
        1 => '', 2 => '', 3 => '', 4 => '', 5 => '', default => '',
        };
        @endphp

        @if(!$loop->first)
        <div class="level-connector"></div>
        @endif

        <div class="level-section">
            <div class="level-header">
                <div class="level-bar {{ $lvlClass }}">
                    <span>{{ $lvlIcon }} <span data-i18n="levelPrefix">Уровень</span> {{ $level }}</span>
                    <span class="count">{{ $levelUsers->count() }}</span>
                </div>
            </div>

            <div class="users-grid">
                @foreach($levelUsers as $user)
                @php
                $creator = ($user->created_by && $users->has($user->created_by)) ? $users->get($user->created_by) : null;
                $canEdit = $authUser->isAdmin() || ($user->id === $authUser->id);
                $canDelete = $authUser->isAdmin() && ($user->id !== $authUser->id);
                @endphp

                <div class="user-card">
                    <div class="user-photo">
                        @if($user->avatar)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                        @else
                        <div class="user-photo-placeholder">
                            {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                        </div>
                        @endif
                        <div class="user-photo-gradient"></div>

                        <div class="photo-top">
                            @if($user->isOnline())
                            <span class="status-pill online">
                                <span class="status-dot active"></span>
                                <span data-i18n="online">Онлайн</span>
                            </span>
                            @else
                            <span class="status-pill offline">
                                <span class="status-dot"></span>
                                <span data-i18n="offline">Офлайн</span>
                            </span>
                            @endif

                            <span class="level-pill">
                                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                L{{ $user->level }}
                            </span>
                        </div>
                    </div>

                    <div class="user-body">
                        <h3 class="user-name">
                            <a href="{{ route('users.show', $user->id) }}">{{ $user->name }}</a>
                        </h3>

                        <span class="user-role">{{ $user->role }}</span>

                        @if($user->isAdmin())
                        <div class="admin-tag-wrapper">
                            <span class="admin-tag" data-i18n="adminTag">Admin</span>
                        </div>
                        @endif

                        <div>
                            <div class="contact-row">
                                <i class="bi bi-envelope-fill"></i>
                                <span title="{{ $user->email }}">{{ $user->email }}</span>
                            </div>
                            <div class="contact-row">
                                <i class="bi bi-telephone-fill"></i>
                                <span>{{ $user->phone ?? '—' }}</span>
                            </div>
                        </div>

                        @if($creator)
                        <div class="creator-box">
                            <div class="creator-box-label" data-i18n="createdBy">Создан пользователем</div>
                            <div class="creator-box-name">{{ $creator->name }}</div>
                        </div>
                        @endif

                        <div class="action-row">
                            <a href="{{ route('users.show', $user->id) }}" class="act-btn view">
                                <i class="bi bi-eye-fill"></i>
                                <span data-i18n="view">Смотреть</span>
                            </a>

                            @if($canEdit)
                            <a href="{{ route('users.edit', $user->id) }}" class="act-btn edit">
                                <i class="bi bi-pencil-fill"></i>
                                <span data-i18n="edit">Изменить</span>
                            </a>
                            @endif

                            @if($canDelete)
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="flex: 1; margin: 0;" data-confirm-i18n="confirmDelete">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="act-btn delete" style="width: 100%;">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        @if($users->isEmpty())
        <div class="empty-wrap">
            <div class="empty-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="empty-title" data-i18n="noUsersTitle">Команда пуста</div>
            <div class="empty-desc" data-i18n="noUsers">Пока нет участников</div>
            @if($authUser->isAdmin())
            <a href="{{ route('users.create') }}" class="btn-add" style="display: inline-flex;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span data-i18n="addFirst">Добавить первого</span>
            </a>
            @endif
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const USERS_TREE_TRANSLATIONS = {
            ru: {
                addBtn: 'Добавить',
                online: 'Онлайн',
                offline: 'Офлайн',
                view: 'Смотреть',
                edit: 'Изменить',
                delete: 'Удалить',
                createdBy: 'Создан пользователем',
                teamStructure: 'Структура команды',
                totalMembers: 'Всего участников',
                leaders: 'Лидеры',
                levels: 'Уровней',
                onlineNow: 'Сейчас онлайн',
                hierarchyTitle: 'Иерархическое дерево',
                hierarchySubtitle: 'Структура команды по уровням',
                noUsers: 'Пока нет участников',
                noUsersTitle: 'Команда пуста',
                addFirst: 'Добавить первого',
                levelPrefix: 'Уровень',
                adminTag: 'Admin',
                confirmDelete: 'Удалить пользователя? Это действие необратимо.'
            },
            tj: {
                addBtn: 'Илова кардан',
                online: 'Онлайн',
                offline: 'Офлайн',
                view: 'Дидан',
                edit: 'Таҳрир',
                delete: 'Нест кардан',
                createdBy: 'Аз ҷониби',
                teamStructure: 'Сохтори даста',
                totalMembers: 'Ҳамагӣ аъзоён',
                leaders: 'Роҳбарон',
                levels: 'Сатҳҳо',
                onlineNow: 'Ҳозир онлайн',
                hierarchyTitle: 'Дарахти иерархия',
                hierarchySubtitle: 'Сохтори даста аз рӯи сатҳҳо',
                noUsers: 'Ҳоло иштирокчиён нестанд',
                noUsersTitle: 'Даста холӣ аст',
                addFirst: 'Аввалинро илова кунед',
                levelPrefix: 'Сатҳи',
                adminTag: 'Админ',
                confirmDelete: 'Корбарро нест мекунед? Ин амал бозгашт надорад.'
            },
            en: {
                addBtn: 'Add',
                online: 'Online',
                offline: 'Offline',
                view: 'View',
                edit: 'Edit',
                delete: 'Delete',
                createdBy: 'Created by',
                teamStructure: 'Team Structure',
                totalMembers: 'Total Members',
                leaders: 'Leaders',
                levels: 'Levels',
                onlineNow: 'Online Now',
                hierarchyTitle: 'Hierarchy Tree',
                hierarchySubtitle: 'Team structure by levels',
                noUsers: 'No participants yet',
                noUsersTitle: 'Team is empty',
                addFirst: 'Add first',
                levelPrefix: 'Level',
                adminTag: 'Admin',
                confirmDelete: 'Delete this user? This action cannot be undone.'
            }
        };

        function applyUsersTreeTranslations(lang) {
            const dict = USERS_TREE_TRANSLATIONS[lang] || USERS_TREE_TRANSLATIONS.ru;

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

            document.querySelectorAll('[data-confirm-i18n]').forEach(el => {
                const key = el.getAttribute('data-confirm-i18n');
                const message = dict[key] || 'Are you sure?';
                const form = el.tagName === 'FORM' ? el : el.closest('form');
                if (form) {
                    form.onsubmit = (e) => {
                        if (!confirm(message)) e.preventDefault();
                    };
                }
            });
        }

        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applyUsersTreeTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applyUsersTreeTranslations(lang);
        });
        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applyUsersTreeTranslations(e.newValue);
            }
        });
    });
</script>

@endsection