<?php

namespace Modules\RestAPI\Http\Requests\TaskComment;

use Modules\RestAPI\Entities\Task;
use Modules\RestAPI\Http\Requests\BaseRequest;

class CreateRequest extends BaseRequest
{
    public function authorize()
    {
        $user = api_user();
        $task = Task::find(request()->route('task_id'));
        $modules = (array) data_get($user, 'modules', []);

        return in_array('tasks', $modules) && $task && $task->visibleTo($user);
    }

    public function rules()
    {
        return [
            'comment' => [
                'required',
                function ($attribute, $value, $fail) {
                    $comment = trim_editor($value);

                    if ($comment === '') {
                        $fail(__('validation.required'));
                    }
                },
            ],
        ];
    }
}
