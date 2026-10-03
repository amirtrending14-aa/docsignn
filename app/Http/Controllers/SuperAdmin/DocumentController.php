<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    private const STATUSES = [
        Document::STATUS_DRAFT,
        Document::STATUS_ACTIVE,
        Document::STATUS_PROCESSING,
        Document::STATUS_COMPLETED,
        Document::STATUS_REJECTED,
        Document::STATUS_APPROVED,
        Document::STATUS_FAILED,
    ];

    public function index(Request $request)
    {
        $query = Document::with(['createdBy:id,name,email', 'receiver:id,name,email'])
            ->withCount(['signatures', 'comments']);

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function ($documents) use ($search) {
                $documents->where('title', 'like', "%{$search}%")
                    ->orWhere('number', 'like', "%{$search}%")
                    ->orWhereHas('createdBy', function ($users) use ($search) {
                        $users->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && in_array($request->input('status'), self::STATUSES, true)) {
            $query->where('status', $request->input('status'));
        }

        $documents = $query->latest()->paginate(20)->withQueryString();

        return view('superadmin.documents.index', compact('documents'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('superadmin.documents.create', [
            'statuses' => self::STATUSES,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $filePath = $request->file('file')?->store('documents', 'public');

        try {
            DB::transaction(function () use ($data, $filePath) {
                Document::create([
                    ...$data,
                    'file_path' => $filePath,
                ]);
            });
        } catch (\Throwable $exception) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }

            throw $exception;
        }

        return redirect()->route('superadmin.documents.index')
            ->with('success', 'Документ создан.');
    }

    public function edit(Document $document)
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('superadmin.documents.edit', [
            'document' => $document,
            'statuses' => self::STATUSES,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Document $document)
    {
        $data = $this->validatedData($request);
        $oldFilePath = $document->file_path;
        $newFilePath = $request->file('file')?->store('documents', 'public');

        if ($newFilePath) {
            $data['file_path'] = $newFilePath;
        } elseif ($request->boolean('remove_file')) {
            $data['file_path'] = null;
        }

        try {
            DB::transaction(fn () => $document->update($data));
        } catch (\Throwable $exception) {
            if ($newFilePath) {
                Storage::disk('public')->delete($newFilePath);
            }

            throw $exception;
        }

        if (($newFilePath || $request->boolean('remove_file')) && $oldFilePath) {
            $this->deleteStoredFiles([$oldFilePath]);
        }

        return redirect()->route('superadmin.documents.index')
            ->with('success', 'Изменения документа сохранены.');
    }

    public function destroy(Document $document)
    {
        $versionPaths = DocumentVersion::withTrashed()
            ->where('document_id', $document->id)
            ->pluck('file_path')
            ->all();
        $filePaths = array_filter(array_merge([$document->file_path], $versionPaths));

        DB::transaction(fn () => $document->forceDelete());

        $filesDeleted = $this->deleteStoredFiles($filePaths);
        $message = $filesDeleted
            ? 'Документ и связанные записи удалены.'
            : 'Документ и связанные записи удалены, но не все файлы удалось очистить. Проверьте журнал приложения.';

        return redirect()->route('superadmin.documents.index')
            ->with($filesDeleted ? 'success' : 'error', $message);
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'number' => 'nullable|string|max:255',
            'type' => 'required|string|max:100',
            'content' => 'nullable|string',
            'status' => 'required|in:' . implode(',', self::STATUSES),
            'created_by' => ['required', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'receiver_id' => ['nullable', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'deadline' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,rtf|max:51200',
            'remove_file' => 'nullable|boolean',
        ]);

        unset($data['file'], $data['remove_file']);

        return $data;
    }

    private function deleteStoredFiles(array $paths): bool
    {
        $disk = Storage::disk('public');
        $allDeleted = true;

        foreach (array_unique($paths) as $path) {
            $path = str_replace('\\', '/', $path);
            if ($path === '' || str_starts_with($path, '/') || preg_match('#(^|/)\.\.(/|$)#', $path)) {
                Log::warning('Skipped unsafe document file path during cleanup.', ['path' => $path]);
                $allDeleted = false;
                continue;
            }

            if (!$disk->delete($path)) {
                Log::warning('Could not delete a document file after record deletion.', ['path' => $path]);
                $allDeleted = false;
            }
        }

        return $allDeleted;
    }
}
