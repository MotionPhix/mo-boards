<?php

declare(strict_types=1);

namespace App\Http\Controllers\Billboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillboardResource;
use App\Models\Billboard;
use App\Services\BillboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class SearchBillboardsController extends Controller
{
    public function __construct(private readonly BillboardService $billboardService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Billboard::class);

        $request->validate([
            'query' => 'required|string|min:2|max:100',
        ]);

        $user = Auth::user();
        $company = $user->currentCompany;

        if (! $company) {
            return response()->json(['billboards' => []]);
        }

        $billboards = $this->billboardService->searchBillboards($company, $request->input('query'));

        return response()->json([
            'billboards' => BillboardResource::collection($billboards),
        ]);
    }
}
