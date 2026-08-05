<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
   public function index(Request $request)
{
    $admin  = $request->user();
    $date   = $request->query('date', now()->toDateString());
    $filter = $request->query('filter', 'all');

    $companies = $admin->isSuperAdmin() ? \App\Models\Company::orderBy('name')->get() : collect();
    $settingsCompany = $admin->companyRelation
        ?? \App\Models\Company::find($request->query('settings_company'))
        ?? \App\Models\Company::first();

    // Все сотрудники компании
    $employees = \App\Models\User::where('needs_face_scan', true)
        ->with('companyRelation')
        ->when(! $admin->isSuperAdmin(), fn ($q) => $q->where('company_id', $admin->company_id))
        ->orderBy('name')
        ->get();

    $ids = $employees->pluck('id');

    $attendances = \App\Models\Attendance::whereDate('date', $date)
        ->whereIn('user_id', $ids)
        ->get()->keyBy('user_id');

    // Штрафы за месяц
    $monthStart = \Carbon\Carbon::parse($date)->startOfMonth();
    $monthEnd   = \Carbon\Carbon::parse($date)->endOfMonth();

    $monthFines = \App\Models\Attendance::whereIn('user_id', $ids)
        ->whereBetween('date', [$monthStart, $monthEnd])
        ->where('status', '!=', \App\Models\Attendance::STATUS_EXCUSED)
        ->get()->groupBy('user_id')
        ->map(fn ($r) => (float) $r->sum('fine'));

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
            $start   = now()->copy()
                ->setTimeFromTimeString($company->work_start_time ?? '08:30')
                ->addMinutes($company->late_tolerance_minutes ?? 0);

            if ($date < now()->toDateString() || ($date === now()->toDateString() && now()->greaterThan($start))) {
                $status = 'absent';
                $fine   = (float) ($company->absence_fine ?? 200);
            } else {
                $status = 'waiting';
                $fine   = 0;
            }
        }

        $counters[$status]++;
        $dayFineTotal += $fine;

        $monthFine = $monthFines[$emp->id] ?? 0;
        if ($virtual && $fine > 0) $monthFine += $fine;

        // Время опоздания
        $lateMinutes = 0;
        if ($status === 'late' && $time) {
            $arrived  = \Carbon\Carbon::parse($time);
            $deadline = \Carbon\Carbon::parse($company->work_start_time ?? '08:30')
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

    // Фильтр
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

    /** Админ сам пишет правило: каждые X минут = Y сом */
  public function saveSettings(Request $request)
{   
    $data = $request->validate([
        'company_id'          => 'required|exists:companies,id',
        'work_start_time'     => 'required|date_format:H:i',  // ← НОВОЕ
        'late_block_minutes'  => 'required|integer|min:1|max:720',
        'late_block_fine'     => 'required|numeric|min:0',
        'absence_fine'        => 'required|numeric|min:0',
    ]);

    $admin   = $request->user();
    $company = Company::findOrFail($data['company_id']);

    if (! $admin->isSuperAdmin() && (int) $company->id !== (int) $admin->company_id) {
        abort(403);
    }

    $company->update([
        'work_start_time'    => $data['work_start_time'],    // ← НОВОЕ
        'late_block_minutes' => $data['late_block_minutes'],
        'late_block_fine'    => $data['late_block_fine'],
        'absence_fine'       => $data['absence_fine'],
    ]);

    return back()->with('success', 'Правила сохранены: ' . $company->name);
}

    /** Кнопка «Я знал» — снять штраф (работает и для тех, у кого нет записи) */
    public function excuse(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date'    => 'required|date',
        ]);

        $admin  = $request->user();
        $target = User::findOrFail($data['user_id']);

        if (! $admin->isSuperAdmin() && $target->company_id !== $admin->company_id) {
            abort(403);
        }

        Attendance::updateOrCreate(
            ['user_id' => $target->id, 'date' => $data['date']],
            ['status' => Attendance::STATUS_EXCUSED, 'fine' => 0, 'check_in_time' => null]
        );

        return back()->with('success', 'Отмечено: админ знал — штраф снят');
    }
}