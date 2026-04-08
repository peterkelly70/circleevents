<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'CircleEvents') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-950 text-stone-100">
        <div class="relative overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(251,146,60,0.28),_transparent_32%),radial-gradient(circle_at_80%_20%,_rgba(16,185,129,0.18),_transparent_28%),linear-gradient(180deg,_#1c1917,_#0c0a09)]"></div>
            <div class="relative mx-auto max-w-7xl px-6 py-8 lg:px-8">
                <header class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="text-2xl font-black tracking-tight text-amber-300">CircleEvents</a>
                    <nav class="flex items-center gap-3 text-sm">
                        <a href="{{ route('events.index') }}" class="rounded-full border border-white/15 px-4 py-2 text-stone-200 transition hover:border-amber-300 hover:text-amber-200">Browse events</a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-full bg-amber-300 px-4 py-2 font-semibold text-stone-950">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-full border border-white/15 px-4 py-2 text-stone-200 transition hover:border-amber-300 hover:text-amber-200">Log in</a>
                            <a href="{{ route('register') }}" class="rounded-full bg-emerald-300 px-4 py-2 font-semibold text-stone-950">Join now</a>
                        @endauth
                    </nav>
                </header>

                <section class="grid gap-10 py-16 lg:grid-cols-[1.3fr_.7fr] lg:items-end">
                    <div>
                        <p class="mb-4 text-sm uppercase tracking-[0.35em] text-amber-200/80">Event community operating system</p>
                        <h1 class="max-w-4xl text-5xl font-black leading-tight text-white lg:text-7xl">A Laravel event platform built to replace scattered Facebook events.</h1>
                        <p class="mt-6 max-w-2xl text-lg leading-8 text-stone-300">
                            Publish events, manage organizations, collect RSVPs, and grow mailing lists from one place. Built for clubs, campuses, local communities, and recurring organizers.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="{{ route('events.index') }}" class="rounded-full bg-amber-300 px-6 py-3 font-semibold text-stone-950">See upcoming events</a>
                            @auth
                                <a href="{{ route('dashboard') }}" class="rounded-full border border-white/20 px-6 py-3 font-semibold text-white">Manage your community</a>
                            @else
                                <a href="{{ route('register') }}" class="rounded-full border border-white/20 px-6 py-3 font-semibold text-white">Create your organizer account</a>
                            @endauth
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 backdrop-blur">
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-200">Core MVP</p>
                        <div class="mt-6 grid gap-4">
                            <div class="rounded-2xl bg-black/20 p-4">
                                <p class="text-sm text-stone-400">Authentication</p>
                                <p class="mt-1 text-lg font-semibold text-white">Login, registration, profile editing</p>
                            </div>
                            <div class="rounded-2xl bg-black/20 p-4">
                                <p class="text-sm text-stone-400">Event replacement</p>
                                <p class="mt-1 text-lg font-semibold text-white">Public event pages with RSVP states</p>
                            </div>
                            <div class="rounded-2xl bg-black/20 p-4">
                                <p class="text-sm text-stone-400">Audience growth</p>
                                <p class="mt-1 text-lg font-semibold text-white">Mailing-list subscriptions tied to organizations</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="py-8">
                    <div class="mb-6 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-amber-200/70">Featured events</p>
                            <h2 class="mt-2 text-3xl font-bold text-white">Upcoming activity</h2>
                        </div>
                        <a href="{{ route('events.index') }}" class="text-sm font-semibold text-amber-200">See all events</a>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @forelse ($featuredEvents as $event)
                            <a href="{{ route('events.show', $event) }}" class="group rounded-[1.75rem] border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-amber-300/60 hover:bg-white/10">
                                <p class="text-sm uppercase tracking-[0.2em] text-amber-200">{{ $event->starts_at->format('D, d M Y') }}</p>
                                <h3 class="mt-3 text-2xl font-bold text-white">{{ $event->title }}</h3>
                                <p class="mt-3 text-sm leading-6 text-stone-300">{{ $event->summary }}</p>
                                <div class="mt-6 flex items-center justify-between text-sm text-stone-300">
                                    <span>{{ $event->organization->name }}</span>
                                    <span>{{ $event->venue_name }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-[1.75rem] border border-dashed border-white/20 bg-white/5 p-8 text-stone-300">No published events yet. Sign in and create the first one from the dashboard.</div>
                        @endforelse
                    </div>
                </section>

                <section class="grid gap-8 py-12 lg:grid-cols-2">
                    <div class="rounded-[2rem] border border-white/10 bg-white/5 p-8">
                        <p class="text-sm uppercase tracking-[0.3em] text-emerald-200/80">Organizations</p>
                        <h2 class="mt-2 text-3xl font-bold text-white">Community hubs</h2>
                        <div class="mt-6 space-y-4">
                            @forelse ($organizations as $organization)
                                <a href="{{ route('organizations.show', $organization) }}" class="flex items-start justify-between gap-4 rounded-2xl bg-black/20 p-4 transition hover:bg-black/30">
                                    <div>
                                        <h3 class="text-lg font-semibold text-white">{{ $organization->name }}</h3>
                                        <p class="mt-1 text-sm text-stone-300">{{ $organization->summary }}</p>
                                    </div>
                                    <div class="text-right text-xs uppercase tracking-[0.2em] text-stone-400">
                                        <div>{{ $organization->events_count }} events</div>
                                        <div class="mt-1">{{ $organization->mailing_lists_count }} lists</div>
                                    </div>
                                </a>
                            @empty
                                <p class="text-stone-300">No organizations created yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-white/10 bg-white/5 p-8">
                        <p class="text-sm uppercase tracking-[0.3em] text-amber-200/80">Mailing lists</p>
                        <h2 class="mt-2 text-3xl font-bold text-white">Audience channels</h2>
                        <div class="mt-6 space-y-4">
                            @forelse ($lists as $list)
                                <a href="{{ route('mailing-lists.show', $list) }}" class="block rounded-2xl bg-black/20 p-4 transition hover:bg-black/30">
                                    <div class="flex items-center justify-between gap-4">
                                        <h3 class="text-lg font-semibold text-white">{{ $list->name }}</h3>
                                        <span class="rounded-full bg-emerald-300/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-200">{{ $list->audience }}</span>
                                    </div>
                                    <p class="mt-2 text-sm text-stone-300">{{ $list->description ?: 'Subscription list for updates, reminders, and community announcements.' }}</p>
                                    <p class="mt-4 text-xs uppercase tracking-[0.2em] text-stone-400">{{ $list->organization->name }}</p>
                                </a>
                            @empty
                                <p class="text-stone-300">No mailing lists yet.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </body>
</html>
