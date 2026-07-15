@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .mode-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(79, 140, 255, 0.1);
        border-radius: 8px;
        margin-bottom: 8px;
    }

    .mode-icon i {
        font-size: 20px;
        color: #4f8cff;
    }

    .mode-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 16px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .mode-btn:hover {
        background: rgba(79, 140, 255, 0.1);
        border-color: #4f8cff;
        transform: translateY(-2px);
    }

    .mode-btn.active {
        background: rgba(79, 140, 255, 0.15);
        border-color: #4f8cff;
        box-shadow: 0 0 20px rgba(79, 140, 255, 0.3);
    }

    /* ИЗМЕНЕНИЕ 1: Стили для заблокированной кнопки "Всей команде" */
    .mode-btn[disabled] {
        opacity: 0.4;
        cursor: not-allowed;
        pointer-events: none;
        border-color: rgba(255, 107, 107, 0.3);
    }
    .mode-btn[disabled] .mode-icon {
        background: rgba(255, 107, 107, 0.1);
        border-color: rgba(255, 107, 107, 0.3);
    }
    .mode-btn[disabled] .mode-icon i {
        color: #ff6b6b;
    }
</style>

<style>
    /* === КОМПАКТНАЯ СТРАНИЦА СОЗДАНИЯ ДОКУМЕНТА === */
    .doc-create-page {
        color: #e7ecf3;
        padding: 24px 16px;
    }

    .form-card {
        background: linear-gradient(180deg, rgba(22, 26, 38, 0.95), rgba(16, 19, 28, 0.95));
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 16px;
        padding: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    }
    .form-card::before {
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: 16px;
        padding: 1px;
        background: linear-gradient(135deg, rgba(79,140,255,0.5), transparent 40%, transparent 60%, rgba(79,140,255,0.3));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
        opacity: 0.7;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        color: #8892a6;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.25s ease;
        margin-bottom: 16px;
    }
    .back-btn:hover {
        color: #fff;
        border-color: rgba(79,140,255, 0.5);
        background: rgba(79,140,255, 0.08);
        box-shadow: 0 0 12px rgba(79,140,255, 0.2);
        transform: translateX(-2px);
    }

    .page-title {
        font-size: 18px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .page-title::before {
        content: "";
        width: 4px;
        height: 18px;
        background: linear-gradient(180deg, #4f8cff, rgba(79,140,255,0.3));
        border-radius: 2px;
        box-shadow: 0 0 8px rgba(79,140,255,0.6);
    }
    .page-subtitle {
        font-size: 11px;
        color: #8892a6;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-bottom: 20px;
    }

    .field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 12px;
    }
    .field-row.single {
        grid-template-columns: 1fr;
    }

    .field-group {
        display: flex;
        flex-direction: column;
    }
    .field-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #8892a6;
        margin-bottom: 6px;
    }
    .field-label .required {
        color: #ff6b6b;
        margin-left: 2px;
    }

    .input-field {
        width: 100%;
        height: 40px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        padding: 0 12px;
        color: #fff;
        font: 500 13px 'Inter', sans-serif;
        outline: none;
        transition: all 0.2s ease;
    }
    .input-field::placeholder {
        color: rgba(255,255,255,0.3);
    }
    .input-field:focus {
        border-color: rgba(79,140,255, 0.7);
        box-shadow: 0 0 0 2px rgba(79,140,255, 0.15), 0 0 12px rgba(79,140,255, 0.1);
        background: rgba(255,255,255,0.05);
    }
    textarea.input-field {
        min-height: 80px;
        padding: 10px 12px;
        resize: vertical;
        line-height: 1.5;
        height: auto;
    }
    select.input-field {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%238892a6' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 32px;
        cursor: pointer;
    }
    input[type="date"].input-field::-webkit-calendar-picker-indicator {
        filter: invert(0.8);
        cursor: pointer;
    }

    .receiver-section {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid rgba(255,255,255,0.06);
    }
    .section-title {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #8892a6;
        margin-bottom: 10px;
    }

    .mode-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-bottom: 12px;
    }
    .mode-btn {
        background: rgba(255,255,255,0.02);
        border: 1.5px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        padding: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        color: #fff;
        text-align: left;
        width: 100%;
    }
    .mode-btn:hover {
        border-color: rgba(79,140,255, 0.5);
        background: rgba(79,140,255, 0.05);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(79,140,255, 0.15);
    }
    .mode-btn.active {
        border-color: rgba(79,140,255, 1);
        background: rgba(79,140,255, 0.12);
        box-shadow: 0 0 16px rgba(79,140,255, 0.3), inset 0 0 8px rgba(79,140,255, 0.05);
    }
    .mode-btn .mode-icon {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        background: rgba(79,140,255, 0.15);
        border: 1px solid rgba(79,140,255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4f8cff;
        font-size: 13px;
        margin-bottom: 8px;
        transition: all 0.2s ease;
    }
    .mode-btn.active .mode-icon {
        background: rgba(79,140,255, 0.3);
        box-shadow: 0 0 10px rgba(79,140,255, 0.4);
    }
    .mode-btn .mode-title {
        font-size: 11px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 2px;
    }
    .mode-btn .mode-desc {
        font-size: 9px;
        color: #8892a6;
        line-height: 1.3;
    }
    .mode-btn .mode-check {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 1.5px solid rgba(255,255,255,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    .mode-btn.active .mode-check {
        background: #4f8cff;
        border-color: #4f8cff;
        color: #0a0d14;
        box-shadow: 0 0 8px rgba(79,140,255, 0.8);
    }
    .mode-btn.active .mode-check::after {
        content: "\F26A";
        font-family: "bootstrap-icons";
        font-size: 9px;
        font-weight: 900;
    }

    .receiver-block {
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 10px;
        padding: 14px;
        margin-top: 10px;
    }
    .receiver-block.hidden {
        display: none;
    }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(79,140,255, 0.15);
        border: 1px solid rgba(79,140,255, 0.4);
        color: #4f8cff;
        padding: 4px 10px;
        border-radius: 14px;
        font-size: 11px;
        font-weight: 600;
    }
    .chip button {
        background: none;
        border: none;
        color: inherit;
        cursor: pointer;
        opacity: 0.7;
        display: flex;
        padding: 0;
        font-size: 10px;
    }
    .chip button:hover {
        opacity: 1;
        color: #ff7a7a;
    }

    .search-dropdown {
        background: rgba(16, 19, 28, 0.98);
        border: 1px solid rgba(79,140,255,0.3);
        border-radius: 8px;
        margin-top: 6px;
        max-height: 200px;
        overflow-y: auto;
        box-shadow: 0 8px 24px rgba(0,0,0,0.6), 0 0 16px rgba(79,140,255,0.1);
        z-index: 100;
        position: absolute;
        left: 0;
        right: 0;
        width: 100%;
    }
    .search-dropdown.hidden {
        display: none !important;
    }
    .dropdown-item {
        padding: 10px 14px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        cursor: pointer;
        transition: all 0.15s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .dropdown-item:last-child {
        border-bottom: none;
    }
    .dropdown-item:hover {
        background: rgba(79,140,255, 0.15);
    }
    .dropdown-item .name {
        font-size: 12px;
        font-weight: 600;
        color: #fff;
        display: block;
        margin-bottom: 2px;
    }
    .dropdown-item .meta {
        font-size: 10px;
        color: #8892a6;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .dropdown-item .meta span {
        display: block;
    }
    .dropdown-item .meta .company {
        color: #4f8cff;
        font-weight: 500;
    }
    .dropdown-item .add-icon {
        color: #4f8cff;
        font-size: 14px;
        opacity: 0.7;
        transition: all 0.2s;
    }
    .dropdown-item:hover .add-icon {
        opacity: 1;
        transform: scale(1.2);
    }
    .dropdown-empty {
        padding: 12px 14px;
        font-size: 11px;
        color: #8892a6;
        text-align: center;
    }

    .search-wrapper {
        position: relative;
    }

    .file-upload {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 40px;
        background: rgba(255,255,255,0.03);
        border: 1px dashed rgba(255,255,255,0.15);
        border-radius: 8px;
        padding: 0 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #8892a6;
        font-size: 12px;
    }
    .file-upload:hover {
        border-color: rgba(79,140,255, 0.5);
        background: rgba(79,140,255, 0.05);
        color: #fff;
    }
    .file-upload input[type="file"] {
        display: none;
    }

    .btn-submit {
        appearance: none;
        border: 1.5px solid rgba(79,140,255, 0.6);
        background: linear-gradient(180deg, rgba(79,140,255, 0.2), rgba(79,140,255, 0.1));
        color: #fff;
        font: 700 12px 'Inter', sans-serif;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 12px 24px;
        border-radius: 10px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 0 16px rgba(79,140,255, 0.2);
        transition: all 0.2s ease;
        width: 100%;
        max-width: 280px;
        margin: 0 auto;
    }
    .btn-submit:hover {
        background: linear-gradient(180deg, rgba(79,140,255, 0.3), rgba(79,140,255, 0.15));
        border-color: rgba(79,140,255, 0.8);
        box-shadow: 0 0 24px rgba(79,140,255, 0.35);
        transform: translateY(-1px);
    }

    .error-box {
        background: rgba(255, 99, 99, 0.05);
        border: 1px solid rgba(255, 99, 99, 0.25);
        border-left: 3px solid #ff6b6b;
        border-radius: 8px;
        padding: 12px;
        color: #ff9999;
        margin-bottom: 16px;
    }
    .error-box .title {
        font-weight: 700;
        font-size: 12px;
        margin-bottom: 4px;
        color: #ff6b6b;
    }
    .error-box ul {
        font-size: 11px;
        margin: 0;
        padding-left: 16px;
    }

    /* === ИИ ГЕНЕРАТОР === */
    .ai-generator-card {
        background: linear-gradient(135deg, rgba(79,140,255,0.08), rgba(79,140,255,0.02));
        border: 1px solid rgba(79,140,255,0.3);
        border-radius: 16px;
        padding: 20px;
        position: relative;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .ai-generator-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, #4f8cff, transparent);
        animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .ai-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
    }

    .ai-icon {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #4f8cff, #6366f1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 20px;
        box-shadow: 0 4px 16px rgba(79,140,255,0.4);
    }

    .ai-title {
        font-size: 16px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 2px;
    }

    .ai-subtitle {
        font-size: 11px;
        color: #8892a6;
    }

    .ai-input-group {
        margin-bottom: 14px;
    }

    .ai-textarea {
        min-height: 90px;
        resize: vertical;
        font-size: 13px;
        line-height: 1.5;
        width: 100%;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        padding: 10px 12px;
        color: #fff;
        font: 500 13px 'Inter', sans-serif;
        outline: none;
        transition: all 0.2s ease;
    }

    .ai-textarea::placeholder {
        color: rgba(255,255,255,0.3);
    }

    .ai-textarea:focus {
        border-color: rgba(79,140,255, 0.7);
        box-shadow: 0 0 0 2px rgba(79,140,255, 0.15), 0 0 12px rgba(79,140,255, 0.1);
        background: rgba(255,255,255,0.05);
    }

    .ai-format-selector {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .format-option {
        flex: 1;
        cursor: pointer;
    }

    .format-option input[type="radio"] {
        display: none;
    }

    .format-label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        color: #8892a6;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .format-option input[type="radio"]:checked + .format-label {
        background: rgba(79,140,255,0.15);
        border-color: rgba(79,140,255,0.5);
        color: #4f8cff;
        box-shadow: 0 0 12px rgba(79,140,255,0.2);
    }

    .format-label i {
        font-size: 16px;
    }

    .ai-generate-btn {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #4f8cff, #6366f1);
        border: none;
        border-radius: 10px;
        color: #fff;
        font: 700 13px 'Inter', sans-serif;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 16px rgba(79,140,255,0.4);
        transition: all 0.3s ease;
    }

    .ai-generate-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(79,140,255,0.5);
    }

    .ai-generate-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .ai-generate-btn i {
        font-size: 16px;
    }

    .ai-status {
        margin-top: 14px;
        padding: 14px;
        background: rgba(79,140,255,0.08);
        border: 1px solid rgba(79,140,255,0.3);
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .ai-status.hidden {
        display: none;
    }

    .ai-status-icon {
        width: 32px;
        height: 32px;
        background: rgba(79,140,255,0.2);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4f8cff;
        font-size: 16px;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .ai-status-text {
        font-size: 12px;
        color: #fff;
        font-weight: 600;
    }

    .ai-questions {
        margin-top: 14px;
        padding: 14px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
    }

    .ai-questions.hidden {
        display: none;
    }

    .questions-title {
        font-size: 12px;
        font-weight: 700;
        color: #4f8cff;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .question-item {
        margin-bottom: 10px;
    }

    .question-text {
        font-size: 11px;
        color: #fff;
        margin-bottom: 6px;
        font-weight: 600;
    }

    .question-input {
        width: 100%;
        height: 36px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 6px;
        padding: 0 10px;
        color: #fff;
        font-size: 12px;
        outline: none;
    }

    .question-input:focus {
        border-color: rgba(79,140,255,0.5);
        background: rgba(255,255,255,0.05);
    }

    .ai-submit-btn {
        width: 100%;
        padding: 10px;
        background: rgba(79,140,255,0.15);
        border: 1px solid rgba(79,140,255,0.4);
        border-radius: 8px;
        color: #4f8cff;
        font: 600 12px 'Inter', sans-serif;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-top: 10px;
        transition: all 0.2s ease;
    }

    .ai-submit-btn:hover {
        background: rgba(79,140,255,0.25);
        border-color: rgba(79,140,255,0.6);
    }

    .ai-result {
        margin-top: 14px;
        padding: 14px;
        background: rgba(34,197,94,0.08);
        border: 1px solid rgba(34,197,94,0.3);
        border-radius: 10px;
    }

    .ai-result.hidden {
        display: none;
    }

    .result-header {
        font-size: 13px;
        font-weight: 700;
        color: #22c55e;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .result-actions {
        display: flex;
        gap: 10px;
    }

    .download-btn {
        flex: 1;
        padding: 10px;
        background: rgba(34,197,94,0.15);
        border: 1px solid rgba(34,197,94,0.4);
        border-radius: 8px;
        color: #22c55e;
        font: 600 12px 'Inter', sans-serif;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .download-btn:hover {
        background: rgba(34,197,94,0.25);
        border-color: rgba(34,197,94,0.6);
    }

    .ai-error {
        margin-top: 14px;
        padding: 12px;
        background: rgba(255,99,99,0.08);
        border: 1px solid rgba(255,99,99,0.3);
        border-radius: 8px;
        color: #ff9999;
        font-size: 12px;
    }

    .ai-error.hidden {
        display: none;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .doc-create-page { padding: 20px 14px; }
        .form-card { padding: 24px; border-radius: 14px; }
        .ai-generator-card { padding: 18px; border-radius: 14px; margin-bottom: 20px; }
        .mode-grid { grid-template-columns: repeat(3, 1fr); gap: 6px; }
        .mode-btn { padding: 10px; }
        .mode-btn .mode-icon { width: 26px; height: 26px; font-size: 12px; }
        .mode-btn .mode-title { font-size: 10px; }
        .mode-btn .mode-desc { font-size: 8px; }
        .ai-title { font-size: 15px; }
        .ai-icon { width: 40px; height: 40px; font-size: 18px; border-radius: 10px; }
    }

    @media (max-width: 768px) {
        .doc-create-page { padding: 18px 12px; }
        .form-card { padding: 20px; border-radius: 14px; }
        .ai-generator-card { padding: 16px; border-radius: 14px; margin-bottom: 18px; }
        .back-btn { padding: 7px 12px; font-size: 11px; margin-bottom: 14px; }
        .page-title { font-size: 16px; gap: 7px; }
        .page-title::before { width: 3px; height: 16px; }
        .page-subtitle { font-size: 10px; letter-spacing: 0.8px; margin-bottom: 16px; }
        .field-row { grid-template-columns: 1fr; gap: 10px; margin-bottom: 10px; }
        .field-label { font-size: 9px; letter-spacing: 1px; margin-bottom: 5px; }
        .input-field { height: 38px; font-size: 12px; padding: 0 11px; border-radius: 7px; }
        textarea.input-field { min-height: 70px; padding: 9px 11px; }
        select.input-field { padding-right: 30px; }
        .receiver-section { margin-top: 14px; padding-top: 14px; }
        .section-title { font-size: 10px; letter-spacing: 1px; margin-bottom: 8px; }
        .mode-grid { grid-template-columns: 1fr 1fr; gap: 6px; }
        .mode-btn { padding: 10px; border-radius: 8px; }
        .mode-btn .mode-icon { width: 26px; height: 26px; font-size: 12px; margin-bottom: 6px; }
        .mode-btn .mode-title { font-size: 10px; }
        .mode-btn .mode-desc { font-size: 8px; }
        .mode-btn .mode-check { width: 14px; height: 14px; top: 6px; right: 6px; }
        .receiver-block { padding: 12px; border-radius: 8px; margin-top: 8px; }
        .chip { font-size: 10px; padding: 3px 8px; border-radius: 12px; }
        .file-upload { height: 38px; font-size: 11px; padding: 0 12px; border-radius: 7px; }
        .btn-submit { padding: 11px 20px; font-size: 11px; border-radius: 8px; max-width: 260px; }
        .error-box { padding: 10px; border-radius: 7px; margin-bottom: 14px; }
        .error-box .title { font-size: 11px; }
        .error-box ul { font-size: 10px; }
        .ai-header { gap: 12px; margin-bottom: 14px; }
        .ai-icon { width: 38px; height: 38px; font-size: 17px; border-radius: 10px; }
        .ai-title { font-size: 14px; }
        .ai-subtitle { font-size: 10px; }
        .ai-textarea { min-height: 80px; font-size: 12px; padding: 9px 11px; border-radius: 7px; }
        .ai-format-selector { gap: 8px; margin-top: 8px; }
        .format-label { padding: 9px; font-size: 11px; border-radius: 7px; gap: 6px; }
        .format-label i { font-size: 14px; }
        .ai-generate-btn { padding: 11px; font-size: 12px; border-radius: 8px; }
        .ai-generate-btn i { font-size: 14px; }
        .ai-status { padding: 12px; border-radius: 8px; gap: 10px; margin-top: 12px; }
        .ai-status-icon { width: 28px; height: 28px; font-size: 14px; border-radius: 7px; }
        .ai-status-text { font-size: 11px; }
        .ai-questions { padding: 12px; border-radius: 8px; margin-top: 12px; }
        .questions-title { font-size: 11px; margin-bottom: 8px; }
        .question-text { font-size: 10px; }
        .question-input { height: 34px; font-size: 11px; border-radius: 5px; }
        .ai-submit-btn { padding: 9px; font-size: 11px; border-radius: 7px; margin-top: 8px; }
        .ai-result { padding: 12px; border-radius: 8px; margin-top: 12px; }
        .result-header { font-size: 12px; margin-bottom: 8px; }
        .download-btn { padding: 9px; font-size: 11px; border-radius: 7px; }
        .ai-error { padding: 10px; font-size: 11px; border-radius: 7px; margin-top: 12px; }
        .search-dropdown { max-height: 180px; border-radius: 7px; }
        .dropdown-item { padding: 9px 12px; }
        .dropdown-item .name { font-size: 11px; }
        .dropdown-item .meta { font-size: 9px; }
        .dropdown-item .add-icon { font-size: 12px; }
        .dropdown-empty { padding: 10px 12px; font-size: 10px; }
    }

    @media (max-width: 576px) {
        .doc-create-page { padding: 16px 10px; }
        .form-card { padding: 18px; border-radius: 12px; }
        .ai-generator-card { padding: 14px; border-radius: 12px; margin-bottom: 16px; }
        .back-btn { padding: 6px 11px; font-size: 10px; margin-bottom: 12px; border-radius: 7px; }
        .page-title { font-size: 15px; gap: 6px; }
        .page-title::before { width: 3px; height: 15px; }
        .page-subtitle { font-size: 9px; letter-spacing: 0.7px; margin-bottom: 14px; }
        .field-row { gap: 8px; margin-bottom: 8px; }
        .field-label { font-size: 9px; letter-spacing: 0.9px; margin-bottom: 4px; }
        .input-field { height: 36px; font-size: 12px; padding: 0 10px; border-radius: 6px; }
        textarea.input-field { min-height: 65px; padding: 8px 10px; }
        .receiver-section { margin-top: 12px; padding-top: 12px; }
        .section-title { font-size: 9px; letter-spacing: 0.9px; margin-bottom: 7px; }
        .mode-grid { grid-template-columns: 1fr; gap: 6px; }
        .mode-btn { padding: 10px 12px; border-radius: 8px; flex-direction: row; align-items: center; gap: 10px; }
        .mode-btn .mode-icon { width: 28px; height: 28px; font-size: 13px; margin-bottom: 0; flex-shrink: 0; }
        .mode-btn .mode-title { font-size: 11px; margin-bottom: 1px; }
        .mode-btn .mode-desc { font-size: 9px; }
        .mode-btn .mode-check { width: 15px; height: 15px; top: 50%; right: 10px; transform: translateY(-50%); }
        .receiver-block { padding: 11px; border-radius: 7px; margin-top: 7px; }
        .chip { font-size: 10px; padding: 3px 7px; border-radius: 11px; gap: 5px; }
        .file-upload { height: 36px; font-size: 11px; padding: 0 11px; border-radius: 6px; }
        .btn-submit { padding: 10px 18px; font-size: 10px; border-radius: 7px; max-width: 240px; letter-spacing: 0.8px; }
        .error-box { padding: 9px; border-radius: 6px; margin-bottom: 12px; }
        .error-box .title { font-size: 10px; }
        .error-box ul { font-size: 9px; }
        .ai-header { gap: 10px; margin-bottom: 12px; }
        .ai-icon { width: 36px; height: 36px; font-size: 16px; border-radius: 9px; }
        .ai-title { font-size: 13px; }
        .ai-subtitle { font-size: 9px; }
        .ai-textarea { min-height: 75px; font-size: 11px; padding: 8px 10px; border-radius: 6px; }
        .ai-format-selector { gap: 6px; margin-top: 7px; }
        .format-label { padding: 8px; font-size: 10px; border-radius: 6px; gap: 5px; }
        .format-label i { font-size: 13px; }
        .ai-generate-btn { padding: 10px; font-size: 11px; border-radius: 7px; }
        .ai-generate-btn i { font-size: 13px; }
        .ai-status { padding: 10px; border-radius: 7px; gap: 9px; margin-top: 10px; }
        .ai-status-icon { width: 26px; height: 26px; font-size: 13px; border-radius: 6px; }
        .ai-status-text { font-size: 10px; }
        .ai-questions { padding: 10px; border-radius: 7px; margin-top: 10px; }
        .questions-title { font-size: 10px; margin-bottom: 7px; }
        .question-item { margin-bottom: 8px; }
        .question-text { font-size: 10px; margin-bottom: 5px; }
        .question-input { height: 32px; font-size: 10px; border-radius: 5px; padding: 0 9px; }
        .ai-submit-btn { padding: 8px; font-size: 10px; border-radius: 6px; margin-top: 7px; }
        .ai-result { padding: 10px; border-radius: 7px; margin-top: 10px; }
        .result-header { font-size: 11px; margin-bottom: 7px; }
        .download-btn { padding: 8px; font-size: 10px; border-radius: 6px; }
        .ai-error { padding: 9px; font-size: 10px; border-radius: 6px; margin-top: 10px; }
        .search-dropdown { max-height: 160px; border-radius: 6px; }
        .dropdown-item { padding: 8px 10px; }
        .dropdown-item .name { font-size: 10px; }
        .dropdown-item .meta { font-size: 9px; }
        .dropdown-item .add-icon { font-size: 11px; }
        .dropdown-empty { padding: 9px 10px; font-size: 9px; }
    }

    @media (max-width: 480px) {
        .doc-create-page { padding: 14px 8px; }
        .form-card { padding: 16px; border-radius: 10px; }
        .ai-generator-card { padding: 12px; border-radius: 10px; margin-bottom: 14px; }
        .back-btn { padding: 5px 10px; font-size: 10px; margin-bottom: 10px; border-radius: 6px; gap: 6px; }
        .page-title { font-size: 14px; gap: 5px; }
        .page-title::before { width: 3px; height: 14px; }
        .page-subtitle { font-size: 9px; letter-spacing: 0.6px; margin-bottom: 12px; }
        .field-row { gap: 7px; margin-bottom: 7px; }
        .field-label { font-size: 8px; letter-spacing: 0.8px; margin-bottom: 4px; }
        .input-field { height: 34px; font-size: 11px; padding: 0 9px; border-radius: 6px; }
        textarea.input-field { min-height: 60px; padding: 7px 9px; font-size: 11px; }
        .receiver-section { margin-top: 10px; padding-top: 10px; }
        .section-title { font-size: 9px; letter-spacing: 0.8px; margin-bottom: 6px; }
        .mode-grid { gap: 5px; margin-bottom: 10px; }
        .mode-btn { padding: 9px 11px; border-radius: 7px; gap: 9px; }
        .mode-btn .mode-icon { width: 26px; height: 26px; font-size: 12px; border-radius: 6px; }
        .mode-btn .mode-title { font-size: 10px; }
        .mode-btn .mode-desc { font-size: 8px; }
        .mode-btn .mode-check { width: 14px; height: 14px; right: 9px; }
        .receiver-block { padding: 10px; border-radius: 6px; margin-top: 6px; }
        .chip { font-size: 9px; padding: 2px 6px; border-radius: 10px; gap: 4px; }
        .chip button { font-size: 9px; }
        .file-upload { height: 34px; font-size: 10px; padding: 0 10px; border-radius: 6px; }
        .btn-submit { padding: 9px 16px; font-size: 10px; border-radius: 6px; max-width: 220px; letter-spacing: 0.7px; gap: 6px; }
        .btn-submit i { font-size: 12px; }
        .error-box { padding: 8px; border-radius: 6px; margin-bottom: 10px; border-left-width: 2px; }
        .error-box .title { font-size: 10px; margin-bottom: 3px; }
        .error-box ul { font-size: 9px; padding-left: 14px; }
        .ai-header { gap: 9px; margin-bottom: 10px; }
        .ai-icon { width: 32px; height: 32px; font-size: 15px; border-radius: 8px; }
        .ai-title { font-size: 12px; }
        .ai-subtitle { font-size: 9px; }
        .ai-textarea { min-height: 70px; font-size: 11px; padding: 7px 9px; border-radius: 6px; }
        .ai-format-selector { gap: 5px; margin-top: 6px; }
        .format-label { padding: 7px; font-size: 10px; border-radius: 6px; gap: 4px; }
        .format-label i { font-size: 12px; }
        .ai-generate-btn { padding: 9px; font-size: 10px; border-radius: 6px; gap: 6px; }
        .ai-generate-btn i { font-size: 12px; }
        .ai-status { padding: 9px; border-radius: 6px; gap: 8px; margin-top: 9px; }
        .ai-status-icon { width: 24px; height: 24px; font-size: 12px; border-radius: 5px; }
        .ai-status-text { font-size: 10px; }
        .ai-questions { padding: 9px; border-radius: 6px; margin-top: 9px; }
        .questions-title { font-size: 10px; margin-bottom: 6px; gap: 5px; }
        .question-item { margin-bottom: 7px; }
        .question-text { font-size: 9px; margin-bottom: 4px; }
        .question-input { height: 30px; font-size: 10px; border-radius: 5px; padding: 0 8px; }
        .ai-submit-btn { padding: 7px; font-size: 10px; border-radius: 5px; margin-top: 6px; gap: 5px; }
        .ai-result { padding: 9px; border-radius: 6px; margin-top: 9px; }
        .result-header { font-size: 10px; margin-bottom: 6px; gap: 6px; }
        .result-actions { gap: 8px; }
        .download-btn { padding: 7px; font-size: 10px; border-radius: 5px; gap: 5px; }
        .ai-error { padding: 8px; font-size: 9px; border-radius: 5px; margin-top: 9px; }
        .search-dropdown { max-height: 150px; border-radius: 5px; margin-top: 5px; }
        .dropdown-item { padding: 7px 9px; }
        .dropdown-item .name { font-size: 10px; margin-bottom: 1px; }
        .dropdown-item .meta { font-size: 8px; }
        .dropdown-item .add-icon { font-size: 10px; }
        .dropdown-empty { padding: 8px 9px; font-size: 9px; }
    }

    @media (max-width: 380px) {
        .doc-create-page { padding: 12px 6px; }
        .form-card { padding: 14px; border-radius: 9px; }
        .ai-generator-card { padding: 10px; border-radius: 9px; margin-bottom: 12px; }
        .back-btn { padding: 4px 9px; font-size: 9px; margin-bottom: 9px; border-radius: 5px; gap: 5px; }
        .page-title { font-size: 13px; gap: 4px; }
        .page-title::before { width: 2px; height: 13px; }
        .page-subtitle { font-size: 8px; letter-spacing: 0.5px; margin-bottom: 10px; }
        .field-row { gap: 6px; margin-bottom: 6px; }
        .field-label { font-size: 8px; letter-spacing: 0.7px; margin-bottom: 3px; }
        .input-field { height: 32px; font-size: 10px; padding: 0 8px; border-radius: 5px; }
        textarea.input-field { min-height: 55px; padding: 6px 8px; font-size: 10px; }
        .receiver-section { margin-top: 9px; padding-top: 9px; }
        .section-title { font-size: 8px; letter-spacing: 0.7px; margin-bottom: 5px; }
        .mode-grid { gap: 4px; margin-bottom: 8px; }
        .mode-btn { padding: 8px 10px; border-radius: 6px; gap: 8px; }
        .mode-btn .mode-icon { width: 24px; height: 24px; font-size: 11px; border-radius: 5px; }
        .mode-btn .mode-title { font-size: 9px; }
        .mode-btn .mode-desc { font-size: 7px; }
        .mode-btn .mode-check { width: 13px; height: 13px; right: 8px; }
        .receiver-block { padding: 9px; border-radius: 5px; margin-top: 5px; }
        .chip { font-size: 8px; padding: 2px 5px; border-radius: 9px; gap: 3px; }
        .file-upload { height: 32px; font-size: 9px; padding: 0 9px; border-radius: 5px; }
        .btn-submit { padding: 8px 14px; font-size: 9px; border-radius: 5px; max-width: 200px; letter-spacing: 0.6px; gap: 5px; }
        .error-box { padding: 7px; border-radius: 5px; margin-bottom: 9px; }
        .error-box .title { font-size: 9px; }
        .error-box ul { font-size: 8px; }
        .ai-header { gap: 8px; margin-bottom: 9px; }
        .ai-icon { width: 30px; height: 30px; font-size: 14px; border-radius: 7px; }
        .ai-title { font-size: 11px; }
        .ai-subtitle { font-size: 8px; }
        .ai-textarea { min-height: 65px; font-size: 10px; padding: 6px 8px; border-radius: 5px; }
        .ai-format-selector { gap: 4px; margin-top: 5px; }
        .format-label { padding: 6px; font-size: 9px; border-radius: 5px; gap: 3px; }
        .format-label i { font-size: 11px; }
        .ai-generate-btn { padding: 8px; font-size: 9px; border-radius: 5px; gap: 5px; }
        .ai-generate-btn i { font-size: 11px; }
        .ai-status { padding: 8px; border-radius: 5px; gap: 7px; margin-top: 8px; }
        .ai-status-icon { width: 22px; height: 22px; font-size: 11px; border-radius: 5px; }
        .ai-status-text { font-size: 9px; }
        .ai-questions { padding: 8px; border-radius: 5px; margin-top: 8px; }
        .questions-title { font-size: 9px; margin-bottom: 5px; }
        .question-item { margin-bottom: 6px; }
        .question-text { font-size: 9px; margin-bottom: 3px; }
        .question-input { height: 28px; font-size: 9px; border-radius: 4px; padding: 0 7px; }
        .ai-submit-btn { padding: 6px; font-size: 9px; border-radius: 4px; margin-top: 5px; }
        .ai-result { padding: 8px; border-radius: 5px; margin-top: 8px; }
        .result-header { font-size: 9px; margin-bottom: 5px; }
        .download-btn { padding: 6px; font-size: 9px; border-radius: 4px; }
        .ai-error { padding: 7px; font-size: 8px; border-radius: 4px; margin-top: 8px; }
        .search-dropdown { max-height: 140px; border-radius: 4px; }
        .dropdown-item { padding: 6px 8px; }
        .dropdown-item .name { font-size: 9px; }
        .dropdown-item .meta { font-size: 8px; }
        .dropdown-item .add-icon { font-size: 9px; }
        .dropdown-empty { padding: 7px 8px; font-size: 8px; }
    }
</style>

<div class="doc-create-page">
    <div class="max-w-3xl mx-auto">

        <a href="{{ route('documents.index') }}" class="back-btn">
            <i class="bi bi-arrow-left"></i>
            <span data-i18n="back">Назад</span>
        </a>

        @if($errors->any())
        <div class="error-box">
            <div class="title" data-i18n="errorTitle">Ошибка при создании документа</div>
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- === ИИ ГЕНЕРАТОР ДОКУМЕНТОВ === --}}
        <div class="ai-generator-card">
            <div class="ai-header">
                <div class="ai-icon">
                    <i class="bi bi-stars"></i>
                </div>
                <div>
                    <div class="ai-title">ИИ Генератор Документов</div>
                    <div class="ai-subtitle">Опиши документ — ИИ заполнит все поля автоматически</div>
                </div>
            </div>

            <div class="ai-input-group">
                <textarea
                        id="aiPrompt"
                        class="ai-textarea"
                        placeholder="Например: Создай договор аренды квартиры на 11 месяцев между Иванов Иван Иванович и Петров Петр Петрович, сумма 50000 руб/мес..."
                        rows="3"
                ></textarea>

                <div class="ai-format-selector">
                    <label class="format-option">
                        <input type="radio" name="ai_format" value="pdf" checked>
                        <span class="format-label">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </span>
                    </label>
                    <label class="format-option">
                        <input type="radio" name="ai_format" value="word">
                        <span class="format-label">
                            <i class="bi bi-file-earmark-word"></i> Word
                        </span>
                    </label>
                </div>
            </div>

            <button type="button" id="generateBtn" class="ai-generate-btn">
                <i class="bi bi-magic"></i>
                <span>Сгенерировать с ИИ</span>
            </button>

            <div id="aiStatus" class="ai-status hidden">
                <div class="ai-status-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="ai-status-text">ИИ генерирует документ...</div>
            </div>

            <div id="aiQuestions" class="ai-questions hidden">
                <div class="questions-title">
                    <i class="bi bi-question-circle"></i>
                    ИИ задаёт уточняющие вопросы:
                </div>
                <div id="questionsList"></div>
                <button type="button" id="submitAnswers" class="ai-submit-btn">
                    <i class="bi bi-check-circle"></i>
                    Отправить ответы
                </button>
            </div>

            <div id="aiResult" class="ai-result hidden">
                <div class="result-header">
                    <i class="bi bi-check-circle-fill"></i>
                    Документ успешно сгенерирован!
                </div>
                <div class="result-actions">
                    <a id="downloadLink" href="#" class="download-btn" download>
                        <i class="bi bi-download"></i>
                        Скачать документ
                    </a>
                </div>
            </div>

            <div id="aiError" class="ai-error hidden"></div>
        </div>

        <div class="form-card">
            <h1 class="page-title" data-i18n="pageTitle">Новый документ</h1>
            <p class="page-subtitle" data-i18n="pageSubtitle">Заполните информацию о документе</p>

            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="documentForm">
                @csrf

                {{-- Номер, Тип и Статус --}}
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">
                            <span data-i18n="labelNumber">Номер документа</span>
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="number" id="field-number" class="input-field"
                               value="{{ old('number', '№ ') }}"
                               data-i18n-placeholder="numberPlaceholder"
                               placeholder="№ 001" required>
                    </div>
                    <div class="field-group">
                        <label class="field-label">
                            <span data-i18n="labelType">Тип документа</span>
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="type" id="field-type" class="input-field"
                               data-i18n-placeholder="typePlaceholder"
                               placeholder="Договор, Акт..." value="{{ old('type') }}" required>
                    </div>
                </div>

                {{-- Статус и Дедлайн --}}
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">
                            <span data-i18n="labelStatus">Статус документа</span>
                            <span class="required">*</span>
                        </label>
                        <select name="status" id="field-status" class="input-field" required>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }} data-i18n="statusSend">Отправить на подпись</option>
                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }} data-i18n="statusDraft">Сохранить как черновик</option>
                        </select>
                    </div>

                    <style>
                        #field-status {
                            width: 100%;
                            background: rgba(255, 255, 255, 0.03);
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
                            -webkit-appearance: none;
                            -moz-appearance: none;
                            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%238892a6' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
                            background-repeat: no-repeat;
                            background-position: right 12px center;
                            padding-right: 32px;
                        }

                        #field-status:focus {
                            border-color: rgba(var(--glow), 0.6);
                            box-shadow: 0 0 0 3px rgba(var(--glow), 0.15), 0 0 12px rgba(var(--glow), 0.1);
                            background-color: rgba(255, 255, 255, 0.05);
                        }

                        #field-status option {
                            background: #161a26;
                            color: var(--text);
                            padding: 10px 14px;
                            font-size: 13px;
                            font-weight: 600;
                        }

                        #field-status option:hover,
                        #field-status option:checked {
                            background: rgba(var(--glow), 0.2) !important;
                            color: #ffffff !important;
                            font-weight: 700;
                        }
                    </style>
                    <div class="field-group">
                        <label class="field-label" data-i18n="labelDeadline">Дедлайн</label>
                        <input type="date" name="deadline" id="field-deadline" class="input-field" value="{{ old('deadline') }}">
                    </div>
                </div>

                {{-- Заголовок --}}
                <div class="field-row single">
                    <div class="field-group">
                        <label class="field-label">
                            <span data-i18n="labelTitle">Заголовок</span>
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="title" id="field-title" class="input-field"
                               data-i18n-placeholder="titlePlaceholder"
                               placeholder="Название документа" value="{{ old('title') }}" required>
                    </div>
                </div>

                {{-- Описание --}}
                <div class="field-row single">
                    <div class="field-group">
                        <label class="field-label" data-i18n="labelDescription">Описание</label>
                        <textarea name="content" id="field-content" rows="3" class="input-field"
                                  data-i18n-placeholder="descriptionPlaceholder"
                                  placeholder="Краткое описание документа...">{{ old('content') }}</textarea>
                    </div>
                </div>

                {{-- Файл --}}
                <div class="field-row single">
                    <div class="field-group">
                        <label class="field-label">
                            <span data-i18n="labelFile">Прикрепить файл</span>
                            <span class="required">*</span>
                        </label>
                        <label class="file-upload">
                            <span id="file-name" data-i18n="filePlaceholder">Выберите файл...</span>
                            <i class="bi bi-paperclip"></i>
                            <input type="file" name="file_path" id="file-input" required>
                        </label>
                    </div>
                </div>

                {{-- Секция получателей --}}
                <div class="receiver-section">
                    <div class="section-title">
                        <span data-i18n="labelReceiverMode">Способ отправки</span>
                        <span class="required" style="color:#ff6b6b">*</span>
                    </div>

                    <div class="mode-grid">
                        <!-- ИЗМЕНЕНИЕ 1: Блокировка кнопки "Всей команде" если нет компании -->
                        <button type="button" data-mode="all_team" class="mode-btn {{ !(auth()->user()->company_id ?? false) ? 'disabled-mode' : '' }}"
                                @if(!(auth()->user()->company_id ?? false)) disabled title="Доступно только для пользователей с компанией" @endif>
                            <div class="mode-icon"><i class="bi bi-people-fill"></i></div>
                            <div class="mode-title" data-i18n="modeAllTeam">Всей команде</div>
                            <div class="mode-desc">
                                @if(auth()->user()->company_id ?? false)
                                <span data-i18n="modeAllTeamDesc">Всем участникам</span>
                                @else
                                <span style="color:#ff6b6b" data-i18n="modeAllTeamDescNoCompany">Требуется компания</span>
                                @endif
                            </div>
                            <div class="mode-check"></div>
                        </button>

                        <button type="button" data-mode="select_team" class="mode-btn">
                            <div class="mode-icon"><i class="bi bi-person-check-fill"></i></div>
                            <div class="mode-title" data-i18n="modeSelectTeam">Выбрать</div>
                            <div class="mode-desc" data-i18n="modeSelectTeamDesc">До 5 человек</div>
                            <div class="mode-check"></div>
                        </button>

                        <button type="button" data-mode="other_company" class="mode-btn">
                            <div class="mode-icon"><i class="bi bi-building"></i></div>
                            <div class="mode-title" data-i18n="modeOtherCompany">Другая команда</div>
                            <div class="mode-desc" data-i18n="modeOtherCompanyDesc">Внешний получатель</div>
                            <div class="mode-check"></div>
                        </button>
                    </div>

                    <input type="hidden" name="receiver_mode" id="receiver_mode" value="">

                    {{-- Блок 1: Всей команде --}}
                    <div id="mode-all_team" class="receiver-block hidden">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <i class="bi bi-info-circle-fill" style="color:#4f8cff;font-size:14px;"></i>
                            <div>
                                <p style="font-size:11px;font-weight:600;color:#fff;" data-i18n="allTeamInfo">Отправка всем участникам</p>
                                <p style="font-size:10px;color:#8892a6;margin-top:2px;">
                                    <span data-i18n="receiversCount">Получателей:</span>
                                    <strong style="color:#4f8cff;">{{ $teamUsers->count() }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Блок 2: Выбор из команды --}}
                    <div id="mode-select_team" class="receiver-block hidden">
                        <label class="field-label" data-i18n="selectReceiversLabel">Выберите получателей (до 5)</label>

                        <div class="search-wrapper">
                            <!-- ИЗМЕНЕНИЕ 2: Обновлен placeholder, чтобы указать на возможность поиска по телефону -->
                            <input type="text" id="team-search" class="input-field"
                                   data-i18n-placeholder="searchPlaceholder"
                                   placeholder="Введите имя, email или телефон..." autocomplete="off">
                            <div id="team-list" class="search-dropdown hidden"></div>
                        </div>

                        <div id="team-selected" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;min-height:28px;">
                            <span style="font-size:10px;color:#8892a6;" id="team-placeholder" data-i18n="selectedPlaceholder">Выбранные пользователи появятся здесь...</span>
                        </div>

                        <input type="hidden" name="team_receivers" id="team_receivers" value="">
                        <p id="team-error" style="font-size:10px;color:#ff6b6b;margin-top:6px;font-weight:600;display:none;">
                            ⚠ <span data-i18n="selectError">Выберите хотя бы одного получателя</span>
                        </p>
                    </div>

                    {{-- Блок 3: Другая команда --}}
                    <div id="mode-other_company" class="receiver-block hidden">
                        <label class="field-label" data-i18n="searchCompanyLabel">Поиск получателя из другой команды</label>

                        <div class="search-wrapper">
                            <!-- ИЗМЕНЕНИЕ 2: Обновлен placeholder -->
                            <input type="text" id="company-search" class="input-field"
                                   data-i18n-placeholder="searchPlaceholder"
                                   placeholder="Введите имя, email или телефон..." autocomplete="off">
                            <div id="company-list" class="search-dropdown hidden"></div>
                        </div>

                        <div id="company-selected" style="margin-top:10px;min-height:28px;"></div>

                        <input type="hidden" name="other_receiver_id" id="company_receiver" value="">
                    </div>
                </div>

                {{-- Кнопка отправки --}}
                <div style="margin-top:20px;text-align:center;">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-send-fill"></i>
                        <span data-i18n="submitButton">Создать документ</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ===== ПЕРЕВОДЫ =====
        const translations = {
            ru: {
                back: "Назад",
                errorTitle: "Ошибка при создании документа",
                pageTitle: "Новый документ",
                pageSubtitle: "Заполните информацию о документе",
                labelNumber: "Номер документа",
                labelType: "Тип документа",
                labelStatus: "Статус документа",
                labelDeadline: "Дедлайн",
                labelTitle: "Заголовок",
                labelDescription: "Описание",
                labelFile: "Прикрепить файл",
                labelReceiverMode: "Способ отправки",
                modeAllTeam: "Всей команде",
                modeAllTeamDesc: "Всем участникам",
                modeAllTeamDescNoCompany: "Требуется компания", // ИЗМЕНЕНИЕ 1
                modeSelectTeam: "Выбрать",
                modeSelectTeamDesc: "До 5 человек",
                modeOtherCompany: "Другая команда",
                modeOtherCompanyDesc: "Внешний получатель",
                allTeamInfo: "Отправка всем участникам",
                receiversCount: "Получателей:",
                selectReceiversLabel: "Выберите получателей (до 5)",
                searchPlaceholder: "Введите имя, email или телефон...", // ИЗМЕНЕНИЕ 2
                selectedPlaceholder: "Выбранные пользователи появятся здесь...",
                selectError: "Выберите хотя бы одного получателя",
                searchCompanyLabel: "Поиск получателя из другой команды",
                submitButton: "Создать документ",
                filePlaceholder: "Выберите файл...",
                usersNotFound: "Пользователи не найдены",
                maxReceivers: "Максимум 5 получателей",
                alertSelectMode: "Выберите способ отправки документа",
                alertSelectCompany: "Выберите получателя из другой команды",
                numberPlaceholder: "№ 001",
                typePlaceholder: "Договор, Акт...",
                titlePlaceholder: "Название документа",
                descriptionPlaceholder: "Краткое описание документа...",
                statusSend: "Отправить на подпись",
                statusDraft: "Сохранить как черновик"
            },
            tj: {
                back: "Бозгашт",
                errorTitle: "Хато ҳангоми эҷоди ҳуҷҷат",
                pageTitle: "Ҳуҷҷати нав",
                pageSubtitle: "Маълумот оид ба ҳуҷҷатро пур кунед",
                labelNumber: "Рақами ҳуҷҷат",
                labelType: "Намуди ҳуҷҷат",
                labelStatus: "Ҳолати ҳуҷҷат",
                labelDeadline: "Мӯҳлат",
                labelTitle: "Сарлавҳа",
                labelDescription: "Тавсиф",
                labelFile: "Файл замима кардан",
                labelReceiverMode: "Усули фиристодан",
                modeAllTeam: "Ба ҳамаи даста",
                modeAllTeamDesc: "Ба ҳамаи иштирокчиён",
                modeAllTeamDescNoCompany: "Ширкат лозим аст", // ИЗМЕНЕНИЕ 1
                modeSelectTeam: "Интихоб кардан",
                modeSelectTeamDesc: "То 5 нафар",
                modeOtherCompany: "Дигар даста",
                modeOtherCompanyDesc: "Гирандаи берунӣ",
                allTeamInfo: "Фиристодан ба ҳамаи иштирокчиён",
                receiversCount: "Гирандаҳо:",
                selectReceiversLabel: "Гирандаҳоро интихоб кунед (то 5)",
                searchPlaceholder: "Ном, email ё телефонро ворид кунед...", // ИЗМЕНЕНИЕ 2
                selectedPlaceholder: "Корбарони интихобшуда дар ин ҷо пайдо мешаванд...",
                selectError: "Ҳадди ақал як гирандаро интихоб кунед",
                searchCompanyLabel: "Ҷустуҷӯи гиранда аз дигар даста",
                submitButton: "Эҷоди ҳуҷҷат",
                filePlaceholder: "Файлро интихоб кунед...",
                usersNotFound: "Корбарон ёфт нашуданд",
                maxReceivers: "Ҳадди аксар 5 гиранда",
                alertSelectMode: "Усули фиристодани ҳуҷҷатро интихоб кунед",
                alertSelectCompany: "Гирандаро аз дигар даста интихоб кунед",
                numberPlaceholder: "№ 001",
                typePlaceholder: "Шартнома, Акт...",
                titlePlaceholder: "Номи ҳуҷҷат",
                descriptionPlaceholder: "Тавсифи мухтасари ҳуҷҷат...",
                statusSend: "Барои имзо фиристодан",
                statusDraft: "Ҳамчун пешнавис нигоҳ доштан"
            },
            en: {
                back: "Back",
                errorTitle: "Error creating document",
                pageTitle: "New Document",
                pageSubtitle: "Fill in the document information",
                labelNumber: "Document Number",
                labelType: "Document Type",
                labelStatus: "Document Status",
                labelDeadline: "Deadline",
                labelTitle: "Title",
                labelDescription: "Description",
                labelFile: "Attach File",
                labelReceiverMode: "Sending Method",
                modeAllTeam: "All Team",
                modeAllTeamDesc: "To all members",
                modeAllTeamDescNoCompany: "Company required", // ИЗМЕНЕНИЕ 1
                modeSelectTeam: "Select",
                modeSelectTeamDesc: "Up to 5 people",
                modeOtherCompany: "Other Team",
                modeOtherCompanyDesc: "External recipient",
                allTeamInfo: "Sending to all members",
                receiversCount: "Recipients:",
                selectReceiversLabel: "Select recipients (up to 5)",
                searchPlaceholder: "Enter name, email or phone...", // ИЗМЕНЕНИЕ 2
                selectedPlaceholder: "Selected users will appear here...",
                selectError: "Select at least one recipient",
                searchCompanyLabel: "Search recipient from another team",
                submitButton: "Create Document",
                filePlaceholder: "Choose file...",
                usersNotFound: "Users not found",
                maxReceivers: "Maximum 5 recipients",
                alertSelectMode: "Select document sending method",
                alertSelectCompany: "Select recipient from another team",
                numberPlaceholder: "No. 001",
                typePlaceholder: "Contract, Act...",
                titlePlaceholder: "Document name",
                descriptionPlaceholder: "Brief document description...",
                statusSend: "Send for signature",
                statusDraft: "Save as draft"
            }
        };

        // ===== CSRF токен =====
        function getCsrfToken() {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) return metaTag.getAttribute('content');
            const csrfInput = document.querySelector('input[name="_token"]');
            if (csrfInput) return csrfInput.value;
            const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
            return match ? decodeURIComponent(match[1]) : '';
        }

        // ===== Язык =====
        function getCurrentLang() {
            return localStorage.getItem('docsign_lang') || localStorage.getItem('app-lang') || 'ru';
        }

        function applyTranslations() {
            const lang = getCurrentLang();
            const t = translations[lang] || translations['ru'];
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (t[key] !== undefined) el.textContent = t[key];
            });
            document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
                const key = el.getAttribute('data-i18n-placeholder');
                if (t[key] !== undefined) el.setAttribute('placeholder', t[key]);
            });
            return t;
        }

        let currentT = applyTranslations();

        window.addEventListener('docsign:lang-changed', function(e) {
            if (e.detail && e.detail.lang) {
                localStorage.setItem('docsign_lang', e.detail.lang);
                localStorage.setItem('app-lang', e.detail.lang);
            }
            currentT = applyTranslations();
        });

        // === Данные пользователей ===
        const teamUsers = @json($teamUsersArray ?? []);
        const otherUsers = @json($otherUsersArray ?? []);

        // === Режимы отправки ===
        const modeBtns = document.querySelectorAll('.mode-btn');
        const modeBlocks = document.querySelectorAll('.receiver-block');
        const modeInput = document.getElementById('receiver_mode');

        modeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.disabled) return; // ИЗМЕНЕНИЕ 1: Игнорируем клики по заблокированной кнопке
                modeBtns.forEach(b => b.classList.remove('active'));
                modeBlocks.forEach(b => b.classList.add('hidden'));
                this.classList.add('active');
                const mode = this.dataset.mode;
                modeInput.value = mode;
                const block = document.getElementById('mode-' + mode);
                if (block) block.classList.remove('hidden');
            });
        });

        // === Загрузка файла ===
        const fileInput = document.getElementById('file-input');
        const fileName = document.getElementById('file-name');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const t = translations[getCurrentLang()] || translations['ru'];
                fileName.textContent = this.files.length > 0 ? this.files[0].name : t.filePlaceholder;
            });
        }

        // === Поиск пользователей команды ===
        const teamSearch = document.getElementById('team-search');
        const teamList = document.getElementById('team-list');
        const teamSelected = document.getElementById('team-selected');
        const teamReceivers = document.getElementById('team_receivers');
        const teamError = document.getElementById('team-error');
        let selectedTeam = [];

        if (teamSearch && teamList) {
            teamSearch.addEventListener('focus', function() {
                const query = this.value.trim().toLowerCase();
                if (query.length >= 2) filterTeamUsers(query);
            });

            teamSearch.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                if (query.length < 2) {
                    teamList.classList.add('hidden');
                    return;
                }
                filterTeamUsers(query);
            });

            function filterTeamUsers(query) {
                const t = translations[getCurrentLang()] || translations['ru'];
                const filtered = teamUsers.filter(user => {
                    if (selectedTeam.find(s => s.id === user.id)) return false;
                    const name = (user.name || '').toLowerCase();
                    const email = (user.email || '').toLowerCase();
                    const phone = (user.phone || '').toLowerCase(); // ИЗМЕНЕНИЕ 2: Добавлен телефон
                    const company = (user.company || user.company_name || '').toLowerCase();
                    return name.includes(query) || email.includes(query) || phone.includes(query) || company.includes(query); // ИЗМЕНЕНИЕ 2: Поиск по телефону
                });

                teamList.innerHTML = '';
                if (filtered.length === 0) {
                    teamList.innerHTML = `<div class="dropdown-empty">${t.usersNotFound}</div>`;
                } else {
                    filtered.forEach(user => {
                        const item = document.createElement('div');
                        item.className = 'dropdown-item';
                        const company = user.company || user.company_name || '';
                        // ИЗМЕНЕНИЕ 2: Отображение телефона в выпадающем списке
                        const phoneDisplay = user.phone ? `<span style="color:#8892a6; display:flex; align-items:center; gap:4px;"><i class="bi bi-telephone" style="font-size:10px"></i> ${user.phone}</span>` : '';

                        item.innerHTML = `
                            <div>
                                <span class="name">${user.name}</span>
                                <div class="meta">
                                    ${company ? `<span class="company">${company}</span>` : ''}
                                    <span>${user.email || ''}</span>
                                    ${phoneDisplay}
                                </div>
                            </div>
                            <i class="bi bi-plus-circle-fill add-icon"></i>
                        `;
                        item.addEventListener('click', function(e) {
                            e.stopPropagation();
                            const t2 = translations[getCurrentLang()] || translations['ru'];
                            if (selectedTeam.length >= 5) {
                                alert(t2.maxReceivers);
                                return;
                            }
                            selectedTeam.push(user);
                            updateTeamSelected();
                            teamSearch.value = '';
                            teamList.classList.add('hidden');
                            teamSearch.focus();
                        });
                        teamList.appendChild(item);
                    });
                }
                teamList.classList.remove('hidden');
            }

            document.addEventListener('click', function(e) {
                if (!teamSearch.contains(e.target) && !teamList.contains(e.target)) {
                    teamList.classList.add('hidden');
                }
            });

            function updateTeamSelected() {
                const t = translations[getCurrentLang()] || translations['ru'];
                teamSelected.innerHTML = '';
                if (selectedTeam.length === 0) {
                    teamSelected.innerHTML = `<span style="font-size:10px;color:#8892a6;" data-i18n="selectedPlaceholder">${t.selectedPlaceholder}</span>`;
                } else {
                    selectedTeam.forEach(user => {
                        const chip = document.createElement('span');
                        chip.className = 'chip';
                        chip.innerHTML = `${user.name} <button type="button" data-id="${user.id}">&times;</button>`;
                        chip.querySelector('button').addEventListener('click', function() {
                            selectedTeam = selectedTeam.filter(u => u.id !== user.id);
                            updateTeamSelected();
                        });
                        teamSelected.appendChild(chip);
                    });
                }
                teamReceivers.value = selectedTeam.map(u => u.id).join(',');
                teamError.style.display = 'none';
            }
        }

        // === Поиск пользователей другой команды ===
        const companySearch = document.getElementById('company-search');
        const companyList = document.getElementById('company-list');
        const companySelected = document.getElementById('company-selected');
        const companyReceiver = document.getElementById('company_receiver');
        let selectedCompany = null;

        if (companySearch && companyList) {
            companySearch.addEventListener('focus', function() {
                const query = this.value.trim().toLowerCase();
                if (query.length >= 2) filterCompanyUsers(query);
            });

            companySearch.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                if (query.length < 2) {
                    companyList.classList.add('hidden');
                    return;
                }
                filterCompanyUsers(query);
            });

            function filterCompanyUsers(query) {
                const t = translations[getCurrentLang()] || translations['ru'];
                const filtered = otherUsers.filter(user => {
                    const name = (user.name || '').toLowerCase();
                    const email = (user.email || '').toLowerCase();
                    const phone = (user.phone || '').toLowerCase(); // ИЗМЕНЕНИЕ 2: Добавлен телефон
                    const company = (user.company || user.company_name || '').toLowerCase();
                    return name.includes(query) || email.includes(query) || phone.includes(query) || company.includes(query); // ИЗМЕНЕНИЕ 2: Поиск по телефону
                });

                companyList.innerHTML = '';
                if (filtered.length === 0) {
                    companyList.innerHTML = `<div class="dropdown-empty">${t.usersNotFound}</div>`;
                } else {
                    filtered.forEach(user => {
                        const item = document.createElement('div');
                        item.className = 'dropdown-item';
                        const company = user.company || user.company_name || '';
                        // ИЗМЕНЕНИЕ 2: Отображение телефона в выпадающем списке
                        const phoneDisplay = user.phone ? `<span style="color:#8892a6; display:flex; align-items:center; gap:4px;"><i class="bi bi-telephone" style="font-size:10px"></i> ${user.phone}</span>` : '';

                        item.innerHTML = `
                            <div>
                                <span class="name">${user.name}</span>
                                <div class="meta">
                                    ${company ? `<span class="company">${company}</span>` : ''}
                                    <span>${user.email || ''}</span>
                                    ${phoneDisplay}
                                </div>
                            </div>
                            <i class="bi bi-check-circle-fill add-icon"></i>
                        `;
                        item.addEventListener('click', function(e) {
                            e.stopPropagation();
                            selectedCompany = user;
                            companyReceiver.value = user.id;
                            companySelected.innerHTML = `
                                <span class="chip">
                                    ${user.name}
                                    <button type="button" id="clear-company">&times;</button>
                                </span>
                            `;
                            const clearBtn = document.getElementById('clear-company');
                            if (clearBtn) {
                                clearBtn.addEventListener('click', function() {
                                    selectedCompany = null;
                                    companyReceiver.value = '';
                                    companySelected.innerHTML = '';
                                });
                            }
                            companySearch.value = '';
                            companyList.classList.add('hidden');
                        });
                        companyList.appendChild(item);
                    });
                }
                companyList.classList.remove('hidden');
            }

            document.addEventListener('click', function(e) {
                if (!companySearch.contains(e.target) && !companyList.contains(e.target)) {
                    companyList.classList.add('hidden');
                }
            });
        }

        // === Валидация формы ===
        const form = document.getElementById('documentForm');
        form.addEventListener('submit', function(e) {
            const t = translations[getCurrentLang()] || translations['ru'];
            const mode = modeInput.value;
            if (!mode) {
                e.preventDefault();
                alert(t.alertSelectMode);
                return;
            }
            if (mode === 'select_team' && selectedTeam.length === 0) {
                e.preventDefault();
                teamError.style.display = 'block';
                return;
            }
            if (mode === 'other_company' && !selectedCompany) {
                e.preventDefault();
                alert(t.alertSelectCompany);
                return;
            }
        });

        // === ИИ ГЕНЕРАТОР ===
        const generateBtn = document.getElementById('generateBtn');
        const aiPrompt = document.getElementById('aiPrompt');
        const aiStatus = document.getElementById('aiStatus');
        const aiQuestions = document.getElementById('aiQuestions');
        const questionsList = document.getElementById('questionsList');
        const submitAnswers = document.getElementById('submitAnswers');
        const aiResult = document.getElementById('aiResult');
        const downloadLink = document.getElementById('downloadLink');
        const aiError = document.getElementById('aiError');

        let currentSessionId = null;

        async function sendAIRequest(payload) {
            const csrfToken = getCsrfToken();
            if (!csrfToken) {
                throw new Error('CSRF токен не найден. Обновите страницу.');
            }

            console.log('📤 Отправляем запрос:', payload);

            const response = await fetch('/ai/generate-document', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('❌ Server error:', response.status, errorText);
                throw new Error(`Ошибка сервера: ${response.status}`);
            }

            const data = await response.json();
            console.log('✅ Ответ сервера:', data);
            return data;
        }

        function fillFormFields(data) {
            console.log('📝 Заполняем поля данными:', data);

            if (!data) {
                console.error('❌ Данные пустые!');
                return;
            }

            const fields = {
                'field-number': data.number,
                'field-type': data.type,
                'field-title': data.title,
                'field-content': data.content,
                'field-deadline': data.deadline,
                'field-status': data.status
            };

            Object.keys(fields).forEach(fieldId => {
                const field = document.getElementById(fieldId);
                const value = fields[fieldId];

                if (field && value) {
                    field.value = value;
                    console.log(`✓ Заполнено: ${fieldId} =`, value);

                    field.style.borderColor = 'rgba(34,197,94,0.5)';
                    field.style.boxShadow = '0 0 0 2px rgba(34,197,94,0.15)';
                    setTimeout(() => {
                        field.style.borderColor = '';
                        field.style.boxShadow = '';
                    }, 3000);
                }
            });

            console.log('✅ Все поля заполнены!');
        }

        function showQuestions(questions) {
            questionsList.innerHTML = '';
            questions.forEach((q, index) => {
                const item = document.createElement('div');
                item.className = 'question-item';
                item.innerHTML = `
                    <div class="question-text">${q}</div>
                    <input type="text" class="question-input" placeholder="Ваш ответ...">
                `;
                questionsList.appendChild(item);
            });
            aiQuestions.classList.remove('hidden');
        }

        function showError(message) {
            if (aiError) {
                aiError.textContent = message;
                aiError.classList.remove('hidden');
                setTimeout(() => {
                    aiError.classList.add('hidden');
                }, 5000);
            }
        }

        if (generateBtn) {
            generateBtn.addEventListener('click', async function() {
                const prompt = aiPrompt.value.trim();
                if (!prompt) {
                    showError('Введите описание документа');
                    return;
                }

                const format = document.querySelector('input[name="ai_format"]:checked').value;

                aiResult.classList.add('hidden');
                aiQuestions.classList.add('hidden');
                aiError.classList.add('hidden');
                aiStatus.classList.remove('hidden');
                generateBtn.disabled = true;

                try {
                    const data = await sendAIRequest({
                        prompt: prompt,
                        format: format,
                        session_id: currentSessionId
                    });

                    aiStatus.classList.add('hidden');
                    generateBtn.disabled = false;

                    if (data.status === 'success') {
                        if (data.needs_questions) {
                            currentSessionId = data.session_id;
                            showQuestions(data.questions);
                        } else {
                            console.log('🎯 Заполняем поля формы...');
                            fillFormFields(data.document_data);

                            if (data.download_url) {
                                downloadLink.href = data.download_url;
                                downloadLink.download = '';
                                console.log('📥 URL для скачивания:', data.download_url);
                            }
                            aiResult.classList.remove('hidden');
                        }
                    } else {
                        showError(data.message || 'Ошибка генерации документа');
                    }
                } catch (error) {
                    aiStatus.classList.add('hidden');
                    generateBtn.disabled = false;
                    showError('Ошибка: ' + error.message);
                    console.error('❌ AI Generation Error:', error);
                }
            });
        }

        if (submitAnswers) {
            submitAnswers.addEventListener('click', async function() {
                const answers = {};
                document.querySelectorAll('.question-input').forEach((input, index) => {
                    answers[`question_${index}`] = input.value.trim();
                });

                aiQuestions.classList.add('hidden');
                aiStatus.classList.remove('hidden');
                submitAnswers.disabled = true;

                try {
                    const data = await sendAIRequest({
                        session_id: currentSessionId,
                        answers: answers
                    });

                    aiStatus.classList.add('hidden');
                    submitAnswers.disabled = false;

                    if (data.status === 'success') {
                        fillFormFields(data.document_data);
                        if (data.download_url) {
                            downloadLink.href = data.download_url;
                            downloadLink.download = '';
                        }
                        aiResult.classList.remove('hidden');
                    } else {
                        showError(data.message || 'Ошибка генерации документа');
                    }
                } catch (error) {
                    aiStatus.classList.add('hidden');
                    submitAnswers.disabled = false;
                    showError('Ошибка: ' + error.message);
                    console.error('❌ AI Generation Error:', error);
                }
            });
        }
    });
</script>

@endsection