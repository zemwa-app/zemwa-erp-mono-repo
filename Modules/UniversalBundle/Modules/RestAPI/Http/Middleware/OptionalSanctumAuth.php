<?php

namespace Modules\RestAPI\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OptionalSanctumAuth
{
    /**
     * Authenticate the request when a Bearer token is present.
     * Does not reject guest requests (unlike auth:sanctum).
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->bearerToken()) {
            $user = Auth::guard('sanctum')->user();

            if ($user) {
                Auth::setUser($user);
            }
        }

        return $next($request);
    }
}
