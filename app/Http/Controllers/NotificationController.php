<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Проверка новых уведомлений (для AJAX polling)
     */
    public function checkNew()
    {
        $user = auth()->user();

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function($n) {
                $data = is_string($n->data) ? json_decode($n->data, true) : ($n->data ?? []);

                return [
                    'id' => $n->id,
                    'sender' => $data['sender_name'] ?? $data['user_name'] ?? 'Система',
                    'docName' => $data['document_name'] ?? $data['document_title'] ?? 'Документ',
                    'message' => $n->messages ?? '',
                    'type' => $n->type ?? 'comment',
                    'isUnread' => !$n->is_read,
                    'url' => $data['url'] ?? (isset($data['document_id']) ? route('documents.show', $data['document_id']) : '#'),
                    'time' => $n->created_at->diffForHumans(),
                    'createdAt' => $n->created_at->timestamp,
                ];
            });

        return response()->json([
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Отметить все уведомления как прочитанные
     */
    public function readAll()
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return back()->with('success', 'Все уведомления прочитаны');
    }

    /**
     * Список всех уведомлений
     */
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->latest()
            ->paginate(25);

        $unreadCount = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * 🔒 Отметить одно уведомление как прочитанное (AJAX - возвращает JSON)
     */
    public function markAsRead($id)
    {
        // 🔒 Проверка что уведомление принадлежит текущему юзеру
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);

        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * ✅ Отметить одно уведомление как прочитанное (FORM - возвращает редирект)
     */
    public function read($id)
    {
        // 🔒 Проверка что уведомление принадлежит текущему юзеру
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);

        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        // ✅ Возвращаем редирект назад на страницу уведомлений
        return back()->with('success', 'Уведомление отмечено как прочитанное');
    }

    /**
     * Удалить уведомление
     */
    public function destroy($id)
    {
        // 🔒 Проверка что уведомление принадлежит текущему юзеру
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->delete();

        return back()->with('success', 'Удалено');
    }

    /**
     * Создать уведомление о комментарии
     */
    public function store(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'comment'     => 'required|string|max:1000'
        ]);

        $currentUser = Auth::user();

        $comment = DocumentComment::create([
            'document_id' => $request->document_id,
            'user_id'     => $currentUser->id,
            'comment'     => $request->comment,
        ]);

        $document = Document::findOrFail($request->document_id);
        $participantIds = DocumentComment::where('document_id', $document->id)
            ->pluck('user_id')
            ->push($document->created_by)
            ->unique()
            ->filter(fn($id) => $id != $currentUser->id);

        foreach ($participantIds as $userId) {
            Notification::create([
                'user_id'         => $userId,
                'type'            => 'comment',
                'messages'        => ($userId == $document->created_by)
                    ? 'Новый ответ в вашем документе'
                    : 'Новый комментарий в обсуждении, где вы участвуете',
                'is_read'         => false,
                'notifiable_type' => User::class,
                'notifiable_id'   => $userId,
                'data' => json_encode([
                    'document_id'     => $document->id,
                    'type'            => 'comment',
                    'sender_name'     => $currentUser->name,
                    'document_name'   => $document->title,
                    'url'             => route('documents.show', $document->id),
                    'messages'        => 'оставил комментарий к',
                ]),
            ]);
        }

        return back()->with('success', 'Комментарий добавлен, участники уведомлены!');
    }
}