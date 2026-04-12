<nav x-data="{ open: false }" class="border-b border-white/10 bg-stone-950/75 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <x-application-logo class="block h-9 w-auto fill-current text-amber-500" />
                        <span class="text-sm font-black uppercase tracking-[0.3em] text-stone-100">CircleEvents</span>
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        {{ __('Home') }}
                    </x-nav-link>
                    <x-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">
                        {{ __('Events') }}
                    </x-nav-link>
                    @auth
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @endauth
                </div>
            </div>

                <div class="hidden sm:flex sm:items-center sm:gap-3 sm:ms-6">
                @auth
                    @php
                        $unreadMemberMessages = Auth::user()->memberMessages()->whereNull('read_at')->count();
                    @endphp
                    @if ($unreadMemberMessages > 0)
                        <a href="{{ route('notifications.member-messages') }}" class="relative inline-flex items-center justify-center rounded-full bg-amber-500 px-3 py-2 text-xs font-bold text-stone-950">
                            {{ $unreadMemberMessages }}
                        </a>
                    @endif

                    <span class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 py-1.5 pl-1.5 pr-4 text-sm font-medium text-stone-300">
                        <span class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full bg-amber-300/15 text-xs font-black text-amber-200">
                            @if (Auth::user()->avatarUrl())
                                <img src="{{ Auth::user()->avatarUrl() }}" alt="{{ Auth::user()->name }} avatar" class="h-full w-full object-cover">
                            @else
                                {{ Auth::user()->avatarInitials() }}
                            @endif
                        </span>
                        {{ Auth::user()->name }}
                    </span>

                    <x-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                        {{ __('Profile') }}
                    </x-nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-stone-400 transition duration-150 ease-in-out hover:border-stone-300 hover:text-stone-200 focus:outline-none focus:text-stone-200 focus:border-stone-300">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                @else
                    <x-nav-link :href="route('login')" :active="request()->routeIs('login')">
                        {{ __('Log In') }}
                    </x-nav-link>

                    <a href="{{ route('register') }}" class="inline-flex items-center rounded-full bg-amber-300 px-4 py-2 text-sm font-semibold text-stone-950">
                        {{ __('Create account') }}
                    </a>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-stone-400 transition duration-150 ease-in-out hover:bg-white/10 hover:text-white focus:bg-white/10 focus:outline-none focus:text-white">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
                {{ __('Home') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">
                {{ __('Events') }}
            </x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @endauth
        </div>

        <div class="border-t border-white/10 pt-4 pb-1">
            @auth
                <div class="flex items-center gap-3 px-4">
                    <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-amber-300/15 text-sm font-black text-amber-200">
                        @if (Auth::user()->avatarUrl())
                            <img src="{{ Auth::user()->avatarUrl() }}" alt="{{ Auth::user()->name }} avatar" class="h-full w-full object-cover">
                        @else
                            {{ Auth::user()->avatarInitials() }}
                        @endif
                    </div>
                    <div>
                        <div class="text-base font-medium text-stone-100">{{ Auth::user()->name }}</div>
                        <div class="text-sm font-medium text-stone-400">{{ Auth::user()->email }}</div>
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('login')">
                        {{ __('Log In') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('register')">
                        {{ __('Create account') }}
                    </x-responsive-nav-link>
                </div>
            @endauth
        </div>
    </div>
</nav>
