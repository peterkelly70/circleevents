<x-app-layout>
    <x-slot name="header">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-amber-300">{{ $event->organization->name }}</p>
                <h1 class="text-3xl font-black text-stone-100">{{ $event->title }}</h1>
            </div>
            @auth
                @if (auth()->user()->isManagerOf($event->organization))
                    <a href="{{ route('events.edit', $event) }}" class="rounded-full bg-amber-300 px-5 py-3 text-sm font-semibold text-stone-950">Edit event</a>
                @endif
            @endauth
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-10 sm:px-6 lg:grid-cols-[1.2fr_.8fr] lg:px-8">
        <section class="rounded-[2rem] border border-white/10 bg-stone-900/70 p-8 shadow-sm ring-1 ring-white/10">
            @if ($event->image_path)
                <img src="{{ $event->imageUrl() }}" alt="{{ $event->title }}" class="mb-8 h-80 w-full rounded-[1.5rem] object-cover">
            @endif

            <p class="text-sm uppercase tracking-[0.25em] text-amber-300">{{ $event->starts_at->format('l, d F Y · g:i A') }} to {{ $event->ends_at->format('g:i A') }} {{ $event->timezone }} · {{ $event->visibilityLabel() }}</p>
            <p class="mt-5 text-lg leading-8 text-stone-300">{{ $event->summary }}</p>
            <div class="mt-8 grid gap-6 md:grid-cols-2">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">Venue</h2>
                    <p class="mt-2 text-xl font-bold text-stone-100">{{ $event->venue_name }}</p>
                    <p class="mt-2 text-stone-400">{{ $event->venue_address }}</p>
                    <p class="text-stone-400">{{ $event->city }}</p>
                </div>
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">Organizer</h2>
                    <p class="mt-2 text-xl font-bold text-stone-100">{{ $event->organization->name }}</p>
                    <p class="mt-2 text-stone-400">Published by {{ $event->creator->name }}</p>
                    <a href="{{ route('organizations.show', $event->organization) }}" class="mt-3 inline-flex text-sm font-semibold text-amber-300">View organization</a>
                </div>
            </div>
            <div class="mt-8 border-t border-white/10 pt-8">
                <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">Description</h2>
                <div class="prose prose-invert mt-4 max-w-none text-stone-300">
                    <p>{{ $event->description ?: 'No extended description provided yet.' }}</p>
                </div>
            </div>

            <div class="mt-8 border-t border-white/10 pt-8">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">Discussion</h2>
                    <span class="text-sm text-stone-500">{{ $discussionPosts->count() }} posts</span>
                </div>

                @auth
                    <form method="POST" action="{{ route('events.discussion.store', $event) }}" enctype="multipart/form-data" class="mt-4">
                        @csrf
                        <textarea name="body" rows="4" placeholder="Ask a question, post an update, or coordinate with attendees." class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500">{{ old('body') }}</textarea>
                        <p class="mt-2 text-xs text-stone-500">Supports BBCode: `[b]bold[/b]`, `[i]italic[/i]`, `[quote]quote[/quote]`, `[url=https://...]link[/url]`.</p>
                        <input name="image" type="file" accept="image/*" class="mt-3 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                        <button class="mt-3 rounded-full bg-amber-300 px-5 py-3 text-sm font-semibold text-stone-950">Post comment</button>
                    </form>
                @else
                    <p class="mt-4 text-sm text-stone-400">Log in to join the event discussion.</p>
                @endauth

                <div class="mt-6 space-y-4">
                    @forelse ($discussionPosts as $post)
                        <div class="rounded-2xl border border-white/10 bg-black/20 p-5">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-semibold text-stone-100">{{ $post->user->name }}</p>
                                <p class="text-xs uppercase tracking-[0.2em] text-stone-500">{{ $post->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="mt-3 prose prose-invert max-w-none text-stone-300">{!! \App\Support\Bbcode::render($post->body) !!}</div>
                            @if ($post->image_path)
                                <img src="{{ $post->imageUrl() }}" alt="Discussion attachment" class="mt-4 max-h-[28rem] w-full rounded-2xl object-cover">
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-stone-400">No discussion yet. Start the thread.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-[2rem] bg-stone-950 p-6 text-stone-100 shadow-sm">
                <h2 class="text-2xl font-bold">RSVP</h2>
                <div class="mt-5 grid grid-cols-2 gap-3 text-center sm:grid-cols-4">
                    <div class="rounded-2xl bg-white/5 p-3">
                        <div class="text-2xl font-black">{{ $rsvpCounts->get('going', 0) }}</div>
                        <div class="mt-1 text-xs uppercase tracking-[0.2em] text-stone-400">Going</div>
                    </div>
                    <div class="rounded-2xl bg-white/5 p-3">
                        <div class="text-2xl font-black">{{ $rsvpCounts->get('interested', 0) }}</div>
                        <div class="mt-1 text-xs uppercase tracking-[0.2em] text-stone-400">Interested</div>
                    </div>
                    <div class="rounded-2xl bg-white/5 p-3">
                        <div class="text-2xl font-black">{{ $rsvpCounts->get('waitlist', 0) }}</div>
                        <div class="mt-1 text-xs uppercase tracking-[0.2em] text-stone-400">Waitlist</div>
                    </div>
                    <div class="rounded-2xl bg-white/5 p-3">
                        <div class="text-2xl font-black">{{ $rsvpCounts->get('not-going', 0) }}</div>
                        <div class="mt-1 text-xs uppercase tracking-[0.2em] text-stone-400">Not going</div>
                    </div>
                </div>

                @if ($event->mailingList)
                    <div class="mt-5 rounded-2xl border border-white/10 bg-white/5 p-4 text-left">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Event updates</p>
                                <p class="mt-2 text-lg font-bold text-stone-100">{{ $event->mailingList->name }}</p>
                                <p class="mt-2 text-sm text-stone-400">{{ $event->mailingList->subscribers->count() }} subscribers</p>
                            </div>
                            <a href="{{ route('mailing-lists.show', $event->mailingList) }}" class="rounded-full border border-white/10 px-4 py-2 text-sm font-semibold text-stone-100">
                                View list
                            </a>
                        </div>

                        @auth
                            <form method="POST" action="{{ route('mailing-lists.subscribe', $event->mailingList) }}" class="mt-4">
                                @csrf
                                <button class="w-full rounded-full bg-emerald-400 px-5 py-3 font-semibold text-stone-950">Get event emails</button>
                            </form>
                        @else
                            <p class="mt-4 text-sm text-stone-300">Log in to subscribe to this event’s update list.</p>
                        @endauth
                    </div>
                @endif

                @auth
                    <form method="POST" action="{{ route('events.rsvp', $event) }}" class="mt-6 space-y-4">
                        @csrf
                        <select name="status" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">
                            <option value="interested">Interested</option>
                            <option value="going">Going</option>
                            <option value="waitlist">Waitlist</option>
                            <option value="not-going">Not going</option>
                        </select>
                        <textarea name="notes" rows="3" placeholder="Optional note" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white"></textarea>
                        <button class="w-full rounded-full bg-amber-300 px-5 py-3 font-semibold text-stone-950">Save RSVP</button>
                    </form>
                @else
                    <p class="mt-6 text-sm text-stone-300">Log in to track interest and attendance.</p>
                    <a href="{{ route('login') }}" class="mt-4 inline-flex rounded-full bg-amber-300 px-5 py-3 font-semibold text-stone-950">Log in</a>
                @endauth
            </section>

            @auth
                @if (auth()->user()->isManagerOf($event->organization))
                    <section class="rounded-[2rem] border border-white/10 bg-stone-900/70 p-6 shadow-sm ring-1 ring-white/10">
                        <h2 class="text-2xl font-bold text-stone-100">Invite people</h2>
                        <form method="POST" action="{{ route('events.invitations.store', $event) }}" class="mt-5 space-y-4">
                            @csrf
                            <input name="name" placeholder="Name (optional)" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500">
                            <input name="email" type="email" placeholder="Email address" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500" required>
                            <textarea name="message" rows="3" placeholder="Optional invitation message" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500"></textarea>
                            <button class="w-full rounded-full bg-emerald-400 px-5 py-3 font-semibold text-stone-950">Send invite</button>
                        </form>

                        <div class="mt-6 space-y-3">
                            @forelse ($pendingInvitations as $invitation)
                                <div class="rounded-2xl border border-white/10 bg-black/20 p-4 text-sm">
                                    <div class="font-semibold text-stone-100">{{ $invitation->name ?: $invitation->email }}</div>
                                    <div class="mt-1 text-stone-400">{{ $invitation->email }}</div>
                                </div>
                            @empty
                                <p class="text-sm text-stone-400">No pending invites.</p>
                            @endforelse
                        </div>
                    </section>
                @endif
            @endauth
        </aside>
    </div>
</x-app-layout>
