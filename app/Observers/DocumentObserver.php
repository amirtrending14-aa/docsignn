<?php

namespace App\Observers;

use App\Models\Document;
use App\Models\DocumentLog;
use App\Services\DocumentIndexer;
use Illuminate\Support\Facades\Auth;

class DocumentObserver
{
    public function __construct(private DocumentIndexer $indexer) {}

    public function created(Document $document): void
    {
        $actor = Auth::id() ?? $document->created_by;

        // === ЛОГИ КАК БЫЛО ===
        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => $document->created_by,
            'action'      => 'CREATED',
            'description' => 'Документ создан: ' . ($document->title ?? 'без названия'),
            'created_at'  => $document->created_at,
            'updated_at'  => $document->created_at,
        ]);

        if ($document->receiver_id && $document->receiver_id !== $document->created_by) {
            DocumentLog::create([
                'document_id' => $document->id,
                'user_id'     => $document->receiver_id,
                'action'      => 'RECEIVED',
                'description' => 'Получен документ от пользователя #' . $document->created_by,
                'created_at'  => $document->created_at,
                'updated_at'  => $document->created_at,
            ]);
        }

        // === НОВОЕ: АВТО-ИНДЕКСАЦИЯ ===
        // try/catch, чтобы ошибка индексации НЕ роняла создание документа
        try {
            $this->indexer->index($document);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function updated(Document $document): void
    {
        // === ЛОГИ КАК БЫЛО ===
        if ($document->isDirty('status')) {
            $oldStatus = $document->getOriginal('status');
            $newStatus = $document->status;

            if ($oldStatus !== $newStatus) {
                $action = match ($newStatus) {
                    Document::STATUS_COMPLETED => 'SIGNED',
                    Document::STATUS_REJECTED  => 'REJECTED',
                    default                    => 'UPDATED',
                };

                $description = match ($action) {
                    'SIGNED'   => 'Документ подписан',
                    'REJECTED' => 'Документ отклонён',
                    default    => "Статус изменён с {$oldStatus} на {$newStatus}",
                };

                DocumentLog::create([
                    'document_id' => $document->id,
                    'user_id'     => $document->created_by,
                    'action'      => $action,
                    'description' => $description,
                ]);

                if ($document->receiver_id && $document->receiver_id !== $document->created_by) {
                    DocumentLog::create([
                        'document_id' => $document->id,
                        'user_id'     => $document->receiver_id,
                        'action'      => $action,
                        'description' => $description,
                    ]);
                }
            }
        }

        // === НОВОЕ: ПЕРЕИНДЕКСАЦИЯ ===
        // Индексируем заново, если:
        // - сменился файл (file_path)
        // - сменился статус (например, SIGNED — появился подписанный файл)
        // - индекса ещё нет (документ создан до внедрения архивиста)
        try {
            if ($document->wasChanged('file_path')
                || $document->wasChanged('status')
                || !$document->index) {
                $this->indexer->index($document);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function deleted(Document $document): void
    {
        if ($document->isForceDeleting()) {
            return;
        }

        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => $document->created_by,
            'action'      => 'DELETED',
            'description' => 'Документ удалён: ' . ($document->title ?? 'без названия'),
        ]);

        if ($document->receiver_id && $document->receiver_id !== $document->created_by) {
            DocumentLog::create([
                'document_id' => $document->id,
                'user_id'     => $document->receiver_id,
                'action'      => 'DELETED',
                'description' => 'Документ удалён отправителем',
            ]);
        }

        // Каскад сам удалит записи из document_indexes и document_chunks
        // (в миграции стоит cascadeOnDelete)
    }
}