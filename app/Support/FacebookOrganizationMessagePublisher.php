<?php

namespace App\Support;

use App\Models\OrganizationMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookOrganizationMessagePublisher
{
    public static function publish(OrganizationMessage $message): bool
    {
        $message->loadMissing(['organization', 'user']);

        $organization = $message->organization;

        if (! $organization?->facebook_page_id || ! $organization->facebook_page_access_token) {
            return false;
        }

        $body = implode("\n\n", array_filter([
            $message->subject,
            strip_tags($message->body),
            'Organization: '.$organization->name,
            'More: '.route('organizations.show', $organization),
        ]));

        try {
            $response = Http::timeout(10)->asForm()->post(
                'https://graph.facebook.com/v23.0/'.$organization->facebook_page_id.'/feed',
                [
                    'message' => $body,
                    'link' => route('organizations.show', $organization),
                    'access_token' => $organization->facebook_page_access_token,
                ]
            );

            if ($response->failed()) {
                Log::warning('Facebook organization announcement post failed.', [
                    'organization_message_id' => $message->id,
                    'organization_id' => $organization->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            $message->forceFill([
                'facebook_posted_at' => now(),
            ])->save();

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Facebook organization announcement post threw an exception.', [
                'organization_message_id' => $message->id,
                'organization_id' => $organization->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
