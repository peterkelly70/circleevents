<?php

namespace App\Support;

use App\Models\OrganizationMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordOrganizationMessagePublisher
{
    public static function publish(OrganizationMessage $message): bool
    {
        $message->loadMissing(['organization', 'user']);

        $organization = $message->organization;

        if (! $organization?->discord_webhook_url) {
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->asJson()
                ->post($organization->discord_webhook_url, [
                    'content' => null,
                    'embeds' => [[
                        'title' => $message->subject,
                        'url' => route('organizations.show', $organization),
                        'description' => mb_strimwidth(strip_tags($message->body), 0, 4000, '...'),
                        'color' => 0x34d399,
                        'fields' => [
                            [
                                'name' => 'Organization',
                                'value' => $organization->name,
                                'inline' => true,
                            ],
                            [
                                'name' => 'Posted by',
                                'value' => $message->user->name,
                                'inline' => true,
                            ],
                        ],
                        'image' => $message->imageUrl() ? ['url' => $message->imageUrl()] : null,
                    ]],
                ]);

            if ($response->failed()) {
                Log::warning('Discord organization announcement post failed.', [
                    'organization_message_id' => $message->id,
                    'organization_id' => $organization->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            $message->forceFill([
                'discord_posted_at' => now(),
            ])->save();

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Discord organization announcement post threw an exception.', [
                'organization_message_id' => $message->id,
                'organization_id' => $organization->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
