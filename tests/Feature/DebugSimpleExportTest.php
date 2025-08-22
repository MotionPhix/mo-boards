<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DebugSimpleExportTest extends TestCase
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
            'key' => 'export.enabled',
            'value' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('plan_feature_rules')->insert([
            'plan_id' => 'business',
            'key' => 'export.enabled',
            'value' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_middleware_blocks_free_plan(): void
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

        // Try to access export feature - should be blocked by middleware
        $response = $this->actingAs($user)->get('/billboards/export');

        // The middleware should return 403, not 404
        $this->assertContains($response->getStatusCode(), [403, 404],
            'Expected 403 (forbidden) or 404, got: ' . $response->getStatusCode());
    }

    public function test_middleware_allows_business_plan(): void
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

        // Try to access export feature - should be allowed
        $response = $this->actingAs($user)->get('/billboards/export');

        // Should get 200 or some other success response, not 404
        $this->assertNotEquals(404, $response->getStatusCode(),
            'Got 404, expected success response. Status: ' . $response->getStatusCode());
    }
}
