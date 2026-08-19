<?php

namespace Modules\RestAPI\Entities;

use App\Models\TaskboardColumn;
use App\Observers\TaskObserver;

class TaskHistory extends \App\Models\TaskHistory
{
    // region Properties

    protected $table = 'task_history';

    protected $fillable = [
        'task_id',
        'user_id',
    ];

    protected $default = [
        'id',
        'task_id',
        'user_id',
        'sub_task_id',
        'details',
        'board_column_id',
        'created_at',
    ];

    protected $hidden = [
        'user_id',
        'board_column_id',
    ];

    protected $guarded = [
        'id',
    ];

    protected $filterable = [
        'id',
        'tasks.id',
        'users.id',
        'details',
        'board_column.id',
    ];

    public static function boot()
    {
        parent::boot();
    }
}
