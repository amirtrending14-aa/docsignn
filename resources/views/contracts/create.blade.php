@extends('layouts.admin')

@section('content')
<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📝 Заполнение данных для Договора займа</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('contract.generate') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Город составления</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city', 'г. Худжанд') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Дата договора</label>
                                <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ФИО Займодавца (кто дает деньги)</label>
                            <input type="text" name="lender_name" class="form-control" placeholder="Например: Аминов Амирҷон Далерович" value="{{ old('lender_name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ФИО Заемщика (кто берет деньги)</label>
                            <input type="text" name="borrower_name" class="form-control" placeholder="Например: Петров Иван Иванович" value="{{ old('borrower_name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Сумма займа (сомони)</label>
                            <input type="number" name="amount" class="form-control" placeholder="Например: 50000" step="0.01" value="{{ old('amount') }}" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            ⚡ Сгенерировать документ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection