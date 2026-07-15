<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DocManager Admin</title>

    <script>
        // Инициализация темы до загрузки контента
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @php
    $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
    $cssFile = $manifest['resources/css/app.css']['file'];
    $jsFile = $manifest['resources/js/app.js']['file'];
    @endphp

    <link rel="stylesheet" href="{{ asset('build/' . $cssFile) }}">
    <script src="{{ asset('build/' . $jsFile) }}" defer></script>
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/css/all.min.css') }}">

    <style>
        /* ===== ПОЛНАЯ АДАПТИВНОСТЬ ===== */

        /* Sidebar off-canvas для мобильных */
        .sidebar-mobile {
            position: fixed;
            top: 0;
            left: -100%;
            width: 280px;
            height: 100vh;
            z-index: 1000;
            transition: left 0.3s ease;
        }

        .sidebar-mobile.open {
            left: 0;
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .sidebar-overlay.open {
            opacity: 1;
            visibility: visible;
        }

        /* Кнопка hamburger */
        .hamburger-btn {
            display: none;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid rgba(156, 163, 175, 0.3);
            background: transparent;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .hamburger-btn:hover {
            background: rgba(156, 163, 175, 0.1);
        }

        .hamburger-btn i {
            font-size: 20px;
            color: #6b7280;
        }

        .dark .hamburger-btn i {
            color: #d1d5db;
        }

        /* Большие ноутбуки и маленькие десктопы (до 1024px) */
        @media (max-width: 1024px) {
            main {
                padding: 24px !important;
            }

            header {
                padding-left: 24px !important;
                padding-right: 24px !important;
            }
        }

        /* Планшеты (до 768px) */
        @media (max-width: 768px) {
            .hamburger-btn {
                display: flex;
            }

            main {
                padding: 20px !important;
            }

            header {
                padding-left: 16px !important;
                padding-right: 16px !important;
                height: 60px !important;
            }

            header .text-sm {
                font-size: 13px !important;
            }

            header .flex.items-center.gap-4 {
                gap: 12px !important;
            }

            #theme-toggle {
                width: 36px !important;
                height: 36px !important;
            }

            #theme-toggle i {
                font-size: 16px !important;
            }

            .border-l {
                padding-left: 12px !important;
            }

            .w-9 {
                width: 32px !important;
                height: 32px !important;
                font-size: 11px !important;
            }

            #lang-select {
                font-size: 11px !important;
                padding: 4px 8px !important;
            }
        }

        /* Большие телефоны (до 640px) */
        @media (max-width: 640px) {
            main {
                padding: 16px !important;
            }

            header {
                padding-left: 12px !important;
                padding-right: 12px !important;
                height: 56px !important;
            }

            header .text-sm {
                font-size: 12px !important;
            }

            header .flex.items-center.gap-4 {
                gap: 8px !important;
            }

            #theme-toggle {
                width: 34px !important;
                height: 34px !important;
            }

            #theme-toggle i {
                font-size: 15px !important;
            }

            .border-l {
                padding-left: 10px !important;
            }

            .w-9 {
                width: 30px !important;
                height: 30px !important;
                font-size: 10px !important;
            }

            #lang-select {
                font-size: 10px !important;
                padding: 3px 6px !important;
            }

            .hamburger-btn {
                width: 36px !important;
                height: 36px !important;
            }

            .hamburger-btn i {
                font-size: 18px !important;
            }

            .sidebar-mobile {
                width: 260px !important;
            }
        }

        /* Телефоны (до 480px) */
        @media (max-width: 480px) {
            main {
                padding: 12px !important;
            }

            header {
                padding-left: 10px !important;
                padding-right: 10px !important;
                height: 52px !important;
            }

            header .text-sm {
                font-size: 11px !important;
            }

            header .flex.items-center.gap-4 {
                gap: 6px !important;
            }

            #theme-toggle {
                width: 32px !important;
                height: 32px !important;
            }

            #theme-toggle i {
                font-size: 14px !important;
            }

            .border-l {
                padding-left: 8px !important;
                margin-left: 4px !important;
            }

            .w-9 {
                width: 28px !important;
                height: 28px !important;
                font-size: 9px !important;
            }

            #lang-select {
                font-size: 9px !important;
                padding: 2px 5px !important;
            }

            .hamburger-btn {
                width: 34px !important;
                height: 34px !important;
            }

            .hamburger-btn i {
                font-size: 17px !important;
            }

            .sidebar-mobile {
                width: 240px !important;
            }

            .sidebar-mobile nav a {
                padding: 10px 12px !important;
                font-size: 13px !important;
            }

            .sidebar-mobile .p-6 {
                padding: 16px !important;
            }

            .sidebar-mobile .font-bold {
                font-size: 15px !important;
            }
        }

        /* Очень маленькие телефоны (до 380px) */
        @media (max-width: 380px) {
            main {
                padding: 10px !important;
            }

            header {
                padding-left: 8px !important;
                padding-right: 8px !important;
                height: 48px !important;
            }

            header .text-sm {
                font-size: 10px !important;
            }

            header .flex.items-center.gap-4 {
                gap: 5px !important;
            }

            #theme-toggle {
                width: 30px !important;
                height: 30px !important;
            }

            #theme-toggle i {
                font-size: 13px !important;
            }

            .border-l {
                padding-left: 6px !important;
            }

            .w-9 {
                width: 26px !important;
                height: 26px !important;
                font-size: 9px !important;
            }

            #lang-select {
                font-size: 9px !important;
                padding: 2px 4px !important;
            }

            .hamburger-btn {
                width: 32px !important;
                height: 32px !important;
            }

            .hamburger-btn i {
                font-size: 16px !important;
            }

            .sidebar-mobile {
                width: 220px !important;
            }

            .sidebar-mobile nav a {
                padding: 9px 10px !important;
                font-size: 12px !important;
                gap: 8px !important;
            }

            .sidebar-mobile nav a i {
                font-size: 14px !important;
            }

            .sidebar-mobile .p-6 {
                padding: 14px !important;
                gap: 8px !important;
            }

            .sidebar-mobile .font-bold {
                font-size: 14px !important;
            }

            .sidebar-mobile img {
                width: 28px !important;
                height: 28px !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-dark-900 text-gray-900 dark:text-gray-100 antialiased transition-colors duration-300">

<!-- Overlay для мобильного меню -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar (десктоп) -->
    <aside class="w-64 bg-white dark:bg-dark-800 border-r dark:border-gray-700 hidden md:flex flex-col">
        <div class="p-6 flex items-center gap-3 border-b dark:border-gray-700">
            <!-- Ваше фото 67.png -->
            <img src="{{ asset('img/67.png') }}" alt="Logo" class="w-8 h-8 rounded-lg object-cover shadow-sm">
            <span class="font-bold text-lg tracking-tight">DocManager</span>
        </div>

        <nav class="flex-1 p-4 space-y-2">
            <a href="#" class="flex items-center gap-3 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium">
                <i class="bi bi-grid-fill"></i> <span data-i18n="dashboard">Панель</span>
            </a>
            <a href="#" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                <i class="bi bi-file-earmark-text"></i> <span data-i18n="documents">Документы</span>
            </a>
        </nav>
    </aside>

    <!-- Sidebar (мобильный) -->
    <aside class="sidebar-mobile bg-white dark:bg-dark-800 border-r dark:border-gray-700 flex flex-col md:hidden">
        <div class="p-6 flex items-center gap-3 border-b dark:border-gray-700">
            <img src="{{ asset('img/67.png') }}" alt="Logo" class="w-8 h-8 rounded-lg object-cover shadow-sm">
            <span class="font-bold text-lg tracking-tight">DocManager</span>
        </div>

        <nav class="flex-1 p-4 space-y-2">
            <a href="#" class="flex items-center gap-3 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium">
                <i class="bi bi-grid-fill"></i> <span data-i18n="dashboard">Панель</span>
            </a>
            <a href="#" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                <i class="bi bi-file-earmark-text"></i> <span data-i18n="documents">Документы</span>
            </a>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">

        <header class="h-16 bg-white dark:bg-dark-800 border-b dark:border-gray-700 flex items-center justify-between px-8">
            <div class="flex items-center gap-3">
                <!-- Hamburger кнопка (только мобильная) -->
                <button class="hamburger-btn md:hidden" id="hamburgerBtn">
                    <i class="bi bi-list"></i>
                </button>

                <div class="text-sm font-medium text-gray-500" data-i18n="pageTitle">Панель управления</div>
            </div>

            <div class="flex items-center gap-4">
                <select id="lang-select" class="bg-transparent border-none text-xs font-bold focus:ring-0 cursor-pointer">
                    <option value="ru">RU</option>
                    <option value="en">EN</option>
                    <option value="tj">TJ</option>
                </select>

                <button id="theme-toggle" class="w-10 h-10 flex items-center justify-center rounded-full border dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                    <i id="theme-icon" class="bi bi-moon text-gray-600 dark:text-gray-300"></i>
                </button>

                <div class="flex items-center gap-3 border-l dark:border-gray-700 pl-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold">{{ Auth::user()->name ?? 'Amir' }}</p>
                        <p class="text-[10px] text-gray-500">Administrator</p>
                    </div>
                    <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-xs">
                        {{ strtoupper(substr(Auth::user()->name ?? 'AD', 0, 2)) }}
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8">
            @yield('content')
        </main>
    </div>
</div>

<script>
    // --- УПРАВЛЕНИЕ ТЕМОЙ ---
    const btn = document.getElementById('theme-toggle');
    const icon = document.getElementById('theme-icon');
    const html = document.documentElement;

    function updateIcon() {
        if (html.classList.contains('dark')) {
            icon.classList.replace('bi-moon', 'bi-sun');
        } else {
            icon.classList.replace('bi-sun', 'bi-moon');
        }
    }
    updateIcon();

    btn.addEventListener('click', () => {
        html.classList.toggle('dark');
        localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        updateIcon();
        // Перезагружаем графики, если нужно (они сами подхватят тему через MutationObserver или просто при обновлении)
        window.dispatchEvent(new Event('resize'));
    });

    // --- УПРАВЛЕНИЕ ПЕРЕВОДАМИ ---
    const langSelect = document.getElementById('lang-select');

    // Устанавливаем текущий язык из хранилища
    const currentLang = localStorage.getItem('app-lang') || 'ru';
    langSelect.value = currentLang;

    langSelect.addEventListener('change', (e) => {
        const lang = e.target.value;
        localStorage.setItem('app-lang', lang);
        // Перезагружаем страницу, чтобы графики и PHP-блоки перерисовались с новым языком
        window.location.reload();
    });

    // --- УПРАВЛЕНИЕ МОБИЛЬНЫМ SIDEBAR ---
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const sidebarMobile = document.querySelector('.sidebar-mobile');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebarMobile.classList.add('open');
        sidebarOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebarMobile.classList.remove('open');
        sidebarOverlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    hamburgerBtn.addEventListener('click', openSidebar);
    sidebarOverlay.addEventListener('click', closeSidebar);

    // Закрытие sidebar при клике на ссылку
    sidebarMobile.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', closeSidebar);
    });

    // Закрытие sidebar при изменении размера экрана (если перешли на десктоп)
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            closeSidebar();
        }
    });
</script>
</body>
</html>