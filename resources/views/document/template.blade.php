<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Документ' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
            padding: 20mm 15mm;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #2c3e50;
        }

        .header h1 {
            font-size: 18pt;
            color: #2c3e50;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .header .subtitle {
            font-size: 10pt;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .meta-info {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            font-size: 10pt;
        }

        .meta-row {
            display: table-row;
        }

        .meta-label {
            display: table-cell;
            width: 30%;
            padding: 5px 10px 5px 0;
            font-weight: bold;
            color: #555;
        }

        .meta-value {
            display: table-cell;
            padding: 5px 0;
        }

        .document-number {
            font-size: 12pt;
            font-weight: bold;
            color: #2c3e50;
        }

        .content {
            text-align: justify;
            margin-bottom: 30px;
        }

        .content p {
            margin-bottom: 12px;
            text-indent: 20px;
        }

        .content h2 {
            font-size: 13pt;
            color: #2c3e50;
            margin: 20px 0 10px 0;
            font-weight: bold;
        }

        .content h3 {
            font-size: 12pt;
            color: #34495e;
            margin: 15px 0 8px 0;
            font-weight: bold;
        }

        .content ol, .content ul {
            margin: 10px 0 10px 30px;
        }

        .content li {
            margin-bottom: 8px;
        }

        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signature-title {
            font-size: 12pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        .signatures {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 20px 0;
        }

        .signature-block {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .signature-party {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .signature-details {
            font-size: 9pt;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .signature-line {
            margin-top: 40px;
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 9pt;
            color: #666;
        }

        .signature-line-content {
            display: table;
            width: 100%;
        }

        .signature-line-left,
        .signature-line-right {
            display: table-cell;
            width: 50%;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #999;
            text-align: center;
        }

        .page-break {
            page-break-after: always;
        }

        .highlight {
            background-color: #f8f9fa;
            padding: 10px;
            border-left: 3px solid #3498db;
            margin: 15px 0;
        }

        .table-wrapper {
            margin: 15px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        table th {
            background-color: #34495e;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
        }

        table td {
            padding: 8px 10px;
            border: 1px solid #ddd;
            font-size: 10pt;
        }

        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        /* ===== АДАПТИВНОСТЬ ДЛЯ ЭКРАНА (не влияет на печать) ===== */

        @media screen {
            body {
                max-width: 100%;
                overflow-x: hidden;
            }

            /* Обёртка для таблиц с горизонтальным скроллом */
            table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            /* Планшеты и маленькие ноутбуки (до 768px) */
            @media (max-width: 768px) {
                body {
                    padding: 15mm 10mm;
                    font-size: 10.5pt;
                }

                .header {
                    margin-bottom: 24px;
                    padding-bottom: 16px;
                }

                .header h1 {
                    font-size: 16pt;
                    margin-bottom: 8px;
                }

                .header .subtitle {
                    font-size: 9.5pt;
                }

                .document-number {
                    font-size: 11pt;
                }

                .meta-info {
                    margin-bottom: 20px;
                    font-size: 9.5pt;
                }

                .meta-label {
                    width: 35%;
                    padding: 4px 8px 4px 0;
                }

                .content {
                    margin-bottom: 24px;
                }

                .content p {
                    margin-bottom: 10px;
                    text-indent: 15px;
                }

                .content h2 {
                    font-size: 12pt;
                    margin: 18px 0 8px 0;
                }

                .content h3 {
                    font-size: 11pt;
                    margin: 14px 0 7px 0;
                }

                .content ol, .content ul {
                    margin: 8px 0 8px 25px;
                }

                .content li {
                    margin-bottom: 6px;
                }

                .signature-section {
                    margin-top: 40px;
                }

                .signature-title {
                    font-size: 11pt;
                    margin-bottom: 16px;
                }

                .signatures {
                    display: block;
                    border-spacing: 0;
                }

                .signature-block {
                    display: block;
                    width: 100%;
                    margin-bottom: 30px;
                    padding-bottom: 20px;
                    border-bottom: 1px dashed #ddd;
                }

                .signature-block:last-child {
                    margin-bottom: 0;
                    padding-bottom: 0;
                    border-bottom: none;
                }

                .signature-party {
                    font-size: 10.5pt;
                    margin-bottom: 12px;
                }

                .signature-details {
                    font-size: 8.5pt;
                    margin-bottom: 16px;
                }

                .signature-line {
                    margin-top: 30px;
                }

                .footer {
                    margin-top: 30px;
                    padding-top: 12px;
                    font-size: 7.5pt;
                }

                .highlight {
                    padding: 8px;
                    margin: 12px 0;
                }

                table th {
                    padding: 8px;
                    font-size: 9.5pt;
                }

                table td {
                    padding: 6px 8px;
                    font-size: 9.5pt;
                }
            }

            /* Большие телефоны (до 576px) */
            @media (max-width: 576px) {
                body {
                    padding: 12mm 8mm;
                    font-size: 10pt;
                }

                .header {
                    margin-bottom: 20px;
                    padding-bottom: 14px;
                    border-bottom-width: 2px;
                }

                .header h1 {
                    font-size: 14pt;
                    margin-bottom: 7px;
                    letter-spacing: 0.3px;
                }

                .header .subtitle {
                    font-size: 9pt;
                }

                .document-number {
                    font-size: 10.5pt;
                }

                .meta-info {
                    margin-bottom: 18px;
                    font-size: 9pt;
                }

                .meta-label {
                    width: 40%;
                    padding: 3px 6px 3px 0;
                }

                .meta-value {
                    padding: 3px 0;
                }

                .content {
                    margin-bottom: 20px;
                }

                .content p {
                    margin-bottom: 9px;
                    text-indent: 12px;
                }

                .content h2 {
                    font-size: 11.5pt;
                    margin: 16px 0 7px 0;
                }

                .content h3 {
                    font-size: 10.5pt;
                    margin: 12px 0 6px 0;
                }

                .content ol, .content ul {
                    margin: 7px 0 7px 22px;
                }

                .content li {
                    margin-bottom: 5px;
                }

                .signature-section {
                    margin-top: 30px;
                }

                .signature-title {
                    font-size: 10.5pt;
                    margin-bottom: 14px;
                }

                .signature-block {
                    margin-bottom: 24px;
                    padding-bottom: 16px;
                }

                .signature-party {
                    font-size: 10pt;
                    margin-bottom: 10px;
                }

                .signature-details {
                    font-size: 8pt;
                    margin-bottom: 14px;
                    line-height: 1.35;
                }

                .signature-line {
                    margin-top: 25px;
                    font-size: 8pt;
                }

                .signature-line-content {
                    display: block;
                }

                .signature-line-left,
                .signature-line-right {
                    display: block;
                    width: 100%;
                    margin-bottom: 8px;
                }

                .footer {
                    margin-top: 24px;
                    padding-top: 10px;
                    font-size: 7pt;
                }

                .highlight {
                    padding: 7px;
                    margin: 10px 0;
                    border-left-width: 2px;
                }

                table th {
                    padding: 6px;
                    font-size: 9pt;
                }

                table td {
                    padding: 5px 6px;
                    font-size: 9pt;
                }
            }

            /* Телефоны (до 480px) */
            @media (max-width: 480px) {
                body {
                    padding: 10mm 6mm;
                    font-size: 9.5pt;
                }

                .header {
                    margin-bottom: 18px;
                    padding-bottom: 12px;
                }

                .header h1 {
                    font-size: 13pt;
                    margin-bottom: 6px;
                }

                .header .subtitle {
                    font-size: 8.5pt;
                    margin-top: 4px;
                }

                .document-number {
                    font-size: 10pt;
                }

                .meta-info {
                    display: block;
                    margin-bottom: 16px;
                    font-size: 8.5pt;
                }

                .meta-row {
                    display: flex;
                    flex-wrap: wrap;
                    margin-bottom: 6px;
                    padding-bottom: 6px;
                    border-bottom: 1px dotted #eee;
                }

                .meta-row:last-child {
                    border-bottom: none;
                }

                .meta-label {
                    display: block;
                    width: 100%;
                    padding: 0 0 2px 0;
                    font-size: 8pt;
                }

                .meta-value {
                    display: block;
                    width: 100%;
                    padding: 0;
                }

                .content {
                    margin-bottom: 18px;
                }

                .content p {
                    margin-bottom: 8px;
                    text-indent: 10px;
                }

                .content h2 {
                    font-size: 11pt;
                    margin: 14px 0 6px 0;
                }

                .content h3 {
                    font-size: 10pt;
                    margin: 11px 0 5px 0;
                }

                .content ol, .content ul {
                    margin: 6px 0 6px 20px;
                }

                .content li {
                    margin-bottom: 4px;
                }

                .signature-section {
                    margin-top: 25px;
                }

                .signature-title {
                    font-size: 10pt;
                    margin-bottom: 12px;
                }

                .signature-block {
                    margin-bottom: 20px;
                    padding-bottom: 14px;
                }

                .signature-party {
                    font-size: 9.5pt;
                    margin-bottom: 8px;
                }

                .signature-details {
                    font-size: 7.5pt;
                    margin-bottom: 12px;
                    line-height: 1.3;
                }

                .signature-line {
                    margin-top: 22px;
                    font-size: 7.5pt;
                }

                .footer {
                    margin-top: 20px;
                    padding-top: 8px;
                    font-size: 6.5pt;
                }

                .highlight {
                    padding: 6px;
                    margin: 8px 0;
                    font-size: 9pt;
                }

                table th {
                    padding: 5px;
                    font-size: 8.5pt;
                }

                table td {
                    padding: 4px 5px;
                    font-size: 8.5pt;
                }
            }

            /* Очень маленькие телефоны (до 380px) */
            @media (max-width: 380px) {
                body {
                    padding: 8mm 5mm;
                    font-size: 9pt;
                }

                .header {
                    margin-bottom: 16px;
                    padding-bottom: 10px;
                }

                .header h1 {
                    font-size: 12pt;
                    margin-bottom: 5px;
                }

                .header .subtitle {
                    font-size: 8pt;
                }

                .document-number {
                    font-size: 9.5pt;
                }

                .meta-info {
                    margin-bottom: 14px;
                    font-size: 8pt;
                }

                .meta-row {
                    margin-bottom: 5px;
                    padding-bottom: 5px;
                }

                .meta-label {
                    font-size: 7.5pt;
                }

                .content {
                    margin-bottom: 16px;
                }

                .content p {
                    margin-bottom: 7px;
                    text-indent: 8px;
                    line-height: 1.5;
                }

                .content h2 {
                    font-size: 10.5pt;
                    margin: 12px 0 5px 0;
                }

                .content h3 {
                    font-size: 9.5pt;
                    margin: 10px 0 4px 0;
                }

                .content ol, .content ul {
                    margin: 5px 0 5px 18px;
                }

                .content li {
                    margin-bottom: 3px;
                }

                .signature-section {
                    margin-top: 20px;
                }

                .signature-title {
                    font-size: 9.5pt;
                    margin-bottom: 10px;
                }

                .signature-block {
                    margin-bottom: 18px;
                    padding-bottom: 12px;
                }

                .signature-party {
                    font-size: 9pt;
                    margin-bottom: 7px;
                }

                .signature-details {
                    font-size: 7pt;
                    margin-bottom: 10px;
                }

                .signature-line {
                    margin-top: 20px;
                    font-size: 7pt;
                }

                .footer {
                    margin-top: 18px;
                    padding-top: 7px;
                    font-size: 6pt;
                }

                .highlight {
                    padding: 5px;
                    margin: 7px 0;
                    font-size: 8.5pt;
                }

                table th {
                    padding: 4px;
                    font-size: 8pt;
                }

                table td {
                    padding: 3px 4px;
                    font-size: 8pt;
                }
            }
        }

        /* ===== СТИЛИ ДЛЯ ПЕЧАТИ (сохраняем оригинал) ===== */
        @media print {
            body {
                padding: 20mm 15mm;
                font-size: 11pt;
            }

            .header h1 { font-size: 18pt; }
            .header .subtitle { font-size: 10pt; }
            .document-number { font-size: 12pt; }
            .meta-info { font-size: 10pt; }
            .meta-label { width: 30%; }

            .content p { text-indent: 20px; }
            .content h2 { font-size: 13pt; }
            .content h3 { font-size: 12pt; }

            .signatures {
                display: table;
                border-spacing: 20px 0;
            }
            .signature-block {
                display: table-cell;
                width: 50%;
            }
            .signature-line-content { display: table; }
            .signature-line-left, .signature-line-right {
                display: table-cell;
                width: 50%;
            }

            table { display: table; }
            table th { font-size: 10pt; padding: 10px; }
            table td { font-size: 10pt; padding: 8px 10px; }

            .footer { font-size: 8pt; }
        }

        @page {
            size: A4;
            margin: 15mm;
        }
    </style>
</head>
<body>
<!-- Заголовок документа -->
<div class="header">
    <h1>{{ $documentType ?? 'Документ' }}</h1>
    @if(isset($documentNumber))
    <div class="document-number">№ {{ $documentNumber }}</div>
    @endif
    @if(isset($subtitle))
    <div class="subtitle">{{ $subtitle }}</div>
    @endif
</div>

<!-- Мета-информация -->
<div class="meta-info">
    @if(isset($date))
    <div class="meta-row">
        <div class="meta-label">Дата составления:</div>
        <div class="meta-value">{{ $date }}</div>
    </div>
    @endif

    @if(isset($recipient))
    <div class="meta-row">
        <div class="meta-label">Контрагент:</div>
        <div class="meta-value">{{ $recipient }}</div>
    </div>
    @endif

    @if(isset($details) && is_array($details))
    @foreach($details as $key => $value)
    <div class="meta-row">
        <div class="meta-label">{{ $key }}:</div>
        <div class="meta-value">{{ $value }}</div>
    </div>
    @endforeach
    @endif
</div>

<!-- Основное содержимое документа -->
<div class="content">
    {!! $content !!}
</div>

<!-- Подписи сторон -->
<div class="signature-section">
    <div class="signature-title">Подписи сторон</div>

    <div class="signatures">
        <!-- Исполнитель -->
        <div class="signature-block">
            <div class="signature-party">Исполнитель:</div>
            <div class="signature-details">
                ООО "Ваша Компания"<br>
                ИНН: 1234567890<br>
                КПП: 123456789<br>
                Адрес: г. Москва, ул. Примерная, д. 1
            </div>
            <div class="signature-line">
                <div class="signature-line-content">
                    <div class="signature-line-left">_________________</div>
                    <div class="signature-line-right">/ ________________ /</div>
                </div>
                <div style="margin-top: 5px; font-size: 8pt; color: #999;">
                    <span style="display: inline-block; width: 48%;">подпись</span>
                    <span style="display: inline-block; width: 48%; text-align: center;">расшифровка</span>
                </div>
            </div>
        </div>

        <!-- Заказчик -->
        <div class="signature-block">
            <div class="signature-party">Заказчик:</div>
            <div class="signature-details">
                {{ $recipient ?? '_________________________' }}<br>
                ИНН: _______________<br>
                КПП: _______________<br>
                Адрес: _______________
            </div>
            <div class="signature-line">
                <div class="signature-line-content">
                    <div class="signature-line-left">_________________</div>
                    <div class="signature-line-right">/ ________________ /</div>
                </div>
                <div style="margin-top: 5px; font-size: 8pt; color: #999;">
                    <span style="display: inline-block; width: 48%;">подпись</span>
                    <span style="display: inline-block; width: 48%; text-align: center;">расшифровка</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Подвал документа -->
<div class="footer">
    Документ сгенерирован автоматически | {{ now()->format('d.m.Y H:i') }} | DocSign System
</div>
</body>
</html>