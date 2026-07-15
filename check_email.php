<?php
error_reporting(0);
ini_set('display_errors', 0);

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$email = $argv[1] ?? '';

if (!$email) {
    echo "ERROR:Missing email";
    exit(1);
}

try {
    $user_exists = DB::table('users')->where('email', $email)->exists();
    $company_exists = DB::table('companies')->where('email', $email)->exists();

    echo ($user_exists || $company_exists) ? "EXISTS:1" : "FREE:0";
} catch (Exception $e) {
    echo "ERROR:" . $e->getMessage();
}