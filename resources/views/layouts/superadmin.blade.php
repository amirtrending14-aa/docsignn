<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin Panel')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-deep: #000000;
            --bg-surface: #0a0a0f;
            --bg-elevated: #111118;
            --neon-red: #ff2d55;
            --neon-blue: #00d4ff;
            --neon-purple: #b14dff;
            --neon-green: #00ff88;
            --neon-orange: #ff9500;
            --neon-pink: #ff2d95;
            --text-primary: #ffffff;
            --text-secondary: #a1a1aa;
            --text-muted: #52525b;
        }

        * {
            font-family: 'Inter', system-ui, sans-serif;
            box-sizing: border-box;
        }

        body {
            background: var(--bg-deep);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* ===== АНИМИРОВАННЫЙ ФОН С НЕОНОВЫМИ ПЯТНАМИ ===== */
        .bg-aurora {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .bg-aurora::before {
            content: '';
            position: absolute;
            width: 800px;
            height: 800px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 45, 85, 0.15) 0%, transparent 70%);
            top: -200px;
            right: -200px;
            filter: blur(80px);
            animation: float1 20s ease-in-out infinite;
        }

        .bg-aurora::after {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 212, 255, 0.12) 0%, transparent 70%);
            bottom: -150px;
            left: -150px;
            filter: blur(80px);
            animation: float2 25s ease-in-out infinite;
        }

        @keyframes float1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-100px, 100px) scale(1.1); }
        }

        @keyframes float2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(100px, -100px) scale(1.15); }
        }

        /* ===== БОКОВАЯ ПАНЕЛЬ ===== */
        .sidebar {
            background: rgba(10, 10, 15, 0.95);
            backdrop-filter: blur(30px) saturate(180%);
            border-right: 1px solid rgba(255, 45, 85, 0.2);
            width: 280px;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 40;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 4px 0 24px rgba(255, 45, 85, 0.1);
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 1px;
            height: 100%;
            background: linear-gradient(180deg,
                transparent 0%,
                var(--neon-red) 20%,
                var(--neon-purple) 50%,
                var(--neon-blue) 80%,
                transparent 100%);
            opacity: 0.6;
            animation: borderGlow 3s ease-in-out infinite;
        }

        @keyframes borderGlow {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 0.8; }
        }

        .sidebar-header {
            padding: 1.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
        }

        .logo-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.85rem;
            background: linear-gradient(135deg, var(--neon-red) 0%, #991b1b 100%);
            border-radius: 8px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #fff;
            box-shadow: 0 4px 16px rgba(255, 45, 85, 0.4),
                        inset 0 1px 0 rgba(255, 255, 255, 0.2);
            animation: badgePulse 2s ease-in-out infinite;
        }

        @keyframes badgePulse {
            0%, 100% { box-shadow: 0 4px 16px rgba(255, 45, 85, 0.4); }
            50% { box-shadow: 0 4px 24px rgba(255, 45, 85, 0.6); }
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.85rem 1.25rem;
            margin: 0.3rem 0.85rem;
            border-radius: 10px;
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 0;
            background: linear-gradient(180deg, var(--neon-red), var(--neon-purple));
            transition: height 0.3s;
            border-radius: 0 2px 2px 0;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            transform: translateX(4px);
        }

        .nav-item:hover::before {
            height: 60%;
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgba(255, 45, 85, 0.15), rgba(177, 77, 255, 0.1));
            color: var(--text-primary);
            border: 1px solid rgba(255, 45, 85, 0.3);
            box-shadow: 0 4px 16px rgba(255, 45, 85, 0.2),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .nav-item.active::before {
            height: 60%;
            box-shadow: 0 0 12px var(--neon-red);
        }

        .nav-item svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            transition: all 0.3s;
        }

        .nav-item:hover svg {
            filter: drop-shadow(0 0 8px currentColor);
        }

        /* ===== ОСНОВНОЙ КОНТЕНТ ===== */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        /* ===== ВЕРХНЯЯ ПАНЕЛЬ ===== */
        .top-bar {
            background: rgba(15, 15, 20, 0.85);
            backdrop-filter: blur(30px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 1.25rem 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }

        .top-bar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg,
                transparent 0%,
                var(--neon-blue) 20%,
                var(--neon-purple) 50%,
                var(--neon-pink) 80%,
                transparent 100%);
            opacity: 0.6;
        }

        /* ===== КАРТОЧКИ ===== */
        .card {
            background: rgba(15, 15, 20, 0.85);
            backdrop-filter: blur(30px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 1.75rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            opacity: 0;
            transition: opacity 0.4s;
        }

        .card:hover {
            border-color: rgba(255, 45, 85, 0.3);
            transform: translateY(-4px);
            box-shadow: 0 12px 48px rgba(255, 45, 85, 0.15),
                        0 4px 16px rgba(0, 0, 0, 0.4);
        }

        .card:hover::before {
            opacity: 1;
        }

        /* ===== СТАТИСТИКА ===== */
        .stat-card {
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent-from), var(--accent-to));
            box-shadow: 0 0 20px var(--accent-from);
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top left, var(--accent-from), transparent 50%);
            opacity: 0.05;
            pointer-events: none;
        }

        /* ===== ТАБЛИЦЫ ===== */
        .table-wrap {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: rgba(255, 255, 255, 0.03);
            padding: 1rem 1.25rem;
            text-align: left;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        table td {
            padding: 1rem 1.25rem;
            font-size: 0.9rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--text-secondary);
        }

        table tr {
            transition: all 0.3s;
        }

        table tr:hover td {
            background: rgba(255, 45, 85, 0.05);
            color: var(--text-primary);
        }

        /* ===== КНОПКИ ===== */
        .btn-primary {
            background: linear-gradient(135deg, var(--neon-red) 0%, #991b1b 100%);
            color: var(--text-primary);
            padding: 0.65rem 1.35rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 16px rgba(255, 45, 85, 0.3),
                        inset 0 1px 0 rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 45, 85, 0.5),
                        inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-ghost {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-secondary);
            padding: 0.55rem 1rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
            border-color: rgba(255, 45, 85, 0.3);
            box-shadow: 0 0 16px rgba(255, 45, 85, 0.2);
        }

        /* ===== БЕЙДЖИ ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.7rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-online {
            background: rgba(0, 255, 136, 0.15);
            color: var(--neon-green);
            border: 1px solid rgba(0, 255, 136, 0.3);
            box-shadow: 0 0 12px rgba(0, 255, 136, 0.2);
        }

        .badge-offline {
            background: rgba(113, 113, 122, 0.15);
            color: var(--text-secondary);
            border: 1px solid rgba(113, 113, 122, 0.3);
        }

        .badge-admin {
            background: rgba(255, 149, 0, 0.15);
            color: var(--neon-orange);
            border: 1px solid rgba(255, 149, 0, 0.3);
            box-shadow: 0 0 12px rgba(255, 149, 0, 0.2);
        }

        .badge-super {
            background: linear-gradient(135deg, rgba(255, 45, 85, 0.2), rgba(177, 77, 255, 0.15));
            color: #fca5a5;
            border: 1px solid rgba(255, 45, 85, 0.4);
            box-shadow: 0 0 16px rgba(255, 45, 85, 0.3);
        }

        /* ===== АВАТАР ===== */
        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3f3f46, #27272a);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--text-primary);
            overflow: hidden;
            border: 2px solid rgba(255, 45, 85, 0.3);
            box-shadow: 0 0 16px rgba(255, 45, 85, 0.2);
            transition: all 0.3s;
        }

        .avatar:hover {
            border-color: var(--neon-red);
            box-shadow: 0 0 24px rgba(255, 45, 85, 0.4);
            transform: scale(1.05);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ===== АЛЕРТЫ ===== */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            border: 1px solid;
            position: relative;
            overflow: hidden;
        }

        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            color: var(--neon-green);
            border-color: rgba(0, 255, 136, 0.3);
        }

        .alert-success::before {
            background: var(--neon-green);
            box-shadow: 0 0 12px var(--neon-green);
        }

        .alert-error {
            background: rgba(255, 45, 85, 0.1);
            color: #fca5a5;
            border-color: rgba(255, 45, 85, 0.3);
        }

        .alert-error::before {
            background: var(--neon-red);
            box-shadow: 0 0 12px var(--neon-red);
        }

        /* ===== ФОРМЫ ===== */
        input, select, textarea {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: var(--text-primary) !important;
            transition: all 0.3s !important;
        }

        input:focus, select:focus, textarea:focus {
            outline: none !important;
            border-color: var(--neon-red) !important;
            box-shadow: 0 0 0 3px rgba(255, 45, 85, 0.1),
                        0 0 16px rgba(255, 45, 85, 0.2) !important;
        }

        /* ===== МОБИЛЬНАЯ АДАПТАЦИЯ ===== */

        /* Overlay для мобильного меню */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            z-index: 39;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Маленькие десктопы (до 1200px) */
        @media (max-width: 1200px) {
            .sidebar {
                width: 260px;
            }

            .main-content {
                margin-left: 260px;
                padding: 1.75rem;
            }

            .sidebar-header {
                padding: 1.5rem;
            }

            .nav-item {
                padding: 0.75rem 1.15rem;
                font-size: 0.85rem;
            }

            .top-bar {
                padding: 1.15rem 1.5rem;
            }

            .card {
                padding: 1.5rem;
            }

            table th, table td {
                padding: 0.9rem 1.15rem;
            }
        }

        /* Большие ноутбуки (до 1024px) */
        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }

            .main-content {
                margin-left: 240px;
                padding: 1.5rem;
            }

            .sidebar-header {
                padding: 1.25rem;
            }

            .nav-item {
                padding: 0.7rem 1rem;
                font-size: 0.85rem;
                gap: 0.75rem;
            }

            .nav-item svg {
                width: 18px;
                height: 18px;
            }

            .top-bar {
                padding: 1rem 1.25rem;
                border-radius: 14px;
            }

            .top-bar h1 {
                font-size: 1.125rem;
            }

            .card {
                padding: 1.25rem;
                border-radius: 14px;
            }

            table th, table td {
                padding: 0.85rem 1rem;
                font-size: 0.85rem;
            }

            table th {
                font-size: 0.65rem;
            }

            .btn-primary {
                padding: 0.6rem 1.2rem;
                font-size: 0.8rem;
            }

            .btn-ghost {
                padding: 0.5rem 0.9rem;
                font-size: 0.75rem;
            }

            .avatar {
                width: 38px;
                height: 38px;
                font-size: 0.85rem;
            }

            .badge {
                padding: 0.2rem 0.6rem;
                font-size: 0.65rem;
            }

            .alert {
                padding: 0.9rem 1.15rem;
                font-size: 0.85rem;
                border-radius: 10px;
            }
        }

        /* Планшеты (до 768px) */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1.25rem;
            }

            .top-bar {
                padding: 1rem 1.15rem;
                border-radius: 12px;
                margin-bottom: 1.5rem;
            }

            .top-bar h1 {
                font-size: 1rem;
            }

            .top-bar p {
                font-size: 0.7rem;
            }

            .card {
                padding: 1.15rem;
                border-radius: 12px;
            }

            table th, table td {
                padding: 0.75rem 0.9rem;
                font-size: 0.8rem;
            }

            table th {
                font-size: 0.6rem;
            }

            .btn-primary {
                padding: 0.55rem 1.1rem;
                font-size: 0.75rem;
                border-radius: 8px;
            }

            .btn-ghost {
                padding: 0.45rem 0.85rem;
                font-size: 0.7rem;
                border-radius: 7px;
            }

            .avatar {
                width: 36px;
                height: 36px;
                font-size: 0.8rem;
            }

            .badge {
                padding: 0.2rem 0.55rem;
                font-size: 0.6rem;
                border-radius: 5px;
            }

            .alert {
                padding: 0.85rem 1rem;
                font-size: 0.8rem;
                border-radius: 9px;
                margin-bottom: 1.25rem;
            }

            .sidebar-header {
                padding: 1.15rem;
            }

            .nav-item {
                padding: 0.75rem 1rem;
                font-size: 0.85rem;
                margin: 0.25rem 0.75rem;
            }

            .nav-item svg {
                width: 18px;
                height: 18px;
            }
        }

        /* Большие телефоны (до 640px) */
        @media (max-width: 640px) {
            .main-content {
                padding: 1rem;
            }

            .top-bar {
                padding: 0.9rem 1rem;
                border-radius: 10px;
                margin-bottom: 1.25rem;
            }

            .top-bar h1 {
                font-size: 0.95rem;
            }

            .top-bar p {
                font-size: 0.65rem;
            }

            .card {
                padding: 1rem;
                border-radius: 10px;
            }

            table th, table td {
                padding: 0.7rem 0.8rem;
                font-size: 0.75rem;
            }

            table th {
                font-size: 0.55rem;
            }

            .btn-primary {
                padding: 0.5rem 1rem;
                font-size: 0.7rem;
                gap: 0.4rem;
            }

            .btn-ghost {
                padding: 0.4rem 0.8rem;
                font-size: 0.65rem;
            }

            .avatar {
                width: 34px;
                height: 34px;
                font-size: 0.75rem;
                border-width: 1.5px;
            }

            .badge {
                padding: 0.15rem 0.5rem;
                font-size: 0.55rem;
                gap: 0.3rem;
            }

            .alert {
                padding: 0.75rem 0.9rem;
                font-size: 0.75rem;
                border-radius: 8px;
                margin-bottom: 1rem;
            }

            .sidebar {
                width: 260px;
            }

            .sidebar-header {
                padding: 1rem;
            }

            .nav-item {
                padding: 0.7rem 0.9rem;
                font-size: 0.8rem;
                margin: 0.2rem 0.65rem;
                gap: 0.7rem;
            }

            .nav-item svg {
                width: 17px;
                height: 17px;
            }

            .logo-badge {
                padding: 0.4rem 0.75rem;
                font-size: 0.6rem;
            }
        }

        /* Телефоны (до 480px) */
        @media (max-width: 480px) {
            .main-content {
                padding: 0.85rem;
            }

            .top-bar {
                padding: 0.8rem 0.9rem;
                border-radius: 9px;
                margin-bottom: 1rem;
            }

            .top-bar h1 {
                font-size: 0.9rem;
            }

            .top-bar p {
                font-size: 0.6rem;
            }

            .card {
                padding: 0.9rem;
                border-radius: 9px;
            }

            table th, table td {
                padding: 0.6rem 0.7rem;
                font-size: 0.7rem;
            }

            table th {
                font-size: 0.5rem;
            }

            .btn-primary {
                padding: 0.45rem 0.9rem;
                font-size: 0.65rem;
                border-radius: 7px;
            }

            .btn-ghost {
                padding: 0.35rem 0.7rem;
                font-size: 0.6rem;
                border-radius: 6px;
            }

            .avatar {
                width: 32px;
                height: 32px;
                font-size: 0.7rem;
            }

            .badge {
                padding: 0.15rem 0.45rem;
                font-size: 0.5rem;
            }

            .alert {
                padding: 0.7rem 0.8rem;
                font-size: 0.7rem;
                border-radius: 7px;
                margin-bottom: 0.9rem;
            }

            .sidebar {
                width: 240px;
            }

            .sidebar-header {
                padding: 0.9rem;
            }

            .nav-item {
                padding: 0.65rem 0.85rem;
                font-size: 0.75rem;
                margin: 0.2rem 0.6rem;
                gap: 0.65rem;
            }

            .nav-item svg {
                width: 16px;
                height: 16px;
            }

            .logo-badge {
                padding: 0.35rem 0.7rem;
                font-size: 0.55rem;
            }
        }

        /* Очень маленькие телефоны (до 380px) */
        @media (max-width: 380px) {
            .main-content {
                padding: 0.75rem;
            }

            .top-bar {
                padding: 0.7rem 0.8rem;
                border-radius: 8px;
                margin-bottom: 0.9rem;
            }

            .top-bar h1 {
                font-size: 0.85rem;
            }

            .card {
                padding: 0.8rem;
                border-radius: 8px;
            }

            table th, table td {
                padding: 0.55rem 0.6rem;
                font-size: 0.65rem;
            }

            .btn-primary {
                padding: 0.4rem 0.8rem;
                font-size: 0.6rem;
            }

            .btn-ghost {
                padding: 0.3rem 0.6rem;
                font-size: 0.55rem;
            }

            .avatar {
                width: 30px;
                height: 30px;
                font-size: 0.65rem;
            }

            .badge {
                padding: 0.1rem 0.4rem;
                font-size: 0.45rem;
            }

            .alert {
                padding: 0.6rem 0.7rem;
                font-size: 0.65rem;
                border-radius: 6px;
            }

            .sidebar {
                width: 220px;
            }

            .sidebar-header {
                padding: 0.8rem;
            }

            .nav-item {
                padding: 0.6rem 0.75rem;
                font-size: 0.7rem;
                margin: 0.15rem 0.5rem;
                gap: 0.6rem;
            }

            .nav-item svg {
                width: 15px;
                height: 15px;
            }

            .logo-badge {
                padding: 0.3rem 0.6rem;
                font-size: 0.5rem;
            }
        }

        /* ===== СКРОЛЛБАР ===== */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--neon-red), var(--neon-purple));
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, var(--neon-red), var(--neon-pink));
        }
    </style>
