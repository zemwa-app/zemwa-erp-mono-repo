<?php

namespace Modules\RestAPI\Http\Controllers;

use App\DataTables\AppreciationsDataTable;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Appreciation\StoreRequest;
use App\Http\Requests\Appreciation\UpdateRequest;
use App\Http\Requests\ClientDocs\CreateRequest;
use App\Http\Requests\EmployeeDocs\DeleteRequest;
use App\Http\Requests\Holiday\IndexRequest;
use App\Models\Award;
use App\Models\User;
use App\Models\Appreciation;
use Carbon\Carbon;
use Froiden\RestAPI\ApiResponse;
use Illuminate\Http\Request;
use Modules\RestAPI\Http\Controllers\ApiBaseController;
use Modules\RestAPI\Http\Requests\Attendance\ShowRequest;

class AppreciationController extends ApiBaseController
{

    protected $model = Appreciation::class;

    protected $indexRequest = IndexRequest::class;

    protected $storeRequest = CreateRequest::class;

    protected $updateRequest = UpdateRequest::class;

    protected $showRequest = ShowRequest::class;

    protected $deleteRequest = DeleteRequest::class;

    public function dashboard()
    {
        app()->make($this->indexRequest);

        $query =  Appreciation::with(['award', 'award.awardIcon', 'awardTo.employeeDetail.designation:id,name', 'awardTo.employeeDetail.department:id,team_name'])->orderByDesc('award_date')
        ->latest()
        ->get()->toArray();

        // $query = Appreciation::select('id', 'award_date', 'award_id', 'user_id', 'to_user_id', 'message')
        //     ->with(['award' => function($q) {
        //         $q->select('id', 'award_name');
        //     }])
        //     ->with(['awardTo' => function($q) {
        //         $q->select('id', 'name');
        //     }]);

        return ApiResponse::make(null, $query);
    }

}
