<x-app-layout>
    @php
        $themeProseClass = $theme['mode'] === 'light' ? 'prose' : 'prose prose-invert';
    @endphp
    <x-slot name="header">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] {{ $theme['eyebrow'] }}">Manage Members</p>
                <h1 class="text-3xl font-black {{ $theme['header_heading'] }}">{{ $organization->name }}</h1>
            </div>
            <a href="{{ route('organizations.show', $organization) }}" class="rounded-full px-5 py-3 text-sm font-semibold {{ $theme['header_button'] }}">Back to organization</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8 {{ $theme['mode'] === 'light' ? 'text-stone-900' : 'text-stone-100' }} {{ $theme['page_backdrop'] }} {{ $theme['font_body'] }}">

        <div x-data="{ selectedUsers: [], searchQuery: '', showBanned: false }" class="space-y-8">
            <div class="rounded-[2rem] border p-6 {{ $theme['surface'] }}">
                <div class="flex items-center justify-between mb-4">
                    <input type="text" x-model="searchQuery" placeholder="Search members..." class="w-full rounded-2xl border px-4 py-3 {{ $theme['input'] }} max-w-md">
                    <label class="flex items-center gap-2 text-sm {{ $theme['body'] }} cursor-pointer">
                        <input type="checkbox" x-model="showBanned" class="rounded border {{ $theme['checkbox'] }}">
                        <span class="{{ $theme['link'] }}">Show banned</span>
                    </label>
                </div>

                <div class="mb-4 flex flex-wrap items-center gap-3 rounded-2xl {{ $theme['panel'] }} p-3">
                    <span class="text-sm font-semibold {{ $theme['heading'] }}" x-text="selectedUsers.length > 0 ? selectedUsers.length + ' selected' : 'Select members to manage'"></span>
                    <button type="button" @click="selectedUsers = []" x-show="selectedUsers.length > 0" class="text-xs {{ $theme['link'] }} hover:{{ $theme['heading'] }} underline">Clear</button>
                    <div class="flex gap-2 border-l border-white/20 pl-2">
                        <form method="POST" action="{{ route('organizations.messages.send-member', $organization) }}" class="inline">
                            @csrf
                            <template x-for="id in selectedUsers" :key="id">
                                <input type="hidden" name="user_ids[]" :value="id">
                            </template>
                            <button type="submit" :disabled="selectedUsers.length === 0" title="Send a message to selected members" :class="selectedUsers.length === 0 ? 'opacity-40 cursor-not-allowed' : ''" class="rounded-full border px-3 py-1 text-xs {{ $theme['secondary_button'] }}">Message</button>
                        </form>
                        @if(auth()->user()->isOwnerOf($organization))
                        <form method="POST" action="{{ route('organizations.members.promote', $organization) }}" class="inline">
                            @csrf
                            <template x-for="id in selectedUsers" :key="id">
                                <input type="hidden" name="user_ids[]" :value="id">
                            </template>
                            <button type="submit" :disabled="selectedUsers.length === 0" title="Promote selected followers to manager" :class="selectedUsers.length === 0 ? 'opacity-40 cursor-not-allowed' : ''" class="rounded-full border px-3 py-1 text-xs {{ $theme['secondary_button'] }}">Promote</button>
                        </form>
                        <form method="POST" action="{{ route('organizations.members.demote', $organization) }}" class="inline">
                            @csrf
                            <template x-for="id in selectedUsers" :key="id">
                                <input type="hidden" name="user_ids[]" :value="id">
                            </template>
                            <button type="submit" :disabled="selectedUsers.length === 0" title="Demote selected managers to follower" :class="selectedUsers.length === 0 ? 'opacity-40 cursor-not-allowed' : ''" class="rounded-full border px-3 py-1 text-xs {{ $theme['secondary_button'] }}">Demote</button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('organizations.members.ban', $organization) }}" class="inline">
                            @csrf
                            <template x-for="id in selectedUsers" :key="id">
                                <input type="hidden" name="user_ids[]" :value="id">
                            </template>
                            <button type="submit" :disabled="selectedUsers.length === 0" title="Ban selected members (they won't be able to see, login or message the organization)" :class="selectedUsers.length === 0 ? 'opacity-40 cursor-not-allowed' : ''" class="rounded-full border px-3 py-1 text-xs {{ $theme['danger_button'] }}">Ban</button>
                        </form>
                        <form method="POST" action="{{ route('organizations.members.unban', $organization) }}" class="inline" x-show="showBanned">
                            @csrf
                            <template x-for="id in selectedUsers" :key="id">
                                <input type="hidden" name="user_ids[]" :value="id">
                            </template>
                            <button type="submit" :disabled="selectedUsers.length === 0" title="Unban selected members" :class="selectedUsers.length === 0 ? 'opacity-40 cursor-not-allowed' : ''" class="rounded-full border px-3 py-1 text-xs {{ $theme['secondary_button'] }}">Unban</button>
                        </form>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }} mb-3">Managers ({{ $managerMembers->count() }})</h3>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            @forelse($managerMembers as $member)
                                <div class="flex items-center justify-between rounded-2xl border p-4 {{ $theme['panel'] }}">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" x-model="selectedUsers" value="{{ $member->id }}" class="rounded border {{ $theme['checkbox'] }}">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $theme['logo_shell'] }} text-sm font-bold">
                                            {{ str($member->name)->substr(0, 2)->upper() }}
                                        </div>
                                        <div>
                                            <div class="font-semibold {{ $theme['heading'] }}">{{ $member->name }}</div>
                                            <div class="text-sm {{ $theme['meta'] }}">{{ $member->email }}</div>
                                        </div>
                                    </div>
                                    <span class="rounded-full border border-white/10 bg-white/5 px-2 py-1 text-xs {{ $theme['muted'] }}">{{ $member->pivot->role === 'owner' ? 'Owner' : 'Manager' }}</span>
                                </div>
                            @empty
                                <p class="text-sm {{ $theme['meta'] }}">No managers found.</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }} mb-3">Followers ({{ $followerMembers->count() }})</h3>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            @forelse($followerMembers as $member)
                                <div class="flex items-center justify-between rounded-2xl border p-4 {{ $theme['panel'] }}">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" x-model="selectedUsers" value="{{ $member->id }}" class="rounded border {{ $theme['checkbox'] }}">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $theme['logo_shell'] }} text-sm font-bold">
                                            {{ str($member->name)->substr(0, 2)->upper() }}
                                        </div>
                                        <div>
                                            <div class="font-semibold {{ $theme['heading'] }}">{{ $member->name }}</div>
                                            <div class="text-sm {{ $theme['meta'] }}">{{ $member->email }}</div>
                                        </div>
                                    </div>
                                    <span class="rounded-full border border-white/10 bg-white/5 px-2 py-1 text-xs {{ $theme['muted'] }}">Follower</span>
                                </div>
                            @empty
                                <p class="text-sm {{ $theme['meta'] }}">No followers yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div x-show="showBanned">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.2em] {{ $theme['muted'] }} mb-3">Banned ({{ $bannedUsers->count() }})</h3>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            @forelse($bannedUsers as $user)
                                <div class="flex items-center justify-between rounded-2xl border border-red-500/30 bg-red-500/5 p-4">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" x-model="selectedUsers" value="{{ $user->id }}" class="rounded border {{ $theme['checkbox'] }}">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $theme['logo_shell'] }} text-sm font-bold">
                                            {{ str($user->name)->substr(0, 2)->upper() }}
                                        </div>
                                        <div>
                                            <div class="font-semibold {{ $theme['heading'] }}">{{ $user->name }}</div>
                                            <div class="text-sm {{ $theme['meta'] }}">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                    <span class="rounded-full border border-rose-500/30 bg-rose-500/10 px-2 py-1 text-xs {{ $theme['danger_button'] }}">Banned</span>
                                </div>
                            @empty
                                <p class="text-sm {{ $theme['meta'] }}">No banned users.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>