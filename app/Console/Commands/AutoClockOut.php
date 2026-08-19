<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\EmployeeShift;
use Illuminate\Console\Command;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AutoClockOut extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto-clock-out';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Clock Out for employees who have not clocked out after their shift ends at the specified time';

    /**
     *
     */

    public function handle()
    {

        $companies = Company::active()->select(['companies.id as id', 'timezone'])
            ->join('attendances', 'attendances.company_id', '=', 'companies.id')
            ->whereNull('attendances.clock_out_time')
            ->where(DB::raw('DATE(clock_in_time)'), now()->subDay()->format('Y-m-d'))
            ->orWhere(DB::raw('DATE(clock_in_time)'), now()->format('Y-m-d'))
            ->get();

        if ($companies->isEmpty()) {
            $this->error("No Company's attendance without Clock out found");

            return Command::SUCCESS;
        }

        foreach ($companies as $company) {
            $this->info('-----------Auto ClockOut Initiated------------------');
            $this->info('Company ID: ' . $company->id);

            $runCron = AttendanceSetting::where('company_id', $company->id)->first();

            if ($runCron) {
                if ($runCron->show_clock_in_button == 'yes') {
                    continue;
                }
            }

            $shifts = EmployeeShift::where('company_id', $company->id)->where('shift_name', '<>', 'Day Off')->where('shift_type', 'strict')->get();

            foreach ($shifts as $shift) {
                $this->info('--------------------------------------------------------------------------------------------------');
                $this->info('Shift ID: ' . $shift->id);
                $this->info('Shift Name: ' . $shift->shift_name);

                $shiftStartTime = Carbon::parse($shift->office_start_time, $company->timezone);
                // if early clock in is not null
                if (!is_null($shift->early_clock_in)) {
                    $shiftStartTime->subMinutes($shift->early_clock_in);
                }

                $shiftEndTime = Carbon::parse($shift->office_end_time, $company->timezone);
                // -----------------------------------------------------------------------------------------------
                $startTimestamp = now($company->timezone)->format('Y-m-d') . ' ' . $shift->office_start_time;
                $endTimestamp = now($company->timezone)->format('Y-m-d') . ' ' . $shift->office_end_time;

                $officeStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $startTimestamp, $company->timezone);
                $officeEndTime = Carbon::createFromFormat('Y-m-d H:i:s', $endTimestamp, $company->timezone);

                // shift crossed a day

                if ($officeStartTime->gt($officeEndTime)) {
                    // check if shift end time is less then current time then shift not ended yet
                    if (now($company->timezone)->lessThan($officeEndTime)) {
                        $officeStartTime->subDay();
                    } else if (now($company->timezone)->greaterThan($officeEndTime) && now($company->timezone)->lessThan($officeStartTime)) {
                        $officeStartTime->subDay();
                    } else {
                        $officeEndTime->addDay();
                    }
                }

                // -----------------------------------------------------------------------------------------------
                $this->info('After Shift start time: ' . $officeStartTime);
                $this->info('Shift end time: ' . $officeEndTime);
                $this->info('now = ' . now($company->timezone));

                $autoClockOutTime = $officeEndTime->copy()->addHours((int)$shift->auto_clock_out_time);

                $this->info('Auto clock-out time: ' . $autoClockOutTime);
                $this->info('Query Date = ' . $shiftEndTime->format('Y-m-d'));
                $this->info('shiftendtime : ' . $shiftEndTime);

                $currentTime = now($company->timezone);
                $Utc = now($company->timezone)->format('p');


                $attendances = Attendance::whereNull('clock_out_time')
                    ->select('id', 'auto_clock_out', 'clock_in_time', 'clock_out_time')
                    ->whereNotNull('clock_in_time')
                    ->where('company_id', $company->id)
                    ->where('employee_shift_id', $shift->id)
                    ->where(function ($query) use ($officeStartTime, $officeEndTime, $Utc) {
                        $query->whereBetween(DB::raw("CONVERT_TZ(clock_in_time, '+00:00', '{$Utc}')"), [$officeStartTime, $officeEndTime]);
                    })
                    ->get();


                foreach ($attendances as $attendance) {
                    $this->info('Attendance ID: ' . $attendance->id);

                    $clockInTime = Carbon::parse($attendance->clock_in_time);

                    $this->info('clockInTime' . $clockInTime);
                    $this->info('shiftEndTime' . $shiftEndTime);



                    $this->info('-------Shift start time: ' . $officeStartTime);
                    $this->info('-------Shift end time: ' . $officeEndTime);
                    $this->info('----------------------------');
                    $this->info('clockInTime = ' . $clockInTime);
                    $this->info('autoClockOutTime = ' . $autoClockOutTime);
                    $this->info('shiftEndTime = ' . $shiftEndTime);
                    $this->info('currentTime = ' . $currentTime);
                    $this->info('check if currentTime >=  autoClockOutTime');
                    $this->info('----------------------------');
                    // Check if current time is greater than or equal to shift end time + auto clock out time
                    if ($currentTime->greaterThanOrEqualTo($autoClockOutTime)) {
                        $this->info('Auto clock-out time reached');

                        $clockOutTime = $officeEndTime->copy()->timezone(config('app.timezone'));

                        // Never store a clock-out that precedes clock-in (bad TZ math / overnight edge cases)
                        if ($clockOutTime->lte($clockInTime)) {
                            $clockOutTime = now(config('app.timezone'));
                        }

                        $attendance->clock_out_time = $clockOutTime;
                        $attendance->auto_clock_out = 1;
                        $attendance->save();
                        $this->info('Self Clock out Marked');
                        $this->info("Auto clocked-out attendance ID: {$attendance->id}");
                    }
                }
            }

            $flexibleShifts = EmployeeShift::where('company_id', $company->id)->where('shift_name', '<>', 'Day Off')->where('shift_type', 'flexible')->get();

            foreach ($flexibleShifts as $shift) {
                $this->info('--------------------------------------------------------------------------------------------------');
                $this->info('Shift ID: ' . $shift->id);
                $this->info('Shift Name: ' . $shift->shift_name);

                $attendances = Attendance::whereNull('clock_out_time')
                    ->select('id', 'auto_clock_out', 'clock_in_time', 'clock_out_time')
                    ->whereNotNull('clock_in_time')
                    ->where('company_id', $company->id)
                    ->where('employee_shift_id', $shift->id)
                    ->get();


                foreach ($attendances as $attendance) {
                    $this->info('Attendance ID: ' . $attendance->id);

                    $totalTime = 0;

                    $endTime = now();
                    $this->info('Clocked minutes ' . $attendance->clock_in_time);
                    $totalTime = (int)$totalTime + abs((int)$endTime->diffInSeconds($attendance->clock_in_time));
               
                    $totalMinimumMinutes = ($shift->flexible_total_hours * 60);
                    $clockedTotalMinutes = floor($totalTime / 60);
                    $autoClockoutMinutes = ($shift->flexible_auto_clockout * 60);

                    $this->info('Required minutes ' . $totalMinimumMinutes);
                    $this->info('Clocked minutes ' . $clockedTotalMinutes);
                    $this->info('Auto Clockout minutes ' . $autoClockoutMinutes);

                    if ($clockedTotalMinutes >= ($totalMinimumMinutes + $autoClockoutMinutes)) {
                        $this->info('Auto Clockout done.');

                        $clockOutTime = $attendance->clock_in_time->copy()->addHours((float) $shift->flexible_total_hours);

                        if ($clockOutTime->lte($attendance->clock_in_time)) {
                            $clockOutTime = now(config('app.timezone'));
                        }

                        $attendance->clock_out_time = $clockOutTime->timezone(config('app.timezone'));
                        $attendance->auto_clock_out = 1;
                        $attendance->save();
                    }
                }
            }

            $this->info('Auto clock-out process completed.');
        }

        return Command::SUCCESS;
    }
}
