<?php

namespace App\Http\Controllers;

use App\Services\ArchivistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ArchivistController extends Controller
{
    public function __construct(private ArchivistService $archivist) {}

    public function page()
    {
        return view('archivist.chat');
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate(['message' => ['required', 'string', 'max:2000']]);

        try {
            return response()->json($this->archivist->ask($validated['message'], Auth::id()));
        } catch (\Throwable $e) {
            Log::error('Archivist controller failed.', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'answer' => 'Не удалось выполнить поиск. Попробуйте ещё раз.',
                'documents' => [],
                'meta' => ['found_count' => 0, 'intent' => 'error'],
            ], 500);
        }
    }
}