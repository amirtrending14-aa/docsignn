<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Support\Facades\Auth;

class Document extends Model
{
    use SoftDeletes;
    use HasFactory;
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_REJECTED = 'rejected';

    protected $fillable =[
        'number',
        'title',
        'content',
        'type',
        'file_path',
        'status',
        'created_by',
        'user_id',
        'receiver_id',
        'deadline'
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];


    public function scopeVisibleToAuth($query)
    {
        $user = Auth::user();

        if (!$user) return $query->whereRaw('1 = 0');

        if ($user->is_admin) {
            return $query;
        }

        return $query->where(function($q) use ($user) {
            $q->where('created_by', $user->id)->orWhere(function($subQ) use ($user) {
                    $subQ->where('receiver_id', $user->id)->where('status', '!=', self::STATUS_DRAFT);
                });
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }


    public function signatures(): HasMany
    {
        return $this->hasMany(DocumentSignature::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(DocumentLog::class);
    }



    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }


    public function isWaiting(): bool
    {
        return $this->status === self::STATUS_ACTIVE &&
            !$this->signatures()->where('signature', '!=', '')->exists();
    }


    public function isSigned(): bool
    {
        return $this->status === self::STATUS_ACTIVE &&
            $this->signatures()->where('signature', '!=', '')->exists();
    }


    public function canManage(): bool
    {
        $user = Auth::user();
        if (!$user) return false;


        return $user->is_admin || $this->created_by === $user->id;
    }



    public function getStatusLabelAttribute(): string
    {
        if ($this->status === self::STATUS_DRAFT) return 'ЧЕРНОВИК';
        if ($this->status === self::STATUS_REJECTED) return 'ОТКЛОНЕН';

        return $this->isSigned() ? 'ПОДПИСАН' : 'ОЖИДАЕТ';
    }


    public function getStatusStyleAttribute(): string
    {
        if ($this->status === self::STATUS_DRAFT) {
            return 'bg-gray-100 text-gray-600 border-gray-300';
        }

        if ($this->status === self::STATUS_REJECTED) {
            return 'bg-red-50 text-red-600 border-red-600';
        }

        if ($this->isSigned()) {
            return 'bg-green-50 text-green-600 border-green-600';
        }

        return 'bg-yellow-50 text-yellow-600 border-yellow-600';
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


}
