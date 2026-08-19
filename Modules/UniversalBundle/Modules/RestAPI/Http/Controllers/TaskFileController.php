<?php

namespace Modules\RestAPI\Http\Controllers;

use App\Helper\Files;
use Froiden\RestAPI\ApiResponse;
use Modules\RestAPI\Entities\TaskFile;
use Modules\RestAPI\Http\Requests\TaskFile\CreateRequest;
use Modules\RestAPI\Http\Requests\TaskFile\DeleteRequest;
use Modules\RestAPI\Http\Requests\TaskFile\IndexRequest;
use Modules\RestAPI\Http\Requests\TaskFile\UpdateRequest;

class TaskFileController extends ApiBaseController
{
    protected $model = TaskFile::class;

    protected $indexRequest = IndexRequest::class;

    protected $storeRequest = CreateRequest::class;

    protected $updateRequest = UpdateRequest::class;

    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        return $query->where('task_id', request()->route('task_id'));
    }

    public function store()
    {
        app()->make($this->storeRequest);

        $taskId = (int) request()->route('task_id');
        $userId = (int) data_get(api_user(), 'id');
        $uploadedFiles = request()->file('file');

        if (!is_array($uploadedFiles)) {
            $uploadedFiles = [$uploadedFiles];
        }

        $createdIds = [];

        foreach ($uploadedFiles as $uploadedFile) {
            if (!$uploadedFile) {
                continue;
            }

            $hashName = Files::uploadLocalOrS3($uploadedFile, TaskFile::FILE_PATH.'/'.$taskId);

            $taskFile = new TaskFile();
            $taskFile->task_id = $taskId;
            $taskFile->user_id = $userId;
            $taskFile->filename = $uploadedFile->getClientOriginalName();
            $taskFile->hashname = $hashName;
            $taskFile->size = $uploadedFile->getSize();
            $taskFile->save();

            $createdIds[] = $taskFile->id;
        }

        $files = TaskFile::whereIn('id', $createdIds)->get();

        return ApiResponse::make('Resource created successfully', $files);
    }

    protected function modifyUpdate($query)
    {
        return $query->where('task_id', request()->route('task_id'));
    }

    public function updating(TaskFile $taskFile)
    {
        $uploadedFile = request()->file('file');

        if (is_array($uploadedFile)) {
            $uploadedFile = $uploadedFile[0] ?? null;
        }

        if ($uploadedFile) {
            if (!empty($taskFile->hashname)) {
                Files::deleteFile($taskFile->hashname, TaskFile::FILE_PATH.'/'.$taskFile->task_id);
            }

            $hashName = Files::uploadLocalOrS3($uploadedFile, TaskFile::FILE_PATH.'/'.$taskFile->task_id);
            $taskFile->filename = $uploadedFile->getClientOriginalName();
            $taskFile->hashname = $hashName;
            $taskFile->size = $uploadedFile->getSize();
        }

        if (request()->has('description')) {
            $taskFile->description = request()->description;
        }

        if (request()->has('external_link')) {
            $taskFile->external_link = request()->external_link;
        }

        if (request()->has('external_link_name')) {
            $taskFile->external_link_name = request()->external_link_name;
        }

        return $taskFile;
    }

    protected function modifyDelete($query)
    {
        return $query->where('task_id', request()->route('task_id'));
    }
}
