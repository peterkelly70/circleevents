<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class OrganizationEmailPreferenceController extends Controller
{
    public function optOut(Organization $organization, string $token): RedirectResponse
    {
        $userIds = DB::table('organization_user')
            ->where('organization_id', $organization->id)
            ->where('email_opt_out_token', $token)
            ->pluck('user_id');

        $updated = DB::table('organization_user')
            ->where('organization_id', $organization->id)
            ->where('email_opt_out_token', $token)
            ->update([
                'email_opt_out_at' => now(),
                'updated_at' => now(),
            ]);

        abort_unless($updated > 0, 404);

        foreach ($userIds as $userId) {
            $user = User::find($userId);

            if ($user) {
                $organization->unsubscribeMemberFromDefaultMailingList($user);
            }
        }

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', 'You have opted out of future email messages from this organization.');
    }
}
