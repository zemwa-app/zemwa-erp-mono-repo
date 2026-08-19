<?php

namespace Modules\RestAPI\Http\Controllers;

use App\Models\EmployeeLeaveQuota;
use App\Models\Holiday;
use App\Models\LeaveType;
use App\Models\User;
use App\Scopes\ActiveScope;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Froiden\RestAPI\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\RestAPI\Entities\Leave;
use Modules\RestAPI\Http\Requests\Leave\CreateRequest;
use Modules\RestAPI\Http\Requests\Leave\DeleteRequest;
use Modules\RestAPI\Http\Requests\Leave\IndexRequest;
use Modules\RestAPI\Http\Requests\Leave\MultiDatesApplyRequest;
use Modules\RestAPI\Http\Requests\Leave\ShowRequest;
use Modules\RestAPI\Http\Requests\Leave\UpdateRequest;

class LeaveController extends ApiBaseController
{
    protected $model = Leave::class;

    protected $indexRequest = IndexRequest::class;

    protected $storeRequest = CreateRequest::class;

    protected $updateRequest = UpdateRequest::class;

    protected $showRequest = ShowRequest::class;

    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        return $query->visibility()
            ->join(
                \DB::raw('(SELECT `id` as `a_user_id`, `name` as `employee_name` FROM `users`) as `a`'),
                'a.a_user_id',
                '=',
                'leaves.user_id'
            );
    }

    public function dashboard()
    {
        $startDate = now()->startOfDay()->startOfDay()->format('Y-m-d H:i:s');
        $endDate = now()->endOfDay()->format('Y-m-d H:i:s');

        $approvedLeaves = Leave::with([
            'user:id,name,image,gender',
            'user.employeeDetails' => function ($q) {
                $q->select('id', 'user_id', 'designation_id');
            },
            'user.employeeDetails.designation' => function ($q) {
                $q->select('id', 'name');
            },
            'type:id,type_name,color',
        ])
            ->where('status', 'approved')
            ->whereBetween('leave_date', [$startDate, $endDate])
            ->get(['id', 'user_id', 'leave_date', 'duration', 'reason', 'leave_type_id'])
            ->toArray();

        return ApiResponse::make(null, $approvedLeaves);
    }

    public function byDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $leaves = Leave::with(['user', 'type'])
            ->visibility()
            ->where('leave_date', $request->date)
            ->get();

        return ApiResponse::make('Leaves retrieved successfully', [
            'leaves' => $leaves,
        ]);
    }

    public function byUniqueId(Request $request)
    {
        $request->validate([
            'unique_id' => 'required|string',
        ]);

        $leaves = Leave::with(['user', 'type'])
            ->visibility()
            ->where('unique_id', $request->unique_id)
            ->get();

        return ApiResponse::make('Leaves retrieved successfully', [
            'leaves' => $leaves,
        ]);
    }

    public function multiDatesLeaveApply(MultiDatesApplyRequest $request)
    {
        $leaveType = LeaveType::findOrFail($request->leave_type_id);
        $employee = User::withoutGlobalScope(ActiveScope::class)->with('roles')->findOrFail($request->user_id);

        if ($leaveType && ! $leaveType->leaveTypeCondition($leaveType, $employee)) {
            return ApiResponse::make(__('messages.leaveTypeNotAllowed'), null, 422);
        }

        $employeeLeaveQuota = EmployeeLeaveQuota::whereUserId($request->user_id)
            ->whereLeaveTypeId($request->leave_type_id)
            ->first();

        if (! $employeeLeaveQuota) {
            return ApiResponse::make(__('messages.leaveLimitError'), null, 422);
        }

        $sDate = Carbon::createFromFormat('Y-m-d', $request->multi_start_date);
        $eDate = Carbon::createFromFormat('Y-m-d', $request->multi_end_date);

        $multiDates = [];

        foreach (CarbonPeriod::create($sDate, $eDate) as $date) {
            $multiDates[] = $date->startOfDay();
        }

        $numberOfLeaves = count($multiDates);

        $leavelimit = Leave::checkOverUtilizationMonthlyLimit(
            $request->user_id,
            $request->leave_type_id,
            $leaveType->monthly_limit,
            $numberOfLeaves
        );

        if ($leavelimit) {
            return ApiResponse::make(__('messages.monthlyLeaveLimitError'), null, 422);
        }

        $multiDatesFormatted = collect($multiDates)->map(fn ($d) => $d->format('Y-m-d'));

        $holidays = Holiday::whereIn('date', $multiDatesFormatted)
            ->where(function ($query) use ($employee) {
                $query->where(function ($sub) use ($employee) {
                    $sub->whereJsonContains('department_id_json', $employee->employeeDetails->department_id)
                        ->orWhereNull('department_id_json');
                });
                $query->where(function ($sub) use ($employee) {
                    $sub->whereJsonContains('designation_id_json', $employee->employeeDetails->designation_id)
                        ->orWhereNull('designation_id_json');
                });
                $query->where(function ($sub) use ($employee) {
                    $sub->whereJsonContains('employment_type_json', $employee->employeeDetails->employment_type)
                        ->orWhereNull('employment_type_json');
                });
            })
            ->get('date');

        $multiDates = collect($multiDates)->filter(
            fn ($date) => $holidays->where('date', $date)->isEmpty()
        );

        if ($multiDates->isEmpty()) {
            return ApiResponse::make(__('messages.noLeaveApplyForSelectedDate'), null, 422);
        }

        $multiDatesWithoutHolidayFormatted = $multiDates->map(fn ($d) => $d->format('Y-m-d'));

        $leaveApplied = Leave::whereIn('status', ['approved', 'pending'])
            ->where('user_id', $request->user_id)
            ->whereIn('leave_date', $multiDatesWithoutHolidayFormatted)
            ->get();

        $pendingAppliedLeavesCount = Leave::where('user_id', $request->user_id)
            ->where('status', 'pending')
            ->where('leave_type_id', $request->leave_type_id)
            ->whereBetween('leave_date', [
                $multiDates->first()->copy()->startOfMonth(),
                $multiDates->first()->copy()->endOfMonth(),
            ])
            ->count();

        $halfDayLeavesCount = $leaveApplied->where('status', 'approved')->where('duration', 'half day')->count();
        $fullDayLeavesCount = $leaveApplied->where('status', 'approved')->where('duration', '!=', 'half day')->count();
        $appliedLeavesCount = $fullDayLeavesCount + ($halfDayLeavesCount * 0.5);

        $employeeLeaveQuotaRemaining = $employeeLeaveQuota->leaves_remaining;
        $totalAllowedLeaves = $employeeLeaveQuotaRemaining + $appliedLeavesCount - $pendingAppliedLeavesCount;
        $applyLeavesCount = $multiDates->count();

        if ($totalAllowedLeaves < $applyLeavesCount && $leaveType->over_utilization == 'not_allowed') {
            return ApiResponse::make(__('messages.leaveLimitError'), null, 422);
        }

        $currentMonthLeaves = Leave::where('leave_type_id', $leaveType->id)
            ->where('user_id', $request->user_id)
            ->whereBetween('leave_date', [
                $multiDates->first()->copy()->startOfMonth(),
                $multiDates->first()->copy()->endOfMonth(),
            ])
            ->whereIn('status', ['approved', 'pending'])
            ->get();

        $currentMonthLeavesCount = ($currentMonthLeaves->where('duration', 'half day')->count() * 0.5)
            + $currentMonthLeaves->where('duration', '!=', 'half day')->count();

        if (
            $leaveType->monthly_limit &&
            ($currentMonthLeavesCount + $applyLeavesCount) > $leaveType->monthly_limit &&
            $leaveType->over_utilization == 'not_allowed'
        ) {
            return ApiResponse::make(__('messages.monthlyLeaveLimitError'), null, 422);
        }

        $uniqueId = Str::random(16);
        $leaveIds = [];

        DB::beginTransaction();

        foreach ($leaveApplied as $oldLeave) {
            $oldLeave->status = 'rejected';
            $oldLeave->reject_reason = __('messages.leaveRejectedByNewLeave');
            $oldLeave->save();
        }

        foreach ($multiDates as $leaveDate) {
            $leave = new \App\Models\Leave();
            $leave->user_id = $request->user_id;
            $leave->unique_id = $uniqueId;
            $leave->leave_type_id = $request->leave_type_id;
            $leave->duration = 'multiple';
            $leave->paid = $leaveType->paid;
            $leave->leave_date = $leaveDate->format('Y-m-d');
            $leave->reason = $request->reason;
            $leave->status = $request->status ?? 'pending';
            $leave->save();

            $leaveIds[] = $leave->id;
        }

        DB::commit();

        return ApiResponse::make(__('messages.leaveApplySuccess'), [
            'unique_id' => $uniqueId,
            'leave_ids' => $leaveIds,
        ]);
    }
}
