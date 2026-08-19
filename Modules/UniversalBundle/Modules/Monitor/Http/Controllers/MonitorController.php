<?php

namespace Modules\Monitor\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Monitor\Exports\MonitorReportExport;
use Modules\Monitor\Services\MonitorAgentConfigService;
use Modules\Monitor\Services\MonitorInstallerService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Modules\Monitor\Services\MonitorEmployeeDetailService;
use Modules\Monitor\Services\MonitorLiveStatusService;
use Modules\Monitor\Services\MonitorPermissionScope;
use Modules\Monitor\Services\MonitorReportService;
use Modules\Monitor\Services\MonitorScreenshotService;
use Modules\Monitor\Services\MonitorSeatService;
use Modules\Monitor\Services\Billing\MonitorPackageBillingService;

class MonitorController extends AccountBaseController
{
    public function __construct(
        private readonly MonitorLiveStatusService $liveStatusService,
        private readonly MonitorEmployeeDetailService $employeeDetailService,
        private readonly MonitorScreenshotService $screenshotService,
        private readonly MonitorReportService $reportService,
        private readonly MonitorAgentConfigService $configService,
        private readonly MonitorInstallerService $installerService,
        private readonly MonitorPermissionScope $permissionScope,
        private readonly MonitorSeatService $seatService,
        private readonly MonitorPackageBillingService $packageBillingService,
    ) {
        parent::__construct();
        $this->pageTitle = 'monitor::app.monitorCenter';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('monitor', $this->user->modules));

