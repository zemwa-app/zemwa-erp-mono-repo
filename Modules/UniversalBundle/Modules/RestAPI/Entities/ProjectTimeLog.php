<?php

namespace Modules\RestAPI\Entities;

use App\Models\ProjectTimeLogBreak;
use App\Observers\ProjectTimelogObserver;
use App\Scopes\ActiveScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProjectTimeLog extends \App\Models\ProjectTimeLog
{
    public function __construct($attributes = [])
    {
        $this->appends = array_merge(['timer_start_time'], $this->appends);
        $this->appendRequestedColumnsToDefault();
        parent::__construct($attributes);
    }

    // region Properties

    protected $table = 'project_time_logs';

    protected $dates = ['start_time', 'end_time'];

    protected $hidden = [
        'updated_at',
    ];

    protected $default = [
        'id',
        'start_time',
        'end_time',
        'memo',
        'task_id',
    ];

    protected $guarded = [
        'id',
    ];

    protected $filterable = [
        'id',
        'project_id',
        'task_id',
        'start_time',
        'end_time',
        'user_id',
    ];

    public static function boot()
    {
        parent::boot();
        static::observe(ProjectTimelogObserver::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id')->withTrashed();
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id')->withTrashed();
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(ProjectTimeLogBreak::class, 'project_time_log_id');
    }

    public function activeBreak(): HasOne
    {
        return $this->hasOne(ProjectTimeLogBreak::class, 'project_time_log_id')->whereNull('end_time');
    }

    private function appendRequestedColumnsToDefault(): void
    {
        if (!app()->bound('request')) {
            return;
        }

        $request = request();
        $allowedOptionalColumns = [
            'project_id',
            'user_id',
            'total_hours',
            'total_minutes',
            'hourly_rate',
            'earnings',
            'edited_by_user',
        ];

        $requestedColumns = [];

        // Support query params like ?total_minutes or ?user_id.
        foreach ($allowedOptionalColumns as $column) {
            if ($request->exists($column)) {
                $requestedColumns[] = $column;
            }
        }

        // Also support ?fields=total_minutes,user_id style requests.
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

    public function getTimerStartTimeAttribute()
    {
        $settings = \company();

        return Carbon::parse($this->start_time)->timezone($settings->timezone);
    }

    public function visibleTo(\App\Models\User $user)
    {
        if ($user->hasRole('employee') || $user->can('view_tasks')) {
            return true;
        }
    }

    public function scopeVisibility($query)
    {
        return $query;
    }
}
