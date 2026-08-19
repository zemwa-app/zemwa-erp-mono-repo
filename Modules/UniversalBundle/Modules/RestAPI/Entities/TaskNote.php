<?php

namespace Modules\RestAPI\Entities;

use App\Observers\TaskNoteObserver;

class TaskNote extends \App\Models\TaskNote
{
    protected $table = 'task_notes';

    protected $default = [
        'id',
        'note',
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
        static::observe(TaskNoteObserver::class);
    }
}
