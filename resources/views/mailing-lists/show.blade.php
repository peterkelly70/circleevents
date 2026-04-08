<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm uppercase tracking-[0.3em] text-amber-300">Mailing list</p>
            <h1 class="text-3xl font-black text-stone-100">{{ $mailingList->name }}</h1>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
            <section class="rounded-[2rem] border border-white/10 bg-stone-900/70 p-8 shadow-sm ring-1 ring-white/10">
                <p class="text-sm uppercase tracking-[0.2em] text-emerald-300">{{ $mailingList->organization->name }}</p>
                <p class="mt-4 text-lg leading-8 text-stone-300">{{ $mailingList->description ?: 'Use this list for reminders, updates, and organizer announcements.' }}</p>
                <div class="mt-8 rounded-3xl border border-white/10 bg-black/20 p-5">
                    <p class="text-sm uppercase tracking-[0.2em] text-stone-500">Audience</p>
                    <p class="mt-2 text-2xl font-bold text-stone-100">{{ str($mailingList->audience)->headline() }}</p>
                </div>
            </section>

            <aside class="space-y-6">
                <section class="rounded-[2rem] bg-stone-950 p-6 text-stone-100 shadow-sm">
                    <h2 class="text-2xl font-bold">Subscribe</h2>
                    <p class="mt-3 text-sm text-stone-300">Join this list to receive event reminders and organization updates.</p>
                    @auth
                        <form method="POST" action="{{ route('mailing-lists.subscribe', $mailingList) }}" class="mt-5">
                            @csrf
                            <button class="w-full rounded-full bg-amber-300 px-5 py-3 font-semibold text-stone-950">Subscribe now</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="mt-5 inline-flex rounded-full bg-amber-300 px-5 py-3 font-semibold text-stone-950">Log in to subscribe</a>
                    @endauth
                </section>

                <section class="rounded-[2rem] border border-white/10 bg-stone-900/70 p-6 shadow-sm ring-1 ring-white/10">
                    <h2 class="text-2xl font-bold text-stone-100">Subscribers</h2>
                    <p class="mt-3 text-4xl font-black text-emerald-300">{{ $mailingList->subscribers->count() }}</p>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
