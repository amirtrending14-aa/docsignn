@extends('layouts.admin')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Темный фон как на скриншоте --}}
    <div class="min-h-screen bg-[#0f172a] px-4 py-12" style="font-family: Inter, sans-serif;">

        <div class="max-w-2xl mx-auto">

            {{-- HEADER: Стиль как в таблице --}}
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-blue-500 rounded-full shadow-[0_0_10px_rgba(59,130,246,0.5)]"></span>
                        <span class="text-blue-400 ml-1">v{{ $version->version }}</span>
                    </h1>

                    <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-1" data-i18n="editSubTitle">
                        Update file or change version data
                    </p>
                </div>

                <a href="{{ route('versions.index') }}"
                   class="text-[11px] font-bold uppercase tracking-wider text-gray-400 hover:text-white transition flex items-center gap-2">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    <span data-i18n="backBtn">Back</span>
                </a>
            </div>

            {{-- FORM CARD: Белая панель с закруглениями как в таблице --}}
            <div class="bg-white rounded-[20px] shadow-xl overflow-hidden border border-gray-100">
                <form id="editVersionForm" action="{{ route('versions.update', $version->id) }}" method="POST" enctype="multipart/form-data" class="p-8">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">

                        {{-- DOCUMENT INFO --}}
                        <div class="grid grid-cols-1 gap-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider" data-i18n="labelDoc">Document</label>
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 leading-none">
                                        {{ $version->document->title ?? 'Deleted' }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 mt-1">ID: {{ $version->document_id }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- CURRENT FILE --}}
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2" data-i18n="labelCurrentFile">Current File</label>
                            <a href="{{ asset('storage/' . $version->file_path) }}"
                               download
                               class="inline-flex items-center px-4 py-2 bg-pink-50 text-pink-600 rounded-lg text-[11px] font-bold uppercase tracking-wider hover:bg-pink-100 transition-all border border-pink-100"
                               data-i18n="btnDownload">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download Current PDF
                            </a>
                        </div>

                        {{-- UPLOAD NEW FILE --}}
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2" data-i18n="labelNewFile">Replace File (Optional)</label>
                            <input type="file"
                                   name="file_path"
                                   class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-blue-600 file:text-white hover:file:bg-blue-700 transition cursor-pointer bg-gray-50 rounded-xl border border-gray-200 p-1">
                        </div>

                        {{-- CHANGE SUMMARY --}}

                    </div>

                    {{-- FOOTER BUTTONS --}}
                    <div class="flex items-center justify-end gap-4 mt-10 pt-6 border-t border-gray-100">
                        <a href="{{ route('versions.index') }}"
                           class="text-[11px] font-bold uppercase tracking-widest text-gray-400 hover:text-gray-600 transition" data-i18n="btnCancel">
                            Cancel
                        </a>

                        <button type="submit"
                                class="px-5 py-2 bg-blue-600 text-white rounded-lg text-[10px] font-bold uppercase tracking-wider hover:bg-blue-700 shadow-md shadow-blue-900/20 transition-all transform active:scale-95"
                                data-i18n="btnSave">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ============================================================
        // ЛОКАЛЬНЫЙ СЛОВАРЬ СТРАНИЦЫ РЕДАКТИРОВАНИЯ ВЕРСИИ
        // ============================================================
        const VERSION_EDIT_TRANSLATIONS = {
            ru: {
                backBtn: 'Назад',
                editTitle: 'Редактирование версии',
                editSubTitle: 'ОБНОВИТЕ ФАЙЛ ИЛИ ИЗМЕНИТЕ ДАННЫЕ',
                labelDoc: 'Документ',
                labelCurrentFile: 'Текущий файл',
                btnDownload: 'СКАЧАТЬ ТЕКУЩИЙ PDF',
                labelNewFile: 'ЗАМЕНИТЬ ФАЙЛ (НЕОБЯЗАТЕЛЬНО)',
                labelSummary: 'ОПИСАНИЕ ИЗМЕНЕНИЙ',
                placeholderSummary: 'Что именно изменилось в этой версии?',
                btnCancel: 'ОТМЕНА',
                btnSave: 'СОХРАНИТЬ ИЗМЕНЕНИЯ',
                confirmMsg: 'Вы уверены, что хотите сохранить изменения?'
            },
            tj: {
                backBtn: 'Бозгашт',
                editTitle: 'Таҳрири нусха',
                editSubTitle: 'ТАЪРИХИ ТАҒЙИРОТИ ФАЙЛҲО',
                labelDoc: 'Ҳуҷҷат',
                labelCurrentFile: 'Файли ҷорӣ',
                btnDownload: 'БОРГИРИИ PDF',
                labelNewFile: 'ИВАЗИ ФАЙЛ (ИХТИЁРӢ)',
                labelSummary: 'ТАВСИФИ ТАҒЙИРОТ',
                placeholderSummary: 'Дар ин нусха чӣ тағйир ёфт?',
                btnCancel: 'БЕКОР КАРДАН',
                btnSave: 'ЗАХИРА КАРДАН',
                confirmMsg: 'Шумо мутмаин ҳастед?'
            },
            en: {
                backBtn: 'Back',
                editTitle: 'Edit Version',
                editSubTitle: 'FILE CHANGE HISTORY',
                labelDoc: 'Document',
                labelCurrentFile: 'Current File',
                btnDownload: 'DOWNLOAD CURRENT PDF',
                labelNewFile: 'REPLACE FILE (OPTIONAL)',
                labelSummary: 'CHANGE SUMMARY',
                placeholderSummary: 'What changed?',
                btnCancel: 'CANCEL',
                btnSave: 'SAVE CHANGES',
                confirmMsg: 'Are you sure?'
            }
        };

        // ============================================================
        // ФУНКЦИЯ ПРИМЕНЕНИЯ ПЕРЕВОДОВ
        // ============================================================
        function applyVersionEditTranslations(lang) {
            const dict = VERSION_EDIT_TRANSLATIONS[lang] || VERSION_EDIT_TRANSLATIONS.ru;

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

            // 4) Обновляем placeholder для textarea (если есть)
            const textarea = document.getElementById('change_summary');
            if (textarea && dict.placeholderSummary) {
                textarea.placeholder = dict.placeholderSummary;
            }

            // 5) Обновляем обработчик confirm для формы
            const form = document.getElementById('editVersionForm');
            if (form) {
                // Клонируем форму, чтобы сбросить старые обработчики
                const newForm = form.cloneNode(true);
                form.parentNode.replaceChild(newForm, form);

                // Вешаем новый обработчик с актуальным переводом
                newForm.addEventListener('submit', function(e) {
                    if (!confirm(dict.confirmMsg)) {
                        e.preventDefault();
                    }
                });
            }
        }

        // ============================================================
        // 1. Применяем сразу при загрузке
        // ============================================================
        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applyVersionEditTranslations(initialLang);

        // ============================================================
        // 2. Слушаем событие смены языка от layouts/admin.blade.php
        // ============================================================
        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applyVersionEditTranslations(lang);
        });

        // ============================================================
        // 3. Синхронизация между вкладками браузера
        // ============================================================
        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applyVersionEditTranslations(e.newValue);
            }
        });
    });
</script>
@endsection
