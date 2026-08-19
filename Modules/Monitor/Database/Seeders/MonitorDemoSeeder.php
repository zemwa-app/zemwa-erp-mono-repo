<?php

namespace Modules\Monitor\Database\Seeders;

use App\Models\Company;
use App\Models\EmployeeDetails;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Monitor\Entities\AgentDailySummary;
use Modules\Monitor\Entities\AgentSession;
use Modules\Monitor\Entities\MonitorSetting;
use Modules\RestAPI\Entities\AgentHeartbeat;

class MonitorDemoSeeder extends Seeder
{
    private const DAYS = 14;

    private const WORK_START_HOUR = 9;

    private const WORK_END_HOUR = 18;

    private const WINDOW_MINUTES = 10;

    /**
     * @var array<int, array{app_name: string, process_name: string, window_title: string, url: ?string, category: string, subcategory: string}>
     */
    private array $activities = [
        [
            'app_name' => 'Visual Studio Code',
            'process_name' => 'code',
            'window_title' => 'worksuite-new — index.php',
            'url' => null,
            'category' => 'productive',
            'subcategory' => 'development',
        ],
        [
            'app_name' => 'Google Chrome',
            'process_name' => 'chrome',
            'window_title' => 'GitHub - Pull requests',
            'url' => 'https://github.com/froiden/worksuite',
            'category' => 'productive',
            'subcategory' => 'development',
        ],
        [
            'app_name' => 'Slack',
            'process_name' => 'slack',
            'window_title' => 'Slack | engineering',
            'url' => null,
            'category' => 'productive',
            'subcategory' => 'communication',
        ],
        [
            'app_name' => 'Google Chrome',
            'process_name' => 'chrome',
            'window_title' => 'Notion – Sprint board',
            'url' => 'https://www.notion.so/sprint',
            'category' => 'productive',
            'subcategory' => 'productivity',
        ],
        [
            'app_name' => 'Figma',
            'process_name' => 'figma',
            'window_title' => 'Monitor Dashboard – Figma',
            'url' => null,
            'category' => 'productive',
            'subcategory' => 'design',
        ],
        [
            'app_name' => 'Terminal',
            'process_name' => 'terminal',
            'window_title' => 'zsh — monitor',
            'url' => null,
            'category' => 'productive',
            'subcategory' => 'development',
        ],
        [
            'app_name' => 'Google Chrome',
            'process_name' => 'chrome',
            'window_title' => 'Stack Overflow',
            'url' => 'https://stackoverflow.com/questions',
            'category' => 'productive',
            'subcategory' => 'development',
        ],
        [
            'app_name' => 'Google Chrome',
            'process_name' => 'chrome',
            'window_title' => 'Gmail',
            'url' => 'https://mail.google.com',
            'category' => 'neutral',
            'subcategory' => 'communication',
        ],
        [
            'app_name' => 'Google Chrome',
            'process_name' => 'chrome',
            'window_title' => 'YouTube',
            'url' => 'https://www.youtube.com',
            'category' => 'unproductive',
            'subcategory' => 'entertainment',
        ],
        [
            'app_name' => 'Spotify',
            'process_name' => 'spotify',
            'window_title' => 'Spotify',
            'url' => null,
            'category' => 'unproductive',
            'subcategory' => 'entertainment',
        ],
    ];

    public function run(): void
    {
        if (! app()->environment('demo')) {
            $this->command?->warn('MonitorDemoSeeder skipped: APP_ENV is not demo.');

            return;
        }

        config(['app.seeding' => true]);

        $companies = Company::query()->get();

        if ($companies->isEmpty()) {
            $this->command?->warn('MonitorDemoSeeder skipped: no companies found.');
            config(['app.seeding' => false]);

            return;
        }

        foreach ($companies as $company) {
            MonitorSetting::addModuleSetting($company);
            $this->seedCompany($company);
        }

        config(['app.seeding' => false]);

        $this->command?->info('Monitor demo data seeded.');
    }

    private function seedCompany(Company $company): void
    {
        $employees = User::query()
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->where('name', 'employee'))
            ->with('employeeDetail')
            ->get();

        if ($employees->isEmpty()) {
            return;
        }

        $userIds = $employees->pluck('id');

        EmployeeDetails::query()
            ->whereIn('user_id', $userIds)
            ->update(['monitoring_enabled' => true]);

        $this->clearExistingDemoData($company->id, $userIds->all());

        $timezone = $company->timezone ?: config('app.timezone', 'UTC');
        $today = Carbon::now($timezone)->startOfDay();

