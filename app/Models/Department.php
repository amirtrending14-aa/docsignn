<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Department extends Model
{
    use SoftDeletes;

    private const MAX_USERS = 10;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'level',
        'parent_id',
        'icon',
        'color',
        'description',
    ];

    protected $casts = [
        'level'      => 'integer',
        'company_id' => 'integer',
    ];

    // =========================================================
    //  BOOT
    // =========================================================
    protected static function booted(): void
    {
        static::creating(function (self $dept): void {
            if (empty($dept->slug)) {
                $dept->slug = Str::slug($dept->name) . '-' . Str::random(4);
            }
        });

        static::saving(function (self $dept): void {
            if ($dept->level === 1) {
                $dept->parent_id = null;
            }
        });
    }

    // =========================================================
    //  СВЯЗИ
    // =========================================================

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }

    /**
     * Пользователи отдела (БЕЗ position).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'department_user')
            ->withTimestamps();
    }

    // =========================================================
    //  ЛИМИТ ПОЛЬЗОВАТЕЛЕЙ
    // =========================================================

    public function isFull(): bool
    {
        return $this->users()->count() >= self::MAX_USERS;
    }

    public function remainingSlots(): int
    {
        return max(0, self::MAX_USERS - $this->users()->count());
    }

    public static function maxUsers(): int
    {
        return self::MAX_USERS;
    }

    // =========================================================
    //  ИЕРАРХИЯ
    // =========================================================

    public function ancestors(): array
    {
        $chain   = [];
        $current = $this->parent;

        while ($current) {
            array_unshift($chain, $current);
            $current = $current->parent;
        }

        return $chain;
    }

    public function breadcrumb(): string
    {
        $parts   = array_map(fn($a) => $a->name, $this->ancestors());
        $parts[] = $this->name;

        return implode(' / ', $parts);
    }

    public function allChildrenIds(): array
    {
        $ids = [];

        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids   = array_merge($ids, $child->allChildrenIds());
        }

        return $ids;
    }

    // =========================================================
    //  ДЕРЕВО ДЛЯ SELECT
    // =========================================================

    public static function getTree(
        ?int $companyId = null,
        ?int $parentId  = null,
        int  $level     = 0,
        ?int $excludeId = null
    ): array {
        $result = [];
        $query  = self::query();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $query->where('parent_id', $parentId)->orderBy('name');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        foreach ($query->get() as $dept) {
            $indent     = str_repeat('│  ', $level);
            $prefix     = $level > 0 ? $indent . '├─ ' : '';
            $levelBadge = "[Ур.{$dept->level}]";

            $result[$dept->id] = $prefix . $dept->icon . ' ' . $dept->name . ' ' . $levelBadge;

            if ($excludeId && $dept->id === $excludeId) {
                continue;
            }

            $result += self::getTree($companyId, $dept->id, $level + 1, $excludeId);
        }

        return $result;
    }

    // =========================================================
    //  ДОПУСТИМЫЕ РОДИТЕЛИ
    // =========================================================

    public static function allowedParents(int $level, int $companyId): array
    {
        if ($level <= 1) {
            return [];
        }

        return self::where('company_id', $companyId)
            ->where('level', $level - 1)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    // =========================================================
    //  НАЗВАНИЯ И ЦВЕТА УРОВНЕЙ
    // =========================================================

    public static function levelNames(): array
    {
        return [
            1  => '🏛️ Дивизион',
            2  => '🏢 Управление',
            3  => '📁 Отдел',
            4  => '📂 Сектор',
            5  => '👥 Группа',
            6  => '🔹 Подгруппа',
            7  => '▫️ Звено 7',
            8  => '▫️ Звено 8',
            9  => '▫️ Звено 9',
            10 => '▫️ Звено 10',
        ];
    }

    public function levelName(): string
    {
        return self::levelNames()[$this->level] ?? "Уровень {$this->level}";
    }

    public static function levelColor(int $level): string
    {
        $colors = [
            1  => '#4f8cff', 2  => '#6366f1', 3  => '#8b5cf6', 4  => '#a855f7',
            5  => '#d946ef', 6  => '#ec4899', 7  => '#f43f5e', 8  => '#f97316',
            9  => '#f59e0b', 10 => '#eab308', 11 => '#84cc16', 12 => '#22c55e',
            13 => '#10b981', 14 => '#14b8a6', 15 => '#06b6d4', 16 => '#0ea5e9',
            17 => '#3b82f6', 18 => '#6366f1', 19 => '#8b5cf6', 20 => '#a855f7',
        ];

        return $colors[$level] ?? '#4f8cff';
    }
}