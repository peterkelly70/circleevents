<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Organization;
use App\Models\OrganizationMessage;
use App\Models\OrganizationPost;
use App\Models\Report;
use App\Models\SiteSetting;
use App\Models\User;
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
        $blockedOrganizationIds = $user->blocks()
            ->where('blockable_type', Organization::class)
            ->pluck('blockable_id');
        $blockedUserIds = $user->blocks()
            ->where('blockable_type', User::class)
            ->pluck('blockable_id');

        $feedItems = $this->feedItemsForOrganizations($organizationIds, $blockedOrganizationIds, $blockedUserIds);

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
                ->whereHas('organization')
                ->whereNotIn('organization_id', $blockedOrganizationIds)
                ->whereIn('organization_id', $organizationIds)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->take(10)
                ->get(),
            'userRegistrationMode' => SiteSetting::userRegistrationMode(),
            'organizationRegistrationMode' => SiteSetting::organizationRegistrationMode(),
            'pendingUsers' => $user->is_admin
                ? User::query()->where('registration_status', 'pending')->latest()->get()
                : collect(),
            'pendingOrganizations' => $user->is_admin
                ? Organization::query()->with('owner')->where('approval_status', 'pending')->latest()->get()
                : collect(),
            'openReports' => $user->is_admin
                ? Report::query()->with(['reporter', 'reportable'])->whereIn('status', ['open', 'reviewing'])->latest()->take(25)->get()
                : collect(),
            'suspendedUsers' => $user->is_admin
                ? User::query()->where('registration_status', 'suspended')->latest()->get()
                : collect(),
            'suspendedOrganizations' => $user->is_admin
                ? Organization::query()->with('owner')->where('approval_status', 'suspended')->latest()->get()
                : collect(),
        ]);
    }

    protected function feedItemsForOrganizations(Collection $organizationIds, Collection $blockedOrganizationIds, Collection $blockedUserIds): Collection
    {
        $posts = OrganizationPost::query()
            ->with(['organization', 'user'])
            ->whereIn('organization_id', $organizationIds)
            ->whereNotIn('organization_id', $blockedOrganizationIds)
            ->whereNotIn('user_id', $blockedUserIds)
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
            ->whereNotIn('organization_id', $blockedOrganizationIds)
            ->whereNotIn('user_id', $blockedUserIds)
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
