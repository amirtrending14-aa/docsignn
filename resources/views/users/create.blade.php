@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    /* ============================================================ */
    /* ===== CREATE USER PAGE (Tabler 3D Light) ===== */
    /* ============================================================ */
    .create-user-page {
        min-height: calc(100vh - 56px);
        padding: 8px clamp(16px, 2vw, 32px) 40px;
        color: var(--text);
        font-family: var(--font-sans);
        position: relative;
    }

    /* Фоновые пятна (только light theme) */
    html[data-theme="light"] .create-user-page::before,
    html[data-theme="light"] .create-user-page::after {
        content: "";
        position: fixed;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
        filter: blur(120px);
        opacity: 0.4;
    }
    html[data-theme="light"] .create-user-page::before {
        top: -200px; left: -100px;
        width: 500px; height: 500px;
        background: radial-gradient(circle, rgba(var(--glow), 0.2) 0%, transparent 70%);
        animation: createBlobFloat 22s ease-in-out infinite;
    }
    html[data-theme="light"] .create-user-page::after {
        bottom: -200px; right: -100px;
        width: 600px; height: 600px;
        background: radial-gradient(circle, rgba(168, 85, 247, 0.12) 0%, transparent 70%);
        animation: createBlobFloat 28s ease-in-out infinite reverse;
    }
    @keyframes createBlobFloat {
        0%,100% { transform: translate(0,0) scale(1); }
        50%     { transform: translate(30px,-30px) scale(1.05); }
    }

    .create-user-page > * { position: relative; z-index: 1; }

    .create-wrap {
        width: 100%;
        max-width: 1480px;
        margin: 0 auto;
    }

    /* === TOP BAR (3D) === */
    .create-topbar {
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
    .create-topbar::before {
        content: "";
        position: absolute;
        left: 12px; right: 12px; bottom: -8px;
        height: 12px;
        background: linear-gradient(180deg, rgba(15,23,42,0.08), rgba(15,23,42,0.02));
        border-radius: var(--radius);
        z-index: -1;
        filter: blur(10px);
    }
    .create-topbar::after {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.9), transparent);
        pointer-events: none;
        opacity: 0.8;
    }

    .create-topbar-left {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
        flex: 1;
    }

    .create-topbar-icon {
        width: 48px;
        height: 48px;
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
    .create-topbar-icon::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: var(--radius);
        background: linear-gradient(180deg, rgba(255,255,255,0.25), transparent 60%);
        pointer-events: none;
    }
    .create-topbar-icon svg {
        width: 24px; height: 24px;
        color: #fff;
        position: relative; z-index: 1;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.2));
    }

    .create-topbar-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--text);
        letter-spacing: -0.3px;
        line-height: 1.2;
        margin: 0;
    }
    .create-topbar-subtitle {
        font-size: 12px;
        color: var(--muted);
        font-weight: 600;
        margin-top: 3px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }
    .create-topbar-subtitle strong {
        color: rgb(var(--glow));
        font-weight: 700;
    }

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
    .form-card form {
        display: flex;
        flex-direction: column;
        gap: 28px;
    }
    .form-columns {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-items: start;
        gap: 36px;
    }
    .form-column {
        display: flex;
        flex-direction: column;
        gap: 28px;
        min-width: 0;
    }
    .form-column > .form-section {
        margin-top: 0;
        margin-bottom: 0;
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

    .avatar-preview-wrap { position: relative; flex-shrink: 0; }

    .avatar-preview-box {
        width: 100px;
        height: 100px;
        border-radius: var(--radius);
        background: linear-gradient(135deg, rgb(var(--glow)), #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 42px;
        font-weight: 900;
        color: #fff;
        overflow: hidden;
        position: relative;
        box-shadow:
            0 8px 24px rgba(var(--glow), 0.35),
            inset 0 1px 0 rgba(255,255,255,0.3);
        border: 1px solid rgba(255,255,255,0.2);
        text-shadow: 0 2px 8px rgba(0,0,0,0.2);
        letter-spacing: -1px;
    }
    .avatar-preview-box::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(255,255,255,0.2), transparent 60%);
        pointer-events: none;
    }
    .avatar-preview-box img {
        width: 100%; height: 100%;
        object-fit: cover;
        position: absolute;
        inset: 0;
        z-index: 2;
    }

    .avatar-upload-btn {
        position: absolute;
        bottom: -6px; right: -6px;
        width: 34px; height: 34px;
        background: linear-gradient(135deg, rgb(var(--glow)), rgba(var(--glow), 0.75));
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow:
            0 4px 12px rgba(var(--glow), 0.45),
            inset 0 1px 0 rgba(255,255,255,0.3);
        transition: all 0.25s ease;
        border: 2px solid var(--bg-1);
        z-index: 3;
    }
    .avatar-upload-btn:hover { transform: scale(1.1) rotate(90deg); }
    .avatar-upload-btn svg {
        width: 16px; height: 16px;
        color: #fff;
        position: relative; z-index: 1;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.2));
    }

    .avatar-info { flex: 1; min-width: 0; }
    .avatar-info h3 {
        font-size: 15px;
        font-weight: 800;
        color: var(--text);
        margin: 0 0 4px;
    }
    .avatar-info p {
        font-size: 12px;
        color: var(--muted);
        margin: 0;
        font-weight: 500;
    }
    .avatar-file-name {
        font-size: 12px;
        color: rgb(var(--glow));
        margin-top: 8px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
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

    /* === SUBMIT (3D) === */
    .submit-wrap {
        padding-top: 24px;
        margin-top: 28px;
        border-top: 1px solid var(--line);
        display: flex;
        justify-content: center;
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
    .btn-submit:active {
        transform: translateY(1px);
        box-shadow:
            0 4px 12px rgba(var(--glow), 0.3),
            inset 0 1px 0 rgba(255,255,255,0.2);
    }
    .btn-submit:active::before { bottom: -2px; }
    .btn-submit svg { width: 18px; height: 18px; }

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

    /* === SALARY (3D) === */
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

    /* === TOGGLE SCAN (3D) === */
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
    .toggle-row:hover {
        border-color: rgba(var(--glow), 0.35);
    }
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

    /* === VALIDATION ERRORS === */
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
    html[data-theme="dark"] .validation-errors {
        color: #fca5a5;
    }
    .validation-errors ul { margin: 5px 0 0 20px; padding: 0; }

    /* ============ RESPONSIVE ============ */
    @media (max-width: 1100px) {
        .form-card form { gap: 24px; }
        .form-columns { grid-template-columns: 1fr; gap: 24px; }
        .form-column { gap: 24px; }
    }
    @media (max-width: 768px) {
        .create-user-page { padding: 6px 12px 36px; }
        .create-topbar {
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
        .form-card { padding: 24px 18px; }
        .field-row { grid-template-columns: 1fr; }
        .toggle-row {
            flex-direction: column;
            align-items: flex-start;
            padding: 16px;
        }
        .toggle-switch { align-self: flex-end; margin-top: -30px; }
    }
    @media (max-width: 480px) {
        .create-topbar-title { font-size: 17px; }
        .form-card { padding: 20px 14px; }
        .avatar-preview-box { width: 80px; height: 80px; font-size: 34px; }
        .role-toggle { gap: 8px; }
        .role-toggle-btn { padding: 12px 8px; }
        .role-toggle-btn .rt-label { font-size: 12px; }
        .role-toggle-btn .rt-icon { font-size: 20px; }
        .btn-submit { padding: 12px 28px; font-size: 12px; }
    }
</style>

<div class="create-user-page">
    <div class="create-wrap">
        <div class="create-topbar">
            <div class="create-topbar-left">
                <div class="create-topbar-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <div>
                    <div class="create-topbar-title" data-i18n="newUser">Новый пользователь</div>
                    <div class="create-topbar-subtitle">
                        <span data-i18n="companyLabel">Компания</span>:
                        <strong>{{ $selectedCompany?->name ?? auth()->user()->companyName ?? '—' }}</strong>
                    </div>
                </div>
            </div>
            <a href="{{ route('users.index') }}" class="btn-back">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                <span data-i18n="backToList">Назад к списку</span>
            </a>
        </div>

        <div class="form-card">
            @if ($errors->any())
                <div class="validation-errors">
                    <strong>Проверьте данные:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-columns">
                    <div class="form-column">
                {{-- ===== ФОТО ===== --}}
                <div class="form-section">
                    <div class="section-title" data-i18n="photoSection">Фото профиля</div>
                    <div class="avatar-block">
                        <div class="avatar-preview-wrap">
                            <div class="avatar-preview-box">
                                <span id="avatarLetter">?</span>
                                <img id="avatarPreview" src="" style="display: none;">
                            </div>
                            <label for="avatarInput" class="avatar-upload-btn" title="Upload">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </label>
                            <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
                        </div>
                        <div class="avatar-info">
                            <h3 data-i18n="photo">Фото</h3>
                            <p data-i18n="photoDesc">JPG, PNG до 2MB</p>
                            <p id="fileNameDisplay" class="avatar-file-name"></p>
                        </div>
                    </div>
                </div>

                {{-- ===== ОСНОВНАЯ ИНФОРМАЦИЯ ===== --}}
                <div class="form-section">
                    <div class="section-title" data-i18n="mainInfo">Основная информация</div>

                    <div class="field-group">
                        <label class="field-label"><span data-i18n="fullName">Полное имя</span><span class="required">*</span></label>
                        <input name="name" type="text" required class="input-custom" placeholder="Иван Иванов" id="nameInput" value="{{ old('name') }}">
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label"><span data-i18n="email">Email</span><span class="required">*</span></label>
                            <input name="email" type="email" required class="input-custom" placeholder="mail@example.com" value="{{ old('email') }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label"><span data-i18n="phone">Телефон</span><span class="required">*</span></label>
                            <input name="phone" type="text" id="phone" required class="input-custom" placeholder="+992 00 000 0000" value="{{ old('phone') }}">
                        </div>
                    </div>

                    {{-- ===== РОЛЬ — 2 КНОПКИ ===== --}}
                    <div class="field-group">
                        <label class="field-label"><span data-i18n="role">Роль</span><span class="required">*</span></label>

                        <div class="role-toggle">
                            <div class="role-toggle-btn active" id="btnRoleAdmin" onclick="setRole('admin')">
                                <div>
                                    <div class="rt-icon">🛡️</div>
                                    <div class="rt-label" data-i18n="role_admin">Администратор</div>
                                    <div class="rt-desc" data-i18n="role_admin_desc">Полный доступ</div>
                                </div>
                            </div>
                            <div class="role-toggle-btn" id="btnRoleCustom" onclick="setRole('custom')">
                                <div>
                                    <div class="rt-icon">✏️</div>
                                    <div class="rt-label" data-i18n="role_custom">Своя роль</div>
                                    <div class="rt-desc" data-i18n="role_custom_desc">Вписать вручную</div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="role" id="roleHidden" value="admin">

                        <div class="role-custom-input" id="roleCustomBlock">
                            <div class="role-custom-wrap">
                                <i class="bi bi-pencil-fill role-custom-icon"></i>
                                <input type="text" class="input-custom" id="roleCustomInput"
                                       placeholder="Например: менеджер, бухгалтер, директор..." autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>

                    </div>
                    <div class="form-column">
                {{-- ===== ДОСТУП ===== --}}
                <div class="form-section">
                    <div class="section-title" data-i18n="accessSection">Доступ и безопасность</div>

                    <div class="field-group">
                        <label class="field-label"><span data-i18n="level">Уровень</span> (1–20)<span class="required">*</span></label>
                        <select name="level" required class="input-custom" id="levelSelect">
                            @for($i = 1; $i <= 20; $i++)
                                <option value="{{ $i }}" {{ old('level', 1) == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                        <p class="note-text" data-i18n="levelNote">Уровень 1 = администратор, 2–20 = сотрудники</p>
                    </div>

                    <div class="field-group">
                        <label class="field-label"><span data-i18n="password">Пароль</span><span class="required">*</span></label>
                        <div class="password-wrap">
                            <input name="password" type="password" id="password" required class="input-custom" style="padding-right: 48px !important;" placeholder="Минимум 8 символов">
                            <button type="button" onclick="togglePassword()" class="password-toggle">
                                <svg id="eyeIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ===== КОМПАНИЯ, ЗАРПЛАТА, СКАН ЛИЦА ===== --}}
                <div class="form-section">
                    <div class="section-title" data-i18n="companySection">Компания и настройки</div>

                    @if($selectedCompany)
                        <input type="hidden" name="company_id" value="{{ $selectedCompany->id }}">
                    @endif

                    <div class="info-banner" style="margin-bottom: 16px;">
                        <div class="info-banner-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="info-banner-text">
                            <span data-i18n="autoCompany">Компания назначается автоматически:</span>
                            <strong>{{ $selectedCompany?->name ?? auth()->user()->companyName ?? '—' }}</strong>
                        </div>
                    </div>

                    {{-- ЗАРПЛАТА --}}
                    <div class="field-group">
                        <label class="field-label"><span data-i18n="salary">Зарплата</span></label>
                        <div class="salary-wrap">
                            <i class="bi bi-coin salary-icon"></i>
                            <input type="number"
                                   name="salary"
                                   id="salaryInput"
                                   class="input-custom"
                                   placeholder="30000"
                                   value="{{ old('salary') }}"
                                   min="0"
                                   step="100">
                            <span class="salary-suffix">сом/мес</span>
                        </div>
                        <p class="note-text" data-i18n="salaryNote">Ежемесячная зарплата сотрудника в сомони</p>
                    </div>

                    {{-- СКАН ЛИЦА (TOGGLE) --}}
                    <div class="field-group" style="margin-bottom: 0;">
                        <label class="toggle-row" id="toggleRow" for="face">
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
                                       {{ old('needs_face_scan') ? 'checked' : '' }}
                                       onchange="toggleFaceScan(this)">
                                <span class="toggle-slider"></span>
                            </label>
                        </label>
                    </div>
                </div>

                    </div>
                </div>

                <div class="submit-wrap">
                    <button type="submit" class="btn-submit">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span data-i18n="createUser">Создать пользователя</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ============================================================
// РОЛЬ — ИСПРАВЛЕННАЯ ЛОГИКА
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

roleCustomInput.addEventListener('input', function() {
    const val = this.value.trim();
    roleHidden.value = val;

    if (val.toLowerCase() === 'admin') {
        levelSelect.value = '1';
    } else if (levelSelect.value === '1') {
        levelSelect.value = '2';
    }
});

document.querySelector('form').addEventListener('submit', function(e) {
    roleHidden.disabled = false;

    if (btnCustom.classList.contains('active')) {
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
    } else {
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
            alert('Максимум 2MB');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('avatarPreview').src = e.target.result;
            document.getElementById('avatarPreview').style.display = 'block';
            document.getElementById('avatarLetter').style.display = 'none';
            document.getElementById('fileNameDisplay').textContent = '📎 ' + file.name;
        };
        reader.readAsDataURL(file);
    }
}

// ============================================================
// ТЕЛЕФОН
// ============================================================
const phoneInput = document.getElementById('phone');
const prefix = '+992 ';
if (phoneInput) {
    phoneInput.addEventListener('input', function(e) {
        if (!e.target.value.startsWith(prefix)) e.target.value = prefix;
        let d = e.target.value.substring(prefix.length).replace(/\D/g, '').substring(0, 9);
        let f = '';
        if (d.length > 0) f += d.substring(0, 2);
        if (d.length >= 3) f += ' ' + d.substring(2, 5);
        if (d.length >= 6) f += ' ' + d.substring(5, 7);
        if (d.length >= 8) f += ' ' + d.substring(7, 9);
        e.target.value = prefix + f;
    });
}

// ============================================================
// ПАРОЛЬ
// ============================================================
function togglePassword() {
    const inp = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
    } else {
        inp.type = 'password';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
    }
}

// ============================================================
// БУКВА АВАТАРА
// ============================================================
const nameInput = document.getElementById('nameInput');
if (nameInput) {
    nameInput.addEventListener('input', e => {
        document.getElementById('avatarLetter').textContent = e.target.value.trim().charAt(0).toUpperCase() || '?';
    });
}

// ============================================================
// СКАН ЛИЦА TOGGLE
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

if (faceCheckbox && faceCheckbox.checked) {
    toggleRow.classList.add('active');
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
    const T = {
        ru: {
            newUser: 'Новый пользователь', companyLabel: 'Компания', backToList: 'Назад к списку',
            photo: 'Фото', photoDesc: 'JPG, PNG до 2MB', fullName: 'Полное имя',
            phone: 'Телефон', role: 'Роль', level: 'Уровень',
            levelNote: 'Уровень 1 = администратор, 2–20 = сотрудники',
            password: 'Пароль', autoCompany: 'Компания назначается автоматически:',
            createUser: 'Создать пользователя', photoSection: 'Фото профиля',
            mainInfo: 'Основная информация', accessSection: 'Доступ и безопасность',
            role_admin: 'Администратор', role_admin_desc: 'Полный доступ',
            role_custom: 'Своя роль', role_custom_desc: 'Вписать вручную',
            companySection: 'Компания и настройки',
            salary: 'Зарплата', salaryNote: 'Ежемесячная зарплата сотрудника в сомони',
            faceScanTitle: 'Сотрудник компании',
            faceScanDesc: 'Требуется сканирование лица (Breez Face ID) при входе'
        },
        tj: {
            newUser: 'Корбари нав', companyLabel: 'Ширкат', backToList: 'Бозгашт ба рӯйхат',
            photo: 'Сурат', photoDesc: 'JPG, PNG то 2MB', fullName: 'Номи пурра',
            phone: 'Телефон', role: 'Вазифа', level: 'Сатҳ',
            levelNote: 'Сатҳи 1 = администратор, 2–20 = кормандон',
            password: 'Рамз', autoCompany: 'Ширкат автоматикӣ таъин мешавад:',
            createUser: 'Эҷоди корбар', photoSection: 'Сурати профил',
            mainInfo: 'Маълумоти асосӣ', accessSection: 'Дастрасӣ ва амният',
            role_admin: 'Администратор', role_admin_desc: 'Дастрасии пурра',
            role_custom: 'Вазифаи худ', role_custom_desc: 'Дастӣ нависед',
            companySection: 'Ширкат ва танзимот',
            salary: 'Маош', salaryNote: 'Маоши моҳонаи корманд бо сомонӣ',
            faceScanTitle: 'Корманди ширкат',
            faceScanDesc: 'Сканкунии чеҳра (Breez Face ID) ҳангоми воридшавӣ лозим аст'
        },
        en: {
            newUser: 'New User', companyLabel: 'Company', backToList: 'Back to list',
            photo: 'Photo', photoDesc: 'JPG, PNG up to 2MB', fullName: 'Full Name',
            phone: 'Phone', role: 'Role', level: 'Level',
            levelNote: 'Level 1 = admin, 2–20 = employees',
            password: 'Password', autoCompany: 'Company is assigned automatically:',
            createUser: 'Create User', photoSection: 'Profile Photo',
            mainInfo: 'Main Information', accessSection: 'Access & Security',
            role_admin: 'Administrator', role_admin_desc: 'Full access',
            role_custom: 'Custom role', role_custom_desc: 'Type manually',
            companySection: 'Company & Settings',
            salary: 'Salary', salaryNote: "Employee's monthly salary in somoni",
            faceScanTitle: 'Company Employee',
            faceScanDesc: 'Face scan (Breez Face ID) required on login'
        }
    };

    function apply(lang) {
        const d = T[lang] || T.ru;
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const k = el.getAttribute('data-i18n');
            if (d[k] !== undefined) el.textContent = d[k];
        });
    }

    apply(localStorage.getItem('docsign_lang') || 'ru');
    window.addEventListener('docsign:lang-changed', e => apply(e.detail?.lang || 'ru'));
    window.addEventListener('storage', e => { if (e.key === 'docsign_lang') apply(e.newValue); });
});
</script>

@endsection