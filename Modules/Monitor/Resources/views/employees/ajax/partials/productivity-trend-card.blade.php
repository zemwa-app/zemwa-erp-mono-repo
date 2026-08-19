<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Productivity Trend</h4>
                <p class="f-12 text-lightest mb-0 mt-1">Employee activity score by day, compared with the team average for the same period.</p>
            </div>
            <div class="d-flex">
                <button type="button" class="btn btn-primary btn-sm rounded f-12 mr-2" data-range-toggle="7">7-day</button>
                <button type="button" class="btn btn-secondary btn-sm rounded f-12" data-range-toggle="30">30-day</button>
            </div>
        </div>
    </div>
    <div class="card-body p-20"
        data-trend-chart
        data-trend-7='@json($trend7)'
        data-trend-30='@json($trend30)'>
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <div class="d-flex flex-wrap">
                <span class="badge badge-secondary mr-2 mb-2" data-trend-comparison>{{ $trendComparison['label'] ?? '+0.0% vs last week' }}</span>
                <span class="badge badge-secondary mr-2 mb-2">This is daily productivity score, not hours worked</span>
                <span class="badge badge-secondary mb-2">Dark line = employee, gray line = team</span>
            </div>
            <div class="d-flex flex-wrap">
                <span class="badge badge-success mr-2 mb-2" data-best-day>
                    {{ $trend7['best_day'] ? $trend7['best_day']['label'] . ' · ' . number_format($trend7['best_day']['employee_score'] ?? 0, 1) . '%' : 'Best day not available' }}
                </span>
                <span class="badge badge-warning mb-2" data-worst-day>
                    {{ $trend7['worst_day'] ? $trend7['worst_day']['label'] . ' · ' . number_format($trend7['worst_day']['employee_score'] ?? 0, 1) . '%' : 'Worst day not available' }}
                </span>
            </div>
        </div>

        <div class="bg-grey rounded p-20">
            <div style="position:relative;height:288px;">
                <canvas data-trend-canvas class="w-100" style="height:288px;"></canvas>
            </div>
            <div class="row mt-3">
                @foreach ([
                    ['label' => 'Employee average', 'attr' => 'data-avg-label', 'value' => number_format($trend7['employee_avg'] ?? 0, 1) . '%'],
                    ['label' => 'Range', 'attr' => 'data-range-label', 'value' => '7-day trend'],
                    ['label' => 'Best day', 'attr' => 'data-best-summary', 'value' => $trend7['best_day'] ? $trend7['best_day']['label'] . ' led the week' : 'No best day available'],
                    ['label' => 'Worst day', 'attr' => 'data-worst-summary', 'value' => $trend7['worst_day'] ? $trend7['worst_day']['label'] . ' needs review' : 'No worst day available'],
                ] as $metric)
                    <div class="col-sm-6 mb-3">
                        <div class="bg-white border-grey rounded p-3">
                            <div class="f-11 text-lightest text-uppercase">{{ $metric['label'] }}</div>
                            <div class="f-14 f-w-500 text-darkest-grey mt-1" {{ $metric['attr'] }}>{{ $metric['value'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
