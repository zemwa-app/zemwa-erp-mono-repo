<?php

namespace Modules\RestAPI\Http\Requests\Agent;

use Modules\RestAPI\Http\Requests\BaseRequest;

class PauseRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|string',
            'reason' => 'nullable|string|max:255',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'started_at' => 'required|date',
        ];
    }
}
