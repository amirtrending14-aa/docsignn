<section class="pwd-section">
    <header class="pwd-header">
        <h2 class="pwd-title" data-i18n="updatePasswordTitle">
            Обновление пароля
        </h2>
        <p class="pwd-desc" data-i18n="updatePasswordDesc">
            Используйте длинный случайный пароль, чтобы ваш аккаунт оставался в безопасности.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="pwd-form">
        @csrf
        @method('put')

        {{-- Текущий пароль --}}
        <div class="pwd-field">
            <label for="update_password_current_password" class="pwd-label" data-i18n="currentPasswordLabel">
                Текущий пароль
            </label>
            <input id="update_password_current_password" name="current_password" type="password"
                   class="pwd-input"
                   autocomplete="current-password" />
            @if($errors->updatePassword->has('current_password'))
            <div class="pwd-error">{{ $errors->updatePassword->first('current_password') }}</div>
            @endif
        </div>

        {{-- Новый пароль --}}
        <div class="pwd-field">
            <label for="update_password_password" class="pwd-label" data-i18n="newPasswordLabel">
                Новый пароль
            </label>
            <input id="update_password_password" name="password" type="password"
                   class="pwd-input"
                   autocomplete="new-password" />
            @if($errors->updatePassword->has('password'))
            <div class="pwd-error">{{ $errors->updatePassword->first('password') }}</div>
            @endif
        </div>

        {{-- Подтверждение пароля --}}
        <div class="pwd-field">
            <label for="update_password_password_confirmation" class="pwd-label" data-i18n="confirmPasswordLabel">
                Подтвердите пароль
            </label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                   class="pwd-input"
                   autocomplete="new-password" />
            @if($errors->updatePassword->has('password_confirmation'))
            <div class="pwd-error">{{ $errors->updatePassword->first('password_confirmation') }}</div>
            @endif
        </div>

        <div class="pwd-actions">
            <button type="submit" class="pwd-btn-save" data-i18n="btnSave">
                Сохранить изменения
            </button>

            @if (session('status') === 'password-updated')
            <div class="pwd-success" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)">
                <svg xmlns="http://www.w3.org/2000/svg" class="pwd-success-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span data-i18n="statusUpdated">Обновлено</span>
            </div>
            @endif
        </div>
    </form>

    <style>
        /* ============================================ */
        /* === PASSWORD FORM STYLES === */
        /* ============================================ */

        .pwd-section {
            max-width: 600px;
            margin: 0 auto;
        }

        /* Header */
        .pwd-header {
            margin-bottom: 1.5rem;
        }

        .pwd-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0 0 0.5rem;
        }

        .pwd-desc {
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0;
        }

        /* Form */
        .pwd-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        /* Field */
        .pwd-field {
            display: flex;
            flex-direction: column;
        }

        .pwd-label {
            display: block;
            font-size: 0.6875rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #334155;
            margin-bottom: 0.5rem;
        }

        .pwd-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            font-weight: 700;
            color: #000000;
            font-size: 0.875rem;
            outline: none;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .pwd-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            background: #ffffff;
        }

        .pwd-input::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }

        /* Error */
        .pwd-error {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: #ef4444;
            font-weight: 700;
        }

        /* Actions */
        .pwd-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-top: 1rem;
            flex-wrap: wrap;
        }

        .pwd-btn-save {
            padding: 0.75rem 2rem;
            background: #0f172a;
            color: #ffffff;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 0.6875rem;
            letter-spacing: 0.1em;
            border-radius: 0.75rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            white-space: nowrap;
        }

        .pwd-btn-save:hover {
            background: #f59e0b;
            transform: translateY(-2px);
            box-shadow: 0 15px 20px -3px rgba(245, 158, 11, 0.3);
        }

        .pwd-btn-save:active {
            transform: scale(0.95);
        }

        /* Success message */
        .pwd-success {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #059669;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .pwd-success-icon {
            width: 1.25rem;
            height: 1.25rem;
        }

        /* ============================================ */
        /* === ПОЛНАЯ АДАПТИВНОСТЬ === */
        /* ============================================ */

        /* Маленькие десктопы (до 1200px) */
        @media (max-width: 1200px) {
            .pwd-title { font-size: 1.2rem; }
            .pwd-desc { font-size: 0.85rem; }
            .pwd-form { gap: 1.2rem; }
            .pwd-input { padding: 0.725rem 0.975rem; font-size: 0.85rem; }
            .pwd-btn-save { padding: 0.725rem 1.9rem; font-size: 0.675rem; }
        }

        /* Планшеты (до 992px) */
        @media (max-width: 992px) {
            .pwd-header { margin-bottom: 1.4rem; }
            .pwd-title { font-size: 1.15rem; }
            .pwd-desc { font-size: 0.825rem; }
            .pwd-form { gap: 1.15rem; }
            .pwd-label { font-size: 0.675rem; margin-bottom: 0.475rem; letter-spacing: 0.095em; }
            .pwd-input { padding: 0.7rem 0.95rem; font-size: 0.825rem; border-radius: 0.7rem; }
            .pwd-error { font-size: 0.725rem; margin-top: 0.475rem; }
            .pwd-actions { gap: 0.95rem; padding-top: 0.95rem; }
            .pwd-btn-save { padding: 0.7rem 1.85rem; font-size: 0.675rem; border-radius: 0.7rem; }
            .pwd-success { font-size: 0.825rem; gap: 0.475rem; }
            .pwd-success-icon { width: 1.2rem; height: 1.2rem; }
        }

        /* Большие телефоны (до 768px) */
        @media (max-width: 768px) {
            .pwd-header { margin-bottom: 1.3rem; }
            .pwd-title { font-size: 1.1rem; }
            .pwd-desc { font-size: 0.8rem; }
            .pwd-form { gap: 1.1rem; }
            .pwd-label { font-size: 0.65rem; margin-bottom: 0.45rem; letter-spacing: 0.09em; }
            .pwd-input { padding: 0.675rem 0.9rem; font-size: 0.8rem; border-radius: 0.675rem; }
            .pwd-error { font-size: 0.7rem; margin-top: 0.45rem; }
            .pwd-actions { gap: 0.9rem; padding-top: 0.9rem; }
            .pwd-btn-save { padding: 0.675rem 1.8rem; font-size: 0.65rem; border-radius: 0.675rem; }
            .pwd-success { font-size: 0.8rem; gap: 0.45rem; }
            .pwd-success-icon { width: 1.15rem; height: 1.15rem; }
        }

        /* Телефоны (до 640px) */
        @media (max-width: 640px) {
            .pwd-header { margin-bottom: 1.2rem; }
            .pwd-title { font-size: 1.05rem; }
            .pwd-desc { font-size: 0.775rem; }
            .pwd-form { gap: 1.05rem; }
            .pwd-label { font-size: 0.625rem; margin-bottom: 0.425rem; letter-spacing: 0.085em; }
            .pwd-input { padding: 0.65rem 0.875rem; font-size: 0.775rem; border-radius: 0.65rem; }
            .pwd-error { font-size: 0.675rem; margin-top: 0.425rem; }
            .pwd-actions { gap: 0.85rem; padding-top: 0.85rem; }
            .pwd-btn-save {
                padding: 0.65rem 1.75rem;
                font-size: 0.625rem;
                border-radius: 0.65rem;
                width: 100%;
                text-align: center;
            }
            .pwd-success { font-size: 0.775rem; gap: 0.425rem; }
            .pwd-success-icon { width: 1.1rem; height: 1.1rem; }
        }

        /* Маленькие телефоны (до 480px) */
        @media (max-width: 480px) {
            .pwd-header { margin-bottom: 1.1rem; }
            .pwd-title { font-size: 1rem; }
            .pwd-desc { font-size: 0.75rem; }
            .pwd-form { gap: 1rem; }
            .pwd-label { font-size: 0.6rem; margin-bottom: 0.4rem; letter-spacing: 0.08em; }
            .pwd-input { padding: 0.625rem 0.85rem; font-size: 0.75rem; border-radius: 0.625rem; }
            .pwd-error { font-size: 0.65rem; margin-top: 0.4rem; }
            .pwd-actions { gap: 0.8rem; padding-top: 0.8rem; flex-direction: column; align-items: flex-start; }
            .pwd-btn-save { padding: 0.625rem 1.7rem; font-size: 0.6rem; border-radius: 0.625rem; }
            .pwd-success { font-size: 0.75rem; gap: 0.4rem; }
            .pwd-success-icon { width: 1.05rem; height: 1.05rem; }
        }

        /* Очень маленькие телефоны (до 380px) */
        @media (max-width: 380px) {
            .pwd-header { margin-bottom: 1rem; }
            .pwd-title { font-size: 0.95rem; }
            .pwd-desc { font-size: 0.725rem; }
            .pwd-form { gap: 0.95rem; }
            .pwd-label { font-size: 0.575rem; margin-bottom: 0.375rem; letter-spacing: 0.075em; }
            .pwd-input { padding: 0.6rem 0.8rem; font-size: 0.725rem; border-radius: 0.6rem; }
            .pwd-error { font-size: 0.625rem; margin-top: 0.375rem; }
            .pwd-actions { gap: 0.75rem; padding-top: 0.75rem; }
            .pwd-btn-save { padding: 0.6rem 1.65rem; font-size: 0.575rem; border-radius: 0.6rem; }
            .pwd-success { font-size: 0.725rem; gap: 0.375rem; }
            .pwd-success-icon { width: 1rem; height: 1rem; }
        }
    </style>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const PASSWORD_TRANSLATIONS = {
            ru: {
                updatePasswordTitle: 'Обновление пароля',
                updatePasswordDesc: 'Используйте длинный случайный пароль, чтобы ваш аккаунт оставался в безопасности.',
                currentPasswordLabel: 'Текущий пароль',
                newPasswordLabel: 'Новый пароль',
                confirmPasswordLabel: 'Подтвердите пароль',
                btnSave: 'Сохранить изменения',
                statusUpdated: 'Обновлено'
            },
            tj: {
                updatePasswordTitle: 'Навсозии рамз',
                updatePasswordDesc: 'Барои бехатарии аккаунти худ рамзи дароз ва тасодуфиро истифода баред.',
                currentPasswordLabel: 'Рамзи ҷорӣ',
                newPasswordLabel: 'Рамзи нав',
                confirmPasswordLabel: 'Тасдиқи рамз',
                btnSave: 'Захира кардани тағйирот',
                statusUpdated: 'Навсозӣ шуд'
            },
            en: {
                updatePasswordTitle: 'Update Password',
                updatePasswordDesc: 'Ensure your account is using a long, random password to stay secure.',
                currentPasswordLabel: 'Current Password',
                newPasswordLabel: 'New Password',
                confirmPasswordLabel: 'Confirm Password',
                btnSave: 'Save Changes',
                statusUpdated: 'Saved'
            }
        };

        function applyPasswordTranslations(lang) {
            const dict = PASSWORD_TRANSLATIONS[lang] || PASSWORD_TRANSLATIONS.ru;

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
        applyPasswordTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applyPasswordTranslations(lang);
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applyPasswordTranslations(e.newValue);
            }
        });
    });
</script>