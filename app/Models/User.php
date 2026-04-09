<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'email', 'password', 'city', 'bio', 'is_admin', 'registration_status', 'approved_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'approved_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isApproved(): bool
    {
        return $this->registration_status === 'active';
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)
            ->withPivot('role', 'email_opt_out_at', 'email_opt_out_token')
            ->withTimestamps();
    }

    public function createdOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'owner_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'creator_id');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function sentEventInvitations(): HasMany
    {
        return $this->hasMany(EventInvitation::class, 'invited_by_user_id');
    }

    public function discussionPosts(): HasMany
    {
        return $this->hasMany(EventDiscussionPost::class);
    }

    public function organizationPosts(): HasMany
    {
        return $this->hasMany(OrganizationPost::class);
    }

    public function organizationMessages(): HasMany
    {
        return $this->hasMany(OrganizationMessage::class);
    }

    public function organizationInvitations(): HasMany
    {
        return $this->hasMany(OrganizationInvitation::class, 'invited_by_user_id');
    }

    public function submittedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_user_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class);
    }

    public function reportsAgainst(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function blocksAgainst(): MorphMany
    {
        return $this->morphMany(Block::class, 'blockable');
    }

    public function mailingLists(): BelongsToMany
    {
        return $this->belongsToMany(MailingList::class)
            ->withPivot('status', 'subscribed_at')
            ->withTimestamps();
    }

    public function isManagerOf(Organization $organization): bool
    {
        if ($this->is_admin) {
            return true;
        }

        return $this->organizations()
            ->where('organization_id', $organization->id)
            ->wherePivotIn('role', ['owner', 'manager'])
            ->exists();
    }

    public function isOwnerOf(Organization $organization): bool
    {
        if ($this->is_admin) {
            return true;
        }

        return $this->organizations()
            ->where('organization_id', $organization->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    public function isMemberOf(Organization $organization): bool
    {
        if ($this->is_admin) {
            return true;
        }

        return $this->organizations()
            ->where('organization_id', $organization->id)
            ->exists();
    }

    public function isEmailOptedOutOf(Organization $organization): bool
    {
        return DB::table('organization_user')
            ->where('organization_id', $organization->id)
            ->where('user_id', $this->id)
            ->whereNotNull('email_opt_out_at')
            ->exists();
    }

    public function hasBlocked(User|Organization $target): bool
    {
        return $this->blocks()
            ->where('blockable_type', $target::class)
            ->where('blockable_id', $target->id)
            ->exists();
    }
}
