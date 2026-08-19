<?php

namespace Modules\Monitor\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Modules\Monitor\Services\Analytics\PeriodHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Monitor\Exports\MonitorAnalyticsCsvExport;
use Modules\Monitor\Services\Analytics\MonitorAnalyticsComplianceService;
use Modules\Monitor\Services\Analytics\MonitorAnalyticsDepartmentService;
use Modules\Monitor\Services\Analytics\MonitorAnalyticsHeatmapService;
use Modules\Monitor\Services\Analytics\MonitorAnalyticsIdleService;
use Modules\Monitor\Services\Analytics\MonitorAnalyticsPageService;
use Modules\Monitor\Services\Analytics\MonitorAnalyticsProjectTimeService;
use Modules\Monitor\Services\Analytics\ActivityUsageService;
use Modules\Monitor\Services\Analytics\MonitorAnalyticsScoreService;
use Modules\Monitor\Services\MonitorPermissionScope;

class MonitorAnalyticsController extends AccountBaseController
{
    public function __construct(
        private readonly MonitorAnalyticsPageService $pageService,
        private readonly MonitorAnalyticsScoreService $scoreService,
        private readonly ActivityUsageService $activityUsageService,
        private readonly MonitorAnalyticsHeatmapService $heatmapService,
        private readonly MonitorAnalyticsIdleService $idleService,
        private readonly MonitorAnalyticsDepartmentService $departmentService,
        private readonly MonitorAnalyticsComplianceService $complianceService,
        private readonly MonitorAnalyticsProjectTimeService $projectTimeService,
        private readonly MonitorPermissionScope $permissionScope,
    ) {
        parent::__construct();
        $this->pageTitle = 'monitor::app.analytics';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('monitor', $this->user->modules));

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $this->permissionScope->authorizeView();

        $filters = $this->pageService->parseRequest($request);
        $tabData = $this->pageService->getTabData(company()->id, $filters);

        $this->filters = $filters;
        $this->period = $filters['period'];
        $this->activeTab = $filters['tab'];
        $this->data = array_merge($this->data, $tabData);

