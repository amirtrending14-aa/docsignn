<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->is_super_admin ?? false) return true;
        return null;
    }
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Document $document): bool
    {
        // Смотри на поле в БД. Если у тебя owner_id, замени user_id на owner_id
        return $document->user_id === $user->id;
    }


    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Document $document): bool
    {
        return $document->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Document $document): bool
    {
        return $document->user_id === $user->id;
    }


    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Document $document): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function reject(User $user, Document $document): bool
    {
        return $document->user_id === $user->id;
    }
    public function sign(User $user, Document $document): bool
    {
        // Либо свой документ, либо тот, который отправили на подпись (адаптируй под свою логику)
        return $document->user_id === $user->id;
    }
}
