<?php

declare(strict_types=1);

namespace App\Http\Controllers\Billboard;

use App\Enums\BillboardStatus;
use App\Http\Controllers\Controller;
use App\Models\Billboard;
use App\Services\BillboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class BulkUpdateBillboardsController extends Controller
{
    public function __construct(private readonly BillboardService $billboardService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->authorize('bulkUpdate', Billboard::class);

        $request->validate([
            'billboard_ids' => 'required|array',
            'billboard_ids.*' => 'exists:billboards,id',
            'action' => 'required|in:activate,set_available,maintenance,remove',
        ]);

        $user = Auth::user();
        $company = $user->currentCompany;

        $hasMaintenance = Billboard::whereIn('id', $request->billboard_ids)
            ->where('status', BillboardStatus::MAINTENANCE->value)
            ->exists();
        if ($hasMaintenance && ! ($user?->hasRole('company_owner'))) {
            return response()->json([
                'message' => 'Only a company owner can change status while a billboard is in maintenance.',
            ], 403);
        }

        $status = match ($request->action) {
            'activate' => BillboardStatus::ACTIVE->value,
            'set_available' => BillboardStatus::AVAILABLE->value,
            'maintenance' => BillboardStatus::MAINTENANCE->value,
            'remove' => BillboardStatus::REMOVED->value,
        };

        $updated = $this->billboardService->bulkUpdateStatus(
            $company,
            $request->billboard_ids,
            $status
        );

        return response()->json([
            'message' => "Successfully updated {$updated} billboards.",
            'updated_count' => $updated,
        ]);
    }
}
