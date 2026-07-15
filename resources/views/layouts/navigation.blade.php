<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm">
    <style>
        /* ===== УЛУЧШЕНИЯ АДАПТИВНОСТИ ===== */

        /* Базовые стили nav */
        nav {
            transition: all 0.3s ease;
        }

        /* Плавная анимация мобильного меню */
        nav [x-show] {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        /* Улучшенные стили для hamburger кнопки */
        nav button[\\@click="open = ! open"] {
            transition: all 0.2s ease;
        }

        nav button[\\@click="open = ! open"]:active {
            transform: scale(0.95);
        }

        /* Большие ноутбуки и маленькие десктопы (до 1024px) */
        @media (max-width: 1024px) {
            nav .max-w-7xl {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }

            nav .hidden.space-x-8 {
                gap: 1.5rem;
            }
        }

        /* Планшеты (до 768px) */
        @media (max-width: 768px) {
            nav .h-16 {
                height: 60px;
            }

            nav .max-w-7xl {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            /* Уменьшаем логотип */
            nav x-application-logo {
                height: 2rem;
            }

            /* Hamburger кнопка */
            nav button[\\@click="open = ! open"] {
                padding: 0.5rem;
            }

            nav button[\\@click="open = ! open"] svg {
                width: 1.25rem;
                height: 1.25rem;
            }

            /* Мобильное меню */
            nav [x-show] .pt-2 {
                padding-top: 0.5rem;
            }

            nav [x-show] .pb-3 {
                padding-bottom: 0.75rem;
            }

            nav [x-show] .space-y-1 > * {
                font-size: 0.875rem;
                padding: 0.5rem 1rem;
            }

            /* Информация о пользователе */
            nav [x-show] .px-4 {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            nav [x-show] .font-medium.text-base {
                font-size: 0.95rem;
            }

            nav [x-show] .font-medium.text-sm {
                font-size: 0.8rem;
            }
        }

        /* Большие телефоны (до 640px) */
        @media (max-width: 640px) {
            nav .h-16 {
                height: 56px;
            }

            nav .max-w-7xl {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            nav x-application-logo {
                height: 1.75rem;
            }

            nav button[\\@click="open = ! open"] {
                padding: 0.4rem;
                border-radius: 0.375rem;
            }

            nav button[\\@click="open = ! open"] svg {
                width: 1.125rem;
                height: 1.125rem;
            }

            /* Мобильное меню */
            nav [x-show] .pt-2 {
                padding-top: 0.4rem;
            }

            nav [x-show] .pb-3 {
                padding-bottom: 0.6rem;
            }

            nav [x-show] .space-y-1 > * {
                font-size: 0.85rem;
                padding: 0.45rem 0.9rem;
            }

            nav [x-show] .px-4 {
                padding-left: 0.9rem;
                padding-right: 0.9rem;
            }

            nav [x-show] .font-medium.text-base {
                font-size: 0.9rem;
            }

            nav [x-show] .font-medium.text-sm {
                font-size: 0.75rem;
            }

            nav [x-show] .mt-3 {
                margin-top: 0.6rem;
            }

            nav [x-show] .pt-4 {
                padding-top: 0.9rem;
            }

            nav [x-show] .pb-1 {
                padding-bottom: 0.6rem;
            }
        }

        /* Телефоны (до 480px) */
        @media (max-width: 480px) {
            nav .h-16 {
                height: 52px;
            }

            nav .max-w-7xl {
                padding-left: 0.6rem;
                padding-right: 0.6rem;
            }

            nav x-application-logo {
                height: 1.5rem;
            }

            nav button[\\@click="open = ! open"] {
                padding: 0.35rem;
            }

            nav button[\\@click="open = ! open"] svg {
                width: 1rem;
                height: 1rem;
            }

            /* Мобильное меню */
            nav [x-show] .space-y-1 > * {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }

            nav [x-show] .px-4 {
                padding-left: 0.8rem;
                padding-right: 0.8rem;
            }

            nav [x-show] .font-medium.text-base {
                font-size: 0.85rem;
            }

            nav [x-show] .font-medium.text-sm {
                font-size: 0.7rem;
            }
        }

        /* Очень маленькие телефоны (до 380px) */
        @media (max-width: 380px) {
            nav .h-16 {
                height: 48px;
            }

            nav .max-w-7xl {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            nav x-application-logo {
                height: 1.25rem;
            }

            nav button[\\@click="open = ! open"] {
                padding: 0.3rem;
            }

            nav button[\\@click="open = ! open"] svg {
                width: 0.9rem;
                height: 0.9rem;
            }

            /* Мобильное меню */
            nav [x-show] .space-y-1 > * {
                font-size: 0.75rem;
                padding: 0.35rem 0.7rem;
            }

            nav [x-show] .px-4 {
                padding-left: 0.7rem;
                padding-right: 0.7rem;
            }

            nav [x-show] .font-medium.text-base {
                font-size: 0.8rem;
            }

            nav [x-show] .font-medium.text-sm {
                font-size: 0.65rem;
            }
        }

        /* Улучшения для dropdown меню (десктоп) */
        @media (min-width: 640px) {
            nav x-dropdown [x-slot="content"] {
                transition: opacity 0.2s ease, transform 0.2s ease;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            }

            nav x-dropdown-link {
                transition: all 0.15s ease;
            }

            nav x-dropdown-link:hover {
                background-color: rgba(243, 244, 246, 0.8);
                transform: translateX(2px);
            }
        }

        /* Темная тема (если используется) */
        @media (prefers-color-scheme: dark) {
            nav.bg-white {
                background-color: rgba(255, 255, 255, 0.95);
            }
        }

        /* Плавная прокрутка */
        html {
            scroll-behavior: smooth;
        }

        /* Улучшения для ссылок */
        nav a {
            transition: color 0.2s ease;
        }

        /* Улучшения для кнопок */
        nav button {
            cursor: pointer;
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ url('/') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Панель') }}
                    </x-nav-link>
                    {{-- Добавь свои ссылки сюда, например на уведомления --}}
                    <x-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                        Уведомления
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            {{-- ИСПРАВЛЕНО: добавлена защита ?? --}}
                            <div>{{ Auth::user()->name ?? 'Пользователь' }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')"> Профиль </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Выйти
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
                @else
                <div class="space-x-4">
                    <a href="{{ route('login') }}" class="text-sm text-gray-700 underline">Войти</a>
                    <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 underline">Регистрация</a>
                </div>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            @auth
            <div class="px-4">
                {{-- ИСПРАВЛЕНО: Безопасный вывод --}}
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')"> Профиль </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        Выйти
                    </x-responsive-nav-link>
                </form>
            </div>
            @else
            <div class="px-4 space-y-2">
                <x-responsive-nav-link :href="route('login')"> Войти </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('register')"> Регистрация </x-responsive-nav-link>
            </div>
            @endauth
        </div>
    </div>
</nav>