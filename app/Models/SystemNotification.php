<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class SystemNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'level',
        'title',
        'message',
        'data',
        'company_id',
        'user_id',
        'is_read',
        'is_dismissed',
        'read_at',
        'dismissed_at',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'is_dismissed' => 'boolean',
        'read_at' => 'datetime',
        'dismissed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread(Builder $query): void
    {
        $query->where('is_read', false);
    }

    public function scopeNotDismissed(Builder $query): void
    {
        $query->where('is_dismissed', false);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where(function ($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeForCompany(Builder $query, Company $company): void
    {
        $query->where('company_id', $company->id);
    }

    public function scopeForUser(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    public function scopeOfLevel(Builder $query, string $level): void
    {
        $query->where('level', $level);
    }

    // Methods
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function dismiss(): void
    {
        $this->update([
            'is_dismissed' => true,
            'dismissed_at' => now(),
        ]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
