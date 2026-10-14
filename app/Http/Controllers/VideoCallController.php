<?php

namespace App\Http\Controllers;

use App\Models\VideoCallSignal;
use App\Models\TelegramConversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VideoCallController extends Controller
{
    /**
     * 📤 Отправить WebRTC сигнал
     */
    public function sendSignal(Request $request)
    {
        // ✅ FormData присылает data как JSON-СТРОКУ — декодируем
        $data = $request->input('data');
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $request->merge(['data' => is_array($decoded) ? $decoded : []]);
        }

        // ✅ ID приводим к int
        $request->merge([
            'conversation_id' => (int) $request->input('conversation_id'),
            'from_user_id'    => (int) $request->input('from_user_id'),
            'to_user_id'      => (int) $request->input('to_user_id'),
        ]);

        $validated = $request->validate([
            'call_id'         => 'required|string|max:255',
            'conversation_id' => 'required|integer|exists:telegram_conversations,id',
            'from_user_id'    => 'required|integer|exists:users,id',
            'to_user_id'      => 'required|integer|exists:users,id',
            'signal_type'     => 'required|in:offer,answer,ice-candidate,reject,end',
            'data'            => 'nullable|array',   // ✅ nullable, не required!
        ]);

        $conv = TelegramConversation::findOrFail($validated['conversation_id']);

        if (!$conv->users->contains(Auth::id())) {
            return response()->json(['error' => 'Нет доступа к чату'], 403);
        }
        if ($conv->type !== 'private') {
            return response()->json(['error' => 'Звонки только в личных чатах'], 403);
        }

        $otherUser = $conv->users->where('id', '!=', Auth::id())->first();
        if ($otherUser) {
            if (Auth::user()->hasBlocked($otherUser->id)) {
                return response()->json(['error' => 'Вы заблокировали пользователя'], 403);
            }
            if ($otherUser->hasBlocked(Auth::id())) {
                return response()->json(['error' => 'Пользователь заблокировал вас'], 403);
            }
        }

        $signal = VideoCallSignal::create([
            'call_id'         => $validated['call_id'],
            'conversation_id' => $validated['conversation_id'],
            'from_user_id'    => $validated['from_user_id'],
            'to_user_id'      => $validated['to_user_id'],
            'signal_type'     => $validated['signal_type'],
            'data'            => $validated['data'] ?? [],
        ]);

        Log::info("📞 Signal {$validated['signal_type']} saved ID={$signal->id}");

        return response()->json(['success' => true, 'id' => $signal->id]);
    }

    /**
     * 📥 Получить непрочитанные сигналы
     */
    public function getSignals($conversationId)
    {
        $conversationId = (int) $conversationId;
        $conv = TelegramConversation::findOrFail($conversationId);

        if (!$conv->users->contains(Auth::id())) {
            return response()->json(['error' => 'Нет доступа'], 403);
        }

        $signals = VideoCallSignal::where('conversation_id', $conversationId)
            ->where('to_user_id', (int) Auth::id())
            ->where('is_read', false)
            ->orderBy('id', 'asc')
            ->get()
            ->map(fn ($s) => [
                'id'              => $s->id,
                'call_id'         => $s->call_id,
                'conversation_id' => (int) $s->conversation_id,
                'from_user_id'    => (int) $s->from_user_id,
                'to_user_id'      => (int) $s->to_user_id,
                'type'            => $s->signal_type,
                'data'            => $s->data,
            ]);

        return response()->json($signals);
    }

    /**
     * 🗑 Пометить прочитанным
     */
    public function deleteSignal($signalId)
    {
        VideoCallSignal::where('id', (int) $signalId)
            ->where('to_user_id', (int) Auth::id())
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * 👀 Онлайн-статус
     */
    public function checkOnline($userId)
    {
        $user = User::find((int) $userId);
        if (!$user) {
            return response()->json(['online' => false]);
        }
        $online = $user->last_seen_at && $user->last_seen_at->greaterThan(now()->subMinutes(2));

        return response()->json(['online' => $online]);
    }
}