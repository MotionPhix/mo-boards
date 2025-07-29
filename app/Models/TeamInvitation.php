<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

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
    public static function generateSecureToken(int $companyId, string $email): string
    {
        $payload = json_encode([
            'company_id' => $companyId,
            'email' => $email,
            'timestamp' => time(),
        ]);

        // Use Laravel's encryption for secure tokens
        return base64_encode(Crypt::encrypt($payload));
    }

    /**
     * Verify and decode an invitation token
     *
     * @param string $token
     * @return array|null
     */
    public static function verifyToken(string $token): ?array
    {
        try {
            $decoded = base64_decode($token);
            $payload = Crypt::decrypt($decoded);
            return json_decode($payload, true);
        } catch (\Exception $e) {
            return null;
        }
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

    /**
     * Find invitation by token
     *
     * @param string $token
     * @return self|null
     */
    public static function findByToken(string $token): ?self
    {
        $tokenData = self::verifyToken($token);

        if (!$tokenData) {
            return null;
        }

        return self::where([
            'company_id' => $tokenData['company_id'],
            'email' => $tokenData['email'],
            'invitation_token' => $token,
        ])->first();
    }
}
