@extends('layouts.admin')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<script src="https://unpkg.com/jszip/dist/jszip.min.js"></script>
<script src="https://unpkg.com/docx-preview/dist/docx-preview.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<style>
    .sig-container {
        font-family: 'Inter', sans-serif !important;
        min-height: 100vh;
        padding: 32px 24px;
        color: var(--text);
    }

    .sig-layout {
        max-width: 1400px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        align-items: start;
    }

    @media (min-width: 1024px) {
        .sig-layout {
            grid-template-columns: 380px 1fr;
        }
    }

    .sig-panel {
        position: relative;
        background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 22px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .sig-panel::before {
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

    .sig-panel-title {
        font-size: 16px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: var(--text);
        margin: 0 0 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sig-panel-title::before {
        content: "";
        width: 3px;
        height: 18px;
        background: linear-gradient(180deg, rgba(var(--glow), 1), rgba(var(--glow), 0.3));
        border-radius: 2px;
        box-shadow: 0 0 8px rgba(var(--glow), 0.6);
    }

    .info-box {
        background: linear-gradient(135deg, rgba(var(--glow), 0.15), rgba(var(--glow), 0.05));
        border: 1px solid rgba(var(--glow), 0.3);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 18px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 0 20px rgba(var(--glow), 0.1), inset 0 1px 0 rgba(255,255,255,0.05);
    }

    .info-box::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(var(--glow), 0.8), transparent);
    }

    .info-box h3 {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: rgba(var(--glow), 1);
        margin: 0 0 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .info-box h3 i {
        font-size: 14px;
        filter: drop-shadow(0 0 4px rgba(var(--glow), 0.6));
    }

    .info-box p {
        font-size: 12px;
        line-height: 1.6;
        color: var(--text);
        margin: 0 0 6px;
        font-weight: 500;
    }

    .info-box p:last-child {
        margin-bottom: 0;
        font-size: 11px;
        color: var(--muted);
    }

    .info-box p strong {
        color: var(--text);
        font-weight: 700;
    }

    .sig-select-wrapper {
        margin-bottom: 18px;
    }

    .sig-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--muted);
        margin-bottom: 8px;
        display: block;
    }

    .sig-label .required {
        color: #ff6363;
        margin-left: 2px;
    }

    .sig-select {
        width: 100%;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 12px 14px;
        color: var(--text);
        font-size: 13px;
        font-weight: 600;
        font-family: 'Inter', sans-serif;
        outline: none;
        cursor: pointer;
        transition: all 0.2s ease;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%238892a6' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 32px;
    }

    .sig-select option {
        background: #161a26;
        color: var(--text);
    }

    .sig-select:focus {
        border-color: rgba(var(--glow), 0.6);
        box-shadow: 0 0 0 3px rgba(var(--glow), 0.15), 0 0 12px rgba(var(--glow), 0.1);
        background: rgba(255,255,255,0.05);
    }

    .qr-section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 14px;
        background: rgba(255,255,255,0.02);
        border: 1px solid var(--line);
        border-radius: 12px;
        margin-bottom: 18px;
    }

    .qr-info {
        flex: 1;
        min-width: 0;
    }

    .qr-title {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--muted);
        margin-bottom: 4px;
    }

    .qr-subtitle {
        font-size: 12px;
        font-weight: 700;
        color: var(--text);
    }

    .qr-preview-box {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        background: #ffffff;
        border: 2px solid rgba(var(--glow), 0.3);
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(var(--glow), 0.2), 0 0 0 1px rgba(255,255,255,0.05);
        flex-shrink: 0;
    }

    .qr-preview-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    .format-badge {
        display: none;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: #ffffff;
        font-family: 'JetBrains Mono', monospace;
        box-shadow: 0 0 16px currentColor, 0 6px 16px rgba(0,0,0,0.4);
        border: 2px solid;
        text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        transition: all 0.3s ease;
    }

    .format-badge.visible {
        display: inline-flex;
        animation: badgeAppear 0.4s ease-out;
    }

    @keyframes badgeAppear {
        from {
            opacity: 0;
            transform: scale(0.8) translateY(-5px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .format-badge i {
        font-size: 16px;
        filter: drop-shadow(0 0 4px rgba(255,255,255,0.6));
    }

    .format-badge.pdf {
        background: linear-gradient(135deg, rgba(255, 99, 99, 0.95), rgba(255, 99, 99, 0.75));
        border-color: rgba(255, 99, 99, 0.9);
        color: #ffffff;
    }

    .format-badge.docx,
    .format-badge.doc {
        background: linear-gradient(135deg, rgba(79, 140, 255, 0.95), rgba(79, 140, 255, 0.75));
        border-color: rgba(79, 140, 255, 0.9);
        color: #ffffff;
    }

    .format-badge.excel,
    .format-badge.xlsx,
    .format-badge.xls {
        background: linear-gradient(135deg, rgba(76, 217, 130, 0.95), rgba(76, 217, 130, 0.75));
        border-color: rgba(76, 217, 130, 0.9);
        color: #ffffff;
    }

    .format-badge.rtf {
        background: linear-gradient(135deg, rgba(167, 139, 250, 0.95), rgba(167, 139, 250, 0.75));
        border-color: rgba(167, 139, 250, 0.9);
        color: #ffffff;
    }

    .sig-viewer-wrapper {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .viewer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .viewer-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--muted);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .viewer-title::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(var(--glow), 1);
        box-shadow: 0 0 8px rgba(var(--glow), 0.8);
    }

    .btn-fullscreen {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--line);
        border-radius: 10px;
        color: var(--muted);
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.25s ease;
    }

    .btn-fullscreen:hover {
        color: var(--text);
        border-color: rgba(var(--glow), 0.4);
        background: rgba(var(--glow), 0.08);
        box-shadow: 0 0 12px rgba(var(--glow), 0.2);
    }

    .viewer-container {
        position: relative;
        background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
        border: 1px solid var(--line);
        border-radius: var(--radius);
        overflow: hidden;
        height: calc(100vh - 260px);
        min-height: 520px;
    }

    .viewer-container::before {
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
        z-index: 2;
    }

    .viewer-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        display: none;
        z-index: 50;
    }

    .viewer-loading .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid rgba(255,255,255,0.1);
        border-top-color: rgba(var(--glow), 1);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        box-shadow: 0 0 20px rgba(var(--glow), 0.4);
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .document-wrapper {
        position: relative;
        width: 100%;
        height: 100%;
        overflow: auto;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        background: #0a0d14;
    }

    #previewViewport {
        width: 100%;
        height: 100%;
        overflow-y: auto;
        background: #0a0d14;
        transition: opacity 0.3s ease;
    }

    #word-preview {
        width: 100%;
        min-height: 100%;
        background: #fff;
        position: relative;
    }

    .docx-wrapper {
        background: transparent !important;
        padding: 0 !important;
    }

    .docx {
        width: 100% !important;
        min-height: 100% !important;
        padding: 20px !important;
        box-shadow: none !important;
    }

    .btn-download {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 22px;
        background: linear-gradient(180deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.65));
        color: #0a0d14;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.25s ease;
        box-shadow: 0 8px 24px rgba(var(--glow), 0.35), inset 0 1px 0 rgba(255,255,255,0.3);
    }

    .btn-download:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(var(--glow), 0.5);
    }

    .submit-wrapper {
        display: flex;
        justify-content: center;
    }

    .btn-submit {
        appearance: none;
        border: 1.5px solid rgba(var(--glow), 0.6);
        background: linear-gradient(180deg, rgba(var(--glow), 0.25), rgba(var(--glow), 0.1));
        color: #fff;
        font: 700 12px 'Inter', sans-serif;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        padding: 16px 32px;
        border-radius: 12px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 0 20px rgba(var(--glow), 0.25), inset 0 1px 0 rgba(255,255,255,0.1);
        transition: all 0.25s ease;
        width: 100%;
        max-width: 360px;
        position: relative;
        overflow: hidden;
    }

    .btn-submit::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
    }

    .btn-submit:hover::before {
        transform: translateX(100%);
    }

    .btn-submit:hover {
        background: linear-gradient(180deg, rgba(var(--glow), 0.35), rgba(var(--glow), 0.15));
        border-color: rgba(var(--glow), 0.8);
        box-shadow: 0 0 28px rgba(var(--glow), 0.4), inset 0 1px 0 rgba(255,255,255,0.15);
        transform: translateY(-2px);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    .btn-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-submit i {
        font-size: 18px;
        filter: drop-shadow(0 0 6px rgba(var(--glow), 0.6));
    }

    .viewer-empty {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--muted);
        text-align: center;
        padding: 40px;
    }

    .viewer-empty i {
        font-size: 56px;
        opacity: 0.3;
        margin-bottom: 16px;
    }

    .viewer-empty p {
        font-size: 13px;
        font-weight: 600;
        margin: 0;
    }

    .excel-preview-container {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        background: #ffffff;
        overflow: hidden;
    }

    .excel-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 16px;
        background: linear-gradient(180deg, #1e2638, #161c2a);
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        flex-wrap: wrap;
    }

    .excel-file-info {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #e6ecf5;
        font-size: 12px;
        font-weight: 600;
    }

    .excel-file-info i {
        font-size: 18px;
        color: #4cd982;
        filter: drop-shadow(0 0 6px rgba(76, 217, 130, 0.5));
    }

    .excel-stats {
        display: flex;
        gap: 14px;
        font-size: 11px;
        color: var(--muted);
        font-family: 'JetBrains Mono', monospace;
    }

    .excel-stats span strong {
        color: #4cd982;
        font-weight: 700;
    }

    .excel-sheet-tabs {
        display: flex;
        gap: 4px;
        padding: 8px 12px;
        background: #141925;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        overflow-x: auto;
        scrollbar-width: thin;
    }

    .excel-sheet-tab {
        padding: 7px 16px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 8px;
        color: var(--muted);
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .excel-sheet-tab i {
        font-size: 12px;
    }

    .excel-sheet-tab:hover {
        background: rgba(76, 217, 130, 0.08);
        border-color: rgba(76, 217, 130, 0.3);
        color: var(--text);
    }

    .excel-sheet-tab.active {
        background: linear-gradient(180deg, rgba(76, 217, 130, 0.25), rgba(76, 217, 130, 0.1));
        border-color: rgba(76, 217, 130, 0.6);
        color: #4cd982;
        box-shadow: 0 0 12px rgba(76, 217, 130, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }

    .excel-table-wrapper {
        flex: 1;
        overflow: auto;
        background: #ffffff;
    }

    .excel-table {
        border-collapse: collapse;
        width: max-content;
        min-width: 100%;
        font-size: 12px;
        font-family: 'Inter', sans-serif;
        background: #ffffff;
    }

    .excel-table th {
        background: linear-gradient(180deg, #2a7a4a, #1f5f39);
        color: #ffffff;
        font-weight: 700;
        padding: 8px 14px;
        text-align: left;
        border: 1px solid rgba(0, 0, 0, 0.15);
        position: sticky;
        top: 0;
        z-index: 10;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .excel-table th.row-header {
        background: linear-gradient(180deg, #3a4a5e, #2a3544);
        text-align: center;
        min-width: 50px;
        position: sticky;
        left: 0;
        z-index: 11;
    }

    .excel-table td {
        padding: 6px 12px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        color: #1a1a1a;
        min-width: 80px;
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .excel-table td.row-header {
        background: #f0f2f5;
        color: #5a6478;
        font-weight: 700;
        text-align: center;
        min-width: 50px;
        position: sticky;
        left: 0;
        z-index: 5;
        border-right: 2px solid rgba(0, 0, 0, 0.12);
    }

    .excel-table tbody tr:nth-child(even) {
        background: rgba(76, 217, 130, 0.03);
    }

    .excel-table tbody tr:hover td:not(.row-header) {
        background: rgba(76, 217, 130, 0.08);
    }

    .excel-table td.cell-number {
        text-align: right;
        font-family: 'JetBrains Mono', monospace;
        color: #1a5c3a;
    }

    .excel-table td.cell-empty {
        color: #c0c5cf;
        font-style: italic;
    }

    .excel-empty-sheet {
        padding: 60px 20px;
        text-align: center;
        color: #8892a6;
        font-size: 13px;
        font-weight: 600;
    }

    .excel-empty-sheet i {
        font-size: 40px;
        opacity: 0.3;
        margin-bottom: 12px;
        display: block;
    }

    /* ============================================ */
    /* === ПОЛНАЯ АДАПТИВНОСТЬ === */
    /* ============================================ */

    /* Маленькие десктопы (до 1200px) */
    @media (max-width: 1200px) {
        .sig-container { padding: 28px 20px; }
        .sig-layout { gap: 18px; }
        .sig-panel { padding: 20px; }
        .sig-panel-title { font-size: 15px; margin-bottom: 16px; }
        .info-box { padding: 14px; margin-bottom: 16px; }
        .info-box h3 { font-size: 10px; }
        .info-box p { font-size: 11px; }
        .sig-select { padding: 11px 13px; font-size: 12px; }
        .qr-section { padding: 13px; gap: 12px; margin-bottom: 16px; }
        .qr-preview-box { width: 75px; height: 75px; }
        .format-badge { padding: 9px 16px; font-size: 11px; }
        .viewer-container { height: calc(100vh - 250px); min-height: 500px; }
        .btn-submit { padding: 15px 30px; font-size: 11px; }
    }

    /* Планшеты (до 992px) - grid в одну колонку */
    @media (max-width: 992px) {
        .sig-container { padding: 24px 18px; }
        .sig-layout { gap: 16px; }
        .sig-panel { padding: 18px; position: static !important; }
        .sig-panel-title { font-size: 14px; margin-bottom: 15px; gap: 9px; }
        .sig-panel-title::before { width: 3px; height: 16px; }
        .info-box { padding: 13px; margin-bottom: 15px; border-radius: 11px; }
        .info-box h3 { font-size: 10px; margin-bottom: 7px; }
        .info-box h3 i { font-size: 13px; }
        .info-box p { font-size: 11px; line-height: 1.55; }
        .sig-select-wrapper { margin-bottom: 16px; }
        .sig-label { font-size: 9px; margin-bottom: 7px; }
        .sig-select { padding: 11px 13px; font-size: 12px; border-radius: 9px; }
        .qr-section { padding: 12px; gap: 11px; margin-bottom: 15px; border-radius: 11px; }
        .qr-title { font-size: 9px; }
        .qr-subtitle { font-size: 11px; }
        .qr-preview-box { width: 70px; height: 70px; border-radius: 10px; }
        .format-badge { padding: 8px 15px; font-size: 11px; border-radius: 10px; }
        .format-badge i { font-size: 15px; }
        .viewer-container { height: calc(100vh - 240px); min-height: 460px; border-radius: 13px; }
        .viewer-title { font-size: 12px; }
        .btn-fullscreen { padding: 7px 12px; font-size: 10px; border-radius: 9px; }
        .btn-submit { padding: 14px 28px; font-size: 11px; border-radius: 11px; }
        .btn-submit i { font-size: 17px; }
        .excel-toolbar { padding: 9px 14px; }
        .excel-file-info { font-size: 11px; }
        .excel-file-info i { font-size: 17px; }
        .excel-stats { gap: 12px; font-size: 10px; }
        .excel-sheet-tab { padding: 6px 14px; font-size: 10px; }
        .excel-table th { padding: 7px 12px; font-size: 10px; }
        .excel-table td { padding: 5px 10px; font-size: 11px; }
        .viewer-empty i { font-size: 50px; }
        .viewer-empty p { font-size: 12px; }
    }

    /* Большие телефоны (до 768px) */
    @media (max-width: 768px) {
        .sig-container { padding: 20px 16px; }
        .sig-layout { gap: 15px; }
        .sig-panel { padding: 16px; border-radius: 13px; }
        .sig-panel-title { font-size: 13px; margin-bottom: 14px; gap: 8px; }
        .info-box { padding: 12px; margin-bottom: 14px; border-radius: 10px; }
        .info-box h3 { font-size: 9px; margin-bottom: 6px; gap: 5px; }
        .info-box h3 i { font-size: 12px; }
        .info-box p { font-size: 10px; line-height: 1.5; }
        .info-box p:last-child { font-size: 10px; }
        .sig-select-wrapper { margin-bottom: 15px; }
        .sig-label { font-size: 9px; margin-bottom: 6px; letter-spacing: 0.9px; }
        .sig-select { padding: 10px 12px; font-size: 11px; border-radius: 9px; }
        .qr-section { padding: 11px; gap: 10px; margin-bottom: 14px; border-radius: 10px; }
        .qr-title { font-size: 9px; margin-bottom: 3px; }
        .qr-subtitle { font-size: 10px; }
        .qr-preview-box { width: 65px; height: 65px; border-radius: 9px; }
        .format-badge { padding: 7px 13px; font-size: 10px; border-radius: 9px; gap: 6px; }
        .format-badge i { font-size: 14px; }
        .viewer-container { height: calc(100vh - 220px); min-height: 400px; border-radius: 12px; }
        .viewer-header { gap: 10px; }
        .viewer-title { font-size: 11px; gap: 7px; }
        .viewer-title::before { width: 7px; height: 7px; }
        .btn-fullscreen { padding: 7px 11px; font-size: 10px; border-radius: 8px; gap: 5px; }
        .btn-submit { padding: 13px 26px; font-size: 10px; border-radius: 10px; max-width: 100%; }
        .btn-submit i { font-size: 16px; }
        .excel-toolbar { padding: 8px 12px; gap: 10px; flex-direction: column; align-items: flex-start; }
        .excel-file-info { font-size: 11px; gap: 8px; }
        .excel-file-info i { font-size: 16px; }
        .excel-stats { gap: 10px; font-size: 10px; }
        .excel-sheet-tabs { padding: 7px 10px; gap: 3px; }
        .excel-sheet-tab { padding: 6px 12px; font-size: 10px; border-radius: 7px; gap: 5px; }
        .excel-sheet-tab i { font-size: 11px; }
        .excel-table th { padding: 6px 10px; font-size: 9px; }
        .excel-table td { padding: 5px 9px; font-size: 10px; min-width: 70px; }
        .excel-table td.row-header { min-width: 40px; }
        .excel-empty-sheet { padding: 50px 18px; font-size: 12px; }
        .excel-empty-sheet i { font-size: 36px; }
        .viewer-empty { padding: 30px; }
        .viewer-empty i { font-size: 46px; margin-bottom: 14px; }
        .viewer-empty p { font-size: 11px; }
    }

    /* Телефоны (до 640px) */
    @media (max-width: 640px) {
        .sig-container { padding: 18px 14px; }
        .sig-layout { gap: 14px; }
        .sig-panel { padding: 15px; border-radius: 12px; }
        .sig-panel-title { font-size: 12px; margin-bottom: 13px; gap: 7px; letter-spacing: 1.1px; }
        .info-box { padding: 11px; margin-bottom: 13px; border-radius: 9px; }
        .info-box h3 { font-size: 9px; margin-bottom: 6px; }
        .info-box p { font-size: 10px; }
        .sig-select-wrapper { margin-bottom: 14px; }
        .sig-label { font-size: 8px; margin-bottom: 6px; }
        .sig-select { padding: 10px 12px; font-size: 11px; border-radius: 8px; }
        .qr-section {
            padding: 10px;
            gap: 10px;
            margin-bottom: 13px;
            border-radius: 9px;
            flex-direction: column;
            align-items: stretch;
        }
        .qr-info { text-align: center; }
        .qr-preview-box {
            width: 80px;
            height: 80px;
            margin: 0 auto;
        }
        .format-badge { padding: 7px 12px; font-size: 10px; border-radius: 8px; }
        .viewer-container { height: calc(100vh - 200px); min-height: 360px; border-radius: 11px; }
        .viewer-header { gap: 8px; }
        .viewer-title { font-size: 11px; }
        .btn-fullscreen { padding: 6px 10px; font-size: 9px; border-radius: 8px; }
        .btn-submit { padding: 12px 24px; font-size: 10px; border-radius: 9px; letter-spacing: 1.3px; }
        .btn-submit i { font-size: 15px; }
        .excel-toolbar { padding: 7px 10px; }
        .excel-file-info { font-size: 10px; }
        .excel-stats { gap: 8px; font-size: 9px; }
        .excel-sheet-tabs { padding: 6px 8px; }
        .excel-sheet-tab { padding: 5px 10px; font-size: 9px; }
        .excel-table th { padding: 5px 8px; font-size: 9px; }
        .excel-table td { padding: 4px 7px; font-size: 10px; min-width: 60px; }
        .excel-empty-sheet { padding: 40px 15px; font-size: 11px; }
        .excel-empty-sheet i { font-size: 32px; }
        .viewer-empty i { font-size: 42px; }
        .viewer-empty p { font-size: 10px; }
    }

    /* Маленькие телефоны (до 480px) */
    @media (max-width: 480px) {
        .sig-container { padding: 16px 12px; }
        .sig-layout { gap: 13px; }
        .sig-panel { padding: 14px; border-radius: 11px; }
        .sig-panel-title { font-size: 11px; margin-bottom: 12px; gap: 6px; letter-spacing: 1px; }
        .sig-panel-title::before { width: 2px; height: 14px; }
        .info-box { padding: 10px; margin-bottom: 12px; border-radius: 8px; }
        .info-box h3 { font-size: 8px; margin-bottom: 5px; }
        .info-box h3 i { font-size: 11px; }
        .info-box p { font-size: 9px; line-height: 1.45; }
        .info-box p:last-child { font-size: 9px; }
        .sig-select-wrapper { margin-bottom: 13px; }
        .sig-label { font-size: 8px; margin-bottom: 5px; letter-spacing: 0.8px; }
        .sig-select { padding: 9px 11px; font-size: 10px; border-radius: 8px; }
        .qr-section { padding: 9px; gap: 8px; margin-bottom: 12px; }
        .qr-title { font-size: 8px; }
        .qr-subtitle { font-size: 10px; }
        .qr-preview-box { width: 70px; height: 70px; border-radius: 8px; }
        .format-badge { padding: 6px 11px; font-size: 9px; border-radius: 7px; gap: 5px; }
        .format-badge i { font-size: 13px; }
        .viewer-container { height: calc(100vh - 180px); min-height: 320px; border-radius: 10px; }
        .viewer-header { gap: 7px; }
        .viewer-title { font-size: 10px; gap: 6px; }
        .viewer-title::before { width: 6px; height: 6px; }
        .btn-fullscreen { padding: 6px 9px; font-size: 9px; border-radius: 7px; }
        .btn-submit { padding: 11px 22px; font-size: 9px; border-radius: 8px; letter-spacing: 1.2px; }
        .btn-submit i { font-size: 14px; }
        .excel-toolbar { padding: 6px 9px; }
        .excel-file-info { font-size: 10px; gap: 7px; }
        .excel-file-info i { font-size: 15px; }
        .excel-stats { gap: 7px; font-size: 9px; }
        .excel-sheet-tabs { padding: 5px 7px; gap: 3px; }
        .excel-sheet-tab { padding: 5px 9px; font-size: 9px; border-radius: 6px; }
        .excel-table th { padding: 5px 7px; font-size: 8px; }
        .excel-table td { padding: 4px 6px; font-size: 9px; min-width: 55px; }
        .excel-empty-sheet { padding: 35px 12px; font-size: 10px; }
        .excel-empty-sheet i { font-size: 28px; }
        .viewer-empty { padding: 25px; }
        .viewer-empty i { font-size: 38px; margin-bottom: 12px; }
        .viewer-empty p { font-size: 10px; }
    }

    /* Очень маленькие телефоны (до 380px) */
    @media (max-width: 380px) {
        .sig-container { padding: 14px 10px; }
        .sig-layout { gap: 12px; }
        .sig-panel { padding: 12px; border-radius: 10px; }
        .sig-panel-title { font-size: 10px; margin-bottom: 11px; letter-spacing: 0.9px; }
        .info-box { padding: 9px; margin-bottom: 11px; border-radius: 7px; }
        .info-box h3 { font-size: 8px; }
        .info-box p { font-size: 9px; }
        .sig-select { padding: 8px 10px; font-size: 10px; border-radius: 7px; }
        .qr-section { padding: 8px; gap: 7px; margin-bottom: 11px; }
        .qr-preview-box { width: 65px; height: 65px; }
        .format-badge { padding: 5px 10px; font-size: 9px; }
        .viewer-container { height: calc(100vh - 170px); min-height: 300px; border-radius: 9px; }
        .viewer-title { font-size: 10px; }
        .btn-fullscreen { padding: 5px 8px; font-size: 8px; }
        .btn-submit { padding: 10px 20px; font-size: 9px; border-radius: 7px; }
        .excel-toolbar { padding: 5px 8px; }
        .excel-file-info { font-size: 9px; }
        .excel-stats { font-size: 8px; }
        .excel-sheet-tab { padding: 4px 8px; font-size: 8px; }
        .excel-table th { padding: 4px 6px; font-size: 8px; }
        .excel-table td { padding: 3px 5px; font-size: 9px; }
        .viewer-empty i { font-size: 34px; }
        .viewer-empty p { font-size: 9px; }
    }
</style>

<div class="sig-container">
    <div class="sig-layout">

        <div class="sig-panel" style="position: sticky; top: 88px;">
            <h2 class="sig-panel-title" data-i18n="title">
                Подпись документа
            </h2>

            <div class="info-box">
                <h3>
                    <i class="bi bi-info-circle-fill"></i>
                    <span data-i18n="autoSignTitle">Автоматическая подпись</span>
                </h3>
                <p data-i18n="autoSignDesc">
                    QR-код с подписью будет автоматически размещён на <strong>последней странице</strong> документа в правом нижнем углу.
                </p>
                <p>
                    ✅ PDF — последняя страница<br>
                    ✅ DOCX — последняя страница<br>
                    ✅ XLSX — последний лист<br>
                    ✅ RTF — конвертируется в DOCX
                </p>
            </div>

            <form action="{{ route('signatures.store') }}" method="POST" id="signatureForm">
                @csrf

                <div class="sig-select-wrapper">
                    <label class="sig-label" data-i18n="selectDocument">
                        Выбор документа <span class="required">*</span>
                    </label>
                    <select name="document_id" id="documentSelect" class="sig-select" required>
                        <option value="" disabled {{ $documents->isEmpty() ? 'selected' : '' }} data-i18n="selectPlaceholder">
                            -- Список документов --
                        </option>
                        @foreach($documents as $index => $doc)
                        @php
                        $ext = strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION));
                        $formatType = 'pdf';
                        if(in_array($ext,['doc','docx'])){ $formatType = 'word'; }
                        elseif(in_array($ext,['xls','xlsx'])){ $formatType = 'excel'; }
                        elseif($ext === 'rtf'){ $formatType = 'rtf'; }

                        $senderName = $doc->sender->name ?? 'Система';
                        $signerName = auth()->user()->name ?? 'Пользователь';
                        $dateSent = $doc->created_at ? $doc->created_at->format('d.m.Y H:i') : now()->format('d.m.Y H:i');

                        $qrText = "DocSign | DOC: {$doc->title} | SENDER: {$senderName} | SIGNED BY: {$signerName} | DATE: {$dateSent}";
                        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qrText);
                        @endphp
                        <option value="{{ $doc->id }}"
                                {{ (request('document_id') == $doc->id) || (!request('document_id') && $index == 0 && !$documents->isEmpty()) ? 'selected' : '' }}
                        data-file="{{ asset('storage/'.$doc->file_path) }}"
                        data-type="{{ $formatType }}"
                        data-ext="{{ $ext }}"
                        data-qr="{{ $qrUrl }}"
                        data-qr-text="{{ $qrText }}"
                        data-signer="{{ $signerName }}">
                        [{{ strtoupper($ext) }}] #{{ $doc->id }} — {{ $doc->title }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="qr-section">
                    <div class="qr-info">
                        <div class="qr-title">QR CODE</div>
                        <div class="qr-subtitle" data-i18n="signatureCheck">Проверка подписи</div>
                    </div>
                    <div class="qr-preview-box">
                        <img id="qrPreview" src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=DocSign" alt="QR Preview">
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <span class="sig-label" style="margin: 0;" data-i18n="preview">Предпросмотр</span>
                    <span id="formatBadge" class="format-badge"></span>
                </div>

                <div class="submit-wrapper" style="margin-top: 20px;">
                    <button type="submit" id="submitBtn" class="btn-submit">
                        <i class="bi bi-shield-check"></i>
                        <span data-i18n="applyStamp">Подписать документ</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="sig-viewer-wrapper">
            <div class="viewer-header">
                <div class="viewer-title" data-i18n="docPreview">Предпросмотр документа</div>
                <a id="fullScreenBtn" href="#" target="_blank" class="btn-fullscreen" style="display: none;">
                    <i class="bi bi-arrows-fullscreen"></i>
                    <span data-i18n="fullscreen">На весь экран</span>
                </a>
            </div>

            <div class="viewer-container">
                <div id="viewerLoader" class="viewer-loading">
                    <div class="spinner"></div>
                </div>

                <div class="document-wrapper" id="documentWrapper">
                    <div id="previewViewport">
                        <div id="renderTarget" style="width: 100%; height: 100%; position: relative;">
                            <div class="viewer-empty">
                                <i class="bi bi-file-earmark-text"></i>
                                <p data-i18n="selectDocPreview">Выберите документ для предпросмотра</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const SIGN_TRANSLATIONS = {
            ru: {
                title: 'Подпись документа',
                autoSignTitle: 'Автоматическая подпись',
                autoSignDesc: 'QR-код с подписью будет автоматически размещён на последней странице документа.',
                selectDocument: 'Выбор документа',
                selectPlaceholder: '-- Список документов --',
                signatureCheck: 'Проверка подписи',
                preview: 'Предпросмотр',
                docPreview: 'Предпросмотр документа',
                fullscreen: 'На весь экран',
                downloadFile: 'Скачать файл',
                applyStamp: 'Подписать документ',
                applyingStamp: 'Подписание...',
                selectAlert: 'Выберите документ!',
                selectDocPreview: 'Выберите документ для предпросмотра',
                excelSheets: 'листов',
                excelRows: 'строк',
                excelCols: 'столбцов',
                excelEmpty: 'Лист пуст',
                excelLoadError: 'Ошибка предпросмотра Excel',
                rowNumber: '№'
            },
            tj: {
                title: 'Имзои ҳуҷҷат',
                autoSignTitle: 'Имзои автоматикӣ',
                autoSignDesc: 'QR-коди имзо ба таври худкор дар саҳифаи охирини ҳуҷҷат ҷойгир мешавад.',
                selectDocument: 'Интихоби ҳуҷҷат',
                selectPlaceholder: '-- Рӯйхати ҳуҷҷатҳо --',
                signatureCheck: 'Санҷиши имзо',
                preview: 'Пешнамоиш',
                docPreview: 'Пешнамоиши ҳуҷҷат',
                fullscreen: 'Тамоми экран',
                downloadFile: 'Боргирии файл',
                applyStamp: 'Имзо кардан',
                applyingStamp: 'Имзо шуда истодааст...',
                selectAlert: 'Ҳуҷҷатро интихоб кунед!',
                selectDocPreview: 'Барои пешнамоиш ҳуҷҷатро интихоб кунед',
                excelSheets: 'варақҳо',
                excelRows: 'сатрҳо',
                excelCols: 'сутунҳо',
                excelEmpty: 'Варақ холӣ аст',
                excelLoadError: 'Хатои пешнамоиши Excel',
                rowNumber: '№'
            },
            en: {
                title: 'Document Signing',
                autoSignTitle: 'Automatic Signature',
                autoSignDesc: 'QR code with signature will be automatically placed on the last page of the document.',
                selectDocument: 'Select Document',
                selectPlaceholder: '-- Document List --',
                signatureCheck: 'Signature Verification',
                preview: 'Preview',
                docPreview: 'Document Preview',
                fullscreen: 'Full Screen',
                downloadFile: 'Download File',
                applyStamp: 'Sign Document',
                applyingStamp: 'Signing...',
                selectAlert: 'Please select a document!',
                selectDocPreview: 'Select a document to preview',
                excelSheets: 'sheets',
                excelRows: 'rows',
                excelCols: 'columns',
                excelEmpty: 'Sheet is empty',
                excelLoadError: 'Excel preview error',
                rowNumber: '#'
            }
        };

        function getCurrentDict() {
            const lang = localStorage.getItem('docsign_lang') || 'ru';
            return SIGN_TRANSLATIONS[lang] || SIGN_TRANSLATIONS.ru;
        }

        function applySignTranslations(lang) {
            const dict = SIGN_TRANSLATIONS[lang] || SIGN_TRANSLATIONS.ru;

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

        const form = document.getElementById('signatureForm');
        const select = document.getElementById('documentSelect');
        const renderTarget = document.getElementById('renderTarget');
        const previewViewport = document.getElementById('previewViewport');
        const loader = document.getElementById('viewerLoader');
        const formatBadge = document.getElementById('formatBadge');
        const fullScreenBtn = document.getElementById('fullScreenBtn');
        const qrPreview = document.getElementById('qrPreview');
        const wrapper = document.getElementById('documentWrapper');
        const submitBtn = document.getElementById('submitBtn');

        function getExcelColumnLabel(colIndex) {
            let label = '';
            let n = colIndex;
            while (n >= 0) {
                label = String.fromCharCode(65 + (n % 26)) + label;
                n = Math.floor(n / 26) - 1;
            }
            return label;
        }

        function isNumericValue(value) {
            if (value === null || value === undefined || value === '') return false;
            return !isNaN(parseFloat(value)) && isFinite(value);
        }

        function renderExcelSheet(sheet, container, dict) {
            const jsonData = XLSX.utils.sheet_to_json(sheet, { header: 1, defval: '', raw: false });

            if (!jsonData || jsonData.length === 0) {
                container.innerHTML = `
                    <div class="excel-empty-sheet">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                        ${dict.excelEmpty}
                    </div>
                `;
                return { rows: 0, cols: 0 };
            }

            let maxCols = 0;
            jsonData.forEach(row => {
                if (row && row.length > maxCols) maxCols = row.length;
            });

            const table = document.createElement('table');
            table.className = 'excel-table';

            const thead = document.createElement('thead');
            const headerRow = document.createElement('tr');

            const cornerTh = document.createElement('th');
            cornerTh.className = 'row-header';
            cornerTh.textContent = '';
            headerRow.appendChild(cornerTh);

            for (let c = 0; c < maxCols; c++) {
                const th = document.createElement('th');
                th.textContent = getExcelColumnLabel(c);
                headerRow.appendChild(th);
            }
            thead.appendChild(headerRow);
            table.appendChild(thead);

            const tbody = document.createElement('tbody');
            for (let r = 0; r < jsonData.length; r++) {
                const row = jsonData[r] || [];
                const tr = document.createElement('tr');

                const rowHeaderTd = document.createElement('td');
                rowHeaderTd.className = 'row-header';
                rowHeaderTd.textContent = r + 1;
                tr.appendChild(rowHeaderTd);

                for (let c = 0; c < maxCols; c++) {
                    const td = document.createElement('td');
                    const cellValue = row[c] !== undefined ? row[c] : '';

                    if (cellValue === '' || cellValue === null) {
                        td.className = 'cell-empty';
                        td.innerHTML = '&nbsp;';
                    } else {
                        td.textContent = cellValue;
                        if (isNumericValue(cellValue)) {
                            td.classList.add('cell-number');
                        }
                    }
                    tr.appendChild(td);
                }
                tbody.appendChild(tr);
            }
            table.appendChild(tbody);

            container.innerHTML = '';
            container.appendChild(table);

            return { rows: jsonData.length, cols: maxCols };
        }

        function renderExcelFile(fileSource, dict) {
            fetch(fileSource)
                .then(res => {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.arrayBuffer();
                })
                .then(data => {
                    const workbook = XLSX.read(data, { type: 'array' });

                    const container = document.createElement('div');
                    container.className = 'excel-preview-container';

                    const toolbar = document.createElement('div');
                    toolbar.className = 'excel-toolbar';
                    toolbar.innerHTML = `
                        <div class="excel-file-info">
                            <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                            <span>Excel Document</span>
                        </div>
                        <div class="excel-stats">
                            <span><strong data-stat="sheets">${workbook.SheetNames.length}</strong> ${dict.excelSheets}</span>
                            <span><strong data-stat="rows">0</strong> ${dict.excelRows}</span>
                            <span><strong data-stat="cols">0</strong> ${dict.excelCols}</span>
                        </div>
                    `;
                    container.appendChild(toolbar);

                    if (workbook.SheetNames.length > 0) {
                        const tabsDiv = document.createElement('div');
                        tabsDiv.className = 'excel-sheet-tabs';

                        workbook.SheetNames.forEach((sheetName, index) => {
                            const tab = document.createElement('div');
                            tab.className = 'excel-sheet-tab';
                            tab.innerHTML = `<i class="bi bi-table"></i><span>${sheetName}</span>`;
                            tab.dataset.sheetIndex = index;

                            if (index === 0) tab.classList.add('active');

                            tab.addEventListener('click', () => {
                                tabsDiv.querySelectorAll('.excel-sheet-tab').forEach(t => t.classList.remove('active'));
                                tab.classList.add('active');
                                const stats = renderExcelSheet(workbook.Sheets[sheetName], tableWrapper, dict);
                                toolbar.querySelector('[data-stat="rows"]').textContent = stats.rows;
                                toolbar.querySelector('[data-stat="cols"]').textContent = stats.cols;
                            });

                            tabsDiv.appendChild(tab);
                        });
                        container.appendChild(tabsDiv);
                    }

                    const tableWrapper = document.createElement('div');
                    tableWrapper.className = 'excel-table-wrapper';
                    container.appendChild(tableWrapper);

                    renderTarget.innerHTML = '';
                    renderTarget.appendChild(container);

                    if (workbook.SheetNames.length > 0) {
                        const stats = renderExcelSheet(workbook.Sheets[workbook.SheetNames[0]], tableWrapper, dict);
                        toolbar.querySelector('[data-stat="rows"]').textContent = stats.rows;
                        toolbar.querySelector('[data-stat="cols"]').textContent = stats.cols;
                    }

                    loader.style.display = 'none';
                    previewViewport.style.opacity = '1';
                })
                .catch(error => {
                    loader.style.display = 'none';
                    previewViewport.style.opacity = '1';
                    renderTarget.innerHTML = `
                        <div style="padding: 24px; text-align: center; color: #ff6363; font-weight: 600;">
                            <i class="bi bi-exclamation-triangle-fill" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                            ${dict.excelLoadError}: ${error.message || 'Не удалось загрузить'}
                        </div>
                    `;
                });
        }

        function updateFormatBadge(ext) {
            const iconMap = {
                'pdf': 'bi-file-earmark-pdf',
                'doc': 'bi-file-earmark-word',
                'docx': 'bi-file-earmark-word',
                'xls': 'bi-file-earmark-excel',
                'xlsx': 'bi-file-earmark-excel',
                'rtf': 'bi-file-earmark-text'
            };

            const icon = iconMap[ext] || 'bi-file-earmark';
            formatBadge.innerHTML = `<i class="bi ${icon}"></i>${ext.toUpperCase()}`;
            formatBadge.className = 'format-badge ' + ext + ' visible';
        }

        function renderDocument(fileSource, type, ext) {
            loader.style.display = 'block';
            previewViewport.style.opacity = '0.3';
            renderTarget.innerHTML = '';
            wrapper.style.display = 'flex';

            updateFormatBadge(ext);

            if (ext === 'pdf') {
                fullScreenBtn.style.display = 'none';
            } else {
                fullScreenBtn.href = fileSource;
                fullScreenBtn.style.display = 'inline-flex';
            }

            if (ext === 'docx') {
                const docxSource = fetch(fileSource).then(res => res.blob());
                docxSource.then(blob => {
                    const wordDiv = document.createElement('div');
                    wordDiv.id = 'word-preview';
                    renderTarget.appendChild(wordDiv);
                    docx.renderAsync(blob, wordDiv)
                        .then(() => {
                            loader.style.display = 'none';
                            previewViewport.style.opacity = '1';
                        })
                        .catch(e => {
                            loader.style.display = 'none';
                            previewViewport.style.opacity = '1';
                            renderTarget.innerHTML = '<div style="padding: 24px; text-align: center; color: #ff6363; font-weight: 600;">Ошибка предпросмотра DOCX: ' + (e.message || 'Не удалось загрузить') + '</div>';
                        });
                }).catch((e) => {
                    loader.style.display = 'none';
                    previewViewport.style.opacity = '1';
                    renderTarget.innerHTML = '<div style="padding: 24px; text-align: center; color: #ff6363; font-weight: 600;">Ошибка получения DOCX: ' + e.message + '</div>';
                });
                return;
            }

            if (ext === 'pdf') {
                const loadingTask = pdfjsLib.getDocument(fileSource);
                loadingTask.promise.then(function (pdf) {
                    const totalPages = pdf.numPages;

                    renderTarget.innerHTML = '';
                    renderTarget.style.display = 'flex';
                    renderTarget.style.flexDirection = 'column';
                    renderTarget.style.alignItems = 'center';

                    if (totalPages === 0) {
                        loader.style.display = 'none';
                        previewViewport.style.opacity = '1';
                        renderTarget.innerHTML = '<div style="padding: 24px; text-align: center; color: #ff6363; font-weight: 600;">PDF не содержит страниц.</div>';
                        return;
                    }

                    const renderPage = (pageNum) => {
                        return pdf.getPage(pageNum).then(function (page) {
                            const scale = 1.5;
                            const viewport = page.getViewport({scale: scale});
                            const canvas = document.createElement('canvas');
                            const context = canvas.getContext('2d');
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            canvas.style.marginBottom = '10px';
                            canvas.style.border = '1px solid rgba(255,255,255,0.06)';
                            canvas.style.maxWidth = '100%';
                            canvas.style.height = 'auto';

                            return page.render({canvasContext: context, viewport: viewport}).promise.then(function () {
                                return canvas;
                            });
                        });
                    };

                    const pageRenderPromises = [];
                    for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                        pageRenderPromises.push(renderPage(pageNum));
                    }

                    Promise.allSettled(pageRenderPromises).then(results => {
                        results.forEach(result => {
                            if (result.status === 'fulfilled') {
                                renderTarget.appendChild(result.value);
                            }
                        });

                        loader.style.display = 'none';
                        previewViewport.style.opacity = '1';
                    });
                }).catch(function (error) {
                    loader.style.display = 'none';
                    previewViewport.style.opacity = '1';
                    renderTarget.innerHTML = '<div style="padding: 24px; text-align: center; color: #ff6363; font-weight: 600;">Ошибка предпросмотра PDF: ' + error.message + '</div>';
                });
                return;
            }

            if (type === 'excel') {
                const dict = getCurrentDict();
                renderExcelFile(fileSource, dict);
                return;
            }

            let iframeSrc = '';
            if (type === 'word') {
                iframeSrc = 'https://view.officeapps.live.com/op/view.aspx?src=' + encodeURIComponent(fileSource);
            } else if (type === 'rtf') {
                iframeSrc = fileSource;
            } else {
                iframeSrc = fileSource + '#toolbar=0&navpanes=0&scrollbar=0&view=FitH';
            }

            const iframe = document.createElement('iframe');
            iframe.src = iframeSrc;
            iframe.style.cssText = 'width: 100%; height: 100%; border: none; display: block;';
            iframe.frameBorder = '0';
            iframe.onload = () => {
                loader.style.display = 'none';
                previewViewport.style.opacity = '1';
            };
            iframe.onerror = (e) => {
                loader.style.display = 'none';
                previewViewport.style.opacity = '1';
                renderTarget.innerHTML = '<div style="padding: 24px; text-align: center; color: #ff6363; font-weight: 600;">Ошибка загрузки предпросмотра.</div>';
            };
            renderTarget.appendChild(iframe);
        }

        function updateSelection() {
            const dict = getCurrentDict();
            const selectedOption = select ? select.options[select.selectedIndex] : null;

            if (selectedOption && selectedOption.value) {
                const fileUrl = selectedOption.getAttribute('data-file');
                const type = selectedOption.getAttribute('data-type');
                const ext = selectedOption.getAttribute('data-ext');
                const qrUrl = selectedOption.getAttribute('data-qr');

                if (qrPreview) qrPreview.src = qrUrl;
                renderDocument(fileUrl, type, ext);
            } else {
                if (renderTarget) renderTarget.innerHTML = '<div class="viewer-empty"><i class="bi bi-file-earmark-text"></i><p>' + dict.selectDocPreview + '</p></div>';
                if (loader) loader.style.display = 'none';
                if (previewViewport) previewViewport.style.opacity = '1';
                if (formatBadge) formatBadge.classList.remove('visible');
                if (fullScreenBtn) fullScreenBtn.style.display = 'none';
                if (wrapper) wrapper.style.display = 'flex';
            }
        }

        if (select) {
            select.addEventListener('change', updateSelection);
        }

        if (select && select.value && select.value !== '') {
            updateSelection();
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                const dict = getCurrentDict();

                if (!select.value) {
                    e.preventDefault();
                    alert(dict.selectAlert);
                    return false;
                }

                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.7';
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split" style="font-size: 18px;"></i><span>' + dict.applyingStamp + '</span>';
            });
        }

        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applySignTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applySignTranslations(lang);
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applySignTranslations(e.newValue);
            }
        });
    });
</script>

@endsection