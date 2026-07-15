<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    public static function log(string $action, string $description, int $userId): void
    {
        Log::info("DocSign Activity: {$action}", [
            'user_id'     => $userId,
            'description' => $description,
            'timestamp'   => now()->toDateTimeString(),
        ]);
    }
}