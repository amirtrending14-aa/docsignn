<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $data['title'] ?? 'Документ' }}</title>
    <style>
        /* ============================================ */
        /* === БАЗОВЫЕ СТИЛИ (для PDF и экрана) === */
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
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #2D3748;
            background: #f5f5f5;
        }

        /* Контейнер документа (имитация листа A4) */
        .document-sheet {
            background: #ffffff;
            max-width: 800px;
            margin: 20px auto;
            padding: 60px 70px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-radius: 4px;
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
            overflow-wrap: break-word;
            line-height: 1.3;
        }

        .meta {
            font-size: 9pt;
            color: #718096;
            font-style: italic;
            line-height: 1.5;
            word-wrap: break-word;
        }

        /* ============================================ */
        /* === CONTENT === */
        /* ============================================ */
        .content {
            text-align: justify;
            line-height: 1.7;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .content p {
            margin-bottom: 12px;
            margin-top: 0;
            text-indent: 1.5em;
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
            line-height: 1.5;
        }

        /* ============================================ */
        /* === АДАПТИВНОСТЬ ДЛЯ ЭКРАНОВ === */
        /* ============================================ */

        /* Планшеты (до 992px) */
        @media screen and (max-width: 992px) {
            body {
                font-size: 10.5pt;
                background: #ffffff;
            }

            .document-sheet {
                margin: 15px auto;
                padding: 40px 50px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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

            .section-title {
                font-size: 11.5pt;
                margin-top: 18px;
                margin-bottom: 9px;
            }

            .content p {
                margin-bottom: 10px;
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
            }

            .document-sheet {
                margin: 12px auto;
                padding: 30px 25px;
                border-radius: 0;
                box-shadow: none;
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
                text-align: left;
                line-height: 1.6;
            }

            .content p {
                margin-bottom: 9px;
                text-indent: 1.2em;
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
            }

            .document-sheet {
                margin: 10px auto;
                padding: 24px 20px;
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

            .content p {
                margin-bottom: 8px;
                text-indent: 1em;
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
            }

            .document-sheet {
                margin: 8px auto;
                padding: 20px 16px;
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

            .content p {
                margin-bottom: 7px;
                text-indent: 0.8em;
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
            }

            .document-sheet {
                margin: 6px auto;
                padding: 16px 12px;
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

            .content p {
                margin-bottom: 6px;
                text-indent: 0.6em;
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
                background: #ffffff;
                font-size: 11pt;
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
                text-indent: 1.5em;
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
        }
    </style>
</head>
<body>

<div class="document-sheet">
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
        // Разбиваем на абзацы по двойному переносу
        $paragraphs = preg_split('/\n\s*\n/', $content);
        @endphp

        @foreach($paragraphs as $paragraph)
        @php
        $paragraph = trim($paragraph);
        if (empty($paragraph)) continue;

        // Определяем это заголовок раздела
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

    <div class="footer">
        Документ сгенерирован с помощью ИИ в системе DocSign | {{ date('d.m.Y H:i:s') }}
    </div>
</div>

</body>
</html>