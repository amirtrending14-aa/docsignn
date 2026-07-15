<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentWorkflow;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentWorkflowController extends Controller
{

    public function index($documentId)
    {
        $document = Document::findOrFail($documentId);

        $workflows = DocumentWorkflow::where('document_id', $documentId)->get();

        return view('workflow.index', compact('workflows', 'documentId', 'document'));
    }

    public function create($documentId)
    {
        Document::findOrFail($documentId)  ;

        $users = User::all();

        return view('workflow.create', compact('users', 'documentId'));
    }


    public function store(Request $request, $documentId)
    {
        Document::findOrFail($documentId);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $exists = DocumentWorkflow::where('document_id', $documentId)
            ->where('user_id', $request->user_id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['user_id' => 'Этот пользователь уже добавлен'])->withInput();
        }


        $max = DocumentWorkflow::where('document_id', $documentId)->max('step_order');
        $stepOrder = $max ? $max + 1 : 1;


        $status = $stepOrder === 1 ? 'pending' : 'waiting';

        DocumentWorkflow::create([
            'document_id' => $documentId,
            'user_id' => $request->user_id,
            'step_order' => $stepOrder,
            'status' => $status,
        ]);
        Notification::create([
            'user_id' => $request->user_id,
            'messages' => 'Вам назначен документ на подпись',
            'type' => 'sign',
            'is_read' => false,
        ]);

        return redirect()->route('workflow.index', $documentId)
            ->with('success', 'Этап добавлен');
    }


    public function approve($id)
    {
        $step = DocumentWorkflow::findOrFail($id);

        if ($step->status !== 'pending') {
            return back()->with('error', 'Уже обработано');
        }

        if ($step->user_id != Auth::id()) {
            return back()->with('error', 'Это не ваш этап');
        }


        $step->update(['status' => 'approved']);


        $next = DocumentWorkflow::where('document_id', $step->document_id)
            ->where('step_order', '>', $step->step_order)
            ->orderBy('step_order')
            ->first();

        if ($next) {
            $next->update(['status' => 'pending']);
        }

        return back()->with('success', 'Одобрено');
    }


    public function reject($id)
    {
        $step = DocumentWorkflow::findOrFail($id);

        if ($step->status !== 'pending') {
            return back()->with('error', 'Уже обработано');
        }

        if ($step->user_id != Auth::id()) {
            return back()->with('error', 'Это не ваш этап');
        }


        $step->update(['status' => 'rejected']);


        DocumentWorkflow::where('document_id', $step->document_id)
            ->where('status', 'waiting')
            ->update(['status' => 'rejected']);

        return back()->with('error', 'Документ отклонён');
    }


    public function edit(DocumentWorkflow $workflow)
    {
        return view('workflow.edit', compact('workflow'));
    }


    public function update(Request $request, DocumentWorkflow $workflow)
    {
        $request->validate([
            'step_order' => 'required|integer|min:1',
        ]);

        $workflow->update([
            'step_order' => $request->step_order,
        ]);


        DocumentWorkflow::where('document_id', $workflow->document_id)
            ->orderBy('step_order')
            ->get()
            ->values()
            ->each(function ($item, $index) {
                $item->update(['step_order' => $index + 1]);
            });

        return redirect()->route('workflow.index', $workflow->document_id)
            ->with('success', 'Порядок обновлён');
    }


    public function destroy(DocumentWorkflow $workflow)
    {
        $documentId = $workflow->document_id;

        $workflow->delete();

        DocumentWorkflow::where('document_id', $documentId)
            ->orderBy('step_order')
            ->get()
            ->values()
            ->each(function ($item, $index) {
                $item->update(['step_order' => $index + 1]);
            });

        return redirect()->route('workflow.index', $documentId)
            ->with('success', 'Этап удалён');
    }


    public function current($documentId)
    {
        $current = DocumentWorkflow::where('document_id', $documentId)
            ->where('status', 'pending')
            ->orderBy('step_order')
            ->first();

        return response()->json($current);
    }
}
