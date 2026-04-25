<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationDiscordAccount extends Model
{
    protected $table = 'organization_discord_accounts';

    protected $fillable = [
        'organization_id',
        'webhook_url_encrypted',
        'webhook_id',
        'channel_name',
        'guild_name',
        'metadata_json',
        'is_active',
        'created_by_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata_json' => 'array',
    ];

    protected $hidden = [
        'webhook_url_encrypted',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function getWebhookUrl(): ?string
    {
        if (! $this->webhook_url_encrypted) {
            return null;
        }

        return decrypt($this->webhook_url_encrypted);
    }

    public function setWebhookUrl(string $url): void
    {
        $this->webhook_url_encrypted = encrypt($url);
    }
}
