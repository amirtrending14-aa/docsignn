<?php
error_reporting(0);
ini_set('display_errors', 0);

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    $total_companies = Schema::hasTable('companies') ? (int) DB::table('companies')->count() : 0;
    $total_users = Schema::hasTable('users') ? (int) DB::table('users')->count() : 0;

    $now = now();
    $today = $now->copy()->startOfDay();
    $week_ago = $now->copy()->subDays(7);
    $month_ago = $now->copy()->subMonth();

    $stats = [
        'total_companies' => $total_companies,
        'total_users' => $total_users,
        'companies_today' => Schema::hasTable('companies') ? (int) DB::table('companies')->where('created_at', '>=', $today)->count() : 0,
        'companies_week' => Schema::hasTable('companies') ? (int) DB::table('companies')->where('created_at', '>=', $week_ago)->count() : 0,
        'companies_month' => Schema::hasTable('companies') ? (int) DB::table('companies')->where('created_at', '>=', $month_ago)->count() : 0,
        'users_today' => Schema::hasTable('users') ? (int) DB::table('users')->where('created_at', '>=', $today)->count() : 0,
        'users_week' => Schema::hasTable('users') ? (int) DB::table('users')->where('created_at', '>=', $week_ago)->count() : 0,
        'users_month' => Schema::hasTable('users') ? (int) DB::table('users')->where('created_at', '>=', $month_ago)->count() : 0,
    ];

    echo "OK:" . json_encode($stats, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo "ERROR:" . $e->getMessage();
}