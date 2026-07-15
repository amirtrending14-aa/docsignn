<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StrelController extends Controller
{
    public function index()
    {
        $authUser = auth()->user();

        // ✅ ПРАВИЛЬНОЕ название компании — как в UserController
        if ($authUser->companyRelation) {
            $companyName = $authUser->companyRelation->name;
        } else {
            $companyName = $authUser->company ?? 'Моя команда';
        }

        // ============================================
        // Собираем ВСЕХ пользователей команды
        // ============================================
        $userIds = $this->getAllTeamUserIds($authUser);

        $users = User::whereIn('id', $userIds)
            ->orderBy('level', 'asc')
            ->get()
            ->keyBy('id');

        $groupedByLevel = $users->groupBy('level')->sortKeys();

        $connections = [];
        $documentCounts = [];
        $documentDetails = [];
        $totalDocs = 0;

        // ============================================
        // 1. Таблица document_routes
        // ============================================
        if (Schema::hasTable('document_routes')) {
            $routeColumns = Schema::getColumnListing('document_routes');

            $senderCol = $this->findColumn($routeColumns, ['sender_id', 'from_user_id', 'created_by', 'user_id']);
            $recipientCol = $this->findColumn($routeColumns, ['receiver_id', 'user_id', 'recipient_id', 'to_user_id', 'assignee_id']);
            $docIdCol = $this->findColumn($routeColumns, ['document_id', 'doc_id']);

            if ($senderCol && $recipientCol && $senderCol !== $recipientCol) {
                $routes = DB::table('document_routes')
                    ->whereNotNull($senderCol)
                    ->whereNotNull($recipientCol)
                    ->whereIn($senderCol, $userIds)
                    ->whereIn($recipientCol, $userIds)
                    ->whereColumn($senderCol, '!=', $recipientCol)
                    ->get();

                foreach ($routes as $route) {
                    $from = $route->$senderCol;
                    $to = $route->$recipientCol;

                    $connections[$from] = $connections[$from] ?? [];
                    $documentCounts["{$from}-{$to}"] = $documentCounts["{$from}-{$to}"] ?? 0;
                    $documentDetails["{$from}-{$to}"] = $documentDetails["{$from}-{$to}"] ?? [];

                    if (!in_array($to, $connections[$from])) {
                        $connections[$from][] = $to;
                    }
                    $documentCounts["{$from}-{$to}"]++;

                    $docInfo = [
                        'id' => $route->id ?? null,
                        'document_id' => $docIdCol ? ($route->$docIdCol ?? null) : null,
                        'created_at' => $route->created_at ?? null,
                        'status' => $route->status ?? null,
                        'route_type' => $route->route_type ?? $route->type ?? null,
                    ];

                    if ($docIdCol && $route->$docIdCol && Schema::hasTable('documents')) {
                        $doc = DB::table('documents')->where('id', $route->$docIdCol)->first();
                        if ($doc) {
                            $docInfo['title'] = $doc->title ?? $doc->name ?? 'Без названия';
                            $docInfo['type'] = $doc->type ?? $doc->document_type ?? null;
                            $docInfo['status'] = $doc->status ?? $docInfo['status'];
                        }
                    }

                    $documentDetails["{$from}-{$to}"][] = $docInfo;
                }

                $totalDocs = $routes->count();
            }
        }

        // ============================================
        // 2. Таблица documents (если document_routes пустая)
        // ============================================
        if (Schema::hasTable('documents') && $totalDocs == 0) {
            $docColumns = Schema::getColumnListing('documents');

            $senderCol = $this->findColumn($docColumns, ['created_by', 'sender_id', 'from_user_id', 'author_id']);
            $recipientCol = $this->findColumn($docColumns, ['receiver_id', 'user_id', 'to_user_id', 'assignee_id', 'recipient_id']);

            if ($senderCol && $recipientCol && $senderCol !== $recipientCol) {
                $docs = DB::table('documents')
                    ->whereNotNull($senderCol)
                    ->whereNotNull($recipientCol)
                    ->whereIn($senderCol, $userIds)
                    ->whereIn($recipientCol, $userIds)
                    ->whereColumn($senderCol, '!=', $recipientCol)
                    ->get();

                foreach ($docs as $doc) {
                    $from = $doc->$senderCol;
                    $to = $doc->$recipientCol;

                    $connections[$from] = $connections[$from] ?? [];
                    $documentCounts["{$from}-{$to}"] = $documentCounts["{$from}-{$to}"] ?? 0;
                    $documentDetails["{$from}-{$to}"] = $documentDetails["{$from}-{$to}"] ?? [];

                    if (!in_array($to, $connections[$from])) {
                        $connections[$from][] = $to;
                    }
                    $documentCounts["{$from}-{$to}"]++;

                    $documentDetails["{$from}-{$to}"][] = [
                        'id' => $doc->id,
                        'title' => $doc->title ?? $doc->name ?? 'Без названия',
                        'type' => $doc->type ?? $doc->document_type ?? null,
                        'status' => $doc->status ?? null,
                        'created_at' => $doc->created_at ?? null,
                    ];
                }

                $totalDocs = $docs->count();
            }
        }

        return view('strel.index', compact(
            'users',
            'groupedByLevel',
            'connections',
            'documentCounts',
            'documentDetails',
            'totalDocs',
            'authUser',
            'companyName'
        ));
    }

    private function getAllTeamUserIds(User $authUser): array
    {
        $userIds = collect([$authUser->id]);

        // ✅ Способ 1: По company_id (самый надёжный)
        if ($authUser->company_id) {
            $companyUsers = User::where('company_id', $authUser->company_id)
                ->where('is_super_admin', false)
                ->pluck('id');
            $userIds = $userIds->merge($companyUsers);
        }

        // ✅ Способ 2: По company (строка)
        if ($userIds->count() === 1 && $authUser->company) {
            $companyUsers = User::where('company', $authUser->company)
                ->where('is_super_admin', false)
                ->pluck('id');
            $userIds = $userIds->merge($companyUsers);
        }

        // Способ 3: Рекурсивно через created_by
        if ($userIds->count() === 1) {
            $rootId = $this->findTeamRoot($authUser->id);
            $descendants = $this->findAllDescendants($rootId);
            $userIds = $userIds->merge($descendants);
        }

        return $userIds->unique()->values()->toArray();
    }

    private function findTeamRoot(int $userId, int $depth = 0): int
    {
        if ($depth > 20) return $userId;

        $user = User::find($userId);
        if (!$user || !$user->created_by) {
            return $userId;
        }

        return $this->findTeamRoot($user->created_by, $depth + 1);
    }

    private function findAllDescendants(int $userId): array
    {
        $result = [$userId];
        $queue = [$userId];

        while (!empty($queue)) {
            $currentId = array_shift($queue);
            $children = User::where('created_by', $currentId)->pluck('id')->toArray();

            foreach ($children as $childId) {
                if (!in_array($childId, $result)) {
                    $result[] = $childId;
                    $queue[] = $childId;
                }
            }
        }

        return $result;
    }

    private function findColumn(array $existingColumns, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $existingColumns)) {
                return $candidate;
            }
        }
        return null;
    }
}