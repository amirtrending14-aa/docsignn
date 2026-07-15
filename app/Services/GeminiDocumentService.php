<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class GeminiDocumentService
{
    protected $apiKey;
    protected $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));
    }

    public function generateDocument(string $prompt, string $format = 'pdf'): array
    {
        $systemPrompt = $this->getSystemPrompt($format);

        $response = $this->callGemini($systemPrompt, $prompt);

        $data = json_decode($response, true);

        if (!$data || !isset($data['document_data'])) {
            // Если ИИ вернул не JSON, попробовать извлечь данные
            $data = $this->parseResponse($response, $format);
        }

        if (isset($data['needs_questions']) && $data['needs_questions']) {
            $sessionId = Str::random(32);
            Cache::put("ai_session_{$sessionId}", [
                'prompt' => $prompt,
                'format' => $format,
                'questions' => $data['questions']
            ], 3600);

            return [
                'needs_questions' => true,
                'session_id' => $sessionId,
                'questions' => $data['questions']
            ];
        }

        // Сгенерировать файл
        $fileContent = $this->generateFile($data['document_data'], $format);

        return [
            'needs_questions' => false,
            'document_data' => $data['document_data'],
            'file_content' => $fileContent,
            'format' => $format
        ];
    }

    public function processAnswers(string $sessionId, array $answers): array
    {
        $session = Cache::get("ai_session_{$sessionId}");

        if (!$session) {
            throw new \Exception('Сессия не найдена или истекла');
        }

        $fullPrompt = $session['prompt'] . "\n\nОтветы на вопросы:\n";
        foreach ($answers as $key => $answer) {
            $fullPrompt .= "- {$answer}\n";
        }

        return $this->generateDocument($fullPrompt, $session['format']);
    }

    protected function getSystemPrompt(string $format): string
    {
        return "Ты помощник для создания документов в системе электронного документооборота. 
        Проанализируй запрос пользователя и верни JSON с полями:
        - document_data: объект с полями number, type, title, content, deadline, status
        - needs_questions: boolean (true если нужна дополнительная информация)
        - questions: массив вопросов если needs_questions=true
        
        Формат ответа ТОЛЬКО JSON без markdown. Пример:
        {
            \"document_data\": {
                \"number\": \"№ 001\",
                \"type\": \"Договор аренды\",
                \"title\": \"Договор аренды квартиры\",
                \"content\": \"Текст договора...\",
                \"deadline\": \"2024-12-31\",
                \"status\": \"draft\"
            },
            \"needs_questions\": false,
            \"questions\": []
        }";
    }

    protected function callGemini(string $systemPrompt, string $userPrompt): string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post("{$this->apiUrl}?key={$this->apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $systemPrompt . "\n\nЗапрос: " . $userPrompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2048
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('Ошибка API Gemini: ' . $response->body());
        }

        $data = $response->json();
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    protected function parseResponse(string $response, string $format): array
    {
        // Попытка извлечь JSON из ответа
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) {
                return $json;
            }
        }

        // Если не получилось, создать базовую структуру
        return [
            'document_data' => [
                'number' => '№ ' . rand(100, 999),
                'type' => 'Документ',
                'title' => 'Сгенерированный документ',
                'content' => $response,
                'deadline' => date('Y-m-d', strtotime('+30 days')),
                'status' => 'draft'
            ],
            'needs_questions' => false,
            'questions' => []
        ];
    }

    protected function generateFile(array $documentData, string $format): string
    {
        if ($format === 'word') {
            return $this->generateWord($documentData);
        } else {
            return $this->generatePdf($documentData);
        }
    }

    protected function generateWord(array $data): string
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        $section->addTitle($data['title'] ?? 'Документ', 1);
        $section->addText("Номер: " . ($data['number'] ?? 'Б/Н'));
        $section->addText("Тип: " . ($data['type'] ?? 'Документ'));
        $section->addTextBreak();
        $section->addText($data['content'] ?? '');

        $tempFile = tempnam(sys_get_temp_dir(), 'word');
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return $content;
    }

    protected function generatePdf(array $data): string
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.ai-document', ['data' => $data]);
        return $pdf->output();
    }
}