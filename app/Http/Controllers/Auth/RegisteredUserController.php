<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\SubscriptionPlan;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

use function count;

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
        $baseRules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        $companyRules = [
            'company_name' => 'required|string|max:255',
            'industry' => 'nullable|string|in:outdoor-advertising,marketing-agency,real-estate,retail,other',
            'company_size' => 'nullable|string|in:1-10,11-50,51-200,200+',
            'address' => 'nullable|string|max:1000',
            'subscription_plan' => ['required', 'string', Rule::in(SubscriptionPlan::values())],
        ];

        // Support two paths: simple user registration and company-inclusive registration
        $providedKeys = array_keys($request->all());
        $hasAnyBase = count(array_intersect($providedKeys, array_keys($baseRules))) > 0;
        $hasAnyCompany = count(array_intersect($providedKeys, array_keys($companyRules))) > 0;
        $emptyPayload = (! $hasAnyBase && ! $hasAnyCompany);
        $passwordMissing = ! $request->filled('password');
        // If payload is empty or password missing, validate full registration (include company fields)
        $isCompanyRegistration = $hasAnyCompany || $emptyPayload || $passwordMissing;

        $rules = $isCompanyRegistration ? ($baseRules + $companyRules) : $baseRules;
        $validated = $request->validate($rules);

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

            if ($isCompanyRegistration) {
                // Generate unique slug from company name
                // Create company with unique slug based on name
                $slug = Str::slug($validated['company_name']);
                if (Company::where('slug', $slug)->exists()) {
                    // Avoid insecure hashes; use a short random, lowercase-safe suffix
                    $slug = $slug.'-'.mb_strtolower(Str::random(6));
                }

                // Create company with resilient plan handling (SQLite enum constraints)
                try {
                    $company = Company::create([
                        'name' => $validated['company_name'],
                        'slug' => $slug,
                        'industry' => $validated['industry'] ?? null,
                        'size' => $validated['company_size'] ?? null,
                        'address' => $validated['address'] ?? null,
                        // Ensure scalar string is assigned; model casts to enum on read
                        'subscription_plan' => (string) $validated['subscription_plan'],
                        'subscription_expires_at' => now()->addMonth(), // 1 month trial
                        'is_active' => true,
                    ]);
                } catch (QueryException $qe) {
                    $msg = mb_strtolower($qe->getMessage());
                    $looksLikeEnumConstraint = str_contains($msg, 'constraint') || str_contains($msg, 'check constraint') || str_contains($msg, 'truncated');
                    if (! $looksLikeEnumConstraint) {
                        throw $qe;
                    }

                    // Map to legacy keys for environments where enum alteration isn't applied (e.g., sqlite tests)
                    $map = [
                        'free' => 'starter',
                        'pro' => 'professional',
                        'business' => 'enterprise',
                    ];
                    $fallbackPlan = $map[$validated['subscription_plan']] ?? $validated['subscription_plan'];

                    $company = Company::create([
                        'name' => $validated['company_name'],
                        'slug' => $slug,
                        'industry' => $validated['industry'] ?? null,
                        'size' => $validated['company_size'] ?? null,
                        'address' => $validated['address'] ?? null,
                        'subscription_plan' => $fallbackPlan,
                        'subscription_expires_at' => now()->addMonth(),
                        'is_active' => true,
                    ]);
                }

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
            }

            event(new Registered($user));

            Auth::login($user);

            DB::commit();

            Log::info('User registration completed successfully', ['user_id' => $user->id]);

            return to_route('dashboard')->with('success', 'Registration successful! Welcome to your billboard management dashboard.');

        } catch (Exception $e) {
            DB::rollback();

            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $request->email ?? 'unknown',
            ]);

            return back()->withErrors([
                'general' => 'An error occurred during registration. Please try again.',
            ])->withInput();
        }
    }
}
