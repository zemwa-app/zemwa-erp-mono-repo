<?php

namespace Modules\Monitor\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Monitor\Services\MonitorReportService;

class MonitorReportExport implements FromCollection, WithHeadings, WithTitle
{
    /**
     * @param  array<string, mixed>  $filters
     * @param  array<string, mixed>  $reportData
     */
    public function __construct(
        private readonly array $filters,
        private readonly array $reportData,
        private readonly MonitorReportService $reportService,
    ) {
    }

    public function collection(): Collection
    {
        return collect($this->reportService->buildExportRows($this->filters, $this->reportData));
    }

    public function headings(): array
    {
        return $this->reportService->buildExportHeadings($this->reportData);
    }

    public function title(): string
    {
        return match ($this->reportData['tab']) {
            MonitorReportService::TAB_APP_USAGE => __('monitor::app.appUsage'),
            MonitorReportService::TAB_IDLE => __('monitor::app.idleAnalysis'),
            MonitorReportService::TAB_SCREENSHOTS => __('monitor::app.screenshotsSummary'),
            default => __('monitor::app.productivitySummary'),
        };
    }
}
