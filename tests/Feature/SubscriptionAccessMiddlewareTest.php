<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriptionAccessMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the roles that are needed for the tests
        Role::create(['name' => 'company_owner']);

        // Seed plan feature rules for testing
        \DB::table('plan_feature_rules')->insert([
            'plan_id' => 'free',
            'key' => 'team.invitations',
            'value' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_middleware_redirects_with_helpful_message(): void
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

        // Create a test route that uses the middleware
        \Route::get('/test-subscription-access', function () {
            return response()->json(['success' => true]);
        })->middleware(['auth', 'subscription.access:team.invitations']);

        // Try to access the route
        $response = $this->actingAs($user)->get('/test-subscription-access');

        // Should be redirected with error message
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionHas('upgrade_required', true);
        $response->assertSessionHas('required_feature', 'team.invitations');
        $response->assertSessionHas('current_plan', 'free');
    }

    public function test_middleware_allows_access_with_proper_plan(): void
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

        // Add pro plan rule
        \DB::table('plan_feature_rules')->insert([
            'plan_id' => 'pro',
            'key' => 'team.invitations',
            'value' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a test route that uses the middleware
        \Route::get('/test-subscription-access-allowed', function () {
            return response()->json(['success' => true]);
        })->middleware(['auth', 'subscription.access:team.invitations']);

        // Try to access the route
        $response = $this->actingAs($user)->get('/test-subscription-access-allowed');

        // Should be allowed
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
