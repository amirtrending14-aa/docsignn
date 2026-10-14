<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\DocumentLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        $userId = $user->id;

        // === 1. БАЗОВАЯ СТАТИСТИКА ДАШБОРДА ===
        $onlineUsers = User::where('id', '!=', $userId)
            ->where('last_seen_at', '>=', now()->subMinutes(5))
            ->orderBy('last_seen_at', 'desc')
            ->limit(8)
            ->get();

        $userDocs = function($q) use ($user) {
            $q->where('created_by', $user->id)->orWhere('receiver_id', $user->id);
        };

        $stats = [
            'total'          => Document::where($userDocs)->count(),
            'active'         => Document::where($userDocs)->where('status', 'active')->count(),
            'draft'          => Document::where($userDocs)->where('status', 'draft')->count(),
            'pending'        => Document::where('receiver_id', $user->id)->where('status', 'active')->count(),
            'waiting'        => Document::where($userDocs)->where('status', 'active')->count(),
            'signed'         => Document::where($userDocs)->where('status', 'completed')->count(),
            'incoming'       => Document::where('receiver_id', $user->id)->whereIn('status', ['active', 'pending'])->count(),
            'users'          => User::count(),
            'new_users'      => User::whereMonth('created_at', now()->month)->count(),
            'pending_change' => 3,
        ];

        // Sparklines (последние 10 дней)
        $sparklineData = [];
        for ($i = 9; $i >= 0; $i--) {
            $dateStart = now()->copy()->subDays($i)->startOfDay();
            $dateEnd   = now()->copy()->subDays($i)->endOfDay();

            $sparklineData['total'][] = Document::where($userDocs)->whereBetween('created_at', [$dateStart, $dateEnd])->count();
            $sparklineData['waiting'][] = Document::where($userDocs)->where('status', 'active')->whereBetween('updated_at', [$dateStart, $dateEnd])->count();
            $sparklineData['signed'][] = Document::where($userDocs)->where('status', 'completed')->whereBetween('updated_at', [$dateStart, $dateEnd])->count();
        }
        $sparklineData['users'] = array_fill(0, 10, round(User::count() / 10));

        // Рост документов
        $currentMonthStart = now()->copy()->startOfMonth();
        $lastMonthDocs = Document::where($userDocs)->where('created_at', '>=', $currentMonthStart)->count();
        $previousMonthStart = now()->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->copy()->startOfMonth();
        $previousMonthDocs = Document::where($userDocs)->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])->count();
        $docsGrowth = $previousMonthDocs > 0 ? round((($lastMonthDocs - $previousMonthDocs) / $previousMonthDocs) * 100) : ($lastMonthDocs > 0 ? 100 : 0);

        // Последние документы
        $documents = Document::with(['createdBy', 'receiver'])->where($userDocs)->latest()->take(5)->get();

        // === 2. АНАЛИТИКА (ГРАФИКИ) ===
        $month = max(1, min(12, (int) $request->get('month', Carbon::now()->month)));
        $year  = (int) $request->get('year', Carbon::now()->year);
        if ($year < 2020 || $year > 2050) $year = Carbon::now()->year;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        $statusData = $this->getStatusData($startDate, $endDate, $userDocs);
        
        $summaryCards = [
            'total' => max(0, (int) ($statusData['total'] ?? 0)),
            'processing' => max(0, (int) ($statusData['processing'] ?? 0)),
            'archive' => max(0, (int) ($statusData['archive'] ?? 0)),
            'rejected' => max(0, (int) ($statusData['rejected'] ?? 0)),
        ];

        // Данные для графиков по дням
        $rawDocs = DocumentLog::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('action', ['CREATED', 'RECEIVED', 'создание', 'отправка'])
            ->selectRaw('DATE(created_at) as date, COUNT(DISTINCT document_id) as count')
            ->groupBy('date')->pluck('count', 'date');

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

            $dailyActivity->push(['date' => $displayDate, 'count' => $rawDocs->get($currentDate, 0)]);
            $userActivity->push(['date' => $displayDate, 'reg' => $registrations->get($currentDate, 0), 'del' => $deletions->get($currentDate, 0)]);
        }

        $prevMonth = $startDate->copy()->subMonth()->month;
        $prevYear = $startDate->copy()->subMonth()->year;
        $nextMonth = $startDate->copy()->addMonth()->month;
        $nextYear = $startDate->copy()->addMonth()->year;
// === ПОСЛЕДНИЕ 4 УВЕДОМЛЕНИЯ ДЛЯ ПРАВОЙ КОЛОНКИ ===
$recentNotifications = $user->notifications()
    ->latest()
    ->take(4)   // 👈 последние 4 уведомления
    ->get();
        return view('dashboard', compact(
            'stats', 'documents', 'onlineUsers', 'sparklineData', 'docsGrowth',
            'month', 'year', 'startDate', 'dailyActivity', 'userActivity', 
            'statusData', 'summaryCards', 'prevMonth','recentNotifications' , 'prevYear', 'nextMonth', 'nextYear'
        ));
    }

    private function getStatusData(Carbon $startDate, Carbon $endDate, $userDocs): array
    {
        $monthlyDocuments = Document::withTrashed()
            ->where($userDocs)
            ->whereBetween('created_at', [$startDate, $endDate]);
            
        $statusCounts = (clone $monthlyDocuments)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $activeSigned = (clone $monthlyDocuments)
            ->where('status', 'active')
            ->whereHas('signatures', fn ($query) => $query->whereNotNull('signature')->where('signature', '!=', ''))
            ->count();
            
        $processingSigned = (clone $monthlyDocuments)
            ->where('status', 'processing')
            ->whereHas('signatures', fn ($query) => $query->whereNotNull('signature')->where('signature', '!=', ''))
            ->count();

        $activeCount = (int) $statusCounts->get('active', 0);
        $processingCount = (int) $statusCounts->get('processing', 0);
        $processing = max(0, $activeCount - $activeSigned) + $processingSigned;
        $generating = max(0, $processingCount - $processingSigned);
        $archive = (int) $statusCounts->get('completed', 0) + (int) $statusCounts->get('approved', 0) + $activeSigned;
        $failed = (int) $statusCounts->get('failed', 0);
        $total = (int) $statusCounts->sum();
        $other = max(0, $total - $processing - $generating - (int) $statusCounts->get('draft', 0) - (int) $statusCounts->get('rejected', 0) - $archive - $failed);

        return [
            'total' => $total, 'processing' => $processing, 'generating' => $generating,
            'draft' => (int) $statusCounts->get('draft', 0), 'rejected' => (int) $statusCounts->get('rejected', 0),
            'archive' => $archive, 'failed' => $failed, 'other' => $other,
        ];
    }
}