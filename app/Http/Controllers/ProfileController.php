<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Carbon\Carbon;

class ProfileController extends Controller
{
    /**
     * Показ профиля с активностью
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        $year = (int) $request->get('year', now()->year);

        $firstDayOfYear = Carbon::create($year, 1, 1);
        $startDate = $firstDayOfYear->copy()->startOfWeek(Carbon::MONDAY);
        $endDate = Carbon::create($year, 12, 31)->endOfWeek(Carbon::SUNDAY);

        $totalDays = $startDate->diffInDays($endDate) + 1;
        $weeksCount = (int) ceil($totalDays / 7);

        $activityData = Document::where('created_by', $user->id)
            ->whereYear('created_at', $year)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return view('profile.show', compact(
            'user',
            'activityData',
            'startDate',
            'year',
            'weeksCount'
        ));
    }

    /**
     * Форма редактирования профиля
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Обновление основной информации профиля
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'         => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value && !preg_match('/^\+992 \d{2} \d{3} \d{2} \d{2}$/', $value)) {
                        $fail('Номер телефона должен быть в формате +992 XX XXX XX XX');
                    }
                }
            ],
            'company'       => ['nullable', 'string', 'max:255'],
            'avatar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'string', 'in:0,1'],
        ]);

        // ===== ОБРАБОТКА АВАТАРА =====
        if ($request->input('remove_avatar') === '1') {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        // ===== ОБНОВЛЕНИЕ ОСНОВНЫХ ДАННЫХ =====
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;

        // ===== СИНХРОНИЗАЦИЯ КОМПАНИИ =====
        if ($user->isAdmin() && !empty($validated['company'])) {
            $newCompanyName = $validated['company'];

            if ($user->company !== $newCompanyName) {
                if ($user->company_id) {
                    $company = Company::find($user->company_id);
                    if ($company) {
                        $company->update(['name' => $newCompanyName]);
                    }
                } else {
                    $company = Company::create([
                        'name' => $newCompanyName,
                        'owner_id' => $user->id,
                    ]);
                    $user->company_id = $company->id;
                }

                User::where('company_id', $user->company_id)
                    ->update(['company' => $newCompanyName]);

                $user->company = $newCompanyName;
            }
        } elseif (!$user->isAdmin() && !empty($validated['company'])) {
            $user->company = $validated['company'];
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Обновление настроек уведомлений
     */
    public function updateGeneral(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'email_notifications' => 'nullable|string',
            'tg_notifications'    => 'nullable|string',
            'language'            => 'required|string|in:ru,tg,en',
        ]);

        $user->update([
            'email_notifications' => $request->has('email_notifications'),
            'tg_notifications'    => $request->has('tg_notifications'),
            'language'            => $data['language'],
        ]);

        return back()->with('success', 'Настройки успешно обновлены!');
    }

    /**
     * Обновление пароля
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }

    /**
     * Удаление аккаунта
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('status', 'profile-deleted');
    }
}