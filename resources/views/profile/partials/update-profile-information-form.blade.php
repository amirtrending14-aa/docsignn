<section class="pf-section">
    <header class="pf-header">
        <h2 class="pf-title" data-i18n="profileDataTitle">
            Данные профиля
        </h2>
        <p class="pf-desc" data-i18n="profileDataDesc">
            Обновите информацию вашего аккаунта и адрес электронной почты.
        </p>
    </header>

    {{-- Форма для верификации (скрытая) --}}
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="pf-form" enctype="multipart/form-data" id="profileForm">
        @csrf
        @method('patch')

        {{-- ===== БЛОК ЗАГРУЗКИ ФОТО ===== --}}
        <div class="pf-avatar-block">
            <div class="pf-avatar-content">

                {{-- Аватар с превью --}}
                <div class="pf-avatar-wrapper">
                    <div id="avatarWrapper" class="pf-avatar-box">
                        @if(auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="pf-avatar-img">
                        <img id="avatarPreview" src="" alt="Preview" class="pf-avatar-preview hidden">
                        <span id="avatarLetter" class="pf-avatar-letter hidden">
                            {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        @else
                        <img id="avatarPreview" src="" alt="Preview" class="pf-avatar-preview hidden">
                        <span id="avatarLetter" class="pf-avatar-letter">
                            {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        @endif
                    </div>

                    {{-- Оверлей с иконкой камеры --}}
                    <label for="avatarInput" class="pf-avatar-overlay">
                        <div class="pf-avatar-overlay-content">
                            <svg class="pf-camera-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="pf-camera-text" data-i18n="changePhoto">Изменить</span>
                        </div>
                    </label>

                    {{-- Индикатор онлайн --}}
                    <div class="pf-online-dot"></div>
                </div>

                {{-- Информация и кнопки --}}
                <div class="pf-avatar-info">
                    <h3 class="pf-avatar-title" data-i18n="profilePhotoTitle">Фотография профиля</h3>
                    <p class="pf-avatar-desc" data-i18n="profilePhotoDesc">JPG, PNG или WEBP. Максимум 2MB.</p>

                    <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/webp" class="pf-file-input-hidden" onchange="previewAvatar(this)">

                    {{-- СКРЫТОЕ ПОЛЕ ДЛЯ УДАЛЕНИЯ ФОТО --}}
                    <input type="hidden" id="removeAvatarFlag" name="remove_avatar" value="0">

                    <div class="pf-avatar-buttons">
                        <label for="avatarInput" id="uploadBtn" class="pf-btn-upload">
                            <svg class="pf-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <span data-i18n="btnUpload">Загрузить фото</span>
                        </label>

                        <button type="button" id="removeBtn" onclick="removeAvatar()" class="pf-btn-remove {{ !auth()->user()->avatar ? 'hidden' : '' }}">
                            <svg class="pf-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            <span data-i18n="btnRemovePhoto">Удалить</span>
                        </button>
                    </div>

                    {{-- Имя файла --}}
                    <p id="fileNameDisplay" class="pf-file-name"></p>
                </div>
            </div>

            @error('avatar')
            <div class="pf-error-box">
                <p class="pf-error-text">
                    <svg class="pf-error-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            </div>
            @enderror
        </div>

        {{-- ===== ИМЯ ===== --}}
        <div class="pf-field">
            <label for="name" class="pf-label" data-i18n="labelName">Имя</label>
            <input id="name" name="name" type="text"
                   class="pf-input"
                   value="{{ old('name', auth()->user()->name) }}"
                   required autofocus autocomplete="name" />
            @if($errors->has('name'))
            <div class="pf-field-error">{{ $errors->first('name') }}</div>
            @endif
        </div>

        {{-- ===== КОМПАНИЯ ===== --}}
        <div class="pf-field">
            <label for="company" class="pf-label" data-i18n="labelCompany">Название компании</label>
            <input id="company" name="company" type="text"
                   class="pf-input"
                   value="{{ old('company', auth()->user()->company ?? '') }}"
                   autocomplete="organization" />
            @if($errors->has('company'))
            <div class="pf-field-error">{{ $errors->first('company') }}</div>
            @endif
        </div>

        {{-- ===== EMAIL ===== --}}
        <div class="pf-field">
            <label for="email" class="pf-label" data-i18n="labelEmail">Email</label>
            <input id="email" name="email" type="email"
                   class="pf-input"
                   value="{{ old('email', auth()->user()->email) }}"
                   required autocomplete="username" />
            @if($errors->has('email'))
            <div class="pf-field-error">{{ $errors->first('email') }}</div>
            @endif

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
            <div class="pf-verify-box">
                <p class="pf-verify-text">
                    <span data-i18n="emailUnverified">Ваш адрес электронной почты не подтвержден.</span>
                    <button form="send-verification" class="pf-verify-link" data-i18n="btnResendVerify">
                        Нажмите здесь, чтобы отправить письмо снова.
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                <p class="pf-verify-success" data-i18n="verifyLinkSent">
                    Новая ссылка отправлена на ваш email.
                </p>
                @endif
            </div>
            @endif
        </div>

        {{-- ===== ТЕЛЕФОН ===== --}}
        <div class="pf-field">
            <label class="pf-label" data-i18n="labelPhone">Телефон</label>
            <input name="phone" type="text" id="phone" required
                   class="pf-input pf-phone-input"
                   value="{{ old('phone', auth()->user()->phone ?? '+992 ') }}"
                   placeholder="+992 00 000 0000">
            @if($errors->has('phone'))
            <div class="pf-field-error">{{ $errors->first('phone') }}</div>
            @endif
        </div>

        {{-- ===== КНОПКА СОХРАНЕНИЯ ===== --}}
        <div class="pf-actions">
            <button type="submit" class="pf-btn-save" data-i18n="btnSaveProfile">
                Сохранить профиль
            </button>

            @if (session('status') === 'profile-updated')
            <p class="pf-success" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)">
                <svg xmlns="http://www.w3.org/2000/svg" class="pf-success-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span data-i18n="statusSaved">Сохранено</span>
            </p>
            @endif
        </div>
    </form>

    <style>
        /* ============================================ */
        /* === PROFILE FORM STYLES === */
        /* ============================================ */

        .pf-section {
            max-width: 700px;
            margin: 0 auto;
        }

        /* Header */
        .pf-header {
            margin-bottom: 1.5rem;
        }

        .pf-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 0.5rem;
        }

        .pf-desc {
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0;
        }

        /* Form */
        .pf-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        /* Avatar Block */
        .pf-avatar-block {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            border: 2px solid #e2e8f0;
            background: linear-gradient(to bottom right, #f8fafc, #ffffff);
            padding: 1.5rem;
        }

        .pf-avatar-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }

        @media (min-width: 640px) {
            .pf-avatar-content {
                flex-direction: row;
            }
        }

        /* Avatar Wrapper */
        .pf-avatar-wrapper {
            position: relative;
            flex-shrink: 0;
        }

        .pf-avatar-box {
            width: 7rem;
            height: 7rem;
            border-radius: 1rem;
            overflow: hidden;
            border: 4px solid #ffffff;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            background: linear-gradient(to bottom right, #6366f1, #9333ea);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .pf-avatar-img,
        .pf-avatar-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pf-avatar-preview {
            position: absolute;
            inset: 0;
        }

        .pf-avatar-letter {
            color: #ffffff;
            font-size: 2.25rem;
            font-weight: 900;
            font-style: italic;
            user-select: none;
        }

        /* Avatar Overlay */
        .pf-avatar-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            opacity: 0;
            transition: opacity 0.3s;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            backdrop-filter: blur(4px);
        }

        .pf-avatar-wrapper:hover .pf-avatar-overlay {
            opacity: 1;
        }

        .pf-avatar-overlay-content {
            text-align: center;
            transform: scale(0.75);
            transition: transform 0.3s;
        }

        .pf-avatar-wrapper:hover .pf-avatar-overlay-content {
            transform: scale(1);
        }

        .pf-camera-icon {
            width: 2rem;
            height: 2rem;
            color: #ffffff;
            margin: 0 auto 0.25rem;
            display: block;
        }

        .pf-camera-text {
            color: #ffffff;
            font-size: 0.625rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Online Dot */
        .pf-online-dot {
            position: absolute;
            bottom: -0.25rem;
            right: -0.25rem;
            width: 1.5rem;
            height: 1.5rem;
            background: #10b981;
            border: 4px solid #ffffff;
            border-radius: 9999px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* Avatar Info */
        .pf-avatar-info {
            flex: 1;
            text-align: center;
        }

        @media (min-width: 640px) {
            .pf-avatar-info {
                text-align: left;
            }
        }

        .pf-avatar-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 0.25rem;
        }

        .pf-avatar-desc {
            font-size: 0.75rem;
            color: #64748b;
            margin: 0 0 0.75rem;
        }

        .pf-file-input-hidden {
            display: none;
        }

        .pf-avatar-buttons {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        @media (min-width: 640px) {
            .pf-avatar-buttons {
                justify-content: flex-start;
            }
        }

        .pf-btn-upload,
        .pf-btn-remove {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .pf-btn-upload {
            background: #4f46e5;
            color: #ffffff;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
        }

        .pf-btn-upload:hover {
            background: #4338ca;
        }

        .pf-btn-remove {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .pf-btn-remove:hover {
            background: #fee2e2;
        }

        .pf-btn-icon {
            width: 1rem;
            height: 1rem;
        }

        .pf-file-name {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.5rem;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 20rem;
        }

        /* Error Box */
        .pf-error-box {
            margin-top: 1rem;
            padding: 0.75rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 0.75rem;
        }

        .pf-error-text {
            font-size: 0.875rem;
            color: #dc2626;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .pf-error-icon {
            width: 1rem;
            height: 1rem;
        }

        /* Field */
        .pf-field {
            display: flex;
            flex-direction: column;
        }

        .pf-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.25rem;
        }

        .pf-input {
            width: 100%;
            padding: 0.625rem 1rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            color: #000000;
            outline: none;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .pf-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
            background: #ffffff;
        }

        .pf-phone-input {
            font-weight: 700;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .pf-field-error {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: #ef4444;
        }

        /* Verify Box */
        .pf-verify-box {
            margin-top: 1rem;
            padding: 1rem;
            background: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 0.75rem;
        }

        .pf-verify-text {
            font-size: 0.875rem;
            color: #92400e;
            margin: 0;
        }

        .pf-verify-link {
            display: block;
            margin-top: 0.25rem;
            text-decoration: underline;
            font-weight: 700;
            color: #92400e;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            transition: color 0.2s;
            text-align: left;
        }

        .pf-verify-link:hover {
            color: #78350f;
        }

        .pf-verify-success {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #16a34a;
            font-style: italic;
        }

        /* Actions */
        .pf-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-top: 0.5rem;
            flex-wrap: wrap;
        }

        .pf-btn-save {
            padding: 0.625rem 1.5rem;
            background: #4f46e5;
            color: #ffffff;
            font-weight: 700;
            border-radius: 0.75rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
            transition: all 0.2s;
            font-size: 0.875rem;
        }

        .pf-btn-save:hover {
            background: #4338ca;
        }

        .pf-btn-save:active {
            transform: scale(0.95);
        }

        .pf-success {
            font-size: 0.875rem;
            font-weight: 500;
            color: #059669;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin: 0;
        }

        .pf-success-icon {
            width: 1rem;
            height: 1rem;
        }

        /* ============================================ */
        /* === ПОЛНАЯ АДАПТИВНОСТЬ === */
        /* ============================================ */

        /* Маленькие десктопы (до 1200px) */
        @media (max-width: 1200px) {
            .pf-title { font-size: 1.2rem; }
            .pf-desc { font-size: 0.85rem; }
            .pf-form { gap: 1.2rem; }
            .pf-avatar-block { padding: 1.4rem; }
            .pf-avatar-content { gap: 1.4rem; }
            .pf-avatar-box { width: 6.75rem; height: 6.75rem; }
            .pf-avatar-letter { font-size: 2.1rem; }
            .pf-avatar-title { font-size: 1.1rem; }
            .pf-avatar-desc { font-size: 0.725rem; }
            .pf-input { padding: 0.6rem 0.975rem; font-size: 0.85rem; }
            .pf-btn-save { padding: 0.6rem 1.45rem; font-size: 0.85rem; }
        }

        /* Планшеты (до 992px) */
        @media (max-width: 992px) {
            .pf-header { margin-bottom: 1.4rem; }
            .pf-title { font-size: 1.15rem; }
            .pf-desc { font-size: 0.825rem; }
            .pf-form { gap: 1.15rem; }
            .pf-avatar-block { padding: 1.3rem; border-radius: 0.95rem; }
            .pf-avatar-content { gap: 1.3rem; }
            .pf-avatar-box { width: 6.5rem; height: 6.5rem; border-radius: 0.95rem; }
            .pf-avatar-letter { font-size: 2rem; }
            .pf-camera-icon { width: 1.9rem; height: 1.9rem; }
            .pf-camera-text { font-size: 0.6rem; }
            .pf-online-dot { width: 1.4rem; height: 1.4rem; }
            .pf-avatar-title { font-size: 1.05rem; }
            .pf-avatar-desc { font-size: 0.7rem; margin-bottom: 0.7rem; }
            .pf-btn-upload, .pf-btn-remove { padding: 0.475rem 0.95rem; font-size: 0.7rem; border-radius: 0.7rem; }
            .pf-btn-icon { width: 0.95rem; height: 0.95rem; }
            .pf-file-name { font-size: 0.7rem; max-width: 19rem; }
            .pf-error-box { padding: 0.7rem; border-radius: 0.7rem; }
            .pf-error-text { font-size: 0.825rem; }
            .pf-label { font-size: 0.825rem; }
            .pf-input { padding: 0.6rem 0.95rem; font-size: 0.825rem; border-radius: 0.7rem; }
            .pf-field-error { font-size: 0.7rem; }
            .pf-verify-box { padding: 0.95rem; border-radius: 0.7rem; }
            .pf-verify-text { font-size: 0.825rem; }
            .pf-verify-success { font-size: 0.825rem; }
            .pf-actions { gap: 0.95rem; }
            .pf-btn-save { padding: 0.6rem 1.4rem; font-size: 0.825rem; border-radius: 0.7rem; }
            .pf-success { font-size: 0.825rem; }
            .pf-success-icon { width: 0.95rem; height: 0.95rem; }
        }

        /* Большие телефоны (до 768px) */
        @media (max-width: 768px) {
            .pf-header { margin-bottom: 1.3rem; }
            .pf-title { font-size: 1.1rem; }
            .pf-desc { font-size: 0.8rem; }
            .pf-form { gap: 1.1rem; }
            .pf-avatar-block { padding: 1.2rem; border-radius: 0.9rem; }
            .pf-avatar-content { gap: 1.2rem; }
            .pf-avatar-box { width: 6.25rem; height: 6.25rem; border-radius: 0.9rem; }
            .pf-avatar-letter { font-size: 1.9rem; }
            .pf-camera-icon { width: 1.8rem; height: 1.8rem; }
            .pf-camera-text { font-size: 0.575rem; }
            .pf-online-dot { width: 1.35rem; height: 1.35rem; }
            .pf-avatar-title { font-size: 1rem; }
            .pf-avatar-desc { font-size: 0.675rem; margin-bottom: 0.65rem; }
            .pf-btn-upload, .pf-btn-remove { padding: 0.45rem 0.9rem; font-size: 0.675rem; border-radius: 0.675rem; }
            .pf-btn-icon { width: 0.9rem; height: 0.9rem; }
            .pf-file-name { font-size: 0.675rem; max-width: 18rem; }
            .pf-error-box { padding: 0.65rem; border-radius: 0.675rem; }
            .pf-error-text { font-size: 0.8rem; }
            .pf-label { font-size: 0.8rem; }
            .pf-input { padding: 0.575rem 0.9rem; font-size: 0.8rem; border-radius: 0.675rem; }
            .pf-field-error { font-size: 0.675rem; }
            .pf-verify-box { padding: 0.9rem; border-radius: 0.675rem; }
            .pf-verify-text { font-size: 0.8rem; }
            .pf-verify-success { font-size: 0.8rem; }
            .pf-actions { gap: 0.9rem; }
            .pf-btn-save { padding: 0.575rem 1.35rem; font-size: 0.8rem; border-radius: 0.675rem; }
            .pf-success { font-size: 0.8rem; }
            .pf-success-icon { width: 0.9rem; height: 0.9rem; }
        }

        /* Телефоны (до 640px) */
        @media (max-width: 640px) {
            .pf-header { margin-bottom: 1.2rem; }
            .pf-title { font-size: 1.05rem; }
            .pf-desc { font-size: 0.775rem; }
            .pf-form { gap: 1.05rem; }
            .pf-avatar-block { padding: 1.1rem; border-radius: 0.85rem; }
            .pf-avatar-content { gap: 1.1rem; }
            .pf-avatar-box { width: 6rem; height: 6rem; border-radius: 0.85rem; border-width: 3px; }
            .pf-avatar-letter { font-size: 1.8rem; }
            .pf-camera-icon { width: 1.7rem; height: 1.7rem; }
            .pf-camera-text { font-size: 0.55rem; }
            .pf-online-dot { width: 1.3rem; height: 1.3rem; border-width: 3px; }
            .pf-avatar-title { font-size: 0.95rem; }
            .pf-avatar-desc { font-size: 0.65rem; margin-bottom: 0.6rem; }
            .pf-btn-upload, .pf-btn-remove { padding: 0.425rem 0.85rem; font-size: 0.65rem; border-radius: 0.65rem; }
            .pf-btn-icon { width: 0.85rem; height: 0.85rem; }
            .pf-file-name { font-size: 0.65rem; max-width: 17rem; }
            .pf-error-box { padding: 0.6rem; border-radius: 0.65rem; }
            .pf-error-text { font-size: 0.775rem; }
            .pf-label { font-size: 0.775rem; }
            .pf-input { padding: 0.55rem 0.875rem; font-size: 0.775rem; border-radius: 0.65rem; }
            .pf-field-error { font-size: 0.65rem; }
            .pf-verify-box { padding: 0.85rem; border-radius: 0.65rem; }
            .pf-verify-text { font-size: 0.775rem; }
            .pf-verify-success { font-size: 0.775rem; }
            .pf-actions { gap: 0.85rem; }
            .pf-btn-save {
                padding: 0.55rem 1.3rem;
                font-size: 0.775rem;
                border-radius: 0.65rem;
                width: 100%;
                text-align: center;
            }
            .pf-success { font-size: 0.775rem; }
            .pf-success-icon { width: 0.85rem; height: 0.85rem; }
        }

        /* Маленькие телефоны (до 480px) */
        @media (max-width: 480px) {
            .pf-header { margin-bottom: 1.1rem; }
            .pf-title { font-size: 1rem; }
            .pf-desc { font-size: 0.75rem; }
            .pf-form { gap: 1rem; }
            .pf-avatar-block { padding: 1rem; border-radius: 0.8rem; }
            .pf-avatar-content { gap: 1rem; }
            .pf-avatar-box { width: 5.5rem; height: 5.5rem; border-radius: 0.8rem; }
            .pf-avatar-letter { font-size: 1.7rem; }
            .pf-camera-icon { width: 1.6rem; height: 1.6rem; }
            .pf-camera-text { font-size: 0.525rem; }
            .pf-online-dot { width: 1.2rem; height: 1.2rem; }
            .pf-avatar-title { font-size: 0.9rem; }
            .pf-avatar-desc { font-size: 0.625rem; margin-bottom: 0.55rem; }
            .pf-btn-upload, .pf-btn-remove { padding: 0.4rem 0.8rem; font-size: 0.625rem; border-radius: 0.6rem; }
            .pf-btn-icon { width: 0.8rem; height: 0.8rem; }
            .pf-file-name { font-size: 0.625rem; max-width: 16rem; }
            .pf-error-box { padding: 0.55rem; border-radius: 0.6rem; }
            .pf-error-text { font-size: 0.75rem; }
            .pf-label { font-size: 0.75rem; }
            .pf-input { padding: 0.525rem 0.85rem; font-size: 0.75rem; border-radius: 0.6rem; }
            .pf-field-error { font-size: 0.625rem; }
            .pf-verify-box { padding: 0.8rem; border-radius: 0.6rem; }
            .pf-verify-text { font-size: 0.75rem; }
            .pf-verify-success { font-size: 0.75rem; }
            .pf-actions { gap: 0.8rem; flex-direction: column; align-items: flex-start; }
            .pf-btn-save { padding: 0.525rem 1.25rem; font-size: 0.75rem; border-radius: 0.6rem; }
            .pf-success { font-size: 0.75rem; }
            .pf-success-icon { width: 0.8rem; height: 0.8rem; }
        }

        /* Очень маленькие телефоны (до 380px) */
        @media (max-width: 380px) {
            .pf-header { margin-bottom: 1rem; }
            .pf-title { font-size: 0.95rem; }
            .pf-desc { font-size: 0.725rem; }
            .pf-form { gap: 0.95rem; }
            .pf-avatar-block { padding: 0.9rem; border-radius: 0.75rem; }
            .pf-avatar-content { gap: 0.9rem; }
            .pf-avatar-box { width: 5rem; height: 5rem; border-radius: 0.75rem; }
            .pf-avatar-letter { font-size: 1.6rem; }
            .pf-camera-icon { width: 1.5rem; height: 1.5rem; }
            .pf-camera-text { font-size: 0.5rem; }
            .pf-online-dot { width: 1.1rem; height: 1.1rem; }
            .pf-avatar-title { font-size: 0.85rem; }
            .pf-avatar-desc { font-size: 0.6rem; margin-bottom: 0.5rem; }
            .pf-btn-upload, .pf-btn-remove { padding: 0.375rem 0.75rem; font-size: 0.6rem; border-radius: 0.55rem; }
            .pf-btn-icon { width: 0.75rem; height: 0.75rem; }
            .pf-file-name { font-size: 0.6rem; max-width: 15rem; }
            .pf-error-box { padding: 0.5rem; border-radius: 0.55rem; }
            .pf-error-text { font-size: 0.725rem; }
            .pf-label { font-size: 0.725rem; }
            .pf-input { padding: 0.5rem 0.8rem; font-size: 0.725rem; border-radius: 0.55rem; }
            .pf-field-error { font-size: 0.6rem; }
            .pf-verify-box { padding: 0.75rem; border-radius: 0.55rem; }
            .pf-verify-text { font-size: 0.725rem; }
            .pf-verify-success { font-size: 0.725rem; }
            .pf-actions { gap: 0.75rem; }
            .pf-btn-save { padding: 0.5rem 1.2rem; font-size: 0.725rem; border-radius: 0.55rem; }
            .pf-success { font-size: 0.725rem; }
            .pf-success-icon { width: 0.75rem; height: 0.75rem; }
        }
    </style>
</section>

<script>
    const PROFILE_INFO_TRANSLATIONS = {
        ru: {
            profileDataTitle: 'Данные профиля',
            profileDataDesc: 'Обновите информацию вашего аккаунта и адрес электронной почты.',
            profilePhotoTitle: 'Фотография профиля',
            profilePhotoDesc: 'JPG, PNG или WEBP. Максимум 2MB.',
            changePhoto: 'Изменить',
            btnUpload: 'Загрузить фото',
            btnRemovePhoto: 'Удалить',
            labelName: 'Имя',
            labelCompany: 'Название компании',
            labelEmail: 'Email',
            labelPhone: 'Телефон',
            emailUnverified: 'Ваш адрес электронной почты не подтвержден.',
            btnResendVerify: 'Нажмите здесь, чтобы отправить письмо снова.',
            verifyLinkSent: 'Новая ссылка отправлена на ваш email.',
            btnSaveProfile: 'Сохранить профиль',
            statusSaved: 'Сохранено',
            alertFileTooLarge: 'Файл слишком большой. Максимум 2MB',
            alertRemovePhoto: 'Удалить фотографию профиля?',
            alertPhoneInvalid: 'Пожалуйста, введите номер телефона полностью (9 цифр после +992)'
        },
        tj: {
            profileDataTitle: 'Маълумоти профил',
            profileDataDesc: 'Маълумоти аккаунт ва суроғаи почтаи электронии худро навсозӣ кунед.',
            profilePhotoTitle: 'Расми профил',
            profilePhotoDesc: 'JPG, PNG ё WEBP. Ҳадди аксар 2MB.',
            changePhoto: 'Тағйир додан',
            btnUpload: 'Боргузории расм',
            btnRemovePhoto: 'Нест кардан',
            labelName: 'Ном',
            labelCompany: 'Номи ширкат',
            labelEmail: 'Email',
            labelPhone: 'Телефон',
            emailUnverified: 'Суроғаи почтаи электронии шумо тасдиқ нашудааст.',
            btnResendVerify: 'Барои дубора фиристодани мактуб инҷоро пахш кунед.',
            verifyLinkSent: 'Пайванди нав ба почтаи электронии шумо фиристода шуд.',
            btnSaveProfile: 'Захираи профил',
            statusSaved: 'Захира шуд',
            alertFileTooLarge: 'Файл хеле калон аст. Ҳадди аксар 2MB',
            alertRemovePhoto: 'Расми профилро нест мекунед?',
            alertPhoneInvalid: 'Лутфан, рақами телефонро пурра ворид кунед (9 рақам пас аз +992)'
        },
        en: {
            profileDataTitle: 'Profile Information',
            profileDataDesc: "Update your account's profile information and email address.",
            profilePhotoTitle: 'Profile Photo',
            profilePhotoDesc: 'JPG, PNG or WEBP. Maximum 2MB.',
            changePhoto: 'Change',
            btnUpload: 'Upload Photo',
            btnRemovePhoto: 'Remove',
            labelName: 'Name',
            labelCompany: 'Company Name',
            labelEmail: 'Email',
            labelPhone: 'Phone',
            emailUnverified: 'Your email address is unverified.',
            btnResendVerify: 'Click here to re-send the verification email.',
            verifyLinkSent: 'A new verification link has been sent to your email address.',
            btnSaveProfile: 'Save Profile',
            statusSaved: 'Saved',
            alertFileTooLarge: 'File too large. Maximum 2MB',
            alertRemovePhoto: 'Remove profile photo?',
            alertPhoneInvalid: 'Please enter the full phone number (9 digits after +992)'
        }
    };

    function applyProfileInfoTranslations(lang) {
        const dict = PROFILE_INFO_TRANSLATIONS[lang] || PROFILE_INFO_TRANSLATIONS.ru;

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

    function getCurrentDict() {
        const lang = localStorage.getItem('docsign_lang') || 'ru';
        return PROFILE_INFO_TRANSLATIONS[lang] || PROFILE_INFO_TRANSLATIONS.ru;
    }

    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];

            if (file.size > 2 * 1024 * 1024) {
                const dict = getCurrentDict();
                alert(dict.alertFileTooLarge);
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                const preview = document.getElementById('avatarPreview');
                const letter = document.getElementById('avatarLetter');
                const removeFlag = document.getElementById('removeAvatarFlag');
                const removeBtn = document.getElementById('removeBtn');
                const fileNameDisplay = document.getElementById('fileNameDisplay');

                if (preview) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                }

                if (letter) letter.classList.add('hidden');

                const currentAvatar = document.querySelector('#avatarWrapper > img:not(#avatarPreview)');
                if (currentAvatar) currentAvatar.classList.add('hidden');

                removeFlag.value = '0';

                if (removeBtn) removeBtn.classList.remove('hidden');

                if (fileNameDisplay) {
                    fileNameDisplay.textContent = '📎 ' + file.name;
                }
            };
            reader.readAsDataURL(file);
        }
    }

    function removeAvatar() {
        const dict = getCurrentDict();

        if (confirm(dict.alertRemovePhoto)) {
            const preview = document.getElementById('avatarPreview');
            const letter = document.getElementById('avatarLetter');
            const input = document.getElementById('avatarInput');
            const removeFlag = document.getElementById('removeAvatarFlag');
            const removeBtn = document.getElementById('removeBtn');
            const fileNameDisplay = document.getElementById('fileNameDisplay');

            if (preview) {
                preview.src = '';
                preview.classList.add('hidden');
            }

            if (letter) letter.classList.remove('hidden');

            const currentAvatar = document.querySelector('#avatarWrapper > img:not(#avatarPreview)');
            if (currentAvatar) currentAvatar.classList.add('hidden');

            if (input) input.value = '';

            removeFlag.value = '1';

            if (removeBtn) removeBtn.classList.add('hidden');

            if (fileNameDisplay) fileNameDisplay.textContent = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const phoneInput = document.getElementById('phone');
        const form = document.getElementById('profileForm');
        const prefix = '+992 ';

        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applyProfileInfoTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applyProfileInfoTranslations(lang);
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applyProfileInfoTranslations(e.newValue);
            }
        });

        if (phoneInput) {
            phoneInput.addEventListener('input', function (e) {
                if (!e.target.value.startsWith(prefix)) e.target.value = prefix;
                let digits = e.target.value.substring(prefix.length).replace(/\D/g, '').substring(0, 9);
                let formatted = '';
                if (digits.length > 0) formatted += digits.substring(0, 2);
                if (digits.length >= 3) formatted += ' ' + digits.substring(2, 5);
                if (digits.length >= 6) formatted += ' ' + digits.substring(5, 7);
                if (digits.length >= 8) formatted += ' ' + digits.substring(7, 9);
                e.target.value = prefix + formatted;
            });
        }

        if (form && phoneInput) {
            form.addEventListener('submit', function (e) {
                let digitsOnly = phoneInput.value.substring(prefix.length).replace(/\D/g, '');
                if (digitsOnly.length < 9) {
                    e.preventDefault();
                    phoneInput.style.border = '2px solid #ef4444';
                    phoneInput.focus();
                    const dict = getCurrentDict();
                    alert(dict.alertPhoneInvalid);
                }
            });
        }
    });
</script>