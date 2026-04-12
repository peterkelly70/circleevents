<?php
$isLoggedIn = isset($isLoggedIn) && $isLoggedIn;
$themeKey = $theme['key'] ?? 'embers';
$themeProseClass = $theme['mode'] === 'light' ? 'prose' : 'prose prose-invert';
?>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] {{ $theme['eyebrow'] }}">Home</p>
                <h2 class="text-3xl font-black leading-tight {{ $theme['header_heading'] }}">{{ $isLoggedIn ? 'Your feed' : 'Welcome to CircleEvents' }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 {{ $theme['mode'] === 'light' ? 'text-stone-900' : 'text-stone-100' }} {{ $theme['page_backdrop'] }} {{ $theme['font_body'] }}">

    @if($isLoggedIn)
        @if($managedOrganizations->count() > 0 || $followedOrganizations->count() > 0)
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-1 space-y-4">
                    @if($managedOrganizations->count() > 0)
                        <div class="rounded-2xl border p-5 {{ $theme['panel'] }}">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['link'] }}">Your organizations</p>
                            <div class="mt-4 space-y-2">
                                @foreach($managedOrganizations as $org)
                                    <a href="{{ route('organizations.show', $org) }}" class="flex items-center gap-3 rounded-xl bg-white/5 p-3 transition hover:bg-white/10">
                                        @if($org->avatar_path)
                                            <img src="{{ $org->avatarUrl() }}" class="h-8 w-8 rounded-full object-cover">
                                        @else
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $theme['logo_shell'] }} text-xs font-bold">{{ str($org->name)->substr(0,2)->upper() }}</div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-semibold {{ $theme['heading'] }}">{{ $org->name }}</div>
                                            <div class="text-xs {{ $theme['meta'] }}">Manager</div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($followedOrganizations->count() > 0)
                        <div class="rounded-2xl border p-5 {{ $theme['panel'] }}">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Following</p>
                            <div class="mt-4 space-y-2">
                                @foreach($followedOrganizations as $org)
                                    <a href="{{ route('organizations.show', $org) }}" class="flex items-center gap-3 rounded-xl bg-white/5 p-3 transition hover:bg-white/10">
                                        @if($org->avatar_path)
                                            <img src="{{ $org->avatarUrl() }}" class="h-8 w-8 rounded-full object-cover">
                                        @else
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $theme['logo_shell'] }} text-xs font-bold">{{ str($org->name)->substr(0,2)->upper() }}</div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-semibold {{ $theme['heading'] }}">{{ $org->name }}</div>
                                            <div class="text-xs {{ $theme['meta'] }}">Following</div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($upcomingEvents->count() > 0)
                        <div class="rounded-2xl border p-5 {{ $theme['panel'] }}">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }}">Upcoming events</p>
                            <div class="mt-4 space-y-3">
                                @foreach($upcomingEvents->take(5) as $event)
                                    <a href="{{ route('events.show', $event) }}" class="block rounded-xl bg-white/5 p-3 transition hover:bg-white/10">
                                        <div class="text-sm font-semibold {{ $theme['heading'] }}">{{ $event->title }}</div>
                                        <div class="text-xs {{ $theme['meta'] }}">{{ $event->starts_at->format('M j, g:i a') }}</div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="lg:col-span-2 space-y-6">
                    @if($feedItems->count() > 0)
                        @foreach($feedItems as $item)
                            <div class="rounded-[2rem] border p-6 {{ $theme['surface'] }}">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $theme['logo_shell'] }} text-sm font-bold">
                                        {{ str($item->author->name)->substr(0,2)->upper() }}
                                    </div>
                                    <div>
                                        <div class="font-semibold {{ $theme['heading'] }}">{{ $item->author->name }}</div>
                                        <div class="text-xs {{ $theme['meta'] }}">{{ $item->organization->name }} · {{ $item->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                @if($item->title)
                                    <h3 class="text-xl font-bold {{ $theme['heading'] }} mb-2">{{ $item->title }}</h3>
                                @endif
                                <div class="{{ $theme['body'] }}">{{ $item->body }}</div>
                            </div>
                        @endforeach
                    @else
                        <div class="rounded-[2rem] border p-8 {{ $theme['surface'] }} text-center">
                            <p class="{{ $theme['meta'] }}">No posts or announcements yet.</p>
                            <p class="text-sm {{ $theme['muted'] }} mt-2">Follow some organizations to see their updates here.</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="rounded-[2rem] border p-8 {{ $theme['surface'] }} text-center">
                <p class="{{ $theme['body'] }}">You're not following any organizations yet.</p>
                <a href="{{ route('events.index') }}" class="mt-4 inline-block rounded-full px-5 py-2.5 font-semibold {{ $theme['secondary_button'] }}">Browse events</a>
            </div>
        @endif
    @else
        <div class="space-y-12">
            <section class="text-center">
                <h1 class="text-4xl font-black {{ $theme['header_heading'] }}">Discover community events</h1>
                <p class="mt-4 text-xl {{ $theme['body'] }}">Join organizations, RSVP to events, and stay connected with your communities.</p>
                <div class="mt-8 flex justify-center gap-4">
                    <a href="{{ route('register') }}" class="rounded-full px-6 py-3 font-semibold {{ $theme['primary_button'] }}">Get started</a>
                    <a href="{{ route('events.index') }}" class="rounded-full border px-6 py-3 font-semibold {{ $theme['soft_button'] }}">Browse events</a>
                </div>
            </section>

            @if(isset($featuredEvents) && $featuredEvents->count() > 0)
            <section>
                <h2 class="text-2xl font-bold {{ $theme['heading'] }} mb-6">Upcoming events</h2>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($featuredEvents as $event)
                        <a href="{{ route('events.show', $event) }}" class="rounded-[2rem] border p-5 {{ $theme['surface'] }} hover:scale-[1.02] transition">
                            @if($event->image_path)
                                <img src="{{ $event->imageUrl() }}" class="h-40 w-full rounded-2xl object-cover mb-4">
                            @endif
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['link'] }}">{{ $event->starts_at->format('M j, g:i a') }}</div>
                            <h3 class="mt-2 text-lg font-bold {{ $theme['heading'] }}">{{ $event->title }}</h3>
                            <p class="mt-1 text-sm {{ $theme['meta'] }}">{{ $event->organization->name }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
            @endif

            @if(isset($organizations) && $organizations->count() > 0)
            <section id="organizations">
                <h2 class="text-2xl font-bold {{ $theme['heading'] }} mb-6">Popular organizations</h2>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($organizations as $org)
                        <a href="{{ route('organizations.show', $org) }}" class="rounded-[2rem] border p-5 {{ $theme['surface'] }} hover:scale-[1.02] transition">
                            <div class="flex items-center gap-3 mb-3">
                                @if($org->avatar_path)
                                    <img src="{{ $org->avatarUrl() }}" class="h-12 w-12 rounded-full object-cover">
                                @else
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full {{ $theme['logo_shell'] }} font-bold">{{ str($org->name)->substr(0,2)->upper() }}</div>
                                @endif
                                <div>
                                    <h3 class="font-bold {{ $theme['heading'] }}">{{ $org->name }}</h3>
                                    <p class="text-xs {{ $theme['meta'] }}">{{ $org->events_count }} events · {{ $org->mailing_lists_count }} lists</p>
                                </div>
                            </div>
                            @if($org->description)
                                <p class="text-sm {{ $theme['body'] }} line-clamp-2">{{ $org->description }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
            @endif
        </div>
    @endif
    </div>
</x-app-layout>