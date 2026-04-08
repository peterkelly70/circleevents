<x-app-layout>
    <x-slot name="header">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-emerald-300">Event</p>
                <h1 class="text-3xl font-black text-stone-100">Edit {{ $event->title }}</h1>
            </div>
            <a href="{{ route('events.show', $event) }}" class="rounded-full border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold text-stone-200">Back</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        @php
            $timeOptions = collect(range(0, 47))->map(function (int $slot) {
                $hour = intdiv($slot, 2);
                $minute = $slot % 2 === 0 ? '00' : '30';
                $value = sprintf('%02d:%s', $hour, $minute);
                $label = \Carbon\CarbonImmutable::createFromTime($hour, (int) $minute)->format('g:i A');

                return compact('value', 'label');
            });
        @endphp

        <div class="rounded-[2rem] border border-white/10 bg-stone-900/70 p-8 shadow-sm ring-1 ring-white/10">
            <form method="POST" action="{{ route('events.update', $event) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PATCH')

                <input type="hidden" name="organization_id" value="{{ $event->organization_id }}">
                <input type="hidden" name="google_place_id" data-event-place-id value="{{ old('google_place_id', $event->google_place_id) }}">
                <input type="hidden" name="latitude" data-event-latitude value="{{ old('latitude', $event->latitude) }}">
                <input type="hidden" name="longitude" data-event-longitude value="{{ old('longitude', $event->longitude) }}">

                <div>
                    <label class="text-sm font-medium text-stone-300" for="title">Title</label>
                    <input id="title" name="title" value="{{ old('title', $event->title) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                </div>

                <div>
                    <label class="text-sm font-medium text-stone-300" for="summary">Summary</label>
                    <input id="summary" name="summary" value="{{ old('summary', $event->summary) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                </div>

                <div>
                    <label class="text-sm font-medium text-stone-300" for="description">Description</label>
                    <textarea id="description" name="description" rows="6" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">{{ old('description', $event->description) }}</textarea>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="venue_name">Venue</label>
                        <input id="venue_name" data-event-venue-name name="venue_name" value="{{ old('venue_name', $event->venue_name) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="venue_address">Address</label>
                        <input id="venue_address" data-event-venue-address name="venue_address" value="{{ old('venue_address', $event->venue_address) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-stone-300">Search place with Google Maps</label>
                    <div data-google-place-widget class="mt-2 rounded-2xl border border-white/10 bg-white/5 px-3 py-2"></div>
                </div>

                <div class="grid gap-5 md:grid-cols-4">
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="start_date">Start date</label>
                        <input id="start_date" type="date" name="start_date" value="{{ old('start_date', $event->starts_at->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="start_time">Start time</label>
                        <select id="start_time" name="start_time" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                            @foreach ($timeOptions as $option)
                                <option value="{{ $option['value'] }}" @selected(old('start_time', $event->starts_at->format('H:i')) === $option['value'])>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="end_date">End date</label>
                        <input id="end_date" type="date" name="end_date" value="{{ old('end_date', $event->ends_at->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="end_time">End time</label>
                        <select id="end_time" name="end_time" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                            @foreach ($timeOptions as $option)
                                <option value="{{ $option['value'] }}" @selected(old('end_time', $event->ends_at->format('H:i')) === $option['value'])>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-3">
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="city">City</label>
                        <input id="city" data-event-city name="city" value="{{ old('city', $event->city) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="timezone">Timezone</label>
                        <input id="timezone" name="timezone" value="{{ old('timezone', $event->timezone) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="capacity">Capacity</label>
                        <input id="capacity" name="capacity" type="number" min="1" value="{{ old('capacity', $event->capacity) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="repeat_frequency">Repeats</label>
                        <select id="repeat_frequency" name="repeat_frequency" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                            <option value="none" @selected(old('repeat_frequency', $event->repeat_frequency ?? 'none') === 'none')>Does not repeat</option>
                            <option value="daily" @selected(old('repeat_frequency', $event->repeat_frequency) === 'daily')>Daily</option>
                            <option value="weekly" @selected(old('repeat_frequency', $event->repeat_frequency) === 'weekly')>Weekly</option>
                            <option value="monthly" @selected(old('repeat_frequency', $event->repeat_frequency) === 'monthly')>Monthly</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-stone-300">Repeat until</label>
                        <div class="mt-2 grid grid-cols-2 gap-3">
                            <input type="date" name="repeat_until_date" value="{{ old('repeat_until_date', $event->repeat_until?->format('Y-m-d')) }}" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                            <select name="repeat_until_time" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                                <option value="">Time</option>
                                @foreach ($timeOptions as $option)
                                    <option value="{{ $option['value'] }}" @selected(old('repeat_until_time', $event->repeat_until?->format('H:i')) === $option['value'])>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="image">Event image</label>
                        @if ($event->image_path)
                            <div class="mt-3 overflow-hidden rounded-[1.5rem] border border-white/10">
                                <img src="{{ $event->imageUrl() }}" alt="{{ $event->title }} image" class="h-32 w-full object-cover">
                            </div>
                        @endif
                        <input id="image" name="image" type="file" accept="image/*" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-stone-300" for="visibility">Visibility</label>
                        <select id="visibility" name="visibility" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-stone-100">
                            <option value="public" @selected(old('visibility', $event->visibility) === 'public')>Public</option>
                            <option value="private" @selected(old('visibility', $event->visibility) === 'private')>Private</option>
                            <option value="unlisted" @selected(old('visibility', $event->visibility) === 'unlisted')>Unlisted</option>
                        </select>
                    </div>
                </div>

                @if ($event->recurrence_group)
                    <div class="rounded-2xl border border-amber-300/20 bg-amber-300/10 px-4 py-3 text-sm text-amber-100">
                        This event belongs to a recurring series. Editing here updates only this occurrence.
                    </div>
                @endif

                <div class="flex gap-3 pt-4">
                    <button class="rounded-full bg-amber-300 px-6 py-3 text-sm font-semibold text-stone-950">Save changes</button>
                    <a href="{{ route('events.show', $event) }}" class="rounded-full border border-white/10 bg-white/5 px-6 py-3 text-sm font-semibold text-stone-200">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
