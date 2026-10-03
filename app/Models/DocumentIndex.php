<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentIndex extends Model
{
    // ⚠️ ВАЖНО: Laravel по умолчанию плюрализует DocumentIndex → document_indices
    // А миграция создала таблицу document_indexes — указываем явно
    protected $table = 'document_indexes';

    protected $fillable = [
        'document_id',
        'extracted_text',
        'ai_summary',
        'people',
        'indexed_at',
    ];

    protected $casts = [
        'people'     => 'array',
        'indexed_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}