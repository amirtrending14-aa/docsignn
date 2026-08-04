<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index()
    {
        $authUser = auth()->user();

        // 1. Люди без компании идут на no-companies
        if (!$authUser->company_id && !$authUser->isSuperAdmin()) {
            return redirect()->route('users.no-companies');
        }

        // 2. Супер-админ видит всех
        if ($authUser->isSuperAdmin()) {
            $users = User::where('is_super_admin', false)->get();
            $companyName = 'Все компании';
        }
        // 3. Админ видит только свою компанию
        else {
            $users = User::where('company_id', $authUser->company_id)
                ->where('is_super_admin', false)
                ->get();
            $companyName = $authUser->companyRelation->name ?? 'Моя команда';
        }

        $groupedByLevel = $users->groupBy('level')->sortKeys();

        return view('users.index', compact('users', 'groupedByLevel', 'authUser', 'companyName'));
    }

    public function noCompanies()
    {
        $authUser = auth()->user();

        if ($authUser->company_id) {
            return redirect()->route('users.index');
        }

        if ($authUser->isAdmin() || $authUser->isSuperAdmin()) {
            $users = User::where(function ($q) {
                $q->whereNull('company_id')->orWhere('company_id', 0);
            })
                ->where('is_super_admin', false)
                ->latest()
                ->paginate(20);

            return view('users.no-companies', compact('users', 'authUser'));
        }

        return view('users.no-companies', compact('authUser'));
    }

    public function create(Request $request)
    {
        $companyId = $request->query('company_id');
        $selectedCompany = null;

        if ($companyId) {
            $selectedCompany = Company::find($companyId);
        }

        return view('users.create', [
            'selectedCompany' => $selectedCompany
        ]);
    }

    public function store(Request $request)
    {
        $authUser = auth()->user();

        if (!$authUser->isAdmin() && !$authUser->isSuperAdmin()) {
            return redirect()->route('users.index')->with('error', 'Только администратор может добавлять пользователей');
        }

        // Валидация данных
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:6',
            'phone'      => 'nullable|string|unique:users,phone',
            'role'       => 'required|string|max:50', // Теперь это поле точно придет благодаря исправленному JS
            'level'      => 'required|integer|min:1|max:20',
            'avatar'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        // Нормализуем телефон (оставляем только цифры и +)
        if (!empty($data['phone'])) {
            $data['phone'] = preg_replace('/[^0-9+]/', '', $data['phone']);
        }

        // --- ЛОГИКА КОМПАНИИ ---
        // 1. Берем ID из формы (если админ создает из другой компании)
        $targetCompanyId = $data['company_id'] ?? null;
        
        // 2. Если ID нет в форме, берем компанию текущего админа
        if (!$targetCompanyId) {
            $targetCompanyId = $authUser->company_id;
        }

        $targetCompanyName = 'Без компании';

        // 3. Если ID есть, находим имя компании
        if ($targetCompanyId) {
            $company = Company::find($targetCompanyId);
            if ($company) {
                $targetCompanyName = $company->name;
            }
        } 
        // 4. Если компании всё еще нет (у админа тоже нет), создаем её автоматически
        elseif ($authUser->company) {
             $company = Company::firstOrCreate(
                ['name' => $authUser->company],
                ['owner_id' => $authUser->id]
            );
            $targetCompanyId = $company->id;
            $targetCompanyName = $company->name;
            
            // Обновляем компанию самого админа, чтобы привязать его
            if (!$authUser->company_id) {
                $authUser->update(['company_id' => $targetCompanyId]);
            }
        }

        // --- ПОДГОТОВКА ДАННЫХ ---
        
        // Пароль хешируется АВТОМАТИЧЕСКИ мутатором в модели User (setPasswordAttribute).
        // Не используйте Hash::make здесь, иначе будет двойное хеширование!
        
        $data['created_by'] = $authUser->id;
        $data['company_id'] = $targetCompanyId;
        $data['company']    = $targetCompanyName;
        
        // Автоматически ставим флаг is_admin, если уровень 1
        $data['is_admin'] = ((int)$data['level'] === 1);
        $data['is_super_admin'] = false;

        // Загрузка аватара
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Создание пользователя
        try {
            User::create($data);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Ошибка создания: ' . $e->getMessage());
        }

        if ($targetCompanyId) {
            return redirect()->route('companies.show', $targetCompanyId)
                ->with('success', 'Пользователь успешно добавлен в команду!');
        }

        return redirect()->route('users.index')->with('success', 'Пользователь создан');
    }

    public function show(User $user)
    {
        $authUser = auth()->user();
        if (!$this->canAccessUser($authUser, $user)) {
            abort(403, 'Нет доступа к этому пользователю');
        }

        $year = now()->year;
        $startDate = Carbon::create($year, 1, 1)->startOfWeek(Carbon::MONDAY);
        $endDate = Carbon::create($year, 12, 31)->endOfWeek(Carbon::SUNDAY);
        $weeksCount = (int)ceil($startDate->diffInDays($endDate) / 7);

        $activityData = Document::where('created_by', $user->id)
            ->whereYear('created_at', $year)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return view('users.show', compact('user', 'activityData', 'year', 'startDate', 'weeksCount'));
    }

    public function edit(User $user)
    {
        $authUser = auth()->user();
        if (!$this->canAccessUser($authUser, $user)) {
            return redirect()->route('users.index')->with('error', 'Нет доступа к этому пользователю');
        }

        if (!$authUser->isAdmin() && $user->id !== $authUser->id) {
            return redirect()->route('users.index')->with('error', 'Нет прав для редактирования');
        }

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $authUser = auth()->user();

        if (!$this->canAccessUser($authUser, $user)) {
            return redirect()->route('users.index')->with('error', 'Нет доступа к этому пользователю');
        }

        if (!$authUser->isAdmin() && $user->id !== $authUser->id) {
            return redirect()->route('users.index')->with('error', 'Нет прав для редактирования');
        }

        $rules = [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'phone'         => 'nullable|string|unique:users,phone,' . $user->id,
            'avatar'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_avatar' => 'nullable|string|in:0,1',
        ];

        if ($authUser->isAdmin()) {
            $rules['role'] = 'required|string|max:50';
            $rules['level'] = 'required|integer|min:1|max:20';
        }

        $data = $request->validate($rules);

        if (!$authUser->isAdmin()) {
            $data['role'] = $user->role;
            $data['level'] = $user->level;
        }

        if ($request->input('remove_avatar') === '1' && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Данные обновлены');
    }

    public function destroy(User $user)
    {
        $authUser = auth()->user();

        if (!$this->canAccessUser($authUser, $user)) {
            return back()->with('error', 'Нет доступа к этому пользователю');
        }

        if ($user->id === $authUser->id) {
            return back()->with('error', 'Нельзя удалить себя');
        }

        if (!$authUser->isAdmin()) {
            return back()->with('error', 'Только администратор может удалять');
        }

        if ($user->avatar) Storage::disk('public')->delete($user->avatar);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Пользователь удалён');
    }

    private function canAccessUser($authUser, $targetUser)
    {
        if ($authUser->isSuperAdmin()) {
            return true;
        }

        if ($authUser->isAdmin()) {
            return $authUser->company_id && $targetUser->company_id === $authUser->company_id;
        }

        return $targetUser->id === $authUser->id;
    }
}