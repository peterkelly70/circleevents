<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordEventPublisher
{
    public static function publish(Event $event, bool $force = false): bool
    {
        $event->loadMissing('organization');

        $organization = $event->organization;

        if (! $organization?->discord_webhook_url || (! $force && ! $organization->auto_post_discord_events)) {
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->asJson()
                ->post($organization->discord_webhook_url, [
                    'content' => null,
                    'embeds' => [[
                        'title' => $event->title,
                        'url' => route('events.show', $event),
                        'description' => $event->summary,
                        'color' => 0xfbbf24,
                        'fields' => array_values(array_filter([
                            [
                                'name' => 'When',
                                'value' => $event->starts_at->format('D, d M Y g:i A').' to '.$event->ends_at->format('g:i A').' '.$event->timezone,
                                'inline' => false,
                            ],
                            $event->venue_name ? [
                                'name' => 'Venue',
                                'value' => trim($event->venue_name."\n".$event->venue_address),
                                'inline' => false,
                            ] : null,
                            [
                                'name' => 'Organization',
                                'value' => $organization->name,
                                'inline' => true,
                            ],
                            [
                                'name' => 'Visibility',
                                'value' => $event->visibilityLabel(),
                                'inline' => true,
                            ],
                        ])),
                        'image' => $event->imageUrl() ? ['url' => $event->imageUrl()] : null,
                    ]],
                ]);

            if ($response->failed()) {
                Log::warning('Discord event post failed.', [
                    'event_id' => $event->id,
                    'organization_id' => $organization->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            $event->forceFill([
                'discord_posted_at' => now(),
            ])->save();

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Discord event post threw an exception.', [
                'event_id' => $event->id,
                'organization_id' => $organization->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
