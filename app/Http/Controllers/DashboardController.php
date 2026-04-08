<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\OrganizationMessage;
use App\Models\OrganizationPost;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load([
            'createdOrganizations',
            'organizations',
            'mailingLists.organization',
            'rsvps.event.organization',
        ]);

        $organizationIds = $user->organizations->pluck('id');

        $feedItems = $this->feedItemsForOrganizations($organizationIds);

        return view('dashboard', [
            'managedOrganizations' => $user->organizations
                ->whereIn('pivot.role', ['owner', 'manager'])
                ->sortBy('name')
                ->values(),
            'feedItems' => $feedItems,
            'subscriptions' => $user->mailingLists->sortBy('name')->values(),
            'rsvps' => $user->rsvps->sortBy(fn ($rsvp) => $rsvp->event?->starts_at)->values(),
            'upcomingEvents' => Event::query()
                ->with('organization')
                ->whereIn('organization_id', $organizationIds)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->take(10)
                ->get(),
        ]);
    }

    protected function feedItemsForOrganizations(Collection $organizationIds): Collection
    {
        $posts = OrganizationPost::query()
            ->with(['organization', 'user'])
            ->whereIn('organization_id', $organizationIds)
            ->latest()
            ->take(20)
            ->get()
            ->map(fn (OrganizationPost $post) => (object) [
                'type' => 'post',
                'organization' => $post->organization,
                'author' => $post->user,
                'title' => null,
                'body' => $post->body,
                'created_at' => $post->created_at,
            ]);

        $messages = OrganizationMessage::query()
            ->with(['organization', 'user'])
            ->whereIn('organization_id', $organizationIds)
            ->latest()
            ->take(20)
            ->get()
            ->map(fn (OrganizationMessage $message) => (object) [
                'type' => 'message',
                'organization' => $message->organization,
                'author' => $message->user,
                'title' => $message->subject,
                'body' => $message->body,
                'created_at' => $message->created_at,
            ]);

        return $posts
            ->concat($messages)
            ->sortByDesc('created_at')
            ->take(20)
            ->values();
    }
}
