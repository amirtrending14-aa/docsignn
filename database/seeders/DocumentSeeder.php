<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::find(1);

        $statuses = ['active', 'completed', 'draft', 'pending'];
        $types = ['incoming', 'outgoing'];
        $titles = [
            'Договор поставки',
            'Акт выполненных работ',
            'Счет на оплату',
            'Спецификация',
            'Дополнительное соглашение',
            'Протокол разногласий',
            'Письмо',
            'Заявление'
        ];

        // Генерируем документы за последние 12 месяцев
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->copy()->subMonths($i);

            // Случайное количество документов (от 5 до 50)
            $count = rand(5, 50);

            for ($j = 0; $j < $count; $j++) {
                $date = $month->copy()->addDays(rand(0, 27));

                Document::create([
                    'title' => $titles[array_rand($titles)] . ' #' . rand(100, 999),
                    'number' => 'Д-' . rand(1000, 9999),
                    'status' => $statuses[array_rand($statuses)],
                    'type' => $types[array_rand($types)],
                    'created_by' => $user->id,
                    'receiver_id' => $user->id,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }

        $this->command->info('Создано ' . Document::count() . ' документов');
    }
}