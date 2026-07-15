<?php

namespace App\Http\Controllers;

use App\Models\DocumentLog;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        // Используем DocumentLog вместо Activity
        $query = DocumentLog::with(['user', 'document'])  ;

        // Фильтры
        if ($request->filled('action')) {
            $query->where('action', $request->action)  ;
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%');
            });
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(50);

        // Статистика
        $totalActivities = DocumentLog::count();

        $todayLogins = DocumentLog::where('action', 'like', '%вход%')
            ->orWhere('action', 'like', '%login%')
            ->whereDate('created_at', Carbon::today())
            ->count();

        $documentActions = DocumentLog::whereIn('action', [
            'создание', 'обновление', 'удаление', 'подписание',
            'create', 'update', 'delete', 'sign', 'signed'
        ])->count();

        $activeUsersCount = User::count();

        $users = User::orderBy('name')->get();

        return view('superadmin.activity', compact(
            'activities',
            'users',
            'totalActivities',
            'todayLogins',
            'documentActions',
            'activeUsersCount'
        ));
    }
}