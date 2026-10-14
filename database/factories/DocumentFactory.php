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
        // Очищаем таблицу перед заполнением (опционально)
        // Document::truncate();

        // Получаем первого пользователя или создаем заглушку, если пользователей нет
        $user = User::first();
        if (!$user) {
            // Если пользователей нет, используем ID 1 (убедись, что он существует в БД)
            $userId = 1; 
        } else {
            $userId = $user->id;
        }

        // Названия документов на таджикском
        $titlesTj = [
            'Шартномаи таъминот',           // Договор поставки
            'Акти иҷрои корҳо',             // Акт выполненных работ
            'Ҳисоб барои пардохт',          // Счет на оплату
            'Спесификатсия',                // Спецификация
            'Созишномаи иловагӣ',           // Дополнительное соглашение
            'Протоколи ихтилофот',          // Протокол разногласий
            'Мактуб',                       // Письмо
            'Ариза',                        // Заявление
            'Фармоиш',                      // Заказ
            'Гузориш',                      // Отчет
        ];

        // Типы документов
        $types = ['incoming', 'outgoing']; // incoming - воридотӣ, outgoing - содиротӣ

        // Даты: Сентябрь 2023 (или текущий год, если нужно)
        // Используем 2023 год для примера, чтобы даты были фиксированными
        $startDate = Carbon::create(2023, 9, 1);
        $endDate = Carbon::create(2023, 9, 30);
        
        $currentDate = $startDate->copy();

        // Цикл по дням сентября
        while ($currentDate->lte($endDate)) {
            
            // Логика: В некоторые дни документов больше, в некоторые меньше.
            // "Через день" или случайно.
            // Шанс 80%, что в этот день будет хотя бы один документ.
            if (rand(1, 10) > 2) {
                
                // Количество документов в день (от 1 до 5)
                $docsPerDay = rand(1, 5);

                for ($i = 0; $i < $docsPerDay; $i++) {
                    
                    // Определяем статус на основе даты (логика реалистичности)
                    // 1-10 сен: Старые документы -> скорее всего Получены (5) или Отправлены (3)
                    // 11-20 сен: Смешанные
                    // 21-30 сен: Новые -> Черновики (6) или Новые (1)
                    
                    $day = $currentDate->day;
                    $status = 6; // Default Draft

                    if ($day <= 10) {
                        // Начало месяца: mostly Received (5)
                        $randStatus = rand(1, 10);
                        if ($randStatus <= 6) $status = 5;      // 60% Получены
                        elseif ($randStatus <= 9) $status = 3;  // 30% Отправлены
                        else $status = 1;                       // 10% Новые
                    } elseif ($day <= 20) {
                        // Середина месяца
                        $randStatus = rand(1, 4);
                        if ($randStatus == 1) $status = 6;      // Черновик
                        elseif ($randStatus == 2) $status = 3;  // Отправлен
                        elseif ($randStatus == 3) $status = 5;  // Получен
                        else $status = 1;                       // Новый
                    } else {
                        // Конец месяца (21-30): mostly Drafts (6) or New (1)
                        $randStatus = rand(1, 10);
                        if ($randStatus <= 5) $status = 6;      // 50% Черновики
                        elseif ($randStatus <= 8) $status = 1;  // 30% Новые
                        else $status = 3;                       // 20% Отправлены (срочные)
                    }

                    // Выбираем случайное название
                    $title = $titlesTj[array_rand($titlesTj)] . ' № ' . rand(100, 999);
                    
                    // Время создания в течение дня (случайное время)
                    $time = sprintf('%02d:%02d:%02d', rand(8, 18), rand(0, 59), rand(0, 59));
                    $fullDate = $currentDate->format('Y-m-d') . ' ' . $time;

                    Document::create([
                        'title' => $title,
                        'content' => fake()->paragraph(3),
                        'type' => $types[array_rand($types)],
                        'status' => $status,
                        'created_by' => $userId,
                        'receiver_id' => rand(1, max(1, User::count())), // Случайный получатель
                        'deadline' => $currentDate->copy()->addDays(rand(3, 10)),
                        'number' => 'Ҳ-' . $currentDate->format('ym') . '-' . rand(100, 999),
                        'created_at' => $fullDate,
                        'updated_at' => $fullDate,
                    ]);
                }
            }

            // Переходим к следующему дню
            // Чтобы было "через день" иногда, добавляем рандомный пропуск
            $skip = rand(0, 1); // 0 - следующий день, 1 - пропустить день
            $currentDate->addDays(1 + $skip);
        }

        $this->command->info('✅ Санадҳои моҳи сентябр бомуваффақият сохта шуданд!');
        $this->command->info(' Шумораи умумии санадҳо: ' . Document::count());
    }
}