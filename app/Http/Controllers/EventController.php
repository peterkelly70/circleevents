<?php

namespace App\Http\Controllers;

use App\Mail\EventPublishedMail;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\MailingList;
use App\Models\Organization;
use App\Support\DiscordEventPublisher;
use App\Support\FacebookEventPublisher;
use App\Support\ImageUploads;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventController extends Controller
{
    protected function mergeDateTimeFields(Request $request): void
    {
        $request->merge([
            'starts_at' => $request->input('starts_at') ?: $this->combineDateAndTime(
                $request->input('start_date'),
                $request->input('start_time'),
            ),
            'ends_at' => $request->input('ends_at') ?: $this->combineDateAndTime(
                $request->input('end_date'),
                $request->input('end_time'),
            ),
            'repeat_until' => $request->input('repeat_until') ?: $this->combineDateAndTime(
                $request->input('repeat_until_date'),
                $request->input('repeat_until_time'),
            ),
        ]);
    }

    protected function combineDateAndTime(?string $date, ?string $time): ?string
    {
        if (! $date || ! $time) {
            return null;
        }

        return trim($date).' '.trim($time).':00';
    }

    public function index(): View
    {
        return view('events.index', [
            'events' => Event::query()
                ->with('organization')
                ->whereHas('organization', fn ($query) => $query->where('approval_status', 'approved'))
                ->where('is_published', true)
                ->where('visibility', 'public')
                ->where('starts_at', '>=', now()->subDay())
                ->orderBy('starts_at')
                ->paginate(12),
        ]);
    }

    public function show(Event $event): View
    {
        abort_unless($event->isVisibleTo(request()->user()), 403);

        $event->load([
            'organization',
            'mailingList.subscribers',
            'creator',
            'rsvps.user',
            'discussionPosts.user',
            'invitations',
        ]);

        return view('events.show', [
            'event' => $event,
            'rsvpCounts' => $event->rsvps
                ->groupBy('status')
                ->map->count(),
            'discussionPosts' => $event->discussionPosts,
            'pendingInvitations' => $event->invitations
                ->whereNotNull('email')
                ->whereNull('accepted_at')
                ->reject(fn ($invitation) => $invitation->isRevoked())
                ->values(),
            'shareInvitations' => $event->invitations
                ->whereNull('email')
                ->reject(fn ($invitation) => $invitation->isExpired() || $invitation->isRevoked())
                ->values(),
        ]);
    }

    public function edit(Request $request, Event $event): View
    {
        abort_unless($request->user()->isManagerOf($event->organization), 403);

        return view('events.edit', [
            'event' => $event->load('organization'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->mergeDateTimeFields($request);

        $validated = $this->validatedEventData($request);

        $organization = Organization::findOrFail($validated['organization_id']);
        abort_unless($request->user()->isManagerOf($organization), 403);

        if (! $organization->isApproved() && ! $request->user()->is_admin) {
            return back()->with('status', 'This organization is still waiting for admin approval before it can publish events.');
        }

        $imagePath = $request->file('image')
            ? ImageUploads::storeResizedPublicImage($request->file('image'), 'event-images', 1600, 900)
            : null;

        $eventSeries = DB::transaction(function () use ($validated, $request, $organization, $imagePath) {
            $seriesPayloads = $this->buildSeriesPayloads($validated, $request->user()->id, $organization->id, $imagePath);

            return collect($seriesPayloads)->map(function (array $payload) use ($organization) {
                $mailingList = MailingList::create([
                    'organization_id' => $organization->id,
                    'name' => $payload['title'].' updates · '.$payload['starts_at']->format('d M Y'),
                    'slug' => Str::slug($payload['title'].' updates '.$payload['starts_at']->format('Y-m-d')).'-'.Str::lower(Str::random(6)),
                    'description' => 'Automatic event update list for '.$payload['title'].' on '.$payload['starts_at']->format('d M Y').'.',
                    'audience' => 'all-members',
                ]);

                return Event::create([
                    ...$payload,
                    'mailing_list_id' => $mailingList->id,
                ]);
            });
        });

        $discordPostedCount = 0;
        $facebookPostedCount = 0;

        $eventSeries->each(function (Event $event) use (&$discordPostedCount, &$facebookPostedCount) {
            $this->notifyMailingListSubscribers($event);

            if (DiscordEventPublisher::publish($event)) {
                $discordPostedCount++;
            }

            if (FacebookEventPublisher::publish($event)) {
                $facebookPostedCount++;
            }
        });

        $event = $eventSeries->first();

        return redirect()
            ->route('events.show', $event)
            ->with('status', match (true) {
                $discordPostedCount > 0 && $facebookPostedCount > 0 => 'Event published and sent to Discord and Facebook.',
                $discordPostedCount > 0 => 'Event published and sent to Discord.',
                $facebookPostedCount > 0 => 'Event published and sent to Facebook.',
                default => 'Event published.',
            });
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        abort_unless($request->user()->isManagerOf($event->organization), 403);

        $this->mergeDateTimeFields($request);

        $validated = $this->validatedEventData($request, false);

        if ($request->hasFile('image')) {
            $validated['image_path'] = ImageUploads::storeResizedPublicImage(
                $request->file('image'),
                'event-images',
                1600,
                900,
            );
        }

        $event->update([
            ...$validated,
            'repeat_frequency' => ($validated['repeat_frequency'] ?? 'none') === 'none'
                ? null
                : ($validated['repeat_frequency'] ?? null),
            'repeat_until' => ($validated['repeat_frequency'] ?? 'none') === 'none'
                ? null
                : ($validated['repeat_until'] ?? null),
        ]);

        if ($event->mailingList) {
            $event->mailingList->update([
                'name' => $event->title.' updates · '.$event->starts_at->format('d M Y'),
                'description' => 'Automatic event update list for '.$event->title.' on '.$event->starts_at->format('d M Y').'.',
            ]);
        }

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Event updated.');
    }

    protected function validatedEventData(Request $request, bool $allowRepeat = true): array
    {
        return $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'venue_name' => ['required', 'string', 'max:255'],
            'venue_address' => ['nullable', 'string', 'max:255'],
            'google_place_id' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'city' => ['nullable', 'string', 'max:120'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'timezone' => ['required', 'string', 'max:64'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'visibility' => ['required', Rule::in(['public', 'private', 'unlisted'])],
            'repeat_frequency' => $allowRepeat
                ? ['nullable', Rule::in(['none', 'daily', 'weekly', 'monthly'])]
                : ['nullable', Rule::in(['none', 'daily', 'weekly', 'monthly'])],
            'repeat_until' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'image' => ['nullable', 'image', 'max:12288'],
        ]);
    }

    protected function buildSeriesPayloads(array $validated, int $creatorId, int $organizationId, ?string $imagePath): array
    {
        $start = CarbonImmutable::parse($validated['starts_at']);
        $end = CarbonImmutable::parse($validated['ends_at']);
        $repeatFrequency = $validated['repeat_frequency'] ?? 'none';
        $repeatUntil = ! empty($validated['repeat_until']) ? CarbonImmutable::parse($validated['repeat_until']) : null;
        $seriesId = $repeatFrequency !== 'none' ? (string) Str::uuid() : null;

        $payloads = [];
        $currentStart = $start;
        $currentEnd = $end;

        do {
            $payloads[] = [
                ...$validated,
                'organization_id' => $organizationId,
                'creator_id' => $creatorId,
                'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(6)),
                'is_published' => true,
                'image_path' => $imagePath,
                'recurrence_group' => $seriesId,
                'starts_at' => $currentStart,
                'ends_at' => $currentEnd,
                'repeat_frequency' => $repeatFrequency !== 'none' ? $repeatFrequency : null,
                'repeat_until' => $seriesId ? $repeatUntil : null,
            ];

            if ($repeatFrequency === 'none' || ! $repeatUntil) {
                break;
            }

            [$currentStart, $currentEnd] = match ($repeatFrequency) {
                'daily' => [$currentStart->addDay(), $currentEnd->addDay()],
                'weekly' => [$currentStart->addWeek(), $currentEnd->addWeek()],
                'monthly' => [$currentStart->addMonthNoOverflow(), $currentEnd->addMonthNoOverflow()],
            };
        } while ($currentStart->lessThanOrEqualTo($repeatUntil));

        return $payloads;
    }

    protected function notifyMailingListSubscribers(Event $event): void
    {
        if ($event->visibility === 'private') {
            return;
        }

        $event->loadMissing('organization');

        $subscribers = $event->organization
            ->mailingLists()
            ->with([
                'subscribers' => fn ($query) => $query
                    ->wherePivot('status', 'subscribed')
                    ->select('users.id', 'users.name', 'users.email'),
            ])
            ->get()
            ->flatMap->subscribers
            ->unique('email')
            ->values();

        foreach ($subscribers as $subscriber) {
            Mail::to($subscriber->email)->send(new EventPublishedMail($event, $subscriber));
        }
    }

    public function rsvp(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['interested', 'going', 'waitlist', 'not-going'])],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        EventRsvp::updateOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => $request->user()->id,
            ],
            $validated,
        );

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Your RSVP has been updated.');
    }
}
