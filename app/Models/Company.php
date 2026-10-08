<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Models\TelegramConversation;

class Company extends Model
{
    use HasFactory;

       protected $fillable = [
        'slug',
        'name',
        'company_number',
        'type',
        'level',
        'region_id',
        'city_id',
        'email',
        'password',
        'status',
        'owner_id',
        'owner_telegram_id',
        'address',
        'parent_id',
        
        // Поля для штрафов и посещаемости
        'work_start_time',
        'late_tolerance_minutes',
        'late_block_minutes',
        'late_block_fine',
        'late_fine',
        'late_fine_per_minute',
        'absence_fine',
    ];

    protected $hidden = ['password'];

    // ✅ СВЯЗИ (твои)
    public function region(): BelongsTo {
        return $this->belongsTo(Region::class);
    }

    public function city(): BelongsTo {
        return $this->belongsTo(City::class);
    }

    public function owner(): BelongsTo {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): HasMany {
        return $this->hasMany(User::class);
    }

    // ✅ ДОБАВЛЕНО: ДЕРЕВО КОМПАНИЙ
    /** Родительская компания */
    public function parent(): BelongsTo {
        return $this->belongsTo(Company::class, 'parent_id');
    }

    /** Прямые дочерние компании */
    public function children(): HasMany {
        return $this->hasMany(Company::class, 'parent_id');
    }

    /** Все потомки рекурсивно (каскад вниз — Вариант B) */
    public function allDescendants(): \Illuminate\Support\Collection {
        $descendants = collect();
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->allDescendants());
        }
        return $descendants;
    }

    /** Является ли корневой компанией */
    public function isRoot(): bool {
        return $this->parent_id === null;
    }

    // ✅ Твой boot (без изменений)
        protected static function boot()
    {
        parent::boot();
        static::creating(function ($company) {
            if (empty($company->slug)) {
                $baseSlug = Str::slug($company->name);
                $slug = $baseSlug;
                $count = 1;
                
                // ✅ ИСПРАВЛЕНО: Более точная проверка существующих слагово
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $count;
                    $count++;
                }
                
                $company->slug = $slug;
            }
        });
    }
   

// === ГРУППОВОЙ ЧАТ КОМПАНИИ ===

/**
 * Связь: у компании может быть один групповой чат
 */
public function groupChat()
{
    return $this->hasOne(TelegramConversation::class, 'company_id')
                ->where('type', 'group');
}

/**
 * 🎯 Получить существующую группу компании ИЛИ создать новую
 * Используется при добавлении пользователя
 */
public function getOrCreateGroupChat(): TelegramConversation
{
    // ✅ Ищем напрямую в БД (минуя кэш отношения — это причина бага)
    $existing = TelegramConversation::where('company_id', $this->id)->first();

    if ($existing) {
        // Если название компании изменилось — синхронизируем
        if ($existing->name !== $this->name) {
            $existing->update(['name' => $this->name]);
        }
        // Подгружаем пользователей если не подгружены
        if (!$existing->relationLoaded('users')) {
            $existing->load('users');
        }
        return $existing;
    }

    // Создаём новую группу
    return TelegramConversation::create([
        'type'       => 'group',
        'name'       => $this->name,
        'company_id' => $this->id,
    ]);
}

/**
 * 🔗 Добавить пользователя в групповой чат компании
 */
public function addUserToGroupChat($userId): void
{
    $chat = $this->getOrCreateGroupChat();
    
    // Добавляем если ещё нет (ignore если уже есть)
    if (!$chat->users()->where('users.id', $userId)->exists()) {
        $chat->users()->attach($userId, ['last_read_at' => now()]);
    }
}

/**
 * 🔓 Удалить пользователя из группового чата компании
 */
public function removeUserFromGroupChat($userId): void
{
    if ($this->groupChat) {
        $this->groupChat->users()->detach($userId);
    }
}

}