<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'organization_id',
    'invited_by_user_id',
    'name',
    'email',
    'message',
    'role',
    'token',
    'share_code',
    'expires_at',
    'accepted_at',
    'opted_out_at',
])]
class OrganizationInvitation extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'opted_out_at' => 'datetime',
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

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }
}
