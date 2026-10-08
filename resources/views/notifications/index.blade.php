@extends('layouts.admin')

@section('content')
<div class="notif-container">
    <div class="notif-header">
        <div class="header-inner">
            <h1 class="doc-main-title tracking-tight flex items-center gap-3">
                <span class="title-accent"></span>
                <span data-i18n="notifTitle">Уведомления</span>
            </h1>

            <div class="header-right-actions">
                @if(isset($unreadCount) && $unreadCount > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST" style="display:inline;" data-confirm-i18n="confirmReadAll">
                    @csrf
                    <button type="submit" class="btn-read-all" data-i18n="btnReadAll">Прочитать все</button>
                </form>
                <span class="unread-count">
                    <span class="pulse-dot"></span>
                    <span data-i18n="newNotifs">Новые:</span> {{ $unreadCount }}
                </span>
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
                        <span class="action-text" data-i18n="{{ $actionKey }}"></span>

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
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ $n->created_at->diffForHumans() }}
                        </span>

                        <div class="notif-actions">
                            @if(!$n->is_read)
                            <form action="{{ route('notifications.read', $n->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="action-btn mark-read" data-i18n="btnMarkRead">Прочитать</button>
                            </form>
                            @endif

                            <form action="{{ route('notifications.destroy', $n->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn delete" data-i18n="btnDelete" data-confirm-i18n="confirmDelete">Удалить</button>
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
        --primary-hover: #4338ca;
        --bg-unread: #f8faff;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --border-unread: #c7d2fe;
        --danger: #ef4444;
        --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        --card-shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
    }

    /* Основной контейнер - делаем ШИРЕ */
    .notif-container {
        max-width: 1000px; /* Было 700px, стало 1000px - займет больше места */
        width: 100%;
        margin: 40px auto;
        padding: 0 24px;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    /* Шапка */
    .notif-header {
        margin-bottom: 32px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 20px;
    }

    .header-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .doc-main-title {
        font-size: 28px; /* Увеличили заголовок */
        font-weight: 800;
        color: var(--text-main);
        margin: 0;
        letter-spacing: -0.025em;
    }

    .title-accent {
        display: inline-block;
        width: 6px;
        height: 28px;
        background: linear-gradient(180deg, #6366f1, #4f46e5);
        border-radius: 4px;
        box-shadow: 0 0 12px rgba(79, 70, 229, 0.4);
    }

    .header-right-actions {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .unread-count {
        font-size: 14px;
        background: #eef2ff;
        color: var(--primary-color);
        padding: 8px 16px;
        border-radius: 30px;
        font-weight: 600;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid var(--border-unread);
    }

    .pulse-dot {
        width: 8px;
        height: 8px;
        background-color: var(--primary-color);
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(79, 70, 229, 0); }
        100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
    }

    .btn-read-all {
        background: #ffffff;
        color: var(--primary-color);
        border: 1px solid var(--border-color);
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .btn-read-all:hover {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        transform: translateY(-1px);
    }

    /* Список карточек */
    .notif-list {
        display: flex;
        flex-direction: column;
        gap: 16px; /* Больше воздуха между карточками */
    }

    .notif-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    
    .notif-card:hover {
        box-shadow: var(--card-shadow-hover);
        transform: translateY(-2px);
        border-color: #cbd5e1;
    }

    .notif-item {
        display: flex;
        gap: 24px; /* Больше отступ между иконкой и текстом */
        padding: 24px; /* Карточка стала выше и просторнее */
        position: relative;
    }

    /* Непрочитанная карточка */
    .notif-card.unread {
        background: var(--bg-unread);
        border-color: var(--border-unread);
        border-left: 4px solid var(--primary-color); /* Красивая полоска слева */
    }
    .notif-card.unread:hover {
        border-color: var(--border-unread);
        border-left-color: var(--primary-hover);
    }

    /* Иконки */
    .notif-icon-wrapper {
        width: 56px; /* Иконки крупнее */
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        flex-shrink: 0;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    }
    .type-assigned { background: #fff7ed; color: #ea580c; }
    .type-comment  { background: #f0fdf4; color: #16a34a; }
    .type-signed   { background: #eff6ff; color: #2563eb; }
    .type-created  { background: #f5f3ff; color: #7c3aed; }
    .type-system   { background: #f8fafc; color: #475569; }

    /* Контент внутри карточки */
    .notif-content {
        flex: 1;
        position: relative;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .notif-title {
        font-size: 16px; /* Текст крупнее */
        line-height: 1.5;
        color: var(--text-main);
        word-wrap: break-word;
        margin-bottom: 4px;
    }

    .user-name {
        font-weight: 700;
        color: #111827;
    }

    .user-email {
        font-weight: 400;
        color: var(--text-muted);
        font-size: 14px;
        margin-left: 6px;
    }

    .action-text {
        color: #4b5563;
        margin: 0 4px;
    }

    .doc-link {
        font-weight: 600;
        color: var(--primary-color);
        text-decoration: none;
        padding: 2px 6px;
        border-radius: 6px;
        background: rgba(79, 70, 229, 0.08);
        transition: background 0.2s;
        word-break: break-word;
    }
    .doc-link:hover {
        background: rgba(79, 70, 229, 0.15);
        text-decoration: underline;
    }

    .doc-name-no-link {
        color: #6b7280;
        font-weight: 500;
    }

    /* Цитата комментария */
    .notif-quote {
        margin-top: 12px;
        padding: 14px 16px;
        background: #ffffff;
        border-radius: 10px;
        font-size: 14px;
        color: #374151;
        border: 1px solid var(--border-color);
        border-left: 4px solid #cbd5e1;
        font-style: italic;
        word-wrap: break-word;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }
    .notif-card.unread .notif-quote {
        background: #ffffff; /* Чтобы на синем фоне цитата была белой */
    }

    /* Подвал карточки (время и кнопки) */
    .notif-meta {
        margin-top: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        padding-top: 12px;
        border-top: 1px dashed rgba(0,0,0,0.06); /* Разделительная линия */
    }
    .notif-card.unread .notif-meta {
        border-top-color: rgba(79, 70, 229, 0.1);
    }

    .time-tag {
        font-size: 13px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 500;
    }

    .notif-actions {
        display: flex;
        gap: 8px;
        position: relative;
        z-index: 10;
    }

    /* Кнопки действий (теперь выглядят как настоящие кнопки, а не ссылки) */
    .action-btn {
        background: #f1f5f9;
        border: 1px solid transparent;
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        color: #475569;
    }
    .action-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    
    .action-btn.mark-read {
        background: rgba(79, 70, 229, 0.1);
        color: var(--primary-color);
    }
    .action-btn.mark-read:hover {
        background: var(--primary-color);
        color: white;
    }

    .action-btn.delete:hover {
        background: #fee2e2;
        color: var(--danger);
    }

    /* Точка непрочитанного */
    .unread-dot {
        width: 12px;
        height: 12px;
        background: var(--primary-color);
        border-radius: 50%;
        position: absolute;
        right: 24px;
        top: 24px;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
    }

    /* Пустое состояние */
    .notif-empty {
        text-align: center;
        padding: 80px 20px; /* Очень высокий блок если пусто */
        border: 2px dashed var(--border-color);
        border-radius: 20px;
        color: var(--text-muted);
        background: #fafafa;
    }
    .empty-icon {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.8;
        display: inline-block;
        animation: float 3s ease-in-out infinite;
    }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .notif-empty p {
        font-size: 18px;
        font-weight: 500;
        margin: 0;
    }

    .notif-pagination {
        margin-top: 40px;
        display: flex;
        justify-content: center;
    }

    /* ============================================ */
    /* === АДАПТИВНОСТЬ (СОХРАНЕНА И УЛУЧШЕНА) === */
    /* ============================================ */

    @media (max-width: 1024px) {
        .notif-container { max-width: 100%; padding: 0 20px; }
    }

    @media (max-width: 768px) {
        .notif-container { margin: 24px auto; padding: 0 16px; }
        .header-inner { flex-direction: column; align-items: flex-start; gap: 16px; }
        .doc-main-title { font-size: 24px; }
        .title-accent { height: 24px; }
        .header-right-actions { width: 100%; justify-content: space-between; }
        .btn-read-all { flex: 1; text-align: center; }
        
        .notif-item { padding: 20px; gap: 16px; }
        .notif-icon-wrapper { width: 48px; height: 48px; font-size: 22px; border-radius: 12px; }
        .notif-title { font-size: 15px; }
        .unread-dot { right: 20px; top: 20px; width: 10px; height: 10px; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15); }
    }

    @media (max-width: 640px) {
        .notif-container { margin: 16px auto; padding: 0 12px; }
        .notif-header { padding-bottom: 16px; margin-bottom: 20px; }
        .doc-main-title { font-size: 22px; }
        
        .notif-item { flex-direction: column; gap: 12px; padding: 16px; position: relative; }
        .notif-icon-wrapper { width: 40px; height: 40px; font-size: 20px; border-radius: 10px; }
        
        /* На мобильных unread dot переносим */
        .unread-dot { right: 16px; top: 16px; }
        
        .notif-title { font-size: 15px; padding-right: 20px; /* место под точку */ }
        .user-email { display: block; margin-left: 0; margin-top: 4px; font-size: 13px; }
        
        .notif-meta { flex-direction: column; align-items: flex-start; gap: 16px; padding-top: 16px; }
        .notif-actions { width: 100%; display: flex; gap: 10px; }
        .action-btn { flex: 1; text-align: center; padding: 10px; } /* Кнопки на всю ширину на мобилках */
        
        .notif-empty { padding: 50px 16px; }
        .empty-icon { font-size: 40px; }
        .notif-empty p { font-size: 16px; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const NOTIF_TRANSLATIONS = {
            ru: {
                notifTitle: 'Уведомления',
                newNotifs: 'Новые:',
                notifAssigned: ' назначил вам документ',
                notifCommented: ' оставил комментарий к ',
                notifSigned: ' подписал документ',
                notifCreated: ' создал документ',
                notifDefault: ' отправил уведомление',
                btnMarkRead: 'Прочитать',
                btnReadAll: 'Прочитать все',
                btnDelete: 'Удалить',
                noNotifs: 'У вас пока нет уведомлений',
                confirmDelete: 'Вы действительно хотите удалить это уведомление?',
                confirmReadAll: 'Отметить все уведомления как прочитанные?'
            },
            tj: {
                notifTitle: 'Огоҳиномаҳо',
                newNotifs: 'Нав:',
                notifAssigned: ' ҳуҷҷатро ба шумо супорид',
                notifCommented: ' шарҳ гузошт ба ',
                notifSigned: ' ҳуҷҷатро имзо кард',
                notifCreated: ' ҳуҷҷат сохт',
                notifDefault: ' огоҳинома фиристод',
                btnMarkRead: 'Хондан',
                btnReadAll: 'Ҳамаро хондан',
                btnDelete: 'Нест кардан',
                noNotifs: 'Шумо огоҳинома надоред',
                confirmDelete: 'Оё мутмаин ҳастед, ки мехоҳед ин огоҳиномаро нест кунед?',
                confirmReadAll: 'Ҳамаи огоҳиномаҳоро ҳамчун хондашуда қайд кунем?'
            },
            en: {
                notifTitle: 'Notifications',
                newNotifs: 'New:',
                notifAssigned: ' assigned a document to you',
                notifCommented: ' commented on ',
                notifSigned: ' signed the document',
                notifCreated: ' created a document',
                notifDefault: ' sent a notification',
                btnMarkRead: 'Read',
                btnReadAll: 'Read all',
                btnDelete: 'Delete',
                noNotifs: 'You have no notifications yet',
                confirmDelete: 'Are you sure you want to delete this notification?',
                confirmReadAll: 'Mark all notifications as read?'
            }
        };

        function applyNotifTranslations(lang) {
            const dict = NOTIF_TRANSLATIONS[lang] || NOTIF_TRANSLATIONS.ru;

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

            document.querySelectorAll('[data-confirm-i18n]').forEach(el => {
                const key = el.getAttribute('data-confirm-i18n');
                const message = dict[key] || 'Are you sure?';

                const newEl = el.cloneNode(true);
                el.parentNode.replaceChild(newEl, el);

                if (newEl.tagName === 'FORM') {
                    newEl.onsubmit = (e) => {
                        if (!confirm(message)) e.preventDefault();
                    };
                } else {
                    const form = newEl.closest('form');
                    if (form) {
                        form.onsubmit = (e) => {
                            if (!confirm(message)) e.preventDefault();
                        };
                    }
                }
            });
        }

        const initialLang = localStorage.getItem('docsign_lang') || 'ru';
        applyNotifTranslations(initialLang);

        window.addEventListener('docsign:lang-changed', (e) => {
            const lang = e.detail?.lang || 'ru';
            applyNotifTranslations(lang);
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'docsign_lang' && e.newValue) {
                applyNotifTranslations(e.newValue);
            }
        });
    });
</script>
@endsection