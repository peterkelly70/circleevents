<x-guest-layout>
    @auth
        @if (! empty($inviteEventTitle) || ! empty($invitedEvent) || ! empty($inviteOrganizationName))
            <div class="mb-4 rounded-2xl border border-emerald-300/30 bg-emerald-300/10 px-4 py-3 text-sm text-emerald-100">
                @if (! empty($inviteEventTitle))
                    You were invited to <span class="font-semibold">{{ $inviteEventTitle }}</span>. You're already logged in.
                @elseif (! empty($inviteOrganizationName))
                    You were invited to follow <span class="font-semibold">{{ $inviteOrganizationName }}</span>. You're already logged in.
                @endif
                The invitation will be processed after you log in again.
            </div>
        @endif
    @else
        @if (! empty($inviteEventTitle))
            <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                You were invited to <span class="font-semibold">{{ $inviteEventTitle }}</span>. Log in with the invited email to accept it.
            </div>
        @endif

        @if (! empty($inviteOrganizationName))
            <div class="mb-4 rounded-2xl border border-emerald-300/30 bg-emerald-300/10 px-4 py-3 text-sm text-emerald-100">
                You were invited to follow <span class="font-semibold">{{ $inviteOrganizationName }}</span>. Log in with the invited email to accept it.
            </div>
        @endif
    @endauth

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $prefillEmail ?? '')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-password-input 
                            id="password" 
                            name="password" 
                            required 
                            autocomplete="current-password" 
                        />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-white/20 bg-white/5 text-amber-300 shadow-sm focus:ring-amber-300" name="remember">
                <span class="ms-2 text-sm text-stone-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
        <div>
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm text-stone-400 underline hover:text-stone-200 focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-2 focus:ring-offset-stone-950" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
            <span class="mx-2 text-stone-600">|</span>
            <a class="rounded-md text-sm text-stone-400 underline hover:text-stone-200 focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-2 focus:ring-offset-stone-950" href="{{ route('register') }}">
                {{ __('Create account') }}
            </a>
        </div>

        <x-primary-button class="ms-3">
            {{ __('Log in') }}
        </x-primary-button>
    </div>
    </form>
</x-guest-layout>
