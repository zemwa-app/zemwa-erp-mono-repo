<?php

namespace Modules\RestAPI\Http\Controllers;

use App\Models\AttendanceSetting;
use App\Models\Company;
use App\Models\EmployeeShiftSchedule;
use App\Models\CompanyAddress;
use App\Models\Holiday;
use App\Traits\EmployeeDashboard;
use Carbon\Carbon;
use Froiden\RestAPI\ApiResponse;
use Froiden\RestAPI\Exceptions\ApiException;
use Froiden\RestAPI\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\DB;
use Modules\RestAPI\Entities\Attendance;
use Modules\RestAPI\Entities\Leave;
use Modules\RestAPI\Entities\User;
use Modules\RestAPI\Http\Requests\Attendance\CreateRequest;
use Modules\RestAPI\Http\Requests\Attendance\DeleteRequest;
use Modules\RestAPI\Http\Requests\Attendance\IndexRequest;
use Modules\RestAPI\Http\Requests\Attendance\ShowRequest;
use Modules\RestAPI\Http\Requests\Attendance\UpdateRequest;

class AttendanceController extends ApiBaseController
{

    protected $model = Attendance::class;

    protected $indexRequest = IndexRequest::class;

    protected $storeRequest = CreateRequest::class;

    protected $updateRequest = UpdateRequest::class;

    protected $showRequest = ShowRequest::class;

    protected $deleteRequest = DeleteRequest::class;

    protected function modifyIndex($query)
    {
        return $query->groupBy('attendances.user_id')->visibility();
    }

    public function filterByDate($query, $dateField, $requestDate) {
        return $query->whereDate($dateField, $requestDate);
    }

    public function dateWise($date)
    {
        $this->viewAttendancePermission = api_user()->permission('view_attendance');
        $requestDate = Carbon::createFromFormat('Y-m-d', $date);
        // Eager load relationships with filters
        $employees = User::with([
            'employeeDetail.designation:id,name',
            'attendance' => function ($query) use ($date) {
                $this->filterByDate($query, 'attendances.clock_in_time', $date);
                if ($this->viewAttendancePermission == 'added') {
                    $query->where('attendances.added_by', api_user()->id);
                } elseif ($this->viewAttendancePermission == 'owned') {
                    $query->where('attendances.user_id', api_user()->id);
                }
            },
            'leaves' => function ($query) use ($requestDate) {
                $this->filterByDate($query, 'leaves.leave_date', $requestDate)
                    ->where('status', 'approved');
            },
            'shifts' => function ($query) use ($requestDate) {
                $this->filterByDate($query, 'employee_shift_schedules.date', $requestDate);
            },
            'leaves.type',
            'shifts.shift',
            'attendance.shift'
        ])
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'employee_details.department_id', 'users.image', 'users.slug')
            ->onlyEmployee()
            ->groupBy('users.id');

        if ($this->viewAttendancePermission == 'owned') {
            $employees = $employees->where('users.id', api_user()->id);
        }

        $employees = $employees->get();
        $user = api_user();
        $this->holidays = Holiday::whereDate('holidays.date', $requestDate)->get();

        $final = [];
        $holidayOccasions = [];
        $leaveReasons = [];
        $this->daysInMonth = 1;

