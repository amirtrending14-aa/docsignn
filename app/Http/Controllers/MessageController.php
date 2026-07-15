<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index($receiverId = null)
    {
        $authId = auth()->id();

        $chatUserIds = Message::where('sender_id', $authId)
            ->orWhere('receiver_id', $authId)
            ->get(['sender_id', 'receiver_id'])
            ->flatMap(fn($msg) => [$msg->sender_id, $msg->receiver_id])
            ->unique()
            ->reject(fn($id) => $id == $authId);

        $users = User::whereIn('id', $chatUserIds)
            ->with(['sentMessages' => fn($q) => $q->where(fn($s) => $s->where('sender_id', $authId)->orWhere('receiver_id', $authId)),
                'receivedMessages' => fn($q) => $q->where(fn($s) => $s->where('sender_id', $authId)->orWhere('receiver_id', $authId))])
            ->get()
            ->sortByDesc(function ($user) {
                $lastMessage = $user->sentMessages->merge($user->receivedMessages)->sortByDesc('created_at')->first();
                return $lastMessage ? $lastMessage->created_at : '1970-01-01';
            });

        $messages = [];
        $receiver = null;

        if ($receiverId) {
            $receiver = User::find($receiverId);

            if ($receiver) {
                Message::where('sender_id', $receiverId)
                    ->where('receiver_id', $authId)
                    ->where('is_read', 0)
                    ->update(['is_read' => 1]);

                $messages = Message::where(function($q) use ($receiverId, $authId) {
                    $q->where('sender_id', $authId)->where('receiver_id', $receiverId);
                })->orWhere(function($q) use ($receiverId, $authId) {
                    $q->where('sender_id', $receiverId)->where('receiver_id', $authId);
                })->orderBy('created_at', 'asc')->get();
            }
        }

        return view('messages.index', compact('users', 'messages', 'receiver', 'receiverId'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');

        $users = User::where('name', 'LIKE', "%{$query}%")
            ->where('id', '!=', auth()->id())
            ->limit(8)
            ->get();

        return response()->json($users);
    }

    public function store(Request $request, $receiverId)
    {
        $request->validate(['body' => 'required|string|max:1000']);

        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId,
            'body' => $request->body,
            'is_read' => false,
        ]);

        return redirect()->route('messages.show', $receiverId);
    }

    public function markRead($userId)
    {
        Message::where('sender_id', $userId)
            ->where('receiver_id', auth()->id())
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $msg = Message::findOrFail($id);
        if ($msg->sender_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Нет прав'], 403);
        }
        $request->validate(['body' => 'required|string|max:2000']);
        $msg->update(['body' => $request->body, 'edited_at' => now()]);
        return response()->json(['success' => true, 'body' => $msg->body]);
    }

    public function destroy(Request $request, $id)
    {
        $msg = Message::findOrFail($id);
        $forAll = $request->input('forAll', false);

        if ($forAll && $msg->sender_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Нет прав'], 403);
        }

        if ($forAll) {
            $msg->delete();
        } else {
            $msg->update([
                'deleted_for_' . auth()->id() => true
            ]);
        }
        return response()->json(['success' => true]);
    }

    public function block($id)
    {
        $userToBlock = User::findOrFail($id);

        if ($userToBlock->id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Вы не можете заблокировать самого себя.'], 400);
        }

        // Создаём или обновляем запись в таблице блокировок
        // Предполагается таблица user_blocks с полями: user_id, blocked_user_id
        \DB::table('user_blocks')->updateOrInsert(
            ['user_id' => Auth::id(), 'blocked_user_id' => $id],
            ['created_at' => now()]
        );

        return response()->json(['success' => true, 'message' => 'Пользователь заблокирован']);
    }

    public function unblock($id)
    {
        \DB::table('user_blocks')
            ->where('user_id', Auth::id())
            ->where('blocked_user_id', $id)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Пользователь разблокирован']);
    }

    public function blockedUsers()
    {
        $blockedIds = \DB::table('user_blocks')
            ->where('user_id', Auth::id())
            ->pluck('blocked_user_id');

        $users = User::whereIn('id', $blockedIds)->get(['id', 'name', 'email']);

        return response()->json($users);
    }

    public function clear($id)
    {
        Message::where(function($query) use ($id) {
            $query->where('sender_id', $id)->where('receiver_id', Auth::id());
        })->orWhere(function($query) use ($id) {
            $query->where('sender_id', Auth::id())->where('receiver_id', $id);
        })->delete();

        return response()->json(['success' => true, 'message' => 'История чата очищена']);
    }

    public function deleteChat($id)
    {
        Message::where(function($query) use ($id) {
            $query->where('sender_id', $id)->where('receiver_id', Auth::id());
        })->orWhere(function($query) use ($id) {
            $query->where('sender_id', Auth::id())->where('receiver_id', $id);
        })->delete();

        return response()->json(['success' => true, 'message' => 'Чат удалён']);
    }
}