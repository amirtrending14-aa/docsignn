<?php

namespace App\Http\Controllers;

use App\Models\TelegramMessage;
use App\Models\TelegramConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;

class TelegramMessageController extends Controller
{
    public function getMessages($conversationId)
    {
        $conv = TelegramConversation::findOrFail($conversationId);
        
        if (!$conv->users->contains(Auth::id())) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        $messages = TelegramMessage::where('telegram_conversation_id', $conversationId)
            ->with('sender', 'replyToMessage.sender')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

   public function sendMessage(Request $request)
{
    $request->validate([
        'conversation_id' => 'required|exists:telegram_conversations,id',
        'body' => 'nullable|string',
        'type' => 'required|in:text,image,file,voice,sticker',
        'reply_to' => 'nullable|exists:telegram_messages,id',
        'attachment' => 'nullable|file|max:51200',
    ]);

    $conv = \App\Models\TelegramConversation::findOrFail($request->conversation_id);
    if (!$conv->users->contains(Auth::id())) {
        return response()->json(['error' => 'Нет доступа'], 403);
    }

    // 🔒 ПРОВЕРКА БЛОКИРОВКИ (только для личных чатов)
    if ($conv->type === 'private') {
        $otherUser = $conv->users->where('id', '!=', Auth::id())->first();
        if ($otherUser) {
            // Проверяем: заблокировали ли МЫ собеседника
            if (Auth::user()->hasBlocked($otherUser->id)) {
                return response()->json([
                    'error' => 'Вы заблокировали этого пользователя. Разблокируйте для отправки сообщений.'
                ], 403);
            }
            // Проверяем: заблокировал ли ОН нас
            if ($otherUser->hasBlocked(Auth::id())) {
                return response()->json([
                    'error' => 'Этот пользователь заблокировал вас.'
                ], 403);
            }
        }
    }


    $conv = \App\Models\TelegramConversation::findOrFail($request->conversation_id);
    if (!$conv->users->contains(Auth::id())) {
        return response()->json(['error' => 'Нет доступа'], 403);
    }

    $path = null;
    if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
        $path = $request->file('attachment')->store('telegram_files', 'public');
        if (!$path || !\Storage::disk('public')->exists($path)) {
            return response()->json(['error' => 'Файл не сохранился'], 500);
        }
    }

    $message = \App\Models\TelegramMessage::create([
        'telegram_conversation_id' => $request->conversation_id,
        'sender_id' => Auth::id(),
        'body' => $request->body,
        'type' => $request->type,
        'attachment_path' => $path,
        'reply_to' => $request->reply_to,
    ]);

    $message->load(['sender', 'replyToMessage.sender']);

    return response()->json($message);
}

/**
 * ✅ Отметить сообщения как прочитанные
 */
public function markAsRead($conversationId)
{
    $conv = \App\Models\TelegramConversation::findOrFail($conversationId);
    if (!$conv->users->contains(Auth::id())) {
        return response()->json(['error' => 'Нет доступа'], 403);
    }

    $conv->users()->updateExistingPivot(Auth::id(), [
        'last_read_at' => now(),
    ]);

    return response()->json(['success' => true]);
}

    public function deleteMessage($id)
    {
        $message = TelegramMessage::findOrFail($id);
        
        if ($message->sender_id != Auth::id()) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        $message->delete();
        return response()->json(['success' => true]);
    }
    /**
 * 📨 Отправить ссылку на документ в чат Telegram
 * 🔒 Двойная проверка: доступ к чату + доступ к документу
 */
public function sendDocument(Request $request)
{
    $request->validate([
        'conversation_id' => 'required|integer|exists:telegram_conversations,id',
        'document_id'     => 'required|integer|exists:documents,id',
    ]);

    $user = Auth::user();
    $conv = TelegramConversation::findOrFail($request->conversation_id);

    // 🔒 Проверка 1: пользователь состоит в этом чате
    if (!$conv->users->contains($user->id)) {
        return response()->json(['error' => 'Нет доступа к этому чату'], 403);
    }

    // 🔒 Проверка 2: пользователь имеет право на этот документ
    $doc = Document::findOrFail($request->document_id);
    $hasAccess = (
        (int) $doc->created_by === (int) $user->id
        || ($doc->receiver_id && (int) $doc->receiver_id === (int) $user->id)
        || $user->is_admin
        || $user->is_super_admin
    );

    if (!$hasAccess) {
        \Log::warning("🚨 Попытка отправить чужой документ #{$doc->id} в чат #{$conv->id} (user #{$user->id})");
        return response()->json(['error' => 'Нет доступа к этому документу'], 403);
    }

    // 🔒 Проверка 3: заблокированные чаты (для личных)
    if ($conv->type === 'private') {
        $other = $conv->users->where('id', '!=', $user->id)->first();
        if ($other && ($user->hasBlocked($other->id) || $other->hasBlocked($user->id))) {
            return response()->json(['error' => 'Нельзя отправить: пользователь заблокирован'], 403);
        }
    }

    $url  = route('documents.show', $doc->id);
    $body = "📄 Документ: «{$doc->title}»\n"
          . ($doc->number ? "№ {$doc->number}\n" : '')
          . "🔗 {$url}";

    $message = \App\Models\TelegramMessage::create([
        'telegram_conversation_id' => $conv->id,
        'sender_id'                => $user->id,
        'body'                     => $body,
        'type'                     => 'text',
    ]);

    \Log::info("📨 Документ #{$doc->id} отправлен в чат #{$conv->id} пользователем #{$user->id}");

    return response()->json([
        'success'    => true,
        'message_id' => $message->id,
    ]);
}
}