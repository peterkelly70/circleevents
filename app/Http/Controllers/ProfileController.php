<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Support\ImageUploads;
use App\Support\OrganizationThemes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $themeKey = $request->user()->personal_theme ?? 'embers';
        $theme = OrganizationThemes::get($themeKey);

        return view('profile.edit', [
            'user' => $request->user(),
            'theme' => $theme,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['avatar']);

        if ($request->hasFile('avatar')) {
            if ($request->user()->avatar_path) {
                Storage::disk('public')->delete($request->user()->avatar_path);
            }

            $validated['avatar_path'] = ImageUploads::storeResizedPublicImage(
                $request->file('avatar'),
                'user-avatars',
                256,
                256,
            );
        }

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
