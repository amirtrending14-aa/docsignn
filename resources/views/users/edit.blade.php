@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    /* ============================================================ */
    /* ===== EDIT USER PAGE (Tabler 3D Light) ===== */
    /* ============================================================ */
    .edit-user-page {
        min-height: calc(100vh - 56px);
        padding: 8px clamp(16px, 2vw, 32px) 40px;
        color: var(--text);
        font-family: var(--font-sans);
        position: relative;
    }

    /* Фоновые пятна (только light theme) */
    html[data-theme="light"] .edit-user-page::before,
    html[data-theme="light"] .edit-user-page::after {
        content: "";
        position: fixed;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
        filter: blur(120px);
        opacity: 0.4;
    }
    html[data-theme="light"] .edit-user-page::before {
        top: -200px; left: -100px;
        width: 500px; height: 500px;
        background: radial-gradient(circle, rgba(var(--glow), 0.2) 0%, transparent 70%);
        animation: editBlobFloat 22s ease-in-out infinite;
    }
    html[data-theme="light"] .edit-user-page::after {
        bottom: -200px; right: -100px;
        width: 600px; height: 600px;
        background: radial-gradient(circle, rgba(168, 85, 247, 0.12) 0%, transparent 70%);
        animation: editBlobFloat 28s ease-in-out infinite reverse;
    }
    @keyframes editBlobFloat {
        0%,100% { transform: translate(0,0) scale(1); }
        50%     { transform: translate(30px,-30px) scale(1.05); }
    }

    .edit-user-page > * { position: relative; z-index: 1; }

    .edit-wrap {
        width: 100%;
        max-width: 1480px;
        margin: 0 auto;
    }

    /* === TOP BAR (3D) === */
    .edit-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 24px;
        background: var(--bg-1);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 18px 22px;
        box-shadow: var(--shadow-card);
        position: relative;
    }
    .edit-topbar::before {
        content: "";
        position: absolute;
        left: 12px; right: 12px; bottom: -8px;
        height: 12px;
        background: linear-gradient(180deg, rgba(15,23,42,0.08), rgba(15,23,42,0.02));
        border-radius: var(--radius);
        z-index: -1;
        filter: blur(10px);
    }
    .edit-topbar::after {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.9), transparent);
        pointer-events: none;
        opacity: 0.8;
    }

    .edit-topbar-left {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
        flex: 1;
    }

    .edit-topbar-icon {
        width: 48px; height: 48px;
        border-radius: var(--radius);
        background: linear-gradient(135deg, rgb(var(--glow)), rgba(var(--glow), 0.7));
        display: grid;
        place-items: center;
        flex-shrink: 0;
        box-shadow:
            0 6px 16px rgba(var(--glow), 0.35),
            inset 0 1px 0 rgba(255,255,255,0.35);
        position: relative;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .edit-topbar-icon::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: var(--radius);
        background: linear-gradient(180deg, rgba(255,255,255,0.25), transparent 60%);
        pointer-events: none;
    }
    .edit-topbar-icon svg {
        width: 24px; height: 24px;
        color: #fff;
        position: relative; z-index: 1;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.2));
    }

    .edit-topbar-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--text);
        letter-spacing: -0.3px;
        line-height: 1.2;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .edit-topbar-title .user-name-accent {
        color: rgb(var(--glow));
    }

    .edit-topbar-subtitle {
        font-size: 12px;
        color: var(--muted);
        font-weight: 600;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }

    .meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: var(--radius);
        background: rgba(var(--glow), 0.10);
        border: 1px solid rgba(var(--glow), 0.25);
        color: rgb(var(--glow));
        font-family: var(--font-mono);
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
        letter-spacing: 0.3px;
    }
    .meta-pill svg { flex-shrink: 0; }

    /* === BACK BUTTON (3D) === */
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: var(--bg-1);
        color: var(--muted);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        transition: all 0.25s ease;
        white-space: nowrap;
        flex-shrink: 0;
        box-shadow: var(--shadow-card);
        position: relative;
        font-family: var(--font-sans);
    }
    .btn-back::before {
        content: "";
        position: absolute;
        left: 6px; right: 6px; bottom: -4px;
        height: 6px;
        background: linear-gradient(180deg, rgba(15,23,42,0.06), rgba(15,23,42,0.02));
        border-radius: var(--radius);
        z-index: -1;
        filter: blur(4px);
        transition: all 0.25s ease;
    }
    .btn-back:hover {
        color: rgb(var(--glow));
        border-color: rgba(var(--glow), 0.4);
        transform: translateX(-2px);
        box-shadow: 0 4px 12px rgba(var(--glow), 0.15);
    }
    .btn-back:hover::before { bottom: -6px; }
    .btn-back svg { width: 14px; height: 14px; }

    /* === FORM CARD (3D) === */
    .form-card {
        background: var(--bg-1);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 32px 28px;
        position: relative;
        box-shadow: var(--shadow-card);
    }
    .form-card > #editUserForm {
        display: flex;
        flex-direction: column;
        gap: 28px;
    }
    .edit-form-columns {
        column-count: 2;
        column-gap: 36px;
        column-fill: balance;
    }
    .edit-form-column {
        display: contents;
    }
    .edit-form-column > .form-section {
        display: inline-block;
        width: 100%;
        margin: 0 0 28px;
        break-inside: avoid;
        vertical-align: top;
    }
    .form-card > #editUserForm > .submit-wrap {
        margin-top: 0;
    }
    .form-card::before {
        content: "";
        position: absolute;
        left: 16px; right: 16px; bottom: -10px;
        height: 14px;
        background: linear-gradient(180deg, rgba(15,23,42,0.08), rgba(15,23,42,0.02));
        border-radius: var(--radius);
        z-index: -1;
        filter: blur(10px);
    }
    .form-card::after {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.9), transparent);
        pointer-events: none;
        opacity: 0.8;
    }

    .form-section { margin-bottom: 28px; }
    .form-section:last-child { margin-bottom: 0; }

    .section-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.3px;
        color: var(--muted);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: var(--font-sans);
    }
    .section-title::before {
        content: "";
        width: 4px;
        height: 16px;
        border-radius: 2px;
        background: rgb(var(--glow));
        box-shadow: 0 0 0 3px rgba(var(--glow), 0.15), 0 0 10px rgba(var(--glow), 0.5);
        flex-shrink: 0;
    }

    /* === AVATAR BLOCK (3D) === */
    .avatar-block {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 22px;
        background: var(--bg-2);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        transition: all 0.3s ease;
        box-shadow: inset 0 1px 2px rgba(15,23,42,0.04);
    }
    .avatar-block:hover {
        border-color: rgba(var(--glow), 0.4);
        box-shadow:
            inset 0 1px 2px rgba(15,23,42,0.04),
            0 0 0 3px rgba(var(--glow), 0.08);
    }

    .avatar-box {
        width: 110px; height: 110px;
        border-radius: var(--radius);
        overflow: hidden;
        position: relative;
        flex-shrink: 0;
        background: linear-gradient(135deg, rgb(var(--glow)), #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow:
            0 8px 24px rgba(var(--glow), 0.35),
            inset 0 1px 0 rgba(255,255,255,0.3);
        border: 1px solid rgba(255,255,255,0.2);
    }
    .avatar-box::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(255,255,255,0.2), transparent 60%);
        pointer-events: none;
        z-index: 3;
    }
    .avatar-box img {
        width: 100%; height: 100%;
        object-fit: cover;
        position: absolute;
        inset: 0;
        z-index: 2;
    }
    .avatar-letter {
        font-size: 46px;
        font-weight: 900;
        font-style: italic;
        color: #fff;
        text-shadow: 0 2px 8px rgba(0,0,0,0.2);
        letter-spacing: -1px;
    }

    .avatar-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
        opacity: 0;
        transition: opacity 0.25s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 4;
    }
    .avatar-box:hover .avatar-overlay { opacity: 1; }
    .avatar-overlay-content {
        text-align: center;
        color: #fff;
    }
    .avatar-overlay-content svg {
        width: 28px; height: 28px;
        margin: 0 auto 4px;
        display: block;
        color: rgb(var(--glow));
        filter: drop-shadow(0 0 8px rgba(var(--glow), 0.6));
    }
    .avatar-overlay-content span {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .avatar-info { flex: 1; min-width: 0; }
    .avatar-info h3 {
        font-size: 15px;
        font-weight: 800;
        color: var(--text);
        margin: 0 0 4px;
        letter-spacing: -0.2px;
    }
    .avatar-info p {
        font-size: 12px;
        color: var(--muted);
        margin: 0;
        font-weight: 500;
    }
    .avatar-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
        flex-wrap: wrap;
    }

    .btn-upload {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 14px;
        background: linear-gradient(180deg, rgb(var(--glow)), rgba(var(--glow), 0.85));
        color: #fff;
        border-radius: var(--radius);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        cursor: pointer;
        transition: all 0.25s ease;
        box-shadow:
            0 4px 12px rgba(var(--glow), 0.35),
            inset 0 1px 0 rgba(255,255,255,0.3);
        border: 1px solid rgba(var(--glow), 0.6);
        font-family: var(--font-sans);
    }
    .btn-upload:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(var(--glow), 0.45);
        filter: brightness(1.05);
    }
    .btn-upload svg { width: 13px; height: 13px; }

    .btn-remove {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 14px;
        background: var(--bg-1);
        color: #dc2626;
        border: 1px solid rgba(220, 38, 38, 0.3);
        border-radius: var(--radius);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        cursor: pointer;
        transition: all 0.25s ease;
        font-family: var(--font-sans);
    }
    html[data-theme="dark"] .btn-remove { color: #fca5a5; }
    .btn-remove:hover {
        background: rgba(239, 68, 68, 0.10);
        border-color: rgba(239, 68, 68, 0.5);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    .btn-remove svg { width: 13px; height: 13px; }
    .avatar-file-name {
        font-size: 12px;
        color: rgb(var(--glow));
        margin-top: 10px;
        font-weight: 700;
        word-break: break-word;
    }

    /* === FIELDS === */
    .field-group { margin-bottom: 18px; }
    .field-group:last-child { margin-bottom: 0; }
    .field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .field-label {
        display: block;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 11px;
        font-weight: 700;
        color: var(--muted);
        margin-bottom: 8px;
        font-family: var(--font-sans);
    }
    .field-label .required {
        color: rgb(var(--glow));
        margin-left: 2px;
    }
    .field-label .hint {
        color: var(--muted);
        text-transform: none;
        font-weight: 500;
        letter-spacing: 0;
        font-size: 11px;
        margin-left: 4px;
        opacity: 0.7;
    }

    .input-custom {
        width: 100%;
        background: var(--bg-2) !important;
        border: 1px solid var(--line) !important;
        color: var(--text) !important;
        font-size: 14px !important;
        font-weight: 500;
        padding: 12px 16px !important;
        border-radius: var(--radius) !important;
        transition: all 0.25s ease;
        font-family: var(--font-sans);
        box-sizing: border-box;
        box-shadow: inset 0 1px 2px rgba(15,23,42,0.04);
    }
    .input-custom:focus {
        border-color: rgba(var(--glow), 0.6) !important;
        background: var(--bg-1) !important;
        box-shadow:
            inset 0 1px 2px rgba(15,23,42,0.04),
            0 0 0 3px rgba(var(--glow), 0.15),
            0 4px 12px rgba(var(--glow), 0.12) !important;
        outline: none !important;
    }
    .input-custom::placeholder {
        color: var(--muted) !important;
        opacity: 0.6;
    }
    .input-custom option {
        background: var(--bg-1);
        color: var(--text);
    }
    select.input-custom {
        appearance: none;
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23626b76' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 16px center !important;
        padding-right: 40px !important;
    }

    .note-text {
        font-size: 11px;
        color: var(--muted);
        margin-top: 8px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .note-text::before {
        content: "ⓘ";
        color: rgb(var(--glow));
        font-size: 12px;
    }

    /* === READONLY BOX === */
    .readonly-box {
        background: var(--bg-2);
        border: 1px dashed rgba(var(--glow), 0.3);
        border-radius: var(--radius);
        padding: 14px 16px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        box-shadow: inset 0 1px 2px rgba(15,23,42,0.04);
    }
    .readonly-box .lock-icon {
        width: 32px; height: 32px;
        border-radius: var(--radius);
        background: rgba(var(--glow), 0.12);
        border: 1px solid rgba(var(--glow), 0.25);
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }
    .readonly-box .lock-icon svg {
        width: 14px; height: 14px;
        color: rgb(var(--glow));
    }
    .readonly-box .meta-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 11px;
        background: var(--bg-1);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        color: rgb(var(--glow));
        font-size: 12px;
        font-weight: 700;
        font-family: var(--font-mono);
    }

    /* === PASSWORD === */
    .password-wrap { position: relative; }
    .password-toggle {
        position: absolute;
        top: 50%; right: 12px;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: var(--muted);
        cursor: pointer;
        padding: 6px;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    .password-toggle:hover {
        color: rgb(var(--glow));
        background: rgba(var(--glow), 0.1);
    }
    .password-toggle svg { width: 18px; height: 18px; }

    /* === SUBMIT === */
    .submit-wrap {
        padding-top: 24px;
        margin-top: 28px;
        border-top: 1px solid var(--line);
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 36px;
        background: linear-gradient(180deg, rgb(var(--glow)), rgba(var(--glow), 0.85));
        color: #fff;
        border-radius: var(--radius);
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        transition: all 0.3s ease;
        box-shadow:
            0 8px 24px rgba(var(--glow), 0.35),
            inset 0 1px 0 rgba(255,255,255,0.3);
        border: 1px solid rgba(var(--glow), 0.6);
        cursor: pointer;
        white-space: nowrap;
        position: relative;
        font-family: var(--font-sans);
    }
    .btn-submit::before {
        content: "";
        position: absolute;
        left: 10px; right: 10px; bottom: -6px;
        height: 8px;
        background: linear-gradient(180deg, rgba(var(--glow),0.35), transparent);
        border-radius: var(--radius);
        z-index: -1;
        filter: blur(6px);
        transition: all 0.3s ease;
    }
    .btn-submit::after {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.7), transparent);
        pointer-events: none;
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow:
            0 14px 36px rgba(var(--glow), 0.45),
            inset 0 1px 0 rgba(255,255,255,0.4);
        filter: brightness(1.08);
    }
    .btn-submit:hover::before { bottom: -10px; }
    .btn-submit:active { transform: translateY(1px); }
    .btn-submit:active::before { bottom: -2px; }
    .btn-submit svg { width: 18px; height: 18px; }

    .btn-delete {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 28px;
        background: var(--bg-1);
        color: #dc2626;
        border: 1px solid rgba(220, 38, 38, 0.3);
        border-radius: var(--radius);
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        transition: all 0.3s ease;
        cursor: pointer;
        white-space: nowrap;
        position: relative;
        font-family: var(--font-sans);
        box-shadow: var(--shadow-card);
    }
    html[data-theme="dark"] .btn-delete { color: #fca5a5; }
    .btn-delete::before {
        content: "";
        position: absolute;
        left: 10px; right: 10px; bottom: -5px;
        height: 7px;
        background: linear-gradient(180deg, rgba(220,38,38,0.15), transparent);
        border-radius: var(--radius);
        z-index: -1;
        filter: blur(5px);
        transition: all 0.3s ease;
    }
    .btn-delete::after {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.7), transparent);
        pointer-events: none;
    }
    .btn-delete:hover {
        background: rgba(239, 68, 68, 0.10);
        border-color: rgba(239, 68, 68, 0.6);
        box-shadow: 0 10px 28px rgba(239, 68, 68, 0.25);
        transform: translateY(-2px);
    }
    .btn-delete:hover::before { bottom: -8px; }
    .btn-delete:active { transform: translateY(1px); }
    .btn-delete:active::before { bottom: -2px; }
    .btn-delete svg { width: 18px; height: 18px; }

    /* === ROLE TOGGLE (3D) === */
    .role-toggle {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 14px;
    }
    .role-toggle-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 16px 12px;
        border-radius: var(--radius);
        border: 2px solid var(--line);
        background: var(--bg-2);
        cursor: pointer;
        transition: all 0.25s ease;
        user-select: none;
        text-align: center;
        position: relative;
        box-shadow: var(--shadow-card);
    }
    .role-toggle-btn::before {
        content: "";
        position: absolute;
        left: 8px; right: 8px; bottom: -5px;
        height: 7px;
        background: linear-gradient(180deg, rgba(15,23,42,0.06), transparent);
        border-radius: var(--radius);
        z-index: -1;
        filter: blur(4px);
        transition: all 0.25s ease;
    }
    .role-toggle-btn::after {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent);
        pointer-events: none;
        opacity: 0.8;
    }
    .role-toggle-btn:hover {
        border-color: rgba(var(--glow), 0.4);
        transform: translateY(-2px);
    }
    .role-toggle-btn:hover::before { bottom: -8px; }
    .role-toggle-btn.active {
        border-color: rgba(var(--glow), 0.8);
        background: rgba(var(--glow), 0.08);
        box-shadow:
            0 6px 18px rgba(var(--glow), 0.2),
            inset 0 1px 0 rgba(255,255,255,0.6);
    }
    .role-toggle-btn .rt-icon { font-size: 22px; line-height: 1; }
    .role-toggle-btn .rt-label {
        font-size: 13px;
        font-weight: 800;
        color: var(--text);
    }
    .role-toggle-btn .rt-desc {
        font-size: 10px;
        color: var(--muted);
        font-weight: 600;
        margin-top: 2px;
    }
    .role-toggle-btn.active .rt-label { color: rgb(var(--glow)); }

    .role-custom-input { display: none; margin-top: 12px; }
    .role-custom-input.visible { display: block; animation: fadeIn 0.25s ease; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .role-custom-wrap { position: relative; }
    .role-custom-wrap .input-custom { padding-left: 36px !important; }
    .role-custom-icon {
        position: absolute;
        left: 12px; top: 50%;
        transform: translateY(-50%);
        color: rgb(var(--glow));
        font-size: 14px;
        pointer-events: none;
    }

    /* === SALARY === */
    .salary-wrap { position: relative; }
    .salary-icon {
        position: absolute;
        left: 14px; top: 50%;
        transform: translateY(-50%);
        color: rgb(var(--glow));
        font-size: 18px;
        pointer-events: none;
        z-index: 2;
        filter: drop-shadow(0 0 6px rgba(var(--glow), 0.35));
    }
    .salary-wrap .input-custom {
        padding-left: 44px !important;
        padding-right: 70px !important;
        font-weight: 700 !important;
        letter-spacing: 0.3px;
    }
    .salary-suffix {
        position: absolute;
        right: 16px; top: 50%;
        transform: translateY(-50%);
        font-size: 10px;
        font-weight: 800;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        pointer-events: none;
        background: var(--bg-1);
        padding: 4px 8px;
        border-radius: var(--radius);
        border: 1px solid var(--line);
        font-family: var(--font-mono);
    }

    /* === TOGGLE SCAN === */
    .toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        background: var(--bg-2);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        transition: all 0.3s ease;
        cursor: pointer;
        box-shadow: inset 0 1px 2px rgba(15,23,42,0.04);
    }
    .toggle-row:hover { border-color: rgba(var(--glow), 0.35); }
    .toggle-row.active {
        border-color: rgba(var(--glow), 0.6);
        background: rgba(var(--glow), 0.06);
        box-shadow:
            inset 0 1px 2px rgba(15,23,42,0.04),
            0 0 0 3px rgba(var(--glow), 0.1);
    }

    .toggle-left {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1;
        min-width: 0;
    }

    .toggle-icon-box {
        width: 40px; height: 40px;
        border-radius: var(--radius);
        background: linear-gradient(135deg, rgba(var(--glow), 0.18), rgba(168, 85, 247, 0.12));
        border: 1px solid rgba(var(--glow), 0.3);
        display: grid;
        place-items: center;
        flex-shrink: 0;
        color: rgb(var(--glow));
        font-size: 20px;
        transition: all 0.3s ease;
        position: relative;
    }
    .toggle-icon-box::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: var(--radius);
        background: linear-gradient(180deg, rgba(255,255,255,0.3), transparent 70%);
        pointer-events: none;
    }
    .toggle-row.active .toggle-icon-box {
        background: linear-gradient(135deg, rgb(var(--glow)), rgba(var(--glow), 0.7));
        color: #fff;
        box-shadow:
            0 4px 12px rgba(var(--glow), 0.45),
            inset 0 1px 0 rgba(255,255,255,0.3);
        border-color: rgba(255,255,255,0.25);
    }

    .toggle-title {
        font-size: 14px;
        font-weight: 800;
        color: var(--text);
        margin: 0;
        line-height: 1.2;
    }
    .toggle-row.active .toggle-title { color: rgb(var(--glow)); }
    .toggle-desc {
        font-size: 11px;
        color: var(--muted);
        font-weight: 500;
        margin-top: 3px;
    }

    .toggle-switch {
        position: relative;
        width: 52px; height: 28px;
        flex-shrink: 0;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0; height: 0;
        position: absolute;
    }
    .toggle-slider {
        position: absolute;
        inset: 0;
        background: var(--bg-1);
        border: 1px solid var(--line);
        border-radius: 50px;
        transition: all 0.3s ease;
        cursor: pointer;
        box-shadow: inset 0 1px 3px rgba(15,23,42,0.08);
    }
    .toggle-slider::before {
        content: "";
        position: absolute;
        height: 20px; width: 20px;
        left: 3px; top: 50%;
        transform: translateY(-50%);
        background: var(--muted);
        border-radius: 50%;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    .toggle-switch input:checked + .toggle-slider {
        background: linear-gradient(135deg, rgb(var(--glow)), rgba(var(--glow), 0.8));
        border-color: rgba(var(--glow), 0.8);
        box-shadow: 0 0 12px rgba(var(--glow), 0.35), inset 0 1px 3px rgba(0,0,0,0.05);
    }
    .toggle-switch input:checked + .toggle-slider::before {
        transform: translateY(-50%) translateX(24px);
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2), 0 0 0 2px rgba(255,255,255,0.3);
    }

    /* === INFO BANNER === */
    .info-banner {
        background: rgba(var(--glow), 0.06);
        border: 1px solid rgba(var(--glow), 0.2);
        border-radius: var(--radius);
        padding: 16px 18px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        position: relative;
        overflow: hidden;
    }
    .info-banner::before {
        content: "";
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3px;
        background: rgb(var(--glow));
        box-shadow: 0 0 10px rgba(var(--glow), 0.6);
    }
    .info-banner-icon {
        width: 36px; height: 36px;
        border-radius: var(--radius);
        background: rgba(var(--glow), 0.15);
        border: 1px solid rgba(var(--glow), 0.3);
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }
    .info-banner-icon svg {
        width: 18px; height: 18px;
        color: rgb(var(--glow));
    }
    .info-banner-text {
        font-size: 13px;
        color: var(--text);
        font-weight: 500;
        line-height: 1.5;
    }
    .info-banner-text strong {
        color: rgb(var(--glow));
        font-weight: 700;
    }

    /* === VALIDATION === */
    .validation-errors {
        background: rgba(239, 68, 68, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #dc2626;
        padding: 15px;
        border-radius: var(--radius);
        margin-bottom: 20px;
        font-size: 13px;
        box-shadow: inset 0 1px 2px rgba(239, 68, 68, 0.05);
    }
    html[data-theme="dark"] .validation-errors { color: #fca5a5; }
    .validation-errors ul { margin: 5px 0 0 20px; padding: 0; }

    /* === DELETE ZONE === */
    .delete-zone {
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px dashed var(--line);
        display: flex;
        justify-content: center;
    }

    /* ============ RESPONSIVE ============ */
    @media (max-width: 1100px) {
        .edit-form-columns { column-count: 1; }
        .edit-form-column > .form-section { margin-bottom: 24px; }
    }
    @media (max-width: 768px) {
        .edit-user-page { padding: 6px 12px 36px; }
        .edit-topbar {
            flex-direction: column;
            align-items: stretch;
            padding: 16px 18px;
        }
        .btn-back { width: 100%; justify-content: center; }
        .avatar-block {
            flex-direction: column;
            text-align: center;
            padding: 20px;
        }
        .avatar-info { text-align: center; width: 100%; }
        .avatar-actions { justify-content: center; }
        .form-card { padding: 24px 18px; }
        .field-row { grid-template-columns: 1fr; }
        .toggle-row {
            flex-direction: column;
            align-items: flex-start;
            padding: 16px;
        }
        .toggle-switch { align-self: flex-end; margin-top: -30px; }
        .submit-wrap { flex-direction: column; }
        .btn-submit, .btn-delete { width: 100%; justify-content: center; }
    }
    @media (max-width: 480px) {
        .edit-topbar-title { font-size: 17px; }
        .form-card { padding: 20px 14px; }
        .avatar-box { width: 90px; height: 90px; }
        .avatar-letter { font-size: 38px; }
        .role-toggle { gap: 8px; }
        .role-toggle-btn { padding: 12px 8px; }
        .role-toggle-btn .rt-label { font-size: 12px; }
        .role-toggle-btn .rt-icon { font-size: 20px; }
        .btn-submit { padding: 12px 28px; font-size: 12px; }
        .btn-delete { padding: 12px 24px; font-size: 12px; }
    }
</style>

<div class="edit-user-page">
    <div class="edit-wrap">
        <div class="edit-topbar">
            <div class="edit-topbar-left">
                <div class="edit-topbar-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div style="min-width: 0; flex: 1;">
                    <div class="edit-topbar-title">
                        <span data-i18n="editUser">Редактировать</span>:
                        <span class="user-name-accent">{{ $user->name }}</span>
                    </div>
                    <div class="edit-topbar-subtitle">
                        <span class="meta-pill">#{{ $user->id }}</span>
                        <span class="meta-pill">
                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 11px; height: 11px;"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span data-i18n="level">Уровень</span> {{ $user->level }}
                        </span>
                        <span class="meta-pill">{{ $user->role }}</span>
                    </div>
                </div>
            </div>

            <a href="{{ route('users.index') }}" class="btn-back">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                <span data-i18n="backBtn">Назад</span>
            </a>
        </div>

        <div class="form-card">
            @if ($errors->any())
                <div class="validation-errors">
                    <strong data-i18n="checkData">Проверьте данные:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('users.update', $user->id) }}" enctype="multipart/form-data" id="editUserForm">
                @csrf
                @method('PUT')

                <div class="edit-form-columns">
                    <div class="edit-form-column">
                {{-- АВАТАР --}}
                <div class="form-section">
                    <div class="section-title" data-i18n="photoSection">Фото профиля</div>
                    <div class="avatar-block">
                        <div class="avatar-box">
                            @if($user->avatar && !old('remove_avatar'))
                                <img id="avatarPreview" src="{{ $user->avatar_url }}">
                                <span id="avatarLetter" class="avatar-letter" style="display: none;">{{ Str::upper(Str::substr(old('name', $user->name), 0, 1)) }}</span>
                            @else
                                <img id="avatarPreview" src="" style="display: none;">
                                <span id="avatarLetter" class="avatar-letter">{{ Str::upper(Str::substr(old('name', $user->name), 0, 1)) }}</span>
                            @endif

                            <label for="avatarInput" class="avatar-overlay">
                                <div class="avatar-overlay-content">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span data-i18n="change">Изменить</span>
                                </div>
                            </label>

                            <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
                            <input type="hidden" id="removeAvatarFlag" name="remove_avatar" value="{{ old('remove_avatar', 0) }}">
                        </div>

                        <div class="avatar-info">
                            <h3 data-i18n="photo">Фото</h3>
                            <p data-i18n="photoDesc">JPG, PNG до 2MB</p>
                            <div class="avatar-actions">
                                <label for="avatarInput" class="btn-upload">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <span data-i18n="upload">Загрузить</span>
                                </label>
                                <button type="button" id="removeBtn" onclick="removeAvatar()" class="btn-remove" style="{{ (!$user->avatar || old('remove_avatar')) ? 'display: none;' : '' }}">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span data-i18n="remove">Удалить</span>
                                </button>
                            </div>
                            <p id="fileNameDisplay" class="avatar-file-name"></p>
                        </div>
                    </div>
                </div>

                {{-- ОСНОВНЫЕ ДАННЫЕ --}}
                <div class="form-section">
                    <div class="section-title" data-i18n="mainInfo">Основная информация</div>

                    <div class="field-group">
                        <label class="field-label">
                            <span data-i18n="fullName">Полное имя</span>
                            <span class="required">*</span>
                        </label>
                        <input name="name" type="text" required class="input-custom" value="{{ old('name', $user->name) }}" id="nameInput">
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">
                                <span data-i18n="email">Email</span>
                                <span class="required">*</span>
                            </label>
                            <input name="email" type="email" required class="input-custom" value="{{ old('email', $user->email) }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">
                                <span data-i18n="phone">Телефон</span>
                                <span class="required">*</span>
                            </label>
                            <input name="phone" type="text" id="phone" required class="input-custom" value="{{ old('phone', $user->phone ?? '+992 ') }}">
                        </div>
                    </div>
                </div>

                    </div>
                    <div class="edit-form-column">
                {{-- РОЛЬ И УРОВЕНЬ --}}
                <div class="form-section">
                    <div class="section-title" data-i18n="accessSection">Роль и уровень</div>

                    @if((int)$user->created_by === auth()->id())
                        <div class="field-group">
                            <label class="field-label">
                                <span data-i18n="role">Роль</span>
                                <span class="required">*</span>
                            </label>

                            <div class="role-toggle">
                                <div class="role-toggle-btn {{ old('role', $user->role) === 'admin' ? 'active' : '' }}" id="btnRoleAdmin" onclick="setRole('admin')">
                                    <div>
                                        <div class="rt-icon">🛡️</div>
                                        <div class="rt-label" data-i18n="role_admin">Администратор</div>
                                        <div class="rt-desc" data-i18n="role_admin_desc">Полный доступ</div>
                                    </div>
                                </div>
                                <div class="role-toggle-btn {{ old('role', $user->role) !== 'admin' ? 'active' : '' }}" id="btnRoleCustom" onclick="setRole('custom')">
                                    <div>
                                        <div class="rt-icon">✏️</div>
                                        <div class="rt-label" data-i18n="role_custom">Своя роль</div>
                                        <div class="rt-desc" data-i18n="role_custom_desc">Вписать вручную</div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="role" id="roleHidden" value="{{ old('role', $user->role) }}">

                            <div class="role-custom-input {{ old('role', $user->role) !== 'admin' ? 'visible' : '' }}" id="roleCustomBlock">
                                <div class="role-custom-wrap">
                                    <i class="bi bi-pencil-fill role-custom-icon"></i>
                                    <input type="text" class="input-custom" id="roleCustomInput"
                                           placeholder="Например: менеджер, бухгалтер, директор..."
                                           value="{{ old('role', $user->role) !== 'admin' ? old('role', $user->role) : '' }}"
                                           {{ old('role', $user->role) !== 'admin' ? 'required' : '' }}
                                           autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label">
                                <span data-i18n="level">Уровень</span> (1–20)
                                <span class="required">*</span>
                            </label>
                            <select name="level" required class="input-custom" id="levelSelect">
                                @for($i = 1; $i <= 20; $i++)
                                    <option value="{{ $i }}" {{ old('level', $user->level) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            <p class="note-text" data-i18n="levelNote">Уровень 1 = администратор, 2–20 = сотрудники</p>
                        </div>
                    @else
                        <input type="hidden" name="role" value="{{ $user->role }}">
                        <input type="hidden" name="level" value="{{ $user->level }}">
                        <div class="readonly-box">
                            <div class="lock-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <span class="meta-item">
                                <span data-i18n="role">Роль</span>: {{ $user->role }}
                            </span>
                            <span class="meta-item">
                                <span data-i18n="level">Уровень</span>: {{ $user->level }}
                            </span>
                        </div>
                    @endif
                </div>

                {{-- КОМПАНИЯ, ЗАРПЛАТА, СКАН ЛИЦА --}}
                <div class="form-section">
                    <div class="section-title" data-i18n="companySection">Компания и настройки</div>

                    @if($selectedCompany ?? false)
                        <input type="hidden" name="company_id" value="{{ $selectedCompany->id }}">
                    @endif

                    <div class="info-banner" style="margin-bottom: 16px;">
                        <div class="info-banner-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="info-banner-text">
                            <span data-i18n="autoCompany">Компания назначается автоматически:</span>
                            <strong>{{ ($selectedCompany ?? null)?->name ?? auth()->user()->companyName ?? $user->companyName ?? '—' }}</strong>
                        </div>
                    </div>

                    {{-- ЗАРПЛАТА --}}
                    <div class="field-group">
                        <label class="field-label">
                            <span data-i18n="salary">Зарплата</span>
                        </label>
                        <div class="salary-wrap">
                            <i class="bi bi-coin salary-icon"></i>
                            <input type="number"
                                   name="salary"
                                   id="salaryInput"
                                   class="input-custom"
                                   placeholder="30000"
                                   value="{{ old('salary', $user->salary ?? '') }}"
                                   min="0"
                                   step="100">
                            <span class="salary-suffix" data-i18n="perMonth">сом/мес</span>
                        </div>
                        <p class="note-text" data-i18n="salaryNote">Ежемесячная зарплата сотрудника в сомони</p>
                    </div>

                    {{-- СКАН ЛИЦА (TOGGLE) --}}
                    <div class="field-group" style="margin-bottom: 0;">
                        <label class="toggle-row {{ old('needs_face_scan', $user->needs_face_scan) ? 'active' : '' }}" id="toggleRow" for="face">
                            <div class="toggle-left">
                                <div class="toggle-icon-box">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div>
                                    <div class="toggle-title" data-i18n="faceScanTitle">Сотрудник компании</div>
                                    <div class="toggle-desc" data-i18n="faceScanDesc">Требуется сканирование лица (Breez Face ID) при входе</div>
                                </div>
                            </div>
                            <label class="toggle-switch" onclick="event.stopPropagation()">
                                <input type="checkbox"
                                       name="needs_face_scan"
                                       value="1"
                                       id="face"
                                       {{ old('needs_face_scan', $user->needs_face_scan) ? 'checked' : '' }}
                                       onchange="toggleFaceScan(this)">
                                <span class="toggle-slider"></span>
                            </label>
                        </label>
                    </div>
                </div>

                {{-- ПАРОЛЬ --}}
                <div class="form-section">
                    <div class="section-title" data-i18n="securitySection">Безопасность</div>

                    <div class="field-group">
                        <label class="field-label">
                            <span data-i18n="newPassword">Новый пароль</span>
                            <span class="hint">(<span data-i18n="leaveEmpty">оставьте пустым, чтобы не менять</span>)</span>
                        </label>
                        <div class="password-wrap">
                            <input name="password" type="password" id="password" class="input-custom" style="padding-right: 48px !important;" placeholder="••••••••">
                            <button type="button" onclick="togglePassword()" class="password-toggle" aria-label="Toggle password">
                                <svg id="eyeIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                    </div>
                </div>

                {{-- КНОПКА СОХРАНИТЬ --}}
                <div class="submit-wrap">
                    <button type="submit" class="btn-submit">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span data-i18n="save">Сохранить</span>
                    </button>
                </div>
            </form>

            {{-- ФОРМА УДАЛЕНИЯ --}}
            @if((int)$user->created_by === auth()->id())
            <div class="delete-zone">
                <form method="POST" action="{{ route('users.destroy', $user->id) }}" onsubmit="return confirm('{{ __('users.confirm_delete') }}')" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span data-i18n="delete">Удалить пользователя</span>
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// ============================================================
// РОЛЬ
// ============================================================
const roleHidden      = document.getElementById('roleHidden');
const roleCustomBlock = document.getElementById('roleCustomBlock');
const roleCustomInput = document.getElementById('roleCustomInput');
const btnAdmin        = document.getElementById('btnRoleAdmin');
const btnCustom       = document.getElementById('btnRoleCustom');
const levelSelect     = document.getElementById('levelSelect');

function setRole(mode) {
    if (mode === 'admin') {
        btnAdmin.classList.add('active');
        btnCustom.classList.remove('active');
        roleCustomBlock.classList.remove('visible');
        roleCustomInput.removeAttribute('required');
        roleCustomInput.value = '';
        roleHidden.value = 'admin';
        roleHidden.disabled = false;
        levelSelect.value = '1';
    } else {
        btnCustom.classList.add('active');
        btnAdmin.classList.remove('active');
        roleCustomBlock.classList.add('visible');
        roleCustomInput.setAttribute('required', 'required');
        roleCustomInput.focus();
        if (levelSelect.value === '1') levelSelect.value = '2';
    }
}

if (roleCustomInput) {
    roleCustomInput.addEventListener('input', function() {
        const val = this.value.trim();
        roleHidden.value = val;
        if (val.toLowerCase() === 'admin') {
            levelSelect.value = '1';
        } else if (levelSelect.value === '1') {
            levelSelect.value = '2';
        }
    });
}

document.getElementById('editUserForm')?.addEventListener('submit', function(e) {
    if (roleHidden) roleHidden.disabled = false;

    if (btnCustom && btnCustom.classList.contains('active')) {
        const val = roleCustomInput.value.trim();
        if (!val) {
            e.preventDefault();
            alert('Пожалуйста, введите название роли');
            roleCustomInput.focus();
            roleCustomInput.style.borderColor = '#ef4444';
            setTimeout(() => { roleCustomInput.style.borderColor = ''; }, 2000);
            return;
        }
        roleHidden.value = val;
    } else if (roleHidden) {
        roleHidden.value = 'admin';
    }
});

// ============================================================
// АВАТАР
// ============================================================
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 2 * 1024 * 1024) {
            const lang = localStorage.getItem('docsign_lang') || 'ru';
            const alerts = {
                ru: 'Файл слишком большой. Максимум 2MB',
                tj: 'Файл хеле калон аст. Ҳадди аксар 2MB',
                en: 'File too large. Maximum 2MB'
            };
            alert(alerts[lang] || alerts.ru);
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            const letter = document.getElementById('avatarLetter');
            preview.src = e.target.result;
            preview.style.display = 'block';
            letter.style.display = 'none';
            document.getElementById('removeAvatarFlag').value = '0';
            document.getElementById('removeBtn').style.display = '';
            document.getElementById('fileNameDisplay').textContent = '📎 ' + file.name;
        }
        reader.readAsDataURL(file);
    }
}

