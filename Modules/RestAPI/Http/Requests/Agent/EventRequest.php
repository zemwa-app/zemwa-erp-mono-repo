<?php

namespace Modules\RestAPI\Http\Requests\Agent;

use Modules\RestAPI\Http\Controllers\Agent\AgentEventController;
use Modules\RestAPI\Http\Requests\BaseRequest;

class EventRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validTypes = implode(',', AgentEventController::VALID_EVENT_TYPES);

        return [
            '*.employee_id' => 'nullable|string',
            '*.event_type' => 'required|string|in:' . $validTypes,
            '*.payload' => 'nullable|array',
        ];
    }
}
