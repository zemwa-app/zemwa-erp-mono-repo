<?php

namespace Modules\RestAPI\Entities;

use App\Observers\TaskCommentObserver;

class TaskComment extends \App\Models\TaskComment
{
    protected $table = 'task_comments';

    protected $default = [
        'id',
        'comment',
        'task_id',
        'user_id',
    ];

    protected $guarded = [
        'id',
        'task_id',
        'user_id',
        'added_by',
        'last_updated_by',
    ];

    protected $filterable = [
        'id',
        'task_id',
        'user_id',
    ];

    public static function boot()
    {
        parent::boot();
        static::observe(TaskCommentObserver::class);
    }
}
