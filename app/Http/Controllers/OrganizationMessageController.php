<?php

namespace App\Http\Controllers;

use App\Mail\OrganizationAnnouncementMail;
use App\Models\Organization;
use App\Models\OrganizationMessage;
use App\Support\DiscordOrganizationMessagePublisher;
use App\Support\FacebookOrganizationMessagePublisher;
use App\Support\ImageUploads;
use App\Support\UploadLimits;
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

        $maxUploadKilobytes = UploadLimits::maxUploadKilobytes();
        $maxUploadLabel = UploadLimits::maxUploadLabel();

        $validated = $request->validateWithBag('organizationMessage', [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.$maxUploadKilobytes],
            'post_to_discord' => ['nullable', 'boolean'],
            'post_to_facebook' => ['nullable', 'boolean'],
        ], [
            'image.uploaded' => 'The image did not upload. Use a JPEG, PNG, or WebP image under '.$maxUploadLabel.'.',
            'image.image' => 'The attachment must be a JPEG, PNG, or WebP image.',
            'image.mimes' => 'The attachment must be a JPEG, PNG, or WebP image.',
            'image.max' => 'The image must be '.$maxUploadLabel.' or smaller. CircleEvents will resize it after upload.',
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

        $discordPosted = false;
        $facebookPosted = false;

        if ($request->boolean('post_to_discord') || $organization->auto_post_discord_announcements) {
            $discordPosted = DiscordOrganizationMessagePublisher::publish($message);
        }

        if ($request->boolean('post_to_facebook') || $organization->auto_post_facebook_announcements) {
            $facebookPosted = FacebookOrganizationMessagePublisher::publish($message);
        }

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', match (true) {
                $discordPosted && $facebookPosted => 'Message saved, emailed to members, and posted to Discord and Facebook.',
                $discordPosted => 'Message saved, emailed to members, and posted to Discord.',
                $facebookPosted => 'Message saved, emailed to members, and posted to Facebook.',
                default => 'Message saved and emailed to organization members.',
            });
    }
}
