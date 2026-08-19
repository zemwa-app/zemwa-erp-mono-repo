<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\RestAPI\Entities\AgentHeartbeat;
use Modules\RestAPI\Http\Requests\Agent\HeartbeatRequest;

class AgentHeartbeatController extends Controller
{
    public function store(HeartbeatRequest $request): JsonResponse
    {

        $user = $request->user();

        AgentHeartbeat::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'agent_version' => $request->input('agent_version'),
            'os' => $request->input('os'),
            'os_version' => $request->input('os_version'),
            'hostname' => $request->input('hostname'),
            'is_idle' => $request->boolean('is_idle'),
            'is_paused' => $request->boolean('is_paused'),
            'active_app' => $request->input('active_app'),
            'pending_sync_count' => $request->input('pending_sync_count', 0),
            'event_timestamp' => $request->input('timestamp'),
        ]);

        $response = ['received' => true];

        $agentVersion = $request->header('X-Agent-Version', $request->input('agent_version'));
        if ($agentVersion && version_compare($agentVersion, '1.0.0', '<')) {
            $response['update_available'] = true;
        }

        return response()->json($response);
    }
}
