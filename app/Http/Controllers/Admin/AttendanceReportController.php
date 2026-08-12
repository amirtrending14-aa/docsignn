<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $admin  = $request->user();
        $date   = $request->query('date', now()->toDateString());
        $filter = $request->query('filter', 'all');

        // Получаем компании для селекта (если супер админ)
        $companies = $admin->isSuperAdmin() ? Company::orderBy('name')->get() : collect();
        
        // Определяем компанию, для которой показываем настройки
        $settingsCompanyId = $request->query('settings_company');
        if ($admin->isSuperAdmin() && $settingsCompanyId) {
            $settingsCompany = Company::find($settingsCompanyId);
        } else {
            $settingsCompany = $admin->companyRelation ?? Company::first();
        }

        // Все сотрудники компании (только те, у кого включен face scan)
        $employees = User::where('needs_face_scan', true)
            ->with('companyRelation')
            ->when(!$admin->isSuperAdmin(), fn ($q) => $q->where('company_id', $admin->company_id))
            ->when($admin->isSuperAdmin() && $settingsCompany, fn ($q) => $q->where('company_id', $settingsCompany->id))
            ->orderBy('name')
            ->get();

        // ВАЖНО: преобразуем в массив для корректной работы whereIn
        $idsArray = $employees->pluck('id')->toArray();

        if (empty($idsArray)) {
            return view('adminn.reports', [
                'date'            => $date,
                'filter'          => $filter,
                'cards'           => collect(),
                'counters'        => ['on_time' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0, 'waiting' => 0],
                'dayFineTotal'    => 0,
                'companies'       => $companies,
                'settingsCompany' => $settingsCompany,
                'totalCount'      => 0,
            ]);
        }

        // Записи посещаемости за выбранный день
        $attendances = Attendance::whereDate('date', $date)
            ->whereIn('user_id', $idsArray)
            ->get()->keyBy('user_id');

        // ===== ИСПРАВЛЕННЫЙ ПОДСЧЁТ ШТРАФОВ ЗА МЕСЯЦ =====
        // ===== ИСПРАВЛЕННЫЙ ПОДСЧЁТ ШТРАФОВ ЗА МЕСЯЦ (RAW SQL) =====
$monthStart = Carbon::parse($date)->startOfMonth()->toDateString();
$monthEnd   = Carbon::parse($date)->endOfMonth()->toDateString();

// Используем RAW SQL с DATE() функцией MySQL для надёжного извлечения даты
$monthFines = Attendance::whereIn('user_id', $idsArray)
    ->whereRaw("DATE(date) >= ?", [$monthStart])
    ->whereRaw("DATE(date) <= ?", [$monthEnd])
    ->where('status', '!=', Attendance::STATUS_EXCUSED)
    ->selectRaw('user_id, SUM(fine) as total_fine')
    ->groupBy('user_id')
    ->pluck('total_fine', 'user_id');

