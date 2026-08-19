<?php

namespace Modules\RestAPI\Entities;

use App\Observers\AttendanceObserver;
use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends \App\Models\Attendance
{
    // region Properties
    protected $default = [
        'id',
        'clock_in_date',
        'clock_in_time',
        'clock_out_time',
        'company_id',
    ];

    protected $hidden = [
        'user_id',
    ];

    protected $guarded = [
        'id',
    ];

    protected $filterable = [
        'id',
        'clock_in_time',
        'clock_out_time',
        'user.id',
    ];

    protected $appends = ['clock_in_date'];

    public static function boot()
    {
        parent::boot();
        static::observe(AttendanceObserver::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function getClockInDateAttribute()
    {
        return $this->clock_in_time->timezone(api_user()->company->timezone)->toDateString();
    }

    public function visibleTo(\App\Models\User $user)
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (($user->hasRole('employee') && $user->can('view_attendance')) || $this->user_id == $user->id) {
            return true;
        }

        return false;
    }

    public function scopeVisibility($query)
    {
        if (api_user()) {
            $user = api_user();

            if ($user->hasRole('admin') || ($user->hasRole('employee') && $user->can('view_attendance'))) {
                return $query;
            }

            if (! $user->can('view_attendance')) {
                $query->where('user_id', $user->id);

                return $query;
            }
        }
    }
}
