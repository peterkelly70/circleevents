<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrganizationMemberController extends Controller
{
    public function follow(Request $request, Organization $organization): RedirectResponse
    {
        if (! $request->user()->organizations()->where('organization_id', $organization->id)->exists()) {
            $request->user()->organizations()->attach($organization->id, ['role' => 'follower']);
        }

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', 'You are now following this organization.');
    }
}
