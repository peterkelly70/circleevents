<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialPublication extends Model
{
    protected $table = 'social_publications';

    protected $fillable = [
        'organization_id',
        'event_id',
        'platform',
        'target_account_id',
        'status',
        'remote_post_id',
        'remote_url',
        'request_payload_json',
        'response_payload_json',
        'error_payload_json',
        'posted_at',
    ];

    protected $casts = [
        'request_payload_json' => 'array',
        'response_payload_json' => 'array',
        'error_payload_json' => 'array',
        'posted_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_POSTED = 'posted';

    public const STATUS_FAILED = 'failed';

    public const PLATFORM_FACEBOOK = 'facebook';

    public const PLATFORM_X = 'x';

    public const PLATFORM_DISCORD = 'discord';

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function markAsPosted(string $remotePostId, ?string $remoteUrl, ?array $responsePayload = null): void
    {
        $this->update([
            'status' => self::STATUS_POSTED,
            'remote_post_id' => $remotePostId,
            'remote_url' => $remoteUrl,
            'response_payload_json' => $responsePayload,
            'posted_at' => now(),
        ]);
    }

    public function markAsFailed(?array $errorPayload = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_payload_json' => $errorPayload,
        ]);
    }
}
