<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\Organization;
use App\Support\ImageUploads;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        return view('events.index', [
            'events' => Event::query()
                ->with('organization')
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
            'pendingInvitations' => $event->invitations->whereNull('accepted_at'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'venue_name' => ['required', 'string', 'max:255'],
            'venue_address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'timezone' => ['required', 'string', 'max:64'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'visibility' => ['required', Rule::in(['public', 'private', 'unlisted'])],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $organization = Organization::findOrFail($validated['organization_id']);
        abort_unless($request->user()->isManagerOf($organization), 403);

        $imagePath = $request->file('image')
            ? ImageUploads::storeResized($request->file('image'), 'event-images', 1600, 900, 'cover')
            : null;

        $event = Event::create([
            ...$validated,
            'creator_id' => $request->user()->id,
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(6)),
            'is_published' => true,
            'image_path' => $imagePath,
        ]);

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Event published.');
    }

    public function rsvp(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['interested', 'going', 'waitlist'])],
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
