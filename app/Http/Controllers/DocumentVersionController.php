<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentVersionController extends Controller
{

    public function index()
    {
        $versions = DocumentVersion::with('document')
            ->whereHas('document', function ($query) {
                // Фильтруем документы: только те, где владелец — текущий юзер
                $query->where('created_by', auth()->id());
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('version.index', compact('versions'));
    }


    public function create()
    {

        $documents = Document::where('created_by', auth()->id())->get();

        return view('version.create', compact('documents'));
    }


    public function store(Request $request)
    {

        $request->validate([
            'document_id' => [
                'required',
                'exists:documents,id,created_by,' . auth()->id()
            ],
            'file_path' => 'required|file',
        ]);

        return DB::transaction(function () use ($request) {
            $file = $request->file('file_path');
            $filePath = $file->store('versions', 'public');

            $lastVersion = DocumentVersion::where('document_id', $request->document_id)
                ->lockForUpdate()
                ->max('version');

            $nextVersion = $lastVersion ? $lastVersion + 1 : 1;

            $version = DocumentVersion::create([
                'document_id'   => $request->document_id,
                'user_id'       => auth()->id(),
                'version'       => $nextVersion,
                'file_path'     => $filePath,
                'original_name' => $file->getClientOriginalName(),
                'extension'     => $file->getClientOriginalExtension(),
                'file_size'     => $file->getSize(),
            ]);

            return redirect()->route('versions.index')
                ->with('success', "Версия V{$version->version} добавлена");
        });
    }


    public function show(DocumentVersion $version)
    {
        if ($version->document->created_by !== auth()->id()) {
            abort(403, 'У вас нет доступа к этой версии.');
        }

        $version->load('document');
        return view('version.show', compact('version'));
    }


    public function edit(DocumentVersion $version)
    {
       if ($version->document->created_by !== auth()->id()) {
            abort(403, 'У вас нет доступа к этой версии.');
        }

       $documents = Document::where('created_by', auth()->id())->get();

        return view('version.edit', compact('version', 'documents'));
    }


    public function update(Request $request, DocumentVersion $version)
    {
        if ($version->document->created_by !== auth()->id()) {
            abort(403, 'Действие запрещено.');
        }

        $request->validate([
            'file_path' => 'required|file',
        ]);

        if ($request->hasFile('file_path')) {

            return DB::transaction(function () use ($request, $version) {
                $file = $request->file('file_path');
                $filePath = $file->store('versions', 'public');

               $lastVersion = DocumentVersion::where('document_id', $version->document_id)
                    ->lockForUpdate()
                    ->max('version');

                $nextVersion = $lastVersion ? $lastVersion + 1 : 1;

               $newVersion = DocumentVersion::create([
                    'document_id'   => $version->document_id,
                    'user_id'       => auth()->id(),
                    'version'       => $nextVersion,
                    'file_path'     => $filePath,
                    'original_name' => $file->getClientOriginalName(),
                    'extension'     => $file->getClientOriginalExtension(),
                    'file_size'     => $file->getSize(),
                ]);

                return redirect()->route('versions.index')
                    ->with('success', "Создана новая версия V{$newVersion->version}");
            });
        }

        return redirect()->back()->with('error', 'Файл не был загружен');
    }

    public function destroy(DocumentVersion $version)
    {
        if ($version->document->created_by !== auth()->id()) {
            abort(403, 'Действие запрещено.');
        }

        Storage::disk('public')->delete($version->file_path);

        $version->delete();

        return redirect()->route('versions.index')
            ->with('success', 'Удалено');
    }
}
