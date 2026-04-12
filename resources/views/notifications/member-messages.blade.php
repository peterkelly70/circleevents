<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-amber-400/70">Notifications</p>
                <h1 class="text-3xl font-black text-stone-100">Member Messages</h1>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="space-y-4">
            @forelse($messages as $message)
                <div class="rounded-[2rem] border p-6 shadow-sm ring-1 {{ $message->read_at ? 'border-white/10 bg-stone-900/50' : 'border-amber-500/30 bg-amber-500/5' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-stone-700 text-sm font-bold text-stone-300">
                                {{ str($message->fromUser->name)->substr(0, 2)->upper() }}
                            </div>
                            <div>
                                <p class="font-semibold text-stone-100">{{ $message->fromUser->name }}</p>
                                <p class="text-sm text-stone-400">{{ $message->organization->name }}</p>
                            </div>
                        </div>
                        <p class="text-xs uppercase tracking-[0.2em] text-stone-500">{{ $message->created_at->diffForHumans() }}</p>
                    </div>

                    <h3 class="mt-4 text-lg font-semibold text-stone-100">{{ $message->subject }}</h3>
                    <div class="mt-2 text-stone-300">{!! \App\Support\Bbcode::render($message->body) !!}</div>

                    <div class="mt-4 flex gap-3">
                        @if(!$message->read_at)
                            <form method="POST" action="{{ route('notifications.member-messages.read', $message) }}">
                                @csrf
                                <button class="rounded-full border border-amber-500 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-amber-400">Mark as read</button>
                            </form>
                        @endif
                        <a href="{{ route('organizations.show', $message->organization) }}" class="rounded-full border border-white/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-stone-400">View organization</a>
                    </div>
                </div>
            @empty
                <div class="rounded-[2rem] border border-white/10 bg-stone-900/50 p-8 text-center">
                    <p class="text-stone-400">No messages from organization managers.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $messages->links() }}
        </div>
    </div>
</x-app-layout>