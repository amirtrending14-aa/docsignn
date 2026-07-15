@extends('layouts.admin')

@section('content')
@php
$ownerId = (int) ($document->created_by ?? 0);
$currentUserId = (int) auth()->id();
$isOwner = ($currentUserId === $ownerId);
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    .doc-edit-page {
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

    .readonly-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        background: rgba(255, 193, 7, 0.1);
        border: 1px solid rgba(255, 193, 7, 0.3);
        border-radius: 12px;
        color: #ffc107;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-left: 8px;
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
    .input-field:focus:not([readonly]) {
        border-color: rgba(79,140,255, 0.7);
        box-shadow: 0 0 0 2px rgba(79,140,255, 0.15), 0 0 12px rgba(79,140,255, 0.1);
        background: rgba(255,255,255,0.05);
    }
    .input-field[readonly] {
        background: rgba(255,255,255,0.02);
        color: #a8b2c1;
        cursor: not-allowed;
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
    select.input-field[disabled] {
        background: rgba(255,255,255,0.02);
        color: #a8b2c1;
        cursor: not-allowed;
        pointer-events: none;
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
        background: rgba(22, 26, 38, 0.98);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        margin-top: 6px;
        max-height: 180px;
        overflow-y: auto;
        box-shadow: 0 8px 24px rgba(0,0,0,0.5);
        z-index: 10;
        position: absolute;
        left: 0;
        right: 0;
        width: 100%;
    }
    .search-dropdown.hidden {
        display: none;
    }
    .dropdown-item {
        padding: 8px 12px;
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
        background: rgba(79,140,255, 0.08);
    }
    .dropdown-item .name {
        font-size: 12px;
        font-weight: 600;
        color: #fff;
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

    /* === БЛОК ТЕКУЩЕГО ФАЙЛА === */
    .current-file-card {
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.08), rgba(34, 197, 94, 0.03));
        border: 1px solid rgba(34, 197, 94, 0.25);
        border-radius: 12px;
        padding: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 12px;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }
    .current-file-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 3px;
        height: 100%;
        background: linear-gradient(180deg, #22c55e, rgba(34, 197, 94, 0.3));
    }
    .current-file-card:hover {
        border-color: rgba(34, 197, 94, 0.4);
        box-shadow: 0 0 16px rgba(34, 197, 94, 0.1);
    }
    .current-file-card.replaced {
        background: linear-gradient(135deg, rgba(255, 107, 107, 0.08), rgba(255, 107, 107, 0.03));
        border-color: rgba(255, 107, 107, 0.25);
        opacity: 0.5;
    }
    .current-file-card.replaced::before {
        background: linear-gradient(180deg, #ff6b6b, rgba(255, 107, 107, 0.3));
    }
    .current-file-info {
        display: flex;
        align-items: center;
        gap: 12px;
        overflow: hidden;
        flex: 1;
    }
    .current-file-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .current-file-icon-pdf {
        background: rgba(255, 99, 99, 0.15);
        border: 1px solid rgba(255, 99, 99, 0.3);
        color: #ff6b6b;
    }
    .current-file-icon-word {
        background: rgba(79,140,255, 0.15);
        border: 1px solid rgba(79,140,255, 0.3);
        color: #4f8cff;
    }
    .current-file-icon-excel {
        background: rgba(34, 197, 94, 0.15);
        border: 1px solid rgba(34, 197, 94, 0.3);
        color: #22c55e;
    }
    .current-file-icon-rtf {
        background: rgba(168, 85, 247, 0.15);
        border: 1px solid rgba(168, 85, 247, 0.3);
        color: #a855f7;
    }
    .current-file-name {
        font-size: 12px;
        font-weight: 700;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px;
    }
    .current-file-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 4px;
    }
    .current-file-meta span {
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        color: #8892a6;
    }
    .current-file-meta .dot {
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: #8892a6;
    }
    .current-file-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 8px;
        font-weight: 700;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        background: rgba(34, 197, 94, 0.15);
        border: 1px solid rgba(34, 197, 94, 0.3);
        color: #22c55e;
    }
    .current-file-badge.replaced {
        background: rgba(255, 107, 107, 0.15);
        border: 1px solid rgba(255, 107, 107, 0.3);
        color: #ff6b6b;
    }
    .current-file-actions {
        display: flex;
        gap: 6px;
        flex-shrink: 0;
    }
    .current-file-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid;
    }
    .current-file-btn-view {
        background: rgba(255,255,255,0.04);
        border-color: rgba(255,255,255,0.15);
        color: #8892a6;
    }
    .current-file-btn-view:hover {
        background: rgba(79,140,255, 0.1);
        border-color: rgba(79,140,255, 0.4);
        color: #4f8cff;
    }
    .current-file-btn-download {
        background: rgba(34, 197, 94, 0.12);
        border-color: rgba(34, 197, 94, 0.3);
        color: #22c55e;
    }
    .current-file-btn-download:hover {
        background: rgba(34, 197, 94, 0.2);
        border-color: rgba(34, 197, 94, 0.5);
        box-shadow: 0 0 10px rgba(34, 197, 94, 0.2);
        color: #fff;
    }

    /* === БЛОК НОВОГО ФАЙЛА === */
    .new-file-section {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px dashed rgba(255,255,255,0.08);
    }
    .new-file-label {
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #8892a6;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .new-file-label i {
        color: #4f8cff;
        font-size: 11px;
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
    .file-upload.has-file {
        border-color: rgba(79,140,255, 0.5);
        background: rgba(79,140,255, 0.05);
        color: #4f8cff;
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

    .receiver-readonly {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 8px;
    }
    .receiver-readonly .avatar {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(79,140,255, 0.15);
        border: 1px solid rgba(79,140,255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4f8cff;
        font-size: 14px;
    }
    .receiver-readonly .info .name {
        font-size: 12px;
        font-weight: 600;
        color: #fff;
    }
    .receiver-readonly .info .email {
        font-size: 10px;
        color: #8892a6;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .doc-edit-page { padding: 20px 14px; }
        .form-card { padding: 24px; border-radius: 14px; }
        .mode-grid { grid-template-columns: repeat(3, 1fr); gap: 6px; }
        .mode-btn { padding: 10px; }
        .mode-btn .mode-icon { width: 26px; height: 26px; font-size: 12px; }
        .mode-btn .mode-title { font-size: 10px; }
        .mode-btn .mode-desc { font-size: 8px; }
        .current-file-card { padding: 12px; gap: 12px; }
        .current-file-icon { width: 36px; height: 36px; font-size: 16px; }
        .current-file-name { font-size: 11px; max-width: 180px; }
        .current-file-btn { padding: 5px 9px; font-size: 8px; }
    }

    @media (max-width: 768px) {
        .doc-edit-page { padding: 18px 12px; }
        .form-card { padding: 20px; border-radius: 14px; }
        .back-btn { padding: 7px 12px; font-size: 11px; margin-bottom: 14px; }
        .page-title { font-size: 16px; gap: 7px; }
        .page-title::before { width: 3px; height: 16px; }
        .page-subtitle { font-size: 10px; letter-spacing: 0.8px; margin-bottom: 16px; }
        .readonly-badge { padding: 3px 8px; font-size: 8px; letter-spacing: 0.8px; margin-left: 6px; }
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
        .current-file-card { flex-direction: column; align-items: stretch; padding: 12px; border-radius: 10px; gap: 10px; margin-bottom: 10px; }
        .current-file-info { justify-content: flex-start; }
        .current-file-icon { width: 34px; height: 34px; font-size: 15px; border-radius: 8px; }
        .current-file-name { font-size: 11px; max-width: 100%; }
        .current-file-meta span { font-size: 8px; }
        .current-file-badge { padding: 3px 7px; font-size: 7px; border-radius: 5px; align-self: flex-start; }
        .current-file-actions { justify-content: stretch; gap: 5px; }
        .current-file-btn { flex: 1; justify-content: center; padding: 5px 8px; font-size: 8px; border-radius: 5px; }
        .new-file-section { margin-top: 10px; padding-top: 10px; }
        .new-file-label { font-size: 8px; letter-spacing: 0.9px; margin-bottom: 5px; }
        .file-upload { height: 38px; font-size: 11px; padding: 0 12px; border-radius: 7px; }
        .btn-submit { padding: 11px 20px; font-size: 11px; border-radius: 8px; max-width: 260px; }
        .receiver-readonly { padding: 9px 12px; border-radius: 7px; }
        .receiver-readonly .avatar { width: 28px; height: 28px; font-size: 12px; border-radius: 7px; }
        .receiver-readonly .info .name { font-size: 11px; }
        .receiver-readonly .info .email { font-size: 9px; }
        .search-dropdown { max-height: 160px; border-radius: 7px; }
        .dropdown-item { padding: 7px 10px; }
        .dropdown-item .name { font-size: 11px; }
        .dropdown-item .meta { font-size: 9px; }
    }

    @media (max-width: 576px) {
        .doc-edit-page { padding: 16px 10px; }
        .form-card { padding: 18px; border-radius: 12px; }
        .back-btn { padding: 6px 11px; font-size: 10px; margin-bottom: 12px; border-radius: 7px; }
        .page-title { font-size: 15px; gap: 6px; }
        .page-title::before { width: 3px; height: 15px; }
        .page-subtitle { font-size: 9px; letter-spacing: 0.7px; margin-bottom: 14px; }
        .readonly-badge { padding: 3px 7px; font-size: 8px; letter-spacing: 0.7px; margin-left: 5px; border-radius: 10px; }
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
        .current-file-card { padding: 11px; border-radius: 9px; gap: 9px; margin-bottom: 9px; }
        .current-file-icon { width: 32px; height: 32px; font-size: 14px; border-radius: 7px; }
        .current-file-name { font-size: 10px; }
        .current-file-meta span { font-size: 8px; }
        .current-file-badge { padding: 2px 6px; font-size: 7px; border-radius: 5px; }
        .current-file-actions { gap: 4px; }
        .current-file-btn { padding: 5px 7px; font-size: 8px; border-radius: 5px; gap: 3px; }
        .new-file-section { margin-top: 9px; padding-top: 9px; }
        .new-file-label { font-size: 8px; letter-spacing: 0.8px; margin-bottom: 4px; }
        .file-upload { height: 36px; font-size: 11px; padding: 0 11px; border-radius: 6px; }
        .btn-submit { padding: 10px 18px; font-size: 10px; border-radius: 7px; max-width: 240px; letter-spacing: 0.8px; }
        .receiver-readonly { padding: 8px 11px; border-radius: 6px; gap: 9px; }
        .receiver-readonly .avatar { width: 26px; height: 26px; font-size: 11px; border-radius: 6px; }
        .receiver-readonly .info .name { font-size: 10px; }
        .receiver-readonly .info .email { font-size: 9px; }
        .search-dropdown { max-height: 150px; border-radius: 6px; }
        .dropdown-item { padding: 6px 9px; }
        .dropdown-item .name { font-size: 10px; }
        .dropdown-item .meta { font-size: 9px; }
    }

    @media (max-width: 480px) {
        .doc-edit-page { padding: 14px 8px; }
        .form-card { padding: 16px; border-radius: 10px; }
        .back-btn { padding: 5px 10px; font-size: 10px; margin-bottom: 10px; border-radius: 6px; gap: 6px; }
        .page-title { font-size: 14px; gap: 5px; }
        .page-title::before { width: 3px; height: 14px; }
        .page-subtitle { font-size: 9px; letter-spacing: 0.6px; margin-bottom: 12px; }
        .readonly-badge { padding: 2px 6px; font-size: 7px; letter-spacing: 0.6px; margin-left: 4px; border-radius: 9px; }
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
        .current-file-card { padding: 10px; border-radius: 8px; gap: 8px; margin-bottom: 8px; }
        .current-file-icon { width: 30px; height: 30px; font-size: 13px; border-radius: 6px; }
        .current-file-name { font-size: 10px; }
        .current-file-meta span { font-size: 7px; }
        .current-file-badge { padding: 2px 5px; font-size: 7px; border-radius: 4px; }
        .current-file-actions { gap: 4px; }
        .current-file-btn { padding: 4px 6px; font-size: 7px; border-radius: 4px; gap: 3px; }
        .new-file-section { margin-top: 8px; padding-top: 8px; }
        .new-file-label { font-size: 8px; letter-spacing: 0.7px; margin-bottom: 4px; }
        .file-upload { height: 34px; font-size: 10px; padding: 0 10px; border-radius: 6px; }
        .btn-submit { padding: 9px 16px; font-size: 10px; border-radius: 6px; max-width: 220px; letter-spacing: 0.7px; gap: 6px; }
        .btn-submit i { font-size: 12px; }
        .receiver-readonly { padding: 7px 10px; border-radius: 5px; gap: 8px; }
        .receiver-readonly .avatar { width: 24px; height: 24px; font-size: 10px; border-radius: 5px; }
        .receiver-readonly .info .name { font-size: 10px; }
        .receiver-readonly .info .email { font-size: 8px; }
        .search-dropdown { max-height: 140px; border-radius: 5px; margin-top: 5px; }
        .dropdown-item { padding: 6px 8px; }
        .dropdown-item .name { font-size: 10px; }
        .dropdown-item .meta { font-size: 8px; }
    }

    @media (max-width: 380px) {
        .doc-edit-page { padding: 12px 6px; }
        .form-card { padding: 14px; border-radius: 9px; }
        .back-btn { padding: 4px 9px; font-size: 9px; margin-bottom: 9px; border-radius: 5px; gap: 5px; }
        .page-title { font-size: 13px; gap: 4px; }
        .page-title::before { width: 2px; height: 13px; }
        .page-subtitle { font-size: 8px; letter-spacing: 0.5px; margin-bottom: 10px; }
        .readonly-badge { padding: 2px 5px; font-size: 7px; letter-spacing: 0.5px; margin-left: 3px; border-radius: 8px; }
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
        .current-file-card { padding: 9px; border-radius: 7px; gap: 7px; margin-bottom: 7px; }
        .current-file-icon { width: 28px; height: 28px; font-size: 12px; border-radius: 5px; }
        .current-file-name { font-size: 9px; }
        .current-file-meta span { font-size: 7px; }
        .current-file-badge { padding: 2px 4px; font-size: 6px; border-radius: 4px; }
        .current-file-actions { gap: 3px; }
        .current-file-btn { padding: 4px 5px; font-size: 7px; border-radius: 4px; gap: 2px; }
        .new-file-section { margin-top: 7px; padding-top: 7px; }
        .new-file-label { font-size: 7px; letter-spacing: 0.6px; margin-bottom: 3px; }
        .file-upload { height: 32px; font-size: 9px; padding: 0 9px; border-radius: 5px; }
        .btn-submit { padding: 8px 14px; font-size: 9px; border-radius: 5px; max-width: 200px; letter-spacing: 0.6px; gap: 5px; }
        .receiver-readonly { padding: 6px 9px; border-radius: 4px; gap: 7px; }
        .receiver-readonly .avatar { width: 22px; height: 22px; font-size: 9px; border-radius: 4px; }
        .receiver-readonly .info .name { font-size: 9px; }
        .receiver-readonly .info .email { font-size: 8px; }
        .search-dropdown { max-height: 130px; border-radius: 4px; }
        .dropdown-item { padding: 5px 7px; }
        .dropdown-item .name { font-size: 9px; }
        .dropdown-item .meta { font-size: 8px; }
    }
</style>

<div class="doc-edit-page">
    <div class="max-w-3xl mx-auto">

        <a href="{{ route('documents.index') }}" class="back-btn">
            <i class="bi bi-arrow-left"></i>
            <span data-i18n="back">Назад</span>
        </a>

        <div class="form-card">
            <h1 class="page-title">
                @if($isOwner)
                <span data-i18n="editDocTitle">Редактировать документ</span>
                @else
                <span data-i18n="viewDocTitle">Просмотр документа</span>
                @endif
                @if(!$isOwner)
                <span class="readonly-badge">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span data-i18n="readOnly">Read Only</span>
                </span>
                @endif
            </h1>
            @if($isOwner)
            <p class="page-subtitle" data-i18n="editSubtitle">Внесите изменения</p>
            @else
            <p class="page-subtitle" data-i18n="viewSubtitle">Только для чтения</p>
            @endif

            <form action="{{ route('documents.update', $document->id) }}" method="POST" enctype="multipart/form-data" id="documentForm">
                @csrf
                @method('PUT')

                {{-- Номер и Тип документа --}}
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label" data-i18n="labelNumber">Номер документа</label>
                        <input type="text" name="number" class="input-field"
                               value="{{ old('number', $document->number) }}"
                               {{ !$isOwner ? 'readonly' : '' }}>
                    </div>
                    <div class="field-group">
                        <label class="field-label">
                            <span data-i18n="labelType">Тип документа</span>
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="type" class="input-field"
                               value="{{ old('type', $document->type ?? '') }}"
                               {{ !$isOwner ? 'readonly' : '' }} required>
                    </div>
                </div>

                {{-- Заголовок и Дедлайн --}}
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">
                            <span data-i18n="labelTitle">Заголовок</span>
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="title" class="input-field"
                               value="{{ old('title', $document->title) }}"
                               {{ !$isOwner ? 'readonly' : '' }} required>
                    </div>
                    <div class="field-group">
                        <label class="field-label" data-i18n="labelDeadline">Дедлайн</label>
                        <input type="date" name="deadline" class="input-field"
                               value="{{ old('deadline', $document->deadline ? \Carbon\Carbon::parse($document->deadline)->format('Y-m-d') : '') }}"
                               {{ !$isOwner ? 'readonly' : '' }}>
                    </div>
                </div>

                {{-- Описание --}}
                <div class="field-row single">
                    <div class="field-group">
                        <label class="field-label" data-i18n="labelDescription">Описание</label>
                        <textarea name="content" rows="3" class="input-field"
                                  {{ !$isOwner ? 'readonly' : '' }}>{{ old('content', $document->content) }}</textarea>
                    </div>
                </div>

                {{-- Секция получателей --}}
                @if($isOwner)
                <div class="receiver-section">
                    <div class="section-title">
                        <span data-i18n="labelReceiverMode">Способ отправки</span>
                        <span class="required">*</span>
                    </div>

                    <div class="mode-grid">
                        <button type="button" data-mode="all_team" class="mode-btn">
                            <div class="mode-icon"><i class="bi bi-people-fill"></i></div>
                            <div class="mode-title" data-i18n="modeAllTeam">Всей команде</div>
                            <div class="mode-desc" data-i18n="modeAllTeamDesc">Всем участникам</div>
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

                    <!-- ИЗМЕНЕНИЕ: Добавлен old() для сохранения режима при ошибке валидации -->
                    <input type="hidden" name="receiver_mode" id="receiver_mode" value="{{ old('receiver_mode', '') }}">

                    {{-- Блок 1: Всей команде --}}
                    <div id="mode-all_team" class="receiver-block hidden">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <i class="bi bi-info-circle-fill" style="color:#4f8cff;font-size:14px;"></i>
                            <div>
                                <p style="font-size:11px;font-weight:600;color:#fff;" data-i18n="allTeamInfo">Отправка всем участникам</p>
                                <p style="font-size:10px;color:#8892a6;margin-top:2px;" data-i18n="allTeamDesc">
                                    Документ будет отправлен всей команде
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Блок 2: Выбор из команды --}}
                    <div id="mode-select_team" class="receiver-block hidden">
                        <label class="field-label" data-i18n="selectReceiversLabel">Выберите получателей (до 5)</label>
                        <input type="text" id="team-search" class="input-field"
                               data-i18n-placeholder="teamSearchPlaceholder"
                               placeholder="Поиск по имени, email или телефону..." autocomplete="off">

                        <div id="team-selected" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;min-height:28px;">
                            <span style="font-size:10px;color:#8892a6;" id="team-placeholder" data-i18n="selectedPlaceholder">Выбранные пользователи...</span>
                        </div>

                        <div id="team-list" class="search-dropdown hidden"></div>

                        <!-- ИЗМЕНЕНИЕ: Добавлен old() для сохранения выбранных ID при ошибке -->
                        <input type="hidden" name="team_receivers" id="team_receivers" value="{{ old('team_receivers', '') }}">
                    </div>

                    {{-- Блок 3: Другая команда --}}
                    <div id="mode-other_company" class="receiver-block hidden">
                        <label class="field-label" data-i18n="searchReceiverLabel">Поиск получателя</label>
                        <input type="text" id="other-search" class="input-field"
                               data-i18n-placeholder="otherSearchPlaceholder"
                               placeholder="Название компании, email или телефон..." autocomplete="off">

                        <!-- ИЗМЕНЕНИЕ: Улучшена логика отображения для корректной работы JS -->
                        <div id="other-selected" class="hidden" style="margin-top:10px; display:none; align-items:center; justify-content:space-between; padding:10px 14px; background:rgba(79,140,255,0.08); border:1px solid rgba(79,140,255,0.3); border-radius:8px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:32px;height:32px;border-radius:8px;background:rgba(79,140,255,0.2);display:flex;align-items:center;justify-content:center;color:#4f8cff;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div>
                                    <p id="other-name" style="font-size:12px;font-weight:600;color:#fff;"></p>
                                    <p id="other-email" style="font-size:10px;color:#8892a6;"></p>
                                </div>
                            </div>
                            <button type="button" onclick="clearOtherReceiver()" style="background:none;border:none;color:#8892a6;cursor:pointer;font-size:14px;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <div id="other-list" class="search-dropdown hidden"></div>

                        <!-- ИЗМЕНЕНИЕ: Добавлен old() с фоллбеком на receiver_id документа -->
                        <input type="hidden" name="other_receiver_id" id="other_receiver_id" value="{{ old('other_receiver_id', $document->receiver_id ?? '') }}">
                    </div>
                </div>
                @else
                {{-- Получатель (readonly для не-владельца) --}}
                <div class="receiver-section">
                    <div class="section-title" data-i18n="receiverLabel">Получатель</div>
                    <div class="receiver-readonly">
                        <div class="avatar">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="info">
                            <p class="name">{{ $currentReceiver ? $currentReceiver->name : __('Не указан') }}</p>
                            <p class="email">{{ $currentReceiver ? $currentReceiver->email : '—' }}</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Статус --}}
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label" data-i18n="labelStatus">Статус</label>
                        <select name="status" id="edit-status" class="input-field" {{ !$isOwner ? 'disabled' : '' }}>
                        <option value="draft" {{ old('status', $document->status) == 'draft' ? 'selected' : '' }} data-i18n="statusDraft">Черновик</option>
                        <option value="active" {{ old('status', $document->status) == 'active' ? 'selected' : '' }} data-i18n="statusActive">Активен</option>
                        <option value="completed" {{ old('status', $document->status) == 'completed' ? 'selected' : '' }} data-i18n="statusCompleted">Завершён</option>
                        </select>
                    </div>
                    <div class="field-group">
                        {{-- Пустая ячейка для выравнивания --}}
                    </div>
                </div>

                <style>
                    #edit-status {
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
                    #edit-status:focus {
                        border-color: rgba(var(--glow), 0.6);
                        box-shadow: 0 0 0 3px rgba(var(--glow), 0.15), 0 0 12px rgba(var(--glow), 0.1);
                        background-color: rgba(255, 255, 255, 0.05);
                    }
                    #edit-status:disabled {
                        opacity: 0.5;
                        cursor: not-allowed;
                    }
                    #edit-status option {
                        background: #161a26;
                        color: var(--text);
                        padding: 10px 14px;
                        font-size: 13px;
                        font-weight: 600;
                    }
                    #edit-status option:hover,
                    #edit-status option:checked {
                        background: rgba(var(--glow), 0.2) !important;
                        color: #ffffff !important;
                        font-weight: 700;
                    }
                </style>

                {{-- === БЛОК ФАЙЛА (для владельца) === --}}
                @if($isOwner)
                <div class="receiver-section">
                    <div class="section-title">
                        <i class="bi bi-paperclip" style="color:#4f8cff;margin-right:4px;"></i>
                        <span data-i18n="fileSection">Файл документа</span>
                    </div>

                    @if($document->file_path)
                    @php
                    $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
                    if ($extension === 'docx' || $extension === 'doc') {
                    $iconClass = 'current-file-icon-word';
                    $biIcon = 'bi-file-earmark-word-fill';
                    } elseif ($extension === 'xlsx' || $extension === 'xls') {
                    $iconClass = 'current-file-icon-excel';
                    $biIcon = 'bi-file-earmark-excel-fill';
                    } elseif ($extension === 'rtf') {
                    $iconClass = 'current-file-icon-rtf';
                    $biIcon = 'bi-file-earmark-richtext-fill';
                    } else {
                    $iconClass = 'current-file-icon-pdf';
                    $biIcon = 'bi-file-earmark-pdf-fill';
                    }
                    $isPdf = $extension === 'pdf';
                    @endphp

                    {{-- Текущий файл --}}
                    <div class="current-file-card" id="current-file-card">
                        <div class="current-file-info">
                            <div class="current-file-icon {{ $iconClass }}">
                                <i class="bi {{ $biIcon }}"></i>
                            </div>
                            <div style="overflow:hidden;flex:1;">
                                <p class="current-file-name">{{ basename($document->file_path) }}</p>
                                <div class="current-file-meta">
                                    <span>{{ strtoupper($extension) }}</span>
                                    <span class="dot"></span>
                                    <span data-i18n="currentFile">Текущий файл</span>
                                </div>
                            </div>
                        </div>
                        <span class="current-file-badge" id="current-file-badge">
                            <i class="bi bi-check-circle-fill"></i>
                            <span data-i18n="activeFile">Активен</span>
                        </span>
                        <div class="current-file-actions">
                            <a href="{{ asset('storage/' . $document->file_path) }}" @if($isPdf) target="_blank" @endif
                               class="current-file-btn current-file-btn-view">
                                <i class="bi bi-eye-fill"></i>
                                <span data-i18n="viewBtn">Смотреть</span>
                            </a>
                            <a href="{{ asset('storage/' . $document->file_path) }}"
                               download="{{ $document->title }}.{{ $extension }}"
                               class="current-file-btn current-file-btn-download">
                                <i class="bi bi-download"></i>
                                <span data-i18n="downloadBtn">Скачать</span>
                            </a>
                        </div>
                    </div>

                    {{-- Новый файл (замена) --}}
                    <div class="new-file-section">
                        <div class="new-file-label">
                            <i class="bi bi-arrow-repeat"></i>
                            <span data-i18n="replaceFileLabel">Заменить файл (необязательно)</span>
                        </div>
                        <label class="file-upload" id="file-upload-label">
                            <span id="file-name" data-i18n="filePlaceholder">Выберите новый файл...</span>
                            <i class="bi bi-paperclip"></i>
                            <input type="file" name="file_path" id="file" accept=".pdf,.docx,.xlsx,.rtf">
                        </label>
                        <p style="font-size:9px;color:#8892a6;margin-top:6px;" data-i18n="fileHint">
                            Если не выберете новый файл — останется текущий
                        </p>
                    </div>
                    @else
                    {{-- Файла нет — просто загрузка --}}
                    <div class="field-group">
                        <label class="field-label" data-i18n="labelNewFile">Файл документа</label>
                        <label class="file-upload" id="file-upload-label">
                            <span id="file-name" data-i18n="filePlaceholderEmpty">Выберите файл...</span>
                            <i class="bi bi-paperclip"></i>
                            <input type="file" name="file_path" id="file" accept=".pdf,.docx,.xlsx,.rtf">
                        </label>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Кнопка отправки --}}
                @if($isOwner)
                <div style="margin-top:20px;text-align:center;">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-circle-fill"></i>
                        <span data-i18n="saveChanges">Сохранить изменения</span>
                    </button>
                </div>
                @endif
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
                editDocTitle: "Редактировать документ",
                viewDocTitle: "Просмотр документа",
                readOnly: "Read Only",
                editSubtitle: "Внесите изменения",
                viewSubtitle: "Только для чтения",
                labelNumber: "Номер документа",
                labelType: "Тип документа",
                labelTitle: "Заголовок",
                labelDeadline: "Дедлайн",
                labelDescription: "Описание",
                labelReceiverMode: "Способ отправки",
                labelStatus: "Статус",
                labelNewFile: "Файл документа",
                modeAllTeam: "Всей команде",
                modeAllTeamDesc: "Всем участникам",
                modeSelectTeam: "Выбрать",
                modeSelectTeamDesc: "До 5 человек",
                modeOtherCompany: "Другая команда",
                modeOtherCompanyDesc: "Внешний получатель",
                allTeamInfo: "Отправка всем участникам",
                allTeamDesc: "Документ будет отправлен всей команде",
                selectReceiversLabel: "Выберите получателей (до 5)",
                teamSearchPlaceholder: "Поиск по имени, email или телефону...",
                selectedPlaceholder: "Выбранные пользователи...",
                searchReceiverLabel: "Поиск получателя",
                otherSearchPlaceholder: "Название компании, email или телефон...",
                receiverLabel: "Получатель",
                statusDraft: "Черновик",
                statusActive: "Активен",
                statusCompleted: "Завершён",
                fileSection: "Файл документа",
                currentFile: "Текущий файл",
                activeFile: "Активен",
                replacedFile: "Будет заменён",
                viewBtn: "Смотреть",
                downloadBtn: "Скачать",
                replaceFileLabel: "Заменить файл (необязательно)",
                filePlaceholder: "Выберите новый файл...",
                filePlaceholderEmpty: "Выберите файл...",
                fileHint: "Если не выберете новый файл — останется текущий",
                saveChanges: "Сохранить изменения",
                notFound: "Не найдено",
                maxReceivers: "Максимум 5 человек",
                alertSelectMode: "Выберите способ отправки документа",
                alertSelectReceiver: "Выберите хотя бы одного получателя",
                notSpecified: "Не указан"
            },
            tj: {
                back: "Бозгашт",
                editDocTitle: "Таҳрири ҳуҷҷат",
                viewDocTitle: "Дидани ҳуҷҷат",
                readOnly: "Танҳо хондан",
                editSubtitle: "Тағйирот ворид кунед",
                viewSubtitle: "Танҳо барои хондан",
                labelNumber: "Рақами ҳуҷҷат",
                labelType: "Намуди ҳуҷҷат",
                labelTitle: "Сарлавҳа",
                labelDeadline: "Мӯҳлат",
                labelDescription: "Тавсиф",
                labelReceiverMode: "Усули фиристодан",
                labelStatus: "Ҳолат",
                labelNewFile: "Файли ҳуҷҷат",
                modeAllTeam: "Ба ҳамаи даста",
                modeAllTeamDesc: "Ба ҳамаи иштирокчиён",
                modeSelectTeam: "Интихоб кардан",
                modeSelectTeamDesc: "То 5 нафар",
                modeOtherCompany: "Дигар даста",
                modeOtherCompanyDesc: "Гирандаи берунӣ",
                allTeamInfo: "Фиристодан ба ҳамаи иштирокчиён",
                allTeamDesc: "Ҳуҷҷат ба ҳамаи даста фиристода мешавад",
                selectReceiversLabel: "Гирандаҳоро интихоб кунед (то 5)",
                teamSearchPlaceholder: "Ҷустуҷӯ аз рӯи ном, email ё телефон...",
                selectedPlaceholder: "Корбарони интихобшуда...",
                searchReceiverLabel: "Ҷустуҷӯи гиранда",
                otherSearchPlaceholder: "Номи ширкат, email ё телефон...",
                receiverLabel: "Гиранда",
                statusDraft: "Пешнавис",
                statusActive: "Фаъол",
                statusCompleted: "Анҷомёфта",
                fileSection: "Файли ҳуҷҷат",
                currentFile: "Файли ҷорӣ",
                activeFile: "Фаъол",
                replacedFile: "Иваз мешавад",
                viewBtn: "Дидан",
                downloadBtn: "Боргирӣ",
                replaceFileLabel: "Иваз кардани файл (ихтиёрӣ)",
                filePlaceholder: "Файли навро интихоб кунед...",
                filePlaceholderEmpty: "Файлро интихоб кунед...",
                fileHint: "Агар файли нав интихоб накунед — файли ҷорӣ боқӣ мемонад",
                saveChanges: "Нигоҳ доштани тағйирот",
                notFound: "Ёфт нашуд",
                maxReceivers: "Ҳадди аксар 5 нафар",
                alertSelectMode: "Усули фиристодани ҳуҷҷатро интихоб кунед",
                alertSelectReceiver: "Ҳадди ақал як гирандаро интихоб кунед",
                notSpecified: "Муайян нашудааст"
            },
            en: {
                back: "Back",
                editDocTitle: "Edit Document",
                viewDocTitle: "View Document",
                readOnly: "Read Only",
                editSubtitle: "Make changes",
                viewSubtitle: "Read only",
                labelNumber: "Document Number",
                labelType: "Document Type",
                labelTitle: "Title",
                labelDeadline: "Deadline",
                labelDescription: "Description",
                labelReceiverMode: "Sending Method",
                labelStatus: "Status",
                labelNewFile: "Document File",
                modeAllTeam: "All Team",
                modeAllTeamDesc: "To all members",
                modeSelectTeam: "Select",
                modeSelectTeamDesc: "Up to 5 people",
                modeOtherCompany: "Other Team",
                modeOtherCompanyDesc: "External recipient",
                allTeamInfo: "Sending to all members",
                allTeamDesc: "Document will be sent to the entire team",
                selectReceiversLabel: "Select recipients (up to 5)",
                teamSearchPlaceholder: "Search by name, email or phone...",
                selectedPlaceholder: "Selected users...",
                searchReceiverLabel: "Search recipient",
                otherSearchPlaceholder: "Company name, email or phone...",
                receiverLabel: "Recipient",
                statusDraft: "Draft",
                statusActive: "Active",
                statusCompleted: "Completed",
                fileSection: "Document File",
                currentFile: "Current file",
                activeFile: "Active",
                replacedFile: "Will be replaced",
                viewBtn: "View",
                downloadBtn: "Download",
                replaceFileLabel: "Replace file (optional)",
                filePlaceholder: "Choose new file...",
                filePlaceholderEmpty: "Choose file...",
                fileHint: "If you don't choose a new file — the current one will remain",
                saveChanges: "Save Changes",
                notFound: "Not found",
                maxReceivers: "Maximum 5 people",
                alertSelectMode: "Select document sending method",
                alertSelectReceiver: "Select at least one recipient",
                notSpecified: "Not specified"
            }
        };

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

        const form = document.getElementById('documentForm');
        const modeInput = document.getElementById('receiver_mode');
        const modeButtons = document.querySelectorAll('.mode-btn');
        let currentMode = modeInput ? modeInput.value : null;
        let selectedTeamUsers = [];

        const teamUsers = @json($teamUsersArray ?? []);
        const otherUsers = @json($otherUsersArray ?? []);

        // === 1. ВОССТАНОВЛЕНИЕ СОСТОЯНИЯ ПРИ ЗАГРУЗКЕ (ИСПРАВЛЕНИЕ ИСЧЕЗНОВЕНИЯ ДАННЫХ) ===
        if (modeInput && currentMode) {
            const activeBtn = document.querySelector(`.mode-btn[data-mode="${currentMode}"]`);
            if (activeBtn) {
                activeBtn.classList.add('active');
                const block = document.getElementById('mode-' + currentMode);
                if (block) block.classList.remove('hidden');
            }
        }

        // Восстановление выбранных пользователей команды
        const teamReceiversInput = document.getElementById('team_receivers');
        if (teamReceiversInput && teamReceiversInput.value && currentMode === 'select_team') {
            const ids = teamReceiversInput.value.split(',').map(id => parseInt(id.trim()));
            selectedTeamUsers = teamUsers.filter(u => ids.includes(u.id));
            updateTeamSelected();
        }

        // Восстановление выбранного внешнего получателя
        const otherReceiverInput = document.getElementById('other_receiver_id');
        if (otherReceiverInput && otherReceiverInput.value && currentMode === 'other_company') {
            const user = otherUsers.find(u => u.id == otherReceiverInput.value);
            if (user) {
                document.getElementById('other-name').textContent = user.name;
                const phoneDisplay = user.phone ? ` | ${user.phone}` : '';
                document.getElementById('other-email').textContent = (user.email || '') + phoneDisplay;

                const otherSelectedDiv = document.getElementById('other-selected');
                otherSelectedDiv.classList.remove('hidden');
                otherSelectedDiv.style.display = 'flex'; // Принудительно показываем
            }
        }

        // === 2. ПЕРЕКЛЮЧЕНИЕ РЕЖИМОВ ===
        if (modeButtons.length > 0) {
            modeButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const mode = this.dataset.mode;
                    currentMode = mode;
                    if (modeInput) modeInput.value = mode;

                    modeButtons.forEach(b => b.classList.remove('active'));
                    document.querySelectorAll('.receiver-block').forEach(b => b.classList.add('hidden'));

                    this.classList.add('active');
                    const block = document.getElementById('mode-' + mode);
                    if (block) block.classList.remove('hidden');
                });
            });
        }

        // === 3. ЗАГРУЗКА ФАЙЛА ===
        const fileInput = document.getElementById('file');
        const fileName = document.getElementById('file-name');
        const fileUploadLabel = document.getElementById('file-upload-label');
        const currentFileCard = document.getElementById('current-file-card');
        const currentFileBadge = document.getElementById('current-file-badge');

        if (fileInput && fileName) {
            fileInput.addEventListener('change', function() {
                const t = translations[getCurrentLang()] || translations['ru'];

                if (this.files.length > 0) {
                    fileName.textContent = this.files[0].name;
                    if (fileUploadLabel) fileUploadLabel.classList.add('has-file');

                    if (currentFileCard) {
                        currentFileCard.classList.add('replaced');
                        if (currentFileBadge) {
                            currentFileBadge.classList.add('replaced');
                            const badgeSpan = currentFileBadge.querySelector('span');
                            if (badgeSpan) badgeSpan.textContent = t.replacedFile;
                            const badgeIcon = currentFileBadge.querySelector('i');
                            if (badgeIcon) badgeIcon.className = 'bi bi-arrow-repeat';
                        }
                    }
                } else {
                    fileName.textContent = currentFileCard ? t.filePlaceholder : t.filePlaceholderEmpty;
                    if (fileUploadLabel) fileUploadLabel.classList.remove('has-file');

                    if (currentFileCard) {
                        currentFileCard.classList.remove('replaced');
                        if (currentFileBadge) {
                            currentFileBadge.classList.remove('replaced');
                            const badgeSpan = currentFileBadge.querySelector('span');
                            if (badgeSpan) badgeSpan.textContent = t.activeFile;
                            const badgeIcon = currentFileBadge.querySelector('i');
                            if (badgeIcon) badgeIcon.className = 'bi bi-check-circle-fill';
                        }
                    }
                }
            });
        }

        // === 4. ПОИСК ПОЛЬЗОВАТЕЛЕЙ КОМАНДЫ (С ПОИСКОМ ПО ТЕЛЕФОНУ) ===
        const teamSearch = document.getElementById('team-search');
        const teamList = document.getElementById('team-list');
        const teamSelected = document.getElementById('team-selected');
        const teamReceivers = document.getElementById('team_receivers');

        if (teamSearch) {
            teamSearch.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                if (query.length < 1) {
                    teamList.classList.add('hidden');
                    return;
                }

                const t = translations[getCurrentLang()] || translations['ru'];
                // ИЗМЕНЕНИЕ: Добавлен поиск по телефону (u.phone)
                const filtered = teamUsers.filter(u => {
                    const name = (u.name || '').toLowerCase();
                    const email = (u.email || '').toLowerCase();
                    const phone = (u.phone || '').toLowerCase();
                    return (name.includes(query) || email.includes(query) || phone.includes(query)) &&
                           !selectedTeamUsers.find(s => s.id === u.id);
                });

                teamList.innerHTML = '';
                if (filtered.length === 0) {
                    teamList.innerHTML = `<div style="padding:10px;font-size:11px;color:#8892a6;">${t.notFound}</div>`;
                } else {
                    filtered.forEach(u => {
                        const item = document.createElement('div');
                        item.className = 'dropdown-item';
                        const phoneDisplay = u.phone ? `<span style="color:#8892a6; display:flex; align-items:center; gap:4px;"><i class="bi bi-telephone" style="font-size:10px"></i> ${u.phone}</span>` : '';

                        item.innerHTML = `
                            <div>
                                <span class="name">${u.name}</span>
                                <div class="meta">
                                    <span>${u.email || ''}</span>
                                    ${phoneDisplay}
                                </div>
                            </div>
                            <i class="bi bi-plus-circle-fill add-icon" style="color:#4f8cff;font-size:14px;opacity:0.7;"></i>
                        `;
                        item.addEventListener('click', () => {
                            const t2 = translations[getCurrentLang()] || translations['ru'];
                            if (selectedTeamUsers.length >= 5) {
                                alert(t2.maxReceivers);
                                return;
                            }
                            selectedTeamUsers.push(u);
                            updateTeamSelected();
                            teamSearch.value = '';
                            teamList.classList.add('hidden');
                        });
                        teamList.appendChild(item);
                    });
                }
                teamList.classList.remove('hidden');
            });

            teamSearch.addEventListener('blur', () => {
                setTimeout(() => teamList.classList.add('hidden'), 200);
            });
        }

        function updateTeamSelected() {
            if (!teamSelected) return;
            const t = translations[getCurrentLang()] || translations['ru'];
            teamSelected.innerHTML = '';
            if (selectedTeamUsers.length === 0) {
                teamSelected.innerHTML = `<span style="font-size:10px;color:#8892a6;" data-i18n="selectedPlaceholder">${t.selectedPlaceholder}</span>`;
            } else {
                selectedTeamUsers.forEach((user, idx) => {
                    const chip = document.createElement('span');
                    chip.className = 'chip';
                    chip.innerHTML = `${user.name} <button type="button" data-idx="${idx}">&times;</button>`;
                    chip.querySelector('button').addEventListener('click', () => {
                        selectedTeamUsers.splice(idx, 1);
                        updateTeamSelected();
                    });
                    teamSelected.appendChild(chip);
                });
            }
            if (teamReceivers) {
                teamReceivers.value = selectedTeamUsers.map(u => u.id).join(',');
            }
        }

        // === 5. ПОИСК ПО ДРУГОЙ КОМАНДЕ (С ПОИСКОМ ПО ТЕЛЕФОНУ) ===
        const otherSearch = document.getElementById('other-search');
        const otherList = document.getElementById('other-list');
        const otherSelected = document.getElementById('other-selected');

        if (otherSearch) {
            otherSearch.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                if (query.length < 1) {
                    otherList.classList.add('hidden');
                    return;
                }

                const t = translations[getCurrentLang()] || translations['ru'];
                // ИЗМЕНЕНИЕ: Добавлен поиск по телефону (u.phone)
                const filtered = otherUsers.filter(u => {
                    const name = (u.name || '').toLowerCase();
                    const email = (u.email || '').toLowerCase();
                    const phone = (u.phone || '').toLowerCase();
                    return name.includes(query) || email.includes(query) || phone.includes(query);
                });

                otherList.innerHTML = '';
                if (filtered.length === 0) {
                    otherList.innerHTML = `<div style="padding:10px;font-size:11px;color:#8892a6;">${t.notFound}</div>`;
                } else {
                    filtered.forEach(u => {
                        const item = document.createElement('div');
                        item.className = 'dropdown-item';
                        const phoneDisplay = u.phone ? `<span style="color:#8892a6; display:flex; align-items:center; gap:4px;"><i class="bi bi-telephone" style="font-size:10px"></i> ${u.phone}</span>` : '';

                        item.innerHTML = `
                            <div>
                                <span class="name">${u.name}</span>
                                <div class="meta">
                                    <span>${u.email || ''}</span>
                                    ${phoneDisplay}
                                </div>
                            </div>
                            <i class="bi bi-check-circle-fill add-icon" style="color:#4f8cff;font-size:14px;opacity:0.7;"></i>
                        `;
                        item.addEventListener('click', () => {
                            document.getElementById('other_receiver_id').value = u.id;
                            document.getElementById('other-name').textContent = u.name;
                            const phoneTxt = u.phone ? ` | ${u.phone}` : '';
                            document.getElementById('other-email').textContent = (u.email || '') + phoneTxt;

                            otherSelected.classList.remove('hidden');
                            otherSelected.style.display = 'flex';
                            otherList.classList.add('hidden');
                            otherSearch.value = '';
                        });
                        otherList.appendChild(item);
                    });
                }
                otherList.classList.remove('hidden');
            });

            otherSearch.addEventListener('blur', () => {
                setTimeout(() => otherList.classList.add('hidden'), 200);
            });
        }

        window.clearOtherReceiver = function() {
            document.getElementById('other_receiver_id').value = '';
            otherSelected.classList.add('hidden');
            otherSelected.style.display = 'none';
        };

        // === 6. ВАЛИДАЦИЯ ПРИ ОТПРАВКЕ ===
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!modeInput) return;
                const t = translations[getCurrentLang()] || translations['ru'];
                const mode = modeInput.value;
                if (!mode) {
                    e.preventDefault();
                    alert(t.alertSelectMode);
                    return;
                }
                if (mode === 'select_team' && selectedTeamUsers.length === 0) {
                    e.preventDefault();
                    alert(t.alertSelectReceiver);
                    return;
                }
            });
        }
    });
</script>
@endsection