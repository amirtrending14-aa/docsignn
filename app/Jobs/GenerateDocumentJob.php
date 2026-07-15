<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\GeminiDocumentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $document;
    protected $type;
    protected $recipient;
    protected $details;
    protected $format;

    public function __construct(
        Document $document,
        string $type,
        string $recipient,
        array $details,
        string $format
    ) {
        $this->document = $document;
        $this->type = $type;
        $this->recipient = $recipient;
        $this->details = $details;
        $this->format = $format;
    }

    public function handle(GeminiDocumentService $service): void
    {
        try {
            // Обновляем статус на "генерируется"
            $this->document->update(['status' => 'processing']);

            // Генерируем контент через Gemini
            $result = $service->generateContent(
                $this->type,
                $this->recipient,
                $this->details
            );

            if (!$result['success']) {
                $this->document->update([
                    'status' => 'failed',
                    'content' => 'Ошибка генерации: ' . $result['error']
                ]);
                return;
            }

            $content = $result['content'];
            $filename = 'doc_' . $this->document->id . '_' . time();

            // Создаём файл в нужном формате
            if ($this->format === 'pdf') {
                $filePath = $service->createPdf($content, $filename);
            } else {
                $filePath = $service->createWord($content, $filename);
            }

            // Определяем заголовок из первых строк
            $title = 'Документ (ИИ)';
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $line = trim($line);
                if (str_starts_with($line, '# ')) {
                    $title = substr($line, 2);
                    break;
                }
                if (!empty($line)) {
                    $title = $line;
                    break;
                }
            }

            // Обновляем документ
            $this->document->update([
                'title' => $title,
                'content' => $content,
                'file_path' => $filePath,
                'status' => 'draft',
            ]);

            Log::info('Документ успешно сгенерирован', [
                'document_id' => $this->document->id,
                'format' => $this->format
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка генерации документа', [
                'document_id' => $this->document->id,
                'error' => $e->getMessage()
            ]);

            $this->document->update([
                'status' => 'failed',
                'content' => 'Ошибка: ' . $e->getMessage()
            ]);
        }
    }
}