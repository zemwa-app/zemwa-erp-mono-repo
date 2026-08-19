<?php

namespace Modules\RestAPI\Http\Requests\Leave;

use Modules\RestAPI\Http\Requests\BaseRequest;

class MultiDatesApplyRequest extends BaseRequest
{
    public function authorize()
    {
        $user = api_user();

        return in_array('leaves', $user->modules)
            && ($user->hasRole('admin') || $user->hasRole('employee') || $user->can('add_leave'));
    }

    public function rules()
    {
        return [
            'user_id'         => 'required|exists:users,id',
            'leave_type_id'   => 'required|exists:leave_types,id',
            'multi_start_date' => 'required|date_format:Y-m-d',
            'multi_end_date'   => 'required|date_format:Y-m-d|after_or_equal:multi_start_date',
            'reason'          => 'required|string',
            'status'          => 'nullable|in:pending,approved,rejected',
        ];
    }
}
