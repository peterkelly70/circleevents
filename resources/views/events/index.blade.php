<?php
$themeKey = $theme['key'] ?? 'embers';
$themeProseClass = $theme['mode'] === 'light' ? 'prose' : 'prose prose-invert';
?>

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm uppercase tracking-[0.3em] {{ $theme['eyebrow'] }}">Public calendar</p>
            <h1 class="text-3xl font-black {{ $theme['header_heading'] }}">Upcoming events</h1>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($events as $event)
                <a href="{{ route('events.show', $event) }}" class="rounded-[2rem] border p-6 shadow-sm transition hover:-translate-y-1 {{ $theme['surface'] }}">
                    <p class="text-xs uppercase tracking-[0.2em] {{ $theme['link'] }}">{{ $event->starts_at->format('D d M · g:i A') }}</p>
                    <h2 class="mt-3 text-2xl font-bold {{ $theme['heading'] }}">{{ $event->title }}</h2>
                    <p class="mt-3 text-sm leading-6 {{ $theme['body'] }}">{{ $event->summary }}</p>
                    <div class="mt-6 flex items-center justify-between gap-4 text-sm {{ $theme['meta'] }}">
                        <span>{{ $event->organization->name }}</span>
                        <span>{{ $event->venue_name }}</span>
                    </div>
                </a>
            @empty
                <div class="rounded-[2rem] border border-dashed p-8 {{ $theme['muted'] }}">No upcoming events have been published yet.</div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $events->links() }}
        </div>
    </div>
</x-app-layout>
