<?php

use App\Models\Company;
use App\Models\TeamInvitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

test('user can accept invitation when logged in', function () {
    // Create a company
    $company = Company::factory()->create();
    
    // Create a user who will receive the invitation
    $user = User::factory()->create();
    
    // Create an invitation for this user
    $invitation = TeamInvitation::create([
        'company_id' => $company->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => 'editor',
        'invitation_token' => TeamInvitation::generateSecureToken($company->id, $user->email),
        'expires_at' => Carbon::now()->addDays(7),
    ]);
    
    // Login as the user
    $this->actingAs($user);
    
    // Accept the invitation
    $response = $this->get(route('team.accept-invitation', ['token' => $invitation->invitation_token]));
    
    // Assert redirection to dashboard
    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('success', "You've successfully joined {$company->name}.");
    
    // Assert the user was added to the company with the correct role
    $this->assertDatabaseHas('company_user', [
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);
    
    // Assert the invitation was deleted
    $this->assertDatabaseMissing('team_invitations', [
        'id' => $invitation->id,
    ]);
    
    // Assert user's current_company_id is set to the new company
    $this->assertEquals($company->id, $user->fresh()->current_company_id);
});

test('non-logged-in user is redirected to register form', function () {
    // Create a company
    $company = Company::factory()->create();
    
    // Create an invitation for a new user
    $invitation = TeamInvitation::create([
        'company_id' => $company->id,
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'role' => 'editor',
        'invitation_token' => TeamInvitation::generateSecureToken($company->id, 'newuser@example.com'),
        'expires_at' => Carbon::now()->addDays(7),
    ]);
    
    // Visit the invitation accept route without being logged in
    $response = $this->get(route('team.accept-invitation', ['token' => $invitation->invitation_token]));
    
    // Assert redirection to register for invited users
    $response->assertRedirect(route('register.invited'));
    $response->assertSessionHas('info');
    
    // Assert session has correct invitation data
    $this->assertEquals($invitation->invitation_token, session('invitation_token'));
    $this->assertEquals($invitation->email, session('invitation_email'));
    $this->assertEquals($invitation->name, session('invitation_name'));
    $this->assertEquals($company->name, session('company_name'));
});

test('expired invitation cannot be accepted', function () {
    // Create a company
    $company = Company::factory()->create();
    
    // Create a user
    $user = User::factory()->create();
    
    // Create an expired invitation
    $invitation = TeamInvitation::create([
        'company_id' => $company->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => 'editor',
        'invitation_token' => TeamInvitation::generateSecureToken($company->id, $user->email),
        'expires_at' => Carbon::now()->subDay(), // Expired yesterday
    ]);
    
    // Login as the user
    $this->actingAs($user);
    
    // Try to accept the expired invitation
    $response = $this->get(route('team.accept-invitation', ['token' => $invitation->invitation_token]));
    
    // Assert redirection to login with error
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'The invitation link has expired or is invalid.');
    
    // Assert the user was not added to the company
    $this->assertDatabaseMissing('company_user', [
        'company_id' => $company->id,
        'user_id' => $user->id,
    ]);
});

test('new user can register and join company through invitation', function () {
    // Mock the session with invitation data
    $company = Company::factory()->create(['name' => 'Test Company']);
    
    $invitationToken = 'fake_token';
    
    // Simulate being on the invited registration page
    $this->withSession([
        'invitation_token' => $invitationToken,
        'invitation_email' => 'newuser@example.com',
        'invitation_name' => 'New User',
        'company_name' => $company->name,
    ]);
    
    // Create the invitation in the database
    $invitation = TeamInvitation::create([
        'company_id' => $company->id,
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'role' => 'editor',
        'invitation_token' => $invitationToken,
        'expires_at' => Carbon::now()->addDays(7),
    ]);
    
    // Make the registration request
    $response = $this->post(route('register.invited'), [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    
    // Assert redirection to dashboard
    $response->assertRedirect(route('dashboard'));
    
    // Assert user was created
    $this->assertDatabaseHas('users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
    ]);
    
    // Get the newly created user
    $user = User::where('email', 'newuser@example.com')->first();
    
    // Assert the user was added to the company
    $this->assertDatabaseHas('company_user', [
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);
    
    // Assert current_company_id was set
    $this->assertEquals($company->id, $user->current_company_id);
    
    // Assert the invitation was deleted
    $this->assertDatabaseMissing('team_invitations', [
        'id' => $invitation->id,
    ]);
});
