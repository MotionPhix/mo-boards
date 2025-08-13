<?php

declare(strict_types=1);

namespace App\Http\Controllers\Contract;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Services\PlaceholderService;
use Illuminate\Http\Request;

final class ApplyContractTemplateController extends Controller
{
    public function __invoke(Request $request, Contract $contract)
    {
        $this->authorize('update', $contract);

        $request->validate([
            'template_id' => 'required|exists:contract_templates,id',
            'content' => 'required|string',
        ]);

        $placeholderService = new PlaceholderService();
        $processedContent = $placeholderService->replacePlaceholders($request->string('content'), $contract);

        $contract->update([
            'template_id' => $request->integer('template_id'),
            'document_content' => $processedContent,
        ]);

        return redirect()->back()->with('success', 'Template applied successfully');
    }
}
