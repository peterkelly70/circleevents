<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
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

        return view('dashboard', [
            'managedOrganizations' => $user->organizations
                ->whereIn('pivot.role', ['owner', 'manager'])
                ->sortBy('name')
                ->values(),
            'subscriptions' => $user->mailingLists->sortBy('name')->values(),
            'rsvps' => $user->rsvps->sortBy(fn ($rsvp) => $rsvp->event?->starts_at)->values(),
            'upcomingEvents' => Event::query()
                ->with('organization')
                ->whereIn('organization_id', $user->organizations->pluck('id'))
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->take(10)
                ->get(),
        ]);
    }
}
