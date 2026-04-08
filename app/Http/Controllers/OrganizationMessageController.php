<?php

namespace App\Http\Controllers;

use App\Mail\OrganizationAnnouncementMail;
use App\Models\Organization;
use App\Models\OrganizationMessage;
use App\Support\ImageUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrganizationMessageController extends Controller
{
    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->isManagerOf($organization), 403);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'max:12288'],
        ]);

        $message = OrganizationMessage::create([
            'organization_id' => $organization->id,
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'image_path' => $request->file('image')
                ? ImageUploads::storeResizedPublicImage($request->file('image'), 'organization-message-images', 1600, 1600)
                : null,
            'emailed_at' => now(),
        ]);

        $message->loadMissing('organization');
        $organization->loadMissing('members');

        foreach ($organization->members->unique('email') as $member) {
            $membership = DB::table('organization_user')
                ->where('organization_id', $organization->id)
                ->where('user_id', $member->id)
                ->first();

            if (! $membership || $membership->email_opt_out_at) {
                continue;
            }

            if (! $membership->email_opt_out_token) {
                $token = Str::random(48);

                DB::table('organization_user')
                    ->where('organization_id', $organization->id)
                    ->where('user_id', $member->id)
                    ->update([
                        'email_opt_out_token' => $token,
                        'updated_at' => now(),
                    ]);

                $membership->email_opt_out_token = $token;
            }

            Mail::to($member->email)->send(new OrganizationAnnouncementMail($message, $member, $membership->email_opt_out_token));
        }

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', 'Message saved and emailed to organization members.');
    }
}
