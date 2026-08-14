<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FaceController extends Controller
{
    // 🔒 ДВОЙНАЯ ПРОВЕРКА: cosine + euclidean
private const COSINE_THRESHOLD   = 0.60;   // Минимальное косинусное сходство (0.0-1.0)
private const EUCLID_THRESHOLD   = 0.60;   // Было 0.55 — увеличиваем
private const SIMILAR_USER_LIMIT = 0.85;   // Порог для защиты от двойников
    private const MAX_ATTEMPTS       = 3;
    private const LOCKOUT_MINUTES    = 10;

    /**
     * Страница сканирования
     */
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
            $this->clearAttempts($user->id);
            return redirect()->route('face.scan');
        }

        $lockout = $this->getLockout($user->id);

        return view('face.scan', [
            'mode'            => $user->face_registered ? 'checkin' : 'register',
            'locked'          => $lockout > 0,
            'lockout_seconds' => $lockout,
            'current_user'    => $user,  // ← ВАЖНО: передаём для отображения в view
        ]);
    }

    /**
     * 🔐 РЕГИСТРАЦИЯ лица
     * Проверяем, что это не лицо другого пользователя
     */
    public function register(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->face_registered) {
            return $this->error('Лицо уже зарегистрировано. Сбросьте через ?reset=1', 403);
        }

        $lockout = $this->getLockout($user->id);
        if ($lockout > 0) {
            return $this->error("Блокировка. Подождите {$lockout} секунд.", 429);
        }

        $user->load('companyRelation');
        $vector = $this->validatedVector($request);

      /*  // 🔒 КРИТИЧНО: проверяем, не принадлежит ли вектор другому пользователю
        $duplicate = $this->findSimilarUser($vector, $user->id);
        if ($duplicate) {
            Log::warning('FaceID: попытка регистрации вектора другого пользователя', [
                'user_id'       => $user->id,
                'email'         => $user->email,
                'duplicate_id'  => $duplicate['user']->id,
                'duplicate_email' => $duplicate['user']->email,
                'similarity'    => round($duplicate['similarity'], 4),
            ]);
            return $this->error(
                "⚠️ Это лицо уже зарегистрировано на {$duplicate['user']->email}. Обратитесь к администратору.",
                409
            );
        }
*/
        $user->update([
            'face_vector'     => $vector,
            'face_registered' => true,
        ]);

        $this->clearAttempts($user->id);

        Log::info('FaceID: лицо зарегистрировано', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'name'    => $user->name,
        ]);

        $attendance = $this->createAttendance($user, now());

        return $this->successResponse($attendance, $this->checkLatency($user, now()));
    }

    /**
     * 🔐 ЕЖЕДНЕВНЫЙ ВХОД
     * Двойная проверка: cosine + euclidean + защита от двойников
     */
    public function checkin(Request $request): JsonResponse
    {
        $user   = $request->user()->load('companyRelation');
        $vector = $this->validatedVector($request);

        $lockout = $this->getLockout($user->id);
        if ($lockout > 0) {
            return $this->error("Блокировка. Подождите {$lockout} секунд.", 429);
        }

        if (! $user->face_vector || ! $user->face_registered) {
            return $this->error('Сначала зарегистрируйте лицо', 422);
        }

        $savedVector = $user->face_vector;
        if (! is_array($savedVector) || count($savedVector) !== 128) {
            Log::error('FaceID: повреждённый вектор', ['user_id' => $user->id]);
            return $this->error('Ошибка данных лица. Обратитесь к администратору.', 422);
        }

        // 🔒 ТРОЙНАЯ ПРОВЕРКА
        $euclid = $this->euclideanDistance($savedVector, $vector);
        $cosine = $this->cosineSimilarity($savedVector, $vector);

        Log::info('FaceID: попытка входа', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'euclid'  => round($euclid, 4),
            'cosine'  => round($cosine, 4),
        ]);

        // ❌ Проверка 1: евклидово расстояние
        if ($euclid > self::EUCLID_THRESHOLD) {
            return $this->handleFailure($user, 'Лицо не совпадает (расстояние)');
        }

        // ❌ Проверка 2: косинусное сходство
        if ($cosine < self::COSINE_THRESHOLD) {
            return $this->handleFailure($user, 'Лицо не совпадает (сходство)');
        }

        // ❌ Проверка 3: не двойник ли это?
        $otherUser = $this->findSimilarUser($vector, $user->id);
        if ($otherUser && $otherUser['similarity'] > $cosine * 0.95) {
            Log::warning('FaceID: подозрение на двойника', [
                'claimed_user'  => $user->id,
                'possible_real' => $otherUser['user']->id,
                'similarity'    => round($otherUser['similarity'], 4),
                'vs_claimed'    => round($cosine, 4),
            ]);
            return $this->handleFailure($user, '⚠️ Обнаружено несоответствие');
        }

        // ✅ Все проверки пройдены
        $this->clearAttempts($user->id);

        $now        = now();
        $today      = $now->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($attendance && $attendance->status !== Attendance::STATUS_ABSENT) {
            return response()->json([
                'ok'       => true,
                'status'   => 'already',
                'message'  => 'Вы уже отметились сегодня',
                'redirect' => route('dashboard'),
            ], 200);
        }

        $attendance = $this->createAttendance($user, $now, $attendance);

        return $this->successResponse($attendance, $this->checkLatency($user, $now));
    }

    /**
     * ❌ Отказ от сканирования
     */
    public function refuse(Request $request): JsonResponse
    {
        $user  = $request->user();
        $today = now()->toDateString();

        $record = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($record) {
            $record->update([
                'status'        => Attendance::STATUS_REFUSED,
                'refused_at'    => now(),
                'check_in_time' => null,
                'fine'          => (float) ($user->companyRelation->absence_fine ?? 200),
            ]);
        } else {
            Attendance::create([
                'user_id'       => $user->id,
                'date'          => $today,
                'status'        => Attendance::STATUS_REFUSED,
                'refused_at'    => now(),
                'check_in_time' => null,
                'fine'          => (float) ($user->companyRelation->absence_fine ?? 200),
            ]);
        }

        Log::info('FaceID: отказ от сканирования', ['user_id' => $user->id]);

        return response()->json(['ok' => true, 'redirect' => route('dashboard')]);
    }

    // ==================== МАТЕМАТИКА ====================

    /**
     * Евклидово расстояние (0 = идентичны)
     */
    private function euclideanDistance(array $a, array $b): float
    {
        $sum = 0.0;
        foreach ($a as $i => $value) {
            $sum += ($value - $b[$i]) ** 2;
        }
        return sqrt($sum);
    }

    /**
     * Косинусное сходство (1.0 = идентичны, 0 = ортогональны)
     * Для нормализованных face-эмбеддингов это НАМНОГО надёжнее евклида
     */
    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0;
        $magA = 0.0;
        $magB = 0.0;
        foreach ($a as $i => $value) {
            $dot  += $value * $b[$i];
            $magA += $value ** 2;
            $magB += $b[$i] ** 2;
        }
        if ($magA == 0 || $magB == 0) return 0.0;
        return $dot / (sqrt($magA) * sqrt($magB));
    }

    /**
     * 🔒 Поиск пользователя с похожим вектором (защита от двойников)
     */
    private function findSimilarUser(array $vector, int $excludeId): ?array
    {
        $candidates = User::where('face_registered', true)
            ->whereNotNull('face_vector')
            ->where('id', '!=', $excludeId)
            ->get();

        $best = null;
        $bestSimilarity = 0;

        foreach ($candidates as $candidate) {
            $saved = $candidate->face_vector;
            if (! is_array($saved) || count($saved) !== 128) continue;

            $sim = $this->cosineSimilarity($saved, $vector);
            if ($sim > $bestSimilarity) {
                $bestSimilarity = $sim;
                $best = $candidate;
            }
        }

        if ($best && $bestSimilarity > self::SIMILAR_USER_LIMIT) {
            return ['user' => $best, 'similarity' => $bestSimilarity];
        }

        return null;
    }

    // ==================== ВСПОМОГАТЕЛЬНЫЕ ====================

    private function handleFailure(User $user, string $reason): JsonResponse
    {
        $this->incrementAttempts($user->id);
        $attempts  = $this->getAttempts($user->id);
        $remaining = self::MAX_ATTEMPTS - $attempts;

        Log::warning('FaceID: неудачная попытка', [
            'user_id'  => $user->id,
            'email'    => $user->email,
            'reason'   => $reason,
            'attempts' => $attempts,
        ]);

        if ($remaining <= 0) {
            return $this->error(
                "❌ Превышен лимит попыток. Блокировка на " . self::LOCKOUT_MINUTES . " мин.",
                429
            );
        }

        return $this->error("{$reason}. Осталось попыток: {$remaining}", 403);
    }

    private function createAttendance(User $user, $now, ?Attendance $existing = null): Attendance
    {
        $lateness = $this->checkLatency($user, $now);
        $data = [
            'date'          => $now->toDateString(),
            'check_in_time' => $now->format('H:i:s'),
            'status'        => $lateness['is_late'] ? Attendance::STATUS_LATE : Attendance::STATUS_ON_TIME,
            'fine'          => $this->calculateFine($user, $lateness),
        ];

        return $existing
            ? tap($existing)->update($data)
            : $user->attendances()->create($data);
    }

    private function getLockout(int $userId): int
    {
        $lockUntil = Cache::get("face_lockout_{$userId}");
        if (! $lockUntil) return 0;
        return max(0, $lockUntil - time());
    }

    private function getAttempts(int $userId): int
    {
        return (int) Cache::get("face_attempts_{$userId}", 0);
    }

    private function incrementAttempts(int $userId): void
    {
        $key = "face_attempts_{$userId}";
        $attempts = $this->getAttempts($userId) + 1;
        Cache::put($key, $attempts, now()->addMinutes(self::LOCKOUT_MINUTES));

        if ($attempts >= self::MAX_ATTEMPTS) {
            Cache::put(
                "face_lockout_{$userId}",
                time() + self::LOCKOUT_MINUTES * 60,
                now()->addMinutes(self::LOCKOUT_MINUTES)
            );
            Log::warning('FaceID: блокировка', ['user_id' => $userId]);
        }
    }

    private function clearAttempts(int $userId): void
    {
        Cache::forget("face_attempts_{$userId}");
        Cache::forget("face_lockout_{$userId}");
    }

    private function checkLatency($user, $now): array
    {
        $company  = $user->companyRelation;
        $deadline = $now->copy()
            ->setTimeFromTimeString($company->work_start_time ?? '08:30')
            ->addMinutes($company->late_tolerance_minutes ?? 0);

        return $now->greaterThan($deadline)
            ? ['is_late' => true, 'minutes' => (int) ceil($deadline->diffInSeconds($now) / 60)]
            : ['is_late' => false, 'minutes' => 0];
    }

    private function calculateFine($user, array $lateness): float
    {
        if (! $lateness['is_late']) return 0.0;

        $company      = $user->companyRelation;
        $blockMinutes = max(1, (int) ($company->late_block_minutes ?? 60));
        $blockFine    = (float) ($company->late_block_fine ?? 100);

        return (float) (ceil($lateness['minutes'] / $blockMinutes) * $blockFine);
    }

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

    private function error(string $message, int $code): JsonResponse
    {
        return response()->json(['error' => $message], $code);
    }
}