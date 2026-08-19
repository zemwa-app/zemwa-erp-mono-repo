@php
    $score = (float) ($overview['productivity_score'] ?? 0);
    $productivityStatusLabel = $overview['productivity_status_label'] ?? 'Good';
    $productivityStatusTone = $overview['productivity_status_tone'] ?? 'gray';
    $liveStatusLabel = $overview['status_label'] ?? 'Offline';
    $liveStatusTone = $overview['status_tone'] ?? 'gray';
    $trendComparison = $overview['trend_comparison'] ?? ['label' => '+0.0% vs last week', 'delta_pct' => 0];
    $managerSummary = $overview['manager_summary'] ?? ['text' => 'No summary available.'];
    $topApps = $overview['top_apps'] ?? [];
    $trend7 = $overview['trend_7'] ?? ['points' => [], 'best_day' => null, 'worst_day' => null, 'employee_avg' => 0, 'team_avg' => 0];
    $trend30 = $overview['trend_30'] ?? ['points' => [], 'best_day' => null, 'worst_day' => null, 'employee_avg' => 0, 'team_avg' => 0];
    $summaryApps = collect($topApps)->take(3)->pluck('app_name')->filter()->implode(', ');
    $attentionCount = count($overview['attention_insights']['attention'] ?? []);
    $positiveCount = count($overview['positive_insights']['positive'] ?? []);
    $appLink = route('monitor.show', $employee->id) . '?tab=apps&date=' . $selectedDate;
@endphp

<div class="p-20">
    <div class="row">
        <div class="col-lg-8 mb-3">
            @include('monitor::employees.ajax.partials.employee-health-card', [
                'overview' => $overview,
                'score' => $score,
                'productivityStatusLabel' => $productivityStatusLabel,
                'productivityStatusTone' => $productivityStatusTone,
            ])

            @include('monitor::employees.ajax.partials.activity-timeline', [
                'timeline' => $overview['timeline'] ?? [],
            ])
        </div>

        <div class="col-lg-4 mb-3">
            @include('monitor::employees.ajax.partials.manager-summary-card', [
                'overview' => $overview,
                'managerSummary' => $managerSummary,
                'score' => $score,
                'trendComparison' => $trendComparison,
                'summaryApps' => $summaryApps,
                'attentionCount' => $attentionCount,
                'positiveCount' => $positiveCount,
            ])

            @include('monitor::employees.ajax.partials.live-status-widget', [
                'overview' => $overview,
            ])
        </div>
    </div>

    <div class="card bg-white border-0 b-shadow-4 mb-3">
        <div class="card-header bg-white border-bottom-grey p-20">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Attention & Insights</h4>
                    <p class="f-12 text-lightest mb-0 mt-1">Signals that help a manager decide whether to review, nudge, or leave the day alone.</p>
                </div>
                <div class="d-flex flex-wrap">
                    <span class="badge badge-success mr-2 mb-1">{{ $positiveCount }} positive signals</span>
                    <span class="badge {{ $attentionCount > 0 ? 'badge-warning' : 'badge-secondary' }} mb-1">
                        {{ $attentionCount }} items need review
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="row no-gutters">
                @include('monitor::employees.ajax.partials.insight-card', [
                    'title' => 'Positive Insights',
                    'subtitle' => 'What is working well today',
                    'items' => $overview['positive_insights']['positive'] ?? [],
                    'tone' => 'green',
                    'emptyText' => 'No standout positives yet',
                    'borderRight' => true,
                ])
                @include('monitor::employees.ajax.partials.insight-card', [
                    'title' => 'Attention Items',
                    'subtitle' => 'What may need a quick manager review',
                    'items' => $overview['attention_insights']['attention'] ?? [],
                    'tone' => 'amber',
                    'emptyText' => 'No unusual activity detected',
                ])
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-3">
            @include('monitor::employees.ajax.partials.application-usage-card', [
                'topApps' => $topApps,
                'selectedDate' => $selectedDate,
                'appLink' => $appLink,
            ])
        </div>
        <div class="col-lg-4 mb-3">
            @include('monitor::employees.ajax.partials.work-distribution-card', [
                'workDistribution' => $overview['work_distribution'] ?? [],
            ])
        </div>
    </div>

    @include('monitor::employees.ajax.partials.productivity-trend-card', [
        'trend7' => $trend7,
        'trend30' => $trend30,
        'trendComparison' => $trendComparison,
    ])

    @include('monitor::employees.ajax.partials.raw-metrics-card', [
        'overview' => $overview,
    ])
</div>

