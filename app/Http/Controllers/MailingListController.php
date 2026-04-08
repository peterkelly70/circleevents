<?php

namespace App\Http\Controllers;

use App\Models\MailingList;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MailingListController extends Controller
{
    public function show(MailingList $mailingList): View
    {
        $mailingList->load('organization', 'subscribers');

        return view('mailing-lists.show', [
            'mailingList' => $mailingList,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'audience' => ['required', Rule::in(['all-members', 'students', 'sponsors', 'volunteers'])],
        ]);

        $organization = Organization::findOrFail($validated['organization_id']);
        abort_unless($request->user()->isManagerOf($organization), 403);

        $list = MailingList::create([
            ...$validated,
            'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(6)),
        ]);

        return redirect()
            ->route('mailing-lists.show', $list)
            ->with('status', 'Mailing list created.');
    }

    public function subscribe(Request $request, MailingList $mailingList): RedirectResponse
    {
        $request->user()->mailingLists()->sync([
            $mailingList->id => [
                'status' => 'subscribed',
                'subscribed_at' => now(),
            ],
        ], false);

        return redirect()
            ->route('mailing-lists.show', $mailingList)
            ->with('status', 'Subscription saved.');
    }
}
