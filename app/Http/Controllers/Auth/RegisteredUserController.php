<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

      Log::info('User created during registration', ['user_id' => $user->id, 'email' => $user->email]);

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

      Log::info('Company created during registration', ['company_id' => $company->id, 'name' => $company->name]);

      // Attach user to company as owner
      $user->companies()->attach($company->id, [
        'is_owner' => true,
        'joined_at' => now(),
        'role' => 'company_owner',
      ]);

      // Set current company for user
      $user->update(['current_company_id' => $company->id]);

      // Assign role using Spatie Permission (check if role exists first)
      if (\Spatie\Permission\Models\Role::where('name', 'company_owner')->exists()) {
        $user->assignRole('company_owner');
      } else {
        Log::warning('Role company_owner does not exist, skipping role assignment');
      }

      event(new Registered($user));

      Auth::login($user);

      DB::commit();

      Log::info('User registration completed successfully', ['user_id' => $user->id]);

      return to_route('dashboard')->with('success', 'Registration successful! Welcome to your billboard management dashboard.');

    } catch (\Exception $e) {
      DB::rollback();

      Log::error('Registration failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'email' => $request->email ?? 'unknown'
      ]);

      return back()->withErrors([
        'general' => 'An error occurred during registration. Please try again.'
      ])->withInput();
    }
  }

  /**
   * Validate step 1 (Personal Information) of the registration form.
   */
  public function validateStep1(Request $request): JsonResponse
  {
    try {
      $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
        'phone' => 'nullable|string|max:20',
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
      ]);

      return response()->json([
        'valid' => true,
        'message' => 'Step 1 validation successful'
      ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json([
        'valid' => false,
        'errors' => $e->errors(),
        'message' => 'Validation failed for step 1'
      ], 422);
    } catch (\Exception $e) {
      Log::error('Step 1 validation error', [
        'error' => $e->getMessage(),
        'email' => $request->email ?? 'unknown'
      ]);

      return response()->json([
        'valid' => false,
        'errors' => ['general' => ['An unexpected error occurred during validation']],
        'message' => 'Server error'
      ], 500);
    }
  }

  /**
   * Validate step 2 (Company Information) of the registration form.
   */
  public function validateStep2(Request $request): JsonResponse
  {
    try {
      $validated = $request->validate([
        'company_name' => 'required|string|max:255',
        'industry' => 'nullable|string|in:outdoor-advertising,marketing-agency,real-estate,retail,other',
        'company_size' => 'nullable|string|in:1-10,11-50,51-200,200+',
        'address' => 'nullable|string|max:1000',
      ]);

      return response()->json([
        'valid' => true,
        'message' => 'Step 2 validation successful'
      ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json([
        'valid' => false,
        'errors' => $e->errors(),
        'message' => 'Validation failed for step 2'
      ], 422);
    } catch (\Exception $e) {
      Log::error('Step 2 validation error', [
        'error' => $e->getMessage(),
        'company_name' => $request->company_name ?? 'unknown'
      ]);

      return response()->json([
        'valid' => false,
        'errors' => ['general' => ['An unexpected error occurred during validation']],
        'message' => 'Server error'
      ], 500);
    }
  }

  /**
   * Validate step 3 (Subscription Plan) of the registration form.
   */
  public function validateStep3(Request $request): JsonResponse
  {
    try {
      $validated = $request->validate([
        'subscription_plan' => 'required|string|in:starter,professional,enterprise',
      ]);

      return response()->json([
        'valid' => true,
        'message' => 'Step 3 validation successful'
      ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json([
        'valid' => false,
        'errors' => $e->errors(),
        'message' => 'Validation failed for step 3'
      ], 422);
    } catch (\Exception $e) {
      Log::error('Step 3 validation error', [
        'error' => $e->getMessage(),
        'subscription_plan' => $request->subscription_plan ?? 'unknown'
      ]);

      return response()->json([
        'valid' => false,
        'errors' => ['general' => ['An unexpected error occurred during validation']],
        'message' => 'Server error'
      ], 500);
    }
  }
}
