<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Services\DocumentIndexer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IndexAllDocuments extends Command
{
    protected $signature = 'archivist:index-all 
                            {--force : Переиндексировать уже обработанные документы}
                            {--limit=0 : Ограничить количество документов (0 = все)}';

    protected $description = 'Проиндексировать существующие документы для ИИ-Архивиста';

    public function handle(DocumentIndexer $indexer): int
    {
        $force = $this->option('force');
        $limit = (int) $this->option('limit');

        $query = Document::query();

        if (!$force) {
            // ВАЖНО: используем DB::table вместо Eloquent модели
            // Это обходит все проблемы с отношениями модели DocumentIndex
            $indexedIds = DB::table('document_indexes')->pluck('document_id')->all();
            if ($indexedIds !== []) {
                $query->whereNotIn('id', $indexedIds);
            }
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('Нечего индексировать. Используй --force для повторной индексации.');
            return self::SUCCESS;
        }

        if ($limit > 0) {
            $query->limit($limit);
            $total = min($total, $limit);
        }

        $this->info("Будет проиндексировано: {$total} документов");

        if (!$this->confirm('Продолжить?', true)) {
            return self::SUCCESS;
        }

        $bar       = $this->output->createProgressBar($total);
        $success   = 0;
        $failed    = 0;
        $skipped   = 0;
        $failedIds = [];

        $bar->start();

        $query->each(function (Document $doc) use ($indexer, $bar, &$success, &$failed, &$skipped, &$failedIds) {
            try {
                $absolutePath = $indexer->resolveFilePath($doc);

                if ($absolutePath === null) {
                    $skipped++;
                    $bar->advance();
                    return;
                }

                $result = $indexer->index($doc, $absolutePath);

                if ($result) {
                    $success++;
                } else {
                    $skipped++;
                }

                // Увеличенная пауза — 1.5 секунды между документами
                // Это защищает от Groq rate limit (8000 токенов/мин)
                usleep(1500000);
            } catch (\Throwable $e) {
                $failed++;
                $failedIds[] = $doc->id . ' (' . $e->getMessage() . ')';
            }

            $bar->advance();
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Успешно: {$success}");
        $this->warn("⏭ Пропущено (нет файла/пустой): {$skipped}");

        if ($failed > 0) {
            $this->error("❌ Ошибок: {$failed}");
            foreach (array_slice($failedIds, 0, 10) as $err) {
                $this->line("   - {$err}");
            }
        }

        return self::SUCCESS;
    }
}