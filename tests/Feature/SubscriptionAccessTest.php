<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriptionAccessTest extends TestCase
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

        // Seed plan feature rules for testing
        $this->seedPlanFeatureRules();
    }

    private function seedPlanFeatureRules(): void
    {
        $rules = [
            // Free plan
            ['plan_id' => 'free', 'key' => 'team.invitations', 'value' => '0'],
            ['plan_id' => 'free', 'key' => 'export.enabled', 'value' => '0'],

            // Pro plan
            ['plan_id' => 'pro', 'key' => 'team.invitations', 'value' => '1'],
            ['plan_id' => 'pro', 'key' => 'export.enabled', 'value' => '0'],

            // Business plan
            ['plan_id' => 'business', 'key' => 'team.invitations', 'value' => '1'],
            ['plan_id' => 'business', 'key' => 'export.enabled', 'value' => '1'],
        ];

        foreach ($rules as $rule) {
            \DB::table('plan_feature_rules')->insert(array_merge($rule, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function test_free_plan_user_can_access_team_page_but_cannot_invite(): void
    {
        // Create a user and company on free plan
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'free']);

        // Attach user as company owner
        $user->companies()->attach($company->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);
        $user->assignRole('company_owner');

        // Should be able to access team page
        $response = $this->actingAs($user)->get('/team');
        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('team/Index')
                ->where('userPermissions.canInviteUsers', false) // Should be false on free plan
        );
    }

    public function test_free_plan_user_cannot_invite_team_members(): void
    {
        // Create a user and company on free plan
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'free']);

        // Attach user as company owner
        $user->companies()->attach($company->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);
        $user->assignRole('company_owner');

        // Try to invite a team member
        $response = $this->actingAs($user)->post('/team/invite', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'editor',
        ]);

        // Should be forbidden due to middleware
        $response->assertStatus(403);
    }

    public function test_pro_plan_user_can_invite_team_members(): void
    {
        // Create a user and company on pro plan
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'pro']);

        // Attach user as company owner
        $user->companies()->attach($company->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);
        $user->assignRole('company_owner');

        // Should be able to access team page with invite permissions
        $response = $this->actingAs($user)->get('/team');
        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('team/Index')
                ->where('userPermissions.canInviteUsers', true) // Should be true on pro plan
        );
    }

    public function test_business_plan_user_has_unlimited_team_access(): void
    {
        // Create a user and company on business plan
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'business']);

        // Attach user as company owner
        $user->companies()->attach($company->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);
        $user->assignRole('company_owner');

        // Should be able to access team page with full permissions
        $response = $this->actingAs($user)->get('/team');
        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('team/Index')
                ->where('userPermissions.canInviteUsers', true) // Should be true on business plan
        );
    }

    public function test_export_feature_subscription_restrictions(): void
    {
        // Test free plan - should not allow export
        $freeUser = User::factory()->create();
        $freeCompany = Company::factory()->create(['subscription_plan' => 'free']);
        $freeUser->companies()->attach($freeCompany->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);
        $freeUser->update(['current_company_id' => $freeCompany->id]);
        $freeUser->assignRole('company_owner');

        $freePlanAllowed = \App\Services\Billing\PlanGate::allows('free', 'export.enabled', false);
        $this->assertFalse($freePlanAllowed, 'Free plan should not allow export feature');

        // Test business plan - should allow export
        $businessUser = User::factory()->create();
        $businessCompany = Company::factory()->create(['subscription_plan' => 'business']);
        $businessUser->companies()->attach($businessCompany->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);
        $businessUser->update(['current_company_id' => $businessCompany->id]);
        $businessUser->assignRole('company_owner');

        $businessPlanAllowed = \App\Services\Billing\PlanGate::allows('business', 'export.enabled', false);
        $this->assertTrue($businessPlanAllowed, 'Business plan should allow export feature');

        // Test authorization
        $this->assertTrue($businessUser->can('exportData', \App\Models\Billboard::class),
            'Business plan user should be able to export data');
    }
}
