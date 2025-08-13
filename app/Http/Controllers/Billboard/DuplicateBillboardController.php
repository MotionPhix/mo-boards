<?php

declare(strict_types=1);

namespace App\Http\Controllers\Billboard;

use App\Http\Controllers\Controller;
use App\Models\Billboard;
use App\Services\BillboardService;
use Illuminate\Http\RedirectResponse;

final class DuplicateBillboardController extends Controller
{
    public function __construct(private readonly BillboardService $billboardService) {}

    public function __invoke(Billboard $billboard): RedirectResponse
    {
        $this->authorize('duplicate', $billboard);

        $newBillboard = $this->billboardService->duplicateBillboard($billboard);

        return redirect()->route('billboards.edit', $newBillboard)
            ->with('success', 'Billboard duplicated successfully! Please review and update the details.');
    }
}
