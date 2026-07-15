<?php

namespace App\Http\Controllers;

use App\Models\{Document, DocumentLog, User};
use App\Http\Requests\DocumentLog\{StoreRequest, UpdateRequest};
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DocumentLogController extends Controller
{
    public function index()
    {
        $query = DocumentLog::with(['document', 'user']);

        if (!auth()->user()->isAdmin()) {
            $userId = auth()->id();

            // Показываем логи где пользователь:
            // 1. Создатель документа ИЛИ
            // 2. Получатель документа ИЛИ
            // 3. Совершил действие (подписал, обновил и т.д.)
            $query->where(function($q) use ($userId) {
                $q->whereHas('document', function($docQ) use ($userId) {
                    $docQ->where('created_by', $userId)
                        ->orWhere('receiver_id', $userId)
                        ->orWhere('sender_id', $userId);
                })->orWhere('user_id', $userId);
            });
        }

        $logs = $query->latest()->paginate(15);
        return view('logs.index', compact('logs'));
    }

    public function create(): View
    {
        if (auth()->user()->isAdmin()) {
            $documents = Document::pluck('title', 'id');
        } else {
            $documents = Document::where('created_by', auth()->id())->pluck('title', 'id');
        }

        $users = User::pluck('name', 'id');

        return view('logs.create', compact('documents', 'users'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        DocumentLog::create($request->validated());

        return redirect()
            ->route('logs.index')
            ->with('success', 'Запись в журнале успешно создана');
    }

    public function show(DocumentLog $log): View
    {
        if (!auth()->user()->isAdmin() && $log->document->created_by !== auth()->id()) {
            abort(403, 'У вас нет доступа к этой истории.');
        }

        $log->load(['document', 'user']);

        return view('logs.show', compact('log'));
    }

    public function edit(DocumentLog $log): View
    {
        if (!auth()->user()->isAdmin() && $log->document->created_by !== auth()->id()) {
            abort(403);
        }

        if (auth()->user()->isAdmin()) {
            $documents = Document::pluck('title', 'id');
        } else {
            $documents = Document::where('created_by', auth()->id())->pluck('title', 'id');
        }

        $users = User::pluck('name', 'id');

        return view('logs.edit', compact('log', 'documents', 'users'));
    }

    public function update(UpdateRequest $request, DocumentLog $log): RedirectResponse
    {
        if (!auth()->user()->isAdmin() && $log->document->created_by !== auth()->id()) {
            abort(403);
        }

        $log->update($request->validated());

        return redirect()
            ->route('logs.index')
            ->with('success', 'Запись журнала обновлена');
    }

    public function destroy(DocumentLog $log): RedirectResponse
    {
        if (!auth()->user()->isAdmin() && $log->document->created_by !== auth()->id()) {
            abort(403);
        }

        $log->delete();

        return back()->with('success', 'Запись удалена');
    }

    public function documentLogs(Document $document): View
    {
        if (!auth()->user()->isAdmin() && $document->created_by !== auth()->id()) {
            abort(403);
        }

        $logs = $document->logs()
            ->with('user:id,name')
            ->latest()
            ->paginate(15);

        return view('logs.document', compact('document', 'logs'));
    }

    public function clear(): \Illuminate\Http\RedirectResponse
    {
        if (!auth()->user()->isAdmin()) {
            return back()->with('error', 'У вас нет прав на очистку журнала истории');
        }

        \App\Models\DocumentLog::truncate();

        return redirect()
            ->route('logs.index')
            ->with('success', 'Журнал истории успешно очищен');
    }
}