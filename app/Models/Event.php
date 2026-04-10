<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\CarbonInterface;

#[Fillable([
    'organization_id',
    'mailing_list_id',
    'recurrence_group',
    'creator_id',
    'title',
    'slug',
    'summary',
    'description',
    'is_online',
    'online_url',
    'venue_name',
    'venue_address',
    'google_place_id',
    'latitude',
    'longitude',
    'city',
    'starts_at',
    'ends_at',
    'timezone',
    'capacity',
    'visibility',
    'repeat_frequency',
    'repeat_until',
    'is_published',
    'image_path',
    'discord_posted_at',
    'facebook_posted_at',
    'notify_followers_one_week_before',
    'notify_followers_one_day_before',
    'notify_followers_one_hour_before',
    'follower_reminder_one_week_sent_at',
    'follower_reminder_one_day_sent_at',
    'follower_reminder_one_hour_sent_at',
])]
class Event extends Model
{
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'repeat_until' => 'datetime',
            'is_online' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_published' => 'boolean',
            'discord_posted_at' => 'datetime',
            'facebook_posted_at' => 'datetime',
            'notify_followers_one_week_before' => 'boolean',
            'notify_followers_one_day_before' => 'boolean',
            'notify_followers_one_hour_before' => 'boolean',
            'follower_reminder_one_week_sent_at' => 'datetime',
            'follower_reminder_one_day_sent_at' => 'datetime',
            'follower_reminder_one_hour_sent_at' => 'datetime',
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

    public function mailingList(): BelongsTo
    {
        return $this->belongsTo(MailingList::class);
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
        if (! $this->organization->isApproved() && ! ($user?->isManagerOf($this->organization) ?? false)) {
            return false;
        }

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

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function googleMapsUrl(): ?string
    {
        if ($this->is_online) {
            return null;
        }

        if ($this->hasCoordinates()) {
            return 'https://www.google.com/maps/search/?api=1&query=' . $this->latitude . ',' . $this->longitude;
        }

        if ($this->venue_address) {
            return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($this->venue_address);
        }

        return null;
    }

    public function calendarDescription(): string
    {
        $parts = array_filter([
            $this->summary,
            $this->description,
            $this->is_online
                ? ('Online event'.($this->online_url ? ': '.$this->online_url : '.'))
                : null,
            'Event page: '.route('events.show', $this),
        ]);

        return implode("\n\n", $parts);
    }

    public function googleCalendarUrl(): string
    {
        return 'https://calendar.google.com/calendar/render?action=TEMPLATE'
            .'&text='.rawurlencode($this->title)
            .'&dates='.rawurlencode($this->calendarTimestamp($this->starts_at).'/'.$this->calendarTimestamp($this->ends_at))
            .'&details='.rawurlencode($this->calendarDescription())
            .'&location='.rawurlencode($this->calendarLocation());
    }

    public function outlookCalendarUrl(): string
    {
        return 'https://outlook.office.com/calendar/0/deeplink/compose?path=/calendar/action/compose&rru=addevent'
            .'&subject='.rawurlencode($this->title)
            .'&startdt='.rawurlencode($this->starts_at->clone()->utc()->format('Y-m-d\TH:i:s\Z'))
            .'&enddt='.rawurlencode($this->ends_at->clone()->utc()->format('Y-m-d\TH:i:s\Z'))
            .'&body='.rawurlencode($this->calendarDescription())
            .'&location='.rawurlencode($this->calendarLocation());
    }

    protected function calendarTimestamp(CarbonInterface $timestamp): string
    {
        return $timestamp->clone()->utc()->format('Ymd\THis\Z');
    }

    public function calendarLocation(): string
    {
        if ($this->is_online) {
            return $this->online_url ?: 'Online event';
        }

        return trim(($this->venue_name ?? '').' '.($this->venue_address ?? ''));
    }
}
