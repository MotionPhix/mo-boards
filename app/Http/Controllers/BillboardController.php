<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillboardRequest;
use App\Http\Requests\UpdateBillboardRequest;
use App\Models\Billboard;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BillboardController extends Controller
{
  public function index(Request $request): Response
  {
    $user = Auth::user();
    $company = $user->currentCompany;

    if (!$company) {
      return redirect()->route('companies.create')
        ->with('info', 'Please create a company first.');
    }

    $billboards = $company->billboards()
      ->when($request->search, function ($query, $search) {
        $query->where('name', 'like', "%{$search}%")
          ->orWhere('location', 'like', "%{$search}%")
          ->orWhere('code', 'like', "%{$search}%");
      })
      ->when($request->status, function ($query, $status) {
        $query->where('status', $status);
      })
      ->orderBy('created_at', 'desc')
      ->paginate(15);

    return Inertia::render('Billboards/Index', [
      'billboards' => $billboards,
      'filters' => $request->only(['search', 'status']),
      'company' => $company,
    ]);
  }

  public function create(): Response
  {
    return Inertia::render('Billboards/Create');
  }

  public function store(StoreBillboardRequest $request)
  {
    $user = Auth::user();

    $billboard = $user->currentCompany->billboards()->create($request->validated());

    return redirect()->route('billboards.index')
      ->with('success', 'Billboard created successfully!');
  }

  public function show(Billboard $billboard): Response
  {
    $this->authorize('view', $billboard);

    $billboard->load('media');

    return Inertia::render('Billboards/Show', [
      'billboard' => $billboard,
    ]);
  }

  public function edit(Billboard $billboard): Response
  {
    $this->authorize('update', $billboard);

    return Inertia::render('Billboards/Edit', [
      'billboard' => $billboard,
    ]);
  }

  public function update(UpdateBillboardRequest $request, Billboard $billboard)
  {
    $this->authorize('update', $billboard);

    $billboard->update($request->validated());

    return redirect()->route('billboards.index')
      ->with('success', 'Billboard updated successfully!');
  }

  public function destroy(Billboard $billboard)
  {
    $this->authorize('delete', $billboard);

    $billboard->delete();

    return redirect()->route('billboards.index')
      ->with('success', 'Billboard deleted successfully!');
  }
}
