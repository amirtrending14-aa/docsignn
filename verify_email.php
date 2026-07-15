<?php
// verify_email_code.php - проверяет код подтверждения
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Cache;

$email = $argv[1] ?? '';
$code = $argv[2] ?? '';

if (!$email || !$code) {
    echo "ERROR:Missing arguments";
    exit(1);
}

try {
    $cachedCode = Cache::get("email_verify:{$email}");

    if (!$cachedCode) {
        echo "ERROR:Code expired or not found";
        exit(1);
    }

    if ($cachedCode === $code) {
        // Код верный - удаляем из кэша и помечаем email как верифицированный
        Cache::forget("email_verify:{$email}");
        Cache::put("email_verified:{$email}", true, now()->addMinutes(30));
        echo "OK:Verified";
    } else {
        echo "ERROR:Invalid code";
    }
} catch (Exception $e) {
    echo "ERROR:" . $e->getMessage();
}