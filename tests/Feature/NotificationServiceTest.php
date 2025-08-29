<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\SystemNotification;
use App\Models\User;
use App\Services\SystemNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SystemNotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(SystemNotificationService::class);
        
        // Create required roles
        Role::create(['name' => 'company_owner']);
    }

    public function test_service_methods_exist_and_work(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        
        $user->companies()->attach($company->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);
        
        $user->update(['current_company_id' => $company->id]);
        
        // Create notifications
        $userNotification = $this->service->createUserNotification(
            $user,
            'test',
            'Test Title',
            'Test Message'
        );
        
        $companyNotification = $this->service->createCompanyNotification(
            $company,
            'test',
            'Company Test',
            'Company Message'
        );
        
        // Test getUserNotifications
        $userNotifications = $this->service->getUserNotifications($user);
        $this->assertCount(1, $userNotifications);
        $this->assertEquals($userNotification->id, $userNotifications->first()->id);
        
        // Test getCompanyNotifications
        $companyNotifications = $this->service->getCompanyNotifications($company);
        $this->assertCount(1, $companyNotifications);
        $this->assertEquals($companyNotification->id, $companyNotifications->first()->id);
        
        // Test markAllAsReadForUser
        $readCount = $this->service->markAllAsReadForUser($user);
        $this->assertEquals(1, $readCount);
        $this->assertTrue($userNotification->fresh()->is_read);
        
        // Test markAllAsReadForCompany
        $readCount = $this->service->markAllAsReadForCompany($company);
        $this->assertEquals(0, $readCount); // Already read
        
        // Create another unread notification
        $this->service->createCompanyNotification(
            $company,
            'test2',
            'Another Test',
            'Another Message'
        );
        
        $readCount = $this->service->markAllAsReadForCompany($company);
        $this->assertEquals(1, $readCount);
    }
}
