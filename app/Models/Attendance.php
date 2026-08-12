<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    public const STATUS_ON_TIME = 'on_time';
    public const STATUS_LATE    = 'late';
    public const STATUS_ABSENT  = 'absent';
    public const STATUS_EXCUSED = 'excused';
    public const STATUS_REFUSED = 'refused';

    protected $fillable = [
        'user_id',
        'date',
        'check_in_time',
        'check_out_time',
        'status',
        'fine',
        'refused_at',       // ← ДОБАВЛЕНО
    ];

    protected $casts = [
        'date'       => 'date',
        'fine'       => 'decimal:2',
        'refused_at' => 'datetime',   // ← ДОБАВЛЕНО
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}