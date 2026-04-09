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
            <form method="POST" action="{{ route('organizations.update', $organization) }}" enctype="multipart/form-data" class="space-y-5">
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

                <div class="grid gap-5 md:grid-cols-3">
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="discord_url">Discord</label>
                        <input id="discord_url" name="discord_url" value="{{ old('discord_url', $organization->discord_url) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                        <x-input-error :messages="$errors->get('discord_url')" class="mt-2" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="twitter_url">X / Twitter</label>
                        <input id="twitter_url" name="twitter_url" value="{{ old('twitter_url', $organization->twitter_url) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                        <x-input-error :messages="$errors->get('twitter_url')" class="mt-2" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="facebook_url">Facebook</label>
                        <input id="facebook_url" name="facebook_url" value="{{ old('facebook_url', $organization->facebook_url) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                        <x-input-error :messages="$errors->get('facebook_url')" class="mt-2" />
                    </div>
                </div>

                <div class="rounded-[1.5rem] border border-emerald-300/20 bg-emerald-400/5 p-5">
                    <h2 class="text-lg font-semibold text-stone-100">Discord publishing</h2>
                    <p class="mt-1 text-sm text-stone-400">Connect a Discord webhook if you want newly published events cross-posted automatically.</p>
                    <div class="mt-4">
                        <label class="text-sm font-medium text-stone-300" for="discord_webhook_url">Discord webhook URL</label>
                        <input id="discord_webhook_url" name="discord_webhook_url" value="{{ old('discord_webhook_url', $organization->discord_webhook_url) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                        <x-input-error :messages="$errors->get('discord_webhook_url')" class="mt-2" />
                    </div>
                    <label class="mt-4 flex items-center gap-3 text-sm text-stone-300">
                        <input type="checkbox" name="auto_post_discord_events" value="1" @checked(old('auto_post_discord_events', $organization->auto_post_discord_events)) class="rounded border-white/10 bg-white/5 text-emerald-400 focus:ring-emerald-400">
                        Automatically post newly published non-private events to Discord
                    </label>
                    <label class="mt-4 flex items-center gap-3 text-sm text-stone-300">
                        <input type="checkbox" name="auto_post_discord_announcements" value="1" @checked(old('auto_post_discord_announcements', $organization->auto_post_discord_announcements)) class="rounded border-white/10 bg-white/5 text-emerald-400 focus:ring-emerald-400">
                        Post organization announcements to Discord by default
                    </label>
                </div>

                <div class="rounded-[1.5rem] border border-blue-300/20 bg-blue-400/5 p-5">
                    <h2 class="text-lg font-semibold text-stone-100">Facebook publishing</h2>
                    <p class="mt-1 text-sm text-stone-400">Connect a Facebook Page ID and Page access token to cross-post events and announcements to a Facebook Page.</p>
                    <div class="mt-4 grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-stone-300" for="facebook_page_id">Facebook Page ID</label>
                            <input id="facebook_page_id" name="facebook_page_id" value="{{ old('facebook_page_id', $organization->facebook_page_id) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                            <x-input-error :messages="$errors->get('facebook_page_id')" class="mt-2" />
                        </div>
                        <div>
                            <label class="text-sm font-medium text-stone-300" for="facebook_page_access_token">Facebook Page access token</label>
                            <input id="facebook_page_access_token" name="facebook_page_access_token" value="{{ old('facebook_page_access_token', $organization->facebook_page_access_token) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                            <x-input-error :messages="$errors->get('facebook_page_access_token')" class="mt-2" />
                        </div>
                    </div>
                    <label class="mt-4 flex items-center gap-3 text-sm text-stone-300">
                        <input type="checkbox" name="auto_post_facebook_events" value="1" @checked(old('auto_post_facebook_events', $organization->auto_post_facebook_events)) class="rounded border-white/10 bg-white/5 text-blue-400 focus:ring-blue-400">
                        Automatically post newly published non-private events to Facebook
                    </label>
                    <label class="mt-4 flex items-center gap-3 text-sm text-stone-300">
                        <input type="checkbox" name="auto_post_facebook_announcements" value="1" @checked(old('auto_post_facebook_announcements', $organization->auto_post_facebook_announcements)) class="rounded border-white/10 bg-white/5 text-blue-400 focus:ring-blue-400">
                        Post organization announcements to Facebook by default
                    </label>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="avatar">Logo / avatar</label>
                        <p class="mt-1 text-xs text-stone-500">Best at 512 x 512. Square logos work best here.</p>
                        @if ($organization->avatar_path)
                            <div class="mt-3">
                                <img src="{{ $organization->avatarUrl() }}" alt="{{ $organization->name }} logo" class="h-24 w-24 rounded-[1.5rem] border border-white/10 object-cover">
                            </div>
                        @endif
                        <input id="avatar" name="avatar" type="file" accept="image/*" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                        <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                    </div>

                    <div>
                        <label class="text-sm font-medium text-stone-300" for="banner">Banner image</label>
                        <p class="mt-1 text-xs text-stone-500">Best at 1600 x 480. Keep important artwork in the center band.</p>
                        @if ($organization->banner_path)
                            <div class="mt-3 overflow-hidden rounded-[1.5rem] border border-white/10">
                                <img src="{{ $organization->bannerUrl() }}" alt="{{ $organization->name }} banner" class="h-24 w-full object-cover">
                            </div>
                        @endif
                        <input id="banner" name="banner" type="file" accept="image/*" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                        <x-input-error :messages="$errors->get('banner')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <p class="text-sm font-medium text-stone-300">Organization theme</p>
                    <p class="mt-1 text-sm text-stone-500">Choose one of the six full page treatments for this organization profile.</p>
                    <div class="theme-picker-grid mt-4">
                        @foreach ($themePresets as $themeKey => $themePreset)
                            <label class="theme-picker-card {{ $themePreset['mode'] === 'light' ? 'bg-white/95 text-stone-900' : 'bg-black/30 text-stone-100' }}" data-selected="{{ old('theme_key', $organization->theme_key) === $themeKey ? 'true' : 'false' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold {{ $themePreset['font_display'] }}">{{ $themePreset['name'] }}</p>
                                        <p class="text-xs uppercase tracking-[0.25em] {{ $themePreset['mode'] === 'light' ? 'text-stone-500' : 'text-stone-400' }}">{{ $themePreset['mode'] }}</p>
                                    </div>
                                    <input class="theme-picker-radio mt-1 shrink-0" type="radio" name="theme_key" value="{{ $themeKey }}" @checked(old('theme_key', $organization->theme_key) === $themeKey)>
                                </div>
                                <div class="theme-preview mt-3 {{ $themePreset['hero'] }}"></div>
                                <div class="mt-3">
                                    <p class="text-sm {{ $themePreset['font_body'] }} {{ $themePreset['mode'] === 'light' ? 'text-stone-600' : 'text-stone-300' }}">{{ $themePreset['description'] }}</p>
                                </div>
                                <div class="mt-3 flex items-center justify-between gap-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $themePreset['accent_button'] }}">{{ old('theme_key', $organization->theme_key) === $themeKey ? 'Selected' : 'Theme' }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('theme_key')" class="mt-2" />
                </div>

                <div>
                    <label class="text-sm font-medium text-stone-300" for="visibility">Visibility</label>
                    <select id="visibility" name="visibility" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                        <option value="public" @selected(old('visibility', $organization->visibility) === 'public')>Public</option>
                        <option value="private" @selected(old('visibility', $organization->visibility) === 'private')>Private</option>
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
