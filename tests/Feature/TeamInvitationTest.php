<?php

use App\Models\Company;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles for tests
    Role::create(['name' => 'company_owner']);
    Role::create(['name' => 'manager']);
    Role::create(['name' => 'editor']);
    Role::create(['name' => 'viewer']);
});

test('team owner can view the invitation form', function () {
    // Create a company owner
    $owner = User::factory()->create();
    $company = Company::factory()->create();

    // Attach owner to company
    $owner->companies()->attach($company, [
        'role' => 'company_owner',
        'is_owner' => true,
    ]);
    $owner->current_company_id = $company->id;
    $owner->save();

    // Login as the owner
    $this->actingAs($owner);

    // Visit the team page
    $response = $this->get(route('team.index'));

    // Assert that the page loaded correctly
    $response->assertStatus(200);

    // Assert that Inertia component was loaded
    $response->assertInertia(fn (Assert $page) => $page
        ->component('team/Index')
        ->has('userPermissions', fn (Assert $page) => $page
            ->where('canInviteUsers', true)
            ->etc()
        )
    );
});

test('team owner can send invitation and email is sent', function () {
    // Mock the notification
    Notification::fake();

    // Create a company owner
    $owner = User::factory()->create();
    $company = Company::factory()->create();

    // Attach owner to company
    $owner->companies()->attach($company, [
        'role' => 'company_owner',
        'is_owner' => true,
    ]);
    $owner->current_company_id = $company->id;
    $owner->save();

    // Login as the owner
    $this->actingAs($owner);

    // Send an invitation
    $response = $this->post(route('team.invite'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'role' => 'editor',
    ]);

    // Assert redirection
    $response->assertRedirect(route('team.index'));
    $response->assertSessionHas('success', 'Invitation has been sent successfully.');

    // Assert invitation was created in the database
    $this->assertDatabaseHas('team_invitations', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'role' => 'editor',
        'company_id' => $company->id,
    ]);

    // Get the invitation that was just created
    $invitation = TeamInvitation::where('email', 'john@example.com')->first();

    // Assert that the notification was sent
    Notification::assertSentTo(
        [new \Illuminate\Notifications\AnonymousNotifiable],
        TeamInvitationNotification::class,
        function ($notification, $channels, $notifiable) use ($invitation) {
            return $notifiable->routes['mail'] === 'john@example.com';
        }
    );
});

test('existing user receives invitation email', function () {
    // Mock the notification
    Notification::fake();

    // Create a company owner
    $owner = User::factory()->create();
    $company = Company::factory()->create();

    // Attach owner to company
    $owner->companies()->attach($company, [
        'role' => 'company_owner',
        'is_owner' => true,
    ]);
    $owner->current_company_id = $company->id;
    $owner->save();

    // Create an existing user (not in the company)
    $existingUser = User::factory()->create([
        'email' => 'existing@example.com',
        'name' => 'Existing User',
    ]);

    // Login as the owner
    $this->actingAs($owner);

    // Send an invitation to the existing user
    $response = $this->post(route('team.invite'), [
        'name' => 'Existing User',
        'email' => 'existing@example.com',
        'role' => 'editor',
    ]);

    // Assert redirection
    $response->assertRedirect(route('team.index'));

    // Assert invitation was created in the database
    $this->assertDatabaseHas('team_invitations', [
        'email' => 'existing@example.com',
        'company_id' => $company->id,
    ]);

    // Assert notification was sent to the existing user
    Notification::assertSentTo(
        $existingUser,
        TeamInvitationNotification::class
    );
});

test('user cannot be invited to a company they already belong to', function () {
    // Create a company owner
    $owner = User::factory()->create();
    $company = Company::factory()->create();

    // Attach owner to company
    $owner->companies()->attach($company, [
        'role' => 'company_owner',
        'is_owner' => true,
    ]);
    $owner->current_company_id = $company->id;
    $owner->save();

    // Create another user already in the company
    $existingMember = User::factory()->create();
    $existingMember->companies()->attach($company, [
        'role' => 'editor',
    ]);

    // Login as the owner
    $this->actingAs($owner);

    // Attempt to invite the user who is already a member
    $response = $this->post(route('team.invite'), [
        'name' => $existingMember->name,
        'email' => $existingMember->email,
        'role' => 'editor',
    ]);

    // Assert redirection with error
    $response->assertRedirect(route('team.index'));
    $response->assertSessionHas('error', 'This user is already a member of your team.');

    // Assert no invitation was created
    $this->assertDatabaseMissing('team_invitations', [
        'email' => $existingMember->email,
        'company_id' => $company->id,
    ]);
});
