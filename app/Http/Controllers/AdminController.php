<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Report;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function updateSettings(Request $request): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $validated = $request->validate([
            'user_registration_mode' => ['required', Rule::in(['open', 'moderated'])],
            'organization_registration_mode' => ['required', Rule::in(['open', 'moderated'])],
        ]);

        SiteSetting::setValue('user_registration_mode', $validated['user_registration_mode']);
        SiteSetting::setValue('organization_registration_mode', $validated['organization_registration_mode']);

        return back()->with('status', 'Registration settings updated.');
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->is_admin, 403);

        $tab = $request->query('tab', 'users');
        $search = $request->query('search', '');

        $users = User::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
            ->orderBy('name')
            ->orderBy('email')
            ->get();

        $organizations = Organization::query()
            ->with('owner', 'members')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        $reports = Report::query()
            ->with(['reporter', 'reportable'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.index', compact('tab', 'search', 'users', 'organizations', 'reports'));
    }

    public function approveUser(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $user->update([
            'registration_status' => 'active',
            'approved_at' => now(),
        ]);

        return back()->with('status', 'User approved.');
    }

    public function suspendUser(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);
        abort_if($user->is_admin, 403);

        $user->update([
            'registration_status' => 'suspended',
            'approved_at' => null,
        ]);

        return back()->with('status', 'User suspended.');
    }

    public function restoreUser(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $user->update([
            'registration_status' => 'active',
            'approved_at' => now(),
        ]);

        return back()->with('status', 'User restored.');
    }

    public function resetUserPassword(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);
        abort_if($user->is_admin, 403);

        $newPassword = $request->input('new_password')
            ?? substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        Mail::html("Your password has been reset by an administrator.<br><br>Your new password is: <strong>{$newPassword}</strong><br><br>Please change it after logging in.", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Password reset by admin');
        });

        return back()->with('status', 'User password reset and emailed.');
    }

    public function deleteUser(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);
        abort_if($user->is_admin, 403);

        $user->delete();

        return redirect()->route('admin.index', ['tab' => 'users'])->with('status', 'User deleted.');
    }

    public function approveOrganization(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $organization->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by_user_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Organization approved.');
    }

    public function suspendOrganization(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $organization->update([
            'approval_status' => 'suspended',
            'approved_at' => null,
            'approved_by_user_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Organization suspended.');
    }

    public function restoreOrganization(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $organization->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by_user_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Organization restored.');
    }

    public function deleteOrganization(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $organization->delete();

        return redirect()->route('admin.index', ['tab' => 'organizations'])->with('status', 'Organization deleted.');
    }

    public function updateReportStatus(Request $request, Report $report): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['open', 'reviewing', 'resolved', 'dismissed'])],
        ]);

        $report->update([
            'status' => $validated['status'],
        ]);

        return back()->with('status', 'Report status updated.');
    }

    public function addUserToOrganization(Request $request): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'organization_id' => ['required', 'exists:organizations,id'],
            'role' => ['required', Rule::in(['follower', 'manager'])],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $organization = Organization::findOrFail($validated['organization_id']);

        $existing = DB::table('organization_user')
            ->where('user_id', $validated['user_id'])
            ->where('organization_id', $validated['organization_id'])
            ->exists();

        if ($existing) {
            return back()->with('status', 'User is already a member of this organization.');
        }

        DB::table('organization_user')->insert([
            'user_id' => $validated['user_id'],
            'organization_id' => $validated['organization_id'],
            'role' => $validated['role'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $organization->subscribeMemberToDefaultMailingList($user);

        return back()->with('status', 'User added to organization.');
    }

    public function removeUserFromOrganization(Request $request, User $user, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        DB::table('organization_user')
            ->where('user_id', $user->id)
            ->where('organization_id', $organization->id)
            ->delete();

        $organization->unsubscribeMemberFromDefaultMailingList($user);

        return back()->with('status', 'User removed from organization.');
    }

    public function impersonate(Request $request): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        abort_if($user->is_admin, 403);

        $request->session()->put('impersonator_user_id', $request->user()->id);
        auth()->login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function stopImpersonating(Request $request): RedirectResponse
    {
        $request->session()->forget('impersonator_user_id');

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
