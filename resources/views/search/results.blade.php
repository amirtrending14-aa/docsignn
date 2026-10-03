@extends('layouts.admin')

@section('content')
<style>
    /* === СТРАНИЦА ПОИСКА В СТИЛЕ АДМИНКИ === */
    .search-page-custom {
        color: var(--text);
        font-family: 'Inter', sans-serif;
    }

    .search-page-custom .page-head-custom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .search-page-custom h1 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -0.5px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .search-page-custom h1 .accent-bar {
        width: 4px;
        height: 24px;
        background: rgb(var(--glow));
        border-radius: 2px;
        box-shadow: 0 0 10px rgba(var(--glow), 0.6);
        flex-shrink: 0;
    }

    .search-page-custom .query-highlight {
        color: rgb(var(--glow));
        font-weight: 600;
        word-break: break-word;
    }

    /* Счётчик результатов */
    .results-counter {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 16px;
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--line);
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .results-counter .count-num {
        font-size: 15px;
        font-weight: 700;
        color: rgb(var(--glow));
    }

    .results-counter .count-label {
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 10px;
    }

    /* Контейнер таблицы */
    .search-table-wrap {
        background: rgba(255,255,255,0.02);
        border: 1px solid var(--line);
        border-radius: 12px;
        overflow: hidden;
        position: relative;
    }

    /* Скролл-контейнер для таблицы */
    .search-table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .search-table-scroll::-webkit-scrollbar {
        height: 6px;
    }

    .search-table-scroll::-webkit-scrollbar-track {
        background: rgba(255,255,255,0.02);
    }

    .search-table-scroll::-webkit-scrollbar-thumb {
        background: rgba(var(--glow), 0.3);
        border-radius: 20px;
    }

    .search-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }

    .search-table th {
        text-align: left;
        padding: 14px 16px;
        font-size: 11px;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--line);
        background: rgba(255,255,255,0.02);
        white-space: nowrap;
    }

    .search-table th.center { text-align: center; }
    .search-table th.right { text-align: right; }

    .search-table td {
        padding: 16px;
        font-size: 13px;
        color: var(--text);
        border-bottom: 1px solid var(--line);
        transition: background .2s ease;
    }

    .search-table td.center { text-align: center; }
    .search-table td.right { text-align: right; }

    .search-table tbody tr {
        transition: background .2s ease;
    }

    .search-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .search-table tbody tr:hover {
        background: rgba(var(--glow), 0.04);
    }

    /* Иконка типа (User/Doc/Sig) */
    .type-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        flex-shrink: 0;
        transition: all .25s ease;
    }

    .search-table tbody tr:hover .type-icon {
        transform: scale(1.05);
    }

    .type-icon.user {
        background: rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(16, 185, 129, 0.25);
        color: #10b981;
    }

    .type-icon.signature {
        background: rgba(245, 158, 11, 0.12);
        border: 1px solid rgba(245, 158, 11, 0.25);
        color: #f59e0b;
    }

    .type-icon.document {
        background: rgba(var(--glow), 0.12);
        border: 1px solid rgba(var(--glow), 0.25);
        color: rgb(var(--glow));
    }

    .type-icon svg {
        width: 16px;
        height: 16px;
    }

    /* Ячейка с названием */
    .item-title {
        font-weight: 600;
        font-size: 14px;
        color: var(--text);
        display: block;
        max-width: 350px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .item-subtitle {
        font-size: 12px;
        color: var(--muted);
        margin-top: 3px;
        display: block;
        max-width: 350px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Бейджи типов */
    .type-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        border: 1px solid;
        transition: all .2s ease;
        white-space: nowrap;
    }

    .type-badge.user {
        background: rgba(16, 185, 129, 0.1);
        border-color: rgba(16, 185, 129, 0.25);
        color: #10b981;
    }

    .type-badge.signature {
        background: rgba(245, 158, 11, 0.1);
        border-color: rgba(245, 158, 11, 0.25);
        color: #f59e0b;
    }

    .type-badge.document {
        background: rgba(var(--glow), 0.1);
        border-color: rgba(var(--glow), 0.25);
        color: rgb(var(--glow));
    }

    /* Детали */
    .item-details {
        font-size: 12px;
        color: var(--muted);
        font-weight: 500;
        white-space: nowrap;
    }

    /* Дата и статус */
    .item-date {
        font-size: 13px;
        color: var(--text);
        font-weight: 600;
        white-space: nowrap;
    }

    .item-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
        white-space: nowrap;
    }

    .item-status::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .item-status.signed { color: #10b981; }
    .item-status.processing { color: #f59e0b; }
    .item-status.active { color: rgb(var(--glow)); }

    /* Кнопка действия (View) */
    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--line);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--muted);
        text-decoration: none;
        transition: all .2s ease;
    }

    .action-btn:hover {
        color: rgb(var(--glow));
        border-color: rgba(var(--glow), 0.4);
        background: rgba(var(--glow), 0.1);
        transform: scale(1.05);
    }

    .action-btn svg {
        width: 14px;
        height: 14px;
    }

    /* Пустое состояние */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }

    .empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: rgba(var(--glow), 0.08);
        border: 1px solid rgba(var(--glow), 0.2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        color: rgb(var(--glow));
        margin: 0 auto 16px;
    }

    .empty-icon svg {
        width: 28px;
        height: 28px;
    }

    .empty-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 4px;
    }

    .empty-sub {
        font-size: 13px;
        color: var(--muted);
    }

    /* ============================================ */
    /* === СВЕТЛАЯ ТЕМА: ИДЕАЛЬНАЯ ЧИТАЕМОСТЬ === */
    /* ============================================ */
    html[data-theme="light"] .search-table-wrap {
        background: #ffffff;
        border-color: #cbd5e1;
    }
    html[data-theme="light"] .search-table th {
        background: #f8fafc;
        color: #475569;
        border-bottom-color: #cbd5e1;
    }
    html[data-theme="light"] .search-table td {
        color: #0f172a;
        border-bottom-color: #e2e8f0;
    }
    html[data-theme="light"] .search-table tbody tr:hover {
        background: rgba(15, 23, 42, 0.04);
    }
    html[data-theme="light"] .results-counter {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    html[data-theme="light"] .results-counter .count-label {
        color: #64748b;
    }
    html[data-theme="light"] .type-badge.user {
        background: rgba(16, 185, 129, 0.1);
        border-color: rgba(16, 185, 129, 0.3);
        color: #059669;
    }
    html[data-theme="light"] .type-badge.signature {
        background: rgba(245, 158, 11, 0.1);
        border-color: rgba(245, 158, 11, 0.3);
        color: #d97706;
    }
    html[data-theme="light"] .type-badge.document {
        background: rgba(var(--glow), 0.08);
        border-color: rgba(var(--glow), 0.3);
        color: rgb(var(--glow));
        filter: brightness(0.85);
    }
    html[data-theme="light"] .action-btn {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #64748b;
    }
    html[data-theme="light"] .action-btn:hover {
        background: rgba(var(--glow), 0.08);
        border-color: rgba(var(--glow), 0.4);
        color: rgb(var(--glow));
    }
    html[data-theme="light"] .empty-icon {
        background: rgba(var(--glow), 0.06);
        border-color: rgba(var(--glow), 0.25);
    }
</style>

<div class="search-page-custom">
    {{-- Заголовок страницы --}}
    <div class="page-head-custom">
        <h1>
            <span class="accent-bar"></span>
            <span data-i18n="resultsFor">Результаты:</span>
            <span class="query-highlight">"{{ $query }}"</span>
        </h1>

        <div class="results-counter">
            <span class="count-num">{{ $results->count() }}</span>
            <span class="count-label" data-i18n="totalFound">Всего найдено</span>
        </div>
    </div>

    {{-- Таблица результатов --}}
    <div class="search-table-wrap">
        <div class="search-table-scroll">
            <table class="search-table">
                <thead>
                <tr>
                    <th><span data-i18n="thObject">Объект и описание</span></th>
                    <th class="center"><span data-i18n="thCategory">Категория</span></th>
                    <th class="center"><span data-i18n="thDetails">Детали</span></th>
                    <th class="center"><span data-i18n="thStatus">Статус / Дата</span></th>
                    <th class="right"><span data-i18n="thAction">Действие</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse($results as $item)
                @php
                $isUser = $item instanceof \App\Models\User;
                $isSig = $item instanceof \App\Models\DocumentSignature;

                if ($isUser) {
                    $typeClass = 'user';
                    $typeKey = 'typeUser';
                    $title = $item->name ?? 'User #' . $item->id;
                    $phoneDisplay = $item->phone ? ' • ' . $item->phone : '';
                    $subtitle = trim(($item->email ?? '') . $phoneDisplay);
                    if (empty($subtitle)) {
                        $subtitle = 'Нет контактных данных';
                    }
                    $details = ucfirst($item->role ?? 'User');
                    $date = $item->created_at?->format('d.m.Y') ?? '—';
                    $statusKey = 'statusActive';
                    $statusClass = 'active';
                    $route = route('users.show', $item->id);
                } elseif ($isSig) {
                    $typeClass = 'signature';
                    $typeKey = 'typeSig';
                    $title = ($item->document->title ?? 'Signature') . ' #' . $item->id;
                    $subtitle = $item->document->title ?? 'N/A';
                    $details = 'ID: ' . $item->id;
                    $date = $item->created_at?->format('d.m.Y') ?? '—';
                    $statusKey = $item->signed_at ? 'statusSigned' : 'statusProcess';
                    $statusClass = $item->signed_at ? 'signed' : 'processing';
                    $route = route('signatures.show', $item->id);
                } else {
                    $typeClass = 'document';
                    $typeKey = 'typeDoc';
                    $title = $item->title ?? 'Document #' . $item->id;
                    $subtitle = \Illuminate\Support\Str::limit($item->content ?? '', 60);
                    $details = $item->number ?? 'Стандартный';
                    $date = $item->created_at?->format('d.m.Y') ?? '—';
                    $statusKey = 'statusActive';
                    $statusClass = 'active';
                    $route = route('documents.show', $item->id);
                }
                @endphp
                <tr>
                    {{-- Объект --}}
                    <td>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div class="type-icon {{ $typeClass }}">
                                @if($isUser)
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($item->name ?? 'U', 0, 1)) }}
                                @elseif($isSig)
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                @else
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                @endif
                            </div>
                            <div style="min-width:0; flex:1;">
                                <span class="item-title">{{ $title }}</span>
                                <span class="item-subtitle">
                                    @if($isUser)
                                        {{ $subtitle }}
                                    @elseif($isSig)
                                        <span data-i18n="docPrefix">Документ:</span> {{ $subtitle }}
                                    @else
                                        {{ $subtitle ?: '—' }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </td>

                    {{-- Категория --}}
                    <td class="center">
                        <span class="type-badge {{ $typeClass }}" data-i18n="{{ $typeKey }}">
                            {{ $typeKey === 'typeUser' ? 'User' : ($typeKey === 'typeSig' ? 'Signature' : 'Document') }}
                        </span>
                    </td>

                    {{-- Детали --}}
                    <td class="center">
                        <span class="item-details">
                            @if($isUser)
                                {{ $details }}
                            @elseif($isSig)
                                ID: {{ $item->id }}
                            @else
                                <span data-i18n="typeStandard">Стандартный</span>
                            @endif
                        </span>
                    </td>

                    {{-- Статус / Дата --}}
                    <td class="center">
                        <div style="display:flex; flex-direction:column; align-items:center;">
                            <span class="item-date">{{ $date }}</span>
                            <span class="item-status {{ $statusClass }}" data-i18n="{{ $statusKey }}">
                                Активен
                            </span>
                        </div>
                    </td>

                    {{-- Действие --}}
                    <td class="right">
                        <a href="{{ $route }}" class="action-btn" data-i18n-title="viewAction" title="View">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                <path d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="empty-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"/>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                </svg>
                            </div>
                            <div class="empty-title" data-i18n="noResults">По запросу ничего не найдено</div>
                            <div class="empty-sub" data-i18n="tryDifferentQuery">Попробуйте изменить запрос</div>
                        </div>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const SEARCH_TRANSLATIONS = {
            ru: {
                resultsFor: 'Результаты:',
                totalFound: 'Всего найдено',
                thObject: 'Объект и описание',
                thCategory: 'Категория',
                thDetails: 'Детали',
                thStatus: 'Статус / Дата',
                thAction: 'Действие',
                typeUser: 'Пользователь',
                typeSig: 'Подпись',
                typeDoc: 'Документ',
                typeStandard: 'Стандартный',
                statusSigned: 'Подписан',
                statusProcess: 'В процессе',
                statusActive: 'Активен',
                docPrefix: 'Документ:',
                noResults: 'По запросу ничего не найдено',
                tryDifferentQuery: 'Попробуйте изменить запрос',
                viewAction: 'Просмотр'
            },
            tj: {
                resultsFor: 'Натиҷаҳо:',
                totalFound: 'Ҳамагӣ ёфт шуд',
                thObject: 'Объект ва тавсиф',
                thCategory: 'Категория',
                thDetails: 'Тафсилот',
                thStatus: 'Статус / Сана',
                thAction: 'Амал',
                typeUser: 'Корбар',
                typeSig: 'Имзо',
                typeDoc: 'Ҳуҷҷат',
                typeStandard: 'Стандартӣ',
                statusSigned: 'Имзо шуд',
                statusProcess: 'Дар ҷараён',
                statusActive: 'Фаъол',
                docPrefix: 'Ҳуҷҷат:',
                noResults: 'Тибқи дархост чизе ёфт нашуд',
                tryDifferentQuery: 'Кӯшиш кунед дархостро иваз кунед',
                viewAction: 'Дидан'
            },
            en: {
                resultsFor: 'Results:',
                totalFound: 'Total found',
                thObject: 'Object & Description',
                thCategory: 'Category',
                thDetails: 'Details',
                thStatus: 'Status / Date',
                thAction: 'Action',
                typeUser: 'User',
                typeSig: 'Signature',
                typeDoc: 'Document',
                typeStandard: 'Standard',
                statusSigned: 'Signed',
                statusProcess: 'In Process',
                statusActive: 'Active',
                docPrefix: 'Document:',
                noResults: 'No results found',
                tryDifferentQuery: 'Try changing your query',
                viewAction: 'View'
            }
        };

        function applySearchTranslations(lang) {
            const dict = SEARCH_TRANSLATIONS[lang] || SEARCH_TRANSLATIONS.ru;

            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (dict[key] !== undefined) el.textContent = dict[key];
            });

            document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
                const key = el.getAttribute('data-i18n-placeholder');
                if (dict[key] !== undefined) el.setAttribute('placeholder', dict[key]);
            });

            document.querySelectorAll('[data-i18n-title]').forEach(el => {
                const key = el.getAttribute('data-i18n-title');
                if (dict[key] !== undefined) el.setAttribute('title', dict[key]);
            });
        }

        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applySearchTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applySearchTranslations(lang);
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applySearchTranslations(e.newValue);
            }
        });
    });
</script>
@endsection