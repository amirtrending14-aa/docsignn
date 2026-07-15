<?php
// create_company.php - исправленная версия
error_reporting(E_ALL);
ini_set('display_errors', 0);

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

$name = $argv[1] ?? '';
$admin_name = $argv[2] ?? '';
$email = $argv[3] ?? '';
$phone = $argv[4] ?? '';
$password = $argv[5] ?? '';
$telegram_id = $argv[6] ?? null;

if (!$name || !$admin_name || !$email || !$phone || !$password) {
    Log::error("Missing arguments");
    echo "ERROR:Missing arguments";
    exit(1);
}

try {
    DB::beginTransaction();

    Log::info("Starting company creation", [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'telegram_id' => $telegram_id
    ]);

    // Проверка уникальности email
    if (DB::table('users')->where('email', $email)->exists()) {
        throw new Exception("Email already exists in users table");
    }

    if (DB::table('companies')->where('email', $email)->exists()) {
        throw new Exception("Email already exists in companies table");
    }

    // Проверка уникальности телефона
    if (DB::table('users')->where('phone', $phone)->exists()) {
        throw new Exception("Phone already exists in users table");
    }

    if (DB::table('companies')->where('phone', $phone)->exists()) {
        throw new Exception("Phone already exists in companies table");
    }

    // Генерируем slug
    $slug = \Str::slug($name) . '-' . time();

    // Создаем компанию - БЕЗ owner_id
    $companyId = DB::table('companies')->insertGetId([
        'name' => $name,
        'slug' => $slug,
        'email' => $email,
        'phone' => $phone,
        'password_hash' => Hash::make($password),
        'owner_telegram_id' => $telegram_id,
        'language' => 'ru',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Log::info("Company created successfully", ['company_id' => $companyId]);

    // Создаем админа с company_id
    $userId = DB::table('users')->insertGetId([
        'name' => $admin_name,
        'email' => $email,
        'phone' => $phone,
        'password' => Hash::make($password),
        'company' => $name,
        'company_id' => $companyId,
        'is_admin' => true,
        'is_super_admin' => false,
        'email_verified_at' => now(),
        'role' => 'admin',
        'level' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Log::info("Admin user created successfully", [
        'user_id' => $userId,
        'company_id' => $companyId
    ]);

    DB::commit();

    Log::info("Company and admin creation completed successfully", [
        'company_id' => $companyId,
        'user_id' => $userId
    ]);

    // ВАЖНО: Возвращаем OK сразу
    echo "OK:{$companyId}";
    exit(0);

} catch (Exception $e) {
    DB::rollBack();
    Log::error("Company creation failed: " . $e->getMessage(), [
        'trace' => $e->getTraceAsString()
    ]);
    echo "ERROR:" . $e->getMessage();
    exit(1);
}