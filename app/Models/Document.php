<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;    // ← ДОБАВЬ ЭТУ СТРОКУ
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Support\Facades\Auth;

class Document extends Model
{
    use SoftDeletes;
    use HasFactory;
const STATUS_DRAFT      = 'draft';
const STATUS_ACTIVE     = 'active';
const STATUS_PROCESSING = 'processing';
const STATUS_COMPLETED  = 'completed';
const STATUS_REJECTED   = 'rejected';
const STATUS_APPROVED   = 'approved';
const STATUS_FAILED     = 'failed';

    protected $fillable = [
    'number',
    'title',
    'content',
    'type',
    'file_path',
    'status',
    'created_by',
    'sender_id',      // ← ДОБАВЛЕНО
    'receiver_id',
    'receiver_mode',  // ← ДОБАВЛЕНО
    'deadline',
];

    protected $casts = [
        'deadline' => 'datetime',
    ];


  public function scopeVisibleToAuth($query)
{
    $user = Auth::user();

    if (!$user) return $query->whereRaw('1 = 0');

    // Админ видит всё
    if ($user->is_admin) {
        return $query;
    }

    return $query->where(function ($q) use ($user) {
        $q->where('created_by', $user->id)
          ->orWhere('sender_id', $user->id)           // ← ДОБАВЛЕНО
          ->orWhere(function ($subQ) use ($user) {
              $subQ->where('receiver_id', $user->id)
                   ->where('status', '!=', self::STATUS_DRAFT);
          });
    });
}
public function sender(): BelongsTo
{
    return $this->belongsTo(User::class, 'sender_id');
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

    // ✅ ДОБАВЬ ЭТОТ МЕТОД СЮДА:
    public function comments(): HasMany
    {
        return $this->hasMany(DocumentComment::class);
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


 // В файле app/Models/Document.php

public function isSigned(): bool
{
    // Проверяем наличие реальной подписи (не null и не пустая строка)
    $hasRealSignature = $this->signatures()
        ->whereNotNull('signature')
        ->where('signature', '!=', '')
        ->exists();

    // Документ подписан, если есть подписи, или если он уже переведен в статус завершенного
    return $hasRealSignature || in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_APPROVED]);
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


    public function recipients()
    {
        return $this->belongsToMany(User::class, 'document_user') // Если таблица называется иначе, поменяй 'document_user' на её имя
        ->withPivot('status', 'created_at', 'updated_at')
            ->withTimestamps();
    }
    public function index(): HasOne
{
    return $this->hasOne(DocumentIndex::class);
}

public function chunks(): HasMany
{
    return $this->hasMany(DocumentChunk::class);
}
}
