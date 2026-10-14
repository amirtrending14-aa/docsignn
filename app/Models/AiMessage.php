<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    protected $fillable = [
        'ai_chat_id',
        'role',
        'content',
        'file_path',
        'pdf_path',
    ];

    /**
     * Чат к которому принадлежит сообщение
     * ВАЖНО: явно указываем foreign key 'ai_chat_id'
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(AiChat::class, 'ai_chat_id');
    }
}