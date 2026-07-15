@extends('layouts.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Подключаем необходимые библиотеки -->
<script src="https://unpkg.com/jszip/dist/jszip.min.js"></script>
<script src="https://unpkg.com/docx-preview/dist/docx-preview.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

@php
$doc = $signature->document ?? null;
$filePath = $doc->file_path ?? '';

// Получаем расширение и размер с проверками
$extension = '';
$fileSize = 0;
$fullFileUrl = '';

if ($filePath && Storage::disk('public')->exists($filePath)) {
$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$fileSize = Storage::disk('public')->size($filePath);
$fullFileUrl = asset('storage/' . $filePath);
}

$isWord = in_array($extension, ['doc', 'docx']);
$isPdf = $extension === 'pdf';
$isExcel = in_array($extension, ['xls', 'xlsx']);

$formattedSize = $fileSize > 1048576
? round($fileSize / 1048576, 2) . ' МБ'
: ($fileSize > 1024 ? round($fileSize / 1024, 1) . ' КБ' : $fileSize . ' Б');
@endphp

<style>
    .view-sig-page {
        min-height: 100vh;
        padding: 32px 24px;
        color: var(--text);
        font-family: 'Inter', sans-serif;
    }

    /* Кнопка назад */
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: rgba(var(--glow), 1);
        text-decoration: none;
        transition: all 0.25s ease;
        margin-bottom: 16px;
    }

    .back-link:hover {
        color: rgba(var(--glow), 0.8);
        transform: translateX(-3px);
    }

    .back-link svg {
        width: 16px;
        height: 16px;
    }

    /* Заголовок */
    .page-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .page-title {
        font-size: 26px;
        font-weight: 700;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 10px;
        letter-spacing: -0.5px;
        margin: 0;
    }

    .page-title::before {
        content: "";
        width: 4px;
        height: 28px;
        background: linear-gradient(180deg, rgba(var(--glow), 1), rgba(var(--glow), 0.3));
        border-radius: 2px;
        box-shadow: 0 0 10px rgba(var(--glow), 0.6);
        flex-shrink: 0;
    }

    .title-badges {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .format-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #ffffff;
        font-family: 'JetBrains Mono', monospace;
        box-shadow: 0 0 12px currentColor;
        white-space: nowrap;
    }

    .format-badge.pdf {
        background: rgba(255, 99, 99, 0.2);
        color: #ff6363;
        border: 1px solid rgba(255, 99, 99, 0.4);
    }
    .format-badge.word {
        background: rgba(79, 140, 255, 0.2);
        color: rgba(79, 140, 255, 1);
        border: 1px solid rgba(79, 140, 255, 0.4);
    }
    .format-badge.excel {
        background: rgba(76, 217, 130, 0.2);
        color: #4cd982;
        border: 1px solid rgba(76, 217, 130, 0.4);
    }
    .format-badge.rtf {
        background: rgba(167, 139, 250, 0.2);
        color: #a78bfa;
        border: 1px solid rgba(167, 139, 250, 0.4);
    }

    .file-size {
        font-size: 12px;
        color: var(--text);
        font-weight: 600;
        font-family: 'JetBrains Mono', monospace;
        padding: 6px 12px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--line);
        border-radius: 8px;
        white-space: nowrap;
    }

    /* Карточки */
    .sig-card {
        position: relative;
        background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 24px;
        overflow: hidden;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }

    .sig-card::before {
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

    .sig-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 40px -20px rgba(var(--glow), 0.3);
    }

    /* Сетка информации */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }

    @media (min-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr 2fr 1fr;
        }
    }

    .info-column {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .info-column.right {
        align-items: flex-end;
        text-align: right;
    }

    /* Поле информации */
    .info-item {
        padding: 14px;
        background: rgba(255,255,255,0.02);
        border: 1px solid var(--line);
        border-radius: 10px;
        transition: all 0.2s ease;
    }

    .info-item:hover {
        border-color: rgba(var(--glow), 0.3);
        background: rgba(var(--glow), 0.03);
    }

    .info-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--muted);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .info-label svg {
        width: 14px;
        height: 14px;
        opacity: 0.7;
        flex-shrink: 0;
    }

    .info-value {
        font-size: 15px;
        font-weight: 700;
        color: var(--text);
        letter-spacing: -0.2px;
        word-break: break-word;
    }

    .info-value.mono {
        font-family: 'JetBrains Mono', monospace;
        font-size: 20px;
        color: rgba(var(--glow), 1);
    }

    .info-value.large {
        font-size: 18px;
    }

    .info-sub {
        font-size: 11px;
        color: var(--muted);
        font-weight: 600;
        margin-top: 2px;
    }

    /* Разделитель */
    .divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--line), transparent);
        margin: 8px 0;
    }

    /* Бейдж статуса */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    .status-badge.signed {
        background: rgba(76, 217, 130, 0.12);
        color: #4cd982;
        border: 1px solid rgba(76, 217, 130, 0.25);
    }

    .status-badge.signed .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #4cd982;
        box-shadow: 0 0 8px #4cd982;
        animation: pulse 2s infinite;
    }

    .status-badge.pending {
        background: rgba(255, 181, 71, 0.12);
        color: #ffb547;
        border: 1px solid rgba(255, 181, 71, 0.25);
    }

    .status-badge.pending .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #ffb547;
        box-shadow: 0 0 8px #ffb547;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    /* Содержимое */
    .content-text {
        font-size: 13px;
        font-weight: 500;
        color: var(--text);
        line-height: 1.6;
        max-height: 96px;
        overflow-y: auto;
        padding-right: 8px;
    }

    .content-text::-webkit-scrollbar {
        width: 4px;
    }

    .content-text::-webkit-scrollbar-thumb {
        background: rgba(var(--glow), 0.3);
        border-radius: 2px;
    }

    /* Кнопки действий */
    .btn-sign {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 12px 20px;
        background: linear-gradient(180deg, rgba(var(--glow), 0.95), rgba(var(--glow), 0.65));
        color: #0a0d14;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.25s ease;
        box-shadow: 0 8px 24px rgba(var(--glow), 0.35), inset 0 1px 0 rgba(255,255,255,0.3);
        border: 1px solid transparent;
    }

    .btn-sign:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(var(--glow), 0.5);
        filter: brightness(1.08);
    }

    .btn-sign svg {
        width: 16px;
        height: 16px;
    }

    .verified-box {
        background: rgba(76, 217, 130, 0.08);
        border: 1px solid rgba(76, 217, 130, 0.25);
        border-radius: 10px;
        padding: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }

    .verified-box img {
        max-height: 40px;
        object-fit: contain;
    }

    .verified-text {
        font-size: 9px;
        font-weight: 800;
        color: #4cd982;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Preview секция */
    .preview-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .preview-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--muted);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .preview-title::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(var(--glow), 1);
        box-shadow: 0 0 8px rgba(var(--glow), 0.8);
        flex-shrink: 0;
    }

    .preview-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--line);
        display: grid;
        place-items: center;
        cursor: pointer;
        color: var(--muted);
        transition: all 0.25s ease;
    }

    .btn-icon:hover {
        color: var(--text);
        border-color: rgba(var(--glow), 0.4);
        box-shadow: 0 0 14px rgba(var(--glow), 0.25);
    }

    .btn-icon svg {
        width: 16px;
        height: 16px;
    }

    .btn-download {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--line);
        border-radius: 10px;
        color: var(--text);
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.25s ease;
        white-space: nowrap;
    }

    .btn-download:hover {
        color: var(--text);
        border-color: rgba(var(--glow), 0.4);
        background: rgba(var(--glow), 0.08);
        box-shadow: 0 0 12px rgba(var(--glow), 0.2);
    }

    /* Preview контейнер */
    .preview-container {
        height: 700px;
        border-radius: var(--radius);
        overflow: hidden;
        border: 1px solid var(--line);
        background: #0a0d14;
        position: relative;
        transition: all 0.3s ease;
    }

    .preview-container:hover {
        border-color: rgba(var(--glow), 0.3);
    }

    .preview-container iframe {
        width: 100%;
        height: 100%;
        border: 0;
    }

    /* Excel Preview Styles */
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
    }

    .excel-sheet-tabs {
        display: flex;
        gap: 4px;
        padding: 8px 12px;
        background: #141925;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        overflow-x: auto;
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
    }

    .excel-table td {
        padding: 6px 12px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        color: #1a1a1a;
    }

    .excel-table tbody tr:nth-child(even) {
        background: rgba(76, 217, 130, 0.03);
    }

    .excel-table tbody tr:hover {
        background: rgba(76, 217, 130, 0.08);
    }

    .preview-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--muted);
        gap: 12px;
    }

    .preview-empty i {
        font-size: 48px;
        opacity: 0.3;
    }

    .preview-empty p {
        font-size: 13px;
        font-weight: 600;
        margin: 0;
    }

    /* Анимация появления */
    .animate-in {
        opacity: 0;
        transform: translateY(12px);
    }

    /* ============================================ */
    /* === ПОЛНАЯ АДАПТИВНОСТЬ === */
    /* ============================================ */

    /* Маленькие десктопы (до 1200px) */
    @media (max-width: 1200px) {
        .view-sig-page { padding: 28px 20px; }
        .page-title-row { margin-bottom: 22px; gap: 14px; }
        .page-title { font-size: 24px; }
        .page-title::before { width: 4px; height: 26px; }
        .sig-card { padding: 22px; margin-bottom: 18px; }
        .info-grid { gap: 22px; }
        .info-column { gap: 16px; }
        .info-item { padding: 13px; border-radius: 9px; }
        .info-label { font-size: 9px; margin-bottom: 5px; }
        .info-value { font-size: 14px; }
        .info-value.mono { font-size: 19px; }
        .info-value.large { font-size: 17px; }
        .info-sub { font-size: 10px; }
        .content-text { font-size: 12px; max-height: 90px; }
        .status-badge { padding: 5px 13px; font-size: 9px; }
        .btn-sign { padding: 11px 18px; font-size: 10px; }
        .btn-sign svg { width: 15px; height: 15px; }
        .verified-box { padding: 11px; }
        .verified-box img { max-height: 38px; }
        .verified-text { font-size: 8px; }
        .preview-header { margin-bottom: 14px; gap: 10px; }
        .preview-title { font-size: 12px; }
        .btn-icon { width: 36px; height: 36px; }
        .btn-icon svg { width: 15px; height: 15px; }
        .btn-download { padding: 7px 13px; font-size: 10px; }
        .preview-container { height: 650px; }
        .excel-toolbar { padding: 9px 14px; }
        .excel-file-info { font-size: 11px; }
        .excel-file-info i { font-size: 17px; }
        .excel-sheet-tab { padding: 6px 14px; font-size: 10px; }
        .excel-table th { padding: 7px 12px; font-size: 11px; }
        .excel-table td { padding: 5px 11px; font-size: 11px; }
        .preview-empty i { font-size: 44px; }
        .preview-empty p { font-size: 12px; }
    }

    /* Планшеты (до 992px) */
    @media (max-width: 992px) {
        .view-sig-page { padding: 24px 18px; }
        .back-link { font-size: 10px; margin-bottom: 14px; gap: 7px; }
        .back-link svg { width: 15px; height: 15px; }
        .page-title-row { margin-bottom: 20px; gap: 12px; }
        .page-title { font-size: 22px; gap: 9px; }
        .page-title::before { width: 3px; height: 24px; }
        .title-badges { gap: 10px; }
        .format-badge { padding: 5px 12px; font-size: 9px; border-radius: 9px; letter-spacing: 0.9px; }
        .file-size { font-size: 11px; padding: 5px 11px; border-radius: 7px; }
        .sig-card { padding: 20px; margin-bottom: 16px; border-radius: 13px; }
        .info-grid { gap: 20px; }
        .info-column { gap: 15px; }
        .info-item { padding: 12px; border-radius: 9px; }
        .info-label { font-size: 9px; margin-bottom: 5px; letter-spacing: 0.7px; gap: 5px; }
        .info-label svg { width: 13px; height: 13px; }
        .info-value { font-size: 14px; }
        .info-value.mono { font-size: 18px; }
        .info-value.large { font-size: 16px; }
        .info-sub { font-size: 10px; }
        .divider { margin: 7px 0; }
        .status-badge { padding: 5px 12px; font-size: 9px; letter-spacing: 0.7px; }
        .status-badge .dot { width: 5px; height: 5px; }
        .content-text { font-size: 12px; max-height: 85px; }
        .btn-sign { padding: 11px 17px; font-size: 10px; border-radius: 9px; letter-spacing: 1.1px; }
        .btn-sign svg { width: 14px; height: 14px; }
        .verified-box { padding: 10px; border-radius: 9px; }
        .verified-box img { max-height: 36px; }
        .verified-text { font-size: 8px; letter-spacing: 0.9px; }
        .preview-header { margin-bottom: 13px; gap: 9px; }
        .preview-title { font-size: 12px; gap: 7px; }
        .preview-title::before { width: 7px; height: 7px; }
        .preview-actions { gap: 7px; }
        .btn-icon { width: 35px; height: 35px; border-radius: 9px; }
        .btn-icon svg { width: 14px; height: 14px; }
        .btn-download { padding: 7px 12px; font-size: 10px; border-radius: 9px; }
        .preview-container { height: 600px; border-radius: 13px; }
        .excel-toolbar { padding: 8px 13px; gap: 10px; }
        .excel-file-info { font-size: 11px; gap: 9px; }
        .excel-file-info i { font-size: 16px; }
        .excel-sheet-tabs { padding: 7px 11px; gap: 3px; }
        .excel-sheet-tab { padding: 6px 13px; font-size: 10px; border-radius: 7px; }
        .excel-table th { padding: 7px 11px; font-size: 11px; }
        .excel-table td { padding: 5px 10px; font-size: 11px; }
        .preview-empty i { font-size: 42px; }
        .preview-empty p { font-size: 12px; }
    }

    /* Большие телефоны (до 768px) */
    @media (max-width: 768px) {
        .view-sig-page { padding: 20px 16px; }
        .back-link { font-size: 10px; margin-bottom: 13px; gap: 6px; letter-spacing: 0.7px; }
        .back-link svg { width: 14px; height: 14px; }
        .page-title-row { margin-bottom: 18px; gap: 10px; }
        .page-title { font-size: 20px; gap: 8px; }
        .page-title::before { width: 3px; height: 22px; }
        .title-badges { gap: 9px; }
        .format-badge { padding: 5px 11px; font-size: 9px; border-radius: 8px; }
        .file-size { font-size: 10px; padding: 5px 10px; border-radius: 7px; }
        .sig-card { padding: 18px; margin-bottom: 15px; border-radius: 12px; }
        .info-grid { gap: 18px; }
        .info-column { gap: 14px; }
        .info-column.right { align-items: flex-start; text-align: left; }
        .info-item { padding: 11px; border-radius: 8px; }
        .info-label { font-size: 9px; margin-bottom: 5px; letter-spacing: 0.6px; }
        .info-value { font-size: 13px; }
        .info-value.mono { font-size: 17px; }
        .info-value.large { font-size: 15px; }
        .info-sub { font-size: 10px; }
        .divider { margin: 6px 0; }
        .status-badge { padding: 5px 11px; font-size: 9px; letter-spacing: 0.6px; }
        .content-text { font-size: 11px; max-height: 80px; }
        .btn-sign { padding: 10px 16px; font-size: 10px; border-radius: 9px; letter-spacing: 1px; }
        .btn-sign svg { width: 14px; height: 14px; }
        .verified-box { padding: 10px; border-radius: 8px; }
        .verified-box img { max-height: 34px; }
        .verified-text { font-size: 8px; }
        .preview-header { margin-bottom: 12px; gap: 8px; }
        .preview-title { font-size: 11px; gap: 6px; }
        .preview-title::before { width: 6px; height: 6px; }
        .preview-actions { gap: 6px; }
        .btn-icon { width: 34px; height: 34px; border-radius: 8px; }
        .btn-icon svg { width: 13px; height: 13px; }
        .btn-download { padding: 7px 11px; font-size: 10px; border-radius: 8px; }
        .preview-container { height: 500px; border-radius: 12px; }
        .excel-toolbar { padding: 8px 12px; }
        .excel-file-info { font-size: 11px; }
        .excel-file-info i { font-size: 15px; }
        .excel-sheet-tabs { padding: 6px 10px; }
        .excel-sheet-tab { padding: 5px 12px; font-size: 10px; }
        .excel-table th { padding: 6px 10px; font-size: 10px; }
        .excel-table td { padding: 5px 9px; font-size: 10px; }
        .preview-empty i { font-size: 38px; }
        .preview-empty p { font-size: 11px; }
    }

    /* Телефоны (до 640px) */
    @media (max-width: 640px) {
        .view-sig-page { padding: 18px 14px; }
        .back-link { font-size: 9px; margin-bottom: 12px; letter-spacing: 0.6px; }
        .back-link svg { width: 13px; height: 13px; }
        .page-title-row { margin-bottom: 16px; gap: 9px; }
        .page-title { font-size: 18px; gap: 7px; }
        .page-title::before { width: 3px; height: 20px; }
        .title-badges { gap: 8px; width: 100%; }
        .format-badge { padding: 4px 10px; font-size: 8px; border-radius: 7px; letter-spacing: 0.8px; }
        .file-size { font-size: 10px; padding: 4px 9px; border-radius: 6px; }
        .sig-card { padding: 16px; margin-bottom: 14px; border-radius: 11px; }
        .info-grid { gap: 16px; }
        .info-column { gap: 13px; }
        .info-item { padding: 10px; border-radius: 8px; }
        .info-label { font-size: 8px; margin-bottom: 4px; letter-spacing: 0.5px; gap: 4px; }
        .info-label svg { width: 12px; height: 12px; }
        .info-value { font-size: 13px; }
        .info-value.mono { font-size: 16px; }
        .info-value.large { font-size: 14px; }
        .info-sub { font-size: 9px; }
        .divider { margin: 5px 0; }
        .status-badge { padding: 4px 10px; font-size: 8px; letter-spacing: 0.5px; }
        .status-badge .dot { width: 5px; height: 5px; }
        .content-text { font-size: 11px; max-height: 75px; line-height: 1.5; }
        .btn-sign { padding: 10px 15px; font-size: 9px; border-radius: 8px; letter-spacing: 0.9px; }
        .btn-sign svg { width: 13px; height: 13px; }
        .verified-box { padding: 9px; border-radius: 8px; }
        .verified-box img { max-height: 32px; }
        .verified-text { font-size: 7px; letter-spacing: 0.8px; }
        .preview-header { margin-bottom: 11px; gap: 7px; }
        .preview-title { font-size: 11px; gap: 5px; }
        .preview-title::before { width: 6px; height: 6px; }
        .preview-actions { gap: 5px; width: 100%; }
        .btn-icon { width: 32px; height: 32px; border-radius: 8px; }
        .btn-icon svg { width: 13px; height: 13px; }
        .btn-download { padding: 6px 10px; font-size: 9px; border-radius: 7px; }
        .preview-container { height: 450px; border-radius: 11px; }
        .excel-toolbar { padding: 7px 11px; gap: 8px; }
        .excel-file-info { font-size: 10px; gap: 8px; }
        .excel-file-info i { font-size: 14px; }
        .excel-sheet-tabs { padding: 6px 9px; }
        .excel-sheet-tab { padding: 5px 11px; font-size: 9px; border-radius: 6px; }
        .excel-table th { padding: 5px 9px; font-size: 10px; }
        .excel-table td { padding: 4px 8px; font-size: 10px; }
        .preview-empty i { font-size: 34px; }
        .preview-empty p { font-size: 10px; }
    }

    /* Маленькие телефоны (до 480px) */
    @media (max-width: 480px) {
        .view-sig-page { padding: 16px 12px; }
        .back-link { font-size: 9px; margin-bottom: 11px; gap: 5px; letter-spacing: 0.5px; }
        .back-link svg { width: 13px; height: 13px; }
        .page-title-row { margin-bottom: 14px; gap: 8px; }
        .page-title { font-size: 17px; gap: 6px; }
        .page-title::before { width: 2px; height: 19px; }
        .title-badges { gap: 7px; }
        .format-badge { padding: 4px 9px; font-size: 8px; border-radius: 7px; }
        .file-size { font-size: 9px; padding: 4px 8px; }
        .sig-card { padding: 14px; margin-bottom: 13px; border-radius: 10px; }
        .info-grid { gap: 14px; }
        .info-column { gap: 12px; }
        .info-item { padding: 10px; border-radius: 7px; }
        .info-label { font-size: 8px; margin-bottom: 4px; letter-spacing: 0.4px; }
        .info-value { font-size: 12px; }
        .info-value.mono { font-size: 15px; }
        .info-value.large { font-size: 13px; }
        .info-sub { font-size: 9px; }
        .status-badge { padding: 4px 9px; font-size: 8px; }
        .content-text { font-size: 10px; max-height: 70px; }
        .btn-sign { padding: 9px 14px; font-size: 9px; border-radius: 8px; }
        .btn-sign svg { width: 12px; height: 12px; }
        .verified-box { padding: 8px; border-radius: 7px; }
        .verified-box img { max-height: 30px; }
        .verified-text { font-size: 7px; }
        .preview-header { margin-bottom: 10px; gap: 6px; }
        .preview-title { font-size: 10px; }
        .btn-icon { width: 30px; height: 30px; border-radius: 7px; }
        .btn-icon svg { width: 12px; height: 12px; }
        .btn-download { padding: 6px 9px; font-size: 9px; border-radius: 7px; }
        .preview-container { height: 400px; border-radius: 10px; }
        .excel-toolbar { padding: 6px 10px; }
        .excel-file-info { font-size: 10px; }
        .excel-file-info i { font-size: 13px; }
        .excel-sheet-tabs { padding: 5px 8px; }
        .excel-sheet-tab { padding: 4px 10px; font-size: 9px; }
        .excel-table th { padding: 5px 8px; font-size: 9px; }
        .excel-table td { padding: 4px 7px; font-size: 9px; }
        .preview-empty i { font-size: 30px; }
        .preview-empty p { font-size: 10px; }
    }

    /* Очень маленькие телефоны (до 380px) */
    @media (max-width: 380px) {
        .view-sig-page { padding: 14px 10px; }
        .back-link { font-size: 8px; margin-bottom: 10px; }
        .back-link svg { width: 12px; height: 12px; }
        .page-title-row { margin-bottom: 12px; gap: 7px; }
        .page-title { font-size: 16px; gap: 5px; }
        .page-title::before { width: 2px; height: 18px; }
        .title-badges { gap: 6px; }
        .format-badge { padding: 3px 8px; font-size: 7px; border-radius: 6px; }
        .file-size { font-size: 9px; padding: 3px 7px; }
        .sig-card { padding: 12px; margin-bottom: 12px; border-radius: 9px; }
        .info-grid { gap: 12px; }
        .info-column { gap: 11px; }
        .info-item { padding: 9px; border-radius: 7px; }
        .info-label { font-size: 7px; margin-bottom: 3px; }
        .info-value { font-size: 12px; }
        .info-value.mono { font-size: 14px; }
        .info-value.large { font-size: 13px; }
        .info-sub { font-size: 8px; }
        .status-badge { padding: 3px 8px; font-size: 7px; }
        .content-text { font-size: 10px; max-height: 65px; }
        .btn-sign { padding: 9px 13px; font-size: 8px; border-radius: 7px; }
        .btn-sign svg { width: 12px; height: 12px; }
        .verified-box { padding: 7px; }
        .verified-box img { max-height: 28px; }
        .verified-text { font-size: 7px; }
        .preview-header { margin-bottom: 9px; }
        .preview-title { font-size: 10px; }
        .btn-icon { width: 28px; height: 28px; border-radius: 7px; }
        .btn-icon svg { width: 11px; height: 11px; }
        .btn-download { padding: 5px 8px; font-size: 8px; border-radius: 6px; }
        .preview-container { height: 360px; border-radius: 9px; }
        .excel-toolbar { padding: 5px 9px; }
        .excel-file-info { font-size: 9px; }
        .excel-sheet-tab { padding: 4px 9px; font-size: 8px; }
        .excel-table th { padding: 4px 7px; font-size: 9px; }
        .excel-table td { padding: 3px 6px; font-size: 9px; }
        .preview-empty i { font-size: 26px; }
        .preview-empty p { font-size: 9px; }
    }
