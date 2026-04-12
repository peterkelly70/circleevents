<x-guest-layout>
    @auth
        @if (! empty($inviteEventTitle) || ! empty($inviteOrganizationName))
            <div class="mb-4 rounded-2xl border border-emerald-300/30 bg-emerald-300/10 px-4 py-3 text-sm text-emerald-100">
                @if (! empty($inviteEventTitle))
                    You were invited to <span class="font-semibold">{{ $inviteEventTitle }}</span>. You're already logged in.
                @elseif (! empty($inviteOrganizationName))
                    You were invited to follow <span class="font-semibold">{{ $inviteOrganizationName }}</span>. You're already logged in.
                @endif
                Create an account to accept the invitation.
            </div>
        @endif
    @else
        @if (! empty($inviteEventTitle))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                You were invited to <span class="font-semibold">{{ $inviteEventTitle }}</span>. Create your account with the invited email to accept it.
            </div>
        @endif

        @if (! empty($inviteOrganizationName))
            <div class="mb-4 rounded-2xl border border-emerald-300/30 bg-emerald-300/10 px-4 py-3 text-sm text-emerald-100">
                You were invited to follow <span class="font-semibold">{{ $inviteOrganizationName }}</span>. Create your account with the invited email to accept it.
            </div>
        @endif
    @endauth

    @if (($userRegistrationMode ?? 'open') === 'moderated')
        <div class="mb-4 rounded-2xl border border-amber-300/30 bg-amber-300/10 px-4 py-3 text-sm text-amber-100">
            New accounts are currently moderated. You can sign up here, but CircleEvents admins must approve the account before it can be used.
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $prefillEmail ?? '')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-password-input 
                            id="password" 
                            name="password" 
                            required 
                            autocomplete="new-password" 
                        />
            <p class="mt-2 text-xs leading-5 text-stone-400">
                Password must be at least 8 characters long.
            </p>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-password-input 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            required 
                            autocomplete="new-password" 
                        />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-5 rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
            <label for="accepted_usage_terms" class="flex items-start gap-3">
                <input
                    id="accepted_usage_terms"
                    name="accepted_usage_terms"
                    type="checkbox"
                    value="1"
                    @checked(old('accepted_usage_terms'))
                    class="mt-1 rounded border-white/10 bg-white/5 text-amber-300 focus:ring-amber-300"
                    required
                >
                <span class="text-sm leading-6 text-stone-300">
                    I have read and accept the
                    <button
                        type="button"
                        x-on:click.prevent="$dispatch('open-modal', 'usage-conditions')"
                        class="font-semibold text-amber-300 underline underline-offset-4"
                    >
                        usage conditions
                    </button>
                    for CircleEvents.
                </span>
            </label>
            <p class="mt-3 text-xs leading-5 text-stone-400">
                Short version: don’t be a dick, don’t break the law, don’t harass people, and don’t use CircleEvents to spam, scam, or endanger anyone.
            </p>
            <x-input-error :messages="$errors->get('accepted_usage_terms')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="rounded-md text-sm text-stone-400 underline hover:text-stone-200 focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-2 focus:ring-offset-stone-950" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <x-modal name="usage-conditions" maxWidth="2xl" focusable>
        <div class="p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-amber-300">CircleEvents</p>
                    <h2 class="mt-2 text-2xl font-black text-stone-100">Usage Conditions</h2>
                </div>
                <button
                    type="button"
                    x-on:click="$dispatch('close-modal', 'usage-conditions')"
                    class="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-stone-300"
                >
                    Close
                </button>
            </div>

            <div class="mt-6 space-y-5 text-sm leading-7 text-stone-300">
                <p>
                    CircleEvents is for legitimate community organizing, event discovery, and member communication. Use it like a normal person in a shared space.
                </p>
                <div>
                    <h3 class="font-semibold text-stone-100">You must not</h3>
                    <ul class="mt-2 list-disc space-y-2 pl-5 text-stone-300">
                        <li>use the platform for anything illegal, fraudulent, abusive, threatening, or deceptive</li>
                        <li>harass, stalk, intimidate, or target other people or groups</li>
                        <li>post spam, malware, scams, or misleading event information</li>
                        <li>impersonate another person, organization, or CircleEvents staff</li>
                        <li>use CircleEvents to organize harmful, dangerous, or rights-violating activity</li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-stone-100">You are responsible for</h3>
                    <ul class="mt-2 list-disc space-y-2 pl-5 text-stone-300">
                        <li>keeping your event and organization information accurate</li>
                        <li>only contacting people who should receive your messages</li>
                        <li>respecting opt-outs, privacy, and community boundaries</li>
                        <li>following applicable laws and platform rules where you operate</li>
                    </ul>
                </div>
                <p>
                    CircleEvents admins may remove content, restrict accounts, or take moderation action when these rules are ignored.
                </p>
            </div>
        </div>
    </x-modal>
</x-guest-layout>
