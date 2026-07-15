@extends('layouts.admin')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    .create-user-page {
        min-height: 100vh;
        padding: 40px 24px 60px;
        color: var(--text);
        font-family: 'Inter', sans-serif;
        position: relative;
    }

    /* Фоновые blob-ы */
    .create-blob {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
        filter: blur(100px);
        opacity: 0.35;
    }

    .create-blob-1 {
        top: -120px;
        left: -120px;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(var(--glow), 0.35) 0%, transparent 70%);
        animation: blobFloat 20s ease-in-out infinite;
    }

    .create-blob-2 {
        bottom: -120px;
        right: -120px;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(168, 85, 247, 0.28) 0%, transparent 70%);
        animation: blobFloat 25s ease-in-out infinite reverse;
    }

    .create-blob-3 {
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

    .create-wrap {
        max-width: 720px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    /* === TOP BAR === */
    .create-topbar {
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

    .create-topbar::before {
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
        border-radius: 13px;
        background: linear-gradient(135deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.4));
        display: grid;
        place-items: center;
        flex-shrink: 0;
        box-shadow: 0 0 24px rgba(var(--glow), 0.5), inset 0 0 12px rgba(255,255,255,0.2);
    }

    .create-topbar-icon svg {
        width: 24px;
        height: 24px;
        color: #0a0d14;
    }

    .create-topbar-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--text);
        letter-spacing: -0.3px;
        line-height: 1.2;
        margin: 0;
        word-break: break-word;
    }

    .create-topbar-subtitle {
        font-size: 12px;
        color: var(--muted);
        font-weight: 600;
        margin-top: 3px;
        word-break: break-word;
    }

    .create-topbar-subtitle strong {
        color: rgba(var(--glow), 1);
        font-weight: 700;
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
        gap: 20px;
        padding: 22px;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--line);
        border-radius: 14px;
        transition: all 0.3s ease;
        position: relative;
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

    .avatar-preview-wrap {
        position: relative;
        flex-shrink: 0;
    }

    .avatar-preview-box {
        width: 100px;
        height: 100px;
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(var(--glow), 0.4), rgba(168, 85, 247, 0.3));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 42px;
        font-weight: 900;
        font-style: italic;
        color: rgba(255,255,255,0.9);
        overflow: hidden;
        position: relative;
        box-shadow: 0 8px 24px rgba(var(--glow), 0.3), inset 0 0 20px rgba(255,255,255,0.1);
        border: 1px solid rgba(var(--glow), 0.3);
        text-shadow: 0 4px 16px rgba(0,0,0,0.5);
    }

    .avatar-preview-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        inset: 0;
    }

    .avatar-upload-btn {
        position: absolute;
        bottom: -6px;
        right: -6px;
        width: 34px;
        height: 34px;
        background: linear-gradient(135deg, rgba(var(--glow), 1), rgba(var(--glow), 0.7));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(var(--glow), 0.5), inset 0 1px 0 rgba(255,255,255,0.3);
        transition: all 0.2s ease;
        border: 2px solid var(--bg-0, #06070b);
    }

    .avatar-upload-btn:hover {
        transform: scale(1.1) rotate(90deg);
        box-shadow: 0 6px 20px rgba(var(--glow), 0.7);
    }

    .avatar-upload-btn svg {
        width: 16px;
        height: 16px;
        color: #0a0d14;
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

    .avatar-file-name {
        font-size: 12px;
        color: rgba(var(--glow), 1);
        margin-top: 8px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
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
        color: rgba(var(--glow), 0.8);
        font-size: 12px;
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

    /* === INFO BANNER === */
    .info-banner {
        background: rgba(var(--glow), 0.08);
        border: 1px solid rgba(var(--glow), 0.25);
        border-radius: 12px;
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
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, rgba(var(--glow), 1), rgba(var(--glow), 0.4));
        box-shadow: 0 0 12px rgba(var(--glow), 0.6);
    }

    .info-banner-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(var(--glow), 0.18);
        border: 1px solid rgba(var(--glow), 0.35);
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }

    .info-banner-icon svg {
        width: 18px;
        height: 18px;
        color: rgba(var(--glow), 1);
    }

    .info-banner-text {
        font-size: 13px;
        color: var(--text);
        font-weight: 500;
        line-height: 1.5;
        word-break: break-word;
    }

    .info-banner-text strong {
        color: rgba(var(--glow), 1);
        font-weight: 700;
    }

    /* === SUBMIT === */
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

    /* ============================================ */
    /* === ПОЛНАЯ АДАПТИВНОСТЬ === */
    /* ============================================ */

    /* Маленькие десктопы (до 1200px) */
    @media (max-width: 1200px) {
        .create-user-page { padding: 36px 22px 55px; }
        .create-topbar { margin-bottom: 22px; padding: 17px 20px; gap: 14px; }
        .create-topbar-icon { width: 46px; height: 46px; border-radius: 12px; }
        .create-topbar-icon svg { width: 23px; height: 23px; }
        .create-topbar-title { font-size: 19px; }
        .create-topbar-subtitle { font-size: 11px; }
        .form-card { padding: 32px 28px; }
        .form-section { margin-bottom: 26px; }
        .section-title { font-size: 10px; margin-bottom: 15px; }
        .avatar-block { padding: 20px; gap: 18px; }
        .avatar-preview-box { width: 95px; height: 95px; font-size: 40px; }
        .avatar-upload-btn { width: 32px; height: 32px; }
        .avatar-info h3 { font-size: 14px; }
        .input-custom { padding: 12px 15px !important; font-size: 13px !important; }
        .info-banner { padding: 15px 17px; gap: 13px; }
        .info-banner-text { font-size: 12px; }
        .btn-submit { padding: 13px 34px; font-size: 12px; }
    }

    /* Планшеты (до 992px) */
    @media (max-width: 992px) {
        .create-user-page { padding: 32px 20px 50px; }
        .create-topbar { margin-bottom: 20px; padding: 16px 18px; gap: 12px; border-radius: 13px; }
        .create-topbar-left { gap: 12px; }
        .create-topbar-icon { width: 44px; height: 44px; border-radius: 12px; }
        .create-topbar-icon svg { width: 22px; height: 22px; }
        .create-topbar-title { font-size: 18px; }
        .create-topbar-subtitle { font-size: 11px; margin-top: 2px; }
        .btn-back { padding: 9px 16px; font-size: 11px; border-radius: 9px; gap: 7px; }
        .btn-back svg { width: 13px; height: 13px; }
        .form-card { padding: 28px 24px; border-radius: 13px; }
        .form-section { margin-bottom: 24px; }
        .section-title { font-size: 10px; letter-spacing: 1.4px; margin-bottom: 14px; gap: 9px; }
        .section-title::before { width: 3px; height: 13px; }
        .avatar-block { padding: 18px; gap: 16px; border-radius: 13px; }
        .avatar-preview-box { width: 90px; height: 90px; font-size: 38px; border-radius: 15px; }
        .avatar-upload-btn { width: 30px; height: 30px; border-radius: 9px; bottom: -5px; right: -5px; }
        .avatar-upload-btn svg { width: 15px; height: 15px; }
        .avatar-info h3 { font-size: 14px; margin-bottom: 3px; }
        .avatar-info p { font-size: 11px; }
        .avatar-file-name { font-size: 11px; margin-top: 7px; }
        .field-group { margin-bottom: 16px; }
        .field-row { gap: 14px; }
        .field-label { font-size: 10px; letter-spacing: 0.9px; margin-bottom: 7px; }
        .input-custom { padding: 12px 14px !important; font-size: 13px !important; border-radius: 9px !important; }
        .note-text { font-size: 10px; margin-top: 7px; }
        .password-toggle { right: 11px; padding: 5px; }
        .password-toggle svg { width: 17px; height: 17px; }
        .info-banner { padding: 14px 16px; gap: 12px; border-radius: 11px; }
        .info-banner-icon { width: 34px; height: 34px; border-radius: 9px; }
        .info-banner-icon svg { width: 17px; height: 17px; }
        .info-banner-text { font-size: 12px; }
        .submit-wrap { padding-top: 22px; margin-top: 26px; }
        .btn-submit { padding: 12px 32px; font-size: 12px; border-radius: 10px; letter-spacing: 1.1px; }
        .btn-submit svg { width: 17px; height: 17px; }
    }

    /* Большие телефоны (до 768px) */
    @media (max-width: 768px) {
        .create-user-page { padding: 28px 18px 45px; }
        .create-topbar { margin-bottom: 18px; padding: 15px 16px; gap: 11px; border-radius: 12px; }
        .create-topbar-left { gap: 11px; }
        .create-topbar-icon { width: 42px; height: 42px; border-radius: 11px; }
        .create-topbar-icon svg { width: 21px; height: 21px; }
        .create-topbar-title { font-size: 17px; }
        .create-topbar-subtitle { font-size: 11px; }
        .btn-back { padding: 9px 15px; font-size: 11px; border-radius: 9px; gap: 6px; letter-spacing: 0.7px; }
        .btn-back svg { width: 13px; height: 13px; }
        .form-card { padding: 24px 20px; border-radius: 12px; }
        .form-section { margin-bottom: 22px; }
        .section-title { font-size: 10px; letter-spacing: 1.3px; margin-bottom: 13px; gap: 8px; }
        .section-title::before { width: 3px; height: 12px; }
        .avatar-block { padding: 16px; gap: 15px; border-radius: 12px; }
        .avatar-preview-box { width: 85px; height: 85px; font-size: 36px; border-radius: 14px; }
        .avatar-upload-btn { width: 28px; height: 28px; border-radius: 8px; bottom: -5px; right: -5px; }
        .avatar-upload-btn svg { width: 14px; height: 14px; }
        .avatar-info h3 { font-size: 13px; }
        .avatar-info p { font-size: 11px; }
        .avatar-file-name { font-size: 11px; margin-top: 6px; }
        .field-group { margin-bottom: 15px; }
        .field-row { gap: 13px; }
        .field-label { font-size: 10px; letter-spacing: 0.8px; margin-bottom: 7px; }
        .input-custom { padding: 11px 14px !important; font-size: 13px !important; border-radius: 9px !important; }
        .note-text { font-size: 10px; margin-top: 6px; }
        .password-toggle { right: 10px; }
        .password-toggle svg { width: 16px; height: 16px; }
        .info-banner { padding: 13px 15px; gap: 11px; border-radius: 10px; }
        .info-banner-icon { width: 32px; height: 32px; border-radius: 8px; }
        .info-banner-icon svg { width: 16px; height: 16px; }
        .info-banner-text { font-size: 11px; line-height: 1.45; }
        .submit-wrap { padding-top: 20px; margin-top: 24px; }
        .btn-submit { padding: 12px 30px; font-size: 11px; border-radius: 10px; letter-spacing: 1px; }
        .btn-submit svg { width: 16px; height: 16px; }
    }

    /* Телефоны (до 640px) */
    @media (max-width: 640px) {
        .create-user-page { padding: 24px 16px 40px; }
        .create-topbar {
            flex-direction: column;
            align-items: stretch;
            margin-bottom: 16px;
            padding: 14px;
            gap: 12px;
            border-radius: 11px;
        }
        .create-topbar-left { gap: 10px; width: 100%; }
        .create-topbar-icon { width: 40px; height: 40px; border-radius: 10px; }
        .create-topbar-icon svg { width: 20px; height: 20px; }
        .create-topbar-title { font-size: 16px; }
        .create-topbar-subtitle { font-size: 10px; }
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
        .avatar-preview-box { width: 80px; height: 80px; font-size: 34px; border-radius: 13px; }
        .avatar-upload-btn { width: 28px; height: 28px; bottom: -4px; right: -4px; }
        .avatar-info { text-align: center; width: 100%; }
        .avatar-info h3 { font-size: 13px; }
        .avatar-info p { font-size: 10px; }
        .avatar-file-name { font-size: 10px; margin-top: 6px; justify-content: center; }
        .field-group { margin-bottom: 14px; }
        .field-row { grid-template-columns: 1fr; gap: 14px; }
        .field-label { font-size: 9px; letter-spacing: 0.7px; margin-bottom: 6px; }
        .input-custom { padding: 11px 13px !important; font-size: 12px !important; border-radius: 8px !important; }
        .note-text { font-size: 10px; margin-top: 6px; }
        .password-toggle { right: 10px; padding: 5px; }
        .password-toggle svg { width: 15px; height: 15px; }
        .info-banner { padding: 12px 14px; gap: 10px; border-radius: 9px; }
        .info-banner-icon { width: 30px; height: 30px; border-radius: 8px; }
        .info-banner-icon svg { width: 15px; height: 15px; }
        .info-banner-text { font-size: 11px; }
        .submit-wrap { padding-top: 18px; margin-top: 22px; }
        .btn-submit {
            padding: 12px 28px;
            font-size: 11px;
            border-radius: 9px;
            letter-spacing: 1px;
            width: 100%;
            max-width: 320px;
            justify-content: center;
        }
        .btn-submit svg { width: 15px; height: 15px; }
    }

    /* Маленькие телефоны (до 480px) */
    @media (max-width: 480px) {
        .create-user-page { padding: 20px 14px 36px; }
        .create-topbar { margin-bottom: 14px; padding: 13px 12px; gap: 10px; border-radius: 10px; }
        .create-topbar-left { gap: 9px; }
        .create-topbar-icon { width: 38px; height: 38px; border-radius: 10px; }
        .create-topbar-icon svg { width: 19px; height: 19px; }
        .create-topbar-title { font-size: 15px; }
        .create-topbar-subtitle { font-size: 10px; }
        .btn-back { padding: 9px 13px; font-size: 10px; border-radius: 8px; letter-spacing: 0.6px; }
        .btn-back svg { width: 12px; height: 12px; }
        .form-card { padding: 20px 16px; border-radius: 10px; }
        .form-section { margin-bottom: 18px; }
        .section-title { font-size: 9px; letter-spacing: 1.1px; margin-bottom: 11px; }
        .avatar-block { padding: 16px; gap: 12px; border-radius: 10px; }
        .avatar-preview-box { width: 75px; height: 75px; font-size: 32px; border-radius: 12px; }
        .avatar-upload-btn { width: 26px; height: 26px; border-radius: 7px; }
        .avatar-upload-btn svg { width: 13px; height: 13px; }
        .avatar-info h3 { font-size: 12px; }
        .avatar-info p { font-size: 10px; }
        .avatar-file-name { font-size: 10px; }
        .field-group { margin-bottom: 13px; }
        .field-label { font-size: 9px; letter-spacing: 0.6px; margin-bottom: 6px; }
        .input-custom { padding: 10px 12px !important; font-size: 12px !important; border-radius: 8px !important; }
        .note-text { font-size: 9px; margin-top: 5px; }
        .password-toggle { right: 9px; }
        .password-toggle svg { width: 14px; height: 14px; }
        .info-banner { padding: 11px 13px; gap: 9px; border-radius: 8px; }
        .info-banner-icon { width: 28px; height: 28px; border-radius: 7px; }
        .info-banner-icon svg { width: 14px; height: 14px; }
        .info-banner-text { font-size: 10px; }
        .submit-wrap { padding-top: 16px; margin-top: 20px; }
        .btn-submit { padding: 11px 24px; font-size: 10px; border-radius: 8px; letter-spacing: 0.9px; }
        .btn-submit svg { width: 14px; height: 14px; }
    }

    /* Очень маленькие телефоны (до 380px) */
    @media (max-width: 380px) {
        .create-user-page { padding: 18px 12px 32px; }
        .create-topbar { margin-bottom: 12px; padding: 12px 10px; gap: 9px; border-radius: 9px; }
        .create-topbar-left { gap: 8px; }
        .create-topbar-icon { width: 36px; height: 36px; border-radius: 9px; }
        .create-topbar-icon svg { width: 18px; height: 18px; }
        .create-topbar-title { font-size: 14px; }
        .create-topbar-subtitle { font-size: 9px; }
        .btn-back { padding: 8px 12px; font-size: 9px; border-radius: 7px; }
        .btn-back svg { width: 11px; height: 11px; }
        .form-card { padding: 18px 14px; border-radius: 9px; }
        .form-section { margin-bottom: 16px; }
        .section-title { font-size: 8px; letter-spacing: 1px; margin-bottom: 10px; }
        .avatar-block { padding: 14px; gap: 11px; border-radius: 9px; }
        .avatar-preview-box { width: 70px; height: 70px; font-size: 30px; border-radius: 11px; }
        .avatar-upload-btn { width: 24px; height: 24px; border-radius: 7px; }
        .avatar-upload-btn svg { width: 12px; height: 12px; }
        .avatar-info h3 { font-size: 12px; }
        .avatar-info p { font-size: 9px; }
        .avatar-file-name { font-size: 9px; }
        .field-group { margin-bottom: 12px; }
        .field-label { font-size: 8px; letter-spacing: 0.5px; margin-bottom: 5px; }
        .input-custom { padding: 10px 11px !important; font-size: 11px !important; border-radius: 7px !important; }
        .note-text { font-size: 9px; }
        .password-toggle svg { width: 13px; height: 13px; }
        .info-banner { padding: 10px 12px; gap: 8px; border-radius: 7px; }
        .info-banner-icon { width: 26px; height: 26px; border-radius: 6px; }
        .info-banner-icon svg { width: 13px; height: 13px; }
        .info-banner-text { font-size: 10px; }
        .submit-wrap { padding-top: 14px; margin-top: 18px; }
        .btn-submit { padding: 10px 22px; font-size: 10px; border-radius: 7px; }
        .btn-submit svg { width: 13px; height: 13px; }
    }
</style>

<div class="create-user-page">

    {{-- Фоновые blob-ы --}}
    <div class="create-blob create-blob-1"></div>
    <div class="create-blob create-blob-2"></div>
    <div class="create-blob create-blob-3"></div>

    <div class="create-wrap">

        {{-- TOP BAR --}}
        <div class="create-topbar">
            <div class="create-topbar-left">
                <div class="create-topbar-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div>
                    <div class="create-topbar-title" data-i18n="newUser">{{ __('users.new_user') }}</div>
                    <div class="create-topbar-subtitle">
                        <span data-i18n="companyLabel">{{ __('users.company') }}</span>:
                        <strong>{{ auth()->user()->company ?? '—' }}</strong>
                    </div>
                </div>
            </div>

            <a href="{{ route('users.index') }}" class="btn-back">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                <span data-i18n="backToList">{{ __('users.back_to_list') }}</span>
            </a>
        </div>

        {{-- FORM --}}
        <div class="form-card">
            <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- АВАТАР --}}
                <div class="form-section">
                    <div class="section-title" data-i18n="photoSection">Фото профиля</div>
                    <div class="avatar-block">
                        <div class="avatar-preview-wrap">
                            <div class="avatar-preview-box">
                                <span id="avatarLetter">?</span>
                                <img id="avatarPreview" src="" style="display: none;">
                            </div>
                            <label for="avatarInput" class="avatar-upload-btn" title="Upload">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                            </label>
                            <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
                        </div>
                        <div class="avatar-info">
                            <h3 data-i18n="photo">{{ __('users.photo') }}</h3>
                            <p data-i18n="photoDesc">{{ __('users.photo_desc') }}</p>
                            <p id="fileNameDisplay" class="avatar-file-name"></p>
                        </div>
                    </div>
                </div>

                {{-- ОСНОВНЫЕ ДАННЫЕ --}}
                <div class="form-section">
                    <div class="section-title" data-i18n="mainInfo">Основная информация</div>

                    <div class="field-group">
                        <label class="field-label">
                            <span data-i18n="fullName">{{ __('users.full_name') }}</span>
                            <span class="required">*</span>
                        </label>
                        <input name="name" type="text" required class="input-custom" placeholder="Иван Иванов" id="nameInput">
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">
                                <span data-i18n="email">{{ __('users.email') }}</span>
                                <span class="required">*</span>
                            </label>
                            <input name="email" type="email" required class="input-custom" placeholder="mail@example.com">
                        </div>
                        <div class="field-group">
                            <label class="field-label">
                                <span data-i18n="phone">{{ __('users.phone') }}</span>
                                <span class="required">*</span>
                            </label>
                            <input name="phone" type="text" id="phone" required class="input-custom" placeholder="+992 00 000 0000">
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">
                            <span data-i18n="role">{{ __('users.role') }}</span>
                            <span class="required">*</span>
                        </label>
                        <input name="role" type="text" required class="input-custom" placeholder="{{ __('users.role_placeholder') }}">
                    </div>
                </div>

                {{-- ДОСТУП --}}
                <div class="form-section">
                    <div class="section-title" data-i18n="accessSection">Доступ и безопасность</div>

                    <div class="field-group">
                        <label class="field-label">
                            <span data-i18n="level">{{ __('users.level') }}</span> (2-20)
                            <span class="required">*</span>
                        </label>
                        <select name="level" required class="input-custom">
                            @for($i = 2; $i <= 20; $i++)
                            <option value="{{ $i }}">{{ __('users.level') }} {{ $i }}</option>
                            @endfor
                        </select>
                        <p class="note-text"><span data-i18n="levelNote">{{ __('users.level_note') }}</span></p>
                    </div>

                    <div class="field-group">
                        <label class="field-label">
                            <span data-i18n="password">{{ __('users.password') }}</span>
                            <span class="required">*</span>
                        </label>
                        <div class="password-wrap">
                            <input name="password" type="password" id="password" required class="input-custom" style="padding-right: 48px !important;" placeholder="{{ __('users.password_placeholder') }}">
                            <button type="button" onclick="togglePassword()" class="password-toggle" aria-label="Toggle password">
                                <svg id="eyeIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ИНФО О КОМПАНИИ --}}
                <div class="form-section">
                    <div class="info-banner">
                        <div class="info-banner-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="info-banner-text">
                            <strong data-i18n="autoCompany">{{ __('users.auto_company') }}:</strong>
                            <strong>{{ auth()->user()->company ?? '—' }}</strong>
                        </div>
                    </div>
                </div>

                {{-- КНОПКА --}}
                <div class="submit-wrap">
                    <button type="submit" class="btn-submit">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span data-i18n="createUser">{{ __('users.create_user') }}</span>
                    </button>
                </div>
            </form>
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
                document.getElementById('fileNameDisplay').textContent = '📎 ' + file.name;
            }
            reader.readAsDataURL(file);
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
    // ОБНОВЛЕНИЕ БУКВЫ АВАТАРА
    // ============================================================
    const nameInput = document.getElementById('nameInput');
    if (nameInput) {
        nameInput.addEventListener('input', function(e) {
            const letter = e.target.value.trim().charAt(0).toUpperCase();
            document.getElementById('avatarLetter').textContent = letter || '?';
        });
    }

    // ============================================================
    // ПЕРЕВОДЫ И ПАРАЛЛАКС
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {
        const CREATE_USER_TRANSLATIONS = {
            ru: {
                newUser: 'Новый пользователь',
                companyLabel: 'Компания',
                backToList: 'Назад к списку',
                photo: 'Фото',
                photoDesc: 'JPG, PNG до 2MB',
                fullName: 'Полное имя',
                email: 'Email',
                phone: 'Телефон',
                role: 'Роль',
                level: 'Уровень',
                levelNote: 'Уровень 1 зарезервирован для администратора',
                password: 'Пароль',
                autoCompany: 'Компания назначается автоматически',
                createUser: 'Создать пользователя',
                photoSection: 'Фото профиля',
                mainInfo: 'Основная информация',
                accessSection: 'Доступ и безопасность'
            },
            tj: {
                newUser: 'Корбари нав',
                companyLabel: 'Ширкат',
                backToList: 'Бозгашт ба рӯйхат',
                photo: 'Сурат',
                photoDesc: 'JPG, PNG то 2MB',
                fullName: 'Номи пурра',
                email: 'Email',
                phone: 'Телефон',
                role: 'Вазифа',
                level: 'Сатҳ',
                levelNote: 'Сатҳи 1 барои администратор аст',
                password: 'Рамз',
                autoCompany: 'Ширкат автоматикӣ таъин мешавад',
                createUser: 'Эҷоди корбар',
                photoSection: 'Сурати профил',
                mainInfo: 'Маълумоти асосӣ',
                accessSection: 'Дастрасӣ ва амният'
            },
            en: {
                newUser: 'New User',
                companyLabel: 'Company',
                backToList: 'Back to list',
                photo: 'Photo',
                photoDesc: 'JPG, PNG up to 2MB',
                fullName: 'Full Name',
                email: 'Email',
                phone: 'Phone',
                role: 'Role',
                level: 'Level',
                levelNote: 'Level 1 is reserved for admin',
                password: 'Password',
                autoCompany: 'Company is assigned automatically',
                createUser: 'Create User',
                photoSection: 'Profile Photo',
                mainInfo: 'Main Information',
                accessSection: 'Access & Security'
            }
        };

        function applyCreateUserTranslations(lang) {
            const dict = CREATE_USER_TRANSLATIONS[lang] || CREATE_USER_TRANSLATIONS.ru;

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
        }

        // ПАРАЛЛАКС ДЛЯ ФОНОВЫХ ПЯТЕН
        const blobs = document.querySelectorAll('.create-blob');
        document.addEventListener('mousemove', (e) => {
            const x = (e.clientX / window.innerWidth - 0.5) * 30;
            const y = (e.clientY / window.innerHeight - 0.5) * 30;
            blobs.forEach((blob, i) => {
                const factor = (i + 1) * 0.4;
                blob.style.transform = `translate(${x * factor}px, ${y * factor}px)`;
            });
        });

        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applyCreateUserTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applyCreateUserTranslations(lang);
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applyCreateUserTranslations(e.newValue);
            }
        });
    });
</script>
@endsection