<?php

use App\Models\Company;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles for tests
    Role::create(['name' => 'company_owner']);
    Role::create(['name' => 'manager']);
    Role::create(['name' => 'editor']);
    Role::create(['name' => 'viewer']);
});

test('complete team invitation flow from invitation to login', function () {
    // Step 1: Setup company and owner
    $owner = User::factory()->create();
    $company = Company::factory()->create(['name' => 'Test Company']);

    $owner->companies()->attach($company, [
        'role' => 'company_owner',
        'is_owner' => true,
    ]);
    $owner->update(['current_company_id' => $company->id]);

    // Step 2: Send invitation
    Notification::fake();

    $this->actingAs($owner);
    $response = $this->post(route('team.invite'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'role' => 'editor',
    ]);

    $response->assertRedirect(route('team.index'));
    $response->assertSessionHas('success');

    // Verify invitation was created
    $invitation = TeamInvitation::where('email', 'john@example.com')->first();
    $this->assertNotNull($invitation);
    $this->assertEquals('editor', $invitation->role);
    $this->assertEquals($company->id, $invitation->company_id);

    // Verify notification was sent
    Notification::assertSentTo(
        [new \Illuminate\Notifications\AnonymousNotifiable],
        TeamInvitationNotification::class
    );

    // Step 3: New user visits invitation link (not logged in)
    auth()->logout(); // Clear authentication
    $this->get(route('team.accept-invitation', ['token' => $invitation->invitation_token]))
        ->assertRedirect(route('register.invited'))
        ->assertSessionHas('invitation_token', $invitation->invitation_token);

    // Step 4: New user registers
    $response = $this->post(route('register.invited'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));

    // Step 5: Verify user was created and added to company
    $newUser = User::where('email', 'john@example.com')->first();
    $this->assertNotNull($newUser);
    $this->assertEquals('John Doe', $newUser->name);
    $this->assertEquals($company->id, $newUser->current_company_id);

    // Verify user is attached to company with correct role
    $this->assertDatabaseHas('company_user', [
        'company_id' => $company->id,
        'user_id' => $newUser->id,
        'role' => 'editor',
    ]);

    // Verify invitation was cleaned up
    $this->assertDatabaseMissing('team_invitations', [
        'id' => $invitation->id,
    ]);

    // Step 6: New user can now login and access company
    $this->actingAs($newUser);
    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
});

test('existing user invitation acceptance flow', function () {
    // Setup
    $owner = User::factory()->create();
    $company = Company::factory()->create(['name' => 'Test Company']);

    $owner->companies()->attach($company, [
        'role' => 'company_owner',
        'is_owner' => true,
    ]);
    $owner->update(['current_company_id' => $company->id]);

    // Create an existing user (not in company)
    $existingUser = User::factory()->create([
        'email' => 'existing@example.com',
        'name' => 'Existing User',
        'current_company_id' => null,
    ]);

    // Send invitation
    $this->actingAs($owner);
    $response = $this->post(route('team.invite'), [
        'name' => 'Existing User',
        'email' => 'existing@example.com',
        'role' => 'viewer',
    ]);

    $invitation = TeamInvitation::where('email', 'existing@example.com')->first();

    // Existing user logs in and accepts invitation
    $this->actingAs($existingUser);
    $response = $this->get(route('team.accept-invitation', ['token' => $invitation->invitation_token]));

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('success');

    // Verify user was added to company
    $this->assertDatabaseHas('company_user', [
        'company_id' => $company->id,
        'user_id' => $existingUser->id,
        'role' => 'viewer',
    ]);

    // Verify user's current company was updated
    $this->assertEquals($company->id, $existingUser->fresh()->current_company_id);
});

