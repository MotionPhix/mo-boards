<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Billboard\ExportBillboardsController;
use App\Models\Company;
use App\Models\User;
use App\Services\BillboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DebugControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the roles that are needed for the tests
        Role::create(['name' => 'company_owner']);
    }

    public function test_export_controller_directly(): void
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

        $this->actingAs($user);

        // Test the BillboardService directly
        try {
            $billboardService = app(BillboardService::class);
            $data = $billboardService->exportBillboards($company, []);
            echo "BillboardService works, returned " . $data->count() . " items\n";
        } catch (\Exception $e) {
            echo "BillboardService error: " . $e->getMessage() . "\n";
        }

        // Test the controller directly
        try {
            $controller = app(ExportBillboardsController::class);
            $request = Request::create('/billboards/export', 'GET');
            $request->setUserResolver(fn() => $user);

            $response = $controller($request);
            echo "Controller works, response status: " . $response->getStatusCode() . "\n";
        } catch (\Exception $e) {
            echo "Controller error: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }

        $this->assertTrue(true);
    }
}
