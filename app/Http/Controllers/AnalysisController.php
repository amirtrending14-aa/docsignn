<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        $month = (int)$request->get('month', Carbon::now()->month);
        $year = (int)$request->get('year', Carbon::now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Базовый запрос для фильтрации по датам (упрощает код)
        $query = Document::whereBetween('created_at', [$startDate, $endDate]);

        // Статистика статусов
        // Используем явное указание на связь, чтобы избежать проблем с 'active'
        $statusData = [
            'signed' => (clone $query)
                ->whereHas('signatures', function ($q) {
                    $q->whereNotNull('signature');
                })->count(),

            'pending' => (clone $query)
                ->where('status', 'active')
                ->whereDoesntHave('signatures', function ($q) {
                    $q->whereNotNull('signature');
                })->count(),

            'incoming' => (clone $query)->whereNotNull('receiver_id')->count(),
            'outgoing' => (clone $query)->whereNotNull('created_by')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
        ];

        // Активность документов по дням
        $rawDocs = (clone $query)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->pluck('count', 'date');

        // Активность пользователей
        $registrations = User::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')->pluck('count', 'date');

        $deletions = User::onlyTrashed()
            ->select(DB::raw('DATE(deleted_at) as date'), DB::raw('COUNT(*) as count'))
            ->whereBetween('deleted_at', [$startDate, $endDate])
            ->groupBy('date')->pluck('count', 'date');

        // Подготовка данных для графиков
        $dailyActivity = collect();
        $userActivity = collect();
        $daysInMonth = $startDate->daysInMonth;

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateInstance = Carbon::createFromDate($year, $month, $i);
            $currentDate = $dateInstance->format('Y-m-d');
            $displayDate = $dateInstance->format('d.m');

            $dailyActivity->push([
                'date' => $displayDate,
                'count' => $rawDocs->get($currentDate, 0),
            ]);

            $userActivity->push([
                'date' => $displayDate,
                'reg' => $registrations->get($currentDate, 0),
                'del' => $deletions->get($currentDate, 0),
            ]);
        }

        $totalDocuments = Document::count();
        $totalUsers = User::count();
        $newThisMonth = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $deletedThisMonth = User::onlyTrashed()->whereBetween('deleted_at', [$startDate, $endDate])->count();

        $churnRate = $totalUsers > 0 ? round(($deletedThisMonth / $totalUsers) * 100, 1) : 0;

        $viewName = $request->is('analysis*') ? 'analysis.index' : 'layouts.site';

        return view($viewName, compact(
            'dailyActivity', 'statusData', 'userActivity', 'totalUsers',
            'totalDocuments', 'newThisMonth', 'deletedThisMonth', 'churnRate', 'month', 'year'
        ));
    }
}
