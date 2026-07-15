<?php

namespace App\Http\Controllers;

use App\Models\DocumentComment;
use App\Models\Document;
use App\Models\DocumentLog; // 🔥 Подключили модель логов
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentCommentController extends Controller
{
    public function index($documentId)
    {
        $document = Document::findOrFail($documentId);

        $comments = DocumentComment::with('users')
            ->where('document_id', $documentId)
            ->latest()
            ->get();

        return view('comment.index', compact('document', 'comments'));
    }

    public function create($documentId)
    {
        $document = Document::findOrFail($documentId);
        $users = User::all();

        return view('comment.create', compact('document', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'comment' => 'required|string|max:1000'
        ]);

        if (!auth()->check()) {
            return back()->with('error', 'Вы должны войти в систему, чтобы оставить комментарий');
        }

        $comment = DocumentComment::create([
            'document_id' => $request->document_id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        $document = Document::find($request->document_id);

        if ($document) {
            DocumentLog::create([
                'document_id' => $document->id,
                'user_id'     => auth()->id(),
                'action'      => 'навсозӣ',
                'description' => 'Добавлен комментарий к обсуждению: "' . Str::limit($request->comment, 50) . '"'
            ]);
        }

        if ($document && $document->created_by && $document->created_by !== auth()->id()) {
            Notification::create([
                'id' => (string) Str::uuid(),
                'user_id' => $document->created_by,
                'messages' => 'Новый комментарий к вашему документу: "' . $document->title . '"',
                'type' => 'comment',
                'is_read' => false,
                'notifiable_type' => User::class,
                'notifiable_id' => $document->created_by,
                'data' => json_encode(['comment_id' => $comment->id]),
            ]);
        }

        return back()->with('success', 'Комментарий успешно добавлен!');
    }

    public function destroy($id)
    {
        $comment = DocumentComment::findOrFail($id);
        $documentId = $comment->document_id;
        $commentPreview = Str::limit($comment->comment, 40);

        $comment->delete();

        DocumentLog::create([
            'document_id' => $documentId,
            'user_id'     => auth()->id(),
            'action'      => 'навсозӣ',
            'description' => 'Удален комментарий из обсуждения ("' . $commentPreview . '")'
        ]);

        return back()->with('success', 'Комментарий удалён');
    }
}
