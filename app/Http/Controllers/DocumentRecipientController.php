<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Region;
use App\Models\City;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DocumentRecipientController extends Controller
{
    private const ALL_TYPES = [
        'gov', 'social', 'finance', 'trade', 'industry', 'services', 'infra'
    ];

    /**
     * Страница выбора получателей по структуре.
     * Принимает:
     *  - document_id  — если пришли из редактирования документа
     *  - return_url   — куда вернуться после сохранения (create или edit)
     */
    public function create(Request $request): View
    {
        $regions = Region::select('id', 'name_tj', 'name_ru')
            ->distinct()
            ->orderBy('name_tj')
            ->get();

        $documentId = $request->query('document_id');
        $returnUrl  = $request->query('return_url');

        return view('document.create_recipients', compact('regions', 'documentId', 'returnUrl'));
    }

    public function getCities(int $regionId): JsonResponse
    {
        $cities = City::where('region_id', $regionId)
            ->select('id', 'name_tj', 'name_ru')
            ->orderBy('name_tj')
            ->get();

        return response()->json($cities);
    }

    public function getOrganizationTypes(int $cityId): JsonResponse
    {
        $hasCompanies = Company::where('city_id', $cityId)->exists();

        if (!$hasCompanies) {
            return response()->json([
                'categories' => self::ALL_TYPES,
                'has_data'   => false,
            ]);
        }

        $existingTypes = Company::where('city_id', $cityId)
            ->whereNotNull('type')
            ->distinct()
            ->pluck('type')
            ->toArray();

        $availableCategories = [];
        foreach (self::ALL_TYPES as $category) {
            $typeCodes = $this->getTypeCodes($category);
            if (count(array_intersect($typeCodes, $existingTypes)) > 0) {
                $availableCategories[] = $category;
            }
        }

        return response()->json([
            'categories' => $availableCategories,
            'has_data'   => true,
        ]);
    }

    public function getOrganizations(int $cityId, string $category): JsonResponse
    {
        $typeCodes = $this->getTypeCodes($category);

        $organizations = Company::where('city_id', $cityId)
            ->whereIn('type', $typeCodes)
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return response()->json($organizations);
    }

    public function getUsers(int $organizationId): JsonResponse
    {
        $users = User::where('company_id', $organizationId)
            ->whereNull('deleted_at')
            ->get(['id', 'name', 'role', 'email', 'phone']);

        return response()->json($users);
    }

    /**
     * Сохранение выбранных получателей.
     * - если есть document_id → привязывает к документу;
     * - возвращает ТУДА, откуда пришли (return_url), иначе на edit/create.
     */
   public function store(Request $request)
{
    $request->validate([
        'document_id'     => 'nullable|exists:documents,id',
        'recipient_ids'   => 'required|array|min:1',
        'recipient_ids.*' => 'exists:users,id',
    ]);

    $returnUrl  = $request->input('return_url');
    $documentId = $request->input('document_id');

    // 1) Привязка к документу (если пришли из edit)
    $bindError = null;
    if ($documentId) {
        DB::beginTransaction();
        try {
            $document = Document::findOrFail($documentId);

            $syncData = [];
            foreach ($request->recipient_ids as $userId) {
                $syncData[$userId] = [
                    'status'     => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $document->recipients()->syncWithoutDetaching($syncData);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $bindError = $e->getMessage();
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ✅ ГЛАВНОЕ ИСПРАВЛЕНИЕ: сохраняем в сессию НАВСЕГДА,
    // а НЕ через ->with() (flash умирает через 1 запрос!)
    // ═══════════════════════════════════════════════════════════
    session(['selected_recipients' => $request->recipient_ids]);

    // Через ->with() передаём ТОЛЬКО сообщения (им можно быть flash)
    $with = ['success' => 'Гирандагон бомуваффақият интихоб шуданд!'];
    if ($bindError) {
        $with['error'] = 'Гирандагон интихоб шуданд, аммо хатогӣ дар пайваст кардан ба ҳуҷҷат: ' . $bindError;
    }

    // 3) Возврат ТУДА, откуда пришли
    if ($returnUrl && $this->isLocalUrl($returnUrl)) {
        return redirect($returnUrl)->with($with);
    }
    if ($documentId) {
        return redirect()->route('documents.edit', $documentId)->with($with);
    }
    return redirect()->route('documents.create')->with($with);
}

    /**
     * Проверяет, что URL ведёт на тот же хост (защита от open-redirect).
     */
    private function isLocalUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        return $host === null || $host === request()->getHost();
    }

    private function getTypeCodes(string $category): array
    {
        $map = [
            'gov'      => ['ministry', 'local_government', 'law_enforcement', 'special_agency'],
            'social'   => ['education', 'healthcare', 'social_protection'],
            'finance'  => ['bank', 'business_services', 'it_development'],
            'trade'    => ['retail', 'catering'],
            'industry' => ['manufacturing', 'construction'],
            'services' => ['household_services', 'hospitality', 'sport_leisure'],
            'infra'    => ['utilities', 'transport', 'communication'],
        ];

        return $map[$category] ?? [$category];
    }
}