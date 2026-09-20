<?php

namespace App\Observers;

use App\Models\Document;
use App\Models\DocumentLog;
use Illuminate\Support\Facades\Auth;

class DocumentObserver
{
    public function created(Document $document): void
    {
        $actor = Auth::id() ?? $document->created_by;

        // Лог для создателя (исходящее событие)
        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => $document->created_by,
            'action'      => 'CREATED',
            'description' => 'Документ создан: ' . ($document->title ?? 'без названия'),
            'created_at'  => $document->created_at,
            'updated_at'  => $document->created_at,
        ]);

        // Лог для получателя (входящее событие) — если получатель другой
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
    }

    public function updated(Document $document): void
    {
        // Реагируем ТОЛЬКО на смену статуса
        if (!$document->isDirty('status')) return;

        $oldStatus = $document->getOriginal('status');
        $newStatus = $document->status;

        if ($oldStatus === $newStatus) return;

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

        // Лог для создателя
        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => $document->created_by,
            'action'      => $action,
            'description' => $description,
        ]);

        // Лог для получателя
        if ($document->receiver_id && $document->receiver_id !== $document->created_by) {
            DocumentLog::create([
                'document_id' => $document->id,
                'user_id'     => $document->receiver_id,
                'action'      => $action,
                'description' => $description,
            ]);
        }
    }

    public function deleted(Document $document): void
    {
        $actor = Auth::id() ?? $document->created_by;

        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => $document->created_by,
            'action'      => 'DELETED',
            'description' => 'Документ удалён: ' . ($document->title ?? 'без названия'),
        ]);

        // Лог для получателя
        if ($document->receiver_id && $document->receiver_id !== $document->created_by) {
            DocumentLog::create([
                'document_id' => $document->id,
                'user_id'     => $document->receiver_id,
                'action'      => 'DELETED',
                'description' => 'Документ удалён отправителем',
            ]);
        }
    }
}