<?php

namespace App\Support;

use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\EventRsvp;
use App\Models\User;
use Illuminate\Http\Request;

class ConsumesEventInvitations
{
    public static function consumeFromSession(Request $request, User $user): ?Event
    {
        $token = $request->session()->pull('event_invitation_token');

        if (! $token) {
            return null;
        }

        $invitation = EventInvitation::query()
            ->with('event')
            ->where('token', $token)
            ->whereNull('accepted_at')
            ->first();

        if (! $invitation) {
            return null;
        }

        if ($invitation->isExpired()) {
            $request->session()->flash('status', 'This event invite has expired.');

            return null;
        }

        if ($invitation->email !== null && strtolower($invitation->email) !== strtolower($user->email)) {
            $request->session()->flash('status', 'Invitation email did not match this account.');
            return null;
        }

        if (! $invitation->isShareLink()) {
            $invitation->update([
                'accepted_at' => now(),
            ]);
        }

        EventRsvp::firstOrCreate(
            [
                'event_id' => $invitation->event_id,
                'user_id' => $user->id,
            ],
            [
                'status' => 'interested',
            ],
        );

        $request->session()->flash('status', 'Invitation accepted. You have been added to the event as interested.');

        return $invitation->event;
    }
}
