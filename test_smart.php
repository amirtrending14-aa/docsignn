<?php

$envFile = file_get_contents(__DIR__ . '/.env');
preg_match('/GROQ_API_KEY=(.+)/', $envFile, $matches);
$apiKey = trim($matches[1] ?? '');

echo "=== ПОЛНЫЙ ОТВЕТ GPT-OSS-120B ===\n";
echo "Ключ: " . substr($apiKey, 0, 10) . "...\n\n";

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'model' => 'openai/gpt-oss-120b',
        'messages' => [
            ['role' => 'system', 'content' => 'Ты помощник. Отвечай ТОЛЬКО валидным JSON без markdown.'],
            ['role' => 'user', 'content' => 'Сделай доверенность'],
        ],
        'max_tokens' => 1000,
        'temperature' => 0.3,
    ]),
    CURLOPT_TIMEOUT => 60,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP: $httpCode\n\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    $content = $data['choices'][0]['message']['content'] ?? '';
    echo "=== ОТВЕТ ИИ (как есть) ===\n";
    echo $content . "\n";
    echo "\n=== ПОПЫТКА РАСПАРСИТЬ ===\n";
    
    $clean = preg_replace('/<think>.*?<\/think>/is', '', $content);
    $clean = preg_replace('/^```(json)?/m', '', trim($clean));
    $clean = preg_replace('/```$/m', '', trim($clean));
    
    if (preg_match('/\{.*\}/s', $clean, $m)) {
        $parsed = json_decode($m[0], true);
        if (is_array($parsed)) {
            echo "✅ JSON ВАЛИДНЫЙ!\n";
            echo json_encode($parsed, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            echo "❌ JSON невалидный\n";
            echo "Ошибка: " . json_last_error_msg() . "\n";
            echo "Вытащенный текст: " . mb_substr($m[0], 0, 500);
        }
    } else {
        echo "❌ JSON не найден в ответе\n";
    }
} else {
    echo "ОШИБКА: $response\n";
}