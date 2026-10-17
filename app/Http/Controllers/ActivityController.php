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
        $query = DocumentLog::with(['user', 'document']);

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
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
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('document', function ($documentQuery) use ($search) {
                        $documentQuery->where('title', 'like', "%{$search}%")
                            ->orWhere('number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(50);

        $totalActivities = DocumentLog::count();

        $todayLogins = DocumentLog::whereDate('created_at', Carbon::today())
            ->where(function ($query) {
                $query->where('action', 'like', '%вход%')
                    ->orWhere('action', 'like', '%login%');
            })
            ->count();

        $documentActions = DocumentLog::whereIn('action', [
            'создание', 'обновление', 'удаление', 'подписание', 'отправка',
            'отклонение', 'экспорт', 'скачивание', 'анализ ИИ', 'генерация ИИ',
            'CREATED', 'RECEIVED', 'UPDATED', 'DELETED', 'SIGNED', 'REJECTED',
            'create', 'update', 'delete', 'sign', 'signed', 'document_created',
            'document_updated', 'document_deleted', 'document_signed',
        ])->count();

        $activeUsersCount = User::count();

        $users = User::orderBy('name')->get();
        $actions = DocumentLog::query()
            ->whereNotNull('action')
            ->where('action', '!=', '')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('superadmin.activity', compact(
            'activities',
            'users',
            'totalActivities',
            'todayLogins',
            'documentActions',
            'activeUsersCount',
            'actions'
        ));
    }
}