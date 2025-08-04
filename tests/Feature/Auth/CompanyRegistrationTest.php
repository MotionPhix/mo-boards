<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class CompanyRegistrationTest extends TestCase
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

    public function test_registration_validates_industry_options(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'company_name' => 'Acme Advertising',
            'industry' => 'invalid-industry',
            'subscription_plan' => 'starter',
        ]);

        $response->assertSessionHasErrors(['industry']);
    }

    public function test_registration_validates_company_size_options(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'company_name' => 'Acme Advertising',
            'company_size' => 'invalid-size',
            'subscription_plan' => 'starter',
        ]);

        $response->assertSessionHasErrors(['company_size']);
    }

    public function test_registration_validates_subscription_plan_options(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'company_name' => 'Acme Advertising',
            'subscription_plan' => 'invalid-plan',
        ]);

        $response->assertSessionHasErrors(['subscription_plan']);
    }

    public function test_registration_with_minimal_required_data(): void
    {
        $registrationData = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'company_name' => 'Smith Corp',
            'subscription_plan' => 'starter',
        ];

        $response = $this->post('/register', $registrationData);

        // Assert user was created
        $this->assertDatabaseHas('users', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => null,
        ]);

        // Assert company was created with minimal data
        $this->assertDatabaseHas('companies', [
            'name' => 'Smith Corp',
            'industry' => null,
            'size' => null,
            'address' => null,
            'subscription_plan' => 'starter',
            'is_active' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    public function test_registration_sets_subscription_expiry_date(): void
    {
        $registrationData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'company_name' => 'Acme Advertising',
            'subscription_plan' => 'professional',
        ];

        $this->post('/register', $registrationData);

        $company = Company::where('name', 'Acme Advertising')->first();

        // Assert subscription expires approximately 1 month from now
        $this->assertNotNull($company->subscription_expires_at);
        $this->assertTrue(
            $company->subscription_expires_at->between(
                now()->addMonth()->subMinute(),
                now()->addMonth()->addMinute()
            )
        );
    }

    public function test_registration_handles_database_transaction_rollback_on_error(): void
    {
        // Mock a scenario where company creation might fail
        // by using an extremely long company name that exceeds database limits
        $registrationData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'company_name' => str_repeat('A', 300), // Exceeds typical varchar(255) limit
            'subscription_plan' => 'starter',
        ];

        $response = $this->post('/register', $registrationData);

        // Assert user was not created due to transaction rollback
        $this->assertDatabaseMissing('users', [
            'email' => 'john@example.com',
        ]);

        // Assert company was not created
        $this->assertDatabaseMissing('companies', [
            'name' => str_repeat('A', 300),
        ]);

        // Assert user is not authenticated
        $this->assertGuest();

        // Assert error message is shown
        $response->assertSessionHasErrors(['general']);
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

    public function test_registration_validates_phone_number_format(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => str_repeat('1', 25), // Too long
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'company_name' => 'Acme Advertising',
            'subscription_plan' => 'starter',
        ]);

        $response->assertSessionHasErrors(['phone']);
    }

    public function test_registration_validates_address_length(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'company_name' => 'Acme Advertising',
            'address' => str_repeat('A', 1001), // Exceeds 1000 character limit
            'subscription_plan' => 'starter',
        ]);

        $response->assertSessionHasErrors(['address']);
    }

    public function test_registration_triggers_registered_event(): void
    {
        $this->expectsEvents(\Illuminate\Auth\Events\Registered::class);

        $registrationData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'company_name' => 'Acme Advertising',
            'subscription_plan' => 'starter',
        ];

        $this->post('/register', $registrationData);
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

    public function test_registration_with_all_industry_options(): void
    {
        $industries = ['outdoor-advertising', 'marketing-agency', 'real-estate', 'retail', 'other'];

        foreach ($industries as $index => $industry) {
            $registrationData = [
                'name' => "User {$index}",
                'email' => "user{$index}@example.com",
                'password' => 'SecurePassword123!',
                'password_confirmation' => 'SecurePassword123!',
                'company_name' => "Company {$index}",
                'industry' => $industry,
                'subscription_plan' => 'starter',
            ];

            $response = $this->post('/register', $registrationData);

            $this->assertDatabaseHas('companies', [
                'name' => "Company {$index}",
                'industry' => $industry,
            ]);

            $response->assertRedirect(route('dashboard'));

            // Logout for next iteration
            auth()->logout();
        }
    }

    public function test_registration_with_all_company_size_options(): void
    {
        $sizes = ['1-10', '11-50', '51-200', '200+'];

        foreach ($sizes as $index => $size) {
            $registrationData = [
                'name' => "User {$index}",
                'email' => "user{$index}@example.com",
                'password' => 'SecurePassword123!',
                'password_confirmation' => 'SecurePassword123!',
                'company_name' => "Company {$index}",
                'company_size' => $size,
                'subscription_plan' => 'starter',
            ];

            $response = $this->post('/register', $registrationData);

            $this->assertDatabaseHas('companies', [
                'name' => "Company {$index}",
                'size' => $size,
            ]);

            $response->assertRedirect(route('dashboard'));

            // Logout for next iteration
            auth()->logout();
        }
    }
}
