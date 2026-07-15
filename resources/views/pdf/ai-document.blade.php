<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $data['title'] ?? 'Документ' }}</title>
    <style>
        /* ============================================ */
        /* === СТИЛИ ДЛЯ ПЕЧАТИ (A4) === */
        /* ============================================ */
        @page {
            margin: 2cm;
            size: A4;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #2D3748;
            background: #ffffff;
            padding: 0;
        }

        /* ============================================ */
        /* === HEADER === */
        /* ============================================ */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4F8CFF;
            padding-bottom: 15px;
        }

        .title {
            font-size: 20pt;
            font-weight: bold;
            color: #1A365D;
            margin-bottom: 10px;
            word-wrap: break-word;
            line-height: 1.3;
        }

        .meta {
            font-size: 9pt;
            color: #718096;
            font-style: italic;
            line-height: 1.5;
        }

        /* ============================================ */
        /* === CONTENT === */
        /* ============================================ */
        .content {
            text-align: justify;
            line-height: 1.7;
            white-space: pre-line;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .content p {
            margin-bottom: 12px;
            margin-top: 0;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #2D3748;
            margin-top: 20px;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        /* ============================================ */
        /* === FOOTER === */
        /* ============================================ */
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #CBD5E0;
            text-align: center;
            font-size: 8pt;
            color: #A0AEC0;
            font-style: italic;
            word-wrap: break-word;
        }

        /* ============================================ */
        /* === ПОЛНАЯ АДАПТИВНОСТЬ ДЛЯ ЭКРАНОВ === */
        /* ============================================ */

        /* Большие экраны (десктопы) - стили по умолчанию */

        /* Планшеты (до 992px) */
        @media screen and (max-width: 992px) {
            body {
                font-size: 10.5pt;
                padding: 15px;
            }

            .header {
                margin-bottom: 25px;
                padding-bottom: 12px;
            }

            .title {
                font-size: 18pt;
            }

            .meta {
                font-size: 8.5pt;
            }

            .content {
                line-height: 1.65;
            }

            .content p {
                margin-bottom: 10px;
            }

            .section-title {
                font-size: 11.5pt;
                margin-top: 18px;
                margin-bottom: 9px;
            }

            .footer {
                margin-top: 35px;
                padding-top: 12px;
                font-size: 7.5pt;
            }
        }

        /* Большие телефоны (до 768px) */
        @media screen and (max-width: 768px) {
            body {
                font-size: 10pt;
                padding: 12px;
            }

            .header {
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom-width: 2px;
            }

            .title {
                font-size: 16pt;
                margin-bottom: 8px;
            }

            .meta {
                font-size: 8pt;
            }

            .content {
                line-height: 1.6;
                text-align: left;
            }

            .content p {
                margin-bottom: 9px;
            }

            .section-title {
                font-size: 11pt;
                margin-top: 16px;
                margin-bottom: 8px;
            }

            .footer {
                margin-top: 30px;
                padding-top: 10px;
                font-size: 7pt;
            }
        }

        /* Телефоны (до 640px) */
        @media screen and (max-width: 640px) {
            body {
                font-size: 9.5pt;
                padding: 10px;
            }

            .header {
                margin-bottom: 18px;
                padding-bottom: 9px;
            }

            .title {
                font-size: 14pt;
                margin-bottom: 7px;
            }

            .meta {
                font-size: 7.5pt;
                line-height: 1.4;
            }

            .content {
                line-height: 1.55;
            }

            .content p {
                margin-bottom: 8px;
            }

            .section-title {
                font-size: 10.5pt;
                margin-top: 14px;
                margin-bottom: 7px;
            }

            .footer {
                margin-top: 25px;
                padding-top: 9px;
                font-size: 7pt;
            }
        }

        /* Маленькие телефоны (до 480px) */
        @media screen and (max-width: 480px) {
            body {
                font-size: 9pt;
                padding: 8px;
            }

            .header {
                margin-bottom: 15px;
                padding-bottom: 8px;
            }

            .title {
                font-size: 13pt;
                margin-bottom: 6px;
            }

            .meta {
                font-size: 7pt;
            }

            .content {
                line-height: 1.5;
            }

            .content p {
                margin-bottom: 7px;
            }

            .section-title {
                font-size: 10pt;
                margin-top: 12px;
                margin-bottom: 6px;
            }

            .footer {
                margin-top: 20px;
                padding-top: 8px;
                font-size: 6.5pt;
            }
        }

        /* Очень маленькие телефоны (до 380px) */
        @media screen and (max-width: 380px) {
            body {
                font-size: 8.5pt;
                padding: 6px;
            }

            .header {
                margin-bottom: 12px;
                padding-bottom: 7px;
            }

            .title {
                font-size: 12pt;
                margin-bottom: 5px;
            }

            .meta {
                font-size: 6.5pt;
            }

            .content {
                line-height: 1.45;
            }

            .content p {
                margin-bottom: 6px;
            }

            .section-title {
                font-size: 9.5pt;
                margin-top: 10px;
                margin-bottom: 5px;
            }

            .footer {
                margin-top: 18px;
                padding-top: 7px;
                font-size: 6pt;
            }
        }

        /* ============================================ */
        /* === ОПТИМИЗАЦИЯ ДЛЯ ПЕЧАТИ === */
        /* ============================================ */
        @media print {
            body {
                font-size: 11pt;
                padding: 0;
                color: #000000;
            }

            .header {
                margin-bottom: 30px;
                padding-bottom: 15px;
                border-bottom: 3px solid #4F8CFF;
            }

            .title {
                font-size: 20pt;
                color: #000000;
            }

            .meta {
                font-size: 9pt;
                color: #555555;
            }

            .content {
                text-align: justify;
                line-height: 1.7;
            }

            .content p {
                margin-bottom: 12px;
                orphans: 3;
                widows: 3;
            }

            .section-title {
                font-size: 12pt;
                color: #000000;
                page-break-after: avoid;
                margin-top: 20px;
                margin-bottom: 10px;
            }

            .footer {
                margin-top: 40px;
                padding-top: 15px;
                border-top: 1px solid #999999;
                font-size: 8pt;
                color: #666666;
                page-break-inside: avoid;
            }

            /* Предотвращаем разрывы страниц внутри элементов */
            h1, h2, h3, h4, h5, h6 {
                page-break-after: avoid;
            }

            table, figure, img {
                page-break-inside: avoid;
            }

            /* Убираем ссылки из текста при печати */
            a {
                color: #000000;
                text-decoration: none;
            }
        }
    </style>
</head>
<body>
<div class="header">
    <div class="title">{{ $data['title'] ?? 'Документ' }}</div>
    <div class="meta">
        Номер: {{ $data['number'] ?? 'Б/Н' }} |
        Тип: {{ $data['type'] ?? 'Документ' }} |
        Дата: {{ date('d.m.Y') }}
    </div>
</div>

<div class="content">
    @php
    $content = $data['content'] ?? '';
    $paragraphs = preg_split('/\n\s*\n/', $content);
    @endphp

    @foreach($paragraphs as $paragraph)
    @php
    $paragraph = trim($paragraph);
    if (empty($paragraph)) continue;

    $isSectionTitle = preg_match('/^(Глава|Раздел|Пункт|Статья|Chapter|Section|Article)\s+\d/i', $paragraph) ||
    preg_match('/^[IVX]+\.\s+/i', $paragraph) ||
    preg_match('/^\d+\.\s+[А-ЯA-Z]/u', $paragraph);
    @endphp

    @if($isSectionTitle)
    <div class="section-title">{{ $paragraph }}</div>
    @else
    <p>{{ $paragraph }}</p>
    @endif
    @endforeach
</div>

@php
$source = isset($data['_source']) && $data['_source'] === 'gemini' ? 'ИИ Gemini' : 'Система DocSign';
@endphp

<div class="footer">
    Документ сгенерирован ({{ $source }}) | {{ date('d.m.Y H:i:s') }}
</div>
</body>
</html>