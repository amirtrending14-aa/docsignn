<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{

    public function before(User $user, string $ability): bool|null
    {
        if ($user->is_super_admin ?? false) return true;
        return null;
    }
    public function viewAny(User $user): bool { return false; }
    /**
     * Determine whether the users can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the users can create models.
     */
    public function create(User $user): bool { return false; }

    /**
     * Determine whether the users can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the users can delete the model.
     */
    public function delete(User $authUser, User $targetUser)
    {
        // 1. Нельзя удалить самого себя
        if ($authUser->id === $targetUser->id) {
            return false;
        }

        // 2. Если ты Админ, можешь удалять всех, кроме других админов
        if ($authUser->role === 'admin') {
            return $targetUser->role !== 'admin';
        }

        // 3. Если ты Директор, можешь удалять только сотрудников и пользователей
        if ($authUser->role === 'director') {
            return in_array($targetUser->role, ['employee', 'users']);
        }

        return false;
    }

    /**
     * Determine whether the users can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the users can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