        foreach ($employees as $index => $employee) {
            $this->seedEmployee($company, $employee, $index, $today, $timezone);
        }
    }

    /**
     * @param  array<int, int>  $userIds
     */
    private function clearExistingDemoData(int $companyId, array $userIds): void
    {
        DB::table('agent_activity_logs')->where('company_id', $companyId)->whereIn('user_id', $userIds)->delete();
        DB::table('agent_activity_windows')->where('company_id', $companyId)->whereIn('user_id', $userIds)->delete();
        DB::table('agent_network_logs')->where('company_id', $companyId)->whereIn('user_id', $userIds)->delete();
        DB::table('agent_heartbeats')->where('company_id', $companyId)->whereIn('user_id', $userIds)->delete();
        DB::table('agent_daily_summaries')->where('company_id', $companyId)->whereIn('user_id', $userIds)->delete();
        DB::table('agent_sessions')->where('company_id', $companyId)->whereIn('user_id', $userIds)->delete();
    }

    private function seedEmployee(
        Company $company,
        User $employee,
        int $index,
        Carbon $today,
        string $timezone
    ): void {
        $profile = $this->employeeProfile($index);
        $startDate = $today->copy()->subDays(self::DAYS - 1);

        $activityRows = [];
        $windowRows = [];
        $networkRows = [];
        $summaryRows = [];
        $now = now();

        foreach (CarbonPeriod::create($startDate, $today) as $date) {
            /** @var Carbon $date */
            if ($date->isWeekend()) {
                continue;
            }

            $daySeed = crc32($employee->id . '|' . $date->format('Y-m-d'));
            $activeSeconds = 0;
            $idleSeconds = 0;
            $activityPctSum = 0.0;
            $activityPctCount = 0;

            $cursor = $date->copy()->setTimezone($timezone)->setTime(self::WORK_START_HOUR, 0);
            $dayEnd = $date->copy()->setTimezone($timezone)->setTime(self::WORK_END_HOUR, 0);

            while ($cursor->lt($dayEnd)) {
                $windowEnd = $cursor->copy()->addMinutes(self::WINDOW_MINUTES);
                $isIdle = (($daySeed + (int) $cursor->format('Hi')) % 10) === 0;
                $activityPct = $isIdle
                    ? round(mt_rand(0, 12) + ($profile['idle_bias'] * 5), 2)
                    : round(mt_rand(55, 98) - ($profile['idle_bias'] * 10), 2);

                $windowRows[] = [
                    'company_id' => $company->id,
                    'user_id' => $employee->id,
                    'window_start' => $cursor->copy()->utc(),
                    'window_end' => $windowEnd->copy()->utc(),
                    'keystrokes' => $isIdle ? mt_rand(0, 20) : mt_rand(80, 420),
                    'mouse_clicks' => $isIdle ? mt_rand(0, 8) : mt_rand(20, 120),
                    'mouse_distance' => $isIdle ? mt_rand(0, 200) : mt_rand(400, 4000),
                    'scroll_events' => $isIdle ? mt_rand(0, 5) : mt_rand(10, 80),
                    'activity_pct' => max(0, min(100, $activityPct)),
                    'is_idle' => $isIdle,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $seconds = self::WINDOW_MINUTES * 60;

                if ($isIdle) {
                    $idleSeconds += $seconds;
                } else {
                    $activeSeconds += $seconds;
                    $activityPctSum += $activityPct;
                    $activityPctCount++;

                    $activity = $this->pickActivity($daySeed, (int) $cursor->format('Hi'), $profile['unproductive_chance']);
                    $duration = mt_rand(120, $seconds);

                    $activityRows[] = [
                        'company_id' => $company->id,
                        'user_id' => $employee->id,
                        'app_name' => $activity['app_name'],
                        'process_name' => $activity['process_name'],
                        'window_title' => $activity['window_title'],
                        'url' => $activity['url'],
                        'category' => $activity['category'],
                        'subcategory' => $activity['subcategory'],
                        'classified_at' => $windowEnd->copy()->utc(),
                        'started_at' => $cursor->copy()->utc(),
                        'ended_at' => $cursor->copy()->addSeconds($duration)->utc(),
                        'duration_seconds' => $duration,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (((int) $cursor->format('i')) === 0) {
                    $networkRows[] = [
                        'company_id' => $company->id,
                        'user_id' => $employee->id,
                        'hour' => $cursor->copy()->utc(),
                        'total_bytes_sent' => mt_rand(50_000, 2_000_000),
                        'total_bytes_received' => mt_rand(200_000, 8_000_000),
                        'top_processes' => json_encode([
                            ['name' => 'chrome', 'bytes' => mt_rand(100_000, 1_000_000)],
                            ['name' => 'code', 'bytes' => mt_rand(20_000, 200_000)],
                        ]),
                        'cloud_uploads_detected' => json_encode([]),
                        'vpn_active' => false,
                        'large_transfer_alert' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                $cursor = $windowEnd;
            }

            $summaryRows[] = [
                'company_id' => $company->id,
                'user_id' => $employee->id,
                'date' => $date->format('Y-m-d'),
                'avg_activity_pct' => $activityPctCount > 0 ? round($activityPctSum / $activityPctCount, 2) : 0,
                'active_seconds' => $activeSeconds,
                'idle_seconds' => $idleSeconds,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->insertInChunks('agent_activity_windows', $windowRows);
        $this->insertInChunks('agent_activity_logs', $activityRows);
        $this->insertInChunks('agent_network_logs', $networkRows);
        $this->upsertDailySummaries($summaryRows);
        $this->seedLivePresence($company, $employee, $profile, $timezone);
    }

    /**
     * @return array{idle_bias: float, unproductive_chance: int, status: string, active_app: string}
     */
    private function employeeProfile(int $index): array
    {
        $profiles = [
            [
                'idle_bias' => 0.2,
                'unproductive_chance' => 8,
                'status' => 'online',
                'active_app' => 'Visual Studio Code',
            ],
            [
                'idle_bias' => 0.5,
                'unproductive_chance' => 18,
                'status' => 'idle',
                'active_app' => 'Google Chrome',
            ],
            [
                'idle_bias' => 0.3,
                'unproductive_chance' => 12,
                'status' => 'online',
                'active_app' => 'Slack',
            ],
            [
                'idle_bias' => 0.8,
                'unproductive_chance' => 28,
                'status' => 'paused',
                'active_app' => 'Spotify',
            ],
            [
                'idle_bias' => 0.4,
                'unproductive_chance' => 15,
                'status' => 'offline',
                'active_app' => 'Terminal',
            ],
        ];

        return $profiles[$index % count($profiles)];
    }

    /**
     * @param  array{idle_bias: float, unproductive_chance: int, status: string, active_app: string}  $profile
     * @return array{app_name: string, process_name: string, window_title: string, url: ?string, category: string, subcategory: string}
     */
    private function pickActivity(int $daySeed, int $minuteKey, int $unproductiveChance): array
    {
        $roll = ($daySeed + $minuteKey) % 100;

        if ($roll < $unproductiveChance) {
            $pool = array_values(array_filter(
                $this->activities,
                fn ($activity) => $activity['category'] === 'unproductive'
            ));
        } elseif ($roll < $unproductiveChance + 20) {
            $pool = array_values(array_filter(
                $this->activities,
                fn ($activity) => $activity['category'] === 'neutral'
            ));
        } else {
            $pool = array_values(array_filter(
                $this->activities,
                fn ($activity) => $activity['category'] === 'productive'
            ));
        }

        return $pool[($daySeed + $minuteKey) % count($pool)];
    }

    /**
     * @param  array{idle_bias: float, unproductive_chance: int, status: string, active_app: string}  $profile
     */
    private function seedLivePresence(Company $company, User $employee, array $profile, string $timezone): void
    {
        $status = $profile['status'];
        $activeApp = $profile['active_app'];

        $createdAt = match ($status) {
            'online', 'idle', 'paused' => Carbon::now($timezone)->subSeconds(mt_rand(10, 90))->utc(),
            default => Carbon::now($timezone)->subHours(mt_rand(3, 18))->utc(),
        };

        AgentHeartbeat::query()->create([
            'company_id' => $company->id,
            'user_id' => $employee->id,
            'agent_version' => '1.2.' . ($employee->id % 5),
            'os' => $employee->id % 2 === 0 ? 'macOS' : 'Windows',
            'os_version' => $employee->id % 2 === 0 ? '15.0' : '11',
            'hostname' => 'demo-laptop-' . $employee->id,
            'is_idle' => $status === 'idle',
            'is_paused' => $status === 'paused',
            'active_app' => $activeApp,
            'pending_sync_count' => mt_rand(0, 3),
            'event_timestamp' => $createdAt,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        AgentSession::query()->updateOrCreate(
            ['user_id' => $employee->id],
            [
                'company_id' => $company->id,
                'is_online' => in_array($status, ['online', 'idle'], true),
                'last_seen_at' => $createdAt,
                'active_app' => $activeApp,
            ]
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function insertInChunks(string $table, array $rows, int $chunkSize = 500): void
    {
        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function upsertDailySummaries(array $rows): void
    {
        foreach ($rows as $row) {
            AgentDailySummary::query()->updateOrCreate(
                [
                    'user_id' => $row['user_id'],
                    'date' => $row['date'],
                ],
                [
                    'company_id' => $row['company_id'],
                    'avg_activity_pct' => $row['avg_activity_pct'],
                    'active_seconds' => $row['active_seconds'],
                    'idle_seconds' => $row['idle_seconds'],
                ]
            );
        }
    }
}
