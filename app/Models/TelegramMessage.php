<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TelegramMessage extends Model
{
    protected $table = 'telegram_messages';
    
    protected $fillable = [
        'telegram_conversation_id',
        'sender_id',
        'body',
        'type',
        'attachment_path',
        'reply_to',
        'is_read',
    ];

    protected $casts = [
        'type' => 'string',
        'is_read' => 'boolean',
    ];

    protected $appends = ['attachment_url'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(TelegramConversation::class, 'telegram_conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyToMessage(): BelongsTo
    {
        return $this->belongsTo(TelegramMessage::class, 'reply_to');
    }

  public function getAttachmentUrlAttribute(): ?string
{
    if (!$this->attachment_path) return null;

    // ✅ Используем наш роут вместо /storage/ (который не работает на Windows)
    return url('/telegram/file/' . $this->attachment_path);
}
}