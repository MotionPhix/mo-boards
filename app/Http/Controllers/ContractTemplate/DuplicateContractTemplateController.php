<?php

declare(strict_types=1);

namespace App\Http\Controllers\ContractTemplate;

use App\Http\Controllers\Controller;
use App\Models\ContractTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final class DuplicateContractTemplateController extends Controller
{
    public function __invoke(ContractTemplate $contractTemplate): RedirectResponse
    {
        $company = Auth::user()->currentCompany;

        $duplicatedTemplate = ContractTemplate::create([
            'name' => $contractTemplate->name.' (Copy)',
            'description' => $contractTemplate->description,
            'content' => $contractTemplate->content,
            'default_terms' => $contractTemplate->default_terms ?? [],
            'custom_fields' => $contractTemplate->custom_fields ?? [],
            'is_active' => true,
            'company_id' => $company->id,
        ]);

        return redirect()->route('contract-templates.edit', $duplicatedTemplate)
            ->with('success', 'Template duplicated successfully');
    }
}
