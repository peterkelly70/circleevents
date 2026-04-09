<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Report;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function approveUser(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $user->update([
            'registration_status' => 'active',
            'approved_at' => now(),
        ]);

        return back()->with('status', 'User approved.');
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
}
