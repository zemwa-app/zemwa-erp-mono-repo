<?php

namespace Modules\RestAPI\Http\Controllers;

use App\Models\AttendanceSetting;
use App\Models\Company;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Froiden\RestAPI\ApiResponse;
use Illuminate\Support\Facades\DB;
use Modules\RestAPI\Entities\EmployeeShiftSchedule;
use Modules\RestAPI\Entities\Holiday;
use Modules\RestAPI\Entities\Leave;
use Modules\RestAPI\Http\Requests\Attendance\IndexRequest;

class EmployeeShiftScheduleController extends ApiBaseController
{
    protected $model = EmployeeShiftSchedule::class;

    protected $indexRequest = IndexRequest::class;

    public function modifyIndex($query)
    {
        return $query->groupBy('user_id')->visibility();
    }

    public function myShiftSchedules()
    {
        $showClockIn = AttendanceSetting::first();
        $currentDay = now(api_user()->company->timezone)->format('m-d');

        $this->attendanceSettings = $this->attendanceShift($showClockIn);

        $now = now(api_user()->company->timezone);
        $this->weekStartDate = $now->copy()->startOfWeek($showClockIn->week_start_from);
        $this->weekEndDate = $this->weekStartDate->copy()->addDays(7);
        $this->weekPeriod = CarbonPeriod::create($this->weekStartDate, $this->weekStartDate->copy()->addDays(6)); // Get All Dates from start to end date

        $this->employeeShifts = EmployeeShiftSchedule::where('user_id', api_user()->id)
            ->whereBetween(DB::raw('DATE(`date`)'), [$this->weekStartDate->format('Y-m-d'), $this->weekEndDate->format('Y-m-d')])
            ->select(DB::raw('DATE_FORMAT(date, "%Y-%m-%d") as dates'), 'employee_shift_schedules.*')
            ->with('shift', 'user', 'requestChange')
            ->get();

        $this->employeeShiftDates = $this->employeeShifts->pluck('dates')->toArray();

        $currentWeekDates = [];
        $weekShifts = [];

//        $weekHolidays = Holiday::whereBetween(DB::raw('DATE(`date`)'),
//            [$this->weekStartDate->format('Y-m-d'), $this->weekEndDate->format('Y-m-d')])
//            ->select(DB::raw('DATE_FORMAT(`date`, "%Y-%m-%d") as hdate'), 'occassion')
//            ->get();
        $user = api_user();
        $weekHolidays = Holiday::whereBetween(DB::raw('DATE(`date`)'), [$this->weekStartDate->format('Y-m-d'), $this->weekEndDate->format('Y-m-d')])
            ->where(function ($query) use ($user) {
                $query->where(function ($subquery) use ($user) {
                    $subquery->where(function ($q) use ($user) {
                        $q->where('department_id_json', 'like', '%"' . $user->employeeDetails->department_id . '"%')
                            ->orWhereNull('department_id_json');
                    });
                    $subquery->where(function ($q) use ($user) {
                        $q->where('designation_id_json', 'like', '%"' . $user->employeeDetails->designation_id . '"%')
                            ->orWhereNull('designation_id_json');
                    });
                    $subquery->where(function ($q) use ($user) {
                        $q->where('employment_type_json', 'like', '%"' . $user->employeeDetails->employment_type . '"%')
                            ->orWhereNull('employment_type_json');
                    });
                });
            })
            ->select(DB::raw('DATE_FORMAT(`date`, "%Y-%m-%d") as hdate'), 'occassion')
            ->get();

        $holidayDates = $weekHolidays->pluck('hdate')->toArray();

        $weekLeaves = Leave::with('type')
            ->select(DB::raw('DATE_FORMAT(`leave_date`, "%Y-%m-%d") as ldate'), 'leaves.*')
            ->where('user_id', api_user()->id)
            ->whereBetween(DB::raw('DATE(`leave_date`)'), [$this->weekStartDate->format('Y-m-d'), $this->weekEndDate->format('Y-m-d')])
            ->where('status', 'approved')
            ->where('duration', '<>', 'half day')
            ->get();

        $leaveDates = $weekLeaves->pluck('ldate')->toArray();
        $generalShift = Company::with(['attendanceSetting', 'attendanceSetting.shift'])->first();

        // phpcs:ignore
        $employeeData = [];
        for ($i = $this->weekStartDate->copy(); $i < $this->weekEndDate->copy(); $i->addDay()) {
            $date = Carbon::parse($i);
            $data = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('l'),
                'holiday' => '',
                'leave' => '',
                'leave_color' => '',
                'shift_id' => '',
                'shift' => '',
                'shift_color' => '',
                'shift_start_time' => '',
                'shift_end_time' => '',
                'flexible_shift_hours' => '',
            ];

            if (in_array($date->toDateString(), $holidayDates)) {
                foreach ($weekHolidays as $holiday) {
                    if ($holiday->hdate == $date->toDateString()) {
                        $data['holiday'] = $holiday->occassion;
                    }
                }
            } elseif (in_array($date->toDateString(), $leaveDates)) {
                foreach ($weekLeaves as $leav) {
                    if ($leav->ldate == $date->toDateString()) {
                        $data['leave'] = $leav->type->type_name;
                        $data['leave_color'] = $leav->type->color;
                    }
                }
            } elseif (in_array($date->toDateString(), $this->employeeShiftDates)) {
                foreach ($this->employeeShifts as $shift) {
                    if ($shift->dates == $date->toDateString()) {
                        $data['shift_id'] = $shift->shift->id;
                        $data['shift'] = $shift->shift->shift_name;
                        $data['shift_color'] = $shift->shift->color;
                        $data['shift_start_time'] = $shift->shift->office_start_time;
                        $data['shift_end_time'] = $shift->shift->office_end_time;
                        $data['flexible_shift_hours'] = $shift->shift->flexible_shift_hours;
                    }
                }
            } else {
                if ($generalShift && $generalShift->attendanceSetting && $generalShift->attendanceSetting->shift) {
                    $data['shift_id'] = $generalShift->attendanceSetting->shift->id;
                    $data['shift'] = $generalShift->attendanceSetting->shift->shift_name;
                    $data['shift_color'] = $generalShift->attendanceSetting->shift->color;
                    $data['shift_start_time'] = $generalShift->attendanceSetting->shift->office_start_time;
                    $data['shift_end_time'] = $generalShift->attendanceSetting->shift->office_end_time;
                    $data['flexible_shift_hours'] = $generalShift->attendanceSetting->shift->flexible_shift_hours;
                } else {
                    $data['shift_id'] = '0';
                    $data['shift'] = 'Shift Not Assigned';
                    $data['shift_color'] = '#000000';
                    $data['shift_start_time'] = '';
                    $data['shift_end_time'] = '';
                    $data['flexible_shift_hours'] = '';
                }
            }

            $employeeData[] = $data;
        }

