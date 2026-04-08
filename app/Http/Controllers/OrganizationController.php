<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    protected function validatedOrganizationData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:120'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:3072'],
            'banner' => ['nullable', 'image', 'max:6144'],
            'visibility' => ['required', Rule::in(['public', 'unlisted'])],
        ]);
    }

    public function show(Organization $organization): View
    {
        $organization->load([
            'owner',
            'members',
            'posts.user',
            'messages.user',
            'events' => fn ($query) => $query->where('is_published', true)->orderBy('starts_at'),
            'mailingLists',
        ]);

        return view('organizations.show', [
            'organization' => $organization,
        ]);
    }

    public function edit(Request $request, Organization $organization): View
    {
        abort_unless($request->user()->isManagerOf($organization), 403);

        return view('organizations.edit', [
            'organization' => $organization,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedOrganizationData($request);

        if ($request->hasFile('avatar')) {
            $validated['avatar_path'] = $request->file('avatar')->store('organization-avatars', 'public');
        }

        if ($request->hasFile('banner')) {
            $validated['banner_path'] = $request->file('banner')->store('organization-banners', 'public');
        }

        $organization = Organization::create([
            ...$validated,
            'owner_id' => $request->user()->id,
            'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(6)),
        ]);

        $organization->members()->attach($request->user()->id, ['role' => 'owner']);

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', 'Organization created.');
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->isManagerOf($organization), 403);

        $validated = $this->validatedOrganizationData($request);

        if ($request->hasFile('avatar')) {
            if ($organization->avatar_path) {
                Storage::disk('public')->delete($organization->avatar_path);
            }

            $validated['avatar_path'] = $request->file('avatar')->store('organization-avatars', 'public');
        }

        if ($request->hasFile('banner')) {
            if ($organization->banner_path) {
                Storage::disk('public')->delete($organization->banner_path);
            }

            $validated['banner_path'] = $request->file('banner')->store('organization-banners', 'public');
        }

        $organization->update($validated);

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', 'Organization updated.');
    }
}
