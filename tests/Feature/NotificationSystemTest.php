<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the roles that are needed for the tests
        Role::create(['name' => 'company_owner']);
        Role::create(['name' => 'viewer']);
    }

    public function test_user_can_access_notifications_api(): void
    {
        // Create a user and company
        $user = User::factory()->create();
        $company = Company::factory()->create(['subscription_plan' => 'free']);

        // Attach user to company with proper pivot data
        $user->companies()->attach($company->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);
        $user->assignRole('company_owner');

        // Create some notifications
        $userNotification = SystemNotification::create([
            'user_id' => $user->id,
            'company_id' => null,
            'type' => 'user_welcome',
            'level' => 'info',
            'title' => 'Welcome!',
            'message' => 'Welcome to the system.',
            'data' => [],
        ]);

        $companyNotification = SystemNotification::create([
            'user_id' => null,
            'company_id' => $company->id,
            'type' => 'company_update',
            'level' => 'success',
            'title' => 'Company Updated',
            'message' => 'Your company settings have been updated.',
            'data' => [],
        ]);

        // Test fetching notifications
        $this->actingAs($user);
        
        $response = $this->getJson('/api/notifications');
        
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'notifications' => [
                '*' => [
                    'id',
                    'type',
                    'level',
                    'title',
                    'message',
                    'is_read',
                    'is_dismissed',
                    'created_at',
                ],
            ],
            'unread_count',
        ]);

        // Should have both notifications
        $response->assertJsonCount(2, 'notifications');
        $response->assertJsonPath('unread_count', 2);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $user->companies()->attach($company->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);
        $user->assignRole('company_owner');

        $notification = SystemNotification::create([
            'user_id' => $user->id,
            'company_id' => null,
            'type' => 'test',
            'level' => 'info',
            'title' => 'Test',
            'message' => 'Test notification',
            'data' => [],
        ]);

        $this->actingAs($user);
        
        $response = $this->postJson("/api/notifications/{$notification->id}/read");
        
        $response->assertSuccessful();
        $response->assertJsonPath('message', 'Notification marked as read');
        
        // Verify notification is marked as read
        $this->assertTrue($notification->fresh()->is_read);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_can_dismiss_notification(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $user->companies()->attach($company->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);
        $user->assignRole('company_owner');

        $notification = SystemNotification::create([
            'company_id' => $company->id,
            'type' => 'test',
            'level' => 'info',
            'title' => 'Test',
            'message' => 'Test notification',
            'data' => [],
        ]);

        $this->actingAs($user);
        
        $response = $this->postJson("/api/notifications/{$notification->id}/dismiss");
        
        $response->assertSuccessful();
        $response->assertJsonPath('message', 'Notification dismissed');
        
        // Verify notification is dismissed
        $this->assertTrue($notification->fresh()->is_dismissed);
        $this->assertNotNull($notification->fresh()->dismissed_at);
    }

    public function test_user_cannot_access_other_users_notifications(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $company = Company::factory()->create();

        $user1->companies()->attach($company->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);
        $user1->update(['current_company_id' => $company->id]);

        // Create notification for user2
        $notification = SystemNotification::create([
            'user_id' => $user2->id,
            'type' => 'test',
            'level' => 'info',
            'title' => 'Test',
            'message' => 'Test notification',
            'data' => [],
        ]);

        // User1 tries to mark user2's notification as read
        $this->actingAs($user1);
        
        $response = $this->postJson("/api/notifications/{$notification->id}/read");
        
        $response->assertForbidden();
    }

    public function test_broadcasting_channel_authorization(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $user->companies()->attach($company->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);

        $user->update(['current_company_id' => $company->id]);

        // Test user channel authorization
        $this->actingAs($user);
        
        // User should be able to subscribe to their own channel
        $response = $this->postJson('/broadcasting/auth', [
            'channel_name' => "private-user.{$user->id}",
        ]);
        
        // This will fail without proper Pusher setup, but shouldn't be 403
        $this->assertNotEquals(403, $response->status());

        // User should not be able to subscribe to another user's channel
        $otherUser = User::factory()->create();
        $response = $this->postJson('/broadcasting/auth', [
            'channel_name' => "private-user.{$otherUser->id}",
        ]);
        
        // This should be 403 or fail validation
        $this->assertTrue($response->status() === 403 || $response->status() === 422);
    }
}
