<?php

$addOnOf = 'worksuite-saas-new';

return [
    'name' => 'Monitor',
    'verification_required' => true,
    'envato_item_id' => 64279781,
    'parent_envato_id' => 23263417,
    'parent_min_version' => '6.0.10',
    'script_name' => $addOnOf.'-monitor-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Monitor\Entities\MonitorSetting::class,


    'compliance' => [
        'unproductive_threshold_pct' => (int) env('MONITOR_UNPRODUCTIVE_THRESHOLD_PCT', 15),
    ],

    'productivity' => [
        'subcategories' => [
            'productive' => ['development', 'design', 'communication', 'productivity', 'research', 'finance', 'devops'],
            'neutral' => ['news', 'reference', 'professional', 'system', 'other'],
            'unproductive' => ['social_media', 'entertainment', 'shopping', 'gaming', 'other'],
        ],
    ],

    'billing' => [
        'min_active_days' => (int) env('MONITOR_BILLING_MIN_ACTIVE_DAYS', 3),
        'min_tracked_seconds' => (int) env('MONITOR_BILLING_MIN_TRACKED_SECONDS', 1800),
    ],

    'installer' => [
        'server_url' => config('app.url'),
        'version' => env('MONITOR_AGENT_VERSION', '1.0.0'),
        'released_at' => env('MONITOR_AGENT_RELEASED_AT', '2026-05-13'),
        'max_upload_mb' => (int) env('MONITOR_AGENT_MAX_UPLOAD_MB', 500),
        'storage_path' => 'monitor/installers',
        'platforms' => [
            'windows' => [
                'filename' => env('MONITOR_AGENT_WINDOWS_INSTALLER', 'MonitoringAgentSetup-1.0.0.exe'),
                'extension' => 'exe',
                'mime' => 'application/octet-stream',
                'label' => 'Windows Installer (.exe)',
                'icon' => 'fab fa-windows',
                'icon_bg' => 'bg-indigo-50',
                'icon_color' => 'text-indigo-600',
            ],
            'mac' => [
                'filename' => env('MONITOR_AGENT_MAC_INSTALLER', 'MonitoringAgent-1.0.0.dmg'),
                'extension' => 'dmg',
                'mime' => 'application/octet-stream',
                'label' => 'macOS Installer (.dmg)',
                'icon' => 'fab fa-apple',
                'icon_bg' => 'bg-slate-100',
                'icon_color' => 'text-slate-700',
            ],
            'ubuntu' => [
                'filename' => env('MONITOR_AGENT_UBUNTU_INSTALLER', 'MonitoringAgentSetup-1.0.0.deb'),
                'extension' => 'deb',
                'mime' => 'application/vnd.debian.binary-package',
                'label' => 'Ubuntu Installer (.deb)',
                'icon' => 'fab fa-ubuntu',
                'icon_bg' => 'bg-orange-50',
                'icon_color' => 'text-orange-600',
            ],
        ],
    ],
];
