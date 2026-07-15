{{-- resources/views/settings/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Заголовок страницы --}}
        <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Настройки профиля и ЭДО
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Управление личными данными, уведомлениями и параметрами интерфейса
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
            ID: {{ auth()->id() }}
        </span>
        </header>

        {{-- Сообщения об успехе --}}
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 text-green-700 dark:text-green-300 rounded-r-xl shadow-sm flex items-start justify-between">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="ml-4 text-green-500 hover:text-green-700 dark:hover:text-green-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        <div class="space-y-6">

            {{-- ======================== БЛОК 1: Электронная подпись ======================== --}}
            <section class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-start mb-6">
                    <div class="p-2.5 bg-amber-50 dark:bg-amber-900/20 rounded-xl mr-4 shrink-0">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Электронная графическая подпись</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Используется для автоматического вшивания в подписываемые PDF-документы</p>
                    </div>
                </div>

                <form action="{{ route('settings.signature.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="flex flex-col lg:flex-row items-stretch lg:items-center gap-6 p-5 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700">

                        {{-- Превью подписи --}}
                        <div class="relative group shrink-0">
                            @if(auth()->user()->signature_path)
                                <img src="{{ asset('storage/' . auth()->user()->signature_path) }}"
                                     alt="Current signature"
                                     class="h-20 w-40 object-contain border rounded-lg p-2 bg-white dark:bg-gray-800 shadow-sm transition-opacity group-hover:opacity-75">
                                <span class="absolute bottom-1 right-1 text-[10px] bg-black/60 text-white px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition">Текущая</span>
                            @else
                                <div class="h-20 w-40 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex flex-col items-center justify-center text-gray-400 bg-white dark:bg-gray-800">
                                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span class="text-[10px] font-medium">Нет файла</span>
                                </div>
                            @endif
                        </div>

                        {{-- Поля ввода --}}
                        <div class="flex-1 min-w-0 space-y-2">
                            <label for="signature" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Загрузить новую подпись
                            </label>
                            <input type="file"
                                   id="signature"
                                   name="signature"
                                   accept="image/png"
                                   required
                                   class="block w-full text-sm text-gray-500 dark:text-gray-400
                                      file:mr-4 file:py-2 file:px-4 file:rounded-xl
                                      file:border-0 file:text-xs file:font-semibold
                                      file:bg-gray-900 file:text-white dark:file:bg-white dark:file:text-gray-900
                                      hover:file:opacity-90 transition cursor-pointer
                                      file:disabled:opacity-50">
                            <p class="text-xs text-gray-400">Только PNG с прозрачным фоном, макс. 2 МБ</p>
                        </div>

                        {{-- Кнопка --}}
                        <button type="submit"
                                class="shrink-0 inline-flex items-center justify-center px-6 py-3 bg-amber-500 hover:bg-amber-600
                                   text-white text-xs font-bold uppercase tracking-widest rounded-xl
                                   transition shadow-lg shadow-amber-200/50 dark:shadow-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                            Обновить
                        </button>
                    </div>
                </form>
            </section>

            {{-- ======================== БЛОК 2: Общие настройки (форма) ======================== --}}
            <form action="{{ route('settings.general.update') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Скрытые поля для Alpine.js --}}
                <input type="hidden" name="language" id="input-language" value="{{ auth()->user()->language ?? 'ru' }}">
                <input type="hidden" name="theme_color" id="input-theme-color" value="{{ auth()->user()->theme_color ?? '#4f46e5' }}">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- Карточка: Уведомления --}}
                    <section class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-5 flex items-center">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2.5"></span>
                            Уведомления
                        </h3>

                        <div class="space-y-4">
                            {{-- Email --}}
                            <label class="flex items-center justify-between cursor-pointer group">
                                <div class="pr-4">
                                    <span class="block text-sm font-medium text-gray-700 dark:text-gray-200 group-hover:text-blue-600 transition">Email-отчеты</span>
                                    <span class="text-xs text-gray-400">Еженедельная сводка и алерты</span>
                                </div>
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" name="email_notifications" value="1"
                                           class="sr-only peer"
                                        {{ auth()->user()->email_notifications ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer
                                            peer-focus:ring-2 peer-focus:ring-blue-500 peer-focus:ring-offset-2 dark:peer-focus:ring-offset-gray-800
                                            peer-checked:after:translate-x-full peer-checked:after:border-white
                                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                            after:bg-white after:border-gray-300 after:border after:rounded-full
                                            after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                    </div>
                                </div>
                            </label>

                            {{-- Telegram --}}
                            <label class="flex items-center justify-between cursor-pointer group">
                                <div class="pr-4">
                                    <span class="block text-sm font-medium text-gray-700 dark:text-gray-200 group-hover:text-blue-600 transition">Telegram Bot</span>
                                    <span class="text-xs text-gray-400">Мгновенные уведомления в чат</span>
                                </div>
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" name="tg_notifications" value="1"
                                           class="sr-only peer"
                                        {{ auth()->user()->tg_notifications ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer
                                            peer-focus:ring-2 peer-focus:ring-blue-500 peer-focus:ring-offset-2 dark:peer-focus:ring-offset-gray-800
                                            peer-checked:after:translate-x-full peer-checked:after:border-white
                                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                            after:bg-white after:border-gray-300 after:border after:rounded-full
                                            after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                    </div>
                                </div>
                            </label>
                        </div>
                    </section>

                    {{-- Карточка: Интерфейс --}}
                    <section class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-5 flex items-center">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2.5"></span>
                            Интерфейс
                        </h3>

                        <div class="space-y-6" x-data="{
                        lang: '{{ auth()->user()->language ?? 'ru' }}',
                        colors: {{ Js::from(['#4f46e5','#0ea5e9','#22c55e','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6','#f97316','#6366f1']) }},
                        currentColor: '{{ auth()->user()->theme_color ?? '#4f46e5' }}',
                        init() {
                            $watch('lang', val => document.getElementById('input-language').value = val);
                            $watch('currentColor', val => document.getElementById('input-theme-color').value = val);
                        }
                    }">

                            {{-- Выбор языка --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Язык системы</label>
                                <div class="relative" @click.away="open = false">
                                    <button type="button" @click="open = !open"
                                            class="flex items-center justify-between w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900
                                               rounded-xl border border-gray-200 dark:border-gray-700
                                               hover:border-amber-500 dark:hover:border-amber-400 transition-all outline-none focus:ring-2 focus:ring-amber-500">
                                        <div class="flex items-center gap-3">
                                            <img :src="`https://flagcdn.com/w20/${lang === 'en' ? 'us' : (lang === 'tg' ? 'tj' : 'ru')}.png`"
                                                 class="w-5 h-auto rounded shadow-sm" alt="">
                                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200"
                                                  x-text="{en: 'English', ru: 'Русский', tg: 'Тоҷикӣ'}[lang]"></span>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <div x-show="open"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="absolute z-10 mt-2 w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 py-1">

                                        <template x-for="[code, label, flag] in [
                                        ['en', 'English', 'us'],
                                        ['ru', 'Русский', 'ru'],
                                        ['tg', 'Тоҷикӣ', 'tj']
                                    ]">
                                            <button type="button"
                                                    @click="lang = code; open = false"
                                                    class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700 transition text-left">
                                                <img :src="`https://flagcdn.com/w20/${flag}.png`" class="w-4 h-auto rounded" alt="">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="label"></span>
                                                <span x-show="lang === code" class="ml-auto text-amber-500">✓</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Палитра цветов --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Цветовая схема</label>
                                <div class="grid grid-cols-5 gap-2.5">
                                    <template x-for="color in colors">
                                        <button type="button"
                                                @click="currentColor = color"
                                                class="relative w-8 h-8 rounded-lg transition-all hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 dark:focus:ring-gray-600"
                                                :style="`background: ${color}`"
                                                :class="currentColor === color ? 'ring-2 ring-offset-2 ring-gray-900 dark:ring-white scale-105' : ''"
                                                :aria-pressed="currentColor === color"
                                                :aria-label="`Выбрать цвет ${color}`">
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- Кнопка сохранения --}}
                <div class="flex justify-end pt-2">
                    <button type="submit"
                            class="inline-flex items-center px-8 py-3 bg-gray-900 dark:bg-white dark:text-gray-900 text-white
                               text-xs font-bold uppercase tracking-widest rounded-xl
                               hover:opacity-90 transition shadow-lg focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Сохранить изменения
                    </button>
                </div>
            </form>

            {{-- ======================== БЛОК 3: Информация об аккаунте ======================== --}}
            <section class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-2xl border border-gray-200 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Информация об аккаунте</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                    <div class="flex items-center gap-4 p-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm">
                        <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-400 font-medium uppercase">Телефон</p>
                            <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white truncate">
                                +992 {{ auth()->user()->phone ?? 'не указан' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-400 font-medium uppercase">Роль</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ auth()->user()->role_name ?? auth()->user()->role ?? 'Пользователь' }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>
@endsection
