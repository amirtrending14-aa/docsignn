<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GroqClient
{
    public function json(string $system, string $user, float $temperature = 0.2): array
    {
        $cacheKey = 'groq_json_' . md5($system . '|' . $user);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        try {
            $content = $this->call($system, $user, $temperature, true);
            
            $decoded = json_decode($content, true);
            if (!is_array($decoded) && preg_match('/\{[\s\S]*\}/', $content, $m)) {
                $decoded = json_decode($m[0], true);
            }
            
            $result = is_array($decoded) ? $decoded : [];
            
            if ($result !== []) {
                Cache::put($cacheKey, $result, now()->addMinutes(10));
            }
            
            return $result;
        } catch (\Throwable $e) {
            Log::warning('Groq failed: ' . $e->getMessage());
            return [];
        }
    }

    private function call(string $system, string $user, float $temperature, bool $asJson): string
    {
        $apiKey  = config('services.ai.api_key');
        $baseUrl = config('services.ai.base_url', 'https://api.groq.com/openai/v1/chat/completions');
        $model   = config('services.ai.model', 'llama-3.3-70b-versatile');

        if (!$apiKey) throw new \RuntimeException('AI API key не задан');

        if ($asJson && !str_contains(mb_strtolower($system . ' ' . $user), 'json')) {
            $system .= ' Ответь строго в формате JSON.';
        }

        $payload = [
            'model'       => $model,
            'temperature' => $temperature,
            'messages'    => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $user],
            ],
            'max_tokens' => $asJson ? 800 : 1200,
        ];

        if ($asJson) $payload['response_format'] = ['type' => 'json_object'];

        $response = Http::withToken($apiKey)
            ->timeout(12)  // ← СТРОГО 12 секунд, не больше
            ->post($baseUrl, $payload);

        if ($response->failed()) {
            throw new \RuntimeException('Groq error: ' . $response->status());
        }

        return (string) ($response->json('choices.0.message.content') ?? '');
    }
}