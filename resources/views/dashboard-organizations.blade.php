<?php
$searchModalOpen = $tagSearch !== '';
?>

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] {{ $theme['eyebrow'] }}">Organization dashboard</p>
                <h2 class="text-3xl font-black leading-tight {{ $theme['header_heading'] }}">{{ __('Your organizations') }}</h2>
                <p class="mt-2 max-w-2xl text-sm {{ $theme['muted'] }}">Manage the organizations you run, keep track of groups you follow, and discover public organizations by tag.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    x-data
                    x-on:click.prevent="$dispatch('open-modal', 'organization-search')"
                    class="rounded-full border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold {{ $theme['soft_button'] }}"
                >
                    Search public organizations
                </button>
                <a href="#new-organization" class="rounded-full px-5 py-3 text-sm font-semibold {{ $theme['primary_button'] }}">New organization</a>
                <a href="{{ route('dashboard') }}" class="rounded-full border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold {{ $theme['soft_button'] }}">Main dashboard</a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1.15fr_.85fr] lg:px-8">
            <div class="space-y-6">
                @if (session('status'))
                    <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800 {{ $theme['surface'] }}">
                        {{ session('status') }}
                    </div>
                @endif

                <section class="rounded-[2rem] border border-white/10 p-6 shadow-sm ring-1 ring-white/10 {{ $theme['surface'] }}">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-2xl font-bold {{ $theme['heading'] }}">Organizations you are in</h3>
                            <p class="mt-1 text-sm {{ $theme['meta'] }}">{{ $allOrganizations->count() }} memberships across managed and followed organizations.</p>
                        </div>
                        <div class="flex gap-2 text-sm">
                            <span class="rounded-full bg-white/10 px-4 py-2 {{ $theme['body'] }}">{{ $managedOrganizations->count() }} managed</span>
                            <span class="rounded-full bg-white/10 px-4 py-2 {{ $theme['body'] }}">{{ $followedOrganizations->count() }} followed</span>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @forelse ($allOrganizations as $organization)
                            <article class="rounded-3xl border border-white/10 bg-white/5 p-5">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-stone-800 text-sm font-black text-amber-200">
                                        @if ($organization->avatar_path)
                                            <img src="{{ $organization->avatarUrl() }}" alt="{{ $organization->name }} logo" class="h-full w-full object-cover">
                                        @else
                                            {{ str($organization->name)->substr(0, 2)->upper() }}
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('organizations.show', $organization) }}" class="text-lg font-bold {{ $theme['heading'] }}">{{ $organization->name }}</a>
                                            <span class="rounded-full bg-black/20 px-3 py-1 text-xs uppercase tracking-[0.18em] {{ $theme['meta'] }}">{{ $organization->pivot->role }}</span>
                                        </div>
                                        <p class="mt-2 text-sm {{ $theme['meta'] }}">{{ $organization->summary }}</p>
                                        @if ($organization->tagList() !== [])
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @foreach ($organization->tagList() as $tag)
                                                    <span class="rounded-full border border-white/10 bg-black/20 px-3 py-1 text-xs {{ $theme['body'] }}">{{ $tag }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-3">
                                    <a href="{{ route('organizations.show', $organization) }}" class="rounded-full border border-white/10 px-4 py-2 text-sm font-semibold {{ $theme['soft_button'] }}">Open</a>
                                    @if (in_array($organization->pivot->role, ['owner', 'manager'], true))
                                        <a href="{{ route('organizations.edit', $organization) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $theme['secondary_button'] }}">Edit</a>
                                        <a href="{{ route('organizations.members.index', $organization) }}" class="rounded-full border border-white/10 px-4 py-2 text-sm font-semibold {{ $theme['soft_button'] }}">Members</a>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="rounded-3xl border border-dashed border-white/15 p-5 text-sm {{ $theme['meta'] }}">You are not in any organizations yet. Search public organizations by tag or create one.</div>
                        @endforelse
                    </div>
                </section>

                @if ($tagSearch !== '')
                    <section class="rounded-[2rem] border border-white/10 p-6 shadow-sm ring-1 ring-white/10 {{ $theme['surface'] }}">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-2xl font-bold {{ $theme['heading'] }}">Search results</h3>
                                <p class="mt-1 text-sm {{ $theme['meta'] }}">Public organizations tagged with {{ implode(', ', $tagTerms) }}.</p>
                            </div>
                            <button type="button" x-data x-on:click.prevent="$dispatch('open-modal', 'organization-search')" class="rounded-full border border-white/10 px-4 py-2 text-sm font-semibold {{ $theme['soft_button'] }}">Refine</button>
                        </div>
                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            @forelse ($searchResults as $organization)
                                <a href="{{ route('organizations.show', $organization) }}" class="rounded-3xl border border-white/10 bg-white/5 p-5 transition hover:border-amber-300/50">
                                    <div class="font-bold {{ $theme['heading'] }}">{{ $organization->name }}</div>
                                    <p class="mt-2 text-sm {{ $theme['meta'] }}">{{ $organization->summary }}</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($organization->tagList() as $tag)
                                            <span class="rounded-full border border-white/10 bg-black/20 px-3 py-1 text-xs {{ $theme['body'] }}">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                </a>
                            @empty
                                <div class="rounded-3xl border border-dashed border-white/15 p-5 text-sm {{ $theme['meta'] }}">No public organizations matched those tags.</div>
                            @endforelse
                        </div>
                    </section>
                @endif
            </div>

            <aside class="space-y-6">
                <section id="new-organization" class="rounded-[2rem] p-6 shadow-sm {{ $theme['page_backdrop'] }}">
                    <h3 class="text-2xl font-bold {{ $theme['heading'] }}">Create an organization</h3>
                    @if ($organizationRegistrationMode === 'moderated' && ! auth()->user()->is_admin)
                        <p class="mt-2 text-sm text-amber-200">New organizations currently need approval from a CircleEvents admin before they go public or can publish events.</p>
                    @endif

                    @if ($errors->any())
                        <div class="mt-4 rounded-2xl border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">
                            Please fix the highlighted fields and try again.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('organizations.store') }}" enctype="multipart/form-data" novalidate class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label class="text-sm font-medium {{ $theme['body'] }}" for="org-name">Name</label>
                            <input id="org-name" name="name" value="{{ old('name') }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white" required>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <label class="text-sm font-medium {{ $theme['body'] }}" for="org-summary">Summary</label>
                            <input id="org-summary" name="summary" value="{{ old('summary') }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white" required>
                            <x-input-error :messages="$errors->get('summary')" class="mt-2" />
                        </div>
                        <div>
                            <label class="text-sm font-medium {{ $theme['body'] }}" for="org-tags">Tags</label>
                            <input id="org-tags" name="tags" value="{{ old('tags') }}" placeholder="music, tabletop games, volunteering" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">
                            <x-input-error :messages="$errors->get('tags')" class="mt-2" />
                        </div>
                        <div>
                            <label class="text-sm font-medium {{ $theme['body'] }}" for="org-description">Description</label>
                            <textarea id="org-description" name="description" rows="4" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium {{ $theme['body'] }}" for="org-city">City</label>
                                <input id="org-city" name="city" value="{{ old('city') }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">
                                <x-input-error :messages="$errors->get('city')" class="mt-2" />
                            </div>
                            <div>
                                <label class="text-sm font-medium {{ $theme['body'] }}" for="org-url">Website</label>
                                <input id="org-url" name="website_url" value="{{ old('website_url') }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">
                                <x-input-error :messages="$errors->get('website_url')" class="mt-2" />
                            </div>
                        </div>
                        @include('organizations.partials.theme-picker', ['selectedThemeKey' => old('theme_key', 'embers')])
                        <div>
                            <select name="visibility" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">
                                <option value="public" @selected(old('visibility', 'public') === 'public')>Public</option>
                                <option value="private" @selected(old('visibility') === 'private')>Private</option>
                                <option value="unlisted" @selected(old('visibility') === 'unlisted')>Unlisted</option>
                            </select>
                            <x-input-error :messages="$errors->get('visibility')" class="mt-2" />
                        </div>
                        <button type="submit" class="w-full rounded-full bg-amber-300 px-5 py-3 font-semibold {{ $theme['secondary_button'] }}">Create organization</button>
                    </form>
                </section>

                @if ($availableTags->isNotEmpty())
                    <section class="rounded-[2rem] border border-white/10 p-6 shadow-sm ring-1 ring-white/10 {{ $theme['surface'] }}">
                        <h3 class="text-xl font-bold {{ $theme['heading'] }}">Popular public tags</h3>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($availableTags as $tag)
                                <a href="{{ route('dashboard.organizations', ['tag' => $tag]) }}" class="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-sm {{ $theme['body'] }}">{{ $tag }}</a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </div>

    <x-modal name="organization-search" :show="$searchModalOpen" maxWidth="2xl" focusable>
        <div class="p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Organization search</p>
                    <h2 class="mt-2 text-2xl font-black {{ $theme['heading'] }}">Find public organizations by tag</h2>
                </div>
                <button type="button" x-on:click="$dispatch('close-modal', 'organization-search')" class="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ $theme['body'] }}">Close</button>
            </div>

            <form method="GET" action="{{ route('dashboard.organizations') }}" class="mt-6 space-y-4">
                <div>
                    <label class="text-sm font-medium {{ $theme['body'] }}" for="organization-tag-search">Tags</label>
                    <input id="organization-tag-search" name="tag" value="{{ $tagSearch }}" placeholder="music, community, volunteering" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                </div>
                <button class="w-full rounded-full bg-amber-300 px-5 py-3 font-semibold text-stone-950">Search organizations</button>
            </form>

            @if ($availableTags->isNotEmpty())
                <div class="mt-6">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['meta'] }}">Popular tags</h3>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($availableTags as $tag)
                            <a href="{{ route('dashboard.organizations', ['tag' => $tag]) }}" class="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-sm {{ $theme['body'] }}">{{ $tag }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </x-modal>
</x-app-layout>
