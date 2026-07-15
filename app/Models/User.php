<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Support\Facades\Storage;

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

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
        // ❌ УБРАЛИ: 'password' => 'hashed',  // Это вызывает двойное хеширование!
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

    // ✅ НОВОЕ: Проверка принадлежности к компании
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

    // ✅ НОВОЕ: Может ли редактировать другого пользователя
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

    // ✅ НОВОЕ: Отметить пользователя как онлайн
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

    // ✅ НОВОЕ: Документы на подписи
    public function documentsForSigning()
    {
        return $this->hasMany(Document::class, 'assigned_to');
    }

    // ✅ НОВОЕ: Все документы компании
    public function companyDocuments()
    {
        if (!$this->company_id) {
            return collect();
        }

        return Document::whereHas('creator', function($q) {
            $q->where('company_id', $this->company_id);
        })->get();
    }

    // ===== АКСЕССУАРЫ =====

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $first = mb_strtoupper(mb_substr($words[0] ?? '', 0, 1));
        $second = isset($words[1]) ? mb_strtoupper(mb_substr($words[1], 0, 1)) : '';
        return $first . $second;
    }

    // ✅ НОВОЕ: Название компании
    public function getCompanyNameAttribute(): string
    {
        if ($this->companyRelation) {
            return $this->companyRelation->name;
        }
        return $this->company ?? 'Моя команда';
    }

    // ✅ НОВОЕ: URL аватара
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

    // ✅ НОВОЕ: Цвет для аватара-заглушки (по имени)
    public function getAvatarColorAttribute(): string
    {
        $colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A',
            '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2',
        ];

        $index = crc32($this->name) % count($colors);
        return $colors[abs($index)];
    }

    // ===== SCOPE МЕТОДЫ (для удобных запросов) =====

    // ✅ Пользователи онлайн
    public function scopeOnline($query)
    {
        return $query->where('last_seen_at', '>', now()->subMinutes(5));
    }

    // ✅ Админы
    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    // ✅ Из той же компании
    public function scopeOfCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // ✅ Активные (не удалённые и с подтверждённым email)
    public function scopeActive($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    // ===== МУТАТОРЫ =====

    // ✅ Автоматически хешировать пароль при установке
    public function setPasswordAttribute($value)
    {
        if ($value && !str_starts_with($value, '$2y$')) {
            $this->attributes['password'] = bcrypt($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }
}