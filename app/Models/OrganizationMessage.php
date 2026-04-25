<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'organization_id',
    'user_id',
    'subject',
    'body',
    'image_path',
    'emailed_at',
    'discord_posted_at',
    'facebook_posted_at',
])]
class OrganizationMessage extends Model
{
    protected function casts(): array
    {
        return [
            'emailed_at' => 'datetime',
            'discord_posted_at' => 'datetime',
            'facebook_posted_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}