// ОТЛАДКА: логируем результат (можно удалить после проверки)
\Log::info('Month fines debug', [
    'date' => $date,
    'month_range' => $monthStart . ' to ' . $monthEnd,
    'employee_count' => count($idsArray),
    'fines_found' => $monthFines->toArray(),
]);

        $cards = collect();
        $counters = ['on_time' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0, 'waiting' => 0];
        $dayFineTotal = 0;

        foreach ($employees as $emp) {
            $att     = $attendances[$emp->id] ?? null;
            $company = $emp->companyRelation;

            if ($att) {
                $status  = $att->status;
                $fine    = (float) $att->fine;
                $time    = $att->check_in_time;
                $virtual = false;
            } else {
                $time    = null;
                $virtual = true;
                // Логика определения отсутствия, если время уже прошло
                $start   = Carbon::parse($date . ' ' . ($company->work_start_time ?? '08:30'))
                    ->addMinutes($company->late_tolerance_minutes ?? 0);

                // Сравниваем с датой просмотра, а не с "сейчас"
                // чтобы при просмотре прошлых дат корректно показывались неявки
                if ($date < now()->toDateString() || ($date === now()->toDateString() && now()->greaterThan($start))) {
                    $status = 'absent';
                    $fine   = (float) ($company->absence_fine ?? 200);
                } else {
                    $status = 'waiting';
                    $fine   = 0;
                }
            }

            if (isset($counters[$status])) {
                $counters[$status]++;
            }
            
            $dayFineTotal += $fine;

            // Берем сумму всех штрафов за месяц из базы
            $monthFine = (float) ($monthFines[$emp->id] ?? 0);
            
            // Если сегодня виртуальная запись (сотрудник не пришел и день уже прошел),
            // добавляем этот штраф к месячному (потому что его еще нет в БД)
            if ($virtual && $fine > 0) {
                $monthFine += $fine;
            }

            // Вычисляем минуты опоздания
            $lateMinutes = 0;
            if ($status === 'late' && $time) {
                $arrived  = Carbon::parse($date . ' ' . $time);
                $deadline = Carbon::parse($date . ' ' . ($company->work_start_time ?? '08:30'))
                    ->addMinutes($company->late_tolerance_minutes ?? 0);
                $lateMinutes = max(0, (int) $deadline->diffInMinutes($arrived));
            }

            $cards->push((object) [
                'user'          => $emp,
                'status'        => $status,
                'time'          => $time,
                'fine'          => $fine,
                'salary'        => (float) ($emp->salary ?? 0),
                'month_fine'    => $monthFine,
                'payout'        => (float) ($emp->salary ?? 0) - $monthFine,
                'late_minutes'  => $lateMinutes,
            ]);
        }

        // Применяем фильтр (Все, Вовремя, Опоздали и т.д.)
        if ($filter !== 'all') {
            $cards = $cards->filter(fn ($c) => $c->status === $filter)->values();
        }

        return view('adminn.reports', [
            'date'            => $date,
            'filter'          => $filter,
            'cards'           => $cards,
            'counters'        => $counters,
            'dayFineTotal'    => $dayFineTotal,
            'companies'       => $companies,
            'settingsCompany' => $settingsCompany,
            'totalCount'      => $employees->count(),
        ]);
    }

    /**
     * СОХРАНЕНИЕ НАСТРОЕК (Исправленная валидация времени)
     */
    public function saveSettings(Request $request)
    {   
        // 1. Сначала просто проверяем, что поля вообще пришли
        $request->validate([
            'company_id'          => 'required|exists:companies,id',
            'work_start_time'     => 'required|string',
            'late_block_minutes'  => 'required|integer|min:1|max:1440',
            'late_block_fine'     => 'required|numeric|min:0',
            'absence_fine'        => 'required|numeric|min:0',
        ], [
            'company_id.required' => 'Не выбрана компания.',
            'company_id.exists' => 'Выбранная компания не найдена.',
            'work_start_time.required' => 'Укажите время начала работы.',
            'late_block_minutes.required' => 'Укажите интервал минут для штрафа.',
            'late_block_minutes.integer' => 'Интервал должен быть целым числом.',
            'late_block_fine.required' => 'Укажите сумму штрафа за опоздание.',
            'absence_fine.required' => 'Укажите сумму штрафа за неявку.',
        ]);

        // 2. Приводим время к нужному формату H:i
        try {
            $workStartTime = Carbon::parse($request->input('work_start_time'))->format('H:i');
        } catch (\Exception $e) {
            return back()->withErrors(['work_start_time' => 'Неверный формат времени начала работы.'])->withInput();
        }

        $admin   = $request->user();
        $company = Company::findOrFail($request->input('company_id'));

        // Проверка прав
        if (!$admin->isSuperAdmin() && (int) $company->id !== (int) $admin->company_id) {
            return back()->withErrors(['error' => 'У вас нет прав для изменения настроек этой компании.'])->withInput();
        }

        // 3. Сохраняем в базу
        $company->update([
            'work_start_time'    => $workStartTime,
            'late_block_minutes' => $request->input('late_block_minutes'),
            'late_block_fine'    => $request->input('late_block_fine'),
            'absence_fine'       => $request->input('absence_fine'),
        ]);

        return back()->with('success', 'Настройки успешно сохранены для компании: ' . $company->name);
    }   

    /**
     * Кнопка «Я знал» — снять штраф
     */
    public function excuse(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date'    => 'required|date',
        ]);

        $admin  = $request->user();
        $target = User::findOrFail($data['user_id']);

        if (!$admin->isSuperAdmin() && $target->company_id !== $admin->company_id) {
            return back()->withErrors(['error' => 'Нет прав для этого сотрудника.']);
        }

        Attendance::updateOrCreate(
            ['user_id' => $target->id, 'date' => $data['date']],
            ['status' => Attendance::STATUS_EXCUSED, 'fine' => 0, 'check_in_time' => null]
        );

        return back()->with('success', 'Статус обновлен: штраф снят (админ был в курсе).');
    }
        /**
     * ПЕРСОНАЛЬНЫЙ ОТЧЁТ СОТРУДНИКА — календарь за месяц
     */
    public function userReport(Request $request, $userId)
    {
        $admin = $request->user();
        $user  = User::with('companyRelation')->findOrFail($userId);

        // Проверка прав
        if (!$admin->isSuperAdmin() && (int) $user->company_id !== (int) $admin->company_id) {
            abort(403);
        }

        // Месяц из URL (?month=2026-08)
        $monthParam = $request->query('month', now()->format('Y-m'));
        try {
            $month = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
        } catch (\Exception $e) {
            $month = now()->startOfMonth();
            $monthParam = $month->format('Y-m');
        }

        $monthStart = $month->copy()->startOfMonth()->toDateString();
        $monthEnd   = $month->copy()->endOfMonth()->toDateString();
        $company    = $user->companyRelation;

        // Все реальные записи за месяц
        $records = Attendance::where('user_id', $user->id)
            ->whereRaw("DATE(date) >= ?", [$monthStart])
            ->whereRaw("DATE(date) <= ?", [$monthEnd])
            ->get()
            ->keyBy(fn ($r) => Carbon::parse($r->date)->toDateString());

        // Собираем дни календаря
        $days     = [];
        $counters = ['on_time' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0, 'waiting' => 0];
        $totalFine = 0;
        $cursor = $month->copy();
        $end    = $month->copy()->endOfMonth();
        $today  = now()->toDateString();

        while ($cursor <= $end) {
            $dateStr  = $cursor->toDateString();
            $record   = $records->get($dateStr);
            $isToday  = $dateStr === $today;
            $isFuture = $dateStr > $today;

            if ($record) {
                $status = $record->status;
                $fine   = (float) $record->fine;
                $time   = $record->check_in_time;
            } elseif ($isFuture) {
                $status = 'future';
                $fine   = 0;
                $time   = null;
            } else {
                // Нет записи: день прошёл → неявка, сегодня → ожидание/неявка
                $start = Carbon::parse($dateStr . ' ' . ($company->work_start_time ?? '08:30'))
                    ->addMinutes($company->late_tolerance_minutes ?? 0);

                if ($isToday && now()->lessThanOrEqualTo($start)) {
                    $status = 'waiting';
                    $fine   = 0;
                } else {
                    $status = 'absent';
                    $fine   = (float) ($company->absence_fine ?? 200);
                }
                $time = null;
            }

            if ($status !== 'future') {
                if (isset($counters[$status])) $counters[$status]++;
                $totalFine += $fine;
            }

            // Минуты опоздания
            $lateMinutes = 0;
            if ($status === 'late' && $time) {
                $arrived  = Carbon::parse($dateStr . ' ' . $time);
                $deadline = Carbon::parse($dateStr . ' ' . ($company->work_start_time ?? '08:30'))
                    ->addMinutes($company->late_tolerance_minutes ?? 0);
                $lateMinutes = max(0, (int) $deadline->diffInMinutes($arrived));
            }

            $days[] = [
                'date'         => $dateStr,
                'day'          => $cursor->day,
                'status'       => $status,
                'fine'         => $fine,
                'time'         => $time,
                'late_minutes' => $lateMinutes,
                'is_today'     => $isToday,
            ];

            $cursor->addDay();
        }

        // Пустые клетки до 1-го числа (неделя с понедельника)
        $leadingBlanks = $month->copy()->startOfMonth()->dayOfWeekIso - 1;

        $salary = (float) ($user->salary ?? 0);
        $payout = $salary - $totalFine;

        return view('adminn.user-report', [
            'user'        => $user,
            'company'     => $company,
            'days'        => $days,
            'leadingBlanks' => $leadingBlanks,
            'counters'    => $counters,
            'totalFine'   => $totalFine,
            'salary'      => $salary,
            'payout'      => $payout,
            'monthParam'  => $monthParam,
            'prevMonth'   => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth'   => $month->copy()->addMonth()->format('Y-m'),
        ]);
    }
}