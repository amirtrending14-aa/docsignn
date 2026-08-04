@extends('layouts.admin')

@section('content')
<style>
    .paper-document {
        background: white;
        padding: 40px;
        border: 1px solid #ccc;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        font-family: 'Times New Roman', Times, serif;
        font-size: 14pt;
        line-height: 1.6;
        color: #000;
        max-width: 800px;
        margin: 0 auto;
    }
    .doc-title { text-align: center; font-weight: bold; font-size: 16pt; margin-bottom: 20px; text-transform: uppercase; }
    .doc-header { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 12pt; }
    .doc-section { margin-bottom: 15px; text-align: justify; }
    .doc-signatures { display: flex; justify-content: space-between; margin-top: 50px; }
    .signature-line { border-top: 1px solid #000; width: 45%; padding-top: 5px; text-align: center; font-size: 12pt; }
</style>

<div class="container mt-4 mb-5">
    <!-- Кнопки управления (не печатаются на бумаге) -->
    <div class="text-center mb-4 no-print">
        <a href="{{ route('contract.create') }}" class="btn btn-secondary">← Назад к форме</a>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Распечатать / Сохранить в PDF</button>
    </div>

    <!-- Сам документ -->
    <div class="paper-document" id="printableArea">
        <div class="doc-title">ДОГОВОР ЗАЙМА ДЕНЕЖНЫХ СРЕДСТВ</div>

        <div class="doc-header">
            <span>г. {{ $data['city'] }}</span>
            <span>«{{ date('d', strtotime($data['date'])) }}» {{ date('F', strtotime($data['date'])) }} {{ date('Y', strtotime($data['date'])) }} г.</span>
        </div>

        <div class="doc-section">
            Гражданин(ка) <strong>{{ $data['lender_name'] }}</strong>, именуемый(ая) в дальнейшем «Займодавец», с одной стороны, и
            гражданин(ка) <strong>{{ $data['borrower_name'] }}</strong>, именуемый(ая) в дальнейшем «Заемщик», с другой стороны,
            заключили настоящий договор о нижеследующем:
        </div>

        <div class="doc-section">
            <strong>1. ПРЕДМЕТ ДОГОВОРА</strong><br>
            1.1. Займодавец передает в собственность Заемщику денежные средства в размере <strong>{{ $data['formatted_amount'] }}</strong> (сумма прописью: {{ \NumberFormatter::create('ru_RU', \NumberFormatter::SPELLOUT)->format($data['amount']) }}) сомони, а Заемщик обязуется возвратить Займодавцу такую же сумму денег в срок до «{{ date('d', strtotime('+1 year', strtotime($data['date']))) }}» {{ date('F', strtotime('+1 year', strtotime($data['date']))) }} {{ date('Y', strtotime('+1 year', strtotime($data['date']))) }} г.
        </div>

        <div class="doc-section">
            <strong>2. ПРАВА И ОБЯЗАННОСТИ СТОРОН</strong><br>
            2.1. Заемщик обязуется своевременно возвратить займодавцу полученную сумму займа.<br>
            2.2. Займодавец имеет право контролировать целевое использование заемных средств (если договор был целевым).
        </div>

        <div class="doc-section">
            <strong>3. ЗАКЛЮЧИТЕЛЬНЫЕ ПОЛОЖЕНИЯ</strong><br>
            3.1. Настоящий договор вступает в силу с момента передачи денежных средств Заемщику.<br>
            3.2. Договор составлен в двух экземплярах, имеющих равную юридическую силу, по одному для каждой из сторон.
        </div>

        <div class="doc-signatures">
            <div class="signature-line">
                <strong>Займодавец:</strong><br><br><br>
                {{ $data['lender_name'] }}
            </div>
            <div class="signature-line">
                <strong>Заемщик:</strong><br><br><br>
                {{ $data['borrower_name'] }}
            </div>
        </div>
    </div>
</div>

<!-- Скрываем кнопки при печати -->
<style>
    @media print {
        .no-print, nav, footer, .sidebar { display: none !important; }
        .paper-document { border: none; box-shadow: none; padding: 0; }
        body { background: white; }
    }
</style>
@endsection