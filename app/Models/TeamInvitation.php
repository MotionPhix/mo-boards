<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class TeamInvitation extends Model
{
    protected $fillable = [
        'name',
        'email',
        'role',
        'company_id',
        'invitation_token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public static function createInvitation(Company $company, array $data): self
    {
        return self::create([
            'company_id' => $company->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'invitation_token' => self::generateSecureToken($company->id, $data['email']),
            'expires_at' => Carbon::now()->addDays(7), // Expires after 7 days
        ]);
    }
    
    /**
     * Generate a secure, signed invitation token
     *
     * @param int $companyId
     * @param string $email
     * @return string
     */
    protected static function generateSecureToken(int $companyId, string $email): string
    {
        $payload = json_encode([
            'company_id' => $companyId,
            'email' => $email,
            'timestamp' => time(),
        ]);
        
        // Use Laravel's encryption for secure tokens
        return base64_encode(\Illuminate\Support\Facades\Crypt::encrypt($payload));
    }
    
    /**
     * Get a URL for this invitation
     *
     * @return string
     */
    public function getInvitationUrl(): string
    {
        return url(route('team.accept-invitation', ['token' => $this->invitation_token]));
    }
}
