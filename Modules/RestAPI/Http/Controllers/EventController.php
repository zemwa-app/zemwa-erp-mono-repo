<?php

namespace Modules\RestAPI\Http\Controllers;

use Carbon\Carbon;
use Froiden\RestAPI\ApiResponse;
use Modules\RestAPI\Entities\Event;
use Modules\RestAPI\Entities\Holiday;
use Modules\RestAPI\Http\Requests\Event\CreateRequest;
use Modules\RestAPI\Http\Requests\Event\DeleteRequest;
use Modules\RestAPI\Http\Requests\Event\IndexRequest;
use Modules\RestAPI\Http\Requests\Event\ShowRequest;
use Modules\RestAPI\Http\Requests\Event\UpdateRequest;

class EventController extends ApiBaseController
{
    protected $model = Event::class;

    protected $indexRequest = IndexRequest::class;

    protected $storeRequest = CreateRequest::class;

    protected $updateRequest = UpdateRequest::class;

    protected $showRequest = ShowRequest::class;

    protected $deleteRequest = DeleteRequest::class;

    public function me()
    {
        app()->make($this->indexRequest);

        $startMonth = request()->month ? request()->month : now()->month;
        $now = now();

        $startYear = request()->year ? request()->year : $now->year;

        if (request()->date) {
            $startDate = Carbon::parse(request()->date)->startOfDay()->format('Y-m-d H:i:s');
            $endDate = Carbon::parse(request()->date)->endOfDay()->format('Y-m-d H:i:s');
        }
        else {
            $startDate = now()->year($startYear)->month($startMonth)->startOfMonth()->format('Y-m-d H:i:s');
            $endDate = now()->year($startYear)->month($startMonth)->endOfMonth()->format('Y-m-d H:i:s');
        }

        $query = Holiday::select('id', 'date', 'occassion');

        // Modify query
        $query->where('holidays.date', '>=', $startDate)->where('holidays.date', '<=', $endDate);

        $holidays = $query->get()->toArray();

        $events = Event::with('attendees')
            ->select('events.id', 'event_name', 'start_date_time', 'end_date_time', 'where', 'label_color');
        // Modify query
        $events->join('event_attendees', 'event_attendees.event_id', '=', 'events.id')
            ->where('events.start_date_time', '>=', $startDate)
            ->where('events.end_date_time', '<=', $endDate)->groupBy('event_attendees.event_id');

        $events = $events->get()->toArray();

        $results = [
            'holidays' => $holidays,
            'events' => $events,
        ];

        return ApiResponse::make(null, $results);
    }
}
