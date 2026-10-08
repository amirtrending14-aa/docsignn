<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\DocumentIndex;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\TextBreak;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use Smalot\PdfParser\Parser as PdfParser;
use Throwable;

class DocumentIndexer
{
    public function __construct(private GroqClient $groq) {}

    /**
     * Публичный метод — умный поиск файла документа на диске.
     * Используется и индексатором, и командой index-all.
     */
    public function resolveFilePath(Document $document): ?string
    {
        $relative = $document->file_path ?? null;

        if (!$relative) {
            return null;
        }

        // Уже абсолютный путь
        if (str_starts_with($relative, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:[\\\\\/]/', $relative)) {
            return is_file($relative) ? $relative : null;
        }

        // Проверяем все типовые места, где мог оказаться файл
        $candidates = [
            storage_path('app/public/' . ltrim($relative, '/')),   // storage/app/public/documents/...
            storage_path('app/' . ltrim($relative, '/')),          // storage/app/documents/...
            public_path($relative),                                // public/documents/...
            base_path($relative),                                  // корень проекта
        ];

        foreach ($candidates as $candidate) {
            if ($candidate !== '' && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Проиндексировать документ.
     */
    public function index(Document $document, ?string $absolutePath = null): bool
    {
        try {
            $path = $absolutePath ?: $this->resolveFilePath($document);

            if ($path === null || !is_file($path)) {
                Log::info('DocumentIndexer: file not found for document #' . $document->id);
                return false;
            }

            $text = trim(preg_replace('/[ \t]+/u', ' ', $this->extractText($path)) ?? '');

            if (mb_strlen($text) < 3) {
                return false;
            }

            $meta = $this->buildMeta($text);

            DocumentIndex::updateOrCreate(
                ['document_id' => $document->id],
                [
                    'extracted_text' => $text,
                    'ai_summary'     => $meta['summary'],
                    'people'         => $meta['people'],
                    'indexed_at'     => now(),
                ]
            );

            DocumentChunk::where('document_id', $document->id)->delete();

            $i = 0;
            foreach ($this->chunk($text) as $content) {
                DocumentChunk::create([
                    'document_id' => $document->id,
                    'chunk_index' => $i++,
                    'content'     => $content,
                ]);
            }

            return true;
        } catch (Throwable $e) {
            Log::error('DocumentIndexer failed', [
                'document_id' => $document->id,
                'error'       => $e->getMessage(),
            ]);

            return false;
        }
    }

    /** Достать текст из PDF / Word / Excel / RTF / TXT */
    public function extractText(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf'       => $this->pdfText($path),
            'doc'       => $this->wordText($path, 'MsDoc'),
            'docx'      => $this->wordText($path, 'Word2007'),
            'rtf'       => $this->wordText($path, 'RTF'),
            'xls', 'xlsx' => $this->spreadsheetText($path),
            'txt', 'md' => (string) file_get_contents($path),
            default     => '',
        };
    }

    private function pdfText(string $path): string
    {
        try {
            return (new PdfParser())->parseFile($path)->getText();
        } catch (Throwable $e) {
            Log::warning('PDF parse failed: ' . $e->getMessage());
            return '';
        }
    }

    private function wordText(string $path, string $reader): string
    {
        try {
            $phpWord = IOFactory::load($path, $reader);
            $out = '';

            foreach ($phpWord->getSections() as $section) {
                $out .= $this->walkElements($section->getElements());
            }

            return $out;
        } catch (Throwable $e) {
            Log::warning('DOCX parse failed: ' . $e->getMessage());  // ← ИСПРАВЛЕНО: было Log->warning
            return '';
        }
    }

    private function spreadsheetText(string $path): string
    {
        try {
            $reader = SpreadsheetIOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            $rows = [];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $rows[] = $worksheet->getTitle();
                foreach ($worksheet->getRowIterator() as $row) {
                    $values = [];
                    foreach ($row->getCellIterator() as $cell) {
                        $value = trim((string) $cell->getFormattedValue());
                        if ($value !== '') $values[] = $value;
                    }
                    if ($values !== []) $rows[] = implode(' | ', $values);
                }
            }

            $spreadsheet->disconnectWorksheets();

            return implode("\n", $rows);
        } catch (Throwable $e) {
            Log::warning('Spreadsheet parse failed: ' . $e->getMessage());
            return '';
        }
    }

    /** Рекурсивный обход элементов DOCX */
    private function walkElements(array $elements): string
    {
        $out = '';

        foreach ($elements as $element) {
            if ($element instanceof Table) {
                foreach ($element->getRows() as $row) {
                    foreach ($row->getCells() as $cell) {
                        $out .= $this->walkElements($cell->getElements()) . ' ';
                    }
                    $out .= "\n";
                }
                continue;
            }

            if (method_exists($element, 'getElements')) {
                $out .= $this->walkElements($element->getElements()) . "\n";
                continue;
            }

            if (method_exists($element, 'getText')) {
                $text = $element->getText();
                if (is_string($text) && $text !== '') {
                    $out .= $text . ' ';
                }
            }

            if ($element instanceof TextBreak) {
                $out .= "\n";
            }
        }

        return $out;
    }

    /** Groq: краткое содержание + люди/компании */
       /** Groq: краткое содержание + люди/компании. При ошибке возвращает пустоту, не падает. */
    private function buildMeta(string $text): array
    {
        try {
            $result = $this->groq->json(
                'Ты — модуль индексации архива ЭДО DocSign. '
                . 'Проанализируй текст и верни СТРОГО JSON: '
                . '{"summary": "3-4 предложения: тип документа, стороны, суть", '
                . '"people": ["Фамилия Имя", ...] — все люди и организации из текста. '
                . 'Если людей нет — пустой массив.',
                "ТЕКСТ ДОКУМЕНТА:\n" . mb_substr($text, 0, 8000)  // ← уменьшил до 8000 символов, чтобы экономить токены
            );

            return [
                'summary' => is_string($result['summary'] ?? null) ? $result['summary'] : null,
                'people'  => is_array($result['people'] ?? null)
                    ? array_values(array_filter($result['people'], 'is_string'))
                    : [],
            ];
        } catch (\Throwable $e) {
            // При ошибке Groq (401, 429, сеть) — просто логируем и возвращаем пустоту
            // Индексация всё равно продолжится: текст и чанки сохранятся
            Log::warning('Indexer meta failed (Groq): ' . $e->getMessage());
            return ['summary' => null, 'people' => []];
        }
    }

    /** Нарезка текста на чанки ~1000 символов */
    private function chunk(string $text, int $max = 1000): array
    {
        $paragraphs = preg_split('/\R+/u', trim($text)) ?: [];
        $chunks = [];
        $current = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim(preg_replace('/\s+/u', ' ', $paragraph) ?? '');

            if ($paragraph === '') continue;

            while (mb_strlen($paragraph) > $max) {
                $chunks[] = mb_substr($paragraph, 0, $max);
                $paragraph = mb_substr($paragraph, $max);
            }

            if ($paragraph === '') continue;

            if ($current !== '' && mb_strlen($current) + 1 + mb_strlen($paragraph) > $max) {
                $chunks[] = $current;
                $current = '';
            }

            $current = $current === '' ? $paragraph : $current . ' ' . $paragraph;
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }
}