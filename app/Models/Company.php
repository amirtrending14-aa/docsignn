<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // ✅ ДОБАВЛЕНО
use Illuminate\Database\Eloquent\Relations\HasMany;   // ✅ ДОБАВЛЕНО
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'email',
        'password',
        'status',
        'owner_id',           // ✅ Владелец компании
        'owner_telegram_id',
        'address',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * ✅ ДОБАВЛЕНО: Связь "Компания принадлежит одному владельцу (User)"
     * Теперь $company->owner будет возвращать объект User или null.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Связь "У компании много пользователей"
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Связь "У компании много документов"
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Автоматическая генерация slug при создании компании
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($company) {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name);

                // Проверяем уникальность slug
                $originalSlug = $company->slug;
                $count = static::where('slug', 'LIKE', "{$company->slug}%")->count();

                if ($count > 0) {
                    $company->slug = "{$originalSlug}-" . ($count + 1);
                }
            }
        });
    }
}