        $this->currentWeekDates = $currentWeekDates;
        $this->weekShifts = $weekShifts;
        return ApiResponse::make('Data fetch successfully', [
            'data' => $employeeData,
        ]);
    }

    public function attendanceShift($defaultAttendanceSettings)
    {
        $checkPreviousDayShift = EmployeeShiftSchedule::with('shift')->where('user_id', api_user()->id)
            ->where('date', now(api_user()->company->timezone)->subDay()->toDateString())
            ->first();

        $checkTodayShift = EmployeeShiftSchedule::with('shift')->where('user_id', api_user()->id)
            ->where('date', now(api_user()->company->timezone)->toDateString())
            ->first();

        $backDayFromDefault = Carbon::parse(now(api_user()->company->timezone)->subDay()->format('Y-m-d') . ' ' . $defaultAttendanceSettings->office_start_time);

        $backDayToDefault = Carbon::parse(now(api_user()->company->timezone)->subDay()->format('Y-m-d') . ' ' . $defaultAttendanceSettings->office_end_time);

        if ($backDayFromDefault->gt($backDayToDefault)) {
            $backDayToDefault->addDay();
        }

        $nowTime = Carbon::createFromFormat('Y-m-d H:i:s', now(api_user()->company->timezone)->toDateTimeString(), 'UTC');

        if ($checkPreviousDayShift && $nowTime->betweenIncluded($checkPreviousDayShift->shift_start_time, $checkPreviousDayShift->shift_end_time)) {
            $attendanceSettings = $checkPreviousDayShift;

        }
        else if ($nowTime->betweenIncluded($backDayFromDefault, $backDayToDefault)) {
            $attendanceSettings = $defaultAttendanceSettings;

        }
        else if ($checkTodayShift &&
            ($nowTime->betweenIncluded($checkTodayShift->shift_start_time, $checkTodayShift->shift_end_time)
                || $nowTime->gt($checkTodayShift->shift_end_time)
                || (!$nowTime->betweenIncluded($checkTodayShift->shift_start_time, $checkTodayShift->shift_end_time) && $defaultAttendanceSettings->show_clock_in_button == 'no'))
        ) {
            $attendanceSettings = $checkTodayShift;
        }
        else if ($checkTodayShift && !is_null($checkTodayShift->shift->early_clock_in)) {
            $attendanceSettings = $checkTodayShift;
        }
        else {
            $attendanceSettings = $defaultAttendanceSettings;
        }

        return $attendanceSettings->shift;

    }
}