        return view('monitor::analytics.index', $this->data);
    }

    public function export(Request $request)
    {
        $this->permissionScope->authorizeView();

        $filters = $this->pageService->parseRequest($request);

        return match ($filters['tab']) {
            MonitorAnalyticsPageService::TAB_COMPLIANCE => $this->exportCompliance(),
            MonitorAnalyticsPageService::TAB_PROJECTS => $this->exportProjectTime($request),
            default => $this->exportScores($request),
        };
    }

    public function exportScores(Request $request)
    {
        $period = PeriodHelper::normalize($request->query('period'), PeriodHelper::DEFAULT_TEAM);
        $data = $this->scoreService->getScoresList(company()->id, $period, null, $request->boolean('below_sixty'));

        return $this->csvDownload(
            'scores',
            $period,
            [
                __('monitor::app.rank'),
                __('app.employee'),
                __('app.menu.teams'),
                __('monitor::app.productivity'),
                __('monitor::app.activeTime'),
            ],
            collect($data['rows'])->map(fn ($row) => [
                $row['rank'],
                $row['name'],
                $row['department'],
                $row['score'],
                $row['active_hours_decimal'],
            ])->all()
        );
    }

    public function scoreDetail(Request $request, int $employee)
    {
        $this->permissionScope->authorizeView();
        $this->permissionScope->authorizeEmployee($employee, company()->id);

        $period = PeriodHelper::normalize($request->query('period'), PeriodHelper::DEFAULT_TEAM);
        $detail = $this->scoreService->getEmployeeScoreDetail(company()->id, $employee);
        $this->employee = $detail['employee'];
        $this->period = $period;
        $this->appUsage = $this->activityUsageService->getEmployeeUsage($employee, $period, 10, ActivityUsageService::KIND_APPS);
        $this->websiteUsage = $this->activityUsageService->getEmployeeUsage($employee, $period, 10, ActivityUsageService::KIND_WEBSITES);
        $this->browsingSummary = $this->activityUsageService->getBrowsingSummary([$employee], $period);
        $this->data = array_merge($this->data, $detail);
        $this->backUrl = route('monitor.analytics.index', ['tab' => MonitorAnalyticsPageService::TAB_SCORES]);
        $this->pageTitle = $this->employee->name . ' — ' . __('monitor::app.productivityScores');

        return view('monitor::analytics.scores.show', $this->data);
    }

    public function heatmap(Request $request, int $employee)
    {
        $this->permissionScope->authorizeView();
        $this->permissionScope->authorizeEmployee($employee, company()->id);

        $days = (int) $request->query('days', 90);
        $detail = $this->heatmapService->getHeatmap($employee, $days);
        $this->employee = $detail['employee'];
        $this->data = array_merge($this->data, $detail);
        $this->backUrl = route('monitor.analytics.index', ['tab' => MonitorAnalyticsPageService::TAB_SCORES]);
        $this->pageTitle = $this->employee->name . ' — ' . __('monitor::app.workPatternHeatmap');

        return view('monitor::analytics.heatmap.show', $this->data);
    }

    public function idle(Request $request, int $employee)
    {
        $this->permissionScope->authorizeView();
        $this->permissionScope->authorizeEmployee($employee, company()->id);

        $date = Carbon::parse($request->query('date', now(company()->timezone)->toDateString()));
        $showAnomalies = in_array(user()->permission('view_monitor'), ['all', 'added']);

        $detail = $this->idleService->getIdleDetail(company()->id, $employee, $date, $showAnomalies);
        $this->employee = $detail['employee'];
        $this->data = array_merge($this->data, $detail);
        $this->backUrl = route('monitor.analytics.index', ['tab' => MonitorAnalyticsPageService::TAB_SCORES]);
        $this->pageTitle = $this->employee->name . ' — ' . __('monitor::app.idleAnalysis');

        return view('monitor::analytics.idle.show', $this->data);
    }

    public function departmentDetail(Request $request, int $department)
    {
        $this->permissionScope->authorizeView();

        $period = PeriodHelper::normalize($request->query('period'), PeriodHelper::DEFAULT_TEAM);
        $detail = $this->departmentService->getDetail(company()->id, $department, $period);
        $this->department = $detail['department'];
        $this->data = array_merge($this->data, $detail);
        $this->period = $period;
        $this->backUrl = route('monitor.analytics.index', ['tab' => MonitorAnalyticsPageService::TAB_DEPARTMENTS, 'period' => $period]);
        $this->pageTitle = $this->department->team_name;

        return view('monitor::analytics.departments.show', $this->data);
    }

    public function exportCompliance()
    {
        $data = $this->complianceService->getCompliance(company()->id);

        return $this->csvDownload(
            'compliance',
            'last-7-days',
            [__('app.employee'), __('monitor::app.issue'), __('monitor::app.dimension')],
            collect($data['non_compliant'])->map(fn ($row) => [
                $row['name'],
                $row['issue'],
                $row['dimension_label'],
            ])->all()
        );
    }

    public function exportProjectTime(Request $request)
    {
        $period = PeriodHelper::normalize($request->query('period'), PeriodHelper::DEFAULT_TEAM);
        $data = $this->projectTimeService->getProjectTime(company()->id, $period);

        return $this->csvDownload(
            'project-time',
            $period,
            [__('app.project'), __('app.duration'), __('monitor::app.budgetStatus'), __('monitor::app.hoursAllocated')],
            collect($data['rows'])->map(fn ($row) => [
                $row['project_name'],
                number_format($row['logged_hours'], 2, '.', ''),
                $row['status_label'],
                $row['budget_hours'] ?? '—',
            ])->all()
        );
    }

    /**
     * @param  array<int, string>  $headings
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function csvDownload(string $feature, string $period, array $headings, array $rows)
    {
        $filename = 'monitoring-' . $feature . '-' . PeriodHelper::slugForExport($period) . '-' . now()->format('Y-m-d') . '.csv';

        return Excel::download(
            new MonitorAnalyticsCsvExport($headings, $rows),
            $filename,
            \Maatwebsite\Excel\Excel::CSV
        );
    }
}
