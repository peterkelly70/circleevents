<?php

namespace App\Http\Controllers;

use App\Mail\EventInvitationMail;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Support\ConsumesEventInvitations;
use App\Support\InvitationAuditLogger;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EventInvitationController extends Controller
{
    public function store(Request $request, Event $event): RedirectResponse
    {
        abort_unless($request->user()->isManagerOf($event->organization), 403);

        $validated = $request->validate([
            'delivery' => ['required', Rule::in(['email', 'share'])],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:1000'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'max_uses' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ]);

        if ($validated['delivery'] === 'email') {
            $request->validate([
                'email' => ['required', 'email', 'max:255'],
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
                    'share_code' => null,
                    'expires_at' => null,
                    'accepted_at' => null,
                ],
            );

            Mail::to($invitation->email)->send(new EventInvitationMail($invitation));
            InvitationAuditLogger::log($invitation, 'created-email', $request, $request->user(), [
                'event_id' => $event->id,
            ]);

            return redirect()
                ->route('events.show', $event)
                ->with('status', 'Invitation sent.');
        }

        $invitation = EventInvitation::create([
            'event_id' => $event->id,
            'invited_by_user_id' => $request->user()->id,
            'name' => $validated['name'] ?? 'Shared invite',
            'email' => null,
            'message' => $validated['message'] ?? null,
            'token' => Str::random(48),
            'share_code' => $this->generateShareCode(),
            'expires_at' => $validated['expires_at'] ?? null,
            'max_uses' => $validated['max_uses'] ?? null,
            'accepted_at' => null,
        ]);

        InvitationAuditLogger::log($invitation, 'created-share', $request, $request->user(), [
            'event_id' => $event->id,
            'max_uses' => $invitation->max_uses,
            'expires_at' => $invitation->expires_at?->toIso8601String(),
        ]);

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Share invite created: '.$invitation->share_code);
    }

    public function revoke(Request $request, Event $event, EventInvitation $invitation): RedirectResponse
    {
        abort_unless($request->user()->isManagerOf($event->organization), 403);
        abort_unless($invitation->event_id === $event->id, 404);

        $invitation->update([
            'revoked_at' => now(),
            'revoked_by_user_id' => $request->user()->id,
        ]);

        InvitationAuditLogger::log($invitation, 'revoked', $request, $request->user(), [
            'event_id' => $event->id,
        ]);

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Invite code revoked.');
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = EventInvitation::query()
            ->with('event')
            ->where('token', $token)
            ->firstOrFail();

        return $this->completeAcceptance($request, $invitation);
    }

    public function acceptCode(Request $request, string $code): RedirectResponse
    {
        $invitation = EventInvitation::query()
            ->with('event')
            ->where('share_code', strtoupper($code))
            ->firstOrFail();

        return $this->completeAcceptance($request, $invitation);
    }

    protected function completeAcceptance(Request $request, EventInvitation $invitation): RedirectResponse
    {
        if ($invitation->isExpired()) {
            InvitationAuditLogger::log($invitation, 'blocked-expired', $request, $request->user(), [
                'event_id' => $invitation->event_id,
            ]);

            return redirect()
                ->route('events.show', $invitation->event)
                ->with('status', 'This invitation link has expired.');
        }

        if ($invitation->isRevoked()) {
            InvitationAuditLogger::log($invitation, 'blocked-revoked', $request, $request->user(), [
                'event_id' => $invitation->event_id,
            ]);

            return redirect()
                ->route('events.show', $invitation->event)
                ->with('status', 'This invitation link is no longer active.');
        }

        if ($invitation->isShareLink() && ! $invitation->hasRemainingUses()) {
            InvitationAuditLogger::log($invitation, 'blocked-max-uses', $request, $request->user(), [
                'event_id' => $invitation->event_id,
                'use_count' => $invitation->use_count,
                'max_uses' => $invitation->max_uses,
            ]);

            return redirect()
                ->route('events.show', $invitation->event)
                ->with('status', 'This invitation link has reached its maximum uses.');
        }

        if (! $invitation->isShareLink() && $invitation->accepted_at) {
            return redirect()
                ->route('events.show', $invitation->event)
                ->with('status', 'This invitation has already been accepted.');
        }

        $request->session()->put('event_invitation_token', $invitation->token);
        if ($invitation->email) {
            $request->session()->put('invited_email', $invitation->email);
        } else {
            $request->session()->forget('invited_email');
        }
        $request->session()->put('invited_event_title', $invitation->event->title);

        if ($request->user()) {
            $event = ConsumesEventInvitations::consumeFromSession($request, $request->user());

            return redirect()->route('events.show', $event ?? $invitation->event);
        }

        return $invitation->email
            ? redirect()->route('register', ['email' => $invitation->email])
            : redirect()->route('register');
    }

    protected function generateShareCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (EventInvitation::query()->where('share_code', $code)->exists());

        return $code;
    }
}
