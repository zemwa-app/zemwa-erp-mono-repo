<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\RestAPI\Entities\AgentNetworkLog;
use Modules\RestAPI\Http\Requests\Agent\NetworkRequest;

class AgentNetworkController extends Controller
{
    public function store(NetworkRequest $request): JsonResponse
    {

        $user = $request->user();

        AgentNetworkLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'hour' => $request->input('hour'),
            'total_bytes_sent' => $request->input('total_bytes_sent'),
            'total_bytes_received' => $request->input('total_bytes_received'),
            'top_processes' => $request->input('top_processes'),
            'cloud_uploads_detected' => $request->input('cloud_uploads_detected'),
            'vpn_active' => $request->boolean('vpn_active'),
            'large_transfer_alert' => $request->boolean('large_transfer_alert'),
        ]);

        return response()->json(['received' => true], 201);
    }
}
