@extends('layouts.admin')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    .edit-user-page {
        min-height: 100vh;
        padding: 40px 24px 60px;
        color: var(--text);
        font-family: 'Inter', sans-serif;
        position: relative;
    }

    /* Фоновые blob-ы */
    .edit-blob {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
        filter: blur(100px);
        opacity: 0.35;
    }

    .edit-blob-1 {
        top: -120px;
        left: -120px;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(var(--glow), 0.35) 0%, transparent 70%);
        animation: blobFloat 20s ease-in-out infinite;
    }

    .edit-blob-2 {
        bottom: -120px;
        right: -120px;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(168, 85, 247, 0.28) 0%, transparent 70%);
        animation: blobFloat 25s ease-in-out infinite reverse;
    }

    .edit-blob-3 {
        top: 40%;
        left: 60%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(236, 72, 153, 0.22) 0%, transparent 70%);
        animation: blobFloat3 30s ease-in-out infinite;
    }

    @keyframes blobFloat {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(30px, -30px); }
    }

    @keyframes blobFloat3 {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(-30px, 30px); }
    }

    .edit-wrap {
        max-width: 720px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    /* === TOP BAR === */
    .edit-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 24px;
        background: linear-gradient(180deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 18px 22px;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        position: relative;
    }

    .edit-topbar::before {
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: var(--radius);
        padding: 1px;
        background: linear-gradient(135deg, rgba(var(--glow),0.4), transparent 40%, transparent 60%, rgba(var(--glow),0.2));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0.6;
        pointer-events: none;
    }

    .edit-topbar-left {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
        flex: 1;
    }

    .edit-topbar-icon {
        width: 48px;
        height: 48px;
        border-radius: 13px;
        background: linear-gradient(135deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.4));
        display: grid;
        place-items: center;
        flex-shrink: 0;
        box-shadow: 0 0 24px rgba(var(--glow), 0.5), inset 0 0 12px rgba(255,255,255,0.2);
    }

    .edit-topbar-icon svg {
        width: 24px;
        height: 24px;
        color: #0a0d14;
    }

    .edit-topbar-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--text);
        letter-spacing: -0.3px;
        line-height: 1.2;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .edit-topbar-title .user-name-accent {
        color: rgba(var(--glow), 1);
        text-shadow: 0 0 14px rgba(var(--glow), 0.4);
    }

    .edit-topbar-subtitle {
        font-size: 12px;
        color: var(--muted);
        font-weight: 600;
        margin-top: 3px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .edit-topbar-subtitle .meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 7px;
        background: rgba(var(--glow), 0.12);
        border: 1px solid rgba(var(--glow), 0.25);
        color: rgba(var(--glow), 1);
        font-family: 'JetBrains Mono', monospace;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .meta-pill svg {
        flex-shrink: 0;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: rgba(255,255,255,0.04);
        color: var(--muted);
        border: 1px solid var(--line);
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        transition: all 0.25s ease;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .btn-back:hover {
        color: rgba(var(--glow), 1);
        border-color: rgba(var(--glow), 0.5);
        background: rgba(var(--glow), 0.08);
        box-shadow: 0 0 18px rgba(var(--glow), 0.25);
        transform: translateX(-2px);
    }

    .btn-back svg {
        width: 14px;
        height: 14px;
    }

    /* === FORM CARD === */
    .form-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 36px 32px;
        position: relative;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }

    .form-card::before {
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: var(--radius);
        padding: 1px;
        background: linear-gradient(135deg, rgba(var(--glow),0.5), transparent 40%, transparent 60%, rgba(var(--glow),0.25));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0.7;
        pointer-events: none;
    }

    .form-section {
        margin-bottom: 28px;
    }

    .form-section:last-child {
        margin-bottom: 0;
    }

    .section-title {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--muted);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title::before {
        content: "";
        width: 4px;
        height: 14px;
        border-radius: 2px;
        background: linear-gradient(180deg, rgba(var(--glow), 1), rgba(var(--glow), 0.4));
        box-shadow: 0 0 10px rgba(var(--glow), 0.6);
        flex-shrink: 0;
    }

    /* === AVATAR BLOCK === */
    .avatar-block {
        display: flex;
        align-items: center;
        gap: 22px;
        padding: 22px;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--line);
        border-radius: 14px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .avatar-block::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(var(--glow), 0.5), transparent);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .avatar-block:hover::before {
        opacity: 1;
    }

    .avatar-block:hover {
        border-color: rgba(var(--glow), 0.3);
        background: rgba(255,255,255,0.05);
    }

    .avatar-box {
        width: 110px;
        height: 110px;
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        flex-shrink: 0;
        background: linear-gradient(135deg, rgba(var(--glow), 0.4), rgba(168, 85, 247, 0.3));
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 24px rgba(var(--glow), 0.3), inset 0 0 20px rgba(255,255,255,0.1);
        border: 1px solid rgba(var(--glow), 0.3);
    }

    .avatar-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        inset: 0;
    }

    .avatar-letter {
        font-size: 46px;
        font-weight: 900;
        font-style: italic;
        color: rgba(255,255,255,0.9);
        text-shadow: 0 4px 16px rgba(0,0,0,0.5);
    }

    .avatar-overlay {
        position: absolute;
        inset: 0;
        background: rgba(10, 13, 20, 0.75);
        backdrop-filter: blur(3px);
        opacity: 0;
        transition: opacity 0.25s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 2;
    }

    .avatar-box:hover .avatar-overlay {
        opacity: 1;
    }

    .avatar-overlay-content {
        text-align: center;
        color: #ffffff;
    }

    .avatar-overlay-content svg {
        width: 28px;
        height: 28px;
        margin: 0 auto 4px;
        display: block;
        color: rgba(var(--glow), 1);
        filter: drop-shadow(0 0 8px rgba(var(--glow), 0.6));
    }

    .avatar-overlay-content span {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .avatar-info {
        flex: 1;
        min-width: 0;
    }

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
        padding: 8px 14px;
        background: linear-gradient(180deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.65));
        color: #0a0d14;
        border-radius: 9px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        cursor: pointer;
        transition: all 0.25s ease;
        box-shadow: 0 4px 14px rgba(var(--glow), 0.35), inset 0 1px 0 rgba(255,255,255,0.3);
        border: none;
    }

    .btn-upload:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(var(--glow), 0.5);
        filter: brightness(1.08);
    }

    .btn-upload svg {
        width: 13px;
        height: 13px;
    }

    .btn-remove {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: rgba(255, 99, 99, 0.08);
        color: #ff6363;
        border: 1px solid rgba(255, 99, 99, 0.3);
        border-radius: 9px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .btn-remove:hover {
        background: rgba(255, 99, 99, 0.15);
        border-color: rgba(255, 99, 99, 0.5);
        box-shadow: 0 0 18px rgba(255, 99, 99, 0.3);
    }

    .btn-remove svg {
        width: 13px;
        height: 13px;
    }

    .avatar-file-name {
        font-size: 12px;
        color: rgba(var(--glow), 1);
        margin-top: 10px;
        font-weight: 700;
        word-break: break-word;
    }

    /* === FIELDS === */
    .field-group {
        margin-bottom: 18px;
    }

    .field-group:last-child {
        margin-bottom: 0;
    }

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
        font-weight: 800;
        color: var(--muted);
        margin-bottom: 8px;
    }

    .field-label .required {
        color: rgba(var(--glow), 1);
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
        background: rgba(255,255,255,0.04) !important;
        border: 1px solid var(--line) !important;
        color: var(--text) !important;
        font-size: 14px !important;
        font-weight: 500;
        padding: 13px 16px !important;
        border-radius: 10px !important;
        transition: all 0.25s ease;
        font-family: 'Inter', sans-serif;
        box-sizing: border-box;
    }

    .input-custom:focus {
        border-color: rgba(var(--glow), 0.6) !important;
        background: rgba(var(--glow), 0.06) !important;
        box-shadow: 0 0 0 3px rgba(var(--glow), 0.15), 0 0 20px rgba(var(--glow), 0.2) !important;
        outline: none !important;
    }

    .input-custom::placeholder {
        color: var(--muted) !important;
        opacity: 0.6;
    }

    .input-custom option {
        background: var(--bg-0, #06070b);
        color: var(--text);
    }

    /* Readonly box */
    .readonly-box {
        background: rgba(255,255,255,0.03);
        border: 1px dashed rgba(var(--glow), 0.25);
        border-radius: 10px;
        padding: 14px 16px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .readonly-box .lock-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: rgba(var(--glow), 0.12);
        border: 1px solid rgba(var(--glow), 0.25);
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }

    .readonly-box .lock-icon svg {
        width: 14px;
        height: 14px;
        color: rgba(var(--glow), 1);
    }

    .readonly-box .meta-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        background: rgba(var(--glow), 0.08);
        border-radius: 7px;
        color: rgba(var(--glow), 1);
        font-size: 12px;
        font-weight: 700;
    }

    /* Password wrapper */
    .password-wrap {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: var(--muted);
        cursor: pointer;
        padding: 6px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .password-toggle:hover {
        color: rgba(var(--glow), 1);
        background: rgba(var(--glow), 0.1);
    }

    .password-toggle svg {
        width: 18px;
        height: 18px;
    }

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
        background: linear-gradient(180deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.65));
        color: #0a0d14;
        border-radius: 11px;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        transition: all 0.3s ease;
        box-shadow: 0 8px 28px rgba(var(--glow), 0.4), inset 0 1px 0 rgba(255,255,255,0.3);
        border: 1px solid transparent;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 40px rgba(var(--glow), 0.6);
        filter: brightness(1.08);
    }

    .btn-submit svg {
        width: 18px;
        height: 18px;
    }

    .btn-delete {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 28px;
        background: rgba(255, 99, 99, 0.08);
        color: #ff6363;
        border: 1px solid rgba(255, 99, 99, 0.3);
        border-radius: 11px;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        transition: all 0.3s ease;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-delete:hover {
        background: rgba(255, 99, 99, 0.15);
        border-color: rgba(255, 99, 99, 0.6);
        box-shadow: 0 8px 28px rgba(255, 99, 99, 0.35);
        transform: translateY(-2px);
    }

    .btn-delete svg {
        width: 18px;
        height: 18px;
    }

    /* ============================================ */
    /* === ПОЛНАЯ АДАПТИВНОСТЬ === */
    /* ============================================ */

    /* Маленькие десктопы (до 1200px) */
    @media (max-width: 1200px) {
        .edit-user-page { padding: 36px 22px 55px; }
        .edit-topbar { margin-bottom: 22px; padding: 17px 20px; gap: 14px; }
        .edit-topbar-icon { width: 46px; height: 46px; border-radius: 12px; }
        .edit-topbar-icon svg { width: 23px; height: 23px; }
        .edit-topbar-title { font-size: 19px; }
        .edit-topbar-subtitle { font-size: 11px; gap: 7px; }
        .meta-pill { padding: 3px 8px !important; font-size: 10px !important; }
        .form-card { padding: 32px 28px; }
        .form-section { margin-bottom: 26px; }
        .section-title { font-size: 10px; margin-bottom: 15px; }
        .avatar-block { padding: 20px; gap: 20px; }
        .avatar-box { width: 105px; height: 105px; }
        .avatar-letter { font-size: 44px; }
        .avatar-overlay-content svg { width: 26px; height: 26px; }
        .avatar-info h3 { font-size: 14px; }
        .avatar-info p { font-size: 11px; }
        .btn-upload, .btn-remove { padding: 7px 13px; font-size: 10px; }
        .input-custom { padding: 12px 15px !important; font-size: 13px !important; }
        .readonly-box { padding: 13px 15px; font-size: 12px; }
        .btn-submit { padding: 13px 34px; font-size: 12px; }
        .btn-delete { padding: 13px 26px; font-size: 12px; }
    }

    /* Планшеты (до 992px) */
    @media (max-width: 992px) {
        .edit-user-page { padding: 32px 20px 50px; }
        .edit-topbar { margin-bottom: 20px; padding: 16px 18px; gap: 12px; border-radius: 13px; }
        .edit-topbar-left { gap: 12px; }
        .edit-topbar-icon { width: 44px; height: 44px; border-radius: 12px; }
        .edit-topbar-icon svg { width: 22px; height: 22px; }
        .edit-topbar-title { font-size: 18px; white-space: normal; }
        .edit-topbar-subtitle { font-size: 11px; gap: 6px; margin-top: 2px; }
        .meta-pill { padding: 3px 8px !important; font-size: 10px !important; }
        .btn-back { padding: 9px 16px; font-size: 11px; border-radius: 9px; gap: 7px; }
        .btn-back svg { width: 13px; height: 13px; }
        .form-card { padding: 28px 24px; border-radius: 13px; }
        .form-section { margin-bottom: 24px; }
        .section-title { font-size: 10px; letter-spacing: 1.4px; margin-bottom: 14px; gap: 9px; }
        .section-title::before { width: 3px; height: 13px; }
        .avatar-block { padding: 18px; gap: 18px; border-radius: 13px; }
        .avatar-box { width: 100px; height: 100px; border-radius: 15px; }
        .avatar-letter { font-size: 42px; }
        .avatar-overlay-content svg { width: 24px; height: 24px; }
        .avatar-overlay-content span { font-size: 9px; }
        .avatar-info h3 { font-size: 14px; }
        .avatar-info p { font-size: 11px; }
        .avatar-actions { gap: 7px; margin-top: 10px; }
        .btn-upload, .btn-remove { padding: 7px 12px; font-size: 10px; border-radius: 8px; }
        .btn-upload svg, .btn-remove svg { width: 12px; height: 12px; }
        .avatar-file-name { font-size: 11px; margin-top: 8px; }
        .field-group { margin-bottom: 16px; }
        .field-row { gap: 14px; }
        .field-label { font-size: 10px; letter-spacing: 0.9px; margin-bottom: 7px; }
        .input-custom { padding: 12px 14px !important; font-size: 13px !important; border-radius: 9px !important; }
        .readonly-box { padding: 12px 14px; border-radius: 9px; font-size: 12px; gap: 8px; }
        .readonly-box .lock-icon { width: 26px; height: 26px; }
        .readonly-box .meta-item { font-size: 11px; padding: 3px 9px; }
        .password-toggle { right: 11px; }
        .password-toggle svg { width: 17px; height: 17px; }
        .submit-wrap { padding-top: 22px; margin-top: 26px; gap: 10px; }
        .btn-submit { padding: 12px 32px; font-size: 12px; border-radius: 10px; letter-spacing: 1.1px; }
        .btn-submit svg { width: 17px; height: 17px; }
        .btn-delete { padding: 12px 24px; font-size: 12px; border-radius: 10px; letter-spacing: 1.1px; }
        .btn-delete svg { width: 17px; height: 17px; }
    }

    /* Большие телефоны (до 768px) */
    @media (max-width: 768px) {
        .edit-user-page { padding: 28px 18px 45px; }
        .edit-topbar { margin-bottom: 18px; padding: 15px 16px; gap: 11px; border-radius: 12px; }
        .edit-topbar-left { gap: 11px; }
        .edit-topbar-icon { width: 42px; height: 42px; border-radius: 11px; }
        .edit-topbar-icon svg { width: 21px; height: 21px; }
        .edit-topbar-title { font-size: 17px; }
        .edit-topbar-subtitle { font-size: 10px; gap: 6px; }
        .meta-pill { padding: 3px 7px !important; font-size: 10px !important; gap: 4px; }
        .btn-back { padding: 9px 15px; font-size: 11px; border-radius: 9px; gap: 6px; letter-spacing: 0.7px; }
        .btn-back svg { width: 13px; height: 13px; }
        .form-card { padding: 24px 20px; border-radius: 12px; }
        .form-section { margin-bottom: 22px; }
        .section-title { font-size: 10px; letter-spacing: 1.3px; margin-bottom: 13px; gap: 8px; }
        .section-title::before { width: 3px; height: 12px; }
        .avatar-block { padding: 16px; gap: 16px; border-radius: 12px; }
        .avatar-box { width: 90px; height: 90px; border-radius: 14px; }
        .avatar-letter { font-size: 38px; }
        .avatar-overlay-content svg { width: 22px; height: 22px; }
        .avatar-overlay-content span { font-size: 9px; }
        .avatar-info h3 { font-size: 13px; }
        .avatar-info p { font-size: 11px; }
        .avatar-actions { gap: 6px; margin-top: 10px; }
        .btn-upload, .btn-remove { padding: 7px 11px; font-size: 10px; border-radius: 8px; }
        .avatar-file-name { font-size: 11px; margin-top: 8px; }
        .field-group { margin-bottom: 15px; }
        .field-row { gap: 13px; }
        .field-label { font-size: 10px; letter-spacing: 0.8px; margin-bottom: 7px; }
        .input-custom { padding: 11px 14px !important; font-size: 13px !important; border-radius: 9px !important; }
        .readonly-box { padding: 11px 13px; border-radius: 9px; font-size: 11px; gap: 7px; }
        .readonly-box .lock-icon { width: 24px; height: 24px; border-radius: 7px; }
        .readonly-box .lock-icon svg { width: 13px; height: 13px; }
        .readonly-box .meta-item { font-size: 10px; padding: 3px 8px; }
        .password-toggle { right: 10px; }
        .password-toggle svg { width: 16px; height: 16px; }
        .submit-wrap { padding-top: 20px; margin-top: 24px; gap: 10px; }
        .btn-submit { padding: 12px 30px; font-size: 11px; border-radius: 10px; letter-spacing: 1px; }
        .btn-submit svg { width: 16px; height: 16px; }
        .btn-delete { padding: 12px 22px; font-size: 11px; border-radius: 10px; letter-spacing: 1px; }
        .btn-delete svg { width: 16px; height: 16px; }
    }

    /* Телефоны (до 640px) */
    @media (max-width: 640px) {
        .edit-user-page { padding: 24px 16px 40px; }
        .edit-topbar {
            flex-direction: column;
            align-items: stretch;
            margin-bottom: 16px;
            padding: 14px;
            gap: 12px;
            border-radius: 11px;
        }
        .edit-topbar-left { gap: 10px; width: 100%; }
        .edit-topbar-icon { width: 40px; height: 40px; border-radius: 10px; }
        .edit-topbar-icon svg { width: 20px; height: 20px; }
        .edit-topbar-title { font-size: 16px; white-space: normal; }
        .edit-topbar-subtitle { font-size: 10px; gap: 5px; }
        .meta-pill { padding: 2px 7px !important; font-size: 9px !important; }
        .btn-back {
            width: 100%;
            justify-content: center;
            padding: 10px 14px;
            font-size: 10px;
            border-radius: 9px;
        }
        .btn-back svg { width: 12px; height: 12px; }
        .form-card { padding: 22px 18px; border-radius: 11px; }
        .form-section { margin-bottom: 20px; }
        .section-title { font-size: 9px; letter-spacing: 1.2px; margin-bottom: 12px; gap: 7px; }
        .section-title::before { width: 3px; height: 11px; }
        .avatar-block {
            flex-direction: column;
            text-align: center;
            padding: 18px;
            gap: 14px;
            border-radius: 11px;
        }
        .avatar-box { width: 85px; height: 85px; border-radius: 13px; }
        .avatar-letter { font-size: 36px; }
        .avatar-overlay-content svg { width: 20px; height: 20px; }
        .avatar-info { text-align: center; width: 100%; }
        .avatar-info h3 { font-size: 13px; }
        .avatar-info p { font-size: 10px; }
        .avatar-actions { justify-content: center; gap: 6px; margin-top: 10px; }
        .btn-upload, .btn-remove { padding: 7px 11px; font-size: 9px; border-radius: 8px; }
        .avatar-file-name { font-size: 10px; margin-top: 7px; text-align: center; }
        .field-group { margin-bottom: 14px; }
        .field-row { grid-template-columns: 1fr; gap: 14px; }
        .field-label { font-size: 9px; letter-spacing: 0.7px; margin-bottom: 6px; }
        .input-custom { padding: 11px 13px !important; font-size: 12px !important; border-radius: 8px !important; }
        .readonly-box { padding: 10px 12px; border-radius: 8px; font-size: 11px; gap: 6px; justify-content: center; }
        .readonly-box .lock-icon { width: 22px; height: 22px; border-radius: 6px; }
        .readonly-box .meta-item { font-size: 10px; padding: 3px 7px; }
        .password-toggle { right: 10px; padding: 5px; }
        .password-toggle svg { width: 15px; height: 15px; }
        .submit-wrap {
            padding-top: 18px;
            margin-top: 22px;
            gap: 10px;
            flex-direction: column;
        }
        .btn-submit, .btn-delete {
            width: 100%;
            justify-content: center;
            padding: 12px 28px;
            font-size: 11px;
            border-radius: 9px;
            letter-spacing: 1px;
        }
        .btn-submit svg, .btn-delete svg { width: 15px; height: 15px; }
    }

    /* Маленькие телефоны (до 480px) */
    @media (max-width: 480px) {
        .edit-user-page { padding: 20px 14px 36px; }
        .edit-topbar { margin-bottom: 14px; padding: 13px 12px; gap: 10px; border-radius: 10px; }
        .edit-topbar-left { gap: 9px; }
        .edit-topbar-icon { width: 38px; height: 38px; border-radius: 10px; }
        .edit-topbar-icon svg { width: 19px; height: 19px; }
        .edit-topbar-title { font-size: 15px; }
        .edit-topbar-subtitle { font-size: 10px; gap: 4px; }
        .meta-pill { padding: 2px 6px !important; font-size: 9px !important; }
        .btn-back { padding: 9px 13px; font-size: 10px; border-radius: 8px; letter-spacing: 0.6px; }
        .btn-back svg { width: 12px; height: 12px; }
        .form-card { padding: 20px 16px; border-radius: 10px; }
        .form-section { margin-bottom: 18px; }
        .section-title { font-size: 9px; letter-spacing: 1.1px; margin-bottom: 11px; }
        .avatar-block { padding: 16px; gap: 12px; border-radius: 10px; }
        .avatar-box { width: 80px; height: 80px; border-radius: 12px; }
        .avatar-letter { font-size: 34px; }
        .avatar-overlay-content svg { width: 18px; height: 18px; }
        .avatar-overlay-content span { font-size: 8px; }
        .avatar-info h3 { font-size: 12px; }
        .avatar-info p { font-size: 10px; }
        .avatar-actions { gap: 5px; margin-top: 9px; }
        .btn-upload, .btn-remove { padding: 6px 10px; font-size: 9px; border-radius: 7px; }
        .btn-upload svg, .btn-remove svg { width: 11px; height: 11px; }
        .avatar-file-name { font-size: 10px; }
        .field-group { margin-bottom: 13px; }
        .field-label { font-size: 9px; letter-spacing: 0.6px; margin-bottom: 6px; }
        .input-custom { padding: 10px 12px !important; font-size: 12px !important; border-radius: 8px !important; }
        .readonly-box { padding: 9px 11px; border-radius: 8px; font-size: 10px; gap: 5px; flex-direction: column; align-items: stretch; }
        .readonly-box .lock-icon { width: 22px; height: 22px; align-self: center; }
        .readonly-box .meta-item { font-size: 10px; padding: 3px 7px; justify-content: center; }
        .password-toggle svg { width: 14px; height: 14px; }
        .submit-wrap { padding-top: 16px; margin-top: 20px; }
        .btn-submit, .btn-delete { padding: 11px 24px; font-size: 10px; border-radius: 8px; letter-spacing: 0.9px; }
        .btn-submit svg, .btn-delete svg { width: 14px; height: 14px; }
    }

    /* Очень маленькие телефоны (до 380px) */
    @media (max-width: 380px) {
        .edit-user-page { padding: 18px 12px 32px; }
        .edit-topbar { margin-bottom: 12px; padding: 12px 10px; gap: 9px; border-radius: 9px; }
        .edit-topbar-left { gap: 8px; }
        .edit-topbar-icon { width: 36px; height: 36px; border-radius: 9px; }
        .edit-topbar-icon svg { width: 18px; height: 18px; }
        .edit-topbar-title { font-size: 14px; }
        .edit-topbar-subtitle { font-size: 9px; }
        .meta-pill { padding: 2px 5px !important; font-size: 8px !important; }
        .btn-back { padding: 8px 12px; font-size: 9px; border-radius: 7px; }
        .btn-back svg { width: 11px; height: 11px; }
        .form-card { padding: 18px 14px; border-radius: 9px; }
        .form-section { margin-bottom: 16px; }
        .section-title { font-size: 8px; letter-spacing: 1px; margin-bottom: 10px; }
        .avatar-block { padding: 14px; gap: 11px; border-radius: 9px; }
        .avatar-box { width: 75px; height: 75px; border-radius: 11px; }
        .avatar-letter { font-size: 32px; }
        .avatar-overlay-content svg { width: 16px; height: 16px; }
        .avatar-overlay-content span { font-size: 8px; }
        .avatar-info h3 { font-size: 12px; }
        .avatar-info p { font-size: 9px; }
        .avatar-actions { gap: 5px; }
        .btn-upload, .btn-remove { padding: 6px 9px; font-size: 8px; }
        .avatar-file-name { font-size: 9px; }
        .field-group { margin-bottom: 12px; }
        .field-label { font-size: 8px; letter-spacing: 0.5px; margin-bottom: 5px; }
        .input-custom { padding: 10px 11px !important; font-size: 11px !important; border-radius: 7px !important; }
        .readonly-box { padding: 8px 10px; }
        .readonly-box .meta-item { font-size: 9px; }
        .password-toggle svg { width: 13px; height: 13px; }
        .submit-wrap { padding-top: 14px; margin-top: 18px; }
        .btn-submit, .btn-delete { padding: 10px 22px; font-size: 10px; border-radius: 7px; }
        .btn-submit svg, .btn-delete svg { width: 13px; height: 13px; }
    }
</style>

<div class="edit-user-page">

    {{-- Фоновые blob-ы --}}
    <div class="edit-blob edit-blob-1"></div>
    <div class="edit-blob edit-blob-2"></div>
    <div class="edit-blob edit-blob-3"></div>

    <div class="edit-wrap">

        {{-- TOP BAR --}}
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

        {{-- FORM --}}
        <div class="form-card">
            <form method="POST" action="{{ route('users.update', $user->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- АВАТАР --}}
                <div class="form-section">
                    <div class="section-title" data-i18n="photoSection">Фото профиля</div>
                    <div class="avatar-block">
                        <div class="avatar-box">
                            @if($user->avatar)
                            <img id="avatarPreview" src="{{ asset('storage/' . $user->avatar) }}">
                            <span id="avatarLetter" class="avatar-letter" style="display: none;">{{ Str::upper(Str::substr($user->name, 0, 1)) }}</span>
                            @else
                            <img id="avatarPreview" src="" style="display: none;">
                            <span id="avatarLetter" class="avatar-letter">{{ Str::upper(Str::substr($user->name, 0, 1)) }}</span>
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
                            <input type="hidden" id="removeAvatarFlag" name="remove_avatar" value="0">
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
                                <button type="button" id="removeBtn" onclick="removeAvatar()" class="btn-remove" style="{{ !$user->avatar ? 'display: none;' : '' }}">
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
                        <input name="name" type="text" required class="input-custom" value="{{ $user->name }}">
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">
                                <span data-i18n="email">Email</span>
                                <span class="required">*</span>
                            </label>
                            <input name="email" type="email" required class="input-custom" value="{{ $user->email }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">
                                <span data-i18n="phone">Телефон</span>
                                <span class="required">*</span>
                            </label>
                            <input name="phone" type="text" id="phone" required class="input-custom" value="{{ $user->phone ?? '+992 ' }}">
                        </div>
                    </div>
                </div>

                {{-- РОЛЬ И УРОВЕНЬ --}}
                <div class="form-section">
                    <div class="section-title" data-i18n="accessSection">Роль и уровень</div>

                    @if((int)$user->created_by === auth()->id())
                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">
                                <span data-i18n="role">Роль</span>
                                <span class="required">*</span>
                            </label>
                            <input name="role" type="text" required class="input-custom" value="{{ $user->role }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">
                                <span data-i18n="level">Уровень</span> (1-20)
                                <span class="required">*</span>
                            </label>
                            <select name="level" required class="input-custom">
                                @for($i = 1; $i <= 20; $i++)
                                <option value="{{ $i }}" {{ $user->level == $i ? 'selected' : '' }}>{{ __('users.level') }} {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
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
            <div class="submit-wrap" style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--line);">
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
    // ПРЕВЬЮ АВАТАРА
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

    // ============================================================
    // УДАЛЕНИЕ АВАТАРА
    // ============================================================
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
    // ФОРМАТИРОВАНИЕ ТЕЛЕФОНА
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
    // TOGGLE PASSWORD
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
    // ПЕРЕВОДЫ
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {
        const EDIT_USER_TRANSLATIONS = {
            ru: {
                editUser: 'Редактировать',
                level: 'Уровень',
                backBtn: 'Назад',
                photo: 'Фото',
                photoDesc: 'JPG, PNG до 2MB',
                change: 'Изменить',
                upload: 'Загрузить',
                remove: 'Удалить',
                fullName: 'Полное имя',
                email: 'Email',
                phone: 'Телефон',
                role: 'Роль',
                newPassword: 'Новый пароль',
                leaveEmpty: 'оставьте пустым, чтобы не менять',
                save: 'Сохранить',
                delete: 'Удалить пользователя',
                photoSection: 'Фото профиля',
                mainInfo: 'Основная информация',
                accessSection: 'Роль и уровень',
                securitySection: 'Безопасность',
                confirmDelete: 'Удалить пользователя? Это действие необратимо.',
                confirmRemovePhoto: 'Удалить фотографию профиля?'
            },
            tj: {
                editUser: 'Таҳрир',
                level: 'Сатҳ',
                backBtn: 'Бозгашт',
                photo: 'Сурат',
                photoDesc: 'JPG, PNG то 2MB',
                change: 'Иваз кардан',
                upload: 'Боркунӣ',
                remove: 'Нест кардан',
                fullName: 'Номи пурра',
                email: 'Email',
                phone: 'Телефон',
                role: 'Вазифа',
                newPassword: 'Рамзи нав',
                leaveEmpty: 'холӣ гузоред',
                save: 'Нигоҳ доштан',
                delete: 'Нест кардани корбар',
                photoSection: 'Сурати профил',
                mainInfo: 'Маълумоти асосӣ',
                accessSection: 'Нақш ва сатҳ',
                securitySection: 'Амният',
                confirmDelete: 'Корбарро нест мекунед? Ин амал бозгашт надорад.',
                confirmRemovePhoto: 'Расми профилро нест мекунед?'
            },
            en: {
                editUser: 'Edit',
                level: 'Level',
                backBtn: 'Back',
                photo: 'Photo',
                photoDesc: 'JPG, PNG up to 2MB',
                change: 'Change',
                upload: 'Upload',
                remove: 'Remove',
                fullName: 'Full Name',
                email: 'Email',
                phone: 'Phone',
                role: 'Role',
                newPassword: 'New Password',
                leaveEmpty: 'leave empty to keep current',
                save: 'Save',
                delete: 'Delete User',
                photoSection: 'Profile Photo',
                mainInfo: 'Main Information',
                accessSection: 'Role & Level',
                securitySection: 'Security',
                confirmDelete: 'Delete this user? This action cannot be undone.',
                confirmRemovePhoto: 'Remove profile photo?'
            }
        };

        function applyEditUserTranslations(lang) {
            const dict = EDIT_USER_TRANSLATIONS[lang] || EDIT_USER_TRANSLATIONS.ru;

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

                const newEl = el.cloneNode(true);
                el.parentNode.replaceChild(newEl, el);

                if (newEl.tagName === 'FORM') {
                    newEl.onsubmit = (e) => {
                        if (!confirm(message)) e.preventDefault();
                    };
                } else {
                    const form = newEl.closest('form');
                    if (form) {
                        form.onsubmit = (e) => {
                            if (!confirm(message)) e.preventDefault();
                        };
                    }
                }
            });
        }

        // ПАРАЛЛАКС ДЛЯ ФОНОВЫХ ПЯТЕН
        const blobs = document.querySelectorAll('.edit-blob');
        document.addEventListener('mousemove', (e) => {
            const x = (e.clientX / window.innerWidth - 0.5) * 30;
            const y = (e.clientY / window.innerHeight - 0.5) * 30;
            blobs.forEach((blob, i) => {
                const factor = (i + 1) * 0.4;
                blob.style.transform = `translate(${x * factor}px, ${y * factor}px)`;
            });
        });

        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applyEditUserTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applyEditUserTranslations(lang);
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applyEditUserTranslations(e.newValue);
            }
        });
    });
</script>

@endsection