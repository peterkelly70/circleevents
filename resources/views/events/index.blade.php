<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm uppercase tracking-[0.3em] text-amber-300">Public calendar</p>
            <h1 class="text-3xl font-black text-stone-100">Upcoming events</h1>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($events as $event)
                <a href="{{ route('events.show', $event) }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-sm ring-1 ring-white/10 transition hover:-translate-y-1 hover:border-amber-300/60 hover:ring-amber-300/30">
                    <p class="text-xs uppercase tracking-[0.2em] text-amber-300">{{ $event->starts_at->format('D d M · g:i A') }}</p>
                    <h2 class="mt-3 text-2xl font-bold text-stone-100">{{ $event->title }}</h2>
                    <p class="mt-3 text-sm leading-6 text-stone-300">{{ $event->summary }}</p>
                    <div class="mt-6 flex items-center justify-between gap-4 text-sm text-stone-400">
                        <span>{{ $event->organization->name }}</span>
                        <span>{{ $event->venue_name }}</span>
                    </div>
                </a>
            @empty
                <div class="rounded-[2rem] border border-dashed border-white/15 bg-white/5 p-8 text-stone-300">No upcoming events have been published yet.</div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $events->links() }}
        </div>
    </div>
</x-app-layout>
