<?php
// send_verification_code.php - отправляет код подтверждения на email
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

$email = $argv[1] ?? '';

if (!$email) {
    echo "ERROR:Missing email";
    exit(1);
}

try {
    // Генерируем 6-значный код
    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    // Сохраняем в кэш на 10 минут
    Cache::put("email_verify:{$email}", $code, now()->addMinutes(10));

    // Отправляем email с кодом
    Mail::raw("Ваш код подтверждения DocSign: {$code}\n\nКод действителен 10 минут.\n\nЕсли вы не запрашивали код, проигнорируйте это письмо.", function ($message) use ($email) {
        $message->to($email)
            ->subject('🔐 Код подтверждения DocSign');
    });

    echo "OK:Code sent to {$email}";
} catch (Exception $e) {
    echo "ERROR:" . $e->getMessage();
}