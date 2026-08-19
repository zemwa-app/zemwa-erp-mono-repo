<?php

namespace Modules\Monitor\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class EmployeeMonitoring
{
    public static function moduleActive(): bool
    {
        return function_exists('module_enabled') && module_enabled('Monitor');
    }

    public static function isEnabledForUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (!self::moduleActive()) {
            return true;
        }

        $detail = $user->employeeDetail;

        if (!$detail) {
            return false;
        }

        return (bool) ($detail->monitoring_enabled ?? false);
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public static function scopeEnabledEmployees(Builder $query): Builder
    {
        if (!self::moduleActive()) {
            return $query;
        }

        return $query->whereHas('employeeDetail', function (Builder $detail) {
            $detail->where('monitoring_enabled', true);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public static function disabledConfigPayload(): array
    {
        return [
            'enabled' => false,
            'interval_minutes' => 0,
            'quality' => 0,
            'pause_on_idle' => true,
            'flagged_apps' => [],
            'poll_seconds' => 0,
            'idle_threshold_minutes' => 0,
            'large_transfer_mb' => 0,
        ];
    }
}
