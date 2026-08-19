<?php

namespace Modules\RestAPI\Http\Controllers;

use Modules\RestAPI\Entities\TaskNote;
use Modules\RestAPI\Http\Requests\TaskNote\CreateRequest;
use Modules\RestAPI\Http\Requests\TaskNote\DeleteRequest;
use Modules\RestAPI\Http\Requests\TaskNote\UpdateRequest;

class TaskNoteController extends ApiBaseController
{
    protected $model = TaskNote::class;

    protected $storeRequest = CreateRequest::class;

    protected $updateRequest = UpdateRequest::class;

    protected $deleteRequest = DeleteRequest::class;

    public function storing(TaskNote $taskNote)
    {
        $taskNote->task_id = request()->route('task_id');
        $taskNote->user_id = (int) data_get(api_user(), 'id');

        return $taskNote;
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
