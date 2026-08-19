<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\RestAPI\Entities\AgentConfig;
use Modules\RestAPI\Entities\AgentConfigOverride;
use Modules\RestAPI\Entities\AgentProductivityCategory;

class AgentConfigController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $config = AgentConfig::where('company_id', $user->company_id)->first();

        $defaults = AgentConfig::defaultConfig();
        $merged = [
            'screenshot' => array_merge($defaults['screenshot'], $config?->screenshot ?? []),
            'app_tracking' => array_merge($defaults['app_tracking'], $config?->app_tracking ?? []),
            'keyboard' => array_merge($defaults['keyboard'], $config?->keyboard ?? []),
            'network' => array_merge($defaults['network'], $config?->network ?? []),
        ];

        $override = AgentConfigOverride::where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->first();

        if ($override) {
            foreach (['screenshot', 'app_tracking', 'keyboard', 'network'] as $section) {
                if (!empty($override->{$section}) && is_array($override->{$section})) {
                    $merged[$section] = array_merge($merged[$section], $override->{$section});
                }
            }
        }

        return response()->json($merged);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $config = AgentConfig::where('company_id', $user->company_id)->first();

        if (!$config) {
            $config = new AgentConfig();
            $config->company_id = $user->company_id;
        }

        $config->screenshot = $request->input('screenshot', AgentConfig::defaultConfig()['screenshot']);
        $config->app_tracking = $request->input('app_tracking', AgentConfig::defaultConfig()['app_tracking']);
        $config->keyboard = $request->input('keyboard', AgentConfig::defaultConfig()['keyboard']);
        $config->network = $request->input('network', AgentConfig::defaultConfig()['network']);
        $config->save();

        return response()->json([
            'screenshot' => $config->screenshot,
            'app_tracking' => $config->app_tracking,
            'keyboard' => $config->keyboard,
            'network' => $config->network,
        ], 200);
    }

    public function productivityCategories(Request $request): JsonResponse
    {
        $user = $request->user();

        $overrides = AgentProductivityCategory::where('company_id', $user->company_id)
            ->orderBy('pattern')
            ->get()
            ->map(fn ($item) => [
                'pattern' => $item->pattern,
                'category' => $item->category,
            ]);

        return response()->json(['overrides' => $overrides]);
    }

    public function seedProductivityCategories(Request $request): JsonResponse
    {
        $request->validate([
            'overrides' => 'required|array',
            'overrides.*.pattern' => 'required|string|max:255',
            'overrides.*.category' => 'required|string|in:productive,unproductive,neutral',
        ]);

        $user = $request->user();
        $companyId = $user->company_id;

        if (AgentProductivityCategory::where('company_id', $companyId)->exists()) {
            $overrides = AgentProductivityCategory::where('company_id', $companyId)
                ->orderBy('pattern')
                ->get()
                ->map(fn ($item) => [
                    'pattern' => $item->pattern,
                    'category' => $item->category,
                ]);

            return response()->json(['overrides' => $overrides, 'message' => 'already configured']);
        }

        foreach ($request->input('overrides') as $override) {
            AgentProductivityCategory::create([
                'company_id' => $companyId,
                'pattern' => $override['pattern'],
                'category' => $override['category'],
            ]);
        }

        $overrides = AgentProductivityCategory::where('company_id', $companyId)
            ->orderBy('pattern')
            ->get()
            ->map(fn ($item) => [
                'pattern' => $item->pattern,
                'category' => $item->category,
            ]);

        return response()->json(['overrides' => $overrides], 201);
    }
}
