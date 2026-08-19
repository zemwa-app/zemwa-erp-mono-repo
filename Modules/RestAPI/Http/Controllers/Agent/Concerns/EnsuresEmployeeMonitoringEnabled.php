<?php

namespace Modules\RestAPI\Http\Controllers\Agent\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Monitor\Support\EmployeeMonitoring;

trait EnsuresEmployeeMonitoringEnabled
{
    protected function ensureMonitoringEnabled(Request $request): ?JsonResponse
    {
        if (EmployeeMonitoring::isEnabledForUser($request->user())) {
            return null;
        }

        return response()->json([
            'monitoring_enabled' => false,
            'message' => __('monitor::app.monitoringDisabledForEmployee'),
        ], 403);
    }
}
