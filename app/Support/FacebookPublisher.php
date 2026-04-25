<?php

namespace App\Support;

use App\Models\Event;
use App\Models\OrganizationFacebookAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookPublisher
{
    protected const GraphApiVersion = 'v23.0';

    public static function getAuthorizationUrl(string $redirectUri, string $state): string
    {
        $clientId = config('services.facebook.client_id');

        return 'https://www.facebook.com/'.self::GraphApiVersion.'/dialog/oauth?'.http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'scope' => 'pages_manage_posts,pages_read_engagement',
            'response_type' => 'code',
        ]);
    }

    public static function handleCallback(string $code, string $redirectUri): ?array
    {
        $clientId = config('services.facebook.client_id');
        $clientSecret = config('services.facebook.client_secret');

        $response = Http::post('https://graph.facebook.com/'.self::GraphApiVersion.'/oauth/access_token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if ($response->failed()) {
            Log::warning('Facebook OAuth token exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $data = $response->json();

        return [
            'access_token' => $data['access_token'],
            'expires_in' => $data['expires_in'] ?? null,
        ];
    }

    public static function getUserPages(string $accessToken): ?array
    {
        $response = Http::withToken($accessToken)
            ->get('https://graph.facebook.com/'.self::GraphApiVersion.'/me/accounts', [
                'fields' => 'id,name,access_token',
            ]);

        if ($response->failed()) {
            Log::warning('Facebook get user pages failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        return $response->json()['data'] ?? [];
    }

    public static function refreshToken(OrganizationFacebookAccount $account): bool
    {
        $refreshToken = $account->getRefreshToken();

        if (! $refreshToken) {
            return false;
        }

        $clientId = config('services.facebook.client_id');
        $clientSecret = config('services.facebook.client_secret');

        $response = Http::post('https://graph.facebook.com/'.self::GraphApiVersion.'/oauth/access_token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'fb_exchange_token' => $refreshToken,
            'grant_type' => 'fb_exchange_token',
        ]);

        if ($response->failed()) {
            return false;
        }

        $data = $response->json();

        $account->setAccessToken($data['access_token']);

        if (isset($data['expires_in'])) {
            $account->token_expires_at = now()->addSeconds($data['expires_in']);
        }

        $account->save();

        return true;
    }

    public static function publishEvent(Event $event, OrganizationFacebookAccount $account): array
    {
        $event->loadMissing('organization');
        $organization = $event->organization;

        $accessToken = $account->getAccessToken();

        if (! $accessToken) {
            return ['success' => false, 'error' => ['message' => 'No access token']];
        }

        $message = implode("\n\n", array_filter([
            $event->title,
            $event->summary,
            'When: '.$event->starts_at->format('D, d M Y g:i A').' to '.$event->ends_at->format('g:i A').' '.$event->timezone,
            $event->venue_name ? 'Venue: '.trim($event->venue_name.($event->venue_address ? ', '.$event->venue_address : '')) : null,
            'Details: '.route('events.show', $event),
        ]));

        try {
            $response = Http::timeout(10)
                ->asForm()
                ->withToken($accessToken)
                ->post('https://graph.facebook.com/'.$account->facebook_page_id.'/feed', [
                    'message' => $message,
                    'link' => route('events.show', $event),
                ]);

            if ($response->failed()) {
                $error = $response->json();
                Log::warning('Facebook event post failed', [
                    'event_id' => $event->id,
                    'account_id' => $account->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => [
                        'message' => $error['error']['message'] ?? 'API error',
                        'code' => $error['error']['code'] ?? null,
                    ],
                ];
            }

            $data = $response->json();

            return [
                'success' => true,
                'post_id' => $data['id'] ?? null,
                'post_url' => isset($data['id'])
                    ? 'https://www.facebook.com/'.$account->facebook_page_id.'_'.$data['id']
                    : null,
                'response' => $data,
            ];
        } catch (\Throwable $exception) {
            Log::warning('Facebook event post threw exception', [
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