function removeAvatar() {
    const lang = localStorage.getItem('docsign_lang') || 'ru';
    const confirms = {
        ru: 'Удалить фотографию профиля?',
        tj: 'Расми профилро нест мекунед?',
        en: 'Remove profile photo?'
    };
    if (confirm(confirms[lang] || confirms.ru)) {
        const preview = document.getElementById('avatarPreview');
        const letter = document.getElementById('avatarLetter');
        preview.src = '';
        preview.style.display = 'none';
        letter.style.display = '';
        document.getElementById('avatarInput').value = '';
        document.getElementById('removeAvatarFlag').value = '1';
        document.getElementById('removeBtn').style.display = 'none';
        document.getElementById('fileNameDisplay').textContent = '';
    }
}

// ============================================================
// БУКВА АВАТАРА
// ============================================================
const nameInput = document.getElementById('nameInput');
if (nameInput) {
    nameInput.addEventListener('input', e => {
        const letter = document.getElementById('avatarLetter');
        if (letter) letter.textContent = e.target.value.trim().charAt(0).toUpperCase() || '?';
    });
}

// ============================================================
// ТЕЛЕФОН
// ============================================================
const phoneInput = document.getElementById('phone');
const prefix = '+992 ';
if (phoneInput) {
    phoneInput.addEventListener('input', function(e) {
        if (!e.target.value.startsWith(prefix)) e.target.value = prefix;
        let digits = e.target.value.substring(prefix.length).replace(/\D/g, '').substring(0, 9);
        let formatted = '';
        if (digits.length > 0) formatted += digits.substring(0, 2);
        if (digits.length >= 3) formatted += ' ' + digits.substring(2, 5);
        if (digits.length >= 6) formatted += ' ' + digits.substring(5, 7);
        if (digits.length >= 8) formatted += ' ' + digits.substring(7, 9);
        e.target.value = prefix + formatted;
    });
}

