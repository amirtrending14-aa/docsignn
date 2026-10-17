<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestRequest extends Model
{
    protected $fillable = [
        'user_id', 'sender_who', 'sender_email', 'sender_phone',
        'doc_type', 'doc_title', 'doc_number', 'doc_description',
        'doc_file', 'deadline', 'recipient_user_id',
        'status', 'admin_comment',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'new'         => 'Новая',
            'in_progress' => 'В работе',
            'answered'    => 'Отвечена',
            'closed'      => 'Закрыта',
            default       => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'new'         => 'bg-blue-100 text-blue-700',
            'in_progress' => 'bg-yellow-100 text-yellow-700',
            'answered'    => 'bg-green-100 text-green-700',
            'closed'      => 'bg-gray-100 text-gray-700',
            default       => 'bg-gray-100',
        };
    }
}