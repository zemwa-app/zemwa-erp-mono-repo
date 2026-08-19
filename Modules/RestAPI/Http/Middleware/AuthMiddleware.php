<?php

namespace Modules\RestAPI\Http\Middleware;

use Closure;
use Froiden\RestAPI\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Auth;
use Modules\RestAPI\Entities\User;
use Modules\RestAPI\Entities\UserAuth;

class AuthMiddleware
{
    public function handle($request, Closure $next)
    {

        // Do not apply this middleware to OPTIONS request
        if ($request->getMethod() !== 'OPTIONS') {
            $user = $this->resolveApiUser(auth()->user());

            if (!$user) {
                throw new UnauthorizedException('User not found auth', null, 403, 403, 2006);
            }

            if ($user->status == 'inactive') {
                $user->currentAccessToken()->delete();
                throw new UnauthorizedException('User account disabled', null, 403, 403, 2015);
            }
        }

        return $next($request);
    }

    private function resolveApiUser($user): ?User
    {
        if ($user instanceof User) {
            return $user;
        }

        if ($user instanceof UserAuth) {
            $apiUser = User::where('user_auth_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($apiUser && method_exists($user, 'currentAccessToken')) {
                $apiUser->withAccessToken($user->currentAccessToken());
                Auth::setUser($apiUser);
            }

            return $apiUser;
        }

        return null;
    }
}
