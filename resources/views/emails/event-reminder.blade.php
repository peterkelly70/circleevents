<div style="font-family: Arial, sans-serif; color: #1c1917;">
    <p>Hello {{ $recipient->name }},</p>
    <p>This is a reminder that you said you are going to:</p>
    <h2>{{ $event->title }}</h2>
    <p><strong>When:</strong> {{ $event->starts_at->format('l, d F Y g:i A') }} {{ $event->timezone }}</p>
    <p><strong>Where:</strong> {{ $event->venue_name }}</p>
    <p>{{ $event->summary }}</p>
    <p>
        View the event page:
        <a href="{{ route('events.show', $event) }}">{{ route('events.show', $event) }}</a>
    </p>
</div>
