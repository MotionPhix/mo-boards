<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class SwitchCompanyController extends Controller
{
    public function __invoke(Request $request, Company $company): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->canAccessCompany($company)) {
            abort(403, 'You do not have access to this company.');
        }

        $user->update(['current_company_id' => $company->id]);

        return redirect()->back()->with('success', "Switched to {$company->name}");
    }
}