            return $next($request);
        });
    }

    public function index()
    {
        $this->permissionScope->authorizeView();

        $this->teams = Team::all();
        $companyId = company()->id;
        $this->hasInstallers = $this->installerService->hasAvailableInstaller();
        $this->canManageInstallers = $this->installerService->canManage();
        $this->enabledMonitorSeatCount = $this->seatService->enabledSeatCount($companyId);

        if (!$this->hasInstallers || $this->enabledMonitorSeatCount === 0) {
            $this->summary = [];
            $this->employees = [];
            $this->dashboard = [];

            return view('monitor::dashboard.index', $this->data);
        }

        $liveData = $this->liveStatusService->getLiveStatus($companyId);
        $this->summary = $liveData['summary'];
        $this->employees = $liveData['employees'];
        $this->dashboard = $liveData['dashboard'];

        return view('monitor::dashboard.index', $this->data);
    }

    public function show($id)
    {
        $this->permissionScope->authorizeView();

        $userId = (int) $id;
        $this->employee = $this->employeeDetailService->resolveEmployee($userId, company()->id);

        $dateInput = request('date', now(company()->timezone)->toDateString());
        $this->selectedDate = Carbon::parse($dateInput)->toDateString();
        $date = Carbon::parse($this->selectedDate);

        $this->pageTitle = $this->employee->name . ' — ' . __('monitor::app.activityDetail');

        $tab = request('tab', 'overview');
        $this->activeTab = $tab ?: 'overview';

        switch ($this->activeTab) {
            case 'timeline':
                $this->timelineHours = $this->employeeDetailService->getTimeline($userId, $date);
                $this->view = 'monitor::employees.ajax.timeline';
                break;
            case 'screenshots':
                $this->screenshotFilters = [
                    'task' => request('task', 'all'),
                    'app' => request('app', ''),
                    'project' => request('project', ''),
                    'category' => request('category', ''),
                    'productivity' => request('productivity', ''),
                    'search' => request('search', ''),
                    'attention' => request()->boolean('attention', false),
                    'idle' => request()->boolean('idle', false),
                ];
                $this->screenshotTaskFilter = $this->screenshotFilters['task'];
                $this->screenshotTaskOptions = $this->employeeDetailService->getScreenshotTaskOptions($userId, $date);
                $this->screenshots = $this->employeeDetailService->getScreenshots($userId, $date, $this->screenshotFilters);
                $this->view = 'monitor::employees.ajax.screenshots';
                break;
            case 'network':
                $this->networkLogs = $this->employeeDetailService->getNetworkLogs($userId, $date);
                $this->view = 'monitor::employees.ajax.network';
                break;
            case 'events':
                $this->events = $this->employeeDetailService->getEvents($userId, $date);
                $this->eventScreenshots = $this->employeeDetailService->getScreenshots($userId, $date, []);
                $this->eventTimelineHours = $this->employeeDetailService->getTimeline($userId, $date);
                $appsData = $this->employeeDetailService->getActiveApps($userId, $date);
                $this->eventAppsSummary = $appsData['summary'];
                $this->eventActiveApps = $appsData['apps'];
                $this->eventActiveWebsites = $appsData['websites'];
                $this->eventNetworkLogs = $this->employeeDetailService->getNetworkLogs($userId, $date);
                $this->view = 'monitor::employees.ajax.events';
                break;
            case 'websites':
                $appsData = $this->employeeDetailService->getActiveApps($userId, $date);
                $this->appsSummary = $appsData['summary'];
                $this->activeWebsites = $appsData['websites'];
                $this->view = 'monitor::employees.ajax.websites';
                break;
            case 'apps':
                $appsData = $this->employeeDetailService->getActiveApps($userId, $date);
                $this->appsSummary = $appsData['summary'];
                $this->activeApps = $appsData['apps'];
                $this->view = 'monitor::employees.ajax.apps';
                break;
            default:
                $this->activeTab = 'overview';
                $this->overview = $this->employeeDetailService->getOverview($userId, $date);
                $this->view = 'monitor::employees.ajax.overview';
                break;
        }

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('monitor::employees.show', $this->data);
    }

    public function screenshots(Request $request)
    {
        $this->permissionScope->authorizeView();

        $this->filters = [
            'employee' => $request->query('employee', 'all'),
            'date' => $request->query('date', now(company()->timezone)->toDateString()),
            'category' => $request->query('category', 'all'),
            'search' => $request->query('search', ''),
        ];

        $this->showEmployeeName = $this->filters['employee'] === 'all';
        $this->employeeOptions = $this->screenshotService->getEmployeeOptions(company()->id);
        $this->screenshots = $this->screenshotService->paginate(company()->id, $this->filters);
        $this->pageTitle = __('monitor::app.screenshotsGallery');

        return view('monitor::screenshots.index', $this->data);
    }

    public function screenshotPreview(Request $request)
    {
        $this->permissionScope->authorizeView();

        $this->imageUrl = $request->query('image_url');
        $this->activeApp = $request->query('active_app');
        $this->windowTitle = $request->query('window_title');
        $this->capturedAt = $request->query('captured_at');
        $this->taskHeading = $request->query('task_heading');
        $this->taskProject = $request->query('task_project');
        $this->taskStatus = $request->query('task_status');
        $this->taskPriority = $request->query('task_priority');
        $this->taskDueDate = $request->query('task_due_date');
        $this->taskUrl = $request->query('task_url');

        return view('monitor::employees.ajax.screenshot-modal', $this->data);
    }

    public function reports(Request $request)
    {
        $this->permissionScope->authorizeView();

        $this->filters = $this->reportService->parseFilters($request);
        $this->hasActiveFilters = $this->reportService->hasActiveFilters($this->filters);
        $options = $this->reportService->getFilterOptions(company()->id);
        $this->employees = $options['employees'];
        $this->teams = $options['teams'];
        $this->report = $this->reportService->getReportData(company()->id, $this->filters);
        $this->pageTitle = __('monitor::app.reports');

        return view('monitor::reports.index', $this->data);
    }

    public function exportReports(Request $request)
    {
        $this->permissionScope->authorizeView();

        $filters = $this->reportService->parseFilters($request);
        $report = $this->reportService->getReportData(company()->id, $filters);
        $format = $request->query('format', 'csv');
        $tabLabel = str_replace('_', '-', $filters['tab']);
        $filename = 'monitor-report-' . $tabLabel . '-' . $filters['start_date'] . '-to-' . $filters['end_date'];

        if ($format === 'pdf') {
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('monitor::reports.pdf', [
                'filters' => $filters,
                'report' => $report,
                'headings' => $this->reportService->buildExportHeadings($report),
                'rows' => $this->reportService->buildExportRows($filters, $report),
                'pageTitle' => __('monitor::app.reports'),
                'company' => company(),
            ])->setPaper('a4', 'landscape');

            return $pdf->download($filename . '.pdf');
        }

        return Excel::download(
            new MonitorReportExport($filters, $report, $this->reportService),
            $filename . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function config()
    {
        abort_403(user()->permission('view_monitor') != 'all');

        $this->permissionScope->authorizeView();

        $companyId = company()->id;
        $orgConfig = $this->configService->getOrgConfig($companyId);
        $this->form = $this->configService->getFormState($orgConfig);
        $this->overrides = $this->configService->getOverrideRows($companyId);
        $this->intervalOptions = MonitorAgentConfigService::intervalOptions();
        $this->idleThresholdOptions = MonitorAgentConfigService::idleThresholdOptions();
        $this->monitorSeats = $this->seatService->getSeatRows($companyId);
        $this->enabledMonitorSeatCount = $this->seatService->enabledSeatCount($companyId);
        $this->monitorBillingEnabled = $this->packageBillingService->companyPackageIncludesMonitor(company());
        $this->monitorPerSeatPrice = company()->package?->monitor_per_seat_price;
        $this->pageTitle = __('monitor::app.agentConfig');

        return view('monitor::config.index', $this->data);
    }

    public function toggleMonitoringSeat(Request $request, int $userId)
    {
        $this->permissionScope->authorizeView();

        $request->validate([
            'monitoring_enabled' => 'required|boolean',
        ]);

        $this->seatService->setMonitoringEnabled(
            company()->id,
            $userId,
            $request->boolean('monitoring_enabled'),
        );

        return Reply::successWithData(__('messages.updateSuccess'), [
            'enabledMonitorSeatCount' => $this->seatService->enabledSeatCount(company()->id),
        ]);
    }

    public function storeConfig(Request $request)
    {
        $this->permissionScope->authorizeView();

        $request->validate([
            'screenshot_interval' => 'required|integer|in:' . implode(',', MonitorAgentConfigService::intervalOptions()),
            'screenshot_quality' => 'required|in:low,medium,high',
            'idle_threshold' => 'required|integer|in:' . implode(',', MonitorAgentConfigService::idleThresholdOptions()),
            'large_transfer_mb' => 'required|integer|min:1|max:1000',
        ]);

        $this->configService->saveOrgConfig(company()->id, $request);

        return Reply::successWithData(__('monitor::app.configSaved'), [
            'redirectUrl' => route('monitor.config.index'),
        ]);
    }

    public function createOverride()
    {
        $this->permissionScope->authorizeView();

        $companyId = company()->id;
        $orgConfig = $this->configService->getOrgConfig($companyId);
        $this->form = $this->configService->getFormState($orgConfig);
        $this->employees = $this->configService->getEmployeeOptions($companyId);
        $this->override = null;
        $this->intervalOptions = MonitorAgentConfigService::intervalOptions();
        $this->idleThresholdOptions = MonitorAgentConfigService::idleThresholdOptions();
        $this->pageTitle = __('monitor::app.addOverride');

        return view('monitor::config.ajax.override-form', $this->data);
    }

    public function editOverride(int $id)
    {
        $this->permissionScope->authorizeView();

        $companyId = company()->id;
        $override = $this->configService->getOverrideForForm($companyId, $id);
        $merged = $this->configService->mergeConfigArrays(
            $this->configService->getOrgConfig($companyId),
            [
                'screenshot' => $override->screenshot ?? [],
                'app_tracking' => $override->app_tracking ?? [],
                'keyboard' => $override->keyboard ?? [],
                'network' => $override->network ?? [],
            ]
        );

        $this->form = $this->configService->getFormState($merged);
        $this->employees = $this->configService->getEmployeeOptions($companyId, $override->user_id);
        $this->override = $override;
        $this->intervalOptions = MonitorAgentConfigService::intervalOptions();
        $this->idleThresholdOptions = MonitorAgentConfigService::idleThresholdOptions();
        $this->pageTitle = __('monitor::app.editOverride');

        return view('monitor::config.ajax.override-form', $this->data);
    }

    public function storeOverride(Request $request)
    {
        $this->permissionScope->authorizeView();

        $request->validate([
            'screenshot_interval' => 'required|integer|in:' . implode(',', MonitorAgentConfigService::intervalOptions()),
            'screenshot_quality' => 'required|in:low,medium,high',
            'idle_threshold' => 'required|integer|in:' . implode(',', MonitorAgentConfigService::idleThresholdOptions()),
            'large_transfer_mb' => 'required|integer|min:1|max:1000',
        ]);

        $this->configService->saveOverride(company()->id, $request);

        return Reply::successWithData(__('messages.recordSaved'), [
            'redirectUrl' => route('monitor.config.index'),
        ]);
    }

    public function updateOverride(Request $request, int $id)
    {
        $this->permissionScope->authorizeView();

        $request->validate([
            'screenshot_interval' => 'required|integer|in:' . implode(',', MonitorAgentConfigService::intervalOptions()),
            'screenshot_quality' => 'required|in:low,medium,high',
            'idle_threshold' => 'required|integer|in:' . implode(',', MonitorAgentConfigService::idleThresholdOptions()),
            'large_transfer_mb' => 'required|integer|min:1|max:1000',
        ]);

        $this->configService->saveOverride(company()->id, $request, $id);

        return Reply::successWithData(__('messages.updateSuccess'), [
            'redirectUrl' => route('monitor.config.index'),
        ]);
    }

    public function destroyOverride(int $id)
    {
        $this->permissionScope->authorizeView();

        $this->configService->deleteOverride(company()->id, $id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function installer()
    {
        $this->permissionScope->authorizeView();

        $data = $this->installerService->getPageData();
        $this->version = $data['version'];
        $this->released_at = $data['released_at'];
        $this->released_at_label = $data['released_at_label'];
        $this->platforms = $data['platforms'];
        $this->can_manage = false;
        $this->pageTitle = __('monitor::app.downloadAgentInstaller');

        return view('monitor::installer.index', $this->data);
    }

    public function downloadInstaller(string $platform): BinaryFileResponse|RedirectResponse
    {
        $this->permissionScope->authorizeView();

        return $this->installerService->download($platform);
    }

    public function liveStatus(Request $request): JsonResponse
    {
        $this->permissionScope->authorizeView();

        $department = $request->query('department');
        $departmentId = $department && $department !== 'all' ? (int) $department : null;

        $data = $this->liveStatusService->getLiveStatus(company()->id, $departmentId);

        return response()->json($data);
    }
}
