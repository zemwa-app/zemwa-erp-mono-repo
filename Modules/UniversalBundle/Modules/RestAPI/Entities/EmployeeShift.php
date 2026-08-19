<?php

namespace Modules\RestAPI\Entities;

use App\Models\TaskboardColumn;
use App\Observers\TaskObserver;

class EmployeeShift extends \App\Models\EmployeeShift
{
    // region Properties

    protected $table = 'employee_shifts';

    protected $fillable = [
        'shift_name',
        'color',
        'office_start_time',
        'office_end_time',
    ];

    protected $default = [
        'id',
        'shift_name',
        'color',
        'office_start_time',
        'office_end_time',
        'halfday_mark_time',
        'late_mark_duration',
        'clockin_in_day',
        'office_open_days',
        'early_clock_in',
    ];

    protected $guarded = [
        'id',
    ];

    protected $filterable = [
        'id',
        'shift_name',
    ];

    public static function boot()
    {
        parent::boot();
    }
}
