@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Подключаем необходимые библиотеки -->
<script src="https://unpkg.com/jszip/dist/jszip.min.js"></script>
<script src="https://unpkg.com/docx-preview/dist/docx-preview.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

@php
$doc = $signature->document ?? null;
$filePath = $doc->file_path ?? '';

$extension = '';
$fileSize = 0;
$streamUrl = '';
$downloadUrl = '';

if ($filePath && Storage::disk('public')->exists($filePath) && $doc) {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $fileSize = Storage::disk('public')->size($filePath);
    $streamUrl = route('documents.stream', $doc->id);
    $downloadUrl = route('documents.download', $doc->id);
}

$isWord = in_array($extension, ['doc', 'docx']);
$isPdf = $extension === 'pdf';
$isExcel = in_array($extension, ['xls', 'xlsx']);

$formattedSize = $fileSize > 1048576
    ? round($fileSize / 1048576, 2) . ' МБ'
    : ($fileSize > 1024 ? round($fileSize / 1024, 1) . ' КБ' : $fileSize . ' Б');
@endphp

<style>
    /* ============================================================ */
    /* ===== VIEW SIGNATURE PAGE (Tabler Style) ===== */
    /* ============================================================ */
    .view-sig-page {
        min-height: 100vh;
        padding: 24px 0 40px;
        color: var(--text);
        font-family: var(--font-sans);
    }

    .view-sig-wrap {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 24px;
    }

    /* Back Link */
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--muted);
        text-decoration: none;
        transition: all 0.25s ease;
        margin-bottom: 20px;
    }
    .back-link:hover {
        color: rgb(var(--glow));
        transform: translateX(-3px);
    }
    .back-link i { font-size: 16px; }

    /* Header */
    .page-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 28px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--line);
    }
    .page-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: -0.3px;
        margin: 0;
    }
    .page-title .title-bar {
        width: 4px;
        height: 24px;
        background: rgb(var(--glow));
        border-radius: 2px;
        box-shadow: 0 0 0 3px rgba(var(--glow), 0.15), 0 0 10px rgba(var(--glow), 0.4);
        flex-shrink: 0;
    }

    .title-badges {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .format-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: var(--radius);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-family: var(--font-mono);
        border: 1px solid;
    }
    .format-badge.pdf { background: rgba(239, 68, 68, 0.10); color: #dc2626; border-color: rgba(239, 68, 68, 0.25); }
    html[data-theme="dark"] .format-badge.pdf { color: #f87171; }
    
    .format-badge.word { background: rgba(59, 130, 246, 0.10); color: #2563eb; border-color: rgba(59, 130, 246, 0.25); }
    html[data-theme="dark"] .format-badge.word { color: #60a5fa; }
    
    .format-badge.excel { background: rgba(16, 185, 129, 0.10); color: #059669; border-color: rgba(16, 185, 129, 0.25); }
    html[data-theme="dark"] .format-badge.excel { color: #34d399; }
    
    .format-badge.rtf { background: rgba(139, 92, 246, 0.10); color: #7c3aed; border-color: rgba(139, 92, 246, 0.25); }
    html[data-theme="dark"] .format-badge.rtf { color: #a78bfa; }

    .file-size {
        font-size: 12px;
        color: var(--text);
        font-weight: 600;
        font-family: var(--font-mono);
        padding: 6px 12px;
        background: var(--bg-2);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        white-space: nowrap;
    }

    /* Cards */
    .sig-card {
        background: var(--bg-1);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-card);
        transition: all 0.3s ease;
    }
    .sig-card:hover {
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }
    @media (min-width: 992px) {
        .info-grid { grid-template-columns: 1fr 2fr 1fr; }
    }

    .info-column {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .info-column.right {
        align-items: flex-end;
        text-align: right;
    }
    @media (max-width: 768px) {
        .info-column.right { align-items: flex-start; text-align: left; }
    }

    .info-item {
        padding: 16px;
        background: var(--bg-2);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        transition: all 0.2s ease;
    }
    .info-item:hover {
        border-color: rgba(var(--glow), 0.3);
        box-shadow: 0 4px 12px rgba(var(--glow), 0.08);
    }

    .info-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--muted);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .info-label i { font-size: 14px; color: rgb(var(--glow)); flex-shrink: 0; }

    .info-value {
        font-size: 15px;
        font-weight: 700;
        color: var(--text);
        letter-spacing: -0.2px;
        word-break: break-word;
        line-height: 1.4;
    }
    .info-value.mono {
        font-family: var(--font-mono);
        font-size: 18px;
        color: rgb(var(--glow));
    }
    .info-value.large { font-size: 17px; }

    .info-sub {
        font-size: 11px;
        color: var(--muted);
        font-weight: 600;
        margin-top: 4px;
    }

    .divider {
        height: 1px;
        background: var(--line);
        margin: 4px 0;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: var(--radius);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }
    .status-badge.signed {
        background: rgba(16, 185, 129, 0.10);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }
    html[data-theme="dark"] .status-badge.signed { color: #34d399; }
    .status-badge.signed .dot {
        width: 6px; height: 6px; border-radius: 50%; background: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        animation: pulse 2s infinite;
    }

    .status-badge.pending {
        background: rgba(245, 158, 11, 0.10);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.25);
    }
    html[data-theme="dark"] .status-badge.pending { color: #fbbf24; }
    .status-badge.pending .dot {
        width: 6px; height: 6px; border-radius: 50%; background: #f59e0b;
        box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.2); }
    }

    .content-text {
        font-size: 13px;
        font-weight: 500;
        color: var(--text);
        line-height: 1.6;
        max-height: 100px;
        overflow-y: auto;
        padding-right: 8px;
    }
    .content-text::-webkit-scrollbar { width: 4px; }
    .content-text::-webkit-scrollbar-thumb { background: rgba(var(--glow), 0.3); border-radius: 2px; }

    /* Buttons */
    .btn-sign {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 12px 20px;
        background: rgb(var(--glow));
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: var(--radius);
        text-decoration: none;
        transition: all 0.25s ease;
        border: 1px solid rgba(var(--glow), 0.8);
        box-shadow: 0 4px 12px rgba(var(--glow), 0.3);
    }
    .btn-sign:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(var(--glow), 0.4);
        filter: brightness(1.1);
    }
    .btn-sign i { font-size: 16px; }

    .verified-box {
        background: rgba(16, 185, 129, 0.08);
        border: 1px solid rgba(16, 185, 129, 0.25);
        border-radius: var(--radius);
        padding: 14px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        width: 100%;
    }
    .verified-box img { max-height: 40px; object-fit: contain; }
    .verified-text {
        font-size: 10px;
        font-weight: 800;
        color: #059669;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    html[data-theme="dark"] .verified-text { color: #34d399; }

    /* Preview Section */
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
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }
    .preview-title::before {
        content: "";
        width: 8px; height: 8px;
        border-radius: 50%;
        background: rgb(var(--glow));
        box-shadow: 0 0 0 3px rgba(var(--glow), 0.15), 0 0 8px rgba(var(--glow), 0.5);
        flex-shrink: 0;
    }

    .preview-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn-icon {
        width: 36px; height: 36px;
        border-radius: var(--radius);
        background: var(--bg-2);
        border: 1px solid var(--line);
        display: grid;
        place-items: center;
        cursor: pointer;
        color: var(--muted);
        transition: all 0.25s ease;
    }
    .btn-icon:hover {
        color: rgb(var(--glow));
        border-color: rgba(var(--glow), 0.4);
        background: rgba(var(--glow), 0.06);
    }
    .btn-icon i { font-size: 16px; }

    .btn-download {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: var(--bg-2);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        color: var(--text);
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.25s ease;
        white-space: nowrap;
    }
    .btn-download:hover {
        border-color: rgba(var(--glow), 0.4);
        color: rgb(var(--glow));
        background: rgba(var(--glow), 0.06);
    }
    .btn-download i { font-size: 14px; }

    .preview-container {
        height: 650px;
        border-radius: var(--radius);
        overflow: hidden;
        border: 1px solid var(--line);
        background: var(--bg-2);
        position: relative;
    }
    .preview-container iframe {
        width: 100%; height: 100%; border: 0;
    }

    /* Excel Preview Styles (Adaptive) */
    .excel-preview-container {
        width: 100%; height: 100%;
        display: flex; flex-direction: column;
        background: var(--bg-1);
        overflow: hidden;
    }
    .excel-toolbar {
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px; padding: 12px 16px;
        background: var(--bg-2);
        border-bottom: 1px solid var(--line);
        flex-wrap: wrap;
    }
    .excel-file-info {
        display: flex; align-items: center; gap: 10px;
        color: var(--text); font-size: 13px; font-weight: 700;
    }
    .excel-file-info i { font-size: 18px; color: #10b981; }
    
    .excel-sheet-tabs {
        display: flex; gap: 4px; padding: 8px 12px;
        background: var(--bg-2);
        border-bottom: 1px solid var(--line);
        overflow-x: auto;
    }
    .excel-sheet-tab {
        padding: 6px 14px;
        background: var(--bg-1);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        color: var(--muted);
        font-size: 11px; font-weight: 700;
        cursor: pointer; transition: all 0.2s ease;
        white-space: nowrap;
    }
    .excel-sheet-tab:hover {
        border-color: rgba(16, 185, 129, 0.4);
        color: var(--text);
    }
    .excel-sheet-tab.active {
        background: rgba(16, 185, 129, 0.10);
        border-color: rgba(16, 185, 129, 0.4);
        color: #059669;
    }
    html[data-theme="dark"] .excel-sheet-tab.active { color: #34d399; }

    .excel-table-wrapper {
        flex: 1; overflow: auto; background: var(--bg-1);
    }
    .excel-table {
        border-collapse: collapse; width: max-content; min-width: 100%;
        font-size: 12px; font-family: var(--font-sans);
        background: var(--bg-1);
    }
    .excel-table th {
        background: var(--bg-2);
        color: var(--text);
        font-weight: 700;
        padding: 10px 14px;
        text-align: left;
        border: 1px solid var(--line);
        position: sticky; top: 0; z-index: 10;
    }
    .excel-table td {
        padding: 8px 14px;
        border: 1px solid var(--line);
        color: var(--text);
    }
    .excel-table tbody tr:nth-child(even) { background: rgba(var(--glow), 0.03); }
    .excel-table tbody tr:hover { background: rgba(var(--glow), 0.06); }

    .preview-empty {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        height: 100%; color: var(--muted); gap: 12px;
    }
    .preview-empty i { font-size: 48px; opacity: 0.3; }
    .preview-empty p { font-size: 13px; font-weight: 600; margin: 0; }

    /* Animation */
    .animate-in {
        opacity: 0; transform: translateY(12px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .view-sig-wrap { padding: 0 16px; }
        .page-title-row { flex-direction: column; align-items: flex-start; gap: 12px; }
        .page-title { font-size: 20px; }
        .sig-card { padding: 18px; }
        .info-grid { gap: 16px; }
        .info-item { padding: 14px; }
        .preview-container { height: 500px; }
        .preview-actions { width: 100%; }
        .btn-download { flex: 1; justify-content: center; }
    }
    @media (max-width: 480px) {
        .view-sig-wrap { padding: 0 12px; }
        .page-title { font-size: 18px; }
        .format-badge { font-size: 10px; padding: 5px 10px; }
        .info-value.mono { font-size: 16px; }
        .preview-container { height: 400px; }
    }
</style>

<div class="view-sig-page">
    <div class="view-sig-wrap">

        {{-- Кнопка назад --}}
        <a href="{{ route('signatures.index') }}" class="back-link animate-in">
            <i class="bi bi-arrow-left"></i>
            <span data-i18n="backToList">Реестр документов</span>
        </a>

        {{-- Заголовок --}}
        <div class="page-title-row animate-in">
            <h1 class="page-title">
                <span class="title-bar"></span>
                <span data-i18n="cardTitle">Карточка документа</span>
            </h1>
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
                            <i class="bi bi-check-circle-fill"></i>
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
                            <i class="bi bi-hash"></i>
                            <span data-i18n="idLabel">ID</span>
                        </div>
                        <div class="info-value mono">#{{ str_pad($signature->id, 6, '0', STR_PAD_LEFT) }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-file-earmark-text"></i>
                            <span data-i18n="numberLabel">№ Документа</span>
                        </div>
                        <div class="info-value">{{ $doc->number ?? '—' }}</div>
                    </div>
                </div>

                {{-- Название + Содержание --}}
                <div class="info-column">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-card-heading"></i>
                            <span data-i18n="nameLabel">Название</span>
                        </div>
                        <div class="info-value large">{{ $doc->title ?? '—' }}</div>
                    </div>

                    <div class="divider"></div>

                    <div class="info-item" style="flex: 1;">
                        <div class="info-label">
                            <i class="bi bi-text-paragraph"></i>
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
                            <i class="bi bi-person-fill"></i>
                            <span data-i18n="executorLabel">Создал</span>
                        </div>
                        <div class="info-value">{{ $signature->user->name ?? $doc->created_by ?? 'Система' }}</div>
                        <div class="info-sub">{{ $signature->created_at?->format('d.m.Y H:i') ?? '—' }}</div>
                    </div>

                    <div class="divider" style="width: 100%;"></div>

                    <div class="info-item" style="width: 100%; text-align: left;">
                        <div class="info-label" style="justify-content: flex-start;">
                            <i class="bi bi-envelope-fill"></i>
                            <span data-i18n="receiverLabel">Получатель</span>
                        </div>
                        <div class="info-value">#{{ $doc->receiver_id ?? '—' }}</div>
                    </div>

                    <div style="width: 100%; margin-top: 8px;">
                        @if(!$signature->signed_at)
                        <a href="{{ route('signatures.create', ['document_id' => $signature->document_id]) }}" class="btn-sign">
                            <i class="bi bi-pen-fill"></i>
                            <span data-i18n="signBtn">Подписать + QR</span>
                        </a>
                        @else
                        <div class="verified-box">
                            <img src="{{ asset('storage/' . $signature->signature) }}" alt="✓">
                            <span class="verified-text" data-i18n="verifiedText">✓ Подтверждено</span>
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
                    <a href="{{ $streamUrl }}" target="_blank" class="btn-download" style="border-color: rgba(var(--glow), 0.4); color: rgb(var(--glow));">
                        <i class="bi bi-eye-fill"></i>
                        <span data-i18n="viewBtn">Смотреть</span>
                    </a>
                    
                    <button id="fullScreenBtn" onclick="toggleFullScreen()" class="btn-icon" data-i18n-title="fullscreenTitle" title="Во весь экран">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                    
                    <a href="{{ $downloadUrl }}" download="{{ $doc->title ?? 'document' }}.{{ $extension }}" class="btn-download">
                        <i class="bi bi-download"></i>
                        <span data-i18n="downloadBtn">Скачать</span>
                    </a>
                </div>
                @endif
            </div>

            <div id="previewBox" class="preview-container">
                @if($filePath)
                    @if($isExcel)
                        <div id="excel-preview" data-url="{{ $streamUrl }}" style="width: 100%; height: 100%;"></div>
                    @elseif($extension === 'docx')
                        <div id="word-preview" style="width: 100%; height: 100%; overflow-y: auto; padding: 20px; background: #fff;" data-url="{{ $streamUrl }}"></div>
                    @elseif($isPdf)
                        <iframe src="{{ $streamUrl }}#toolbar=0&view=FitH" loading="lazy" title="Document Preview"></iframe>
                    @elseif($extension === 'rtf')
                        <iframe src="{{ $streamUrl }}" loading="lazy" title="Document Preview"></iframe>
                    @else
                        <div class="preview-empty">
                            <i class="bi bi-file-earmark-{{ $isWord ? 'word' : 'excel' }}"></i>
                            <p data-i18n="officePreviewHint">Предпросмотр Office файлов доступен по кнопке</p>
                            <a href="{{ $streamUrl }}" target="_blank" class="btn-download" style="margin-top: 12px; border-color: rgba(var(--glow), 0.4); color: rgb(var(--glow));">
                                <i class="bi bi-eye-fill"></i>
                                <span data-i18n="viewBtn">Смотреть в новой вкладке</span>
                            </a>
                        </div>
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
                excelCols: 'столбцов',
                viewBtn: 'Смотреть',
                officePreviewHint: 'Предпросмотр Office файлов доступен по кнопке',
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
                excelCols: 'сутунҳо',
                viewBtn: 'Дидан',
                officePreviewHint: 'Пешнамоиши Office файлҳо тавассути тугма дастрас аст',
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
                excelCols: 'columns',
                viewBtn: 'View',
                officePreviewHint: 'Office files preview available via button',
            }
        };

        function applySignViewTranslations(lang) {
            const dict = SIGN_VIEW_TRANSLATIONS[lang] || SIGN_VIEW_TRANSLATIONS.ru;

            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (dict[key] !== undefined) {
                    const icon = el.querySelector('i');
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
            fetch(fileUrl, { credentials: 'include' })
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.arrayBuffer();
                })
                .then(data => {
                    const workbook = XLSX.read(data, { type: 'array' });
                    const lang = localStorage.getItem('docsign_lang') || 'ru';
                    const dict = SIGN_VIEW_TRANSLATIONS[lang] || SIGN_VIEW_TRANSLATIONS.ru;

                    const container = document.createElement('div');
                    container.className = 'excel-preview-container';

                    const toolbar = document.createElement('div');
                    toolbar.className = 'excel-toolbar';
                    toolbar.innerHTML = `
                        <div class="excel-file-info">
                            <i class="bi bi-file-earmark-excel-fill"></i>
                            <span>Excel Document</span>
                        </div>
                        <div style="font-size: 11px; color: var(--muted); font-family: var(--font-mono);">
                            <strong style="color: #10b981;">${workbook.SheetNames.length}</strong> ${dict.excelSheets}
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
                    const lang = localStorage.getItem('docsign_lang') || 'ru';
                    const dict = SIGN_VIEW_TRANSLATIONS[lang] || SIGN_VIEW_TRANSLATIONS.ru;
                    excelContainer.innerHTML = `
                        <div style="display: flex; height: 100%; align-items: center; justify-content: center; color: #ef4444; font-weight: 600; padding: 20px; text-align: center; flex-direction: column; gap: 12px;">
                            <i class="bi bi-exclamation-triangle-fill" style="font-size: 32px;"></i>
                            <div>${dict.errorRender}</div>
                            <div style="font-size: 11px; color: var(--muted); font-weight: 400;">${err.message || ''}</div>
                            <a href="${excelContainer.getAttribute('data-url')}" target="_blank" style="padding: 8px 16px; background: rgba(var(--glow), 0.1); border: 1px solid rgba(var(--glow), 0.3); border-radius: var(--radius); color: rgb(var(--glow)); text-decoration: none; font-size: 11px; font-weight: 700;">
                                <i class="bi bi-eye-fill"></i> ${dict.viewBtn}
                            </a>
                        </div>`;
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
            fetch(fileUrl, { credentials: 'include' })
                .then(async (response) => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('text/html')) {
                        throw new Error(`Server returned HTML instead of DOCX`);
                    }
                    return response.arrayBuffer();
                })
                .then(data => {
                    return docx.renderAsync(data, wordContainer, null, {
                        className: 'docx',
                        inWrapper: true,
                        ignoreWidth: false,
                        ignoreHeight: false,
                        ignoreFonts: false,
                        breakPages: true,
                        debug: false,
                    });
                })
                .catch(err => {
                    console.error("Word error:", err);
                    const lang = localStorage.getItem('docsign_lang') || 'ru';
                    const dict = SIGN_VIEW_TRANSLATIONS[lang] || SIGN_VIEW_TRANSLATIONS.ru;
                    wordContainer.innerHTML = `<div style="display: flex; height: 100%; align-items: center; justify-content: center; color: #ef4444; font-weight: 600; padding: 20px; text-align: center; flex-direction: column; gap: 10px;"><i class="bi bi-exclamation-triangle-fill" style="font-size: 24px;"></i>${dict.errorRender}: ${err.message}</div>`;
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