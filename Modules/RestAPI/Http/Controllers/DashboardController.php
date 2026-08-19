<?php

namespace Modules\RestAPI\Http\Controllers;

use App\Models\AttendanceSetting;
use App\Models\ProjectTimeLogBreak;
use App\Models\TaskboardColumn;
use App\Models\User;
use Carbon\CarbonPeriod;
use Froiden\RestAPI\ApiResponse;
use Modules\RestAPI\Entities\Holiday;
use Modules\RestAPI\Entities\Leave;
use Modules\RestAPI\Entities\Attendance;
use Modules\RestAPI\Entities\Project;
use Modules\RestAPI\Entities\ProjectTimeLog;
use Modules\RestAPI\Entities\Task;
use Modules\RestAPI\Entities\Ticket;

class DashboardController extends ApiBaseController
{
    public function dashboard()
    {
        $taskBoardColumn = TaskboardColumn::all();

        // Clock-in/out for current user
        $now = now(api_user()->company->timezone);
        $todayAttendance = Attendance::where('user_id', api_user()->id)
            ->whereDate('clock_in_time', $now->toDateString())
            ->orderByDesc('id')
            ->first();
        $clockInTime = $todayAttendance && $todayAttendance->clock_in_time
            ? $todayAttendance->clock_in_time->timezone(api_user()->company->timezone)->toDateTimeString()
            : null;
        $clockOutTime = $todayAttendance && $todayAttendance->clock_out_time
            ? $todayAttendance->clock_out_time->timezone(api_user()->company->timezone)->toDateTimeString()
            : null;

        $completedTaskColumn = $taskBoardColumn->filter(function ($value, $key) {
            return $value->slug == 'completed';
        })->first();

        $totalProjects = Project::select('projects.id')
            ->get()
            ->count();

        $openTasks = Task::select('tasks.id')
            ->where('board_column_id', '!=', $completedTaskColumn->id)
            ->where('is_private', 0)
            ->whereNull('deleted_at')
            ->get()
            ->count();

        $openTickets = Ticket::select('tickets.id')
            ->where('status', 'open')
            ->get()
            ->count();

        return ApiResponse::make(null, [
            'openTickets' => $openTickets,
            'totalProjects' => $totalProjects,
            'openTasks' => $openTasks,
            'clockInTime' => $clockInTime,
            'clockOutTime' => $clockOutTime,
            'hasUnreadNotifications' => api_user()->unreadNotifications()->exists(),
        ]);
    }

    public function convertMinutesToHours($totalMinutes): string
    {
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;
        return sprintf("%02d:%02d", $hours, $minutes);
    }

    public function myDashboard()
    {
        $taskBoardColumn = TaskboardColumn::all();

        $now = now(api_user()->company->timezone);
        $showClockIn = AttendanceSetting::first();
        $this->weekStartDate = $now->copy()->startOfWeek($showClockIn->week_start_from);
        $this->weekEndDate = $this->weekStartDate->copy()->addDays(7);
        $this->weekPeriod = CarbonPeriod::create($this->weekStartDate, $this->weekStartDate->copy()->addDays(6)); // Get All Dates from start to end date

        $completedTaskColumn = $taskBoardColumn->filter(function ($value, $key) {
            return $value->slug == 'completed';
        })->first();

        $projects = Project::select('projects.id', 'projects.deadline')
            ->join('project_members', 'project_members.project_id', '=', 'projects.id')
            ->where('project_members.user_id', '=', api_user()->id)
            ->where('completion_percent', '<>', '100')
            ->whereNull('deleted_at')
            ->groupBy('projects.id')
            ->get();
        $totalProjects = $projects->count();

        $overdueProjects = $projects->filter(function ($item) {
            return !is_null($item->deadline) && $item->deadline->endOfDay()->isPast();
        })->count();

        $tasks = Task::select('tasks.id', 'tasks.due_date', 'tasks.board_column_id')
            ->where('board_column_id', '!=', $completedTaskColumn->id)
            ->join('task_users', 'task_users.task_id', '=', 'tasks.id')
            ->where('task_users.user_id', '=', api_user()->id)
            ->where('is_private', 0)
            ->whereNull('deleted_at')
            ->groupBy('tasks.id')
            ->get();
        $pendingTasks = $tasks->count();

        $dueTasks = $tasks->filter(function ($item) {
            return !is_null($item->due_date) && $item->due_date->endOfDay()->isPast();
        })->count();

        $openTickets = Ticket::select('tickets.id')
            ->where('status', 'open')
            ->where('agent_id', api_user()->id)
            ->get()->count();

        $dateWiseTimelogs = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $this->weekPeriod->getStartDate()->copy()->addDays($i);
            $proiectTimelogs = ProjectTimeLog::dateWiseTimelogs($date->toDateString(), api_user()->id);
            $timelogMinutes = $proiectTimelogs->sum('total_minutes');
            $timelogBreak = ProjectTimeLogBreak::dateWiseTimelogBreak($date->toDateString(), api_user()->id)->sum('total_minutes');
            $dateWiseTimelogs[] = [
                'date' => $date->toDateString(),
                'total_timelog_minutes' => $timelogMinutes,
                'total_timelog_hours' => $this->convertMinutesToHours($timelogMinutes),
                'total_timelog__break_minutes' => $timelogBreak,
                'total_timelog_break_hours' => $this->convertMinutesToHours($timelogBreak),
            ];
        }

