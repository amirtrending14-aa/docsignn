<!DOCTYPE html>
<html lang="{{ $lang ?? 'ru' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document->title }}</title>
    <style>
        /* ============================================ */
        /* === БАЗОВЫЕ СТИЛИ (PDF + экран) === */
        /* ============================================ */
        * {
            box-sizing: border-box;
        }

        @page {
            margin: 2cm;
            size: A4;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12pt;
            color: #1a1a1a;
            background: #f5f5f5;
            line-height: 1.5;
        }

        /* Обёртка-лист A4 (для экрана) */
        .document-sheet {
            background: #ffffff;
            max-width: 800px;
            margin: 20px auto;
            padding: 50px 60px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-radius: 4px;
        }

        /* ============================================ */
        /* === HEADER === */
        /* ============================================ */
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }

        .doc-title {
            text-transform: uppercase;
            font-size: 18pt;
            margin-bottom: 8px;
            font-weight: bold;
            word-wrap: break-word;
            overflow-wrap: break-word;
            line-height: 1.3;
        }

        .doc-meta {
            font-size: 10pt;
            color: #666;
            word-wrap: break-word;
        }

        /* ============================================ */
        /* === CONTENT === */
        /* ============================================ */
        .content {
            margin-top: 30px;
            line-height: 1.6;
            text-align: justify;
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* ============================================ */
        /* === FOOTER / ПОДПИСИ === */
        /* ============================================ */
        .footer {
            margin-top: 80px;
            width: 100%;
            overflow: hidden;
        }

        .signature-block {
            width: 50%;
            float: left;
            font-size: 11pt;
            padding-right: 20px;
        }

        .signature-block p {
            margin: 8px 0;
            word-wrap: break-word;
        }

        .stamp-space {
            border-top: 1px solid #000;
            width: 200px;
            max-width: 100%;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 9pt;
            color: #888;
        }

        .system-footer {
            margin-top: 40px;
            font-size: 8pt;
            color: #ccc;
            text-align: center;
            word-wrap: break-word;
        }

        /* ============================================ */
        /* === АДАПТИВНОСТЬ ДЛЯ ЭКРАНОВ === */
        /* ============================================ */

        /* Планшеты (до 992px) */
        @media screen and (max-width: 992px) {
            body {
                font-size: 11.5pt;
                background: #ffffff;
            }

            .document-sheet {
                margin: 15px auto;
                padding: 40px 45px;
            }

            .header {
                margin-bottom: 30px;
                padding-bottom: 16px;
            }

            .doc-title {
                font-size: 16pt;
            }

            .doc-meta {
                font-size: 9.5pt;
            }

            .content {
                margin-top: 25px;
            }

            .footer {
                margin-top: 60px;
            }

            .signature-block {
                font-size: 10.5pt;
            }
        }

        /* Большие телефоны (до 768px) */
        @media screen and (max-width: 768px) {
            body {
                font-size: 11pt;
            }

            .document-sheet {
                margin: 12px auto;
                padding: 30px 25px;
                border-radius: 0;
                box-shadow: none;
            }

            .header {
                margin-bottom: 25px;
                padding-bottom: 14px;
            }

            .doc-title {
                font-size: 14pt;
            }

            .doc-meta {
                font-size: 9pt;
            }

            .content {
                margin-top: 20px;
                text-align: left;
                line-height: 1.55;
            }

            .footer {
                margin-top: 50px;
            }

            /* Подписи в столбик на мобильных */
            .signature-block {
                width: 100%;
                float: none;
                padding-right: 0;
                margin-bottom: 20px;
            }

            .stamp-space {
                width: 180px;
                margin-top: 30px;
            }
        }

        /* Телефоны (до 640px) */
        @media screen and (max-width: 640px) {
            body {
                font-size: 10.5pt;
            }

            .document-sheet {
                margin: 10px auto;
                padding: 24px 20px;
            }

            .header {
                margin-bottom: 20px;
                padding-bottom: 12px;
            }

            .doc-title {
                font-size: 13pt;
            }

            .doc-meta {
                font-size: 8.5pt;
            }

            .content {
                margin-top: 18px;
            }

            .footer {
                margin-top: 40px;
            }

            .signature-block {
                font-size: 10pt;
            }

            .stamp-space {
                width: 160px;
                margin-top: 25px;
                font-size: 8.5pt;
            }

            .system-footer {
                margin-top: 30px;
                font-size: 7.5pt;
            }
        }

        /* Маленькие телефоны (до 480px) */
        @media screen and (max-width: 480px) {
            body {
                font-size: 10pt;
            }

            .document-sheet {
                margin: 8px auto;
                padding: 20px 16px;
            }

            .header {
                margin-bottom: 18px;
                padding-bottom: 10px;
            }

            .doc-title {
                font-size: 12pt;
            }

            .doc-meta {
                font-size: 8pt;
            }

            .content {
                margin-top: 16px;
                line-height: 1.5;
            }

            .footer {
                margin-top: 35px;
            }

            .signature-block {
                font-size: 9.5pt;
            }

            .stamp-space {
                width: 140px;
                margin-top: 22px;
                font-size: 8pt;
            }

            .system-footer {
                margin-top: 25px;
                font-size: 7pt;
            }
        }

        /* Очень маленькие телефоны (до 380px) */
        @media screen and (max-width: 380px) {
            body {
                font-size: 9.5pt;
            }

            .document-sheet {
                margin: 6px auto;
                padding: 16px 12px;
            }

            .header {
                margin-bottom: 15px;
                padding-bottom: 8px;
            }

            .doc-title {
                font-size: 11pt;
            }

            .doc-meta {
                font-size: 7.5pt;
            }

            .content {
                margin-top: 14px;
            }

            .footer {
                margin-top: 30px;
            }

            .signature-block {
                font-size: 9pt;
            }

            .stamp-space {
                width: 130px;
                margin-top: 20px;
                font-size: 7.5pt;
            }

            .system-footer {
                margin-top: 20px;
                font-size: 7pt;
            }
        }

        /* ============================================ */
        /* === ОПТИМИЗАЦИЯ ДЛЯ ПЕЧАТИ === */
        /* ============================================ */
        @media print {
            body {
                background: #ffffff;
                font-size: 12pt;
                color: #000000;
            }

            .document-sheet {
                max-width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
                border-radius: 0;
            }

            .header {
                margin-bottom: 40px;
                padding-bottom: 20px;
                border-bottom: 1px solid #ccc;
            }

            .doc-title {
                font-size: 18pt;
                color: #000000;
            }

            .doc-meta {
                font-size: 10pt;
                color: #555555;
            }

            .content {
                margin-top: 30px;
                line-height: 1.6;
                text-align: justify;
            }

            .content p {
                orphans: 3;
                widows: 3;
            }

            .footer {
                margin-top: 100px;
                page-break-inside: avoid;
            }

            .signature-block {
                width: 50%;
                float: left;
                font-size: 11pt;
            }

            .stamp-space {
                border-top: 1px solid #000;
                width: 200px;
                margin-top: 40px;
                font-size: 9pt;
                color: #555555;
            }

            .system-footer {
                margin-top: 30px;
                font-size: 8pt;
                color: #999999;
            }
        }
    </style>
</head>
<body>

<div class="document-sheet">
    <div class="header">
        <div class="doc-title">{{ $document->title }}</div>
        <div class="doc-meta">
            {{ $translations['docLabel'] }} {{ $document->number ?? $document->id }} |
            {{ $translations['dateLabel'] }} {{ date('d.m.Y') }}
        </div>
    </div>

    <div class="content">
        {!! nl2br(e($document->content)) !!}
    </div>

    <div class="footer">
        <div class="signature-block">
            <p><strong>{{ $translations['senderLabel'] }}</strong> {{ $document->user->name ?? 'Администратор' }}</p>
            <p><strong>{{ $translations['receiverLabel'] }}</strong> {{ $document->receiver->name ?? 'N/A' }}</p>
            <div class="stamp-space">{{ $translations['stampLabel'] }}</div>
        </div>

        <div style="clear: both;"></div>
    </div>

    <div class="system-footer">
        {{ $translations['systemFooter'] }}
    </div>
</div>

</body>
</html>