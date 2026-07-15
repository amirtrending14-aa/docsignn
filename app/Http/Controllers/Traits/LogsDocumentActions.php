<?php

namespace App\Http\Controllers\Traits;

use App\Models\DocumentLog; // Убедитесь, что эта модель существует
use Illuminate\Support\Facades\Auth;

trait LogsDocumentActions
{
    /**
     * Логирует действие по документу.
     */
    protected function logAction(int $docId, string $action, string $desc): void
    {
        DocumentLog::create([
            'document_id' => $docId,
            'user_id'     => Auth::id(),
            'action'      => $action,
            'description' => $desc
        ]);
    }
}