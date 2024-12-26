<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        // Get the authenticated user
        $user = Auth::user();

        // Check if the user has one of the required roles
        if (empty($roles) || in_array($user->role, $roles)) {
            return $next($request);
        }

        // If user doesn't have the required role, redirect or return unauthorized
        return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
    }
}
