<?php

namespace Modules\RestAPI\Http\Requests\Agent;

use Modules\RestAPI\Http\Requests\BaseRequest;

class HeartbeatRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|string',
            'agent_version' => 'nullable|string|max:20',
            'os' => 'nullable|string|max:50',
            'os_version' => 'nullable|string|max:50',
            'hostname' => 'nullable|string|max:255',
            'is_idle' => 'nullable|boolean',
            'is_paused' => 'nullable|boolean',
            'active_app' => 'nullable|string|max:255',
            'pending_sync_count' => 'nullable|integer|min:0',
            'timestamp' => 'nullable|date',
        ];
    }
}
