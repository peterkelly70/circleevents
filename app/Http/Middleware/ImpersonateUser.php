<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ImpersonateUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->is_admin) {
            return $next($request);
        }

        $impersonatingUserId = $request->session()->get('impersonating_user_id');

        if ($impersonatingUserId) {
            $impersonatingUser = User::find($impersonatingUserId);

            if ($impersonatingUser) {
                Auth::setUser($impersonatingUser);
            }
        }

        return $next($request);
    }
}
