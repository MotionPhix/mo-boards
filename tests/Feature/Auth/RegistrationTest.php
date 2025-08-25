<?php

use App\Models\User;
use App\Models\Company;
use App\Enums\SubscriptionPlan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\TestDatabaseSeeder;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Run migrations and seed test data
    $this->seed(TestDatabaseSeeder::class);

    // Set up storage and session
    Storage::fake('local');
    Session::start();
});

test('validates phone number format using Propaganistas/Laravel-Phone', function () {
    /** @var TestResponse */
    $response = $this->post(route('register.validate.step1'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => 'invalid-phone',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertInvalid(['phone' => 'Please provide a valid phone number']);
    expect(Session::has('registration.step1'))->toBeFalse();
})->group('registration');

test('completes registration step 1 with valid data', function () {
    /** @var TestResponse */
    $response = $this->post(route('register.validate.step1'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+12025550123',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertValid()
        ->assertRedirect(route('register.step2'));

    expect(Session::get('registration.step1'))->toHaveKeys(['name', 'email', 'phone', 'password']);
})->group('registration');

test('completes registration step 2 with valid data and logo', function () {
    // Set up step 1 data
    Session::put('registration.step1', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+12025550123',
        'password' => Hash::make('password'),
    ]);

    $logo = UploadedFile::fake()->image('company-logo.jpg');

    /** @var TestResponse */
    $response = $this->post(route('register.validate.step2'), [
        'company_name' => 'Test Company',
        'industry' => 'outdoor-advertising',
        'company_size' => '1-10',
        'address' => '123 Test St',
        'logo' => $logo,
    ]);

    $response->assertValid()
        ->assertRedirect(route('register.step3'));

    expect(Session::get('registration.step2'))->toHaveKeys(['company_name', 'industry', 'company_size', 'address']);
    expect(Session::get('registration.step2.temp_logo'))->toContain('temp/logos');

    // Verify the logo was stored temporarily
    expect(Storage::disk('local')->exists(Session::get('registration.step2.temp_logo')))->toBeTrue();
})->group('registration');

test('completes full registration process successfully', function () {
    // Set up session data for previous steps
    Session::put('registration.step1', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+12025550123',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $logo = UploadedFile::fake()->image('company-logo.jpg');
    $tempPath = Storage::disk('local')->putFile('temp/logos', $logo);

    Session::put('registration.step2', [
        'company_name' => 'Test Company',
        'industry' => 'outdoor-advertising',
        'company_size' => '1-10',
        'address' => '123 Test St',
        'temp_logo' => $tempPath,
    ]);

    /** @var TestResponse */
    $response = $this->post(route('register.store'), [
        'subscription_plan' => SubscriptionPlan::PRO->value,
        'password_confirmation' => 'password',
    ]);

    $response->assertValid()
        ->assertRedirect(route('dashboard'));

    // Assert user was created with correct data
    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Test User')
        ->and($user->phone)->toBe('+12025550123')
        ->and($user->current_company_id)->not->toBeNull();

    // Assert company was created with correct data
    $company = $user->currentCompany;
    expect($company)->not->toBeNull()
        ->and($company->name)->toBe('Test Company')
        ->and($company->industry)->toBe('outdoor-advertising')
        ->and($company->size)->toBe('1-10')
        ->and($company->subscription_plan)->toBe(SubscriptionPlan::PRO->value)
        ->and($company->getFirstMedia('company_logo'))->not->toBeNull()
        ->and($company->is_active)->toBeTrue();

    // Assert user role is set correctly
    expect($user->companies()->first()->pivot->role)->toBe('owner');

    // Assert user is authenticated
    expect(auth()->check())->toBeTrue()
        ->and(auth()->id())->toBe($user->id);

    // Assert registration session data was cleared
    expect(Session::has('registration.step1'))->toBeFalse()
        ->and(Session::has('registration.step2'))->toBeFalse();

    // Assert temp logo was cleaned up
    expect(Storage::disk('local')->exists($tempPath))->toBeFalse();
})->group('registration');

test('requires all steps to be completed before final registration', function () {
    /** @var TestResponse */
    $response = $this->post(route('register.store'), [
        'subscription_plan' => SubscriptionPlan::PRO->value,
    ]);

    $response->assertRedirect(route('register'))
        ->assertSessionHasErrors('general');

    expect(User::count())->toBe(0)
        ->and(Company::count())->toBe(0);
})->group('registration');

afterEach(function () {
    // Clean up any temporary files
    Storage::disk('local')->deleteDirectory('temp/logos');

    // Clear session data
    Session::flush();
});
