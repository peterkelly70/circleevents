<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationBlacklist;
use App\Models\OrganizationMemberMessage;
use App\Models\User;
use App\Support\OrganizationThemes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrganizationMemberController extends Controller
{
    public function index(Request $request, Organization $organization): View
    {
        abort_unless($request->user()->isManagerOf($organization), 403);

        $themeKey = $request->user()->resolvedOrganizationThemeKey($organization);
        $theme = OrganizationThemes::get($themeKey);

        $managerMembers = $organization->members->whereIn('pivot.role', ['owner', 'manager']);
        $followerMembers = $organization->members->where('pivot.role', 'follower');

        $bannedUserIds = OrganizationBlacklist::where('organization_id', $organization->id)->pluck('user_id');
        $bannedUsers = User::whereIn('id', $bannedUserIds)->get();

        return view('organizations.members.index', compact('organization', 'theme', 'managerMembers', 'followerMembers', 'bannedUsers'));
    }

    public function follow(Request $request, Organization $organization): RedirectResponse
    {
        if (! $request->user()->organizations()->where('organization_id', $organization->id)->exists()) {
            $request->user()->organizations()->attach($organization->id, ['role' => 'follower']);
            $request->user()->organizations()->updateExistingPivot($organization->id, [
                'email_opt_out_token' => Str::random(48),
                'email_opt_out_at' => null,
            ]);
        } else {
            $request->user()->organizations()->updateExistingPivot($organization->id, [
                'email_opt_out_at' => null,
            ]);
        }

        $organization->subscribeMemberToDefaultMailingList($request->user());

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', 'You are now following this organization.');
    }

    public function leave(Request $request, Organization $organization): RedirectResponse
    {
        $membership = $request->user()->organizations()
            ->where('organization_id', $organization->id)
            ->first();

        if (! $membership) {
            return redirect()
                ->route('organizations.show', $organization)
                ->with('status', 'You are not following this organization.');
        }

        if ($membership->pivot->role === 'owner') {
            abort(403);
        }

        $request->user()->organizations()->detach($organization->id);
        $request->user()->mailingLists()->detach($organization->mailingLists()->pluck('mailing_lists.id'));

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', 'You left this organization.');
    }

    public function promote(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->isOwnerOf($organization), 403);

        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        foreach ($validated['user_ids'] as $userId) {
            $member = $organization->members()->where('user_id', $userId)->first();

            if (! $member || $member->pivot->role !== 'follower') {
                continue;
            }

            $organization->members()->updateExistingPivot($userId, ['role' => 'manager']);
        }

        return back()->with('status', 'Members promoted to managers.');
    }

    public function remove(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->isManagerOf($organization), 403);

        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        foreach ($validated['user_ids'] as $userId) {
            $member = $organization->members()->where('user_id', $userId)->first();

            if (! $member) {
                continue;
            }

            if ($member->pivot->role === 'owner') {
                continue;
            }

            if ($request->boolean('blacklist')) {
                OrganizationBlacklist::create([
                    'organization_id' => $organization->id,
                    'user_id' => $userId,
                    'blocked_by_user_id' => $request->user()->id,
                ]);
            }

            $organization->members()->detach($userId);
            $organization->unsubscribeMemberFromDefaultMailingList($member);
        }

        $action = $request->boolean('blacklist') ? 'removed and banned' : 'removed';

        return back()->with('status', 'Members '.$action.'.');
    }

    public function demote(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->isOwnerOf($organization), 403);

        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        foreach ($validated['user_ids'] as $userId) {
            $member = $organization->members()->where('user_id', $userId)->first();

            if (! $member || $member->pivot->role !== 'manager') {
                continue;
            }

            $organization->members()->updateExistingPivot($userId, ['role' => 'follower']);
            $organization->subscribeMemberToDefaultMailingList($member);
        }

        return back()->with('status', 'Managers demoted to followers.');
    }

    public function sendMemberMessage(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->isManagerOf($organization), 403);

        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        foreach ($validated['user_ids'] as $userId) {
            $member = $organization->members()->where('user_id', $userId)->first();

            if (! $member) {
                continue;
            }

            OrganizationMemberMessage::create([
                'organization_id' => $organization->id,
                'user_id' => $userId,
                'from_user_id' => $request->user()->id,
                'subject' => $validated['subject'],
                'body' => $validated['body'],
            ]);
        }

        return back()->with('status', 'Message sent to '.count($validated['user_ids']).' member(s).');
    }

    public function ban(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->isManagerOf($organization), 403);

        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        foreach ($validated['user_ids'] as $userId) {
            $member = $organization->members()->where('user_id', $userId)->first();

            if (! $member) {
                continue;
            }

            if ($member->pivot->role === 'owner') {
                continue;
            }

            OrganizationBlacklist::create([
                'organization_id' => $organization->id,
                'user_id' => $userId,
                'blocked_by_user_id' => $request->user()->id,
            ]);

            $organization->members()->detach($userId);
            $organization->unsubscribeMemberFromDefaultMailingList($member);
        }

        return back()->with('status', 'Members banned.');
    }

    public function unban(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->isManagerOf($organization), 403);

        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        OrganizationBlacklist::where('organization_id', $organization->id)
            ->whereIn('user_id', $validated['user_ids'])
            ->delete();

        return back()->with('status', 'Members unbanned.');
    }
}
