@php
    $compositeTier = \Modules\Monitor\Services\Analytics\MonitorAnalyticsHelper::scoreTier($composite);
    $compositeTextClass = match ($compositeTier) {
        'green' => 'text-success',
        'yellow', 'orange' => 'text-warning',
        default => 'text-danger',
    };
    $compositeBarClass = match ($compositeTier) {
        'green' => 'progress-bar-success',
        'yellow', 'orange' => 'progress-bar-warning',
        default => 'progress-bar-danger',
    };
@endphp
<div class="p-20">
    <p class="f-14 text-dark-grey mb-3">@lang('monitor::app.complianceBasedOn7Days')</p>

    <div class="card bg-additional-grey border-0 b-shadow-4 mb-3">
        <div class="card-body p-20 text-center">
            <p class="monitor-compliance-score mb-1 {{ $compositeTextClass }}">{{ $composite }}%</p>
            <p class="f-14 text-dark-grey mb-3">@lang('monitor::app.overallCompliance')</p>
            <div class="monitor-usage-progress" style="height: 12px;">
                <div class="progress-bar {{ $compositeBarClass }}" style="width: {{ $composite }}%"></div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6 mb-3 mb-md-0">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-body p-20">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-2">@lang('monitor::app.complianceAgentCoverage')</h4>
                    <p class="f-21 font-weight-bold text-darkest-grey mb-1">{{ $agent_coverage }}%</p>
                    <p class="f-12 text-dark-grey mb-2">@lang('monitor::app.complianceAgentCoverageDetail', ['active' => $active_agent_count, 'total' => $total_employees])</p>
                    <div class="monitor-usage-progress">
                        <div class="progress-bar bg-primary" style="width: {{ $agent_coverage }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-body p-20">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-2">@lang('monitor::app.complianceAcceptableUse')</h4>
                    <p class="f-21 font-weight-bold text-darkest-grey mb-1">{{ $acceptable_use }}%</p>
                    <p class="f-12 text-dark-grey mb-2">@lang('monitor::app.complianceAcceptableDetail', ['count' => $acceptable_count, 'total' => $total_employees, 'threshold' => $threshold])</p>
                    <div class="monitor-usage-progress">
                        <div class="progress-bar progress-bar-success" style="width: {{ $acceptable_use }}%"></div>
                    </div>
                    <p class="mt-2 mb-0 f-12 text-lightest">
                        <a href="{{ $config_url }}" class="text-primary">@lang('monitor::app.agentConfig')</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if (count($non_compliant) > 0)
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">@lang('monitor::app.nonCompliantEmployees')</h4>
            <a href="{{ route('monitor.analytics.export', ['tab' => 'compliance']) }}" class="f-12 text-primary">@lang('monitor::app.exportCsv')</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover w-100">
                <thead>
                    <tr>
                        <th class="f-12 text-dark-grey">@lang('app.employee')</th>
                        <th class="f-12 text-dark-grey">@lang('monitor::app.issue')</th>
                        <th class="f-12 text-dark-grey">@lang('monitor::app.dimension')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($non_compliant as $row)
                        <tr>
                            <td class="f-14 f-w-500">{{ $row['name'] }}</td>
                            <td class="f-14 text-dark-grey">{{ $row['issue'] }}</td>
                            <td class="f-14">
                                <span class="badge {{ $row['dimension'] === 'coverage' ? 'badge-danger' : 'badge-warning' }}">
                                    {{ $row['dimension_label'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <x-alert type="success" icon="check-circle">
            @lang('monitor::app.allCompliant')
        </x-alert>
    @endif
</div>
