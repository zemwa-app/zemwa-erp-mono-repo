<?php

namespace Modules\RestAPI\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AgentVersionMiddleware
{
    const MINIMUM_SUPPORTED_VERSION = '1.0.0';

    public function handle(Request $request, Closure $next)
    {
        $agentVersion = $request->header('X-Agent-Version');

        if ($agentVersion) {
            if (version_compare($agentVersion, self::MINIMUM_SUPPORTED_VERSION, '<')) {
                return response()->json([
                    'message' => 'Agent version too old. Please update.',
                    'minimum_version' => self::MINIMUM_SUPPORTED_VERSION,
                ], 426);
            }
        }

        return $next($request);
    }
}
