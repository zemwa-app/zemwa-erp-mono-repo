<?php

namespace Modules\Zoom\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Modules\Zoom\DataTables\MeetingDataTable;
use Modules\Zoom\Entities\ZoomCategory;
use Modules\Zoom\Entities\ZoomMeeting;
use Modules\Zoom\Entities\ZoomSetting;
use Modules\Zoom\Events\MeetingHostEvent;
use Modules\Zoom\Events\MeetingHostUpdateEvent;
use Modules\Zoom\Events\MeetingInviteEvent;
use Modules\Zoom\Events\MeetingUpdateEvent;
use Modules\Zoom\Http\Requests\ZoomMeeting\StoreMeeting;
use Modules\Zoom\Http\Requests\ZoomMeeting\UpdateMeeting;
use Modules\Zoom\Http\Requests\ZoomMeeting\UpdateOccurrence;
use Modules\Zoom\Services\ZoomApiClient;
use Modules\Zoom\Traits\ZoomSettingsTrait;

class ZoomMeetingController extends AccountBaseController
{

    use ZoomSettingsTrait;

    public function __construct(protected ZoomApiClient $zoomApi)
    {
        parent::__construct();
        $this->pageTitle = __('zoom::app.menu.zoomMeeting');

        $this->middleware(
            function ($request, $next) {

                abort_403(!in_array(ZoomSetting::MODULE_NAME, $this->user->modules));
                $this->setZoomConfigs();

                return $next($request);
            }
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(MeetingDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_zoom_meetings');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        if (!request()->ajax()) {
            $this->employees = User::allEmployees(null, true, ($viewPermission == 'all' ? 'all' : null));
            $this->clients = User::allClients();
            $this->events = ZoomMeeting::all();
            $this->categories = ZoomCategory::all();
            $this->projects = Project::allProjects();
        }

        return $dataTable->render('zoom::meeting.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->pageTitle = __('zoom::modules.zoommeeting.addMeeting');

        $this->addPermission = user()->permission('add_zoom_meetings');

        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->employees = User::allEmployees(null, true, (($this->addPermission == 'all' || $this->addPermission == 'added') ? 'all' : null));
        $this->clients = User::allClients(($this->addPermission == 'all' ? 'all' : null));
        $this->categories = ZoomCategory::all();
        $this->projects = Project::allProjects();
        $this->zoomSetting = ZoomSetting::first();

        if (user()->roles[0]->name == 'client') {

            $this->clients = User::where('id', user()->id)->get();
        }

        if (request()->ajax()) {

            $html = view('zoom::meeting.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'zoom::meeting.ajax.create';

        return view('zoom::meeting.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(StoreMeeting $request)
    {
        $this->createOrUpdateMeetings($request, null);

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('zoom-meetings.index')]);
    }

    /**
     * Show the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $this->event = ZoomMeeting::with('attendees', 'host', 'notes')->findOrFail($id);
        $this->viewPermission = user()->permission('view_zoom_meetings');
        $attendeesIds = $this->event->attendees->pluck('id')->toArray();
        abort_403(
            !(
                $this->viewPermission == 'all'
                || ($this->viewPermission == 'added' && $this->event->added_by == user()->id)
                || ($this->viewPermission == 'owned' && (in_array(user()->id, $attendeesIds || $this->event->created_by == user()->id)))
                || ($this->viewPermission == 'both' && (in_array(user()->id, $attendeesIds) || $this->event->added_by == user()->id || $this->event->created_by == user()->id))
            )
        );

        $this->zoomSetting = ZoomSetting::first();
        $tab = request('view');

        switch ($tab) {

        case 'notes':
            $this->tab = 'zoom::meeting.ajax.notes';
            break;

        default:
            $this->tab = 'zoom::meeting.ajax.notes';
            break;
        }

        if (request()->ajax()) {
            $view = (request('json') == true) ? $this->tab : 'zoom::meeting.ajax.show';
            $html = view($view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'zoom::meeting.ajax.show';

        return view('zoom::meeting.create', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $editPermission = user()->permission('edit_zoom_meetings');

        $this->event = ZoomMeeting::with('attendees')->findOrFail($id);
        $eventUsers = $this->event->attendees->pluck('id')->toArray();

        abort_403(
            !(
                $editPermission == 'all'
                || ($editPermission == 'added' && user()->id == $this->event->added_by)
                || ($editPermission == 'owned' && (in_array(user()->id, $eventUsers) || $this->event->created_by == user()->id))
                || ($editPermission == 'both' && (user()->id == $this->event->added_by || in_array(user()->id, $eventUsers) || $this->event->created_by == user()->id))
            )
        );

        $this->employees = User::allEmployees(null, true, ($editPermission == 'all' ? 'all' : null));
        $this->clients = User::allClients();
        $this->categories = ZoomCategory::all();
        $this->projects = Project::allProjects();

        if (!is_null($this->event->occurrence_id)) {
            if (request()->ajax()) {
                $html = view('zoom::meeting.ajax.edit_occurrence', $this->data)->render();

                return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
            }

            $this->view = 'zoom::meeting.ajax.edit_occurrence';

        }
        else {
            if (request()->ajax()) {
                $html = view('zoom::meeting.ajax.edit', $this->data)->render();

                return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
            }

            $this->view = 'zoom::meeting.ajax.edit';
        }

        return view('zoom::meeting.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(UpdateMeeting $request, $id)
    {

        $this->createOrUpdateMeetings($request, $id);

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('zoom-meetings.index')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $meeting = ZoomMeeting::findOrFail($id);

        // destroy meeting via zoom api
        if (! is_null($meeting->occurrence_id)) {

            if (request()->has('recurring') && request('recurring') == 'yes') {
                $this->zoomApi->deleteMeeting((string) $meeting->meeting_id);
            }
            else {
                $this->zoomApi->deleteMeeting((string) $meeting->meeting_id, (string) $meeting->occurrence_id);
            }
        }
        else {
            $this->zoomApi->deleteMeeting((string) $meeting->meeting_id);
        }

        $meeting->attendees()->detach();
        $meeting->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    // phpcs:ignore
    public function createMeeting(ZoomMeeting $meeting, $id, $host = null)
    {
        $tz = company()->timezone ?: config('app.timezone');
        $start = Carbon::parse($meeting->start_date_time)->timezone($tz);
        $end = Carbon::parse($meeting->end_date_time)->timezone($tz);
        $duration = max(1, (int) $start->diffInMinutes($end));

        $commonSettings = [
            'topic' => $meeting->meeting_name,
            'type' => 2,
            'start_time' => $this->zoomStartTimeLocal($start),
            'duration' => $duration,
            'timezone' => $tz,
            'agenda' => $meeting->description ?? '',
            'settings' => [
                'host_video' => $meeting->host_video == 1,
                'participant_video' => $meeting->participant_video == 1,
            ],
        ];

        if ($host && $host->email) {
            $commonSettings['settings']['alternative_hosts'] = $host->email;
        }

        if (is_null($id)) {
            $savedMeeting = $this->zoomApi->createMeeting($commonSettings, 'me');
            $meeting->meeting_id = (string) ($savedMeeting['id'] ?? $savedMeeting['pmi'] ?? '');
            $meeting->start_link = $savedMeeting['start_url'] ?? null;
            $meeting->join_link = $savedMeeting['join_url'] ?? null;
            $meeting->password = $savedMeeting['password'] ?? null;
            $meeting->save();
        }
        else {
            $this->zoomApi->updateMeeting((string) $meeting->meeting_id, $commonSettings);
        }

        return $meeting;
    }

    public function createOrUpdateMeetings($request, $id)
    {

        $host = User::find($request->create_by);
        $host_user = User::find($request->created_by);

        if ($request->has('repeat') && $request->repeat) {
            $this->createRepeatMeeting($request, $id);

        }
        else {
            $tz = company()->timezone ?: config('app.timezone');
            $startDate = Carbon::createFromFormat(company()->date_format . ' ' . company()->time_format, $request->start_date . ' ' . $request->start_time, $tz);
            $endDate = Carbon::createFromFormat(company()->date_format . ' ' . company()->time_format, $request->end_date . ' ' . $request->end_time, $tz);

            $meeting = is_null($id) ? new ZoomMeeting : ZoomMeeting::find($id);
            $data = $request->all();

            if (!$request->has('send_reminder')) {

                $data['send_reminder'] = 0;
                $data['remind_time'] = 1;
                $data['remind_type'] = 'day';

            }

            $data['meeting_name'] = $request->meeting_title;
            $data['start_date_time'] = $startDate->toDateTimeString();

            $data['end_date_time'] = $endDate->toDateTimeString();
            $data['repeat'] = 0;

            if (is_null($id)) {
                $meeting = $meeting->create($data);
                $this->syncAttendees($request, $meeting);
                $this->createMeeting($meeting, $id, $host);
                $meetingUsers = $meeting->attendees->filter(
                    function ($value, $key) use ($host_user) {
                        return $value->id != $host_user->id;
                    }
                );
                event(new MeetingInviteEvent($meeting, $meetingUsers));

                event(new MeetingHostEvent($meeting, $host_user));

            }
            else {
                $meeting->update($data);
                $this->syncAttendees($request, $meeting);
                $this->createMeeting($meeting, $id, $host);

                $meetingUsers = $meeting->attendees->filter(
                    function ($value, $key) use ($host_user) {
                        return $value->id != $host_user->id;
                    }
                );
                event(new MeetingUpdateEvent($meeting, $meetingUsers));
                event(new MeetingHostUpdateEvent($meeting, $host_user));

            }

        }

    }

    public function syncAttendees($request, $meeting)
    {
        $employees = $request->has('employee_id') ? $request->employee_id : [];
        $clients = $request->has('client_id') ? $request->client_id : [];
        $attendees = array_merge($employees, $clients);

        $meeting->attendees()->sync($attendees);

    }

    /**
     * start zoom meeting in app
     *
     * @return \Illuminate\Http\Response
     */
    public function startMeeting($id)
    {
        $this->zoomSetting = ZoomSetting::first();
        $this->meeting = ZoomMeeting::findOrFail($id);
        $zoom = $this->zoomApi->getMeeting((string) $this->meeting->meeting_id);
        $this->zoomMeeting = (object) [
            'password' => Arr::get($zoom, 'password', $this->meeting->password),
        ];

        return view('zoom::meeting.start_meeting', $this->data);
    }

    /**
     * cancel meeting
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelMeeting()
    {
        $id = request('id');
        ZoomMeeting::where('id', $id)->update(['status' => 'canceled']);

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * end meeting
     *
     * @return \Illuminate\Http\Response
     */
    public function endMeeting()
    {
        $id = request('id');
        $meeting = ZoomMeeting::findOrFail($id);

        $this->zoomApi->endMeeting((string) $meeting->meeting_id);

        $meeting->status = 'finished';
        $meeting->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * create repeated meeting
     *
     * @return \Illuminate\Http\Response
     */
    //phpcs:ignore

    public function createRepeatMeeting($request, $id)
    {
        $host_user = User::find($request->created_by);

        $tz = company()->timezone ?: config('app.timezone');
        $startDate = Carbon::createFromFormat(company()->date_format . ' ' . company()->time_format, $request->start_date . ' ' . $request->start_time, $tz);
        $endDate = Carbon::createFromFormat(company()->date_format . ' ' . company()->time_format, $request->end_date . ' ' . $request->end_time, $tz);

        $meeting = is_null($id) ? new ZoomMeeting : ZoomMeeting::findOrFail($id);
        $data = $request->all();
        $data['meeting_name'] = $request->meeting_title;
        $data['start_date_time'] = $startDate->toDateTimeString();
        $data['end_date_time'] = $endDate->toDateTimeString();

        if (is_null($id)) {

            $meeting = $meeting->create($data);

        }
        else {

            $meeting->update($data);
        }

        $meeting->source_meeting_id = $meeting->id;
        $meeting->occurrence_order = 1;
        $meeting->save();

        $this->syncAttendees($request, $meeting);

        if ($request->repeat_type == 'day') {

            $repeatType = 1;

        }
        elseif ($request->repeat_type == 'week') {

            $repeatType = 2;

        }
        else {

            $repeatType = 3;
        }

        $repeatData = $this->createRepeatData($repeatType, $request);
        $duration = max(1, (int) Carbon::parse($meeting->start_date_time)->timezone($tz)->diffInMinutes(Carbon::parse($meeting->end_date_time)->timezone($tz)));

        $payload = [
            'topic' => $request->meeting_title,
            'type' => 8,
            'start_time' => $this->zoomStartTimeLocal($startDate),
            'duration' => $duration,
            'timezone' => $tz,
            'agenda' => $request->description ?? '',
            'recurrence' => $repeatData,
            'settings' => [
                'host_video' => $request->host_video == 1,
                'participant_video' => $request->participant_video == 1,
            ],
        ];

        $savedMeeting = $this->zoomApi->createMeeting($payload, 'me');

        // Save zoom response data
        $meeting->repeat_type = $request->repeat_type;

        if ($request->repeat_type == 'day') {
            $meeting->repeat_every = $request->repeat_every_daily;
        }
        elseif ($request->repeat_type == 'week') {
            $meeting->repeat_every = $request->repeat_every_weekly;
        }
        else {
            $meeting->repeat_every = $request->repeat_every_monthly;
        }

        if ($request->recurrence_end_date == 'date') {
            $meeting->end_date_time = Carbon::createFromFormat(company()->date_format, $request->recurrence_end_date_date, $tz)->endOfDay()->toDateTimeString();
        }

        $meeting->meeting_id = (string) ($savedMeeting['id'] ?? $savedMeeting['pmi'] ?? '');
        $meeting->start_link = $savedMeeting['start_url'] ?? null;
        $meeting->join_link = $savedMeeting['join_url'] ?? null;
        $meeting->password = $savedMeeting['password'] ?? null;
        $meeting->save();
        event(new MeetingInviteEvent($meeting, $meeting->attendees));

        if ($host_user != null) {
            event(new MeetingHostEvent($meeting, $host_user));
        }
    }

    /**
     * update meeting occurrence
     *
     * @return \Illuminate\Http\Response
     */
    public function updateOccurrence(UpdateOccurrence $request, $id)
    {
        $tz = company()->timezone ?: config('app.timezone');
        $startDate = Carbon::createFromFormat(company()->date_format . ' ' . company()->time_format, $request->start_date . ' ' . $request->start_time, $tz);
        $endDate = Carbon::createFromFormat(company()->date_format . ' ' . company()->time_format, $request->end_date . ' ' . $request->end_time, $tz);

        $zoomMeeting = ZoomMeeting::find($id);
        $data = $request->all();
        $data['start_date_time'] = $startDate->toDateTimeString();
        $data['end_date_time'] = $endDate->toDateTimeString();
        $zoomMeeting->update($data);

        $this->zoomApi->updateMeeting(
            (string) $zoomMeeting->meeting_id,
            [
                'start_time' => $this->zoomStartTimeLocal($startDate),
                'duration' => max(1, (int) $startDate->diffInMinutes($endDate)),
                'timezone' => $tz,
            ],
            (string) $zoomMeeting->occurrence_id
        );

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('zoom-meetings.index')]);
    }

    public function createRepeatData($repeatType, $request)
    {
        $tz = company()->timezone ?: config('app.timezone');

        $repeatData = [
            'type' => $repeatType,
            'repeat_interval' => (int) $request->repeat_every_daily,
        ];

        if ($request->recurrence_end_date == 'date') {
            $endLocal = Carbon::createFromFormat(company()->date_format, $request->recurrence_end_date_date, $tz)->endOfDay();
            $repeatData['end_date_time'] = $endLocal->copy()->utc()->format('Y-m-d\TH:i:s\Z');
        }
        else {
            $repeatData['end_times'] = (int) $request->recurrence_end_date_after;
        }

        switch ($repeatType) {

        case 2:
            $repeatData['repeat_interval'] = (int) $request->repeat_every_weekly;
            $repeatData['weekly_days'] = implode(',', $request->occurs_on ?? []);
            break;

        case 3:
            $repeatData['repeat_interval'] = (int) $request->repeat_every_monthly;

            if ($request->occurs_on_monthly == 'when') {
                $repeatData['monthly_week'] = $request->occurs_month_when;
                $repeatData['monthly_week_day'] = $request->occurs_month_weekday;
            }
            else {
                $repeatData['monthly_day'] = $request->occurs_month_day;
            }

            break;

        default:
            $repeatData['repeat_interval'] = (int) $request->repeat_every_daily;
            break;
        }

        return $repeatData;
    }

    protected function zoomStartTimeLocal(Carbon $moment): string
    {
        return $moment->format('Y-m-d\TH:i:s');
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        case 'change-status':
            $this->changeBulkStatus($request);

            return Reply::success(__('messages.updateSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {

        ZoomMeeting::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    protected function changeBulkStatus($request)
    {
        abort_403(user()->permission('edit_tasks') != 'all');

        ZoomMeeting::whereIn('id', explode(',', $request->row_ids))->update(['status' => 'canceled']);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function calendar(Request $request)
    {
        $viewPermission = user()->permission('view_zoom_meetings');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        if (!request()->ajax()) {
            $this->employees = User::allEmployees();
            $this->clients = User::allClients();
            $this->events = ZoomMeeting::all();
            $this->categories = ZoomCategory::all();
            $this->projects = Project::allProjects();
        }

        if (request('start') && request('end')) {
            $meetingsArray = [];
            $startDate = Carbon::parse(request('start'))->format('Y-m-d');
            $endDate = Carbon::parse(request('end'))->format('Y-m-d');

            $meetings = ZoomMeeting::select(
                'id',
                'meeting_id',
                'created_by',
                'meeting_name',
                'start_date_time',
                'end_date_time',
                'start_link',
                'join_link',
                'status',
                'label_color',
                'occurrence_id',
                'source_meeting_id',
                'occurrence_order'
            );

            if (!is_null($request->start)) {

                $meetings->whereRaw('DATE(zoom_meetings.`start_date_time`) >= ?', [$startDate]);
            }

            if (!is_null($request->end)) {

                $meetings->whereRaw('DATE(zoom_meetings.`end_date_time`) <= ?', [$endDate]);
            }

            if (request()->has('status') && $request->status != 'all') {
                if ($request->status == 'not finished') {

                    $meetings->where('status', '<>', 'finished');

                }
                else {

                    $meetings->where('status', $request->status);

                }
            }

            if (request()->has('employee') && $request->employee != 0 && $request->employee != 'all') {
                $meetings->whereHas(
                    'attendees', function ($query) use ($request) {

                    return $query->where('user_id', $request->employee);

                }
                );
            }

            if (request()->has('client') && $request->client != 0 && $request->client != 'all') {

                $meetings->whereHas(
                    'attendees', function ($query) use ($request) {
                    return $query->where('user_id', $request->client);
                }
                );
            }

            if (request()->has('category') && $request->category != 0 && $request->category != 'all') {

                $meetings->whereHas(
                    'category', function ($query) use ($request) {
                    return $query->where('id', $request->category);
                }
                );
            }

            if (request()->has('project') && $request->project != 0 && $request->project != 'undefined') {
                $meetings->whereHas(
                    'project', function ($query) use ($request) {
                    return $query->where('id', $request->project);
                }
                );
            }

            if ($request->searchText != '') {

                $meetings->where(
                    function ($query) {
                        $query->where('zoom_meetings.meeting_name', 'like', '%' . request('searchText') . '%');
                    }
                );
                $meetings->where(
                    function ($query) {

                        $query->where('zoom_meetings.meeting_name', 'like', '%' . request('searchText') . '%');

                    }
                );
            }

            $meetings = $meetings->get();

            foreach ($meetings as $key => $meeting) {

                $meetingsArray[] = [
                    'id' => $meeting->id,
                    'start' => $meeting->start_date_time,
                    'end' => $meeting->end_date_time,
                    'title' => ($meeting->meeting_name),
                    'color' => $meeting->label_color,
                ];
            }

            return $meetingsArray;
        }

        return view('zoom::meeting.calendar', $this->data);

    }

}
