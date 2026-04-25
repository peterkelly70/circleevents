<?php

namespace App\Support;

use App\Models\Event;
use App\Models\OrganizationXAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XPublisher
{
    public static function getAuthorizationUrl(string $redirectUri, string $state): string
    {
        $clientId = config('services.twitter.client_id');

        return 'https://twitter.com/i/oauth2/authorize?'.http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'scope' => 'tweet.read tweet.write users.read offline.access',
            'response_type' => 'code',
            'code_challenge' => bin2hex(random_bytes(32)),
            'code_challenge_method' => 'S256',
        ]);
    }

    public static function handleCallback(string $code, string $redirectUri, string $codeVerifier): ?array
    {
        $clientId = config('services.twitter.client_id');
        $clientSecret = config('services.twitter.client_secret');

        $response = Http::asForm()->withBasicAuth($clientId, $clientSecret)->post('https://api.twitter.com/2/oauth2/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'code_verifier' => $codeVerifier,
        ]);

        if ($response->failed()) {
            Log::warning('X OAuth token exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $data = $response->json();

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_in' => $data['expires_in'] ?? null,
        ];
    }

    public static function refreshToken(OrganizationXAccount $account): bool
    {
        $refreshToken = $account->getRefreshToken();

        if (! $refreshToken) {
            return false;
        }

        $clientId = config('services.twitter.client_id');
        $clientSecret = config('services.twitter.client_secret');

        $response = Http::asForm()->withBasicAuth($clientId, $clientSecret)->post('https://api.twitter.com/2/oauth2/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        if ($response->failed()) {
            return false;
        }

        $data = $response->json();

        $account->setAccessToken($data['access_token']);

        if (isset($data['refresh_token'])) {
            $account->setRefreshToken($data['refresh_token']);
        }

        if (isset($data['expires_in'])) {
            $account->token_expires_at = now()->addSeconds($data['expires_in']);
        }

        $account->save();

        return true;
    }

    public static function getUserInfo(string $accessToken): ?array
    {
        $response = Http::withToken($accessToken)
            ->get('https://api.twitter.com/2/users/me', [
                'user.fields' => 'id,name,username',
            ]);

        if ($response->failed()) {
            return null;
        }

        return $response->json()['data'] ?? null;
    }

    public static function publishEvent(Event $event, OrganizationXAccount $account): array
    {
        $event->loadMissing('organization');
        $organization = $event->organization;

        $accessToken = $account->getAccessToken();

        if (! $accessToken) {
            return ['success' => false, 'error' => ['message' => 'No access token']];
        }

        $eventUrl = route('events.show', $event);
        $text = "{$event->title}\n\n";

        if ($event->summary) {
            $text .= $event->summary."\n\n";
        }

        $text .= '📅 '.$event->starts_at->format('D, d M Y g:i A')."\n";

        if ($event->venue_name) {
            $text .= '📍 '.$event->venue_name."\n";
        }

        $text .= "🔗 {$eventUrl}";

        if (strlen($text) > 280) {
            $text = substr($text, 0, 276).'... '.$eventUrl;
        }

        try {
            $response = Http::withToken($accessToken)
                ->asJson()
                ->post('https://api.twitter.com/2/tweets', [
                    'text' => $text,
                ]);

            if ($response->failed()) {
                $error = $response->json();
                Log::warning('X event post failed', [
                    'event_id' => $event->id,
                    'account_id' => $account->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => [
                        'message' => $error['detail'] ?? 'API error',
                    ],
                ];
            }

            $data = $response->json();

            return [
                'success' => true,
                'post_id' => $data['data']['id'] ?? null,
                'post_url' => isset($data['data']['id'])
                    ? 'https://twitter.com/'.$account->x_screen_name.'/status/'.$data['data']['id']
                    : null,
                'response' => $data,
            ];
        } catch (\Throwable $exception) {
            Log::warning('X event post threw exception', [
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
