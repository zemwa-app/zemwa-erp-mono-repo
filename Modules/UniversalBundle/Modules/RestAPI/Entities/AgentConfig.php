<?php

namespace Modules\RestAPI\Entities;

use Illuminate\Database\Eloquent\Model;

class AgentConfig extends Model
{
    protected $table = 'agent_configs';

    protected $fillable = [
        'company_id',
        'screenshot',
        'app_tracking',
        'keyboard',
        'network',
    ];

    protected $casts = [
        'screenshot' => 'array',
        'app_tracking' => 'array',
        'keyboard' => 'array',
        'network' => 'array',
    ];

    public static function defaultConfig(): array
    {
        return [
            'screenshot' => [
                'enabled' => true,
                'interval_minutes' => 5,
                'quality' => 75,
                'pause_on_idle' => true,
                'flagged_apps' => [],
            ],
            'app_tracking' => [
                'enabled' => true,
                'poll_seconds' => 5,
            ],
            'keyboard' => [
                'enabled' => true,
                'idle_threshold_minutes' => 10,
            ],
            'network' => [
                'enabled' => true,
                'large_transfer_mb' => 50,
            ],
        ];
    }
}
