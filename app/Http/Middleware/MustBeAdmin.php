<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redirect;

class MustBeAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            return $request->expectsJson()
                ? abort(403, "You're not allowed to access that location.")
                : Redirect::route('home');
        }

        return $next($request);
    }
}
