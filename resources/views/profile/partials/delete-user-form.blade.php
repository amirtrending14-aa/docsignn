<section class="space-y-6">
    <!-- 1. СКРЫТАЯ ТЕХНИЧЕСКАЯ ФОРМА (Для Laravel) -->
    <div style="display: none;">
        <form id="real-delete-form" method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')
            <input type="password" name="password" id="hidden-password-input">
        </form>
    </div>

    <!-- 2. ОШИБКА -->
    @if($errors->userDeletion->has('password'))
    <div class="dz-error-alert" data-i18n="errorPassword">
        ❌ Неверный пароль. Попробуйте еще раз.
    </div>
    @endif

    <!-- 3. КРАСИВЫЙ БЛОК DANGER ZONE -->
    <div class="dz-wrapper">
        <div class="dz-glow"></div>

        <div class="dz-card">
            <div class="dz-content">
                <div class="dz-info">
                    <div class="dz-header">
                        <div class="dz-pulse-wrapper">
                            <span class="dz-ping"></span>
                            <span class="dz-dot"></span>
                        </div>
                        <h3 class="dz-title">Danger Zone</h3>
                    </div>
                    <p class="dz-text" data-i18n="deleteWarning">
                        Удаление аккаунта сотрет все данные безвозвратно.
                    </p>
                </div>

                <div class="dz-button-wrapper">
                    <button
                            type="button"
                            onclick="openCustomDeleteModal()"
                            class="dz-button"
                            data-i18n="btnDeleteAccount"
                    >
                        УДАЛИТЬ АККАУНТ
                    </button>
                </div>
            </div>
            <div class="dz-gradient-line"></div>
        </div>
    </div>

    <!-- 4. ТВОЯ КАСТОМНАЯ МОДАЛКА -->
    <div id="customDeleteModal" class="dz-modal-overlay">
        <div class="dz-modal">
            <div class="dz-modal-content">
                <h2 class="dz-modal-title" data-i18n="confirmPassTitle">Подтвердите пароль</h2>
                <p class="dz-modal-desc" data-i18n="confirmPassDesc">Введите пароль для безвозвратного удаления аккаунта.</p>

                <form onsubmit="submitLaravelDeletion(event)">
                    <input
                            type="password"
                            id="customPasswordInput"
                            data-i18n-placeholder="placeholderPass"
                            placeholder="Ваш пароль"
                            required
                            class="dz-modal-input"
                    >
                    <div class="dz-modal-actions">
                        <button type="button" onclick="closeCustomDeleteModal()" class="dz-btn-cancel" data-i18n="btnCancel">Отмена</button>
                        <button type="submit" class="dz-btn-confirm" data-i18n="btnConfirmDelete">Удалить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* ============================================ */
        /* === DANGER ZONE STYLES === */
        /* ============================================ */

        /* Error Alert */
        .dz-error-alert {
            max-width: 56rem;
            margin: 0 auto 1rem;
            padding: 1rem;
            background: #fee2e2;
            border: 1px solid #f87171;
            color: #b91c1c;
            border-radius: 1rem;
            text-align: center;
            font-weight: 900;
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        /* Wrapper */
        .dz-wrapper {
            position: relative;
            isolation: isolate;
            max-width: 56rem;
            margin: 2.5rem auto;
            font-family: system-ui, -apple-system, sans-serif;
        }

        /* Glow Effect */
        .dz-glow {
            position: absolute;
            inset: -0.25rem;
            background: linear-gradient(to right, #dc2626, #ea580c);
            border-radius: 2rem;
            filter: blur(1rem);
            opacity: 0;
            transition: opacity 0.5s;
        }

        .dz-wrapper:hover .dz-glow {
            opacity: 0.1;
        }

        /* Card */
        .dz-card {
            position: relative;
            background: #ffffff;
            border-radius: 1rem;
            border: 1px solid rgba(254, 202, 202, 0.5);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        /* Content */
        .dz-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 1.5rem;
        }

        @media (min-width: 768px) {
            .dz-content {
                flex-direction: row;
            }
        }

        /* Info Section */
        .dz-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        @media (min-width: 768px) {
            .dz-info {
                align-items: flex-start;
            }
        }

        /* Header with pulse */
        .dz-header {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .dz-pulse-wrapper {
            position: relative;
            display: flex;
            height: 0.75rem;
            width: 0.75rem;
        }

        .dz-ping {
            position: absolute;
            display: inline-flex;
            height: 100%;
            width: 100%;
            border-radius: 9999px;
            background: #f87171;
            opacity: 0.75;
            animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        @keyframes ping {
            75%, 100% {
                transform: scale(2);
                opacity: 0;
            }
        }

        .dz-dot {
            position: relative;
            display: inline-flex;
            border-radius: 9999px;
            height: 0.75rem;
            width: 0.75rem;
            background: #dc2626;
        }

        .dz-title {
            color: #dc2626;
            font-size: 0.6875rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            margin: 0;
        }

        .dz-text {
            color: #000000;
            font-size: 0.8125rem;
            font-weight: 700;
            line-height: 1.25;
            margin: 0;
            opacity: 0.8;
            text-align: center;
        }

        @media (min-width: 768px) {
            .dz-text {
                text-align: left;
            }
        }

        /* Button */
        .dz-button-wrapper {
            flex-shrink: 0;
        }

        .dz-button {
            background: #fef2f2;
            color: #dc2626;
            border: 2px solid rgba(220, 38, 38, 0.2);
            font-weight: 900;
            text-transform: uppercase;
            font-size: 0.5625rem;
            letter-spacing: 0.1em;
            padding: 0.5rem 1.25rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .dz-button:hover {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
            box-shadow: 0 0 15px rgba(220, 38, 38, 0.3);
        }

        .dz-button:active {
            transform: scale(0.95);
        }

        /* Gradient Line */
        .dz-gradient-line {
            height: 0.25rem;
            width: 100%;
            background: linear-gradient(to right, transparent, rgba(239, 68, 68, 0.2), transparent);
        }

        /* ============================================ */
        /* === MODAL === */
        /* ============================================ */
        .dz-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
        }

        .dz-modal-overlay.active {
            display: flex;
        }

        .dz-modal {
            background: #ffffff;
            width: 100%;
            max-width: 28rem;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid #fee2e2;
            overflow: hidden;
        }

        .dz-modal-content {
            padding: 1.5rem;
            text-align: center;
        }

        .dz-modal-title {
            font-size: 1.25rem;
            font-weight: 900;
            color: #111827;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            margin-top: 0;
        }

        .dz-modal-desc {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            margin-top: 0;
        }

        .dz-modal-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            outline: none;
            margin-bottom: 1rem;
            text-align: center;
            color: #000000;
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }

        .dz-modal-input:focus {
            border-color: #ef4444;
        }

        .dz-modal-actions {
            display: flex;
            gap: 0.75rem;
        }

        .dz-btn-cancel,
        .dz-btn-confirm {
            flex: 1;
            padding: 0.75rem 1rem;
            font-weight: 700;
            border-radius: 0.75rem;
            text-transform: uppercase;
            font-size: 0.625rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .dz-btn-cancel {
            background: #f3f4f6;
            color: #4b5563;
        }

        .dz-btn-cancel:hover {
            background: #e5e7eb;
        }

        .dz-btn-confirm {
            background: #dc2626;
            color: #ffffff;
        }

        .dz-btn-confirm:hover {
            background: #b91c1c;
        }

        /* ============================================ */
        /* === ПОЛНАЯ АДАПТИВНОСТЬ === */
        /* ============================================ */

        /* Маленькие десктопы (до 1200px) */
        @media (max-width: 1200px) {
            .dz-wrapper { margin: 2.25rem auto; }
            .dz-content { padding: 1.4rem; gap: 1.4rem; }
            .dz-modal-content { padding: 1.4rem; }
            .dz-modal-title { font-size: 1.2rem; }
        }

        /* Планшеты (до 992px) */
        @media (max-width: 992px) {
            .dz-wrapper { margin: 2rem auto; }
            .dz-error-alert { padding: 0.9rem; font-size: 0.9rem; border-radius: 0.9rem; }
            .dz-card { border-radius: 0.9rem; }
            .dz-content { padding: 1.3rem; gap: 1.3rem; }
            .dz-header { gap: 0.6rem; }
            .dz-pulse-wrapper { height: 0.7rem; width: 0.7rem; }
            .dz-dot { height: 0.7rem; width: 0.7rem; }
            .dz-title { font-size: 0.65rem; letter-spacing: 0.18em; }
            .dz-text { font-size: 0.8rem; }
            .dz-button { font-size: 0.55rem; padding: 0.45rem 1.15rem; border-radius: 0.45rem; }
            .dz-modal { max-width: 26rem; border-radius: 0.9rem; }
            .dz-modal-content { padding: 1.3rem; }
            .dz-modal-title { font-size: 1.15rem; margin-bottom: 0.45rem; }
            .dz-modal-desc { font-size: 0.85rem; margin-bottom: 1.4rem; }
            .dz-modal-input { padding: 0.7rem 0.95rem; border-radius: 0.7rem; margin-bottom: 0.95rem; font-size: 0.85rem; }
            .dz-modal-actions { gap: 0.7rem; }
            .dz-btn-cancel, .dz-btn-confirm { padding: 0.7rem 0.95rem; border-radius: 0.7rem; font-size: 0.6rem; }
        }

        /* Большие телефоны (до 768px) */
        @media (max-width: 768px) {
            .dz-wrapper { margin: 1.75rem auto; }
            .dz-error-alert { padding: 0.85rem; font-size: 0.85rem; border-radius: 0.85rem; margin-bottom: 0.85rem; }
            .dz-card { border-radius: 0.85rem; }
            .dz-content { padding: 1.2rem; gap: 1.2rem; }
            .dz-header { gap: 0.55rem; }
            .dz-pulse-wrapper { height: 0.65rem; width: 0.65rem; }
            .dz-dot { height: 0.65rem; width: 0.65rem; }
            .dz-title { font-size: 0.625rem; letter-spacing: 0.17em; }
            .dz-text { font-size: 0.775rem; }
            .dz-button { font-size: 0.525rem; padding: 0.425rem 1.1rem; border-radius: 0.425rem; letter-spacing: 0.09em; }
            .dz-modal { max-width: 24rem; border-radius: 0.85rem; }
            .dz-modal-content { padding: 1.2rem; }
            .dz-modal-title { font-size: 1.1rem; margin-bottom: 0.4rem; }
            .dz-modal-desc { font-size: 0.8rem; margin-bottom: 1.3rem; }
            .dz-modal-input { padding: 0.65rem 0.9rem; border-radius: 0.65rem; margin-bottom: 0.9rem; font-size: 0.8rem; }
            .dz-modal-actions { gap: 0.65rem; }
            .dz-btn-cancel, .dz-btn-confirm { padding: 0.65rem 0.9rem; border-radius: 0.65rem; font-size: 0.575rem; }
        }

        /* Телефоны (до 640px) */
        @media (max-width: 640px) {
            .dz-wrapper { margin: 1.5rem auto; }
            .dz-error-alert { padding: 0.8rem; font-size: 0.8rem; border-radius: 0.8rem; margin-bottom: 0.8rem; }
            .dz-card { border-radius: 0.8rem; }
            .dz-content { padding: 1.1rem; gap: 1.1rem; }
            .dz-header { gap: 0.5rem; }
            .dz-pulse-wrapper { height: 0.6rem; width: 0.6rem; }
            .dz-dot { height: 0.6rem; width: 0.6rem; }
            .dz-title { font-size: 0.6rem; letter-spacing: 0.16em; }
            .dz-text { font-size: 0.75rem; }
            .dz-button { font-size: 0.5rem; padding: 0.4rem 1rem; border-radius: 0.4rem; letter-spacing: 0.08em; }
            .dz-modal { max-width: 22rem; border-radius: 0.8rem; }
            .dz-modal-content { padding: 1.1rem; }
            .dz-modal-title { font-size: 1.05rem; margin-bottom: 0.35rem; }
            .dz-modal-desc { font-size: 0.775rem; margin-bottom: 1.2rem; }
            .dz-modal-input { padding: 0.6rem 0.85rem; border-radius: 0.6rem; margin-bottom: 0.85rem; font-size: 0.775rem; }
            .dz-modal-actions { gap: 0.6rem; }
            .dz-btn-cancel, .dz-btn-confirm { padding: 0.6rem 0.85rem; border-radius: 0.6rem; font-size: 0.55rem; }
        }

        /* Маленькие телефоны (до 480px) */
        @media (max-width: 480px) {
            .dz-wrapper { margin: 1.25rem auto; }
            .dz-error-alert { padding: 0.75rem; font-size: 0.75rem; border-radius: 0.75rem; margin-bottom: 0.75rem; }
            .dz-card { border-radius: 0.75rem; }
            .dz-content { padding: 1rem; gap: 1rem; }
            .dz-header { gap: 0.45rem; }
            .dz-pulse-wrapper { height: 0.55rem; width: 0.55rem; }
            .dz-dot { height: 0.55rem; width: 0.55rem; }
            .dz-title { font-size: 0.575rem; letter-spacing: 0.15em; }
            .dz-text { font-size: 0.725rem; }
            .dz-button { font-size: 0.475rem; padding: 0.375rem 0.9rem; border-radius: 0.375rem; letter-spacing: 0.07em; }
            .dz-modal { max-width: 20rem; border-radius: 0.75rem; }
            .dz-modal-content { padding: 1rem; }
            .dz-modal-title { font-size: 1rem; margin-bottom: 0.3rem; }
            .dz-modal-desc { font-size: 0.75rem; margin-bottom: 1.1rem; }
            .dz-modal-input { padding: 0.55rem 0.8rem; border-radius: 0.55rem; margin-bottom: 0.8rem; font-size: 0.75rem; }
            .dz-modal-actions { gap: 0.55rem; }
            .dz-btn-cancel, .dz-btn-confirm { padding: 0.55rem 0.8rem; border-radius: 0.55rem; font-size: 0.525rem; }
        }

        /* Очень маленькие телефоны (до 380px) */
        @media (max-width: 380px) {
            .dz-wrapper { margin: 1rem auto; }
            .dz-error-alert { padding: 0.7rem; font-size: 0.7rem; border-radius: 0.7rem; margin-bottom: 0.7rem; }
            .dz-card { border-radius: 0.7rem; }
            .dz-content { padding: 0.9rem; gap: 0.9rem; }
            .dz-header { gap: 0.4rem; }
            .dz-pulse-wrapper { height: 0.5rem; width: 0.5rem; }
            .dz-dot { height: 0.5rem; width: 0.5rem; }
            .dz-title { font-size: 0.55rem; letter-spacing: 0.14em; }
            .dz-text { font-size: 0.7rem; }
            .dz-button { font-size: 0.45rem; padding: 0.35rem 0.8rem; border-radius: 0.35rem; letter-spacing: 0.06em; }
            .dz-modal { max-width: 18rem; border-radius: 0.7rem; }
            .dz-modal-content { padding: 0.9rem; }
            .dz-modal-title { font-size: 0.95rem; margin-bottom: 0.25rem; }
            .dz-modal-desc { font-size: 0.725rem; margin-bottom: 1rem; }
            .dz-modal-input { padding: 0.5rem 0.75rem; border-radius: 0.5rem; margin-bottom: 0.75rem; font-size: 0.725rem; }
            .dz-modal-actions { gap: 0.5rem; }
            .dz-btn-cancel, .dz-btn-confirm { padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.5rem; }
        }
    </style>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const DANGER_ZONE_TRANSLATIONS = {
            ru: {
                deleteWarning: 'Удаление аккаунта сотрет все данные безвозвратно.',
                btnDeleteAccount: 'УДАЛИТЬ АККАУНТ',
                confirmPassTitle: 'Подтвердите пароль',
                confirmPassDesc: 'Это действие необратимо. Введите пароль для удаления.',
                placeholderPass: 'Ваш пароль',
                btnCancel: 'Отмена',
                btnConfirmDelete: 'Удалить',
                errorPassword: '❌ Неверный пароль. Попробуйте еще раз.'
            },
            tj: {
                deleteWarning: 'Нест кардани аккаунт ҳамаи маълумотро ба таври ҳамешагӣ нест мекунад.',
                btnDeleteAccount: 'НЕСТ КАРДАНИ АККАУНТ',
                confirmPassTitle: 'Рамзро тасдиқ кунед',
                confirmPassDesc: 'Ин амал бозгашт надорад. Барои нест кардан рамзро ворид кунед.',
                placeholderPass: 'Рамзи шумо',
                btnCancel: 'Бекор кардан',
                btnConfirmDelete: 'Нест кардан',
                errorPassword: '❌ Рамз нодуруст аст. Дубора кӯшиш кунед.'
            },
            en: {
                deleteWarning: 'Deleting your account will erase all data permanently.',
                btnDeleteAccount: 'DELETE ACCOUNT',
                confirmPassTitle: 'Confirm Password',
                confirmPassDesc: 'This action is irreversible. Enter your password to delete.',
                placeholderPass: 'Your password',
                btnCancel: 'Cancel',
                btnConfirmDelete: 'Delete',
                errorPassword: '❌ Incorrect password. Please try again.'
            }
        };

        function applyDangerZoneTranslations(lang) {
            const dict = DANGER_ZONE_TRANSLATIONS[lang] || DANGER_ZONE_TRANSLATIONS.ru;

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
        applyDangerZoneTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applyDangerZoneTranslations(lang);
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applyDangerZoneTranslations(e.newValue);
            }
        });
    });

    function openCustomDeleteModal() {
        const m = document.getElementById('customDeleteModal');
        m.classList.add('active');
        setTimeout(() => document.getElementById('customPasswordInput').focus(), 100);
    }

    function closeCustomDeleteModal() {
        const m = document.getElementById('customDeleteModal');
        m.classList.remove('active');
        document.getElementById('customPasswordInput').value = '';
    }

    function submitLaravelDeletion(e) {
        e.preventDefault();
        const password = document.getElementById('customPasswordInput').value;
        const realForm = document.getElementById('real-delete-form');
        const hiddenInput = document.getElementById('hidden-password-input');

        if (realForm && hiddenInput) {
            hiddenInput.value = password;
            realForm.submit();
        }
    }

    // Закрытие модального окна при клике на overlay
    document.getElementById('customDeleteModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeCustomDeleteModal();
        }
    });
</script>