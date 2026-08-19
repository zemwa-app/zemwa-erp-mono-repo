<?php

namespace App\Http\Requests\AttendanceSetting;

use App\Helper\AttendanceWorkFrom;
use App\Http\Requests\CoreRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAttendanceSetting extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $data = [
            'allowed_work_from' => 'required|array|min:1',
            'allowed_work_from.*' => ['required', Rule::in(AttendanceWorkFrom::TYPES)],
        ];

        if($this->radius_check == 'yes')
        {
            $data['radius'] = 'required';
        }

        if($this->has('alert_after_status') && $this->has('alert_after_status') == 'on')
        {
            $data['alert_after'] = 'required';
        }

        if($this->has('monthly_report') && $this->has('monthly_report') == '1')
        {
            $data['monthly_report_roles'] = 'required';
        }

        return $data;
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            if ($this->auto_clock_in != 'yes') {
                return;
            }

            $allowed = (array) $this->allowed_work_from;
            $location = $this->auto_clock_in_location;

            if ($location && !in_array($location, $allowed, true)) {
                $validator->errors()->add(
                    'auto_clock_in_location',
                    __('messages.autoClockInLocationNotAllowed')
                );
            }
        });
    }

}
