<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'event_id',
    'invited_by_user_id',
    'name',
    'email',
    'message',
    'token',
    'share_code',
    'expires_at',
    'max_uses',
    'use_count',
    'revoked_at',
    'revoked_by_user_id',
    'revoked_reason',
    'accepted_at',
])]
class EventInvitation extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function isShareLink(): bool
    {
        return $this->email === null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function revokedMessage(): string
    {
        return $this->revoked_reason
            ? 'This invitation was canceled. Reason: '.$this->revoked_reason
            : 'This invitation was canceled.';
    }

    public function hasRemainingUses(): bool
    {
        return $this->max_uses === null || $this->use_count < $this->max_uses;
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }
}
