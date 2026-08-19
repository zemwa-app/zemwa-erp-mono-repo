<?php

namespace Modules\RestAPI\Http\Requests\Notice;

use Modules\RestAPI\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function authorize()
    {
        $user = api_user();
        // Either user has role admin or has permission edit_notice
        // Plus he needs to have notices module enabled from settings
        return in_array('notices', $user->modules) && ($user->hasRole('admin') || $user->can('edit_notice'));
    }

    public function rules()
    {
        return [
            'heading' => 'sometimes|required',
            'to' => 'sometimes|required|in:employee,client',
            'employees' => 'required_if:to,employee|array|min:1',
            'clients' => 'required_if:to,client|array|min:1',
        ];
    }

    public function messages()
    {
        return [
            'to.in' => 'to field can only have value client or employee',
            'employees.0.required_if' => __('messages.atleastOneValidation'),
            'clients.0.required_if' => __('messages.atleastOneValidation')
        ];
    }
}
