<?php

namespace Modules\RestAPI\Entities;

class ProjectStatus extends \App\Models\ProjectStatusSetting
{
    protected $table = 'project_status_settings';
    protected $default = [
        'id',
        'status_name',
        'color',
        'status',
        'default_status',
    ];

    protected $filterable = [
        'id',
        'status_name',
    ];

    public static function boot()
    {
        parent::boot();
    }
}
