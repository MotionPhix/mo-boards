<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

final class AuthenticatedSessionController extends Controller
{
  /**
   * Display the login view.
   */
  public function create(): Response
  {
    return Inertia::render('auth/Login', [
      'canResetPassword' => Route::has('password.request'),
      'status' => session('status'),
    ]);
  }

  /**
   * Handle an incoming authentication request.
   */
  public function store(LoginRequest $request): RedirectResponse
  {
    $request->authenticate();

    $request->session()->regenerate();

    $user = Auth::user();

    // Update last active timestamp
    $user->update(['last_active_at' => now()]);

    // Ensure user has a current company set
    if (!$user->current_company_id && $user->companies()->count() > 0) {
      $firstCompany = $user->companies()->first();
      $user->update(['current_company_id' => $firstCompany->id]);
    }

    return redirect()->intended(route('dashboard'));
  }

  /**
   * Destroy an authenticated session.
   */
  public function destroy(Request $request): RedirectResponse
  {
    Auth::guard('web')->logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return redirect('/');
  }

  /**
   * Switch to a different company context.
   */
  public function switchCompany(Request $request): RedirectResponse
  {
    $request->validate([
      'company_id' => 'required|integer|exists:companies,id'
    ]);

    $user = Auth::user();
    $companyId = $request->integer('company_id');

    // Verify user can access this company
    if (!$user->canAccessCompany(\App\Models\Company::find($companyId))) {
      return back()->withErrors(['company' => 'You do not have access to this company.']);
    }

    $user->update(['current_company_id' => $companyId]);

    return back()->with('success', 'Company context switched successfully.');
  }
}
