<?php

namespace Modules\RestAPI\Http\Controllers;

use Modules\RestAPI\Entities\TaskComment;
use Modules\RestAPI\Http\Requests\TaskComment\CreateRequest;
use Modules\RestAPI\Http\Requests\TaskComment\DeleteRequest;
use Modules\RestAPI\Http\Requests\TaskComment\UpdateRequest;

class TaskCommentController extends ApiBaseController
{
    protected $model = TaskComment::class;

    protected $storeRequest = CreateRequest::class;

    protected $updateRequest = UpdateRequest::class;

    protected $deleteRequest = DeleteRequest::class;

    public function storing(TaskComment $taskComment)
    {
        $taskComment->task_id = request()->route('task_id');
        $taskComment->user_id = auth()->id();

        return $taskComment;
    }

    protected function modifyUpdate($query)
    {
        return $query->where('task_id', request()->route('task_id'));
    }

    protected function modifyDelete($query)
    {
        return $query->where('task_id', request()->route('task_id'));
    }
}