</head>
<body>

{{-- Анимированный фон --}}
<div class="bg-aurora"></div>

{{-- Overlay для мобильного меню --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="document.getElementById('sidebar').classList.remove('open'); this.classList.remove('active');"></div>

{{-- Боковая панель --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center shadow-lg shadow-red-900/50 relative">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6 text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-red-500 to-red-700 opacity-50 blur-xl"></div>
            </div>
            <div>
                <div class="text-base font-bold text-white">Super Admin</div>
                <div class="text-[10px] text-zinc-500 uppercase tracking-wider">Control Panel</div>
            </div>
        </div>
        <span class="logo-badge">
            <svg fill="currentColor" viewBox="0 0 20 20" class="w-3 h-3"><path d="M10 2L12.5 7.5L18 8.5L14 12.5L15 18L10 15L5 18L6 12.5L2 8.5L7.5 7.5L10 2Z"/></svg>
            Root Access
        </span>
    </div>

    <nav class="py-4">
        <div class="px-4 mb-2 text-[10px] font-bold text-zinc-600 uppercase tracking-wider">Основное</div>

        <a href="{{ route('superadmin.dashboard') }}"
           class="nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Дашборд
        </a>

        <a href="{{ route('superadmin.users.index') }}"
           class="nav-item {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Все пользователи
        </a>

        <a href="{{ route('superadmin.companies.index') }}"
           class="nav-item {{ request()->routeIs('superadmin.companies.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Компании
        </a>

        <a href="{{ route('superadmin.activity') }}"
           class="nav-item {{ request()->routeIs('superadmin.activity.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            Активность
        </a>

        <div class="px-4 mt-6 mb-2 text-[10px] font-bold text-zinc-600 uppercase tracking-wider">Личное</div>

        <a href="{{ route('superadmin.profile') }}"
           class="nav-item {{ request()->routeIs('superadmin.profile') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Мой профиль
        </a>

        <div class="px-4 mt-6 mb-2 text-[10px] font-bold text-zinc-600 uppercase tracking-wider">Система</div>

        <a href="{{ route('users.index') }}" class="nav-item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            В обычную админку
        </a>

        <form method="POST" action="{{ route('logout') }}" class="block">
            @csrf
            <button type="submit" class="nav-item w-full text-left text-red-400 hover:text-red-300 hover:bg-red-500/10">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Выйти
            </button>
        </form>
    </nav>
</aside>

{{-- Основной контент --}}
<main class="main-content">
    {{-- Верхняя панель --}}
    <div class="top-bar">
        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('sidebar').classList.add('open'); document.getElementById('sidebarOverlay').classList.add('active');" class="md:hidden btn-ghost">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div>
                <h1 class="text-xl font-bold text-white">@yield('page-title', 'Super Admin')</h1>
                <p class="text-xs text-zinc-500">@yield('page-subtitle', 'Центр управления системой')</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <div class="text-sm font-semibold text-white">{{ auth()->user()->name }}</div>
                <div class="text-[10px] text-zinc-500 uppercase tracking-wider">Super Administrator</div>
            </div>
            <a href="{{ route('superadmin.profile') }}" class="avatar">
                @if(auth()->user()->avatar)
                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="">
                @else
                {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
                @endif
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-error">✕ {{ session('error') }}</div>
    @endif

    {{-- Контент страницы --}}
    @yield('content')
</main>

<script>
    // Закрытие sidebar при клике вне его (для мобильных)
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const hamburger = e.target.closest('.btn-ghost');

        if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !hamburger) {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        }
    });

    // Закрытие sidebar при изменении размера экрана
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('active');
        }
    });
</script>

</body>
</html>