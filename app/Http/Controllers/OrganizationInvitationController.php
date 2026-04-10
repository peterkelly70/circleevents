<?php

namespace App\Http\Controllers;

use App\Mail\OrganizationInvitationMail;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Support\ConsumesOrganizationInvitations;
use App\Support\InvitationAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrganizationInvitationController extends Controller
{
    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->isManagerOf($organization), 403);

        $validated = $request->validate([
            'delivery' => ['required', Rule::in(['email', 'share'])],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:1000'],
            'role' => ['required', Rule::in(['follower', 'manager'])],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'max_uses' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ]);

        if ($validated['delivery'] === 'email') {
            $request->validate([
                'email' => ['required', 'email', 'max:255'],
            ]);

            $invitation = OrganizationInvitation::updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'email' => strtolower($validated['email']),
                ],
                [
                    'invited_by_user_id' => $request->user()->id,
                    'name' => $validated['name'] ?? null,
                    'message' => $validated['message'] ?? null,
                    'role' => $validated['role'],
                    'token' => Str::random(48),
                    'share_code' => null,
                    'expires_at' => null,
                    'accepted_at' => null,
                    'opted_out_at' => null,
                ],
            );

            Mail::to($invitation->email)->send(new OrganizationInvitationMail($invitation));
            InvitationAuditLogger::log($invitation, 'created-email', $request, $request->user(), [
                'organization_id' => $organization->id,
                'role' => $invitation->role,
            ]);

            return redirect()
                ->route('organizations.show', $organization)
                ->with('status', 'Organization invitation sent.');
        }

        $invitation = OrganizationInvitation::create([
            'organization_id' => $organization->id,
            'invited_by_user_id' => $request->user()->id,
            'name' => $validated['name'] ?? 'Shared invite',
            'email' => null,
            'message' => $validated['message'] ?? null,
            'role' => 'follower',
            'token' => Str::random(48),
            'share_code' => $this->generateShareCode(),
            'expires_at' => $validated['expires_at'] ?? null,
            'max_uses' => $validated['max_uses'] ?? null,
            'accepted_at' => null,
            'opted_out_at' => null,
        ]);

        InvitationAuditLogger::log($invitation, 'created-share', $request, $request->user(), [
            'organization_id' => $organization->id,
            'max_uses' => $invitation->max_uses,
            'expires_at' => $invitation->expires_at?->toIso8601String(),
        ]);

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', 'Share invite created: '.$invitation->share_code);
    }

    public function revoke(Request $request, Organization $organization, OrganizationInvitation $invitation): RedirectResponse
    {
        abort_unless($request->user()->isManagerOf($organization), 403);
        abort_unless($invitation->organization_id === $organization->id, 404);

        $validated = $request->validate([
            'revoked_reason' => [$invitation->isShareLink() ? 'nullable' : 'required', 'string', 'max:500'],
        ]);

        $invitation->update([
            'revoked_at' => now(),
            'revoked_by_user_id' => $request->user()->id,
            'revoked_reason' => $validated['revoked_reason'] ?? null,
        ]);

        InvitationAuditLogger::log($invitation, 'revoked', $request, $request->user(), [
            'organization_id' => $organization->id,
            'reason' => $validated['revoked_reason'] ?? null,
        ]);

        $status = $invitation->isShareLink()
            ? 'Invite code revoked.'
            : 'Invitation canceled.';

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', $status);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = OrganizationInvitation::query()
            ->with('organization')
            ->where('token', $token)
            ->firstOrFail();

        return $this->completeAcceptance($request, $invitation);
    }

    public function acceptCode(Request $request, string $code): RedirectResponse
    {
        $invitation = OrganizationInvitation::query()
            ->with('organization')
            ->where('share_code', strtoupper($code))
            ->firstOrFail();

        return $this->completeAcceptance($request, $invitation);
    }

    protected function completeAcceptance(Request $request, OrganizationInvitation $invitation): RedirectResponse
    {
        if ($invitation->isExpired()) {
            InvitationAuditLogger::log($invitation, 'blocked-expired', $request, $request->user(), [
                'organization_id' => $invitation->organization_id,
            ]);

            return redirect()
                ->route('organizations.show', $invitation->organization)
                ->with('status', 'This invitation link has expired.');
        }

        if ($invitation->isRevoked()) {
            InvitationAuditLogger::log($invitation, 'blocked-revoked', $request, $request->user(), [
                'organization_id' => $invitation->organization_id,
                'reason' => $invitation->revoked_reason,
            ]);

            return redirect()
                ->route('organizations.show', $invitation->organization)
                ->with('status', $invitation->revokedMessage());
        }

        if ($invitation->isShareLink() && ! $invitation->hasRemainingUses()) {
            InvitationAuditLogger::log($invitation, 'blocked-max-uses', $request, $request->user(), [
                'organization_id' => $invitation->organization_id,
                'use_count' => $invitation->use_count,
                'max_uses' => $invitation->max_uses,
            ]);

            return redirect()
                ->route('organizations.show', $invitation->organization)
                ->with('status', 'This invitation link has reached its maximum uses.');
        }

        if (! $invitation->isShareLink() && $invitation->accepted_at) {
            return redirect()
                ->route('organizations.show', $invitation->organization)
                ->with('status', 'This invitation has already been accepted.');
        }

        $request->session()->put('organization_invitation_token', $invitation->token);
        if ($invitation->email) {
            $request->session()->put('invited_email', $invitation->email);
        } else {
            $request->session()->forget('invited_email');
        }
        $request->session()->put('invited_organization_name', $invitation->organization->name);

        if ($request->user()) {
            $organization = ConsumesOrganizationInvitations::consumeFromSession($request, $request->user());

            return redirect()->route('organizations.show', $organization ?? $invitation->organization);
        }

        return $invitation->email
            ? redirect()->route('register', ['email' => $invitation->email])
            : redirect()->route('register');
    }

    protected function generateShareCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (OrganizationInvitation::query()->where('share_code', $code)->exists());

        return $code;
    }
}
