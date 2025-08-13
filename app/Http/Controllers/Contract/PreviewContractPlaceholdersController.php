<?php

declare(strict_types=1);

namespace App\Http\Controllers\Contract;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Services\PlaceholderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PreviewContractPlaceholdersController extends Controller
{
    public function __invoke(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('view', $contract);

        $request->validate([
            'content' => 'required|string',
        ]);

        $placeholderService = new PlaceholderService();
        $contract->loadMissing(['billboards', 'company']);
        $previewContent = $placeholderService->replacePlaceholders($request->string('content'), $contract);
        $placeholderValues = $placeholderService->getPlaceholderValues($contract);
        $placeholderValues['{{billboards_table}}'] = 'Rendered billboards table layout';
        $placeholderValues['{{billboards_list}}'] = 'Rendered billboards list layout';

        return response()->json([
            'preview_content' => $previewContent,
            'placeholder_values' => $placeholderValues,
        ]);
    }
}
