<?php
$themeKey = $theme['key'] ?? 'embers';
$themeProseClass = $theme['mode'] === 'light' ? 'prose' : 'prose prose-invert';
?>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] {{ $theme['eyebrow'] }}">Organizer dashboard</p>
                <h2 class="text-3xl font-black leading-tight {{ $theme['header_heading'] }}">{{ __('Run your event network') }}</h2>
            </div>
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    x-data
                    x-on:click.prevent="$dispatch('open-modal', 'dashboard-help')"
                    class="rounded-full border border-stone-300 bg-white px-5 py-3 text-sm font-semibold {{ $theme['soft_button'] }}"
                >
                    Help
                </button>
                <button
                    type="button"
                    x-data
                    x-on:click.prevent="$dispatch('open-modal', 'organization-search')"
                    class="rounded-full border border-stone-300 bg-white px-5 py-3 text-sm font-semibold {{ $theme['soft_button'] }}"
                >
                    Search organizations
                </button>
                <a href="{{ route('dashboard.organizations') }}" class="rounded-full px-5 py-3 text-sm font-semibold {{ $theme['primary_button'] }}">Organization dashboard</a>
                <a href="#new-event" class="rounded-full px-5 py-3 text-sm font-semibold {{ $theme['primary_button'] }}">New event</a>
                <a href="{{ route('dashboard.organizations') }}#new-organization" class="rounded-full px-5 py-3 text-sm font-semibold {{ $theme['primary_button'] }}">New organization</a>
                <a href="#new-mailing-list" class="rounded-full px-5 py-3 text-sm font-semibold {{ $theme['secondary_button'] }}">New mailing list</a>
                <p class="max-w-md text-sm {{ $theme['muted'] }}">Create organizations, publish events, and manage audience subscriptions from one place.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1.1fr_.9fr] lg:px-8">
            <div class="space-y-6">
                @if (session('status'))
                    <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800 {{ $theme['surface'] }}">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session()->has('impersonator_user_id'))
                    <div class="rounded-2xl border border-amber-300/30 bg-amber-500/10 p-4">
                        <span class="text-amber-200">You are viewing this account as another user. Log out to return to your admin account.</span>
                    </div>
                @endif

                @if (auth()->user()->is_admin)
                    <section class="rounded-[2rem] border border-amber-300/20 p-6 shadow-sm ring-1 ring-amber-300/10 {{ $theme['surface'] }}">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-2xl font-bold {{ $theme['heading'] }}">Admin moderation</h3>
                                <p class="mt-1 text-sm {{ $theme['meta'] }}">Control whether people can register immediately or require approval first.</p>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="rounded-full bg-amber-300 px-4 py-2 text-sm font-semibold {{ $theme['secondary_button'] }}">Admin</span>
                                <a href="{{ route('admin.index') }}" class="text-xs text-amber-400 hover:text-amber-200 underline {{ $theme['link'] }}">Open Admin Panel</a>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                            @csrf
                            <div>
                                <label class="text-sm font-medium {{ $theme['body'] }}" for="user-registration-mode">General user registration</label>
                                <select id="user-registration-mode" name="user_registration_mode" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                                    <option value="open" @selected($userRegistrationMode === 'open')>Open self-registration</option>
                                    <option value="moderated" @selected($userRegistrationMode === 'moderated')>Moderated approval</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-medium {{ $theme['body'] }}" for="organization-registration-mode">Organization creation</label>
                                <select id="organization-registration-mode" name="organization_registration_mode" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                                    <option value="open" @selected($organizationRegistrationMode === 'open')>Open self-service</option>
                                    <option value="moderated" @selected($organizationRegistrationMode === 'moderated')>Require admin approval</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <button class="rounded-full bg-amber-300 px-5 py-3 font-semibold {{ $theme['secondary_button'] }}">Save moderation settings</button>
                            </div>
                        </form>

                        <div class="mt-6 grid gap-6 lg:grid-cols-2">
                            <div class="rounded-3xl border border-white/10 bg-black/20 p-5 {{ $theme['surface'] }}">
                                <div class="flex items-center justify-between gap-4">
                                    <h4 class="text-lg font-semibold {{ $theme['heading'] }}">Pending users</h4>
                                    <span class="text-sm {{ $theme['meta'] }}">{{ $pendingUsers->count() }}</span>
                                </div>
                                <div class="mt-4 space-y-3">
                                    @forelse ($pendingUsers as $pendingUser)
                                        <form method="POST" action="{{ route('admin.users.approve', $pendingUser) }}" class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                                            @csrf
                                            <div>
                                                <div class="font-semibold {{ $theme['heading'] }}">{{ $pendingUser->name }}</div>
                                                <div class="mt-1 text-sm {{ $theme['meta'] }}">{{ $pendingUser->email }}</div>
                                            </div>
                                            <button class="rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold {{ $theme['secondary_button'] }}">Approve</button>
                                        </form>
                                    @empty
                                        <p class="text-sm {{ $theme['meta'] }}">No pending user accounts.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="rounded-3xl border border-white/10 bg-black/20 p-5 {{ $theme['surface'] }}">
                                <div class="flex items-center justify-between gap-4">
                                    <h4 class="text-lg font-semibold {{ $theme['heading'] }}">Pending organizations</h4>
                                    <span class="text-sm {{ $theme['meta'] }}">{{ $pendingOrganizations->count() }}</span>
                                </div>
                                <div class="mt-4 space-y-3">
                                    @forelse ($pendingOrganizations as $pendingOrganization)
                                        <form method="POST" action="{{ route('admin.organizations.approve', $pendingOrganization) }}" class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                                            @csrf
                                            <div>
                                                <div class="font-semibold {{ $theme['heading'] }}">{{ $pendingOrganization->name }}</div>
                                                <div class="mt-1 text-sm {{ $theme['meta'] }}">Owner: {{ $pendingOrganization->owner?->name ?? 'Unknown' }}</div>
                                            </div>
                                            <button class="rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold {{ $theme['secondary_button'] }}">Approve</button>
                                        </form>
                                    @empty
                                        <p class="text-sm {{ $theme['meta'] }}">No pending organizations.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 rounded-3xl border border-white/10 bg-black/20 p-5 {{ $theme['surface'] }}">
                            <div class="flex items-center justify-between gap-4">
                                <h4 class="text-lg font-semibold {{ $theme['heading'] }}">Reports queue</h4>
                                <span class="text-sm {{ $theme['meta'] }}">{{ $openReports->count() }}</span>
                            </div>
                            <div class="mt-4 space-y-3">
                                @forelse ($openReports as $report)
                                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <div class="font-semibold {{ $theme['heading'] }}">
                                                    {{ class_basename($report->reportable_type) }} report
                                                </div>
                                                <div class="mt-1 text-sm {{ $theme['meta'] }}">
                                                    {{ $report->reason }}
                                                </div>
                                                <div class="mt-1 text-sm {{ $theme['meta'] }}">
                                                    Reporter: {{ $report->reporter?->email ?? 'Unknown' }}
                                                </div>
                                                @if ($report->reportable)
                                                    <div class="mt-1 text-sm {{ $theme['meta'] }}">
                                                        Target:
                                                        @if ($report->reportable instanceof \App\Models\User)
                                                            {{ $report->reportable->name }} ({{ $report->reportable->email }})
                                                        @else
                                                            {{ $report->reportable->name }}
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <span class="text-xs uppercase tracking-[0.2em] text-amber-200">{{ $report->status }}</span>
                                        </div>
                                        <div class="mt-4 flex flex-wrap gap-3">
                                            <form method="POST" action="{{ route('admin.reports.update', $report) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="reviewing">
                                                <button class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold {{ $theme['body'] }}">Mark reviewing</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.reports.update', $report) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="resolved">
                                                <button class="rounded-full border border-emerald-300/30 bg-emerald-300/10 px-4 py-2 text-sm font-semibold text-emerald-200">Resolve</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.reports.update', $report) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="dismissed">
                                                <button class="rounded-full border border-stone-300/20 bg-white/5 px-4 py-2 text-sm font-semibold {{ $theme['body'] }}">Dismiss</button>
                                            </form>
                                            @if ($report->reportable instanceof \App\Models\User && ! $report->reportable->is_admin)
                                                <form method="POST" action="{{ route('admin.users.suspend', $report->reportable) }}">
                                                    @csrf
                                                    <button class="rounded-full border border-rose-300/30 bg-rose-500/10 px-4 py-2 text-sm font-semibold text-rose-200">Suspend user</button>
                                                </form>
                                            @endif
                                            @if ($report->reportable instanceof \App\Models\Organization)
                                                <form method="POST" action="{{ route('admin.organizations.suspend', $report->reportable) }}">
                                                    @csrf
                                                    <button class="rounded-full border border-rose-300/30 bg-rose-500/10 px-4 py-2 text-sm font-semibold text-rose-200">Suspend organization</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm {{ $theme['meta'] }}">No open reports.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="mt-6 grid gap-6 lg:grid-cols-2">
                            <div class="rounded-3xl border border-white/10 bg-black/20 p-5 {{ $theme['surface'] }}">
                                <div class="flex items-center justify-between gap-4">
                                    <h4 class="text-lg font-semibold {{ $theme['heading'] }}">Suspended users</h4>
                                    <span class="text-sm {{ $theme['meta'] }}">{{ $suspendedUsers->count() }}</span>
                                </div>
                                <div class="mt-4 space-y-3">
                                    @forelse ($suspendedUsers as $suspendedUser)
                                        <form method="POST" action="{{ route('admin.users.restore', $suspendedUser) }}" class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                                            @csrf
                                            <div>
                                                <div class="font-semibold {{ $theme['heading'] }}">{{ $suspendedUser->name }}</div>
                                                <div class="mt-1 text-sm {{ $theme['meta'] }}">{{ $suspendedUser->email }}</div>
                                            </div>
                                            <button class="rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold {{ $theme['secondary_button'] }}">Restore</button>
                                        </form>
                                    @empty
                                        <p class="text-sm {{ $theme['meta'] }}">No suspended users.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="rounded-3xl border border-white/10 bg-black/20 p-5 {{ $theme['surface'] }}">
                                <div class="flex items-center justify-between gap-4">
                                    <h4 class="text-lg font-semibold {{ $theme['heading'] }}">Suspended organizations</h4>
                                    <span class="text-sm {{ $theme['meta'] }}">{{ $suspendedOrganizations->count() }}</span>
                                </div>
                                <div class="mt-4 space-y-3">
                                    @forelse ($suspendedOrganizations as $suspendedOrganization)
                                        <form method="POST" action="{{ route('admin.organizations.restore', $suspendedOrganization) }}" class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                                            @csrf
                                            <div>
                                                <div class="font-semibold {{ $theme['heading'] }}">{{ $suspendedOrganization->name }}</div>
                                                <div class="mt-1 text-sm {{ $theme['meta'] }}">Owner: {{ $suspendedOrganization->owner?->name ?? 'Unknown' }}</div>
                                            </div>
                                            <button class="rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold {{ $theme['secondary_button'] }}">Restore</button>
                                        </form>
                                    @empty
                                        <p class="text-sm {{ $theme['meta'] }}">No suspended organizations.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="mt-6 rounded-3xl border border-white/10 bg-black/20 p-5 {{ $theme['surface'] }}">
                        <div class="flex items-center justify-between gap-4">
                            <h4 class="text-lg font-semibold {{ $theme['heading'] }}">Impersonate user</h4>
                            <span class="text-sm {{ $theme['meta'] }}">{{ $allUsers->count() }} users</span>
                        </div>
                        @if($allUsers->count() > 1)
                        <form method="POST" action="{{ route('admin.impersonate') }}" class="mt-4">
                            @csrf
                            <select name="user_id" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                                <option value="">Select a user</option>
                                @foreach ($allUsers as $u)
                                    @if (!$u->is_admin && $u->id !== Auth::id())
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                    @endif
                                @endforeach
                            </select>
                            <button class="mt-3 w-full rounded-full border border-amber-300/30 bg-amber-500/10 px-4 py-2 text-sm font-semibold text-amber-200">Impersonate</button>
                        </form>
                        @else
                        <p class="mt-4 text-sm {{ $theme['meta'] }}">No other users to impersonate.</p>
                        @endif
                    </div>
                @endif

                <section class="rounded-[2rem] border border-white/10 p-6 shadow-sm ring-1 ring-white/10 {{ $theme['surface'] }}">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-2xl font-bold {{ $theme['heading'] }}">Organizations</h3>
                            <p class="mt-1 text-sm {{ $theme['meta'] }}">You manage {{ $managedOrganizations->count() }} and follow {{ $followedOrganizations->count() }}.</p>
                        </div>
                        <a href="{{ route('dashboard.organizations') }}" class="rounded-full px-5 py-3 text-center text-sm font-semibold {{ $theme['primary_button'] }}">Open organization dashboard</a>
                    </div>
                </section>

                <section class="rounded-[2rem] p-6 shadow-sm ring-1 ring-stone-200 {{ $theme['surface'] }}">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-2xl font-bold {{ $theme['heading'] }}">Upcoming events</h3>
                            <p class="mt-1 text-sm {{ $theme['muted'] }}">Live event pages work as the public replacement for Facebook Events.</p>
                        </div>
                    </div>
                    <div class="mt-6 space-y-4">
                        @forelse ($upcomingEvents as $event)
                            <a href="{{ route('events.show', $event) }}" class="flex flex-col justify-between gap-4 rounded-3xl border border-stone-200 p-5 transition hover:border-amber-300 md:flex-row md:items-center">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.2em] text-amber-700">{{ $event->starts_at->format('D d M, g:i A') }}</p>
                                    <h4 class="mt-2 text-xl font-bold {{ $theme['heading'] }}">{{ $event->title }}</h4>
                                    <p class="mt-2 text-sm {{ $theme['muted'] }}">{{ $event->organization?->name ?? 'Unknown organization' }} · {{ $event->is_online ? 'Online event' : ($event->venue_name ?: 'Venue TBA') }}</p>
                                </div>
                                <div class="text-sm font-medium {{ $theme['muted'] }}">{{ $event->timezone }}</div>
                            </a>
                        @empty
                            <div class="rounded-3xl border border-dashed border-stone-300 p-5 text-sm {{ $theme['muted'] }}">No events published yet.</div>
                        @endforelse
                    </div>
                </section>

                <section class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-[2rem] p-6 shadow-sm ring-1 ring-stone-200 {{ $theme['surface'] }}">
                        <h3 class="text-2xl font-bold {{ $theme['heading'] }}">Your subscriptions</h3>
                        <div class="mt-4 space-y-3">
                            @forelse ($subscriptions as $subscription)
                                <a href="{{ route('mailing-lists.show', $subscription) }}" class="block rounded-2xl bg-stone-50 p-4 text-sm text-stone-700 transition hover:bg-amber-50">
                                    <div class="font-semibold {{ $theme['heading'] }}">{{ $subscription->name }}</div>
                                    <div class="mt-1">{{ $subscription->organization->name }}</div>
                                </a>
                            @empty
                                <p class="text-sm {{ $theme['muted'] }}">You have not joined any mailing lists yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-[2rem] p-6 shadow-sm ring-1 ring-stone-200 {{ $theme['surface'] }}">
                        <h3 class="text-2xl font-bold {{ $theme['heading'] }}">Your RSVPs</h3>
                        <div class="mt-4 space-y-3">
                            @forelse ($rsvps as $rsvp)
                                @if ($rsvp->event)
                                    <a href="{{ route('events.show', $rsvp->event) }}" class="block rounded-2xl bg-stone-50 p-4 text-sm text-stone-700 transition hover:bg-emerald-50">
                                        <div class="font-semibold {{ $theme['heading'] }}">{{ $rsvp->event->title }}</div>
                                        <div class="mt-1 text-xs uppercase tracking-[0.2em] text-emerald-700">{{ $rsvp->status }}</div>
                                    </a>
                                @endif
                            @empty
                                <p class="text-sm {{ $theme['muted'] }}">No RSVP activity yet.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-[2rem] border border-white/10 p-6 shadow-sm ring-1 ring-white/10 {{ $theme['surface'] }}">
                    <h3 class="text-2xl font-bold {{ $theme['heading'] }}">Organization work</h3>
                    <p class="mt-2 text-sm {{ $theme['meta'] }}">Memberships, followed organizations, public tag search, and organization creation now live on the organization dashboard.</p>
                    <div class="mt-5 grid gap-3">
                        <a href="{{ route('dashboard.organizations') }}" class="rounded-full px-5 py-3 text-center text-sm font-semibold {{ $theme['primary_button'] }}">Open organization dashboard</a>
                        <button type="button" x-data x-on:click.prevent="$dispatch('open-modal', 'organization-search')" class="rounded-full border border-white/10 px-5 py-3 text-sm font-semibold {{ $theme['soft_button'] }}">Search by tag</button>
                    </div>
                </section>

                <section id="new-event" class="rounded-[2rem] border border-emerald-300/20 p-6 shadow-sm ring-1 ring-emerald-300/15 {{ $theme['surface'] }}">
                    @php
                        $timeOptions = collect(range(0, 47))->map(function (int $slot) {
                            $hour = intdiv($slot, 2);
                            $minute = $slot % 2 === 0 ? '00' : '30';
                            $value = sprintf('%02d:%s', $hour, $minute);
                            $label = \Carbon\CarbonImmutable::createFromTime($hour, (int) $minute)->format('g:i A');

                            return compact('value', 'label');
                        });
                    @endphp
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-[0.25em] text-emerald-400">Primary action</p>
                            <h3 class="text-2xl font-bold {{ $theme['heading'] }}">Publish an event</h3>
                            <p class="mt-1 text-sm {{ $theme['meta'] }}">Create the public event page, optional repeating schedule, and automatic update mailing list in one step.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4" x-data="{ isOnline: {{ old('is_online') ? 'true' : 'false' }} }">
                        @csrf
                        <select name="organization_id" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                            <option value="">Select organization</option>
                            @foreach ($managedOrganizations as $organization)
                                <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                            @endforeach
                        </select>
                        <input name="title" value="{{ old('title') }}" placeholder="Event title" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500" required>
                        <input name="summary" value="{{ old('summary') }}" placeholder="Short summary" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500" required>
                        <textarea name="description" rows="4" placeholder="Full description" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500">{{ old('description') }}</textarea>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <label class="flex items-center gap-3 text-sm {{ $theme['body'] }}">
                                <input type="checkbox" name="is_online" value="1" x-model="isOnline" class="rounded border-white/10 bg-white/5 text-emerald-400 focus:ring-emerald-400">
                                This is an online event
                            </label>
                            <input x-show="isOnline" x-cloak name="online_url" value="{{ old('online_url') }}" placeholder="Optional meeting link" class="mt-3 w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-stone-100 placeholder:text-stone-500">
                        </div>
                        <div x-show="!isOnline" x-cloak class="grid gap-4 md:grid-cols-2">
                            <input name="venue_name" data-event-venue-name value="{{ old('venue_name') }}" placeholder="Venue" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500" x-bind:required="!isOnline">
                            <input name="venue_address" data-event-venue-address value="{{ old('venue_address') }}" placeholder="Address" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500">
                        </div>
                        <div x-show="!isOnline" x-cloak>
                            <label class="mb-2 block text-sm font-medium {{ $theme['body'] }}">Search place with Google Maps</label>
                            <div data-google-place-widget class="rounded-2xl border border-white/10 bg-white/5 px-3 py-2"></div>
                            <input type="hidden" name="google_place_id" data-event-place-id value="{{ old('google_place_id') }}">
                            <input type="hidden" name="latitude" data-event-latitude value="{{ old('latitude') }}">
                            <input type="hidden" name="longitude" data-event-longitude value="{{ old('longitude') }}">
                        </div>
                        <div class="grid gap-4 md:grid-cols-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium {{ $theme['body'] }}" for="event-start-date">Start date</label>
                                <input id="event-start-date" type="date" name="start_date" value="{{ old('start_date', old('starts_at') ? \Carbon\Carbon::parse(old('starts_at'))->format('Y-m-d') : '') }}" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium {{ $theme['body'] }}" for="event-start-time">Start time</label>
                                <select id="event-start-time" name="start_time" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                                    @foreach ($timeOptions as $option)
                                        <option value="{{ $option['value'] }}" @selected(old('start_time', old('starts_at') ? \Carbon\Carbon::parse(old('starts_at'))->format('H:i') : '') === $option['value'])>{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium {{ $theme['body'] }}" for="event-end-date">End date</label>
                                <input id="event-end-date" type="date" name="end_date" value="{{ old('end_date', old('ends_at') ? \Carbon\Carbon::parse(old('ends_at'))->format('Y-m-d') : '') }}" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium {{ $theme['body'] }}" for="event-end-time">End time</label>
                                <select id="event-end-time" name="end_time" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                                    @foreach ($timeOptions as $option)
                                        <option value="{{ $option['value'] }}" @selected(old('end_time', old('ends_at') ? \Carbon\Carbon::parse(old('ends_at'))->format('H:i') : '') === $option['value'])>{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid gap-4 md:grid-cols-3">
                            <input x-show="!isOnline" x-cloak name="city" data-event-city value="{{ old('city') }}" placeholder="City" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500">
                            <input name="timezone" value="{{ old('timezone', 'Australia/Perth') }}" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                            <input name="capacity" type="number" min="1" value="{{ old('capacity') }}" placeholder="Capacity" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500">
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium {{ $theme['body'] }}" for="event-repeat-frequency">Repeats</label>
                                <select id="event-repeat-frequency" name="repeat_frequency" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                                    <option value="none" @selected(old('repeat_frequency', 'none') === 'none')>Does not repeat</option>
                                    <option value="daily" @selected(old('repeat_frequency') === 'daily')>Daily</option>
                                    <option value="weekly" @selected(old('repeat_frequency') === 'weekly')>Weekly</option>
                                    <option value="monthly" @selected(old('repeat_frequency') === 'monthly')>Monthly</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium {{ $theme['body'] }}" for="event-repeat-until-date">Repeat until</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <input id="event-repeat-until-date" type="date" name="repeat_until_date" value="{{ old('repeat_until_date', old('repeat_until') ? \Carbon\Carbon::parse(old('repeat_until'))->format('Y-m-d') : '') }}" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                                    <select name="repeat_until_time" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                                        <option value="">Time</option>
                                        @foreach ($timeOptions as $option)
                                            <option value="{{ $option['value'] }}" @selected(old('repeat_until_time', old('repeat_until') ? \Carbon\Carbon::parse(old('repeat_until'))->format('H:i') : '') === $option['value'])>{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
<div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <h4 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['body'] }}">Follower reminder emails</h4>
                            <p class="mt-2 text-sm {{ $theme['meta'] }}">Choose when CircleEvents should remind followers and subscribed list members about this event.</p>
                            <div class="mt-4 space-y-3">
                                <label class="flex items-center gap-3 text-sm {{ $theme['body'] }}">
                                    <input type="checkbox" name="notify_followers_one_week_before" value="1" @checked(old('notify_followers_one_week_before')) class="rounded border-white/10 bg-white/5 text-amber-300 focus:ring-amber-300">
                                    Remind followers 1 week before
                                </label>
                                <label class="flex items-center gap-3 text-sm {{ $theme['body'] }}">
                                    <input type="checkbox" name="notify_followers_one_day_before" value="1" @checked(old('notify_followers_one_day_before')) class="rounded border-white/10 bg-white/5 text-amber-300 focus:ring-amber-300">
                                    Remind followers 1 day before
                                </label>
                                <label class="flex items-center gap-3 text-sm {{ $theme['body'] }}">
                                    <input type="checkbox" name="notify_followers_one_hour_before" value="1" @checked(old('notify_followers_one_hour_before')) class="rounded border-white/10 bg-white/5 text-amber-300 focus:ring-amber-300">
                                    Remind followers 1 hour before
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium {{ $theme['body'] }}" for="event-image">Event image</label>
                            <input id="event-image" name="image" type="file" accept="image/*" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                        </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-stone-300" for="event-image">Event image</label>
                            <input id="event-image" name="image" type="file" accept="image/*" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                        </div>
                        <select name="visibility" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                            <option value="public">Public</option>
                            <option value="private">Private</option>
                            <option value="unlisted">Unlisted</option>
                        </select>
                        <button class="w-full rounded-full bg-emerald-400 px-5 py-3 font-semibold {{ $theme['secondary_button'] }}">Publish event</button>
                    </form>
                </section>

                <section id="new-mailing-list" class="rounded-[2rem] border border-white/10 p-6 shadow-sm ring-1 ring-white/10 {{ $theme['surface'] }}">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-2xl font-bold {{ $theme['heading'] }}">Add a mailing list</h3>
                            <p class="mt-1 text-sm {{ $theme['meta'] }}">Manual lists still sit alongside the automatic event update lists.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('mailing-lists.store') }}" class="mt-5 space-y-4">
                        @csrf
                        <select name="organization_id" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                            <option value="">Select organization</option>
                            @foreach ($managedOrganizations as $organization)
                                <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                            @endforeach
                        </select>
                        <input name="name" placeholder="List name" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500" required>
                        <textarea name="description" rows="3" placeholder="What subscribers should expect" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500"></textarea>
                        <select name="audience" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                            <option value="all-members">All members</option>
                            <option value="students">Students</option>
                            <option value="sponsors">Sponsors</option>
                            <option value="volunteers">Volunteers</option>
                        </select>
                        <button class="w-full rounded-full px-5 py-3 font-semibold {{ $theme['secondary_button'] }}">Create mailing list</button>
                    </form>
                </section>
            </div>
        </div>
    </div>

    <x-modal name="organization-search" maxWidth="2xl" focusable>
        <div class="p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Organization search</p>
                    <h2 class="mt-2 text-2xl font-black {{ $theme['heading'] }}">Find public organizations by tag</h2>
                </div>
                <button
                    type="button"
                    x-on:click="$dispatch('close-modal', 'organization-search')"
                    class="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['body'] }}"
                >
                    Close
                </button>
            </div>

            <form method="GET" action="{{ route('dashboard.organizations') }}" class="mt-6 space-y-4">
                <div>
                    <label class="text-sm font-medium {{ $theme['body'] }}" for="dashboard-organization-tag-search">Tags</label>
                    <input id="dashboard-organization-tag-search" name="tag" placeholder="music, community, volunteering" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                </div>
                <button class="w-full rounded-full bg-amber-300 px-5 py-3 font-semibold text-stone-950">Search organizations</button>
            </form>

            @if ($availableTags->isNotEmpty())
                <div class="mt-6">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['meta'] }}">Popular tags</h3>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($availableTags as $tag)
                            <a href="{{ route('dashboard.organizations', ['tag' => $tag]) }}" class="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-sm {{ $theme['body'] }}">{{ $tag }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </x-modal>

    <x-modal name="dashboard-help" maxWidth="2xl" focusable>
        <div class="p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Dashboard help</p>
                    <h2 class="mt-2 text-2xl font-black {{ $theme['heading'] }}">How CircleEvents works</h2>
                </div>
                <button
                    type="button"
                    x-on:click="$dispatch('close-modal', 'dashboard-help')"
                    class="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['body'] }}"
                >
                    Close
                </button>
            </div>

            <div class="mt-6 space-y-5 text-sm leading-7 {{ $theme['body'] }}">
                <p>
                    Use the right-hand forms for the core organizer actions: create an organization, publish an event, or make a mailing list.
                </p>
                <div>
                    <h3 class="font-semibold {{ $theme['heading'] }}">Good first steps</h3>
                    <ul class="mt-2 list-disc space-y-2 pl-5">
                        <li>create an organization profile with a logo, banner, and theme</li>
                        <li>publish your first event and choose follower reminder timings</li>
                        <li>invite members or share a code from the organization page</li>
                        <li>use member messages for announcements and event pages for discussion</li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold {{ $theme['heading'] }}">Where to manage things later</h3>
                    <ul class="mt-2 list-disc space-y-2 pl-5">
                        <li>organization pages: invites, member messages, published events, themes</li>
                        <li>event pages: RSVPs, attendee reminders, discussion, re-announcing changes</li>
                        <li>mailing lists: subscriber pages for updates and announcements</li>
                    </ul>
                </div>
            </div>
        </div>
    </x-modal>
</x-app-layout>
