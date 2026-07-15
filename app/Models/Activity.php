<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Activity extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Отношение к пользователю
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Скоуп: только логины
    public function scopeLogins(Builder $query): Builder
    {
        return $query->where('action', 'login');
    }

    // Скоуп: только действия с документами
    public function scopeDocumentActions(Builder $query): Builder
    {
        return $query->whereIn('action', [
            'document_created',
            'document_updated',
            'document_deleted',
            'document_signed',
        ]);
    }

    // Скоуп: за последние N дней
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Красивое название действия
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'login' => 'Вход в систему',
            'logout' => 'Выход из системы',
            'document_created' => 'Создание документа',
            'document_updated' => 'Обновление документа',
            'document_deleted' => 'Удаление документа',
            'document_signed' => 'Подписание документа',
            'user_created' => 'Создание пользователя',
            'user_updated' => 'Обновление пользователя',
            'user_deleted' => 'Удаление пользователя',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}