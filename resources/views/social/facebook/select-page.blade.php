<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm uppercase tracking-[0.3em] text-amber-300">Connect Facebook</p>
            <h2 class="text-3xl font-black leading-tight text-stone-100">Select a Page</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <p class="mb-6 text-stone-300">Choose which Facebook Page to connect to {{ $organization->name }}. Events will be posted to this page.</p>

            <form method="post" action="{{ route('social.facebook.select-page', $organization) }}">
                @csrf
                
                <div class="space-y-4">
                    @foreach($pages as $page)
                        <label class="block cursor-pointer">
                            <input type="radio" name="page_id" value="{{ $page['id'] }}" class="peer sr-only" required>
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:border-amber-300/50 peer-checked:border-amber-300 peer-checked:bg-amber-300/10">
                                <p class="font-semibold text-stone-100">{{ $page['name'] }}</p>
                                <p class="mt-1 text-sm text-stone-400">Page ID: {{ $page['id'] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="mt-6 flex gap-4">
                    <x-primary-button>Connect Page</x-primary-button>
                    <a href="{{ route('organizations.show', $organization) }}" class="rounded-full border border-white/10 bg-white/5 px-6 py-3 text-sm font-semibold text-stone-200">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>