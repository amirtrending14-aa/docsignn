<?php
// create_admin.php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$name = $argv[1] ?? '';
$email = $argv[2] ?? '';
$password = $argv[3] ?? '';
$company = $argv[4] ?? '';

if (!$name || !$email || !$password || !$company) {
    echo "ERROR:Missing arguments";
    exit(1);
}

try {
    // ДВОЙНАЯ ПРОВЕРКА — на случай гонки данных
    if (User::where('email', $email)->exists()) {
        echo "ERROR:Email already exists";
        exit(1);
    }

    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make($password),
        'company' => $company,
        'is_admin' => true,
        'is_super_admin' => false,
        'email_verified_at' => now(),
        'role' => 'admin',
        'level' => 1,
    ]);

    echo "OK:" . $user->id;
} catch (Exception $e) {
    // Ловим дубликаты отдельно
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        echo "ERROR:Email already exists (duplicate)";
    } else {
        echo "ERROR:" . $e->getMessage();
    }
}