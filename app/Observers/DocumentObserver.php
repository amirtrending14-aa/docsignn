<?php

namespace App\Observers;

use App\Models\Document;
use App\Models\DocumentLog;
use Illuminate\Support\Facades\Auth;

class DocumentObserver
{
    /**
     * Срабатывает после создания документа
     */
    public function created(Document $document): void
    {
        $this->createLog($document, 'CREATED', 'Документ создан в системе');
    }

    /**
     * Срабатывает после обновления документа
     */
    public function updated(Document $document): void
    {
        // Можно даже записывать, какие именно поля изменились
        $changes = $document->getChanges();
        unset($changes['updated_at']); // Игнорируем техническое поле

        $this->createLog($document, 'UPDATED', 'Изменены поля: ' . json_encode($changes, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Срабатывает перед удалением документа
     */
    public function deleted(Document $document): void
    {
        $this->createLog($document, 'DELETED', 'Документ удален пользователем');
    }

    /**
     * Вспомогательный метод для записи в базу
     */
    private function createLog(Document $document, string $action, string $description): void
    {
        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => Auth::id(), // ID текущего пользователя
            'action'      => $action,
            'description' => $description,
        ]);
    }
}
