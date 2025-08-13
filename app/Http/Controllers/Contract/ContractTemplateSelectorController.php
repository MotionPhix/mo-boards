<?php

declare(strict_types=1);

namespace App\Http\Controllers\Contract;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ContractTemplateSelectorController extends Controller
{
    public function __invoke(Request $request, Contract $contract): Response
    {
        $this->authorize('view', $contract);

        $company = $request->user()->currentCompany;

        return Inertia::render('contracts/partials/TemplateSelectionModal', [
            'templates' => ContractTemplate::where('company_id', $company->id)
                ->active()
                ->get(['id', 'name', 'description', 'content', 'is_active', 'created_at', 'updated_at']),
            'selectedTemplateId' => $contract->template_id,
            'contractId' => $contract->id,
        ]);
    }
}
