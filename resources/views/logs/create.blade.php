@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-8">

        {{-- Шапка --}}
        <div class="mb-8">
            <a href="{{ route('logs.index') }}" class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] hover:text-indigo-600 transition flex items-center gap-2 mb-2">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                <span data-i18n="backToList">Назад к списку</span>
            </a>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight" data-i18n="createLogTitle">Создать запись в логе</h1>
        </div>

        <div class="max-w-3xl">
            {{-- Вывод ошибок --}}
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 text-red-700 rounded-r-xl">
                    <p class="text-xs font-black uppercase tracking-widest mb-2" data-i18n="attention">Внимание, ошибки:</p>
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Форма --}}
            <form action="{{ route('logs.store') }}" method="POST" class="bg-white dark:bg-dark-800 p-8 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Выбор документа --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1" data-i18n="document">Документ</label>
                        <select name="document_id" class="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-dark-900 focus:bg-white dark:focus:bg-dark-800 focus:border-indigo-500 focus:ring-0 transition-all font-bold text-gray-700 dark:text-gray-300 text-sm outline-none cursor-pointer">
                            @foreach($documents as $document)
                                <option value="{{ $document->id }}">{{ $document->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Выбор пользователя --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1" data-i18n="user">Пользователь</label>
                        <select name="user_id" class="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-dark-900 focus:bg-white dark:focus:bg-dark-800 focus:border-indigo-500 focus:ring-0 transition-all font-bold text-gray-700 dark:text-gray-300 text-sm outline-none cursor-pointer">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $user->id == auth()->id() ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Действие --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1" data-i18n="actionType">Тип действия</label>
                    <select name="action" class="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-dark-900 focus:bg-white dark:focus:bg-dark-800 focus:border-indigo-500 focus:ring-0 transition-all font-bold text-gray-700 dark:text-gray-300 text-sm outline-none cursor-pointer">
                        <option value="created" data-i18n="actionCreated">🟢 Создание</option>
                        <option value="updated" data-i18n="actionUpdated">🔵 Обновление</option>
                        <option value="deleted" data-i18n="actionDeleted">🔴 Удаление</option>
                        <option value="signed" data-i18n="actionSigned">🖋️ Подписание</option>
                        <option value="status_changed" data-i18n="actionStatus">⚙️ Смена статуса</option>
                    </select>
                </div>

                {{-- Описание --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1" data-i18n="descriptionLabel">Описание события</label>
                    <textarea name="description" rows="4" data-i18n-placeholder="descriptionPlaceholder" placeholder="Введите подробности..."
                              class="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-dark-900 focus:bg-white dark:focus:bg-dark-800 focus:border-indigo-500 focus:ring-0 transition-all font-bold text-gray-700 dark:text-gray-300 text-sm outline-none resize-none"></textarea>
                </div>

                {{-- Кнопки --}}
                <div class="pt-4 flex items-center gap-4">
                    <button type="submit" class="flex-1 bg-gray-900 dark:bg-indigo-600 hover:bg-black dark:hover:bg-indigo-700 text-white px-8 py-4 rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] transition-all shadow-xl shadow-gray-200 dark:shadow-none">
                        <span data-i18n="saveRecord">Сохранить запись</span>
                    </button>
                    <a href="{{ route('logs.index') }}" class="px-8 py-4 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] hover:text-red-500 transition">
                        <span data-i18n="cancel">Отмена</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const translations = {
                ru: {
                    backToList: "Назад к списку",
                    createLogTitle: "Создать запись в логе",
                    attention: "Внимание, ошибки:",
                    document: "Документ",
                    user: "Пользователь",
                    actionType: "Тип действия",
                    actionCreated: "🟢 Создание",
                    actionUpdated: "🔵 Обновление",
                    actionDeleted: "🔴 Удаление",
                    actionSigned: "🖋️ Подписание",
                    actionStatus: "⚙️ Смена статуса",
                    descriptionLabel: "Описание события",
                    descriptionPlaceholder: "Введите подробности...",
                    saveRecord: "Сохранить запись",
                    cancel: "Отмена"
                },
                tj: {
                    backToList: "Бозгашт ба рӯйхат",
                    createLogTitle: "Сабти лог сохтан",
                    attention: "Диққат, хатогиҳо:",
                    document: "Ҳуҷҷат",
                    user: "Корбар",
                    actionType: "Намуди амал",
                    actionCreated: "🟢 Сохтан",
                    actionUpdated: "🔵 Навсозӣ",
                    actionDeleted: "🔴 Нест кардан",
                    actionSigned: "🖋️ Имзо кардан",
                    actionStatus: "⚙️ Ивази статус",
                    descriptionLabel: "Тавсифи ҳодиса",
                    descriptionPlaceholder: "Тафсилотро ворид кунед...",
                    saveRecord: "Захира кардани сабт",
                    cancel: "Бекор кардан"
                },
                en: {
                    backToList: "Back to list",
                    createLogTitle: "Create log entry",
                    attention: "Attention, errors:",
                    document: "Document",
                    user: "User",
                    actionType: "Action Type",
                    actionCreated: "🟢 Created",
                    actionUpdated: "🔵 Updated",
                    actionDeleted: "🔴 Deleted",
                    actionSigned: "🖋️ Signed",
                    actionStatus: "⚙️ Status Change",
                    descriptionLabel: "Event Description",
                    descriptionPlaceholder: "Enter details...",
                    saveRecord: "Save Record",
                    cancel: "Cancel"
                }
            };

            const lang = localStorage.getItem('app-lang') || 'ru';
            const t = translations[lang];

            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (t[key]) el.textContent = t[key];
            });

            document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
                const key = el.getAttribute('data-i18n-placeholder');
                if (t[key]) el.placeholder = t[key];
            });
        });
    </script>
@endsection
