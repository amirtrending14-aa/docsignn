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

        // Keep each document in its creation month, including soft-deleted records.
        $statusData     = $this->getStatusData($startDate, $endDate);
        $prevStatusData = $this->getStatusData($prevStartDate, $prevEndDate);

        $trends = [];
        foreach ($statusData as $key => $value) {
            $prev = $prevStatusData[$key] ?? 0;
            $trends[$key] = [
                'diff'    => $value - $prev,
                'percent' => $prev > 0 ? round((($value - $prev) / $prev) * 100, 1) : ($value > 0 ? 100 : 0),
            ];
        }

        // === Дневная активность по созданию и отправке документов ===
        $rawDocs = DocumentLog::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('action', ['CREATED', 'RECEIVED', 'создание', 'отправка'])
            ->selectRaw('DATE(created_at) as date, COUNT(DISTINCT document_id) as count')
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

    private function getStatusData(Carbon $startDate, Carbon $endDate): array
    {
        $monthlyDocuments = Document::withTrashed()
            ->visibleToAuth()
            ->whereBetween('created_at', [$startDate, $endDate]);
        $statusCounts = (clone $monthlyDocuments)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $activeSigned = (clone $monthlyDocuments)
            ->where('status', Document::STATUS_ACTIVE)
            ->whereHas('signatures', fn ($query) => $query
                ->whereNotNull('signature')
                ->where('signature', '!=', ''))
            ->count();
        $processingSigned = (clone $monthlyDocuments)
            ->where('status', Document::STATUS_PROCESSING)
            ->whereHas('signatures', fn ($query) => $query
                ->whereNotNull('signature')
                ->where('signature', '!=', ''))
            ->count();
        $activeCount = (int) $statusCounts->get(Document::STATUS_ACTIVE, 0);
        $processingCount = (int) $statusCounts->get(Document::STATUS_PROCESSING, 0);
        $processing = max(0, $activeCount - $activeSigned) + $processingSigned;
        $generating = max(0, $processingCount - $processingSigned);
        $archive = (int) $statusCounts->get(Document::STATUS_COMPLETED, 0)
            + (int) $statusCounts->get(Document::STATUS_APPROVED, 0)
            + $activeSigned;
        $failed = (int) $statusCounts->get(Document::STATUS_FAILED, 0);
        $total = (int) $statusCounts->sum();
        $other = max(0, $total - $processing - $generating
            - (int) $statusCounts->get(Document::STATUS_DRAFT, 0)
            - (int) $statusCounts->get(Document::STATUS_REJECTED, 0)
            - $archive - $failed);

        return [
            'total' => $total,
            'processing' => $processing,
            'generating' => $generating,
            'draft' => (int) $statusCounts->get(Document::STATUS_DRAFT, 0),
            'rejected' => (int) $statusCounts->get(Document::STATUS_REJECTED, 0),
            'archive' => $archive,
            'failed' => $failed,
            'other' => $other,
        ];
    }
}