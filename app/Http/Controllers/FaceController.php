<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FaceController extends Controller
{
    /**
     * Строгий порог: 0.5
     * < 0.4 = точно тот же человек
     * 0.4 - 0.5 = вероятно тот же человек
     * > 0.5 = РАЗНЫЕ ЛЮДИ (блокируем)
     */
    private const FACE_THRESHOLD = 0.50;

    public function scanPage(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->needs_face_scan) {
            return redirect()->route('dashboard');
        }

        if ($request->query('reset') === '1') {
            $user->update([
                'face_vector'     => null,
                'face_registered' => false,
            ]);
            return redirect()->route('face.scan');
        }

        if ($user->face_registered) {
            return view('face.scan', ['mode' => 'checkin']);
        }

        return view('face.scan', ['mode' => 'register']);
    }

    public function register(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->face_registered) {
            return $this->error('Лицо уже зарегистрировано. Сбросьте через ?reset=1', 403);
        }

        $user->load('companyRelation');
        $vector = $this->validatedVector($request);

        // ✅ ПРОВЕРКА: вектор должен быть ровно 128 чисел
        if (count($vector) !== 128) {
            return $this->error('Некорректный вектор лица (длина: ' . count($vector) . ')', 422);
        }

        $user->update([
            'face_vector'     => $vector,
            'face_registered' => true,
        ]);

        Log::info('FaceID: лицо зарегистрировано', [
            'user_id'   => $user->id,
            'vector_sample' => array_slice($vector, 0, 3), // первые 3 числа для диагностики
        ]);

        // Сразу отмечаем приход
        $now        = now();
        $lateness   = $this->checkLatency($user, $now);
        $attendance = $user->attendances()->create([
            'date'          => $now->toDateString(),
            'check_in_time' => $now->format('H:i:s'),
            'status'        => $lateness['is_late'] ? Attendance::STATUS_LATE : Attendance::STATUS_ON_TIME,
            'fine'          => $this->calculateFine($user, $lateness),
        ]);

        return $this->successResponse($attendance, $lateness);
    }

    public function checkin(Request $request): JsonResponse
    {
        $user   = $request->user()->load('companyRelation');
        $vector = $this->validatedVector($request);

        if (! $user->face_vector) {
            return $this->error('Сначала зарегистрируйте лицо', 422);
        }

        // ✅ ПРОВЕРКА 1: сохранённый вектор должен быть массивом 128 чисел
        $savedVector = $user->face_vector;
        if (! is_array($savedVector) || count($savedVector) !== 128) {
            Log::error('FaceID: повреждённый вектор в БД', [
                'user_id'  => $user->id,
                'type'     => gettype($savedVector),
                'count'    => is_array($savedVector) ? count($savedVector) : 'N/A',
            ]);
            return $this->error('Ошибка данных лица. Обратитесь к администратору для перерегистрации.', 422);
        }

        // ✅ ПРОВЕРКА 2: считаем расстояние
        $distance = $this->distance($savedVector, $vector);

        Log::info('FaceID: попытка входа', [
            'user_id'      => $user->id,
            'user_name'    => $user->name,
            'distance'     => round($distance, 4),
            'threshold'    => self::FACE_THRESHOLD,
            'match'        => $distance <= self::FACE_THRESHOLD,
            'saved_sample' => array_slice($savedVector, 0, 3),
            'current_sample' => array_slice($vector, 0, 3),
        ]);

        // ✅ ПРОВЕРКА 3: строгое сравнение
        if ($distance > self::FACE_THRESHOLD) {
            return $this->error(sprintf(
                'Лицо не совпадает! (расстояние: %.3f, порог: %.2f). Это не %s.',
                $distance,
                self::FACE_THRESHOLD,
                $user->name
            ), 422);
        }

        // ✅ ПРОВЕРКА 4: дополнительная защита — очень близкое расстояние
        // Если расстояние < 0.1, это может быть фото/видео — но оставим пока
        if ($distance < 0.05) {
            Log::warning('FaceID: подозрительно близкое совпадение', [
                'user_id' => $user->id,
                'distance' => $distance,
            ]);
        }

        // Проверка: не отмечался ли уже сегодня
        $now        = now();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $now->toDateString())
            ->first();

        if ($attendance && $attendance->status !== Attendance::STATUS_ABSENT) {
            return response()->json([
                'ok'       => true,
                'status'   => 'already',
                'message'  => 'Вы уже отметились сегодня',
                'redirect' => route('dashboard'),
            ], 200);
        }

        $lateness = $this->checkLatency($user, $now);

        $data = [
            'check_in_time' => $now->format('H:i:s'),
            'status'        => $lateness['is_late'] ? Attendance::STATUS_LATE : Attendance::STATUS_ON_TIME,
            'fine'          => $this->calculateFine($user, $lateness),
        ];

        $attendance
            ? $attendance->update($data)
            : $attendance = $user->attendances()->create(['date' => $now->toDateString()] + $data);

        return $this->successResponse($attendance, $lateness);
    }

    // ================= ЛОГИКА ШТРАФОВ =================

    private function checkLatency($user, $now): array
    {
        $company  = $user->companyRelation;
        $deadline = $now->copy()
            ->setTimeFromTimeString($company->work_start_time ?? '08:30')
            ->addMinutes($company->late_tolerance_minutes ?? 0);

        if (! $now->greaterThan($deadline)) {
            return ['is_late' => false, 'minutes' => 0];
        }

        return [
            'is_late' => true,
            'minutes' => (int) ceil($deadline->diffInSeconds($now) / 60),
        ];
    }

    private function calculateFine($user, array $lateness): float
    {
        if (! $lateness['is_late']) return 0.0;

        $company = $user->companyRelation;
        $blockMinutes = (int) ($company->late_block_minutes ?? 60);
        $blockFine    = (float) ($company->late_block_fine ?? 100);

        if ($blockMinutes <= 0) $blockMinutes = 60;

        $blocks = (int) ceil($lateness['minutes'] / $blockMinutes);
        return (float) ($blocks * $blockFine);
    }

    // ================= HELPERS =================

    private function successResponse(Attendance $attendance, array $lateness): JsonResponse
    {
        return response()->json([
            'ok'           => true,
            'status'       => $attendance->status,
            'time'         => $attendance->check_in_time,
            'late_minutes' => $lateness['minutes'],
            'fine'         => (float) $attendance->fine,
            'redirect'     => route('dashboard'),
        ]);
    }

    private function validatedVector(Request $request): array
    {
        return $request->validate([
            'vector'   => 'required|array|size:128',
            'vector.*' => 'numeric',
        ])['vector'];
    }

    private function distance(array $a, array $b): float
    {
        $sum = 0.0;
        foreach ($a as $i => $value) {
            $sum += ($value - $b[$i]) ** 2;
        }
        return sqrt($sum);
    }

    private function error(string $message, int $code): JsonResponse
    {
        return response()->json(['error' => $message], $code);
    }
}