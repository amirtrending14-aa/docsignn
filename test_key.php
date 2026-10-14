<?php

$envFile = file_get_contents(__DIR__ . '/.env');
preg_match('/GROQ_API_KEY=(.+)/', $envFile, $matches);
$apiKey = trim($matches[1] ?? '');

echo "=== ТЕСТ НОВЫХ МОДЕЛЕЙ GROQ (август 2026) ===\n\n";

$models = [
    'openai/gpt-oss-120b',
    'qwen/qwen3.6-27b',
    'qwen/qwen3-32b',
    'meta-llama/llama-4-scout-17b-16e-instruct',
    'meta-llama/llama-4-maverick-17b-128e-instruct',
    'moonshotai/kimi-k2-instruct',
    'deepseek-r1-distill-llama-70b',
];

$working = [];

foreach ($models as $model) {
    echo "🔄 $model ... ";
    
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => 'Скажи слово: салом']],
            'max_tokens' => 20,
        ]),
        CURLOPT_TIMEOUT => 20,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $reply = $data['choices'][0]['message']['content'] ?? '';
        echo "✅ OK (ответ: " . trim($reply) . ")\n";
        $working[] = $model;
    } else {
        $data = json_decode($response, true);
        $err = $data['error']['code'] ?? 'unknown';
        echo "❌ ($err)\n";
    }
}

echo "\n" . str_repeat('=', 50) . "\n";

if (empty($working)) {
    echo "❌ Ни одна новая модель не работает. Проверь права на https://console.groq.com/models\n";
} else {
    echo "✅ РАБОЧИЕ МОДЕЛИ:\n";
    foreach ($working as $m) echo "   • $m\n";
    echo "\n🎯 РЕКОМЕНДУЮ: " . $working[0] . "\n";
}