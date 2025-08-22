<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Services\Billing\PlanGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DebugExportTest extends TestCase
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
            ['plan_id' => 'free', 'key' => 'export.enabled', 'value' => '0'],

            // Business plan
            ['plan_id' => 'business', 'key' => 'export.enabled', 'value' => '1'],
        ];

        foreach ($rules as $rule) {
            \DB::table('plan_feature_rules')->insert(array_merge($rule, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function test_debug_export_route(): void
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

        // Debug plan gate
        echo "Plan ID: " . ($company->subscription_plan ?? 'free') . "\n";
        echo "Export enabled: " . (PlanGate::allows('free', 'export.enabled', false) ? 'true' : 'false') . "\n";

        // Check if route exists
        $routes = \Route::getRoutes();
        $exportRoute = null;
        foreach ($routes as $route) {
            if ($route->uri() === 'billboards/export') {
                $exportRoute = $route;
                break;
            }
        }

        if ($exportRoute) {
            echo "Route found: " . $exportRoute->uri() . "\n";
            echo "Route methods: " . implode(',', $exportRoute->methods()) . "\n";
            echo "Route middleware: " . implode(',', $exportRoute->middleware()) . "\n";
        } else {
            echo "Route not found\n";
        }

        // Try to access export feature
        $response = $this->actingAs($user)->get('/billboards/export');

        echo "Response status: " . $response->getStatusCode() . "\n";
        if ($response->getStatusCode() !== 200) {
            echo "Response content: " . substr($response->getContent(), 0, 500) . "\n";
        }

        // Just assert that we get some response
        $this->assertTrue(true);
    }
}
