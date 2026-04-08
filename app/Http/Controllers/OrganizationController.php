<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Support\ImageUploads;
use App\Models\User;
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
        $request->merge([
            'website_url' => $this->normalizeWebsiteUrl($request->input('website_url')),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:120'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:12288'],
            'banner' => ['nullable', 'image', 'max:20480'],
            'visibility' => ['required', Rule::in(['public', 'private', 'unlisted'])],
        ]);
    }

    protected function normalizeWebsiteUrl(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : null;

        if (! $value) {
            return null;
        }

        if (! str_starts_with($value, 'http://') && ! str_starts_with($value, 'https://')) {
            $value = 'https://'.$value;
        }

        return $value;
    }

    public function show(Organization $organization): View
    {
        abort_unless($organization->isVisibleTo(request()->user()), 403);

        $organization->load([
            'owner',
            'members',
            'posts.user',
            'messages.user',
            'invitations',
            'events' => fn ($query) => $query->where('is_published', true)->orderBy('starts_at'),
            'mailingLists',
        ]);

        $user = request()->user();

        if ($user) {
            $blockedUserIds = $user->blocks()
                ->where('blockable_type', User::class)
                ->pluck('blockable_id');

            $organization->setRelation('posts', $organization->posts->reject(
                fn ($post) => $blockedUserIds->contains($post->user_id)
            )->values());

            $organization->setRelation('messages', $organization->messages->reject(
                fn ($message) => $blockedUserIds->contains($message->user_id)
            )->values());
        }

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
            $validated['avatar_path'] = ImageUploads::storeResizedPublicImage(
                $request->file('avatar'),
                'organization-avatars',
                512,
                512,
            );
        }

        if ($request->hasFile('banner')) {
            $validated['banner_path'] = ImageUploads::storeResizedPublicImage(
                $request->file('banner'),
                'organization-banners',
                1560,
                1024,
            );
        }

        $organization = Organization::create([
            ...$validated,
            'owner_id' => $request->user()->id,
            'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(6)),
        ]);

        $organization->members()->attach($request->user()->id, ['role' => 'owner']);
        $organization->members()->updateExistingPivot($request->user()->id, [
            'email_opt_out_token' => Str::random(48),
        ]);

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

            $validated['avatar_path'] = ImageUploads::storeResizedPublicImage(
                $request->file('avatar'),
                'organization-avatars',
                512,
                512,
            );
        }

        if ($request->hasFile('banner')) {
            if ($organization->banner_path) {
                Storage::disk('public')->delete($organization->banner_path);
            }

            $validated['banner_path'] = ImageUploads::storeResizedPublicImage(
                $request->file('banner'),
                'organization-banners',
                1560,
                1024,
            );
        }

        $organization->update($validated);

        return redirect()
            ->route('organizations.show', $organization)
            ->with('status', 'Organization updated.');
    }
}
