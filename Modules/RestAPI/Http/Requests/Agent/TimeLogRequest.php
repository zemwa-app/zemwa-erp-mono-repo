<?php

namespace Modules\RestAPI\Http\Requests\Agent;

use Modules\RestAPI\Http\Requests\BaseRequest;

class TimeLogRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '*.task_id' => 'required',
            '*.started_at' => 'required|date',
            '*.ended_at' => 'nullable|date',
            '*.duration_seconds' => 'required|integer|min:1',
            '*.note' => 'nullable|string|max:2000',
            '*.task_name' => 'nullable|string|max:255',
            '*.project_id' => 'nullable',
            '*.project_name' => 'nullable|string|max:255',
            '*.employee_id' => 'nullable|string',
            '*.company_id' => 'nullable',
        ];
    }
}
