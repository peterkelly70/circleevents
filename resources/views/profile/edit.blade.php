<x-app-layout>
    @php
        if (!isset($theme)) {
            $theme = \App\Support\OrganizationThemes::get(auth()->user()?->personal_theme ?? 'embers');
        }
    @endphp
    <x-slot name="header">
        <div>
            <p class="text-sm uppercase tracking-[0.3em] {{ $theme['eyebrow'] }}">Account</p>
            <h2 class="text-3xl font-black leading-tight {{ $theme['header_heading'] }}">{{ __('Profile') }}</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] border p-4 shadow-sm {{ $theme['surface_secondary'] ?? $theme['surface'] }} sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="rounded-[2rem] border p-4 shadow-sm {{ $theme['surface_secondary'] ?? $theme['surface'] }} sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="rounded-[2rem] border p-4 shadow-sm {{ $theme['surface_secondary'] ?? $theme['surface'] }} sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
