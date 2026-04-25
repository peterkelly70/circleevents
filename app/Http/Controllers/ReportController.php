<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['user', 'organization'])],
            'id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'max:120'],
            'details' => ['nullable', 'string', 'max:2000'],
        ]);

        $target = $validated['type'] === 'user'
            ? User::findOrFail($validated['id'])
            : Organization::findOrFail($validated['id']);

        Report::create([
            'reporter_user_id' => $request->user()->id,
            'reportable_type' => $target::class,
            'reportable_id' => $target->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
        ]);

        return back()->with('status', 'Report sent to CircleEvents admins.');
    }
}