</style>

<div class="view-sig-page">

    {{-- Кнопка назад --}}
    <a href="{{ route('signatures.index') }}" class="back-link animate-in">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path d="M15 19l-7-7 7-7"/>
        </svg>
        <span data-i18n="backToList">Реестр документов</span>
    </a>

    {{-- Заголовок --}}
    <div class="page-title-row animate-in">
        <h1 class="page-title" data-i18n="cardTitle">Карточка документа</h1>
        @if($extension)
        <div class="title-badges">
            <span class="format-badge {{ $isPdf ? 'pdf' : ($isWord ? 'word' : ($isExcel ? 'excel' : 'rtf')) }}">
                <i class="bi bi-file-earmark-{{ $isPdf ? 'pdf' : ($isWord ? 'word' : ($isExcel ? 'excel' : 'text')) }}"></i>
                {{ strtoupper($extension) }}
            </span>
            <span class="file-size">{{ $formattedSize }}</span>
        </div>
        @endif
    </div>

    {{-- ИНФОРМАЦИОННАЯ КАРТОЧКА --}}
    <div class="sig-card animate-in">
        <div class="info-grid">

            {{-- Статус + Мета --}}
            <div class="info-column">
                <div class="info-item">
                    <div class="info-label">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span data-i18n="statusLabel">Статус</span>
                    </div>
                    @if($signature->signed_at)
                    <div class="status-badge signed">
                        <span class="dot"></span>
                        <span data-i18n="statusSigned">Подписано</span>
                    </div>
                    @else
                    <div class="status-badge pending">
                        <span class="dot"></span>
                        <span data-i18n="statusPending">Ожидание</span>
                    </div>
                    @endif
                </div>

                <div class="info-item">
                    <div class="info-label">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                        <span data-i18n="idLabel">ID</span>
                    </div>
                    <div class="info-value mono">#{{ str_pad($signature->id, 6, '0', STR_PAD_LEFT) }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span data-i18n="numberLabel">№ Документа</span>
                    </div>
                    <div class="info-value">{{ $doc->number ?? '—' }}</div>
                </div>
            </div>

            {{-- Название + Содержание --}}
            <div class="info-column">
                <div class="info-item">
                    <div class="info-label">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span data-i18n="nameLabel">Название</span>
                    </div>
                    <div class="info-value large">{{ $doc->title ?? '—' }}</div>
                </div>

                <div class="divider"></div>

                <div class="info-item" style="flex: 1;">
                    <div class="info-label">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
                        <span data-i18n="descLabel">Содержание</span>
                    </div>
                    <div class="content-text">
                        {{ $doc->content ?? 'Описание отсутствует' }}
                    </div>
                </div>
            </div>

            {{-- Исполнитель + Действия --}}
            <div class="info-column right">
                <div class="info-item" style="width: 100%; text-align: left;">
                    <div class="info-label" style="justify-content: flex-start;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span data-i18n="executorLabel">Создал</span>
                    </div>
                    <div class="info-value">{{ $signature->user->name ?? $doc->created_by ?? 'Система' }}</div>
                    <div class="info-sub">{{ $signature->created_at?->format('d.m.Y H:i') ?? '—' }}</div>
                </div>

                <div class="divider" style="width: 100%;"></div>

                <div class="info-item" style="width: 100%; text-align: left;">
                    <div class="info-label" style="justify-content: flex-start;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span data-i18n="receiverLabel">Получатель</span>
                    </div>
                    <div class="info-value">#{{ $doc->receiver_id ?? '—' }}</div>
                </div>

                <div style="width: 100%; margin-top: 8px;">
                    @if(!$signature->signed_at)
                    <a href="{{ route('signatures.create', ['document_id' => $signature->document_id]) }}" class="btn-sign">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span data-i18n="signBtn">Подписать + QR</span>
                    </a>
                    @else
                    <div class="verified-box">
                        <img src="{{ asset('storage/' . $signature->signature) }}" alt="✓">
                        <span class="verified-text">✓ Verified</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ПРЕДПРОСМОТР --}}
    <div class="sig-card animate-in" style="padding: 20px;">
        <div class="preview-header">
            <div class="preview-title" data-i18n="previewLabel">Предпросмотр</div>

            @if($filePath)
            <div class="preview-actions">
                <button id="fullScreenBtn" onclick="toggleFullScreen()" class="btn-icon" title="Во весь экран">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>
                <a href="{{ $fullFileUrl }}" download="{{ $doc->title ?? 'document' }}.{{ $extension }}" class="btn-download">
                    <i class="bi bi-download"></i>
                    <span data-i18n="downloadBtn">Скачать</span>
                </a>
            </div>
            @endif
        </div>

        <div id="previewBox" class="preview-container">
            @if($filePath)
            @if($isExcel)
            <div id="excel-preview" data-url="{{ $fullFileUrl }}" style="width: 100%; height: 100%;"></div>
            @elseif($extension === 'docx')
            <div id="word-preview" style="width: 100%; height: 100%; overflow-y: auto;" data-url="{{ $fullFileUrl }}"></div>
            @else
            @php
            if (in_array($extension, ['doc', 'xls', 'ppt', 'pptx'])) {
            $iframeSrc = 'https://view.officeapps.live.com/op/view.aspx?src=' . rawurlencode($fullFileUrl);
            } elseif ($extension === 'rtf') {
            $iframeSrc = $fullFileUrl;
            } else {
            $iframeSrc = $fullFileUrl . '#toolbar=0&view=FitH';
            }
            @endphp
            <iframe src="{{ $iframeSrc }}" loading="lazy" title="Document Preview"></iframe>
            @endif
            @else
            <div class="preview-empty">
                <i class="bi bi-file-earmark-text"></i>
                <p data-i18n="noFile">Файл не загружен</p>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const SIGN_VIEW_TRANSLATIONS = {
            ru: {
                backToList: 'Реестр документов',
                cardTitle: 'Карточка документа',
                statusLabel: 'Статус',
                statusSigned: 'Подписано',
                statusPending: 'Ожидание',
                idLabel: 'ID',
                numberLabel: '№ Документа',
                nameLabel: 'Название',
                descLabel: 'Содержание',
                executorLabel: 'Создал',
                receiverLabel: 'Получатель',
                signBtn: 'Подписать + QR',
                previewLabel: 'Предпросмотр',
                downloadBtn: 'Скачать',
                noFile: 'Файл не загружен',
                fullscreenTitle: 'Во весь экран',
                verifiedText: '✓ Подтверждено',
                errorNetwork: 'Сбой сети при получении файла',
                errorRender: 'Не удалось отобразить документ',
                excelSheets: 'листов',
                excelRows: 'строк',
                excelCols: 'столбцов'
            },
            tj: {
                backToList: 'Феҳристи ҳуҷҷатҳо',
                cardTitle: 'Корти ҳуҷҷат',
                statusLabel: 'Статус',
                statusSigned: 'Имзо шуд',
                statusPending: 'Интизорӣ',
                idLabel: 'ID',
                numberLabel: 'Рақами ҳуҷҷат',
                nameLabel: 'Ном',
                descLabel: 'Мазмун',
                executorLabel: 'Сохт',
                receiverLabel: 'Гиранда',
                signBtn: 'Имзо + QR',
                previewLabel: 'Пешнамоиш',
                downloadBtn: 'Боргирӣ',
                noFile: 'Файл нест',
                fullscreenTitle: 'Тамоми экран',
                verifiedText: '✓ Тасдиқ шуд',
                errorNetwork: 'Хатои шабака',
                errorRender: 'Намоиши ҳуҷҷат имконнопазир',
                excelSheets: 'варақҳо',
                excelRows: 'сатрҳо',
                excelCols: 'сутунҳо'
            },
            en: {
                backToList: 'Document Registry',
                cardTitle: 'Document Card',
                statusLabel: 'Status',
                statusSigned: 'Signed',
                statusPending: 'Pending',
                idLabel: 'ID',
                numberLabel: 'Document No.',
                nameLabel: 'Title',
                descLabel: 'Content',
                executorLabel: 'Created By',
                receiverLabel: 'Receiver',
                signBtn: 'Sign + QR',
                previewLabel: 'Preview',
                downloadBtn: 'Download',
                noFile: 'No file uploaded',
                fullscreenTitle: 'Full Screen',
                verifiedText: '✓ Verified',
                errorNetwork: 'Network error',
                errorRender: 'Failed to display document',
                excelSheets: 'sheets',
                excelRows: 'rows',
                excelCols: 'columns'
            }
        };

        function applySignViewTranslations(lang) {
            const dict = SIGN_VIEW_TRANSLATIONS[lang] || SIGN_VIEW_TRANSLATIONS.ru;

            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (dict[key] !== undefined) {
                    const icon = el.querySelector('svg, i');
                    el.textContent = dict[key];
                    if (icon) el.insertBefore(icon, el.firstChild);
                }
            });

            const verifiedText = document.querySelector('.verified-text');
            if (verifiedText && dict.verifiedText) {
                verifiedText.textContent = dict.verifiedText;
            }
        }

        const excelContainer = document.getElementById("excel-preview");
        if (excelContainer) {
            const fileUrl = excelContainer.getAttribute("data-url");

            fetch(fileUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.arrayBuffer();
                })
                .then(data => {
                    const workbook = XLSX.read(data, { type: 'array' });

                    const container = document.createElement('div');
                    container.className = 'excel-preview-container';

                    const toolbar = document.createElement('div');
                    toolbar.className = 'excel-toolbar';
                    toolbar.innerHTML = `
                        <div class="excel-file-info">
                            <i class="bi bi-file-earmark-excel-fill"></i>
                            <span>Excel Document</span>
                        </div>
                        <div style="font-size: 11px; color: var(--muted); font-family: 'JetBrains Mono', monospace;">
                            <strong style="color: #4cd982;">${workbook.SheetNames.length}</strong> ${SIGN_VIEW_TRANSLATIONS.ru.excelSheets}
                        </div>
                    `;
                    container.appendChild(toolbar);

                    if (workbook.SheetNames.length > 0) {
                        const tabsDiv = document.createElement('div');
                        tabsDiv.className = 'excel-sheet-tabs';

                        workbook.SheetNames.forEach((sheetName, index) => {
                            const tab = document.createElement('div');
                            tab.className = 'excel-sheet-tab' + (index === 0 ? ' active' : '');
                            tab.innerHTML = `<i class="bi bi-table"></i> ${sheetName}`;
                            tab.onclick = () => {
                                tabsDiv.querySelectorAll('.excel-sheet-tab').forEach(t => t.classList.remove('active'));
                                tab.classList.add('active');
                                renderSheet(workbook.Sheets[sheetName], tableWrapper);
                            };
                            tabsDiv.appendChild(tab);
                        });
                        container.appendChild(tabsDiv);

                        const tableWrapper = document.createElement('div');
                        tableWrapper.className = 'excel-table-wrapper';
                        container.appendChild(tableWrapper);

                        renderSheet(workbook.Sheets[workbook.SheetNames[0]], tableWrapper);
                    }

                    excelContainer.innerHTML = '';
                    excelContainer.appendChild(container);
                })
                .catch(err => {
                    console.error("Excel error:", err);
                    excelContainer.innerHTML = '<div style="display: flex; height: 100%; align-items: center; justify-content: center; color: #ff6363; font-weight: 600; padding: 20px; text-align: center;">' + SIGN_VIEW_TRANSLATIONS.ru.errorRender + '</div>';
                });
        }

        function renderSheet(sheet, container) {
            const jsonData = XLSX.utils.sheet_to_json(sheet, { header: 1, defval: '' });

            if (!jsonData || jsonData.length === 0) {
                container.innerHTML = '<div style="padding: 40px; text-align: center; color: var(--muted);">Empty sheet</div>';
                return;
            }

            const table = document.createElement('table');
            table.className = 'excel-table';

            const thead = document.createElement('thead');
            const headerRow = document.createElement('tr');
            if (jsonData.length > 0) {
                jsonData[0].forEach(cell => {
                    const th = document.createElement('th');
                    th.textContent = cell || '';
                    headerRow.appendChild(th);
                });
            }
            thead.appendChild(headerRow);
            table.appendChild(thead);

            const tbody = document.createElement('tbody');
            for (let i = 1; i < jsonData.length; i++) {
                const row = jsonData[i];
                if (!row) continue;
                const tr = document.createElement('tr');
                row.forEach(cell => {
                    const td = document.createElement('td');
                    td.textContent = cell || '';
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            }
            table.appendChild(tbody);

            container.innerHTML = '';
            container.appendChild(table);
        }

        const wordContainer = document.getElementById("word-preview");
        if (wordContainer) {
            const fileUrl = wordContainer.getAttribute("data-url");
            fetch(fileUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.blob();
                })
                .then(blob => {
                    docx.renderAsync(blob, wordContainer)
                        .then(() => console.log("docx rendered"))
                        .catch(e => console.error("docx error:", e));
                })
                .catch(err => {
                    console.error("Word error:", err);
                    wordContainer.innerHTML = '<div style="display: flex; height: 100%; align-items: center; justify-content: center; color: #ff6363; font-weight: 600; padding: 20px; text-align: center;">' + SIGN_VIEW_TRANSLATIONS.ru.errorRender + '</div>';
                });
        }

        document.querySelectorAll('.animate-in').forEach((el, i) => {
            setTimeout(() => {
                el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, 100 + (i * 120));
        });

        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applySignViewTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            applySignViewTranslations(e.detail?.lang || 'ru');
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applySignViewTranslations(e.newValue);
            }
        });
    });

    function toggleFullScreen() {
        const el = document.getElementById('previewBox');
        if (!el) return;
        if (!document.fullscreenElement) {
            el.requestFullscreen?.() || el.webkitRequestFullscreen?.();
        } else {
            document.exitFullscreen?.() || document.webkitExitFullscreen?.();
        }
    }
</script>

@endsection