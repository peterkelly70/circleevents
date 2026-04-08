<div style="font-family: Arial, sans-serif; color: #1c1917;">
    <p>Hello {{ $recipient->name }},</p>
    <p>{{ $messageRecord->organization->name }} sent a message to its members.</p>
    <h2>{{ $messageRecord->subject }}</h2>
    <p style="white-space: pre-line;">{{ $messageRecord->body }}</p>
    <p>
        View the organization page:
        <a href="{{ route('organizations.show', $messageRecord->organization) }}">{{ route('organizations.show', $messageRecord->organization) }}</a>
    </p>
</div>
