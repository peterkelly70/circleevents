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
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password', 'city', 'bio', 'avatar_path', 'font_size', 'organization_theme_override', 'personal_theme', 'is_admin', 'registration_status', 'approved_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const FONT_SIZE_CLASSES = [
        'small' => 'text-base leading-7',
        'medium' => 'text-[17px] leading-7',
        'large' => 'text-[19px] leading-8',
        'x-large' => 'text-[21px] leading-9',
    ];

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

    public function isSuspended(): bool
    {
        return $this->registration_status === 'suspended';
    }

    public function isPending(): bool
    {
        return $this->registration_status === 'pending';
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)->withPivot('role', 'email_opt_out_at', 'email_opt_out_token')->withTimestamps();
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function mailingLists(): BelongsToMany
    {
        return $this->belongsToMany(MailingList::class)->withPivot('status', 'subscribed_at')->withTimestamps();
    }

    public function blocks(): MorphMany
    {
        return $this->morphMany(Block::class, 'blockable');
    }

    public function createdOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'owner_id');
    }

    public function isManagerOf(Organization $organization): bool
    {
        $resolved = $this->resolvedUser();

        if ($resolved->is_admin) {
            return true;
        }

        return $resolved->organizations()
            ->where('organization_id', $organization->id)
            ->wherePivotIn('role', ['owner', 'manager'])
            ->exists();
    }

    public function isOwnerOf(Organization $organization): bool
    {
        $resolved = $this->resolvedUser();

        if ($resolved->is_admin) {
            return true;
        }

        return $resolved->organizations()
            ->where('organization_id', $organization->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    public function isMemberOf(Organization $organization): bool
    {
        $resolved = $this->resolvedUser();

        return $resolved->organizations()
            ->where('organization_id', $organization->id)
            ->exists();
    }

    public function roleIn(Organization $organization): ?string
    {
        $resolved = $this->resolvedUser();

        return $resolved->organizations()
            ->where('organization_id', $organization->id)
            ->first()?->pivot->role;
    }

    public function avatarUrl(): string
    {
        if ($this->avatar_path) {
            return Storage::disk('public')->url($this->avatar_path);
        }

        return '';
    }

    public function avatarInitials(): string
    {
        return str($this->name)->substr(0, 2)->upper();
    }

    public function fontSizeClass(): string
    {
        return self::FONT_SIZE_CLASSES[$this->font_size] ?? self::FONT_SIZE_CLASSES['medium'];
    }

    public function resolvedOrganizationThemeKey(?Organization $organization = null): string
    {
        $resolved = $this->resolvedUser();

        if ($organization) {
            if ($resolved->organization_theme_override) {
                return $resolved->personal_theme ?? 'embers';
            }

            return $organization->theme_key ?? 'embers';
        }

        return $resolved->personal_theme ?? 'embers';
    }

    public function shouldOverrideOrganizationTheme(): bool
    {
        return ! empty($this->organization_theme_override);
    }

    public function resolvedUser(): ?User
    {
        return $this;
    }

    public function isBlacklistedFrom(int $organizationId): bool
    {
        return OrganizationBlacklist::where('organization_id', $organizationId)
            ->where('user_id', $this->id)
            ->exists();
    }

    public function memberMessages()
    {
        return $this->hasMany(OrganizationMemberMessage::class, 'user_id');
    }
}
