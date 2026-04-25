<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Organization;
use App\Models\SocialPublication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class SocialPublishJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Event $event;

    public Organization $organization;

    public ?SocialPublication $publication;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(Event $event, Organization $organization, ?SocialPublication $publication = null)
    {
        $this->event = $event;
        $this->organization = $organization;
        $this->publication = $publication;
    }

    protected function logSuccess(string $remotePostId, ?string $remoteUrl, ?array $response = null): void
    {
        if ($this->publication) {
            $this->publication->markAsPosted($remotePostId, $remoteUrl, $response);
        }
    }

    protected function logFailure(?array $error = null): void
    {
        if ($this->publication) {
            $this->publication->markAsFailed($error);
        }
    }
}
