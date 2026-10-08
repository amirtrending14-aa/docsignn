<?php

namespace App\Observers;

use App\Models\Company;

class CompanyObserver
{
    public function updated(Company $company): void
    {
        // Если сменилось название — обновляем имя группы
        if ($company->wasChanged('name') && $company->groupChat) {
            $company->groupChat->update(['name' => $company->name]);
        }
    }

    public function deleted(Company $company): void
    {
        // Группа удалится автоматически через cascadeOnDelete
        // Но можно добавить логирование
        \Log::info("🏢 Company #{$company->id} deleted, group removed");
    }
}