@php
    $summary = $appsSummary ?? [];
    $apps = collect($activeApps ?? []);
    $timeAllocation = collect($summary['time_allocation'] ?? []);
    $categoryDistribution = collect($summary['category_distribution'] ?? []);
    $positiveSignals = collect($summary['positive_signals'] ?? []);
    $attentionItems = collect($summary['attention_items'] ?? []);
    $timeline = collect($summary['timeline'] ?? []);
    $topApps = $apps->take(6);
    $flatLogs = $apps->flatMap(function (array $app) {
        return collect($app['sessions'] ?? [])->map(function (array $session) use ($app) {
            return array_merge($session, [
                'app_name' => $app['app_name'] ?? ($session['process_name'] ?? 'Unknown'),
                'icon_url' => $app['icon_url'] ?? null,
                'letter_avatar' => $app['letter_avatar'] ?? null,
                'app_category' => $app['category'] ?? null,
                'app_category_label' => $app['category_label'] ?? null,
                'started_timestamp' => $session['started_timestamp'] ?? 0,
                'ended_timestamp' => $session['ended_timestamp'] ?? null,
                'trend_vs_average_label' => $app['trend_vs_average_label'] ?? 'Within normal range',
                'trend_vs_average_tone' => $app['trend_vs_average_tone'] ?? 'gray',
            ]);
        });
    })->sortBy('started_timestamp')->values();
    $activeTimeLabel = $summary['total_duration_label'] ?? '0m';
@endphp

<div class="p-20">
    <div class="row">
        <div class="col-lg-8 mb-3">
            @include('monitor::employees.ajax.partials.application-summary-card', [
                'summary' => $summary,
            ])

            @include('monitor::employees.ajax.partials.ai-summary-card', [
                'summary' => $summary,
            ])
        </div>

        <div class="col-lg-4 mb-3">
            @include('monitor::employees.ajax.partials.application-health-card', [
                'summary' => $summary,
            ])
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-3">
            @include('monitor::employees.ajax.partials.time-allocation-chart', [
                'timeAllocation' => $timeAllocation,
                'activeTimeLabel' => $activeTimeLabel,
            ])

            @include('monitor::employees.ajax.partials.application-cards', [
                'apps' => $topApps,
            ])

            @include('monitor::employees.ajax.partials.anomaly-card', [
                'positiveSignals' => $positiveSignals,
                'attentionItems' => $attentionItems,
            ])

            @include('monitor::employees.ajax.partials.activity-timeline', [
                'timeline' => $timeline,
            ])

            @include('monitor::employees.ajax.partials.detailed-activity-table', [
                'logs' => $flatLogs,
            ])
        </div>

        <div class="col-lg-4 mb-3">
            @include('monitor::employees.ajax.partials.category-distribution-card', [
                'categoryBuckets' => $categoryDistribution,
            ])
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(function () {
            $('body').off('click.monitorLogDetails').on('click.monitorLogDetails', '[data-log-toggle]', function () {
                const target = $(this).data('log-toggle');
                const $row = $(target);
                const expanded = $(this).attr('aria-expanded') === 'true';

                $(this).attr('aria-expanded', expanded ? 'false' : 'true');
                $row.toggleClass('d-none', expanded);
                $(this).find('i').toggleClass('fa-plus', expanded).toggleClass('fa-minus', !expanded);
            });

            $('body').off('click.monitorLogsCollapse').on('click.monitorLogsCollapse', '[data-logs-toggle]', function () {
                const $panel = $('[data-logs-panel]').first();
                const expanded = $panel.hasClass('d-none');

                $panel.toggleClass('d-none', !expanded);
                $(this).find('span').text(expanded ? 'Collapse detailed log' : 'Expand detailed log');
                $(this).find('i').toggleClass('fa-plus', !expanded).toggleClass('fa-minus', expanded);
            });
        });
    </script>
@endpush
