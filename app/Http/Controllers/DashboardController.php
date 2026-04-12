<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Organization;
use App\Models\Report;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\OrganizationThemes;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $themeKey = $request->user()->resolvedOrganizationThemeKey(null) ?? 'default';
        $theme = OrganizationThemes::get($themeKey);

        $user = $request->user()->resolvedUser()->load([
            'createdOrganizations',
            'organizations',
            'mailingLists.organization',
            'rsvps.event.organization',
        ]);

        $organizationIds = $user->organizations->pluck('id');
        $blockedOrganizationIds = $user->blocks()
            ->where('blockable_type', Organization::class)
            ->pluck('blockable_id');

        return view('dashboard', [
            'managedOrganizations' => $user->organizations
                ->whereIn('pivot.role', ['owner', 'manager'])
                ->sortBy('name')
                ->values(),
            'followedOrganizations' => $user->organizations
                ->whereIn('pivot.role', ['follower'])
                ->sortBy('name')
                ->values(),
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
            'allUsers' => ($user->is_admin || $request->session()->has('impersonating_user_id'))
                ? User::query()->latest()->take(50)->get()
                : collect(),
            'theme' => $theme,
        ]);
    }
}
