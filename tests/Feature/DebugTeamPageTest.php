<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DebugTeamPageTest extends TestCase
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

    public function test_debug_team_page_response(): void
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

        // Get the team page
        $response = $this->actingAs($user)->get('/team');

        // Debug the response
        echo "Status: " . $response->getStatusCode() . "\n";

        if ($response->getStatusCode() === 200) {
            $props = $response->viewData('page')['props'] ?? [];
            echo "Props keys: " . json_encode(array_keys($props)) . "\n";

            if (isset($props['userPermissions'])) {
                echo "userPermissions: " . json_encode($props['userPermissions']) . "\n";
            } else {
                echo "userPermissions not found\n";
            }
        } else {
            echo "Response content: " . $response->getContent() . "\n";
        }

        $response->assertOk();
    }
}
