<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema; // Добавлено для проверки схемы

class User extends Authenticatable implements CanResetPassword
{
    use HasFactory, Notifiable, SoftDeletes, CanResetPasswordTrait;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'role',
        'company',
        'company_id',
        'created_by',
        'level',
        'is_admin',
        'is_super_admin',
        'last_seen_at',
        'email_verified_at',
        'organization_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'level' => 'integer',
        'is_admin' => 'boolean',
        'is_super_admin' => 'boolean',
    ];

    protected $attributes = [
        'role' => 'employee',
        'level' => 2,
        'is_admin' => false,
        'is_super_admin' => false,
    ];

    // ===== МЕТОДЫ ДЛЯ ВОССТАНОВЛЕНИЯ ПАРОЛЯ =====

    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
    }

    // ===== МЕТОДЫ ПРОВЕРКИ РОЛЕЙ =====

    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    public function isAdmin(): bool
    {
        return $this->is_admin === true || $this->level === 1;
    }

    public function isEmployee(): bool
    {
        return !$this->isAdmin() && !$this->isSuperAdmin();
    }

    public function isOnline(): bool
    {
        if (!$this->last_seen_at) {
            return false;
        }
        return $this->last_seen_at->gt(now()->subMinutes(5));
    }

    // ===== ПРОВЕРКИ ПРАВ ДОСТУПА =====

    public function isInSameCompany(User $user): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->company_id && $user->company_id) {
            return $this->company_id === $user->company_id;
        }

        return $this->company === $user->company;
    }

    public function canEditUser(User $user): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->id === $user->id) {
            return true;
        }

        if ($this->isAdmin() && $this->isInSameCompany($user)) {
            return true;
        }

        return false;
    }

    public function markAsOnline(): void
    {
        $this->update(['last_seen_at' => now()]);
    }

    // ===== СВЯЗИ =====

    public function companyRelation()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    public function documentsForSigning()
    {
        return $this->hasMany(Document::class, 'assigned_to');
    }

    public function companyDocuments()
    {
        if (!$this->company_id) {
            return collect();
        }

        return Document::whereHas('creator', function($q) {
            $q->where('company_id', $this->company_id);
        })->get();
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'user_departments')
            ->withPivot('position')
            ->withTimestamps();
    }

    // ===== АКСЕССУАРЫ =====

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $first = mb_strtoupper(mb_substr($words[0] ?? '', 0, 1));
        $second = isset($words[1]) ? mb_strtoupper(mb_substr($words[1], 0, 1)) : '';
        return $first . $second;
    }

    public function getCompanyNameAttribute(): string
    {
        if ($this->companyRelation) {
            return $this->companyRelation->name;
        }
        return $this->company ?? 'Моя команда';
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            return null;
        }

        if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }

        return Storage::disk('public')->url($this->avatar);
    }

    public function getAvatarColorAttribute(): string
    {
        $colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A',
            '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2',
        ];

        $index = crc32($this->name) % count($colors);
        return $colors[abs($index)];
    }

    public function getRoleLabelAttribute(): string
    {
        if ($this->is_super_admin) {
            return 'Super Admin';
        }

        if ($this->is_admin) {
            return 'Admin';
        }

        return $this->role ?: 'Employee';
    }

    // ===== SCOPE МЕТОДЫ =====

    public function scopeOnline($query)
    {
        return $query->where('last_seen_at', '>', now()->subMinutes(5));
    }

    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    public function scopeOfCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    // ===== МУТАТОРЫ =====

    public function setPasswordAttribute($value)
    {
        if ($value && !str_starts_with($value, '$2y$')) {
            $this->attributes['password'] = bcrypt($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    // ==========================================
    // ✅ ИСПРАВЛЕННЫЕ МЕТОДЫ ДЛЯ РАБОТЫ С КОМПАНИЯМИ
    // ==========================================

    /**
     * Проверяет, существует ли колонка parent_id в таблице companies
     */
    private function hasParentColumn(): bool
    {
        return Schema::hasColumn('companies', 'parent_id');
    }

    /**
     * Возвращает ВСЕ компании дерева, к которому принадлежит пользователь.
     * Если иерархии нет, возвращает только текущую компанию.
     */
  public function managedCompanies(): Collection
{
    if (!$this->company_id) {
        return collect();
    }

    // ✅ Подгрузка связей во ВСЕХ точках возврата
    $relations = ['owner', 'region', 'city', 'users'];

    if (!$this->hasParentColumn()) {
        return Company::with($relations)->where('id', $this->company_id)->get();
    }

    $root = $this->companyRelation;
    
    $visited = [];
    while ($root && $root->parent_id !== null && !in_array($root->id, $visited)) {
        $visited[] = $root->id;
        $root = $root->parent;
    }

    if (!$root) {
        return Company::with($relations)->where('id', $this->company_id)->get();
    }

    $treeIds = $this->getAllTreeIds($root->id);

    return Company::with($relations)->whereIn('id', $treeIds)->get();
}

    /**
     * Рекурсивно собирает все ID дочерних компаний
     */
    private function getAllTreeIds($parentId, $ids = []): array
    {
        $ids[] = $parentId;
        
        // Проверка существования колонки перед запросом
        if (!$this->hasParentColumn()) {
            return $ids;
        }

        $children = Company::where('parent_id', $parentId)->pluck('id')->toArray();
        
        foreach ($children as $childId) {
            $ids = $this->getAllTreeIds($childId, $ids);
        }
        
        return $ids;
    }

    /**
     * Может ли этот пользователь управлять компанией $target?
     */
       /**
     * Может ли этот пользователь управлять компанией $target?
     */
     /**
     * Может ли этот пользователь УПРАВЛЯТЬ (редактировать/удалять) компанией $target?
     */
    public function canManageCompany(Company $target): bool
    {
        // 1. Супер-админ может всё
        if ($this->is_super_admin) {
            return true;
        }

        // 2. Без компании управлять нельзя
        if (!$this->company_id) {
            return false;
        }

        // 3. Управлять можно ТОЛЬКО своей компанией
        if ((int)$this->company_id === (int)$target->id) {
            // И только если ты админ или владелец
            return $this->is_admin || ((int)$target->owner_id === (int)$this->id);
        }

        // 4. Чужие компании редактировать нельзя
        return false;
    }

    public function canViewCompany(Company $target): bool
    {
        // 1. Супер-админ видит всё
        if ($this->is_super_admin) {
            return true;
        }

        // 2. Обычные пользователи без компании ничего не видят в разделе компаний
        if (!$this->company_id) {
            return false;
        }

        // 3. Если у пользователя есть компания, он может просматривать другие компании
        // (Это снимает ошибку 403 при переходе по ссылке)
        return true;
    }
}