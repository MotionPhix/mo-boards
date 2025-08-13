<?php

declare(strict_types=1);

namespace App\Http\Controllers\Contract;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Services\ContractContentService;
use App\Services\PlaceholderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ContractDocumentController extends Controller
{
    public function show(Contract $contract): RedirectResponse
    {
        return redirect()->route('contracts.show', $contract->uuid);
    }

    public function edit(Contract $contract): RedirectResponse
    {
        return redirect()->route('contracts.edit', $contract->uuid);
    }

    public function update(Request $request, Contract $contract): RedirectResponse
    {
        $this->authorize('update', $contract);

        // Support both full design updates and content-only updates
        if ($request->has('design')) {
            $validated = $request->validate([
                'design' => 'required|string',
                'custom_field_values' => 'nullable|array',
            ]);

            $contract->update([
                'design' => $validated['design'],
                'custom_field_values' => $validated['custom_field_values'] ?? [],
            ]);

            // Process the content after updating design
            $contentService = new ContractContentService();
            $contentService->processContractContent($contract);

            return redirect()->route('contracts.document.show', $contract->uuid)
                ->with('success', 'Contract document updated successfully.');
        }

        if ($request->has('content')) {
            $request->validate([
                'content' => 'required|string',
            ]);

            $placeholderService = new PlaceholderService();
            $processedContent = $placeholderService->replacePlaceholders($request->string('content'), $contract);

            $contract->update([
                'document_content' => $processedContent,
            ]);

            return redirect()->back()->with('success', 'Document content updated successfully');
        }

        return redirect()->back()->with('error', 'No updatable fields provided.');
    }
}
