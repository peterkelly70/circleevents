<x-app-layout>
    <x-slot name="header">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-amber-300">Organization</p>
                <h1 class="text-3xl font-black text-stone-100">{{ $organization->name }}</h1>
            </div>
            @auth
                @if (auth()->user()->isManagerOf($organization))
                    <a href="{{ route('organizations.edit', $organization) }}" class="rounded-full bg-amber-300 px-5 py-3 text-sm font-semibold text-stone-950">Edit organization</a>
                @endif
            @endauth
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
            <section class="rounded-[2rem] border border-white/10 bg-stone-900/70 p-8 shadow-sm ring-1 ring-white/10">
                <p class="text-lg leading-8 text-stone-300">{{ $organization->summary }}</p>
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">Owner</h2>
                        <p class="mt-2 text-lg font-bold text-stone-100">{{ $organization->owner->name }}</p>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">City</h2>
                        <p class="mt-2 text-lg font-bold text-stone-100">{{ $organization->city ?: 'TBA' }}</p>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">Website</h2>
                        @if ($organization->website_url)
                            <a href="{{ $organization->website_url }}" class="mt-2 inline-flex text-lg font-bold text-amber-300">{{ $organization->website_url }}</a>
                        @else
                            <p class="mt-2 text-lg font-bold text-stone-100">Not set</p>
                        @endif
                    </div>
                </div>
                <div class="mt-8 border-t border-white/10 pt-8">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">About</h2>
                    <p class="mt-4 leading-7 text-stone-300">{{ $organization->description ?: 'No long-form description has been added yet.' }}</p>
                </div>
            </section>

            <section class="space-y-6">
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
                        @forelse ($organization->mailingLists as $list)
                            <a href="{{ route('mailing-lists.show', $list) }}" class="block rounded-2xl border border-white/10 bg-black/20 p-4 transition hover:border-emerald-300/40 hover:bg-black/30">
                                <div class="font-semibold text-stone-100">{{ $list->name }}</div>
                                <div class="mt-1 text-sm text-stone-400">{{ $list->audience }}</div>
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
