<?php
error_reporting(0);
ini_set('display_errors', 0);

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$phone = $argv[1] ?? '';

if (!$phone) {
    echo "ERROR:Missing phone";
    exit(1);
}

try {
    $exists = DB::table('users')->where('phone', $phone)->exists();
    echo $exists ? "EXISTS:1" : "FREE:0";
} catch (Exception $e) {
    echo "ERROR:" . $e->getMessage();
}