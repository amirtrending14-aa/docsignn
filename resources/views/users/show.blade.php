@extends('layouts.admin')

@section('content')

@if(session('error') || session('success'))
<div class="show-toast-wrap">
    <div class="show-toast {{ session('error') ? 'show-toast-error' : 'show-toast-success' }}">
        @if(session('error'))
        <div class="show-toast-icon show-toast-icon-error">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        @else
        <div class="show-toast-icon show-toast-icon-success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        @endif
        <div class="show-toast-text">{{ session('error') ?? session('success') }}</div>
        <button onclick="this.closest('.show-toast-wrap').remove()" class="show-toast-close">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
@endif

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    .show-user-page {
        min-height: 100vh;
        padding: 40px 24px 60px;
        color: var(--text);
        font-family: 'Inter', sans-serif;
        position: relative;
    }

    /* Toast */
    .show-toast-wrap {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        max-width: 440px;
        z-index: 200;
        padding: 0 16px;
        animation: toastSlideDown 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes toastSlideDown {
        0% { opacity: 0; transform: translate(-50%, -20px); }
        100% { opacity: 1; transform: translate(-50%, 0); }
    }

    .show-toast {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        border-radius: 14px;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid;
        box-shadow: 0 20px 50px -10px rgba(0,0,0,0.5);
    }

    .show-toast-success {
        background: rgba(76, 217, 130, 0.12);
        border-color: rgba(76, 217, 130, 0.35);
        color: #4cd982;
    }

    .show-toast-error {
        background: rgba(255, 99, 99, 0.12);
        border-color: rgba(255, 99, 99, 0.35);
        color: #ff6363;
    }

    .show-toast-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }

    .show-toast-icon svg { width: 18px; height: 18px; }

    .show-toast-icon-success {
        background: rgba(76, 217, 130, 0.2);
        border: 1px solid rgba(76, 217, 130, 0.4);
        box-shadow: 0 0 16px rgba(76, 217, 130, 0.3);
    }

    .show-toast-icon-error {
        background: rgba(255, 99, 99, 0.2);
        border: 1px solid rgba(255, 99, 99, 0.4);
        box-shadow: 0 0 16px rgba(255, 99, 99, 0.3);
    }

    .show-toast-text {
        flex: 1;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: -0.1px;
        color: var(--text);
        word-break: break-word;
    }

    .show-toast-close {
        background: transparent;
        border: none;
        color: var(--muted);
        cursor: pointer;
        padding: 4px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .show-toast-close:hover {
        color: var(--text);
        background: rgba(255,255,255,0.08);
    }

    .show-toast-close svg { width: 14px; height: 14px; }

    /* Фоновые blob-ы */
    .show-blob {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
        filter: blur(100px);
        opacity: 0.35;
    }

    .show-blob-1 {
        top: -120px;
        left: -120px;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(var(--glow), 0.35) 0%, transparent 70%);
        animation: blobFloat 20s ease-in-out infinite;
    }

    .show-blob-2 {
        bottom: -120px;
        right: -120px;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(168, 85, 247, 0.28) 0%, transparent 70%);
        animation: blobFloat 25s ease-in-out infinite reverse;
    }

    .show-blob-3 {
        top: 40%;
        left: 50%;
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

    .show-wrap {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    /* === TOP BAR === */
    .show-topbar {
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

    .show-topbar::before {
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

    .show-topbar-left {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
        flex: 1;
    }

    .show-topbar-icon {
        width: 48px;
        height: 48px;
        border-radius: 13px;
        background: linear-gradient(135deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.4));
        display: grid;
        place-items: center;
        flex-shrink: 0;
        box-shadow: 0 0 24px rgba(var(--glow), 0.5), inset 0 0 12px rgba(255,255,255,0.2);
    }

    .show-topbar-icon svg {
        width: 24px;
        height: 24px;
        color: #0a0d14;
    }

    .show-topbar-title {
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

    .show-topbar-subtitle {
        font-size: 12px;
        color: var(--muted);
        font-weight: 600;
        margin-top: 3px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .show-topbar-subtitle .meta-pill {
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

    .show-topbar-subtitle .meta-pill svg {
        flex-shrink: 0;
    }

    .show-topbar-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-edit {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: linear-gradient(180deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.65));
        color: #0a0d14;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        transition: all 0.25s ease;
        box-shadow: 0 8px 24px rgba(var(--glow), 0.35), inset 0 1px 0 rgba(255,255,255,0.3);
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(var(--glow), 0.5);
        filter: brightness(1.08);
    }

    .btn-edit svg {
        width: 14px;
        height: 14px;
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

    /* === PROFILE GRID === */
    .profile-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 18px;
        margin-bottom: 24px;
    }

    @media (min-width: 1024px) {
        .profile-grid {
            grid-template-columns: 340px 1fr;
        }
    }

    /* === LEFT CARD (Profile) === */
    .profile-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
        border: 1px solid var(--line);
        border-radius: var(--radius);
        position: relative;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        overflow: hidden;
    }

    .profile-card::before {
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

    .profile-left {
        padding: 32px 24px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }

    .profile-left-bg {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 110px;
        background: linear-gradient(135deg, rgba(var(--glow), 0.35), rgba(168, 85, 247, 0.25));
        overflow: hidden;
    }

    .profile-left-bg::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 40%, var(--bg-0, #06070b) 100%);
        opacity: 0.85;
    }

    .avatar-sq {
        width: 110px;
        height: 110px;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(var(--glow), 0.5), rgba(168, 85, 247, 0.35));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 46px;
        font-weight: 900;
        font-style: italic;
        color: rgba(255,255,255,0.9);
        overflow: hidden;
        margin-bottom: 18px;
        box-shadow: 0 10px 30px rgba(var(--glow), 0.4), inset 0 0 20px rgba(255,255,255,0.1);
        border: 3px solid var(--bg-0, #06070b);
        position: relative;
        z-index: 1;
        text-shadow: 0 4px 16px rgba(0,0,0,0.5);
    }

    .avatar-sq img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-name {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
        margin: 0 0 6px;
        letter-spacing: -0.3px;
        line-height: 1.2;
        text-align: center;
        word-break: break-word;
    }

    .profile-email {
        font-size: 13px;
        color: var(--muted);
        font-weight: 500;
        margin-bottom: 18px;
        word-break: break-all;
        display: flex;
        align-items: center;
        gap: 6px;
        text-align: center;
        justify-content: center;
    }

    .profile-email::before {
        content: "✉";
        color: rgba(var(--glow), 0.9);
        font-size: 13px;
        flex-shrink: 0;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 11px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        background: linear-gradient(180deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.65));
        color: #0a0d14;
        box-shadow: 0 8px 24px rgba(var(--glow), 0.4), inset 0 1px 0 rgba(255,255,255,0.3);
        border: 1px solid transparent;
    }

    .role-badge .role-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #0a0d14;
        box-shadow: 0 0 8px rgba(0,0,0,0.5);
    }

    /* === RIGHT CARD (Details) === */
    .profile-right {
        display: flex;
        flex-direction: column;
    }

    .profile-right-header {
        padding: 16px 24px;
        border-bottom: 1px solid var(--line);
        background: rgba(255,255,255,0.03);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .profile-right-header::before {
        content: "";
        width: 4px;
        height: 16px;
        border-radius: 2px;
        background: linear-gradient(180deg, rgba(var(--glow), 1), rgba(var(--glow), 0.4));
        box-shadow: 0 0 10px rgba(var(--glow), 0.6);
        flex-shrink: 0;
    }

    .profile-right-header h3 {
        font-size: 12px;
        font-weight: 800;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin: 0;
    }

    .profile-right-body {
        padding: 28px 24px;
        flex-grow: 1;
    }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    @media (min-width: 640px) {
        .info-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    .info-item {
        padding: 14px 16px;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--line);
        border-radius: 12px;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .info-item::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, rgba(var(--glow), 0.8), rgba(var(--glow), 0.2));
        opacity: 0;
        transition: opacity 0.25s ease;
    }

    .info-item:hover {
        background: rgba(var(--glow), 0.05);
        border-color: rgba(var(--glow), 0.25);
        transform: translateY(-2px);
    }

    .info-item:hover::before {
        opacity: 1;
    }

    .info-label {
        display: flex;
        align-items: center;
        gap: 6px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 10px;
        font-weight: 800;
        color: var(--muted);
        margin-bottom: 8px;
    }

    .info-label i {
        color: rgba(var(--glow), 0.9);
        font-size: 12px;
        flex-shrink: 0;
    }

    .info-value {
        color: var(--text);
        font-size: 15px;
        font-weight: 700;
        letter-spacing: -0.2px;
        word-break: break-word;
    }

    /* Status section */
    .status-section {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid var(--line);
    }

    .status-indicator {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        background: rgba(76, 217, 130, 0.08);
        border: 1px solid rgba(76, 217, 130, 0.25);
        border-radius: 12px;
        position: relative;
        overflow: hidden;
    }

    .status-indicator::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, #4cd982, rgba(76, 217, 130, 0.3));
        box-shadow: 0 0 12px rgba(76, 217, 130, 0.6);
    }

    .status-dot-wrap {
        position: relative;
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    .status-dot {
        position: absolute;
        inset: 3px;
        border-radius: 50%;
        background: #4cd982;
        box-shadow: 0 0 12px #4cd982;
    }

    .status-dot-pulse {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: rgba(76, 217, 130, 0.3);
        animation: statusPulse 2s ease-in-out infinite;
    }

    @keyframes statusPulse {
        0%, 100% { transform: scale(1); opacity: 0.6; }
        50% { transform: scale(1.6); opacity: 0; }
    }

    .status-text {
        font-size: 13px;
        font-weight: 800;
        color: #4cd982;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* === ACTIVITY CARD === */
    .activity-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 28px;
        position: relative;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        overflow: hidden;
    }

    .activity-card::before {
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

    .activity-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .activity-title-wrap {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .activity-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: rgba(var(--glow), 0.15);
        border: 1px solid rgba(var(--glow), 0.3);
        display: grid;
        place-items: center;
        flex-shrink: 0;
        box-shadow: inset 0 0 12px rgba(var(--glow), 0.2), 0 0 16px rgba(var(--glow), 0.15);
    }

    .activity-icon svg {
        width: 20px;
        height: 20px;
        color: rgba(var(--glow), 1);
    }

    .activity-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--text);
        margin: 0;
        letter-spacing: -0.2px;
    }

    .activity-title .count-accent {
        color: rgba(var(--glow), 1);
        text-shadow: 0 0 14px rgba(var(--glow), 0.5);
        font-family: 'JetBrains Mono', monospace;
        font-size: 20px;
        font-weight: 800;
    }

    .activity-subtitle {
        font-size: 12px;
        color: var(--muted);
        font-weight: 600;
        margin-top: 2px;
    }

    .gh-wrapper {
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(var(--glow), 0.3) transparent;
        position: relative;
        padding-bottom: 4px;
    }

    .gh-wrapper::-webkit-scrollbar {
        height: 6px;
    }

    .gh-wrapper::-webkit-scrollbar-track {
        background: rgba(255,255,255,0.03);
        border-radius: 3px;
    }

    .gh-wrapper::-webkit-scrollbar-thumb {
        background: rgba(var(--glow), 0.3);
        border-radius: 3px;
    }

    .gh-wrapper::-webkit-scrollbar-thumb:hover {
        background: rgba(var(--glow), 0.5);
    }

    .gh-grid {
        display: inline-grid;
        grid-template-areas: ". months" "days squares";
        grid-template-columns: 40px 1fr;
        gap: 6px 10px;
    }

    .gh-months {
        grid-area: months;
        display: grid;
        grid-template-columns: repeat({{ $weeksCount }}, 12px);
        gap: 4px;
        font-size: 10px;
        color: var(--muted);
        height: 18px;
        position: relative;
    }

    .gh-days {
        grid-area: days;
        display: grid;
        grid-template-rows: repeat(7, 12px);
        gap: 4px;
        font-size: 9px;
        color: var(--muted);
        user-select: none;
    }

    .gh-day-label {
        display: flex;
        align-items: center;
        height: 12px;
        line-height: 1;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .gh-squares {
        grid-area: squares;
        display: grid;
        grid-template-rows: repeat(7, 12px);
        grid-auto-flow: column;
        grid-auto-columns: 12px;
        gap: 4px;
    }

    .sq {
        width: 12px;
        height: 12px;
        border-radius: 3px;
        background-color: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        box-sizing: border-box;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .sq:hover {
        transform: scale(1.3);
        outline: 1px solid rgba(var(--glow), 0.6);
        z-index: 2;
        position: relative;
    }

    .l1 {
        background-color: rgba(var(--glow), 0.22) !important;
        border-color: rgba(var(--glow), 0.35) !important;
        box-shadow: inset 0 0 4px rgba(var(--glow), 0.15);
    }

    .l2 {
        background-color: rgba(var(--glow), 0.42) !important;
        border-color: rgba(var(--glow), 0.55) !important;
        box-shadow: inset 0 0 6px rgba(var(--glow), 0.25), 0 0 4px rgba(var(--glow), 0.2);
    }

    .l3 {
        background-color: rgba(var(--glow), 0.65) !important;
        border-color: rgba(var(--glow), 0.8) !important;
        box-shadow: inset 0 0 8px rgba(var(--glow), 0.35), 0 0 8px rgba(var(--glow), 0.3);
    }

    .l4 {
        background-color: rgba(var(--glow), 0.95) !important;
        border-color: rgba(var(--glow), 1) !important;
        box-shadow: inset 0 0 10px rgba(255,255,255,0.2), 0 0 14px rgba(var(--glow), 0.6);
    }

    .activity-legend {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding-top: 18px;
        border-top: 1px solid var(--line);
        font-size: 11px;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .legend-items {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .legend-items .sq {
        width: 11px;
        height: 11px;
        cursor: default;
    }

    .legend-items .sq:hover {
        transform: none;
    }

    /* Tippy dark theme override */
    .tippy-box[data-theme~='dark-glow'] {
        background: rgba(10, 13, 20, 0.95);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(var(--glow), 0.35);
        border-radius: 10px;
        color: var(--text);
        font-size: 12px;
        font-weight: 600;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5), 0 0 20px rgba(var(--glow), 0.25);
    }

    .tippy-box[data-theme~='dark-glow'] .tippy-arrow {
        color: rgba(10, 13, 20, 0.95);
    }

    .tippy-box[data-theme~='dark-glow'] .tippy-arrow::before {
        border-top-color: rgba(var(--glow), 0.35);
    }

    /* ============================================ */
    /* === ПОЛНАЯ АДАПТИВНОСТЬ === */
    /* ============================================ */

    /* Маленькие десктопы (до 1200px) */
    @media (max-width: 1200px) {
        .show-user-page { padding: 36px 22px 55px; }
        .show-topbar { margin-bottom: 22px; padding: 17px 20px; gap: 14px; }
        .show-topbar-icon { width: 46px; height: 46px; border-radius: 12px; }
        .show-topbar-icon svg { width: 23px; height: 23px; }
        .show-topbar-title { font-size: 19px; }
        .show-topbar-subtitle { font-size: 11px; gap: 7px; }
        .meta-pill { padding: 3px 8px !important; font-size: 10px !important; }
        .btn-edit, .btn-back { padding: 9px 16px; font-size: 11px; }
        .profile-grid { gap: 16px; margin-bottom: 22px; }
        .profile-left { padding: 28px 22px; }
        .avatar-sq { width: 100px; height: 100px; border-radius: 18px; font-size: 42px; margin-bottom: 16px; }
        .profile-name { font-size: 20px; margin-bottom: 5px; }
        .profile-email { font-size: 12px; margin-bottom: 16px; }
        .role-badge { padding: 9px 18px; font-size: 10px; }
        .profile-right-header { padding: 15px 22px; }
        .profile-right-body { padding: 26px 22px; }
        .info-item { padding: 13px 15px; border-radius: 11px; }
        .info-value { font-size: 14px; }
        .status-indicator { padding: 13px 15px; }
        .activity-card { padding: 26px; }
        .activity-icon { width: 40px; height: 40px; border-radius: 11px; }
        .activity-icon svg { width: 19px; height: 19px; }
        .activity-title { font-size: 15px; }
        .activity-title .count-accent { font-size: 19px; }
    }

    /* Планшеты (до 992px) */
    @media (max-width: 992px) {
        .show-user-page { padding: 32px 20px 50px; }
        .show-topbar { margin-bottom: 20px; padding: 16px 18px; gap: 12px; border-radius: 13px; }
        .show-topbar-left { gap: 12px; }
        .show-topbar-icon { width: 44px; height: 44px; border-radius: 11px; }
        .show-topbar-icon svg { width: 22px; height: 22px; }
        .show-topbar-title { font-size: 18px; white-space: normal; }
        .show-topbar-subtitle { font-size: 11px; gap: 6px; margin-top: 2px; }
        .meta-pill { padding: 2px 7px !important; font-size: 10px !important; }
        .show-topbar-actions { gap: 8px; }
        .btn-edit, .btn-back { padding: 9px 15px; font-size: 11px; border-radius: 9px; gap: 7px; }
        .btn-edit svg, .btn-back svg { width: 13px; height: 13px; }
        .profile-grid { gap: 15px; margin-bottom: 20px; }
        .profile-left { padding: 26px 20px; }
        .profile-left-bg { height: 100px; }
        .avatar-sq { width: 95px; height: 95px; border-radius: 17px; font-size: 40px; margin-bottom: 15px; border-width: 2.5px; }
        .profile-name { font-size: 19px; margin-bottom: 5px; }
        .profile-email { font-size: 12px; margin-bottom: 15px; }
        .role-badge { padding: 9px 17px; font-size: 10px; border-radius: 10px; letter-spacing: 1.1px; }
        .profile-right-header { padding: 14px 20px; }
        .profile-right-header h3 { font-size: 11px; letter-spacing: 1.4px; }
        .profile-right-body { padding: 24px 20px; }
        .info-grid { gap: 18px; }
        .info-item { padding: 12px 14px; border-radius: 10px; }
        .info-label { font-size: 9px; letter-spacing: 0.9px; margin-bottom: 7px; }
        .info-label i { font-size: 11px; }
        .info-value { font-size: 13px; }
        .status-section { margin-top: 22px; padding-top: 18px; }
        .status-indicator { padding: 12px 14px; border-radius: 10px; }
        .status-dot-wrap { width: 13px; height: 13px; }
        .status-text { font-size: 12px; letter-spacing: 0.9px; }
        .activity-card { padding: 24px; border-radius: 13px; }
        .activity-header { margin-bottom: 18px; gap: 10px; }
        .activity-title-wrap { gap: 12px; }
        .activity-icon { width: 38px; height: 38px; border-radius: 10px; }
        .activity-icon svg { width: 18px; height: 18px; }
        .activity-title { font-size: 14px; }
        .activity-title .count-accent { font-size: 18px; }
        .activity-subtitle { font-size: 11px; }
        .gh-grid { grid-template-columns: 36px 1fr; gap: 5px 9px; }
        .gh-months { grid-template-columns: repeat({{ $weeksCount }}, 11px); font-size: 9px; }
        .gh-days { grid-template-rows: repeat(7, 11px); font-size: 8px; }
        .gh-day-label { height: 11px; }
        .gh-squares { grid-template-rows: repeat(7, 11px); grid-auto-columns: 11px; }
        .sq { width: 11px; height: 11px; border-radius: 2px; }
        .activity-legend { margin-top: 18px; padding-top: 16px; font-size: 10px; letter-spacing: 0.9px; }
        .legend-items .sq { width: 10px; height: 10px; }
    }

    /* Большие телефоны (до 768px) */
    @media (max-width: 768px) {
        .show-user-page { padding: 28px 18px 45px; }
        .show-topbar {
            flex-direction: column;
            align-items: stretch;
            margin-bottom: 18px;
            padding: 15px 16px;
            gap: 12px;
            border-radius: 12px;
        }
        .show-topbar-left { gap: 11px; width: 100%; }
        .show-topbar-icon { width: 42px; height: 42px; border-radius: 11px; }
        .show-topbar-icon svg { width: 21px; height: 21px; }
        .show-topbar-title { font-size: 17px; }
        .show-topbar-subtitle { font-size: 10px; gap: 6px; }
        .meta-pill { padding: 2px 7px !important; font-size: 10px !important; }
        .show-topbar-actions {
            width: 100%;
            gap: 8px;
        }
        .btn-edit, .btn-back {
            flex: 1;
            justify-content: center;
            padding: 10px 14px;
            font-size: 10px;
            border-radius: 9px;
        }
        .btn-edit svg, .btn-back svg { width: 12px; height: 12px; }
        .profile-grid { gap: 14px; margin-bottom: 18px; }
        .profile-left { padding: 24px 18px; }
        .profile-left-bg { height: 90px; }
        .avatar-sq { width: 90px; height: 90px; border-radius: 16px; font-size: 38px; margin-bottom: 14px; border-width: 2.5px; }
        .profile-name { font-size: 18px; margin-bottom: 4px; }
        .profile-email { font-size: 11px; margin-bottom: 14px; }
        .role-badge { padding: 8px 16px; font-size: 10px; border-radius: 9px; letter-spacing: 1px; }
        .profile-right-header { padding: 13px 18px; }
        .profile-right-header h3 { font-size: 10px; letter-spacing: 1.3px; }
        .profile-right-body { padding: 22px 18px; }
        .info-grid { gap: 16px; }
        .info-item { padding: 11px 13px; border-radius: 9px; }
        .info-label { font-size: 9px; letter-spacing: 0.8px; margin-bottom: 6px; }
        .info-value { font-size: 13px; }
        .status-section { margin-top: 20px; padding-top: 16px; }
        .status-indicator { padding: 11px 13px; border-radius: 9px; gap: 10px; }
        .status-dot-wrap { width: 12px; height: 12px; }
        .status-text { font-size: 11px; letter-spacing: 0.8px; }
        .activity-card { padding: 22px 18px; border-radius: 12px; }
        .activity-header { margin-bottom: 16px; gap: 10px; }
        .activity-title-wrap { gap: 11px; }
        .activity-icon { width: 36px; height: 36px; border-radius: 10px; }
        .activity-icon svg { width: 17px; height: 17px; }
        .activity-title { font-size: 13px; }
        .activity-title .count-accent { font-size: 17px; }
        .activity-subtitle { font-size: 11px; margin-top: 1px; }
        .gh-grid { grid-template-columns: 34px 1fr; gap: 5px 8px; }
        .gh-months { grid-template-columns: repeat({{ $weeksCount }}, 10px); font-size: 9px; height: 16px; }
        .gh-days { grid-template-rows: repeat(7, 10px); font-size: 8px; }
        .gh-day-label { height: 10px; font-size: 8px; }
        .gh-squares { grid-template-rows: repeat(7, 10px); grid-auto-columns: 10px; }
        .sq { width: 10px; height: 10px; }
        .activity-legend { margin-top: 16px; padding-top: 14px; font-size: 10px; letter-spacing: 0.8px; gap: 10px; }
        .legend-items .sq { width: 9px; height: 9px; }
    }

    /* Телефоны (до 640px) */
    @media (max-width: 640px) {
        .show-user-page { padding: 24px 16px 40px; }
        .show-toast-wrap { max-width: 100%; padding: 0 12px; top: 16px; }
        .show-toast { padding: 12px 15px; gap: 12px; border-radius: 12px; }
        .show-toast-icon { width: 30px; height: 30px; border-radius: 8px; }
        .show-toast-icon svg { width: 16px; height: 16px; }
        .show-toast-text { font-size: 12px; }
        .show-topbar { margin-bottom: 16px; padding: 14px; gap: 11px; border-radius: 11px; }
        .show-topbar-left { gap: 10px; }
        .show-topbar-icon { width: 40px; height: 40px; border-radius: 10px; }
        .show-topbar-icon svg { width: 20px; height: 20px; }
        .show-topbar-title { font-size: 16px; }
        .show-topbar-subtitle { font-size: 10px; gap: 5px; }
        .meta-pill { padding: 2px 6px !important; font-size: 9px !important; }
        .btn-edit, .btn-back { padding: 9px 13px; font-size: 10px; border-radius: 8px; letter-spacing: 0.7px; }
        .profile-grid { gap: 13px; margin-bottom: 16px; }
        .profile-left { padding: 22px 16px; }
        .profile-left-bg { height: 85px; }
        .avatar-sq { width: 85px; height: 85px; border-radius: 15px; font-size: 36px; margin-bottom: 13px; }
        .profile-name { font-size: 17px; margin-bottom: 4px; }
        .profile-email { font-size: 11px; margin-bottom: 13px; }
        .role-badge { padding: 8px 15px; font-size: 9px; border-radius: 9px; letter-spacing: 0.9px; }
        .profile-right-header { padding: 12px 16px; }
        .profile-right-header h3 { font-size: 10px; letter-spacing: 1.2px; }
        .profile-right-body { padding: 20px 16px; }
        .info-grid { gap: 14px; }
        .info-item { padding: 10px 12px; border-radius: 9px; }
        .info-label { font-size: 9px; letter-spacing: 0.7px; margin-bottom: 6px; gap: 5px; }
        .info-label i { font-size: 10px; }
        .info-value { font-size: 12px; }
        .status-section { margin-top: 18px; padding-top: 14px; }
        .status-indicator { padding: 10px 12px; border-radius: 9px; gap: 9px; }
        .status-dot-wrap { width: 11px; height: 11px; }
        .status-text { font-size: 10px; letter-spacing: 0.7px; }
        .activity-card { padding: 20px 16px; border-radius: 11px; }
        .activity-header { margin-bottom: 14px; gap: 9px; }
        .activity-title-wrap { gap: 10px; }
        .activity-icon { width: 34px; height: 34px; border-radius: 9px; }
        .activity-icon svg { width: 16px; height: 16px; }
        .activity-title { font-size: 12px; }
        .activity-title .count-accent { font-size: 16px; }
        .activity-subtitle { font-size: 10px; }
        .gh-grid { grid-template-columns: 32px 1fr; gap: 4px 7px; }
        .gh-months { grid-template-columns: repeat({{ $weeksCount }}, 9px); font-size: 8px; height: 15px; }
        .gh-days { grid-template-rows: repeat(7, 9px); font-size: 7px; }
        .gh-day-label { height: 9px; font-size: 7px; }
        .gh-squares { grid-template-rows: repeat(7, 9px); grid-auto-columns: 9px; }
        .sq { width: 9px; height: 9px; }
        .activity-legend { margin-top: 14px; padding-top: 12px; font-size: 9px; letter-spacing: 0.7px; gap: 9px; }
        .legend-items .sq { width: 9px; height: 9px; }
    }

    /* Маленькие телефоны (до 480px) */
    @media (max-width: 480px) {
        .show-user-page { padding: 20px 14px 36px; }
        .show-toast-wrap { padding: 0 10px; top: 14px; }
        .show-toast { padding: 11px 13px; gap: 10px; border-radius: 11px; }
        .show-toast-icon { width: 28px; height: 28px; }
        .show-toast-icon svg { width: 15px; height: 15px; }
        .show-toast-text { font-size: 11px; }
        .show-topbar { margin-bottom: 14px; padding: 13px 12px; gap: 10px; border-radius: 10px; }
        .show-topbar-left { gap: 9px; }
        .show-topbar-icon { width: 38px; height: 38px; border-radius: 10px; }
        .show-topbar-icon svg { width: 19px; height: 19px; }
        .show-topbar-title { font-size: 15px; }
        .show-topbar-subtitle { font-size: 9px; gap: 4px; }
        .meta-pill { padding: 2px 6px !important; font-size: 9px !important; }
        .btn-edit, .btn-back { padding: 8px 12px; font-size: 9px; border-radius: 8px; letter-spacing: 0.6px; }
        .btn-edit svg, .btn-back svg { width: 11px; height: 11px; }
        .profile-grid { gap: 12px; margin-bottom: 14px; }
        .profile-left { padding: 20px 14px; }
        .profile-left-bg { height: 80px; }
        .avatar-sq { width: 80px; height: 80px; border-radius: 14px; font-size: 34px; margin-bottom: 12px; border-width: 2px; }
        .profile-name { font-size: 16px; margin-bottom: 3px; }
        .profile-email { font-size: 10px; margin-bottom: 12px; }
        .role-badge { padding: 7px 14px; font-size: 9px; border-radius: 8px; letter-spacing: 0.8px; }
        .profile-right-header { padding: 11px 14px; }
        .profile-right-header h3 { font-size: 9px; letter-spacing: 1.1px; }
        .profile-right-body { padding: 18px 14px; }
        .info-grid { gap: 12px; }
        .info-item { padding: 10px 11px; border-radius: 8px; }
        .info-label { font-size: 8px; letter-spacing: 0.6px; margin-bottom: 5px; }
        .info-value { font-size: 12px; }
        .status-section { margin-top: 16px; padding-top: 12px; }
        .status-indicator { padding: 10px 11px; border-radius: 8px; }
        .status-text { font-size: 10px; }
        .activity-card { padding: 18px 14px; border-radius: 10px; }
        .activity-header { margin-bottom: 12px; }
        .activity-title-wrap { gap: 9px; }
        .activity-icon { width: 32px; height: 32px; border-radius: 9px; }
        .activity-icon svg { width: 15px; height: 15px; }
        .activity-title { font-size: 12px; }
        .activity-title .count-accent { font-size: 15px; }
        .activity-subtitle { font-size: 10px; }
        .gh-grid { grid-template-columns: 30px 1fr; gap: 4px 6px; }
        .gh-months { grid-template-columns: repeat({{ $weeksCount }}, 8px); font-size: 8px; height: 14px; }
        .gh-days { grid-template-rows: repeat(7, 8px); font-size: 7px; }
        .gh-day-label { height: 8px; font-size: 7px; }
        .gh-squares { grid-template-rows: repeat(7, 8px); grid-auto-columns: 8px; }
        .sq { width: 8px; height: 8px; border-radius: 2px; }
        .activity-legend { margin-top: 12px; padding-top: 10px; font-size: 9px; gap: 8px; }
        .legend-items .sq { width: 8px; height: 8px; }
    }

    /* Очень маленькие телефоны (до 380px) */
    @media (max-width: 380px) {
        .show-user-page { padding: 18px 12px 32px; }
        .show-toast-wrap { padding: 0 8px; top: 12px; }
        .show-toast { padding: 10px 12px; gap: 9px; border-radius: 10px; }
        .show-toast-icon { width: 26px; height: 26px; }
        .show-toast-icon svg { width: 14px; height: 14px; }
        .show-toast-text { font-size: 11px; }
        .show-topbar { margin-bottom: 12px; padding: 12px 10px; gap: 9px; border-radius: 9px; }
        .show-topbar-left { gap: 8px; }
        .show-topbar-icon { width: 36px; height: 36px; border-radius: 9px; }
        .show-topbar-icon svg { width: 18px; height: 18px; }
        .show-topbar-title { font-size: 14px; }
        .show-topbar-subtitle { font-size: 9px; }
        .meta-pill { padding: 2px 5px !important; font-size: 8px !important; }
        .btn-edit, .btn-back { padding: 8px 11px; font-size: 9px; border-radius: 7px; letter-spacing: 0.5px; }
        .btn-edit svg, .btn-back svg { width: 11px; height: 11px; }
        .profile-grid { gap: 11px; margin-bottom: 12px; }
        .profile-left { padding: 18px 12px; }
        .profile-left-bg { height: 75px; }
        .avatar-sq { width: 75px; height: 75px; border-radius: 13px; font-size: 32px; margin-bottom: 11px; }
        .profile-name { font-size: 15px; margin-bottom: 3px; }
        .profile-email { font-size: 10px; margin-bottom: 11px; }
        .role-badge { padding: 7px 13px; font-size: 8px; border-radius: 7px; letter-spacing: 0.7px; }
        .profile-right-header { padding: 10px 12px; }
        .profile-right-header h3 { font-size: 9px; letter-spacing: 1px; }
        .profile-right-body { padding: 16px 12px; }
        .info-grid { gap: 10px; }
        .info-item { padding: 9px 10px; border-radius: 7px; }
        .info-label { font-size: 8px; letter-spacing: 0.5px; margin-bottom: 5px; }
        .info-value { font-size: 11px; }
        .status-section { margin-top: 14px; padding-top: 10px; }
        .status-indicator { padding: 9px 10px; border-radius: 7px; }
        .status-text { font-size: 9px; }
        .activity-card { padding: 16px 12px; border-radius: 9px; }
        .activity-header { margin-bottom: 10px; }
        .activity-title-wrap { gap: 8px; }
        .activity-icon { width: 30px; height: 30px; border-radius: 8px; }
        .activity-icon svg { width: 14px; height: 14px; }
        .activity-title { font-size: 11px; }
        .activity-title .count-accent { font-size: 14px; }
        .activity-subtitle { font-size: 9px; }
        .gh-grid { grid-template-columns: 28px 1fr; gap: 3px 5px; }
        .gh-months { grid-template-columns: repeat({{ $weeksCount }}, 7px); font-size: 7px; height: 13px; }
        .gh-days { grid-template-rows: repeat(7, 7px); font-size: 6px; }
        .gh-day-label { height: 7px; font-size: 6px; }
        .gh-squares { grid-template-rows: repeat(7, 7px); grid-auto-columns: 7px; }
        .sq { width: 7px; height: 7px; border-radius: 1px; }
        .activity-legend { margin-top: 10px; padding-top: 9px; font-size: 8px; gap: 7px; }
        .legend-items .sq { width: 7px; height: 7px; }
    }
</style>

<div class="show-user-page">

    {{-- Фоновые blob-ы --}}
    <div class="show-blob show-blob-1"></div>
    <div class="show-blob show-blob-2"></div>
    <div class="show-blob show-blob-3"></div>

    <div class="show-wrap">

        {{-- TOP BAR --}}
        <div class="show-topbar">
            <div class="show-topbar-left">
                <div class="show-topbar-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div style="min-width: 0; flex: 1;">
                    <div class="show-topbar-title">{{ $user->name }}</div>
                    <div class="show-topbar-subtitle">
                        <span class="meta-pill">#{{ $user->id }}</span>
                        <span class="meta-pill">
                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 11px; height: 11px;"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            L{{ $user->level }}
                        </span>
                        <span class="meta-pill">{{ $user->role }}</span>
                    </div>
                </div>
            </div>

            <div class="show-topbar-actions">
                @if(Auth::id() === $user->id || (Auth::user()->role == 'admin' && $user->role !== 'admin'))
                <a href="{{ route('users.edit', $user->id) }}" class="btn-edit">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span data-i18n="editBtn">Редактировать</span>
                </a>
                @endif
                <a href="{{ route('users.index') }}" class="btn-back">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span data-i18n="backBtn">Назад</span>
                </a>
            </div>
        </div>

        {{-- PROFILE GRID --}}
        <div class="profile-grid">

            {{-- LEFT: Profile Card --}}
            <div class="profile-card profile-left">
                <div class="profile-left-bg"></div>

                <div class="avatar-sq">
                    @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}">
                    @else
                    {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                    @endif
                </div>

                <h2 class="profile-name">{{ $user->name }}</h2>
                <div class="profile-email">{{ $user->email }}</div>

                <div class="role-badge">
                    {{ $user->role }}
                </div>
            </div>

            {{-- RIGHT: Details Card --}}
            <div class="profile-card profile-right">
                <div class="profile-right-header">
                    <h3 data-i18n="detailsTitle">Детальные данные</h3>
                </div>
                <div class="profile-right-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="bi bi-person-fill"></i>
                                <span data-i18n="labelName">ФИО</span>
                            </label>
                            <p class="info-value">{{ $user->name }}</p>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="bi bi-envelope-fill"></i>
                                <span data-i18n="labelEmail">Email</span>
                            </label>
                            <p class="info-value">{{ $user->email }}</p>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="bi bi-telephone-fill"></i>
                                <span data-i18n="labelPhone">Телефон</span>
                            </label>
                            <p class="info-value">{{ $user->phone ?? '—' }}</p>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="bi bi-building"></i>
                                <span data-i18n="labelCompany">Компания</span>
                            </label>
                            <p class="info-value">{{ $user->company ?? '—' }}</p>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="bi bi-calendar-event"></i>
                                <span data-i18n="labelReg">Регистрация</span>
                            </label>
                            <p class="info-value">{{ $user->created_at->format('d.m.Y — H:i') }}</p>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="bi bi-layers-fill"></i>
                                <span data-i18n="labelLevel">Уровень</span>
                            </label>
                            <p class="info-value">{{ $user->level }}</p>
                        </div>
                    </div>

                    <div class="status-section">
                        <label class="info-label" style="margin-bottom: 10px;">
                            <i class="bi bi-shield-fill-check"></i>
                            <span data-i18n="labelStatus">Статус</span>
                        </label>
                        <div class="status-indicator">
                            <div class="status-dot-wrap">
                                <span class="status-dot-pulse"></span>
                                <span class="status-dot"></span>
                            </div>
                            <span class="status-text" data-i18n="statusActive">Активный доступ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTIVITY GRID --}}
        @php
        use Carbon\Carbon;
        $year = $year ?? now()->year;
        $firstDayOfYear = Carbon::create($year, 1, 1);
        $startDate = $firstDayOfYear->copy()->startOfWeek(Carbon::MONDAY);
        $lastDayOfYear = Carbon::create($year, 12, 31);
        $endDate = $lastDayOfYear->copy()->endOfWeek(Carbon::SUNDAY);
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $weeksCount = ceil($totalDays / 7);
        @endphp

        <div class="activity-card">
            <div class="activity-header">
                <div class="activity-title-wrap">
                    <div class="activity-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="activity-title">
                            <span class="count-accent">{{ isset($activityData) ? array_sum($activityData) : 0 }}</span>
                            <span data-i18n="activitySummary">вкладов в</span> {{ $year }}
                        </div>
                        <div class="activity-subtitle" data-i18n="activitySubtitle">История активности за год</div>
                    </div>
                </div>
            </div>

            <div class="gh-wrapper">
                <div class="gh-grid">
                    <div class="gh-months">
                        @php $lastMonth = -1; @endphp
                        @for($w = 0; $w < $weeksCount; $w++)
                        @php
                        $dateInWeek = $startDate->copy()->addWeeks($w);
                        $month = $dateInWeek->month;
                        @endphp
                        <div style="grid-column: {{ $w + 1 }}; position: relative;">
                            @if($month != $lastMonth && $dateInWeek->year == $year)
                            <span class="month-label" data-month="{{ $month }}" style="position: absolute; left: 0; bottom: 0; white-space: nowrap; color: var(--muted); font-weight: 700; text-transform: uppercase; font-size: 9px; letter-spacing: 0.5px;">
                                {{ $dateInWeek->translatedFormat('M') }}
                            </span>
                            @php $lastMonth = $month; @endphp
                            @endif
                        </div>
                        @endfor
                    </div>

                    <div class="gh-days">
                        <div class="gh-day-label" data-i18n="dayMon">Пн</div>
                        <div class="gh-day-label"></div>
                        <div class="gh-day-label" data-i18n="dayWed">Ср</div>
                        <div class="gh-day-label"></div>
                        <div class="gh-day-label" data-i18n="dayFri">Пт</div>
                        <div class="gh-day-label"></div>
                        <div class="gh-day-label"></div>
                    </div>

                    <div class="gh-squares">
                        @for($i = 0; $i < ($weeksCount * 7); $i++)
                        @php
                        $day = $startDate->copy()->addDays($i);
                        $isCurrentYear = $day->year == $year;
                        $key = $day->format('Y-m-d');
                        $count = $activityData[$key] ?? 0;
                        $level = $count > 10 ? 4 : ($count > 5 ? 3 : ($count > 2 ? 2 : ($count > 0 ? 1 : 0)));
                        @endphp
                        @if($isCurrentYear)
                        <div class="sq {{ $level ? 'l'.$level : '' }}"
                             data-count="{{ $count }}"
                             data-date="{{ $day->format('Y-m-d') }}"
                             data-day="{{ $day->day }}"
                             data-month="{{ $day->month }}"
                             data-year="{{ $day->year }}">
                        </div>
                        @else
                        <div class="sq" style="background: transparent; border: none; pointer-events: none;"></div>
                        @endif
                        @endfor
                    </div>
                </div>
            </div>

            <div class="activity-legend">
                <span data-i18n="activityLegend">Как мы считаем вклады</span>
                <div class="legend-items">
                    <span data-i18n="legendLess">Меньше</span>
                    <div class="sq"></div>
                    <div class="sq l1"></div>
                    <div class="sq l2"></div>
                    <div class="sq l3"></div>
                    <div class="sq l4"></div>
                    <span data-i18n="legendMore">Больше</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const translations = {
            ru: {
                backBtn: "Назад", editBtn: "Редактировать", detailsTitle: "Детальные данные",
                labelName: "ФИО", labelEmail: "Email", labelPhone: "Телефон",
                labelCompany: "Компания", labelReg: "Регистрация", labelLevel: "Уровень",
                labelStatus: "Статус", statusActive: "Активный доступ",
                roleEmp: "Сотрудник", roleDir: "Директор", roleAdm: "Администратор",
                activitySummary: "вкладов в", activitySubtitle: "История активности за год",
                activityLegend: "Как мы считаем вклады",
                legendLess: "Меньше", legendMore: "Больше",
                dayMon: "Пн", dayWed: "Ср", dayFri: "Пт",
                noContributions: "Нет вкладов",
                contributions: "вкладов",
                onText: "от",
                months: ["", "Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"]
            },
            en: {
                backBtn: "Back", editBtn: "Edit Profile", detailsTitle: "Detailed Information",
                labelName: "Full Name", labelEmail: "Email", labelPhone: "Phone",
                labelCompany: "Company", labelReg: "Registration", labelLevel: "Level",
                labelStatus: "Status", statusActive: "Active Access",
                roleEmp: "Employee", roleDir: "Director", roleAdm: "Administrator",
                activitySummary: "contributions in", activitySubtitle: "Activity history for the year",
                activityLegend: "Contribution history rules",
                legendLess: "Less", legendMore: "More",
                dayMon: "Mon", dayWed: "Wed", dayFri: "Fri",
                noContributions: "No contributions",
                contributions: "contributions",
                onText: "on",
                months: ["", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
            },
            tg: {
                backBtn: "Бозгашт", editBtn: "Таҳрир", detailsTitle: "Маълумоти муфассал",
                labelName: "Номи пурра", labelEmail: "Email", labelPhone: "Телефон",
                labelCompany: "Ширкат", labelReg: "Бақайдгирӣ", labelLevel: "Сатҳ",
                labelStatus: "Статус", statusActive: "Дастрасии фаъол",
                roleEmp: "Корманд", roleDir: "Директор", roleAdm: "Администратор",
                activitySummary: "саҳмҳо дар соли", activitySubtitle: "Таърихи фаъолият барои сол",
                activityLegend: "Чӣ тавр мо саҳмҳоро ҳисоб мекунем",
                legendLess: "Камтар", legendMore: "Бештар",
                dayMon: "Дш", dayWed: "Чш", dayFri: "Ҷм",
                noContributions: "Саҳмҳо нест",
                contributions: "саҳм",
                onText: "дар тарихи",
                months: ["", "Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"]
            }
        };

        function getCurrentLang() {
            const htmlLang = document.documentElement.lang;
            if (htmlLang && translations[htmlLang]) return htmlLang;
            const stored = localStorage.getItem('admin_lang');
            if (stored && translations[stored]) return stored;
            return 'ru';
        }

        function applyLanguage(lang) {
            const t = translations[lang] || translations['ru'];

            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (t[key]) el.textContent = t[key];
            });

            document.querySelectorAll('.month-label').forEach(el => {
                const mIndex = parseInt(el.getAttribute('data-month'), 10);
                if (t.months && t.months[mIndex]) {
                    el.textContent = t.months[mIndex];
                }
            });

            document.querySelectorAll('.sq[data-count]').forEach(el => {
                const count = parseInt(el.getAttribute('data-count'), 10);
                const day = el.getAttribute('data-day');
                const mIndex = parseInt(el.getAttribute('data-month'), 10);
                const year = el.getAttribute('data-year');
                const monthName = (t.months && t.months[mIndex]) ? t.months[mIndex] : '';
                let tooltipText = '';
                if (count === 0) {
                    tooltipText = `${t.noContributions} ${t.onText} ${day} ${monthName}, ${year}`;
                } else {
                    tooltipText = `${count} ${t.contributions} ${t.onText} ${day} ${monthName}, ${year}`;
                }
                el.setAttribute('data-tippy-content', tooltipText);
            });

            if (window.tippyInstances) {
                window.tippyInstances.forEach(i => i.destroy());
            }
            window.tippyInstances = tippy('[data-tippy-content]', {
                theme: 'dark-glow',
                animation: 'shift-toward',
                placement: 'top',
                arrow: true
            });
        }

        applyLanguage(getCurrentLang());

        const observer = new MutationObserver(() => applyLanguage(getCurrentLang()));
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });

        const blobs = document.querySelectorAll('.show-blob');
        document.addEventListener('mousemove', (e) => {
            const x = (e.clientX / window.innerWidth - 0.5) * 30;
            const y = (e.clientY / window.innerHeight - 0.5) * 30;
            blobs.forEach((blob, i) => {
                const factor = (i + 1) * 0.4;
                blob.style.transform = `translate(${x * factor}px, ${y * factor}px)`;
            });
        });

        const toastWrap = document.querySelector('.show-toast-wrap');
        if (toastWrap) {
            setTimeout(() => {
                toastWrap.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                toastWrap.style.opacity = '0';
                toastWrap.style.transform = 'translate(-50%, -20px)';
                setTimeout(() => toastWrap.remove(), 400);
            }, 5000);
        }
    });
</script>

@endsection