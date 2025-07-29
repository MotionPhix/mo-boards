<?php

use App\Models\Company;
use App\Models\TeamInvitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

test('invitation token generation and validation works correctly', function () {
    // Create a company
    $company = Company::factory()->create();
    
    // Generate a secure token for a test email
    $email = 'test@example.com';
    $token = TeamInvitation::generateSecureToken($company->id, $email);
    
    // Ensure token is not empty
    $this->assertNotEmpty($token);
    
    // Verify token format (should be an encrypted string)
    $this->assertIsString($token);
    
    // Try to decrypt the token
    $decrypted = Crypt::decrypt($token);
    
    // Verify the decrypted token contains both company ID and email
    $this->assertStringContainsString((string)$company->id, $decrypted);
    $this->assertStringContainsString($email, $decrypted);
});

test('tampered invitation token is rejected', function () {
    // Create a company
    $company = Company::factory()->create();
    
    // Create a user who will receive the invitation
    $user = User::factory()->create();
    
    // Create a valid invitation first
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
    
    // Try to accept with a tampered token
    $tampered_token = Str::random(40); // Random token that's not valid
    
    $response = $this->get(route('team.accept-invitation', ['token' => $tampered_token]));
    
    // Assert redirection to login with error
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'The invitation link has expired or is invalid.');
    
    // Assert the user was not added to the company
    $this->assertDatabaseMissing('company_user', [
        'company_id' => $company->id,
        'user_id' => $user->id,
    ]);
});

test('invitation can be canceled by team admin', function () {
    // Create a company
    $company = Company::factory()->create();
    
    // Create team admin
    $admin = User::factory()->create();
    $company->users()->attach($admin, ['role' => 'admin']);
    $admin->update(['current_company_id' => $company->id]);
    
    // Create an invitation
    $invitation = TeamInvitation::create([
        'company_id' => $company->id,
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'role' => 'editor',
        'invitation_token' => TeamInvitation::generateSecureToken($company->id, 'newuser@example.com'),
        'expires_at' => Carbon::now()->addDays(7),
    ]);
    
    // Login as the admin
    $this->actingAs($admin);
    
    // Cancel the invitation
    $response = $this->delete(route('teams.cancel-invitation', ['invitation' => $invitation->id]));
    
    // Assert success response
    $response->assertStatus(200);
    
    // Assert the invitation was deleted
    $this->assertDatabaseMissing('team_invitations', [
        'id' => $invitation->id,
    ]);
});

test('non-admin cannot cancel invitations', function () {
    // Create a company
    $company = Company::factory()->create();
    
    // Create regular team member (not admin)
    $regularMember = User::factory()->create();
    $company->users()->attach($regularMember, ['role' => 'editor']);
    $regularMember->update(['current_company_id' => $company->id]);
    
    // Create an invitation
    $invitation = TeamInvitation::create([
        'company_id' => $company->id,
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'role' => 'editor',
        'invitation_token' => TeamInvitation::generateSecureToken($company->id, 'newuser@example.com'),
        'expires_at' => Carbon::now()->addDays(7),
    ]);
    
    // Login as regular member
    $this->actingAs($regularMember);
    
    // Try to cancel the invitation
    $response = $this->delete(route('teams.cancel-invitation', ['invitation' => $invitation->id]));
    
    // Assert forbidden response
    $response->assertStatus(403);
    
    // Assert the invitation still exists
    $this->assertDatabaseHas('team_invitations', [
        'id' => $invitation->id,
    ]);
});
