<div style="font-family: Arial, sans-serif; color: #1c1917;">
    <p>Hello {{ $recipient->name }},</p>
    <p>{{ $event->organization->name }} published a new event on CircleEvents.</p>
    <h2>{{ $event->title }}</h2>
    <p>{{ $event->summary }}</p>
    <p><strong>When:</strong> {{ $event->starts_at->format('l, d F Y g:i A') }} {{ $event->timezone }}</p>
    <p><strong>Where:</strong> {{ $event->venue_name }}</p>
    <p style="white-space: pre-line;">{{ $event->description }}</p>
    <p>
        View the event page:
        <a href="{{ route('events.show', $event) }}">{{ route('events.show', $event) }}</a>
    </p>
</div>
