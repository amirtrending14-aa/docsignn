<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        Log::info('StrelController: team scope', [
            'auth_user_id' => $authUser->id,
            'auth_user_company_id' => $authUser->company_id ?? null,
            'auth_user_company_string' => $authUser->company ?? null,
            'resolved_user_ids' => $userIds,
        ]);

        $connections = [];
        $documentCounts = [];
        $documentDetails = [];
        $totalDocs = 0;

        // ============================================
        // 1. Таблица document_routes
        // ============================================
        if (Schema::hasTable('document_routes')) {
            $routeColumns = Schema::getColumnListing('document_routes');

            // ⚠️ ВАЖНО: расширенные и НЕ пересекающиеся списки кандидатов.
            // Порядок важен — более специфичные имена стоят первыми.
            $senderCol = $this->findColumn($routeColumns, [
                'sender_id', 'from_user_id', 'from_id', 'author_id',
                'sent_by', 'sender', 'creator_id', 'created_by',
            ]);
            $recipientCol = $this->findColumn($routeColumns, [
                'receiver_id', 'recipient_id', 'to_user_id', 'to_id',
                'assignee_id', 'assigned_to', 'receiver', 'delegate_id',
                'user_id',
            ]);
            $docIdCol = $this->findColumn($routeColumns, ['document_id', 'doc_id']);

            Log::info('StrelController: document_routes columns', [
                'all_columns' => $routeColumns,
                'senderCol' => $senderCol,
                'recipientCol' => $recipientCol,
                'docIdCol' => $docIdCol,
            ]);

            if ($senderCol && $recipientCol && $senderCol !== $recipientCol) {
                $routes = DB::table('document_routes')
                    ->whereNotNull($senderCol)
                    ->whereNotNull($recipientCol)
                    ->whereIn($senderCol, $userIds)
                    ->whereIn($recipientCol, $userIds)
                    ->whereColumn($senderCol, '!=', $recipientCol)
                    ->get();

                foreach ($routes as $route) {
                    $from = (int) $route->$senderCol;
                    $to = (int) $route->$recipientCol;

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
            } else {
                Log::warning('StrelController: document_routes sender/recipient columns not resolved, skipping this source', [
                    'senderCol' => $senderCol,
                    'recipientCol' => $recipientCol,
                ]);
            }
        }

        // ============================================
        // 2. Таблица documents (если document_routes пустая)
        // ============================================
        if (Schema::hasTable('documents') && $totalDocs == 0) {
            $docColumns = Schema::getColumnListing('documents');

            // 🛡️ Подстраховка: если "логичный" по названию кандидат на практике
            // всегда NULL (бывает, что колонка есть в схеме, но приложение её
            // не заполняет — как было с sender_id/created_by), пробуем
            // следующего кандидата по списку, у которого реально есть данные.
            $senderCol = $this->firstNonEmptyColumn($docColumns, [
                'created_by', 'creator_id',
                'sender_id', 'from_user_id', 'from_id', 'author_id',
            ], 'documents');
            $recipientCol = $this->firstNonEmptyColumn($docColumns, [
                'receiver_id', 'recipient_id', 'to_user_id', 'to_id',
                'assignee_id', 'assigned_to', 'user_id',
            ], 'documents');

            Log::info('StrelController: documents columns', [
                'all_columns' => $docColumns,
                'senderCol' => $senderCol,
                'recipientCol' => $recipientCol,
            ]);

            // 🔎 Диагностика без фильтра по $userIds — чтобы увидеть
            // РЕАЛЬНЫХ sender/receiver последних документов и сравнить
            // их с $userIds (список ниже, "team scope").
            if ($senderCol && $recipientCol) {
                $rawLatest = DB::table('documents')
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get(['id', $senderCol, $recipientCol]);

                Log::info('StrelController: latest documents (unfiltered by team)', [
                    'rows' => $rawLatest->toArray(),
                    'current_team_user_ids' => $userIds,
                ]);
            }

            if ($senderCol && $recipientCol && $senderCol !== $recipientCol) {
                $docs = DB::table('documents')
                    ->whereNotNull($senderCol)
                    ->whereNotNull($recipientCol)
                    ->whereIn($senderCol, $userIds)
                    ->whereIn($recipientCol, $userIds)
                    ->whereColumn($senderCol, '!=', $recipientCol)
                    ->get();

                foreach ($docs as $doc) {
                    $from = (int) $doc->$senderCol;
                    $to = (int) $doc->$recipientCol;

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
            } else {
                Log::warning('StrelController: documents sender/recipient columns not resolved either', [
                    'senderCol' => $senderCol,
                    'recipientCol' => $recipientCol,
                ]);
            }
        }

        Log::info('StrelController: final counts', [
            'user_count' => count($userIds),
            'connections_count' => count($connections),
            'total_docs' => $totalDocs,
        ]);

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
        if ($depth > 20) {
            return $userId;
        }

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

    /**
     * Как findColumn(), но дополнительно проверяет, что колонка реально
     * содержит хотя бы одно не-NULL значение в таблице. Нужно для случаев,
     * когда колонка существует в схеме, но приложение её не заполняет
     * (например sender_id всегда NULL, а реальный отправитель в created_by).
     */
    private function firstNonEmptyColumn(array $existingColumns, array $candidates, string $table): ?string
    {
        $fallback = null;

        foreach ($candidates as $candidate) {
            if (!in_array($candidate, $existingColumns)) {
                continue;
            }

            if ($fallback === null) {
                $fallback = $candidate;
            }

            $hasData = DB::table($table)->whereNotNull($candidate)->exists();
            if ($hasData) {
                return $candidate;
            }
        }

        // Если ни одна колонка не содержит данных, вернём первую найденную
        // по схеме — так поведение останется предсказуемым, а не молча null.
        return $fallback;
    }
}