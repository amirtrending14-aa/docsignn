<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class DocumentAssigned extends Notification
{
    use Queueable;

    public $document;

    public function __construct($document)
    {
        $this->document = $document;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    // ... остальные методы (via, construct)

    public function toDatabase($notifiable)
    {
        return [
            'messages' => 'Вам назначен документ: ' . $this->document->title,
            'url' => route('documents.show', $this->document->id),
            // Мы явно передаем user_id в массив для базы
            'user_id' => $notifiable->id,
        ];
    }

// Метод toArray можно оставить или удалить,
// если есть toDatabase, Laravel приоритетно возьмет его для базы.
    public function toArray($notifiable)
    {
        return [
            'messages' => 'Вам назначен документ: ' . $this->document->title,
            'url' => route('documents.show', $this->document->id),
        ];
    }
}
