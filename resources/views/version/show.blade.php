@extends('layouts.admin')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <div class="min-h-screen bg-[#0f172a] px-4 py-8 antialiased selection:bg-blue-500/30 selection:text-blue-200" style="font-family: 'Inter', sans-serif;">
        <div class="max-w-4xl mx-auto">

            {{-- HEADER --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-blue-500 rounded-full shadow-[0_0_10px_rgba(59,130,246,0.5)]"></span>
                        <span class="text-blue-400 ml-1">v{{ $version->version }}</span>
                    </h1>
                    <p class="text-[10px] text-gray-400 uppercase font-semibold tracking-widest mt-1" data-i18n="detailsSubtitle">Document revision details</p>
                </div>

                <div class="flex items-center gap-3">
                    {{-- КНОПКА НАЗАД --}}
                    <a href="{{ route('versions.index') }}" class="text-[11px] font-bold uppercase tracking-wider text-gray-400 hover:text-white transition flex items-center gap-1.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        <span data-i18n="backBtn">Back</span>
                    </a>

                    {{-- КНОПКА РЕДАКТИРОВАТЬ --}}
                    <a href="{{ route('versions.edit', $version->id) }}" class="px-4 py-1.5 bg-blue-600 text-white rounded-lg text-[11px] font-bold uppercase tracking-wider hover:bg-blue-700 transition shadow-lg shadow-blue-900/20" data-i18n="editBtn">Edit</a>

                    {{-- КНОПКА УДАЛИТЬ --}}
                    <form action="{{ route('versions.destroy', $version->id) }}"
                          method="POST"
                          class="inline-block m-0 p-0"
                          data-confirm-i18n="confirmDelete">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-1.5 bg-red-600/20 text-red-400 border border-red-500/30 rounded-lg text-[11px] font-bold uppercase tracking-wider hover:bg-red-600 hover:text-white transition shadow-lg shadow-red-900/10">
                            <span data-i18n="deleteBtn">Удалить</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- GRID --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">

                {{-- LEFT: MAIN FILE CARD --}}
                <div class="lg:col-span-1 flex flex-col">
                    <div class="w-full flex-grow bg-white rounded-[24px] shadow-xl p-5 text-center border border-gray-100 flex flex-col items-center justify-center min-h-[320px]">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-3 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>

                        {{-- Блок исправления длинного имени файла (разрешаем контролируемый перенос строк) --}}
                        <div class="w-full max-w-[190px] px-1 mb-2">
                            <h2 class="text-[13px] font-bold text-gray-900 leading-tight break-all" title="{{ basename($version->file_path) }}">
                                {{ basename($version->file_path) }}
                            </h2>
                        </div>

                        <span class="inline-block px-2 py-0.5 text-[9px] font-black tracking-widest text-blue-600 bg-blue-50 border border-blue-100 rounded-md uppercase">{{ pathinfo($version->file_path, PATHINFO_EXTENSION) }}</span>

                        {{-- Сделали кнопку скачивания аккуратной и меньшего размера --}}
                        <a href="{{ asset('storage/' . $version->file_path) }}" download class="mt-5 inline-flex items-center gap-1.5 px-4 py-2 bg-pink-50 text-pink-600 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-pink-100 transition-all border border-pink-100 shadow-2xs" data-i18n="viewFileBtn">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download File
                        </a>
                    </div>
                </div>

                {{-- RIGHT: SIDEBAR INFO (РОВНАЯ ТАБЛИЦА) --}}
                <div class="lg:col-span-2 flex flex-col">
                    <div class="bg-white rounded-[24px] shadow-xl border border-gray-100 p-6 flex flex-col justify-between h-full">

                        <div class="divide-y divide-gray-100 overflow-hidden">

                            {{-- Строка 1: Документ --}}
                            <div class="grid grid-cols-3 py-3.5 first:pt-0 items-center gap-4">
                                <div class="text-[10px] text-gray-400 uppercase font-bold tracking-wider" data-i18n="labelDoc">Document</div>
                                <div class="col-span-2 flex items-center justify-between gap-4">
                                    <span class="text-xs font-bold text-gray-900 leading-snug truncate max-w-[240px]" title="{{ $version->document?->title ?? 'Deleted' }}">
                                        {{ $version->document?->title ?? 'Deleted' }}
                                    </span>
                                    <a href="{{ route('documents.show', $version->document_id) }}" class="text-[9px] font-bold uppercase tracking-widest text-blue-600 hover:text-blue-800 transition flex items-center gap-0.5 shrink-0">
                                        <span data-i18n="openOriginal">Open original</span>
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="3"/></svg>
                                    </a>
                                </div>
                            </div>

                            {{-- Строка 2: Дата загрузки --}}
                            <div class="grid grid-cols-3 py-3.5 items-center gap-4">
                                <div class="text-[10px] text-gray-400 uppercase font-bold tracking-wider" data-i18n="labelUploaded">Uploaded</div>
                                <div class="col-span-2 text-xs font-bold text-gray-900">
                                    {{ $version->created_at->format('d.m.Y') }}
                                    <span class="text-gray-400 font-medium ml-1.5">{{ $version->created_at->format('H:i') }}</span>
                                </div>
                            </div>

                            {{-- Строка 3: ID версии --}}
                            <div class="grid grid-cols-3 py-3.5 items-center gap-4">
                                <div class="text-[10px] text-gray-400 uppercase font-bold tracking-wider" data-i18n="labelVersionId">Version ID</div>
                                <div class="col-span-2 text-xs font-mono font-bold text-gray-700">
                                    #{{ $version->id }}
                                </div>
                            </div>

                            {{-- Строка 4: Статус --}}
                            <div class="grid grid-cols-3 py-3.5 last:pb-0 items-center gap-4">
                                <div class="text-[10px] text-gray-400 uppercase font-bold tracking-wider" data-i18n="labelStatus">Status</div>
                                <div class="col-span-2">
                                    <span class="px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded-md bg-green-50 text-green-600 border border-green-100 inline-block" data-i18n="statusActive">Active</span>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ============================================================
        // ЛОКАЛЬНЫЙ СЛОВАРЬ СТРАНИЦЫ ПРОСМОТРА ВЕРСИИ
        // ============================================================
        const VERSION_SHOW_TRANSLATIONS = {
            ru: {
                backBtn: 'Назад',
                versionTitle: 'Версия',
                detailsSubtitle: 'ФАЙЛ И ДАННЫЕ РЕВИЗИИ',
                editBtn: 'Редактировать',
                deleteBtn: 'Удалить',
                viewFileBtn: 'СКАЧАТЬ',
                labelDoc: 'Документ',
                openOriginal: 'ОТКРЫТЬ',
                labelUploaded: 'Дата загрузки',
                labelVersionId: 'ID версии',
                labelStatus: 'Статус',
                statusActive: 'Активен',
                confirmDelete: 'Вы уверены, что хотите безвозвратно удалить эту версию документа?'
            },
            tj: {
                backBtn: 'Бозгашт',
                versionTitle: 'Нусха',
                detailsSubtitle: 'ТАФСИЛОТИ ТАҲРИРИ ҲУҶҶАТ',
                editBtn: 'Таҳрир',
                deleteBtn: 'Ҳазф',
                viewFileBtn: 'БОРГИРИИ',
                labelDoc: 'Ҳуҷҷат',
                openOriginal: 'АСЛӢ',
                labelUploaded: 'Санаи боргузорӣ',
                labelVersionId: 'ID-и нусха',
                labelStatus: 'Статус',
                statusActive: 'Фаъол',
                confirmDelete: 'Шумо боварӣ доред, ки ин нусхаи ҳуҷҷатро комилан ҳазф мекунед?'
            },
            en: {
                backBtn: 'Back',
                versionTitle: 'Version',
                detailsSubtitle: 'FILE AND REVISION DETAILS',
                editBtn: 'Edit',
                deleteBtn: 'Delete',
                viewFileBtn: 'DOWNLOAD',
                labelDoc: 'Document',
                openOriginal: 'OPEN',
                labelUploaded: 'Uploaded',
                labelVersionId: 'Version ID',
                labelStatus: 'Status',
                statusActive: 'Active',
                confirmDelete: 'Are you sure you want to permanently delete this document version?'
            }
        };

        // ============================================================
        // ФУНКЦИЯ ПРИМЕНЕНИЯ ПЕРЕВОДОВ
        // ============================================================
        function applyVersionShowTranslations(lang) {
            const dict = VERSION_SHOW_TRANSLATIONS[lang] || VERSION_SHOW_TRANSLATIONS.ru;

            // 1) Переводим все элементы с data-i18n
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (dict[key] !== undefined) el.textContent = dict[key];
            });

            // 2) Переводим placeholder
            document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
                const key = el.getAttribute('data-i18n-placeholder');
                if (dict[key] !== undefined) el.setAttribute('placeholder', dict[key]);
            });

            // 3) Переводим title (подсказки)
            document.querySelectorAll('[data-i18n-title]').forEach(el => {
                const key = el.getAttribute('data-i18n-title');
                if (dict[key] !== undefined) el.setAttribute('title', dict[key]);
            });

            // 4) Обновляем обработчики confirm для форм удаления
            document.querySelectorAll('[data-confirm-i18n]').forEach(el => {
                const key = el.getAttribute('data-confirm-i18n');
                const message = dict[key] || 'Are you sure?';

                // Клонируем элемент, чтобы сбросить старые обработчики
                const newEl = el.cloneNode(true);
                el.parentNode.replaceChild(newEl, el);

                // Если дата-атрибут на форме
                if (newEl.tagName === 'FORM') {
                    newEl.onsubmit = (e) => {
                        if (!confirm(message)) e.preventDefault();
                    };
                } else {
                    // Если на кнопке
                    const form = newEl.closest('form');
                    if (form) {
                        form.onsubmit = (e) => {
                            if (!confirm(message)) e.preventDefault();
                        };
                    }
                }
            });
        }

        // ============================================================
        // 1. Применяем сразу при загрузке
        // ============================================================
        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applyVersionShowTranslations(initialLang);

        // ============================================================
        // 2. Слушаем событие смены языка от layouts/admin.blade.php
        // ============================================================
        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applyVersionShowTranslations(lang);
        });

        // ============================================================
        // 3. Синхронизация между вкладками браузера
        // ============================================================
        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applyVersionShowTranslations(e.newValue);
            }
        });
    });
</script>
@endsection
