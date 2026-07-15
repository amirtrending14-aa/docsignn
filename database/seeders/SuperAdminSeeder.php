<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        // 🔒 Создаём супер-админа БЕЗ полей 2FA
        User::updateOrCreate(
            ['email' => 'amirtrending14@gmail.com'],
            [
                'name' => 'Amir SuperAdmin',
                'password' => Hash::make('1404trend'),
                'role' => 'super_admin',
                'is_admin' => true,
                'is_super_admin' => true,
                'email_verified_at' => now(),
                'level' => 100,
                'company_id' => null,
                'company' => null,
                'remember_token' => Str::random(60),
            ]
        );

        $this->command->info('');
        $this->command->info('╔════════════════════════════════════════════════╗');
        $this->command->info('║   ✅ СУПЕР-АДМИН СОЗДАН УСПЕШНО!              ║');
        $this->command->info('╠════════════════════════════════════════════════╣');
        $this->command->info('║ 📧 Email:    amirtrending14@gmail.com         ║');
        $this->command->info('║ 🔑 Пароль:   1404trend                        ║');
        $this->command->info('║ 🆔 Уровень:  100 (максимальный)               ║');
        $this->command->info('╚════════════════════════════════════════════════╝');
        $this->command->info('');
    }
}