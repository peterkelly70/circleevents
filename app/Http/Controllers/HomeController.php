<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Organization;
use App\Models\OrganizationMessage;
use App\Models\OrganizationPost;
use App\Support\OrganizationThemes;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $themeKey = $request->user()?->resolvedOrganizationThemeKey(null) ?? 'default';
        $theme = OrganizationThemes::get($themeKey);

        if ($request->user()) {
            $user = $request->user()->resolvedUser()->load(['organizations', 'rsvps.event.organization']);

            $organizationIds = $user->organizations->pluck('id');
            $blockedOrganizationIds = $user->blocks()
                ->where('blockable_type', Organization::class)
                ->pluck('blockable_id');

            $feedItems = $this->feedItemsForOrganizations($organizationIds, $blockedOrganizationIds);

            return view('home', [
                'isLoggedIn' => true,
                'managedOrganizations' => $user->organizations
                    ->whereIn('pivot.role', ['owner', 'manager'])
                    ->sortBy('name')
                    ->values(),
                'followedOrganizations' => $user->organizations
                    ->whereIn('pivot.role', ['follower'])
                    ->sortBy('name')
                    ->values(),
                'feedItems' => $feedItems,
                'upcomingEvents' => Event::query()
                    ->with('organization')
                    ->whereHas('organization')
                    ->whereNotIn('organization_id', $blockedOrganizationIds)
                    ->whereIn('organization_id', $organizationIds)
                    ->where('starts_at', '>=', now())
                    ->orderBy('starts_at')
                    ->take(10)
                    ->get(),
                'theme' => $theme,
            ]);
        }

        return view('home', [
            'isLoggedIn' => false,
            'theme' => $theme,
            'featuredEvents' => Event::query()
                ->with('organization')
                ->whereHas('organization', fn ($query) => $query->where('approval_status', 'approved'))
                ->where('is_published', true)
                ->where('visibility', 'public')
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->take(6)
                ->get(),
            'organizations' => Organization::query()
                ->withCount([
                    'events' => fn ($query) => $query->where('is_published', true),
                    'mailingLists',
                ])
                ->where('approval_status', 'approved')
                ->where('visibility', 'public')
                ->orderBy('name')
                ->take(6)
                ->get(),
        ]);
    }

    protected function feedItemsForOrganizations($organizationIds, $blockedOrganizationIds)
    {
        $posts = OrganizationPost::query()
            ->with(['organization', 'user'])
            ->whereIn('organization_id', $organizationIds)
            ->whereNotIn('organization_id', $blockedOrganizationIds)
            ->latest()
            ->take(15)
            ->get()
            ->map(fn ($post) => (object) [
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
            ->whereNotIn('organization_id', $blockedOrganizationIds)
            ->latest()
            ->take(15)
            ->get()
            ->map(fn ($message) => (object) [
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
            ->take(15)
            ->values();
    }

    public function install(): View
    {
        return view('install');
    }
}
