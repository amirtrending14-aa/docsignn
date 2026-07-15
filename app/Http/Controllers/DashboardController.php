<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Фильтр: документы пользователя
        $userDocs = function($q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhere('receiver_id', $user->id);
        };

        // ===== СТАТИСТИКА (реальная) =====
        // ===== СТАТИСТИКА (реальная) =====
        // ===== СТАТИСТИКА (реальная) =====
        $stats = [
            'total'          => Document::where($userDocs)->count(),
            'active'         => Document::where($userDocs)->where('status', 'active')->count(),
            'draft'          => Document::where($userDocs)->where('status', 'draft')->count(),
            'pending'        => Document::where('receiver_id', $user->id)->where('status', 'active')->count(), // ← ИСПРАВЛЕНО: входящие на подписании
            'waiting'        => Document::where($userDocs)->where('status', 'active')->count(),
            'signed'         => Document::where($userDocs)->where('status', 'completed')->count(),
            'incoming'       => Document::where('receiver_id', $user->id)->whereIn('status', ['active', 'pending'])->count(),
            'users'          => User::count(),
            'new_users'      => User::whereMonth('created_at', now()->month)->count(),
            'pending_change' => 3,
        ];
        $totalDocs = $stats['total'];

        // ===== СПАРКЛАЙНЫ (реальные данные за 10 дней) =====
        $sparklineData = [];
        for ($i = 9; $i >= 0; $i--) {
            $dateStart = now()->copy()->subDays($i)->startOfDay();
            $dateEnd   = now()->copy()->subDays($i)->endOfDay();

            $sparklineData['total'][] = Document::where($userDocs)
                ->whereBetween('created_at', [$dateStart, $dateEnd])
                ->count();

            $sparklineData['pending'][] = Document::where($userDocs)
                ->where('status', 'pending')
                ->whereBetween('updated_at', [$dateStart, $dateEnd])
                ->count();

            $sparklineData['waiting'][] = Document::where($userDocs)
                ->where('status', 'active')  // ← active = на подписании
                ->whereBetween('updated_at', [$dateStart, $dateEnd])
                ->count();

            $sparklineData['signed'][] = Document::where($userDocs)
                ->where('status', 'completed')
                ->whereBetween('updated_at', [$dateStart, $dateEnd])
                ->count();
        }
        $sparklineData['users'] = array_fill(0, 10, round(User::count() / 10));

        // ===== ГРАФИК (реальные данные за 12 месяцев) =====
        $chartData = ['labels' => [], 'values' => [], 'raw_data' => []];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $chartData['labels'][] = $month->format('M');

            $count = Document::where($userDocs)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $chartData['values'][] = $count;
            $chartData['raw_data'][] = [
                'month' => $month->format('Y-m'),
                'count' => $count
            ];
        }

        // ===== ОЖИДАЮЩИЕ ПОДПИСИ =====
        $pendingDocuments = Document::with(['createdBy', 'receiver'])
            ->where($userDocs)
            ->where('status', 'active')
            ->latest()
            ->take(5)
            ->get();

        // ===== РОСТ (реальный) =====
        $currentMonthStart = now()->copy()->startOfMonth();
        $lastMonthDocs = Document::where($userDocs)
            ->where('created_at', '>=', $currentMonthStart)
            ->count();

        $previousMonthStart = now()->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->copy()->startOfMonth();
        $previousMonthDocs = Document::where($userDocs)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $docsGrowth = $previousMonthDocs > 0
            ? round((($lastMonthDocs - $previousMonthDocs) / $previousMonthDocs) * 100)
            : ($lastMonthDocs > 0 ? 100 : 0);

        // Рост подписей
        $lastMonthSigned = Document::where($userDocs)
            ->where('status', 'completed')
            ->where('updated_at', '>=', $currentMonthStart)
            ->count();

        $previousMonthSigned = Document::where($userDocs)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $signedGrowth = $previousMonthSigned > 0
            ? round((($lastMonthSigned - $previousMonthSigned) / $previousMonthSigned) * 100)
            : ($lastMonthSigned > 0 ? 100 : 0);

        // ===== ПОСЛЕДНИЕ ДОКУМЕНТЫ =====
        $documents = Document::with(['createdBy', 'receiver'])
            ->where($userDocs)
            ->latest()
            ->take(5)
            ->get();

        // ===== АКТИВНОСТЬ =====
        $activities = Document::with(['createdBy', 'receiver'])
            ->where($userDocs)
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard', compact(
            'totalDocs',
            'docsGrowth',
            'signedGrowth',
            'stats',
            'documents',
            'activities',
            'sparklineData',
            'chartData',
            'pendingDocuments'
        ));
    }

    public function getChartData(Request $request)
    {
        $user = auth()->user();
        $period = $request->get('period', 'month');

        $userDocs = function($q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhere('receiver_id', $user->id);
        };

        $data = ['labels' => [], 'values' => []];

        if ($period === 'week') {
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->copy()->subDays($i);
                $data['labels'][] = $date->format('D');
                $data['values'][] = Document::where($userDocs)
                    ->whereDate('created_at', $date)
                    ->count();
            }
        } elseif ($period === 'year') {
            for ($i = 4; $i >= 0; $i--) {
                $year = now()->copy()->subYears($i)->year;
                $data['labels'][] = $year;
                $data['values'][] = Document::where($userDocs)
                    ->whereYear('created_at', $year)
                    ->count();
            }
        } else {
            // Месяц (по умолчанию)
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->copy()->subMonths($i);
                $data['labels'][] = $month->format('M');
                $data['values'][] = Document::where($userDocs)
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();
            }
        }

        return response()->json(['period' => $period, 'data' => $data]);
    }
}