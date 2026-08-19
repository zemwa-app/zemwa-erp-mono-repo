<?php

namespace Modules\RestAPI\Http\Requests\TaskNote;

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
            'note' => [
                'required',
                function ($attribute, $value, $fail) {
                    $note = trim_editor($value);

                    if ($note === '') {
                        $fail(__('validation.required'));
                    }
                },
            ],
        ];
    }
}
