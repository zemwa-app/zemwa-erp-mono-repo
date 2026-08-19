<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\RestAPI\Entities\AgentActivityWindow;
use Modules\RestAPI\Http\Requests\Agent\ActivityWindowRequest;

class AgentActivityWindowController extends Controller
{
    public function store(ActivityWindowRequest $request): JsonResponse
    {

        $user = $request->user();
        $entries = $request->all();
        $inserted = 0;

        foreach ($entries as $entry) {
            AgentActivityWindow::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'window_start' => $entry['window_start'],
                'window_end' => $entry['window_end'],
                'keystrokes' => $entry['keystrokes'],
                'mouse_clicks' => $entry['mouse_clicks'],
                'mouse_distance' => $entry['mouse_distance'] ?? 0,
                'scroll_events' => $entry['scroll_events'] ?? 0,
                'activity_pct' => $entry['activity_pct'],
                'is_idle' => $entry['is_idle'],
            ]);
            $inserted++;
        }

        return response()->json(['inserted' => $inserted], 201);
    }
}
