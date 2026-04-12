<section>
    <header>
        <h2 class="text-lg font-medium {{ $theme['heading'] }}">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm {{ $theme['meta'] }}">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="avatar" :value="__('Avatar')" />
            <div class="mt-3 flex flex-wrap items-center gap-4">
                <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-3xl border {{ $theme['panel'] }} {{ $theme['logo_shell'] }} text-xl font-black">
                    @if ($user->avatarUrl())
                        <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }} avatar" class="h-full w-full object-cover">
                    @else
                        <span>{{ $user->avatarInitials() }}</span>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <input id="avatar" name="avatar" type="file" accept="image/*" class="block w-full rounded-2xl border {{ $theme['input'] }}">
                    <p class="mt-2 text-sm {{ $theme['muted'] }}">Upload a square image. CircleEvents stores it as 256 x 256.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full {{ $theme['input'] }}" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full {{ $theme['input'] }}" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm {{ $theme['body'] }}">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="rounded-md text-sm {{ $theme['link'] }} underline focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-2 focus:ring-offset-stone-950 hover:text-amber-200">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium {{ $theme['accent_button'] }}">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="city" :value="__('City')" />
            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full {{ $theme['input'] }}" :value="old('city', $user->city)" autocomplete="address-level2" />
            <x-input-error class="mt-2" :messages="$errors->get('city')" />
        </div>

        <div>
            <x-input-label for="bio" :value="__('Bio')" />
            <textarea id="bio" name="bio" rows="4" class="mt-1 block w-full rounded-2xl border {{ $theme['input'] }} shadow-sm focus:border-amber-300 focus:ring-amber-300">{{ old('bio', $user->bio) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div>
            <x-input-label for="font_size" :value="__('Reading size')" />
            <p class="mt-1 text-sm {{ $theme['muted'] }}">Increase this if the site text feels too small.</p>
            <select id="font_size" name="font_size" class="mt-2 block w-full rounded-2xl border {{ $theme['input'] }} shadow-sm focus:border-amber-300 focus:ring-amber-300">
                <option value="small" @selected(old('font_size', $user->font_size ?? 'medium') === 'small')>Small</option>
                <option value="medium" @selected(old('font_size', $user->font_size ?? 'medium') === 'medium')>Medium</option>
                <option value="large" @selected(old('font_size', $user->font_size ?? 'medium') === 'large')>Large</option>
                <option value="x-large" @selected(old('font_size', $user->font_size ?? 'medium') === 'x-large')>Extra large</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('font_size')" />
        </div>

        <div>
            <p class="text-sm font-medium {{ $theme['meta'] }}">Personal theme</p>
            <p class="mt-1 text-sm {{ $theme['muted'] }}">Apply your own theme to home, dashboard and all organizations you visit.</p>
            
            @php
                $themePresets = \App\Support\OrganizationThemes::presets();
                $featuredPresets = \App\Support\OrganizationThemes::featuredPresets();
                $selectedThemeKey = old('personal_theme', $user->personal_theme ?? '');
            @endphp

            <div x-data="{ open: false, selected: @js($selectedThemeKey), activeCategory: 'basic' }">
                <input type="hidden" name="personal_theme" x-model="selected">

                <div class="theme-picker-grid mt-4">
                    <button
                        type="button"
                        @click="selected = ''"
                        class="theme-picker-card {{ $theme['mode'] === 'light' ? 'bg-white/95 text-stone-900' : 'bg-black/30 text-stone-100' }}"
                        :data-selected="selected === '' ? 'true' : 'false'"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold">Default</p>
                                <p class="text-xs uppercase tracking-[0.25em] {{ $theme['mode'] === 'light' ? 'text-stone-500' : 'text-stone-400' }}">auto</p>
                            </div>
                            <span x-show="selected === ''" x-cloak class="rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] {{ $theme['accent_button'] }}">Selected</span>
                        </div>
                        <div class="theme-preview mt-3 bg-[radial-gradient(circle_at_top_left,_rgba(251,146,60,0.08),_transparent_24%),linear-gradient(180deg,_transparent,_transparent)]"></div>
                        <div class="mt-3">
                            <p class="text-sm {{ $theme['mode'] === 'light' ? 'text-stone-600' : 'text-stone-300' }}">Use organization or default theme</p>
                        </div>
                    </button>

                    @foreach ($featuredPresets as $themeKey => $themePreset)
                        <button
                            type="button"
                            @click="selected = '{{ $themeKey }}'"
                            class="theme-picker-card {{ $themePreset['mode'] === 'light' ? 'bg-white/95 text-stone-900' : 'bg-black/30 text-stone-100' }}"
                            :data-selected="selected === '{{ $themeKey }}' ? 'true' : 'false'"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold {{ $themePreset['font_display'] }}">{{ $themePreset['name'] }}</p>
                                    <p class="text-xs uppercase tracking-[0.25em] {{ $themePreset['mode'] === 'light' ? 'text-stone-500' : 'text-stone-400' }}">{{ $themePreset['mode'] }}</p>
                                </div>
                                <span x-show="selected === '{{ $themeKey }}'" x-cloak class="rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] {{ $themePreset['accent_button'] }}">Selected</span>
                            </div>
                            <div class="theme-preview mt-3 {{ $themePreset['hero'] }}"></div>
                            <div class="mt-3">
                                <p class="text-sm {{ $themePreset['font_body'] }} {{ $themePreset['mode'] === 'light' ? 'text-stone-600' : 'text-stone-300' }}">{{ $themePreset['description'] }}</p>
                            </div>
                        </button>
                    @endforeach
                </div>

                <button type="button" @click="open = true" class="mt-4 rounded-full border {{ $theme['soft_button'] }} px-4 py-2 text-sm font-semibold">
                    Browse all {{ count($themePresets) }} themes
                </button>

                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4 py-8" @keydown.escape.window="open = false">
                    <div class="max-h-[90vh] w-full max-w-6xl overflow-hidden rounded-[2rem] border {{ $theme['panel'] }} {{ $theme['page_backdrop'] }} shadow-2xl">
                        <div class="flex items-center justify-between gap-4 border-b {{ $theme['panel'] }} px-6 py-5">
                            <div>
                                <h3 class="text-2xl font-bold {{ $theme['heading'] }}">Theme library</h3>
                                <p class="mt-1 text-sm {{ $theme['meta'] }}">The full theme collection. Pick one to apply it immediately.</p>
                            </div>
                            <button type="button" @click="open = false" class="rounded-full border {{ $theme['soft_button'] }} px-4 py-2 text-sm font-semibold">Close</button>
                        </div>

                        <div class="max-h-[calc(90vh-6rem)] overflow-y-auto px-6 py-6">
                            @php
                                $themeCategories = \App\Support\OrganizationThemes::categorizedPresets();
                                $themeCategoryLabels = \App\Support\OrganizationThemes::categoryLabels();
                            @endphp

                            <div class="mb-6 flex flex-wrap gap-2">
                                @foreach ($themeCategoryLabels as $categoryKey => $categoryLabel)
                                    <button
                                        type="button"
                                        @click="activeCategory = '{{ $categoryKey }}'"
                                        class="rounded-full border px-4 py-2 text-sm font-semibold transition"
                                        :class="activeCategory === '{{ $categoryKey }}' ? '{{ $theme['primary_button'] }}' : '{{ $theme['soft_button'] }}'"
                                    >
                                        {{ $categoryLabel }}
                                    </button>
                                @endforeach
                            </div>

                            @foreach ($themeCategories as $categoryKey => $categoryThemes)
                                <div x-show="activeCategory === '{{ $categoryKey }}'" x-cloak>
                                    <div class="mb-4">
                                        <h4 class="text-lg font-semibold {{ $theme['heading'] }}">{{ $themeCategoryLabels[$categoryKey] }}</h4>
                                    </div>

                                    <div class="theme-picker-grid">
                                        @foreach ($categoryThemes as $themeKey => $themePreset)
                                            <button
                                                type="button"
                                                @click="selected = '{{ $themeKey }}'; open = false"
                                                class="theme-picker-card {{ $themePreset['mode'] === 'light' ? 'bg-white/95 text-stone-900' : 'bg-black/30 text-stone-100' }}"
                                                :data-selected="selected === '{{ $themeKey }}' ? 'true' : 'false'"
                                            >
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <p class="font-semibold {{ $themePreset['font_display'] }}">{{ $themePreset['name'] }}</p>
                                                        <p class="text-xs uppercase tracking-[0.25em] {{ $themePreset['mode'] === 'light' ? 'text-stone-500' : 'text-stone-400' }}">{{ $themePreset['mode'] }}</p>
                                                    </div>
                                                    <span x-show="selected === '{{ $themeKey }}'" x-cloak class="rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] {{ $themePreset['accent_button'] }}">Selected</span>
                                                </div>
                                                <div class="theme-preview mt-3 {{ $themePreset['hero'] }}"></div>
                                                <div class="mt-3">
                                                    <p class="text-sm {{ $themePreset['font_body'] }} {{ $themePreset['mode'] === 'light' ? 'text-stone-600' : 'text-stone-300' }}">{{ $themePreset['description'] }}</p>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('personal_theme')" />
        </div>

        <div class="flex items-center gap-3" x-show="selected === ''">
            <input type="checkbox" id="override_organization_theme" name="override_organization_theme" value="1" {{ $user->organization_theme_override ? 'checked' : '' }} class="rounded border {{ $theme['checkbox'] }}">
            <label for="override_organization_theme" class="text-sm {{ $theme['body'] }}">Override organization theme</label>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm {{ $theme['meta'] }}"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
