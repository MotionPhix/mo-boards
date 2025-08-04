<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class CompanyRegistrationTestFixed extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles for tests
        Role::create(['name' => 'company_owner']);
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'editor']);
        Role::create(['name' => 'viewer']);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('auth/Register')
        );
    }

    public function test_new_user_can_register_with_company_information(): void
    {
        $registrationData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'company_name' => 'Acme Advertising',
            'industry' => 'outdoor-advertising',
            'company_size' => '11-50',
            'address' => '123 Main St, City, State 12345',
            'subscription_plan' => 'professional',
        ];

        $response = $this->post('/register', $registrationData);

        // Assert user was created
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
        ]);

        // Assert company was created
        $this->assertDatabaseHas('companies', [
            'name' => 'Acme Advertising',
            'industry' => 'outdoor-advertising',
            'size' => '11-50',
            'address' => '123 Main St, City, State 12345',
            'subscription_plan' => 'professional',
            'is_active' => true,
        ]);

        // Assert user is attached to company as owner
        $user = User::where('email', 'john@example.com')->first();
        $company = Company::where('name', 'Acme Advertising')->first();

        $this->assertDatabaseHas('company_user', [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'is_owner' => true,
            'role' => 'company_owner',
        ]);

        // Assert user's current company is set
        $this->assertEquals($company->id, $user->current_company_id);

        // Assert user has company_owner role
        $this->assertTrue($user->hasRole('company_owner'));

        // Assert user is authenticated
        $this->assertAuthenticated();

        // Assert redirect to dashboard
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
    }

    public function test_registration_validates_company_name_length(): void
    {
        // Test validation error handling with invalid company name length
        $registrationData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'company_name' => str_repeat('A', 300), // Exceeds typical varchar(255) limit
            'subscription_plan' => 'starter',
        ];

        $response = $this->post('/register', $registrationData);

        // Assert user was not created due to validation error
        $this->assertDatabaseMissing('users', [
            'email' => 'john@example.com',
        ]);

        // Assert company was not created
        $this->assertDatabaseMissing('companies', [
            'name' => str_repeat('A', 300),
        ]);

        // Assert user is not authenticated
        $this->assertGuest();

        // Assert validation error is shown for company_name
        $response->assertSessionHasErrors(['company_name']);
    }

    public function test_registration_triggers_registered_event(): void
    {
        Event::fake();

        $registrationData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'company_name' => 'Acme Advertising',
            'subscription_plan' => 'starter',
        ];

        $this->post('/register', $registrationData);

        Event::assertDispatched(\Illuminate\Auth\Events\Registered::class);
    }

    public function test_registration_requires_all_mandatory_fields(): void
    {
        $response = $this->post('/register', []);

        $response->assertSessionHasErrors([
            'name',
            'email',
            'password',
            'company_name',
            'subscription_plan',
        ]);
    }

    public function test_registration_validates_email_format(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'company_name' => 'Acme Advertising',
            'subscription_plan' => 'starter',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_registration_validates_unique_email(): void
    {
        // Create existing user
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'company_name' => 'Acme Advertising',
            'subscription_plan' => 'starter',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_registration_validates_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'DifferentPassword123!',
            'company_name' => 'Acme Advertising',
            'subscription_plan' => 'starter',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_registration_encrypts_password(): void
    {
        $registrationData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'company_name' => 'Acme Advertising',
            'subscription_plan' => 'starter',
        ];

        $this->post('/register', $registrationData);

        $user = User::where('email', 'john@example.com')->first();

        // Assert password is hashed
        $this->assertNotEquals('SecurePassword123!', $user->password);
        $this->assertTrue(Hash::check('SecurePassword123!', $user->password));
    }

    public function test_registration_with_all_subscription_plans(): void
    {
        $plans = ['starter', 'professional', 'enterprise'];

        foreach ($plans as $index => $plan) {
            $registrationData = [
                'name' => "User {$index}",
                'email' => "user{$index}@example.com",
                'password' => 'SecurePassword123!',
                'password_confirmation' => 'SecurePassword123!',
                'company_name' => "Company {$index}",
                'subscription_plan' => $plan,
            ];

            $response = $this->post('/register', $registrationData);

            $this->assertDatabaseHas('companies', [
                'name' => "Company {$index}",
                'subscription_plan' => $plan,
            ]);

            $response->assertRedirect(route('dashboard'));

            // Logout for next iteration
            auth()->logout();
        }
    }
}
