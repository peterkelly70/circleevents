<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganizationMemberController extends Controller
{
    public function follow(Request $request, Organization $organization): RedirectResponse
    {
        if (! $request->user()->organizations()->where('organization_id', $organization->id)->exists()) {
            $request->user()->organizations()->attach($organization->id, ['role' => 'follower']);
            $request->user()->organizations()->updateExistingPivot($organization->id, [
                'email_opt_out_token' => Str::random(48),
            ]);
        }

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', 'You are now following this organization.');
    }

    public function promote(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->isOwnerOf($organization), 403);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $organization->members()->updateExistingPivot($validated['user_id'], [
            'role' => 'manager',
        ]);

        return back()->with('status', 'Manager added.');
    }
}
