<?php

namespace Modules\Monitor\Services\Analytics;

use Modules\Monitor\Services\Analytics\PeriodHelper;
use Illuminate\Http\Request;

class MonitorAnalyticsPageService
{
    public const TAB_SCORES = 'scores';

    public const TAB_DEPARTMENTS = 'departments';

    public const TAB_COMPLIANCE = 'compliance';

    public const TAB_PROJECTS = 'projects';

    public function __construct(
        private readonly MonitorAnalyticsScoreService $scoreService,
        private readonly MonitorAnalyticsDepartmentService $departmentService,
        private readonly MonitorAnalyticsComplianceService $complianceService,
        private readonly MonitorAnalyticsProjectTimeService $projectTimeService,
        private readonly ActivityUsageService $activityUsageService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function parseRequest(Request $request): array
    {
        $tab = $request->query('tab', self::TAB_SCORES);

        if (!in_array($tab, [self::TAB_SCORES, self::TAB_DEPARTMENTS, self::TAB_COMPLIANCE, self::TAB_PROJECTS], true)) {
            $tab = self::TAB_SCORES;
        }

        $period = PeriodHelper::normalize($request->query('period'), PeriodHelper::DEFAULT_TEAM);
        $departmentId = $request->query('department');
        $departmentId = $departmentId && $departmentId !== 'all' ? (int) $departmentId : null;

        return [
            'tab' => $tab,
            'period' => $period,
            'department_id' => $departmentId,
            'below_sixty_only' => $request->boolean('below_sixty'),
            'search' => \Modules\Monitor\Support\ListSearch::normalize($request->query('search')),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getTabData(int $companyId, array $filters): array
    {
        $employeeUserIds = $this->scoreService->getEmployees($companyId, $filters['department_id'])->pluck('id')->all();
        $search = $filters['search'] ?? '';

        return match ($filters['tab']) {
            self::TAB_DEPARTMENTS => $this->departmentService->getOverview($companyId, $filters['period'], $search),
            self::TAB_COMPLIANCE => $this->complianceService->getCompliance($companyId, $search),
            self::TAB_PROJECTS => $this->projectTimeService->getProjectTime($companyId, $filters['period'], $search),
            default => array_merge(
                $this->scoreService->getScoresList(
                    $companyId,
                    $filters['period'],
                    $filters['department_id'],
                    $filters['below_sixty_only'],
                    $search
                ),
                [
                    'browsing_summary' => $this->activityUsageService->getBrowsingSummary(
                        $employeeUserIds,
                        $filters['period']
                    ),
                    'top_unproductive' => $this->activityUsageService->getTopUnproductiveOrg(
                        $companyId,
                        $filters['period'],
                        3,
                        false,
                        $employeeUserIds
                    ),
                    'top_unproductive_websites' => $this->activityUsageService->getTopUnproductiveOrg(
                        $companyId,
                        $filters['period'],
                        3,
                        true,
                        $employeeUserIds
                    ),
                ]
            ),
        };
    }
}
