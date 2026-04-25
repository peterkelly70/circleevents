<?php

namespace App\Support;

use App\Models\Event;
use App\Models\OrganizationDiscordAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordPublisher
{
    public static function connectWebhook(string $webhookUrl): ?array
    {
        try {
            $response = Http::timeout(5)->get($webhookUrl);

            if ($response->failed()) {
                return null;
            }

            return $response->json();
        } catch (\Throwable $exception) {
            Log::warning('Discord webhook validation failed', [
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public static function publishEvent(Event $event, OrganizationDiscordAccount $account): array
    {
        $event->loadMissing('organization');
        $organization = $event->organization;

        $webhookUrl = $account->getWebhookUrl();

        if (! $webhookUrl) {
            return ['success' => false, 'error' => ['message' => 'No webhook URL']];
        }

        $eventUrl = route('events.show', $event);

        try {
            $response = Http::timeout(10)
                ->asJson()
                ->post($webhookUrl, [
                    'content' => "Event link: {$eventUrl}",
                    'embeds' => [[
                        'title' => $event->title,
                        'url' => $eventUrl,
                        'description' => $event->summary,
                        'color' => 0xFBBF24,
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
                            [
                                'name' => 'Event link',
                                'value' => $eventUrl,
                                'inline' => false,
                            ],
                        ])),
                        'image' => $event->imageUrl() ? ['url' => $event->imageUrl()] : null,
                    ]],
                ]);

            if ($response->failed()) {
                $error = $response->json();
                Log::warning('Discord event post failed', [
                    'event_id' => $event->id,
                    'account_id' => $account->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => [
                        'message' => $error['message'] ?? 'Webhook rejected request',
                        'code' => $response->status(),
                    ],
                ];
            }

            return [
                'success' => true,
                'message_id' => $response->header('x-amz-cf-id') ?? null,
                'response' => ['sent' => true],
            ];
        } catch (\Throwable $exception) {
            Log::warning('Discord event post threw exception', [
                'event_id' => $event->id,
                'account_id' => $account->id,
                'message' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => ['message' => $exception->getMessage()],
            ];
        }
    }
}
