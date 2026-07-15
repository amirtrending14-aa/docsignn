<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DocSign — Вход в систему ЭДО</title>
    <style>
        @import url('https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #818cf8;
            --accent: #06b6d4;
            --bg-dark: #0f172a;
            --bg-card: rgba(15, 23, 42, 0.6);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border: rgba(148, 163, 184, 0.15);
            --glow: rgba(79, 70, 229, 0.4);
        }

        body {
            font-family: 'Figtree', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-dark);
            overflow: hidden;
            position: relative;
        }

        /* Animated background */
        .bg-animation {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(79, 70, 229, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(6, 182, 212, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 80%, rgba(139, 92, 246, 0.1) 0%, transparent 50%);
            animation: bgShift 15s ease-in-out infinite alternate;
        }

        @keyframes bgShift {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-5%, -5%) rotate(3deg); }
        }

        /* Floating particles */
        .particles {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: rgba(129, 140, 248, 0.5);
            border-radius: 50%;
            animation: float linear infinite;
        }

        @keyframes float {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }

        /* Grid lines */
        .grid-overlay {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-image:
                linear-gradient(rgba(148, 163, 184, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            animation: gridMove 20s linear infinite;
        }

        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(60px, 60px); }
        }

        /* Main container */
        .container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 460px;
            padding: 20px;
        }

        /* Language switcher */
        .lang-switcher {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
            display: flex;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 4px;
            gap: 2px;
        }

        .lang-btn {
            padding: 8px 14px;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            font-family: 'Figtree', sans-serif;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
        }

        .lang-btn:hover {
            color: var(--text-primary);
            background: rgba(79, 70, 229, 0.15);
        }

        .lang-btn.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 2px 10px var(--glow);
        }

        /* Login card */
        .login-card {
            background: var(--bg-card);
            backdrop-filter: blur(40px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 48px 40px;
            position: relative;
            overflow: hidden;
            animation: cardAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
            box-shadow:
                0 0 0 1px rgba(148, 163, 184, 0.05),
                0 25px 80px rgba(0, 0, 0, 0.4),
                0 0 120px rgba(79, 70, 229, 0.08);
        }

        @keyframes cardAppear {
            0% {
                opacity: 0;
                transform: translateY(30px) scale(0.96);
                filter: blur(10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
                filter: blur(0);
            }
        }

        /* Top accent line */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent), var(--primary-light));
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Glow effect on card */
        .login-card::after {
            content: '';
            position: absolute;
            top: -1px;
            left: -1px;
            right: -1px;
            bottom: -1px;
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.2), transparent 40%, transparent 60%, rgba(6, 182, 212, 0.1));
            z-index: -1;
            pointer-events: none;
        }

        /* Logo section */
        .logo-section {
            text-align: center;
            margin-bottom: 36px;
            animation: logoAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both;
        }

        @keyframes logoAppear {
            0% { opacity: 0; transform: translateY(15px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .logo-img {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            margin: 0 auto 16px;
            display: block;
            box-shadow: 0 8px 30px rgba(79, 70, 229, 0.3);
            transition: transform 0.3s ease;
        }

        .logo-img:hover {
            transform: scale(1.05) rotate(-2deg);
        }

        .logo-title {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .logo-title span {
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo-subtitle {
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
            animation: formAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .form-group:nth-child(1) { animation-delay: 0.3s; }
        .form-group:nth-child(2) { animation-delay: 0.4s; }
        .form-group:nth-child(3) { animation-delay: 0.5s; }

        @keyframes formAppear {
            0% { opacity: 0; transform: translateX(-15px); }
            100% { opacity: 1; transform: translateX(0); }
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            color: var(--text-secondary);
            transition: color 0.3s ease;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid var(--border);
            border-radius: 14px;
            color: var(--text-primary);
            font-family: 'Figtree', sans-serif;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input::placeholder {
            color: rgba(148, 163, 184, 0.4);
        }

        .form-input:focus {
            border-color: var(--primary);
            background: rgba(30, 41, 59, 0.8);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15), 0 0 20px rgba(79, 70, 229, 0.1);
        }

        .form-input:focus + .input-icon,
        .form-input:focus ~ .input-icon {
            color: var(--primary-light);
        }

        /* Password toggle */
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 4px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            color: var(--text-primary);
            background: rgba(148, 163, 184, 0.1);
        }

        .toggle-password svg {
            width: 18px;
            height: 18px;
        }

        /* Remember & Forgot */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            animation: formAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.5s both;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 500;
            user-select: none;
        }

        .custom-checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid var(--border);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .custom-checkbox svg {
            width: 12px;
            height: 12px;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.2s ease;
            color: white;
        }

        .remember-input {
            display: none;
        }

        .remember-input:checked + .custom-checkbox {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 2px 8px var(--glow);
        }

        .remember-input:checked + .custom-checkbox svg {
            opacity: 1;
            transform: scale(1);
        }

        .forgot-link {
            font-size: 13px;
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            position: relative;
        }

        .forgot-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 1px;
            background: var(--primary-light);
            transition: width 0.3s ease;
        }

        .forgot-link:hover::after {
            width: 100%;
        }

        .forgot-link:hover {
            color: var(--accent);
        }

        /* Submit button */
        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 14px;
            color: white;
            font-family: 'Figtree', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            letter-spacing: 0.3px;
            animation: formAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.6s both;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px var(--glow), 0 0 40px rgba(6, 182, 212, 0.15);
        }

        .submit-btn:hover::before {
            opacity: 1;
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn .btn-text {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .submit-btn .btn-arrow {
            transition: transform 0.3s ease;
        }

        .submit-btn:hover .btn-arrow {
            transform: translateX(4px);
        }

        /* Loading state */
        .submit-btn.loading .btn-text {
            opacity: 0;
        }

        .submit-btn.loading::after {
            content: '';
            position: absolute;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            top: 50%;
            left: 50%;
            margin: -12px 0 0 -12px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            margin: 28px 0;
            gap: 16px;
            animation: formAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.7s both;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .divider-text {
            font-size: 12px;
            color: var(--text-secondary);
            font-weight: 500;
            white-space: nowrap;
        }

        /* Register link */
        .register-section {
            text-align: center;
            animation: formAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.8s both;
        }

        .register-text {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .register-link {
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .register-link:hover {
            color: var(--accent);
            text-shadow: 0 0 20px rgba(6, 182, 212, 0.3);
        }

        /* Footer badges */
        .footer-badges {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-top: 32px;
            animation: footerAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.9s both;
        }

        @keyframes footerAppear {
            0% { opacity: 0; transform: translateY(10px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .badge {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
            opacity: 0.7;
        }

        .badge svg {
            width: 14px;
            height: 14px;
            color: var(--primary-light);
        }

        /* Copyright */
        .copyright {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: rgba(148, 163, 184, 0.4);
            font-weight: 500;
            animation: footerAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1) 1s both;
        }

        /* Error state */
        .form-input.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
        }

        .error-message {
            font-size: 12px;
            color: #ef4444;
            margin-top: 6px;
            font-weight: 500;
            display: none;
        }

        .error-message.visible {
            display: block;
            animation: shake 0.4s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Success notification */
        .notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            z-index: 1000;
            transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            backdrop-filter: blur(20px);
        }

        .notification.show {
            transform: translateX(-50%) translateY(0);
        }

        .notification.success {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
        }

        .notification.error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        /* ===== ПОЛНАЯ АДАПТИВНОСТЬ ===== */
        
        /* Маленькие ноутбуки и большие планшеты (до 992px) */
        @media (max-width: 992px) {
            .container { padding: 18px; }
            .login-card { padding: 42px 36px; border-radius: 22px; }
            .logo-section { margin-bottom: 32px; }
            .logo-img { width: 72px; height: 72px; border-radius: 18px; margin-bottom: 14px; }
            .logo-title { font-size: 26px; }
            .logo-subtitle { font-size: 11px; letter-spacing: 1.8px; }
            .form-group { margin-bottom: 18px; }
            .form-label { font-size: 12px; margin-bottom: 7px; }
            .form-input { padding: 13px 15px 13px 44px; font-size: 14px; border-radius: 12px; }
            .input-icon { left: 15px; width: 18px; height: 18px; }
            .toggle-password { right: 12px; }
            .toggle-password svg { width: 16px; height: 16px; }
            .form-options { margin-bottom: 24px; }
            .remember-me { font-size: 12px; gap: 8px; }
            .custom-checkbox { width: 18px; height: 18px; border-radius: 5px; }
            .forgot-link { font-size: 12px; }
            .submit-btn { padding: 15px; font-size: 14px; border-radius: 12px; }
            .divider { margin: 24px 0; gap: 14px; }
            .divider-text { font-size: 11px; }
            .register-text { font-size: 13px; }
            .footer-badges { gap: 20px; margin-top: 28px; }
            .badge { font-size: 10px; gap: 5px; }
            .badge svg { width: 12px; height: 12px; }
            .copyright { margin-top: 20px; font-size: 11px; }
            .notification { padding: 12px 20px; font-size: 13px; border-radius: 10px; }
            .lang-switcher { top: 16px; right: 16px; }
        }

        /* Планшеты (до 768px) */
        @media (max-width: 768px) {
            .container { padding: 16px; }
            .login-card { padding: 36px 28px; border-radius: 20px; }
            .logo-section { margin-bottom: 28px; }
            .logo-img { width: 64px; height: 64px; border-radius: 16px; margin-bottom: 12px; }
            .logo-title { font-size: 24px; }
            .logo-subtitle { font-size: 10px; letter-spacing: 1.6px; }
            .form-group { margin-bottom: 16px; }
            .form-label { font-size: 12px; margin-bottom: 6px; }
            .form-input { padding: 12px 14px 12px 42px; font-size: 13px; border-radius: 11px; }
            .input-icon { left: 14px; width: 17px; height: 17px; }
            .toggle-password { right: 11px; padding: 3px; }
            .toggle-password svg { width: 15px; height: 15px; }
            .form-options { margin-bottom: 22px; flex-direction: column; gap: 12px; align-items: flex-start; }
            .remember-me { font-size: 12px; gap: 8px; }
            .custom-checkbox { width: 17px; height: 17px; }
            .forgot-link { font-size: 12px; }
            .submit-btn { padding: 14px; font-size: 13px; border-radius: 11px; }
            .submit-btn .btn-text { gap: 6px; }
            .submit-btn .btn-arrow { width: 16px; height: 16px; }
            .submit-btn.loading::after { width: 22px; height: 22px; margin: -11px 0 0 -11px; }
            .divider { margin: 22px 0; gap: 12px; }
            .divider-text { font-size: 11px; }
            .register-text { font-size: 13px; }
            .footer-badges { gap: 16px; margin-top: 24px; }
            .badge { font-size: 10px; gap: 4px; }
            .badge svg { width: 11px; height: 11px; }
            .copyright { margin-top: 18px; font-size: 11px; }
            .notification { padding: 11px 18px; font-size: 12px; border-radius: 9px; top: 16px; }
            .lang-switcher { top: 12px; right: 12px; padding: 3px; }
        }

        /* Большие телефоны (до 576px) */
        @media (max-width: 576px) {
            .container { padding: 14px; }
            .login-card { padding: 32px 24px; border-radius: 18px; }
            .logo-section { margin-bottom: 24px; }
            .logo-img { width: 56px; height: 56px; border-radius: 14px; margin-bottom: 10px; }
            .logo-title { font-size: 22px; }
            .logo-subtitle { font-size: 10px; letter-spacing: 1.4px; }
            .form-group { margin-bottom: 14px; }
            .form-label { font-size: 11px; margin-bottom: 5px; }
            .form-input { padding: 11px 13px 11px 40px; font-size: 13px; border-radius: 10px; }
            .input-icon { left: 13px; width: 16px; height: 16px; }
            .toggle-password { right: 10px; padding: 3px; }
            .toggle-password svg { width: 14px; height: 14px; }
            .form-options { margin-bottom: 20px; gap: 10px; }
            .remember-me { font-size: 11px; gap: 7px; }
            .custom-checkbox { width: 16px; height: 16px; border-radius: 4px; }
            .custom-checkbox svg { width: 10px; height: 10px; }
            .forgot-link { font-size: 11px; }
            .submit-btn { padding: 13px; font-size: 13px; border-radius: 10px; }
            .submit-btn .btn-text { gap: 5px; }
            .submit-btn .btn-arrow { width: 15px; height: 15px; }
            .submit-btn.loading::after { width: 20px; height: 20px; margin: -10px 0 0 -10px; border-width: 2px; }
            .divider { margin: 20px 0; gap: 10px; }
            .divider-text { font-size: 10px; }
            .register-text { font-size: 12px; }
            .footer-badges { gap: 14px; margin-top: 20px; flex-wrap: wrap; }
            .badge { font-size: 9px; gap: 3px; }
            .badge svg { width: 10px; height: 10px; }
            .copyright { margin-top: 16px; font-size: 10px; }
            .notification { padding: 10px 16px; font-size: 11px; border-radius: 8px; top: 12px; max-width: 92%; }
            .lang-switcher { top: 10px; right: 10px; padding: 3px; border-radius: 9px; }
        }

        /* Телефоны (до 480px) */
        @media (max-width: 480px) {
            .container { padding: 12px; }
            .login-card { padding: 28px 20px; border-radius: 16px; }
            .logo-section { margin-bottom: 20px; }
            .logo-img { width: 52px; height: 52px; border-radius: 12px; margin-bottom: 9px; }
            .logo-title { font-size: 20px; }
            .logo-subtitle { font-size: 9px; letter-spacing: 1.2px; }
            .form-group { margin-bottom: 12px; }
            .form-label { font-size: 10px; margin-bottom: 4px; }
            .form-input { padding: 10px 12px 10px 38px; font-size: 12px; border-radius: 9px; }
            .input-icon { left: 12px; width: 15px; height: 15px; }
            .toggle-password { right: 9px; padding: 2px; }
            .toggle-password svg { width: 13px; height: 13px; }
            .form-options { margin-bottom: 18px; gap: 8px; }
            .remember-me { font-size: 11px; gap: 6px; }
            .custom-checkbox { width: 15px; height: 15px; }
            .custom-checkbox svg { width: 9px; height: 9px; }
            .forgot-link { font-size: 10px; }
            .submit-btn { padding: 12px; font-size: 12px; border-radius: 9px; letter-spacing: 0.2px; }
            .submit-btn .btn-text { gap: 4px; }
            .submit-btn .btn-arrow { width: 14px; height: 14px; }
            .submit-btn.loading::after { width: 18px; height: 18px; margin: -9px 0 0 -9px; }
            .divider { margin: 18px 0; gap: 8px; }
            .divider-text { font-size: 10px; }
            .register-text { font-size: 11px; }
            .footer-badges { gap: 12px; margin-top: 18px; }
            .badge { font-size: 9px; gap: 3px; }
            .badge svg { width: 10px; height: 10px; }
            .copyright { margin-top: 14px; font-size: 10px; }
            .notification { padding: 9px 14px; font-size: 10px; border-radius: 7px; top: 10px; }
            .lang-switcher { top: 8px; right: 8px; padding: 2px; border-radius: 8px; }
        }

        /* Очень маленькие телефоны (до 380px) */
        @media (max-width: 380px) {
            .container { padding: 10px; }
            .login-card { padding: 24px 16px; border-radius: 14px; }
            .logo-section { margin-bottom: 18px; }
            .logo-img { width: 48px; height: 48px; border-radius: 10px; margin-bottom: 8px; }
            .logo-title { font-size: 18px; }
            .logo-subtitle { font-size: 9px; letter-spacing: 1px; }
            .form-group { margin-bottom: 10px; }
            .form-label { font-size: 10px; margin-bottom: 3px; }
            .form-input { padding: 9px 11px 9px 36px; font-size: 11px; border-radius: 8px; }
            .input-icon { left: 11px; width: 14px; height: 14px; }
            .toggle-password { right: 8px; padding: 2px; }
            .toggle-password svg { width: 12px; height: 12px; }
            .form-options { margin-bottom: 16px; gap: 7px; }
            .remember-me { font-size: 10px; gap: 5px; }
            .custom-checkbox { width: 14px; height: 14px; }
            .custom-checkbox svg { width: 8px; height: 8px; }
            .forgot-link { font-size: 10px; }
            .submit-btn { padding: 11px; font-size: 11px; border-radius: 8px; }
            .submit-btn .btn-text { gap: 3px; }
            .submit-btn .btn-arrow { width: 13px; height: 13px; }
            .submit-btn.loading::after { width: 16px; height: 16px; margin: -8px 0 0 -8px; }
            .divider { margin: 16px 0; gap: 7px; }
            .divider-text { font-size: 9px; }
            .register-text { font-size: 10px; }
            .footer-badges { gap: 10px; margin-top: 16px; }
            .badge { font-size: 8px; gap: 2px; }
            .badge svg { width: 9px; height: 9px; }
            .copyright { margin-top: 12px; font-size: 9px; }
            .notification { padding: 8px 12px; font-size: 9px; border-radius: 6px; top: 8px; }
            .lang-switcher { top: 6px; right: 6px; padding: 2px; }
        }

        /* Focus visible for accessibility */
        :focus-visible {
            outline: 2px solid var(--primary-light);
            outline-offset: 2px;
        }

        /* Input autofill styling */
        .form-input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 30px rgba(30, 41, 59, 0.9) inset !important;
            -webkit-text-fill-color: var(--text-primary) !important;
        }
    </style>
</head>
<body>
<!-- Background effects -->
<div class="bg-animation"></div>
<div class="grid-overlay"></div>
<div class="particles" id="particles"></div>

<!-- Notification -->
<div class="notification" id="notification"></div>

<!-- Language switcher -->
<div class="lang-switcher" style="position: fixed; top: 20px; right: 20px; z-index: 100; display: inline-block;">
    <div style="position: relative;">
        <select id="lang-select" onchange="switchLang(this.value)"
                style="appearance: none; -webkit-appearance: none; -moz-appearance: none;
                       background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
                       border: 1px solid rgba(148, 163, 184, 0.15); border-radius: 12px;
                       padding: 8px 32px 8px 14px; color: #94a3b8; font-family: 'Figtree', sans-serif;
                       font-size: 13px; font-weight: 600; cursor: pointer; outline: none;
                       letter-spacing: 0.5px; transition: all 0.3s ease; width: 100%; text-transform: uppercase;">
            <option value="ru" style="background: #0f172a; color: #f1f5f9;">🇷🇺 РУ</option>
            <option value="tj" style="background: #0f172a; color: #f1f5f9;">🇹🇯 TJ</option>
            <option value="en" style="background: #0f172a; color: #f1f5f9;">🇬🇧 EN</option>
        </select>

        <div style="position: absolute; inset-y: 0; right: 12px; display: flex; align-items: center; pointer-events: none; color: #94a3b8;">
            <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                <path d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>
</div>

<!-- Main container -->
<div class="container">
    <div class="login-card">
        <!-- Logo -->
        <div class="logo-section">
            <img
                    src="{{ asset('img/123.png') }}"
                    alt="DocSign Logo"
                    class="logo-img"
            >
            <div class="logo-title">Doc<span>Sign</span></div>
            <div class="logo-subtitle" data-i18n="subtitle">Система электронного документооборота</div>
        </div>

        <!-- Login form -->
        <form id="loginForm" onsubmit="handleLogin(event)">
            <div class="form-group">
                <label class="form-label" data-i18n="emailLabel">Электронная почта</label>
                <div class="input-wrapper">
                    <input
                            type="email"
                            class="form-input"
                            id="email"
                            data-i18n-placeholder="emailPlaceholder"
                            placeholder="name@company.com"
                            autocomplete="email"
                            required
                    >
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                    </svg>
                </div>
                <div class="error-message" id="emailError" data-i18n="emailError">Введите корректный email</div>
            </div>

            <div class="form-group">
                <label class="form-label" data-i18n="passwordLabel">Пароль</label>
                <div class="input-wrapper">
                    <input
                            type="password"
                            class="form-input"
                            id="password"
                            data-i18n-placeholder="passwordPlaceholder"
                            placeholder="••••••••••"
                            autocomplete="current-password"
                            required
                    >
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Toggle password visibility">
                        <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <div class="error-message" id="passwordError" data-i18n="passwordError">Введите пароль</div>
            </div>

            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" class="remember-input" id="remember">
                    <div class="custom-checkbox">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>
                    <span data-i18n="remember">Запомнить меня</span>
                </label>
                <a href="#" class="forgot-link" data-i18n="forgot">Забыли пароль?</a>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">
                    <span class="btn-text">
                        <span data-i18n="loginBtn">Войти в систему</span>
                        <svg class="btn-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/>
                            <path d="m12 5 7 7-7 7"/>
                        </svg>
                    </span>
            </button>
        </form>

        <div class="divider">
            <div class="divider-line"></div>
            <span class="divider-text" data-i18n="divider">или</span>
            <div class="divider-line"></div>
        </div>

        <div class="register-section">
            <p class="register-text">
                <span data-i18n="noAccount">Нет аккаунта?</span>
                <a href="#" class="register-link" data-i18n="register">Зарегистрироваться</a>
            </p>
        </div>
    </div>

    <!-- Footer badges -->
    <div class="footer-badges">
        <div class="badge">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
            <span>SSL</span>
        </div>
        <div class="badge">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
            <span data-i18n="badgeSecurity">Защита</span>
        </div>
        <div class="badge">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"/></svg>
            <span data-i18n="badgeSign">ЭЦП</span>
        </div>
    </div>

    <p class="copyright">
        © <span id="year"></span> DocSign Ecosystem. <span data-i18n="rights">Все права защищены.</span>
    </p>
</div>

<script>
    // Set current year
    document.getElementById('year').textContent = new Date().getFullYear();

    // Generate particles
    function createParticles() {
        const container = document.getElementById('particles');
        const count = 30;
        for (let i = 0; i < count; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDuration = (Math.random() * 10 + 8) + 's';
            particle.style.animationDelay = (Math.random() * 10) + 's';
            particle.style.width = (Math.random() * 3 + 1) + 'px';
            particle.style.height = particle.style.width;
            particle.style.opacity = Math.random() * 0.5 + 0.1;
            container.appendChild(particle);
        }
    }
    createParticles();

    // Translations
    const translations = {
        ru: {
            subtitle: 'Система электронного документооборота',
            emailLabel: 'Электронная почта',
            emailPlaceholder: 'name@company.com',
            emailError: 'Введите корректный email',
            passwordLabel: 'Пароль',
            passwordPlaceholder: '••••••••••',
            passwordError: 'Введите пароль',
            remember: 'Запомнить меня',
            forgot: 'Забыли пароль?',
            loginBtn: 'Войти в систему',
            divider: 'или',
            noAccount: 'Нет аккаунта?',
            register: 'Зарегистрироваться',
            badgeSecurity: 'Защита',
            badgeSign: 'ЭЦП',
            rights: 'Все права защищены.',
            loginSuccess: 'Вход выполнен успешно! Перенаправление...',
            loginError: 'Ошибка входа. Проверьте данные.',
            emptyFields: 'Заполните все поля',
            invalidEmail: 'Неверный формат email'
        },
        tj: {
            subtitle: 'Системаи ҳуҷҷатгардонии электронӣ',
            emailLabel: 'Почтаи электронӣ',
            emailPlaceholder: 'name@company.com',
            emailError: 'Email-и дурустро ворид кунед',
            passwordLabel: 'Рамз',
            passwordPlaceholder: '••••••••••',
            passwordError: 'Рамзро ворид кунед',
            remember: 'Маро дар ёд дор',
            forgot: 'Рамзро фаромӯш кардед?',
            loginBtn: 'Ворид шудан',
            divider: 'ё',
            noAccount: 'Ҳисоб надоред?',
            register: 'Бақайдгирӣ',
            badgeSecurity: 'Ҳифз',
            badgeSign: 'ЭИИ',
            rights: 'Ҳамаи ҳуқуқҳо ҳифз шудаанд.',
            loginSuccess: 'Вуруди муваффақ! Боз равона карда мешавад...',
            loginError: 'Хатогии вуруд. Маълумотро санҷед.',
            emptyFields: 'Ҳамаи майдонҳоро пур кунед',
            invalidEmail: 'Формати email нодуруст'
        },
        en: {
            subtitle: 'Electronic Document Management System',
            emailLabel: 'Email Address',
            emailPlaceholder: 'name@company.com',
            emailError: 'Please enter a valid email',
            passwordLabel: 'Password',
            passwordPlaceholder: '••••••••••',
            passwordError: 'Please enter your password',
            remember: 'Remember me',
            forgot: 'Forgot password?',
            loginBtn: 'Sign In',
            divider: 'or',
            noAccount: "Don't have an account?",
            register: 'Sign Up',
            badgeSecurity: 'Security',
            badgeSign: 'EDS',
            rights: 'All rights reserved.',
            loginSuccess: 'Login successful! Redirecting...',
            loginError: 'Login failed. Please check your credentials.',
            emptyFields: 'Please fill in all fields',
            invalidEmail: 'Invalid email format'
        }
    };

    let currentLang = 'ru';

    function switchLang(lang) {
        currentLang = lang;

        // Update active button
        document.querySelectorAll('.lang-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.lang === lang);
        });

        // Update HTML lang attribute
        document.documentElement.lang = lang;

        // Update all translatable elements
        const t = translations[lang];

        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (t[key]) {
                el.textContent = t[key];
            }
        });

        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            if (t[key]) {
                el.placeholder = t[key];
            }
        });
    }

    // Toggle password visibility
    let passwordVisible = false;
    function togglePassword() {
        passwordVisible = !passwordVisible;
        const input = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');

        if (passwordVisible) {
            input.type = 'text';
            icon.innerHTML = `
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                `;
        } else {
            input.type = 'password';
            icon.innerHTML = `
                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                    <circle cx="12" cy="12" r="3"/>
                `;
        }
    }

    // Show notification
    function showNotification(message, type) {
        const notif = document.getElementById('notification');
        notif.textContent = message;
        notif.className = `notification ${type} show`;

        setTimeout(() => {
            notif.classList.remove('show');
        }, 3000);
    }

    // Handle login
    function handleLogin(e) {
        e.preventDefault();

        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');
        const btn = document.getElementById('submitBtn');
        const t = translations[currentLang];

        // Reset errors
        email.classList.remove('error');
        password.classList.remove('error');
        emailError.classList.remove('visible');
        passwordError.classList.remove('visible');

        let hasError = false;

        // Validate email
        if (!email.value.trim()) {
            email.classList.add('error');
            emailError.textContent = t.emptyFields;
            emailError.classList.add('visible');
            hasError = true;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            email.classList.add('error');
            emailError.textContent = t.invalidEmail;
            emailError.classList.add('visible');
            hasError = true;
        }

        // Validate password
        if (!password.value.trim()) {
            password.classList.add('error');
            passwordError.classList.add('visible');
            hasError = true;
        }

        if (hasError) return;

        // Simulate loading
        btn.classList.add('loading');
        btn.disabled = true;

        setTimeout(() => {
            btn.classList.remove('loading');
            btn.disabled = false;

            // Demo: show success or error
            if (email.value === 'demo@docsign.tj' && password.value === 'demo') {
                showNotification(t.loginSuccess, 'success');
            } else {
                showNotification(t.loginError, 'error');
            }
        }, 2000);
    }

    // Clear error on input
    document.getElementById('email').addEventListener('input', function() {
        this.classList.remove('error');
        document.getElementById('emailError').classList.remove('visible');
    });

    document.getElementById('password').addEventListener('input', function() {
        this.classList.remove('error');
        document.getElementById('passwordError').classList.remove('visible');
    });

    // Keyboard shortcut: Enter to submit (already handled by form)
    // Escape to clear
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('email').classList.remove('error');
            document.getElementById('password').classList.remove('error');
            document.getElementById('emailError').classList.remove('visible');
            document.getElementById('passwordError').classList.remove('visible');
        }
    });
</script>
</body>
</html>