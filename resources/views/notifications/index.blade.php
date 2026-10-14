@extends('layouts.admin')

@section('content')
<div class="notif-container">
    <div class="notif-header">
        <div class="max-w-5xl mx-auto mb-10 flex justify-between items-end w-full">

            <h1 class="text-xl font-bold doc-main-title tracking-tight flex items-center gap-2">
                <span class="w-2 h-6 bg-blue-500 rounded-full shadow-[0_0_10px_rgba(59,130,246,0.5)]"></span>
                <span data-i18n="notifTitle">Уведомления</span>
            </h1>

            <div class="header-right-actions" style="display: flex; align-items: center; gap: 15px;">
                @if(isset($unreadCount) && $unreadCount > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST" style="display:inline;" data-confirm-i18n="confirmReadAll">
                    @csrf
                    <button type="submit" class="btn-read-all" data-i18n="btnReadAll">Прочитать все</button>
                </form>
                <span class="unread-count"><span data-i18n="newNotifs">У вас новые:</span> {{ $unreadCount }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="notif-list">
        @forelse($notifications as $n)
        @php
        // 1. Декодируем JSON данные уведомления
        $data = is_array($n->data) ? $n->data : json_decode($n->data, true);
        $docId = $data['document_id'] ?? ($data['id'] ?? null);
        $type = $data['type'] ?? ($n->type ?? 'system');

        // 2. Умный поиск реального имени отправителя по всем ключам JSON
        $userName = $data['user_name'] ??
        ($data['sender_name'] ??
        ($data['from_user_name'] ??
        ($data['user']['name'] ??
        ($data['user'] ??
        ($data['sender'] ?? null)))));

        // Ищем email в JSON, если он там вдруг есть
        $userEmail = $data['user_email'] ?? ($data['sender_email'] ?? null);

        // 3. Умный поиск названия документа в JSON
        $docTitle = $data['document_title'] ?? ($data['document'] ?? ($data['title'] ?? null));

        // ====================================================================
        // СУПЕР-ЗАЩИТА: Если в JSON пусто, идем напрямую в базу данных через Модели!
        // ====================================================================
        if ($docId) {
        // Пытаемся найти документ в БД
        $dbDoc = \App\Models\Document::find($docId);

        if ($dbDoc) {
        // Если названия документа нет в JSON — берем реальное из базы
        if (!$docTitle) {
        $docTitle = $dbDoc->title;
        }

        // Если имени или email нет, ищем юзера-создателя
        if (!$userName || !$userEmail) {
        $creatorUser = null;
        if ($dbDoc->creator) {
        $creatorUser = $dbDoc->creator;
        } else {
        $creatorUser = \App\Models\User::find($dbDoc->created_by);
        }

        if ($creatorUser) {
        if (!$userName) {
        $userName = $creatorUser->name;
        }
        if (!$userEmail) {
        $userEmail = $creatorUser->email;
        }
        }
        }
        }
        }

        // Если имя нашли, а email всё еще нет, попробуем найти юзера по имени
        if ($userName && !$userEmail && $userName !== 'Владелец') {
        $findUser = \App\Models\User::where('name', $userName)->first();
        if ($findUser) {
        $userEmail = $findUser->email;
        }
        }

        // Если вообще ничего не помогло, ставим дефолтные значения
        if (!$userName) $userName = 'Владелец';
        if (!$docTitle) $docTitle = 'Документ #' . ($docId ?? '');
        // ====================================================================

        $commentText = $data['comment_preview'] ?? ($data['comment_text'] ?? null);

        $actionKey = match($type) {
        'assigned' => 'notifAssigned',
        'comment'  => 'notifCommented',
        'signed'   => 'notifSigned',
        'created'  => 'notifCreated',
        default    => 'notifDefault'
        };
        @endphp

        <div class="notif-card {{ !$n->is_read ? 'unread' : '' }}">
            <div class="notif-item">
                <div class="notif-icon-wrapper type-{{ $type }}">
                    @switch($type)
                    @case('assigned') <span class="icon">📌</span> @break
                    @case('comment')  <span class="icon">💬</span> @break
                    @case('signed')   <span class="icon">✍️</span> @break
                    @case('created')  <span class="icon">📄</span> @break
                    @default          <span class="icon">🔔</span>
                    @endswitch
                </div>

                <div class="notif-content">
                    <div class="notif-title">
                                <span class="user-name">
                                    {{ $userName }}
                                    @if($userEmail)
                                        <span class="user-email">({{ $userEmail }})</span>
                                    @endif
                                </span>
                        <span data-i18n="{{ $actionKey }}"></span>

                        @if($docId)
                        <a href="{{ url('/documents/' . $docId) }}" class="doc-link">
                            «{{ $docTitle }}»
                        </a>
                        @else
                        <span class="doc-name-no-link">«{{ $docTitle }}»</span>
                        @endif
                    </div>

                    @if($commentText)
                    <div class="notif-quote">{{ $commentText }}</div>
                    @endif

                    <div class="notif-meta">
                                <span class="time-tag">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    {{ $n->created_at->diffForHumans() }}
                                </span>

                        <div class="notif-actions">
                            @if(!$n->is_read)
                            <form action="{{ route('notifications.read', $n->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="action-link mark-read" data-i18n="btnMarkRead">Прочитать</button>
                            </form>
                            @endif

                            <form action="{{ route('notifications.destroy', $n->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-link delete" data-i18n="btnDelete" data-confirm-i18n="confirmDelete">Удалить</button>
                            </form>
                        </div>
                    </div>
                </div>

                @if(!$n->is_read)
                <div class="unread-dot"></div>
                @endif
            </div>
        </div>
        @empty
        <div class="notif-empty">
            <div class="empty-icon">🔔</div>
            <p data-i18n="noNotifs">У вас пока нет уведомлений</p>
        </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="notif-pagination">
        {{ $notifications->links() }}
    </div>
    @endif
</div>

<style>
    :root {
        --primary-color: #4f46e5;
        --bg-unread: #f8faff;
        --text-main: #1f2937;
        --text-muted: #6b7280;
        --border-color: #cbd5e1;
        --border-unread: rgba(79, 70, 229, 0.4);
        --danger: #ef4444;
    }

    .notif-container {
        max-width: 700px;
        margin: 20px auto;
        padding: 0 15px;
        font-family: 'Inter', sans-serif;
    }

    .notif-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .doc-main-title {
        font-size: 20px;
    }

    .unread-count {
        font-size: 13px;
        background: var(--primary-color);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 600;
        white-space: nowrap;
    }

    .notif-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .btn-read-all {
        background: transparent;
        color: var(--primary-color);
        border: 1.5px solid var(--primary-color);
        padding: 6px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .btn-read-all:hover {
        background: var(--primary-color);
        color: white;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.2);
    }

    .notif-card {
        background: #ffffff;
        border: 1.5px solid var(--border-color);
        border-radius: 16px;
        transition: all 0.2s ease;
        position: relative;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
    }
    .notif-card:hover {
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
        border-color: #94a3b8;
    }
    .notif-item {
        display: flex;
        gap: 16px;
        padding: 16px;
    }

    .notif-card.unread {
        background: var(--bg-unread);
        border-color: var(--border-unread);
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.05);
    }
    .notif-card.unread:hover {
        border-color: var(--primary-color);
    }

    .notif-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
        border: 1px solid rgba(0,0,0,0.04);
    }
    .type-assigned { background: #fff7ed; }
    .type-comment  { background: #f0fdf4; }
    .type-signed   { background: #eff6ff; }
    .type-created  { background: #f5f3ff; }

    .notif-content {
        flex: 1;
        position: relative;
        min-width: 0;
    }

    .notif-title {
        font-size: 15px;
        line-height: 1.4;
        color: var(--text-main);
        word-wrap: break-word;
    }

    .user-name {
        font-weight: 700;
    }

    .user-email {
        font-weight: 400;
        color: var(--text-muted);
        font-size: 13px;
        margin-left: 4px;
    }

    .doc-link {
        font-weight: 700;
        color: var(--primary-color);
        text-decoration: underline;
        padding: 2px 4px;
        border-radius: 4px;
        background: rgba(79, 70, 229, 0.05);
        word-break: break-word;
    }

    .doc-name-no-link {
        color: #9ca3af;
        font-size: 12px;
    }

    .notif-quote {
        margin-top: 8px;
        padding: 10px;
        background: rgba(0,0,0,0.03);
        border-radius: 8px;
        font-size: 13px;
        color: #4b5563;
        border-left: 3px solid #cbd5e1;
        font-style: italic;
        border-top: 1px solid rgba(0,0,0,0.02);
        border-right: 1px solid rgba(0,0,0,0.02);
        border-bottom: 1px solid rgba(0,0,0,0.02);
        word-wrap: break-word;
    }

    .notif-meta {
        margin-top: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .time-tag {
        font-size: 12px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .notif-actions {
        display: flex;
        gap: 12px;
        position: relative;
        z-index: 10;
    }

    .action-link {
        background: none;
        border: none;
        padding: 0;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .action-link:hover {
        opacity: 0.8;
        text-decoration: underline;
    }
    .mark-read {
        color: var(--primary-color);
    }
    .delete {
        color: var(--text-muted);
    }
    .delete:hover {
        color: var(--danger);
    }

    .unread-dot {
        width: 10px;
        height: 10px;
        background: var(--primary-color);
        border-radius: 50%;
        position: absolute;
        right: 16px;
        top: 20px;
    }

    .notif-empty {
        text-align: center;
        padding: 40px;
        border: 1.5px dashed var(--border-color);
        border-radius: 16px;
        color: var(--text-muted);
    }
    .empty-icon {
        font-size: 32px;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .notif-pagination {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }

    /* ============================================ */
    /* === ПОЛНАЯ АДАПТИВНОСТЬ === */
    /* ============================================ */

    /* Маленькие десктопы (до 1200px) */
    @media (max-width: 1200px) {
        .notif-container { max-width: 100%; padding: 0 12px; }
        .doc-main-title { font-size: 19px; }
        .notif-item { padding: 14px; gap: 14px; }
        .notif-icon-wrapper { width: 44px; height: 44px; font-size: 20px; }
        .notif-title { font-size: 14px; }
        .notif-quote { font-size: 12px; padding: 9px; }
    }

    /* Планшеты (до 992px) */
    @media (max-width: 992px) {
        .notif-container { margin: 16px auto; }
        .notif-header { margin-bottom: 16px; }
        .doc-main-title { font-size: 18px; }
        .header-right-actions { gap: 12px; }
        .btn-read-all { padding: 5px 14px; font-size: 12px; }
        .unread-count { font-size: 12px; padding: 3px 10px; }
        .notif-list { gap: 10px; }
        .notif-card { border-radius: 14px; }
        .notif-item { padding: 14px; gap: 12px; }
        .notif-icon-wrapper { width: 42px; height: 42px; font-size: 19px; border-radius: 10px; }
        .notif-title { font-size: 14px; }
        .user-email { font-size: 12px; }
        .notif-quote { font-size: 12px; padding: 8px; margin-top: 7px; }
        .notif-meta { margin-top: 8px; }
        .time-tag { font-size: 11px; }
        .time-tag svg { width: 11px; height: 11px; }
        .action-link { font-size: 11px; }
        .unread-dot { width: 9px; height: 9px; right: 14px; top: 18px; }
        .notif-empty { padding: 32px; }
        .empty-icon { font-size: 28px; }
    }

    /* Большие телефоны (до 768px) */
    @media (max-width: 768px) {
        .notif-container { margin: 14px auto; padding: 0 10px; }
        .notif-header { margin-bottom: 14px; flex-wrap: wrap; gap: 10px; }
        .doc-main-title { font-size: 17px; gap: 6px; }
        .doc-main-title span:first-child { width: 6px; height: 20px; }
        .header-right-actions { gap: 10px; }
        .btn-read-all { padding: 5px 12px; font-size: 11px; border-radius: 7px; }
        .unread-count { font-size: 11px; padding: 3px 9px; border-radius: 18px; }
        .notif-list { gap: 9px; }
        .notif-card { border-radius: 12px; }
        .notif-item { padding: 12px; gap: 10px; }
        .notif-icon-wrapper { width: 40px; height: 40px; font-size: 18px; border-radius: 9px; }
        .notif-title { font-size: 13px; line-height: 1.35; }
        .user-name { font-size: 13px; }
        .user-email { font-size: 11px; margin-left: 3px; }
        .doc-link { font-size: 13px; padding: 1px 3px; }
        .doc-name-no-link { font-size: 11px; }
        .notif-quote { font-size: 11px; padding: 8px; margin-top: 6px; border-radius: 7px; }
        .notif-meta { margin-top: 7px; gap: 6px; }
        .time-tag { font-size: 10px; gap: 3px; }
        .time-tag svg { width: 10px; height: 10px; }
        .notif-actions { gap: 10px; }
        .action-link { font-size: 11px; }
        .unread-dot { width: 8px; height: 8px; right: 12px; top: 16px; }
        .notif-empty { padding: 28px; border-radius: 14px; }
        .empty-icon { font-size: 26px; margin-bottom: 8px; }
        .notif-empty p { font-size: 13px; }
    }

    /* Телефоны (до 640px) */
    @media (max-width: 640px) {
        .notif-container { margin: 12px auto; padding: 0 8px; }
        .notif-header { margin-bottom: 12px; }
        .doc-main-title { font-size: 16px; }
        .header-right-actions { gap: 8px; flex-wrap: wrap; }
        .btn-read-all { padding: 4px 10px; font-size: 10px; border-radius: 6px; }
        .unread-count { font-size: 10px; padding: 3px 8px; }
        .notif-list { gap: 8px; }
        .notif-card { border-radius: 10px; }
        .notif-item { padding: 11px; gap: 9px; }
        .notif-icon-wrapper { width: 38px; height: 38px; font-size: 17px; border-radius: 8px; }
        .notif-title { font-size: 12px; line-height: 1.3; }
        .user-name { font-size: 12px; }
        .user-email { font-size: 10px; display: block; margin-left: 0; margin-top: 2px; }
        .doc-link { font-size: 12px; }
        .doc-name-no-link { font-size: 10px; }
        .notif-quote { font-size: 11px; padding: 7px; margin-top: 6px; }
        .notif-meta { margin-top: 6px; gap: 5px; flex-direction: column; align-items: flex-start; }
        .time-tag { font-size: 10px; }
        .time-tag svg { width: 10px; height: 10px; }
        .notif-actions { gap: 8px; width: 100%; justify-content: flex-start; }
        .action-link { font-size: 10px; }
        .unread-dot { width: 7px; height: 7px; right: 11px; top: 14px; }
        .notif-empty { padding: 24px; }
        .empty-icon { font-size: 24px; }
        .notif-empty p { font-size: 12px; }
    }

    /* Маленькие телефоны (до 480px) */
    @media (max-width: 480px) {
        .notif-container { margin: 10px auto; padding: 0 6px; }
        .notif-header { margin-bottom: 10px; }
        .doc-main-title { font-size: 15px; }
        .header-right-actions { gap: 6px; }
        .btn-read-all { padding: 4px 9px; font-size: 10px; }
        .unread-count { font-size: 9px; padding: 2px 7px; }
        .notif-list { gap: 7px; }
        .notif-card { border-radius: 9px; }
        .notif-item { padding: 10px; gap: 8px; }
        .notif-icon-wrapper { width: 36px; height: 36px; font-size: 16px; border-radius: 7px; }
        .notif-title { font-size: 11px; }
        .user-name { font-size: 11px; }
        .user-email { font-size: 9px; }
        .doc-link { font-size: 11px; }
        .notif-quote { font-size: 10px; padding: 6px; }
        .notif-meta { margin-top: 5px; gap: 4px; }
        .time-tag { font-size: 9px; }
        .time-tag svg { width: 9px; height: 9px; }
        .notif-actions { gap: 7px; }
        .action-link { font-size: 10px; }
        .unread-dot { width: 6px; height: 6px; right: 10px; top: 13px; }
        .notif-empty { padding: 20px; }
        .empty-icon { font-size: 22px; }
        .notif-empty p { font-size: 11px; }
    }

    /* Очень маленькие телефоны (до 380px) */
    @media (max-width: 380px) {
        .notif-container { margin: 8px auto; padding: 0 5px; }
        .notif-header { margin-bottom: 8px; }
        .doc-main-title { font-size: 14px; }
        .header-right-actions { gap: 5px; }
        .btn-read-all { padding: 3px 8px; font-size: 9px; }
        .unread-count { font-size: 9px; padding: 2px 6px; }
        .notif-list { gap: 6px; }
        .notif-card { border-radius: 8px; }
        .notif-item { padding: 9px; gap: 7px; }
        .notif-icon-wrapper { width: 34px; height: 34px; font-size: 15px; }
        .notif-title { font-size: 11px; }
        .user-name { font-size: 11px; }
        .user-email { font-size: 9px; }
        .notif-quote { font-size: 10px; padding: 5px; }
        .notif-meta { margin-top: 4px; }
        .time-tag { font-size: 9px; }
        .notif-actions { gap: 6px; }
        .action-link { font-size: 9px; }
        .unread-dot { width: 6px; height: 6px; right: 9px; top: 12px; }
        .notif-empty { padding: 18px; }
        .empty-icon { font-size: 20px; }
        .notif-empty p { font-size: 10px; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ============================================================
        // ЛОКАЛЬНЫЙ СЛОВАРЬ СТРАНИЦЫ УВЕДОМЛЕНИЙ
        // (дополняет глобальный TRANSLATIONS из layouts/admin.blade.php)
        // ============================================================
        const NOTIF_TRANSLATIONS = {
            ru: {
                notifTitle: 'Уведомления',
                newNotifs: 'У вас новые:',
                notifAssigned: ' назначил вам документ',
                notifCommented: ' оставил комментарий к ',
                notifSigned: ' подписал документ',
                notifCreated: ' создал документ',
                notifDefault: ' отправил уведомление',
                btnMarkRead: 'Прочитать',
                btnReadAll: 'Прочитать все',
                btnDelete: 'Удалить',
                noNotifs: 'У вас пока нет уведомлений',
                confirmDelete: 'Удалить?',
                confirmReadAll: 'Вы уверены, что хотите отметить все уведомления как прочитанные?'
            },
            tj: {
                notifTitle: 'Огоҳиномаҳо',
                newNotifs: 'Нав доред:',
                notifAssigned: ' ҳуҷҷатро ба шумо супорид',
                notifCommented: ' шарҳ гузошт ба ',
                notifSigned: ' ҳуҷҷатро имзо кард',
                notifCreated: ' ҳуҷҷат сохт',
                notifDefault: ' огоҳинома фиристод',
                btnMarkRead: 'Хондан',
                btnReadAll: 'Ҳамаро хондан',
                btnDelete: 'Нест кардан',
                noNotifs: 'Шумо огоҳинома надоред',
                confirmDelete: 'Нест кунем?',
                confirmReadAll: 'Шумо мутмаин ҳастед, ки мехоҳед ҳамаи огоҳиномаҳоро ҳамчун хондашуда қайд кунед?'
            },
            en: {
                notifTitle: 'Notifications',
                newNotifs: 'New ones:',
                notifAssigned: ' assigned a document',
                notifCommented: ' commented on ',
                notifSigned: ' signed the document',
                notifCreated: ' created a document',
                notifDefault: ' sent a notification',
                btnMarkRead: 'Read',
                btnReadAll: 'Read all',
                btnDelete: 'Delete',
                noNotifs: 'No notifications',
                confirmDelete: 'Delete?',
                confirmReadAll: 'Are you sure you want to mark all notifications as read?'
            }
        };

        // ============================================================
        // ФУНКЦИЯ ПРИМЕНЕНИЯ ПЕРЕВОДОВ НА ЭТОЙ СТРАНИЦЕ
        // ============================================================
        function applyNotifTranslations(lang) {
            const dict = NOTIF_TRANSLATIONS[lang] || NOTIF_TRANSLATIONS.ru;

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

            // 3) Переводим title
            document.querySelectorAll('[data-i18n-title]').forEach(el => {
                const key = el.getAttribute('data-i18n-title');
                if (dict[key] !== undefined) el.setAttribute('title', dict[key]);
            });

            // 4) Обработка confirm-диалогов (и на формах, и на кнопках)
            document.querySelectorAll('[data-confirm-i18n]').forEach(el => {
                const key = el.getAttribute('data-confirm-i18n');
                const message = dict[key] || 'Are you sure?';

                // Клонируем элемент, чтобы сбросить старые обработчики
                const newEl = el.cloneNode(true);
                el.parentNode.replaceChild(newEl, el);

                // Если дата-атрибут висит на самой форме
                if (newEl.tagName === 'FORM') {
                    newEl.onsubmit = (e) => {
                        if (!confirm(message)) e.preventDefault();
                    };
                } else {
                    // Если висит на кнопке внутри формы
                    const form = newEl.closest('form');
                    if (form) {
                        form.onsubmit = (e) => {
                            if (!confirm(message)) e.preventDefault();
                        };
                    }
                }
            });
        }

        // ============================================================
        // 1. Применяем сразу при загрузке
        // ============================================================
        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applyNotifTranslations(initialLang);

        // ============================================================
        // 2. Слушаем событие смены языка от layouts/admin.blade.php
        //    (когда юзер кликает на 🇷🇺/🇹🇯/🇬🇧 в админке)
        // ============================================================
        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applyNotifTranslations(lang);
        });

        // ============================================================
        // 3. Синхронизация между вкладками браузера
        // ============================================================
        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applyNotifTranslations(e.newValue);
            }
        });
    });
</script>
@endsection