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

    protected $fillable = [
        'user_id',
        'date',
        'check_in_time',
        'status',
        'fine',
    ];

    protected $casts = [
        'date' => 'date',
        'fine' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}