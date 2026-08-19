<?php

namespace Modules\RestAPI\Entities;

use App\Models\Team;
use App\Observers\HolidayObserver;

class Holiday extends \App\Models\Holiday
{
    // region Properties

    protected $table = 'holidays';

    protected $dates = ['date'];

    protected $default = [
        'id',
        'date',
        'occassion',
        'department_id_json',
        'designation_id_json',
        'employment_type_json',
    ];

    protected $filterable = [
        'id',
        'date',
        'occassion',
        'department_id_json',
        'designation_id_json',
        'employment_type_json',
    ];

    //endregion

    public static function boot()
    {
        parent::boot();
        static::observe(HolidayObserver::class);
    }

    public static function designation($ids)
    {
        $designation = null;

        if ($ids != null) {
            $designation = Designation::whereIn('id', $ids)->pluck('name')->toArray();
        }

        return $designation;
    }

    public static function department($ids)
    {
        $department = null;

        if ($ids != null) {
            $department = Team::whereIn('id', $ids)->pluck('team_name')->toArray();
        }

        return $department;
    }
}
