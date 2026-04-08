<?php

namespace App\Http\Controllers;

use App\Mail\OrganizationInvitationMail;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Support\ConsumesOrganizationInvitations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrganizationInvitationController extends Controller
{
    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->isManagerOf($organization), 403);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:1000'],
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
                'token' => Str::random(48),
                'accepted_at' => null,
                'opted_out_at' => null,
            ],
        );

        Mail::to($invitation->email)->send(new OrganizationInvitationMail($invitation));

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', 'Organization invitation sent.');
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = OrganizationInvitation::query()
            ->with('organization')
            ->where('token', $token)
            ->firstOrFail();

        if ($invitation->accepted_at) {
            return redirect()
                ->route('organizations.show', $invitation->organization)
                ->with('status', 'This invitation has already been accepted.');
        }

        $request->session()->put('organization_invitation_token', $invitation->token);
        $request->session()->put('invited_email', $invitation->email);
        $request->session()->put('invited_organization_name', $invitation->organization->name);

        if ($request->user()) {
            $organization = ConsumesOrganizationInvitations::consumeFromSession($request, $request->user());

            return redirect()->route('organizations.show', $organization ?? $invitation->organization);
        }

        return redirect()->route('register', [
            'email' => $invitation->email,
        ]);
    }
}
