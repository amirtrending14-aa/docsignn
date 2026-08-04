<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'document_type',
        'collected_data',
        'language'
    ];

    protected $casts = [
        'collected_data' => 'array', // Автоматически превращает JSON в массив PHP
        'last_active_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}