test('multiple pending invitations are handled correctly', function () {
    $owner = User::factory()->create();
    $company1 = Company::factory()->create(['name' => 'Company 1']);
    $company2 = Company::factory()->create(['name' => 'Company 2']);

    // Setup owner for both companies
    $owner->companies()->attach($company1, ['role' => 'company_owner', 'is_owner' => true]);
    $owner->companies()->attach($company2, ['role' => 'company_owner', 'is_owner' => true]);
    $owner->update(['current_company_id' => $company1->id]);

    $this->actingAs($owner);

    // Send invitation from company 1
    $this->post(route('team.invite'), [
        'name' => 'Multi User',
        'email' => 'multi@example.com',
        'role' => 'editor',
    ]);

    // Switch to company 2 and send another invitation
    $owner->update(['current_company_id' => $company2->id]);
    $owner->refresh(); // Refresh the model to reload relationships
    $this->post(route('team.invite'), [
        'name' => 'Multi User',
        'email' => 'multi@example.com',
        'role' => 'viewer',
    ]);

    // Verify both invitations exist
    $this->assertDatabaseCount('team_invitations', 2);

    $invitation1 = TeamInvitation::where([
        'email' => 'multi@example.com',
        'company_id' => $company1->id,
    ])->first();

    $invitation2 = TeamInvitation::where([
        'email' => 'multi@example.com',
        'company_id' => $company2->id,
    ])->first();

    $this->assertNotNull($invitation1);
    $this->assertNotNull($invitation2);
    $this->assertEquals('editor', $invitation1->role);
    $this->assertEquals('viewer', $invitation2->role);
});

test('invitation link security and validation', function () {
    $company = Company::factory()->create();
    $invitation = TeamInvitation::create([
        'company_id' => $company->id,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'editor',
        'invitation_token' => TeamInvitation::generateSecureToken($company->id, 'test@example.com'),
        'expires_at' => Carbon::now()->addDays(7),
    ]);

    // Test with invalid token
    $response = $this->get(route('team.accept-invitation', ['token' => 'invalid-token']));
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');

    // Test with valid token
    $response = $this->get(route('team.accept-invitation', ['token' => $invitation->invitation_token]));
    $response->assertRedirect(route('register.invited'));
    $response->assertSessionHas('invitation_token');
});

test('invitation expiration cleanup', function () {
    $company = Company::factory()->create();

    // Create expired invitation
    $expiredInvitation = TeamInvitation::create([
        'company_id' => $company->id,
        'name' => 'Expired User',
        'email' => 'expired@example.com',
        'role' => 'editor',
        'invitation_token' => TeamInvitation::generateSecureToken($company->id, 'expired@example.com'),
        'expires_at' => Carbon::now()->subDays(1),
    ]);

    // Create valid invitation
    $validInvitation = TeamInvitation::create([
        'company_id' => $company->id,
        'name' => 'Valid User',
        'email' => 'valid@example.com',
        'role' => 'editor',
        'invitation_token' => TeamInvitation::generateSecureToken($company->id, 'valid@example.com'),
        'expires_at' => Carbon::now()->addDays(7),
    ]);

    // Test expired invitation
    $response = $this->get(route('team.accept-invitation', ['token' => $expiredInvitation->invitation_token]));
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'The invitation link has expired or is invalid.');

    // Test valid invitation
    $response = $this->get(route('team.accept-invitation', ['token' => $validInvitation->invitation_token]));
    $response->assertRedirect(route('register.invited'));
});

test('role-based invitation permissions', function () {
    $company = Company::factory()->create();

    // Create regular team member (not owner/admin)
    $teamMember = User::factory()->create();
    $teamMember->companies()->attach($company, ['role' => 'editor']);
    $teamMember->update(['current_company_id' => $company->id]);

    $this->actingAs($teamMember);

    // Try to send invitation (should be denied)
    $response = $this->post(route('team.invite'), [
        'name' => 'New User',
        'email' => 'new@example.com',
        'role' => 'viewer',
    ]);

    // Should be forbidden or redirected with error
    $this->assertTrue(
        $response->status() === 403 ||
        ($response->isRedirection() && session()->has('error'))
    );
});
