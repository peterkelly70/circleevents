<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'owner_id',
    'name',
    'slug',
    'summary',
    'description',
    'city',
    'website_url',
    'discord_url',
    'twitter_url',
    'facebook_url',
    'avatar_path',
    'banner_path',
    'visibility',
])]
class Organization extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role', 'email_opt_out_at', 'email_opt_out_token')
            ->withTimestamps();
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function mailingLists(): HasMany
    {
        return $this->hasMany(MailingList::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(OrganizationPost::class)->latest();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OrganizationMessage::class)->latest();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(OrganizationInvitation::class)->latest();
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function blocks(): MorphMany
    {
        return $this->morphMany(Block::class, 'blockable');
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path
            ? Storage::disk('public')->url($this->avatar_path)
            : null;
    }

    public function bannerUrl(): ?string
    {
        return $this->banner_path
            ? Storage::disk('public')->url($this->banner_path)
            : null;
    }

    public function isVisibleTo(?User $user): bool
    {
        return match ($this->visibility) {
            'public', 'unlisted' => true,
            'private' => $user?->isMemberOf($this) ?? false,
            default => false,
        };
    }

    public function visibilityLabel(): string
    {
        return Str::headline($this->visibility);
    }
}
