<?php

namespace App\Jobs;

use App\Support\DiscordPublisher;
use Illuminate\Support\Facades\Log;

class PublishEventToDiscordJob extends SocialPublishJob
{
    public function handle(): bool
    {
        $account = $this->organization->discordAccount()->first();

        if (! $account) {
            $this->logFailure(['error' => 'No active Discord account/webhook linked']);

            return false;
        }

        $result = DiscordPublisher::publishEvent($this->event, $account);

        if ($result['success']) {
            $this->logSuccess(
                $result['message_id'] ?? 'unknown',
                null,
                $result['response'] ?? null
            );

            return true;
        }

        $this->logFailure($result['error'] ?? null);

        return false;
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Discord publish job failed', [
            'event_id' => $this->event->id,
            'organization_id' => $this->organization->id,
            'exception' => $exception?->getMessage(),
        ]);

        $this->logFailure(['exception' => $exception?->getMessage()]);
    }
}
