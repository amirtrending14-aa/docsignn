<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanySettingsController extends Controller
{
    public function settings(Company $company)
    {
        $user = request()->user();

        // Admin может менять только свою компанию
        if (! $user->isSuperAdmin() && ! $user->canManageCompany($company)) {
            abort(403);
        }

        return view('admin.company-settings', ['company' => $company]);
    }

    public function updateSettings(Request $request, Company $company)
    {
        $user = request()->user();

        if (! $user->isSuperAdmin() && ! $user->canManageCompany($company)) {
            abort(403);
        }

        $data = $request->validate([
            'work_start_time'        => ['required', 'date_format:H:i'],
            'late_tolerance_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'late_fine'              => ['required', 'numeric', 'min:0'],
            'absence_fine'           => ['required', 'numeric', 'min:0'],
        ]);

        $company->update($data);

        return back()->with('success', 'Правила компании сохранены');
    }
}