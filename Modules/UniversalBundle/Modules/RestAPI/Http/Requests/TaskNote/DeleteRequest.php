<?php

namespace Modules\RestAPI\Http\Requests\TaskNote;

use Modules\RestAPI\Entities\TaskNote;
use Modules\RestAPI\Http\Requests\BaseRequest;

class DeleteRequest extends BaseRequest
{
    public function authorize()
    {
        $user = api_user();
        $note = TaskNote::where('task_id', request()->route('task_id'))
            ->find(request()->route('note'));
        $modules = (array) data_get($user, 'modules', []);
        $userId = (int) data_get($user, 'id', 0);

        return in_array('tasks', $modules)
            && $note
            && ($user->hasRole('admin') || (int) $note->user_id === $userId || (int) $note->added_by === $userId);
    }

    public function rules()
    {
        return [
            //
        ];
    }
}
