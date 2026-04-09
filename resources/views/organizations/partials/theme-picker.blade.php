@php
    $themePresets = \App\Support\OrganizationThemes::presets();
    $featuredPresets = \App\Support\OrganizationThemes::featuredPresets();
    $themeCategories = \App\Support\OrganizationThemes::categorizedPresets();
    $themeCategoryLabels = \App\Support\OrganizationThemes::categoryLabels();
    $selectedThemeKey = $selectedThemeKey ?? \App\Support\OrganizationThemes::DEFAULT;
@endphp

<div x-data="{ open: false, selected: @js($selectedThemeKey), activeCategory: 'basic' }">
    <input type="hidden" name="theme_key" x-model="selected">

    <div class="flex items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-stone-300">Organization theme</p>
            <p class="mt-1 text-sm text-stone-500">Choose a featured theme here, or open the full library for the complete set.</p>
        </div>
        <button type="button" @click="open = true" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-stone-200">
            Browse all {{ count($themePresets) }} themes
        </button>
    </div>

    <div class="theme-picker-grid mt-4">
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

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4 py-8" @keydown.escape.window="open = false">
        <div class="max-h-[90vh] w-full max-w-6xl overflow-hidden rounded-[2rem] border border-white/10 bg-stone-950 shadow-2xl">
            <div class="flex items-center justify-between gap-4 border-b border-white/10 px-6 py-5">
                <div>
                    <h3 class="text-2xl font-bold text-stone-100">Theme library</h3>
                    <p class="mt-1 text-sm text-stone-400">The full organization theme collection. Pick one to apply it immediately.</p>
                </div>
                <button type="button" @click="open = false" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-stone-200">Close</button>
            </div>

            <div class="max-h-[calc(90vh-6rem)] overflow-y-auto px-6 py-6">
                <div class="mb-6 flex flex-wrap gap-2">
                    @foreach ($themeCategoryLabels as $categoryKey => $categoryLabel)
                        <button
                            type="button"
                            @click="activeCategory = '{{ $categoryKey }}'"
                            class="rounded-full border px-4 py-2 text-sm font-semibold transition"
                            :class="activeCategory === '{{ $categoryKey }}' ? 'border-amber-300 bg-amber-300 text-stone-950' : 'border-white/10 bg-white/5 text-stone-200'"
                        >
                            {{ $categoryLabel }}
                        </button>
                    @endforeach
                </div>

                @foreach ($themeCategories as $categoryKey => $categoryThemes)
                    <div x-show="activeCategory === '{{ $categoryKey }}'" x-cloak>
                        <div class="mb-4">
                            <h4 class="text-lg font-semibold text-stone-100">{{ $themeCategoryLabels[$categoryKey] }}</h4>
                            <p class="mt-1 text-sm text-stone-400">
                                @if ($categoryKey === 'basic')
                                    Straightforward defaults that fit most organizations.
                                @elseif ($categoryKey === 'fantasy')
                                    Storybook, manuscript, ember, and magical treatments.
                                @elseif ($categoryKey === 'refined')
                                    Clean editorial and polished luxury themes.
                                @elseif ($categoryKey === 'scifi')
                                    Futuristic interfaces and industrial dark palettes.
                                @elseif ($categoryKey === 'nature')
                                    Forest, lake, and ocean-inspired palettes.
                                @else
                                    Cosmic, nebula, and iridescent night themes.
                                @endif
                            </p>
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

    <x-input-error :messages="$errors->get('theme_key')" class="mt-2" />
</div>
