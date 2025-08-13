<?php

declare(strict_types=1);

namespace App\Http\Controllers\Contract;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\RedirectResponse;

final class ContractDocumentRedirectController extends Controller
{
    public function __invoke(Contract $contract): RedirectResponse
    {
        return redirect()->route('contracts.show', $contract->uuid);
    }
}
