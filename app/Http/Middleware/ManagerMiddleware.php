<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || Auth::user()->role !== 'Manager') {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied. Manager privileges required.');
        }

        return $next($request);
    }
}
