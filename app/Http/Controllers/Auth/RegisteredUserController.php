<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\SubscriptionPlan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterStep1Request;
use App\Http\Requests\Auth\RegisterStep2Request;
use App\Http\Responses\ValidationErrorResponse;
use App\Models\Company;
use App\Models\User;
use App\Services\PlanService;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\Rules\Phone;

final class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly PlanService $planService,
        private readonly ValidationErrorResponse $validationResponse
    ) {}

    /**
     * Show step 1 of registration (basic info).
     */
    public function create(Request $request): Response
    {
        $data = $request->only([
            'name', 'email', 'phone', 'password', 'password_confirmation',
            'company_name', 'industry', 'company_size', 'address', 'subscription_plan'
        ]);

        return Inertia::render('auth/Register', [
            'step' => 1,
            'data' => $data
        ]);
    }

    /**
     * Validate step 1 of registration.
     */
    public function validateStep1(RegisterStep1Request $request): RedirectResponse
    {
        $validated = $request->validated();
        // Preserve password_confirmation in session for the final 'confirmed' validation
        $validated['password_confirmation'] = $request->input('password_confirmation');
        Session::put('registration.step1', $validated);

        return redirect()->route('register.step2');
    }

    /**
     * Show step 2 of registration (company info).
     */
    public function showStep2(): Response|RedirectResponse
    {
        if (!Session::has('registration.step1')) {
            return to_route('register');
        }

        return Inertia::render('auth/Register', [
            'step' => 2,
            'data' => Session::get('registration.step2', [])
        ]);
    }

    /**
     * Validate step 2 of registration.
     */
    public function validateStep2(RegisterStep2Request $request): RedirectResponse
    {
        $validated = $request->safe()->except(['logo']);

        // Store validated data in session
        Session::put('registration.step2', $validated);

        // Handle logo upload separately
        if ($request->hasFile('logo')) {
            // Store the file temporarily and save the path in session
            $path = $request->file('logo')->store('temp/logos', 'local');
            Session::put('registration.step2.temp_logo', $path);
        }

        return to_route('register.step3');
    }

    /**
     * Show step 3 of registration (plan selection).
     */
    public function createPlan(Request $request): Response|RedirectResponse
    {
        if (!Session::has('registration.step2')) {
            return to_route('register.step2');
        }

        $data = $request->only([
            'name', 'email', 'phone', 'password', 'password_confirmation',
            'company_name', 'industry', 'company_size', 'address', 'subscription_plan'
        ]);

        return Inertia::render('auth/Register', [
            'step' => 3,
            'data' => $data,
            'plans' => $this->planService->getPlans()
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Ensure all steps are completed
            if (!Session::has('registration.step1') || !Session::has('registration.step2')) {
                Log::warning('Registration attempt without completing all steps');
                return redirect()->route('register')->withErrors([
                    'general' => 'Please complete all registration steps.',
                ]);
            }

            // Merge all registration data
            $mergeExtras = [
                'subscription_plan' => $request->input('subscription_plan'),
            ];

            // Only override password_confirmation if provided in this request.
            // Otherwise keep the value from step 1 so the 'confirmed' rule passes.
            if ($request->filled('password_confirmation')) {
                $mergeExtras['password_confirmation'] = $request->input('password_confirmation');
            }

            $registrationData = array_merge(
                Session::get('registration.step1', []),
                Session::get('registration.step2', []),
                $mergeExtras
            );

            // Defensive: ensure password_confirmation is present for the 'confirmed' rule
            if (!array_key_exists('password_confirmation', $registrationData) || $registrationData['password_confirmation'] === null || $registrationData['password_confirmation'] === '') {
                $pc = data_get(Session::get('registration.step1', []), 'password_confirmation');
                if ($pc !== null && $pc !== '') {
                    $registrationData['password_confirmation'] = $pc;
                }
            }

            Log::debug('Registration data:', array_merge(
                $registrationData,
                ['password' => '******', 'password_confirmation' => '******']
            ));

            // Revalidate all data as a final check
            $validator = Validator::make($registrationData, [
                'name' => 'required|string|max:255',
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
                'phone' => ['required', new Phone],
                'password' => ['required', Rules\Password::defaults()],
                'password_confirmation' => ['sometimes', 'same:password'],
                'company_name' => 'required|string|max:255',
                'industry' => 'nullable|string|in:outdoor-advertising,marketing-agency,real-estate,retail,other',
                'company_size' => 'nullable|string|in:1-10,11-50,51-200,200+',
                'address' => 'nullable|string|max:1000',
                'subscription_plan' => ['required', 'string', Rule::in(SubscriptionPlan::values())],
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                Log::warning('Registration validation failed', ['errors' => $validator->errors()->toArray()]);
                return back()->withErrors($validator)->withInput();
            }

            $validated = $validator->validated();

            try {
                // Create user
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'password' => Hash::make($validated['password']),
                ]);

                Log::info('User created during registration', ['user_id' => $user->id, 'email' => $user->email]);

                // Generate unique slug from company name
                $slug = Str::slug($validated['company_name']);
                if (Company::where('slug', $slug)->exists()) {
                    $slug = $slug.'-'.mb_strtolower(Str::random(6));
                }

                // Create company
                $trialDays = (int) config('billing.trial_days', 7);
                $trialEndsAt = now()->addDays($trialDays);

                $company = Company::create([
                    'name' => $validated['company_name'],
                    'slug' => $slug,
                    'industry' => $validated['industry'] ?? null,
                    'size' => $validated['company_size'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'subscription_plan' => $validated['subscription_plan'],
                    'subscription_expires_at' => $validated['subscription_plan'] === 'free' ? null : $trialEndsAt,
                    'is_active' => true,
                ]);

                // Handle logo upload if exists in session
                if (Session::has('registration.step2.temp_logo')) {
                    $tempLogoPath = Session::get('registration.step2.temp_logo');
                    if (Storage::disk('local')->exists($tempLogoPath)) {
                        $company->addMediaFromDisk($tempLogoPath, 'local')
                            ->toMediaCollection('company_logo');
                        Storage::disk('local')->delete($tempLogoPath);
                    }
                }

                // Associate user with company and set as current company
                $user->companies()->attach($company->id, ['role' => 'owner']);
                $user->assignRole('company_owner');
                $user->update(['current_company_id' => $company->id]);

                event(new Registered($user));
                Auth::login($user);

                DB::commit();

                // Clear registration session data
                Session::forget(['registration.step1', 'registration.step2']);

                return redirect()->route('dashboard');

            } catch (QueryException $qe) {
                DB::rollBack();
                Log::error('Failed to create company during registration', [
                    'error' => $qe->getMessage(),
                    'user_id' => $user->id ?? null,
                    'trace' => $qe->getTraceAsString(),
                ]);
                throw $qe;
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['general' => 'Registration failed. Please try again.']);
        }
    }
}
