<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'organization_id',
    'creator_id',
    'title',
    'slug',
    'summary',
    'description',
    'venue_name',
    'venue_address',
    'city',
    'starts_at',
    'ends_at',
    'timezone',
    'capacity',
    'visibility',
    'is_published',
    'image_path',
])]
class Event extends Model
{
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(EventInvitation::class);
    }

    public function discussionPosts(): HasMany
    {
        return $this->hasMany(EventDiscussionPost::class)->latest();
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function isVisibleTo(?User $user): bool
    {
        return match ($this->visibility) {
            'public', 'unlisted' => true,
            'private' => $user?->isMemberOf($this->organization) ?? false,
            default => false,
        };
    }

    public function visibilityLabel(): string
    {
        return Str::headline($this->visibility);
    }
}
