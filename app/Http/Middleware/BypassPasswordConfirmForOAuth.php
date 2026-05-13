<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bypass password confirmation for OAuth users who don't have a local password.
 * These users logged in via Google and never set a password.
 */
class BypassPasswordConfirmForOAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user logged in via Google (has google_id) and has no local password,
        // auto-confirm the password by setting the session timestamp.
        if ($user && $user->google_id && empty($user->password)) {
            session(['auth.password_confirmed_at' => time()]);
        }

        return $next($request);
    }
}
