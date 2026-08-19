<?php

namespace Modules\RestAPI\Entities;

use App\Observers\TaskFileObserver;

class TaskFile extends \App\Models\TaskFile
{
    protected $table = 'task_files';

    protected $default = [
        'id',
        'filename',
        'task_id',
        'user_id',
        'created_at',
    ];

    protected $filterable = [
        'id',
        'task_id',
        'user_id',
        'created_at',
    ];

    public function __construct($attributes = [])
    {
        $this->appendRequestedColumnsToDefault();
        parent::__construct($attributes);
    }

    public static function boot()
    {
        parent::boot();
        static::observe(TaskFileObserver::class);
    }

    private function appendRequestedColumnsToDefault(): void
    {
        if (!app()->bound('request')) {
            return;
        }

        $request = request();
        $allowedOptionalColumns = [
            'description',
            'google_url',
            'hashname',
            'size',
            'dropbox_link',
            'external_link',
            'external_link_name',
            'updated_at',
        ];

        $requestedColumns = [];

        foreach ($allowedOptionalColumns as $column) {
            if ($request->exists($column)) {
                $requestedColumns[] = $column;
            }
        }

        $fields = $request->query('fields');

        if (is_string($fields) && $fields !== '') {
            $fieldColumns = array_map('trim', explode(',', $fields));
            $requestedColumns = array_merge(
                $requestedColumns,
                array_intersect($fieldColumns, $allowedOptionalColumns)
            );
        }

        if (!empty($requestedColumns)) {
            $this->default = array_values(array_unique(array_merge($this->default, $requestedColumns)));
        }
    }
}
