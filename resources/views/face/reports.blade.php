@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Отчёт по посещаемости</h2>

        <form method="GET" class="d-flex gap-2">
            <input type="date" name="date" value="{{ $date }}" class="form-control w-auto">
            <button class="btn btn-primary">Показать</button>
        </form>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @php
        $groups = [
            'absent'  => ['Не пришли', 'danger'],
            'late'    => ['Опоздали', 'warning'],
            'on_time' => ['Вовремя', 'success'],
            'excused' => ['Разрешено (админ знал)', 'secondary'],
        ];
    @endphp

    @foreach ($groups as $status => [$title, $color])
        <div class="card bg-dark border-secondary mb-4 text-white">
            <div class="card-header border-secondary text-{{ $color }}">
                {{ $title }} — {{ count($attendances[$status] ?? []) }}
            </div>

            <table class="table table-dark table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Имя</th>
                        <th>Компания</th>
                        <th>Время</th>
                        <th>Штраф</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances[$status] ?? [] as $a)
                        <tr>
                            <td>{{ $a->user->name }}</td>
                            <td>{{ $a->user->companyRelation?->name ?? '—' }}</td>
                            <td>{{ $a->check_in_time ?? '—' }}</td>
                            <td>
                                @if ($a->fine > 0)
                                    <span class="text-danger">-{{ $a->fine }} сом</span>
                                @else
                                    <span class="text-success">0</span>
                                @endif
                            </td>
                            <td>
                                @if (in_array($a->status, ['absent', 'late']))
                                    <form method="POST" action="{{ route('admin.attendances.excuse', $a) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success">Я знал</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-secondary">Никого</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach
</div>
@endsection