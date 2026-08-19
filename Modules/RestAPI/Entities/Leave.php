<?php

namespace Modules\RestAPI\Entities;

use App\Observers\LeaveObserver;
use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\RestAPI\Entities\Concerns\ParsesFlexibleDateTimes;

class Leave extends \App\Models\Leave
{
    use ParsesFlexibleDateTimes;
    public function __construct($attributes = [])
    {
        $this->appends = array_merge(['duration_type'], $this->appends);
        parent::__construct($attributes);
    }

    // region Properties

    protected $table = 'leaves';

    protected $default = [
        'id',
        'leave_type_id',
        'leave_date',
        'duration',
        'reason',
        'status',
    ];

    protected $hidden = [
        'leave.leave_type_id',
    ];

    protected $casts = [
        'leave_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected $guarded = [
        'id',
    ];

    protected $filterable = [
        'id',
        'status',
        'duration',
        'user_id',
        'leave_type_id',
        'employee_name',
        'leave_date',
    ];

    public static function boot()
    {
        parent::boot();
        static::observe(LeaveObserver::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }

    public function employee(): HasOne
    {
        return $this->hasOne(EmployeeDetails::class, 'user_id', 'user_id');
    }

    public function getDurationTypeAttribute()
    {
        return $this->duration == 'half day' ? __('modules.leaves.halfDay') : ($this->duration == 'multiple' ? __('modules.leaves.multiple') : __('app.' . $this->duration));
    }

    public function visibleTo(\App\Models\User $user)
    {
        if ($user->hasRole('admin') || $user->hasRole('employee') || $user->can('view_leave')) {
            return true;
        }

        return false;
    }

    public function scopeVisibility($query)
    {
        if (api_user()) {
            $user = api_user();

            if ($user->hasRole('admin')) {
                return $query;
            }

            if ($user->hasRole('employee')) {
                $query->where('user_id', $user->id);

                return $query;
            }
        }
        return $query;
    }
}
