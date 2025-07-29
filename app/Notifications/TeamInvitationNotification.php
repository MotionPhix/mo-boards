<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class TeamInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected TeamInvitation $invitation;
    protected Company $company;
    protected User $inviter;

    /**
     * Create a new notification instance.
     */
    public function __construct(TeamInvitation $invitation, User $inviter)
    {
        $this->invitation = $invitation;
        $this->company = $invitation->company;
        $this->inviter = $inviter;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $companyLogo = $this->company->getFirstMediaUrl('logo');
        $companyName = $this->company->name;
        $inviterName = $this->inviter->name;
        $role = $this->getRoleDisplayName($this->invitation->role);
        $expirationDate = $this->invitation->expires_at->format('F j, Y \a\t g:i A');
        
        return (new MailMessage)
            ->subject("You've been invited to join {$companyName}")
            ->greeting("Hello {$this->invitation->name},")
            ->line("{$inviterName} has invited you to join {$companyName} as a {$role}.")
            ->line("This invitation will expire on {$expirationDate}.")
            ->action('Accept Invitation', $this->invitation->getInvitationUrl())
            ->line("If you didn't expect this invitation, you can safely ignore this email.")
            ->when($companyLogo, function ($mailMessage) use ($companyLogo, $companyName) {
                return $mailMessage->with([
                    'image' => $companyLogo,
                    'imageAlt' => "{$companyName} Logo",
                ]);
            });
    }
    
    /**
     * Get user-friendly role name
     */
    protected function getRoleDisplayName(string $role): string
    {
        return match ($role) {
            'company_owner' => 'Company Owner',
            'manager' => 'Manager',
            'editor' => 'Editor',
            'viewer' => 'Viewer',
            default => ucfirst($role),
        };
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'company_id' => $this->company->id,
            'company_name' => $this->company->name,
            'inviter_id' => $this->inviter->id,
            'inviter_name' => $this->inviter->name,
            'role' => $this->invitation->role,
        ];
    }
}
