<?php

namespace Modules\RestAPI\Http\Requests\Agent;

use Modules\RestAPI\Http\Requests\BaseRequest;

class ResumeRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|string',
            'resumed_at' => 'required|date',
        ];
    }
}
