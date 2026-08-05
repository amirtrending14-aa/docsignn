<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'company_number',  // ✅ ДОБАВЛЕНО: номер компании
        'type',
        'level',           // ✅ ДОБАВЛЕНО: уровень в дереве
        'region_id',
        'city_id',
        'email',
        'password',
        'status',
        'owner_id',
        'owner_telegram_id',
        'address',
        'parent_id',     
          'work_start_time',
    'late_tolerance_minutes',
    'late_fine',
    'absence_fine',  
    'late_fine_per_minute',
    'late_block_minutes', 'late_block_fine'
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
   

}