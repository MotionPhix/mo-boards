<?php

use App\Models\Company;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Support\Facades\Notification;

test('notification content is properly formatted', function () {
    // Create the necessary models
    $company = Company::factory()->create(['name' => 'Test Company']);
    $inviter = User::factory()->create(['name' => 'Admin User']);
    
    // Create a test invitation using the public method
    $invitation = TeamInvitation::createInvitation($company, [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'role' => 'editor',
    ]);
    
    // Update the token to a known value for testing
    $invitation->invitation_token = 'test-token-123';
    $invitation->save();
    
    // Create the notification
    $notification = new TeamInvitationNotification($invitation, $inviter);
    
    // Create an AnonymousNotifiable to use as the recipient
    $notifiable = new \Illuminate\Notifications\AnonymousNotifiable;
    $notifiable->route('mail', 'john@example.com');
    
    // Get the mail representation
    $mail = $notification->toMail($notifiable);
    
    // Assert basic mail properties
    expect($mail->subject)->toBe('You\'ve been invited to join Test Company');
    
    // Get the content of the notification
    $mailData = $mail->data();
    
    // Assert that the mail contains key elements
    // Since we're using a custom mailMessage, we need to check its properties
    expect($mail->greeting)->toBe('Hello John Doe,');
    expect($mail->introLines[0])->toContain('invited you to join');
    expect($mail->actionText)->toBe('Accept Invitation');
    // Debugging: see what the action URL actually is
    dump($mail->actionUrl);
    // Check that it contains the token
    expect($mail->actionUrl)->toContain('test-token-123');
    expect($mail->introLines[1])->toContain('This invitation will expire');
});

test('notification is routable to proper channel', function () {
    // Create test data
    $company = Company::factory()->create();
    $inviter = User::factory()->create();
    $invitation = TeamInvitation::create([
        'company_id' => $company->id,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'role' => 'editor',
        'invitation_token' => 'test-token-123',
        'expires_at' => now()->addDays(7),
    ]);
    
    // Create notification instance
    $notification = new TeamInvitationNotification($invitation, $inviter);
    
    // Create a notifiable object for testing
    $notifiable = new \Illuminate\Notifications\AnonymousNotifiable;
    
    // Test via method returns email
    $via = $notification->via($notifiable);
    expect($via)->toContain('mail');
    
    // Test routing for AnonymousNotifiable
    $anonymousNotifiable = new \Illuminate\Notifications\AnonymousNotifiable;
    $anonymousNotifiable->route('mail', 'john@example.com');
    
    // For mail notifications, we can't easily check the recipient from the MailMessage object
    // Instead we'll verify that the notification would route to mail
    expect($notification->via($anonymousNotifiable))->toContain('mail');
    
    // Get the mail message
    $mailMessage = $notification->toMail($anonymousNotifiable);
    
    // Check that it's properly formatted
    expect($mailMessage->subject)->toBe("You've been invited to join {$company->name}");
});

test('real world notification sending', function () {
    // Mock the notification facade
    Notification::fake();
    
    // Create a company and inviter
    $company = Company::factory()->create(['name' => 'Acme Corp']);
    $inviter = User::factory()->create(['name' => 'Team Admin']);
    
    // Create a new invitation (using the public createInvitation method)
    $invitation = TeamInvitation::createInvitation($company, [
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'role' => 'editor',
    ]);
    
    // Manually send the notification (this is what happens in the TeamController)
    Notification::route('mail', 'jane@example.com')
        ->notify(new TeamInvitationNotification($invitation, $inviter));
    
    // Assert the notification was sent
    Notification::assertSentTo(
        [new \Illuminate\Notifications\AnonymousNotifiable],
        TeamInvitationNotification::class,
        function ($notification, $channels, $notifiable) {
            return $notifiable->routes['mail'] === 'jane@example.com';
        }
    );
    
    // Assert count of sent notifications
    Notification::assertCount(1);
});
