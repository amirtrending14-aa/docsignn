@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6 min-h-screen">
    @php
    $extension = strtolower(pathinfo($signature->document->file_path, PATHINFO_EXTENSION));

    // Расширенная проверка форматов
    $isWord = in_array($extension, ['doc', 'docx']);
    $isExcel = in_array($extension, ['xls', 'xlsx']);
    $isRtf = $extension === 'rtf';
    $isPdf = $extension === 'pdf';

    // Привязка уникального цвета темы под каждый формат
    if ($isWord) {
    $themeColor = '#2b579a'; // Синий Word
    $badgeClass = 'bg-blue-600';
    } elseif ($isExcel) {
    $themeColor = '#107c41'; // Зеленый Excel
    $badgeClass = 'bg-emerald-600';
    } elseif ($isRtf) {
    $themeColor = '#7c3aed'; // Фиолетовый RTF
    $badgeClass = 'bg-purple-600';
    } else {
    $themeColor = '#6366f1'; // Индиго по умолчанию для PDF
    $badgeClass = 'bg-red-600';
    }

    // Динамическая локализация содержимого самого QR-кода на стороне бэкенда
    $doc = $signature->document;
    $senderName = $doc->sender->name ?? 'Система';
    $signerName = auth()->user()->name ?? 'Пользователь';
    $dateSent = $doc->created_at ? $doc->created_at->format('d.m.Y H:i') : date('d.m.Y H:i');
    $dateSigned = date('d.m.Y H:i');

    $qrText = __('Document') . ": {$doc->title}\n" .
    __('From') . ": {$senderName}\n" .
    __('Signer') . ": {$signerName}\n" .
    __('Date Sent') . ": {$dateSent}\n" .
    __('Date Signed') . ": {$dateSigned}";

    // URL для рендеринга картинки на фронтенде
    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrText);
    @endphp

    <style>
        .edit-sig-page {
            --primary-color: {{ $themeColor }};
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .navbar-style-text, .theme-heading, label, button, .btn-update {
            font-weight: 700 !important;
            letter-spacing: -0.02em !important;
            text-transform: none;
        }

        /* Page Header */
        .edit-sig-header {
            margin-bottom: 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .back-link {
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: #6366f1;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .back-link:hover {
            gap: 12px;
        }

        .back-link svg {
            width: 16px;
            height: 16px;
        }

        .page-title-wrap {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .page-title {
            font-size: 30px;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin: 0;
            color: #1e293b;
        }

        .dark .page-title { color: #ffffff; }

        .format-badge {
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 900;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Grid Layout */
        .edit-sig-grid {
            max-width: 1024px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
        }

        @media (min-width: 1024px) {
            .edit-sig-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Form Card */
        .form-card {
            background: rgba(255, 255, 255, 0.96) !important;
            backdrop-filter: blur(14px);
            border-radius: 2rem;
            border: 2px solid rgba(0, 0, 0, 0.12);
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .dark .form-card { background: #1e293b !important; border-color: rgba(255,255,255,0.12); }

        .form-card-inner {
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        /* Form Fields */
        .form-field {
            display: flex;
            flex-direction: column;
        }

        .form-field-label {
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            margin-bottom: 8px;
            display: block;
        }

        .form-field-label.primary {
            color: #6366f1;
            margin-bottom: 12px;
        }

        .doc-title-display {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            padding: 12px 0;
            border-bottom: 2px solid rgba(99, 102, 241, 0.2);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dark .doc-title-display { color: #ffffff; }

        /* QR Preview Pad */
        .pad-container {
            background-color: rgba(0,0,0,0.02) !important;
            border: 2px dashed var(--primary-color);
            border-radius: 1.7rem;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 208px;
            transition: all 0.3s ease;
        }

        .dark .pad-container { background-color: rgba(255,255,255,0.02) !important; }

        .qr-image-wrapper {
            background: #ffffff;
            padding: 12px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-image {
            width: 120px;
            height: 120px;
            object-fit: contain;
            display: block;
        }

        .qr-verified-text {
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #94a3b8;
            margin-top: 12px;
            text-align: center;
        }

        /* Submit Button */
        .submit-wrapper {
            display: flex;
            justify-content: center;
            padding-top: 8px;
        }

        .btn-update {
            background: var(--primary-color);
            color: #ffffff !important;
            font-weight: 900 !important;
            text-transform: uppercase;
            letter-spacing: 0.08em !important;
            border: 2px solid rgba(255,255,255,0.08);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .btn-update:active {
            transform: scale(0.95);
        }

        /* Right Column */
        .right-column {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* Old Signature Display */
        .old-sig-card {
            padding: 28px;
        }

        .old-sig-label {
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            margin-bottom: 20px;
            display: block;
            text-align: center;
        }

        .old-sig-display {
            background: rgba(0,0,0,0.02);
            border: 2px solid rgba(0,0,0,0.05);
            border-radius: 1.25rem;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 140px;
        }

        .dark .old-sig-display {
            background: #0f172a;
            border-color: rgba(255,255,255,0.05);
        }

        .old-sig-image {
            max-height: 128px;
            object-fit: contain;
            border-radius: 12px;
        }

        .no-stamp-text {
            text-align: center;
            padding: 16px;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        /* Info Warning Box */
        .info-warning-box {
            background: #dc2626;
            border-radius: 2.2rem;
            padding: 28px;
            color: #ffffff;
            box-shadow: 0 25px 50px -12px rgba(220, 38, 38, 0.25);
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.15);
        }

        .info-warning-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .info-icon-box {
            width: 32px;
            height: 32px;
            border-radius: 12px;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .info-icon-box svg {
            width: 16px;
            height: 16px;
            color: #fee2e2;
        }

        .info-warning-title {
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.22em;
            color: #fee2e2;
            margin: 0;
        }

        .info-warning-text {
            font-size: 12px;
            font-weight: 500;
            line-height: 1.6;
            opacity: 0.9;
            margin: 0;
        }

        /* ============================================ */
        /* === ПОЛНАЯ АДАПТИВНОСТЬ === */
        /* ============================================ */

        /* Маленькие десктопы (до 1200px) */
        @media (max-width: 1200px) {
            .edit-sig-header { margin-bottom: 1.6rem; }
            .page-title { font-size: 28px; }
            .edit-sig-grid { gap: 22px; }
            .form-card { border-radius: 1.9rem; }
            .form-card-inner { padding: 26px; gap: 26px; }
            .doc-title-display { font-size: 17px; }
            .pad-container { padding: 22px; min-height: 200px; }
            .qr-image { width: 115px; height: 115px; }
            .btn-update { padding: 10px 19px; font-size: 11px; }
            .right-column { gap: 22px; }
            .old-sig-card { padding: 26px; }
            .old-sig-display { padding: 15px; min-height: 135px; }
            .old-sig-image { max-height: 122px; }
            .info-warning-box { padding: 26px; border-radius: 2.1rem; }
            .info-warning-text { font-size: 11.5px; }
        }

        /* Планшеты (до 992px) */
        @media (max-width: 992px) {
            .container { padding-left: 16px; padding-right: 16px; }
            .edit-sig-header { margin-bottom: 1.5rem; gap: 0.9rem; }
            .back-link { font-size: 10px; margin-bottom: 7px; }
            .back-link svg { width: 15px; height: 15px; }
            .page-title { font-size: 26px; }
            .format-badge { font-size: 9px; padding: 2px 9px; }
            .edit-sig-grid { gap: 20px; }
            .form-card { border-radius: 1.8rem; }
            .form-card-inner { padding: 24px; gap: 24px; }
            .form-field-label { font-size: 10px; margin-bottom: 7px; }
            .form-field-label.primary { margin-bottom: 11px; }
            .doc-title-display { font-size: 16px; padding: 11px 0; }
            .pad-container { padding: 20px; min-height: 190px; border-radius: 1.6rem; }
            .qr-image-wrapper { padding: 11px; border-radius: 15px; }
            .qr-image { width: 110px; height: 110px; }
            .qr-verified-text { font-size: 8px; margin-top: 11px; }
            .btn-update { padding: 9px 18px; font-size: 11px; border-radius: 11px; }
            .right-column { gap: 20px; }
            .old-sig-card { padding: 24px; }
            .old-sig-label { font-size: 10px; margin-bottom: 18px; }
            .old-sig-display { padding: 14px; min-height: 130px; border-radius: 1.2rem; }
            .old-sig-image { max-height: 118px; }
            .no-stamp-text { font-size: 11px; }
            .info-warning-box { padding: 24px; border-radius: 2rem; }
            .info-warning-header { gap: 11px; margin-bottom: 14px; }
            .info-icon-box { width: 30px; height: 30px; border-radius: 11px; }
            .info-icon-box svg { width: 15px; height: 15px; }
            .info-warning-title { font-size: 9px; letter-spacing: 0.2em; }
            .info-warning-text { font-size: 11px; line-height: 1.55; }
        }

        /* Большие телефоны (до 768px) */
        @media (max-width: 768px) {
            .container { padding-left: 14px; padding-right: 14px; padding-top: 20px; padding-bottom: 20px; }
            .edit-sig-header { margin-bottom: 1.35rem; gap: 0.8rem; }
            .back-link { font-size: 10px; margin-bottom: 6px; gap: 7px; }
            .back-link:hover { gap: 10px; }
            .back-link svg { width: 14px; height: 14px; }
            .page-title { font-size: 23px; }
            .format-badge { font-size: 9px; padding: 2px 8px; border-radius: 5px; }
            .edit-sig-grid { gap: 18px; }
            .form-card { border-radius: 1.6rem; }
            .form-card-inner { padding: 22px; gap: 22px; }
            .form-field-label { font-size: 10px; margin-bottom: 6px; letter-spacing: 0.09em; }
            .form-field-label.primary { margin-bottom: 10px; }
            .doc-title-display { font-size: 15px; padding: 10px 0; }
            .pad-container { padding: 18px; min-height: 180px; border-radius: 1.5rem; }
            .qr-image-wrapper { padding: 10px; border-radius: 14px; }
            .qr-image { width: 100px; height: 100px; }
            .qr-verified-text { font-size: 8px; margin-top: 10px; letter-spacing: 0.13em; }
            .btn-update { padding: 9px 17px; font-size: 11px; border-radius: 10px; }
            .right-column { gap: 18px; }
            .old-sig-card { padding: 22px; }
            .old-sig-label { font-size: 10px; margin-bottom: 16px; letter-spacing: 0.09em; }
            .old-sig-display { padding: 13px; min-height: 125px; border-radius: 1.15rem; }
            .old-sig-image { max-height: 112px; }
            .no-stamp-text { font-size: 11px; letter-spacing: 0.09em; }
            .info-warning-box { padding: 22px; border-radius: 1.8rem; }
            .info-warning-header { gap: 10px; margin-bottom: 13px; }
            .info-icon-box { width: 28px; height: 28px; border-radius: 10px; }
            .info-icon-box svg { width: 14px; height: 14px; }
            .info-warning-title { font-size: 9px; letter-spacing: 0.19em; }
            .info-warning-text { font-size: 11px; line-height: 1.5; }
        }

        /* Телефоны (до 640px) */
        @media (max-width: 640px) {
            .container { padding-left: 12px; padding-right: 12px; padding-top: 18px; padding-bottom: 18px; }
            .edit-sig-header { margin-bottom: 1.25rem; gap: 0.7rem; }
            .back-link { font-size: 9px; margin-bottom: 5px; letter-spacing: 0.16em; }
            .back-link svg { width: 13px; height: 13px; }
            .page-title { font-size: 20px; }
            .format-badge { font-size: 8px; padding: 2px 7px; }
            .edit-sig-grid { gap: 16px; }
            .form-card { border-radius: 1.5rem; border-width: 1.5px; }
            .form-card-inner { padding: 20px; gap: 20px; }
            .form-field-label { font-size: 9px; margin-bottom: 6px; letter-spacing: 0.08em; }
            .form-field-label.primary { margin-bottom: 9px; }
            .doc-title-display { font-size: 14px; padding: 9px 0; border-bottom-width: 1.5px; }
            .pad-container { padding: 16px; min-height: 170px; border-radius: 1.4rem; border-width: 1.5px; }
            .qr-image-wrapper { padding: 9px; border-radius: 13px; }
            .qr-image { width: 90px; height: 90px; }
            .qr-verified-text { font-size: 8px; margin-top: 9px; }
            .submit-wrapper { padding-top: 6px; }
            .btn-update {
                padding: 10px 18px;
                font-size: 10px;
                border-radius: 10px;
                width: 100%;
                max-width: 320px;
                justify-content: center;
            }
            .right-column { gap: 16px; }
            .old-sig-card { padding: 20px; }
            .old-sig-label { font-size: 9px; margin-bottom: 14px; letter-spacing: 0.08em; }
            .old-sig-display { padding: 12px; min-height: 120px; border-radius: 1.1rem; }
            .old-sig-image { max-height: 105px; }
            .no-stamp-text { font-size: 10px; letter-spacing: 0.08em; }
            .info-warning-box { padding: 20px; border-radius: 1.6rem; border-width: 1.5px; }
            .info-warning-header { gap: 9px; margin-bottom: 12px; }
            .info-icon-box { width: 26px; height: 26px; border-radius: 9px; }
            .info-icon-box svg { width: 13px; height: 13px; }
            .info-warning-title { font-size: 9px; letter-spacing: 0.18em; }
            .info-warning-text { font-size: 10.5px; line-height: 1.5; }
        }

        /* Маленькие телефоны (до 480px) */
        @media (max-width: 480px) {
            .container { padding-left: 10px; padding-right: 10px; padding-top: 16px; padding-bottom: 16px; }
            .edit-sig-header { margin-bottom: 1.15rem; gap: 0.6rem; }
            .back-link { font-size: 9px; margin-bottom: 5px; letter-spacing: 0.15em; }
            .back-link svg { width: 13px; height: 13px; }
            .page-title { font-size: 18px; }
            .format-badge { font-size: 8px; padding: 2px 6px; }
            .edit-sig-grid { gap: 14px; }
            .form-card { border-radius: 1.35rem; }
            .form-card-inner { padding: 18px; gap: 18px; }
            .form-field-label { font-size: 9px; margin-bottom: 5px; }
            .doc-title-display { font-size: 13px; padding: 8px 0; }
            .pad-container { padding: 14px; min-height: 160px; border-radius: 1.3rem; }
            .qr-image-wrapper { padding: 8px; border-radius: 12px; }
            .qr-image { width: 80px; height: 80px; }
            .qr-verified-text { font-size: 7px; margin-top: 8px; letter-spacing: 0.12em; }
            .btn-update { padding: 9px 16px; font-size: 10px; border-radius: 9px; max-width: 100%; }
            .right-column { gap: 14px; }
            .old-sig-card { padding: 18px; }
            .old-sig-label { font-size: 9px; margin-bottom: 13px; }
            .old-sig-display { padding: 11px; min-height: 115px; border-radius: 1rem; }
            .old-sig-image { max-height: 98px; }
            .no-stamp-text { font-size: 10px; }
            .info-warning-box { padding: 18px; border-radius: 1.45rem; }
            .info-warning-header { gap: 8px; margin-bottom: 11px; }
            .info-icon-box { width: 24px; height: 24px; border-radius: 8px; }
            .info-icon-box svg { width: 12px; height: 12px; }
            .info-warning-title { font-size: 8px; letter-spacing: 0.17em; }
            .info-warning-text { font-size: 10px; line-height: 1.45; }
        }

        /* Очень маленькие телефоны (до 380px) */
        @media (max-width: 380px) {
            .container { padding-left: 8px; padding-right: 8px; padding-top: 14px; padding-bottom: 14px; }
            .edit-sig-header { margin-bottom: 1rem; gap: 0.5rem; }
            .back-link { font-size: 8px; margin-bottom: 4px; letter-spacing: 0.14em; }
            .back-link svg { width: 12px; height: 12px; }
            .page-title { font-size: 17px; }
            .format-badge { font-size: 7px; padding: 2px 5px; }
            .edit-sig-grid { gap: 12px; }
            .form-card { border-radius: 1.25rem; }
            .form-card-inner { padding: 16px; gap: 16px; }
            .form-field-label { font-size: 8px; }
            .doc-title-display { font-size: 12px; padding: 7px 0; }
            .pad-container { padding: 12px; min-height: 150px; border-radius: 1.2rem; }
            .qr-image-wrapper { padding: 7px; border-radius: 11px; }
            .qr-image { width: 70px; height: 70px; }
            .qr-verified-text { font-size: 7px; margin-top: 7px; }
            .btn-update { padding: 9px 15px; font-size: 9px; border-radius: 8px; }
            .right-column { gap: 12px; }
            .old-sig-card { padding: 16px; }
            .old-sig-label { font-size: 8px; margin-bottom: 12px; }
            .old-sig-display { padding: 10px; min-height: 110px; border-radius: 0.95rem; }
            .old-sig-image { max-height: 92px; }
            .info-warning-box { padding: 16px; border-radius: 1.3rem; }
            .info-warning-header { gap: 7px; margin-bottom: 10px; }
            .info-icon-box { width: 22px; height: 22px; border-radius: 7px; }
            .info-icon-box svg { width: 11px; height: 11px; }
            .info-warning-title { font-size: 8px; letter-spacing: 0.16em; }
            .info-warning-text { font-size: 10px; }
        }
    </style>

    <div class="edit-sig-page">
        <div class="edit-sig-header">
            <div>
                <a href="{{ route('signatures.show', $signature->id) }}" class="back-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span data-i18n="backBtn">Назад</span>
                </a>

                <div class="page-title-wrap">
                    <h1 class="page-title" data-i18n="pageTitle">Обновление QR-защиты</h1>
                    <span class="format-badge {{ $badgeClass }}">
                        {{ $extension }}
                    </span>
                </div>
            </div>
        </div>

        <div class="edit-sig-grid">
            {{-- ЛЕВАЯ КОЛОНКА (ФОРМА) --}}
            <div class="form-card">
                <form method="POST" action="{{ route('signatures.update', $signature->id) }}" id="signatureForm" class="form-card-inner">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="qr_payload" id="qrPayloadInput" value="{{ $qrText }}">

                    <div class="form-field">
                        <label class="form-field-label" data-i18n="labelDoc">Документ</label>
                        <div class="doc-title-display">
                            {{ $signature->document->title }}
                        </div>
                    </div>

                    <div class="form-field">
                        <label class="form-field-label primary" data-i18n="labelNewSig">Новый QR-код Верификации</label>

                        <div class="pad-container">
                            <div class="qr-image-wrapper">
                                <img src="{{ $qrUrl }}" alt="New QR Verification" class="qr-image">
                            </div>
                            <span class="qr-verified-text">Verified DocSign</span>
                        </div>
                    </div>

                    {{-- Компактная кнопка отправки --}}
                    <div class="submit-wrapper">
                        <button type="submit" class="btn-update">
                            <span data-i18n="submitBtn">Обновить документ</span> ({{ strtoupper($extension) }})
                        </button>
                    </div>
                </form>
            </div>

            {{-- ПРАВАЯ КОЛОНКА (ИНФОРМАЦИЯ) --}}
            <div class="right-column">
                <div class="form-card old-sig-card">
                    <label class="old-sig-label" data-i18n="labelCurrent">Предыдущий QR Штамп</label>
                    <div class="old-sig-display">
                        @if($signature->signature && Storage::disk('public')->exists($signature->signature))
                        <img src="{{ asset('storage/' . $signature->signature) }}" class="old-sig-image" alt="Current QR Signature">
                        @else
                        <div class="no-stamp-text" data-i18n="noStamp">
                            Файл штампа не найден
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Блок "Внимание" с адаптивным текстом под формат --}}
                <div class="info-warning-box">
                    <div class="info-warning-header">
                        <div class="info-icon-box">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h4 class="info-warning-title" data-i18n="infoTitle">Внимание</h4>
                    </div>
                    <p class="info-warning-text" id="infoTextContainer">
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const SIGN_EDIT_TRANSLATIONS = {
            ru: {
                backBtn: 'Назад',
                pageTitle: 'Обновление QR-защиты',
                labelDoc: 'Документ',
                labelNewSig: 'Новый QR-код Верификации',
                submitBtn: 'Обновить документ',
                labelCurrent: 'Предыдущий QR Штамп',
                noStamp: 'Файл штампа не найден',
                infoTitle: 'Внимание',
                infoTextPdf: 'При обновлении штампа система полностью перегенерирует документ. Текущий QR-код будет замещён актуальной версией, а старый файл удалён из системы для оптимизации памяти.',
                infoTextWord: 'Для документов Word (.docx) штамп защиты перезапишется в структуре файла. Убедитесь, что исходный макет не содержит конфликтов.',
                infoTextExcel: 'Для таблиц Excel (.xlsx) защитный QR-код будет обновлен непосредственно внутри структуры листа метаданных без потери ваших формул.',
                infoTextRtf: 'Для документов формата RTF структура разметки будет перекомпилирована с интеграцией нового бинарного контейнера штампа.'
            },
            tj: {
                backBtn: 'Бозгашт',
                pageTitle: 'Навсозии муҳофизати QR',
                labelDoc: 'Ҳуҷҷат',
                labelNewSig: 'QR-коди нави тасдиқкунанда',
                submitBtn: 'Навсозии ҳуҷҷат',
                labelCurrent: 'Муҳри QR-и қаблӣ',
                noStamp: 'Файли муҳр ёфт нашуд',
                infoTitle: 'Диққат',
                infoTextPdf: 'Ҳангоми навсозии муҳр система ҳуҷҷати комилан аз нав месозад. QR-коди ҷорӣ бо нусхаи нав иваз карда шуда, файли кӯҳна барои сарфаи хотира нест карда мешавад.',
                infoTextWord: 'Барои ҳуҷҷатҳои Word (.docx) муҳри муҳофизатӣ дар сохтори файл аз нав навишта мешавад. Боварӣ ҳосил кунед, ки формати файл дуруст аст.',
                infoTextExcel: 'Барои ҷадвалҳои Excel (.xlsx) коди муҳофизатии QR бевосита дар дохили сохтори варақ бидуни вайрон кардани формулаҳо нав карда мешавад.',
                infoTextRtf: 'Барои ҳуҷҷатҳои формати RTF сохтори маркап аз нав компилятсия шуда, контейнери бинарии нав ворид карда мешавад.'
            },
            en: {
                backBtn: 'Back',
                pageTitle: 'Update QR Protection',
                labelDoc: 'Document',
                labelNewSig: 'New QR Verification Code',
                submitBtn: 'Update Document',
                labelCurrent: 'Previous QR Stamp',
                noStamp: 'Stamp file not found',
                infoTitle: 'Attention',
                infoTextPdf: 'When updating the stamp, the system completely regenerates document. The current QR code will be replaced with the updated version, and the old file will be deleted to optimize storage.',
                infoTextWord: 'For Word documents (.docx), the protection stamp will be overwritten within the file structure. Please ensure the file format is valid.',
                infoTextExcel: 'For Excel spreadsheets (.xlsx), the secure QR code will be updated right inside the sheet structure without breaking formulas.',
                infoTextRtf: 'For RTF files, the layout markup will be recompiled to natively integrate the new stamp binary container.'
            }
        };

        function applySignatureEditTranslations(lang) {
            const dict = SIGN_EDIT_TRANSLATIONS[lang] || SIGN_EDIT_TRANSLATIONS.ru;

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

            updateInfoText(dict);
        }

        function updateInfoText(dict) {
            const ext = "{{ $extension }}";
            const infoContainer = document.getElementById('infoTextContainer');

            if (!infoContainer) return;

            if (['doc', 'docx'].includes(ext)) {
                infoContainer.textContent = dict.infoTextWord;
            } else if (['xls', 'xlsx'].includes(ext)) {
                infoContainer.textContent = dict.infoTextExcel;
            } else if (ext === 'rtf') {
                infoContainer.textContent = dict.infoTextRtf;
            } else {
                infoContainer.textContent = dict.infoTextPdf;
            }
        }

        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applySignatureEditTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applySignatureEditTranslations(lang);
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applySignatureEditTranslations(e.newValue);
            }
        });
    });
</script>
@endsection