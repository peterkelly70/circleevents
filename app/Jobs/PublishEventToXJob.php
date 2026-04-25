<?php

namespace App\Jobs;

use App\Support\XPublisher;
use Illuminate\Support\Facades\Log;

class PublishEventToXJob extends SocialPublishJob
{
    public function handle(): bool
    {
        $account = $this->organization->xAccount()->first();

        if (! $account) {
            $this->logFailure(['error' => 'No active X account linked']);

            return false;
        }

        if ($account->token_expires_at && $account->token_expires_at->isPast()) {
            $refreshed = XPublisher::refreshToken($account);
            if (! $refreshed) {
                $this->logFailure(['error' => 'Token refresh failed']);

                return false;
            }
        }

        $result = XPublisher::publishEvent($this->event, $account);

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
        Log::error('X publish job failed', [
            'event_id' => $this->event->id,
            'organization_id' => $this->organization->id,
            'exception' => $exception?->getMessage(),
        ]);

        $this->logFailure(['exception' => $exception?->getMessage()]);
    }
}
