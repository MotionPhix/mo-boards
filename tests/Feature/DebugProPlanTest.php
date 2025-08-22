<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Services\Billing\PlanGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DebugProPlanTest extends TestCase
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

    public function test_debug_pro_plan_permissions(): void
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

        // Debug plan gate
        echo "Plan ID: " . ($company->subscription_plan ?? 'free') . "\n";
        echo "Team invitations allowed: " . (PlanGate::allows('pro', 'team.invitations', false) ? 'true' : 'false') . "\n";
        echo "User is owner: " . ($user->isOwnerOf($company) ? 'true' : 'false') . "\n";
        echo "User role in company: " . ($user->getRoleInCompany($company) ?? 'null') . "\n";

        // Check policy directly
        echo "Policy result: " . ($user->can('inviteTeamMember', $company) ? 'true' : 'false') . "\n";

        // Get the team page
        $response = $this->actingAs($user)->get('/team');

        if ($response->getStatusCode() === 200) {
            $props = $response->viewData('page')['props'] ?? [];
            if (isset($props['userPermissions'])) {
                echo "userPermissions: " . json_encode($props['userPermissions']) . "\n";
            }
        }

        $response->assertOk();
    }
}
