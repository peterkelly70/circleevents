<?php

namespace App\Support;

use App\Models\Organization;
use App\Models\OrganizationBlacklist;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConsumesOrganizationInvitations
{
    public static function consumeFromSession(Request $request, User $user): ?Organization
    {
        $token = $request->session()->pull('organization_invitation_token');

        if (! $token) {
            return null;
        }

        $invitation = OrganizationInvitation::query()
            ->with('organization')
            ->where('token', $token)
            ->whereNull('accepted_at')
            ->first();

        if (! $invitation) {
            return null;
        }

        if ($invitation->isExpired()) {
            $request->session()->flash('status', 'This organization invite has expired.');

            return null;
        }

        if ($invitation->isRevoked()) {
            $request->session()->flash('status', $invitation->revokedMessage());

            return null;
        }

        if ($invitation->isShareLink() && ! $invitation->hasRemainingUses()) {
            $request->session()->flash('status', 'This organization invite has reached its maximum uses.');

            return null;
        }

        if ($invitation->email !== null && strtolower($invitation->email) !== strtolower($user->email)) {
            $request->session()->flash('status', 'Invitation email did not match this account.');

            return null;
        }

        if (OrganizationBlacklist::where('organization_id', $invitation->organization_id)->where('user_id', $user->id)->exists()) {
            $request->session()->flash('status', 'You are blocked from this organization.');

            return null;
        }

        if (! $invitation->isShareLink()) {
            $invitation->update([
                'accepted_at' => now(),
            ]);
        }

        $existingMembership = DB::table('organization_user')
            ->where('organization_id', $invitation->organization_id)
            ->where('user_id', $user->id)
            ->exists();

        $newMembership = false;

        if (! $existingMembership) {
            $user->organizations()->attach($invitation->organization_id, [
                'role' => $invitation->role,
                'email_opt_out_token' => Str::random(48),
            ]);
            $newMembership = true;
        } else {
            $role = DB::table('organization_user')
                ->where('organization_id', $invitation->organization_id)
                ->where('user_id', $user->id)
                ->value('role');

            DB::table('organization_user')
                ->where('organization_id', $invitation->organization_id)
                ->where('user_id', $user->id)
                ->update([
                    'role' => $role === 'owner' ? 'owner' : $invitation->role,
                    'email_opt_out_at' => null,
                    'email_opt_out_token' => DB::raw("COALESCE(email_opt_out_token, '".Str::random(48)."')"),
                    'updated_at' => now(),
                ]);
        }

        if ($invitation->isShareLink() && $newMembership) {
            $invitation->increment('use_count');
        }

        InvitationAuditLogger::log($invitation, 'accepted', $request, $user, [
            'organization_id' => $invitation->organization_id,
            'new_membership' => $newMembership,
            'share_link' => $invitation->isShareLink(),
        ]);

        $request->session()->flash('status', 'Invitation accepted. You are now following this organization.');

        return $invitation->organization;
    }
}
