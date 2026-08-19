<?php

namespace Modules\RestAPI\Http\Controllers;

use Froiden\RestAPI\ApiResponse;
use Modules\RestAPI\Entities\TaskHistory;
use Modules\RestAPI\Http\Requests\SubTask\ShowRequest;

class TaskHistoryController extends ApiBaseController
{
    protected $model = TaskHistory::class;

    public function taskHistory()
    {
        $taskHistory = TaskHistory::where('task_id', request()->route('task_id'))->get();
        $data = [];
        if ($taskHistory->isEmpty())
            $message = __('messages.noRecordFound');
        else
            $message = 'Data fetched successfully';
        foreach ($taskHistory as $history) {
            $data[] = [
                'id' => $history->id,
                'user_id' => $history->user_id,
                'user_name' => $history->user->name,
                'user_image' => $history->user->image_url,
                'details' => __('modules.tasks.' . $history->details),
                'sub_task' => !is_null($history->sub_task_id) ? $history->subTask->title : '',
                'board_column' => !is_null($history->board_column_id) ? $history->boardColumn->column_name : '',
                'board_column_color' => !is_null($history->board_column_id) ? $history->boardColumn->label_color : '',
                'created_at' => $history->created_at->timezone(api_user()->company->timezone)->translatedFormat(api_user()->company->date_format .' '. api_user()->company->time_format)
            ];

        }
        return ApiResponse::make($message, $data);
    }
}
