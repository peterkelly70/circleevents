<div style="font-family: Arial, sans-serif; color: #1c1917;">
    <p>Hello{{ $invitation->name ? ' '.$invitation->name : '' }},</p>

    <p>You have been invited to join <strong>{{ $invitation->organization->name }}</strong> on CircleEvents.</p>

    @if ($invitation->message)
        <p style="white-space: pre-line;">{{ $invitation->message }}</p>
    @endif

    <p>
        <a href="{{ route('organizations.invitations.accept', $invitation->token) }}">Accept the invitation</a>
    </p>
</div>
