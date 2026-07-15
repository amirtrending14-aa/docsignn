<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Document;
use App\Models\DocumentSignature as Signature;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim($request->input('query'));
        $userId = Auth::id();

        if (empty($query)) {
            $results = collect();
            return view('search.results', compact('results', 'query'));
        }

        // ✅ ПОИСК ПОЛЬЗОВАТЕЛЕЙ: Имя, Email или Телефон
        $users = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->get();

        // ✅ ПОИСК ДОКУМЕНТОВ
        $documents = Document::where(function($q) use ($query, $userId) {
            // Мои документы (которые я создал)
            $q->where('documents.created_by', $userId)
                ->where(function($q2) use ($query) {
                    $q2->where('title', 'LIKE', "%{$query}%")
                        ->orWhere('content', 'LIKE', "%{$query}%")
                        ->orWhere('number', 'LIKE', "%{$query}%"); // Добавил поиск по номеру документа
                });
        })
            ->orWhere(function($q) use ($query, $userId) {
                // Документы, которые мне отправили (через signatures)
                $q->whereHas('signatures', function($q2) use ($userId) {
                    $q2->where('document_signatures.user_id', $userId);
                })
                    ->where(function($q2) use ($query) {
                        $q2->where('title', 'LIKE', "%{$query}%")
                            ->orWhere('content', 'LIKE', "%{$query}%")
                            ->orWhere('number', 'LIKE', "%{$query}%");
                    });
            })
            ->get();

        // ✅ ПОИСК ПОДПИСЕЙ
        $signatures = Signature::with(['document', 'user'])
            ->where(function($q) use ($userId) {
                $q->where('document_signatures.user_id', $userId)
                    ->orWhereHas('document', function($q2) use ($userId) {
                        $q2->where('documents.created_by', $userId);
                    });
            })
            ->where(function($q) use ($query) {
                $q->where('id', 'LIKE', "%{$query}%")
                    ->orWhereHas('document', function($q2) use ($query) {
                        $q2->where('title', 'LIKE', "%{$query}%")
                            ->orWhere('number', 'LIKE', "%{$query}%");
                    });
            })
            ->get();

        // Объединяем и сортируем по дате создания (новые сверху)
        $results = collect()
            ->concat($users)
            ->concat($documents)
            ->concat($signatures)
            ->sortByDesc('created_at')
            ->values(); // Сбрасываем ключи коллекции

        return view('search.results', compact('results', 'query'));
    }
}