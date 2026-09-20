<?php

namespace App\Http\Controllers;

use App\Models\AiChat;
use App\Models\AiMessage;
use App\Services\AiService;
use App\Services\DocumentGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return redirect()->route('login');
            }

            $chats = AiChat::where('user_id', $userId)->latest()->get();

            $activeChat = null;
            $messages = collect();

            $chatId = $request->input('chat_id') ?? $request->route('id');

            if ($chatId) {
                $activeChat = AiChat::where('user_id', $userId)
                    ->where('id', $chatId)
                    ->first();

                if ($activeChat) {
                    try {
                        $messages = $activeChat->messages()->oldest()->get();
                    } catch (\Throwable $e) {
                        Log::error('Ошибка загрузки сообщений', [
                            'chat_id' => $chatId,
                            'error' => $e->getMessage(),
                        ]);
                        $messages = collect();
                    }
                }
            }

            return view('ai.chat', [
                'chats' => $chats,
                'activeChat' => $activeChat,
                'messages' => $messages,
            ]);

        } catch (\Throwable $e) {
            Log::error('ChatController@index error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return view('ai.chat', [
                'chats' => collect(),
                'activeChat' => null,
                'messages' => collect(),
            ]);
        }
    }

    public function send(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:5000',
                'chat_id' => 'nullable|integer',
            ]);

            $userId = Auth::id();

            if ($request->chat_id) {
                $chat = AiChat::where('user_id', $userId)
                    ->where('id', $request->chat_id)
                    ->firstOrFail();
            } else {
                $chat = AiChat::create([
                    'user_id' => $userId,
                    'title' => Str::limit($request->message, 40),
                    'state' => null,
                ]);
            }

            $recentMessages = AiMessage::where('ai_chat_id', $chat->id)
                ->where('role', 'user')
                ->latest()
                ->limit(3)
                ->pluck('content')
                ->toArray();

            $isStuckInLoop = count($recentMessages) >= 3
                && $recentMessages[0] === $recentMessages[1]
                && $recentMessages[1] === $recentMessages[2];

            AiMessage::create([
                'ai_chat_id' => $chat->id,
                'role' => 'user',
                'content' => $request->message,
            ]);

            $history = AiMessage::where('ai_chat_id', $chat->id)
                ->latest()
                ->take(12)
                ->get()
                ->reverse()
                ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
                ->values()
                ->all();

            $savedState = [];
            try {
                $rawState = $chat->getRawOriginal('state');
                if ($rawState) {
                    $decoded = json_decode($rawState, true);
                    if (is_array($decoded)) {
                        $savedState = $decoded;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Некорректный state в БД, сбрасываем', [
                    'chat_id' => $chat->id,
                    'error' => $e->getMessage(),
                ]);
                $chat->forceFill(['state' => null])->save();
            }

            $result = app(AiService::class)->chat($history, $savedState, $isStuckInLoop);

            if (!$result || !isset($result['action'])) {
                $msg = AiMessage::create([
                    'ai_chat_id' => $chat->id,
                    'role' => 'assistant',
                    'content' => '⚠️ ИИ муваққатан дастрас нест / ИИ временно недоступен.',
                ]);
                return response()->json([
                    'success' => false,
                    'chat_id' => $chat->id,
                    'message_id' => $msg->id,
                    'reply' => $msg->content,
                ]);
            }

            $newState = [
                'doc_type' => $result['doc_type'] ?? null,
                'lang' => $result['lang'] ?? null,
                'memory' => $result['memory'] ?? [],
                'last_questioned_field' => $result['last_questioned_field'] ?? null,
                'pending_type_clarification' => $result['pending_type_clarification'] ?? null,
            ];

            $chat->forceFill(['state' => $newState])->save();

            $docxPath = null;
            $pdfPath = null;

            if ($result['action'] === 'document' && isset($result['structure']) && !empty($result['structure'])) {
                try {
                    $paths = app(DocumentGenerator::class)->generate(
                        $result['structure'],
                        'doc_' . time() . '_' . Str::random(5)
                    );
                    $docxPath = $paths['docx'] ?? null;
                    $pdfPath = $paths['pdf'] ?? null;
                } catch (\Throwable $e) {
                    Log::error('Document generation FAILED', [
                        'error' => $e->getMessage(),
                        'chat_id' => $chat->id,
                    ]);
                }
            }

            $assistantMsg = AiMessage::create([
                'ai_chat_id' => $chat->id,
                'role' => 'assistant',
                'content' => $result['reply'] ?? '...',
                'file_path' => $docxPath,
                'pdf_path' => $pdfPath,
            ]);

            return response()->json([
                'success' => true,
                'chat_id' => $chat->id,
                'message_id' => $assistantMsg->id,
                'reply' => $assistantMsg->content,
                'is_document' => $docxPath !== null,
                'docx_url' => $docxPath ? route('ai.download', [$assistantMsg->id, 'docx']) : null,
                'pdf_url' => $pdfPath ? route('ai.download', [$assistantMsg->id, 'pdf']) : null,
            ]);

        } catch (\Throwable $e) {
            Log::error('ChatController@send error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'reply' => '❌ Ошибка сервера: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function download($id, $format)
    {
        try {
            $msg = AiMessage::whereHas('chat', function ($q) {
                $q->where('user_id', Auth::id());
            })->findOrFail($id);

            $path = ($format === 'pdf') ? $msg->pdf_path : $msg->file_path;

            if (!$path || !file_exists($path)) {
                Log::warning('Файл не существует', [
                    'id' => $id,
                    'path' => $path,
                    'format' => $format,
                ]);
                abort(404, 'Файл не найден');
            }

            $realPath = realpath($path);
            $allowedDir = realpath(storage_path('app'));

            if (!$realPath || !$allowedDir) {
                Log::error('Не удалось получить реальный путь', [
                    'path' => $path,
                    'realPath' => $realPath,
                    'allowedDir' => $allowedDir,
                ]);
                abort(500, 'Ошибка пути');
            }

            // Нормализуем пути для Windows
            $realPathNormalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $realPath);
            $allowedDirNormalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $allowedDir);

            if (!str_starts_with($realPathNormalized, $allowedDirNormalized)) {
                Log::warning('Попытка доступа к файлу вне разрешённой директории', [
                    'id' => $id,
                    'realPath' => $realPathNormalized,
                    'allowedDir' => $allowedDirNormalized,
                ]);
                abort(403, 'Доступ запрещён');
            }

            return response()->download($realPath, 'document.' . $format);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Сообщение не найдено', ['id' => $id]);
            abort(404, 'Сообщение не найдено');
        } catch (\Throwable $e) {
            Log::error('Download error', [
                'id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            abort(500, 'Ошибка скачивания: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Не авторизован'], 401);
            }

            $chat = AiChat::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$chat) {
                return response()->json(['success' => false, 'message' => 'Чат не найден'], 404);
            }

            $messages = AiMessage::where('ai_chat_id', $chat->id)->get();
            foreach ($messages as $msg) {
                if (!empty($msg->file_path) && file_exists($msg->file_path)) {
                    @unlink($msg->file_path);
                }
                if (!empty($msg->pdf_path) && file_exists($msg->pdf_path)) {
                    @unlink($msg->pdf_path);
                }
            }

            AiMessage::where('ai_chat_id', $chat->id)->delete();
            $chat->delete();

            return response()->json([
                'success' => true,
                'message' => 'Чат удалён',
            ]);

        } catch (\Throwable $e) {
            Log::error('ChatController delete error', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage(),
            ], 500);
        }
    }
}