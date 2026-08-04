<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Company;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // ✅ ДОБАВЛЕНО
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule; // ✅ ДОБАВЛЕНО

class CompanyRegistrationController extends Controller
{
    // ✅ ДОБАВЛЕНО: Массив городов для валидации (чтобы избежать ошибки 1452)
    private function getValidCityIds(): array 
    {
        return [
            101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118,
            201, 202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 220, 221, 222, 223,
            301, 302, 303, 304, 305, 306, 307, 308, 309, 310, 311, 312, 313,
            401, 402, 403, 404, 405, 406, 407, 408,
            501
        ];
    }

    public function showForm()
    {
        $regions = Region::orderBy('id')->get();
        $cities  = City::select('id', 'region_id', 'name_ru')->get();
        return view('auth.register-company', compact('regions', 'cities'));
    }

    public function store(Request $request)
    {
        $validCityIds = $this->getValidCityIds();

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_type' => ['required', 'string', 'max:100'],
            // ✅ ИСПРАВЛЕНО: Строгая валидация через Rule::in для предотвращения FK 1452
            'region_id'    => ['required', Rule::in([1, 2, 3, 4, 5])],
            'city_id'      => ['required', 'integer', Rule::in($validCityIds)],
            
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'confirmed', Password::min(8)],
        ], [
            'city_id.in' => 'Выбран некорректный город или район.',
            'region_id.in' => 'Выбрана некорректная область.',
            // ... остальные твои сообщения об ошибках ...
        ]);

        $user = null;
        $company = null;

        DB::transaction(function () use ($validated, &$user, &$company) {
            // ✅ ИСПРАВЛЕНО: Явное хеширование пароля для гарантии безопасности
            $user = User::create([
                'name'           => $validated['name'],
                'email'          => $validated['email'],
                'password'       => Hash::make($validated['password']), 
                'role'           => 'admin',
                'level'          => 1,
                'is_admin'       => true,
                'is_super_admin' => false,
                'company_id'     => null,
                'created_by'     => null,
            ]);

            $company = Company::create([
                'name'      => $validated['company_name'],
                'type'      => $validated['company_type'],
                'region_id' => $validated['region_id'],
                'city_id'   => $validated['city_id'],
                'email'     => $validated['email'], 
                'address'   => null, 
                'password'  => Str::random(16),
                'status'    => 'active',
                'owner_id'  => $user->id,
                'parent_id' => null,
            ]);

            $user->update(['company_id' => $company->id]);
        });

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('status', 'Компания «' . $company->name . '» успешно создана!');
    }
}