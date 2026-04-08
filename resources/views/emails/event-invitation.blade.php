<!DOCTYPE html>
<html lang="en">
    <body style="font-family: Arial, sans-serif; line-height: 1.5; color: #1c1917;">
        <h1 style="margin-bottom: 12px;">You are invited to {{ $invitation->event->title }}</h1>
        <p>{{ $invitation->invitedBy->name }} invited you to an event on CircleEvents.</p>
        @if ($invitation->message)
            <p><strong>Message:</strong> {{ $invitation->message }}</p>
        @endif
        <p><strong>When:</strong> {{ $invitation->event->starts_at->format('l, d F Y g:i A') }} {{ $invitation->event->timezone }}</p>
        <p><strong>Where:</strong> {{ $invitation->event->venue_name }}</p>
        <p>
            <a href="{{ route('event-invitations.accept', $invitation->token) }}" style="display:inline-block;padding:12px 18px;background:#fbbf24;color:#1c1917;text-decoration:none;border-radius:999px;font-weight:bold;">
                Accept invitation
            </a>
        </p>
        <p>If you do not already have an account, the link will take you through sign up first.</p>
    </body>
</html>
