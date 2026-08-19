<?php

namespace Modules\RestAPI\Entities;

class EmployeeShiftSchedule extends \App\Models\EmployeeShiftSchedule
{
    // region Properties

    protected $table = 'employee_shift_schedules';

    protected $fillable = [
        'user_id',
        'date',
        'employee_shift_id',
        'shift_start_time',
        'shift_end_time',
    ];

    protected $default = [
        'id',
        'user_id',
        'date',
        'employee_shift_id',
        'shift_start_time',
        'shift_end_time',
    ];

    protected $guarded = [
        'id',
    ];

    protected $filterable = [
        'id',
        'user_id',
        'date',
        'employee_shift_id',
        'shift_start_time',
        'shift_end_time',
    ];

    public static function boot()
    {
        parent::boot();
    }

    public function visibleTo(\App\Models\User $user)
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (($user->hasRole('employee') && $user->cans('view_attendance')) || $this->user_id == $user->id) {
            return true;
        }

        return false;
    }

    public function scopeVisibility($query)
    {
        if (api_user()) {
            $user = api_user();

            if ($user->hasRole('admin') || ($user->hasRole('employee') && $user->cans('view_attendance'))) {
                return $query;
            }

            if (! $user->cans('view_attendance')) {
                $query->where('user_id', $user->id);

                return $query;
            }
        }
    }
}
