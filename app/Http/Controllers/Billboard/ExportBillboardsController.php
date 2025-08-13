<?php

declare(strict_types=1);

namespace App\Http\Controllers\Billboard;

use App\Http\Controllers\Controller;
use App\Models\Billboard;
use App\Services\BillboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class ExportBillboardsController extends Controller
{
    public function __construct(private readonly BillboardService $billboardService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->authorize('exportData', Billboard::class);

        $user = Auth::user();
        $company = $user->currentCompany;

        if (! $company) {
            return response()->json(['error' => 'No company selected'], 400);
        }

        $filters = $request->only([
            'search', 'status', 'size', 'availability',
            'min_rate', 'max_rate', 'created_from', 'created_to',
        ]);

        $data = $this->billboardService->exportBillboards($company, $filters);

        return response()->json([
            'data' => $data,
            'filename' => 'billboards_'.now()->format('Y-m-d_H-i-s').'.csv',
        ]);
    }
}
