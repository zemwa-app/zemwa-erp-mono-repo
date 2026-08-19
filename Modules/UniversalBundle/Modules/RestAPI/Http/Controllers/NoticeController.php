<?php

namespace Modules\RestAPI\Http\Controllers;

use Modules\RestAPI\Entities\Notice;
use Modules\RestAPI\Http\Requests\Notice\CreateRequest;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\NoticeBoardUser;
use Froiden\RestAPI\ApiResponse;
use Modules\RestAPI\Http\Requests\Notice\DeleteRequest;
use Modules\RestAPI\Http\Requests\Notice\IndexRequest;
use Modules\RestAPI\Http\Requests\Notice\ShowRequest;
use Modules\RestAPI\Http\Requests\Notice\UpdateRequest;

class NoticeController extends ApiBaseController
{
    protected $model = Notice::class;

    protected $indexRequest = IndexRequest::class;

    // protected $storeRequest = CreateRequest::class;

    protected $updateRequest = UpdateRequest::class;

    protected $showRequest = ShowRequest::class;

    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        return $query->with([
            'files' => function($q) {
                $q->select('id', 'notice_id', 'filename', 'hashname', 'external_link');
            },
            'member' => function($q) {
                $q->select('id', 'notice_id', 'user_id', 'read')
                  ->with(['user' => function($sq) {
                      $sq->select('id', 'name');
                  }]);
            },
            'noticeEmployees' => function($q) {
                $q->with(['user' => function($oq) {
                    $oq->select('id', 'name');
                }]);
            },
            'noticeClients' => function($q) {
                $q->with(['user' => function($oq) {
                    $oq->select('id', 'name');
                }]);
            },
            'department' => function($q) {
                $q->select('id', 'team_name');
            }
        ])->visibility();
    }

    public function index()
    {
        $query = $this->model::query();
        $query = $this->modifyIndex($query);
        
        $notices = $query->get();
        
        return response()->json([
            'data' => $notices,
            'meta' => [
                'paging' => [
                    'total' => $notices->count(),
                    'links' => []
                ],
                'time' => 0.031
            ]
        ]);
    }

     // override store
    public function store()
    {

        $notice = new Notice();
        $notice->heading =  request()->heading;
        $notice->description = trim_editor(request()->description);
        $notice->to = request()->to;
        $notice->department_id = request()->team_id == 0 ? null : request()->team_id;
        $notice->save();

        if ((request()->to == 'employee' && isset(request()->employees)) || (request()->to == 'client' && isset(request()->clients))) {
            $noticeUsers = [];
            $type = request()->to;
            $usersIds = ($type == 'employee') ? request()->employees : request()->clients;
            // check if usersIds is exit 
            $users = User::whereIn('id', $usersIds)->where('status', 'active')->pluck('id')->toArray();

            if (empty($users)) {
                return ApiResponse::make(__('messages.noUsersFound'));
            }

            foreach ($users as $user) {
                $noticeUsers[] = [
                    'notice_id' => $notice->id,
                    'type' => $type,
                    'user_id' => $user
                ];
            }

            if (!empty($noticeUsers)) {
                NoticeBoardUser::insert($noticeUsers);
            }
        }
       
        return ApiResponse::make(__('messages.recordSaved'));

    }

 
}
