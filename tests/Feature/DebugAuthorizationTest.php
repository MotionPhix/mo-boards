<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Billboard;
use App\Models\Company;
use App\Models\User;
use App\Policies\BillboardPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DebugAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the roles that are needed for the tests
        Role::create(['name' => 'company_owner']);
    }

    public function test_billboard_policy_export_data(): void
    {
        // Create a user and company
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

        // Test the policy directly
        $policy = new BillboardPolicy();
        $result = $policy->exportData($user);

        echo "Policy exportData result: " . ($result ? 'true' : 'false') . "\n";

        // Test Laravel's authorization
        $this->actingAs($user);

        try {
            $canExport = $user->can('exportData', Billboard::class);
            echo "Laravel can() result: " . ($canExport ? 'true' : 'false') . "\n";
        } catch (\Exception $e) {
            echo "Laravel can() exception: " . $e->getMessage() . "\n";
        }

        $this->assertTrue(true);
    }
}
