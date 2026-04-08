<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrganizationPostController extends Controller
{
    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->isMemberOf($organization), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        OrganizationPost::create([
            'organization_id' => $organization->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', 'Post published to the organization page.');
    }
}