        foreach ($employees as $employee) {
            $totalHours = '--';
            if ($employee->attendance->count() > 0 && !is_null($employee->attendance[0]->clock_out_time)) {
                $diff = $employee->attendance[0]->clock_out_time->diff($employee->attendance[0]->clock_in_time);
                $totalHours = sprintf("%d hours %d minutes", $diff->h + ($diff->days * 24), $diff->i);
            }

            $employeeData = [
                'id' => $employee->id,
                'attendance_type' => 'Absent',
                'name' => $employee->name,
                'image' => $employee->image_url,
                'designation' => optional($employee->employeeDetail->designation)->name ?: '--',
                'clock_in' => $employee->attendance->count() > 0 && $employee->attendance[0]->clock_in_time ? $employee->attendance[0]->clock_in_time->timezone(api_user()->company->timezone)->format('H:i') : '--',
                'clock_out' => $employee->attendance->count() > 0 && $employee->attendance[0]->clock_out_time ? $employee->attendance[0]->clock_out_time->timezone(api_user()->company->timezone)->format('H:i') : '--',
                'total_hours' => $totalHours,
            ];

            $shiftScheduleCollection = $employee->shifts->keyBy('date');

            foreach ($employee->shifts as $shifts) {
                if ($shifts->shift->shift_name == 'Day Off') {
                    $employeeData['attendance_type'] = 'Day Off';
                }
            }

            $firstAttendanceProcessed = [];
            foreach ($employee->attendance as $attendance) {
                $clockInTime = $attendance->clock_in_time->timezone(api_user()->company->timezone);
                $startOfDayKey = $clockInTime->startOfDay()->toDateTimeString();
                $shiftSchedule = $shiftScheduleCollection[$startOfDayKey] ?? null;

                $shift = $shiftSchedule ? $shiftSchedule->shift : null;
                if ($shift) {
                    $shiftStartTime = Carbon::parse($clockInTime->toDateString() . ' ' . $shift->office_start_time);
                    $shiftEndTime = Carbon::parse($clockInTime->toDateString() . ' ' . $shift->office_end_time);
                    $isWithinShift = $clockInTime->between($shiftStartTime, $shiftEndTime);
                    $isPreviousShift = $clockInTime->betweenIncluded($shiftStartTime->subDay(), $shiftEndTime->subDay());
                    $isAssignedShift = $attendance->employee_shift_id == $shift->id;
                } else {
                    $isWithinShift = $isPreviousShift = $isAssignedShift = false;
                }

                $firstAttendanceProcessed[$startOfDayKey] = $firstAttendanceProcessed[$startOfDayKey] ?? false;
                if (!$firstAttendanceProcessed[$startOfDayKey]) {
                    $firstAttendanceProcessed[$startOfDayKey] = true;
                    $isHalfDay[$employee->id][$startOfDayKey] = $attendance->half_day == 'yes';
                    $isLate[$employee->id][$startOfDayKey] = $attendance->late == 'yes';
                }

                if ($isWithinShift || $isAssignedShift || $isPreviousShift) {
                    $employeeData['attendance_type'] = __('app.present');
                } else {
                    $employeeData['attendance_type'] = __('app.present');
                }
            }

            foreach ($employee->leaves as $leave) {
                $employeeData['attendance_type'] = $leave->duration == 'half day' ? ($employeeData['attendance_type'] == '-' || $employeeData['attendance_type'] == 'Absent' ? 'Half Day' : $employeeData['attendance_type']) : 'Leave';
                if ($leave->duration != 'half day') {
                    $leaveReasons[$employee->id][$leave->leave_date->day] = "{$leave->type->type_name}: {$leave->reason}";
                }
            }

            foreach ($this->holidays as $holiday) {
                $departmentId = $employee->employeeDetail->department_id;
                $designationId = $employee->employeeDetail->designation_id;
                $employmentType = $employee->employeeDetail->employment_type;
                $holidayDepartment = $holiday->department_id_json ? json_decode($holiday->department_id_json) : [];
                $holidayDesignation = $holiday->designation_id_json ? json_decode($holiday->designation_id_json) : [];
                $holidayEmploymentType = $holiday->employment_type_json ? json_decode($holiday->employment_type_json) : [];

                if ((in_array($departmentId, $holidayDepartment) || !$holiday->department_id_json) &&
                    (in_array($designationId, $holidayDesignation) || !$holiday->designation_id_json) &&
                    (in_array($employmentType, $holidayEmploymentType) || !$holiday->employment_type_json)) {

                    if ($employeeData['attendance_type'] == 'Absent' || $employeeData['attendance_type'] == '-') {
                        $employeeData['attendance_type'] = 'Holiday';
                        $holidayOccasions[$holiday->date->day] = $holiday->occassion;
                    }
                }
            }

            $final[] = $employeeData;
        }

