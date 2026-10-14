<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        // Берем ТОЛЬКО существующих пользователей
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ В базе нет пользователей! Сначала создайте пользователей.');
            return;
        }

        // Создаем директорию для документов
        Storage::disk('public')->makeDirectory('documents');

        // Путь к файлу ТЗ (создадим фейковый PDF)
        $tzFilePath = $this->createTZFile();

        // Реалистичные типы документов (из твоего Blade-файла)
        $types = [
            'order' => 'Приказ',
            'decision' => 'Решение',
            'application' => 'Заявление',
            'act' => 'Акт',
            'protocol' => 'Протокол',
            'contract' => 'Договор',
            'agreement' => 'Соглашение',
            'license' => 'Лицензия',
            'power_of_attorney' => 'Доверенность',
            'invoice' => 'Счёт-фактура',
            'letter' => 'Письмо',
            'reference' => 'Справка',
            'report' => 'Отчёт',
            'memo' => 'Служебная записка',
            'bill' => 'Счёт на оплату',
            'instruction' => 'Инструкция',
            'guarantee_letter' => 'Гарантийное письмо'
        ];

        // Реалистичные заголовки и описания на основе ТЗ School Management System
        $documentTemplates = [
            [
                'title' => 'Техническое задание: Модуль управления учениками',
                'content' => 'Документ описывает функциональные требования к подсистеме учета контингента учащихся. Включает CRUD операции, импорт из Excel, фильтрацию по классам и параллелям. Требуется интеграция с модулем оплаты.',
                'type' => 'contract'
            ],
            [
                'title' => 'Спецификация API: Интеграция с платежной системой',
                'content' => 'REST API спецификация для приема платежей за обучение. Описаны эндпоинты /api/payments/create, /api/payments/verify. Поддержка валюты TJS.',
                'type' => 'agreement'
            ],
            [
                'title' => 'Протокол согласования: Электронный журнал оценок',
                'content' => 'Протокол совещания. Согласована структура БД для хранения оценок. Утверждены весовые коэффициенты для расчета среднего балла.',
                'type' => 'protocol'
            ],
            [
                'title' => 'ТЗ: Система контроля посещаемости с Face ID',
                'content' => 'Требования к модулю учета посещаемости с использованием биометрии. Интеграция с камерами Hikvision. Время распознавания < 2 сек.',
                'type' => 'contract'
            ],
            [
                'title' => 'Акт выполненных работ: Разработка модуля расписания',
                'content' => 'Акт сдачи-приемки работ по этапу 3. Реализован алгоритм автоматического составления расписания с учетом нагрузки учителей.',
                'type' => 'act'
            ],
            [
                'title' => 'ТЗ: Личный кабинет родителя (мобильное приложение)',
                'content' => 'Требования к мобильному приложению для iOS и Android. Просмотр оценок ребенка, расписания, домашнего задания.',
                'type' => 'contract'
            ],
            [
                'title' => 'Договор поставки: Серверное оборудование',
                'content' => 'Договор на поставку 2 серверов для production-среды. Конфигурация: 2x Intel Xeon Gold 6338, 256GB RAM.',
                'type' => 'contract'
            ],
            [
                'title' => 'Отчет о нагрузочном тестировании',
                'content' => 'Результаты тестирования производительности системы при пиковой нагрузке. Среднее время ответа API: 245ms.',
                'type' => 'report'
            ],
            [
                'title' => 'ТЗ: Модуль управления библиотекой',
                'content' => 'Функционал учета библиотечного фонда школы. Штрих-кодирование книг, выдача ученикам на учебный год.',
                'type' => 'contract'
            ],
            [
                'title' => 'Протокол тестирования: Безопасность данных',
                'content' => 'Проведен аудит безопасности согласно требованиям Закона РТ "О персональных данных". Внедрено шифрование паролей bcrypt.',
                'type' => 'protocol'
            ],
            [
                'title' => 'Счет на оплату: Лицензия Microsoft SQL Server',
                'content' => 'Счет на приобретение корпоративной лицензии MS SQL Server 2022 Enterprise Edition на 2 сервера.',
                'type' => 'bill'
            ],
            [
                'title' => 'ТЗ: Интеграция с государственной системой "Маориф"',
                'content' => 'Требования к обмену данными с министерской системой учета учащихся. Еженедельная синхронизация контингента.',
                'type' => 'contract'
            ],
            [
                'title' => 'Дополнительное соглашение: Модуль питания',
                'content' => 'Доп. соглашение к договору №SMS-2026-001. Добавление функции предзаказа блюд в школьной столовой.',
                'type' => 'agreement'
            ],
            [
                'title' => 'Отчет по проекту: Статус разработки',
                'content' => 'Еженедельный отчет о ходе работ. Завершено: модули учеников (100%), учителей (100%). В процессе: оплата (60%).',
                'type' => 'report'
            ],
            [
                'title' => 'ТЗ: Система уведомлений (SMS, Email, Push)',
                'content' => 'Требования к мультиканальной системе оповещения. SMS через провайдеров Megafon/Tcell/Babilon-M.',
                'type' => 'contract'
            ],
            [
                'title' => 'Приказ о назначении ответственных лиц',
                'content' => 'Приказ о назначении ответственных лиц за внедрение системы School Management System.',
                'type' => 'order'
            ],
            [
                'title' => 'Служебная записка: Запрос на закупку оборудования',
                'content' => 'Служебная записка с обоснованием необходимости закупки дополнительного серверного оборудования.',
                'type' => 'memo'
            ],
            [
                'title' => 'Инструкция для администраторов системы',
                'content' => 'Подробная инструкция по настройке и администрированию системы School Management System.',
                'type' => 'instruction'
            ],
            [
                'title' => 'Гарантийное письмо: Сроки внедрения',
                'content' => 'Гарантийное письмо о соблюдении сроков внедрения системы до 31 декабря 2026 года.',
                'type' => 'guarantee_letter'
            ],
            [
                'title' => 'Заявление на подключение к системе',
                'content' => 'Заявление от школы №15 г. Душанбе на подключение к системе School Management System.',
                'type' => 'application'
            ]
        ];

        $count = 0;
        
        // Генерируем 50 документов за сентябрь 2026
        for ($i = 0; $i < 50; $i++) {
            
            // Выбираем случайный шаблон
            $template = $documentTemplates[array_rand($documentTemplates)];
            
            // Распределяем по дням сентября (1-30) - ГОД 2026
            $day = rand(1, 30);
            $date = Carbon::create(2026, 9, $day, rand(8, 18), rand(0, 59), rand(0, 59));
            
            // Дедлайн в пределах сентября 2026
            $deadlineDays = rand(1, max(1, 30 - $day));
            $deadline = $date->copy()->addDays($deadlineDays);
            
            // Логика статусов
            $status = rand(1, 10) > 7 ? 'draft' : 'active';
            
            // Генерируем номер документа (год 2026)
            $typeKey = $template['type'];
            $typeLabel = $types[$typeKey] ?? 'Документ';
            $number = $typeLabel . '-SMS-2026-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Берем случайных пользователей из базы
            $creator = $users->random();
            $receiver = $users->random();

            Document::create([
                'title' => $template['title'],
                'content' => $template['content'],
                'type' => $typeKey,
                'status' => $status,
                'number' => $number,
                'created_by' => $creator->id,
                'receiver_id' => $receiver->id,
                'deadline' => $deadline,
                'file_path' => $tzFilePath,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
            
            $count++;
        }

        $this->command->info("✅ Создано {$count} реалистичных документов за СЕНТЯБРЬ 2026!");
        $this->command->info('📁 Все документы используют файл: ' . $tzFilePath);
        $this->command->info('👥 Отправители и получатели взяты из вашей базы данных');
    }

    /**
     * Создает фейковый PDF файл ТЗ
     */
    private function createTZFile(): string
    {
        $filename = 'tz_school_management_system.pdf';
        $path = 'documents/' . $filename;

        // Проверяем, существует ли файл
        if (Storage::disk('public')->exists($path)) {
            return $path;
        }

        // Создаем простой PDF (фейковый)
        $pdfContent = $this->generateFakePDF();
        Storage::disk('public')->put($path, $pdfContent);

        return $path;
    }

    /**
     * Генерирует простой PDF файл
     */
    private function generateFakePDF(): string
    {
        // Простой PDF header
        $pdf = "%PDF-1.4\n";
        $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";
        
        $content = "BT\n/F1 12 Tf\n50 750 Td\n(Техническое задание: School Management System) Tj\n";
        $content .= "0 -30 Td\n(Версия 2.1 от сентября 2026 года) Tj\n";
        $content .= "0 -40 Td\n(Содержание:) Tj\n";
        $content .= "0 -20 Td\n(1. Модуль управления учениками) Tj\n";
        $content .= "0 -20 Td\n(2. Электронный журнал оценок) Tj\n";
        $content .= "0 -20 Td\n(3. Система контроля посещаемости) Tj\n";
        $content .= "0 -20 Td\n(4. Модуль оплаты обучения) Tj\n";
        $content .= "0 -20 Td\n(5. Личный кабинет родителя) Tj\n";
        $content .= "0 -20 Td\n(6. Интеграция с государственными системами) Tj\n";
        $content .= "ET";
        
        $pdf .= "4 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream\nendobj\n";
        $pdf .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $pdf .= "xref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000266 00000 n \n";
        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n" . (strlen($pdf) - 10) . "\n%%EOF";

        return $pdf;
    }
}