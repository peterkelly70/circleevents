<x-app-layout>
    <x-slot name="header">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-amber-300">Organization</p>
                <h1 class="text-3xl font-black text-stone-100">Edit {{ $organization->name }}</h1>
            </div>
            <a href="{{ route('organizations.show', $organization) }}" class="rounded-full border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold text-stone-200">Back</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="rounded-[2rem] border border-white/10 bg-stone-900/70 p-8 shadow-sm ring-1 ring-white/10">
            <form method="POST" action="{{ route('organizations.update', $organization) }}" class="space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label class="text-sm font-medium text-stone-300" for="name">Name</label>
                    <input id="name" name="name" value="{{ old('name', $organization->name) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <label class="text-sm font-medium text-stone-300" for="summary">Summary</label>
                    <input id="summary" name="summary" value="{{ old('summary', $organization->summary) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                    <x-input-error :messages="$errors->get('summary')" class="mt-2" />
                </div>

                <div>
                    <label class="text-sm font-medium text-stone-300" for="description">Description</label>
                    <textarea id="description" name="description" rows="6" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">{{ old('description', $organization->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="city">City</label>
                        <input id="city" name="city" value="{{ old('city', $organization->city) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                        <x-input-error :messages="$errors->get('city')" class="mt-2" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="website_url">Website</label>
                        <input id="website_url" name="website_url" value="{{ old('website_url', $organization->website_url) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                        <x-input-error :messages="$errors->get('website_url')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-stone-300" for="visibility">Visibility</label>
                    <select id="visibility" name="visibility" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                        <option value="public" @selected(old('visibility', $organization->visibility) === 'public')>Public</option>
                        <option value="unlisted" @selected(old('visibility', $organization->visibility) === 'unlisted')>Unlisted</option>
                    </select>
                    <x-input-error :messages="$errors->get('visibility')" class="mt-2" />
                </div>

                <div class="flex gap-3 pt-4">
                    <button class="rounded-full bg-amber-300 px-6 py-3 text-sm font-semibold text-stone-950">Save changes</button>
                    <a href="{{ route('organizations.show', $organization) }}" class="rounded-full border border-white/10 bg-white/5 px-6 py-3 text-sm font-semibold text-stone-200">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