@push('scripts')
    <script>
        $(function () {
            const $chart = $('[data-trend-chart]').first();

            if (!$chart.length || typeof window.Chart === 'undefined') {
                return;
            }

            const readSeries = (value) => {
                if (typeof value === 'string') {
                    try {
                        return JSON.parse(value);
                    } catch (error) {
                        return {};
                    }
                }

                return value || {};
            };

            const series7 = readSeries($chart.data('trend-7'));
            const series30 = readSeries($chart.data('trend-30'));
            const canvas = $chart.find('[data-trend-canvas]')[0];
            const chart = {
                bestLabel: $chart.find('[data-best-day]'),
                worstLabel: $chart.find('[data-worst-day]'),
                avgLabel: $chart.find('[data-avg-label]'),
                rangeLabel: $chart.find('[data-range-label]'),
                buttons: $chart.find('[data-range-toggle]'),
                comparison: $chart.find('[data-trend-comparison]'),
                bestSummary: $chart.find('[data-best-summary]'),
                worstSummary: $chart.find('[data-worst-summary]'),
            };

            const datasets = { '7': series7, '30': series30 };
            let trendChart = window.monitorTrendChart || null;

            const setSummary = (dataset, range) => {
                chart.rangeLabel.text(range === '30' ? '30-day trend' : '7-day trend');
                chart.avgLabel.text(`${Number(dataset.employee_avg ?? 0).toFixed(1)}%`);
                chart.bestLabel.text(dataset.best_day ? `${dataset.best_day.label} · ${Number(dataset.best_day.employee_score ?? 0).toFixed(1)}%` : 'No best day yet');
                chart.worstLabel.text(dataset.worst_day ? `${dataset.worst_day.label} · ${Number(dataset.worst_day.employee_score ?? 0).toFixed(1)}%` : 'No worst day yet');
                chart.bestSummary.text(dataset.best_day ? `${dataset.best_day.label} was the strongest day` : 'Strongest day not available');
                chart.worstSummary.text(dataset.worst_day ? `${dataset.worst_day.label} was the weakest day` : 'Weakest day not available');

                const employeeAvg = Number(dataset.employee_avg ?? 0);
                const teamAvg = Number(dataset.team_avg ?? 0);
                const delta = Math.abs(employeeAvg - teamAvg).toFixed(1);

                chart.comparison.text(employeeAvg >= teamAvg
                    ? `${delta}% above team average`
                    : `${delta}% below team average`);
            };

            const renderChart = (range) => {
                const dataset = datasets[range] || datasets['7'];
                const points = Array.isArray(dataset.points) ? dataset.points : [];
                setSummary(dataset, range);

                chart.buttons.removeClass('btn-primary').addClass('btn-secondary');
                chart.buttons.filter(`[data-range-toggle="${range}"]`).removeClass('btn-secondary').addClass('btn-primary');

                if (!canvas) {
                    return;
                }

                if (trendChart) {
                    trendChart.destroy();
                    trendChart = null;
                }

                if (!points.length) {
                    return;
                }

                trendChart = new window.Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: points.map((point) => point.label ?? point.date ?? ''),
                        datasets: [
                            {
                                label: 'Employee',
                                data: points.map((point) => Number(point.employee_score ?? 0)),
                                borderColor: '#111827',
                                backgroundColor: 'rgba(17, 24, 39, 0.12)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 3,
                                pointHoverRadius: 5,
                                pointBackgroundColor: '#111827',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                borderWidth: 2.5,
                            },
                            {
                                label: 'Team average',
                                data: points.map((point) => Number(point.team_score ?? 0)),
                                borderColor: '#9ca3af',
                                backgroundColor: 'rgba(156, 163, 175, 0.06)',
                                fill: false,
                                tension: 0.35,
                                pointRadius: 2.5,
                                pointHoverRadius: 4,
                                pointBackgroundColor: '#9ca3af',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 1.5,
                                borderWidth: 2,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                backgroundColor: 'rgba(17, 24, 39, 0.96)',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                padding: 12,
                                displayColors: true,
                                callbacks: {
                                    label: (context) => `${context.dataset.label}: ${Number(context.parsed.y ?? 0).toFixed(1)}%`,
                                    footer: () => 'Daily productivity score, not hours worked',
                                },
                            },
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                },
                                ticks: {
                                    color: '#6b7280',
                                    maxRotation: 0,
                                    autoSkip: true,
                                },
                                border: {
                                    color: '#e5e7eb',
                                },
                            },
                            y: {
                                min: 0,
                                max: 100,
                                ticks: {
                                    color: '#6b7280',
                                    callback: (value) => `${value}%`,
                                    stepSize: 20,
                                },
                                grid: {
                                    color: 'rgba(229, 231, 235, 0.9)',
                                },
                                border: {
                                    color: '#e5e7eb',
                                },
                            },
                        },
                    },
                });

                window.monitorTrendChart = trendChart;
            };

            $('body').off('click.monitorTrendToggle').on('click.monitorTrendToggle', '[data-range-toggle]', function () {
                const range = $(this).data('range-toggle').toString();
                renderChart(range);
            });

            renderChart('7');
        });
    </script>
@endpush
