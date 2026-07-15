    @extends('layouts.admin')

    @section('content')
        <div class="container mx-auto px-4 py-8 min-h-screen">

            <div class="max-w-7xl mx-auto">

                {{-- HEADER --}}
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-xl font-bold doc-main-title tracking-tight flex items-center gap-2">
                            <span class="w-2 h-6 bg-blue-500 rounded-full shadow-[0_0_10px_rgba(59,130,246,0.5)]"></span>
                            <span data-i18n="pageTitle">Версия документ</span>
                        </h1>

                        <p class="text-xs text-gray-400 uppercase tracking-widest" data-i18n="pageSubTitle">
                            История изменений файлов
                        </p>
                    </div>

                    <a href="{{ route('versions.create') }}"
                       class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase hover:bg-indigo-700">
                        <span data-i18n="btnAdd">+ Добавить версию</span>
                    </a>
                </div>

                {{-- TABLE --}}
                <div class="bg-white border rounded-xl shadow-sm overflow-hidden">

                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                        <tr class="text-left text-[10px] uppercase text-gray-500 tracking-wider">
                            {{-- Уменьшили p-4 до px-3 py-2 --}}
                            <th class="px-3 py-2 w-10">#</th>
                            <th class="px-3 py-2" data-i18n="thDoc">Документ</th>
                            <th class="px-3 py-2 text-center" data-i18n="thVer">Версия</th>
                            <th class="px-3 py-2 text-center" data-i18n="thFile">Файл</th>
                            <th class="px-3 py-2 text-center" data-i18n="thDate">Дата</th>
                            <th class="px-3 py-2 text-right" data-i18n="thActions">Действия</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($versions as $index =>$v)
                            <tr class="border-b hover:bg-gray-50 transition">

                                {{-- ID: Уменьшен padding и шрифт --}}
                                <td class="px-3 py-1.5 font-bold text-gray-400 text-[10px]">
                                    {{ $index + 1 }}
                                </td>

                                {{-- DOCUMENT: tight для плотности строк --}}
                                <td class="px-3 py-1.5">
                                    <div class="font-semibold text-black text-xs leading-tight">
                                        {{ $v->document->title ?? 'Удалённый документ' }}
                                    </div>
                                    <div class="text-[10px] text-gray-400">
                                        ID: {{ $v->document_id }}
                                    </div>
                                </td>

                                {{-- VERSION: Уменьшен бейдж --}}
                                <td class="text-center px-3 py-1.5">
            <span class="px-1.5 py-0.5 bg-indigo-50 text-indigo-700 rounded-md font-bold text-[10px]">
                V{{ $v->version }}
            </span>
                                </td>

                                {{-- FILE --}}
                                <td class="text-center px-3 py-1.5">
                                    <a href="{{ asset('storage/'.$v->file_path) }}"
                                       target="_blank"
                                       class="text-blue-600 text-[10px] font-bold uppercase hover:underline" data-i18n="btnDownload">
                                        Скачать
                                    </a>
                                </td>

                                {{-- DATE --}}
                                <td class="text-center text-[10px] text-gray-500 px-3 py-1.5 whitespace-nowrap">
                                    {{ $v->created_at->format('d.m.Y') }}
                                </td>

                                {{-- ACTIONS: Уменьшены отступы между кнопками --}}
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">

                                        {{-- BUTTON: SHOW --}}
                                        <a href="{{ route('versions.show', $v->id) }}"
                                           class="action-btn-circle bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-600 dark:hover:text-white"
                                           data-tooltip-key="btnShow">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>

                                        {{-- BUTTON: EDIT --}}


                                        {{-- BUTTON: DELETE --}}


                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-6 text-gray-400 uppercase text-[10px]" data-i18n="noVersions">
                                    Нет версий
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                </div>

                {{-- PAGINATION --}}
                <div class="mt-6">
                    {{ $versions->links() }}
                </div>

            </div>

        </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ============================================================
            // ЛОКАЛЬНЫЙ СЛОВАРЬ СТРАНИЦЫ ВЕРСИЙ
            // ============================================================
            const VERSIONS_TRANSLATIONS = {
                ru: {
                    pageTitle: 'Версия документ',
                    pageSubTitle: 'История изменений файлов',
                    btnAdd: '+ Добавить версию',
                    thDoc: 'Документ',
                    thVer: 'Версия',
                    thFile: 'Файл',
                    thDate: 'Дата',
                    thActions: 'Действия',
                    btnDownload: 'Скачать',
                    btnShow: 'Просмотр',
                    btnEdit: 'Редактировать',
                    btnDelete: 'Удалить',
                    noVersions: 'Нет версий',
                    confirmDelete: 'Удалить версию?'
                },
                tj: {
                    pageTitle: 'Нусхаи ҳуҷҷат',
                    pageSubTitle: 'Таърихи тағйироти файлҳо',
                    btnAdd: '+ Иловаи нусха',
                    thDoc: 'Ҳуҷҷат',
                    thVer: 'Нусха',
                    thFile: 'Файл',
                    thDate: 'Сана',
                    thActions: 'Амалиёт',
                    btnDownload: 'Боргирӣ',
                    btnShow: 'Намоиш',
                    btnEdit: 'Таҳрир',
                    btnDelete: 'Ҳазф',
                    noVersions: 'Нусха мавҷуд нест',
                    confirmDelete: 'Нусха ҳазф карда шавад?'
                },
                en: {
                    pageTitle: 'Document Versions',
                    pageSubTitle: 'File Change History',
                    btnAdd: '+ Add Version',
                    thDoc: 'Document',
                    thVer: 'Version',
                    thFile: 'File',
                    thDate: 'Date',
                    thActions: 'Actions',
                    btnDownload: 'Download',
                    btnShow: 'Show',
                    btnEdit: 'Edit',
                    btnDelete: 'Delete',
                    noVersions: 'No versions found',
                    confirmDelete: 'Delete version?'
                }
            };

            // ============================================================
            // ФУНКЦИЯ ПРИМЕНЕНИЯ ПЕРЕВОДОВ
            // ============================================================
            function applyVersionsTranslations(lang) {
                const dict = VERSIONS_TRANSLATIONS[lang] || VERSIONS_TRANSLATIONS.ru;

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

                // 4) Обрабатываем data-tooltip-key — ставим в title
                document.querySelectorAll('[data-tooltip-key]').forEach(el => {
                    const key = el.getAttribute('data-tooltip-key');
                    if (dict[key] !== undefined) {
                        el.setAttribute('title', dict[key]);
                    }
                });

                // 5) Обновляем обработчики confirm для форм удаления
                document.querySelectorAll('[data-confirm-i18n]').forEach(el => {
                    const key = el.getAttribute('data-confirm-i18n');
                    const message = dict[key] || 'Are you sure?';

                    // Клонируем элемент, чтобы сбросить старые обработчики
                    const newEl = el.cloneNode(true);
                    el.parentNode.replaceChild(newEl, el);

                    if (newEl.tagName === 'FORM') {
                        newEl.onsubmit = (e) => {
                            if (!confirm(message)) e.preventDefault();
                        };
                    } else {
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
            applyVersionsTranslations(initialLang);

            // ============================================================
            // 2. Слушаем событие смены языка от layouts/admin.blade.php
            // ============================================================
            window.addEventListener('docsign:lang-changed', (e) => {
                const lang = e.detail?.lang || 'ru';
                applyVersionsTranslations(lang);
            });

            // ============================================================
            // 3. Синхронизация между вкладками браузера
            // ============================================================
            window.addEventListener('storage', (e) => {
                if (e.key === 'docsign_lang' && e.newValue) {
                    applyVersionsTranslations(e.newValue);
                }
            });
        });
    </script>
@endsection
