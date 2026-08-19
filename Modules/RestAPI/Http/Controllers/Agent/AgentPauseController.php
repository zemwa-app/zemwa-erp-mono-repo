<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\RestAPI\Entities\AgentEvent;
use Modules\RestAPI\Entities\AgentPause;
use Modules\RestAPI\Http\Requests\Agent\PauseRequest;
use Modules\RestAPI\Http\Requests\Agent\ResumeRequest;

class AgentPauseController extends Controller
{
    public function pause(PauseRequest $request): JsonResponse
    {

        $user = $request->user();
        $startedAt = Carbon::parse($request->input('started_at'));
        $durationMinutes = $request->input('duration_minutes');
        $endsAt = $startedAt->copy()->addMinutes($durationMinutes);

        $pause = AgentPause::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'reason' => $request->input('reason'),
            'duration_minutes' => $durationMinutes,
            'started_at' => $startedAt,
            'ends_at' => $endsAt,
        ]);

        AgentEvent::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'event_type' => 'pause_started',
            'payload' => [
                'reason' => $request->input('reason'),
                'duration_minutes' => $durationMinutes,
                'started_at' => $startedAt->toIso8601String(),
                'ends_at' => $endsAt->toIso8601String(),
            ],
        ]);

        return response()->json([
            'pause_id' => $pause->id,
            'ends_at' => $endsAt->toIso8601String(),
        ]);
    }

    public function resume(ResumeRequest $request): JsonResponse
    {

        $user = $request->user();
        $resumedAt = Carbon::parse($request->input('resumed_at'));

        $activePause = AgentPause::where('user_id', $user->id)
            ->whereNull('resumed_at')
            ->latest('started_at')
            ->first();

        if ($activePause) {
            $activePause->update(['resumed_at' => $resumedAt]);
        }

        AgentEvent::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'event_type' => 'pause_ended',
            'payload' => [
                'resumed_at' => $resumedAt->toIso8601String(),
                'pause_id' => $activePause?->id,
            ],
        ]);

        return response()->json(['message' => 'Monitoring resumed']);
    }
}
