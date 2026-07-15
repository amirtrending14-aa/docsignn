<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentStatusChanged extends Notification
{
    use Queueable;

    public $document;
    public $status;

    public function __construct($document, $status)
    {
        $this->document = $document;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'messages' => "Статус документа изменён на {$this->status}",
            'url' => route('documents.show', $this->document->id),
        ];
    }
}
