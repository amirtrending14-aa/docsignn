@extends('layouts.superadmin')

@section('title', 'История активности')
@section('page-title', 'История действий')
@section('page-subtitle', 'Журнал событий и операций пользователей')

@section('content')
<style>
    .activity-stat {
        border: 1px solid #e5eaf1;
        border-radius: 14px;
        background: #fff;
        padding: 18px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .035);
    }
    .activity-stat-icon {
        display: flex;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: #eff6ff;
        color: #2563eb;
    }
    .activity-stat-label {
        color: #64748b;
        font-size: .76rem;
        font-weight: 600;
    }
    .activity-stat-value {
        margin-top: 3px;
        color: #0f172a;
        font-size: 1.45rem;
        font-weight: 750;
        line-height: 1.2;
    }
    .activity-filter {
        width: 100%;
        min-height: 42px;
        border: 1px solid #dbe2ea;
        border-radius: 9px;
        background: #fff;
        padding: 9px 11px;
        color: #172033;
        font-size: .84rem;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }
    .activity-filter:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
    }
    .activity-label {
        display: block;
        margin-bottom: 6px;
        color: #475569;
        font-size: .76rem;
        font-weight: 650;
    }
    .activity-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: 1px solid #dbeafe;
        border-radius: 999px;
        background: #eff6ff;
        padding: 5px 10px;
        color: #1d4ed8;
        font-size: .74rem;
        font-weight: 650;
        white-space: nowrap;
    }
    .activity-badge[data-kind="success"] { border-color: #bbf7d0; background: #f0fdf4; color: #15803d; }
    .activity-badge[data-kind="warning"] { border-color: #fde68a; background: #fffbeb; color: #b45309; }
    .activity-badge[data-kind="danger"] { border-color: #fecaca; background: #fef2f2; color: #b91c1c; }
    .activity-badge[data-kind="muted"] { border-color: #e2e8f0; background: #f8fafc; color: #64748b; }
    .activity-avatar {
        display: flex;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        align-items: center;
        justify-content: center;
        border: 1px solid #dbeafe;
        border-radius: 12px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .9rem;
        font-weight: 700;
    }
    .activity-empty {
        padding: 54px 20px;
        text-align: center;
    }
    @media (max-width: 640px) {
        .activity-stat { padding: 14px; }
        .activity-stat-icon { width: 36px; height: 36px; flex-basis: 36px; }
        .activity-stat-value { font-size: 1.2rem; }
    }
</style>

<div class="space-y-6">
    <section class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="activity-stat flex items-center gap-3">
            <div class="activity-stat-icon" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0">
                <div class="activity-stat-label">Всего записей</div>
                <div class="activity-stat-value">{{ number_format($totalActivities, 0, ',', ' ') }}</div>
            </div>
        </div>
        <div class="activity-stat flex items-center gap-3">
            <div class="activity-stat-icon !bg-emerald-50 !text-emerald-700" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.1a7.5 7.5 0 0115 0A17.9 17.9 0 0112 22.5a17.9 17.9 0 01-7.5-2.4z"/></svg>
            </div>
            <div class="min-w-0">
                <div class="activity-stat-label">Входов сегодня</div>
                <div class="activity-stat-value">{{ number_format($todayLogins, 0, ',', ' ') }}</div>
            </div>
        </div>
        <div class="activity-stat flex items-center gap-3">
            <div class="activity-stat-icon !bg-violet-50 !text-violet-700" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9v10a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="min-w-0">
                <div class="activity-stat-label">Операций с документами</div>
                <div class="activity-stat-value">{{ number_format($documentActions, 0, ',', ' ') }}</div>
            </div>
        </div>
        <div class="activity-stat flex items-center gap-3">
            <div class="activity-stat-icon !bg-amber-50 !text-amber-700" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m16 0v-2a4 4 0 00-3-3.87M14 3.13a4 4 0 010 7.75M14 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div class="min-w-0">
                <div class="activity-stat-label">Всего пользователей</div>
                <div class="activity-stat-value">{{ number_format($activeUsersCount, 0, ',', ' ') }}</div>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="mb-4">
            <h2 class="text-base font-bold text-slate-900">Фильтры журнала</h2>
            <p class="mt-1 text-sm text-slate-500">Найдите событие по действию, пользователю или описанию.</p>
        </div>
        <form method="GET" action="{{ route('superadmin.activity') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
            <div class="xl:col-span-2">
                <label class="activity-label" for="activity-search">Поиск</label>
                <input id="activity-search" type="search" name="search" class="activity-filter"
                       value="{{ request('search') }}" placeholder="По описанию события">
            </div>
            <div>
                <label class="activity-label" for="activity-action">Действие</label>
                <select id="activity-action" name="action" class="activity-filter">
                    <option value="">Все действия</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>
                            {{ match(mb_strtolower($action)) {
                                'created', 'создание' => 'Создание',
                                'received', 'получение' => 'Получение',
                                'updated', 'обновление' => 'Обновление',
                                'deleted', 'удаление' => 'Удаление',
                                'signed', 'sign', 'подписание' => 'Подписание',
                                'sent', 'отправка' => 'Отправка',
                                'rejected', 'отклонение' => 'Отклонение',
                                'экспорт' => 'Экспорт',
                                'скачивание' => 'Скачивание',
                                'анализ ии', 'генерация ии' => mb_convert_case($action, MB_CASE_TITLE, 'UTF-8'),
                                'super_admin_access' => 'Доступ супер-администратора',
                                default => mb_convert_case(str_replace('_', ' ', $action), MB_CASE_TITLE, 'UTF-8'),
                            } }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="activity-label" for="activity-user">Пользователь</label>
                <select id="activity-user" name="user_id" class="activity-filter">
                    <option value="">Все пользователи</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="activity-label" for="activity-date-from">Дата с</label>
                <input id="activity-date-from" type="date" name="date_from" class="activity-filter" value="{{ request('date_from') }}">
            </div>
            <div>
                <label class="activity-label" for="activity-date-to">Дата по</label>
                <input id="activity-date-to" type="date" name="date_to" class="activity-filter" value="{{ request('date_to') }}">
            </div>
            <div class="flex items-end gap-2 sm:col-span-2 xl:col-span-6">
                <button type="submit" class="btn-primary justify-center">Применить фильтры</button>
                @if(request()->hasAny(['search', 'action', 'user_id', 'date_from', 'date_to']))
                    <a href="{{ route('superadmin.activity') }}" class="btn-ghost">Сбросить</a>
                @endif
            </div>
        </form>
    </section>

    <section class="card !p-0">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">События</h2>
                <p class="mt-1 text-sm text-slate-500">Последние события системы в хронологическом порядке.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                {{ number_format($activities->total(), 0, ',', ' ') }} записей
            </span>
        </div>

        <div class="table-wrap !border-0">
            <table>
                <thead>
                <tr>
                    <th>Действие</th>
                    <th>Пользователь</th>
                    <th>Описание</th>
                    <th>Документ</th>
                    <th>IP-адрес</th>
                    <th>Время</th>
                </tr>
                </thead>
                <tbody>
                @forelse($activities as $activity)
                    @php
                        $actionText = mb_strtolower($activity->action);
                        $kind = match(true) {
                            str_contains($actionText, 'delete') || str_contains($actionText, 'удален') || str_contains($actionText, 'отклон') => 'danger',
                            str_contains($actionText, 'create') || str_contains($actionText, 'создан') || str_contains($actionText, 'sign') || str_contains($actionText, 'подпис') => 'success',
                            str_contains($actionText, 'update') || str_contains($actionText, 'обнов') => 'warning',
                            default => 'default',
                        };
                        $actionLabel = match($actionText) {
                            'created', 'создание' => 'Создание',
                            'received' => 'Получение',
                            'updated', 'обновление' => 'Обновление',
                            'deleted', 'удаление' => 'Удаление',
                            'signed', 'sign', 'подписание' => 'Подписание',
                            'sent', 'отправка' => 'Отправка',
                            'rejected', 'отклонение' => 'Отклонение',
                            'экспорт' => 'Экспорт',
                            'скачивание' => 'Скачивание',
                            'анализ ии' => 'Анализ ИИ',
                            'генерация ии' => 'Генерация ИИ',
                            'super_admin_access' => 'Доступ супер-администратора',
                            default => mb_convert_case(str_replace('_', ' ', $activity->action), MB_CASE_TITLE, 'UTF-8'),
                        };
                    @endphp
                    <tr>
                        <td>
                            <span class="activity-badge" data-kind="{{ $kind }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ $actionLabel }}
                            </span>
                        </td>
                        <td>
                            <div class="flex min-w-40 items-center gap-3">
                                <div class="activity-avatar" aria-hidden="true">
                                    {{ mb_strtoupper(mb_substr($activity->user?->name ?? '?', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate font-semibold text-slate-800">{{ $activity->user?->name ?? 'Пользователь удалён' }}</div>
                                    @if($activity->user?->email)
                                        <div class="truncate text-xs text-slate-500">{{ $activity->user->email }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="max-w-sm">
                            <span class="line-clamp-2 text-slate-600" title="{{ $activity->description }}">
                                {{ $activity->description ?: 'Описание не указано' }}
                            </span>
                        </td>
                        <td>
                            @if($activity->document)
                                <div class="max-w-48 truncate font-medium text-slate-700" title="{{ $activity->document->title }}">
                                    {{ $activity->document->title }}
                                </div>
                                @if($activity->document->number)
                                    <div class="text-xs text-slate-500">{{ $activity->document->number }}</div>
                                @endif
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td><span class="font-mono text-xs text-slate-500">{{ $activity->ip_address ?: '—' }}</span></td>
                        <td>
                            <div class="whitespace-nowrap font-medium text-slate-700">{{ $activity->created_at?->format('d.m.Y H:i') }}</div>
                            <div class="mt-0.5 whitespace-nowrap text-xs text-slate-500">{{ $activity->created_at?->diffForHumans() }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="activity-empty">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h8m-8 4h8m-8 4h5m-8 6h14a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="font-semibold text-slate-800">События не найдены</div>
                                <p class="mt-1 text-sm text-slate-500">Измените фильтры или сбросьте поиск, чтобы увидеть другие записи.</p>
                                @if(request()->hasAny(['search', 'action', 'user_id', 'date_from', 'date_to']))
                                    <a href="{{ route('superadmin.activity') }}" class="btn-ghost mt-4 inline-flex">Сбросить фильтры</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($activities->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-5 py-4">
                <div class="text-sm text-slate-500">
                    Показаны {{ $activities->firstItem() }}–{{ $activities->lastItem() }} из {{ $activities->total() }}
                </div>
                {{ $activities->withQueryString()->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
