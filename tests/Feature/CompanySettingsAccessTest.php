<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CompanySettingsAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the roles that are needed for the tests
        Role::create(['name' => 'company_owner']);
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'editor']);
        Role::create(['name' => 'viewer']);
        Role::create(['name' => 'super_admin']);
    }

    public function test_company_owner_can_access_settings(): void
    {
        // Create a user and company
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'free']);

        // Attach user as company owner with proper pivot data
        $user->companies()->attach($company->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);
        $user->assignRole('company_owner');

        $this->actingAs($user);
        
        $response = $this->get(route('companies.settings.profile'));
        
        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('companies/settings/Profile')
            ->has('company')
            ->has('options')
        );
    }

    public function test_manager_can_access_settings(): void
    {
        // Create a user and company
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'free']);

        // Attach user as manager with proper pivot data
        $user->companies()->attach($company->id, [
            'is_owner' => false,
            'role' => 'manager',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);
        $user->assignRole('manager');

        $this->actingAs($user);
        
        $response = $this->get(route('companies.settings.profile'));
        
        $response->assertSuccessful();
    }

    public function test_viewer_cannot_access_settings(): void
    {
        // Create a user and company
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'free']);

        // Attach user as viewer with proper pivot data
        $user->companies()->attach($company->id, [
            'is_owner' => false,
            'role' => 'viewer',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);
        $user->assignRole('viewer');

        $this->actingAs($user);
        
        $response = $this->get(route('companies.settings.profile'));
        
        $response->assertForbidden();
    }

    public function test_editor_cannot_access_settings(): void
    {
        // Create a user and company
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'free']);

        // Attach user as editor with proper pivot data
        $user->companies()->attach($company->id, [
            'is_owner' => false,
            'role' => 'editor',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);
        $user->assignRole('editor');

        $this->actingAs($user);
        
        $response = $this->get(route('companies.settings.profile'));
        
        $response->assertForbidden();
    }

    public function test_user_without_company_access_cannot_access_settings(): void
    {
        // Create two companies and two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $company1 = Company::factory()->create(['subscription_plan' => 'free']);
        $company2 = Company::factory()->create(['subscription_plan' => 'free']);

        // User1 owns company1
        $user1->companies()->attach($company1->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);
        $user1->update(['current_company_id' => $company1->id]);
        $user1->assignRole('company_owner');

        // User2 owns company2
        $user2->companies()->attach($company2->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);
        $user2->update(['current_company_id' => $company2->id]);
        $user2->assignRole('company_owner');

        // User2 tries to access company1's settings (should fail)
        $user2->update(['current_company_id' => $company1->id]);
        
        $this->actingAs($user2);
        
        $response = $this->get(route('companies.settings.profile'));
        
        $response->assertForbidden();
    }
}
