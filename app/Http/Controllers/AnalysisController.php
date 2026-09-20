<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\DocumentLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        $userId = $user->id;

        $month = max(1, min(12, (int) $request->get('month', Carbon::now()->month)));
        $year  = (int) $request->get('year', Carbon::now()->year);
        if ($year < 2020 || $year > 2050) $year = Carbon::now()->year;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        $prevStartDate = $startDate->copy()->subMonth()->startOfMonth();
        $prevEndDate   = $prevStartDate->copy()->endOfMonth();

        // === Считаем по ЛОГАМ ===
        $statusData     = $this->getStatusData($userId, $startDate, $endDate);
        $prevStatusData = $this->getStatusData($userId, $prevStartDate, $prevEndDate);

        $trends = [];
        foreach ($statusData as $key => $value) {
            $prev = $prevStatusData[$key] ?? 0;
            $trends[$key] = [
                'diff'    => $value - $prev,
                'percent' => $prev > 0 ? round((($value - $prev) / $prev) * 100, 1) : ($value > 0 ? 100 : 0),
            ];
        }

        // === Дневная активность (по логам CREATED/RECEIVED) ===
        $rawDocs = DocumentLog::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('action', ['CREATED', 'RECEIVED'])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $registrations = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')->pluck('count', 'date');

        $deletions = User::onlyTrashed()
            ->selectRaw('DATE(deleted_at) as date, COUNT(*) as count')
            ->whereBetween('deleted_at', [$startDate, $endDate])
            ->groupBy('date')->pluck('count', 'date');

        $dailyActivity = collect();
        $userActivity  = collect();
        $daysInMonth   = $startDate->daysInMonth;

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateInstance = Carbon::createFromDate($year, $month, $i);
            $currentDate  = $dateInstance->format('Y-m-d');
            $displayDate  = $dateInstance->format('d.m');

            $dailyActivity->push([
                'date'  => $displayDate,
                'count' => $rawDocs->get($currentDate, 0),
            ]);
            $userActivity->push([
                'date' => $displayDate,
                'reg'  => $registrations->get($currentDate, 0),
                'del'  => $deletions->get($currentDate, 0),
            ]);
        }

        $totalDocuments   = Document::count();
        $totalUsers       = User::count();
        $newThisMonth     = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $deletedThisMonth = User::onlyTrashed()->whereBetween('deleted_at', [$startDate, $endDate])->count();
        $churnRate        = $totalUsers > 0 ? round(($deletedThisMonth / $totalUsers) * 100, 1) : 0;

        $viewName = $request->is('analysis*') ? 'analysis.index' : 'layouts.site';

        return view($viewName, compact(
            'dailyActivity', 'statusData', 'prevStatusData', 'trends',
            'userActivity', 'totalUsers', 'totalDocuments',
            'newThisMonth', 'deletedThisMonth', 'churnRate',
            'month', 'year', 'startDate', 'endDate'
        ));
    }

    /**
     * Считает события по логам
     */
    private function getStatusData(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $logs = DocumentLog::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            // Входящие = сколько получил (RECEIVED)
            'incoming'   => $logs->where('action', 'RECEIVED')->count(),

            // Исходящие = сколько создал (CREATED)
            'outgoing'   => $logs->where('action', 'CREATED')->count(),

            // На подписании = документы в активном статусе (по живым документам)
            'processing' => Document::where(function ($q) use ($userId) {
                                $q->where('created_by', $userId)
                                  ->orWhere('receiver_id', $userId);
                            })
                            ->whereIn('status', [Document::STATUS_ACTIVE, Document::STATUS_PROCESSING])
                            ->count(),

            // Черновики = текущее состояние (живые документы)
            'draft'      => Document::where('created_by', $userId)
                            ->where('status', Document::STATUS_DRAFT)
                            ->count(),

            // Отклонённые = события REJECTED
            'rejected'   => $logs->where('action', 'REJECTED')->count(),

            // Архив (подписанные) = события SIGNED
            'archive'    => $logs->where('action', 'SIGNED')->count(),
        ];
    }
}