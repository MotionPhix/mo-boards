<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

final class RegisteredUserController extends Controller
{
  /**
   * Show the registration page.
   */
  public function create(): Response
  {
    return Inertia::render('auth/Register');
  }

  /**
   * Handle an incoming registration request.
   *
   * @throws \Illuminate\Validation\ValidationException
   */
  public function store(Request $request): RedirectResponse
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
      'phone' => 'nullable|string|max:20',
      'password' => ['required', 'confirmed', Rules\Password::defaults()],
      'company_name' => 'required|string|max:255',
      'industry' => 'nullable|string|in:outdoor-advertising,marketing-agency,real-estate,retail,other',
      'company_size' => 'nullable|string|in:1-10,11-50,51-200,200+',
      'address' => 'nullable|string|max:1000',
      'subscription_plan' => 'required|string|in:starter,professional,enterprise',
    ]);

    try {
      DB::beginTransaction();

      // Create user
      $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'phone' => $validated['phone'] ?? null,
        'password' => Hash::make($validated['password']),
      ]);

      // Create company
      $company = Company::create([
        'name' => $validated['company_name'],
        'industry' => $validated['industry'] ?? null,
        'size' => $validated['company_size'] ?? null,
        'address' => $validated['address'] ?? null,
        'subscription_plan' => $validated['subscription_plan'],
        'subscription_expires_at' => now()->addMonth(), // 1 month trial
        'is_active' => true,
      ]);

      // Attach user to company as owner
      $user->companies()->attach($company->id, [
        'is_owner' => true,
        'joined_at' => now(),
      ]);

      // Set current company for user
      $user->update(['current_company_id' => $company->id]);

      // Assign role (you might want to create roles using Spatie Permission)
      // $user->assignRole('company_owner');

      event(new Registered($user));

      Auth::login($user);

      DB::commit();

      return to_route('dashboard')->with('success', 'Registration successful! Welcome to your billboard management dashboard.');

    } catch (\Exception $e) {
      DB::rollback();

      return back()->withErrors([
        'general' => 'An error occurred during registration. Please try again.'
      ])->withInput();
    }
  }
}
