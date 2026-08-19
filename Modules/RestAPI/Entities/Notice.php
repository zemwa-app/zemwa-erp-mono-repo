<?php

namespace Modules\RestAPI\Entities;

use App\Observers\NoticeObserver;
use Illuminate\Support\Facades\DB;

class Notice extends \App\Models\Notice
{

    // region Properties

    protected $table = 'notices';

    protected $hidden = [
        'updated_at',
    ];

    protected $default = [
        'id',
        'heading',
        'description',
        'to',
        'department_id',
        'added_by',
        'created_at',
        'notice_date',
        'files',
        'member',
        'notice_employees',
        'notice_clients',
        'department'
    ];

    protected $appends = [
        'notice_date'
    ];

    protected $filterable = [
        'id',
        'heading',
        'created_at'
    ];

    public static function boot()
    {
        parent::boot();
        static::observe(NoticeObserver::class);
    }

    public function visibleTo(\App\Models\User $user)
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('client')) {
            return $this->to === 'client';
        }

        if ($user->hasRole('employee')) {
            return $this->to === 'employee';
        }

        return true;
    }

    public function scopeVisibility($query)
    {
        $user = api_user();

        if (!$user) {
            return $query;
        }

        $viewNoticePermission = $user->permission('view_notice');

        if (is_null($viewNoticePermission) || $viewNoticePermission == 'none') {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('admin') && $viewNoticePermission == 'all') {
            return $query;
        }

        $userId = $user->id;

        if ($user->hasRole('employee') && !$user->hasRole('admin')) {
            $query->where(function ($q) use ($user) {
                if ($user->employeeDetail && $user->employeeDetail->department) {
                    $departmentId = $user->employeeDetail->department->id;
                    $q->whereNull('notices.department_id');
                    $q->orWhere('notices.department_id', $departmentId);
                }
            });

            if ($viewNoticePermission == 'owned') {
                $query->whereHas('noticeEmployees', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
            }
        }

        if ($user->hasRole('client')) {
            $query->whereHas('noticeClients', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        if ($viewNoticePermission == 'added') {
            $query->where('notices.added_by', $userId);
        }

        if ($viewNoticePermission === 'both') {
            $query->where(function ($q) use ($userId) {
                $q->where('notices.added_by', $userId)
                    ->orWhereHas('member', fn ($mq) => $mq->where('user_id', $userId));
            });
        }

        return $query;
    }

}
