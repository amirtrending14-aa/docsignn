<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramMessage extends Model
{
    protected $fillable = [
        'telegram_conversation_id',
        'sender_id',
        'type',
        'body',
        'attachment_path',
        'reply_to',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function conversation()
    {
        return $this->belongsTo(TelegramConversation::class, 'telegram_conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /** ✅ Сообщение, на которое ответили */
    public function replyTo()
    {
        return $this->belongsTo(TelegramMessage::class, 'reply_to');
    }
}