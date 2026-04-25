<x-app-layout>
    @php
        $theme = \App\Support\OrganizationThemes::get(auth()->user()?->resolvedOrganizationThemeKey($event->organization) ?? $event->organization->theme_key);
        $themeProseClass = $theme['mode'] === 'light' ? 'prose' : 'prose prose-invert';
        $dividerClass = $theme['mode'] === 'light' ? 'border-stone-300/60' : 'border-white/10';
        $statusClass = $theme['mode'] === 'light'
            ? 'border-emerald-300/40 bg-emerald-50 text-emerald-800'
            : 'border-emerald-300/20 bg-emerald-300/10 text-emerald-100';
        $selectReadableClass = $theme['mode'] === 'light' ? 'select-readable-light' : 'select-readable';
    @endphp
    <x-slot name="header">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] {{ $theme['eyebrow'] }}">{{ $event->organization->name }}</p>
                <h1 class="text-3xl font-black {{ $theme['header_heading'] }} {{ $theme['font_display'] }}">{{ $event->title }}</h1>
            </div>
            @auth
                @if (auth()->user()->isManagerOf($event->organization))
                    <div class="flex flex-wrap items-center gap-3">
                        <form method="POST" action="{{ route('events.announce', $event) }}">
                            @csrf
                            <button class="rounded-full border px-5 py-3 text-sm font-semibold {{ $theme['soft_button'] }}">Re-announce event</button>
                        </form>
                        @if ($event->organization->discord_webhook_url)
                            <form method="POST" action="{{ route('events.discord', $event) }}">
                                @csrf
                                <button class="rounded-full border px-5 py-3 text-sm font-semibold {{ $theme['accent_badge'] }}">Post to Discord</button>
                            </form>
                        @endif
                        <a href="{{ route('events.edit', $event) }}" class="rounded-full px-5 py-3 text-sm font-semibold {{ $theme['header_button'] }}">Edit event</a>
                    </div>
                @endif
            @endauth
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-10 sm:px-6 lg:grid-cols-[1.2fr_.8fr] lg:px-8 {{ $theme['mode'] === 'light' ? 'text-stone-900' : 'text-stone-100' }} {{ $theme['page_backdrop'] }} {{ $theme['font_body'] }}">
        @if (session('status'))
            <div class="rounded-3xl border px-5 py-4 text-sm font-medium lg:col-span-2 {{ $statusClass }}">
                {{ session('status') }}
            </div>
        @endif

        <section class="rounded-[2rem] border p-8 shadow-sm ring-1 {{ $theme['surface'] }}">
            @if ($event->image_path)
                <img src="{{ $event->imageUrl() }}" alt="{{ $event->title }}" class="mb-8 h-80 w-full rounded-[1.5rem] object-cover">
            @endif

            <p class="text-sm uppercase tracking-[0.25em] {{ $theme['link'] }}">{{ $event->starts_at->format('l, d F Y · g:i A') }} to {{ $event->ends_at->format('g:i A') }} {{ $event->timezone }} · {{ $event->visibilityLabel() }}</p>
            <p class="mt-5 text-lg leading-8 {{ $theme['body'] }}">{{ $event->summary }}</p>
            <div class="mt-5 flex flex-wrap items-center gap-3">
                <span class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Share</span>
                <button
                    type="button"
                    data-share-button
                    data-share-title="{{ $event->title }}"
                    data-share-text="{{ $event->summary }}"
                    data-share-url="{{ route('events.show', $event) }}"
                    data-tooltip="Share"
                    title="Share"
                    aria-label="Share"
                    class="share-icon-button {{ $theme['soft_button'] }}"
                >
                    <span aria-hidden="true">↗</span>
                </button>
                <a
                    href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('events.show', $event)) }}"
                    target="_blank"
                    rel="noreferrer"
                    data-tooltip="Share on Facebook"
                    title="Share on Facebook"
                    aria-label="Share on Facebook"
                    class="share-icon-button {{ $theme['soft_button'] }}"
                >
                    <span aria-hidden="true">f</span>
                </a>
                <a
                    href="https://x.com/intent/tweet?text={{ urlencode($event->title.' - '.$event->summary) }}&url={{ urlencode(route('events.show', $event)) }}"
                    target="_blank"
                    rel="noreferrer"
                    data-tooltip="Share on X"
                    title="Share on X"
                    aria-label="Share on X"
                    class="share-icon-button {{ $theme['soft_button'] }}"
                >
                    <span aria-hidden="true">x</span>
                </a>
                <a
                    href="mailto:?subject={{ rawurlencode($event->title) }}&body={{ rawurlencode($event->summary . "\n\n" . route('events.show', $event)) }}"
                    data-tooltip="Share by email"
                    title="Share by email"
                    aria-label="Share by email"
                    class="share-icon-button {{ $theme['soft_button'] }}"
                >
                    <span aria-hidden="true">✉</span>
                </a>
                <button
                    type="button"
                    data-copy-button
                    data-copy-text="{{ route('events.show', $event) }}"
                    data-copy-success="Event link copied"
                    data-tooltip="Copy link"
                    title="Copy link"
                    aria-label="Copy link"
                    class="share-icon-button {{ $theme['soft_button'] }}"
                >
                    <span aria-hidden="true">⧉</span>
                </button>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <span class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Save</span>
                <a href="{{ $event->googleCalendarUrl() }}" target="_blank" rel="noreferrer" class="rounded-full border px-4 py-2 text-sm font-semibold transition {{ $theme['soft_button'] }}">Google Calendar</a>
                <a href="{{ $event->outlookCalendarUrl() }}" target="_blank" rel="noreferrer" class="rounded-full border px-4 py-2 text-sm font-semibold transition {{ $theme['soft_button'] }}">Outlook</a>
                <a href="{{ route('events.calendar', $event) }}" class="rounded-full border px-4 py-2 text-sm font-semibold transition {{ $theme['soft_button'] }}">Download .ics</a>
            </div>
            <div class="mt-8 grid gap-6 md:grid-cols-2">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">{{ $event->is_online ? 'Format' : 'Venue' }}</h2>
                    @if ($event->is_online)
                        <p class="mt-2 text-xl font-bold {{ $theme['heading'] }}">Online event</p>
                        <p class="mt-2 {{ $theme['meta'] }}">No physical location is stored for this event.</p>
                        @if ($event->online_url)
                            <a href="{{ $event->online_url }}" target="_blank" rel="noreferrer" class="mt-4 inline-flex rounded-full px-5 py-3 text-sm font-semibold transition {{ $theme['secondary_button'] }}">Open event link</a>
                        @endif
                    @else
                        <p class="mt-2 text-xl font-bold {{ $theme['heading'] }}">{{ $event->venue_name }}</p>
                        <p class="mt-2 {{ $theme['meta'] }}">{{ $event->venue_address }}</p>
                        <p class="{{ $theme['meta'] }}">{{ $event->city }}</p>
                    @endif
                    @if (! $event->is_online && $event->googleMapsUrl())
                        <a href="{{ $event->googleMapsUrl() }}" target="_blank" rel="noreferrer" class="mt-3 inline-flex text-sm font-semibold {{ $theme['link'] }}">Open in Google Maps</a>
                    @endif
                </div>
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Organizer</h2>
                    <p class="mt-2 text-xl font-bold {{ $theme['heading'] }}">{{ $event->organization->name }}</p>
                    <p class="mt-2 {{ $theme['meta'] }}">Published by {{ $event->creator->name }}</p>
                    <a href="{{ route('organizations.show', $event->organization) }}" class="mt-3 inline-flex text-sm font-semibold {{ $theme['link'] }}">View organization</a>
                </div>
            </div>

            @if (! $event->is_online && $event->hasCoordinates())
                <div class="mt-8 border-t pt-8 {{ $dividerClass }}">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Map</h2>
                        @if ($event->googleMapsUrl())
                            <a href="{{ $event->googleMapsUrl() }}" target="_blank" rel="noreferrer" class="text-sm font-semibold {{ $theme['link'] }}">Directions</a>
                        @endif
                    </div>
                    <div
                        class="event-map mt-4"
                        data-event-map
                        data-event-title="{{ $event->title }}"
                        data-event-latitude="{{ $event->latitude }}"
                        data-event-longitude="{{ $event->longitude }}"
                    ></div>
                </div>
            @endif
            <div class="mt-8 border-t pt-8 {{ $dividerClass }}">
                <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Description</h2>
                <div class="mt-4 max-w-none {{ $theme['body'] }} {{ $themeProseClass }}">
                    <p>{{ $event->description ?: 'No extended description provided yet.' }}</p>
                </div>
            </div>

            <div id="discussion" class="mt-8 border-t pt-8 {{ $dividerClass }}">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Discussion</h2>
                    <span class="text-sm {{ $theme['muted'] }}">{{ $discussionPosts->count() }} posts</span>
                </div>

                @auth
                    <form method="POST" action="{{ route('events.discussion.store', $event) }}" enctype="multipart/form-data" class="mt-4">
                        @csrf
                        <textarea name="body" rows="4" placeholder="Ask a question, post an update, or coordinate with attendees." class="w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}">{{ old('body') }}</textarea>
                        <p class="mt-2 text-xs {{ $theme['muted'] }}">Supports BBCode: `[b]bold[/b]`, `[i]italic[/i]`, `[quote]quote[/quote]`, `[url=https://...]link[/url]`.</p>
                        <input name="image" type="file" accept="image/*" class="mt-3 w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}">
                        <button class="mt-3 rounded-full px-5 py-3 text-sm font-semibold {{ $theme['primary_button'] }}">Post comment</button>
                    </form>
                @else
                    <p class="mt-4 text-sm {{ $theme['meta'] }}">Log in to join the event discussion.</p>
                @endauth

                <div class="mt-6 space-y-4">
                    @forelse ($discussionPosts as $post)
                        <div class="rounded-2xl border p-5 {{ $theme['panel'] }}">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <x-user-avatar :user="$post->user" size="sm" :shell="$theme['logo_shell']" />
                                    <p class="font-semibold {{ $theme['heading'] }}">{{ $post->user->name }}</p>
                                </div>
                                <p class="text-xs uppercase tracking-[0.2em] {{ $theme['muted'] }}">{{ $post->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="mt-3 max-w-none {{ $theme['body'] }} {{ $themeProseClass }}">{!! \App\Support\Bbcode::render($post->body) !!}</div>
                            @if ($post->image_path)
                                <img src="{{ $post->imageUrl() }}" alt="Discussion attachment" class="mt-4 max-h-[28rem] w-full rounded-2xl object-cover">
                            @endif
                        </div>
                    @empty
                        <p class="text-sm {{ $theme['meta'] }}">No discussion yet. Start the thread.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-[2rem] border p-6 shadow-sm ring-1 {{ $theme['surface_secondary'] ?? $theme['surface'] }}">
                <h2 class="text-2xl font-bold {{ $theme['heading'] }}">RSVP</h2>
                <div class="mt-5 grid grid-cols-2 gap-3 text-center sm:grid-cols-4">
                    <div class="rounded-2xl border p-3 {{ $theme['panel'] }}">
                        <div class="text-2xl font-black {{ $theme['heading'] }}">{{ $rsvpCounts->get('going', 0) }}</div>
                        <div class="mt-1 text-xs uppercase tracking-[0.2em] {{ $theme['muted'] }}">Going</div>
                    </div>
                    <div class="rounded-2xl border p-3 {{ $theme['panel'] }}">
                        <div class="text-2xl font-black {{ $theme['heading'] }}">{{ $rsvpCounts->get('interested', 0) }}</div>
                        <div class="mt-1 text-xs uppercase tracking-[0.2em] {{ $theme['muted'] }}">Maybe</div>
                    </div>
                    <div class="rounded-2xl border p-3 {{ $theme['panel'] }}">
                        <div class="text-2xl font-black {{ $theme['heading'] }}">{{ $rsvpCounts->get('waitlist', 0) }}</div>
                        <div class="mt-1 text-xs uppercase tracking-[0.2em] {{ $theme['muted'] }}">Waitlist</div>
                    </div>
                    <div class="rounded-2xl border p-3 {{ $theme['panel'] }}">
                        <div class="text-2xl font-black {{ $theme['heading'] }}">{{ $rsvpCounts->get('not-going', 0) }}</div>
                        <div class="mt-1 text-xs uppercase tracking-[0.2em] {{ $theme['muted'] }}">Not going</div>
                    </div>
                </div>

                @if ($event->mailingList)
                    <div class="mt-5 rounded-2xl border p-4 text-left {{ $theme['panel'] }}">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] {{ $theme['muted'] }}">Event updates</p>
                                <p class="mt-2 text-lg font-bold {{ $theme['heading'] }}">{{ $event->mailingList->name }}</p>
                                <p class="mt-2 text-sm {{ $theme['meta'] }}">{{ $event->mailingList->subscribers->count() }} subscribers</p>
                            </div>
                            <a href="{{ route('mailing-lists.show', $event->mailingList) }}" class="rounded-full border px-4 py-2 text-sm font-semibold {{ $theme['soft_button'] }}">
                                View list
                            </a>
                        </div>

                        @auth
                            <form method="POST" action="{{ route('mailing-lists.subscribe', $event->mailingList) }}" class="mt-4">
                                @csrf
                                <button class="w-full rounded-full px-5 py-3 font-semibold {{ $theme['secondary_button'] }}">Get event emails</button>
                            </form>
                        @else
                            <p class="mt-4 text-sm {{ $theme['meta'] }}">Log in to subscribe to this event’s update list.</p>
                        @endauth
                    </div>
                @endif

                @auth
                    <form method="POST" action="{{ route('events.rsvp', $event) }}" class="mt-6 space-y-4">
                        @csrf
                        <select name="status" class="{{ $selectReadableClass }} w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}">
                            <option value="interested" @selected(old('status', $currentRsvp?->status) === 'interested')>Maybe</option>
                            <option value="going" @selected(old('status', $currentRsvp?->status) === 'going')>Going</option>
                            <option value="waitlist" @selected(old('status', $currentRsvp?->status) === 'waitlist')>Waitlist</option>
                            <option value="not-going" @selected(old('status', $currentRsvp?->status) === 'not-going')>Not going</option>
                        </select>
                        <textarea name="notes" rows="3" placeholder="Optional note" class="w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}">{{ old('notes', $currentRsvp?->notes) }}</textarea>
                        <div class="rounded-2xl border p-4 {{ $theme['panel'] }}">
                            <p class="text-sm font-semibold {{ $theme['heading'] }}">Your reminders</p>
                            <p class="mt-1 text-xs leading-5 {{ $theme['meta'] }}">These reminders apply when you mark yourself as going. You can tick any combination.</p>
                            <div class="mt-3 space-y-3">
                                <label class="flex items-center gap-3 text-sm {{ $theme['body'] }}">
                                    <input type="checkbox" name="remind_one_week_before" value="1" @checked(old('remind_one_week_before', $currentRsvp?->remind_one_week_before)) class="rounded border-current bg-transparent {{ $theme['checkbox'] }}">
                                    Remind me 1 week before
                                </label>
                                <label class="flex items-center gap-3 text-sm {{ $theme['body'] }}">
                                    <input type="checkbox" name="remind_one_day_before" value="1" @checked(old('remind_one_day_before', $currentRsvp?->remind_one_day_before ?? true)) class="rounded border-current bg-transparent {{ $theme['checkbox'] }}">
                                    Remind me 1 day before
                                </label>
                                <label class="flex items-center gap-3 text-sm {{ $theme['body'] }}">
                                    <input type="checkbox" name="remind_one_hour_before" value="1" @checked(old('remind_one_hour_before', $currentRsvp?->remind_one_hour_before)) class="rounded border-current bg-transparent {{ $theme['checkbox'] }}">
                                    Remind me 1 hour before
                                </label>
                            </div>
                        </div>
                        <button class="w-full rounded-full px-5 py-3 font-semibold {{ $theme['primary_button'] }}">Save RSVP</button>
                    </form>
                @else
                    <p class="mt-6 text-sm {{ $theme['meta'] }}">Log in to track interest and attendance.</p>
                    <a href="{{ route('login') }}" class="mt-4 inline-flex rounded-full px-5 py-3 font-semibold {{ $theme['primary_button'] }}">Log in</a>
                @endauth
            </section>

            @auth
                @if (auth()->user()->isManagerOf($event->organization))
                    <section class="rounded-[2rem] border p-6 shadow-sm ring-1 {{ $theme['surface'] }}">
                        <h2 class="text-2xl font-bold {{ $theme['heading'] }}">Outbound posting</h2>
                        <div class="mt-4 rounded-2xl border p-4 {{ $theme['panel'] }}">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold {{ $theme['heading'] }}">Discord</p>
                                    <p class="mt-1 text-sm {{ $theme['meta'] }}">
                                        @if (! $event->organization->discord_webhook_url)
                                            Not connected for this organization.
                                        @elseif ($event->discord_posted_at)
                                            Last posted {{ $event->discord_posted_at->diffForHumans() }}.
                                        @elseif ($event->visibility === 'private')
                                            Connected. Private means hidden from public discovery; Discord posting is still allowed for this channel.
                                        @elseif ($event->organization->auto_post_discord_events)
                                            Auto-post is enabled, but this event has not been marked as posted.
                                        @else
                                            Connected. Use the button to post manually.
                                        @endif
                                    </p>
                                </div>
                                @if ($event->organization->discord_webhook_url)
                                    <form method="POST" action="{{ route('events.discord', $event) }}">
                                        @csrf
                                        <button class="rounded-full px-4 py-2 text-sm font-semibold {{ $theme['secondary_button'] }}">Post now</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[2rem] border p-6 shadow-sm ring-1 {{ $theme['surface'] }}">
                        <h2 class="text-2xl font-bold {{ $theme['heading'] }}">Invite people</h2>
                        <form method="POST" action="{{ route('events.invitations.store', $event) }}" class="mt-5 space-y-4">
                            @csrf
                            <input type="hidden" name="delivery" value="email">
                            <input name="name" placeholder="Name (optional)" class="w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}">
                            <input name="email" type="email" placeholder="Email address" class="w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}" required>
                            <textarea name="message" rows="3" placeholder="Optional invitation message" class="w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}"></textarea>
                            <button class="w-full rounded-full px-5 py-3 font-semibold {{ $theme['secondary_button'] }}">Send invite</button>
                        </form>

                        <form method="POST" action="{{ route('events.invitations.store', $event) }}" class="mt-5 space-y-4 border-t pt-5 {{ $dividerClass }}">
                            @csrf
                            <input type="hidden" name="delivery" value="share">
                            <h3 class="text-lg font-semibold {{ $theme['heading'] }}">Create share invite code</h3>
                            <input name="name" placeholder="Label (optional)" class="w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}">
                            <input name="expires_at" type="datetime-local" class="w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}">
                            <input name="max_uses" type="number" min="1" step="1" placeholder="Max uses (optional)" class="w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}">
                            <textarea name="message" rows="3" placeholder="Optional invitation message" class="w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}"></textarea>
                            <button class="w-full rounded-full px-5 py-3 font-semibold {{ $theme['primary_button'] }}">Create share code</button>
                        </form>

                        <div class="mt-6 border-t pt-6 {{ $dividerClass }}">
                            <h3 class="text-lg font-semibold {{ $theme['heading'] }}">Active share invites</h3>
                            <div class="mt-4 space-y-3">
                                @forelse ($shareInvitations as $invitation)
                                    <div class="rounded-2xl border p-4 text-sm {{ $theme['panel'] }}">
                                        <div class="font-semibold {{ $theme['heading'] }}">{{ $invitation->name ?: 'Share invite' }}</div>
                                        <div class="mt-1 text-xs uppercase tracking-[0.2em] {{ $theme['link'] }}">Code {{ $invitation->share_code }}</div>
                                        <div class="mt-2 {{ $theme['meta'] }}">{{ $invitation->expires_at ? 'Expires '.$invitation->expires_at->diffForHumans() : 'No expiry' }}</div>
                                        <div class="mt-1 {{ $theme['meta'] }}">{{ $invitation->use_count }} uses{{ $invitation->max_uses ? ' of '.$invitation->max_uses : '' }}</div>
                                        <input readonly value="{{ route('event-invitations.accept-code', $invitation->share_code) }}" class="mt-3 w-full rounded-2xl border px-4 py-3 text-sm {{ $theme['input'] }}">
                                        <form method="POST" action="{{ route('events.invitations.revoke', [$event, $invitation]) }}" class="mt-3">
                                            @csrf
                                            <button class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['danger_button'] }}">Revoke code</button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="text-sm {{ $theme['meta'] }}">No active share invites.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="mt-6 space-y-3 border-t pt-6 {{ $dividerClass }}">
                            @forelse ($pendingInvitations as $invitation)
                                <div x-data="{ cancelling: false }" class="rounded-2xl border p-4 text-sm {{ $theme['panel'] }}">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="font-semibold {{ $theme['heading'] }}">{{ $invitation->name ?: $invitation->email }}</div>
                                            <div class="mt-1 {{ $theme['meta'] }}">{{ $invitation->email }}</div>
                                        </div>
                                        <button type="button" @click="cancelling = ! cancelling" class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['danger_button'] }}">Cancel invite</button>
                                    </div>
                                    <form x-show="cancelling" x-cloak method="POST" action="{{ route('events.invitations.revoke', [$event, $invitation]) }}" class="mt-4 space-y-3">
                                        @csrf
                                        <label class="block text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Reason shown if they use the invite</label>
                                        <textarea name="revoked_reason" rows="3" maxlength="500" class="w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}" placeholder="Example: We changed the guest list for this event." required></textarea>
                                        <div class="flex gap-3">
                                            <button class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['danger_button'] }}">Confirm cancelation</button>
                                            <button type="button" @click="cancelling = false" class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['soft_button'] }}">Keep invite</button>
                                        </div>
                                    </form>
                                </div>
                            @empty
                                <p class="text-sm {{ $theme['meta'] }}">No pending invites.</p>
                            @endforelse
                        </div>
                    </section>
                @endif
            @endauth
        </aside>
    </div>
</x-app-layout>
