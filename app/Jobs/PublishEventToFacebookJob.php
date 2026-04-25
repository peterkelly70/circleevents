<?php

namespace App\Jobs;

use App\Support\FacebookPublisher;
use Illuminate\Support\Facades\Log;

class PublishEventToFacebookJob extends SocialPublishJob
{
    public function handle(): bool
    {
        $account = $this->organization->facebookAccount()->first();

        if (! $account) {
            $this->logFailure(['error' => 'No active Facebook account linked']);

            return false;
        }

        if ($account->token_expires_at && $account->token_expires_at->isPast()) {
            $refreshed = FacebookPublisher::refreshToken($account);
            if (! $refreshed) {
                $this->logFailure(['error' => 'Token refresh failed']);

                return false;
            }
        }

        $result = FacebookPublisher::publishEvent($this->event, $account);

        if ($result['success']) {
            $this->logSuccess(
                $result['post_id'] ?? 'unknown',
                $result['post_url'] ?? null,
                $result['response'] ?? null
            );

            return true;
        }

        $this->logFailure($result['error'] ?? null);

        return false;
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Facebook publish job failed', [
            'event_id' => $this->event->id,
            'organization_id' => $this->organization->id,
            'exception' => $exception?->getMessage(),
        ]);

        $this->logFailure(['exception' => $exception?->getMessage()]);
    }
}
