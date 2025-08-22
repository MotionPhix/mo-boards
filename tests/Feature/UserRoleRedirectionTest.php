<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRoleRedirectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the roles that are needed for the tests
        Role::create(['name' => 'super_admin']);
        Role::create(['name' => 'company_owner']);
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'editor']);
        Role::create(['name' => 'viewer']);
    }

    public function test_company_owner_is_redirected_to_company_owner_dashboard(): void
    {
        // Create a user and company
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'free']);

        // Attach user as company owner with proper role
        $user->companies()->attach($company->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);
        $user->assignRole('company_owner');

        // Act as the user and visit dashboard
        $response = $this->actingAs($user)->get('/dashboard');

        // Assert the correct component is rendered
        $response->assertInertia(fn ($page) =>
            $page->component('dashboard/CompanyOwner')
        );
    }

    public function test_manager_is_redirected_to_manager_dashboard(): void
    {
        // Create a user and company
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'pro']);

        // Attach user as manager
        $user->companies()->attach($company->id, [
            'is_owner' => false,
            'role' => 'manager',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);
        $user->assignRole('manager');

        // Act as the user and visit dashboard
        $response = $this->actingAs($user)->get('/dashboard');

        // Assert the correct component is rendered
        $response->assertInertia(fn ($page) =>
            $page->component('dashboard/Manager')
        );
    }

    public function test_editor_is_redirected_to_editor_dashboard(): void
    {
        // Create a user and company
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'pro']);

        // Attach user as editor
        $user->companies()->attach($company->id, [
            'is_owner' => false,
            'role' => 'editor',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);
        $user->assignRole('editor');

        // Act as the user and visit dashboard
        $response = $this->actingAs($user)->get('/dashboard');

        // Assert the correct component is rendered
        $response->assertInertia(fn ($page) =>
            $page->component('dashboard/Editor')
        );
    }

    public function test_viewer_is_redirected_to_viewer_dashboard(): void
    {
        // Create a user and company
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'free']);

        // Attach user as viewer
        $user->companies()->attach($company->id, [
            'is_owner' => false,
            'role' => 'viewer',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);
        $user->assignRole('viewer');

        // Act as the user and visit dashboard
        $response = $this->actingAs($user)->get('/dashboard');

        // Assert the correct component is rendered
        $response->assertInertia(fn ($page) =>
            $page->component('dashboard/Viewer')
        );
    }

    public function test_user_with_null_role_but_is_owner_gets_company_owner_dashboard(): void
    {
        // Create a user and company (simulating the current issue)
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'free']);

        // Attach user as owner but with null role (current issue)
        $user->companies()->attach($company->id, [
            'is_owner' => true,
            'role' => null,
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);

        // Act as the user and visit dashboard
        $response = $this->actingAs($user)->get('/dashboard');

        // Should be redirected to CompanyOwner dashboard, not Viewer
        $response->assertInertia(fn ($page) =>
            $page->component('dashboard/CompanyOwner')
        );
    }

    public function test_user_with_null_role_and_not_owner_gets_viewer_dashboard(): void
    {
        // Create a user and company
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'free']);

        // Attach user with null role and not owner
        $user->companies()->attach($company->id, [
            'is_owner' => false,
            'role' => null,
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);

        // Act as the user and visit dashboard
        $response = $this->actingAs($user)->get('/dashboard');

        // Should default to Viewer dashboard
        $response->assertInertia(fn ($page) =>
            $page->component('dashboard/Viewer')
        );
    }
}
