<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationFacebookAccount extends Model
{
    protected $table = 'organization_facebook_accounts';

    protected $fillable = [
        'organization_id',
        'facebook_user_id',
        'facebook_page_id',
        'facebook_page_name',
        'access_token_encrypted',
        'refresh_token_encrypted',
        'token_expires_at',
        'scopes_json',
        'metadata_json',
        'is_active',
        'created_by_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'token_expires_at' => 'datetime',
        'scopes_json' => 'array',
        'metadata_json' => 'array',
    ];

    protected $hidden = [
        'access_token_encrypted',
        'refresh_token_encrypted',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function getAccessToken(): ?string
    {
        if (! $this->access_token_encrypted) {
            return null;
        }

        return decrypt($this->access_token_encrypted);
    }

    public function setAccessToken(string $token): void
    {
        $this->access_token_encrypted = encrypt($token);
    }

    public function getRefreshToken(): ?string
    {
        if (! $this->refresh_token_encrypted) {
            return null;
        }

        return decrypt($this->refresh_token_encrypted);
    }

    public function setRefreshToken(string $token): void
    {
        $this->refresh_token_encrypted = encrypt($token);
    }
}
