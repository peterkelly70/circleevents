<x-app-layout>
    @php
        $theme = \App\Support\OrganizationThemes::get(auth()->user()?->resolvedOrganizationThemeKey($organization) ?? $organization->theme_key);
        $themeProseClass = $theme['mode'] === 'light' ? 'prose' : 'prose prose-invert';
        $controlReadableClass = $theme['mode'] === 'light' ? 'control-readable-light' : 'control-readable';
        $controlThemeClass = $theme['mode'] === 'light' ? 'control-surface-light' : 'control-surface-dark';
        $eventErrorFields = [
            'organization_id',
            'title',
            'summary',
            'description',
            'is_online',
            'online_url',
            'venue_name',
            'venue_address',
            'google_place_id',
            'latitude',
            'longitude',
            'start_date',
            'start_time',
            'end_date',
            'end_time',
            'city',
            'timezone',
            'capacity',
            'repeat_frequency',
            'repeat_until_date',
            'repeat_until_time',
            'notify_followers_one_week_before',
            'notify_followers_one_day_before',
            'notify_followers_one_hour_before',
            'image',
            'visibility',
        ];
        $hasEventErrors = collect($eventErrorFields)->contains(fn (string $field) => $errors->has($field));
    @endphp
    <div x-data="{ manageMembersModal: false, messageMemberModal: false, selectedUsers: [], searchQuery: '' }" @open-members-modal.window="manageMembersModal = true">
    <x-slot name="header">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] {{ $theme['eyebrow'] }}">Organization</p>
                <h1 class="text-3xl font-black {{ $theme['header_heading'] }}">{{ $organization->name }}</h1>
            </div>
            @auth
                @if (auth()->user()->isManagerOf($organization))
                    <div class="flex gap-3">
                        <a href="{{ route('organizations.members.index', $organization) }}" class="rounded-full border px-4 py-3 text-sm font-semibold {{ $theme['header_button'] }}">Manage members</a>
                        <a href="{{ route('organizations.edit', $organization) }}" class="rounded-full px-5 py-3 text-sm font-semibold {{ $theme['header_button'] }}">Edit organization</a>
                    </div>
                @endif
            @endauth
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 {{ $theme['mode'] === 'light' ? 'text-stone-900' : 'text-stone-100' }} {{ $theme['page_backdrop'] }} {{ $theme['font_body'] }}">
        <section class="mb-6 overflow-hidden rounded-[2rem] border shadow-sm ring-1 {{ $theme['surface'] }}">
            <div class="relative h-52 {{ $theme['hero'] }} sm:h-64">
                @if ($organization->banner_path)
                    <div class="absolute inset-0 bg-black">
                        <img src="{{ $organization->bannerUrl() }}" alt="{{ $organization->name }} banner" class="h-full w-full object-contain object-center">
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t {{ $theme['hero_overlay'] }} to-transparent"></div>
                @endif

                <div class="absolute inset-x-0 bottom-0 flex items-end gap-4 px-5 pb-5 sm:px-6">
                    <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-[1.75rem] border text-3xl font-black shadow-xl sm:h-28 sm:w-28 {{ $theme['logo_shell'] }}">
                        @if ($organization->avatar_path)
                            <img src="{{ $organization->avatarUrl() }}" alt="{{ $organization->name }} logo" class="h-full w-full object-contain object-center">
                        @else
                            <span>{{ str($organization->name)->substr(0, 2)->upper() }}</span>
                        @endif
                    </div>

	                    <div class="pb-1">
	                        <p class="text-xs uppercase tracking-[0.35em] {{ $theme['hero_eyebrow'] }}">Community profile</p>
	                        <h2 class="mt-1.5 text-2xl font-black leading-tight sm:text-[2rem] {{ $theme['hero_heading'] }} {{ $theme['font_display'] }}">{{ $organization->name }}</h2>
	                        @if ($organization->tagList() !== [])
	                            <div class="mt-3 flex flex-wrap gap-2">
	                                @foreach ($organization->tagList() as $tag)
	                                    <span class="rounded-full border px-3 py-1 text-xs {{ $theme['panel'] }}">{{ $tag }}</span>
	                                @endforeach
	                            </div>
	                        @endif
	                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-5 lg:grid-cols-[1.08fr_.92fr]">
            <section class="space-y-5">
                <div class="rounded-[2rem] border p-6 shadow-sm ring-1 {{ $theme['surface'] }}">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-[0.25em] {{ $theme['link'] }}">Published now</p>
                            <h2 class="mt-1 text-2xl font-bold {{ $theme['heading'] }}">Published events</h2>
                        </div>
                        <span class="text-sm {{ $theme['meta'] }}">{{ $organization->events->count() }} live</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($organization->events as $event)
                            <div x-data="{ open: false }" class="rounded-2xl border p-4 {{ $theme['panel'] }}">
                                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                    <div class="min-w-0">
                                        <div class="text-xs uppercase tracking-[0.2em] {{ $theme['link'] }}">{{ $event->starts_at->format('D d M · g:i A') }}</div>
                                        <h3 class="mt-2 text-xl font-semibold {{ $theme['heading'] }}">{{ $event->title }}</h3>
                                        <p class="mt-2 text-sm leading-6 {{ $theme['body'] }}">{{ $event->summary }}</p>
                                        <div class="mt-3 flex flex-wrap gap-3 text-sm {{ $theme['meta'] }}">
                                            @if ($event->is_online)
                                                <span>Online</span>
                                            @elseif ($event->venue_name)
                                                <span>{{ $event->venue_name }}</span>
                                            @endif
                                            <span>{{ $event->visibilityLabel() }}</span>
                                            <span>{{ $event->discussionPosts->count() }} messages</span>
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 flex-wrap gap-2">
                                        <button type="button" @click="open = ! open" class="rounded-full border px-4 py-2 text-sm font-semibold {{ $theme['soft_button'] }}" x-text="open ? 'Hide messages' : 'Show messages'"></button>
                                        <a href="{{ route('events.show', $event) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $theme['secondary_button'] }}">Open event</a>
                                    </div>
                                </div>

                                <div x-show="open" x-cloak class="mt-4 border-t border-white/10 pt-4">
                                    <div class="grid gap-4 md:grid-cols-[1.1fr_.9fr]">
                                        <div>
                                            <h4 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Event details</h4>
                                            <dl class="mt-3 grid gap-3 text-sm {{ $theme['body'] }}">
                                                <div>
                                                    <dt class="text-xs uppercase tracking-[0.2em] {{ $theme['muted'] }}">When</dt>
                                                    <dd class="mt-1">{{ $event->starts_at->format('l, j F Y g:i A') }} to {{ $event->ends_at->format('g:i A') }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="text-xs uppercase tracking-[0.2em] {{ $theme['muted'] }}">Where</dt>
                                                    <dd class="mt-1">
                                                        @if ($event->is_online)
                                                            Online event
                                                        @else
                                                            {{ $event->venue_name ?: 'Venue not set' }}@if ($event->venue_address), {{ $event->venue_address }}@endif
                                                        @endif
                                                    </dd>
                                                </div>
                                                @if ($event->is_online && $event->online_url)
                                                    <div>
                                                        <dt class="text-xs uppercase tracking-[0.2em] {{ $theme['muted'] }}">Join</dt>
                                                        <dd class="mt-2">
                                                            <a href="{{ $event->online_url }}" target="_blank" rel="noreferrer" class="inline-flex rounded-full px-4 py-2 text-sm font-semibold {{ $theme['secondary_button'] }}">Open event link</a>
                                                        </dd>
                                                    </div>
                                                @endif
                                                @if ($event->description)
                                                    <div>
                                                        <dt class="text-xs uppercase tracking-[0.2em] {{ $theme['muted'] }}">Description</dt>
                                                        <dd class="mt-1 leading-6">{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 280) }}</dd>
                                                    </div>
                                                @endif
                                            </dl>
                                        </div>
                                        <div>
                                            <div class="flex items-center justify-between gap-3">
                                                <h4 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Event messages</h4>
                                                <a href="{{ route('events.show', $event) }}#discussion" class="text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['link'] }}">Open full thread</a>
                                            </div>
                                            <div class="mt-3 space-y-3">
                                                @forelse ($event->discussionPosts->take(3) as $post)
                                                    <div class="rounded-2xl border p-3 {{ $theme['surface_secondary'] ?? $theme['surface'] }}">
                                                        <div class="flex items-center justify-between gap-3">
                                                            <div class="flex items-center gap-3">
                                                                <x-user-avatar :user="$post->user" size="sm" :shell="$theme['logo_shell']" />
                                                                <p class="font-semibold {{ $theme['heading'] }}">{{ $post->user->name }}</p>
                                                            </div>
                                                            <p class="text-xs uppercase tracking-[0.2em] {{ $theme['muted'] }}">{{ $post->created_at->diffForHumans() }}</p>
                                                        </div>
                                                        <div class="mt-2 text-sm leading-6 {{ $theme['body'] }}">{!! \App\Support\Bbcode::render(\Illuminate\Support\Str::limit($post->body, 220)) !!}</div>
                                                    </div>
                                                @empty
                                                    <p class="text-sm {{ $theme['meta'] }}">No event discussion yet.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm {{ $theme['meta'] }}">No events published yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-[2rem] border p-5 shadow-sm ring-1 lg:p-6 {{ $theme['surface_secondary'] ?? $theme['surface'] }}">
                    <h2 class="text-2xl font-bold {{ $theme['heading'] }}">Member messages</h2>
                    <p class="mt-2 text-sm {{ $theme['meta'] }}">Managers can write announcements here and email them to all followers and members.</p>

	                    @auth
	                        @if (auth()->user()->isManagerOf($organization))
	                            @php
	                                $managerMembers = $organization->members->whereIn('pivot.role', ['owner', 'manager']);
	                                $followerMembers = $organization->members->where('pivot.role', 'follower');
	                            @endphp
	                            @if ($errors->organizationMessage->any())
	                                <div class="mt-4 rounded-2xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
	                                    Fix the highlighted member message details and try sending again.
	                                </div>
	                            @endif
	                            <form method="POST" action="{{ route('organizations.messages.store', $organization) }}" enctype="multipart/form-data" class="mt-4 space-y-3.5">
	                                @csrf
	                                <input name="subject" value="{{ old('subject') }}" placeholder="Message subject" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}" required>
	                                <x-input-error :messages="$errors->organizationMessage->get('subject')" class="mt-2" />
	                                <textarea name="body" rows="4" placeholder="Write the message that members should receive on-site and by email." class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}" required>{{ old('body') }}</textarea>
	                                <x-input-error :messages="$errors->organizationMessage->get('body')" class="mt-2" />
	                                <p class="text-xs {{ $theme['muted'] }}">Supports BBCode and an optional image attachment.</p>
	                                <input name="image" type="file" accept="image/*" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
	                                <x-input-error :messages="$errors->organizationMessage->get('image')" class="mt-2" />
                                <label class="flex items-center gap-3 text-sm {{ $theme['body'] }}">
                                    <input type="checkbox" name="post_to_discord" value="1" @checked(old('post_to_discord', $organization->auto_post_discord_announcements)) class="rounded border-white/10 bg-white/5 {{ $theme['checkbox'] }}">
                                    Send this announcement to Discord too
                                </label>
                                <label class="flex items-center gap-3 text-sm {{ $theme['body'] }}">
                                    <input type="checkbox" name="post_to_facebook" value="1" @checked(old('post_to_facebook', $organization->auto_post_facebook_announcements)) class="rounded border-white/10 bg-white/5 {{ $theme['facebook_checkbox'] }}">
                                    Send this announcement to Facebook too
                                </label>
                                <button class="w-full rounded-full px-5 py-2.5 font-semibold {{ $theme['secondary_button'] }}">Send to members</button>
                            </form>
                        @endif
                    @endauth

                    <div class="mt-6 space-y-4">
                        @forelse ($organization->messages as $message)
                            @php
                                $organizationUrl = route('organizations.show', $organization);
                                $messagePreview = \Illuminate\Support\Str::limit(strip_tags($message->body), 140);
                                $messagePlain = trim(strip_tags($message->body));
                                $emailBody = rawurlencode($messagePlain."\n\n".$organizationUrl);
                                $copyPayload = $message->subject."\n\n".$messagePlain."\n\n".$organizationUrl;
                            @endphp
                            <div class="rounded-2xl border p-4 {{ $theme['panel'] }}">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <x-user-avatar :user="$message->user" size="md" :shell="$theme['logo_shell']" />
                                        <div>
                                            <h3 class="font-semibold {{ $theme['heading'] }}">{{ $message->subject }}</h3>
                                            <p class="mt-1 text-sm {{ $theme['meta'] }}">From {{ $message->user->name }}</p>
                                        </div>
                                    </div>
                                    <p class="text-xs uppercase tracking-[0.2em] {{ $theme['muted'] }}">{{ $message->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="mt-3 max-w-none {{ $theme['body'] }} {{ $themeProseClass }}">{!! \App\Support\Bbcode::render($message->body) !!}</div>
                                @if ($message->image_path)
                                    <img src="{{ $message->imageUrl() }}" alt="Member message attachment" class="mt-4 max-h-[28rem] w-full rounded-2xl object-cover">
                                @endif
                                <div class="mt-4 flex flex-wrap items-center gap-3">
                                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-400">Share</span>
                                    <button
                                        type="button"
                                        data-share-button
                                        data-share-title="{{ $message->subject }}"
                                        data-share-text="{{ \Illuminate\Support\Str::limit(strip_tags($message->body), 180) }}"
                                        data-share-url="{{ route('organizations.show', $organization) }}"
                                        data-tooltip="Share"
                                        title="Share"
                                        aria-label="Share"
                                        class="share-icon-button share-icon-button-sm"
                                    >
                                        <span aria-hidden="true">↗</span>
                                    </button>
                                    <a
                                        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($organizationUrl) }}"
                                        target="_blank"
                                        rel="noreferrer"
                                        data-tooltip="Share on Facebook"
                                        title="Share on Facebook"
                                        aria-label="Share on Facebook"
                                        class="share-icon-button share-icon-button-sm"
                                    >
                                        <span aria-hidden="true">f</span>
                                    </a>
                                    <a
                                        href="https://x.com/intent/tweet?text={{ urlencode($message->subject.' - '.$messagePreview) }}&url={{ urlencode($organizationUrl) }}"
                                        target="_blank"
                                        rel="noreferrer"
                                        data-tooltip="Share on X"
                                        title="Share on X"
                                        aria-label="Share on X"
                                        class="share-icon-button share-icon-button-sm"
                                    >
                                        <span aria-hidden="true">x</span>
                                    </a>
                                    <a
                                        href="mailto:?subject={{ rawurlencode($message->subject) }}&body={{ $emailBody }}"
                                        data-tooltip="Share by email"
                                        title="Share by email"
                                        aria-label="Share by email"
                                        class="share-icon-button share-icon-button-sm"
                                    >
                                        <span aria-hidden="true">✉</span>
                                    </a>
                                    <button
                                        type="button"
                                        data-copy-button
                                        data-copy-text="{{ $copyPayload }}"
                                        data-copy-success="Announcement copied"
                                        data-tooltip="Copy text"
                                        title="Copy text"
                                        aria-label="Copy text"
                                        class="share-icon-button share-icon-button-sm"
                                    >
                                        <span aria-hidden="true">⧉</span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm {{ $theme['meta'] }}">No member messages yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-[2rem] border p-6 shadow-sm ring-1 lg:p-7 {{ $theme['surface'] }}">
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-4 text-sm {{ $theme['meta'] }}">
                        <span>{{ $organization->members->count() }} followers</span>
                        <span>{{ $organization->events->count() }} published events</span>
                        <span>{{ $organization->visibilityLabel() }}</span>
                    </div>

                    @auth
                        @if (! auth()->user()->isMemberOf($organization))
                            <form method="POST" action="{{ route('organizations.follow', $organization) }}">
                                @csrf
                                <button class="rounded-full px-5 py-3 text-sm font-semibold {{ $theme['accent_button'] }}">Follow organization</button>
                            </form>
                        @else
                            <span class="rounded-full border px-4 py-2 text-sm font-semibold {{ $theme['accent_badge'] }}">Following</span>
                            @if (! auth()->user()->isManagerOf($organization))
                                <form method="POST" action="{{ route('organizations.leave', $organization) }}" class="inline-flex">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-full border px-4 py-2 text-sm font-semibold {{ $theme['danger_button'] }}">Leave organization</button>
                                </form>
                            @endif
                        @endif

                        <form method="POST" action="{{ route('reports.store') }}" class="inline-flex">
                            @csrf
                            <input type="hidden" name="type" value="organization">
                            <input type="hidden" name="id" value="{{ $organization->id }}">
                            <input type="hidden" name="reason" value="organization reported to CircleEvents admins">
                            <button class="rounded-full border px-4 py-2 text-sm font-semibold {{ $theme['report_button'] }}">Report organization</button>
                        </form>

                        @if (! auth()->user()->isMemberOf($organization))
                            <form method="POST" action="{{ route('blocks.store') }}" class="inline-flex">
                                @csrf
                                <input type="hidden" name="type" value="organization">
                                <input type="hidden" name="id" value="{{ $organization->id }}">
                                <button class="rounded-full border px-4 py-2 text-sm font-semibold {{ $theme['danger_button'] }}">Block</button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="rounded-full border px-5 py-3 text-sm font-semibold {{ $theme['soft_button'] }}">Log in to follow</a>
                    @endauth
                </div>

                <p class="text-[1.05em] leading-7 {{ $theme['body'] }}">{{ $organization->summary }}</p>
                <div class="mt-6 grid gap-5 md:grid-cols-3">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Owner</h2>
                        <p class="mt-1.5 text-lg font-bold {{ $theme['heading'] }}">{{ $organization->owner->name }}</p>
                        @auth
                            @if (auth()->id() !== $organization->owner->id)
                                <div class="mt-3 flex gap-2">
                                    <form method="POST" action="{{ route('reports.store') }}">
                                        @csrf
                                        <input type="hidden" name="type" value="user">
                                        <input type="hidden" name="id" value="{{ $organization->owner->id }}">
                                        <input type="hidden" name="reason" value="organizer reported to CircleEvents admins">
                                        <button class="text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['link'] }}">Report organizer</button>
                                    </form>
                                </div>
                            @endif
                        @endauth
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">City</h2>
                        <p class="mt-1.5 text-lg font-bold {{ $theme['heading'] }}">{{ $organization->city ?: 'TBA' }}</p>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Website</h2>
                        @if ($organization->website_url)
                            <a href="{{ $organization->website_url }}" class="mt-1.5 inline-flex text-lg font-bold leading-7 break-all {{ $theme['link'] }}">{{ $organization->website_url }}</a>
                        @else
                            <p class="mt-1.5 text-lg font-bold {{ $theme['heading'] }}">Not set</p>
                        @endif
                    </div>
                </div>
                @if ($organization->discord_url || $organization->twitter_url || $organization->facebook_url)
                    <div class="mt-6 border-t border-white/10 pt-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Community links</h2>
                            <div class="flex flex-wrap gap-2">
                                @if ($organization->discord_url)
                                    <a href="{{ $organization->discord_url }}" target="_blank" rel="noreferrer" class="rounded-full border px-4 py-2 text-sm font-semibold {{ $theme['soft_button'] }}">Discord</a>
                                @endif
                                @if ($organization->twitter_url)
                                    <a href="{{ $organization->twitter_url }}" target="_blank" rel="noreferrer" class="rounded-full border px-4 py-2 text-sm font-semibold {{ $theme['soft_button'] }}">X / Twitter</a>
                                @endif
                                @if ($organization->facebook_url)
                                    <a href="{{ $organization->facebook_url }}" target="_blank" rel="noreferrer" class="rounded-full border px-4 py-2 text-sm font-semibold {{ $theme['soft_button'] }}">Facebook</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                    <div class="mt-6 border-t border-white/10 pt-6">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">About</h2>
                    <p class="mt-3 leading-7 {{ $theme['body'] }}">{{ $organization->description ?: 'No long-form description has been added yet.' }}</p>
                    </div>

                    <div class="mt-6 border-t border-white/10 pt-6">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Community posts</h2>
                        <span class="text-sm {{ $theme['muted'] }}">{{ $organization->posts->count() }} posts</span>
                    </div>

                    @auth
                        @if (auth()->user()->isMemberOf($organization))
                            <form method="POST" action="{{ route('organizations.posts.store', $organization) }}" enctype="multipart/form-data" class="mt-4">
                                @csrf
                                <textarea name="body" rows="4" placeholder="Share an update, ask a question, or post to the community." class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">{{ old('body') }}</textarea>
                                <p class="mt-2 text-xs {{ $theme['muted'] }}">Supports BBCode: `[b]bold[/b]`, `[i]italic[/i]`, `[quote]quote[/quote]`, `[url=https://...]link[/url]`.</p>
                                <input name="image" type="file" accept="image/*" class="mt-3 w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                                <button class="mt-3 rounded-full px-5 py-2.5 text-sm font-semibold {{ $theme['primary_button'] }}">Post to organization</button>
                            </form>
                        @else
                            <p class="mt-4 text-sm {{ $theme['meta'] }}">Follow this organization to join the conversation.</p>
                        @endif
                    @else
                        <p class="mt-4 text-sm {{ $theme['meta'] }}">Log in and follow this organization to post.</p>
                    @endauth

                    <div class="mt-5 space-y-4">
                        @forelse ($organization->posts as $post)
                            <div class="rounded-2xl border p-4 {{ $theme['panel'] }}">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <x-user-avatar :user="$post->user" size="md" :shell="$theme['logo_shell']" />
                                        <div>
                                            <p class="font-semibold {{ $theme['heading'] }}">{{ $post->user->name }}</p>
                                        @auth
                                            @if (auth()->id() !== $post->user->id)
                                                <div class="mt-1 flex gap-2">
                                                    <form method="POST" action="{{ route('reports.store') }}">
                                                        @csrf
                                                        <input type="hidden" name="type" value="user">
                                                        <input type="hidden" name="id" value="{{ $post->user->id }}">
                                                        <input type="hidden" name="reason" value="community post reported to CircleEvents admins">
                                                        <button class="text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['link'] }}">Report to admins</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('blocks.store') }}">
                                                        @csrf
                                                        <input type="hidden" name="type" value="user">
                                                        <input type="hidden" name="id" value="{{ $post->user->id }}">
                                                        <button class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-300">Block</button>
                                                    </form>
                                                </div>
                                            @endif
                                        @endauth
                                        </div>
                                    </div>
                                    <p class="text-xs uppercase tracking-[0.2em] {{ $theme['muted'] }}">{{ $post->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="mt-3 max-w-none {{ $theme['body'] }} {{ $themeProseClass }}">{!! \App\Support\Bbcode::render($post->body) !!}</div>
                                @if ($post->image_path)
                                    <img src="{{ $post->imageUrl() }}" alt="Organization post attachment" class="mt-4 max-h-[28rem] w-full rounded-2xl object-cover">
                                @endif
                            </div>
                        @empty
                            <p class="text-sm {{ $theme['meta'] }}">No community posts yet.</p>
                        @endforelse
                    </div>
                    </div>
                </div>
            </section>

            <section class="space-y-5">
                @auth
                    @if (auth()->user()->isManagerOf($organization))
                        <div class="rounded-[2rem] border p-5 shadow-sm ring-1 lg:p-6 {{ $theme['surface_secondary'] ?? $theme['surface'] }}">
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
                                    <p class="text-sm uppercase tracking-[0.25em] {{ $theme['link'] }}">Primary action</p>
                                    <h2 class="text-2xl font-bold {{ $theme['heading'] }}">Create event</h2>
                                    <p class="mt-1 text-sm {{ $theme['meta'] }}">Publish from this organization page, with repeating dates and the org-wide mailing list ready to notify followers.</p>
                                </div>
                            </div>

                            @if ($hasEventErrors)
                                <div class="mt-4 rounded-2xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                                    Fix the highlighted event details and try publishing again.
                                </div>
                            @endif

                            <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" class="mt-4 space-y-3.5" x-data="{ isOnline: {{ old('is_online') ? 'true' : 'false' }} }">
                                @csrf
                                <input type="hidden" name="organization_id" value="{{ $organization->id }}">
                                <input name="title" value="{{ old('title') }}" placeholder="Event title" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}" required>
                                <input name="summary" value="{{ old('summary') }}" placeholder="Short summary" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}" required>
                                <textarea name="description" rows="4" placeholder="Full description" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">{{ old('description') }}</textarea>

                                <div class="rounded-2xl border p-4 {{ $theme['panel'] }}">
                                    <label class="flex items-center gap-3 text-sm {{ $theme['body'] }}">
                                        <input type="checkbox" name="is_online" value="1" x-model="isOnline" class="rounded border-white/10 bg-white/5 {{ $theme['checkbox'] }}">
                                        This is an online event
                                    </label>
                                    <input x-show="isOnline" x-cloak name="online_url" value="{{ old('online_url') }}" placeholder="Optional meeting link" class="mt-3 w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                                </div>

                                <div x-show="!isOnline" x-cloak class="grid gap-4 md:grid-cols-2">
                                    <input name="venue_name" value="{{ old('venue_name') }}" data-event-venue-name placeholder="Venue" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}" x-bind:required="!isOnline">
                                    <input name="venue_address" value="{{ old('venue_address') }}" data-event-venue-address placeholder="Address" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                                </div>

                                <div x-show="!isOnline" x-cloak>
                                    <label class="mb-2 block text-sm font-medium {{ $theme['meta'] }}">Search place with Google Maps</label>
                                    <div data-google-place-widget class="rounded-2xl border border-white/10 bg-white/5 px-3 py-2"></div>
                                    <input type="hidden" name="google_place_id" value="{{ old('google_place_id') }}" data-event-place-id>
                                    <input type="hidden" name="latitude" value="{{ old('latitude') }}" data-event-latitude>
                                    <input type="hidden" name="longitude" value="{{ old('longitude') }}" data-event-longitude>
                                </div>

                                <div class="grid gap-4 md:grid-cols-4">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium {{ $theme['meta'] }}" for="org-event-start-date">Start date</label>
                                        <input id="org-event-start-date" type="date" name="start_date" value="{{ old('start_date') }}" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}" required>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium {{ $theme['meta'] }}" for="org-event-start-time">Start time</label>
                                        <select id="org-event-start-time" name="start_time" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}" required>
                                            @foreach ($timeOptions as $option)
                                                <option value="{{ $option['value'] }}" @selected(old('start_time', '00:00') === $option['value'])>{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium {{ $theme['meta'] }}" for="org-event-end-date">End date</label>
                                        <input id="org-event-end-date" type="date" name="end_date" value="{{ old('end_date') }}" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}" required>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium {{ $theme['meta'] }}" for="org-event-end-time">End time</label>
                                        <select id="org-event-end-time" name="end_time" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}" required>
                                            @foreach ($timeOptions as $option)
                                                <option value="{{ $option['value'] }}" @selected(old('end_time', '00:00') === $option['value'])>{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-3">
                                    <input x-show="!isOnline" x-cloak name="city" value="{{ old('city') }}" data-event-city placeholder="City" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                                    <input name="timezone" value="{{ old('timezone', 'Australia/Perth') }}" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}" required>
                                    <input name="capacity" value="{{ old('capacity') }}" type="number" min="1" placeholder="Capacity" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium {{ $theme['meta'] }}" for="org-event-repeat-frequency">Repeats</label>
                                        <select id="org-event-repeat-frequency" name="repeat_frequency" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                                            <option value="none" @selected(old('repeat_frequency', 'none') === 'none')>Does not repeat</option>
                                            <option value="daily" @selected(old('repeat_frequency') === 'daily')>Daily</option>
                                            <option value="weekly" @selected(old('repeat_frequency') === 'weekly')>Weekly</option>
                                            <option value="monthly" @selected(old('repeat_frequency') === 'monthly')>Monthly</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium {{ $theme['meta'] }}" for="org-event-repeat-until">Repeat until</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <input id="org-event-repeat-until" type="date" name="repeat_until_date" value="{{ old('repeat_until_date') }}" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                                            <select name="repeat_until_time" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                                                <option value="">Time</option>
                                                @foreach ($timeOptions as $option)
                                                    <option value="{{ $option['value'] }}" @selected(old('repeat_until_time') === $option['value'])>{{ $option['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl border p-4 {{ $theme['panel'] }}">
                                    <h3 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Follower reminder emails</h3>
                                    <p class="mt-2 text-sm {{ $theme['body'] }}">Choose when CircleEvents should remind followers and subscribers about this event.</p>
                                    <div class="mt-4 space-y-3">
                                        <label class="flex items-center gap-3 text-sm {{ $theme['body'] }}">
                                            <input type="checkbox" name="notify_followers_one_week_before" value="1" @checked(old('notify_followers_one_week_before')) class="rounded border-white/10 bg-white/5 {{ $theme['checkbox'] }}">
                                            Remind followers 1 week before
                                        </label>
                                        <label class="flex items-center gap-3 text-sm {{ $theme['body'] }}">
                                            <input type="checkbox" name="notify_followers_one_day_before" value="1" @checked(old('notify_followers_one_day_before')) class="rounded border-white/10 bg-white/5 {{ $theme['checkbox'] }}">
                                            Remind followers 1 day before
                                        </label>
                                        <label class="flex items-center gap-3 text-sm {{ $theme['body'] }}">
                                            <input type="checkbox" name="notify_followers_one_hour_before" value="1" @checked(old('notify_followers_one_hour_before')) class="rounded border-white/10 bg-white/5 {{ $theme['checkbox'] }}">
                                            Remind followers 1 hour before
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium {{ $theme['meta'] }}" for="org-event-image">Event image</label>
                                    <input id="org-event-image" name="image" type="file" accept="image/*" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                                </div>

                                <select name="visibility" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                                    <option value="public" @selected(old('visibility', 'public') === 'public')>Public</option>
                                    <option value="private" @selected(old('visibility') === 'private')>Private</option>
                                    <option value="unlisted" @selected(old('visibility') === 'unlisted')>Unlisted</option>
                                </select>

                                <button class="w-full rounded-full px-5 py-2.5 font-semibold {{ $theme['secondary_button'] }}">Publish event</button>
                            </form>
                        </div>
                    @endif
                @endauth

                @auth
                    @if (auth()->user()->isOwnerOf($organization))
                        <div class="rounded-[2rem] border p-5 shadow-sm ring-1 lg:p-6 {{ $theme['surface_secondary'] ?? $theme['surface'] }}">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm uppercase tracking-[0.25em] {{ $theme['muted'] }}">Publishing integrations</p>
                                    <h2 class="mt-1 text-xl font-bold {{ $theme['heading'] }}">Social accounts</h2>
                                </div>
                            </div>

                            <div class="mt-4 space-y-4">
                                {{-- Facebook --}}
                                <div class="rounded-2xl border p-4 {{ $theme['panel'] }}">
                                    <div class="flex items-center justify-between gap-3">
                                        <h3 class="font-semibold {{ $theme['heading'] }}">Facebook Page</h3>
                                        @if ($organization->facebookAccount->first())
                                            <span class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['accent_badge'] }}">Connected</span>
                                        @else
                                            <span class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Not connected</span>
                                        @endif
                                    </div>
                                    @if ($fbAccount = $organization->facebookAccount->first())
                                        <p class="mt-2 text-sm {{ $theme['body'] }}">{{ $fbAccount->facebook_page_name }}</p>
                                        <form method="POST" action="{{ route('social.facebook.disconnect', $organization) }}" class="mt-3">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-400">Disconnect</button>
                                        </form>
                                    @else
                                        <p class="mt-2 text-sm {{ $theme['meta'] }}">Connect a Facebook Page to auto-post events.</p>
                                        <a href="{{ route('social.facebook.connect', $organization) }}" class="mt-3 inline-block rounded-full border px-4 py-2 text-sm font-semibold {{ $theme['primary_button'] }}">Connect Facebook</a>
                                    @endif
                                </div>

                                {{-- X (Twitter) --}}
                                <div class="rounded-2xl border p-4 {{ $theme['panel'] }}">
                                    <div class="flex items-center justify-between gap-3">
                                        <h3 class="font-semibold {{ $theme['heading'] }}">X / Twitter</h3>
                                        @if ($organization->xAccount->first())
                                            <span class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['accent_badge'] }}">Connected</span>
                                        @else
                                            <span class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Not connected</span>
                                        @endif
                                    </div>
                                    @if ($xAccount = $organization->xAccount->first())
                                        <p class="mt-2 text-sm {{ $theme['body'] }}">@{{ $xAccount->x_screen_name }}</p>
                                        <form method="POST" action="{{ route('social.x.disconnect', $organization) }}" class="mt-3">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-400">Disconnect</button>
                                        </form>
                                    @else
                                        <p class="mt-2 text-sm {{ $theme['meta'] }}">Connect an X account to post event announcements.</p>
                                        <a href="{{ route('social.x.connect', $organization) }}" class="mt-3 inline-block rounded-full border px-4 py-2 text-sm font-semibold {{ $theme['primary_button'] }}">Connect X</a>
                                    @endif
                                </div>

                                {{-- Discord --}}
                                <div class="rounded-2xl border p-4 {{ $theme['panel'] }}">
                                    <div class="flex items-center justify-between gap-3">
                                        <h3 class="font-semibold {{ $theme['heading'] }}">Discord</h3>
                                        @if ($organization->discordAccount->first())
                                            <span class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['accent_badge'] }}">Connected</span>
                                        @else
                                            <span class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Not connected</span>
                                        @endif
                                    </div>
                                    @if ($discordAccount = $organization->discordAccount->first())
                                        <p class="mt-2 text-sm {{ $theme['body'] }}">
                                            @if ($discordAccount->channel_name)
                                                #{{ $discordAccount->channel_name }}
                                                @if ($discordAccount->guild_name)
                                                    ({{ $discordAccount->guild_name }})
                                                @endif
                                            @else
                                                Webhook configured
                                            @endif
                                        </p>
                                        <form method="POST" action="{{ route('social.discord.disconnect', $organization) }}" class="mt-3">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-400">Disconnect</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('social.discord.connect', $organization) }}" class="mt-3 space-y-3">
                                            @csrf
                                            <input type="url" name="webhook_url" placeholder="Discord webhook URL" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}" required>
                                            <button type="submit" class="rounded-full border px-4 py-2 text-sm font-semibold {{ $theme['primary_button'] }}">Connect Discord Webhook</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endauth

                @auth
                    @if (auth()->user()->isManagerOf($organization))
                        <div class="rounded-[2rem] border p-5 shadow-sm ring-1 lg:p-6 {{ $theme['surface_secondary'] ?? $theme['surface'] }}">
                            @php
                                $managerMembers = $organization->members->whereIn('pivot.role', ['owner', 'manager']);
                                $followerMembers = $organization->members->where('pivot.role', 'follower');
                            @endphp
                            <form method="POST" action="{{ route('organizations.invitations.store', $organization) }}" class="space-y-3.5">
                                @csrf
                                <input type="hidden" name="delivery" value="email">
                                <h3 class="text-lg font-semibold {{ $theme['heading'] }}">Invite people to the group</h3>
                                <input name="name" placeholder="Name (optional)" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                                <input name="email" type="email" placeholder="Email address" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}" required>
                                <select name="role" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                                    <option value="follower">Invite as follower</option>
                                    <option value="manager">Invite as manager</option>
                                </select>
                                <textarea name="message" rows="3" placeholder="Optional invite note" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}"></textarea>
                                <button class="w-full rounded-full px-5 py-2.5 font-semibold {{ $theme['primary_button'] }}">Send invite</button>
                            </form>

                            <form method="POST" action="{{ route('organizations.invitations.store', $organization) }}" class="mt-5 space-y-3.5 border-t border-white/10 pt-5">
                                @csrf
                                <input type="hidden" name="delivery" value="share">
                                <input type="hidden" name="role" value="follower">
                                <h3 class="text-lg font-semibold {{ $theme['heading'] }}">Create share invite code</h3>
                                <p class="text-sm {{ $theme['meta'] }}">Share links join people as followers. Use email invites or promotion for managers.</p>
                                <input name="name" placeholder="Label (optional)" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                                <input name="expires_at" type="datetime-local" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                                <input name="max_uses" type="number" min="1" step="1" placeholder="Max uses (optional)" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                                <textarea name="message" rows="3" placeholder="Optional invite note" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}"></textarea>
                                <button class="w-full rounded-full px-5 py-2.5 font-semibold {{ $theme['secondary_button'] }}">Create share code</button>
                            </form>

                            <div class="mt-6 border-t border-white/10 pt-6">
                                <h3 class="text-lg font-semibold {{ $theme['heading'] }}">Active share invites</h3>
                                <div class="mt-4 space-y-3">
                                    @forelse ($shareInvitations as $invitation)
                                        @php
                                            $shareAcceptUrl = route('organizations.invitations.accept-code', $invitation->share_code);
                                            $shareRevokeUrl = route('organizations.invitations.revoke', [$organization, $invitation]);
                                        @endphp
                                        <div class="rounded-2xl border p-4 {{ $theme['panel'] }}">
                                            <div class="font-semibold {{ $theme['heading'] }}">{{ $invitation->name ?: 'Share invite' }}</div>
                                            <div class="mt-1 text-xs uppercase tracking-[0.2em] {{ $theme['link'] }}">Code {{ $invitation->share_code }}</div>
                                            <div class="mt-2 text-sm {{ $theme['meta'] }}">{{ $invitation->expires_at ? 'Expires '.$invitation->expires_at->diffForHumans() : 'No expiry' }}</div>
                                            <div class="mt-1 text-sm {{ $theme['meta'] }}">{{ $invitation->use_count }} uses{{ $invitation->max_uses ? ' of '.$invitation->max_uses : '' }}</div>
                                            <input readonly value="{{ $shareAcceptUrl }}" class="mt-3 w-full rounded-2xl border px-4 py-2.5 text-sm {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                                            <div class="mt-3 flex flex-wrap gap-3">
                                                <button
                                                    type="button"
                                                    data-copy-button
                                                    data-copy-text="{{ $shareAcceptUrl }}"
                                                    data-copy-success="Link copied"
                                                    class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['soft_button'] }}"
                                                >
                                                    Copy link
                                                </button>
                                                <form method="POST" action="{{ $shareRevokeUrl }}">
                                                    @csrf
                                                    <button class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['danger_button'] }}">Revoke code</button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-sm {{ $theme['meta'] }}">No active share invites.</p>
                                    @endforelse
                                </div>
                            </div>

                            @if ($pendingInvitations->isNotEmpty())
                                <div class="mt-6 border-t border-white/10 pt-6">
                                    <h3 class="text-lg font-semibold {{ $theme['heading'] }}">Pending invites</h3>
                                    <div class="mt-4 space-y-3">
                                        @foreach ($pendingInvitations as $invitation)
                                            <div x-data="{ cancelling: false }" class="rounded-2xl border p-4 {{ $theme['panel'] }}">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div>
                                                        <div class="font-semibold {{ $theme['heading'] }}">{{ $invitation->name ?: $invitation->email }}</div>
                                                        <div class="mt-1 text-sm {{ $theme['meta'] }}">{{ $invitation->email }}</div>
                                                    </div>
                                                    <button type="button" @click="cancelling = ! cancelling" class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['danger_button'] }}">Cancel invite</button>
                                                </div>
                                                <form x-show="cancelling" x-cloak method="POST" action="{{ route('organizations.invitations.revoke', [$organization, $invitation]) }}" class="mt-4 space-y-3">
                                                    @csrf
                                                    <label class="block text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Reason shown if they use the invite</label>
                                                    <textarea name="revoked_reason" rows="3" maxlength="500" class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}" placeholder="Example: This invite was sent in error, so it is no longer valid." required></textarea>
                                                    <div class="flex gap-3">
                                                        <button class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['danger_button'] }}">Confirm cancelation</button>
                                                        <button type="button" @click="cancelling = false" class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['soft_button'] }}">Keep invite</button>
                                                    </div>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mt-6 border-t border-white/10 pt-6">
                                <h3 class="text-lg font-semibold {{ $theme['heading'] }}">Managers</h3>
                                <div class="mt-4 space-y-3">
                                    @foreach ($managerMembers as $member)
                                        <div class="rounded-2xl border p-4 {{ $theme['panel'] }}">
                                            <div class="flex items-center justify-between gap-4">
                                                <div>
                                                    <div class="font-semibold {{ $theme['heading'] }}">{{ $member->name }}</div>
                                                    <div class="mt-1 text-sm {{ $theme['meta'] }}">{{ $member->email }}</div>
                                                </div>
                                                <div class="text-xs uppercase tracking-[0.2em] {{ $theme['link'] }}">{{ $member->pivot->role }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @if (auth()->user()->isOwnerOf($organization))
                                <div class="mt-6 border-t border-white/10 pt-6">
                                    <h3 class="text-lg font-semibold {{ $theme['heading'] }}">Promote follower to manager</h3>
                                    <div class="mt-4 space-y-3">
                                        @forelse ($followerMembers as $member)
                                            <form method="POST" action="{{ route('organizations.members.promote', $organization) }}" class="flex items-center justify-between gap-4 rounded-2xl border p-4 {{ $theme['panel'] }}">
                                                @csrf
                                                <input type="hidden" name="user_ids[]" value="{{ $member->id }}">
                                                <div>
                                                    <div class="font-semibold {{ $theme['heading'] }}">{{ $member->name }}</div>
                                                    <div class="mt-1 text-sm {{ $theme['meta'] }}">{{ $member->email }}</div>
                                                </div>
                                                <button class="rounded-full px-4 py-2 text-sm font-semibold {{ $theme['secondary_button'] }}">Make manager</button>
                                            </form>
                                        @empty
                                            <p class="text-sm {{ $theme['meta'] }}">No followers available to promote.</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                @endauth
                <div class="rounded-[2rem] border p-6 shadow-sm ring-1 {{ $theme['surface_secondary'] ?? $theme['surface'] }}">
                    <h2 class="text-2xl font-bold {{ $theme['heading'] }}">Mailing lists</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($visibleMailingLists as $entry)
                            <a href="{{ route('mailing-lists.show', $entry['list']) }}" class="block rounded-2xl border p-4 transition {{ $theme['panel'] }} hover:border-emerald-300/40 hover:bg-black/5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-semibold {{ $theme['heading'] }}">{{ $entry['list']->name }}</div>
                                        <div class="mt-1 text-sm {{ $theme['meta'] }}">{{ $entry['list']->audience }}</div>
                                    </div>
                                    @if ($entry['kind'] === 'event')
                                        <span class="rounded-full border border-emerald-300/30 bg-emerald-400/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-200">Event updates</span>
                                    @endif
                                </div>

                                @if ($entry['event'])
                                    <div class="mt-3 text-sm {{ $theme['meta'] }}">
                                        Linked to <span class="font-medium {{ $theme['heading'] }}">{{ $entry['event']->title }}</span>
                                    </div>
                                @endif
                            </a>
                        @empty
                            <p class="text-sm {{ $theme['meta'] }}">No mailing lists yet.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>

    <div x-data="{ manageMembersModal: false, messageMemberModal: false, selectedUsers: [], searchQuery: '' }" @open-members-modal.window="manageMembersModal = true">
            <div x-show="manageMembersModal" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="manageMembersModal = false; selectedUsers = []">
                <div @click.stop class="w-full max-w-3xl max-h-[80vh] overflow-auto rounded-3xl {{ $theme['surface'] }} p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold {{ $theme['heading'] }}">Manage Members</h2>
                        <button type="button" @click="manageMembersModal = false; selectedUsers = []" class="{{ $theme['muted'] }} hover:{{ $theme['heading'] }} text-xl">×</button>
                    </div>

                    <input type="text" x-model="searchQuery" placeholder="Search members..." class="mb-4 w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">

                    <div x-show="selectedUsers.length > 0" class="mb-4 flex flex-wrap items-center gap-3 rounded-2xl border border-amber-500/30 bg-amber-500/10 p-3">
                        <span class="text-sm text-amber-200" x-text="selectedUsers.length + ' selected'"></span>
                        <button type="button" @click="selectedUsers = []" class="text-xs {{ $theme['muted'] }} hover:{{ $theme['body'] }}">Clear</button>
                        <div class="flex gap-2 border-l border-white/10 pl-2">
                            <button type="button" @click="messageMemberModal = true" class="rounded-full border border-emerald-500 px-3 py-1 text-xs text-emerald-400">Message</button>
                            @if(auth()->user()->isOwnerOf($organization))
                            <button type="button" @click="$refs.promoteForm.submit()" class="rounded-full border border-amber-500 px-3 py-1 text-xs text-amber-400">Promote</button>
                            <button type="button" @click="$refs.demoteForm.submit()" class="rounded-full border border-blue-500 px-3 py-1 text-xs text-blue-400">Demote</button>
                            @endif
                            <button type="button" @click="$refs.removeForm.submit()" class="rounded-full border border-rose-500 px-3 py-1 text-xs text-rose-400">Remove</button>
                            <button type="button" @click="$refs.blacklistForm.submit()" class="rounded-full border border-red-600 px-3 py-1 text-xs text-red-400">Ban</button>
                        </div>
                        <form x-ref="promoteForm" method="POST" action="{{ route('organizations.members.promote', $organization) }}" class="hidden">
                            @csrf
                            <template x-for="id in selectedUsers" :key="id">
                                <input type="hidden" name="user_ids[]" :value="id">
                            </template>
                        </form>
                        <form x-ref="demoteForm" method="POST" action="{{ route('organizations.members.demote', $organization) }}" class="hidden">
                            @csrf
                            <template x-for="id in selectedUsers" :key="id">
                                <input type="hidden" name="user_ids[]" :value="id">
                            </template>
                        </form>
                        <form x-ref="removeForm" method="POST" action="{{ route('organizations.members.remove', $organization) }}" class="hidden">
                            @csrf
                            @method('delete')
                            <template x-for="id in selectedUsers" :key="id">
                                <input type="hidden" name="user_ids[]" :value="id">
                            </template>
                        </form>
                        <form x-ref="blacklistForm" method="POST" action="{{ route('organizations.members.remove', $organization) }}" class="hidden">
                            @csrf
                            @method('delete')
                            <input type="hidden" name="blacklist" value="1">
                            <template x-for="id in selectedUsers" :key="id">
                                <input type="hidden" name="user_ids[]" :value="id">
                            </template>
                        </form>
                    </div>

                    @php
                        $managerMembersList = $organization->members->whereIn('pivot.role', ['owner', 'manager']);
                        $followerMembersList = $organization->members->where('pivot.role', 'follower');
                    @endphp

                    <div class="space-y-6">
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }} mb-3">Managers ({{ $managerMembersList->count() }})</h3>
                            <div class="space-y-2">
                                @forelse($managerMembersList as $member)
                                    <div class="flex items-center justify-between rounded-2xl border p-4 {{ $theme['panel'] }}">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" x-model="selectedUsers" value="{{ $member->id }}" class="rounded border {{ $theme['checkbox'] }}">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $theme['logo_shell'] }} text-sm font-bold">
                                                {{ str($member->name)->substr(0, 2)->upper() }}
                                            </div>
                                            <div>
                                                <div class="font-semibold {{ $theme['heading'] }}">{{ $member->name }}</div>
                                                <div class="text-sm {{ $theme['meta'] }}">{{ $member->email }}</div>
                                            </div>
                                        </div>
                                        @if($member->pivot->role === 'manager' && auth()->user()->isOwnerOf($organization))
                                            <div class="flex gap-2">
                                                <button type="button" @click="selectedUsers = [...selectedUsers, {{ $member->id }}]; messageMemberModal = true" class="rounded-full border px-3 py-1 text-xs {{ $theme['soft_button'] }}">Message</button>
                                                <form method="POST" action="{{ route('organizations.members.remove', [$organization, $member]) }}">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="rounded-full border px-3 py-1 text-xs {{ $theme['danger_button'] }}">Remove</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-xs {{ $theme['muted'] }}">Owner</span>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-sm {{ $theme['meta'] }}">No managers found.</p>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }} mb-3">Followers ({{ $followerMembersList->count() }})</h3>
                            <div class="space-y-2">
                                @forelse($followerMembersList as $member)
                                    <div class="flex items-center justify-between rounded-2xl border p-4 {{ $theme['panel'] }}">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" x-model="selectedUsers" value="{{ $member->id }}" class="rounded border {{ $theme['checkbox'] }}">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $theme['logo_shell'] }} text-sm font-bold">
                                                {{ str($member->name)->substr(0, 2)->upper() }}
                                            </div>
                                            <div>
                                                <div class="font-semibold {{ $theme['heading'] }}">{{ $member->name }}</div>
                                                <div class="text-sm {{ $theme['meta'] }}">{{ $member->email }}</div>
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" @click="selectedUsers = [...selectedUsers, {{ $member->id }}]; messageMemberModal = true" class="rounded-full border px-3 py-1 text-xs {{ $theme['soft_button'] }}">Message</button>
                                            <form method="POST" action="{{ route('organizations.members.remove', [$organization, $member]) }}">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="rounded-full border px-3 py-1 text-xs {{ $theme['danger_button'] }}">Remove</button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm {{ $theme['meta'] }}">No followers yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="messageMemberModal" x-cloak x-transition class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4" @click.self="messageMemberModal = false">
                <div @click.stop class="w-full max-w-lg rounded-3xl {{ $theme['surface'] }} p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold {{ $theme['heading'] }}">Send Message</h2>
                        <button type="button" @click="messageMemberModal = false" class="{{ $theme['muted'] }} hover:{{ $theme['heading'] }} text-xl">×</button>
                    </div>
                    <form method="POST" action="{{ route('organizations.messages.send-member', $organization) }}">
                        @csrf
                        <template x-for="id in selectedUsers" :key="id">
                            <input type="hidden" name="user_ids[]" :value="id">
                        </template>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium {{ $theme['body'] }} mb-2">Subject</label>
                                <input type="text" name="subject" required class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium {{ $theme['body'] }} mb-2">Message</label>
                                <textarea name="body" rows="4" required class="w-full rounded-2xl border px-4 py-2.5 {{ $theme['input'] }} {{ $controlThemeClass }} {{ $controlReadableClass }}"></textarea>
                            </div>
                            <button type="submit" class="w-full rounded-full px-5 py-2.5 font-semibold {{ $theme['secondary_button'] }}">Send message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>
