<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;

class SyncCompanyGroups extends Command
{
    protected $signature = 'chat:sync-company-groups';
    protected $description = 'Создать группы компаний и добавить всех пользователей';

  public function handle(): int
{
    $companies = Company::with('users')->get();
    
    $this->info("🏢 Found {$companies->count()} companies");

    $bar = $this->output->createProgressBar($companies->count());
    $bar->start();

    $successCount = 0;
    $errorCount = 0;

   foreach ($companies as $company) {
    try {
        $chat = $company->getOrCreateGroupChat();

        // Наполняем участниками
        $attach = [];
        foreach ($company->users as $u) {
            $attach[$u->id] = ['role' => 'member'];
        }
        $chat->users()->syncWithoutDetaching($attach);

        // ✅ Владелец компании = создатель и админ группы
        if ($company->owner_id) {
            if (!$chat->created_by) {
                $chat->update(['created_by' => $company->owner_id]);
            }
            $chat->users()->updateExistingPivot($company->owner_id, ['role' => 'admin']);
        }

        $this->newLine();
        $this->line("  ✅ {$company->name}: chat #{$chat->id}, участников: " . $chat->users()->count());
    } catch (\Throwable $e) {
        $this->newLine();
        $this->error("  ❌ {$company->name}: " . $e->getMessage());
    }
    $bar->advance();
}

    $bar->finish();
    $this->newLine(2);
    $this->info("✅ Done! Success: {$successCount}, Errors: {$errorCount}");

    return $errorCount > 0 ? Command::FAILURE : Command::SUCCESS;
}
}