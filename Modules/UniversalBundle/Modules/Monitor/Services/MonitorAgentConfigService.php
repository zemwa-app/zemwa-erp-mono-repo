<?php

namespace Modules\Monitor\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\RestAPI\Entities\AgentConfig;
use Modules\RestAPI\Entities\AgentConfigOverride;

class MonitorAgentConfigService
{
    public function __construct(
        private readonly MonitorPermissionScope $permissionScope,
    ) {
    }
    public const QUALITY_LOW = 50;

    public const QUALITY_MEDIUM = 75;

    public const QUALITY_HIGH = 90;

  /**
     * @return array<string, int>
     */
    public static function qualityOptions(): array
    {
        return [
            'low' => self::QUALITY_LOW,
            'medium' => self::QUALITY_MEDIUM,
            'high' => self::QUALITY_HIGH,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function intervalOptions(): array
    {
        return [1, 2, 5, 10, 15, 30];
    }

    /**
     * @return array<int, string>
     */
    public static function idleThresholdOptions(): array
    {
        return [5, 10, 15, 20, 30];
    }

    /**
     * @return array<string, mixed>
     */
    public function getOrgConfig(int $companyId): array
    {
        $config = AgentConfig::where('company_id', $companyId)->first();
        $defaults = AgentConfig::defaultConfig();

        return [
            'screenshot' => array_merge($defaults['screenshot'], $config?->screenshot ?? []),
            'app_tracking' => array_merge($defaults['app_tracking'], $config?->app_tracking ?? []),
            'keyboard' => array_merge($defaults['keyboard'], $config?->keyboard ?? []),
            'network' => array_merge($defaults['network'], $config?->network ?? []),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormState(array $config): array
    {
        return [
            'screenshot_enabled' => (bool) ($config['screenshot']['enabled'] ?? true),
            'screenshot_interval' => (int) ($config['screenshot']['interval_minutes'] ?? 5),
            'screenshot_quality' => $this->qualityToKey((int) ($config['screenshot']['quality'] ?? self::QUALITY_MEDIUM)),
            'screenshot_pause_on_idle' => (bool) ($config['screenshot']['pause_on_idle'] ?? true),
            'flagged_apps' => implode(',', $config['screenshot']['flagged_apps'] ?? []),
            'app_tracking_enabled' => (bool) ($config['app_tracking']['enabled'] ?? true),
            'keyboard_enabled' => (bool) ($config['keyboard']['enabled'] ?? true),
            'idle_threshold' => (int) ($config['keyboard']['idle_threshold_minutes'] ?? 10),
            'network_enabled' => (bool) ($config['network']['enabled'] ?? true),
            'large_transfer_mb' => (int) ($config['network']['large_transfer_mb'] ?? 50),
        ];
    }

    public function saveOrgConfig(int $companyId, Request $request): void
    {
        AgentConfig::updateOrCreate(
            ['company_id' => $companyId],
            $this->parseConfigPayload($request)
        );
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getOverrideRows(int $companyId): Collection
    {
        return AgentConfigOverride::query()
            ->where('company_id', $companyId)
            ->with('user:id,name')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (AgentConfigOverride $override) => [
                'id' => $override->id,
                'user_id' => $override->user_id,
                'employee_name' => $override->user?->name ?? '—',
                'summary' => $this->buildSettingsSummary($this->mergeConfigArrays(
                    AgentConfig::defaultConfig(),
                    [
                        'screenshot' => $override->screenshot ?? [],
                        'app_tracking' => $override->app_tracking ?? [],
                        'keyboard' => $override->keyboard ?? [],
                        'network' => $override->network ?? [],
                    ]
                )),
                'updated_at' => $override->updated_at?->timezone(company()->timezone)
                    ->format(company()->date_format . ' ' . company()->time_format),
            ]);
    }

    public function saveOverride(int $companyId, Request $request, ?int $overrideId = null): AgentConfigOverride
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $userId = (int) $request->input('user_id');

        $exists = User::query()
            ->where('id', $userId)
            ->where('company_id', $companyId)
            ->exists();

        abort_unless($exists, 422, __('messages.permissionDenied'));

        $duplicate = AgentConfigOverride::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->when($overrideId, fn ($q) => $q->where('id', '!=', $overrideId))
            ->exists();

        abort_if($duplicate, 422, __('monitor::app.overrideExists'));

        $payload = $this->parseConfigPayload($request);

        if ($overrideId) {
            $override = AgentConfigOverride::query()
                ->where('company_id', $companyId)
                ->where('id', $overrideId)
                ->firstOrFail();
            $override->update(array_merge(['user_id' => $userId], $payload));

            return $override;
        }

        return AgentConfigOverride::create(array_merge(
            ['company_id' => $companyId, 'user_id' => $userId],
            $payload
        ));
    }

    public function deleteOverride(int $companyId, int $overrideId): void
    {
        AgentConfigOverride::query()
            ->where('company_id', $companyId)
            ->where('id', $overrideId)
            ->delete();
    }

    public function getOverrideForForm(int $companyId, ?int $overrideId): ?AgentConfigOverride
    {
        if (!$overrideId) {
            return null;
        }

        return AgentConfigOverride::query()
            ->where('company_id', $companyId)
            ->where('id', $overrideId)
            ->firstOrFail();
    }

    /**
     * @return array<int, User>
     */
    public function getEmployeeOptions(int $companyId, ?int $excludeUserId = null): Collection
    {
        $existingOverrides = AgentConfigOverride::query()
            ->where('company_id', $companyId)
            ->when($excludeUserId, fn ($q) => $q->where('user_id', '!=', $excludeUserId))
            ->pluck('user_id');

        return $this->permissionScope
            ->scopedEmployeeQuery($companyId)
            ->when($existingOverrides->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $existingOverrides))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return array{screenshot: array, app_tracking: array, keyboard: array, network: array}
     */
    public function parseConfigPayload(Request $request): array
    {
        $flaggedApps = collect(preg_split('/\s*,\s*/', (string) $request->input('flagged_apps', ''), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($app) => trim($app))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $qualityKey = $request->input('screenshot_quality', 'medium');
        $qualityMap = self::qualityOptions();

        return [
            'screenshot' => [
                'enabled' => $request->boolean('screenshot_enabled'),
                'interval_minutes' => (int) $request->input('screenshot_interval', 5),
                'quality' => $qualityMap[$qualityKey] ?? self::QUALITY_MEDIUM,
                'pause_on_idle' => $request->boolean('screenshot_pause_on_idle'),
                'flagged_apps' => $flaggedApps,
            ],
            'app_tracking' => [
                'enabled' => $request->boolean('app_tracking_enabled'),
                'poll_seconds' => 5,
            ],
            'keyboard' => [
                'enabled' => $request->boolean('keyboard_enabled'),
                'idle_threshold_minutes' => (int) $request->input('idle_threshold', 10),
            ],
            'network' => [
                'enabled' => $request->boolean('network_enabled'),
                'large_transfer_mb' => (int) $request->input('large_transfer_mb', 50),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function buildSettingsSummary(array $config): string
    {
        $parts = [];

        $screenshot = $config['screenshot'] ?? [];
        if (!empty($screenshot['enabled'])) {
            $parts[] = __('monitor::app.screenshotCapture') . ': ' . ($screenshot['interval_minutes'] ?? 5) . ' min';
        } else {
            $parts[] = __('monitor::app.screenshotCapture') . ': ' . __('app.disabled');
        }

        if (!empty($config['app_tracking']['enabled'])) {
            $parts[] = __('monitor::app.appTracking') . ': ' . __('app.on');
        }

        if (!empty($config['keyboard']['enabled'])) {
            $parts[] = __('monitor::app.idleThreshold') . ': ' . ($config['keyboard']['idle_threshold_minutes'] ?? 10) . ' min';
        }

        if (!empty($config['network']['enabled'])) {
            $parts[] = __('monitor::app.networkMonitoring') . ': ' . ($config['network']['large_transfer_mb'] ?? 50) . ' MB';
        }

        $flagged = $screenshot['flagged_apps'] ?? [];
        if (!empty($flagged)) {
            $parts[] = __('monitor::app.flaggedApps') . ': ' . implode(', ', array_slice($flagged, 0, 3))
                . (count($flagged) > 3 ? '…' : '');
        }

        return implode(' · ', $parts);
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    public function mergeConfigArrays(array $base, array $override): array
    {
        foreach (['screenshot', 'app_tracking', 'keyboard', 'network'] as $section) {
            if (!empty($override[$section]) && is_array($override[$section])) {
                $base[$section] = array_merge($base[$section] ?? [], $override[$section]);
            }
        }

        return $base;
    }

    public function qualityToKey(int $quality): string
    {
        return match (true) {
            $quality <= 55 => 'low',
            $quality <= 80 => 'medium',
            default => 'high',
        };
    }
}
