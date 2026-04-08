<x-app-layout>
    <x-slot name="header">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-amber-600">Organizer dashboard</p>
                <h2 class="text-3xl font-black leading-tight text-stone-900">{{ __('Run your event network') }}</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="#new-mailing-list" class="rounded-full bg-stone-900 px-5 py-3 text-sm font-semibold text-white">New mailing list</a>
                <p class="max-w-md text-sm text-stone-600">Create organizations, publish events, and manage audience subscriptions from one place.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1.1fr_.9fr] lg:px-8">
            <div class="space-y-6">
                @if (session('status'))
                    <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                <section class="rounded-[2rem] border border-white/10 bg-stone-900/70 p-6 shadow-sm ring-1 ring-white/10">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-2xl font-bold text-stone-100">Following feed</h3>
                            <p class="mt-1 text-sm text-stone-400">Recent posts and announcements from the organizations you follow.</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($feedItems as $item)
                            <div class="rounded-3xl border border-white/10 bg-black/20 p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-3 text-xs uppercase tracking-[0.2em]">
                                            <span class="{{ $item->type === 'message' ? 'text-emerald-300' : 'text-amber-300' }}">
                                                {{ $item->type === 'message' ? 'Member message' : 'Community post' }}
                                            </span>
                                            <a href="{{ route('organizations.show', $item->organization) }}" class="text-stone-500 hover:text-stone-300">
                                                {{ $item->organization->name }}
                                            </a>
                                        </div>

                                        @if ($item->title)
                                            <h4 class="mt-3 text-xl font-bold text-stone-100">{{ $item->title }}</h4>
                                        @endif

                                        <p class="mt-2 text-sm text-stone-400">By {{ $item->author->name }}</p>
                                    </div>

                                    <p class="shrink-0 text-xs uppercase tracking-[0.2em] text-stone-500">{{ $item->created_at->diffForHumans() }}</p>
                                </div>

                                <p class="mt-4 whitespace-pre-line text-stone-300">{{ $item->body }}</p>
                            </div>
                        @empty
                            <div class="rounded-3xl border border-dashed border-white/15 p-5 text-sm text-stone-400">
                                No feed activity yet. Follow more organizations or wait for new posts and announcements.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-stone-200">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-2xl font-bold text-stone-900">Your organizations</h3>
                            <p class="mt-1 text-sm text-stone-600">Manager access controls who can publish events and own mailing lists.</p>
                        </div>
                        <span class="rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-800">{{ $managedOrganizations->count() }} managed</span>
                    </div>
                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @forelse ($managedOrganizations as $organization)
                            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-5 transition hover:-translate-y-1 hover:border-amber-300">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-stone-900 text-lg font-black text-amber-200">
                                        @if ($organization->avatar_path)
                                            <img src="{{ $organization->avatarUrl() }}" alt="{{ $organization->name }} logo" class="h-full w-full object-cover">
                                        @else
                                            <span>{{ str($organization->name)->substr(0, 2)->upper() }}</span>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <p class="text-xs uppercase tracking-[0.2em] text-stone-500">{{ $organization->pivot->role }}</p>
                                        <a href="{{ route('organizations.show', $organization) }}" class="mt-2 block text-xl font-bold text-stone-900">{{ $organization->name }}</a>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm text-stone-600">{{ $organization->summary }}</p>
                                <div class="mt-4 flex gap-3">
                                    <a href="{{ route('organizations.show', $organization) }}" class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700">View</a>
                                    <a href="{{ route('organizations.edit', $organization) }}" class="rounded-full bg-stone-900 px-4 py-2 text-sm font-semibold text-white">Edit</a>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-3xl border border-dashed border-stone-300 p-5 text-sm text-stone-600">No organizations yet. Use the form on the right to create the first one.</div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-stone-200">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-2xl font-bold text-stone-900">Upcoming events</h3>
                            <p class="mt-1 text-sm text-stone-600">Live event pages work as the public replacement for Facebook Events.</p>
                        </div>
                    </div>
                    <div class="mt-6 space-y-4">
                        @forelse ($upcomingEvents as $event)
                            <a href="{{ route('events.show', $event) }}" class="flex flex-col justify-between gap-4 rounded-3xl border border-stone-200 p-5 transition hover:border-amber-300 md:flex-row md:items-center">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.2em] text-amber-700">{{ $event->starts_at->format('D d M, g:i A') }}</p>
                                    <h4 class="mt-2 text-xl font-bold text-stone-900">{{ $event->title }}</h4>
                                    <p class="mt-2 text-sm text-stone-600">{{ $event->organization?->name ?? 'Unknown organization' }} · {{ $event->venue_name }}</p>
                                </div>
                                <div class="text-sm font-medium text-stone-500">{{ $event->timezone }}</div>
                            </a>
                        @empty
                            <div class="rounded-3xl border border-dashed border-stone-300 p-5 text-sm text-stone-600">No events published yet.</div>
                        @endforelse
                    </div>
                </section>

                <section class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-stone-200">
                        <h3 class="text-2xl font-bold text-stone-900">Your subscriptions</h3>
                        <div class="mt-4 space-y-3">
                            @forelse ($subscriptions as $subscription)
                                <a href="{{ route('mailing-lists.show', $subscription) }}" class="block rounded-2xl bg-stone-50 p-4 text-sm text-stone-700 transition hover:bg-amber-50">
                                    <div class="font-semibold text-stone-900">{{ $subscription->name }}</div>
                                    <div class="mt-1">{{ $subscription->organization->name }}</div>
                                </a>
                            @empty
                                <p class="text-sm text-stone-600">You have not joined any mailing lists yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-stone-200">
                        <h3 class="text-2xl font-bold text-stone-900">Your RSVPs</h3>
                        <div class="mt-4 space-y-3">
                            @forelse ($rsvps as $rsvp)
                                @if ($rsvp->event)
                                    <a href="{{ route('events.show', $rsvp->event) }}" class="block rounded-2xl bg-stone-50 p-4 text-sm text-stone-700 transition hover:bg-emerald-50">
                                        <div class="font-semibold text-stone-900">{{ $rsvp->event->title }}</div>
                                        <div class="mt-1 text-xs uppercase tracking-[0.2em] text-emerald-700">{{ $rsvp->status }}</div>
                                    </a>
                                @endif
                            @empty
                                <p class="text-sm text-stone-600">No RSVP activity yet.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-[2rem] bg-stone-950 p-6 text-stone-100 shadow-sm">
                    <h3 class="text-2xl font-bold">Create an organization</h3>

                    @if ($errors->any())
                        <div class="mt-4 rounded-2xl border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">
                            Please fix the highlighted fields and try again.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('organizations.store') }}" enctype="multipart/form-data" novalidate class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label class="text-sm font-medium text-stone-300" for="org-name">Name</label>
                            <input id="org-name" name="name" value="{{ old('name') }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white" required>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <label class="text-sm font-medium text-stone-300" for="org-summary">Summary</label>
                            <input id="org-summary" name="summary" value="{{ old('summary') }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white" required>
                            <x-input-error :messages="$errors->get('summary')" class="mt-2" />
                        </div>
                        <div>
                            <label class="text-sm font-medium text-stone-300" for="org-description">Description</label>
                            <textarea id="org-description" name="description" rows="4" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-stone-300" for="org-city">City</label>
                                <input id="org-city" name="city" value="{{ old('city') }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">
                                <x-input-error :messages="$errors->get('city')" class="mt-2" />
                            </div>
                            <div>
                                <label class="text-sm font-medium text-stone-300" for="org-url">Website</label>
                                <input id="org-url" name="website_url" value="{{ old('website_url') }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">
                                <x-input-error :messages="$errors->get('website_url')" class="mt-2" />
                            </div>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-stone-300" for="org-avatar">Logo / avatar</label>
                                <input id="org-avatar" name="avatar" type="file" accept="image/*" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">
                                <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                            </div>
                            <div>
                                <label class="text-sm font-medium text-stone-300" for="org-banner">Banner image</label>
                                <input id="org-banner" name="banner" type="file" accept="image/*" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">
                                <x-input-error :messages="$errors->get('banner')" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <select name="visibility" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">
                                <option value="public" @selected(old('visibility', 'public') === 'public')>Public</option>
                                <option value="private" @selected(old('visibility') === 'private')>Private</option>
                                <option value="unlisted" @selected(old('visibility') === 'unlisted')>Unlisted</option>
                            </select>
                            <x-input-error :messages="$errors->get('visibility')" class="mt-2" />
                        </div>
                        <button type="submit" class="w-full rounded-full bg-amber-300 px-5 py-3 font-semibold text-stone-950">Create organization</button>
                    </form>
                </section>

                <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-stone-200">
                    <h3 class="text-2xl font-bold text-stone-900">Publish an event</h3>
                    <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                        @csrf
                        <select name="organization_id" class="w-full rounded-2xl border border-stone-200 px-4 py-3" required>
                            <option value="">Select organization</option>
                            @foreach ($managedOrganizations as $organization)
                                <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                            @endforeach
                        </select>
                        <input name="title" placeholder="Event title" class="w-full rounded-2xl border border-stone-200 px-4 py-3" required>
                        <input name="summary" placeholder="Short summary" class="w-full rounded-2xl border border-stone-200 px-4 py-3" required>
                        <textarea name="description" rows="4" placeholder="Full description" class="w-full rounded-2xl border border-stone-200 px-4 py-3"></textarea>
                        <div class="grid gap-4 md:grid-cols-2">
                            <input name="venue_name" placeholder="Venue" class="w-full rounded-2xl border border-stone-200 px-4 py-3" required>
                            <input name="venue_address" placeholder="Address" class="w-full rounded-2xl border border-stone-200 px-4 py-3">
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <input type="datetime-local" name="starts_at" class="w-full rounded-2xl border border-stone-200 px-4 py-3" required>
                            <input type="datetime-local" name="ends_at" class="w-full rounded-2xl border border-stone-200 px-4 py-3" required>
                        </div>
                        <div class="grid gap-4 md:grid-cols-3">
                            <input name="city" placeholder="City" class="w-full rounded-2xl border border-stone-200 px-4 py-3">
                            <input name="timezone" value="Australia/Perth" class="w-full rounded-2xl border border-stone-200 px-4 py-3" required>
                            <input name="capacity" type="number" min="1" placeholder="Capacity" class="w-full rounded-2xl border border-stone-200 px-4 py-3">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-stone-700" for="event-image">Event image</label>
                            <input id="event-image" name="image" type="file" accept="image/*" class="w-full rounded-2xl border border-stone-200 px-4 py-3">
                        </div>
                        <select name="visibility" class="w-full rounded-2xl border border-stone-200 px-4 py-3">
                            <option value="public">Public</option>
                            <option value="private">Private</option>
                            <option value="unlisted">Unlisted</option>
                        </select>
                        <button class="w-full rounded-full bg-emerald-400 px-5 py-3 font-semibold text-stone-950">Publish event</button>
                    </form>
                </section>

                <section id="new-mailing-list" class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-stone-200">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-2xl font-bold text-stone-900">Add a mailing list</h3>
                            <p class="mt-1 text-sm text-stone-600">Manual lists still sit alongside the automatic event update lists.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('mailing-lists.store') }}" class="mt-5 space-y-4">
                        @csrf
                        <select name="organization_id" class="w-full rounded-2xl border border-stone-200 px-4 py-3" required>
                            <option value="">Select organization</option>
                            @foreach ($managedOrganizations as $organization)
                                <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                            @endforeach
                        </select>
                        <input name="name" placeholder="List name" class="w-full rounded-2xl border border-stone-200 px-4 py-3" required>
                        <textarea name="description" rows="3" placeholder="What subscribers should expect" class="w-full rounded-2xl border border-stone-200 px-4 py-3"></textarea>
                        <select name="audience" class="w-full rounded-2xl border border-stone-200 px-4 py-3">
                            <option value="all-members">All members</option>
                            <option value="students">Students</option>
                            <option value="sponsors">Sponsors</option>
                            <option value="volunteers">Volunteers</option>
                        </select>
                        <button class="w-full rounded-full bg-stone-900 px-5 py-3 font-semibold text-white">Create mailing list</button>
                    </form>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
