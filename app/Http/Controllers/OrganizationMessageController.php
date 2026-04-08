<?php

namespace App\Http\Controllers;

use App\Mail\OrganizationAnnouncementMail;
use App\Models\Organization;
use App\Models\OrganizationMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrganizationMessageController extends Controller
{
    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->isManagerOf($organization), 403);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $message = OrganizationMessage::create([
            'organization_id' => $organization->id,
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'emailed_at' => now(),
        ]);

        $message->loadMissing('organization');
        $organization->loadMissing('members');

        foreach ($organization->members->unique('email') as $member) {
            Mail::to($member->email)->send(new OrganizationAnnouncementMail($message, $member));
        }

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', 'Message saved and emailed to organization members.');
    }
}
