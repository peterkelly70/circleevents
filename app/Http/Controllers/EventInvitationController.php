<?php

namespace App\Http\Controllers;

use App\Mail\EventInvitationMail;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Support\ConsumesEventInvitations;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EventInvitationController extends Controller
{
    public function store(Request $request, Event $event): RedirectResponse
    {
        abort_unless($request->user()->isManagerOf($event->organization), 403);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $invitation = EventInvitation::updateOrCreate(
            [
                'event_id' => $event->id,
                'email' => strtolower($validated['email']),
            ],
            [
                'invited_by_user_id' => $request->user()->id,
                'name' => $validated['name'] ?? null,
                'message' => $validated['message'] ?? null,
                'token' => Str::random(48),
                'accepted_at' => null,
            ],
        );

        Mail::to($invitation->email)->send(new EventInvitationMail($invitation));

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Invitation sent.');
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = EventInvitation::query()
            ->with('event')
            ->where('token', $token)
            ->firstOrFail();

        if ($invitation->accepted_at) {
            return redirect()
                ->route('events.show', $invitation->event)
                ->with('status', 'This invitation has already been accepted.');
        }

        $request->session()->put('event_invitation_token', $invitation->token);
        $request->session()->put('invited_email', $invitation->email);
        $request->session()->put('invited_event_title', $invitation->event->title);

        if ($request->user()) {
            $event = ConsumesEventInvitations::consumeFromSession($request, $request->user());

            return redirect()->route('events.show', $event ?? $invitation->event);
        }

        return redirect()->route('register', [
            'email' => $invitation->email,
        ]);
    }
}
