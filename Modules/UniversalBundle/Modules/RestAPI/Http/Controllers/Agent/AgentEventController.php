<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\RestAPI\Entities\AgentEvent;
use Modules\RestAPI\Http\Requests\Agent\EventRequest;

class AgentEventController extends Controller
{
    const VALID_EVENT_TYPES = [
        'tamper_detected',
        'pause_started',
        'pause_ended',
        'session_started',
        'session_ended',
        'agent_error',
        'usb_connected',
        'large_upload_detected',
        'cloud_upload_detected',
        'idle_period',
    ];

    public function store(EventRequest $request): JsonResponse
    {

        $user = $request->user();
        $events = $request->all();
        $inserted = 0;

        foreach ($events as $event) {
            AgentEvent::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'event_type' => $event['event_type'],
                'payload' => $event['payload'] ?? null,
            ]);
            $inserted++;
        }

        return response()->json(['inserted' => $inserted], 201);
    }
}
