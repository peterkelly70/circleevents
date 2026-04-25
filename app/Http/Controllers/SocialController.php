<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Support\DiscordPublisher;
use App\Support\FacebookPublisher;
use App\Support\XPublisher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SocialController extends Controller
{
    public function facebookConnect(Organization $organization)
    {
        if (! request()->user()->isOwnerOf($organization)) {
            abort(403);
        }

        $redirectUri = route('social.facebook.callback');
        $state = $organization->id.'|'.Str::random(40);

        session(['facebook_oauth_state' => $state, 'facebook_org_id' => $organization->id]);

        return redirect(FacebookPublisher::getAuthorizationUrl($redirectUri, $state));
    }

    public function facebookCallback(Request $request)
    {
        $state = $request->get('state');
        $code = $request->get('code');

        $expectedState = session('facebook_oauth_state');
        $orgId = session('facebook_org_id');

        if (! $state || ! $expectedState || $state !== $expectedState) {
            return redirect('/')->with('error', 'Invalid OAuth state. Please try again.');
        }

        $organization = Organization::find($orgId);

        if (! $organization || ! request()->user()->isOwnerOf($organization)) {
            return redirect('/')->with('error', 'Organization not found or access denied.');
        }

        $redirectUri = route('social.facebook.callback');
        $tokenData = FacebookPublisher::handleCallback($code, $redirectUri);

        if (! $tokenData) {
            return redirect()->route('organizations.show', $organization)
                ->with('error', 'Failed to connect Facebook account. Please try again.');
        }

        $pages = FacebookPublisher::getUserPages($tokenData['access_token']);

        if (empty($pages)) {
            return redirect()->route('organizations.show', $organization)
                ->with('error', 'No Facebook Pages found. You need a Page to post events.');
        }

        session([
            'facebook_pages' => $pages,
            'facebook_access_token' => $tokenData['access_token'],
            'facebook_token_expires_at' => isset($tokenData['expires_in'])
                ? now()->addSeconds($tokenData['expires_in'])
                : null,
        ]);

        return redirect()->route('social.facebook.select-page', $organization);
    }

    public function facebookSelectPage(Organization $organization, Request $request)
    {
        if (! request()->user()->isOwnerOf($organization)) {
            abort(403);
        }

        $pages = session('facebook_pages', []);
        $accessToken = session('facebook_access_token');

        if (empty($pages) || ! $accessToken) {
            return redirect()->route('organizations.show', $organization)
                ->with('error', 'Session expired. Please try connecting again.');
        }

        if ($request->isMethod('post')) {
            $pageId = $request->input('page_id');
            $page = collect($pages)->firstWhere('id', $pageId);

            if (! $page) {
                return back()->with('error', 'Invalid page selection.');
            }

            $organization->facebookAccount()->create([
                'facebook_user_id' => null,
                'facebook_page_id' => $page['id'],
                'facebook_page_name' => $page['name'],
                'access_token_encrypted' => encrypt($page['access_token']),
                'token_expires_at' => session('facebook_token_expires_at'),
                'scopes_json' => ['pages_manage_posts', 'pages_read_engagement'],
                'is_active' => true,
                'created_by_user_id' => request()->user()->id,
            ]);

            session()->forget(['facebook_pages', 'facebook_access_token', 'facebook_oauth_state', 'facebook_org_id', 'facebook_token_expires_at']);

            return redirect()->route('organizations.show', $organization)
                ->with('success', 'Facebook Page connected successfully.');
        }

        return view('social.facebook.select-page', [
            'organization' => $organization,
            'pages' => $pages,
        ]);
    }

    public function facebookDisconnect(Organization $organization)
    {
        if (! request()->user()->isOwnerOf($organization)) {
            abort(403);
        }

        $organization->facebookAccount()->delete();

        return redirect()->route('organizations.show', $organization)
            ->with('success', 'Facebook account disconnected.');
    }

    public function xConnect(Organization $organization)
    {
        if (! request()->user()->isOwnerOf($organization)) {
            abort(403);
        }

        $redirectUri = route('social.x.callback');
        $state = $organization->id.'|'.Str::random(40);
        $codeVerifier = Str::random(64);

        session([
            'x_oauth_state' => $state,
            'x_org_id' => $organization->id,
            'x_code_verifier' => $codeVerifier,
        ]);

        return redirect(XPublisher::getAuthorizationUrl($redirectUri, $state));
    }

    public function xCallback(Request $request)
    {
        $state = $request->get('state');
        $code = $request->get('code');

        $expectedState = session('x_oauth_state');
        $orgId = session('x_org_id');
        $codeVerifier = session('x_code_verifier');

        if (! $state || ! $expectedState || $state !== $expectedState) {
            return redirect('/')->with('error', 'Invalid OAuth state. Please try again.');
        }

        $organization = Organization::find($orgId);

        if (! $organization || ! request()->user()->isOwnerOf($organization)) {
            return redirect('/')->with('error', 'Organization not found or access denied.');
        }

        $redirectUri = route('social.x.callback');
        $tokenData = XPublisher::handleCallback($code, $redirectUri, $codeVerifier);

        if (! $tokenData) {
            return redirect()->route('organizations.show', $organization)
                ->with('error', 'Failed to connect X account. Please try again.');
        }

        $userInfo = XPublisher::getUserInfo($tokenData['access_token']);

        if (! $userInfo) {
            return redirect()->route('organizations.show', $organization)
                ->with('error', 'Failed to get X user info. Please try again.');
        }

        $account = $organization->xAccount()->create([
            'x_user_id' => $userInfo['id'],
            'x_screen_name' => $userInfo['username'],
            'access_token_encrypted' => encrypt($tokenData['access_token']),
            'refresh_token_encrypted' => isset($tokenData['refresh_token'])
                ? encrypt($tokenData['refresh_token'])
                : null,
            'token_expires_at' => isset($tokenData['expires_in'])
                ? now()->addSeconds($tokenData['expires_in'])
                : null,
            'scopes_json' => explode(' ', 'tweet.read tweet.write users.read offline.access'),
            'is_active' => true,
            'created_by_user_id' => request()->user()->id,
        ]);

        session()->forget(['x_oauth_state', 'x_org_id', 'x_code_verifier']);

        return redirect()->route('organizations.show', $organization)
            ->with('success', 'X account connected successfully.');
    }

    public function xDisconnect(Organization $organization)
    {
        if (! request()->user()->isOwnerOf($organization)) {
            abort(403);
        }

        $organization->xAccount()->delete();

        return redirect()->route('organizations.show', $organization)
            ->with('success', 'X account disconnected.');
    }

    public function discordConnect(Organization $organization, Request $request)
    {
        if (! request()->user()->isOwnerOf($organization)) {
            abort(403);
        }

        $request->validate([
            'webhook_url' => 'required|url',
        ]);

        $validation = DiscordPublisher::connectWebhook($request->input('webhook_url'));

        if (! $validation) {
            return back()->with('error', 'Invalid Discord webhook URL. Please check and try again.');
        }

        $organization->discordAccount()->create([
            'webhook_url_encrypted' => encrypt($request->input('webhook_url')),
            'channel_name' => $validation['name'] ?? null,
            'guild_name' => $validation['guild'] ?? null,
            'is_active' => true,
            'created_by_user_id' => request()->user()->id,
        ]);

        return redirect()->route('organizations.show', $organization)
            ->with('success', 'Discord webhook connected successfully.');
    }

    public function discordDisconnect(Organization $organization)
    {
        if (! request()->user()->isOwnerOf($organization)) {
            abort(403);
        }

        $organization->discordAccount()->delete();

        return redirect()->route('organizations.show', $organization)
            ->with('success', 'Discord account disconnected.');
    }
}
