<?php

namespace Modules\RestAPI\Http\Requests\TaskComment;

use Modules\RestAPI\Entities\TaskComment;
use Modules\RestAPI\Http\Requests\BaseRequest;

class DeleteRequest extends BaseRequest
{
    public function authorize()
    {
        $user = api_user();
        $comment = TaskComment::where('task_id', request()->route('task_id'))
            ->find(request()->route('comment'));
        $modules = (array) data_get($user, 'modules', []);
        $userId = (int) data_get($user, 'id', 0);

        return in_array('tasks', $modules)
            && $comment
            && ($user->hasRole('admin') || (int) $comment->user_id === $userId || (int) $comment->added_by === $userId);
    }

    public function rules()
    {
        return [
            //
        ];
    }
}
