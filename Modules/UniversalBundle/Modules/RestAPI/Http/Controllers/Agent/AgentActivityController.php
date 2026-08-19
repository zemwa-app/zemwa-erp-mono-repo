<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\RestAPI\Entities\AgentActivityLog;
use Modules\RestAPI\Http\Requests\Agent\ActivityRequest;

class AgentActivityController extends Controller
{
    public function store(ActivityRequest $request): JsonResponse
    {

        $user = $request->user();
        $entries = $request->all();
        $inserted = 0;

        foreach ($entries as $entry) {
            AgentActivityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'app_name' => $entry['app_name'],
                'process_name' => $entry['process_name'] ?? null,
                'window_title' => $entry['window_title'] ?? null,
                'url' => $entry['url'] ?? null,
                'category' => $entry['category'] ?? 'neutral',
                'subcategory' => $entry['subcategory'] ?? null,
                'started_at' => $entry['started_at'],
                'ended_at' => $entry['ended_at'] ?? null,
                'duration_seconds' => $entry['duration_seconds'],
            ]);
            $inserted++;
        }

        return response()->json(['inserted' => $inserted], 201);
    }
}
