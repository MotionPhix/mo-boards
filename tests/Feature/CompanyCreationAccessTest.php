<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CompanyCreationAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the roles that are needed for the tests
        Role::create(['name' => 'company_owner']);

        // Seed plan feature rules for testing
        $this->seedPlanFeatureRules();
    }

    private function seedPlanFeatureRules(): void
    {
        $rules = [
            // Free plan - 1 company only
            ['plan_id' => 'free', 'key' => 'companies.max', 'value' => '1'],

            // Pro plan - up to 3 companies
            ['plan_id' => 'pro', 'key' => 'companies.max', 'value' => '3'],

            // Business plan - unlimited companies
            ['plan_id' => 'business', 'key' => 'companies.max', 'value' => 'unlimited'],
        ];

        foreach ($rules as $rule) {
            \DB::table('plan_feature_rules')->updateOrInsert(
                ['plan_id' => $rule['plan_id'], 'key' => $rule['key']],
                array_merge($rule, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function test_free_plan_user_can_create_first_company(): void
    {
        // Create a user with no companies
        $user = User::factory()->create();

        // Should be able to create first company
        $response = $this->actingAs($user)->get('/companies/create');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('companies/CreateModal'));
    }

    public function test_free_plan_user_cannot_create_second_company(): void
    {
        // Create a user with one company on free plan
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'free']);

        // Attach user as company owner
        $user->companies()->attach($company->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);

        $user->assignRole('company_owner');

        // Try to create another company
        $response = $this->actingAs($user)->get('/companies/create');

        // Should be redirected with error
        $response->assertRedirect('/companies');
        $response->assertSessionHas('error');
        $response->assertSessionHas('upgrade_required', true);
    }

    public function test_pro_plan_user_can_create_up_to_three_companies(): void
    {
        // Create a user with two companies on pro plan
        $user = User::factory()->create();

        for ($i = 0; $i < 2; $i++) {
            $company = Company::factory()->create(['subscription_plan' => 'pro']);
            $user->companies()->attach($company->id, [
                'is_owner' => true,
                'role' => 'company_owner',
                'joined_at' => now(),
            ]);
        }

        $user->assignRole('company_owner');

        // Should be able to create third company
        $response = $this->actingAs($user)->get('/companies/create');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('companies/CreateModal'));
    }

    public function test_pro_plan_user_cannot_create_fourth_company(): void
    {
        // Create a user with three companies on pro plan
        $user = User::factory()->create();

        for ($i = 0; $i < 3; $i++) {
            $company = Company::factory()->create(['subscription_plan' => 'pro']);
            $user->companies()->attach($company->id, [
                'is_owner' => true,
                'role' => 'company_owner',
                'joined_at' => now(),
            ]);
        }

        $user->assignRole('company_owner');

        // Try to create fourth company
        $response = $this->actingAs($user)->get('/companies/create');

        // Should be redirected with error
        $response->assertRedirect('/companies');
        $response->assertSessionHas('error');
        $response->assertSessionHas('upgrade_required', true);
    }

    public function test_business_plan_user_can_create_unlimited_companies(): void
    {
        // Create a user with five companies on business plan
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $company = Company::factory()->create(['subscription_plan' => 'business']);
            $user->companies()->attach($company->id, [
                'is_owner' => true,
                'role' => 'company_owner',
                'joined_at' => now(),
            ]);
        }

        $user->assignRole('company_owner');

        // Should still be able to create more companies
        $response = $this->actingAs($user)->get('/companies/create');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('companies/CreateModal'));
    }

    public function test_company_creation_post_request_respects_limits(): void
    {
        // Create a user with one company on free plan
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'free']);

        $user->companies()->attach($company->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);

        $user->assignRole('company_owner');

        // Try to POST create another company
        $response = $this->actingAs($user)->post('/companies', [
            'name' => 'Second Company',
            'industry' => 'Technology',
            'size' => 'small',
        ]);

        // Should be redirected with error
        $response->assertRedirect('/companies');
        $response->assertSessionHas('error');
        $response->assertSessionHas('upgrade_required', true);
    }

    public function test_subscription_data_includes_company_limits(): void
    {
        // Create a user with one company on pro plan
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'pro']);

        $user->companies()->attach($company->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);
        $user->assignRole('company_owner');

        // Get any page to check shared subscription data
        $response = $this->actingAs($user)->get('/companies');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('subscription.companies.current')
                ->has('subscription.companies.limit')
                ->has('subscription.companies.can_create_more')
                ->where('subscription.companies.current', 1)
                ->where('subscription.companies.limit', '3')
                ->where('subscription.companies.can_create_more', true)
        );
    }
}
