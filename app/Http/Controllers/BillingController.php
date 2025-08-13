<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CompanyTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

final class BillingController extends Controller
{
    // List transactions (keep RESTful by moving to invokable controller if needed by arch)
    public function index(Request $request)
    {
        $company = Auth::user()->currentCompany;
        $this->authorize('viewBilling', $company);

        $query = CompanyTransaction::where('company_id', $company->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->string('search');
                $q->where(function ($qq) use ($s) {
                    $qq->where('reference', 'like', "%{$s}%")
                        ->orWhere('description', 'like', "%{$s}%");
                });
            })
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');

        $transactions = $query->paginate(15)->withQueryString();

        return Inertia::render('companies/settings/Transactions', [
            'transactions' => $transactions,
        ]);
    }

    // Show/download a receipt (treat as show)
    public function show(CompanyTransaction $transaction)
    {
        $company = Auth::user()->currentCompany;
        $this->authorize('viewBilling', $company);

        if ($transaction->company_id !== $company->id) {
            abort(403);
        }

        // Minimal text receipt for now; can be improved to PDF
        $lines = [
            'Receipt',
            '--------',
            'Company: '.$company->name,
            'Reference: '.$transaction->reference,
            'Type: '.ucfirst($transaction->type),
            'Amount: '.number_format($transaction->amount / 100, 2).' '.$transaction->currency,
            'Status: '.ucfirst($transaction->status),
            'Date: '.optional($transaction->occurred_at)->toDateTimeString(),
        ];

        $content = implode("\n", $lines);

        return Response::make($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="receipt-'.$transaction->reference.'.txt"',
        ]);
    }
}
