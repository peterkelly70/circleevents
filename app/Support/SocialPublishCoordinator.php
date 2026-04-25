<?php

namespace App\Support;

use App\Jobs\PublishEventToDiscordJob;
use App\Jobs\PublishEventToFacebookJob;
use App\Jobs\PublishEventToXJob;
use App\Models\Event;
use App\Models\SocialPublication;

class SocialPublishCoordinator
{
    public static function publishEvent(Event $event): void
    {
        $event->loadMissing('organization');
        $organization = $event->organization;

        if ($organization->facebookAccount()->exists()) {
            $publication = SocialPublication::create([
                'organization_id' => $organization->id,
                'event_id' => $event->id,
                'platform' => SocialPublication::PLATFORM_FACEBOOK,
                'status' => SocialPublication::STATUS_PENDING,
            ]);

            PublishEventToFacebookJob::dispatch($event, $organization, $publication);
        }

        if ($organization->xAccount()->exists()) {
            $publication = SocialPublication::create([
                'organization_id' => $organization->id,
                'event_id' => $event->id,
                'platform' => SocialPublication::PLATFORM_X,
                'status' => SocialPublication::STATUS_PENDING,
            ]);

            PublishEventToXJob::dispatch($event, $organization, $publication);
        }

        if ($organization->discordAccount()->exists()) {
            $publication = SocialPublication::create([
                'organization_id' => $organization->id,
                'event_id' => $event->id,
                'platform' => SocialPublication::PLATFORM_DISCORD,
                'status' => SocialPublication::STATUS_PENDING,
            ]);

            PublishEventToDiscordJob::dispatch($event, $organization, $publication);
        }
    }
}