// ============================================================
// ПАРОЛЬ
// ============================================================
function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
    }
}

// ============================================================
// FACE SCAN TOGGLE
// ============================================================
const faceCheckbox = document.getElementById('face');
const toggleRow    = document.getElementById('toggleRow');

function toggleFaceScan(cb) {
    if (cb.checked) {
        toggleRow.classList.add('active');
    } else {
        toggleRow.classList.remove('active');
    }
}

if (toggleRow) {
    toggleRow.addEventListener('click', function(e) {
        if (e.target.closest('.toggle-switch')) return;
        faceCheckbox.checked = !faceCheckbox.checked;
        toggleFaceScan(faceCheckbox);
    });
}

// ============================================================
// ПЕРЕВОДЫ
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const EDIT_USER_TRANSLATIONS = {
        ru: {
            editUser: 'Редактировать', level: 'Уровень', backBtn: 'Назад',
            photo: 'Фото', photoDesc: 'JPG, PNG до 2MB',
            change: 'Изменить', upload: 'Загрузить', remove: 'Удалить',
            fullName: 'Полное имя', email: 'Email', phone: 'Телефон', role: 'Роль',
            newPassword: 'Новый пароль', leaveEmpty: 'оставьте пустым, чтобы не менять',
            save: 'Сохранить', delete: 'Удалить пользователя', checkData: 'Проверьте данные:',
            photoSection: 'Фото профиля', mainInfo: 'Основная информация',
            accessSection: 'Роль и уровень', securitySection: 'Безопасность',
            role_admin: 'Администратор', role_admin_desc: 'Полный доступ',
            role_custom: 'Своя роль', role_custom_desc: 'Вписать вручную',
            levelNote: 'Уровень 1 = администратор, 2–20 = сотрудники',
            companySection: 'Компания и настройки',
            autoCompany: 'Компания назначается автоматически:',
            salary: 'Зарплата', perMonth: 'сом/мес',
            salaryNote: 'Ежемесячная зарплата сотрудника в сомони',
            faceScanTitle: 'Сотрудник компании',
            faceScanDesc: 'Требуется сканирование лица (Breez Face ID) при входе'
        },
        tj: {
            editUser: 'Таҳрир', level: 'Сатҳ', backBtn: 'Бозгашт',
            photo: 'Сурат', photoDesc: 'JPG, PNG то 2MB',
            change: 'Иваз кардан', upload: 'Боркунӣ', remove: 'Нест кардан',
            fullName: 'Номи пурра', email: 'Email', phone: 'Телефон', role: 'Вазифа',
            newPassword: 'Рамзи нав', leaveEmpty: 'холӣ гузоред',
            save: 'Нигоҳ доштан', delete: 'Нест кардани корбар', checkData: 'Маълумотро тафтиш кунед:',
            photoSection: 'Сурати профил', mainInfo: 'Маълумоти асосӣ',
            accessSection: 'Нақш ва сатҳ', securitySection: 'Амният',
            role_admin: 'Администратор', role_admin_desc: 'Дастрасии пурра',
            role_custom: 'Вазифаи худ', role_custom_desc: 'Дастӣ нависед',
            levelNote: 'Сатҳи 1 = администратор, 2–20 = кормандон',
            companySection: 'Ширкат ва танзимот',
            autoCompany: 'Ширкат автоматикӣ таъин мешавад:',
            salary: 'Маош', perMonth: 'сомонӣ/моҳ',
            salaryNote: 'Маоши моҳонаи корманд бо сомонӣ',
            faceScanTitle: 'Корманди ширкат',
            faceScanDesc: 'Сканкунии чеҳра (Breez Face ID) ҳангоми воридшавӣ лозим аст'
        },
        en: {
            editUser: 'Edit', level: 'Level', backBtn: 'Back',
            photo: 'Photo', photoDesc: 'JPG, PNG up to 2MB',
            change: 'Change', upload: 'Upload', remove: 'Remove',
            fullName: 'Full Name', email: 'Email', phone: 'Phone', role: 'Role',
            newPassword: 'New Password', leaveEmpty: 'leave empty to keep current',
            save: 'Save', delete: 'Delete User', checkData: 'Check the data:',
            photoSection: 'Profile Photo', mainInfo: 'Main Information',
            accessSection: 'Role & Level', securitySection: 'Security',
            role_admin: 'Administrator', role_admin_desc: 'Full access',
            role_custom: 'Custom role', role_custom_desc: 'Type manually',
            levelNote: 'Level 1 = admin, 2–20 = employees',
            companySection: 'Company & Settings',
            autoCompany: 'Company is assigned automatically:',
            salary: 'Salary', perMonth: 'somoni/month',
            salaryNote: "Employee's monthly salary in somoni",
            faceScanTitle: 'Company Employee',
            faceScanDesc: 'Face scan (Breez Face ID) required on login'
        }
    };

    function applyEditUserTranslations(lang) {
        const dict = EDIT_USER_TRANSLATIONS[lang] || EDIT_USER_TRANSLATIONS.ru;
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (dict[key] !== undefined) el.textContent = dict[key];
        });
    }

    const initialLang = localStorage.getItem('docsign_lang') || 'ru';
    applyEditUserTranslations(initialLang);

    window.addEventListener('docsign:lang-changed', (e) => {
        applyEditUserTranslations(e.detail?.lang || 'ru');
    });
    window.addEventListener('storage', (e) => {
        if (e.key === 'docsign_lang' && e.newValue) applyEditUserTranslations(e.newValue);
    });
});
</script>

@endsection