<?php

namespace Modules\RestAPI\Http\Controllers;

use Carbon\Carbon;
use Froiden\RestAPI\ApiResponse;
use Modules\RestAPI\Entities\EmployeeShift;
use Modules\RestAPI\Entities\Holiday;
use Modules\RestAPI\Entities\User;
use Modules\RestAPI\Http\Requests\Attendance\IndexRequest;

class EmployeeShiftController extends ApiBaseController
{
    protected $model = EmployeeShift::class;

    protected $indexRequest = IndexRequest::class;

    public function filterByDate($query, $dateField, $requestDate) {
        return $query->whereDate($dateField, $requestDate);
    }

    public function employeeShift($date)
    {
        $this->viewAttendancePermission = api_user()->permission('view_attendance');
        $requestDate = Carbon::createFromFormat('Y-m-d', $date);

        // Eager load relationships with filters
        $employees = User::with([
            'leaves' => function ($query) use ($requestDate) {
                $this->filterByDate($query, 'leaves.leave_date', $requestDate)
                    ->where('status', 'approved');
            },
            'shifts' => function ($query) use ($requestDate) {
                $this->filterByDate($query, 'employee_shift_schedules.date', $requestDate);
            },
            'shifts.shift',
        ])->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'employee_details.department_id', 'users.image', 'users.slug')
            ->onlyEmployee()
            ->groupBy('users.id');

        $employees = $employees->get();
        $user = api_user();
        $this->holidays = Holiday::whereDate('holidays.date', $requestDate)->get();

        $final = [];
        $holidayOccasions = [];
        $this->daysInMonth = 1;

        foreach ($employees as $employee) {

            $employeeData = [
                'id' => $employee->id,
                'attendance_type' => 'Shift Not Assigned',
                'name' => $employee->name,
                'shift_id' => '--',
                'shift_name' => '--',
                'shift_code' => '--',
                'shift_color' => '--',
                'shift_start_time' => '--',
                'shift_end_time' => '--',
                'flexible_shift_hours' => '--',
            ];

            foreach ($employee->shifts as $shift) {

                if ($shift->shift->shift_name == 'Day Off') {
                    $employeeData['attendance_type'] = $shift->shift->shift_name;
                    $employeeData['shift_id'] = $shift->id;
                    $employeeData['shift_name'] = $shift->shift->shift_name;
                    $employeeData['shift_code'] = $shift->shift->shift_short_code;
                    $employeeData['shift_color'] = $shift->shift->color;
                    $employeeData['shift_start_time'] = $shift->shift->office_start_time;
                    $employeeData['shift_end_time'] = $shift->shift->office_end_time;
                    $employeeData['flexible_shift_hours'] = $shift->shift->flexible_total_hours;
                }
                else {
                    $employeeData['attendance_type'] = $shift->shift->shift_name;
                    $employeeData['shift_id'] = $shift->shift->id;
                    $employeeData['shift_name'] = $shift->shift->shift_name;
                    $employeeData['shift_code'] = $shift->shift->shift_short_code;
                    $employeeData['shift_color'] = $shift->shift->color;
                    $employeeData['shift_start_time'] = $shift->shift->office_start_time;
                    $employeeData['shift_end_time'] = $shift->shift->office_end_time;
                    $employeeData['flexible_shift_hours'] = $shift->shift->flexible_total_hours;
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

                    if ($employeeData['attendance_type'] == 'Shift Not Assigned' || $employeeData['attendance_type'] == '-') {
                        $employeeData['attendance_type'] = 'Holiday';
                        $holidayOccasions[$holiday->date->day] = $holiday->occassion;
                    }
                }
            }
            $final[] = $employeeData;
        }
        $message = 'Data fetch successfully.';
        if ($final == null) {
            $message = 'No data found.';

        }

        return ApiResponse::make($message, $final);
    }
}