        return ApiResponse::make(null, $final);
    }

    /**
     * @throws UnauthorizedException
     * @throws ApiException
     */
    public function attendanceSetting()
    {
        $user = api_user();

        if (!$user || !$user->id ) {
            throw new ApiException(__('messages.noUser'), null, 401, 401, 2006);
        }

        $company = $user->company ?? null;
        if (!$company) {
            throw new ApiException(__('restapi::app.noCompany'), null, 404, 404, 3001);
        }

        $attendanceSetting = $company->attendanceSetting ?? null;
        if (!$attendanceSetting) {
            throw new ApiException(__('messages.moduleDisabled'), null, 404, 404, 3002);
        }

        $showClockIn = AttendanceSetting::first();
        $shiftSettings = $this->attendanceShift($showClockIn, $company);

        // Mobile app reads clockin_in_day from attendance settings; use the active employee shift value.
        $attendanceSetting->setAttribute('clockin_in_day', $shiftSettings->clockin_in_day);

        return ApiResponse::make('Attendance setting retrieved successfully', [
            'attendance_setting' => $attendanceSetting,
            'employee_shift' => $shiftSettings,
            'max_clock_in' => $shiftSettings->clockin_in_day,
        ]);
    }

    public function today()
    {
        $showClockIn = AttendanceSetting::first();
        $this->global = Company::first();
        $company = api_user()->company;
        $now = now($company->timezone);
        $userId = api_user()->id;

        $attendanceShiftSettings = $this->attendanceShift($showClockIn, $company);
        $maxAttendanceInDay = $attendanceShiftSettings->clockin_in_day;
        $attendance = $this->getOpenClockIn($userId, $company, $now);
        [$officeStartTime, $officeEndTime] = $this->getShiftOfficeWindow($attendanceShiftSettings, $showClockIn, $company, $now);
        $todayTotalClockin = $this->getUserTodayClockInCount($attendanceShiftSettings, $officeStartTime, $officeEndTime, $userId, $company);
        $completedClockIns = $this->getUserCompletedClockInCount($attendanceShiftSettings, $officeStartTime, $officeEndTime, $userId, $company);

        // Check Holiday by date
        $checkTodayHoliday = Holiday::where('date', $now->format('Y-m-d'))->first();

        if ($checkTodayHoliday) {
            throw new ApiException('Today is holiday', null, 422, 422, 2001);
        }

        $hasOpenSession = !is_null($attendance);
        // Remaining slots are based on completed sessions so an open clock-in never blocks clock-out.
        $remainingClockIn = max(0, $maxAttendanceInDay - $completedClockIns);

        $result['attendance'] = $attendance;
        $result['office_hours_passed'] = false;
        $result['time'] = $now->format('c');
        $result['ip_address'] = request()->ip();
        $result['max_clock_in'] = $maxAttendanceInDay;
        $result['today_clock_in_count'] = $todayTotalClockin;
        $result['completed_clock_in_count'] = $completedClockIns;
        $result['remaining_clock_in'] = $remainingClockIn;
        $result['is_clocked_in'] = $hasOpenSession;
        $result['can_clock_in'] = !$hasOpenSession && $todayTotalClockin < $maxAttendanceInDay;
        $result['can_clock_out'] = $hasOpenSession;

        return ApiResponse::make(null, $result);
    }

    public function clockIn()
    {
        $this->company = api_user()->company;
        $now = now(api_user()->company->timezone);

        $showClockIn = AttendanceSetting::first();

        $this->attendanceSettings = $this->attendanceShift($showClockIn);

        $startTimestamp = now($this->company->timezone)->format('Y-m-d') . ' ' . $this->attendanceSettings->office_start_time;
        $endTimestamp = now($this->company->timezone)->format('Y-m-d') . ' ' . $this->attendanceSettings->office_end_time;
        $officeStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $startTimestamp, $this->company->timezone);
        $officeEndTime = Carbon::createFromFormat('Y-m-d H:i:s', $endTimestamp, $this->company->timezone);

        if ($showClockIn->show_clock_in_button == 'yes') {
            $officeEndTime = now($this->company->timezone);
        }

        // check if user has clocked in on time or not
        $lateCheckData = Attendance::whereBetween('clock_in_time', [
            $officeStartTime->copy()->timezone(config('app.timezone')),
            $officeEndTime->copy()->timezone(config('app.timezone'))
        ])
            ->where('user_id', api_user()->id)
            ->orderBy('clock_in_time', 'asc')
            ->first();

        $isLate = 'yes';

        if ($lateCheckData && $lateCheckData->late === 'no' || $this->attendanceSettings->shift_type == 'flexible') {
            // user has reached office on time ,so late check will be disabled now
            $isLate = 'no';
        }

        if ($officeStartTime->gt($officeEndTime)) {
            $officeEndTime->addDay();
        }

        $this->cannotLogin = false;
        $clockInCount = $this->getUserTodayClockInCount($this->attendanceSettings, $officeStartTime, $officeEndTime, api_user()->id, $this->company);

        if ($showClockIn->employee_clock_in_out == 'yes') {
            if (is_null($this->attendanceSettings->early_clock_in) && !now($this->company->timezone)->between($officeStartTime, $officeEndTime) && $showClockIn->show_clock_in_button == 'no' && $this->attendanceSettings->shift_type == 'strict') {
                $this->cannotLogin = true;
                throw new ApiException('Clock-in outside shift hours not Allowed', null, 422, 422, 2001);
            } elseif ($this->attendanceSettings->shift_type == 'strict') {
                $earlyClockIn = now($this->company->timezone)->addMinutes((int) ($this->attendanceSettings->early_clock_in ?? 0));

                if ($earlyClockIn->gte($officeStartTime) || $showClockIn->show_clock_in_button == 'yes') {
                    $this->cannotLogin = false;
                } else {
                    $this->cannotLogin = true;
                    throw new ApiException('Clock-in outside shift hours not Allowed', null, 422, 422, 2001);
                }
            }

            if ($this->cannotLogin && now($this->company->timezone)->betweenIncluded($officeStartTime->copy()->subDay(), $officeEndTime->copy()->subDay())) {
                $this->cannotLogin = false;

                if ($this->attendanceSettings->shift_type == 'strict') {
                    $clockInCount = Attendance::getTotalUserClockInWithTime($officeStartTime->copy()->subDay(), $officeEndTime->copy()->subDay(), api_user()->id);
                }
            }
        }
        else {
            $this->cannotLogin = true;
            throw new ApiException('Employee self Clock-In/Clock-Out Not Allowed', null, 422, 422, 2001);
        }

        // Check maximum attendance in a day (clock-in limit only; clock-out is always allowed for open sessions)
        if ($this->getOpenClockIn(api_user()->id, $this->company, $now)) {
            throw new ApiException('Please clock out before clocking in again', null, 422, 422, 2000);
        }

        if ($clockInCount < $this->attendanceSettings->clockin_in_day) {

            if ($this->attendanceSettings->halfday_mark_time) {
                $halfDayTimes = Carbon::createFromFormat('Y-m-d H:i:s', $now->format('Y-m-d') . ' ' . $this->attendanceSettings->halfday_mark_time, $this->company->timezone);
            }

            $officeStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $now->format('Y-m-d') . ' ' . $this->attendanceSettings->office_start_time, $this->company->timezone);

        // Late mark threshold
        $lateTime = $officeStartTime->copy()->addMinutes((int)($this->attendanceSettings->late_mark_duration ?? 0));

            $checkTodayAttendance = Attendance::where('user_id', api_user()->id)
                ->where(DB::raw('DATE(attendances.clock_in_time)'), '=', $now->format('Y-m-d'))->first();

            $attendance = new Attendance();
            $attendance->user_id = api_user()->id;
            $attendance->clock_in_time = $now->copy()->timezone(config('app.timezone'));
            $attendance->clock_in_ip = request()->ip();

            if ($now->gt($lateTime) && $isLate === 'yes') {
                $attendance->late = 'yes';
            }

            $leave = Leave::where('leave_date', $attendance->clock_in_time->format('Y-m-d'))
                ->where('status', 'approved')
                ->where('user_id', api_user()->id)->first();

            if (isset($leave) && !is_null($leave->half_day_type)) {
                $attendance->half_day = 'yes';
            }
            else {
                $attendance->half_day = 'no';
            }

            // Check day's first record and half day time
            if (
                !is_null($this->attendanceSettings->halfday_mark_time)
                && is_null($checkTodayAttendance)
                && isset($halfDayTimes)
                && ($now->gt($halfDayTimes))
                && ($showClockIn->show_clock_in_button == 'no') // DO NOT allow half day when allowed outside hours clock-in
            ) {
                $attendance->half_day = 'yes';
            }

            $attendance->employee_shift_id = $this->attendanceSettings->id;

            $attendance->shift_start_time = $attendance->clock_in_time->format('Y-m-d') . ' ' . $this->attendanceSettings->office_start_time;

            if (Carbon::parse($this->attendanceSettings->office_start_time, $this->company->timezone)->gt(Carbon::parse($this->attendanceSettings->office_end_time, $this->company->timezone))) {
                $attendance->shift_end_time = $attendance->clock_in_time->addDay()->format('Y-m-d') . ' ' . $this->attendanceSettings->office_end_time;

            }
            else {
                $attendance->shift_end_time = $attendance->clock_in_time->format('Y-m-d') . ' ' . $this->attendanceSettings->office_end_time;
            }

        $attendance->save();

            if ($attendance) {
                return ApiResponse::make('Clocked in successfully', [
                    'id' => $attendance->id,
                    'time' => $attendance->clock_in_time,
                ]);
            }
        }
        else {
            throw new ApiException('Maximum check-ins reached', null, 422, 422, 2000);
        }
    }

    public function currentClockIn()
    {
        $company = api_user()->company;
        $now = now($company->timezone);
        $currentClockIn = $this->getOpenClockIn(api_user()->id, $company, $now);

        if (!$currentClockIn) {
            $previousDay = $now->copy()->subDay();
            $currentClockIn = $this->getOpenClockIn(api_user()->id, $company, $previousDay);
        }

        if ($currentClockIn) {
            return ApiResponse::make('Clock-in found', [
                'is_clocked_in' => true,
                'can_clock_out' => true,
                'id' => $currentClockIn->id]);
        }
        else {
            return ApiResponse::make('Clock-in not found', [
                'is_clocked_in' => false,
                'can_clock_out' => false,
                'id' => 0]);
        }
    }

    public function clockOut($id)
    {

        $now = now(api_user()->company->timezone);
        $attendance = Attendance::findOrFail($id);

        if ($attendance->user_id !== api_user()->id) {
            throw new ApiException(__('messages.permissionDenied'), null, 403, 403, 2000);
        }

        if ($attendance->clock_out_time) {
            throw new ApiException('User Already clocked out', null, 422, 422, 2000);
        }

        $this->attendanceSettings = AttendanceSetting::first();

        if ($this->attendanceSettings->ip_check == 'yes') {
            $ips = (array)json_decode($this->attendanceSettings->ip_address);

            if (!in_array(request()->ip(), $ips)) {
                throw new ApiException(__('messages.notAnAuthorisedDevice'), null, 422, 422, 2000);
            }
        }

        $attendance->clock_out_time = $now->copy()->timezone(config('app.timezone'));
        $attendance->clock_out_ip = request()->ip();
        $attendance->save();

        return ApiResponse::make('Clocked out successfully', [
            'time' => $attendance->clock_out_time,
            'ip' => $attendance->clock_out_ip,
        ]);
    }

    private function getShiftOfficeWindow($attendanceShiftSettings, AttendanceSetting $showClockIn, Company $company, Carbon $now): array
    {
        $startTimestamp = $now->format('Y-m-d') . ' ' . $attendanceShiftSettings->office_start_time;
        $endTimestamp = $now->format('Y-m-d') . ' ' . $attendanceShiftSettings->office_end_time;
        $officeStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $startTimestamp, $company->timezone);
        $officeEndTime = Carbon::createFromFormat('Y-m-d H:i:s', $endTimestamp, $company->timezone);

        if ($showClockIn->show_clock_in_button == 'yes') {
            $officeEndTime = $now->copy();
        }

        if ($officeStartTime->gt($officeEndTime)) {
            $officeEndTime->addDay();
        }

        return [$officeStartTime, $officeEndTime];
    }

    private function getUserTodayClockInCount($attendanceShiftSettings, Carbon $officeStartTime, Carbon $officeEndTime, int $userId, Company $company): int
    {
        if (($attendanceShiftSettings->shift_type ?? 'strict') == 'strict') {
            return Attendance::getTotalUserClockInWithTime($officeStartTime, $officeEndTime, $userId);
        }

        $Utc = now($company->timezone)->format('P');

        return Attendance::where('user_id', $userId)
            ->whereDate(DB::raw("CONVERT_TZ(clock_in_time, '+00:00', '{$Utc}')"), now($Utc)->format('Y-m-d'))
            ->count();
    }

    private function getUserCompletedClockInCount($attendanceShiftSettings, Carbon $officeStartTime, Carbon $officeEndTime, int $userId, Company $company): int
    {
        if (($attendanceShiftSettings->shift_type ?? 'strict') == 'strict') {
            return Attendance::whereBetween('clock_in_time', [
                $officeStartTime->copy()->timezone(config('app.timezone')),
                $officeEndTime->copy()->timezone(config('app.timezone')),
            ])
                ->where('user_id', $userId)
                ->whereNotNull('clock_out_time')
                ->count();
        }

        $Utc = now($company->timezone)->format('P');

        return Attendance::where('user_id', $userId)
            ->whereDate(DB::raw("CONVERT_TZ(clock_in_time, '+00:00', '{$Utc}')"), now($Utc)->format('Y-m-d'))
            ->whereNotNull('clock_out_time')
            ->count();
    }

    private function getOpenClockIn(int $userId, Company $company, Carbon $now): ?Attendance
    {
        $Utc = $now->format('P');

        return Attendance::whereNull('clock_out_time')
            ->select('id', 'clock_in_time', 'clock_out_time', 'employee_shift_id')
            ->where('user_id', $userId)
            ->whereDate(DB::raw("CONVERT_TZ(clock_in_time, '+00:00', '{$Utc}')"), $now->format('Y-m-d'))
            ->orderByDesc('id')
            ->first();
    }

    private function isWithinRadius($request)
    {
        $this->global = Company::first();
        $attendanceSettings = AttendanceSetting::first();
        $radius = $attendanceSettings->radius;
        $currentLatitude = request()->currentLatitude;
        $currentLongitude = request()->currentLongitude;

        $latFrom = deg2rad($this->global->latitude);
        $latTo = deg2rad($currentLatitude);

        $lonFrom = deg2rad($this->global->longitude);
        $lonTo = deg2rad($currentLongitude);

        $theta = $lonFrom - $lonTo;

        $dist = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($theta);
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $distance = $dist * 60 * 1.1515 * 1609.344;

        return $distance <= $radius;
    }

    public function attendanceShift($defaultAttendanceSettings, $company = null)
    {
        if (!is_null($company)) {
            $this->company = $company;
        }
        else {
            $this->company = api_user()->company;
        }

        $checkPreviousDayShift = EmployeeShiftSchedule::with('shift')->where('user_id', user()->id)
            ->where('date', now($this->company->timezone)->subDay()->toDateString())
            ->first();

        $checkTodayShift = EmployeeShiftSchedule::with('shift')->where('user_id', user()->id)
            ->where('date', now(company()->timezone)->toDateString())
            ->first();

        $backDayFromDefault = Carbon::parse(now($this->company->timezone)->subDay()->format('Y-m-d') . ' ' . $defaultAttendanceSettings->office_start_time, $this->company->timezone);

        $backDayToDefault = Carbon::parse(now($this->company->timezone)->subDay()->format('Y-m-d') . ' ' . $defaultAttendanceSettings->office_end_time, $this->company->timezone);

        if ($backDayFromDefault->gt($backDayToDefault)) {
            $backDayToDefault->addDay();
        }

        $nowTime = Carbon::createFromFormat('Y-m-d H:i:s', now($this->company->timezone)->toDateTimeString(), 'UTC');

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


        if (isset($attendanceSettings->shift)) {
            return $attendanceSettings->shift;
        }

        if ($attendanceSettings instanceof AttendanceSetting) {
            $attendanceSettings->loadMissing('shift');

            if ($attendanceSettings->shift) {
                return $attendanceSettings->shift;
            }
        }

        return $attendanceSettings;

    }
}
