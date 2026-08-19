@php
    $departments = $dashboard['department_stats'] ?? [];
    $productivityDistribution = $dashboard['productivity_distribution'] ?? [];
    $currentActivity = $dashboard['current_activity'] ?? [];
    $applicationUsage = $dashboard['application_usage'] ?? [];
@endphp

<x-cards.data class="mb-0">
    <details open>
        <summary class="cursor-pointer list-none border-0 bg-white p-0 mb-0">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="f-18 f-w-500 text-darkest-grey mb-1">Workforce Analytics</h4>
                    <p class="f-12 text-lightest mb-0">Supportive diagnostics for deeper investigation</p>
                </div>
                <span class="f-12 text-lightest">Collapse</span>
            </div>
        </summary>

        <div class="mt-4">
            <div class="row">
                <div class="col-xl-4 col-lg-12 mb-3">
                    @include('monitor::dashboard.partials.department-health-widget', [
                        'headline' => 'Department Status',
                        'subtitle' => 'Online versus offline balance by department',
                        'departments' => $departments,
                    ])
                </div>

                <div class="col-xl-4 col-lg-12 mb-3">
                    @include('monitor::dashboard.partials.insight-card', [
                        'title' => 'Productivity Distribution',
                        'subtitle' => 'Where the team sits across productivity bands',
                        'items' => $productivityDistribution,
                    ])
                </div>

                <div class="col-xl-4 col-lg-12 mb-3">
                    @include('monitor::dashboard.partials.insight-card', [
                        'title' => 'Current Activity Summary',
                        'subtitle' => 'Live state of the monitored workforce',
                        'items' => $currentActivity,
                    ])
                </div>
            </div>

            @if (!empty($applicationUsage))
                <div class="card bg-additional-grey border-0 p-20">
                    <p class="f-14 f-w-500 text-darkest-grey mb-1">Application Usage</p>
                    <p class="f-12 text-lightest mb-4">What the team is using right now</p>
                    <div class="row">
                        @foreach ($applicationUsage as $app)
                            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                                <div class="card bg-white border-0 b-shadow-4 p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="mb-0 f-14 text-darkest-grey text-truncate">{{ $app['label'] }}</p>
                                        <span class="f-12 font-weight-bold text-lightest ml-2">{{ number_format((float) ($app['pct'] ?? 0), 1) }}%</span>
                                    </div>
                                    <div class="progress mt-2" style="height: 8px; margin-bottom: 0;">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                            style="width: {{ max(0, min(100, (float) ($app['pct'] ?? 0))) }}%"></div>
                                    </div>
                                    <p class="f-12 text-lightest mb-0 mt-2">{{ number_format((int) ($app['value'] ?? 0)) }} active employees</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </details>
</x-cards.data>
