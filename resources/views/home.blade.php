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
                        <a href="#organizations" class="rounded-full border border-white/15 px-4 py-2 text-stone-200 transition hover:border-amber-300 hover:text-amber-200">Organizations</a>
                        <a href="{{ route('install') }}" class="rounded-full border border-white/15 px-4 py-2 text-stone-200 transition hover:border-amber-300 hover:text-amber-200">Install</a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-full bg-amber-300 px-4 py-2 font-semibold text-stone-950">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-full border border-white/15 px-4 py-2 text-stone-200 transition hover:border-amber-300 hover:text-amber-200">Log in</a>
                            <a href="{{ route('register') }}" class="rounded-full bg-emerald-300 px-4 py-2 font-semibold text-stone-950">Register</a>
                        @endauth
                    </nav>
                </header>

                <section class="grid gap-10 py-16 lg:grid-cols-[1.3fr_.7fr] lg:items-end">
                    <div>
                        <p class="mb-4 text-sm uppercase tracking-[0.35em] text-amber-200/80">Find events. Follow communities. Stay in the loop.</p>
                        <h1 class="max-w-4xl text-5xl font-black leading-tight text-white lg:text-7xl">One place for public events and the communities behind them.</h1>
                        <p class="mt-6 max-w-2xl text-lg leading-8 text-stone-300">
                            Discover upcoming public events, browse local organizations, RSVP, and follow the groups you care about. CircleEvents is built for clubs, hobby groups, campuses, neighborhoods, and recurring community organizers.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="{{ route('register') }}" class="rounded-full bg-emerald-300 px-6 py-3 font-semibold text-stone-950">Create free account</a>
                            <a href="{{ route('events.index') }}" class="rounded-full bg-amber-300 px-6 py-3 font-semibold text-stone-950">See upcoming events</a>
                            @auth
                                <a href="{{ route('dashboard') }}" class="rounded-full border border-white/20 px-6 py-3 font-semibold text-white">Open dashboard</a>
                            @else
                                <a href="#organizations" class="rounded-full border border-white/20 px-6 py-3 font-semibold text-white">Browse organizations</a>
                            @endauth
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 backdrop-blur">
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-200">Why people use it</p>
                        <div class="mt-6 grid gap-4">
                            <div class="rounded-2xl bg-black/20 p-4">
                                <p class="text-sm text-stone-400">Public discovery</p>
                                <p class="mt-1 text-lg font-semibold text-white">Find public events without digging through social feeds</p>
                            </div>
                            <div class="rounded-2xl bg-black/20 p-4">
                                <p class="text-sm text-stone-400">Community follow</p>
                                <p class="mt-1 text-lg font-semibold text-white">Follow organizations and get notified when they publish new events</p>
                            </div>
                            <div class="rounded-2xl bg-black/20 p-4">
                                <p class="text-sm text-stone-400">Real event pages</p>
                                <p class="mt-1 text-lg font-semibold text-white">RSVP, invitations, attendee updates, and discussion in one place</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="py-8">
                    <div class="mb-6 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-amber-200/70">Public events</p>
                            <h2 class="mt-2 text-3xl font-bold text-white">What’s coming up</h2>
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
                            <div class="rounded-[1.75rem] border border-dashed border-white/20 bg-white/5 p-8 text-stone-300">No public events have been published yet.</div>
                        @endforelse
                    </div>
                </section>

                <section id="organizations" class="grid gap-8 py-12 lg:grid-cols-[1.1fr_.9fr]">
                    <div class="rounded-[2rem] border border-white/10 bg-white/5 p-8">
                        <p class="text-sm uppercase tracking-[0.3em] text-emerald-200/80">Organizations</p>
                        <h2 class="mt-2 text-3xl font-bold text-white">Public organizations</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-stone-300">Browse communities, see what they organize, and follow the ones you want updates from.</p>
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
                        <p class="text-sm uppercase tracking-[0.3em] text-amber-200/80">Get started</p>
                        <h2 class="mt-2 text-3xl font-bold text-white">Join and organize</h2>
                        <div class="mt-6 space-y-4">
                            <div class="rounded-2xl bg-black/20 p-5">
                                <h3 class="text-lg font-semibold text-white">For attendees</h3>
                                <p class="mt-2 text-sm leading-6 text-stone-300">Create an account to RSVP, follow organizations, receive new event emails, and join attendee discussions.</p>
                            </div>
                            <div class="rounded-2xl bg-black/20 p-5">
                                <h3 class="text-lg font-semibold text-white">For organizers</h3>
                                <p class="mt-2 text-sm leading-6 text-stone-300">Create a public organization page, publish events, invite people, message members, and keep your community updated.</p>
                            </div>
                            <div class="flex flex-wrap gap-3 pt-2">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="rounded-full bg-amber-300 px-5 py-3 font-semibold text-stone-950">Open dashboard</a>
                                @else
                                    <a href="{{ route('register') }}" class="rounded-full bg-emerald-300 px-5 py-3 font-semibold text-stone-950">Register now</a>
                                    <a href="{{ route('login') }}" class="rounded-full border border-white/20 px-5 py-3 font-semibold text-white">Log in</a>
                                @endauth
                                <a href="{{ route('install') }}" class="rounded-full border border-white/20 px-5 py-3 font-semibold text-white">Self-host CircleEvents</a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </body>
</html>
