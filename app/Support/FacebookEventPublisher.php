<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookEventPublisher
{
    public static function publish(Event $event): bool
    {
        $event->loadMissing('organization');

        $organization = $event->organization;

        if (! $organization?->facebook_page_id || ! $organization->facebook_page_access_token || ! $organization->auto_post_facebook_events) {
            return false;
        }

        if ($event->visibility === 'private') {
            return false;
        }

        $message = implode("\n\n", array_filter([
            $event->title,
            $event->summary,
            'When: '.$event->starts_at->format('D, d M Y g:i A').' to '.$event->ends_at->format('g:i A').' '.$event->timezone,
            $event->venue_name ? 'Venue: '.trim($event->venue_name.($event->venue_address ? ', '.$event->venue_address : '')) : null,
            'Details: '.route('events.show', $event),
        ]));

        try {
            $response = Http::timeout(10)->asForm()->post(
                'https://graph.facebook.com/v23.0/'.$organization->facebook_page_id.'/feed',
                [
                    'message' => $message,
                    'link' => route('events.show', $event),
                    'access_token' => $organization->facebook_page_access_token,
                ]
            );

            if ($response->failed()) {
                Log::warning('Facebook event post failed.', [
                    'event_id' => $event->id,
                    'organization_id' => $organization->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            $event->forceFill([
                'facebook_posted_at' => now(),
            ])->save();

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Facebook event post threw an exception.', [
                'event_id' => $event->id,
                'organization_id' => $organization->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
