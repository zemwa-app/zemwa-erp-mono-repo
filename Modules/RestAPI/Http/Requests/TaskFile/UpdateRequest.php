<?php

namespace Modules\RestAPI\Http\Requests\TaskFile;

use Modules\RestAPI\Entities\TaskFile;
use Modules\RestAPI\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function authorize()
    {
        $user = api_user();
        $file = TaskFile::where('task_id', request()->route('task_id'))
            ->find(request()->route('file'));
        $modules = (array) data_get($user, 'modules', []);
        $userId = (int) data_get($user, 'id', 0);

        return in_array('tasks', $modules)
            && $file
            && ($user->hasRole('admin') || (int) $file->user_id === $userId || (int) $file->added_by === $userId);
    }

    public function rules()
    {
        return [
            'file' => 'sometimes|file',
            'description' => 'sometimes|nullable|string',
            'external_link' => 'sometimes|nullable|string',
            'external_link_name' => 'sometimes|nullable|string',
        ];
    }
}
