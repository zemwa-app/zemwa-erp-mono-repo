<?php
namespace App\Http\Requests\ClockIn;

use App\Helper\AttendanceLocation;
use App\Helper\AttendanceWorkFrom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class CreateRequest
 * @package App\Http\Requests\Admin\Employee
 */
class ClockInRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    public function authorize()
    {
        // If admin
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $clockOutTime = $this->input('clock_out_time');
        $clockOutTimeWorkFromType = $this->input('clock_out_time_work_from_type');
        $settings = attendance_setting();
        $allowedTypes = AttendanceWorkFrom::allowedTypes($settings);

        $rules = [
            'work_from_type'  => ['required', Rule::in($allowedTypes)],
            'working_from'  => 'required_if:work_from_type,==,other',
        ];

        if ($clockOutTime){

            $rules['clock_out_time_work_from_type'] = ['required', Rule::in($allowedTypes)];

            if($clockOutTimeWorkFromType == 'other') {

                $rules['clock_out_time_working_from'] = 'required';
            }
        }

        // GPS is required for employee self clock-in only — admin attendance edit has no location fields
        if (
            !$this->routeIs('attendances.update')
            && $settings
            && AttendanceLocation::isRequired($settings, $this->input('work_from_type'))
        ) {
            $rules['currentLatitude'] = 'required|numeric|between:-90,90';
            $rules['currentLongitude'] = 'required|numeric|between:-180,180';
        }

        return $rules;

    }

    public function messages()
    {
        return [
            'work_from_type.in' => __('messages.workFromTypeNotAllowed'),
            'clock_out_time_work_from_type.in' => __('messages.workFromTypeNotAllowed'),
        ];
    }

}
