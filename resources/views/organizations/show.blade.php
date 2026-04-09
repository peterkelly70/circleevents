<x-app-layout>
    @php
        $theme = \App\Support\OrganizationThemes::get(auth()->user()?->resolvedOrganizationThemeKey($organization) ?? $organization->theme_key);
        $themeProseClass = $theme['mode'] === 'light' ? 'prose' : 'prose prose-invert';
    @endphp
    <x-slot name="header">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] {{ $theme['eyebrow'] }}">Organization</p>
                <h1 class="text-3xl font-black {{ $theme['header_heading'] }}">{{ $organization->name }}</h1>
            </div>
            @auth
                @if (auth()->user()->isManagerOf($organization))
                    <a href="{{ route('organizations.edit', $organization) }}" class="rounded-full px-5 py-3 text-sm font-semibold {{ $theme['header_button'] }}">Edit organization</a>
                @endif
            @endauth
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 {{ $theme['mode'] === 'light' ? 'text-stone-900' : 'text-stone-100' }} {{ $theme['page_backdrop'] }} {{ $theme['font_body'] }}">
        <section class="mb-6 overflow-hidden rounded-[2rem] border shadow-sm ring-1 {{ $theme['surface'] }}">
            <div class="relative h-56 {{ $theme['hero'] }} sm:h-72">
                @if ($organization->banner_path)
                    <div class="absolute inset-0 bg-black">
                        <img src="{{ $organization->bannerUrl() }}" alt="{{ $organization->name }} banner" class="h-full w-full object-contain object-center">
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t {{ $theme['hero_overlay'] }} to-transparent"></div>
                @endif

                <div class="absolute inset-x-0 bottom-0 flex items-end gap-5 px-6 pb-6 sm:px-8">
                    <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-[1.75rem] border text-3xl font-black shadow-xl sm:h-28 sm:w-28 {{ $theme['logo_shell'] }}">
                        @if ($organization->avatar_path)
                            <img src="{{ $organization->avatarUrl() }}" alt="{{ $organization->name }} logo" class="h-full w-full object-contain object-center">
                        @else
                            <span>{{ str($organization->name)->substr(0, 2)->upper() }}</span>
                        @endif
                    </div>

                    <div class="pb-1">
                        <p class="text-xs uppercase tracking-[0.35em] {{ $theme['hero_eyebrow'] }}">Community profile</p>
                        <h2 class="mt-2 text-2xl font-black sm:text-4xl {{ $theme['hero_heading'] }} {{ $theme['font_display'] }}">{{ $organization->name }}</h2>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
            <section class="rounded-[2rem] border p-8 shadow-sm ring-1 {{ $theme['surface'] }}">
                <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
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

                <p class="text-lg leading-8 {{ $theme['body'] }}">{{ $organization->summary }}</p>
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Owner</h2>
                        <p class="mt-2 text-lg font-bold {{ $theme['heading'] }}">{{ $organization->owner->name }}</p>
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
                        <p class="mt-2 text-lg font-bold {{ $theme['heading'] }}">{{ $organization->city ?: 'TBA' }}</p>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Website</h2>
                        @if ($organization->website_url)
                            <a href="{{ $organization->website_url }}" class="mt-2 inline-flex text-lg font-bold {{ $theme['link'] }}">{{ $organization->website_url }}</a>
                        @else
                            <p class="mt-2 text-lg font-bold {{ $theme['heading'] }}">Not set</p>
                        @endif
                    </div>
                </div>
                @if ($organization->discord_url || $organization->twitter_url || $organization->facebook_url)
                    <div class="mt-8 border-t border-white/10 pt-8">
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Social links</h2>
                        <div class="mt-4 flex flex-wrap gap-3">
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
                @endif
                @auth
                    @if (auth()->user()->isManagerOf($organization))
                        <div class="mt-8 border-t border-white/10 pt-8">
                            <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Discord publishing</h2>
                            <p class="mt-4 {{ $theme['body'] }}">
                                @if ($organization->discord_webhook_url)
                                    Connected.
                                    {{ $organization->auto_post_discord_events ? ' Events auto-post to Discord.' : ' Events do not auto-post yet.' }}
                                    {{ $organization->auto_post_discord_announcements ? ' Announcements default to Discord posting.' : ' Announcements require manual Discord opt-in.' }}
                                @else
                                    Not configured for automatic Discord posts yet.
                                @endif
                            </p>
                        </div>
                        <div class="mt-8 border-t border-white/10 pt-8">
                            <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Facebook publishing</h2>
                            <p class="mt-4 {{ $theme['body'] }}">
                                @if ($organization->facebook_page_id && $organization->facebook_page_access_token)
                                    Connected.
                                    {{ $organization->auto_post_facebook_events ? ' Events auto-post to Facebook.' : ' Events do not auto-post yet.' }}
                                    {{ $organization->auto_post_facebook_announcements ? ' Announcements default to Facebook posting.' : ' Announcements require manual Facebook opt-in.' }}
                                @else
                                    Not configured for Facebook Page publishing yet.
                                @endif
                            </p>
                        </div>
                    @endif
                @endauth
                <div class="mt-8 border-t border-white/10 pt-8">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">About</h2>
                    <p class="mt-4 leading-7 {{ $theme['body'] }}">{{ $organization->description ?: 'No long-form description has been added yet.' }}</p>
                </div>

                <div class="mt-8 border-t border-white/10 pt-8">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Community posts</h2>
                        <span class="text-sm {{ $theme['muted'] }}">{{ $organization->posts->count() }} posts</span>
                    </div>

                    @auth
                        @if (auth()->user()->isMemberOf($organization))
                            <form method="POST" action="{{ route('organizations.posts.store', $organization) }}" enctype="multipart/form-data" class="mt-4">
                                @csrf
                                <textarea name="body" rows="4" placeholder="Share an update, ask a question, or post to the community." class="w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}">{{ old('body') }}</textarea>
                                <p class="mt-2 text-xs {{ $theme['muted'] }}">Supports BBCode: `[b]bold[/b]`, `[i]italic[/i]`, `[quote]quote[/quote]`, `[url=https://...]link[/url]`.</p>
                                <input name="image" type="file" accept="image/*" class="mt-3 w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}">
                                <button class="mt-3 rounded-full px-5 py-3 text-sm font-semibold {{ $theme['primary_button'] }}">Post to organization</button>
                            </form>
                        @else
                            <p class="mt-4 text-sm {{ $theme['meta'] }}">Follow this organization to join the conversation.</p>
                        @endif
                    @else
                        <p class="mt-4 text-sm {{ $theme['meta'] }}">Log in and follow this organization to post.</p>
                    @endauth

                    <div class="mt-6 space-y-4">
                        @forelse ($organization->posts as $post)
                            <div class="rounded-2xl border p-5 {{ $theme['panel'] }}">
                                <div class="flex items-center justify-between gap-4">
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
            </section>

            <section class="space-y-6">
                <div class="rounded-[2rem] border p-6 shadow-sm ring-1 {{ $theme['surface'] }}">
                    <h2 class="text-2xl font-bold {{ $theme['heading'] }}">Member messages</h2>
                    <p class="mt-2 text-sm {{ $theme['meta'] }}">Managers can write announcements here and email them to all followers and members.</p>

                    @auth
                        @if (auth()->user()->isManagerOf($organization))
                            @php
                                $managerMembers = $organization->members->whereIn('pivot.role', ['owner', 'manager']);
                                $followerMembers = $organization->members->where('pivot.role', 'follower');
                            @endphp
                            <form method="POST" action="{{ route('organizations.messages.store', $organization) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                                @csrf
                                <input name="subject" placeholder="Message subject" class="w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}" required>
                                <textarea name="body" rows="5" placeholder="Write the message that members should receive on-site and by email." class="w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}" required></textarea>
                                <p class="text-xs {{ $theme['muted'] }}">Supports BBCode and an optional image attachment.</p>
                                <input name="image" type="file" accept="image/*" class="w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }}">
                                <label class="flex items-center gap-3 text-sm {{ $theme['body'] }}">
                                    <input type="checkbox" name="post_to_discord" value="1" @checked(old('post_to_discord', $organization->auto_post_discord_announcements)) class="rounded border-white/10 bg-white/5 {{ $theme['checkbox'] }}">
                                    Send this announcement to Discord too
                                </label>
                                <label class="flex items-center gap-3 text-sm {{ $theme['body'] }}">
                                    <input type="checkbox" name="post_to_facebook" value="1" @checked(old('post_to_facebook', $organization->auto_post_facebook_announcements)) class="rounded border-white/10 bg-white/5 {{ $theme['facebook_checkbox'] }}">
                                    Send this announcement to Facebook too
                                </label>
                                <button class="w-full rounded-full px-5 py-3 font-semibold {{ $theme['secondary_button'] }}">Send to members</button>
                            </form>

                            <form method="POST" action="{{ route('organizations.invitations.store', $organization) }}" class="mt-5 space-y-4 border-t border-white/10 pt-5">
                                @csrf
                                <input type="hidden" name="delivery" value="email">
                                <h3 class="text-lg font-semibold text-stone-100">Invite people to the group</h3>
                                <input name="name" placeholder="Name (optional)" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500">
                                <input name="email" type="email" placeholder="Email address" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500" required>
                                <select name="role" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                                    <option value="follower">Invite as follower</option>
                                    <option value="manager">Invite as manager</option>
                                </select>
                                <textarea name="message" rows="3" placeholder="Optional invite note" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500"></textarea>
                                <button class="w-full rounded-full bg-amber-300 px-5 py-3 font-semibold text-stone-950">Send invite</button>
                            </form>

                            <form method="POST" action="{{ route('organizations.invitations.store', $organization) }}" class="mt-5 space-y-4 border-t border-white/10 pt-5">
                                @csrf
                                <input type="hidden" name="delivery" value="share">
                                <input type="hidden" name="role" value="follower">
                                <h3 class="text-lg font-semibold text-stone-100">Create share invite code</h3>
                                <p class="text-sm text-stone-400">Share links join people as followers. Use email invites or promotion for managers.</p>
                                <input name="name" placeholder="Label (optional)" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500">
                                <input name="expires_at" type="datetime-local" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                                <input name="max_uses" type="number" min="1" step="1" placeholder="Max uses (optional)" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500">
                                <textarea name="message" rows="3" placeholder="Optional invite note" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500"></textarea>
                                <button class="w-full rounded-full bg-emerald-400 px-5 py-3 font-semibold text-stone-950">Create share code</button>
                            </form>

                            <div class="mt-6 border-t border-white/10 pt-6">
                                <h3 class="text-lg font-semibold text-stone-100">Active share invites</h3>
                                <div class="mt-4 space-y-3">
                                    @forelse ($shareInvitations as $invitation)
                                        @php
                                            $shareAcceptUrl = route('organizations.invitations.accept-code', $invitation->share_code);
                                            $shareRevokeUrl = route('organizations.invitations.revoke', [$organization, $invitation]);
                                        @endphp
                                        <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                                            <div class="font-semibold text-stone-100">{{ $invitation->name ?: 'Share invite' }}</div>
                                            <div class="mt-1 text-xs uppercase tracking-[0.2em] text-emerald-300">Code {{ $invitation->share_code }}</div>
                                            <div class="mt-2 text-sm text-stone-400">{{ $invitation->expires_at ? 'Expires '.$invitation->expires_at->diffForHumans() : 'No expiry' }}</div>
                                            <div class="mt-1 text-sm text-stone-400">{{ $invitation->use_count }} uses{{ $invitation->max_uses ? ' of '.$invitation->max_uses : '' }}</div>
                                            <input readonly value="{{ $shareAcceptUrl }}" class="mt-3 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-200">
                                            <form method="POST" action="{{ $shareRevokeUrl }}" class="mt-3">
                                                @csrf
                                                <button class="rounded-full border border-rose-300/30 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-rose-200">Revoke code</button>
                                            </form>
                                        </div>
                                    @empty
                                        <p class="text-sm text-stone-400">No active share invites.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="mt-6 border-t border-white/10 pt-6">
                                <h3 class="text-lg font-semibold text-stone-100">Managers</h3>
                                <div class="mt-4 space-y-3">
                                    @foreach ($managerMembers as $member)
                                        <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                                            <div class="flex items-center justify-between gap-4">
                                                <div>
                                                    <div class="font-semibold text-stone-100">{{ $member->name }}</div>
                                                    <div class="mt-1 text-sm text-stone-400">{{ $member->email }}</div>
                                                </div>
                                                <div class="text-xs uppercase tracking-[0.2em] text-amber-300">{{ $member->pivot->role }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @if (auth()->user()->isOwnerOf($organization))
                                <div class="mt-6 border-t border-white/10 pt-6">
                                    <h3 class="text-lg font-semibold text-stone-100">Promote follower to manager</h3>
                                    <div class="mt-4 space-y-3">
                                        @forelse ($followerMembers as $member)
                                            <form method="POST" action="{{ route('organizations.members.promote', $organization) }}" class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-black/20 p-4">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $member->id }}">
                                                <div>
                                                    <div class="font-semibold text-stone-100">{{ $member->name }}</div>
                                                    <div class="mt-1 text-sm text-stone-400">{{ $member->email }}</div>
                                                </div>
                                                <button class="rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-stone-950">Make manager</button>
                                            </form>
                                        @empty
                                            <p class="text-sm text-stone-400">No followers available to promote.</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endif
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
                            <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <h3 class="font-semibold text-stone-100">{{ $message->subject }}</h3>
                                        <p class="mt-1 text-sm text-stone-400">From {{ $message->user->name }}</p>
                                    </div>
                                    <p class="text-xs uppercase tracking-[0.2em] text-stone-500">{{ $message->created_at->diffForHumans() }}</p>
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
                                        title="Share on X"
                                        aria-label="Share on X"
                                        class="share-icon-button share-icon-button-sm"
                                    >
                                        <span aria-hidden="true">x</span>
                                    </a>
                                    <a
                                        href="mailto:?subject={{ rawurlencode($message->subject) }}&body={{ $emailBody }}"
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
                                        title="Copy text"
                                        aria-label="Copy text"
                                        class="share-icon-button share-icon-button-sm"
                                    >
                                        <span aria-hidden="true">⧉</span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-stone-400">No member messages yet.</p>
                        @endforelse
                    </div>

                    @if ($pendingInvitations->isNotEmpty())
                        <div class="mt-6 border-t border-white/10 pt-6">
                            <h3 class="text-lg font-semibold text-stone-100">Pending invites</h3>
                            <div class="mt-4 space-y-3">
                                @foreach ($pendingInvitations as $invitation)
                                    <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                                        <div class="font-semibold text-stone-100">{{ $invitation->name ?: $invitation->email }}</div>
                                        <div class="mt-1 text-sm text-stone-400">{{ $invitation->email }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                @auth
                    @if (auth()->user()->isManagerOf($organization))
                        <div class="rounded-[2rem] border border-emerald-300/20 bg-stone-900/80 p-6 shadow-sm ring-1 ring-emerald-300/15">
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
                                    <h2 class="text-2xl font-bold text-stone-100">Create event</h2>
                                    <p class="mt-1 text-sm text-stone-400">Publish directly from this organization page, with repeating dates and an automatic update list.</p>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                                @csrf
                                <input type="hidden" name="organization_id" value="{{ $organization->id }}">
                                <input name="title" placeholder="Event title" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500" required>
                                <input name="summary" placeholder="Short summary" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500" required>
                                <textarea name="description" rows="4" placeholder="Full description" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500"></textarea>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <input name="venue_name" data-event-venue-name placeholder="Venue" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500" required>
                                    <input name="venue_address" data-event-venue-address placeholder="Address" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500">
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-stone-300">Search place with Google Maps</label>
                                    <div data-google-place-widget class="rounded-2xl border border-white/10 bg-white/5 px-3 py-2"></div>
                                    <input type="hidden" name="google_place_id" data-event-place-id>
                                    <input type="hidden" name="latitude" data-event-latitude>
                                    <input type="hidden" name="longitude" data-event-longitude>
                                </div>

                                <div class="grid gap-4 md:grid-cols-4">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-stone-300" for="org-event-start-date">Start date</label>
                                        <input id="org-event-start-date" type="date" name="start_date" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-stone-300" for="org-event-start-time">Start time</label>
                                        <select id="org-event-start-time" name="start_time" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                                            @foreach ($timeOptions as $option)
                                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-stone-300" for="org-event-end-date">End date</label>
                                        <input id="org-event-end-date" type="date" name="end_date" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-stone-300" for="org-event-end-time">End time</label>
                                        <select id="org-event-end-time" name="end_time" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                                            @foreach ($timeOptions as $option)
                                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-3">
                                    <input name="city" data-event-city placeholder="City" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500">
                                    <input name="timezone" value="Australia/Perth" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                                    <input name="capacity" type="number" min="1" placeholder="Capacity" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100 placeholder:text-stone-500">
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-stone-300" for="org-event-repeat-frequency">Repeats</label>
                                        <select id="org-event-repeat-frequency" name="repeat_frequency" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                                            <option value="none">Does not repeat</option>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-stone-300" for="org-event-repeat-until">Repeat until</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <input id="org-event-repeat-until" type="date" name="repeat_until_date" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                                            <select name="repeat_until_time" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                                                <option value="">Time</option>
                                                @foreach ($timeOptions as $option)
                                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-stone-300" for="org-event-image">Event image</label>
                                    <input id="org-event-image" name="image" type="file" accept="image/*" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                                </div>

                                <select name="visibility" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                                    <option value="public">Public</option>
                                    <option value="private">Private</option>
                                    <option value="unlisted">Unlisted</option>
                                </select>

                                <button class="w-full rounded-full bg-emerald-400 px-5 py-3 font-semibold text-stone-950">Publish event</button>
                            </form>
                        </div>
                    @endif
                @endauth

                <div class="rounded-[2rem] border border-white/10 bg-stone-900/70 p-6 shadow-sm ring-1 ring-white/10">
                    <h2 class="text-2xl font-bold text-stone-100">Published events</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($organization->events as $event)
                            <a href="{{ route('events.show', $event) }}" class="block rounded-2xl border border-white/10 bg-black/20 p-4 transition hover:border-amber-300/50 hover:bg-black/30">
                                <div class="text-xs uppercase tracking-[0.2em] text-amber-300">{{ $event->starts_at->format('D d M') }}</div>
                                <div class="mt-2 font-semibold text-stone-100">{{ $event->title }}</div>
                            </a>
                        @empty
                            <p class="text-sm text-stone-400">No events published yet.</p>
                        @endforelse
                    </div>
                </div>
                <div class="rounded-[2rem] border border-white/10 bg-stone-900/70 p-6 shadow-sm ring-1 ring-white/10">
                    <h2 class="text-2xl font-bold text-stone-100">Mailing lists</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($visibleMailingLists as $entry)
                            <a href="{{ route('mailing-lists.show', $entry['list']) }}" class="block rounded-2xl border border-white/10 bg-black/20 p-4 transition hover:border-emerald-300/40 hover:bg-black/30">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-semibold text-stone-100">{{ $entry['list']->name }}</div>
                                        <div class="mt-1 text-sm text-stone-400">{{ $entry['list']->audience }}</div>
                                    </div>
                                    @if ($entry['kind'] === 'event')
                                        <span class="rounded-full border border-emerald-300/30 bg-emerald-400/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-200">Event updates</span>
                                    @endif
                                </div>

                                @if ($entry['event'])
                                    <div class="mt-3 text-sm text-stone-400">
                                        Linked to <span class="font-medium text-stone-200">{{ $entry['event']->title }}</span>
                                    </div>
                                @endif
                            </a>
                        @empty
                            <p class="text-sm text-stone-400">No mailing lists yet.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
