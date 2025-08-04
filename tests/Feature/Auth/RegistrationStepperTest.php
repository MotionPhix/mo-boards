<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class RegistrationStepperTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_form_renders_with_step_1_by_default(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('auth/Register')
        );
    }

    public function test_step_1_validates_personal_information_fields(): void
    {
        // Test with empty fields
        $response = $this->post('/register/validate-step-1', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name',
            'email',
            'password',
        ]);
    }

    public function test_step_1_validates_email_format(): void
    {
        $response = $this->post('/register/validate-step-1', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_step_1_validates_password_confirmation(): void
    {
        $response = $this->post('/register/validate-step-1', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_step_1_validates_password_strength(): void
    {
        $response = $this->post('/register/validate-step-1', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_step_1_passes_with_valid_data(): void
    {
        $response = $this->post('/register/validate-step-1', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['valid' => true]);
    }

    public function test_step_2_validates_company_information_fields(): void
    {
        $response = $this->post('/register/validate-step-2', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['company_name']);
    }

    public function test_step_2_validates_industry_options(): void
    {
        $response = $this->post('/register/validate-step-2', [
            'company_name' => 'Acme Corp',
            'industry' => 'invalid-industry',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['industry']);
    }

    public function test_step_2_validates_company_size_options(): void
    {
        $response = $this->post('/register/validate-step-2', [
            'company_name' => 'Acme Corp',
            'company_size' => 'invalid-size',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['company_size']);
    }

    public function test_step_2_validates_address_length(): void
    {
        $response = $this->post('/register/validate-step-2', [
            'company_name' => 'Acme Corp',
            'address' => str_repeat('A', 1001),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address']);
    }

    public function test_step_2_passes_with_valid_data(): void
    {
        $response = $this->post('/register/validate-step-2', [
            'company_name' => 'Acme Advertising',
            'industry' => 'outdoor-advertising',
            'company_size' => '11-50',
            'address' => '123 Main St, City, State 12345',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['valid' => true]);
    }

    public function test_step_2_passes_with_minimal_data(): void
    {
        $response = $this->post('/register/validate-step-2', [
            'company_name' => 'Acme Corp',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['valid' => true]);
    }

    public function test_step_3_validates_subscription_plan(): void
    {
        $response = $this->post('/register/validate-step-3', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['subscription_plan']);
    }

    public function test_step_3_validates_subscription_plan_options(): void
    {
        $response = $this->post('/register/validate-step-3', [
            'subscription_plan' => 'invalid-plan',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['subscription_plan']);
    }

    public function test_step_3_passes_with_valid_subscription_plan(): void
    {
        $validPlans = ['starter', 'professional', 'enterprise'];

        foreach ($validPlans as $plan) {
            $response = $this->post('/register/validate-step-3', [
                'subscription_plan' => $plan,
            ]);

            $response->assertStatus(200);
            $response->assertJson(['valid' => true]);
        }
    }

    public function test_complete_registration_validates_all_steps(): void
    {
        // Test with incomplete data
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            // Missing password, company_name, subscription_plan
        ]);

        $response->assertSessionHasErrors([
            'password',
            'company_name',
            'subscription_plan',
        ]);
    }

    public function test_registration_preserves_form_data_on_validation_errors(): void
    {
        $formData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'company_name' => 'Acme Corp',
            'industry' => 'outdoor-advertising',
            'company_size' => '11-50',
            'address' => '123 Main St',
            // Missing required fields to trigger validation errors
        ];

        $response = $this->post('/register', $formData);

        $response->assertSessionHasInput([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'company_name' => 'Acme Corp',
            'industry' => 'outdoor-advertising',
            'company_size' => '11-50',
            'address' => '123 Main St',
        ]);
    }

    public function test_registration_does_not_preserve_sensitive_data_on_errors(): void
    {
        $formData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'company_name' => 'Acme Corp',
            // Missing subscription_plan to trigger validation error
        ];

        $response = $this->post('/register', $formData);

        // Assert password fields are not preserved in session
        $response->assertSessionMissing('password');
        $response->assertSessionMissing('password_confirmation');
    }

    public function test_step_validation_endpoints_require_specific_fields(): void
    {
        // Test that step 1 validation doesn't require company fields
        $response = $this->post('/register/validate-step-1', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            // No company fields
        ]);

        $response->assertStatus(200);

        // Test that step 2 validation doesn't require personal fields
        $response = $this->post('/register/validate-step-2', [
            'company_name' => 'Acme Corp',
            // No personal fields
        ]);

        $response->assertStatus(200);

        // Test that step 3 validation doesn't require other fields
        $response = $this->post('/register/validate-step-3', [
            'subscription_plan' => 'starter',
            // No other fields
        ]);

        $response->assertStatus(200);
    }

    public function test_step_validation_returns_appropriate_error_messages(): void
    {
        $response = $this->post('/register/validate-step-1', [
            'email' => 'invalid-email',
            'password' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
                'email',
                'password',
            ]
        ]);
    }

    public function test_step_validation_handles_optional_fields_correctly(): void
    {
        // Test step 1 with optional phone field
        $response = $this->post('/register/validate-step-1', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'phone' => '', // Empty optional field
        ]);

        $response->assertStatus(200);

        // Test step 2 with optional fields
        $response = $this->post('/register/validate-step-2', [
            'company_name' => 'Acme Corp',
            'industry' => '', // Empty optional field
            'company_size' => '', // Empty optional field
            'address' => '', // Empty optional field
        ]);

        $response->assertStatus(200);
    }
}
