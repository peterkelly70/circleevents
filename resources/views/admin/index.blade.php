<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-stone-100">
            {{ __('Admin Panel') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Tabs -->
            <div class="mb-6 flex gap-2 border-b border-stone-700">
                <a href="{{ route('admin.index', ['tab' => 'users']) }}" class="px-4 py-2 {{ $tab === 'users' ? 'border-b-2 border-amber-400 text-amber-400' : 'text-stone-400 hover:text-stone-200' }}">
                    Users
                </a>
                <a href="{{ route('admin.index', ['tab' => 'organizations']) }}" class="px-4 py-2 {{ $tab === 'organizations' ? 'border-b-2 border-amber-400 text-amber-400' : 'text-stone-400 hover:text-stone-200' }}">
                    Organizations
                </a>
                <a href="{{ route('admin.index', ['tab' => 'reports']) }}" class="px-4 py-2 {{ $tab === 'reports' ? 'border-b-2 border-amber-400 text-amber-400' : 'text-stone-400 hover:text-stone-200' }}">
                    Reports
                </a>
            </div>

            <!-- Search -->
            <form method="GET" action="{{ route('admin.index') }}" class="mb-6">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="Search {{ $tab }}..." 
                        class="flex-1 rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-stone-100 placeholder:text-stone-500"
                    >
                    <button type="submit" class="rounded-full bg-amber-400 px-6 py-2 text-sm font-semibold text-stone-900">
                        Search
                    </button>
                    @if($search)
                        <a href="{{ route('admin.index', ['tab' => $tab]) }}" class="rounded-full border border-stone-600 px-4 py-2 text-sm text-stone-400">
                            Clear
                        </a>
                    @endif
                </div>
            </form>

            <!-- Users Tab -->
            @if($tab === 'users')
                <div class="rounded-2xl border border-stone-700 bg-stone-800/50 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-stone-100">All Users ({{ $users->count() }})</h3>
                    </div>
                    @forelse($users as $user)
                        <div class="flex items-center justify-between py-3 border-b border-stone-700 last:border-0">
                            <div>
                                <div class="font-medium text-stone-100">{{ $user->name }}</div>
                                <div class="text-sm text-stone-400">{{ $user->email }}</div>
                                <div class="text-xs text-stone-500 mt-1">
                                    Status: {{ $user->registration_status }} 
                                    @if($user->is_admin) | Admin @endif
                                </div>
                            </div>
                            <div class="flex gap-2 flex-wrap">
                                @if(!$user->is_admin)
                                    <form method="POST" action="{{ route('admin.impersonate') }}">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                        <button class="rounded-full border border-amber-500 px-3 py-1 text-xs text-amber-400">Impersonate</button>
                                    </form>
                                @endif
                                @if($user->registration_status === 'pending')
                                    <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                                        @csrf
                                        <button class="rounded-full bg-emerald-500 px-3 py-1 text-xs text-white">Approve</button>
                                    </form>
                                @endif
                                @if($user->registration_status === 'active' && !$user->is_admin)
                                    <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                                        @csrf
                                        <button class="rounded-full border border-rose-500 px-3 py-1 text-xs text-rose-400">Suspend</button>
                                    </form>
                                @endif
                                @if($user->registration_status === 'suspended')
                                    <form method="POST" action="{{ route('admin.users.restore', $user) }}">
                                        @csrf
                                        <button class="rounded-full bg-emerald-500 px-3 py-1 text-xs text-white">Restore</button>
                                    </form>
                                @endif
                                @if(!$user->is_admin)
                                    <div class="flex items-center gap-1">
                                        <button type="button" onclick="this.previousElementSibling.value = Math.random().toString(36).slice(2, 10)" class="rounded-full border border-stone-600 px-2 py-1 text-xs text-stone-400">Generate</button>
                                        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="flex items-center gap-1">
                                            @csrf
                                            <input type="password" name="new_password" placeholder="New password" class="w-28 rounded bg-stone-700 px-2 py-1 text-xs text-stone-200" required minlength="8">
                                            <button class="rounded-full border border-blue-500 px-2 py-1 text-xs text-blue-400">Reset</button>
                                        </form>
                                    </div>
                                    <form method="POST" action="{{ route('admin.users.delete', $user) }}" onsubmit="return confirm('Delete this user?');">
                                        @csrf
                                        @method('delete')
                                        <button class="rounded-full border border-red-600 px-3 py-1 text-xs text-red-500">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-stone-400">No users found.</p>
                    @endforelse
                </div>
            @endif

            <!-- Organizations Tab -->
            @if($tab === 'organizations')
                <div class="rounded-2xl border border-stone-700 bg-stone-800/50 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-stone-100">All Organizations ({{ $organizations->count() }})</h3>
                    </div>
                    @forelse($organizations as $org)
                        <div class="py-3 border-b border-stone-700 last:border-0">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-stone-100">{{ $org->name }}</div>
                                    <div class="text-sm text-stone-400">{{ $org->slug }}</div>
                                    <div class="text-xs text-stone-500 mt-1">
                                        Owner: {{ $org->owner?->name ?? 'Unknown' }} | 
                                        Status: {{ $org->approval_status }}
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('organizations.show', $org) }}" class="rounded-full border border-stone-500 px-3 py-1 text-xs text-stone-300">View</a>
                                    <a href="{{ route('organizations.edit', $org) }}" class="rounded-full border border-amber-500 px-3 py-1 text-xs text-amber-400">Edit</a>
                                    @if($org->approval_status === 'pending')
                                        <form method="POST" action="{{ route('admin.organizations.approve', $org) }}">
                                            @csrf
                                            <button class="rounded-full bg-emerald-500 px-3 py-1 text-xs text-white">Approve</button>
                                        </form>
                                    @endif
                                    @if($org->approval_status === 'approved')
                                        <form method="POST" action="{{ route('admin.organizations.suspend', $org) }}">
                                            @csrf
                                            <button class="rounded-full border border-rose-500 px-3 py-1 text-xs text-rose-400">Suspend</button>
                                        </form>
                                    @endif
                                    @if($org->approval_status === 'suspended')
                                        <form method="POST" action="{{ route('admin.organizations.restore', $org) }}">
                                            @csrf
                                            <button class="rounded-full bg-emerald-500 px-3 py-1 text-xs text-white">Restore</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.organizations.delete', $org) }}" onsubmit="return confirm('Delete this organization?');">
                                        @csrf
                                        @method('delete')
                                        <button class="rounded-full border border-red-600 px-3 py-1 text-xs text-red-500">Delete</button>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Members Section -->
                            <div class="mt-3 pl-4 border-l-2 border-stone-600">
                                <div class="text-xs text-stone-400 mb-2">Members:</div>
                                <div class="flex flex-wrap gap-2 mb-3">
                                    @forelse($org->members as $member)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-stone-700 px-2 py-1 text-xs text-stone-300">
                                            {{ $member->name }}
                                            <span class="text-stone-500">({{ $member->pivot->role }})</span>
                                            @if($member->pivot->role !== 'owner')
                                            <form method="POST" action="{{ route('admin.organizations.members.remove', [$org->slug, $member->id]) }}">
                                                @csrf
                                                @method('delete')
                                                <button class="text-rose-400 hover:text-rose-200 ml-1">×</button>
                                            </form>
                                            @endif
                                        </span>
                                    @empty
                                        <span class="text-xs text-stone-500">No members</span>
                                    @endforelse
                                </div>
                                
                                <!-- Add Member Form -->
                                <form method="POST" action="{{ route('admin.organizations.members.add', $org->slug) }}" class="mt-3 flex flex-wrap items-center gap-3">
                                    @csrf
                                    <input type="hidden" name="organization_id" value="{{ $org->id }}">
                                    <select name="user_id" class="flex-1 min-w-[150px] rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-stone-100" required>
                                        <option value="">Select user...</option>
                                        @foreach($users as $user)
                                            @if(!$org->members->contains('id', $user->id))
                                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <select name="role" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-stone-100" required>
                                        <option value="follower">Follower</option>
                                        <option value="manager">Manager</option>
                                    </select>
                                    <button type="submit" class="rounded-full bg-emerald-500 hover:bg-emerald-400 px-5 py-2 text-sm font-semibold text-white transition">
                                        Add to org
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-stone-400">No organizations found.</p>
                    @endforelse
                </div>
            @endif

            <!-- Reports Tab -->
            @if($tab === 'reports')
                <div class="rounded-2xl border border-stone-700 bg-stone-800/50 p-6">
                    <h3 class="text-lg font-semibold text-stone-100 mb-4">All Reports ({{ $reports->count() }})</h3>
                    @forelse($reports as $report)
                        <div class="py-3 border-b border-stone-700 last:border-0">
                            <div class="flex justify-between">
                                <div>
                                    <div class="font-medium text-stone-100">{{ class_basename($report->reportable_type) }} Report #{{ $report->id }}</div>
                                    <div class="text-sm text-stone-400">{{ $report->reason }}</div>
                                    <div class="text-xs text-stone-500 mt-1">
                                        By: {{ $report->reporter?->email ?? 'Unknown' }} | 
                                        Status: {{ $report->status }}
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('admin.reports.update', $report) }}">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" class="rounded bg-stone-700 text-stone-200 text-xs px-2 py-1">
                                        <option value="open" {{ $report->status === 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="reviewing" {{ $report->status === 'reviewing' ? 'selected' : '' }}>Reviewing</option>
                                        <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                        <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-stone-400">No reports found.</p>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</x-app-layout>