<section>
    <header>
        <h2 class="text-lg font-medium text-stone-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-stone-400">
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
                <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-3xl border border-white/10 bg-white/5 text-xl font-black text-amber-200">
                    @if ($user->avatarUrl())
                        <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }} avatar" class="h-full w-full object-cover">
                    @else
                        <span>{{ $user->avatarInitials() }}</span>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <input id="avatar" name="avatar" type="file" accept="image/*" class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                    <p class="mt-2 text-sm text-stone-400">Upload a square image. CircleEvents stores it as 256 x 256.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-stone-300">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="rounded-md text-sm text-amber-300 underline focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-2 focus:ring-offset-stone-950 hover:text-amber-200">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-emerald-300">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="city" :value="__('City')" />
            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $user->city)" autocomplete="address-level2" />
            <x-input-error class="mt-2" :messages="$errors->get('city')" />
        </div>

        <div>
            <x-input-label for="bio" :value="__('Bio')" />
            <textarea id="bio" name="bio" rows="4" class="mt-1 block w-full rounded-2xl border border-white/10 bg-white/5 text-stone-100 shadow-sm focus:border-amber-300 focus:ring-amber-300">{{ old('bio', $user->bio) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div>
            <x-input-label for="font_size" :value="__('Reading size')" />
            <p class="mt-1 text-sm text-stone-400">Increase this if the site text feels too small.</p>
            <select id="font_size" name="font_size" class="mt-2 block w-full rounded-2xl border border-white/10 bg-white/5 text-stone-100 shadow-sm focus:border-amber-300 focus:ring-amber-300">
                <option value="small" @selected(old('font_size', $user->font_size ?? 'medium') === 'small')>Small</option>
                <option value="medium" @selected(old('font_size', $user->font_size ?? 'medium') === 'medium')>Medium</option>
                <option value="large" @selected(old('font_size', $user->font_size ?? 'medium') === 'large')>Large</option>
                <option value="x-large" @selected(old('font_size', $user->font_size ?? 'medium') === 'x-large')>Extra large</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('font_size')" />
        </div>

        <div>
            <x-input-label for="organization_theme_override" :value="__('Organization theme preference')" />
            <p class="mt-1 text-sm text-stone-400">Use this to override an organization’s chosen theme for your own reading comfort.</p>
            <select id="organization_theme_override" name="organization_theme_override" class="mt-2 block w-full rounded-2xl border border-white/10 bg-white/5 text-stone-100 shadow-sm focus:border-amber-300 focus:ring-amber-300">
                <option value="">Use organization default</option>
                @foreach (\App\Support\OrganizationThemes::presets() as $themeKey => $themePreset)
                    <option value="{{ $themeKey }}" @selected(old('organization_theme_override', $user->organization_theme_override) === $themeKey)>{{ $themePreset['name'] }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('organization_theme_override')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-stone-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