        $weekWiseTimelogs = ProjectTimeLog::weekWiseTimelogs($this->weekStartDate->copy()->toDateString(), $this->weekEndDate->copy()->toDateString(), api_user()->id);
        $weekWiseTimelogBreak = ProjectTimeLogBreak::weekWiseTimelogBreak($this->weekStartDate->toDateString(), $this->weekEndDate->toDateString(), api_user()->id);

        $isTodayHoliday = Holiday::where('date', $now->format('Y-m-d'))
            ->where(function ($query) {
                $query->orWhere('department_id_json', 'like', '%"' . api_user()->employeeDetail->department_id . '"%')
                    ->orWhereNull('department_id_json');
            })
            ->where(function ($query) {
                $query->orWhere('designation_id_json', 'like', '%"' . api_user()->employeeDetail->designation_id . '"%')
                    ->orWhereNull('designation_id_json');
            })
            ->where(function ($query) {
                if (!is_Null(api_user()->employeeDetail->employment_type)) {
                    $query->orWhere('employment_type_json', 'like', '%"' . api_user()->employeeDetail->employment_type . '"%')
                        ->orWhereNull('employment_type_json');
                }
            })
            ->first();

        $checkTodayLeave = Leave::where('status', 'approved')
            ->select('id')
            ->where('leave_date', now(api_user()->company->timezone)->toDateString())
            ->where('user_id', api_user()->id)
            ->where('duration', '<>', 'half day')
            ->first();

        // Clock-in/out for current user
        $todayAttendance = Attendance::where('user_id', api_user()->id)
            ->whereDate('clock_in_time', $now->toDateString())
            ->orderByDesc('id')
            ->first();
        $clockInTime = $todayAttendance && $todayAttendance->clock_in_time
            ? $todayAttendance->clock_in_time->timezone(api_user()->company->timezone)->toDateTimeString()
            : null;
        $clockOutTime = $todayAttendance && $todayAttendance->clock_out_time
            ? $todayAttendance->clock_out_time->timezone(api_user()->company->timezone)->toDateTimeString()
            : null;

        return ApiResponse::make(null, [
            'totalProjects' => $totalProjects,
            'overdueProjects' => $overdueProjects,
            'pendingTasks' => $pendingTasks,
            'openTasks' => $pendingTasks,
            'dueTasks' => $dueTasks,
            'openTickets' => $openTickets,
            'dateWiseTimelogs' => $dateWiseTimelogs,
            'weekWiseTimelogs' => $weekWiseTimelogs,
            'weekWiseTimelogBreak' => $weekWiseTimelogBreak,
            'isTodayHoliday' => (bool)$isTodayHoliday,
            'isLeave' => (bool)$checkTodayLeave,
            'clockInTime' => $clockInTime,
            'clockOutTime' => $clockOutTime,
            'hasUnreadNotifications' => api_user()->unreadNotifications()->exists(),
            'unreadNotificationsCount' => api_user()->unreadNotifications()->count(),
        ]);
    }

    public function aniversaries()
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;
        $currentDay = now()->day;
        $today = now()->format('Y-m-d');

        $query = User::whereHas('employeeDetail', function ($q) {
                $q->whereNotNull('joining_date');
            })
            ->with('employeeDetail')
            ->select('users.id', 'users.name', 'users.email', 'users.image', 'users.gender');

        $workAnniversaries = $query->clone()->with('employeeDetail', function ($q) use ($currentMonth, $currentYear) {
                $q->select('id', 'user_id', 'joining_date', 'employee_id', 'department_id', 'designation_id')
                    ->whereMonth('joining_date', $currentMonth)
                    ->whereYear('joining_date', '<', $currentYear);
            })
            ->whereHas('employeeDetail', function ($q) use ($currentMonth, $currentYear) {
                $q->select('id', 'user_id', 'joining_date', 'employee_id', 'department_id', 'designation_id')
                    ->whereMonth('joining_date', $currentMonth)
                    ->whereYear('joining_date', '<', $currentYear);
            })
            ->orderByRaw('DAY((SELECT joining_date FROM employee_details WHERE employee_details.user_id = users.id))')
            ->get()
            ->toArray();

        $joinings = $query->with('employeeDetail', function ($q) use ($today) {
                $q->select('id', 'user_id', 'joining_date', 'employee_id', 'department_id', 'designation_id')->whereDate('joining_date', $today);
            })
            ->whereHas('employeeDetail', function ($q) use ($today) {
                $q->select('id', 'user_id', 'joining_date', 'employee_id', 'department_id', 'designation_id')->whereDate('joining_date', $today);
            })
            ->orderByRaw('DAY((SELECT joining_date FROM employee_details WHERE employee_details.user_id = users.id))')
            ->limit(3)
            ->get()
            ->toArray();

        $results = [
            'workAnniversaries' => $workAnniversaries,
            'joinings' => $joinings,
        ];

        return ApiResponse::make(null, $results);
    }

}
