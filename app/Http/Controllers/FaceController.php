<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FaceController extends Controller
{
    private const FACE_THRESHOLD     = 0.50;   // Максимальная дистанция для совпадения
    private const PHOTO_THRESHOLD    = 0.05;   // Минимальная дистанция (защита от фото)
    private const MAX_ATTEMPTS       = 3;
    private const LOCKOUT_MINUTES    = 5;

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
        if ($lockout > 0) {
            return view('face.scan', [
                'mode'            => $user->face_registered ? 'checkin' : 'register',
                'locked'          => true,
                'lockout_seconds' => $lockout,
            ]);
        }

        return view('face.scan', [
            'mode'            => $user->face_registered ? 'checkin' : 'register',
            'locked'          => false,
            'lockout_seconds' => 0,
        ]);
    }

    /**
     * РЕГИСТРАЦИЯ лица (первый раз)
     * Просто сохраняем вектор лица — без движений головой
     */
    public function register(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->face_registered) {
            return $this->error('Лицо уже зарегистрировано. Сбросьте через ?reset=1', 403);
        }

        $lockout = $this->getLockout($user->id);
        if ($lockout > 0) {
            return $this->error("Слишком много попыток. Подождите {$lockout} секунд.", 429);
        }

        $user->load('companyRelation');
        $vector = $this->validatedVector($request);

        if (count($vector) !== 128) {
            return $this->error('Некорректный вектор лица', 422);
        }

        // Сохраняем вектор лица
        $user->update([
            'face_vector'     => $vector,
            'face_registered' => true,
        ]);

        $this->clearAttempts($user->id);

        Log::info('FaceID: лицо зарегистрировано', [
            'user_id' => $user->id,
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

    /**
     * ЕЖЕДНЕВНЫЙ ВХОД по лицу
     * Сравниваем вектор — проверяем что это ИМЕННО владелец, а не другой человек
     */
    public function checkin(Request $request): JsonResponse
    {
        $user   = $request->user()->load('companyRelation');
        $vector = $this->validatedVector($request);

        $lockout = $this->getLockout($user->id);
        if ($lockout > 0) {
            return $this->error("Слишком много неудачных попыток. Подождите {$lockout} секунд.", 429);
        }

        if (! $user->face_vector) {
            return $this->error('Сначала зарегистрируйте лицо', 422);
        }

        $savedVector = $user->face_vector;
        if (! is_array($savedVector) || count($savedVector) !== 128) {
            Log::error('FaceID: повреждённый вектор в БД', ['user_id' => $user->id]);
            return $this->error('Ошибка данных лица. Обратитесь к администратору.', 422);
        }

        // Сравниваем векторы
        $distance = $this->distance($savedVector, $vector);

        Log::info('FaceID: попытка входа', [
            'user_id'  => $user->id,
            'distance' => round($distance, 4),
        ]);

        // 🔒 Защита от фотографии/видео (слишком идеальное совпадение = подделка)
        if ($distance < self::PHOTO_THRESHOLD) {
            $this->incrementAttempts($user->id);
            Log::warning('FaceID: обнаружена фотография!', [
                'user_id'  => $user->id,
                'distance' => $distance,
            ]);
            return $this->error('🚫 Обнаружена фотография/видео! Нужно живое лицо.', 403);
        }

        // 🔒 Защита от ЧУЖОГО лица (слишком большая разница)
        if ($distance > self::FACE_THRESHOLD) {
            $this->incrementAttempts($user->id);
            $attempts  = $this->getAttempts($user->id);
            $remaining = self::MAX_ATTEMPTS - $attempts;

            Log::warning('FaceID: лицо не совпадает', [
                'user_id'  => $user->id,
                'distance' => $distance,
                'attempts' => $attempts,
            ]);

            if ($remaining <= 0) {
                return $this->error(
                    "❌ Лицо не совпадает. Превышен лимит попыток. Блокировка на " . self::LOCKOUT_MINUTES . " минут.",
                    429
                );
            }

            return $this->error(
                sprintf('Лицо не совпадает. Осталось попыток: %d из %d', $remaining, self::MAX_ATTEMPTS),
                422
            );
        }

        // ✅ Лицо совпало!
        $this->clearAttempts($user->id);

        $now        = now();
        $today      = $now->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereRaw("DATE(date) = ?", [$today])
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

    /**
     * ❌ ОТКАЗ от сканирования — регистрирует неявку в отчёте
     */
    public function refuse(Request $request): JsonResponse
    {
        $user  = $request->user();
        $today = now()->toDateString();

        $record = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($record) {
            $record->status        = Attendance::STATUS_REFUSED;
            $record->refused_at    = now();
            $record->check_in_time = null;
            $record->fine          = (float) ($user->companyRelation->absence_fine ?? 200);
            $record->save();
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

        Log::info('FaceID: пользователь отказался от сканирования', [
            'user_id' => $user->id,
        ]);

        return response()->json([
            'ok'       => true,
            'redirect' => route('dashboard'),
        ]);
    }

    // ==================== ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ ====================

    private function getLockout(int $userId): int
    {
        $lockKey   = "face_lockout_{$userId}";
        $lockUntil = Cache::get($lockKey);

        if (! $lockUntil) return 0;

        $remaining = $lockUntil - time();
        return $remaining > 0 ? $remaining : 0;
    }

    private function getAttempts(int $userId): int
    {
        return (int) Cache::get("face_attempts_{$userId}", 0);
    }

    private function incrementAttempts(int $userId): void
    {
        $key      = "face_attempts_{$userId}";
        $attempts = (int) Cache::get($key, 0) + 1;

        Cache::put($key, $attempts, now()->addMinutes(self::LOCKOUT_MINUTES));

        if ($attempts >= self::MAX_ATTEMPTS) {
            $lockKey = "face_lockout_{$userId}";
            Cache::put($lockKey, time() + (self::LOCKOUT_MINUTES * 60), now()->addMinutes(self::LOCKOUT_MINUTES));

            Log::warning('FaceID: блокировка пользователя', [
                'user_id'  => $userId,
                'attempts' => $attempts,
                'minutes'  => self::LOCKOUT_MINUTES,
            ]);
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

        $company      = $user->companyRelation;
        $blockMinutes = (int) ($company->late_block_minutes ?? 60);
        $blockFine    = (float) ($company->late_block_fine ?? 100);

        if ($blockMinutes <= 0) $blockMinutes = 60;

        $blocks = (int) ceil($lateness['minutes'] / $blockMinutes);
        return (float) ($blocks * $blockFine);
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