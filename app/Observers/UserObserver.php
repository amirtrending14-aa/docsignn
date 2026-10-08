<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * ✅ Пользователь создан → добавляем в группу его компании
     */
    public function created(User $user): void
    {
        if (!$user->company_id) {
            return;
        }

        try {
            $user->company->addUserToGroupChat($user->id);
            Log::info("✅ User #{$user->id} added to company #{$user->company_id} group chat");
        } catch (\Throwable $e) {
            Log::error("❌ Failed to add user to group: " . $e->getMessage());
        }
    }

    /**
     * 🔄 Пользователь обновлён → если сменилась компания, переносим в новую группу
     */
    public function updated(User $user): void
    {
        // Проверяем изменение company_id
        if ($user->wasChanged('company_id')) {
            $oldCompanyId = $user->getOriginal('company_id');
            $newCompanyId = $user->company_id;

            // Удаляем из старой группы
            if ($oldCompanyId) {
                $oldCompany = \App\Models\Company::find($oldCompanyId);
                if ($oldCompany) {
                    $oldCompany->removeUserFromGroupChat($user->id);
                    Log::info("👋 User #{$user->id} removed from old company #{$oldCompanyId}");
                }
            }

            // Добавляем в новую группу
            if ($newCompanyId && $user->company) {
                $user->company->addUserToGroupChat($user->id);
                Log::info("✅ User #{$user->id} added to new company #{$newCompanyId}");
            }
        }
    }

    /**
     * 🗑 Пользователь удалён → удаляем из всех чатов
     */
    public function deleted(User $user): void
    {
        try {
            // Удаляем из всех conversations где он состоит
            \App\Models\TelegramConversation::whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->get()->each(function ($conv) use ($user) {
                $conv->users()->detach($user->id);
            });
            
            Log::info("🗑 User #{$user->id} removed from all chats");
        } catch (\Throwable $e) {
            Log::error("❌ Failed to detach user: " . $e->getMessage());
        }
    }
}