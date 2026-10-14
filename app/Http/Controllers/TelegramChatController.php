<?php

namespace App\Http\Controllers;

use App\Models\TelegramBlock;
use App\Models\TelegramConversation;
use App\Models\TelegramMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class TelegramChatController extends Controller
{
    /**
     * Главная страница чата
     */
    public function index()
    {
        return view('telegram.chat');
    }

    /* =========================================================
       СПИСОК ЧАТОВ (САЙДБАР)
       ========================================================= */

    /**
     * Список чатов пользователя (сайдбар слева)
     */
    public function getConversations()
    {
        $user = Auth::user();
        $me   = $user->id;

        $conversations = $user->telegramConversations()
            ->with(['lastMessage.sender', 'users'])
            ->get()
            ->sortByDesc(fn ($c) => $c->lastMessage?->created_at ?? $c->created_at)
            ->values();

        $formatted = $conversations->map(function ($conv) use ($user, $me) {
            $isGroup   = $conv->type === 'group';
            $otherUser = !$isGroup ? $conv->users->where('id', '!=', $me)->first() : null;

            // Непрочитанные
            $unreadCount = 0;
            $pivot = $conv->users->where('id', $me)->first();
            if ($pivot && $pivot->pivot->last_read_at) {
                $unreadCount = $conv->messages()
                    ->where('sender_id', '!=', $me)
                    ->where('created_at', '>', $pivot->pivot->last_read_at)
                    ->count();
            } else {
                $unreadCount = $conv->messages()
                    ->where('sender_id', '!=', $me)
                    ->count();
            }

            // ✅ Права текущего пользователя в группе
            $myPivot = $conv->users->firstWhere('id', $me);
            $isManager = $isGroup && (
                (int) $conv->created_by === (int) $me
                || $myPivot?->pivot?->role === 'admin'
                || $user->is_super_admin
            );

            return [
                'id'            => $conv->id,
                'type'          => $conv->type,
                'name'          => $isGroup ? ($conv->name ?? 'Группа') : ($otherUser?->name ?? 'Пользователь'),
                'avatar'        => $this->resolveAvatar($isGroup ? $conv : $otherUser, $isGroup),
                'is_online'     => !$isGroup && $otherUser ? $this->isUserOnline($otherUser) : false,
                'unread_count'  => $unreadCount,
                'other_user_id' => $otherUser?->id ?? null,
                'is_blocked'    => $otherUser ? $user->hasBlocked($otherUser->id) : false,
                'member_count'  => $isGroup ? $conv->users->count() : 2,
                'is_company'    => !is_null($conv->company_id),
                'is_manager'    => $isManager,
                'can_add'       => $isManager,
                'last_message'  => $conv->lastMessage ? [
                    'text'    => $this->formatLastMessage($conv->lastMessage, $user),
                    'time'    => $conv->lastMessage->created_at->format('H:i'),
                    'is_mine' => $conv->lastMessage->sender_id == $me,
                ] : null,
            ];
        });

        return response()->json($formatted);
    }

    /* =========================================================
       СООБЩЕНИЯ
       ========================================================= */

    /**
     * Получить сообщения чата (пагинация не нужна — чат небольшой)
     */
public function getMessages($conversationId)
{
    $conv = TelegramConversation::findOrFail($conversationId);

    if (! $conv->users->contains(Auth::id())) {
        return response()->json(['error' => 'Нет доступа'], 403);
    }

    $messages = $conv->messages()
        ->with('sender')
        ->latest('id')          // берём ПОСЛЕДНИЕ 500
        ->limit(500)
        ->get()
        ->reverse()             // возвращаем хронологию снизу-вверх
        ->values()
        ->map(function ($m) {
            $url = $m->attachment_path ? url('/chat/file/' . $m->attachment_path) : null;

            // ✅ Безопасный reply: не зависит от eager-loading
            $reply = null;
            if (! empty($m->reply_to)) {
                $r = TelegramMessage::find($m->reply_to);
                if ($r) {
                    $reply = [
                        'id'     => $r->id,
                        'body'   => $r->body,
                        'sender' => $r->sender ? ['name' => $r->sender->name] : null,
                    ];
                }
            }

            return [
                'id'               => $m->id,
                'sender_id'        => $m->sender_id,
                'sender'           => $m->sender ? ['id' => $m->sender->id, 'name' => $m->sender->name] : null,
                'type'             => $m->type,
                'body'             => $m->body,
                'attachment_url'   => $url,
                'reply_to'         => $m->reply_to,
                'reply_to_message' => $reply,
                'is_read'          => (bool) $m->is_read,
                'created_at'       => optional($m->created_at)->toIso8601String(),
            ];
        });

    return response()->json($messages);
}

    /**
     * Отправить сообщение / файл / голос
     */
    public function send(Request $request)
    {
      $request->validate([
        'conversation_id' => 'required|exists:telegram_conversations,id',
        'body'            => 'nullable|string|max:5000',
        'type'            => 'required|in:text,image,file,voice,sticker', // ✅ ДОБАВЛЕН sticker
        'attachment'      => 'nullable|file|max:51200',
        'reply_to'        => 'nullable|exists:telegram_messages,id',
    ]);

        $conv = TelegramConversation::findOrFail($request->conversation_id);

        if (!$conv->users->contains(Auth::id())) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        // Проверка блокировки в личном чате
        if ($conv->type === 'private') {
            $otherUser = $conv->users->where('id', '!=', Auth::id())->first();
            if ($otherUser && $otherUser->hasBlocked(Auth::id())) {
                return response()->json(['error' => 'Вас заблокировали'], 403);
            }
            if ($otherUser && Auth::user()->hasBlocked($otherUser->id)) {
                return response()->json(['error' => 'Разблокируйте пользователя'], 403);
            }
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
       $attachmentPath = $request->file('attachment')->store('telegram_files', 'public'); }

        $message = TelegramMessage::create([
            'telegram_conversation_id' => $conv->id,
            'sender_id'                => Auth::id(),
            'type'                     => $request->type,
            'body'                     => $request->body,
            'attachment_path'          => $attachmentPath,
            'reply_to'                 => $request->reply_to,
        ]);

        // Автоматически помечаем свои входящие как прочитанные
        $conv->users()->updateExistingPivot(Auth::id(), ['last_read_at' => now()]);

        $url = $attachmentPath ?url('/chat/file/' . $attachmentPath) : null;

        return response()->json([
            'id'             => $message->id,
            'sender_id'      => Auth::id(),
            'sender'         => ['id' => Auth::id(), 'name' => Auth::user()->name],
            'type'           => $message->type,
            'body'           => $message->body,
            'attachment_url' => $url,
            'reply_to'       => $message->reply_to,
            'is_read'        => false,
            'created_at'     => $message->created_at->toIso8601String(),
        ]);
    }

    /**
     * Удалить сообщение (только своё)
     */
    public function deleteMessage($messageId)
    {
        $message = TelegramMessage::findOrFail($messageId);

        if ((int) $message->sender_id !== (int) Auth::id()) {
            return response()->json(['error' => 'Можно удалять только свои сообщения'], 403);
        }

        if ($message->attachment_path) {
            Storage::disk('public')->delete($message->attachment_path);
        }

        $message->delete();

        return response()->json(['success' => true]);
    }

    /* =========================================================
       СТАТУС ПРОЧИТАНО / НЕПРОЧИТАНО
       ========================================================= */

    /**
     * Отметить чат прочитанным
     */
    public function markAsRead($conversationId)
    {
        $conv = TelegramConversation::findOrFail($conversationId);

        if (!$conv->users->contains(Auth::id())) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        // Обновляем pivot
        $conv->users()->updateExistingPivot(Auth::id(), ['last_read_at' => now()]);

        // Помечаем входящие сообщения прочитанными (галочки у отправителя)
        TelegramMessage::where('telegram_conversation_id', $conv->id)
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Пометить чат как непрочитанный
     */
    public function markUnread($id)
    {
        $conv = TelegramConversation::findOrFail($id);

        if (!$conv->users->contains(Auth::id())) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        $conv->users()->updateExistingPivot(Auth::id(), ['last_read_at' => null]);

        return response()->json(['success' => true]);
    }

    /* =========================================================
       "ПЕЧАТАЕТ..."
       ========================================================= */

    /**
     * Отправить сигнал "печатает"
     */
    public function typing(Request $request)
    {
        $request->validate(['conversation_id' => 'required|exists:telegram_conversations,id']);

        $conv = TelegramConversation::findOrFail($request->conversation_id);

        if (!$conv->users->contains(Auth::id())) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        Cache::put("tg_typing_{$request->conversation_id}_" . Auth::id(), Auth::user()->name, 5);

        return response()->json(['success' => true]);
    }

    /**
     * Получить кто сейчас печатает в чате
     */
    public function getTyping($conversationId)
    {
        $conv   = TelegramConversation::findOrFail($conversationId);
        $typers = [];

        foreach ($conv->users as $u) {
            if ($u->id != Auth::id()) {
                $name = Cache::get("tg_typing_{$conversationId}_{$u->id}");
                if ($name) {
                    $typers[] = $name;
                }
            }
        }

        return response()->json(['typers' => $typers]);
    }

    /* =========================================================
       ПОИСК ПОЛЬЗОВАТЕЛЕЙ
       ========================================================= */

    /**
     * Поиск пользователей (имя / email / телефон)
     */
    public function getUsers(Request $request)
    {
        $search = trim($request->query('q', ''));

        $query = User::query()
            ->where('id', '!=', Auth::id())
            ->select('id', 'name', 'email', 'phone', 'avatar');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->limit(50)->get()->map(function ($u) {
            return [
                'id'     => $u->id,
                'name'   => $u->name,
                'email'  => $u->email,
                'phone'  => $u->phone,
                'avatar' => $this->userAvatarUrl($u)
                    ?: 'https://ui-avatars.com/api/?name=' . urlencode($u->name) . '&size=40&background=4f8cff&color=fff&bold=true',
            ];
        });

        return response()->json($users);
    }

    /* =========================================================
       СОЗДАНИЕ ЧАТОВ
       ========================================================= */

    /**
     * Создать / открыть личный чат 1-на-1
     */
    public function startPrivateChat(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $myId     = Auth::id();
        $targetId = (int) $request->user_id;

        if ($myId === $targetId) {
            return response()->json(['error' => 'Нельзя создать чат с самим собой'], 400);
        }

        // Проверяем блокировку
        $target = User::find($targetId);
        if ($target->hasBlocked($myId)) {
            return response()->json(['error' => 'Вас заблокировали'], 403);
        }

        $conversation = TelegramConversation::where('type', 'private')
            ->whereHas('users', fn ($q) => $q->where('user_id', $myId))
            ->whereHas('users', fn ($q) => $q->where('user_id', $targetId))
            ->first();

        if (!$conversation) {
            $conversation = TelegramConversation::create(['type' => 'private']);
            $conversation->users()->attach([$myId, $targetId]);
        }

        return response()->json([
            'id'     => $conversation->id,
            'name'   => $target->name,
            'avatar' => $this->userAvatarUrl($target)
                ?: 'https://ui-avatars.com/api/?name=' . urlencode($target->name) . '&size=48&background=4f8cff&color=fff&bold=true',
        ]);
    }

    /**
     * Создать группу
     */
    public function createGroup(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $conversation = TelegramConversation::create([
            'type'       => 'group',
            'name'       => $request->name,
            'created_by' => Auth::id(),
        ]);

        // Уникальные ID, включая создателя
        $userIds = array_unique(array_merge([Auth::id()], $request->user_ids));

        // ✅ ОДИН attach с ролями
        $attachData = [];
        foreach ($userIds as $uid) {
            $attachData[$uid] = ['role' => $uid == Auth::id() ? 'admin' : 'member'];
        }
        $conversation->users()->attach($attachData);

        return response()->json([
            'id'   => $conversation->id,
            'name' => $conversation->name,
        ]);
    }

    /* =========================================================
       ИНФО О ЧАТЕ + МЕДИА
       ========================================================= */

    /**
     * 📋 Инфо о чате/группе: участники, статистика, права
     */
    public function info($id)
    {
        $conv = TelegramConversation::with('users')->findOrFail($id);

        if (!$conv->users->contains(Auth::id())) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        $isGroup = $conv->type === 'group';
        $me      = Auth::id();

               // ✅ Права текущего пользователя (универсальная проверка)
        $isManager = $isGroup && $this->isGroupManager($conv, $me);

        $members = $conv->users->map(function ($u) use ($me, $conv) {
            return [
                'id'         => $u->id,
                'name'       => $u->name,
                'avatar'     => $this->userAvatarUrl($u)
                    ?: 'https://ui-avatars.com/api/?name=' . urlencode($u->name) . '&size=40&background=4f8cff&color=fff&bold=true',
                'online'     => $this->isUserOnline($u),
                'role'       => $u->pivot->role ?? 'member',
                'is_creator' => (int) $conv->created_by === (int) $u->id,
                'is_me'      => (int) $u->id === (int) $me,
            ];
        })->values();

        $msg = TelegramMessage::where('telegram_conversation_id', $conv->id);
        $stats = [
            'messages' => (clone $msg)->count(),
            'files'    => (clone $msg)->where('type', 'file')->count(),
            'photos'   => (clone $msg)->where('type', 'image')->count(),
            'voice'    => (clone $msg)->where('type', 'voice')->count(),
            'links'    => (clone $msg)->where('type', 'text')->where('body', 'like', '%http%')->count(),
        ];

        $otherUser = $isGroup ? null : $conv->users->where('id', '!=', $me)->first();

        return response()->json([
            'id'           => $conv->id,
            'type'         => $conv->type,
            'name'         => $isGroup ? ($conv->name ?? 'Группа') : ($otherUser?->name ?? 'Пользователь'),
            'avatar'       => $this->resolveAvatar($isGroup ? $conv : $otherUser, $isGroup),
            'member_count' => $conv->users->count(),
            'members'      => $members,
            'stats'        => $stats,
            'is_creator'   => (int) $conv->created_by === (int) $me,
            'is_manager'   => $isManager,
            'can_add'      => $isManager,
            'is_company'   => !is_null($conv->company_id),
            'can_leave'    => $isGroup && is_null($conv->company_id),
            'can_delete'   => $isGroup
                ? ((int) $conv->created_by === (int) $me || Auth::user()->is_super_admin)
                : true,
            'other_user'   => $otherUser ? [
                'id'     => $otherUser->id,
                'email'  => $otherUser->email,
                'phone'  => $otherUser->phone,
                'online' => $this->isUserOnline($otherUser),
            ] : null,
        ]);
    }

    /**
     * 🖼 Медиа чата: фото / файлы / голосовые / ссылки
     */
    public function media($id, Request $request)
    {
        $conv = TelegramConversation::findOrFail($id);
        if (!$conv->users->contains(Auth::id())) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        $type = $request->query('type', 'image');

        if ($type === 'link') {
            $items = TelegramMessage::where('telegram_conversation_id', $conv->id)
                ->where('type', 'text')
                ->where('body', 'like', '%http%')
                ->with('sender')
                ->latest()->limit(100)->get()
                ->map(fn ($m) => [
                    'id'     => $m->id,
                    'body'   => $m->body,
                    'sender' => $m->sender?->name,
                    'time'   => $m->created_at->format('d.m.Y H:i'),
                ]);
            return response()->json($items);
        }

        if (!in_array($type, ['image', 'file', 'voice'])) {
            $type = 'image';
        }

        $items = TelegramMessage::where('telegram_conversation_id', $conv->id)
            ->where('type', $type)
            ->whereNotNull('attachment_path')
            ->with('sender')
            ->latest()->limit(200)->get()
            ->map(fn ($m) => [
                'id'     => $m->id,
                'url'    => url('/chat/file/' . $m->attachment_path),
                'name'   => basename($m->attachment_path),
                'sender' => $m->sender?->name,
                'time'   => $m->created_at->format('d.m.Y H:i'),
            ]);

        return response()->json($items);
    }

    /* =========================================================
       УПРАВЛЕНИЕ ГРУППОЙ
       ========================================================= */

    /**
     * ➕ Добавить участников в группу (только менеджер)
     */
    public function addMembers(Request $request, $id)
    {
        $request->validate([
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $conv = TelegramConversation::findOrFail($id);
        $me   = Auth::id();

        if ($conv->type !== 'group' || !$conv->users->contains($me)) {
            return response()->json(['error' => 'Недоступно'], 403);
        }
        if (!$this->isGroupManager($conv, $me)) {
            return response()->json(['error' => 'Только создатель или админ группы'], 403);
        }

        $ids = $request->user_ids;

        // 🔒 В группу компании — только сотрудники компании
        if (!is_null($conv->company_id)) {
            $ids = User::whereIn('id', $ids)
                ->where('company_id', $conv->company_id)
                ->pluck('id')->toArray();
        }

        if (empty($ids)) {
            return response()->json(['error' => 'Нет допустимых пользователей'], 422);
        }

        $attach = [];
        foreach ($ids as $uid) {
            $attach[$uid] = ['role' => 'member'];
        }
        $conv->users()->syncWithoutDetaching($attach);

        return response()->json(['success' => true, 'added' => count($ids)]);
    }

    /**
     * ➖ Удалить участника из группы (только менеджер)
     */
    public function removeMember($id, $userId)
    {
        $conv = TelegramConversation::findOrFail($id);
        $me   = Auth::id();

        if ($conv->type !== 'group' || !$conv->users->contains($me)) {
            return response()->json(['error' => 'Недоступно'], 403);
        }

        $myPivot   = $conv->users->firstWhere('id', $me);
        $isManager = (int) $conv->created_by === (int) $me
            || $myPivot?->pivot?->role === 'admin'
            || Auth::user()->is_super_admin;

        if (!$isManager) {
            return response()->json(['error' => 'Только создатель или админ группы'], 403);
        }

        if ((int) $conv->created_by === (int) $userId) {
            return response()->json(['error' => 'Нельзя удалить создателя группы'], 403);
        }

        if (!is_null($conv->company_id)) {
            $target = User::find($userId);
            if ($target && (int) $target->company_id === (int) $conv->company_id) {
                return response()->json(['error' => 'Нельзя убрать сотрудника группы компании'], 403);
            }
        }

        $conv->users()->detach($userId);

        if ($conv->users()->count() === 0) {
            $conv->delete();
        }

        return response()->json(['success' => true]);
    }

    /**
     * 🚪 Покинуть группу
     */
    public function leave($id)
    {
        $conv = TelegramConversation::findOrFail($id);

        if ($conv->type !== 'group') {
            return response()->json(['error' => 'Это не группа'], 400);
        }
        if (!$conv->users->contains(Auth::id())) {
            return response()->json(['error' => 'Вы не участник'], 403);
        }
        if (!is_null($conv->company_id)) {
            return response()->json(['error' => 'Нельзя покинуть группу компании'], 403);
        }

        $conv->users()->detach(Auth::id());

        if ($conv->users()->count() === 0) {
            $conv->delete();
        }

        return response()->json(['success' => true]);
    }

    /**
     * 🗑 Удалить чат/группу
     */
    public function deleteConversation($id)
    {
        $conv = TelegramConversation::findOrFail($id);
        $me   = Auth::id();

        if (!$conv->users->contains($me)) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        if ($conv->type === 'group') {
            if (!is_null($conv->company_id)) {
                return response()->json(['error' => 'Группу компании удалить нельзя'], 403);
            }

            // ✅ Удалять может менеджер группы
            if (!$this->isGroupManager($conv, $me)) {
                return response()->json(['error' => 'Удалять группу может только её создатель или админ'], 403);
            }
        }

        $conv->delete(); // сообщения и pivot удалятся каскадно

        return response()->json(['success' => true]);
    }

    /* =========================================================
       БЛОКИРОВКА ПОЛЬЗОВАТЕЛЕЙ
       ========================================================= */

    /**
     * 🔒 Заблокировать пользователя
     */
    public function blockUser($userId)
    {
        $myId = Auth::id();

        if ((int) $myId === (int) $userId) {
            return response()->json(['error' => 'Нельзя заблокировать себя'], 400);
        }

        if (!User::where('id', $userId)->exists()) {
            return response()->json(['error' => 'Пользователь не найден'], 404);
        }

        TelegramBlock::firstOrCreate([
            'user_id'         => $myId,
            'blocked_user_id' => $userId,
        ]);

        return response()->json(['success' => true, 'message' => 'Пользователь заблокирован']);
    }

    /**
     * 🔓 Разблокировать пользователя
     */
    public function unblockUser($userId)
    {
        TelegramBlock::where('user_id', Auth::id())
            ->where('blocked_user_id', $userId)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Пользователь разблокирован']);
    }

    /**
     * Статус блокировки
     */
    public function blockStatus($userId)
    {
        return response()->json([
            'is_blocked' => Auth::user()->hasBlocked((int) $userId),
        ]);
    }

    /* =========================================================
       ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ (private)
       ========================================================= */

    /**
     * Правильный URL аватарки пользователя
     */
    private function userAvatarUrl($user): ?string
    {
        if (!$user) {
            return null;
        }

        if (!empty($user->avatar)) {
            $path = ltrim(str_replace('\\', '/', $user->avatar), '/');

            if (!str_starts_with($path, 'avatars/')) {
                $path = 'avatars/' . ltrim(basename($path), '/');
            }

            if (Storage::disk('public')->exists($path)) {
                return url('/avatar/' . $path);
            }

            if (file_exists(public_path($path))) {
                return asset($path);
            }
        }

        return $user->avatar_url ?? null;
    }

    /**
     * Аватарка для чата: пользователь или группа
     */
    private function resolveAvatar($entity, bool $isGroup): string
    {
        if (!$entity) {
            return 'https://ui-avatars.com/api/?name=U&size=48&background=4f8cff&color=fff&bold=true';
        }

        if ($isGroup) {
            $name = $entity->name ?? 'Г';
            return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=48&background=a855f7&color=fff&bold=true&font-size=0.4';
        }

        $url = $this->userAvatarUrl($entity);
        if ($url) {
            return $url;
        }

        $name = $entity->name ?? 'U';
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=48&background=4f8cff&color=fff&bold=true';
    }

    /**
     * Онлайн-статус (активен последние 2 минуты)
     */
    private function isUserOnline($user): bool
    {
        return $user->last_seen_at && $user->last_seen_at->greaterThan(now()->subMinutes(2));
    }

    /**
     * Текст последнего сообщения для сайдбара
     */
    private function formatLastMessage(TelegramMessage $message, $currentUser): string
    {
        $prefix = $message->sender_id == $currentUser->id ? 'Вы: ' : '';

        return match ($message->type) {
            'image'   => $prefix . '📷 Фото',
            'file'    => $prefix . '📎 Файл',
            'voice'   => $prefix . '🎤 Голосовое',
            'sticker' => $prefix . '🎭 Стикер',
            default   => $prefix . ($message->body ?? ''),
        };
    }
    /**
 * ✅ Кто менеджер группы:
 * 1) создатель группы (created_by)
 * 2) суперадмин
 * 3) участник с ролью admin в pivot
 * 4) для группы компании: владелец компании ИЛИ админ компании
 */
private function isGroupManager(TelegramConversation $conv, int $me): bool
{
    if ((int) $conv->created_by === (int) $me) return true;
    if (Auth::user()->is_super_admin) return true;

    $myPivot = $conv->users->firstWhere('id', $me);
    if ($myPivot && $myPivot->pivot->role === 'admin') return true;

    // Группа компании → менеджер = владелец компании или её админ
    if (!is_null($conv->company_id)) {
        $company = \App\Models\Company::find($conv->company_id);
        if ($company && (int) $company->owner_id === (int) $me) return true;

        $myUser = User::find($me);
        if ($myUser && $myUser->is_admin && (int) $myUser->company_id === (int) $conv->company_id) return true;
    }

    return false;
}
/**
 * 📁 Отдача файлов с поддержкой Range-запросов (исправляет длительность аудио)
 */
public function downloadFile($path)
{
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    
    // Защита от выхода за пределы папки
    $path = str_replace(['..', '\\'], '', $path);
    
    if (!$disk->exists($path)) {
        abort(404);
    }

    $fullPath = $disk->path($path);
    $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';
    $size     = $disk->size($path);
    $filename = basename($path);

    // ✅ Определяем правильное расширение по MIME
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if (!$ext || $ext === 'webm') {
        if (str_contains($mimeType, 'mp4')) $filename = pathinfo($filename, PATHINFO_FILENAME) . '.m4a';
        elseif (str_contains($mimeType, 'ogg')) $filename = pathinfo($filename, PATHINFO_FILENAME) . '.ogg';
        elseif (str_contains($mimeType, 'mpeg')) $filename = pathinfo($filename, PATHINFO_FILENAME) . '.mp3';
    }

    // ✅ Заголовки для корректной работы HTML5 audio
    $headers = [
        'Content-Type'        => $mimeType,
        'Content-Length'      => $size,
        'Accept-Ranges'       => 'bytes',
        'Cache-Control'       => 'public, max-age=31536000',
        'Content-Disposition' => 'inline; filename="' . $filename . '"',
    ];

    // ✅ Поддержка Range (для перемотки и определения длительности)
    if (isset($_SERVER['HTTP_RANGE'])) {
        $range = $_SERVER['HTTP_RANGE'];
        if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
            $start = (int) $matches[1];
            $end   = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : $size - 1;
            $length = $end - $start + 1;

            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $start-$end/$size");
            header("Content-Length: $length");
            foreach ($headers as $k => $v) {
                if ($k !== 'Content-Length') header("$k: $v");
            }

            $fp = fopen($fullPath, 'rb');
            fseek($fp, $start);
            $remaining = $length;
            while ($remaining > 0 && !feof($fp)) {
                $chunk = min(8192, $remaining);
                echo fread($fp, $chunk);
                $remaining -= $chunk;
                flush();
            }
            fclose($fp);
            exit;
        }
    }

    return response()->file($fullPath, $headers);
} 
}