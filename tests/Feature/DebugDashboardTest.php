<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DebugDashboardTest extends TestCase
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

    public function test_debug_manager_dashboard_access(): void
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

        // Debug the response
        echo "Status: " . $response->getStatusCode() . "\n";
        echo "Headers: " . json_encode($response->headers->all()) . "\n";

        if ($response->getStatusCode() !== 200) {
            echo "Content: " . $response->getContent() . "\n";
        }

        $response->assertOk();
    }
}
