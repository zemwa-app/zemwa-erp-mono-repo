<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\RestAPI\Entities\AgentConfig;
use Modules\RestAPI\Entities\AgentProductivityCategory;

class AdminConfigController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $config = AgentConfig::where('company_id', $user->company_id)->first();

        if (!$config) {
            return response()->json(AgentConfig::defaultConfig());
        }

        return response()->json([
            'screenshot' => $config->screenshot ?? AgentConfig::defaultConfig()['screenshot'],
            'app_tracking' => $config->app_tracking ?? AgentConfig::defaultConfig()['app_tracking'],
            'keyboard' => $config->keyboard ?? AgentConfig::defaultConfig()['keyboard'],
            'network' => $config->network ?? AgentConfig::defaultConfig()['network'],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'screenshot' => 'nullable|array',
            'screenshot.enabled' => 'nullable|boolean',
            'screenshot.interval_minutes' => 'nullable|integer|min:1|max:60',
            'screenshot.quality' => 'nullable|integer|min:10|max:100',
            'screenshot.pause_on_idle' => 'nullable|boolean',
            'screenshot.flagged_apps' => 'nullable|array',
            'app_tracking' => 'nullable|array',
            'app_tracking.enabled' => 'nullable|boolean',
            'app_tracking.poll_seconds' => 'nullable|integer|min:1|max:60',
            'keyboard' => 'nullable|array',
            'keyboard.enabled' => 'nullable|boolean',
            'keyboard.idle_threshold_minutes' => 'nullable|integer|min:1|max:60',
            'network' => 'nullable|array',
            'network.enabled' => 'nullable|boolean',
            'network.large_transfer_mb' => 'nullable|integer|min:1|max:1000',
        ]);

        $user = $request->user();

        $config = AgentConfig::updateOrCreate(
            ['company_id' => $user->company_id],
            [
                'screenshot' => $request->input('screenshot'),
                'app_tracking' => $request->input('app_tracking'),
                'keyboard' => $request->input('keyboard'),
                'network' => $request->input('network'),
            ]
        );

        return response()->json([
            'message' => 'Config updated. Agents will refresh within 24 hours.',
        ]);
    }

    public function productivityCategories(Request $request): JsonResponse
    {
        $user = $request->user();

        $overrides = AgentProductivityCategory::where('company_id', $user->company_id)
            ->get()
            ->map(function ($item) {
                $data = [
                    'pattern' => $item->pattern,
                    'category' => $item->category,
                ];
                if ($item->note) {
                    $data['note'] = $item->note;
                }
                return $data;
            });

        return response()->json(['overrides' => $overrides]);
    }

    public function updateProductivityCategories(Request $request): JsonResponse
    {
        $request->validate([
            'overrides' => 'required|array',
            'overrides.*.pattern' => 'required|string|max:255',
            'overrides.*.category' => 'required|string|in:productive,unproductive,neutral',
            'overrides.*.note' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $companyId = $user->company_id;

        AgentProductivityCategory::where('company_id', $companyId)->delete();

        foreach ($request->input('overrides') as $override) {
            AgentProductivityCategory::create([
                'company_id' => $companyId,
                'pattern' => $override['pattern'],
                'category' => $override['category'],
                'note' => $override['note'] ?? null,
            ]);
        }

        return response()->json(['message' => 'Productivity categories updated.']);
    }
}
